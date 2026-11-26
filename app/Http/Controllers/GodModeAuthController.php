<?php

namespace App\Http\Controllers;

use App\Models\AuditEvent;
use App\Models\User;
use App\Services\GodMode\GodReceivableService;
use App\Services\SensitiveActionLogger;
use App\Support\Tenant;
use Illuminate\Auth\SessionGuard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\View\View;

/**
 * Card 6b0341cf42a2071fc8b38ea5 — the god mode door.
 *
 * Deliberately NOT built on the `LoginThrottle` trait: the limits, the keys,
 * the decay and the error handling differ from the agency login in ways the
 * spec pins down (§4.3), and the trait's 60-second `hit()` decay and its
 * `email`-shaped errors are wrong for this form. The trait itself is
 * untouched, so every existing caller behaves exactly as before.
 *
 * Nothing here touches a tenant model before authentication. The one
 * cross-agency read (resolving the operator by username or email) is an
 * explicit, audited `Tenant::bypass()`.
 */
class GodModeAuthController extends Controller
{
    /** Generic message — identical for "no such user", "wrong password" and "not an operator". */
    private const GENERIC_ERROR = 'The provided credentials do not match our records.';

    public const PER_USER_ATTEMPTS = 5;
    public const PER_IP_ATTEMPTS   = 10;
    public const DECAY_SECONDS     = 900;

    /**
     * The `guest:godmode` middleware already sends an authenticated god user
     * to the dashboard; the check below is the same answer if that middleware
     * is ever removed from this route.
     */
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (auth('godmode')->check()) {
            return redirect()->route('godmode.dashboard');
        }

        return view('godmode.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $username = (string) $credentials['username'];
        $userKey  = $this->userRateLimitKey($username);
        $ipKey    = $this->ipRateLimitKey($request->ip());

        // Per-IP first: one office IP burning attempts across usernames must
        // be refused even when each username is still under its own budget.
        if ($this->locked($ipKey, self::PER_IP_ATTEMPTS)) {
            return $this->lockout($request, $ipKey, $username, 'ip');
        }

        if ($this->locked($userKey, self::PER_USER_ATTEMPTS)) {
            return $this->lockout($request, $userKey, $username, 'username');
        }

        // The operator is resolved before any tenant exists — a legitimate,
        // recorded cross-agency read.
        $user = Tenant::bypass(
            fn () => User::withoutGlobalScopes()
                ->where('username', $username)
                ->orWhere('email', $username)
                ->first(),
            'godmode: resolve operator before tenant exists',
            ['attempted_username' => $username]
        );

        // Constant work on the not-an-operator path: verify the password
        // anyway so timing and the response body are the same as a wrong
        // password. A distinct message would confirm the account exists.
        if (! $user || $user->user_type !== 'super_admin' || $user->status !== 'active') {
            Hash::check($credentials['password'], $user?->password ?? self::dummyHash());

            return $this->reject($request, $userKey, $ipKey, $username);
        }

        // Canonical email so the provider matches the users table exactly.
        // $remember is hard-coded false: a god session must never survive the
        // session cookie. `guard('session')` throws if this guard is misregistered.
        //
        // The attempt MUST run inside Tenant::bypass(): the guard resolves the
        // user through the provider's own `users` query, and on the main host
        // nothing binds a tenant, so TenantScope would throw UnboundTenantException
        // (500) before the password was ever checked. This is the same shape as
        // AuthController::login(), which wraps its Auth::attempt() for exactly
        // this reason. An operator has no agency by definition, so there is no
        // tenant to bind — the bypass IS the correct context, and it is audited.
        //
        // The guard is resolved on its own line, before the bypass call rather
        // than inline in the same expression, so the guarantee above does not
        // depend on argument evaluation order.
        $guard = Auth::guard('godmode');

        $ok = $guard instanceof SessionGuard && Tenant::bypass(
            fn () => $guard->attempt(
                ['email' => $user->email, 'password' => $credentials['password']],
                false
            ),
            'godmode: authenticate an agency-less operator on the control plane',
            ['attempted_username' => $username]
        );

        if (! $ok) {
            Hash::check($credentials['password'], $user->password);

            return $this->reject($request, $userKey, $ipKey, $username);
        }

        // Success clears ONLY the per-username key. Clearing the IP key would
        // let one valid account launder an attacker's budget on that IP.
        RateLimiter::clear($userKey);

        $request->session()->regenerate();
        $request->session()->put('godmode.started_at', now());
        $request->session()->put('godmode.last_activity_at', now());

        $this->logActivity('godmode.login', $user, 'God mode login succeeded.');
        $this->writeAudit('login', $user);

        return redirect()->route('godmode.dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = $request->user('godmode');

        if ($user) {
            $this->logActivity('godmode.logout', $user, 'God mode logout.');
            $this->writeAudit('logout', $user);
        }

        Auth::guard('godmode')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('godmode.login');
    }

    /**
     * Card 6b0342ac4cf7cfdc3e26d6a9 §3.4 — the dashboard is no longer a
     * standalone placeholder page. It reads the SAME `GodReceivableService`
     * methods as `/godmode/payments`, so a number on the index and a number on
     * the ledger are one computation, not two that agree today.
     */
    public function index(Request $request, GodReceivableService $ledger): View
    {
        // The village is the console. One building per agency, coloured by how
        // it is paying; every other God Mode screen hangs off a modal on it.
        // The numbers still come from `GodReceivableService`, so the HUD and
        // the payments ledger remain one computation.
        $village = $ledger->village();

        return view('godmode.dashboard', [
            'user' => $request->user('godmode'),
            'village' => $village,
            'villageSummary' => $ledger->villageSummary($village),
        ] + $ledger->dashboard());
    }

    // ── rate limiting ────────────────────────────────────────────────

    public static function userRateLimitKey(string $username): string
    {
        return 'godmode:user:'.mb_strtolower(trim($username));
    }

    public static function ipRateLimitKey(?string $ip): string
    {
        return 'godmode:ip:'.($ip ?: 'unknown');
    }

    private function locked(string $key, int $max): bool
    {
        return RateLimiter::tooManyAttempts($key, $max);
    }

    /**
     * Record a failure on both budgets, then answer generically.
     */
    private function reject(Request $request, string $userKey, string $ipKey, string $username): RedirectResponse
    {
        RateLimiter::hit($userKey, self::DECAY_SECONDS);
        RateLimiter::hit($ipKey, self::DECAY_SECONDS);

        SensitiveActionLogger::failedLogin($username);

        return back()->withErrors(['username' => self::GENERIC_ERROR])->onlyInput('username');
    }

    /**
     * A lockout is not a failed password: it is a distinct security event and
     * gets its own activity_logs row. Seeded once per lockout window (a
     * `godmode:lockout` key lives exactly as long as the decay), so retries
     * inside the window do not write a row per request.
     */
    private function lockout(Request $request, string $key, string $username, string $scope): RedirectResponse
    {
        $seconds = RateLimiter::availableIn($key);

        // RateLimiter has no `has()`: tooManyAttempts($key, 0) answers "has
        // any hits", which is the question here.
        $flagKey = 'godmode:lockout-reported:'.$key;

        if (! RateLimiter::tooManyAttempts($flagKey, 0)) {
            RateLimiter::hit($flagKey, max($seconds, 1));

            SensitiveActionLogger::log(
                'godmode.lockout',
                description: 'God mode lockout ('.$scope.') for "'.$username.'".',
                metadata: ['attempted_username' => $username, 'scope' => $scope],
            );
        }

        return back()->withErrors([
            'username' => 'Too many login attempts. Please try again in '.$seconds.' seconds.',
        ])->onlyInput('username');
    }

    // ── audit ────────────────────────────────────────────────────────

    /**
     * `activity_logs` via the existing logger. agency_id stays null for a god
     * account (it has no agency), which is exactly the control-plane row the
     * Sensitive Actions screen shows to an agency-less actor.
     */
    private function logActivity(string $action, User $user, string $description): void
    {
        SensitiveActionLogger::log($action, subject: $user, description: $description, userId: $user->id);
    }

    /**
     * `audit_events` row for the god session. `agency_id` is nullable after
     * migration 2026_11_22_000001 — a control-plane row belongs to no agency
     * and is deliberately NOT invented one.
     */
    private function writeAudit(string $action, User $user): void
    {
        $request = request();

        AuditEvent::withoutGlobalScopes()->create([
            'agency_id'     => null,
            'actor_user_id' => $user->id,
            'action'        => $action,
            'subject_type'  => User::class,
            'subject_id'    => $user->id,
            'subject_label' => (string) ($user->username ?: $user->email),
            'before'        => null,
            'after'         => [
                'channel'  => 'godmode',
                'username' => $user->username,
            ],
            'ip_address'    => $request?->ip(),
            'user_agent'    => $request?->userAgent() ? mb_substr((string) $request->userAgent(), 0, 512) : null,
        ]);
    }

    /**
     * A real bcrypt hash (of a random string) built by the hasher itself, so
     * the unknown-user path runs one genuine Hash::check() at the configured
     * cost instead of returning instantly. Matching the configured rounds is
     * the point: a hard-coded hash with different rounds is a timing tell.
     */
    private static function dummyHash(): string
    {
        static $hash = null;

        return $hash ??= Hash::make(\Illuminate\Support\Str::random(32));
    }
}
