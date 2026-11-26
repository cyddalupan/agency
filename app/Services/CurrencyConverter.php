<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Currency conversion for reports.
 *
 * Card 6b05c0bc (Bitoy decision 2026-11-25, "Option A"): there is exactly
 * ONE USD->PHP rate, config('expense.usd_to_php'), admin-editable, default
 * 63.769743. Every screen reads it. NO code path fetches a rate from the
 * internet while rendering: a number in a ledger has to be reproducible, and
 * a third-party API deciding the rate at render time is not.
 *
 * The free open.er-api.com feed still has a job, but not at render time:
 * `php artisan fx:refresh-rate` (routes/console.php, daily) fetches it, writes
 * the number into this config's storage and logs old -> new.
 *
 * Rate resolution order, and why:
 *   1. Cache `fx_usd_to_php`  — the daily refresh writes here, and the test
 *      suite seeds it (`Cache::put('fx_usd_to_php', 56.0)`). Cache-first keeps
 *      those fixtures deterministic.
 *   2. config('expense.usd_to_php') — the live, on-screen rate.
 * Never 1.0: a rate <= 1.0 is rejected as an error, not used as a value. The
 * old code fell back to 1.0, which rendered $470 as P470 with no warning.
 */
class CurrencyConverter
{
    public const CACHE_KEY = 'fx_usd_to_php';

    /** Below this, a "rate" is a bug, not a conversion factor. */
    public const MIN_RATE = 1.0;

    public function toPhp(float $amount, string $currency, ?float $rate = null): float
    {
        if (strtoupper($currency) === 'PHP') {
            return $amount;
        }

        return $amount * ($rate ?? $this->usdToPhpRate());
    }

    public function usdToPhpRate(): float
    {
        $cached = Cache::get(self::CACHE_KEY);
        if ($cached !== null && (float) $cached > self::MIN_RATE) {
            return (float) $cached;
        }

        return $this->configuredRate();
    }

    /**
     * The configured rate, guarded. Used by the daily refresh to read the
     * current value before overwriting it, and as the final fallback.
     */
    public function configuredRate(): float
    {
        $rate = (float) config('expense.usd_to_php');

        if ($rate <= self::MIN_RATE) {
            // A misconfigured rate is an error to surface, not a value to use.
            // The config default is valid, so this only trips on a bad override.
            throw new \RuntimeException(
                'config(expense.usd_to_php) resolved to '.$rate
                .'; a USD->PHP rate must be > 1. Fix config/expense.php or EXPENSE_USD_TO_PHP.'
            );
        }

        return $rate;
    }
}
