<?php

header('Content-type: application/json');

$accountId = 1;
$appId = 1;
$puzzleVersion = 1;
$puzzleExpiry = 0x0c;
$numberOfSolutions = 0x30;
$puzzleDifficulty = 133;

$numberOfSolutions = 0x30;
$puzzleDifficulty = 0x85;

$nonce = random_bytes(8);

function padHex($hexValue, $bytes)
{
    return str_pad($hexValue, $bytes * 2, '0', STR_PAD_LEFT);
}

$timeHex = dechex(time());
$accountIdHex = padHex(dechex($accountId), 4);
$appIdHex = padHex(dechex($appId), 4);
$puzzleVersionHex = padHex(dechex($appId), 1);
$puzzleExpiryHex = padHex(dechex($puzzleExpiry), 1);
$numberOfSolutionsHex = padHex(dechex($numberOfSolutions), 1);
$puzzleDifficultyHex = padHex(dechex($puzzleDifficulty), 1);
$reservedHex = padHex('', 8);
$puuzleNonceHex = padHex(bin2hex($nonce), 8);

$bufferHex = padHex($timeHex, 4) . $accountIdHex . $appIdHex . $puzzleVersionHex . $puzzleExpiryHex . $numberOfSolutionsHex . $puzzleDifficultyHex . $reservedHex . $puzzleNonceHex;


$buffer = hex2bin($bufferHex);
$hash = hash_hmac('sha256', $buffer, 'mysecret');

$puzzle = $hash . '.' . base64_encode($buffer);


$result = [
        'data' => [
                'puzzle' => $puzzle
            ]
        ];

echo json_encode($result);
