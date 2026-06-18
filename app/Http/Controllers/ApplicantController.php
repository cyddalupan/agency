<?php

namespace App\Http\Controllers;

use App\Models\Applicant;
use App\Models\Bill;
use App\Models\StatusCode;
use App\Services\SensitiveActionLogger;
use App\Services\StatusCodeService;
use App\Services\StatusTransitionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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
        SensitiveActionLogger::deletion($applicant);

        $applicant->delete();

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant deleted successfully.');
    }

    public function export()
    {
        $applicants = Applicant::with('statusCode')->get();

        // Log the export
        SensitiveActionLogger::dataExport('applicant', auth()->user()->name . ' exported applicant data.');

        $headers = [
            'First Name', 'Last Name', 'Middle Name', 'Email', 'Contact',
            'Date of Birth', 'Gender', 'Nationality',
            'Street', 'City', 'State', 'Postal Code', 'Country',
            'Status', 'Created At',
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
                    $applicant->nationality,
                    $applicant->street,
                    $applicant->city,
                    $applicant->state,
                    $applicant->postal_code,
                    $applicant->country,
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
