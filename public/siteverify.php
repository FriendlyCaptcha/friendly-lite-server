<?php

/**
 * https://github.com/FriendlyCaptcha/friendly-pow
 */

header('Content-type: application/json');

$demoPayload = '09e0a9debed8e1448ea0135dd969a2e37e15fa8e65b77922c844bb86b1e72ca6.YXPJcEREREFERERBQUwwhURERERERERE.AAAAAMGpAQABAAAAQiQAAAIAAADYkwIAAwAAADsnAAAEAAAAOB4AAAUAAAC+eAEABgAAAPrVAQAHAAAA+UcCAAgAAADjIwIACQAAAHMYAQAKAAAAqGEDAAsAAADCLQAADAAAAGGSAQANAAAAfQUAAA4AAACNFwAADwAAADsnAAAQAAAAq60CABEAAACJhAMAEgAAAJ89AgATAAAAuV4DABQAAADa1QIAFQAAAD8/AAAWAAAAmU0AABcAAACcAAAAGAAAAKDgAAAZAAAArIIBABoAAAAx1QAAGwAAAFQKBgAcAAAAl4QAAB0AAABcIwAAHgAAAKFpAgAfAAAADGAAACAAAAB5VwAAIQAAALhhAAAiAAAAPjwDACMAAAD31AAAJAAAAD4wBAAlAAAAy5QBACYAAABGCwAAJwAAAIliAAAoAAAAJVoCACkAAADyOwEAKgAAAHTLAAArAAAAgpkAACwAAADHMAQALQAAAEPUAAAuAAAAkMgAAC8AAADhLwQA.AgAD';

$payload = $demoPayload;

list($signature, $puzzle, $solutions, $diagnostics) = explode('.', $payload);
$puzzleHex = bin2hex(base64_decode($puzzle));
$numberOfSolutions = hexdec(extractHexBytes($puzzleHex, 14, 1));
$timeStamp = hexdec(extractHexBytes($puzzleHex, 0, 4));
$expiry = hexdec(extractHexBytes($puzzleHex, 13, 1));
$expiryInSeconds = $expiry * 300;

echo "timeStamp: " . $timeStamp . PHP_EOL;
$age = time()- $timeStamp;
echo "age:" . $age . PHP_EOL;

if ($expiry == 0) {
    echo "does not expire" . PHP_EOL;
} else {
    if ($age <= $expiry) {
        echo "puzzle is young enough" . PHP_EOL;
    } else {
        echo "puzzle is too old" . PHP_EOL;
    }
}

echo "numberOfSolutions: " . $numberOfSolutions . PHP_EOL;

$d = hexdec(extractHexBytes($puzzleHex, 15, 1));
echo "d: " . $d . PHP_EOL;
$T = floor(pow(2, (255.999 - $d) / 8.0));
echo "T: " . $T . PHP_EOL;
$Thex = dechex($T);
echo $Thex . PHP_EOL;

//var_dump($solutions);

for ($solutionIndex = 0; $solutionIndex < $numberOfSolutions; $solutionIndex++) {
    $currentSolution = substr(bin2hex(base64_decode($solutions)), $solutionIndex * 16, 16);
    $fullSolution = padHex($puzzleHex, 120) . $currentSolution;

    /** @source https://lindevs.com/generate-blake2b-hash-using-php/ */

    $blake2b256hash = bin2hex(sodium_crypto_generichash(hex2bin($fullSolution), '', 32));
    $first4Bytes = extractHexBytes($blake2b256hash, 0, 4);

    if ($first4Bytes < $Thex) {
        echo $currentSolution . ' is valid' . PHP_EOL;
    }
}

function padHex($hexValue, $bytes)
{
    return str_pad($hexValue, $bytes * 2, 0, STR_PAD_LEFT);
}

function extractHexBytes($string, $offset, $count)
{
    return substr($string, $offset * 2, $count * 2);
}

