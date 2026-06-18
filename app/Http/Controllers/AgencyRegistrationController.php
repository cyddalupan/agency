<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AgencyRegistrationController extends Controller
{
    /**
     * Show the public agency registration form.
     */
    public function showRegistrationForm(): View|RedirectResponse
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('agencies.public-register');
    }

    /**
     * Handle the public agency registration request.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'agency_name'  => ['required', 'string', 'max:255'],
            'subdomain'    => ['required', 'string', 'max:63', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/i', 'unique:agencies,subdomain'],
            'admin_name'   => ['required', 'string', 'max:255'],
            'admin_email'  => ['required', 'string', 'email', 'max:255'],
            'admin_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'subdomain.regex' => 'The subdomain may only contain letters, numbers, and hyphens.',
            'admin_password.confirmed' => 'The password confirmation does not match.',
        ]);

        $agency = Agency::create([
            'name'      => $validated['agency_name'],
            'subdomain' => strtolower($validated['subdomain']),
            'status'    => 'pending',
        ]);

        User::create([
            'agency_id' => $agency->id,
            'name'      => $validated['admin_name'],
            'email'     => $validated['admin_email'],
            'password'  => Hash::make($validated['admin_password']),
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        return redirect()->route('agency.pending-approval')
            ->with('success', 'Your agency registration has been submitted. An administrator will review and activate your account.');
    }

    /**
     * Show the pending approval notice page.
     */
    public function pendingApproval(): View
    {
        return view('agencies.pending-approval');
    }
}
