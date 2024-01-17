<?php

use FriendlyCaptcha\Lite\Captcha;
use FriendlyCaptcha\Lite\Exceptions\EmptySolutionException;
use FriendlyCaptcha\Lite\Exceptions\TimeoutOrDuplicateException;
use FriendlyCaptcha\Lite\Exceptions\WrongApiKeyException;
use FriendlyCaptcha\Lite\Polite;

require_once 'vendor/autoload.php';

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

$captcha = new Captcha();

try {
    if($captcha->verifyPuzzle($apiKey, $solution)) {
        Polite::returnValid();
    } else {
        Polite::returnSolutionInvalid();
    }
} catch(TimeoutOrDuplicateException $e) {
    Polite::returnSolutionTimeoutOrDuplicate();
} catch(WrongApiKeyException $e) {
    Polite::returnWrongApiKeyError();
} catch(EmptySolutionException $e) {
    Polite::returnErrorEmptySolution();
}