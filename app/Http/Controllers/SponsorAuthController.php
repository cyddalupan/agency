<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\Sponsor;
use App\Models\User;
use App\Traits\LoginThrottle;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;

class SponsorAuthController extends Controller
{
    use LoginThrottle;

    private function getDefaultAgency(): Agency
    {
        return Agency::where('status', 'active')->first()
            ?? Agency::first()
            ?? throw new \RuntimeException('No agency found. Contact administrator.');
    }

    public function registerForm(): View
    {
        return view('sponsor.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'id_number'    => 'required|string|max:100|unique:sponsors,id_number',
            'company_name' => 'required|string|max:255',
            'email'        => 'required|email|max:255|unique:users,email',
            'contact_no'   => 'nullable|string|max:50',
            'viber'        => 'nullable|string|max:50',
            'address'      => 'nullable|string|max:1000',
            'city'         => 'nullable|string|max:255',
            'password'     => 'required|string|min:8|confirmed',
        ]);

        $defaultAgency = $this->getDefaultAgency();

        // Create the sponsor record
        $sponsor = Sponsor::create([
            'agency_id'    => $defaultAgency->id,
            'id_number'    => $validated['id_number'],
            'company_name' => $validated['company_name'],
            'email'        => $validated['email'],
            'contact_no'   => $validated['contact_no'],
            'viber'        => $validated['viber'],
            'address'      => $validated['address'],
            'city'         => $validated['city'],
            'status'       => 'active',
        ]);

        // Create the user account
        $user = User::create([
            'agency_id' => $sponsor->agency_id,
            'name'      => $validated['company_name'],
            'email'     => $validated['email'],
            'username'  => $validated['id_number'],
            'password'  => Hash::make($validated['password']),
            'user_type' => 'sponsor',
            'status'    => 'active',
        ]);

        // Log the user in
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('sponsor.dashboard'));
    }

    public function loginForm(): View
    {
        return view('sponsor.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $throttleKey = $this->loginRateLimitKey('sponsor', $login);

        if ($blocked = $this->checkLoginRateLimit($throttleKey)) {
            throw ValidationException::withMessages([
                'login' => [trans('auth.throttle', ['seconds' => 60, 'minutes' => 1])],
            ]);
        }

        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->first();

        if (! $user || $user->user_type !== 'sponsor') {
            $this->hitRateLimit($throttleKey);
            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        if ($blocked = $this->checkUserActiveStatus($user)) {
            $this->hitRateLimit($throttleKey);
            throw ValidationException::withMessages([
                'login' => ['Your account is ' . $user->status . '. Please contact your administrator.'],
            ]);
        }

        if (! Hash::check($request->password, $user->password)) {
            $this->hitRateLimit($throttleKey);
            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        $this->clearRateLimit($throttleKey);

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('sponsor.dashboard'));
    }

    public function forgotPasswordForm(): View
    {
        return view('sponsor.forgot-password');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? redirect()->route('sponsor.password.request')->with('status', __($status))
            : redirect()->route('sponsor.password.request')->with('status', __('passwords.sent'));
    }

    public function resetForm(string $token): View
    {
        return view('sponsor.reset-password', ['token' => $token]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('sponsor.login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sponsor.login');
    }
}
