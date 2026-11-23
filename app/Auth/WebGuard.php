<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Auth\EloquentUserProvider;
use Illuminate\Auth\SessionGuard;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Timebox;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Card 6b0420a205f32d19776021d4 — the `web` guard refuses an agency-less
 * operator, from a session id AND from a remember-me recaller.
 *
 * be7a8ffb only stopped a `web` SESSION from 500ing. Production kept failing
 * because the operator's browser held a remember-me recaller cookie and an
 * empty `sessions` table:
 *
 *   SessionGuard::userFromRecaller() -> users query -> TenantScope -> throw
 *
 * and that read happens inside SetLocale, after the old global middleware had
 * already passed the request through.
 *
 * The seam is the guard's provider, because that is the ONE place a web session
 * is turned into a user:
 *
 *   - `userFromRecaller()` is a closure over the provider captured in the
 *     parent constructor; it calls `retrieveByToken()` on the provider itself,
 *     so an override of the guard method is not on that path.
 *   - `user()` -> provider `retrieveById()` for the session id, same story.
 *
 * Both lookups are answered by a provider wrapper that refuses a user with no
 * `agency_id` who is `super_admin`: the guard reports "no user", `Auth::user()`
 * is null, SetLocale takes the guest branch, and no tenant model is ever read
 * on an unbound operator's behalf. The wrapper never widens what the guard
 * accepts — every other row passes through unchanged.
 *
 * No audit row per request: this is the account's own row being declined, not a
 * cross-agency read (the login-path bypasses still log theirs).
 *
 * NOT a `godmode` grant: it refuses a session, it never promotes one.
 * GodModeGuard keeps its own session key so the two can never satisfy each
 * other, and that is deliberately untouched.
 */
final class WebGuard extends SessionGuard
{
    /**
     * The historical `web` session key.
     *
     * `SessionGuard::getName()` is `'login_'.$name.'_'.sha1(static::class)`.
     * Before this subclass existed that sha1 was of `SessionGuard::class`;
     * inheriting it here would yield a different key and silently log out every
     * agency-bound user on deploy. Pinned so the guard's identity does not
     * change.
     */
    private const STOCK_HASH = '59ba36addc2b2f9401580f014c7f58ea4e30989d';

    public function __construct(
        $name,
        UserProvider $provider,
        Session $session,
        ?SymfonyRequest $request = null,
        ?Timebox $timebox = null,
        bool $rehashOnLogin = true,
        int $timeboxDuration = 200000,
        ?string $hashKey = null,
    ) {
        parent::__construct(
            $name,
            self::refusingOperators($provider),
            $session,
            $request,
            $timebox,
            $rehashOnLogin,
            $timeboxDuration,
            $hashKey
        );
    }

    /**
     * The provider wrapper. Every method but the two lookups forwards to the
     * real provider, so credentials, rehashing and remember-token writes behave
     * exactly as before.
     */
    private static function refusingOperators(UserProvider $inner): UserProvider
    {
        if (! $inner instanceof EloquentUserProvider) {
            return $inner;
        }

        return new class($inner) extends EloquentUserProvider {
            public function __construct(private readonly EloquentUserProvider $inner)
            {
                parent::__construct($inner->getHasher(), $inner->getModel());
            }

            public function retrieveById($identifier)
            {
                return $this->allowed($this->inner->retrieveById($identifier));
            }

            public function retrieveByToken($identifier, #[\SensitiveParameter] $token)
            {
                return $this->allowed($this->inner->retrieveByToken($identifier, $token));
            }

            public function retrieveByCredentials(array $credentials)
            {
                return $this->inner->retrieveByCredentials($credentials);
            }

            public function validateCredentials(Authenticatable $user, array $credentials)
            {
                return $this->inner->validateCredentials($user, $credentials);
            }

            public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false)
            {
                return $this->inner->rehashPasswordIfRequired($user, $credentials, $force);
            }

            public function updateRememberToken(Authenticatable $user, $token)
            {
                return $this->inner->updateRememberToken($user, $token);
            }

            public function getModel()
            {
                return $this->inner->getModel();
            }

            private function allowed($user)
            {
                $isOperator = $user instanceof User
                    && $user->agency_id === null
                    && $user->user_type === 'super_admin';

                return $isOperator ? null : $user;
            }
        };
    }

    public function getName()
    {
        return 'login_web_'.self::STOCK_HASH;
    }

    /**
     * The recaller cookie NAME, pinned for the same reason as the session key:
     * it is derived from `sha1(static::class)`, and every browser currently
     * holding one has the PRE-fix name. Renaming it would orphan the very
     * cookies this fix has to retire.
     */
    public function getRecallerName()
    {
        return 'remember_'.$this->name.'_'.self::STOCK_HASH;
    }

    /**
     * The cookie side of the same decision.
     *
     * `SessionGuard::logout()` only drops the recaller cookie when it can read
     * one; for a refused operator the point is to drop it even though the guard
     * refuses to give them a user, so the browser stops presenting a token that
     * can never be honoured.
     */
    public function logout()
    {
        $this->clearUserDataFromStorage();

        $this->user = null;
        $this->loggedOut = true;

        return $this;
    }
}
