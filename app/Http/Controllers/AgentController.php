<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index()
    {
        $agents = Agent::with('agency')
            ->where('agency_id', $this->resolveAgencyId())
            ->orderBy('name')
            ->paginate(20);

        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        return view('agents.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:agents,email',
            'contact'         => 'nullable|string|max:50',
            'password'        => 'required|string|min:8|confirmed',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['agency_id'] = $this->resolveAgencyId();

        if (!$validated['agency_id']) {
            return back()->withErrors(['agency' => 'No agency context.'])->withInput();
        }

        Agent::create($validated);

        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully.');
    }

    public function edit(Agent $agent)
    {
        return view('agents.edit', compact('agent'));
    }

    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', 'max:255', Rule::unique('agents', 'email')->ignore($agent->id)],
            'contact'         => 'nullable|string|max:50',
            'password'        => 'nullable|string|min:8|confirmed',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $agent->update($validated);

        return redirect()->route('agents.index')
            ->with('success', 'Agent updated successfully.');
    }

    public function destroy(Agent $agent)
    {
        $agent->update(['status' => 'inactive']);

        return redirect()->route('agents.index')
            ->with('success', 'Agent deactivated.');
    }
}
