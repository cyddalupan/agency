<?php

namespace App\Http\Controllers;

use App\Models\User;
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

        $key = $this->loginRateLimitKey('employer', $request->input('email'));

        // Rate-limit check
        if ($response = $this->checkLoginRateLimit($key)) {
            return $response;
        }

        // Check user status before attempting login
        $user = User::where('email', $credentials['email'])->first();
        if ($response = $this->checkUserActiveStatus($user)) {
            return $response;
        }

        $remember = $request->boolean('remember');

        if (Auth::guard('web')->attempt($credentials, $remember)) {
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

            return redirect()->intended(route('employer.dashboard'));
        }

        $this->hitRateLimit($key);

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('employer.login');
    }
}
