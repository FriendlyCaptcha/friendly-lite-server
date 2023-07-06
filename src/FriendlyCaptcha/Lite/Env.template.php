<?php
namespace FriendlyCaptcha\Lite;

class Env {

    /* 
     * Adjustable constants 
     */

    private const LOG_FILE =  __DIR__ . '/../logs/polite.log'; // set false to disable

    private const SECRET = 'FILL-YOUR-SECRET-HERE';
    private const API_KEY = 'test';

    // Difficulty 2 = 51 Solutions, 122 Difficulty
    // Difficulty 4 = 51 Solutions, 130 Difficulty
    // Difficulty 9 = 45 Solutions, 141 Difficulty
    // Difficulty 18 = 45 Solutions, 149 Difficulty

    private const SCALING_TTL_SECONDS = 30 * 60;
    private const SCALING =  // calls from the same IP shortended address in the past 30 minutes => difficulty
        [
            0 => ['solutions' => 51, 'difficulty' => 122],
            4 => ['solutions' => 51, 'difficulty' => 130],
            10 => ['solutions' => 45, 'difficulty' => 141],
            20 => ['solutions' => 45, 'difficulty' => 149],
        ];
    private const EXPIRY_TIMES_5_MINUTES = 12; // 1 hour

    /*
     * Functions
     */

    private static function getFromEnvironmentWithFallback($key, $fallback) {
        // If variables are set via environment (for example when using Docker),
        // these take precedence.
        $value = getenv($key);
        return $value === false ? $fallback : $value;
    }

    public static function getLogFile() : string|bool {
        $val = self::getFromEnvironmentWithFallback('LOG_FILE', self::LOG_FILE);
        if ($val === false || $val === 'false') {
            return false;
        }
        return $val;
    }

    public static function getSecret() : string {
        return self::getFromEnvironmentWithFallback('SECRET', self::SECRET);
    }

    public static function getApiKey() : string {
        return self::getFromEnvironmentWithFallback('API_KEY', self::API_KEY);
    }

    public static function getScalingTtlSeconds() : int {
        return intval(self::getFromEnvironmentWithFallback('SCALING_TTL_SECONDS', self::SCALING_TTL_SECONDS));
    }

    public static function getScaling() : array {
        return self::SCALING;
    }

    public static function getExpiryTimes5Minutes() : int {
        return intval(self::getFromEnvironmentWithFallback('EXPIRY_TIMES_5_MINUTES', self::EXPIRY_TIMES_5_MINUTES));
    }
}