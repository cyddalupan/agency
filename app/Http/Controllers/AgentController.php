<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::with('agency');

        // Super admin sees all agents; agency users see only theirs
        if (auth()->user()->user_type !== 'super_admin') {
            $query->where('agency_id', $this->resolveAgencyId());
        }

        // Filtering
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $agents = $query->orderBy('name')->paginate(20);

        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        $branches = Branch::where('agency_id', auth()->user()->agency_id)->orderBy('name')->get();

        return view('agents.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|max:255|unique:agents,email',
            'contact'         => 'nullable|string|max:50',
            'branch_id'       => [
                'nullable',
                Rule::exists('branches', 'id')->where(function ($query) {
                    $query->where('agency_id', auth()->user()->agency_id);
                }),
            ],
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();

        // Super admin can create agents without an agency (they can assign later)
        if (!$validated['agency_id'] && auth()->user()->user_type !== 'super_admin') {
            return back()->withErrors(['agency' => 'No agency context.'])->withInput();
        }

        Agent::create($validated);

        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully.');
    }

    public function edit(Agent $agent)
    {
        $branches = Branch::where('agency_id', $agent->agency_id)->orderBy('name')->get();

        return view('agents.edit', compact('agent', 'branches'));
    }

    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['required', 'email', 'max:255', Rule::unique('agents', 'email')->ignore($agent->id)],
            'contact'         => 'nullable|string|max:50',
            'branch_id'       => [
                'nullable',
                Rule::exists('branches', 'id')->where(function ($query) use ($agent) {
                    $query->where('agency_id', $agent->agency_id);
                }),
            ],
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

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
