<?php

require_once 'polite.class.php';
Polite::cors();

header('Content-type: application/json');

$accountId = 1;
$appId = 1;
$puzzleVersion = 1;
$puzzleExpiry = 0x0c;
$numberOfSolutions = 48;
$puzzleDifficulty = 133;


$numberOfSolutions = 20;
$puzzleDifficulty = 133;


$nonce = random_bytes(8);


$timeHex = dechex(time());
$accountIdHex = Polite::padHex(dechex($accountId), 4);
$appIdHex = Polite::padHex(dechex($appId), 4);
$puzzleVersionHex = Polite::padHex(dechex($appId), 1);
$puzzleExpiryHex = Polite::padHex(dechex($puzzleExpiry), 1);
$numberOfSolutionsHex = Polite::padHex(dechex($numberOfSolutions), 1);
$puzzleDifficultyHex = Polite::padHex(dechex($puzzleDifficulty), 1);
$reservedHex = Polite::padHex('', 8);
$puzzleNonceHex = Polite::padHex(bin2hex($nonce), 8);

$bufferHex = Polite::padHex($timeHex, 4) . $accountIdHex . $appIdHex . $puzzleVersionHex . $puzzleExpiryHex . $numberOfSolutionsHex . $puzzleDifficultyHex . $reservedHex . $puzzleNonceHex;


$buffer = hex2bin($bufferHex);
$hash = hash_hmac('sha256', $buffer, 'mysecret');

$puzzle = $hash . '.' . base64_encode($buffer);


$result = [
        'data' => [
                'puzzle' => $puzzle
            ]
        ];

echo json_encode($result);

