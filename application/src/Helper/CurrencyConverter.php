<?php

namespace App\Helper;

use App\Model\Coins;

class CurrencyConverter
{
    /**
     * Convert pences to pounds
     *
     * @param float $amount
     *
     * @return float
     */
    public static function convertPencesToPounds(float $amount): float
    {
        return round($amount / 100, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Convert pounds to pences
     *
     * @param float $amount
     *
     * @return float
     */
    public static function convertPoundsToPence(float $amount): float
    {
        return round($amount * 100, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Determines if the amount is in pounds or pence
     *
     * @param string $amount
     *
     * @return boolean
     */
    public static function isAmountInPounds(string $amount): bool
    {
        return strpbrk($amount, Coins::CURRENCY)
            || strpbrk($amount, '.') && strpbrk($amount, 'p')
            || strpbrk($amount, '.') && !strpbrk($amount, Coins::CURRENCY . 'p')
        ;
    }

    /**
     * Remove currency symbols etc and keep only numbers
     *
     * @param string $amount
     *
     * @return string
     */
    public static function formatCurrency(string $amount, bool $isAmountInPounds): string
    {
        return $isAmountInPounds ? preg_replace("/[^0-9.]/", "", $amount) : preg_replace("/[^0-9]/", "", $amount);
    }
}
