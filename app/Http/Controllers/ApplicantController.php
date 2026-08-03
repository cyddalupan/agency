<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Country;
use App\Models\Employer;
use App\Models\StatusCode;
use App\Services\SensitiveActionLogger;
use App\Services\StatusCodeService;
use App\Services\StatusTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        $query = Applicant::with(['statusCode', 'position', 'agent']);

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

        // Filter by employer
        if ($request->filled('employer')) {
            $query->where('employer_id', $request->integer('employer'));
        }

        // Filter by country
        if ($request->filled('country')) {
            $query->where('country_id', $request->integer('country'));
        }

        $statusCodes = StatusCode::orderBy('sort_order')->get();

        // Get status counts for all applicants (ignoring filters)
        $statusCounts = Applicant::query()
            ->selectRaw('status_code, count(*) as total')
            ->whereNotNull('status_code')
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        $employers = Employer::orderBy('name')->get(['id', 'name']);
        $countries = Country::orderBy('name')->get(['id', 'name']);

        $applicants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('applicants.index', compact('applicants', 'statusCodes', 'statusCounts', 'employers', 'countries'));
    }

    public function create()
    {
        $defaults     = app_applicant_form_defaults();
        $agencyId     = resolve_agency_id();

        // Positions and statuses always show the FULL list on the Add Applicant form.
        // (Per Mjolnir "For Fixing" card: restricting to only the agency's newly-added
        // options caused "Data Missing" — users expected all options available.)
        $positions = \App\Models\Position::orderBy('name')->get();
        $statusCodes = \App\Models\StatusCode::orderBy('sort_order')->get();

        $sources   = array_values(array_intersect(app_source_options(), $defaults['sources'] ?? []));
        $branches  = \App\Models\Branch::where('agency_id', $agencyId)->orderBy('name')->get();
        $agents    = \App\Models\Agent::where('agency_id', $agencyId)->where('status', 'active')->orderBy('name')->get();

        return view('applicants.create', compact(
            'positions', 'statusCodes', 'sources', 'branches', 'agents', 'defaults'
        ));
    }

    public function store(Request $request)
    {
        $this->validateCustomFields($request, 'Applicant');

        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'suffix'       => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:255',
            'contact'      => 'nullable|string|max:50',
            'gender'       => 'nullable|string|max:20',
            'has_passport' => 'nullable|string|in:with,without',
            'birthdate'    => 'nullable|date',
            'address'      => 'nullable|string',
            'remarks'      => 'nullable|string',
            'source'       => 'nullable|string|max:255',
            'firstimer_type' => ['nullable', 'string', \Illuminate\Validation\Rule::in(['firstimer', 'ex-abroad'])],
            'country_id'   => 'nullable|integer|exists:countries,id',
            'position_id'  => 'nullable|integer|exists:positions,id',
            'agent_id'     => ['nullable', 'integer', 'exists:agents,id', function ($attribute, $value, $fail) use ($request) {
                if (blank($value)) {
                    return;
                }
                // When Source = Branch and an agent is selected, the agent must
                // belong to the selected branch (prevents cross-branch assignment).
                if ($request->input('source') === 'Branch' && $request->filled('branch_id')) {
                    $agent = \App\Models\Agent::find($value);
                    if (! $agent || (int) $agent->branch_id !== (int) $request->input('branch_id')) {
                        $fail('The selected agent does not belong to the selected branch.');
                    }
                }
            }],
            'branch_id'    => 'nullable|integer|exists:branches,id',
            'branch'       => 'nullable|string|max:255',
            'encoder'      => 'nullable|string|max:255',
            'contract'     => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'contract_received_date' => 'nullable|date',
            'status_code'  => 'nullable|integer|exists:status_codes,code',
            'photo'        => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
            'full_body_photo' => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
        ]);

        $validated['status_code'] = $validated['status_code'] ?? 0; // Default: Pending if not provided

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $validated['photo'] = resize_and_save_photo($request->file('photo'));
        }
        if ($request->hasFile('full_body_photo')) {
            $validated['full_body_photo'] = resize_and_save_photo($request->file('full_body_photo'), 'applicant-full-body-photos', 1024);
        }

        if ($request->hasFile('contract')) {
            $validated['contract'] = $request->file('contract')->store('contracts', 'public');
        }

        $validated['agency_id'] = $this->resolveAgencyId();
        if (! $validated['agency_id']) {
            return back()->withErrors(['agency' => 'No agency context. Please log in with an agency account to add applicants.'])->withInput();
        }

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
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'suffix'       => 'nullable|string|max:50',
            'email'        => 'nullable|email|max:255',
            'contact'      => 'nullable|string|max:50',
            'gender'       => 'nullable|string|max:20',
            'has_passport' => 'nullable|string|in:with,without',
            'birthdate'    => 'nullable|date',
            'address'      => 'nullable|string',
            'remarks'      => 'nullable|string',
            'source'       => 'nullable|string|max:255',
            'country_id'   => 'nullable|integer|exists:countries,id',
            'position_id'  => 'nullable|integer|exists:positions,id',
            'agent_id'     => 'nullable|integer|exists:agents,id',
            'branch'       => 'nullable|string|max:255',
            'encoder'      => 'nullable|string|max:255',
            'contract'     => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'contract_received_date' => 'nullable|date',
            'status_code'  => 'nullable|integer|exists:status_codes,code',
            'photo'        => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
            'full_body_photo' => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
            'employer_id'  => 'nullable|integer|exists:employers,id',
        ]);

        // Handle photo upload — delete old photo if replaced
        if ($request->hasFile('photo')) {
            if ($applicant->photo) {
                Storage::disk('public')->delete($applicant->photo);
            }
            $validated['photo'] = resize_and_save_photo($request->file('photo'));
        }
        // Handle full body photo upload
        if ($request->hasFile('full_body_photo')) {
            if ($applicant->full_body_photo) {
                Storage::disk('public')->delete($applicant->full_body_photo);
            }
            $validated['full_body_photo'] = resize_and_save_photo($request->file('full_body_photo'), 'applicant-full-body-photos', 1024);
        }

        // Handle contract file upload — delete old contract if replaced
        if ($request->hasFile('contract')) {
            if ($applicant->contract && Storage::disk('public')->exists($applicant->contract)) {
                Storage::disk('public')->delete($applicant->contract);
            }
            $validated['contract'] = $request->file('contract')->store('contracts', 'public');
        }

        $applicant->update($validated);

        $applicant->syncCustomFields($request->all());

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant updated successfully.');
    }

    public function destroy(Applicant $applicant)
    {
        SensitiveActionLogger::deletion($applicant);

        // Delete photo file if exists
        if ($applicant->photo) {
            Storage::disk('public')->delete($applicant->photo);
        }
        if ($applicant->full_body_photo) {
            Storage::disk('public')->delete($applicant->full_body_photo);
        }

        $applicant->delete();

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant deleted successfully.');
    }

    public function export(Request $request)
    {
        $query = Applicant::with(['statusCode', 'country', 'position', 'agent', 'employer'])->orderBy('created_at', 'desc');

        // Apply the same filters as index()
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status_code', $request->integer('status'));
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->input('gender'));
        }
        if ($request->filled('employer')) {
            $query->where('employer_id', $request->integer('employer'));
        }

        $applicants = $query->get();

        // Log the export
        SensitiveActionLogger::dataExport('applicant', auth()->user()->name . ' exported applicant data.');

        $headers = [
            'First Name', 'Last Name', 'Middle Name', 'Email', 'Contact',
            'Date of Birth', 'Gender', 'Has Passport', 'Nationality',
            'Street', 'City', 'State', 'Postal Code', 'Country',
            'Employer', 'Preferred Position', 'Referred By', 'Status', 'Created At',
        ];

        $callback = function () use ($applicants, $headers) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($applicants as $applicant) {
                fputcsv($file, [
                    $applicant->first_name,
                    $applicant->last_name,
                    $applicant->middle_name,
                    $applicant->email,
                    $applicant->contact,
                    $applicant->date_of_birth?->format('Y-m-d'),
                    $applicant->gender,
                    $applicant->has_passport ?? 'N/A',
                    $applicant->nationality,
                    $applicant->street,
                    $applicant->city,
                    $applicant->state,
                    $applicant->postal_code,
                    $applicant->country?->name ?? 'N/A',
                    $applicant->employer?->name ?? 'N/A',
                    $applicant->position?->name ?? 'N/A',
                    $applicant->agent?->name ?? 'N/A',
                    $applicant->statusCode?->name ?? 'N/A',
                    $applicant->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=applicants.csv',
        ]);
    }

    public function updateStatus(Request $request, Applicant $applicant, StatusTransitionService $transitionService)
    {
        $validated = $request->validate([
            'status_code' => ['required', 'integer', function ($attribute, $value, $fail) {
                // Only validate existence when status_codes table has data
                if (\App\Models\StatusCode::count() > 0 && !\App\Models\StatusCode::where('code', $value)->exists()) {
                    $fail('The selected status code is invalid.');
                }
            }],
        ]);

        $fromCode = $applicant->status_code;
        $toCode = (int) $validated['status_code'];

        // Only validate transition when reference data exists
        if (StatusCodeService::exists($fromCode) && StatusCodeService::exists($toCode)) {
            $error = $transitionService->validateTransition($fromCode, $toCode);

            if ($error) {
                return redirect()->back()
                    ->withErrors(['status_code' => $error]);
            }
        }

        $applicant->update(['status_code' => $toCode]);

        SensitiveActionLogger::log(
            'status_changed',
            subject: $applicant,
            description: auth()->user()->name . " changed applicant {$applicant->full_name} status from {$fromCode} to {$toCode}.",
            metadata: [
                'old_status' => $fromCode,
                'new_status' => $toCode,
            ],
        );

        return redirect()->back()
            ->with('success', 'Applicant status updated successfully.');
    }

    public function soa(Applicant $applicant)
    {
        if ($applicant->agency_id !== auth()->user()->agency_id) {
            abort(404);
        }

        $bills = Bill::with('payments')
            ->where('applicant_id', $applicant->id)
            ->latest()
            ->get();

        $totalCost = $bills->sum('applicant_cost');
        $totalPaid = $bills->flatMap->payments->sum('amount');
        $balance = $totalCost - $totalPaid;

        return view('applicants.soa', compact('applicant', 'bills', 'totalCost', 'totalPaid', 'balance'));
    }
}
