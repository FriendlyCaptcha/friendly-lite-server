<?php

use FriendlyCaptcha\Captcha;
use FriendlyCaptcha\Polite;

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
