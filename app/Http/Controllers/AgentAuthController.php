<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\StatusCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AgentAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('agent.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('agent')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('agent.dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function dashboard()
    {
        $agent = Auth::guard('agent')->user();

        // Get status counts for this agent's applicants
        $statusCounts = $agent->applicants()
            ->selectRaw('status_code, count(*) as total')
            ->whereNotNull('status_code')
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        // Build applicants query with optional status filter
        $query = $agent->applicants()
            ->with('country', 'position', 'statusCode');

        if (request()->filled('status')) {
            $query->where('status_code', request()->integer('status'));
        }

        $applicants = $query->orderBy('created_at', 'desc')->paginate(20);

        // Attach the status filter to pagination links
        if (request()->filled('status')) {
            $applicants->appends(['status' => request()->integer('status')]);
        }

        $statusCodes = StatusCode::orderBy('sort_order')->get();

        return view('agent.dashboard', compact('agent', 'applicants', 'statusCodes', 'statusCounts'));
    }

    public function logout(Request $request)
    {
        Auth::guard('agent')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('agent.login');
    }
}
