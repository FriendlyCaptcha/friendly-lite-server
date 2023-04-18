<?php
require_once 'env.php';
require_once 'polite.class.php';
require_once 'exceptions/emptySolutionException.class.php';
require_once 'exceptions/timeoutOrDuplicateException.class.php';
require_once 'exceptions/wrongApiKeyException.class.php';

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

    public function verifyPuzzle(string $apiKey, string $solution): bool {
        // check API key
        if ($apiKey != API_KEY){
            throw new WrongApiKeyException();
        }

        if (empty($solution) || $solution[0] === '.') {
            Polite::log('Empty or pending solution: ' . $solution);
            throw new EmptySolutionException();
        }

        list($signature, $puzzle, $solutions, $diagnostics) = explode('.', $solution);
        $puzzleBin = base64_decode($puzzle);
        $puzzleHex = bin2hex($puzzleBin);

        if (($calculated = Polite::signBuffer($puzzleBin)) !== $signature) {
            Polite::log(sprintf('Signature mismatch. Calculated "%s", given "%s"', $calculated, $signature));
            return false;
        }

        // only need to store as long as valid, after that the timeout will kick in
        if (!apcu_add($puzzleHex, true, EXPIRY_TIMES_5_MINUTES * 300)) {
            Polite::log(sprintf('Puzzle "%s" was already successfully used before', $puzzleHex));
            throw new TimeoutOrDuplicateException();
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
                throw new TimeoutOrDuplicateException();
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
                return false;
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
                return false;
            }
        }

        Polite::log('all valid');
        return true;
    }
}