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

        // (Cyd 2026-11-04 card) Search bar — free text over the columns the
        // accountant actually reads: code/AR#, agent, applicant, particular,
        // account, mode, type, deposit account, status, encoder, date and
        // amount. Blank/absent = no filter. Applied AFTER the encoder list is
        // derived so the encoder dropdown always offers every encoder.
        $activeSearch = trim((string) request()->query('q'));

        // (Card 6af8296b) Status tabs: Pending / Received / Cancelled.
        // 'all' keeps the old combined view; the tab also actually filters the
        // list, which is the point of the card ("separate the transaction with
        // these status").
        $activeStatus = (string) request()->query('status', 'all');
        if (! in_array($activeStatus, [...Receivable::STATUSES, 'all'], true)) {
            $activeStatus = 'all';
        }

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

        // Tab counters come off the base scope (encoder + search applied) so a
        // tab always shows how many rows it will actually contain.
        $scoped  = (clone $base)
            ->when($activeEncoder, fn ($q) => $q->where('user_id', $activeEncoder))
            ->when($activeSearch !== '', fn ($q) => $this->applySearch($q, $activeSearch));
        $counts  = (clone $scoped)->toBase()
            ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as c'))
            ->groupBy('status')->pluck('c', 'status')->all();

        $statusCounts = [
            'all' => array_sum($counts),
            Receivable::STATUS_PENDING   => (int) ($counts[Receivable::STATUS_PENDING] ?? 0),
            Receivable::STATUS_RECEIVED  => (int) ($counts[Receivable::STATUS_RECEIVED] ?? 0),
            Receivable::STATUS_CANCELLED => (int) ($counts[Receivable::STATUS_CANCELLED] ?? 0),
        ];

        $receivables = (clone $scoped)
            ->with(['agent', 'applicant', 'encoder'])
            ->when($activeStatus !== 'all', fn ($q) => $q->where('status', $activeStatus))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('receivable.index', [
            'receivables'   => $receivables,
            'encoders'      => $encoders,
            'activeEncoder' => $activeEncoder,
            'activeSearch'  => $activeSearch,
            // (Josh 2026-11-11, card 6af3ea66) The headline "Total Amount" must
            // show the RECEIVED amount only, not the sum of every status.
            // `allAmount` keeps the old full-set figure for anything that still
            // needs it; the per-status lines below still show Pending.
            'totalAmount'   => round((float) $receivables->where('status', Receivable::STATUS_RECEIVED)->sum('amount'), 2),
            'allAmount'     => round((float) $receivables->where('status', '!=', Receivable::STATUS_CANCELLED)->sum('amount'), 2),
            'pendingTotal'  => round((float) $receivables->where('status', Receivable::STATUS_PENDING)->sum('amount'), 2),
            'receivedTotal' => round((float) $receivables->where('status', Receivable::STATUS_RECEIVED)->sum('amount'), 2),
            'cancelledTotal' => round((float) $receivables->where('status', Receivable::STATUS_CANCELLED)->sum('amount'), 2),
            'activeStatus'  => $activeStatus,
            'statusCounts'  => $statusCounts,
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

        // (Cyd 2026-10-23) Applicants follow the Agent list: a branch account
        // only sees applicants whose agent is in its own branch (or a
        // shared/main-office agent). An applicant therefore only ever appears
        // under its own agent.
        $applicants = Applicant::where('agency_id', $agencyId)
            ->when($user && $user->isBranchLocked(), fn ($q) => $q->whereHas('agent', function ($agentQ) use ($user) {
                $agentQ->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            }))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get(['id', 'agent_id', 'first_name', 'middle_name', 'last_name', 'suffix']);

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
            // (Card 6af8296b) Office Returns / Monthly Collection / Other
            // Collection are internal money movements with no worker attached,
            // so Agent and Applicant are optional for exactly those accounts.
            // Expressed as a rule (not a post-validation check) so the error is
            // reported alongside any other validation failure.
            // Note: neither 'nullable' nor 'sometimes' — both skip the rule when
            // the field is absent, which is exactly the case we must catch.
            // requiredUnless keeps it a real field-level error that surfaces
            // together with any other validation failure.
            'agent_id'      => [
                \Illuminate\Validation\Rule::requiredUnless(
                    fn () => Receivable::allowsStandalone($request->input('account'))
                ),
                'nullable',
                'integer',
                'exists:agents,id',
            ],
            'applicant_id'  => ['nullable', 'integer', 'exists:applicants,id'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'account'       => ['nullable', 'string', 'max:80'],
            'debit_account' => ['nullable', 'string', 'max:40'],
            'type'          => ['nullable', 'string', 'max:40'],
            'mode'          => ['nullable', 'string', 'max:40'],
            'particular'    => ['nullable', 'string'],
        ] + \App\Support\RequestAttachments::rules());

        // Security: agent + applicant must belong to the same agency
        $agent = empty($validated['agent_id'])
            ? null
            : Agent::where('agency_id', $agencyId)->findOrFail($validated['agent_id']);

        if (! $agent) {
            // Standalone account: no agent, so no applicant may be attached either.
            $validated['agent_id'] = null;
            $validated['applicant_id'] = null;
        }

        // (Branch feature) Branch accounts (non-admin with a branch) may only
        // file receivables against agents of their OWN branch or main-office
        // agents (no branch); another branch's agent is rejected.
        $user = auth()->user();
        if ($user && $user->isBranchLocked() && $agent) {
            $agentBranchId = $agent->branch_id;
            if ($agentBranchId !== null && (int) $agentBranchId !== (int) $user->branch_id) {
                return back()->withErrors([
                    'agent_id' => 'You can only file receivables for agents in your own branch.',
                ])->withInput();
            }
        }

        if (! empty($validated['applicant_id'])) {
            $applicant = Applicant::where('agency_id', $agencyId)->find($validated['applicant_id']);
            if (! $applicant || ! $agent || $applicant->agent_id !== $agent->id) {
                return back()->withErrors(['applicant_id' => 'Applicant must belong to the selected agent.'])->withInput();
            }
        }

        $validated['agency_id'] = $agencyId;
        $validated['user_id']   = auth()->id();
        $validated['status']    = Receivable::STATUS_PENDING;
        $validated['code']      = Receivable::nextCode($agencyId);

        $receivable = Receivable::create($validated);

        $this->saveAttachments($request, $receivable);

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
            'statuses'         => Receivable::STATUSES,
        ]);
    }

    /**
     * Edit form for an existing transaction (Cyd 2026-11-01: Admin + Accounting
     * may edit Receivable and Expenses & Payments transactions).
     */
    public function edit(Receivable $receivable): View
    {
        $this->authorizeAgency($receivable);
        $this->authorizeBranch($receivable);
        $this->authorizeTransactionEdit($receivable);

        $agencyId = auth()->user()->agency_id;
        $user = auth()->user();

        $agents = Agent::where('agency_id', $agencyId)->with('branch');
        if ($user->isBranchLocked()) {
            $agents->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }

        $applicants = Applicant::where('agency_id', $agencyId);
        if ($user->isBranchLocked()) {
            $applicants->whereHas('agent', function ($agentQ) use ($user) {
                $agentQ->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }

        return view('receivable.create', [
            'record'         => $receivable,
            'isEdit'         => true,
            'agents'         => $agents->orderBy('name')->get(),
            'applicants'     => $applicants->orderBy('last_name')->orderBy('first_name')->get(['id', 'agent_id', 'first_name', 'middle_name', 'last_name', 'suffix']),
            'code'           => $receivable->code,
            'accounts'       => Receivable::ACCOUNTS,
            'debitAccounts'  => Receivable::DEBIT_ACCOUNTS,
            'types'          => Receivable::TYPES,
            'modes'          => Receivable::MODES,
        ]);
    }

    /**
     * Save the edited transaction. Code, status and encoder stay untouched.
     */
    public function update(Request $request, Receivable $receivable): RedirectResponse
    {
        $this->authorizeAgency($receivable);
        $this->authorizeBranch($receivable);
        $this->authorizeTransactionEdit($receivable);

        $agencyId = auth()->user()->agency_id;

        $validated = $request->validate([
            'date'          => ['required', 'date'],
            'ref_ar'        => ['nullable', 'string', 'max:100'],
            // Legacy rows may carry no agent at all; keep them editable instead
            // of forcing the user to attach one just to fix a typo. A standalone
            // account (card 6af8296b) may also drop an agent it once had.
            'agent_id'      => [
                ($receivable->agent_id && ! Receivable::allowsStandalone($request->input('account'))) ? 'required' : 'nullable',
                'integer', 'exists:agents,id',
            ],
            'applicant_id'  => ['nullable', 'integer', 'exists:applicants,id'],
            'amount'        => ['required', 'numeric', 'min:0.01'],
            'account'       => ['nullable', 'string', 'max:80'],
            'debit_account' => ['nullable', 'string', 'max:40'],
            'type'          => ['nullable', 'string', 'max:40'],
            'mode'          => ['nullable', 'string', 'max:40'],
            'particular'    => ['nullable', 'string'],
        ] + \App\Support\RequestAttachments::rules());

        $agent = ! empty($validated['agent_id'])
            ? Agent::where('agency_id', $agencyId)->findOrFail($validated['agent_id'])
            : null;

        $user = auth()->user();
        if ($agent && $user->isBranchLocked()) {
            $agentBranchId = $agent->branch_id;
            if ($agentBranchId !== null && (int) $agentBranchId !== (int) $user->branch_id) {
                return back()->withErrors([
                    'agent_id' => 'You can only file receivables for agents in your own branch.',
                ])->withInput();
            }
        }

        if (! empty($validated['applicant_id'])) {
            $applicant = Applicant::where('agency_id', $agencyId)->find($validated['applicant_id']);
            if (! $applicant || ($agent && (int) $applicant->agent_id !== (int) $agent->id)) {
                return back()->withErrors(['applicant_id' => 'Applicant must belong to the selected agent.'])->withInput();
            }
        }

        $receivable->update($validated);

        ReceivableHistory::create([
            'receivable_id' => $receivable->id,
            'agency_id'     => $receivable->agency_id,
            'user_id'       => auth()->id(),
            'from_status'   => $receivable->status,
            'to_status'     => $receivable->status,
            'note'          => 'Transaction details edited by ' . ($user->name ?? 'user'),
        ]);

        $this->saveAttachments($request, $receivable);

        return redirect()->route('receivable.show', $receivable)
            ->with('success', "Receivable {$receivable->code} updated.");
    }

    /**
     * Store optional multi-file attachments (Cyd 2026-11-09, "Upload and Edit").
     */
    private function saveAttachments(Request $request, Receivable $receivable): void
    {
        $files = $request->file('attachments', []);
        if (! is_array($files) || $files === []) {
            return;
        }

        $files = array_values(array_filter($files));
        $existing = $receivable->attachments()->count();

        if ($message = \App\Support\RequestAttachments::overflowMessage($existing, count($files))) {
            throw \Illuminate\Validation\ValidationException::withMessages(['attachments' => $message]);
        }

        foreach (\App\Support\RequestAttachments::store($files, 'receivable-attachments', auth()->id()) as $payload) {
            $receivable->attachments()->create($payload);
        }
    }

    /**
     * Admin + Accounting may always edit; a Requestor may edit their own
     * request while it is still Pending (Cyd 2026-11-09, "Upload and Edit").
     */
    private function authorizeTransactionEdit(?Receivable $receivable = null): void
    {
        $user = auth()->user();
        if (! $user) {
            abort(403, 'You must be signed in to edit transactions.');
        }

        if ($user->canEditTransaction($receivable)) {
            return;
        }

        abort(403, $user->transactionEditRefusal($receivable));
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
            'status' => ['required', 'in:' . implode(',', Receivable::STATUSES)],
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
            'status' => ['required', 'in:' . implode(',', Receivable::STATUSES)],
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
        // Column set + order mirror the agency's own Receivable sheet
        // (Receivable_Template.csv on the Trello card "LANDAS: Expense &
        // Receivable Bulk Upload - Formats").
        //  - Code #   : auto-generated per agency, ignored on import.
        //  - User     : encoder (matched by name/email; blank = the uploading user).
        //  - Status   : Pending | Received (blank = Pending).
        //  - Deposit Bank: receivable's deposit (debit) account.
        $headers = [
            'Code #', 'Date', 'User', 'Status', 'Ref#/AR#', 'Applicant', agent_header('Agent'),
            'Amount', 'Account', 'Particular', 'Mode', 'Type', 'Deposit Bank',
        ];

        $today = now()->toDateString();
        $sampleRows = [
            [
                '', $today, '', 'Pending', 'AR-1001', '', 'Juan Dela Cruz',
                '15000.00', 'Placement Fee', 'Partial placement fee', 'GCash', 'Full Payment', 'Receivable',
            ],
            [
                '', $today, '', 'Pending', '', '', 'Juan Dela Cruz',
                '5000.00', 'Monthly Collection', 'Monthly collection', 'Fund Transfer', 'Partial', 'Dollar Request',
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

            // ---- Status (optional; blank = Pending) ----
            $status = $this->resolveBulkStatus($d['status'] ?? '', $rowErrors);

            // ---- User / encoder (optional; blank or unknown = importing user) ----
            $encoderId = $this->resolveBulkUser($d['user'] ?? '', (int) $agencyId);

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
                'status'        => $status,
                'user_id'       => $encoderId,
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
                    'user_id'       => $data['user_id'] ?? auth()->id(),
                    'agent_id'      => $data['agent_id'],
                    'applicant_id'  => $data['applicant_id'],
                    'code'          => Receivable::nextCode($agencyId),
                    'date'          => $data['date'],
                    'status'        => $data['status'],
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
    /**
     * Map the template's Status column onto a receivable status. Blank means
     * Pending, which is what a freshly imported row defaults to.
     */
    private function resolveBulkStatus($raw, array &$rowErrors): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return Receivable::STATUS_PENDING;
        }

        $key = mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $raw));
        $map = [
            'pending'   => Receivable::STATUS_PENDING,
            'unpaid'    => Receivable::STATUS_PENDING,
            'open'      => Receivable::STATUS_PENDING,
            'received'  => Receivable::STATUS_RECEIVED,
            'paid'      => Receivable::STATUS_RECEIVED,
            'collected' => Receivable::STATUS_RECEIVED,
            'cancelled' => Receivable::STATUS_CANCELLED,
            'canceled'  => Receivable::STATUS_CANCELLED,
            'void'      => Receivable::STATUS_CANCELLED,
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        $rowErrors[] = "Status '{$raw}' not found. Use 'Pending', 'Received' or 'Cancelled' (or leave blank for Pending).";

        return Receivable::STATUS_PENDING;
    }

    /**
     * Resolve the template's User column to an encoder id (name or email).
     * Blank / unmatched values fall back to the importing user so a migrated
     * sheet with legacy usernames still imports.
     */
    private function resolveBulkUser($raw, int $agencyId): ?int
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return auth()->id();
        }

        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));
        $needle = $norm($raw);

        $users = User::where('agency_id', $agencyId)->get(['id', 'name', 'surname', 'email']);
        foreach ($users as $user) {
            if ($norm($user->email) === $needle
                || $norm($user->name) === $needle
                || $norm(trim($user->name.' '.$user->surname)) === $needle) {
                return $user->id;
            }
        }

        return auth()->id();
    }

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

            // Toybits 2026-11-08 (card VrkCQ97s): an *expense* sheet also has
            // Agent + Amount, so it used to import here silently and turn
            // expense transactions into receivables. Recognise the expense
            // template by its own columns and send it to the right page.
            $expenseSheet = $this->looksLikeExpenseSheet($cells);
            if ($expenseSheet && in_array('agent', $fields, true) && in_array('amount', $fields, true)) {
                return 'These transactions should be Expenses, not Receivable. This file is an '
                    . 'Expense sheet (it has the ' . $expenseSheet . ' column). Upload it on the '
                    . 'Expenses page instead: /expense-requests/bulk — nothing was imported here.';
            }

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
            // Display columns from the agency's sheet: Code # is generated by
            // the app, User/Status are honoured when filled.
            'code' => 'code',
            'user' => 'user',
            'encoder' => 'user',
            'encodedby' => 'user',
            'status' => 'status',
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
            'depositbank' => 'debit_account',
            'bank' => 'debit_account',
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

    /**
     * Returns the first expense-only column the header row carries, or null.
     * The expense template has Charge / Payment / Request Status / Ref # /
     * Currency / Office; the receivable template has none of those. Used to
     * refuse an expense sheet on the receivable bulk page (card VrkCQ97s).
     */
    private function looksLikeExpenseSheet(array $cells): ?string
    {
        // Ordered by how unmistakable they are, so the message quotes
        // "Charge" rather than the ambiguous "Ref #".
        $expenseOnly = ['charge', 'payment', 'requeststatus', 'currency', 'office', 'ref'];
        $found = [];
        foreach ($cells as $header) {
            $key = $this->normKey($header);
            $pos = $key === '' ? false : array_search($key, $expenseOnly, true);
            if ($pos !== false) {
                $found[$pos] = trim((string) $header);
            }
        }
        if (! $found) {
            return null;
        }
        ksort($found);

        return reset($found);

        return null;
    }

    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
    }

    /**
     * (Cyd 2026-11-04 card) Receivable search.
     *
     * One free-text box that looks through every field shown on the list —
     * including the related agent / applicant / encoder names — so typing
     * "CAROL", a code, an AR number or part of a particular returns all the
     * transactions related to it. `%` characters are escaped so a stray
     * wildcard cannot turn the search into a full table scan of everything.
     */
    private function applySearch($query, string $term)
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';

        return $query->where(function ($q) use ($like, $term) {
            $q->where('code', 'like', $like)
                ->orWhere('ref_ar', 'like', $like)
                ->orWhere('particular', 'like', $like)
                ->orWhere('account', 'like', $like)
                ->orWhere('debit_account', 'like', $like)
                ->orWhere('mode', 'like', $like)
                ->orWhere('type', 'like', $like)
                ->orWhere('status', 'like', $like)
                ->orWhere('amount', 'like', $like)
                ->orWhereHas('agent', fn ($a) => $a->where('name', 'like', $like))
                ->orWhereHas('applicant', function ($a) use ($like) {
                    $a->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like);
                })
                ->orWhereHas('encoder', fn ($u) => $u->where('name', 'like', $like));

            // Date search stays portable (MySQL + the sqlite test DB): a full
            // date, a YYYY-MM month or a bare year each narrow the list.
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $term)) {
                $q->orWhereDate('date', $term);
            } elseif (preg_match('/^(\d{4})-(\d{2})$/', $term, $m)) {
                $q->orWhere(fn ($d) => $d->whereYear('date', (int) $m[1])->whereMonth('date', (int) $m[2]));
            } elseif (preg_match('/^\d{4}$/', $term)) {
                $q->orWhereYear('date', (int) $term);
            }
        });
    }
}
