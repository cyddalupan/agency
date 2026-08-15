<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use App\Services\SensitiveActionLogger;
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

        // Check user status before attempting login (email or username)
        $user = User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first();
        if ($response = $this->checkUserActiveStatus($user)) {
            return $response;
        }

        $remember = $request->boolean('remember');

        // Resolve to the canonical email so Auth::attempt matches the users table
        $attempt = ['email' => $user?->email ?? $credentials['email'], 'password' => $credentials['password']];

        if (Auth::attempt($attempt, $remember)) {
            $this->clearRateLimit($key);
            $request->session()->regenerate();

            SensitiveActionLogger::login(auth()->user());

            // If this is a super admin (no agency), go to super dashboard
            if (!auth()->user()->agency_id) {
                return redirect()->intended(route('dashboard'));
            }

            // Agency user logged in on main domain - set tenant context
            $this->bindTenantFromUser();

            return redirect()->intended(route('agency.dashboard'));
        }

        $this->hitRateLimit($key);

        // Log failed login attempt
        $userModel = User::where('email', $email)->orWhere('username', $email)->first();
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

        // Check user status before attempting login (email or username)
        $user = User::where('email', $credentials['email'])
            ->orWhere('username', $credentials['email'])
            ->first();
        if ($response = $this->checkUserActiveStatus($user)) {
            return $response;
        }

        $remember = $request->boolean('remember');

        // Resolve to the canonical email so Auth::attempt matches the users table
        $attempt = ['email' => $user?->email ?? $credentials['email'], 'password' => $credentials['password']];

        if (Auth::attempt($attempt, $remember)) {
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

            return redirect()->intended(route('agency.dashboard'));
        }

        $this->hitRateLimit($key);

        $userModel = User::where('email', $email)->orWhere('username', $email)->first();
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

    private function bindTenantFromUser(): void
    {
        $user = auth()->user();
        if ($user->agency_id) {
            $agency = Agency::find($user->agency_id);
            if ($agency) {
                app()->instance('tenant_agency', $agency);
                session(['tenant_agency_id' => $agency->id]);
            }
        }
    }

    /**
     * Track failed login attempts and log suspicious activity if thresholds are exceeded.
     */
    private function trackFailedLoginAttempt(string $email, ?int $agencyId, Request $request): void
    {
        $ip = $request->ip() ?? '127.0.0.1';

        // Find the user to get agency_id if not provided
        if (is_null($agencyId)) {
            $user = User::where('email', $email)->orWhere('username', $email)->first();
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
