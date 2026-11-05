<?php

namespace App\Support;

/**
 * Salary currencies offered on the Applicant form (card "LANDAS: Add
 * Applicant 'Currency'", 2026-11-05) and job positions.
 *
 * Kept in one place so the applicant salary select, the job position form
 * and any future report all speak the same codes. Values are ISO-4217
 * three letter codes (the DB column is varchar(3)).
 */
class Currencies
{
    /** code => [flag, label] */
    public const OPTIONS = [
        'PHP' => ['🇵🇭', 'PHP — Philippine Peso'],
        'USD' => ['🇺🇸', 'USD — US Dollar'],
        'SAR' => ['🇸🇦', 'SAR — Saudi Riyal'],
        'AED' => ['🇦🇪', 'AED — UAE Dirham'],
        'QAR' => ['🇶🇦', 'QAR — Qatari Riyal'],
        'KWD' => ['🇰🇼', 'KWD — Kuwaiti Dinar'],
        'BHD' => ['🇧🇭', 'BHD — Bahraini Dinar'],
        'OMR' => ['🇴🇲', 'OMR — Omani Rial'],
        'JOD' => ['🇯🇴', 'JOD — Jordanian Dinar'],
        'HKD' => ['🇭🇰', 'HKD — Hong Kong Dollar'],
        'SGD' => ['🇸🇬', 'SGD — Singapore Dollar'],
        'TWD' => ['🇹🇼', 'TWD — New Taiwan Dollar'],
        'JPY' => ['🇯🇵', 'JPY — Japanese Yen'],
        'KRW' => ['🇰🇷', 'KRW — South Korean Won'],
        'CAD' => ['🇨🇦', 'CAD — Canadian Dollar'],
        'ILS' => ['🇮🇱', 'ILS — Israeli Shekel'],
        'EUR' => ['🇪🇺', 'EUR — Euro'],
    ];

    public const DEFAULT = 'PHP';

    /** @return array<int, string> */
    public static function codes(): array
    {
        return array_keys(self::OPTIONS);
    }

    public static function isValid(?string $code): bool
    {
        return $code !== null && array_key_exists(strtoupper(trim($code)), self::OPTIONS);
    }

    /** Normalise user input to a supported code, falling back to PHP. */
    public static function normalise(?string $code): string
    {
        $code = strtoupper(trim((string) $code));

        return self::isValid($code) ? $code : self::DEFAULT;
    }

    /** Symbol used when we only want to prefix an amount. */
    public static function symbol(?string $code): string
    {
        return match (self::normalise($code)) {
            'PHP' => '₱',
            'USD' => '$',
            'SAR' => 'SAR ',
            'AED' => 'AED ',
            'QAR' => 'QAR ',
            'KWD' => 'KWD ',
            'BHD' => 'BHD ',
            'OMR' => 'OMR ',
            'JOD' => 'JOD ',
            'HKD' => 'HK$',
            'SGD' => 'S$',
            'TWD' => 'NT$',
            'JPY' => '¥',
            'KRW' => '₩',
            'CAD' => 'CA$',
            'ILS' => '₪',
            'EUR' => '€',
            default => '',
        };
    }

    /** "SAR 1,500.00" — code + amount, for reports and profile pages. */
    public static function format(?string $code, float|int|string|null $amount, int $decimals = 2): ?string
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        return self::normalise($code).' '.number_format((float) $amount, $decimals);
    }
}
