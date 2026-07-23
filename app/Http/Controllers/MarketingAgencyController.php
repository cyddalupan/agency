<?php

namespace App\Http\Controllers;

use App\Models\MarketingAgency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingAgencyController extends Controller
{
    public function index(): View
    {
        $agencies = MarketingAgency::latest()->paginate(15);
        return view('marketing-agencies.index', compact('agencies'));
    }

    public function create(): View
    {
        return view('marketing-agencies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'contact'         => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|max:50',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }

        MarketingAgency::create($validated);

        return redirect()->route('marketing-agencies.index')
            ->with('success', 'Marketing agency created successfully.');
    }

    public function show(MarketingAgency $marketingAgency): View
    {
        $marketingAgency->load('marketingAgents');
        return view('marketing-agencies.show', compact('marketingAgency'));
    }

    public function edit(MarketingAgency $marketingAgency): View
    {
        return view('marketing-agencies.edit', compact('marketingAgency'));
    }

    public function update(Request $request, MarketingAgency $marketingAgency): RedirectResponse
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'contact_person'  => 'nullable|string|max:255',
            'contact'         => 'nullable|string|max:100',
            'email'           => 'nullable|email|max:255',
            'address'         => 'nullable|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|max:50',
        ]);

        $marketingAgency->update($validated);

        return redirect()->route('marketing-agencies.index')
            ->with('success', 'Marketing agency updated successfully.');
    }

    public function destroy(MarketingAgency $marketingAgency): RedirectResponse
    {
        $marketingAgency->delete();

        return redirect()->route('marketing-agencies.index')
            ->with('success', 'Marketing agency deleted successfully.');
    }
}
