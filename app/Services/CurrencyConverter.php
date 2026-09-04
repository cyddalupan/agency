<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Currency conversion for reports (Toybits 2026-08-16).
 *
 * Uses the free open.er-api.com endpoint (no API key, daily updates).
 * Rates are cached for 6 hours. If the API is unreachable, falls back to a
 * 1.0 rate (i.e. amounts are shown unconverted) so reports never break.
 */
class CurrencyConverter
{
    private const CACHE_KEY = 'fx_usd_to_php';

    private const CACHE_TTL = 21600; // 6 hours

    public function toPhp(float $amount, string $currency): float
    {
        if (strtoupper($currency) === 'PHP') {
            return $amount;
        }

        return $amount * $this->usdToPhpRate();
    }

    public function usdToPhpRate(): float
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            try {
                $response = Http::timeout(3)->get('https://open.er-api.com/v6/latest/USD');
                $data = $response->json();

                if (($data['result'] ?? '') === 'success' && isset($data['rates']['PHP'])) {
                    return (float) $data['rates']['PHP'];
                }
            } catch (\Throwable $e) {
                // API unreachable — fall through to the 1.0 fallback.
            }

            return 1.0;
        });
    }
}
