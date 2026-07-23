<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\LoginThrottle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class FraAuthController extends Controller
{
    use LoginThrottle;

    public function loginForm()
    {
        return view('fra.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'login'    => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->input('login');
        $throttleKey = $this->loginRateLimitKey('fra', $login);

        // Check rate limit
        if ($blocked = $this->checkLoginRateLimit($throttleKey)) {
            throw ValidationException::withMessages([
                'login' => [trans('auth.throttle', ['seconds' => 60, 'minutes' => 1])],
            ]);
        }

        // Find user by either email or username
        $user = User::where('username', $login)
            ->orWhere('email', $login)
            ->first();

        // Must exist, be employer type, and be active
        if (! $user || $user->user_type !== 'employer') {
            $this->hitRateLimit($throttleKey);
            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        // Check active status
        if ($blocked = $this->checkUserActiveStatus($user)) {
            $this->hitRateLimit($throttleKey);
            // Return the status error on the 'login' field
            throw ValidationException::withMessages([
                'login' => ['Your account is ' . $user->status . '. Please contact your administrator.'],
            ]);
        }

        // Check password
        if (! Hash::check($request->password, $user->password)) {
            $this->hitRateLimit($throttleKey);
            throw ValidationException::withMessages([
                'login' => [trans('auth.failed')],
            ]);
        }

        // Clear rate limit on success
        $this->clearRateLimit($throttleKey);

        Auth::login($user, $request->boolean('remember'));

        $request->session()->regenerate();

        return redirect()->intended(route('fra.dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('fra.login');
    }
}
