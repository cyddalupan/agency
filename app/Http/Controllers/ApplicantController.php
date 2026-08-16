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
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApplicantController extends Controller
{
    public function index(Request $request)
    {
        // Withdrawn & Repat statuses (35 Repatriated, 38 Cancel, 50 Backout)
        // live ONLY on the Withdrawn & Repat tab — never the main applicants
        // page. (Toybits report 2026-08-10.)
        $withdrawnStatuses = [35, 38, 50];

        $query = Applicant::with(['statusCode', 'position', 'agent', 'branch', 'contractRecords'])
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

        $applicants = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('applicants.index', compact('applicants', 'statusCodes', 'statusCounts', 'employers', 'countries'));
    }

    /**
     * Withdrawn & Repat tab — applicants whose status is Cancel (38),
     * Backout (50), or Repatriated (35). Same list/filters as index()
     * but restricted to those three statuses.
     */
    public function withdrawn(Request $request)
    {
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
        $agents = Agent::where('agency_id', $agencyId)->where('status', 'active')->orderBy('name')->get();

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
        $validated['encoder'] = $validated['encoder']
            ?? (auth()->user()->name.' - '.now()->format('M d, Y h:i A'));
        $validated['created_by'] = auth()->id();

        $applicant = Applicant::create($validated);

        $applicant->syncCustomFields($request->all());
        $this->syncSkillsLanguages($applicant);

        return redirect()->route('applicants.index')
            ->with('success', 'Applicant created successfully.');
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
        $agents = Agent::where('agency_id', $agencyId)->where('status', 'active')->orderBy('name')->get();

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
     * Branch dropdown default: the logged-in branch user's branch_id, or null
     * for agency admins / non-branch users (they pick freely).
     */
    private function defaultBranchId(): ?int
    {
        $user = auth()->user();

        return ($user && (int) $user->branch_id > 0) ? (int) $user->branch_id : null;
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
