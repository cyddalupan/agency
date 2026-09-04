<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Applicant;
use App\Services\SensitiveActionLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class ApplicantAuthController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::guard('applicant')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:applicants'],
            'password'   => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $applicant = Applicant::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'agency_id'  => resolve_agency_id() ?? Agency::first()?->id,
            'status'     => 'pending',
        ]);

        Auth::guard('applicant')->login($applicant);

        return redirect()->route('portal.dashboard');
    }

    public function loginForm()
    {
        if (Auth::guard('applicant')->check()) {
            return redirect()->route('portal.dashboard');
        }

        return view('portal.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        $key = 'applicant-login-' . Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        if (Auth::guard('applicant')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            SensitiveActionLogger::login(auth()->guard('applicant')->user());

            return redirect()->intended(route('portal.dashboard'));
        }

        RateLimiter::hit($key, 60);

        SensitiveActionLogger::failedLogin($credentials['email']);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        if (auth()->guard('applicant')->user()) {
            SensitiveActionLogger::logout(auth()->guard('applicant')->user());
        }

        Auth::guard('applicant')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
