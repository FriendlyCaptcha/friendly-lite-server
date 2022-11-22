<?php

require_once 'env.php';
require_once 'polite.class.php';

/**
 * https://github.com/FriendlyCaptcha/friendly-pow
 */

header('Content-type: application/json');

if (isset($_POST['solution'])) {
    $solution = $_POST['solution'];
    $apiKey = $_POST['secret'];
} else {
    $inputRaw = file_get_contents('php://input');
    $input = json_decode($inputRaw, true);
    if (empty($input)) {
	$input = [];
	Polite::parseRawHttpInput($inputRaw, $input);
    }
    $solution = $input['solution'];
    $apiKey = $input['secret'];
}

// check API key
if ($apiKey != API_KEY){
    Polite::returnWrongApiKeyError();
}

if (empty($solution) || $solution[0] === '.') {
    Polite::log('Empty or pending solution: ' . $solution);
    Polite::returnErrorEmptySolution();
}

list($signature, $puzzle, $solutions, $diagnostics) = explode('.', $solution);
$puzzleBin = base64_decode($puzzle);
$puzzleHex = bin2hex($puzzleBin);

if (($calculated = Polite::signBuffer($puzzleBin)) !== $signature) {
    Polite::log(sprintf('Signature mismatch. Calculated "%s", given "%s"', $calculated, $signature));
    Polite::returnSolutionInvalid();
}

// only need to store as long as valid, after that the timeout will kick in
if (!apcu_add($puzzleHex, true, EXPIRY_TIMES_5_MINUTES * 300)) {
    Polite::log(sprintf('Puzzle "%s" was already successfully used before', $puzzleHex));
    Polite::returnSolutionTimeoutOrDuplicate();
}

$numberOfSolutions = hexdec(Polite::extractHexBytes($puzzleHex, 14, 1));
$timeStamp = hexdec(Polite::extractHexBytes($puzzleHex, 0, 4));
$expiry = hexdec(Polite::extractHexBytes($puzzleHex, 13, 1));
$expiryInSeconds = $expiry * 300;
$solutionsHex = bin2hex(base64_decode($solutions));

Polite::log('puzzleHex: ' . $puzzleHex);

Polite::log("timeStamp: " . $timeStamp);
$age = time() - $timeStamp;
Polite::log("age:" . $age);

if ($expiry == 0) {
    Polite::log("does not expire" );
} else {
    if ($age <= $expiryInSeconds) {
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


$solutionSeenInThisRequest = [];

for ($solutionIndex = 0; $solutionIndex < $numberOfSolutions; $solutionIndex++) {
    $currentSolution = Polite::extractHexBytes($solutionsHex, $solutionIndex * 8, 8);

    if (isset($solutionSeenInThisRequest[$currentSolution])) {
        Polite::log('Solution seen in this request before');
        Polite::returnSolutionInvalid();
    }
    $solutionSeenInThisRequest[$currentSolution] = true;

    $fullSolution = Polite::padHex($puzzleHex, 120, STR_PAD_RIGHT) . $currentSolution;

    Polite::log('fullsolution length: ' . strlen($fullSolution));
    Polite::log('fullsolution: ' . $fullSolution);

    $blake2b256hash = bin2hex(sodium_crypto_generichash(hex2bin($fullSolution), '', 32));
    Polite::log('Blake hash: ' . $blake2b256hash);
    $first4Bytes = Polite::extractHexBytes($blake2b256hash, 0, 4);
    $first4Int = Polite::littleEndianHexToDec($first4Bytes);

    if ($first4Int < $T) {
        Polite::log($currentSolution . ' is valid');
    } else {
        Polite::log($currentSolution . ' (index: ' . $solutionIndex . ') is invalid (' . $first4Int . ' >= ' . $T . ')');
        Polite::returnSolutionInvalid();
    }
}

Polite::log('all valid');
Polite::returnValid();
