<?php

use PHPUnit\Framework\TestCase;
use FriendlyCaptcha\Lite\Env;

final class EnvTest extends TestCase {

    protected function tearDown(): void {
        // Clear env vars set by tests
        putenv('LOG_FILE');
        putenv('SECRET');
        putenv('API_KEY');
        putenv('SCALING_TTL_SECONDS');
        putenv('EXPIRY_TIMES_5_MINUTES');
    }

    public function testGetLogFileDefault(): void {
        $logFile = Env::getLogFile();
        $this->assertTrue(str_ends_with($logFile, '/../logs/polite.log'));
    }

    public function testGetLogFileEnvOverride(): void {
        putenv('LOG_FILE=foo');
        $logFile = Env::getLogFile();
        $this->assertEquals('foo', $logFile);
    }

    public function testGetLogFileReturnsFalse(): void {
        putenv('LOG_FILE=false');
        $logFile = Env::getLogFile();
        $this->assertFalse($logFile);
    }

    public function testGetSecretDefault(): void {
        $secret = Env::getSecret();
        $this->assertEquals('FILL-YOUR-SECRET-HERE', $secret);
    }
    
    public function testGetSecretEnvOverride(): void {
        putenv('SECRET=mySecret');
        $secret = Env::getSecret();
        $this->assertEquals('mySecret', $secret);
    }

    public function testGetApiKeyDefault(): void {
        $apiKey = Env::getApiKey();
        $this->assertEquals('test', $apiKey);
    }

    public function testGetApiKeyEnvOverride(): void {
        putenv('API_KEY=myApiKey');
        $apiKey = Env::getApiKey();
        $this->assertEquals('myApiKey', $apiKey);
    }

    public function testGetScalingTtlSecondsDefault(): void {
        $scalingTtlSeconds = Env::getScalingTtlSeconds();
        $this->assertEquals(30 * 60, $scalingTtlSeconds);
    }

    public function testGetScalingTtlSecondsEnvOverride(): void {
        putenv('SCALING_TTL_SECONDS=123');
        $scalingTtlSeconds = Env::getScalingTtlSeconds();
        $this->assertEquals(123, $scalingTtlSeconds);
    }

    public function testGetScalingDefault(): void {
        $scaling = Env::getScaling();
        $this->assertEquals(
            [
                0 => ['solutions' => 51, 'difficulty' => 122],
                4 => ['solutions' => 51, 'difficulty' => 130],
                10 => ['solutions' => 45, 'difficulty' => 141],
                20 => ['solutions' => 45, 'difficulty' => 149],
            ],
            $scaling
        );
    }

    public function testGetExpiryTimes5MinutesDefault(): void {
        $expiryTimes5Minutes = Env::getExpiryTimes5Minutes();
        $this->assertEquals(12, $expiryTimes5Minutes);
    }

    public function testGetExpiryTimes5MinutesEnvOverride(): void {
        putenv('EXPIRY_TIMES_5_MINUTES=123');
        $expiryTimes5Minutes = Env::getExpiryTimes5Minutes();
        $this->assertEquals(123, $expiryTimes5Minutes);
    }
}