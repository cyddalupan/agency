<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgencyRegistrationController extends Controller
{
    public function showRegistrationForm()
    {
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }

        return view('agencies.public-register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'agency_name'    => 'required|string|max:255',
            'address'        => 'required|string|max:255',
            'city'           => 'required|string|max:100',
            'email'          => 'required|email|max:255|unique:agencies,email',
            'contact_person' => 'required|string|max:255',
            'num_branches'   => 'required|integer|min:1',
            'admin_username' => 'required|string|max:50|unique:users,username',
            'admin_password' => 'required|string|min:1',
        ]);

        $agency = Agency::create([
            'name'           => $validated['agency_name'],
            'address'        => $validated['address'],
            'city'           => $validated['city'],
            'email'          => $validated['email'],
            'contact_person' => $validated['contact_person'],
            'num_branches'   => $validated['num_branches'],
            'status'         => 'pending',
        ]);

        User::create([
            'agency_id' => $agency->id,
            'name'      => $validated['contact_person'],
            'email'     => $validated['email'],
            'username'  => trim($validated['admin_username']),
            'password'  => Hash::make($validated['admin_password']),
            'user_type' => 'admin',
            'status'    => 'active',
        ]);

        return redirect()
            ->route('agency.pending-approval')
            ->with('success', 'Your agency registration has been submitted! Please wait for approval.');
    }

    public function pendingApproval()
    {
        return view('agencies.pending-approval');
    }
}
