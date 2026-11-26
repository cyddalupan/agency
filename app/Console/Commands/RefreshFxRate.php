<?php

namespace App\Console\Commands;

use App\Services\CurrencyConverter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Daily USD->PHP rate refresh (card 6b05c0bc, Option A, Bitoy 2026-11-25).
 *
 * This is the ONLY thing in the application that talks to the rate feed. It
 * runs once a day, writes the number into the cache that CurrencyConverter
 * reads, and logs the change. Rendering never fetches: a peso figure in a
 * ledger must be reproducible, and a third-party API deciding the rate at
 * render time is not.
 *
 * The old CurrencyConverter fetched inline and fell back to a 1.0 rate on
 * failure, which silently rendered $470 as P470. This command does the
 * fetching instead, and it refuses to write a nonsense rate.
 */
class RefreshFxRate extends Command
{
    protected $signature = 'fx:refresh-rate {--dry-run : Fetch and report without writing}';

    protected $description = 'Refresh the USD->PHP rate used by reports (once a day)';

    private const FEED = 'https://open.er-api.com/v6/latest/USD';

    public function handle(CurrencyConverter $converter): int
    {
        $old = Cache::has(CurrencyConverter::CACHE_KEY)
            ? (float) Cache::get(CurrencyConverter::CACHE_KEY)
            : null;

        try {
            $response = Http::timeout(10)->get(self::FEED);
        } catch (\Throwable $e) {
            // Leave the existing rate alone. A stale-but-real rate is strictly
            // better than a wrong one, and better than no rate at all.
            $this->error('FX refresh failed: feed unreachable ('.$e->getMessage().')');
            logger()->error('fx:refresh-rate feed unreachable; keeping previous rate', [
                'previous' => $old,
                'error' => $e->getMessage(),
            ]);

            return Command::FAILURE;
        }

        $data = $response->json();
        $new = (float) ($data['rates']['PHP'] ?? 0);

        if (($data['result'] ?? '') !== 'success' || $new <= CurrencyConverter::MIN_RATE) {
            $this->error('FX refresh failed: feed returned no usable PHP rate');
            logger()->error('fx:refresh-rate returned no usable rate; keeping previous rate', [
                'previous' => $old,
                'result' => $data['result'] ?? null,
                'php' => $data['rates']['PHP'] ?? null,
            ]);

            return Command::FAILURE;
        }

        if ($old !== null && abs($old - $new) < 0.000001) {
            $this->info(sprintf('USD->PHP unchanged at %.6F', $new));
            logger()->info('fx:refresh-rate unchanged', ['rate' => $new]);

            return Command::SUCCESS;
        }

        $this->info(sprintf(
            'USD->PHP %s -> %.6F',
            $old === null ? '(none cached)' : sprintf('%.6F', $old),
            $new
        ));

        if ($this->option('dry-run')) {
            $this->warn('dry-run: not written');
            return Command::SUCCESS;
        }

        // No TTL: this is a stored setting refreshed daily, not a cache entry
        // that should vanish. If the command stops running, the last known
        // real rate keeps being used rather than the app inventing one.
        Cache::forever(CurrencyConverter::CACHE_KEY, $new);

        logger()->info('fx:refresh-rate updated', ['old' => $old, 'new' => $new]);

        return Command::SUCCESS;
    }
}
