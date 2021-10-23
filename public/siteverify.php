<?php

require_once 'env.php';
require_once 'polite.class.php';

/**
 * https://github.com/FriendlyCaptcha/friendly-pow
 */

header('Content-type: application/json');

$inputJson = file_get_contents('php://input');
if ($_POST['solution']) {
    $solution = $_POST['solution'];
} else {
    $input = json_decode($inputJson, true);
    $solution = $input['solution'];
}

if (empty($solution)) {
    Polite::returnErrorEmptySolution();
}

list($signature, $puzzle, $solutions, $diagnostics) = explode('.', $solution);
$puzzleBin = base64_decode($puzzle);
$puzzleHex = bin2hex($puzzleBin);

if (($calculated = Polite::signBuffer($puzzleBin)) !== $signature) {
    Polite::log(sprintf('Signature mismatch. Calculated "%s", given "%s"', $calculated, $signature));
    Polite::returnSolutionInvalid();
}

$numberOfSolutions = hexdec(Polite::extractHexBytes($puzzleHex, 14, 1));
$timeStamp = hexdec(Polite::extractHexBytes($puzzleHex, 0, 4));
$expiry = hexdec(Polite::extractHexBytes($puzzleHex, 13, 1));
$expiryInSeconds = $expiry * 300;
$solutionsHex = bin2hex(base64_decode($solutions));

Polite::log('puzzleHex: ' . $puzzleHex);

Polite::log("timeStamp: " . $timeStamp);
$age = time()- $timeStamp;
Polite::log("age:" . $age);

if ($expiry == 0) {
    Polite::log("does not expire" );
} else {
    if ($age <= $expiry) {
        Polite::log("puzzle is young enough");
    } else {
        Polite::log(sprintf("puzzle is too old (%d seconds, allowed: %d", $age, $expiry));
        Polite::returnSolutionTimeoutOrDuplicate();
    }
}

Polite::log("numberOfSolutions: " . $numberOfSolutions);

$d = hexdec(Polite::extractHexBytes($puzzleHex, 15, 1));
Polite::log("d: " . $d);
$T = floor(pow(2, (255.999 - $d) / 8.0));
Polite::log("T: " . $T);

for ($solutionIndex = 0; $solutionIndex < $numberOfSolutions; $solutionIndex++) {
    $currentSolution = Polite::extractHexBytes($solutionsHex, $solutionIndex * 8, 8);
    $fullSolution = Polite::padHex($puzzleHex, 120, STR_PAD_RIGHT) . $currentSolution;

    Polite::log('fullsolution length: ' . strlen($fullSolution));
    Polite::log('fullsolution: ' . $fullSolution);
    /** @source https://lindevs.com/generate-blake2b-hash-using-php/ */

    $blake2b256hash = bin2hex(sodium_crypto_generichash(hex2bin($fullSolution), '', 32));
    Polite::log('Blake hash: ' . $blake2b256hash);
    $first4Bytes = Polite::extractHexBytes($blake2b256hash, 0, 4);
    $first4Int = Polite::littleEndianHexToDec($first4Bytes);

    if ($first4Int < $T) {
        Polite::log($currentSolution . ' is valid');
    } else {
        Polite::log($currentSolution . ' (index: ' . $solutionIndex . ') is invalid (' . $first4Int . ' >= ' . $T . ')');

        $result = [
            'success' => false,
            'error' => 'solution_invalid',
        ];

        http_response_code(200);
        echo json_encode($result);
        exit(0);
    }
}


Polite::log('all valid');
$result = [
    'success' => true,
];

echo json_encode($result);
