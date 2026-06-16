<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\StatusCode;
use Illuminate\Http\Request;



class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::with('statusCode');

        // Search by name (first, last, middle)
        if ($search = $request->input('search')) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        // Filter by status code
        if ($request->filled('status')) {
            $query->where('status_code', $request->integer('status'));
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }

        $statusCodes = StatusCode::orderBy('sort_order')->get();
        $applicants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('applicants.index', compact('applicants', 'statusCodes'));
    }

    public function create()
    {
        $statusCodes = StatusCode::orderBy('sort_order')->get();
        return view('applicants.create', compact('statusCodes'));
    }

    public function store(Request $request)
    {
        $this->validateCustomFields($request, 'Applicant');

        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix'      => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'contact'     => 'nullable|string|max:50',
            'gender'      => 'nullable|string|max:20',
            'birthdate'   => 'nullable|date',
            'address'     => 'nullable|string',
            'remarks'     => 'nullable|string',
            'source'      => 'nullable|string|max:255',
        ]);

        $validated['status_code'] = 0; // Default: Pending
        $validated['agency_id'] = auth()->user()->agency_id;

        $applicant = Applicant::create($validated);

        $applicant->syncCustomFields($request->all());

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant created successfully.');
    }

    public function show(Applicant $applicant)
    {
        $applicant->load([
            'statusCode',
            'country',
            'position',
            'passport',
            'education',
            'certificates',
            'requirements',
            'workExperiences',
            'skills',
            'references',
            'salaryRecords',
        ]);
        return view('applicants.show', compact('applicant'));
    }

    public function edit(Applicant $applicant)
    {
        $statusCodes = StatusCode::orderBy('sort_order')->get();
        return view('applicants.edit', compact('applicant', 'statusCodes'));
    }

    public function update(Request $request, Applicant $applicant)
    {
        $this->validateCustomFields($request, 'Applicant');

        $validated = $request->validate([
            'first_name'  => 'required|string|max:255',
            'last_name'   => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix'      => 'nullable|string|max:50',
            'email'       => 'nullable|email|max:255',
            'contact'     => 'nullable|string|max:50',
            'gender'      => 'nullable|string|max:20',
            'birthdate'   => 'nullable|date',
            'address'     => 'nullable|string',
            'remarks'     => 'nullable|string',
            'source'      => 'nullable|string|max:255',
            'status_code' => 'nullable|integer|exists:status_codes,code',
        ]);

        $applicant->update($validated);

        $applicant->syncCustomFields($request->all());

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant updated successfully.');
    }

    public function destroy(Applicant $applicant)
    {
        $applicant->delete();

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant deleted successfully.');
    }
}
