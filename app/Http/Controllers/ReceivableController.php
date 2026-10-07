<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\ReceivableHistory;
use App\Models\User;
use App\Support\ModuleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    /**
     * Tab 1 — Receivable list.
     */
    public function index(): View
    {
        $agencyId = auth()->user()->agency_id;

        // (Toybits 2026-10-07) Encoder filter — optional ?encoder=<userId>
        // narrows the list to the rows that user encoded. Blank/absent = all.
        $activeEncoder = request()->query('encoder');

        // Base scope = agency + row-visibility + branch rules. Both the table
        // and the encoder dropdown derive from it, so the dropdown only ever
        // offers encoders the current user is allowed to see.
        $base = Receivable::query()
            ->where('agency_id', $agencyId)
            // (Cyd 2026-09-28 #4) Everyone except the privileged accounts
            // (Mae/Evelyn/Angel) and admins sees ONLY the receivables they
            // created. Status changes still show on their own rows.
            ->when(! auth()->user()->seesAllAccountingData(),
                fn ($q) => $q->where('user_id', auth()->id()))
            // (Branch feature) Branch-locked users only see their own branch's
            // receivables (plus main-office agents with no branch), mirroring create().
            ->when(auth()->user()->isBranchLocked(), function ($q) {
                $q->whereHas('agent', function ($agentQ) {
                    $agentQ->where('branch_id', auth()->user()->branch_id)
                        ->orWhereNull('branch_id');
                });
            });

        $encoderIds = (clone $base)->whereNotNull('user_id')->pluck('user_id')->unique()->values()->all();
        $encoders   = User::whereIn('id', $encoderIds)->orderBy('name')->get(['id', 'name']);

        $receivables = (clone $base)
            ->with(['agent', 'applicant', 'encoder'])
            ->when($activeEncoder, fn ($q) => $q->where('user_id', $activeEncoder))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('receivable.index', [
            'receivables'   => $receivables,
            'encoders'      => $encoders,
            'activeEncoder' => $activeEncoder,
            'totalAmount'   => round((float) $receivables->sum('amount'), 2),
            'pendingTotal'  => round((float) $receivables->where('status', Receivable::STATUS_PENDING)->sum('amount'), 2),
            'receivedTotal' => round((float) $receivables->where('status', Receivable::STATUS_RECEIVED)->sum('amount'), 2),
        ]);
    }

    /**
     * Create form — shows all agents (across branches, agency-scoped).
     */
    public function create(): View
    {
        $agencyId = auth()->user()->agency_id;

        $agents = Agent::where('agency_id', $agencyId)
            ->with('branch');

        // (Branch feature) Branch accounts may only file receivables for
        // agents in their OWN branch (plus main-office agents with no branch).
        $user = auth()->user();
        if ($user && $user->isBranchLocked()) {
            $agents->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }

        $agents = $agents->orderBy('name')->get();

        $applicants = Applicant::where('agency_id', $agencyId)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'agent_id', 'first_name', 'last_name']);

        return view('receivable.create', [
            'agents'            => $agents,
            'applicants'        => $applicants,
            'code'              => Receivable::nextCode($agencyId),
            'accounts'          => Receivable::ACCOUNTS,
            'debitAccounts'     => Receivable::DEBIT_ACCOUNTS,
            'types'             => Receivable::TYPES,
            'modes'             => Receivable::MODES,
        ]);
    }

    /**
     * Store a receivable ("Save Transaction").
     */
    public function store(Request $request): RedirectResponse
    {
        $agencyId = auth()->user()->agency_id;

        $validated = $request->validate([
            'date'          => ['required', 'date'],
            'ref_ar'        => ['nullable', 'string', 'max:100'],
            'agent_id'      => ['required', 'integer', 'exists:agents,id'],
            'applicant_id'  => ['nullable', 'integer', 'exists:applicants,id'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'account'       => ['nullable', 'string', 'max:80'],
            'debit_account' => ['nullable', 'string', 'max:40'],
            'type'          => ['nullable', 'string', 'max:40'],
            'mode'          => ['nullable', 'string', 'max:40'],
            'particular'    => ['nullable', 'string'],
        ]);

        // Security: agent + applicant must belong to the same agency
        $agent = Agent::where('agency_id', $agencyId)->findOrFail($validated['agent_id']);

        // (Branch feature) Branch accounts (non-admin with a branch) may only
        // file receivables against agents of their OWN branch or main-office
        // agents (no branch); another branch's agent is rejected.
        $user = auth()->user();
        if ($user && $user->isBranchLocked()) {
            $agentBranchId = $agent->branch_id;
            if ($agentBranchId !== null && (int) $agentBranchId !== (int) $user->branch_id) {
                return back()->withErrors([
                    'agent_id' => 'You can only file receivables for agents in your own branch.',
                ])->withInput();
            }
        }

        if (! empty($validated['applicant_id'])) {
            $applicant = Applicant::where('agency_id', $agencyId)->find($validated['applicant_id']);
            if (! $applicant || $applicant->agent_id !== $agent->id) {
                return back()->withErrors(['applicant_id' => 'Applicant must belong to the selected agent.'])->withInput();
            }
        }

        $validated['agency_id'] = $agencyId;
        $validated['user_id']   = auth()->id();
        $validated['status']    = Receivable::STATUS_PENDING;
        $validated['code']      = Receivable::nextCode($agencyId);

        $receivable = Receivable::create($validated);

        return redirect()->route('receivable.show', $receivable)
            ->with('success', "Receivable {$receivable->code} saved.");
    }

    /**
     * Review — shows the transaction + history. Only admin can change status.
     */
    public function show(Receivable $receivable): View
    {
        $this->authorizeAgency($receivable);
        $this->authorizeBranch($receivable);

        $history = $receivable->histories()->with('actor')->get();
        $canChangeStatus = in_array(auth()->user()->user_type, ['super_admin', 'admin']);

        return view('receivable.show', [
            'receivable'       => $receivable->load(['agent', 'applicant', 'encoder']),
            'history'          => $history,
            'canChangeStatus'  => $canChangeStatus,
            'statuses'         => [Receivable::STATUS_PENDING, Receivable::STATUS_RECEIVED],
        ]);
    }

    /**
     * Admin-only: change status and log the change.
     */
    public function updateStatus(Request $request, Receivable $receivable): RedirectResponse
    {
        $this->authorizeAgency($receivable);
        $this->authorizeBranch($receivable);
        $this->authorizeStatusChange();

        $validated = $request->validate([
            'status' => ['required', 'in:' . Receivable::STATUS_PENDING . ',' . Receivable::STATUS_RECEIVED],
            'note'   => ['nullable', 'string'],
        ]);

        $to = $validated['status'];

        if ($receivable->status !== $to) {
            DB::transaction(function () use ($receivable, $to, $validated) {
                $this->applyStatusChange($receivable, $to, $validated['note'] ?? null);
            });
        }

        return redirect()->route('receivable.show', $receivable)
            ->with('success', "Status updated to {$to}.");
    }

    /**
     * Admin-only batch status change (Toybits 2026-08-31): one status applied to
     * many selected receivables at once via the checkboxes on the index page.
     * Each changed receivable gets its own history entry, inside a single
     * transaction. Receivables already in the target status are skipped.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $this->authorizeStatusChange();

        $validated = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
            'status' => ['required', 'in:' . Receivable::STATUS_PENDING . ',' . Receivable::STATUS_RECEIVED],
            'note'   => ['nullable', 'string'],
        ]);

        $to   = $validated['status'];
        $note = $validated['note'] ?? null;

        // Only the caller's own agency's receivables are ever touched (plus, for
        // branch-locked users, only receivables of agents in their own branch or
        // main-office/no-branch agents — matching index()).
        $receivables = Receivable::where('agency_id', auth()->user()->agency_id)
            ->whereIn('id', $validated['ids'])
            ->when(auth()->user()->isBranchLocked(), function ($q) {
                $q->whereHas('agent', function ($aq) {
                    $aq->where('branch_id', auth()->user()->branch_id)
                        ->orWhereNull('branch_id');
                });
            })
            ->get();

        if ($receivables->isEmpty()) {
            return redirect()->route('receivable.index')
                ->with('error', 'No matching receivables selected.');
        }

        $changed = 0;

        DB::transaction(function () use ($receivables, $to, $note, &$changed) {
            foreach ($receivables as $receivable) {
                if ($receivable->status === $to) {
                    continue;
                }
                $this->applyStatusChange($receivable, $to, $note);
                $changed++;
            }
        });

        $message = $changed === 0
            ? "All selected receivables were already {$to}."
            : "Updated {$changed} receivable(s) to {$to}.";

        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Apply one status change to a single receivable + history entry. Shared by
     * the single (updateStatus) and batch (bulkUpdateStatus) paths so both stay
     * consistent (Toybits 2026-08-31).
     */
    private function applyStatusChange(Receivable $receivable, string $to, ?string $note): void
    {
        $from = $receivable->status;

        $receivable->update(['status' => $to]);

        ReceivableHistory::create([
            'receivable_id' => $receivable->id,
            'agency_id'     => $receivable->agency_id,
            'user_id'       => auth()->id(),
            'from_status'   => $from,
            'to_status'     => $to,
            'note'          => $note,
        ]);
    }

    /**
     * Only admin/super_admin may change receivable statuses (single or batch).
     */
    private function authorizeStatusChange(): void
    {
        if (! in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Only admin can change receivable status.');
        }
    }

    /**
     * Admin-only soft delete with a mandatory reason (stored on the history row).
     */
    public function destroy(Request $request, Receivable $receivable): RedirectResponse
    {
        $this->authorizeAgency($receivable);

        if (! in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Only admin can delete receivables.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($receivable, $validated) {
            ReceivableHistory::create([
                'receivable_id' => $receivable->id,
                'agency_id'     => $receivable->agency_id,
                'user_id'       => auth()->id(),
                'from_status'   => $receivable->status,
                'to_status'     => 'deleted',
                'note'          => $validated['reason'],
            ]);

            $receivable->delete(); // soft delete
        });

        return redirect()->route('receivable.index')
            ->with('success', 'Receivable deleted.');
    }

    /**
     * Ensure the receivable belongs to the caller's agency (isolation).
     */
    private function authorizeAgency(Receivable $receivable): void
    {
        if ((int) $receivable->agency_id !== (int) auth()->user()->agency_id) {
            abort(404);
        }
    }

    /**
     * (Branch feature) Branch-locked users may only view receivables filed for
     * agents in their OWN branch or main-office (no-branch) agents.
     */
    private function authorizeBranch(Receivable $receivable): void
    {
        $user = auth()->user();
        if (! $user || ! $user->isBranchLocked()) {
            return;
        }

        $agentBranchId = $receivable->agent?->branch_id;
        if ($agentBranchId !== null && (int) $agentBranchId !== (int) $user->branch_id) {
            abort(404);
        }
    }

    // ---------- Bulk CSV upload (Toybits 2026-09-06) ----------

    /**
     * Bulk upload page — mirrors the expense-request bulk flow, adapted to the
     * receivable create fields. Agents are matched by full name; the fixed
     * Receivable option lists (Account / Debit Account / Type / Mode) are shown
     * as reference on the page.
     */
    public function bulkUpload(): View
    {
        return view('receivable.bulk', [
            'accounts'      => Receivable::ACCOUNTS,
            'debitAccounts' => Receivable::DEBIT_ACCOUNTS,
            'types'         => Receivable::TYPES,
            'modes'         => Receivable::MODES,
        ]);
    }

    public function bulkTemplate()
    {
        $headers = [
            'Date', 'Ref#/AR#', 'Agent', 'Applicant', 'Amount',
            'Account', 'Debit Account', 'Type', 'Mode', 'Particular',
        ];

        $today = now()->toDateString();
        $sampleRows = [
            [
                $today, 'AR-1001', 'Juan Dela Cruz', '', '15000.00',
                'Placement Fee', 'Receivable', 'Full Payment', 'GCash', 'Partial placement fee',
            ],
            [
                $today, '', 'Juan Dela Cruz', '', '5000.00',
                'Monthly Collection', 'Dollar Request', 'Partial', 'Fund Transfer', 'Monthly collection',
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
            'Content-Disposition' => 'attachment; filename=receivables_bulk_template.csv',
        ]);
    }

    /**
     * Parse + validate the uploaded CSV. All rows must be valid before anything
     * is inserted (all-or-nothing, single transaction). Mirrors the expense
     * request bulk import flow, adapted to the receivable model fields.
     */
    public function bulkImport(Request $request): RedirectResponse
    {
        $agencyId = auth()->user()->agency_id;
        $user = auth()->user();

        if (! $agencyId) {
            return back()->withErrors(['csv_file' => 'No agency context. Please log in with an agency account to import receivables.']);
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        $file = $request->file('csv_file');
        $rows = $this->parseBulkCsv($file->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        // ---- Reference lookups (exact normalized matches, agency-scoped) ----
        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        $agentRows = Agent::where('agency_id', $agencyId)->get(['id', 'branch_id', 'name']);
        $agentByName = [];
        foreach ($agentRows as $agent) {
            $agentByName[$norm($agent->name)][] = $agent;
        }

        $applicantRows = Applicant::where('agency_id', $agencyId)->get(['id', 'agent_id', 'first_name', 'middle_name', 'last_name', 'suffix']);
        $applicantByName = [];
        foreach ($applicantRows as $applicant) {
            $applicantByName[$norm($applicant->full_name)][] = $applicant;
        }

        // Fixed enum maps (case/space-insensitive key => canonical stored value).
        $enumMap = function (array $options): array {
            $map = [];
            foreach ($options as $option) {
                $map[mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $option))] = $option;
            }
            return $map;
        };
        $accountByKey = $enumMap(Receivable::ACCOUNTS);
        $debitByKey   = $enumMap(Receivable::DEBIT_ACCOUNTS);
        $typeByKey    = $enumMap(Receivable::TYPES);
        $modeByKey    = $enumMap(Receivable::MODES);

        $errors = [];
        $validatedRows = [];
        $lockedBranchId = $user && $user->isBranchLocked() ? (int) $user->branch_id : null;

        foreach ($rows as $row) {
            $line = $row['line'];
            $d = $row['data'];
            $rowErrors = [];

            // ---- Date (blank = today) ----
            $date = now()->toDateString();
            $dateRaw = trim((string) ($d['date'] ?? ''));
            if ($dateRaw !== '') {
                $ts = strtotime($dateRaw);
                if ($ts === false) {
                    $rowErrors[] = "Date '{$dateRaw}' is not a valid date. Use YYYY-MM-DD (or leave blank for today).";
                } else {
                    $date = date('Y-m-d', $ts);
                }
            }

            // ---- Ref# / AR# (optional) ----
            $refAr = trim((string) ($d['ref_ar'] ?? ''));
            $refAr = $refAr !== '' ? $refAr : null;

            // ---- Agent (required by full name) ----
            $agentId = null;
            $agentRaw = trim((string) ($d['agent'] ?? ''));
            if ($agentRaw === '') {
                $rowErrors[] = 'Agent is required.';
            } else {
                $matches = $agentByName[$norm($agentRaw)] ?? [];
                if (count($matches) === 0) {
                    $rowErrors[] = "Agent '{$agentRaw}' not found.";
                } elseif (count($matches) > 1) {
                    $rowErrors[] = "Multiple agents match '{$agentRaw}'. Use the exact full name.";
                } else {
                    $agent = $matches[0];
                    $agentBranchId = $agent->branch_id ? (int) $agent->branch_id : null;
                    if ($lockedBranchId && $agentBranchId !== null && $agentBranchId !== $lockedBranchId) {
                        $rowErrors[] = 'You can only file receivables for agents in your own branch (or main-office agents without a branch).';
                    } else {
                        $agentId = $agent->id;
                    }
                }
            }

            // ---- Applicant (by full name; optional, must belong to agent) ----
            $applicantId = null;
            $applicantRaw = trim((string) ($d['applicant'] ?? ''));
            if ($applicantRaw !== '') {
                $matches = $applicantByName[$norm($applicantRaw)] ?? [];
                if (count($matches) === 0) {
                    $rowErrors[] = "Applicant '{$applicantRaw}' not found.";
                } elseif (count($matches) > 1) {
                    $rowErrors[] = "Multiple applicants match '{$applicantRaw}'. Use the exact full name.";
                } else {
                    $applicant = $matches[0];
                    if ($agentId && (int) $applicant->agent_id !== $agentId) {
                        $rowErrors[] = "Applicant '{$applicantRaw}' must belong to the selected agent.";
                    } else {
                        $applicantId = $applicant->id;
                    }
                }
            }

            // ---- Amount (required, > 0) ----
            $amountRaw = trim((string) ($d['amount'] ?? ''));
            if ($amountRaw === '') {
                $rowErrors[] = 'Amount is required.';
            } elseif (! is_numeric($amountRaw)) {
                $rowErrors[] = "Amount '{$amountRaw}' must be a number.";
            } elseif ((float) $amountRaw <= 0) {
                $rowErrors[] = 'Amount must be greater than 0.';
            }

            // ---- Fixed enum columns (optional; validated when filled) ----
            $account       = $this->resolveEnumValue($d['account'] ?? '', $accountByKey, 'Account', $rowErrors);
            $debitAccount  = $this->resolveEnumValue($d['debit_account'] ?? '', $debitByKey, 'Debit Account', $rowErrors);
            $type          = $this->resolveEnumValue($d['type'] ?? '', $typeByKey, 'Type', $rowErrors);
            $mode          = $this->resolveEnumValue($d['mode'] ?? '', $modeByKey, 'Mode', $rowErrors);

            // ---- Particular (optional) ----
            $particularRaw = trim((string) ($d['particular'] ?? ''));
            $particular = $particularRaw !== '' ? $particularRaw : null;

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $validatedRows[] = [
                'date'          => $date,
                'ref_ar'        => $refAr,
                'agent_id'      => $agentId,
                'applicant_id'  => $applicantId,
                'amount'        => $amountRaw,
                'account'       => $account,
                'debit_account' => $debitAccount,
                'type'          => $type,
                'mode'          => $mode,
                'particular'    => $particular,
            ];
        }

        if (count($validatedRows) > 2000) {
            return back()->withErrors(['csv_file' => 'Too many rows: the file has more than 2,000 receivable rows. Split it into smaller files.']);
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        // ---- All rows valid: insert in one transaction (all-or-nothing) ----
        $count = count($validatedRows);
        DB::transaction(function () use ($validatedRows, $agencyId) {
            foreach ($validatedRows as $data) {
                Receivable::create([
                    'agency_id'     => $agencyId,
                    'user_id'       => auth()->id(),
                    'agent_id'      => $data['agent_id'],
                    'applicant_id'  => $data['applicant_id'],
                    'code'          => Receivable::nextCode($agencyId),
                    'date'          => $data['date'],
                    'status'        => Receivable::STATUS_PENDING,
                    'ref_ar'        => $data['ref_ar'],
                    'amount'        => $data['amount'],
                    'account'       => $data['account'],
                    'debit_account' => $data['debit_account'],
                    'type'          => $data['type'],
                    'mode'          => $data['mode'],
                    'particular'    => $data['particular'],
                ]);
            }
        });

        return redirect()->route('receivable.index')
            ->with('success', "Bulk upload complete: {$count} receivable(s) imported from CSV.");
    }

    /**
     * Match an optional fixed-option value (Account / Debit Account / Type /
     * Mode) case/space-insensitively against its canonical list. Blank is
     * allowed (nullable columns); unknown values push a row error.
     */
    private function resolveEnumValue($raw, array $byKey, string $label, array &$rowErrors): ?string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return null;
        }
        $key = mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $raw));
        if (isset($byKey[$key])) {
            return $byKey[$key];
        }
        $available = array_values($byKey);
        $preview = array_slice($available, 0, 6);
        $rowErrors[] = "{$label} '{$raw}' not found.".($available !== [] ? ' Available: '.implode(', ', $preview).'...' : '');

        return null;
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
            // A row only counts as the header when it names the required columns.
            if (in_array('agent', $fields, true) && in_array('amount', $fields, true)) {
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

            return 'No header row found. The file must have a header row containing "Agent" and "Amount" columns (use the downloaded template and keep its first row unchanged). What the parser saw first: "'.$preview.'". If you added a title or blank line above the header, remove it, or just re-download the template and fill it in.';
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
                return 'Too many rows: the file exceeds 2,000 receivable rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
    }

    /**
     * Map normalized header labels to canonical receivable fields.
     *
     * @return array<string, string>
     */
    private function bulkHeaderAliases(): array
    {
        return [
            'date' => 'date',
            'transactiondate' => 'date',
            'refar' => 'ref_ar',
            'arref' => 'ref_ar',
            'refno' => 'ref_ar',
            'reference' => 'ref_ar',
            'ref' => 'ref_ar',
            'ar' => 'ref_ar',
            'agent' => 'agent',
            'agentname' => 'agent',
            'applicant' => 'applicant',
            'applicantname' => 'applicant',
            'amount' => 'amount',
            'account' => 'account',
            'accountname' => 'account',
            'debitaccount' => 'debit_account',
            'debit' => 'debit_account',
            'depositaccount' => 'debit_account',
            'type' => 'type',
            'paymenttype' => 'type',
            'mode' => 'mode',
            'modeofpayment' => 'mode',
            'particular' => 'particular',
            'particulars' => 'particular',
            'description' => 'particular',
            'notes' => 'particular',
            'remarks' => 'particular',
        ];
    }

    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
    }
}
