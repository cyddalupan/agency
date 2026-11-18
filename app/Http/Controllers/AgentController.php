<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use App\Services\SensitiveActionLogger;
use App\Support\AgentDuplicateChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        // Branch is a table column (client request 2026-11-05), so eager
        // load it to avoid a query per row.
        $query = Agent::with(['agency', 'branch', 'creator']);

        // Super admin sees all agents; agency users see only theirs
        if (auth()->user()->user_type !== 'super_admin') {
            $query->where('agency_id', $this->resolveAgencyId());
        }

        // Filtering
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Card 6afae4a7 (Cyd 2026-11-16): no pagination. The largest tenant has
        // 427 agents, which renders in one pass; the pager only hid rows from
        // anyone who did not notice page 2. Reports: agents already did this.
        $agents = $query->orderBy('name')->get();

        return view('agents.index', compact('agents'));
    }

    public function create()
    {
        $branches = Branch::where('agency_id', auth()->user()->agency_id)->orderBy('name')->get();

        return view('agents.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255|unique:agents,email',
            'contact'         => 'nullable|string|max:50',
            'branch_id'       => [
                'nullable',
                Rule::exists('branches', 'id')->where(function ($query) {
                    $query->where('agency_id', auth()->user()->agency_id);
                }),
            ],
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

        $validated['agency_id'] = $this->resolveAgencyId();
        // Creator (Cyd / accounting) for the "Created by" column (Toybits 2026-09-23).
        $validated['created_by'] = auth()->id();

        // Super admin can create agents without an agency (they can assign later)
        if (!$validated['agency_id'] && auth()->user()->user_type !== 'super_admin') {
            return back()->withErrors(['agency' => 'No agency context.'])->withInput();
        }

        // (Branch feature) Branch accounts may only create agents in their own
        // branch: omitted defaults to their branch, a different branch is rejected.
        $this->applyBranchDefaults($validated);

        // Duplicate guard (Cyd 2026-11-08, LANDAS card 6af02dd56cff0b42b7c91ee3):
        // if this agent looks like one we already have, bounce back with the
        // matches and let the user confirm. The form resubmits with
        // confirm_duplicate=1 to proceed.
        if (! $request->boolean('confirm_duplicate') && $validated['agency_id']) {
            $duplicates = AgentDuplicateChecker::find($validated['agency_id'], [
                'name' => $validated['name'] ?? null,
                'email' => $validated['email'] ?? null,
                'contact' => $validated['contact'] ?? null,
            ]);

            if ($duplicates->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('duplicate_agents', $this->agentDuplicatePayload($duplicates))
                    ->withErrors([
                        'duplicate' => 'Possible duplicate agent found. Review the existing record(s), then click "Create anyway" if this is a different person.',
                    ]);
            }
        }

        Agent::create($validated);

        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully.');
    }

    /** Shape duplicate agent matches for the create view. */
    private function agentDuplicatePayload($duplicates): array
    {
        return $duplicates->map(function ($m) {
            $a = $m['agent'];

            // What the client asked to see (LANDAS card): Name, Branch, Email,
            // Contact, Created By.
            return [
                'id' => $a->id,
                'name' => $a->name,
                'branch' => optional($a->branch)->name ?: '—',
                'email' => $a->email ?: '—',
                'contact' => $a->contact ?: '—',
                'created_by' => optional($a->creator)->name ?: '—',
                'url' => route('agents.edit', $a),
                'reasons' => $m['reasons'],
            ];
        })->values()->all();
    }

    public function edit(Agent $agent)
    {
        $branches = Branch::where('agency_id', $agent->agency_id)->orderBy('name')->get();

        return view('agents.edit', compact('agent', 'branches'));
    }

    public function update(Request $request, Agent $agent)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => ['nullable', 'email', 'max:255', Rule::unique('agents', 'email')->ignore($agent->id)],
            'contact'         => 'nullable|string|max:50',
            'branch_id'       => [
                'nullable',
                Rule::exists('branches', 'id')->where(function ($query) use ($agent) {
                    $query->where('agency_id', $agent->agency_id);
                }),
            ],
            'commission_rate' => 'nullable|numeric|min:0|max:100',
            'status'          => 'nullable|string|in:active,inactive',
        ]);

        // (Branch feature) Branch accounts may only keep agents in their own branch.
        $this->applyBranchDefaults($validated);

        $agent->update($validated);

        return redirect()->route('agents.index')
            ->with('success', 'Agent updated successfully.');
    }

    /**
     * Per-row delete (CYD 2026-11-04 card: "Agent module delete — admin only,
     * currently only bulk delete is available").
     *
     * Administrator-only and tenant-scoped. The agent is hard-deleted only when
     * nothing points at it; otherwise the delete is refused so applicants,
     * commissions, deductions, expense lines, receivables and starting
     * balances keep their history (the agent can be set to Inactive instead).
     */
    public function destroy(Agent $agent)
    {
        $user = auth()->user();

        abort_unless(in_array((string) $user->user_type, ['admin', 'super_admin'], true), 403,
            'Only an administrator can delete an agent.');

        // Tenant isolation: an admin may only delete agents of their own agency.
        $agencyId = $this->resolveAgencyId();
        abort_unless($agencyId && (int) $agent->agency_id === (int) $agencyId, 404);

        // Do not break history: refuse while any record still references the agent.
        $references = [
            'applicants'            => 'applicant(s)',
            'agent_deductions'      => 'deduction(s)',
            'commissions'           => 'commission(s)',
            'expense_request_items' => 'expense line(s)',
            'receivables'           => 'receivable(s)',
            'starting_balances'     => 'starting balance(s)',
        ];
        $blockers = [];
        foreach ($references as $table => $label) {
            $count = DB::table($table)->where('agent_id', $agent->id)->count();
            if ($count > 0) {
                $blockers[] = $count . ' ' . $label;
            }
        }

        if ($blockers) {
            return back()->with('error',
                'Cannot delete ' . $agent->name . ': still referenced by ' . implode(', ', $blockers)
                . '. Set the agent to Inactive instead.');
        }

        $agentId = $agent->id;
        $agentName = $agent->name;

        DB::transaction(fn () => $agent->delete());

        SensitiveActionLogger::log(
            'agents.delete',
            null,
            "Deleted agent {$agentName}.",
            ['agent_id' => $agentId, 'agent_name' => $agentName],
            $agencyId,
        );

        return redirect()->route('agents.index')
            ->with('success', 'Agent deleted successfully.');
    }

    /**
     * Delete every registered agent of the Gulf agency (CYD 2026-11-04).
     *
     * Gulf-only, admin-only, and guarded by an explicit confirmation phrase.
     * Agents are hard-deleted only when nothing references them; otherwise the
     * action is refused so historical deductions/commissions stay intact.
     */
    public function deleteAll(Request $request)
    {
        $user = auth()->user();
        $agency = $this->gulfAgencyFor($user);

        abort_unless($agency, 403, 'This action is only available for the Gulf agency.');
        abort_unless(in_array((string) $user->user_type, ['admin', 'super_admin'], true), 403,
            'Only an administrator can delete all agents.');

        $request->validate(['confirmation' => ['required', 'string']]);

        if (trim((string) $request->input('confirmation')) !== 'DELETE ALL') {
            return back()->withErrors([
                'confirmation' => 'Type DELETE ALL (in capitals) to confirm the deletion.',
            ]);
        }

        $agentIds = Agent::where('agency_id', $agency->id)->pluck('id');

        if ($agentIds->isEmpty()) {
            return redirect()->route('agents.index')->with('success', 'No registered agents to delete.');
        }

        // Do not break history: refuse when any record still points at an agent.
        $references = [
            'applicants'            => 'applicant(s)',
            'agent_deductions'      => 'deduction(s)',
            'commissions'           => 'commission(s)',
            'expense_request_items' => 'expense line(s)',
            'receivables'           => 'receivable(s)',
            'starting_balances'     => 'starting balance(s)',
        ];
        $blockers = [];
        foreach ($references as $table => $label) {
            $count = DB::table($table)->whereIn('agent_id', $agentIds)->count();
            if ($count > 0) {
                $blockers[] = $count . ' ' . $label;
            }
        }

        if ($blockers) {
            return back()->with('error',
                'Cannot delete all agents: they are still referenced by ' . implode(', ', $blockers) . '.');
        }

        $deleted = DB::transaction(fn () => Agent::whereIn('id', $agentIds)->delete());

        SensitiveActionLogger::log(
            'agents.delete_all',
            null,
            "Deleted all {$deleted} registered agent(s) of the Gulf agency.",
            ['deleted' => $deleted, 'agency_subdomain' => $agency->subdomain],
            $agency->id,
        );

        return redirect()->route('agents.index')
            ->with('success', "Deleted {$deleted} registered agent(s).");
    }

    /**
     * Resolve the Gulf agency for the delete-all action. Agency-bound users get
     * their own agency; a super admin gets the tenant they are browsing.
     */
    private function gulfAgencyFor(?User $user): ?Agency
    {
        $agency = $user?->agency;

        if (! $agency && $user?->user_type === 'super_admin') {
            $agency = request()->attributes->get('tenant_agency');
        }

        if (! $agency || strtolower((string) $agency->subdomain) !== 'gulf') {
            return null;
        }

        return $agency;
    }

    // -------------------------------------------------------------------
    // Bulk CSV upload (mirrors the Employer/Applicant bulk flows)
    // -------------------------------------------------------------------

    public function bulkUpload()
    {
        // Same branch scope as create(): the authenticated user's own agency.
        $branches = Branch::where('agency_id', auth()->user()->agency_id)->orderBy('name')->get();

        return view('agents.bulk', compact('branches'));
    }

    public function bulkTemplate()
    {
        $headers = [
            'Name', 'Email', 'Contact', 'Branch', 'Commission Rate', 'Status',
        ];

        $sampleRows = [
            [
                'Juan Dela Cruz', 'juan.delacruz@example.com', '09171234567',
                '', '10', 'Active',
            ],
            [
                'Maria Santos', 'maria.santos@example.com', '09179876543',
                '', '', 'Inactive',
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
            'Content-Disposition' => 'attachment; filename=agents_bulk_template.csv',
        ]);
    }

    public function bulkImport(Request $request)
    {
        $agencyId = $this->resolveAgencyId();
        $user = auth()->user();

        // Mirror store(): agency context required unless the actor is a
        // super admin (who may create agents without an agency).
        if (! $agencyId && $user?->user_type !== 'super_admin') {
            return back()->withErrors(['csv_file' => 'No agency context. Please log in with an agency account to import agents.']);
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

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        // Branch lookup by name, scoped to the user's agency (same as create()).
        $branchMap = [];
        foreach (Branch::where('agency_id', $user->agency_id)->get() as $branch) {
            $key = $norm($branch->name);
            if ($key !== '' && ! array_key_exists($key, $branchMap)) {
                $branchMap[$key] = $branch->id;
            }
        }

        // Preload every existing agent email (lowercased) — agents.email is
        // nullable + UNIQUE now, so a non-empty duplicate is rejected, not
        // silently merged. (Multiple NULLs are allowed by MySQL.)
        $existingEmails = Agent::query()->pluck('email')
            ->map(fn ($e) => mb_strtolower(trim((string) $e)))
            ->flip();

        $errors = [];
        $validatedRows = [];
        $seenEmails = [];
        // In-file duplicates are tracked by name+contact (and name+email when
        // supplied) so the same person listed twice in one CSV is flagged even
        // when the email column is blank. (Cyd 2026-11-08)
        $seenPeople = [];
        $lockedBranchId = $user->isBranchLocked() ? (int) $user->branch_id : null;

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            $name = trim((string) ($d['name'] ?? ''));
            $emailRaw = trim((string) ($d['email'] ?? ''));
            $emailKey = mb_strtolower($emailRaw);
            $contact = trim((string) ($d['contact'] ?? ''));
            $branchRaw = trim((string) ($d['branch'] ?? ''));
            $commissionRaw = trim((string) ($d['commission_rate'] ?? ''));
            $status = trim((string) ($d['status'] ?? ''));

            // ---- Plain fields ----
            if ($name === '') {
                $rowErrors[] = 'Name is required.';
            } elseif (mb_strlen($name) > 255) {
                $rowErrors[] = 'Name is too long (max 255).';
            }

            // Email: optional (Toybits 2026-10-06 — mirrors the create form).
            // When supplied it must be valid and unique (file + existing agents).
            if ($emailRaw === '') {
                // No email supplied — allowed.
            } elseif (! filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
                $rowErrors[] = "Email '{$emailRaw}' is not a valid email address.";
            } elseif (mb_strlen($emailRaw) > 255) {
                $rowErrors[] = 'Email is too long (max 255).';
            } elseif (in_array($emailKey, $seenEmails, true)) {
                $rowErrors[] = "Email '{$emailRaw}' appears more than once in this file.";
            } elseif (isset($existingEmails[$emailKey])) {
                $rowErrors[] = "Email '{$emailRaw}' is already in use by an existing agent.";
            }

            if (mb_strlen($contact) > 50) {
                $rowErrors[] = 'Contact is too long (max 50).';
            }

            // ---- Branch (by name; optional) ----
            $branchId = null;
            if ($branchRaw !== '') {
                $key = $norm($branchRaw);
                if (isset($branchMap[$key])) {
                    $branchId = $branchMap[$key];
                } else {
                    $available = collect(array_keys($branchMap))->take(6)->map(fn ($k) => ucwords($k))->implode(', ');
                    $rowErrors[] = "Branch '{$branchRaw}' not found.".($available !== '' ? " Available: {$available}" : '');
                }
            }
            // Branch accounts may only create agents under their own branch
            // (blank defaults to their branch, mirroring applyBranchDefaults).
            if ($lockedBranchId) {
                if (! $branchId) {
                    $branchId = $lockedBranchId;
                } elseif ((int) $branchId !== $lockedBranchId) {
                    $rowErrors[] = 'You can only assign agents to your own branch.';
                }
            }

            // ---- Commission rate (optional, 0-100) ----
            $commissionRate = null;
            if ($commissionRaw !== '') {
                if (! is_numeric($commissionRaw)) {
                    $rowErrors[] = "Commission Rate '{$commissionRaw}' must be a number.";
                } else {
                    $rate = (float) $commissionRaw;
                    if ($rate < 0 || $rate > 100) {
                        $rowErrors[] = 'Commission Rate must be between 0 and 100.';
                    } else {
                        $commissionRate = $commissionRaw; // keep original string; DB/casts normalize
                    }
                }
            }

            // ---- Status (Active / Inactive; case/space-insensitive; default Active) ----
            $statusValue = 'active';
            if ($status !== '') {
                // Strip ALL whitespace so 'In Active' == 'Inactive' (case/space-insensitive).
                $statusKey = mb_strtolower(preg_replace('/\s+/', '', $status));
                if ($statusKey === 'active' || $statusKey === '1') {
                    $statusValue = 'active';
                } elseif ($statusKey === 'inactive' || $statusKey === '0') {
                    $statusValue = 'inactive';
                } else {
                    $rowErrors[] = "Status '{$status}' not found. Use 'Active' or 'Inactive' (or leave blank for Active).";
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            // ---- Duplicate detection (Cyd 2026-11-08, LANDAS card) ----
            // Same rule as the applicant bulk upload: repeats inside this file
            // first, then almost-the-same agents already on record. Only run
            // once the row is otherwise valid so messages stay useful.
            $personKey = AgentDuplicateChecker::norm($name).'|'.(AgentDuplicateChecker::digits($contact) ?: '');
            $emailKeyForRow = $emailKey !== '' ? AgentDuplicateChecker::norm($emailRaw) : '';
            $fileDupLine = $seenPeople[$personKey] ?? ($emailKeyForRow !== '' ? ($seenPeople['email:'.$emailKeyForRow] ?? null) : null);

            if ($fileDupLine !== null) {
                $rowErrors[] = "Duplicate of line {$fileDupLine} in this same file — this agent is listed more than once. Delete the extra row(s) and upload again.";
            } else {
                $seenPeople[$personKey] = $line;
                if ($emailKeyForRow !== '') {
                    $seenPeople['email:'.$emailKeyForRow] = $line;
                }

                $dupes = AgentDuplicateChecker::find($agencyId, [
                    'name' => $name,
                    'email' => $emailRaw,
                    'contact' => $contact,
                ]);
                if ($dupes->isNotEmpty()) {
                    $names = $dupes->take(3)->map(fn ($m) => $m['agent']->name.($m['agent']->branch ? ' ('.optional($m['agent']->branch)->name.')' : ''))->implode('; ');
                    $reasons = $dupes->take(3)->pluck('reasons')->flatten()->unique()->take(3)->implode('; ');
                    $rowErrors[] = "Possible duplicate of existing agent: {$names}".($reasons !== '' ? " ({$reasons})" : '').'. Remove this row, or confirm it is a different person (create it manually instead of uploading).';
                }
            }

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $validatedRows[] = [
                'name'            => $name,
                'email'           => $emailRaw !== '' ? $emailRaw : null,
                'contact'         => $contact !== '' ? $contact : null,
                'branch_id'       => $branchId,
                'commission_rate' => $commissionRate,
                'status'          => $statusValue,
            ];
            if ($emailKey !== '') {
                $seenEmails[] = $emailKey;
            }
        }

        if (count($validatedRows) > 2000) {
            return back()->withErrors(['csv_file' => 'Too many rows: the file has more than 2,000 agent rows. Split it into smaller files.']);
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        // ---- All rows valid: insert in one transaction (all-or-nothing) ----
        $count = count($validatedRows);
        DB::transaction(function () use ($validatedRows, $agencyId) {
            foreach ($validatedRows as $data) {
                $data['agency_id'] = $agencyId;
                $data['created_by'] = auth()->id();

                Agent::create($data);
            }
        });

        SensitiveActionLogger::log(
            'bulk_agent_import',
            subject: null,
            description: $user?->name." bulk-imported {$count} agent(s) via CSV.",
            metadata: ['count' => $count],
            agencyId: $agencyId,
        );

        return redirect()->route('agents.index')
            ->with('success', "Bulk upload complete: {$count} agent(s) imported from CSV.");
    }

    /**
     * (Branch feature) Branch accounts (non-admin with a branch) may only
     * assign/create agents under their OWN branch. Omitted branch_id defaults
     * to their branch; any other branch is rejected server-side.
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
            abort(403, 'You can only assign agents to your own branch.');
        }
    }

    /**
     * Read + normalize a CSV upload, returning either an array of rows
     * [['line' => int, 'data' => [field => value]], ...] or an error string.
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

        // Normalize line endings — Excel for Mac / some exports use a lone CR,
        // which fgetcsv would otherwise read as a single giant row (so the
        // header row is never seen). (Fixed 2026-10-07)
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        // ---- Try each plausible delimiter; the first one that yields a valid
        // header row wins. ----
        $lastError = null;
        $headerFoundError = null;
        foreach ([',', ';', "\t"] as $delimiter) {
            $result = $this->parseBulkCsvWithDelimiter($raw, $delimiter);
            if (is_array($result)) {
                return $result;
            }

            // A delimiter that *did* recognize the header row but then failed
            // for a concrete reason (no data rows / too many rows) is far more
            // useful than the generic "no header row" from a wrong delimiter —
            // don't let the wrong delimiter's message win. (Fixed 2026-10-07)
            if (! str_starts_with($result, 'No header row found')) {
                $headerFoundError ??= $result;
            }

            $lastError = $result;
        }

        return $headerFoundError ?? $lastError;
    }

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
            // A row counts as the header when it names the required column(s).
            // Email is optional (Toybits 2026-10-06), so only "Name" is required.
            if (in_array('name', $fields, true)) {
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

            return 'No header row found. The file must have a header row containing a "Name" column (an "Email" column is optional). Use the downloaded template and keep its first row unchanged. What the parser saw first: "'.$preview.'". If you added a title or blank line above the header, remove it, or just re-download the template and fill it in.';
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
                return 'Too many rows: the file exceeds 2,000 agent rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
    }

    /**
     * Map normalized header labels to canonical agent fields.
     *
     * @return array<string, string>
     */
    private function bulkHeaderAliases(): array
    {
        return [
            'name' => 'name',
            'agent' => 'name',
            'agentname' => 'name',
            'fullname' => 'name',
            'email' => 'email',
            'emailaddress' => 'email',
            'contact' => 'contact',
            'phone' => 'contact',
            'phonenumber' => 'contact',
            'telephone' => 'contact',
            'mobilenumber' => 'contact',
            'branch' => 'branch',
            'branchname' => 'branch',
            'commissionrate' => 'commission_rate',
            'commission' => 'commission_rate',
            'rate' => 'commission_rate',
            'status' => 'status',
        ];
    }

    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
    }
}
