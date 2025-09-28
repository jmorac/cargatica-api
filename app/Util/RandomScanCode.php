<?php

namespace App\Util;

use App\Util\Enums\ScanType;

class RandomScanCode {

    /**
     * @param string $prefix
     *
     * @return string
     */
    public function generate( $prefix = '' ): string
    {
        $allowedCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $allowedCharacters = $allowedCharacters . $allowedCharacters . $allowedCharacters;
        return $prefix . substr( str_shuffle( $allowedCharacters ), 0, 25 );
    }

    /**
     * @param $scanCode
     *
     * @return bool
     */
    public static function isValid( $scanCode ): bool
    {
        return strlen( $scanCode ) === 33 || strlen( $scanCode ) === 63  || strlen( $scanCode ) === 26;
    }

    /**
     * @param $scanCode
     *
     * @return bool
     */
    public static function isBetScanCode( $scanCode ): bool
    {
        return RandomScanCode::isValid( $scanCode ) && $scanCode[0] === ScanType::BET;
    }

    /**
     * @param $scanCode
     *
     * @return bool
     */
    public static function isBetDepositCode( $scanCode ): bool
    {
        return RandomScanCode::isValid( $scanCode ) && $scanCode[0] === ScanType::DEPOSIT;
    }

}