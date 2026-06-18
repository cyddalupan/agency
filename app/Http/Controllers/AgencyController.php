<?php

namespace App\Http\Controllers;

use App\Models\Agency;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgencyController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of agencies.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Agency::class);

        $query = Agency::orderBy('name');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $agencies = $query->paginate(20);

        return view('agencies.index', compact('agencies'));
    }

    /**
     * Show the form for creating a new agency.
     */
    public function create()
    {
        $this->authorize('create', Agency::class);

        return view('agencies.create');
    }

    /**
     * Store a newly created agency.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Agency::class);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'subdomain' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('agencies')],
        ]);

        $validated['status'] = 'active';

        Agency::create($validated);

        return redirect()->route('agencies.index')
            ->with('success', 'Agency created successfully.');
    }

    /**
     * Show the form for editing an agency.
     */
    public function edit(Agency $agency)
    {
        $this->authorize('update', $agency);

        return view('agencies.edit', compact('agency'));
    }

    /**
     * Update the specified agency.
     */
    public function update(Request $request, Agency $agency)
    {
        $this->authorize('update', $agency);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:255'],
            'subdomain' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('agencies')->ignore($agency->id),
            ],
        ]);

        $agency->update($validated);

        return redirect()->route('agencies.index')
            ->with('success', 'Agency updated successfully.');
    }

    /**
     * Deactivate the specified agency.
     */
    public function deactivate(Agency $agency)
    {
        $this->authorize('deactivate', $agency);

        $agency->update(['status' => 'inactive']);

        return redirect()->route('agencies.index')
            ->with('success', 'Agency deactivated successfully.');
    }

    /**
     * Activate the specified agency.
     */
    public function activate(Agency $agency)
    {
        $this->authorize('activate', $agency);

        $agency->update(['status' => 'active']);

        return redirect()->route('agencies.index')
            ->with('success', 'Agency activated successfully.');
    }
}
