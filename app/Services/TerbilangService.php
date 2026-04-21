<?php

namespace App\Services;

/**
 * Terbilang Service
 *
 * Converts a numeric amount to Indonesian words (terbilang).
 * Handles 1 to 999,999,999,999 (ratusan miliar), decimals, and negatives.
 *
 * Examples:
 *   1500000   → "Satu Juta Lima Ratus Ribu Rupiah"
 *   10500.50  → "Sepuluh Ribu Lima Ratus Rupiah Lima Puluh Sen"
 *   -5000     → "Minus Lima Ribu Rupiah"
 *
 * @package App\Services
 */
class TerbilangService
{
    private const ONES = [
        '',
        'Satu',
        'Dua',
        'Tiga',
        'Empat',
        'Lima',
        'Enam',
        'Tujuh',
        'Delapan',
        'Sembilan',
        'Sepuluh',
        'Sebelas',
        'Dua Belas',
        'Tiga Belas',
        'Empat Belas',
        'Lima Belas',
        'Enam Belas',
        'Tujuh Belas',
        'Delapan Belas',
        'Sembilan Belas',
    ];

    private const TENS = [
        '',
        '',
        'Dua Puluh',
        'Tiga Puluh',
        'Empat Puluh',
        'Lima Puluh',
        'Enam Puluh',
        'Tujuh Puluh',
        'Delapan Puluh',
        'Sembilan Puluh',
    ];

    /**
     * Convert a numeric amount to Indonesian terbilang text.
     *
     * @param int|float $amount
     * @return string
     */
    public static function convert(int|float $amount): string
    {
        $isNegative = $amount < 0;
        $amount     = abs($amount);

        // Split integer and decimal parts
        $parts       = explode('.', number_format($amount, 2, '.', ''));
        $integerPart = (int) $parts[0];
        $decimalPart = isset($parts[1]) ? (int) $parts[1] : 0;

        $result = self::convertInteger($integerPart) . ' Rupiah';

        if ($decimalPart > 0) {
            $result .= ' ' . self::convertInteger($decimalPart) . ' Sen';
        }

        $result = trim($result);

        if ($isNegative) {
            $result = 'Minus ' . $result;
        }

        return $result;
    }

    /**
     * Convert a non-negative integer to Indonesian words.
     *
     * @param int $n
     * @return string
     */
    private static function convertInteger(int $n): string
    {
        if ($n === 0) {
            return 'Nol';
        }

        if ($n < 0) {
            return 'Minus ' . self::convertInteger(-$n);
        }

        if ($n < 20) {
            return self::ONES[$n];
        }

        if ($n < 100) {
            $tens = self::TENS[(int) ($n / 10)];
            $ones = self::ONES[$n % 10];
            return $ones ? $tens . ' ' . $ones : $tens;
        }

        if ($n < 200) {
            // "Seratus ..." instead of "Satu Ratus ..."
            $remainder = $n - 100;
            return $remainder > 0
                ? 'Seratus ' . self::convertInteger($remainder)
                : 'Seratus';
        }

        if ($n < 1_000) {
            $hundreds  = (int) ($n / 100);
            $remainder = $n % 100;
            $text      = self::ONES[$hundreds] . ' Ratus';
            return $remainder > 0 ? $text . ' ' . self::convertInteger($remainder) : $text;
        }

        if ($n < 2_000) {
            // "Seribu ..." instead of "Satu Ribu ..."
            $remainder = $n - 1_000;
            return $remainder > 0
                ? 'Seribu ' . self::convertInteger($remainder)
                : 'Seribu';
        }

        if ($n < 1_000_000) {
            $thousands = (int) ($n / 1_000);
            $remainder = $n % 1_000;
            $text      = self::convertInteger($thousands) . ' Ribu';
            return $remainder > 0 ? $text . ' ' . self::convertInteger($remainder) : $text;
        }

        if ($n < 1_000_000_000) {
            $millions  = (int) ($n / 1_000_000);
            $remainder = $n % 1_000_000;
            $text      = self::convertInteger($millions) . ' Juta';
            return $remainder > 0 ? $text . ' ' . self::convertInteger($remainder) : $text;
        }

        // Up to 999,999,999,999 (ratusan miliar)
        $billions  = (int) ($n / 1_000_000_000);
        $remainder = $n % 1_000_000_000;
        $text      = self::convertInteger($billions) . ' Miliar';
        return $remainder > 0 ? $text . ' ' . self::convertInteger($remainder) : $text;
    }
}
