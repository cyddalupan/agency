<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

/**
 * Encapsulates rate-limiting and user-status-check logic shared
 * across the admin, agency, and employer authentication controllers.
 *
 * Using a trait keeps the behaviour testable and consistent without
 * forcing an inheritance change or duplicating logic in three places.
 */
trait LoginThrottle
{
    /**
     * Build a consistent rate-limiter key for the given email and prefix.
     */
    protected function loginRateLimitKey(string $prefix, string $email): string
    {
        return Str::lower("login:{$prefix}:{$email}");
    }

    /**
     * Check whether the given key has exceeded the maximum allowed attempts.
     * Returns a redirect response (with the throttle error) if blocked, or null.
     */
    protected function checkLoginRateLimit(string $key, int $maxAttempts = 5): ?RedirectResponse
    {
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in '.$seconds.' seconds.',
            ])->onlyInput('email');
        }

        return null;
    }

    /**
     * If the user exists and is not active, return a redirect response
     * with an appropriate error message. Returns null if the user is
     * active (or does not exist).
     */
    protected function checkUserActiveStatus(?User $user): ?RedirectResponse
    {
        if ($user && $user->status !== 'active') {
            $status = $user->status;

            return back()->withErrors([
                'email' => "Your account is {$status}. Please contact your administrator.",
            ])->onlyInput('email');
        }

        return null;
    }

    /**
     * Record a failed login attempt for the given key.
     */
    protected function hitRateLimit(string $key): void
    {
        RateLimiter::hit($key);
    }

    /**
     * Clear all rate-limit attempts for the given key.
     */
    protected function clearRateLimit(string $key): void
    {
        RateLimiter::clear($key);
    }
}
