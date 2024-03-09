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
     * @return integer
     */
    public static function convertPencesToPounds(float $amount): int
    {
        return round($amount / 100, 2, PHP_ROUND_HALF_UP);
    }

    /**
     * Convert pounds to pences
     *
     * @param float $amount
     * 
     * @return integer
     */
    public static function convertPoundsToPence(float $amount): int
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
        return $isAmountInPounds ? preg_replace("/[^0-9.]/", "" , $amount) : preg_replace("/[^0-9]/", "" , $amount);
    }
}
