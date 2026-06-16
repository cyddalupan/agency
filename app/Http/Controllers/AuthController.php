<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = Str::lower('login:web:' . $request->input('email'));

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            // If this is a super admin (no agency), go to super dashboard
            if (!auth()->user()->agency_id) {
                return redirect()->intended(route('dashboard'));
            }

            // Agency user logged in on main domain — set tenant context
            $this->bindTenantFromUser();

            return redirect()->intended(route('agency.dashboard'));
        }

        RateLimiter::hit($key);

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
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = Str::lower('login:agency:' . $request->input('email'));

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => 'Too many login attempts. Please try again in ' . $seconds . ' seconds.',
            ])->onlyInput('email');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

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

        RateLimiter::hit($key);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        // Determine where to redirect before clearing session
        $isAgencyUser = auth()->user()?->agency_id || session()->has('tenant_agency_id');

        $request->session()->forget('tenant_agency_id');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($isAgencyUser ? 'agency.login' : 'login');
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
}
