<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SensitiveActionLogger;
use App\Support\Tenant;
use App\Traits\LoginThrottle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EmployerAuthController extends Controller
{
    use LoginThrottle;

    public function loginForm()
    {
        return view('employer.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $email = $request->input('email');
        $key = $this->loginRateLimitKey('employer', $email);

        // Rate-limit check
        if ($response = $this->checkLoginRateLimit($key)) {
            return $response;
        }

        // Check user status before attempting login
        // Control-plane lookup before any agency is known: audited bypass.
        $user = Tenant::bypass(
            fn () => User::where('email', $credentials['email'])->first(),
            'employer login: resolve user before tenant is known',
        );
        if ($response = $this->checkUserActiveStatus($user)) {
            return $response;
        }

        $remember = $request->boolean('remember');

        if (Tenant::bypass(
            fn () => Auth::guard('web')->attempt($credentials, $remember),
            'employer login: authenticate before tenant is known',
        )) {
            $this->clearRateLimit($key);
            $user = Auth::guard('web')->user();

            if ($user->user_type !== 'employer') {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'These credentials are not registered as an employer.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Bind this employer's agency so the dashboard runs scoped.
            $this->bindTenantForUser($user);

            SensitiveActionLogger::login($user);

            return redirect()->route('employer.dashboard');
        }

        $this->hitRateLimit($key);

        SensitiveActionLogger::failedLogin($email, $user?->agency_id);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Bind the tenant for a known user. `Agency::find()` reads the
     * control-plane agencies table, so it runs inside an audited bypass.
     */
    private function bindTenantForUser(?User $user): void
    {
        if (! $user || ! $user->agency_id) {
            return;
        }

        $agency = Tenant::bypass(
            fn () => \App\Models\Agency::find($user->agency_id),
            'employer login: bind tenant from user',
        );

        if ($agency) {
            app()->instance('tenant_agency', $agency);
            session(['tenant_agency_id' => $agency->id]);
        }
    }

    public function logout(Request $request)
    {
        if (auth()->user()) {
            SensitiveActionLogger::logout(auth()->user());
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employer.login');
    }
}
