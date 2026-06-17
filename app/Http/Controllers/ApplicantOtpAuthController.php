<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Applicant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ApplicantOtpAuthController extends Controller
{
    public function showOtpLoginForm(): View
    {
        return view('portal.otp-login');
    }

    public function sendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'exists:applicants,email'],
        ]);

        $email = $request->input('email');
        $otp = Str::padLeft((string) random_int(0, 999999), 6, '0');

        Cache::put("otp:applicant:{$email}", $otp, now()->addMinutes(10));

        logger()->info("OTP sent to {$email}: {$otp}");

        return view('portal.otp-login')->with('success', 'OTP sent');
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp'   => ['required', 'string', 'size:6'],
        ]);

        $email = $request->input('email');
        $otp = $request->input('otp');
        $cacheKey = "otp:applicant:{$email}";

        $storedOtp = Cache::get($cacheKey);

        if (! $storedOtp || $storedOtp !== $otp) {
            return back()->withErrors(['otp' => 'Invalid or expired OTP.']);
        }

        // Invalidate OTP after successful use
        Cache::forget($cacheKey);

        $applicant = Applicant::where('email', $email)->firstOrFail();

        Auth::guard('applicant')->login($applicant);

        $request->session()->regenerate();

        return redirect()->intended(route('portal.dashboard'));
    }
}
