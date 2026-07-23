<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use App\Models\Country;
use App\Models\Employer;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

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

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) { return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account.'])->withInput(); }

        $employer = Employer::create($validated);

        $employer->syncCustomFields($request->all());

        // Auto-create employer login user if email is set and no user exists
        if ($employer->email && ! User::where('email', $employer->email)->exists()) {
            $password = $request->filled('password') ? $request->password : Str::random(12);
            User::create([
                'name'        => $employer->contact_person ?? $employer->name,
                'email'       => $employer->email,
                'password'    => bcrypt($password),
                'user_type'   => 'employer',
                'employer_id' => $employer->id,
                'agency_id'   => $employer->agency_id,
            ]);
        }

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
