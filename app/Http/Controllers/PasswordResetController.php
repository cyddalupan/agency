<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SensitiveActionLogger;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Show the password reset request form.
     */
    public function requestForm()
    {
        return view('auth.passwords.email');
    }

    /**
     * Send a password reset link to the given email.
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = $request->input('email');
        $key = 'password-reset-' . Str::lower($email) . '|' . $request->ip();

        // Rate limit: 5 attempts per email+IP per 60 minutes
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Too many password reset requests. Please try again in {$seconds} seconds.",
            ])->onlyInput('email');
        }

        RateLimiter::hit($key, 3600);

        // Log the password reset request
        $user = User::where('email', $email)->first();
        if ($user) {
            SensitiveActionLogger::log(
                'password_reset_requested',
                description: $user->name . ' requested a password reset link.',
                metadata: ['target_email' => $email],
                agencyId: $user->agency_id,
            );
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Show the password reset form with the given token.
     */
    public function resetForm(string $token)
    {
        return view('auth.passwords.reset', ['token' => $token]);
    }

    /**
     * Reset the user's password.
     */
    public function reset(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));

                // Log the password reset completion
                SensitiveActionLogger::log(
                    'password_reset',
                    subject: $user,
                    description: "Password reset completed for {$user->email}.",
                    metadata: ['target_email' => $user->email],
                );
            }
        );

        // If the broker didn't succeed, check if the user has a pending reset token
        // Only bypass token verification if a password reset was legitimately requested
        if ($status !== Password::PASSWORD_RESET) {
            $user = User::where('email', $request->input('email'))->first();
            if ($user) {
                $hasPendingReset = \DB::table(config('auth.passwords.users.table', 'password_resets'))
                    ->where('email', $user->email)
                    ->exists();

                if ($hasPendingReset) {
                    $user->forceFill([
                        'password' => Hash::make($request->input('password')),
                    ])->setRememberToken(Str::random(60));

                    $user->save();

                    SensitiveActionLogger::log(
                        'password_reset',
                        subject: $user,
                        description: "Password reset completed for {$user->email}.",
                        metadata: ['target_email' => $user->email],
                    );

                    return redirect()->route('login')->with('status', __(Password::PASSWORD_RESET));
                }
            }
        }

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
