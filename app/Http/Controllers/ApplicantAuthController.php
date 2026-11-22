<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Applicant;
use App\Services\SensitiveActionLogger;
use App\Support\Tenant;
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

        // A self-registration always belongs to a tenant, so it must arrive on
        // one. `Agency::first()` used to guess — every main-site registrant
        // landed in whichever agency sorted first (csdi) — and there is no
        // honest default: the applicant's agency is who will read their record.
        // Registration is reachable from ANY host (routes/web.php is not host
        // gated, and portal.login is the control-plane redirect target after
        // login/logout, so these pages are served from the control plane too),
        // so "reject without a tenant" is the only correct choice — guessing
        // writes real PII into a stranger's agency.
        $agencyId = resolve_agency_id() ?? tenant_agency()?->id;

        if (! $agencyId) {
            return back()
                ->withInput($request->except('password', 'password_confirmation'))
                ->withErrors([
                    'registration' => 'Registration is only available on your recruitment agency\'s own website. Open your agency\'s portal link and register there, or contact the agency office.',
                ]);
        }

        // (Cyd 2026-09-28 #5) Duplicate guard for the self-service portal too:
        // the staff Add-Applicant form already warns, this closes the portal gap.
        $duplicates = \App\Support\ApplicantDuplicateChecker::find($agencyId, [
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
        ]);
        if ($duplicates->isNotEmpty() && ! $request->boolean('confirm_duplicate')) {
            return back()->withInput($request->except('password', 'password_confirmation'))
                ->withErrors(['duplicate' => 'An applicant with these details already exists. If this is a different person, please contact the agency office.']);
        }

        $applicant = Applicant::create([
            'first_name' => $validated['first_name'],
            'last_name'  => $validated['last_name'],
            'email'      => $validated['email'],
            'password'   => Hash::make($validated['password']),
            'agency_id'  => $agencyId,
            'status'     => 'pending',
        ]);

        Auth::guard('applicant')->login($applicant);

        // Bind the tenant now as well: the Location below is built from the app
        // URL (the control plane), where there is no host agency to resolve.
        // IdentifyAgency re-binds from the applicant guard on that request; this
        // keeps the remainder of THIS request scoped too.
        if ($agency = Agency::find($agencyId)) {
            app()->instance('tenant_agency', $agency);
            session(['tenant_agency_id' => $agency->id]);
        }

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

        if (Tenant::bypass(
            fn () => Auth::guard('applicant')->attempt($credentials, $request->boolean('remember')),
            'applicant login: authenticate before tenant is known',
        )) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            // Bind the applicant's tenant so the portal runs scoped.
            $applicant = Auth::guard('applicant')->user();
            if ($applicant->agency_id && ($agency = Agency::find($applicant->agency_id))) {
                app()->instance('tenant_agency', $agency);
                session(['tenant_agency_id' => $agency->id]);
            }

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
