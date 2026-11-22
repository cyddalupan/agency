<?php

namespace App\Auth;

use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Support\Tenant;
use Illuminate\Auth\SessionGuard;

/**
 * Card 6b0341cf42a2071fc8b38ea5 — the `godmode` session guard.
 *
 * `Illuminate\Auth\SessionGuard` derives its session key from the class name
 * (`login_web_<sha1(class)>`), so two `session` guards on the same provider
 * share ONE session key: a normal agency login on `web` would satisfy
 * `auth:godmode`, and a god login would satisfy `auth:web`. That is the whole
 * attack this class exists to close.
 *
 * Overriding getName() gives the god guard its own session key,
 * `login_godmode_<sha1>`, so a `web` session and a `godmode` session can never
 * be the same session. `Auth::guard('godmode')->loginUsingId()` cannot
 * substitute for this: it would still write the colliding key.
 *
 * Registered through Auth::extend('godmode') in AppServiceProvider::boot(); see
 * config/auth.php `guards.godmode`.
 *
 * It also owns the tenant context for every user lookup, which is the second
 * thing it exists for (card 6b0341cf42a2071fc8b38ea5, QA re-run BUG 3).
 *
 * A god operator has `agency_id NULL`, so on the main host IdentifyAgency
 * binds no tenant. TenantScope fails closed on the web, so ANY `users` read on
 * that request throws UnboundTenantException — including the guard's own
 * `retrieveById()` when `auth:godmode` rehydrates the session on the redirect
 * target. That happens in the framework's `Authenticate` middleware before any
 * route middleware runs, so it cannot be fixed in `EnsureIsGodMode`, and
 * wrapping each call site means the next call site is the next 500.
 *
 * The seam is here: both paths that touch the users table opt out of the
 * scope themselves, so every consumer of this guard — middleware, controller,
 * Blade, a future console check — inherits the fix. An operator is
 * agency-less by definition, so there is no tenant whose data the guard's own
 * lookup could cross.
 *
 * The class is final on purpose: the previous 500 was caused by which
 * implementation of `user()` actually ran, and a subclass could silently
 * reintroduce that.
 */
final class GodModeGuard extends SessionGuard
{
    /**
     * The session key this guard stores its logged-in id under.
     *
     * Spelled out rather than inherited, because inheriting is the trap:
     * `SessionGuard::getName()` is `'login_'.$this->name.'_'.sha1(static::class)`,
     * and for the `godmode` guard that already yields `login_godmode_<sha1>` —
     * identical to what `web` would yield ONLY if `static::class` resolved to
     * the same class, which it does not for the plain `web` guard
     * (`SessionGuard`). The prefix is pinned here so the key is decided in one
     * readable place and cannot drift with the guard's configured name; the
     * real test of the whole mechanism is an actual login on each guard
     * (GodModeLoginTest: a real web login does not satisfy auth:godmode).
     *
     * @return string
     */
    public function getName()
    {
        return 'login_godmode_'.sha1(static::class);
    }

    /**
     * The session's operator, read without the tenant filter.
     *
     * `retrieveById()` is a plain Eloquent `users` query, and `User` carries
     * TenantScope as a GLOBAL scope, so it is filtered whether it is spelled
     * here or in the parent. On the main host a god operator has
     * `agency_id NULL`, nothing can be bound, and the scope fails closed: the
     * query throws UnboundTenantException.
     *
     * This has to bypass at the QUERY, not around a parent call. An earlier
     * version wrapped `parent::user()` in `Tenant::bypass()` and did not work:
     * the trace still landed in `SessionGuard::user()`'s `retrieveById()`, and
     * the live dashboard kept 500ing. The parent's user()/check() dispatch is
     * not something this fix should depend on, so the retrieval is done here:
     * same session key, same provider call, same caching in `$this->user`.
     *
     * Unlike `attempt()`, this does not go through `Tenant::bypass()`: that
     * path always writes an `audit_events` row, and rehydration runs on EVERY
     * god page view — one row per page load would drown the login bypass row
     * it is meant to be read beside. The read is still deliberate and still
     * scope-free, but it is an agency-less operator's own session row, not a
     * cross-agency decision worth auditing per request.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if ($this->user !== null) {
            return $this->user;
        }

        if ($this->loggedOut) {
            return null;
        }

        $id = $this->session->get($this->getName());

        if ($id === null) {
            return null;
        }

        // The one query, with the scope opted out for exactly this call.
        // TenantScope's own escape hatch is `Tenant::isBypassing()`, which this
        // never sets: the counter stays honest, no audit row is written, and no
        // global "bypassing" state survives the call on a path that runs on
        // every god page view. Return type is the same model the provider would
        // have returned — the `godmode` guard and `web` share the `users`
        // provider (config/auth.php).
        return User::withoutGlobalScope(TenantScope::class)->find($id);
    }

    /**
     * Credential check, read without the tenant filter.
     *
     * Same reason as user(): `retrieveByCredentials()` is a `users` query. The
     * controller keeps its own explicit bypass around this call as the
     * login-path audit row; the two nest harmlessly and each writes one row.
     *
     * @return bool
     */
    public function attempt(array $credentials = [], $remember = false)
    {
        return Tenant::bypass(
            fn () => parent::attempt($credentials, $remember),
            'godmode: authenticate operator before tenant exists',
            ['attempted_identifier' => $credentials['email'] ?? null]
        );
    }
}
