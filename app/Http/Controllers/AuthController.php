<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Services\SensitiveActionLogger;
use App\Support\Tenant;
use App\Traits\LoginThrottle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    use LoginThrottle;

    public function loginForm()
    {
        return view('auth.login');
    }

    /** Agency bound by bindTenantForUser() during this request, if any. */
    private ?int $boundTenantId = null;

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $email = $request->input('email');
        $key = $this->loginRateLimitKey('web', $email);

        // Track all failed attempts (before rate limit check) for suspicious activity detection
        $this->trackFailedLoginAttempt($email, null, $request);

        // Rate-limit check
        if ($response = $this->checkLoginRateLimit($key)) {
            return $response;
        }

        // Check user status before attempting login (email or username).
        // The lookup happens on the control plane before any agency is known,
        // so it is a legitimate (audited) cross-agency read.
        $user = Tenant::bypass(fn () => User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first(), 'login: resolve user before tenant is known', ['attempted_email' => $credentials['email']]);
        if ($response = $this->checkUserActiveStatus($user)) {
            return $response;
        }

        // Bind the tenant before Auth::attempt so every query that follows
        // (including the session / user reload) is already scoped.
        $this->bindTenantForUser($user);

        $remember = $request->boolean('remember');

        // Resolve to the canonical email so Auth::attempt matches the users table
        $attempt = ['email' => $user?->email ?? $credentials['email'], 'password' => $credentials['password']];

        // The guard looks the user up itself. When a pre-attempt lookup found a
        // tenant, that lookup now scopes the request; a wrong password is then
        // simply "no match". When nothing was found, there is no tenant to bind
        // and the control-plane lookup is a deliberate cross-agency read.
        $authenticated = $this->boundTenantId !== null
            ? Auth::attempt($attempt, $remember)
            : Tenant::bypass(fn () => Auth::attempt($attempt, $remember), 'login: authenticate before tenant is known', ['attempted_email' => $credentials['email']]);

        if ($authenticated) {
            $this->clearRateLimit($key);
            $request->session()->regenerate();

            SensitiveActionLogger::login(auth()->user());

            // If this is a super admin (no agency), go to super dashboard
            if (!auth()->user()->agency_id) {
                return redirect()->intended(route('dashboard'));
            }

            // Already bound above from the resolved user; re-bind from the
            // authenticated user only as a safety net (idempotent).
            $this->bindTenantFromUser();

            // Portal accounts must ignore any stored "intended" URL — a prior
            // visit to an agency page would bounce them straight back into the 403.
            if ($this->isPortalAccount(auth()->user())) {
                return redirect($this->homeFor(auth()->user()));
            }

            return redirect()->intended(route('agency.dashboard'));
        }

        $this->hitRateLimit($key);

        // Log failed login attempt
        $userModel = Tenant::bypass(
            fn () => User::where('email', $email)->orWhere('username', $email)->first(),
            'agencyLogin: failed-login attribution before tenant is known',
            ['attempted_email' => $email]
        );
        SensitiveActionLogger::failedLogin($email, $userModel?->agency_id);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function agencyLoginForm()
    {
        return view('auth.agency-login');
    }

    public function agencyLogin(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'string', 'max:255'],
            'password' => ['required'],
        ]);

        $email = $request->input('email');
        $key = $this->loginRateLimitKey('agency', $email);

        // Rate-limit check
        if ($response = $this->checkLoginRateLimit($key)) {
            return $response;
        }

        // Check user status before attempting login (email or username).
        // Same control-plane rule as login(): the agency is not known yet, so
        // this is a deliberate, audited cross-agency read.
        $user = Tenant::bypass(fn () => User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first(), 'agencyLogin: resolve user before tenant is known', ['attempted_email' => $credentials['email']]);
        if ($response = $this->checkUserActiveStatus($user)) {
            return $response;
        }

        $remember = $request->boolean('remember');

        // Resolve to the canonical email so Auth::attempt matches the users table
        $attempt = ['email' => $user?->email ?? $credentials['email'], 'password' => $credentials['password']];

        // The guard re-queries the user, so it must see the tenant the account
        // belongs to (TenantScope fails closed on web requests).
        $authenticated = Tenant::bypass(
            fn () => Auth::attempt($attempt, $remember),
            'agencyLogin: authenticate before the tenant is bound',
            ['attempted_email' => $credentials['email']]
        );

        if ($authenticated) {
            $this->clearRateLimit($key);
            $request->session()->regenerate();

            SensitiveActionLogger::login(auth()->user());

            if (!auth()->user()->agency_id) {
                Auth::logout();
                $request->session()->invalidate();
                return back()->withErrors([
                    'email' => 'This account is not associated with an agency.',
                ])->onlyInput('email');
            }

            $this->bindTenantFromUser();

            // Portal accounts must ignore any stored "intended" URL — a prior
            // visit to an agency page would bounce them straight back into the 403.
            if ($this->isPortalAccount(auth()->user())) {
                return redirect($this->homeFor(auth()->user()));
            }

            return redirect()->intended(route('agency.dashboard'));
        }

        $this->hitRateLimit($key);

        // Card 6b008b8dc9020955b2cc16e1, defect D: the failed-login bypass
        // already had $email in scope but recorded only a reason, so its
        // tenant_bypass row did not say what was attempted. It was also never
        // reached — the line below re-queried the user directly — so the lost
        // bypass is restored here and its result is what gets attributed.
        $userModel = Tenant::bypass(
            fn () => User::where('email', $email)->orWhere('username', $email)->first(),
            'agencyLogin: failed-login attribution before tenant is known',
            ['attempted_email' => $email]
        );
        SensitiveActionLogger::failedLogin($email, $userModel?->agency_id);
        $this->trackFailedLoginAttempt($email, $userModel?->agency_id, $request);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Log logout before clearing session
        if (auth()->user()) {
            SensitiveActionLogger::logout(auth()->user());
        }

        $request->session()->forget('tenant_agency_id');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Everyone returns to /login — it auto-detects role & tenant:
        // super-admin goes to the main dashboard, agency users to their
        // agency dashboard. On a tenant subdomain this is also the
        // agency-branded login page. `/agency-login` remains only as a
        // restricted non-subdomain fallback for testing.
        return redirect()->route('login');
    }

    /** FRA (employer) and sponsor accounts have their own portal, not the agency panel. */
    private function isPortalAccount(?User $user): bool
    {
        return in_array($user?->user_type, ['employer', 'sponsor'], true);
    }

    /**
     * Landing route for a user who just authenticated on the agency login form.
     *
     * FRA (user_type employer) and sponsor accounts do NOT belong to the agency
     * panel — ModuleAccess denies every agency module, so sending them to
     * /agency/dashboard produced a bare 403 ("Your account does not have access
     * to this module.") after a successful login (Mjolnir card "FINAS: FRA
     * Feature", Josh report 2026-10-22). Send them to their own portal instead.
     */
    private function homeFor(?User $user): string
    {
        return match ($user?->user_type) {
            'employer' => route('fra.dashboard'),
            'sponsor'  => route('sponsor.dashboard'),
            default    => route('agency.dashboard'),
        };
    }

    private function bindTenantFromUser(): void
    {
        $this->bindTenantForUser(auth()->user());
    }

    /**
     * Bind the tenant for a known user (before or after authentication).
     * `Agency::find()` reads the control-plane agencies table, so it is done
     * inside a bypass while no tenant is bound yet.
     */
    private function bindTenantForUser(?User $user): void
    {
        $this->boundTenantId = null;

        if (! $user || ! $user->agency_id) {
            return;
        }

        $agency = Tenant::bypass(
            fn () => Agency::find($user->agency_id),
            'auth: bind tenant from user',
        );

        if ($agency) {
            app()->instance('tenant_agency', $agency);
            session(['tenant_agency_id' => $agency->id]);
            $this->boundTenantId = (int) $agency->id;
        }
    }

    /**
     * Track failed login attempts and log suspicious activity if thresholds are exceeded.
     */
    private function trackFailedLoginAttempt(string $email, ?int $agencyId, Request $request): void
    {
        $ip = $request->ip() ?? '127.0.0.1';

        // Find the user to get agency_id if not provided. Runs before login /
        // before any tenant is bound, so it is an audited cross-agency read.
        if (is_null($agencyId)) {
            $user = Tenant::bypass(
                fn () => User::where('email', $email)->orWhere('username', $email)->first(),
                'login: failed-attempt user lookup',
                ['attempted_email' => $email],
            );
            if ($user) {
                $agencyId = $user->agency_id;
            }
        }

        // 1) Track per-email failed attempts
        $emailKey = 'failed_login_email_' . sha1($email);
        $emailAttempts = (int) Cache::get($emailKey, 0) + 1;
        Cache::put($emailKey, $emailAttempts, now()->addMinutes(30));

        // Alert if 10+ failed attempts for same account
        if ($emailAttempts >= 10) {
            SensitiveActionLogger::suspiciousActivity(
                "Multiple failed login attempts ({$emailAttempts}) for {$email}.",
                [
                    'failed_attempt_count' => $emailAttempts,
                    'target_email'         => $email,
                ],
                agencyId: $agencyId,
            );
            Cache::forget($emailKey);
        }

        // 2) Track per-IP failed login accounts (cross-account rapid attempts)
        $multiAccountKey = 'failed_login_accounts_' . sha1($ip);
        $attemptedAccounts = Cache::get($multiAccountKey, []);
        if (!in_array($email, $attemptedAccounts)) {
            $attemptedAccounts[] = $email;
        }
        Cache::put($multiAccountKey, $attemptedAccounts, now()->addMinutes(15));

        // Alert if 5+ different accounts attempted from same IP
        if (count($attemptedAccounts) >= 5) {
            SensitiveActionLogger::suspiciousActivity(
                "Rapid failed login attempts across " . count($attemptedAccounts) . " different accounts from IP {$ip}.",
                [
                    'failed_attempt_count' => count($attemptedAccounts),
                    'target_ip'            => $ip,
                ],
                agencyId: $agencyId,
            );
            Cache::forget($multiAccountKey);
        }
    }
}
