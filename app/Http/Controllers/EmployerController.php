<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Country;
use App\Models\Employer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployerController extends Controller
{
    public function index(): View
    {
        $employers = Employer::latest()->paginate(15);
        return view('employers.index', compact('employers'));
    }

    public function create(): View
    {
        $countries = Country::orderBy('name')->get();
        return view('employers.create', compact('countries'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->validateCustomFields($request, 'Employer');

        $validated = $request->validate([
            'company_no'    => 'nullable|string|max:50',
            'name'          => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact'       => 'nullable|string|max:100',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'country_id'    => 'nullable|exists:countries,id',
        ]);

        $validated['agency_id'] = auth()->user()->agency_id;

        $employer = Employer::create($validated);

        $employer->syncCustomFields($request->all());

        return redirect()->route('employers.index')
            ->with('success', 'Employer created successfully.');
    }

    public function show(Employer $employer): View
    {
        $employer->load('country', 'jobPositions');
        return view('employers.show', compact('employer'));
    }

    public function edit(Employer $employer): View
    {
        $countries = Country::orderBy('name')->get();
        return view('employers.edit', compact('employer', 'countries'));
    }

    public function update(Request $request, Employer $employer): RedirectResponse
    {
        $this->validateCustomFields($request, 'Employer');

        $validated = $request->validate([
            'company_no'    => 'nullable|string|max:50',
            'name'          => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact'       => 'nullable|string|max:100',
            'email'         => 'nullable|email|max:255',
            'address'       => 'nullable|string',
            'country_id'    => 'nullable|exists:countries,id',
            'status'        => 'nullable|string|max:50',
        ]);

        $employer->update($validated);

        $employer->syncCustomFields($request->all());

        return redirect()->route('employers.index')
            ->with('success', 'Employer updated successfully.');
    }

    public function destroy(Employer $employer): RedirectResponse
    {
        $employer->delete();

        return redirect()->route('employers.index')
            ->with('success', 'Employer deleted successfully.');
    }

    public function soa(Employer $employer): View
    {
        $bills = Bill::with('payments')
            ->where('employer_id', $employer->id)
            ->latest()
            ->get();

        $totalBilled = $bills->sum('employer_cost');
        $totalPaid = $bills->flatMap->payments->sum('amount');
        $balance = $totalBilled - $totalPaid;

        return view('employers.soa', compact('employer', 'bills', 'totalBilled', 'totalPaid', 'balance'));
    }
}
