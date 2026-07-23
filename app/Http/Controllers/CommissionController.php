<?php

namespace App\Http\Controllers;

use App\Models\Commission;
use App\Models\Employer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommissionController extends Controller
{
    public function index(): View
    {
        $commissions = Commission::with(['employer'])
            ->latest()
            ->paginate(15);

        return view('commissions.index', compact('commissions'));
    }

    public function create(): View
    {
        $employers = Employer::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('commissions.create', compact('employers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employer_id' => 'nullable|exists:employers,id',
            'amount'      => 'required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'status'      => 'nullable|string|in:pending,partial,paid',
            'due_date'    => 'nullable|date',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }
        $validated['paid_amount'] ??= 0;

        Commission::create($validated);

        return redirect()->route('commissions.index')
            ->with('success', 'Commission recorded successfully.');
    }

    public function show(Commission $commission): View
    {
        $commission->load(['employer']);
        return view('commissions.show', compact('commission'));
    }

    public function edit(Commission $commission): View
    {
        $employers = Employer::where('agency_id', auth()->user()->agency_id)
            ->latest()
            ->get();

        return view('commissions.edit', compact('commission', 'employers'));
    }

    public function update(Request $request, Commission $commission): RedirectResponse
    {
        $validated = $request->validate([
            'employer_id' => 'nullable|exists:employers,id',
            'amount'      => 'required|numeric|min:0.01',
            'paid_amount' => 'nullable|numeric|min:0',
            'status'      => 'nullable|string|in:pending,partial,paid',
            'due_date'    => 'nullable|date',
            'notes'       => 'nullable|string|max:1000',
        ]);

        $validated['paid_amount'] ??= 0;

        $commission->update($validated);

        return redirect()->route('commissions.index')
            ->with('success', 'Commission updated successfully.');
    }

    public function destroy(Commission $commission): RedirectResponse
    {
        $commission->delete();

        return redirect()->route('commissions.index')
            ->with('success', 'Commission deleted successfully.');
    }
}
