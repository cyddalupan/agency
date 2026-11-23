<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Auth\Recaller;
use Illuminate\Auth\SessionGuard;
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
 * Registered at the END of the `web` group (bootstrap/app.php) and pinned by
 * $middleware->priority() to run right after StartSession, BEFORE SetLocale
 * and SubstituteBindings. That position is the fix. The first version was a
 * GLOBAL append, so it ran before StartSession — with the sessions table empty
 * and only a remember-me cookie in the browser, `$guard->check()` was false,
 * the middleware passed through, and the recaller was resolved later inside
 * SetLocale's Auth::user(), throwing while reading [users]. A check that runs
 * before the guard has a session cannot see an authentication.
 *
 * App\Auth\WebGuard is the earlier gate: it refuses to resolve an agency-less
 * user from a session id or a recaller at all. This middleware is what turns
 * that refusal into the honest answer (logout, drop the cookie, clear the one
 * stored token, redirect to the god mode door), and it also catches the case
 * where the guard has the session id of an agency-less account.
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
        /** @var SessionGuard $guard */
        $guard = Auth::guard('web');

        // Ask the guard first, exactly as the framework would. App\Auth\WebGuard
        // refuses to hand back an agency-less user, so this is false for both a
        // session id and a recaller — the point is that asking does NOT throw.
        // The rows behind it are inspected below to decide the response.
        if ($guard->check()) {
            return false;
        }

        $carried = self::operatorCarryingAuth($request, $guard);

        if ($carried === null) {
            return false;
        }

        // Drop the session id, the CSRF token, the recaller cookie, and the one
        // stored remember token that minted it. Clearing the token here —
        // together with AuthController never issuing one at login — is what
        // keeps it cleared: no web login mints a token for an agency-less
        // account, so there is nothing left to re-mint one from.
        // Session work goes through the guard's store, not `$request->session()`:
        // this also runs from Authenticate's redirect callback, where the
        // request may not have been given a session yet.
        $guard->logout();
        $guard->getSession()->forget('tenant_agency_id');
        $guard->getSession()->invalidate();
        $guard->getSession()->regenerateToken();

        if (! empty($carried->remember_token)) {
            $carried->forceFill(['remember_token' => null])->saveQuietly();
        }

        return true;
    }

    /**
     * The agency-less super_admin this request is authenticated AS — by session
     * id, by remember-me recaller, or not at all. Read without the tenant scope:
     * it is this one account's own row, before any tenant is bound.
     */
    private static function operatorCarryingAuth(Request $request, SessionGuard $guard): ?User
    {
        $id = $guard->getSession()->get($guard->getName());

        // Session ids carry a remember-me prefix when one was issued, so the
        // stored value is not always a bare integer.
        if (is_string($id) && str_contains($id, '|')) {
            $id = substr($id, strrpos($id, '|') + 1);
        }

        $user = $id === null
            ? null
            : User::withoutGlobalScope(TenantScope::class)->find($id);

        if ($user !== null) {
            return self::isOperator($user) ? $user : null;
        }

        // The 11:40:04 shape: empty sessions table, recaller cookie only.
        $value = $request->cookies->get($guard->getRecallerName());

        if (empty($value)) {
            return null;
        }

        $recaller = new Recaller($value);

        if (! $recaller->valid()) {
            return null;
        }

        $user = User::withoutGlobalScope(TenantScope::class)
            ->where('id', $recaller->id())
            ->first();

        return $user !== null && self::isOperator($user) ? $user : null;
    }

    private static function isOperator(User $user): bool
    {
        return $user->agency_id === null && $user->user_type === 'super_admin';
    }
}
