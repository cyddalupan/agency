<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Employer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BillController extends Controller
{
    public function index(): View
    {
        $bills = Bill::with(['employer', 'applicant'])
            ->latest()
            ->paginate(15);

        return view('bills.index', compact('bills'));
    }

    public function create(): View
    {
        $employers = Employer::where('agency_id', auth()->user()->agency_id)
            ->orderBy('name')
            ->get();

        $applicants = Applicant::where('agency_id', auth()->user()->agency_id)
            ->orderBy('last_name')
            ->get();

        return view('bills.create', compact('employers', 'applicants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employer_id'      => 'required|exists:employers,id',
            'applicant_id'     => 'nullable|exists:applicants,id',
            'employer_cost'    => 'required|numeric|min:0',
            'applicant_cost'   => 'nullable|numeric|min:0',
            'employer_deposit' => 'nullable|numeric|min:0',
            'applicant_deposit'=> 'nullable|numeric|min:0',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }
        $validated['status'] = 'pending';

        Bill::create($validated);

        return redirect()->route('bills.index')
            ->with('success', 'Bill created successfully.');
    }

    public function show(Bill $bill): View
    {
        $bill->load(['employer', 'applicant', 'payments']);
        return view('bills.show', compact('bill'));
    }

    public function edit(Bill $bill): View
    {
        $employers = Employer::where('agency_id', auth()->user()->agency_id)
            ->orderBy('name')
            ->get();

        $applicants = Applicant::where('agency_id', auth()->user()->agency_id)
            ->orderBy('last_name')
            ->get();

        return view('bills.edit', compact('bill', 'employers', 'applicants'));
    }

    public function update(Request $request, Bill $bill): RedirectResponse
    {
        $validated = $request->validate([
            'employer_id'      => 'required|exists:employers,id',
            'applicant_id'     => 'nullable|exists:applicants,id',
            'employer_cost'    => 'required|numeric|min:0',
            'applicant_cost'   => 'nullable|numeric|min:0',
            'employer_deposit' => 'nullable|numeric|min:0',
            'applicant_deposit'=> 'nullable|numeric|min:0',
            'status'           => 'nullable|string|max:50',
            'notes'            => 'nullable|string|max:1000',
        ]);

        $bill->update($validated);

        return redirect()->route('bills.index')
            ->with('success', 'Bill updated successfully.');
    }

    public function destroy(Bill $bill): RedirectResponse
    {
        $bill->delete();

        return redirect()->route('bills.index')
            ->with('success', 'Bill deleted successfully.');
    }
}
