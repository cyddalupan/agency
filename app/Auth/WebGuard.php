<?php

namespace App\Auth;

use App\Models\Scopes\TenantScope;
use App\Models\User;
use Illuminate\Auth\SessionGuard;

/**
 * Card 6b0420a205f32d19776021d4 — the `web` guard itself refuses an
 * agency-less user, from a session id or a remember-me recaller.
 *
 * The first attempt at this card put the operator check in a GLOBAL middleware
 * (EnsureNoOperatorWebSession). That middleware ran before StartSession, so
 * `Auth::guard('web')->check()` was false on every request that carried only a
 * remember-me recaller cookie: no session had been read yet. The middleware
 * passed through, StartSession restored the session in the `web` group,
 * SetLocale called Auth::user(), and `SessionGuard::userFromRecaller()` did a
 * plain `users` query — TenantScope, no agency, UnboundTenantException 500 on
 * table [users]. The middleware could never see it, because it ran before the
 * guard had a session to resolve from.
 *
 * The clean seam is therefore the guard, at the ONE place `web` reads a user:
 * `retrieveById()` / `retrieveByToken()` are the only two `users` queries a
 * SessionGuard makes. Both are routed through `canBeWebUser()`, so:
 *
 *   - Session restore and remember-me recall both go through it (same path).
 *   - No tenant is bound yet at that point in the pipeline — before
 *     `Authenticate` runs — so the lookup opts out of the scope explicitly,
 *     exactly like `GodModeGuard::user()` does for the god guard. This is the
 *     operator's own row, not a cross-agency read, so no audit row is written
 *     per request (the login-path bypasses still are).
 *   - An agency-less super_admin is refused here: the guard reports "no user",
 *     so `Auth::user()` is null, SetLocale takes the guest branch, and nothing
 *     downstream ever queries a tenant model with an unbound operator.
 *
 * Agency-bound users are untouched: `canBeWebUser()` returns true for anything
 * with an agency_id, and the scope is still applied normally for every other
 * query in the request.
 *
 * NOT a `godmode` grant: this refuses the session, it never promotes one. See
 * GodModeGuard for why the two session keys stay disjoint.
 */
class WebGuard extends SessionGuard
{
    /**
     * The stock `web` key, spelled out.
     *
     * `SessionGuard::getName()` is `'login_'.$name.'_'.sha1(static::class)`.
     * Before this subclass existed that sha1 was of `SessionGuard::class`
     * (login_web_59ba36ad...); inheriting it here would produce a DIFFERENT
     * key (sha1 of WebGuard), silently logging out every agency-bound user on
     * deploy and ignoring every existing session. Pinned to the historical
     * value so the guard's identity does not change.
     */
    public function getName()
    {
        return 'login_web_'.self::STOCK_SESSION_HASH;
    }

    private const STOCK_SESSION_HASH = '59ba36addc2b2f9401580f014c7f58ea4e30989d';

    /**
     * An authenticated `web` user must belong to a tenant. `super_admin` with no
     * agency is the control-plane operator: it is served by the `godmode` guard
     * and must never hold a `web` session. An agency-LESS non-operator is an
     * orphaned account, not a legitimately tenant-free one, so it is refused
     * too — the scope would throw on its first tenant query anyway.
     */
    protected function canBeWebUser(?User $user): ?User
    {
        if ($user === null) {
            return null;
        }

        return $user->agency_id === null ? null : $user;
    }

    /**
     * Session restore (`login_web_<sha1>` id) — the parent makes the same
     * single provider query, which TenantScope would filter (or throw on).
     */
    public function retrieveById($identifier)
    {
        return $this->canBeWebUser(
            User::withoutGlobalScope(TenantScope::class)->find($identifier)
        );
    }

    /**
     * Remember-me recall — the parent calls `retrieveByToken()` here, which is a
     * `users` query by id + remember_token. This is the path that 500'd in
     * production: `sessions` was empty and only the recaller cookie existed.
     */
    public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
    {
        return $this->canBeWebUser(
            User::withoutGlobalScope(TenantScope::class)
                ->where('id', $identifier)
                ->where('remember_token', $token)
                ->first()
        );
    }

    /**
     * The recaller cookie NAME, pinned to the historical value.
     *
     * The name is derived from sha1(static::class) exactly like the session
     * key, so inheriting it would rename the cookie and orphan every browser
     * that is currently holding one. Pinned to the PRE-fix value
     * (sha1('Illuminate\\Auth\\SessionGuard')) on purpose: the cookie an
     * already-affected operator is carrying today must still be seen, or the
     * fix cannot retire it.
     */
    public function getRecallerName()
    {
        return 'remember_'.$this->name.'_'.self::STOCK_SESSION_HASH;
    }

    /**
     * The cookie side of the same decision.
     *
     * `SessionGuard::logout()` only drops the recaller cookie when it can read
     * one; for a refused operator the point is to drop it even though the guard
     * refuses to give them a user, so the browser stops presenting a token that
     * can never be honoured. `clearUserDataFromStorage()` is exactly the parent's
     * "forget the cookie and unqueue any queued one" step.
     */
    public function logout()
    {
        $this->clearUserDataFromStorage();

        $this->user = null;
        $this->loggedOut = true;

        return $this;
    }
}
