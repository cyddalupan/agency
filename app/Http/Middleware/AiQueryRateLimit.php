<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ActivityLog;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AiQueryRateLimit
{
    /**
     * Max queries per minute per agency.
     */
    private const PER_MINUTE_LIMIT = 30;

    /**
     * Max queries per day per agency.
     */
    private const DAILY_LIMIT = 100;

    /**
     * Handle an incoming request — enforce both per-minute and per-agency daily rate limits.
     */
    public function handle(Request $request, Closure $next): JsonResponse
    {
        $user = $request->user();

        // (1) Per-minute rate limit (cache-based)
        $minuteKey = sprintf('ai_rate:minute:%d', $user->agency_id);
        $minuteHits = (int) Cache::get($minuteKey, 0);

        if ($minuteHits >= self::PER_MINUTE_LIMIT) {
            return response()->json([
                'error'      => 'Too many requests. Please try again in a minute.',
                'retry_after' => 60,
            ], 429);
        }

        // Increment per-minute counter with 1-minute TTL
        Cache::put($minuteKey, $minuteHits + 1, now()->addMinute());

        // (2) Per-agency daily rate limit (DB-based via ActivityLog)
        $dailyCount = ActivityLog::where('agency_id', $user->agency_id)
            ->where('action', 'ai_query')
            ->whereDate('created_at', today())
            ->count();

        if ($dailyCount >= self::DAILY_LIMIT) {
            $secondsUntilMidnight = now()->diffInSeconds(now()->endOfDay());

            return response()->json([
                'error'       => 'Daily AI query limit reached (100 queries). Please try again tomorrow.',
                'retry_after' => $secondsUntilMidnight,
            ], 429);
        }

        return $next($request);
    }
}
