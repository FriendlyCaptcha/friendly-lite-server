<?php

require_once 'env.php';
require_once 'polite.class.php';
require_once 'captcha.class.php';

Polite::cors();

header('Content-type: application/json');

$captcha = new Captcha();

$result = [
        'data' => [
                'puzzle' => $captcha->buildPuzzle($_SERVER['REMOTE_ADDR'])
            ]
        ];

echo json_encode($result);
