<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Card 6b0420a205f32d19776021d4 — drops a stale operator `web` session.
 *
 * AuthController::login() no longer creates a web session for an agency-less
 * super_admin, but sessions created before that fix are still open in some
 * browsers. Such a session is authenticated with no tenant, so every tenant
 * page it opens resolves to `users` with no agency bound and TenantScope
 * throws — the [users] 500 this card exists for.
 *
 * Registered as `auth:web`'s redirect-using callback (bootstrap/app.php), so it
 * runs on EVERY `auth:web` request: before the route middleware as the redirect
 * target, and as a post-handler if the guard somehow authenticated mid-request.
 * Same session key the `web` guard reads, so it sees exactly the session the
 * guard would.
 *
 * It is NOT an escalation and NOT a redirect into god mode: the operator is
 * sent to the god mode login page, signs in there themselves, and the god guard
 * gives them its own disjoint session key. `GodModeGuard` exists precisely so
 * these two sessions cannot satisfy each other; nothing here joins them.
 *
 * The branch requires BOTH agency_id IS NULL and user_type = super_admin, so
 * every agency-bound user — admin, staff, agent — is untouched: the lookup
 * never runs for them.
 */
class EnsureNoOperatorWebSession
{
    public static function redirectTo(Request $request): ?string
    {
        if (self::logoutStaleOperator($request)) {
            return route('godmode.login');
        }

        // null: the framework keeps its own answer (the tenant dashboard for a
        // logged-in user, /login for a guest).
        return null;
    }

    public function handle(Request $request, Closure $next): Response
    {
        if (self::logoutStaleOperator($request)) {
            return redirect()->route('godmode.login');
        }

        return $next($request);
    }

    /** True when a session was dropped and the caller should redirect. */
    private static function logoutStaleOperator(Request $request): bool
    {
        $guard = Auth::guard('web');

        if (! $guard->check()) {
            return false;
        }

        $user = $guard->user();

        if (! $user || $user->agency_id !== null || $user->user_type !== 'super_admin') {
            return false;
        }

        // A rendered page keeps its tenant data.
        $guard->logout();
        $request->session()->forget('tenant_agency_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return true;
    }
}
