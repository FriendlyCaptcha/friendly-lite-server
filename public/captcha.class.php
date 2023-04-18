<?php
require_once 'env.php';
require_once 'polite.class.php';

class Captcha {
    public function buildPuzzle(string $remoteIp): string {
        $accountId = 1;
        $appId = 1;
        $puzzleVersion = 1;
        $puzzleExpiry = EXPIRY_TIMES_5_MINUTES;
        
        // smart scaling
        $anonymizedIp = Polite::anonymizeIp($remoteIp);
        $ipKey = 'ip_rate_limit_' . $anonymizedIp;

        if ($requestTimes = apcu_fetch($ipKey)) {
            $requestTimes++;
        } else {
            $requestTimes = 1;
        }
        apcu_store($ipKey, $requestTimes, SCALING_TTL_SECOUNDS);

        Polite::log(sprintf('This is request %d from IP %s in the last 30 minutes (or longer, if there were subsequent requests)', $requestTimes, $anonymizedIp));

        foreach(array_reverse(SCALING, true) as $threshold => $scale) {
            if ($requestTimes > $threshold) {
                $numberOfSolutions = $scale['solutions'];
                $puzzleDifficulty = $scale['difficulty'];
                break;
            }
        }

        if (!isset($numberOfSolutions) || !isset($puzzleDifficulty)) {
            die('Error in configuration');
        }

        Polite::log(sprintf('configured with %d solutions of %d difficulty', $numberOfSolutions, $puzzleDifficulty));


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
        $hash = Polite::signBuffer($buffer);

        $puzzle = $hash . '.' . base64_encode($buffer);
        return $puzzle;
    }
}