<?php

define('LOG_FILE', __DIR__ . '/../logs/polite.log'); // set false to disable

define('SECRET', 'FILL-YOUR-SECRET-HERE');
define('API_KEY', 'FILL-YOUR-API-KEY-HERE');

// Difficulty 2 = 51 Solutions, 122 Difficulty
// Difficulty 4 = 51 Solutions, 130 Difficulty
// Difficulty 9 = 45 Solutions, 141 Difficulty
// Difficulty 18 = 45 Solutions, 149 Difficulty

define('SCALING_TTL_SECOUNDS', 30 * 60);
define('SCALING', // calls from the same IP shortended address in the past 30 minutes => difficulty
    [
        0 => ['solutions' => 51, 'difficulty' => 122],
        4 => ['solutions' => 51, 'difficulty' => 130],
        10 => ['solutions' => 45, 'difficulty' => 141],
        20 => ['solutions' => 45, 'difficulty' => 149],
    ]
);
define('EXPIRY_TIMES_5_MINUTES', 12); // 1 hour