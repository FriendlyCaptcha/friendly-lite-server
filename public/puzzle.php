<?php

use FriendlyCaptcha\Lite\Captcha;
use FriendlyCaptcha\Lite\Polite;

require_once 'vendor/autoload.php';

Polite::cors();

header('Content-type: application/json');

$captcha = new Captcha();

$result = [
        'data' => [
                'puzzle' => $captcha->buildPuzzle($_SERVER['REMOTE_ADDR'])
            ]
        ];

echo json_encode($result);
