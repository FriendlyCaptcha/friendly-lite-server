<?php

require_once 'env.php';
require_once 'polite.class.php';
require_once 'captcha.class.php';
require_once 'exceptions/emptySolutionException.class.php';
require_once 'exceptions/timeoutOrDuplicateException.class.php';
require_once 'exceptions/wrongApiKeyException.class.php';


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