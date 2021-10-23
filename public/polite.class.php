<?php

class Polite
{
    public static function cors(): void
    {
        // Allow from any origin
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
            // you want to allow, and if so:
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }

        // Access-Control headers are received during OPTIONS requests
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
                // may also be using PUT, PATCH, HEAD etc
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

            exit(0);
        }
    }

    public static function padHex(string $hexValue, int $bytes, $where = STR_PAD_LEFT): string
    {
        return str_pad($hexValue, $bytes * 2, '0', $where);
    }

    public static function extractHexBytes($string, $offset, $count)
    {
        return substr($string, $offset * 2, $count * 2);
    }

    public static function log($message)
    {
        error_log($message);
    }

    public function littleEndianHexToDec(string $hexValue): int
    {
        $bigEndianHex = implode('', array_reverse(str_split($hexValue, 2)));
        return hexdec($bigEndianHex);
    }
}