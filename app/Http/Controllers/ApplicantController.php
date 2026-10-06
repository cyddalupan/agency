<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Bill;
use App\Models\Branch;
use App\Models\CivilStatus;
use App\Models\Country;
use App\Models\Employer;
use App\Models\Language;
use App\Models\Nationality;
use App\Models\Position;
use App\Models\Religion;
use App\Models\Skill;
use App\Models\StatusCode;
use App\Services\SensitiveActionLogger;
use App\Support\ApplicantDuplicateChecker;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        // Withdrawn & Repat statuses (35 Repatriated, 38 Cancel, 50 Backout)
        // live ONLY on the Withdrawn & Repat tab — never the main applicants
        // page. (Toybits report 2026-08-10.)
        $withdrawnStatuses = [35, 38, 50];

        $query = Applicant::with(['statusCode', 'position', 'agent', 'branch', 'country', 'contractRecords'])
            ->forBranchUser()
            ->whereNotIn('status_code', $withdrawnStatuses);

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

        // Chips/dropdown exclude withdrawn statuses too (they have their own tab).
        $statusCodes = StatusCode::whereNotIn('code', $withdrawnStatuses)
            ->orderBy('sort_order')
            ->get();

        // Get status counts for all applicants (ignoring filters), excluding
        // the withdrawn & repat statuses.
        $statusCounts = Applicant::query()
            ->forBranchUser()
            ->selectRaw('status_code, count(*) as total')
            ->whereNotNull('status_code')
            ->whereNotIn('status_code', $withdrawnStatuses)
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        $employers = Employer::orderBy('name')->get(['id', 'name']);
        $countries = Country::orderBy('name')->get(['id', 'name']);

        // Per-agency table column selection (see app_applicant_table_columns).
        $tableColumns = app_applicant_table_columns();

        $applicants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('applicants.index', compact('applicants', 'statusCodes', 'statusCounts', 'employers', 'countries', 'tableColumns'));
    }

    /**
     * Withdrawn & Repat tab — applicants whose status is Cancel (38),
     * Backout (50), or Repatriated (35). Same list/filters as index()
     * but restricted to those three statuses.
     */
    public function withdrawn(Request $request)
    {
        // (Mjolnir "Backout, Repat Module" 2026-10-06) Restricted to Admin and
        // Accounting users only.
        abort_unless(auth()->user()->canViewBackoutRepat(), 403, 'This folder is restricted to Admin and Accounting.');

        $withdrawnStatuses = [35, 38, 50]; // Repatriated, Cancel, Backout

        $query = Applicant::with(['statusCode', 'position', 'agent', 'branch', 'contractRecords'])
            ->forBranchUser()
            ->whereIn('status_code', $withdrawnStatuses);

        // Search by name (first, last, middle)
        if ($search = $request->input('search')) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('middle_name', 'like', "%{$search}%");
            });
        }

        // Filter by status code (within the three)
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

        $statusCodes = StatusCode::whereIn('code', $withdrawnStatuses)->orderBy('sort_order')->get();

        // Get status counts for the three statuses (ignoring filters)
        $statusCounts = Applicant::query()
            ->forBranchUser()
            ->selectRaw('status_code, count(*) as total')
            ->whereIn('status_code', $withdrawnStatuses)
            ->whereNotNull('status_code')
            ->groupBy('status_code')
            ->pluck('total', 'status_code');

        $employers = Employer::orderBy('name')->get(['id', 'name']);
        $countries = Country::orderBy('name')->get(['id', 'name']);

        $applicants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('applicants.withdrawn', compact('applicants', 'statusCodes', 'statusCounts', 'employers', 'countries'));
    }

    /**
     * Live duplicate check for the Add Applicant form (AJAX).
     * Mirrors the store() guard so the warning can appear as the user types.
     */
    public function checkDuplicates(Request $request): JsonResponse
    {
        $agencyId = $this->resolveAgencyId();
        if (! $agencyId) {
            return response()->json(['count' => 0, 'duplicates' => []]);
        }

        $duplicates = ApplicantDuplicateChecker::find(
            $agencyId,
            $request->all(),
            $request->integer('exclude_id') ?: null
        );

        return response()->json([
            'count' => $duplicates->count(),
            'duplicates' => $this->duplicatePayload($duplicates),
        ]);
    }

    /** Shape duplicate matches for views / JSON. */
    private function duplicatePayload($duplicates): array
    {
        return $duplicates->map(fn ($m) => [
            'id' => $m['applicant']->id,
            'applicant_no' => $m['applicant']->applicant_no,
            'name' => trim($m['applicant']->first_name.' '.$m['applicant']->last_name),
            'status' => $m['applicant']->status,
            'created_at' => optional($m['applicant']->created_at)->format('M d, Y'),
            'url' => route('applicants.show', $m['applicant']),
            'reasons' => $m['reasons'],
        ])->values()->all();
    }

    public function create()
    {
        $defaults = app_applicant_form_defaults();
        $agencyId = resolve_agency_id();

        // Positions and statuses always show the FULL list on the Add Applicant form.
        // (Per Mjolnir "For Fixing" card: restricting to only the agency's newly-added
        // options caused "Data Missing" — users expected all options available.)
        $positions = Position::orderBy('name')->get();
        $statusCodes = StatusCode::orderBy('sort_order')->get();

        $nationalities = Nationality::orderBy('name')->get();
        $religions = Religion::orderBy('name')->get();
        $civilStatuses = CivilStatus::orderBy('name')->get();

        $sources = array_values(array_intersect(app_source_options(), $defaults['sources'] ?? []));
        $branches = $this->assignableBranches();
        $agents = $this->assignableAgents();

        // (PI card) Skills & Languages restricted to the Settings-configured lists.
        $skills = Skill::orderBy('name')->get();
        $languages = Language::orderBy('name')->get();

        // (Branch feature) Branch dropdown default: the logged-in branch user's
        // own branch; null for agency admins (they pick freely).
        $defaultBranchId = $this->defaultBranchId();

        return view('applicants.create', compact(
            'positions', 'statusCodes', 'nationalities', 'religions', 'civilStatuses',
            'sources', 'branches', 'agents', 'defaults', 'skills', 'languages', 'defaultBranchId'
        ));
    }

    public function store(Request $request)
    {
        $this->validateCustomFields($request, 'Applicant');

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:20',
            'has_passport' => 'nullable|string|in:with,without',
            'education_level' => 'nullable|string|in:high_school,vocational,bachelor,master',
            'passport_no' => 'nullable|string|max:50',
            'passport_issue_date' => 'nullable|date',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'passport_place_of_issue' => 'nullable|string|max:255',
            'civil_status_id' => ['nullable', 'integer', 'exists:civil_statuses,id'],
            'nationality_id' => ['nullable', 'integer', 'exists:nationalities,id'],
            'religion_id' => ['nullable', 'integer', 'exists:religions,id'],
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255|exists:skills,name',
            'languages' => 'nullable|array',
            'languages.*' => 'nullable|string|max:255|exists:languages,name',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'remarks' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'firstimer_type' => ['nullable', 'string', Rule::in(['firstimer', 'ex-abroad'])],
            'country_id' => 'nullable|integer|exists:countries,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'expected_salary' => 'nullable|numeric|min:0',
            'employer_id' => 'nullable|integer|exists:employers,id',
            'agent_id' => ['nullable', 'integer', 'exists:agents,id', function ($attribute, $value, $fail) use ($request) {
                if (blank($value)) {
                    return;
                }
                // When Source = Branch and an agent is selected, the agent must
                // belong to the selected branch (prevents cross-branch assignment).
                if ($request->input('source') === 'Branch' && $request->filled('branch_id')) {
                    $agent = Agent::find($value);
                    if (! $agent || (int) $agent->branch_id !== (int) $request->input('branch_id')) {
                        $fail('The selected agent does not belong to the selected branch.');
                    }
                }
            }],
            'branch_id' => 'nullable|integer|exists:branches,id',
            'branch' => 'nullable|string|max:255',
            'encoder' => 'nullable|string|max:255',
            'contract' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'contract_received_date' => 'nullable|date',
            'status_code' => 'nullable|integer|exists:status_codes,code',
            'photo' => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
            'full_body_photo' => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
        ]);

        $validated['status_code'] = $validated['status_code'] ?? 0; // Default: Pending if not provided

        // (Branch feature) Enforce branch ownership on create: a branch account
        // may only create applicants in its own branch. If omitted, default to
        // the logged-in branch user's branch.
        $this->applyBranchDefaults($validated);

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

        // Encoder is auto-derived (stored in DB, not editable by users) and
        // created_by uses Laravel's default convention (auth user id).
        // Name only — no date/time suffix (Cyd 2026-08-16).
        $validated['encoder'] = $validated['encoder'] ?? auth()->user()->name;
        $validated['created_by'] = auth()->id();

        // Duplicate guard (Cyd 2026-09-26): if this applicant looks like one we
        // already have, bounce back with the matches and let the user confirm.
        // The form resubmits with confirm_duplicate=1 to proceed.
        if (! $request->boolean('confirm_duplicate')) {
            $duplicates = ApplicantDuplicateChecker::find($validated['agency_id'], array_merge($request->all(), [
                'birthdate'  => $request->input('birthdate', $validated['birthdate'] ?? null),
                'passport_no' => $request->input('passport_no'),
            ]));

            if ($duplicates->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('duplicate_applicants', $this->duplicatePayload($duplicates))
                    ->withErrors([
                        'duplicate' => 'Possible duplicate applicant found. Review the existing record(s), then click "Create anyway" if this is a different person.',
                    ]);
            }
        }

        $applicant = Applicant::create($validated);

        $this->syncPassport($request, $applicant);

        $applicant->syncCustomFields($request->all());
        $this->syncSkillsLanguages($applicant);

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant created successfully.');
    }

    // ---------------------------------------------------------------------
    // Bulk CSV upload (page, template download, import)
    // ---------------------------------------------------------------------

    /**
     * Bulk upload page: explains the flow, offers the template download and
     * the CSV upload form.
     */
    public function bulkUpload()
    {
        $agencyId = resolve_agency_id();

        // Reference lists so staff can type exact names and avoid row errors.
        $statusCodes = StatusCode::orderBy('sort_order')->get();
        $agents = $this->assignableAgents();
        $branches = $this->assignableBranches();
        $employers = Employer::where('agency_id', $agencyId)->orderBy('name')->get(['id', 'name']);
        $positions = Position::orderBy('name')->get(['id', 'name']);
        $nationalities = Nationality::orderBy('name')->get(['id', 'name']);
        $religions = Religion::orderBy('name')->get(['id', 'name']);
        $civilStatuses = CivilStatus::orderBy('name')->get(['id', 'name']);
        $countries = Country::orderBy('name')->get(['id', 'name']);

        return view('applicants.bulk', compact(
            'statusCodes', 'agents', 'branches', 'employers', 'positions',
            'nationalities', 'religions', 'civilStatuses', 'countries'
        ));
    }

    /**
     * Download the bulk import CSV template. Includes two SAMPLE rows (with
     * different statuses) so users can follow the exact format.
     */
    public function bulkTemplate()
    {
        $statusLabels = StatusCode::orderBy('sort_order')->pluck('label')->take(2);
        $sampleStatus1 = $statusLabels[0] ?? 'Pending';
        $sampleStatus2 = $statusLabels[1] ?? 'For Interview';

        $headers = [
            'First Name', 'Middle Name', 'Last Name', 'Suffix', 'Gender', 'Birthdate',
            'Contact', 'Email', 'Address', 'Nationality', 'Religion', 'Civil Status',
            'Country', 'Position', 'Employer', 'Agent', 'Branch', 'Source', 'Status', 'Remarks',
            'Date Applied', 'Passport #', 'Date Issued', 'Place Issued', 'Expiration',
        ];

        $sampleRows = [
            [
                'Juan', 'P.', 'Dela Cruz', '', 'Male', '1995-03-14', '09171234567',
                'juan.sample@email.com', '123 Sample St, Manila', 'Filipino', 'Christian', 'Single',
                'Saudi Arabia', 'Caregiver', '', '', '', 'Walk-in', $sampleStatus1,
                'SAMPLE ROW - replace with real data then delete',
                '2026-09-15', 'P1234567A', '2021-05-10', 'DFA Manila', '2031-05-09',
            ],
            [
                'Maria', 'S.', 'Santos', '', 'Female', '1990-07-22', '09179876543',
                'maria.sample@email.com', '456 Another St, Cebu City', 'Filipino', 'Christian', 'Married',
                'UAE', 'Chef', '', '', '', 'Facebook', $sampleStatus2,
                'SAMPLE ROW - replace with real data then delete',
                '2026-09-20', '', '', '', '',
            ],
        ];

        $callback = function () use ($headers, $sampleRows) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Excel compatibility
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers);

            foreach ($sampleRows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=applicants_bulk_template.csv',
        ]);
    }

    /**
     * Parse the uploaded CSV, validate EVERY row first, and only then insert.
     * Any row error aborts the whole import and reports each bad row (with its
     * CSV line number) so the file can be fixed and re-uploaded.
     *
     * Matching is deliberately case/space-insensitive: lookups (agent, branch,
     * employer, status, etc.) are resolved on a trimmed + lowercased key, so
     * "PENDING" or "For  Interview " never fail because of casing/spacing.
     */
    public function bulkImport(Request $request)
    {
        $agencyId = $this->resolveAgencyId();
        if (! $agencyId) {
            return back()->withErrors(['csv_file' => 'No agency context. Please log in with an agency account to import applicants.']);
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv_file');
        $rows = $this->parseBulkCsv($file->getRealPath());
        if (is_string($rows)) {
            // parse error (bad file / missing required headers / too many rows)
            return back()->withErrors(['csv_file' => $rows]);
        }

        $user = auth()->user();
        $branchLocked = $user && $user->isBranchLocked();
        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        // ---- Preload lookup maps (name-key => id), scoped for this user ----
        $agentOptions = $this->assignableAgents()->load('branch:id,name');
        $branchOptions = $this->assignableBranches();
        $employerOptions = Employer::where('agency_id', $agencyId)->get();

        $nameMap = function ($collection) use ($norm) {
            $map = [];
            foreach ($collection as $item) {
                $key = $norm($item->name);
                if ($key !== '' && ! array_key_exists($key, $map)) {
                    $map[$key] = $item;
                }
            }

            return $map;
        };

        $agentMap = $nameMap($agentOptions);
        $branchMap = $nameMap($branchOptions);
        $employerMap = $nameMap($employerOptions);
        $nationalityMap = $nameMap(Nationality::all());
        $religionMap = $nameMap(Religion::all());
        $civilStatusMap = $nameMap(CivilStatus::all());
        $countryMap = $nameMap(Country::all());
        $positionMap = $nameMap(Position::all());

        $statusByLabel = [];
        $statusByCode = [];
        foreach (StatusCode::all() as $sc) {
            $statusByLabel[$norm($sc->label)] = (int) $sc->code;
            $statusByCode[$norm((string) $sc->code)] = (int) $sc->code;
        }

        $errors = [];
        $validatedRows = [];
        $maxRows = 2000;

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            $first = trim((string) ($d['first_name'] ?? ''));
            $last = trim((string) ($d['last_name'] ?? ''));
            if ($first === '' && $last === '') {
                continue; // fully empty line -> skip silently
            }

            // ---- Plain fields ----
            if ($first === '') {
                $rowErrors[] = 'First Name is required.';
            } elseif (mb_strlen($first) > 255) {
                $rowErrors[] = 'First Name is too long (max 255).';
            }
            if ($last === '') {
                $rowErrors[] = 'Last Name is required.';
            } elseif (mb_strlen($last) > 255) {
                $rowErrors[] = 'Last Name is too long (max 255).';
            }

            $middle = trim((string) ($d['middle_name'] ?? ''));
            $suffix = trim((string) ($d['suffix'] ?? ''));
            $gender = trim((string) ($d['gender'] ?? ''));
            $email = mb_strtolower(trim((string) ($d['email'] ?? '')));
            $contact = trim((string) ($d['contact'] ?? ''));
            $address = trim((string) ($d['address'] ?? ''));
            $remarks = trim((string) ($d['remarks'] ?? ''));
            $source = trim((string) ($d['source'] ?? ''));

            if (mb_strlen($middle) > 255) {
                $rowErrors[] = 'Middle Name is too long (max 255).';
            }
            if (mb_strlen($suffix) > 50) {
                $rowErrors[] = 'Suffix is too long (max 50).';
            }
            if (mb_strlen($gender) > 20) {
                $rowErrors[] = 'Gender is too long (max 20).';
            } elseif ($gender !== '') {
                $gender = ucfirst(mb_strtolower($gender)); // Male / Female / etc.
            }
            if ($email !== '' && ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Email '{$email}' is not a valid email address.";
            }
            if (mb_strlen($contact) > 50) {
                $rowErrors[] = 'Contact is too long (max 50).';
            }

            // ---- Birthdate (flexible formats; stored as Y-m-d) ----
            $birthdate = null;
            $birthRaw = trim((string) ($d['birthdate'] ?? ''));
            if ($birthRaw !== '') {
                try {
                    $birthdate = Carbon::parse($birthRaw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $rowErrors[] = "Birthdate '{$birthRaw}' is not a valid date.";
                }
            }

            // ---- Date Applied (optional; maps to the applicant's "Date Applied",
            // i.e. created_at shown in Browse Applicants) — Toybits 2026-10-06 ----
            $dateApplied = null;
            $dateAppliedRaw = trim((string) ($d['date_applied'] ?? ''));
            if ($dateAppliedRaw !== '') {
                try {
                    $dateApplied = Carbon::parse($dateAppliedRaw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $rowErrors[] = "Date Applied '{$dateAppliedRaw}' is not a valid date.";
                }
            }

            // ---- Passport details (optional) — Toybits 2026-10-06 ----
            $passportNo = trim((string) ($d['passport_no'] ?? ''));
            $passportIssue = null;
            $passportIssueRaw = trim((string) ($d['passport_issue_date'] ?? ''));
            if ($passportIssueRaw !== '') {
                try {
                    $passportIssue = Carbon::parse($passportIssueRaw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $rowErrors[] = "Date Issued '{$passportIssueRaw}' is not a valid date.";
                }
            }
            $passportExpiry = null;
            $passportExpiryRaw = trim((string) ($d['passport_expiry_date'] ?? ''));
            if ($passportExpiryRaw !== '') {
                try {
                    $passportExpiry = Carbon::parse($passportExpiryRaw)->format('Y-m-d');
                } catch (\Throwable $e) {
                    $rowErrors[] = "Expiration '{$passportExpiryRaw}' is not a valid date.";
                }
            }
            $passportPlace = trim((string) ($d['passport_place_of_issue'] ?? ''));
            if (mb_strlen($passportNo) > 50) {
                $rowErrors[] = 'Passport # is too long (max 50).';
            }
            if (mb_strlen($passportPlace) > 255) {
                $rowErrors[] = 'Place Issued is too long (max 255).';
            }
            if ($passportIssue && $passportExpiry && $passportExpiry <= $passportIssue) {
                $rowErrors[] = 'Expiration must be after Date Issued.';
            }

            // ---- Lookup-by-name columns ----
            $resolve = function ($raw, $map, string $label) use ($norm, &$rowErrors) {
                $raw = trim((string) $raw);
                if ($raw === '') {
                    return null;
                }
                $item = $map[$norm($raw)] ?? null;
                if (! $item) {
                    $available = collect(array_keys($map))->take(6)->map(fn ($k) => ucwords($k))->implode(', ');
                    $rowErrors[] = "{$label} '{$raw}' not found.".($available !== '' ? " Available: {$available}" : '');

                    return null;
                }

                return $item->id;
            };

            $nationalityId = $resolve($d['nationality'] ?? '', $nationalityMap, 'Nationality');
            $religionId = $resolve($d['religion'] ?? '', $religionMap, 'Religion');
            $civilStatusId = $resolve($d['civil_status'] ?? '', $civilStatusMap, 'Civil Status');
            $countryId = $resolve($d['country'] ?? '', $countryMap, 'Country');
            $positionId = $resolve($d['position'] ?? '', $positionMap, 'Position');
            $employerId = $resolve($d['employer'] ?? '', $employerMap, 'Employer');
            $agentId = $resolve($d['agent'] ?? '', $agentMap, 'Agent');

            // ---- Branch (respects branch-locked accounts) ----
            $branchId = null;
            $branchRaw = trim((string) ($d['branch'] ?? ''));
            if ($branchRaw !== '') {
                $branch = $branchMap[$norm($branchRaw)] ?? null;
                if (! $branch) {
                    $available = collect(array_keys($branchMap))->take(6)->map(fn ($k) => ucwords($k))->implode(', ');
                    $rowErrors[] = "Branch '{$branchRaw}' not found.".($available !== '' ? " Available: {$available}" : '');
                } else {
                    $branchId = $branch->id;
                    if ($branchLocked && (int) $branchId !== (int) $user->branch_id) {
                        $rowErrors[] = 'You can only assign applicants to your own branch.';
                    }
                }
            } elseif ($branchLocked) {
                $branchId = $user->branch_id; // default to own branch
            }

            // Mirror the create-form rule: Source = Branch requires the agent
            // to belong to the chosen branch.
            if ($norm($source) === 'branch' && $agentId && $branchId) {
                $agent = $agentMap[$norm(trim((string) ($d['agent'] ?? '')))] ?? null;
                if ($agent && $agent->branch_id && (int) $agent->branch_id !== (int) $branchId) {
                    $rowErrors[] = 'The selected agent does not belong to the selected branch.';
                }
            }

            // ---- Status (label OR code; case/space-insensitive) ----
            $statusCode = 0; // default Pending, mirrors single-create
            $statusRaw = trim((string) ($d['status'] ?? ''));
            if ($statusRaw !== '') {
                $key = $norm($statusRaw);
                if (isset($statusByCode[$key])) {
                    $statusCode = $statusByCode[$key];
                } elseif (isset($statusByLabel[$key])) {
                    $statusCode = $statusByLabel[$key];
                } else {
                    $rowErrors[] = "Status '{$statusRaw}' not found. Use a status label (e.g. Pending, For Interview) or its code number.";
                }
            }

            // ---- Duplicate detection (Cyd 2026-09-26) ----
            // Flag rows that look like an applicant we already have. Only run
            // once the row is otherwise valid so the message is useful.
            if (empty($rowErrors)) {
                $dupes = ApplicantDuplicateChecker::find($agencyId, [
                    'first_name' => $first,
                    'last_name' => $last,
                    'email' => $email,
                    'contact' => $contact,
                    'birthdate' => $birthdate,
                ]);
                if ($dupes->isNotEmpty()) {
                    $names = $dupes->take(3)->map(fn ($m) => trim($m['applicant']->first_name.' '.$m['applicant']->last_name).' (#'.$m['applicant']->applicant_no.')')->implode('; ');
                    $rowErrors[] = "Possible duplicate of existing applicant: {$names}. Remove this row or confirm it is a different person.";
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $validatedRows[] = [
                'first_name' => $first,
                'middle_name' => $middle !== '' ? $middle : null,
                'last_name' => $last,
                'suffix' => $suffix !== '' ? $suffix : null,
                'gender' => $gender !== '' ? $gender : null,
                'birthdate' => $birthdate,
                'contact' => $contact !== '' ? $contact : null,
                'email' => $email !== '' ? $email : null,
                'address' => $address !== '' ? $address : null,
                'remarks' => $remarks !== '' ? $remarks : null,
                'source' => $source !== '' ? $source : null,
                'nationality_id' => $nationalityId,
                'religion_id' => $religionId,
                'civil_status_id' => $civilStatusId,
                'country_id' => $countryId,
                'position_id' => $positionId,
                'employer_id' => $employerId,
                'agent_id' => $agentId,
                'branch_id' => $branchId,
                'status_code' => $statusCode,
                'has_passport' => $passportNo !== '' ? 'with' : null,
                // Non-column payloads handled after Applicant::create().
                '_date_applied' => $dateApplied,
                '_passport' => ($passportNo !== '' || $passportIssue || $passportExpiry || $passportPlace !== '')
                    ? [
                        'passport_no' => $passportNo !== '' ? $passportNo : null,
                        'issue_date' => $passportIssue,
                        'expiry_date' => $passportExpiry,
                        'place_of_issue' => $passportPlace !== '' ? $passportPlace : null,
                    ]
                    : null,
            ];
        }

        if (count($validatedRows) > $maxRows) {
            return back()->withErrors(['csv_file' => "Too many rows: the file has more than {$maxRows} applicant rows. Split it into smaller files."]);
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        // ---- All rows valid: insert in one transaction ----
        $count = count($validatedRows);
        DB::transaction(function () use ($validatedRows, $agencyId) {
            foreach ($validatedRows as $data) {
                $passport = $data['_passport'] ?? null;
                $dateApplied = $data['_date_applied'] ?? null;
                unset($data['_passport'], $data['_date_applied']);

                $data['agency_id'] = $agencyId;
                $data['encoder'] = auth()->user()->name; // name only, no date suffix (matches store())
                $data['created_by'] = auth()->id();

                $applicant = Applicant::create($data);

                // Date Applied is the applicant's created_at (Browse Applicants column).
                if ($dateApplied) {
                    $applicant->created_at = Carbon::parse($dateApplied)->startOfDay();
                    $applicant->saveQuietly();
                }

                // Passport sub-record (mirrors syncPassport() on the create form).
                if ($passport) {
                    $applicant->passport()->create(array_merge($passport, [
                        'agency_id' => $agencyId,
                    ]));
                }
            }
        });

        SensitiveActionLogger::log(
            'bulk_applicant_import',
            subject: null,
            description: auth()->user()->name." bulk-imported {$count} applicants via CSV.",
            metadata: ['count' => $count],
            agencyId: $agencyId,
        );

        return redirect()->route('applicants.index')
            ->with('success', "Bulk upload complete: {$count} applicant(s) imported from CSV.");
    }

    /**
     * Read a CSV file into rows keyed by canonical field name.
     *
     * Tolerant parser aimed at real-world Excel round-trips:
     *  - strips UTF-8 BOM, decodes UTF-16 (LE/BE), falls back to Windows-1252
     *  - auto-detects delimiter (comma / semicolon / tab)
     *  - does NOT assume the header is the very first line: leading blank lines
     *    or a stray title row are skipped until a row containing the required
     *    "First Name" / "Last Name" headers is found
     *  - unknown extra columns are ignored
     *
     * @return array|string array of ['line' => int, 'data' => [field => value]] or an error message
     */
    private function parseBulkCsv(string $path)
    {
        $raw = @file_get_contents($path);
        if ($raw === false) {
            return 'Could not read the uploaded file.';
        }

        // ---- Encoding normalization (Excel writes all of these) ----
        if (str_starts_with($raw, "\xFF\xFE")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE');
        } elseif (str_starts_with($raw, "\xFE\xFF")) {
            $raw = mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE');
        } elseif (str_starts_with($raw, "\xEF\xBB\xBF")) {
            $raw = substr($raw, 3); // UTF-8 BOM
        }
        if ($raw !== '' && ! mb_check_encoding($raw, 'UTF-8')) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'Windows-1252');
        }
        if (trim($raw) === '') {
            return 'The file is empty.';
        }

        // ---- Try each plausible delimiter; the first one that yields a valid
        // header row wins. (Fully re-parsing 2-3 times is fine: files are
        // capped at 2,000 data rows and 5 MB.) ----
        $lastError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $this->parseBulkCsvWithDelimiter($raw, $delimiter);
            if (is_array($result)) {
                return $result;
            }
            $lastError = $result;
        }

        return $lastError;
    }

    /**
     * @return array|string parsed rows, or an error message for this delimiter
     */
    private function parseBulkCsvWithDelimiter(string $raw, string $delimiter)
    {
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $raw);
        rewind($handle);

        $parsedRows = [];
        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            $parsedRows[] = $cells;
        }
        fclose($handle);

        if (empty($parsedRows)) {
            return 'The file has no readable rows.';
        }

        $aliases = $this->bulkHeaderAliases();

        // ---- Find the header row: skip blank lines and stray title lines ----
        $headerIndex = null;
        $fieldByIndex = [];
        $scanLimit = min(count($parsedRows), 30);
        for ($i = 0; $i < $scanLimit; $i++) {
            $cells = $parsedRows[$i];
            if (empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                continue; // blank line
            }
            $candidate = [];
            foreach ($cells as $idx => $header) {
                $field = $aliases[$this->normKey($header)] ?? null;
                if ($field && ! in_array($field, $candidate, true)) {
                    $candidate[$idx] = $field;
                }
            }
            $fields = array_values($candidate);
            // A row only counts as the header when it names BOTH required columns.
            if (in_array('first_name', $fields, true) && in_array('last_name', $fields, true)) {
                $headerIndex = $i;
                $fieldByIndex = $candidate;
                break;
            }
        }

        if ($headerIndex === null) {
            $firstNonBlank = null;
            foreach (array_slice($parsedRows, 0, 5) as $cells) {
                if (! empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''))) {
                    $firstNonBlank = implode(' | ', array_map(fn ($c) => trim((string) $c), $cells));
                    break;
                }
            }
            $preview = mb_substr($firstNonBlank ?? '(empty file)', 0, 160);

            return 'No header row found. The file must have a header row containing "First Name" and "Last Name" columns (use the downloaded template and keep its first row unchanged). What the parser saw first: "'.$preview.'". If you added a title or blank line above the header, remove it, or just re-download the template and fill it in.';
        }

        // ---- Data rows = everything after the header row ----
        $rows = [];
        $line = $headerIndex + 1; // physical CSV line of the header
        $dataLines = 0;
        for ($i = $headerIndex + 1; $i < count($parsedRows); $i++) {
            $line++;
            $cells = $parsedRows[$i];
            $data = [];
            foreach ($fieldByIndex as $idx => $field) {
                $data[$field] = trim((string) ($cells[$idx] ?? ''));
            }
            if (implode('', array_values($data)) === '') {
                continue; // skip blank lines
            }
            $dataLines++;
            if ($dataLines > 2000) {
                return 'Too many rows: the file exceeds 2,000 applicant rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
    }

    /**
     * Map normalized header labels to canonical applicant fields.
     *
     * @return array<string, string>
     */
    private function bulkHeaderAliases(): array
    {
        return [
            'firstname' => 'first_name',
            'first' => 'first_name',
            'middlename' => 'middle_name',
            'middle' => 'middle_name',
            'lastname' => 'last_name',
            'last' => 'last_name',
            'suffix' => 'suffix',
            'gender' => 'gender',
            'sex' => 'gender',
            'birthdate' => 'birthdate',
            'dateofbirth' => 'birthdate',
            'dob' => 'birthdate',
            'birthday' => 'birthdate',
            'contact' => 'contact',
            'contactno' => 'contact',
            'contactnumber' => 'contact',
            'phone' => 'contact',
            'phonenumber' => 'contact',
            'mobile' => 'contact',
            'cellphone' => 'contact',
            'email' => 'email',
            'emailaddress' => 'email',
            'address' => 'address',
            'homeaddress' => 'address',
            'nationality' => 'nationality',
            'religion' => 'religion',
            'civilstatus' => 'civil_status',
            'maritalstatus' => 'civil_status',
            'country' => 'country',
            'position' => 'position',
            'positionapplied' => 'position',
            'preferredposition' => 'position',
            'employer' => 'employer',
            'company' => 'employer',
            'agent' => 'agent',
            'branch' => 'branch',
            'branchname' => 'branch',
            'source' => 'source',
            'status' => 'status',
            'statuscode' => 'status',
            'remarks' => 'remarks',
            'notes' => 'remarks',
            // Toybits 2026-10-06 — passport + date-applied columns on the template.
            'dateapplied' => 'date_applied',
            'dateofapplication' => 'date_applied',
            'applied' => 'date_applied',
            'passport' => 'passport_no',
            'passportno' => 'passport_no',
            'passportnumber' => 'passport_no',
            'dateissued' => 'passport_issue_date',
            'issueddate' => 'passport_issue_date',
            'issuedate' => 'passport_issue_date',
            'passportissuedate' => 'passport_issue_date',
            'placeissued' => 'passport_place_of_issue',
            'placeofissue' => 'passport_place_of_issue',
            'passportplaceofissue' => 'passport_place_of_issue',
            'expiration' => 'passport_expiry_date',
            'expirationdate' => 'passport_expiry_date',
            'expiry' => 'passport_expiry_date',
            'expirydate' => 'passport_expiry_date',
            'passportexpiry' => 'passport_expiry_date',
            'passportexpirydate' => 'passport_expiry_date',
            'validuntil' => 'passport_expiry_date',
        ];
    }

    /**
     * Normalize a header label: lowercase, strip non-alphanumerics.
     */
    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
    }

    public function show(Applicant $applicant)
    {
        // (Branch feature) A branch account may only view applicants of its own branch.
        $this->authorizeBranchAccess($applicant);

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

        // Status-tab dropdown: the FULL status list, matching Add/Edit.
        // (Toybits report 2026-08-10: filtering by the agency-configured
        // status_codes hid statuses like Repatriated — the dropdown must be
        // identical to the Add/Edit form, same as positions/statuses there.)
        $allStatuses = StatusCode::orderBy('sort_order')->get();
        $statusCodes = $allStatuses;

        // Status-tab FRA/Employer dropdown: the agency's FRA list (the employers
        // table — FRA portal users are employer-type), same as the edit page.
        // (Toybits report 2026-08-15: the old static No FRA / For FRA / FRA
        // Completed options were wrong — it must list the FRA like Edit.)
        $employers = Employer::where('agency_id', resolve_agency_id())
            ->orderBy('name')
            ->get(['id', 'name']);

        // Settings-sourced dropdowns for the Skills & Language tabs (PI items 4 & 5).
        $skills = Skill::orderBy('name')->get();
        $languages = Language::orderBy('name')->get();

        // Status history for the Status tab (PI item 8): past status_changed
        // activity with the encoder name + timestamp.
        $statusHistory = ActivityLog::with('user')
            ->where('subject_type', Applicant::class)
            ->where('subject_id', $applicant->id)
            ->where('action', 'status_changed')
            ->orderByDesc('id')
            ->get();

        // Status code map (code => model) for the colored Status History tabs.
        $statusCodeMap = $statusCodes->keyBy('code');

        return view('applicants.show', compact(
            'applicant', 'statusCodes', 'employers', 'skills', 'languages', 'statusHistory', 'statusCodeMap'
        ));
    }

    public function edit(Applicant $applicant)
    {
        // (Branch feature) A branch account may only edit applicants of its own branch.
        $this->authorizeBranchAccess($applicant);

        $defaults = app_applicant_form_defaults();
        $agencyId = resolve_agency_id();
        $statusCodes = StatusCode::orderBy('sort_order')->get();

        // Same configurable source list as the Add Applicant form — never a
        // hardcoded list. This keeps Add and Edit in sync so sources like
        // "Branch" (and any agency-enabled source) render and stay selected.
        $sources = array_values(array_intersect(app_source_options(), $defaults['sources'] ?? []));
        $branches = $this->assignableBranches();
        $agents = $this->assignableAgents();

        // (PI card) Same Settings-backed dropdowns as Add Applicant, so Edit is in sync.
        $nationalities = Nationality::orderBy('name')->get();
        $religions = Religion::orderBy('name')->get();
        $civilStatuses = CivilStatus::orderBy('name')->get();

        // (PI card) Skills & Languages restricted to the Settings-configured lists.
        $applicant->load(['skills', 'languages']);
        $skills = Skill::orderBy('name')->get();
        $languages = Language::orderBy('name')->get();

        // (Branch feature) Branch dropdown default: logged-in branch user's
        // branch; for agency admins fall back to the applicant's current branch.
        $defaultBranchId = $this->defaultBranchId() ?? $applicant->branch_id;

        return view('applicants.edit', compact(
            'applicant', 'statusCodes', 'sources', 'branches', 'agents',
            'nationalities', 'religions', 'civilStatuses', 'skills', 'languages', 'defaultBranchId'
        ));
    }

    public function update(Request $request, Applicant $applicant)
    {
        // (Branch feature) A branch account may only update its own branch's applicants.
        $this->authorizeBranchAccess($applicant);

        $this->validateCustomFields($request, 'Applicant');

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'suffix' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'contact' => 'nullable|string|max:50',
            'gender' => 'nullable|string|max:20',
            'has_passport' => 'nullable|string|in:with,without',
            'education_level' => 'nullable|string|in:high_school,vocational,bachelor,master',
            'passport_no' => 'nullable|string|max:50',
            'passport_issue_date' => 'nullable|date',
            'passport_expiry_date' => 'nullable|date|after:passport_issue_date',
            'passport_place_of_issue' => 'nullable|string|max:255',
            'civil_status_id' => ['nullable', 'integer', 'exists:civil_statuses,id'],
            'nationality_id' => ['nullable', 'integer', 'exists:nationalities,id'],
            'religion_id' => ['nullable', 'integer', 'exists:religions,id'],
            'mother_name' => 'nullable|string|max:255',
            'mother_occupation' => 'nullable|string|max:255',
            'father_name' => 'nullable|string|max:255',
            'father_occupation' => 'nullable|string|max:255',
            'skills' => 'nullable|array',
            'skills.*' => 'nullable|string|max:255|exists:skills,name',
            'languages' => 'nullable|array',
            'languages.*' => 'nullable|string|max:255|exists:languages,name',
            'birthdate' => 'nullable|date',
            'address' => 'nullable|string',
            'remarks' => 'nullable|string',
            'source' => 'nullable|string|max:255',
            'country_id' => 'nullable|integer|exists:countries,id',
            'position_id' => 'nullable|integer|exists:positions,id',
            'agent_id' => 'nullable|integer|exists:agents,id',
            'branch_id' => 'nullable|integer|exists:branches,id',
            'branch' => 'nullable|string|max:255',
            'encoder' => 'nullable|string|max:255',
            'contract' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png',
            'contract_received_date' => 'nullable|date',
            'status_code' => 'nullable|integer|exists:status_codes,code',
            'photo' => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
            'full_body_photo' => 'nullable|mimes:jpg,jpeg,png,JPG,JPEG,PNG',
            'employer_id' => 'nullable|integer|exists:employers,id',
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

        // (Branch feature) On update, enforce the same branch rules as create.
        $this->applyBranchDefaults($validated);

        $oldStatusCode = $applicant->status_code;

        $applicant->update($validated);

        $this->syncPassport($request, $applicant);

        // Status changes made via the Edit Applicant form must also appear in
        // the Status tab history (Cyd report 2026-08-09). Only record when the
        // status actually changed.
        if ((int) $applicant->status_code !== (int) $oldStatusCode) {
            SensitiveActionLogger::log(
                'status_changed',
                subject: $applicant,
                description: auth()->user()->name." changed applicant {$applicant->full_name} status from {$oldStatusCode} to {$applicant->status_code}.",
                metadata: $this->statusChangeMetadata($oldStatusCode, $applicant->status_code, $applicant),
            );
        }

        $applicant->syncCustomFields($request->all());
        $this->syncSkillsLanguages($applicant);

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant updated successfully.');
    }

    /**
     * (PI card) Sync the Skills & Languages selections from the Add/Edit form
     * with the Settings-configured lists. Clears existing rows, then re-creates
     * them from the submitted skill/language names.
     */
    private function syncSkillsLanguages(Applicant $applicant): void
    {
        $agencyId = $applicant->agency_id ?: $this->resolveAgencyId();

        $applicant->skills()->delete();
        $applicant->languages()->delete();

        foreach (request('skills', []) as $skillName) {
            if (is_string($skillName) && trim($skillName) !== '') {
                $applicant->skills()->create([
                    'agency_id' => $agencyId,
                    'skill_name' => trim($skillName),
                ]);
            }
        }

        foreach (request('languages', []) as $langName) {
            if (is_string($langName) && trim($langName) !== '') {
                $applicant->languages()->create([
                    'agency_id' => $agencyId,
                    'name' => trim($langName),
                ]);
            }
        }
    }

    /**
     * (Cyd 2026-08-31) Passport fields on the Add/Edit form are stored in the
     * applicant_passports sub-table. Creates the record when none exists,
     * updates it when it does, and removes it when the applicant is marked
     * without a passport.
     */
    private function syncPassport(Request $request, Applicant $applicant): void
    {
        $hasPassport = $request->input('has_passport');
        $passportNo  = trim((string) $request->input('passport_no', ''));

        // Marked without a passport (or cleared) → drop the passport record.
        if ($hasPassport === 'without' || $passportNo === '') {
            if ($applicant->passport) {
                $applicant->passport()->delete();
            }

            return;
        }

        $data = [
            'agency_id'       => $applicant->agency_id ?: $this->resolveAgencyId(),
            'passport_no'     => $passportNo,
            'issue_date'      => $request->input('passport_issue_date'),
            'expiry_date'     => $request->input('passport_expiry_date'),
            'place_of_issue'  => $request->input('passport_place_of_issue'),
        ];

        if ($applicant->passport) {
            $applicant->passport()->update($data);
        } else {
            $applicant->passport()->create($data);
        }
    }

    /**
     * Branch dropdown default: the logged-in branch user's branch_id, or null
     * for agency admins / non-branch users (they pick freely).
     */
    private function defaultBranchId(): ?int
    {
        $user = auth()->user();

        return ($user && (int) $user->branch_id > 0) ? (int) $user->branch_id : null;
    }

    /**
     * (Branch feature) Agents a branch user may assign an applicant to. Branch
     * accounts (non-admin) only see agents of their OWN branch (plus unassigned
     * main-office agents); admins see every active agent in the agency.
     */
    private function assignableAgents()
    {
        $agencyId = resolve_agency_id();
        $query = Agent::where('agency_id', $agencyId)->where('status', 'active');

        $user = auth()->user();
        if ($user && $user->isBranchLocked()) {
            $query->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }

        return $query->orderBy('name')->get();
    }

    /**
     * (Branch feature) Branches a user may actually assign an applicant to.
     * Branch accounts (non-admin) only see their OWN branch in the Add/Edit
     * dropdown (assigning elsewhere is forbidden); admins see all branches
     * even when their account carries a branch_id.
     */
    private function assignableBranches()
    {
        $agencyId = resolve_agency_id();
        $query = Branch::where('agency_id', $agencyId)->orderBy('name');

        $user = auth()->user();
        if ($user && $user->isBranchLocked()) {
            $query->where('id', $user->branch_id);
        }

        return $query->get();
    }

    /**
     * (Branch feature) Enforce branch ownership rules when persisting an
     * applicant. A branch account (non-admin) is locked to their own branch:
     * if omitted it defaults to their branch; if set to some other branch it
     * is rejected. Admins may assign to any branch, even when their account
     * carries a branch_id.
     */
    private function applyBranchDefaults(array &$validated): void
    {
        $user = auth()->user();
        if (! $user || ! $user->isBranchLocked()) {
            return;
        }

        $submitted = $validated['branch_id'] ?? null;

        if (blank($submitted)) {
            $validated['branch_id'] = $user->branch_id;

            return;
        }

        if ((int) $submitted !== (int) $user->branch_id) {
            abort(403, 'You can only assign applicants to your own branch.');
        }
    }

    /**
     * (Branch feature) Authorize that a branch account may view/edit an
     * applicant only when it belongs to their branch. Admins pass regardless
     * of their own branch_id.
     */
    private function authorizeBranchAccess(Applicant $applicant): void
    {
        $user = auth()->user();
        if (! $user || ! $user->isBranchLocked()) {
            return;
        }

        if ((int) $applicant->branch_id !== (int) $user->branch_id) {
            abort(403, 'This applicant belongs to another branch.');
        }
    }

    public function destroy(Applicant $applicant)
    {
        // (Branch feature) A branch account may only delete its own branch's applicants.
        $this->authorizeBranchAccess($applicant);

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
        // Same rule as index(): withdrawn & repat statuses are excluded from
        // the main applicants export — they have their own Withdrawn & Repat tab.
        $withdrawnStatuses = [35, 38, 50];

        $query = Applicant::with(['statusCode', 'country', 'position', 'agent', 'employer', 'branch'])
            ->whereNotIn('status_code', $withdrawnStatuses)
            ->orderBy('created_at', 'desc');

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
        SensitiveActionLogger::dataExport('applicant', auth()->user()->name.' exported applicant data.');

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

    /**
     * CSV export for the Withdrawn & Repat tab — same columns as export()
     * but restricted to Cancel (38), Backout (50), Repatriated (35).
     */
    public function withdrawnExport(Request $request)
    {
        abort_unless(auth()->user()->canViewBackoutRepat(), 403, 'This folder is restricted to Admin and Accounting.');

        $withdrawnStatuses = [35, 38, 50]; // Repatriated, Cancel, Backout

        $query = Applicant::with(['statusCode', 'country', 'position', 'agent', 'employer', 'branch'])
            ->whereIn('status_code', $withdrawnStatuses)
            ->orderBy('created_at', 'desc');

        // Apply the same filters as withdrawn()
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
        SensitiveActionLogger::dataExport('applicant', auth()->user()->name.' exported withdrawn & repat applicant data.');

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
            'Content-Disposition' => 'attachment; filename=withdrawn-repat-applicants.csv',
        ]);
    }

    public function updateStatus(Request $request, Applicant $applicant)
    {
        $validated = $request->validate([
            'status_code' => ['required', 'integer', function ($attribute, $value, $fail) {
                // Only validate existence when status_codes table has data
                if (StatusCode::count() > 0 && ! StatusCode::where('code', $value)->exists()) {
                    $fail('The selected status code is invalid.');
                }
            }],
            // PI: 6 Status tab fields
            'applicant_no' => ['nullable', 'string', 'max:255'],
            'employer_id' => ['nullable', 'integer', \Illuminate\Validation\Rule::exists('employers', 'id')->where('agency_id', resolve_agency_id())],
            'status_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $fromCode = $applicant->status_code;
        $toCode = (int) $validated['status_code'];

        // NOTE: pipeline transition rules deliberately do NOT block the Status tab.
        // The dropdown lists every Settings status and users must be able to move
        // an applicant to any of them (e.g. applicants on statuses with no defined
        // transitions, like 51 For Passporting, could never save otherwise).
        // StatusTransitionService remains available for other flows that opt in.

        $applicant->update([
            'status_code' => $toCode,
            'applicant_no' => $validated['applicant_no'] ?? null,
            'employer_id' => $validated['employer_id'] ?? null,
            'status_date' => $validated['status_date'] ?? null,
            'remarks' => isset($validated['remarks']) && $validated['remarks'] !== '' ? $validated['remarks'] : null,
        ]);

        SensitiveActionLogger::log(
            'status_changed',
            subject: $applicant,
            description: auth()->user()->name." changed applicant {$applicant->full_name} status from {$fromCode} to {$toCode}.",
            metadata: $this->statusChangeMetadata($fromCode, $toCode, $applicant),
        );

        return redirect()->back()
            ->with('success', 'Applicant status updated successfully.');
    }

    /**
     * Snapshot the context shown in the Status History table at the moment of
     * the change (sub status, agency/employer, country, remarks, status date).
     * Older entries without snapshots fall back to the applicant's current
     * values in the view.
     */
    private function statusChangeMetadata(int $oldStatus, int $newStatus, Applicant $applicant): array
    {
        return [
            'old_status' => $oldStatus,
            'new_status' => $newStatus,
            'sub_status' => $applicant->fra,
            'agency' => $applicant->agency?->name,
            'employer' => $applicant->employer?->name,
            'country' => $applicant->country?->name,
            'remarks' => $applicant->remarks,
            'status_date' => $applicant->status_date?->toDateString(),
        ];
    }

    /**
     * The FRA values allowed for this agency's Status tab (PI: 8 item 3).
     * Falls back to the full canonical list when the agency hasn't configured
     * any fra_options.
     */
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
