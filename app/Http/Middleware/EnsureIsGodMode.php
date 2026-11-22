<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Card 6b0341cf42a2071fc8b38ea5 — the one gate behind `auth:godmode`.
 *
 * `auth:godmode` proves a session exists and names a user. It does NOT prove
 * the account may use god mode: the session outlives a role change, a status
 * change and an idle period. This middleware is the backstop for all three.
 *
 * Order matters:
 *   1. 30-minute idle cap and 8-hour absolute cap. Checked before anything
 *      else so an expired session never gets as far as a role test.
 *   2. `user_type === 'super_admin'` — god mode is NOT `CheckRole`: that
 *      admits `admin` too, and an agency admin must never reach this.
 *   3. `status === 'active'` — suspending an operator has to take effect on
 *      sessions already open on other devices.
 *   4. `Cache-Control: no-store` on every authenticated god response, so Back
 *      after logout cannot redisplay a cross-tenant screen and no shared cache
 *      keeps one.
 *
 * Anything else is 403, not a redirect: a probe reading the status code sees
 * the refusal.
 */
class EnsureIsGodMode
{
    /** Idle minutes before a god session is dropped. */
    public const IDLE_MINUTES = 30;

    /** Absolute hours before a fresh login is forced (one shift). */
    public const ABSOLUTE_HOURS = 8;

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user('godmode');

        if (! $user) {
            // `auth:godmode` should have caught this; answer honestly if not.
            abort(403);
        }

        if ($this->expired($request)) {
            $this->forgetSession($request);

            return redirect()->route('godmode.login')->withErrors([
                'username' => 'Your god mode session expired. Sign in again.',
            ]);
        }

        if ($user->user_type !== 'super_admin' || $user->status !== 'active') {
            $this->forgetSession($request);

            abort(403, 'This account may not use god mode.');
        }

        $request->session()->put('godmode.last_activity_at', now());

        $response = $next($request);

        // The god session must not be cached anywhere: neither the browser's
        // Back button nor a proxy may replay a cross-tenant page after logout.
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, private');
        $response->headers->set('Pragma', 'no-cache');

        return $response;
    }

    /**
     * Idle > 30 min or started > 8 h ago. The two caps are independent: the
     * idle one limits a stolen laptop, the absolute one forces a fresh login
     * at least once per shift however busy the operator is.
     */
    private function expired(Request $request): bool
    {
        $session = $request->session();

        $startedAt = $session->get('godmode.started_at');

        if (! $startedAt || now()->diffInMinutes($startedAt, true) >= self::ABSOLUTE_HOURS * 60) {
            // A session opened before this card shipped has no started_at;
            // treat it as expired rather than granting an unbounded one.
            return true;
        }

        $last = $session->get('godmode.last_activity_at');

        if (! $last) {
            return true;
        }

        return now()->diffInMinutes($last, true) >= self::IDLE_MINUTES;
    }

    /**
     * Drop the god login and the session data, keeping the session itself
     * alive only long enough to render the login page with an error.
     */
    private function forgetSession(Request $request): void
    {
        Auth::guard('godmode')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
