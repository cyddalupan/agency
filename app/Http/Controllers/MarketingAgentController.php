<?php

namespace App\Http\Controllers;

use App\Models\MarketingAgency;
use App\Models\MarketingAgent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MarketingAgentController extends Controller
{
    public function index(MarketingAgency $marketingAgency): View
    {
        $agents = $marketingAgency->marketingAgents()->latest()->paginate(15);
        return view('marketing-agents.index', compact('marketingAgency', 'agents'));
    }

    public function create(MarketingAgency $marketingAgency): View
    {
        return view('marketing-agents.create', compact('marketingAgency'));
    }

    public function store(Request $request, MarketingAgency $marketingAgency): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'nullable|string|max:100',
            'email'   => 'nullable|email|max:255',
            'status'  => 'nullable|string|max:50',
        ]);

        $validated['agency_id'] = auth()->user()->agency_id;
        $validated['marketing_agency_id'] = $marketingAgency->id;

        MarketingAgent::create($validated);

        return redirect()->route('marketing-agencies.marketing-agents.index', $marketingAgency)
            ->with('success', 'Marketing agent created successfully.');
    }

    public function show(MarketingAgency $marketingAgency, MarketingAgent $marketingAgent): View
    {
        $marketingAgent->load(['marketingAgency']);
        return view('marketing-agents.show', compact('marketingAgency', 'marketingAgent'));
    }

    public function edit(MarketingAgency $marketingAgency, MarketingAgent $marketingAgent): View
    {
        return view('marketing-agents.edit', compact('marketingAgency', 'marketingAgent'));
    }

    public function update(Request $request, MarketingAgency $marketingAgency, MarketingAgent $marketingAgent): RedirectResponse
    {
        $validated = $request->validate([
            'name'    => 'required|string|max:255',
            'contact' => 'nullable|string|max:100',
            'email'   => 'nullable|email|max:255',
            'status'  => 'nullable|string|max:50',
        ]);

        $marketingAgent->update($validated);

        return redirect()->route('marketing-agencies.marketing-agents.index', $marketingAgency)
            ->with('success', 'Marketing agent updated successfully.');
    }

    public function destroy(MarketingAgency $marketingAgency, MarketingAgent $marketingAgent): RedirectResponse
    {
        $marketingAgent->delete();

        return redirect()->route('marketing-agencies.marketing-agents.index', $marketingAgency)
            ->with('success', 'Marketing agent deleted successfully.');
    }
}
