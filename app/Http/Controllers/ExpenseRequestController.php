<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\AgentDeduction;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\ExpenseRequestStatusHistory;
use App\Services\CurrencyConverter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseRequestController extends Controller
{
    /**
     * Tab 2 — Expenses & Payments: request list.
     *
     * Optional ?status= query param filters the table to one status
     * (Pending / Approved / For Releasing / Released / Cancelled).
     */
    public function index(): View
    {
        $agencyId = auth()->user()->agency_id;

        $allRequests = ExpenseRequest::with(['items', 'user', 'branch'])
            ->where('agency_id', $agencyId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        // Status tab filter (Toybits 2026-08-18). Invalid/absent status = show all.
        $status = request()->query('status');
        $activeStatus = in_array($status, ExpenseRequest::STATUSES, true) ? $status : null;
        $requests = $activeStatus
            ? $allRequests->where('status', $activeStatus)->values()
            : $allRequests;

        // Per-status request counts for the tab badges (always over the full set).
        $statusCounts = [];
        foreach (ExpenseRequest::STATUSES as $statusKey) {
            $statusCounts[$statusKey] = $allRequests->where('status', $statusKey)->count();
        }

        $totals = $this->currencyTotals($allRequests);

        // Duplicate detection (Toybits 2026-08-16): an item is a duplicate when
        // another item in the same agency shares amount + applicant (null matches null).
        $keyCounts = [];
        foreach ($requests as $requestModel) {
            // A cancelled transaction must not flag a live one as a duplicate.
            if ($requestModel->status === ExpenseRequest::STATUS_CANCELLED) {
                continue;
            }
            foreach ($requestModel->items as $item) {
                $key = $this->duplicateKey((float) $item->amount, $item->applicant_id);
                $keyCounts[$key] = ($keyCounts[$key] ?? 0) + 1;
            }
        }
        $duplicateKeys = array_keys(array_filter($keyCounts, fn ($c) => $c > 1));

        return view('expense_request.index', [
            'requests'         => $requests,
            'allRequests'      => $allRequests,
            'activeStatus'     => $activeStatus,
            'statusCounts'     => $statusCounts,
            'phpTotal'         => $totals['PHP'],
            'usdTotal'         => $totals['USD'],
            'totalAmount'      => round($totals['PHP'] + $totals['USD'] * config('expense.usd_to_php', 56), 2),
            'chargeTotals'     => $totals['charge'],
            'pendingPhpTotal'       => $totals['status']['pending']['PHP'],
            'pendingUsdTotal'       => $totals['status']['pending']['USD'],
            'approvedPhpTotal'      => $totals['status']['approved']['PHP'],
            'approvedUsdTotal'      => $totals['status']['approved']['USD'],
            'forReleasingPhpTotal'  => $totals['status']['for_releasing']['PHP'],
            'forReleasingUsdTotal'  => $totals['status']['for_releasing']['USD'],
            'releasedPhpTotal'      => $totals['status']['released']['PHP'],
            'releasedUsdTotal'      => $totals['status']['released']['USD'],
            'duplicateKeys'    => $duplicateKeys,
        ]);
    }

    /**
     * Save-time duplicate check (Toybits 2026-08-16): a line is a duplicate
     * when an existing item in the same agency shares the same amount AND the
     * same applicant (null applicant matches null applicant).
     */
    public function checkDuplicates(Request $request): \Illuminate\Http\JsonResponse
    {
        $agencyId = auth()->user()->agency_id;

        $lines = $request->validate([
            'lines'                 => ['required', 'array', 'min:1'],
            'lines.*.applicant_id'  => ['nullable', 'integer'],
            'lines.*.amount'        => ['required', 'numeric'],
        ])['lines'];

        $duplicate = false;

        foreach ($lines as $line) {
            $applicantId = $line['applicant_id'] ?? null;
            $amount      = number_format((float) $line['amount'], 2);

            $query = ExpenseRequestItem::query()
                ->whereHas('expenseRequest', fn ($q) => $q->where('agency_id', $agencyId))
                ->where('amount', $amount);

            if ($applicantId === null) {
                $query->whereNull('applicant_id');
            } else {
                $query->where('applicant_id', $applicantId);
            }

            if ($query->exists()) {
                $duplicate = true;
                break;
            }
        }

        return response()->json(['duplicate' => $duplicate]);
    }

    /**
     * Duplicate key for an item: amount (2dp) + applicant id, null-aware.
     */
    private function duplicateKey(?float $amount, ?int $applicantId): string
    {
        return number_format((float) $amount, 2) . '|' . ($applicantId ?? 'null');
    }

    /**
     * Create form.
     */
    public function create(): View
    {
        $agencyId = auth()->user()->agency_id;
        $user = auth()->user();

        // (Branch feature) Branch accounts only file expenses for their OWN branch
        // (admins/main-office users see the full list and pick freely).
        $branches = Branch::where('agency_id', $agencyId);
        $agents = Agent::where('agency_id', $agencyId)->with('branch');
        if ($user && $user->isBranchLocked()) {
            $branches->where('id', $user->branch_id);
            $agents->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }
        $branches = $branches->orderBy('name')->get();
        $agents = $agents->orderBy('name')->get();
        $applicants = Applicant::where('agency_id', $agencyId)->orderBy('last_name')->get(['id', 'agent_id', 'first_name', 'last_name']);
        $countries = Country::orderBy('name')->get();

        // Main accounts (with children) + flat selectable account list for the two-level picker.
        $mains = Account::mains()->with('children')
            ->where('agency_id', $agencyId)
            ->orderBy('name')
            ->get();

        $allAccounts = collect();
        foreach ($mains as $main) {
            foreach ($main->children as $child) {
                $allAccounts->push((object) [
                    'id'          => $child->id,
                    'parent_id'   => $main->id,
                    'name'        => $main->name . ' → ' . $child->name,
                    'charge_type' => $child->charge_type ?? 'office',
                ]);
            }
            if ($main->children->isEmpty()) {
                $allAccounts->push((object) [
                    'id'          => $main->id,
                    'parent_id'   => $main->id,
                    'name'        => $main->name,
                    'charge_type' => $main->charge_type ?? 'office',
                ]);
            }
        }

        return view('expense_request.create', compact(
            'branches', 'agents', 'applicants', 'countries', 'mains', 'allAccounts'
        ));
    }

    /**
     * Store an expense request ("Save Request") with multiple line items.
     */
    public function store(Request $request): RedirectResponse
    {
        $agencyId = auth()->user()->agency_id;

        $validated = $request->validate([
            'date'      => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'notes'     => ['nullable', 'string'],
            'lines'     => ['required', 'array', 'min:1'],
            'lines.*.charge'          => ['required', 'in:office,agent'],
            'lines.*.main_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'lines.*.sub_account_id'  => ['required', 'integer', 'exists:accounts,id'],
            'lines.*.agent_id'        => ['nullable', 'integer', 'exists:agents,id'],
            'lines.*.applicant_id' => ['nullable', 'integer', 'exists:applicants,id'],
            'lines.*.country_id'   => ['nullable', 'integer', 'exists:countries,id'],
            'lines.*.currency'     => ['required', 'in:PHP,USD'],
            'lines.*.amount'       => ['required', 'numeric', 'min:0.01'],
            'lines.*.payment'      => ['nullable', 'numeric', 'min:0'],
            'lines.*.particular'   => ['nullable', 'string'],
            'lines.*.file'         => ['nullable', 'file', 'max:5120'],
        ]);

        $branchId = $validated['branch_id'] ?? null;

        // (Branch feature) Branch accounts (non-admin with a branch) may only file
        // expense requests for their OWN branch: omitted defaults to their branch,
        // a different branch is rejected. Admins/main-office users pick freely.
        $user = auth()->user();
        if ($user && $user->isBranchLocked()) {
            if (blank($branchId)) {
                $branchId = $user->branch_id;
            } elseif ((int) $branchId !== (int) $user->branch_id) {
                return back()->withErrors([
                    'branch_id' => 'You can only file expense requests for your own branch.',
                ])->withInput();
            }
        }

        if ($branchId) {
            $branch = Branch::where('agency_id', $agencyId)->find($branchId);
            if (! $branch) {
                return back()->withErrors(['branch_id' => 'Invalid branch for this agency.'])->withInput();
            }
        }

        try {
            $requestModel = DB::transaction(function () use ($request, $validated, $agencyId, $branchId) {
                $reference = $this->nextReference($agencyId);

                $parent = ExpenseRequest::create([
                    'agency_id'    => $agencyId,
                    'user_id'      => auth()->id(),
                    'reference_no' => $reference,
                    'date'         => $validated['date'] ?? now()->toDateString(),
                    'status'       => 'pending',
                    'branch_id'    => $branchId,
                    'notes'        => $validated['notes'] ?? null,
                ]);

                // First history entry: who encoded the request (Toybits 2026-08-18).
                ExpenseRequestStatusHistory::create([
                    'expense_request_id' => $parent->id,
                    'agency_id'          => $agencyId,
                    'user_id'            => auth()->id(),
                    'from_status'        => null,
                    'to_status'          => ExpenseRequest::STATUS_PENDING,
                    'note'               => 'Request created',
                ]);

                foreach ($validated['lines'] as $index => $line) {
                    // Account group mirrors the front-end picker rule (Toybits 2026-08-16):
                    //   Charge = agent                -> agent accounts
                    //   Charge = office, no applicant -> office accounts
                    //   Charge = office + applicant   -> applicant accounts
                    $group = $line['charge'] === 'agent'
                        ? 'agent'
                        : (! empty($line['applicant_id']) ? 'applicant' : 'office');

                    // Main account is auto-resolved from the group (office -> office
                    // main, agent -> agent main, applicant -> applicant main). An
                    // explicit main_account_id is still accepted for backwards compatibility.
                    if (! empty($line['main_account_id'])) {
                        $main = Account::where('agency_id', $agencyId)->find($line['main_account_id']);
                    } else {
                        $main = Account::mains()
                            ->where('agency_id', $agencyId)
                            ->where('charge_type', $group)
                            ->orderBy('name')
                            ->first();
                    }
                    if (! $main || ! $main->isMain()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "lines.$index.main_account_id" => 'Selected Main Account is invalid.',
                        ]);
                    }

                    // CoA gating: the main must match the group.
                    if ($main->charge_type !== $group) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "lines.$index.main_account_id" => 'Account type must match the charge (office/agent).',
                        ]);
                    }

                    // Sub-account picker (restored): the item's account is the chosen
                    // sub-account (child of the group's main). Falls back to the main
                    // when omitted.
                    $account = $main;
                    if (! empty($line['sub_account_id'])) {
                        $sub = Account::where('agency_id', $agencyId)
                            ->where('parent_id', $main->id)
                            ->find($line['sub_account_id']);
                        if (! $sub) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                "lines.$index.sub_account_id" => 'Selected Sub Account is invalid.',
                            ]);
                        }
                        if ($sub->charge_type !== $group) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                "lines.$index.sub_account_id" => 'Sub Account type must match the charge (office/agent).',
                            ]);
                        }
                        $account = $sub;
                    }

                    // Branch-scoped agent: agent must belong to selected branch + this agency.
                    if (! empty($line['agent_id'])) {
                        $agentQuery = Agent::where('agency_id', $agencyId);
                        if ($branchId) {
                            $agentQuery->where('branch_id', $branchId);
                        }
                        if (! $agentQuery->whereKey($line['agent_id'])->exists()) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'lines.*.agent_id' => 'Agent must belong to the selected branch.',
                            ]);
                        }
                    }

                    // Applicant under the selected agent.
                    if (! empty($line['applicant_id'])) {
                        $applicant = Applicant::where('agency_id', $agencyId)->find($line['applicant_id']);
                        $agentId = $line['agent_id'] ?? null;
                        if (! $applicant || ($agentId && $applicant->agent_id !== (int) $agentId)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'lines.*.applicant_id' => 'Applicant must belong to the selected agent.',
                            ]);
                        }
                    }

                    ExpenseRequestItem::create([
                        'expense_request_id' => $parent->id,
                        'charge'             => $line['charge'],
                        'agent_id'           => $line['agent_id'] ?? null,
                        'applicant_id'       => $line['applicant_id'] ?? null,
                        'country_id'         => $line['country_id'] ?? null,
                        'currency'           => $line['currency'],
                        'amount'             => $line['amount'],
                        'payment'            => $line['payment'] ?? 0,
                        'account_id'         => $account->id,
                        'particular'         => $line['particular'] ?? null,
                        'file_path'          => $this->storeLineFile($request, $index),
                    ]);
                }

                return $parent;
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('expense_request.index')
            ->with('success', "Expense request {$requestModel->reference_no} saved.");
    }

    /**
     * Next reference number, unique per agency (sequential, starts at the configured value).
     */
    private function nextReference(int $agencyId): string
    {
        $start = (int) config('expense.reference_start', 2000);

        $max = ExpenseRequest::where('agency_id', $agencyId)
            ->get('reference_no')
            ->map(fn ($r) => (int) $r->reference_no)
            ->max();

        return (string) ($max ? max($max + 1, $start) : $start);
    }

    /**
     * Store an uploaded line attachment onto the public disk.
     */
    private function storeLineFile(Request $request, int $index): ?string
    {
        $file = $request->file("lines.$index.file");
        if (! $file) {
            return null;
        }

        return $file->store('expense-request-items', 'public');
    }

    /**
     * Currency + charge + status breakdown used on the index summary.
     */
    private function currencyTotals($requests): array
    {
        $php = 0.0;
        $usd = 0.0;
        $charge = ['office' => 0.0, 'agent' => 0.0];
        $status = [
            'pending'       => ['PHP' => 0.0, 'USD' => 0.0],
            'approved'      => ['PHP' => 0.0, 'USD' => 0.0],
            'for_releasing' => ['PHP' => 0.0, 'USD' => 0.0],
            'released'      => ['PHP' => 0.0, 'USD' => 0.0],
        ];

        foreach ($requests as $requestModel) {
            // Cancelled transactions are rejected: they count toward nothing.
            if ($requestModel->status === ExpenseRequest::STATUS_CANCELLED) {
                continue;
            }

            $statusKey = in_array($requestModel->status, ['approved', 'for_releasing', 'released'], true)
                ? $requestModel->status
                : 'pending';

            foreach ($requestModel->items as $item) {
                $isUsd = $item->currency === 'USD';
                $amount = (float) $item->amount;

                if ($isUsd) {
                    $usd += $amount;
                } else {
                    $php += $amount;
                }

                $status[$statusKey][$isUsd ? 'USD' : 'PHP'] += $amount;
                $charge[$item->charge] = ($charge[$item->charge] ?? 0) + $amount;
            }
        }

        return [
            'PHP'    => round($php, 2),
            'USD'    => round($usd, 2),
            'charge' => $charge,
            'status' => [
                'pending'       => ['PHP' => round($status['pending']['PHP'], 2), 'USD' => round($status['pending']['USD'], 2)],
                'approved'      => ['PHP' => round($status['approved']['PHP'], 2), 'USD' => round($status['approved']['USD'], 2)],
                'for_releasing' => ['PHP' => round($status['for_releasing']['PHP'], 2), 'USD' => round($status['for_releasing']['USD'], 2)],
                'released'      => ['PHP' => round($status['released']['PHP'], 2), 'USD' => round($status['released']['USD'], 2)],
            ],
        ];
    }

    /**
     * Review page: admin-only status change + transaction history.
     */
    public function show(ExpenseRequest $expenseRequest): View
    {
        $this->authorizeAgency($expenseRequest);

        $expenseRequest->load(['items.account', 'items.agent', 'items.applicant', 'items.country', 'user', 'branch', 'histories.actor']);

        return view('expense_request.show', [
            'request' => $expenseRequest,
        ]);
    }

    /**
     * Admin-only status change (pending -> approved -> for_releasing -> released,
     * or cancelled) + history log.
     */
    public function updateStatus(Request $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorizeAgency($expenseRequest);
        $this->authorizeStatusChange();

        $validated = $request->validate([
            'status' => ['required', 'in:' . implode(',', ExpenseRequest::STATUSES)],
            'note'   => ['nullable', 'string'],
        ]);

        $to = $validated['status'];

        if ($expenseRequest->status !== $to) {
            DB::transaction(function () use ($expenseRequest, $to, $validated) {
                $this->applyStatusChange($expenseRequest, $to, $validated['note'] ?? null);
            });
        }

        return redirect()->route('expense_request.show', $expenseRequest)
            ->with('success', "Status updated to {$to}.");
    }

    /**
     * Admin-only batch status change (Toybits 2026-08-31): one status applied to
     * many selected requests at once via the checkboxes on the index page.
     * Each changed request gets its own history entry + Paid-entry sync, inside
     * a single transaction. Requests already in the target status are skipped.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $this->authorizeStatusChange();

        $validated = $request->validate([
            'ids'    => ['required', 'array', 'min:1'],
            'ids.*'  => ['integer'],
            'status' => ['required', 'in:' . implode(',', ExpenseRequest::STATUSES)],
            'note'   => ['nullable', 'string'],
        ]);

        $to   = $validated['status'];
        $note = $validated['note'] ?? null;

        // Only the caller's own agency's requests are ever touched.
        $requests = ExpenseRequest::where('agency_id', auth()->user()->agency_id)
            ->whereIn('id', $validated['ids'])
            ->get();

        if ($requests->isEmpty()) {
            return redirect()->route('expense_request.index')
                ->with('error', 'No matching expense requests selected.');
        }

        $changed = 0;

        DB::transaction(function () use ($requests, $to, $note, &$changed) {
            foreach ($requests as $expenseRequest) {
                if ($expenseRequest->status === $to) {
                    continue;
                }
                $this->applyStatusChange($expenseRequest, $to, $note);
                $changed++;
            }
        });

        $message = $changed === 0
            ? "All selected requests were already {$to}."
            : "Updated {$changed} expense request(s) to {$to}.";

        // Back to the same status tab the admin was viewing.
        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Apply one status change to a single request + history entry + Paid-entry
     * sync. Shared by the single (updateStatus) and batch (bulkUpdateStatus) paths
     * so both stay consistent (Toybits 2026-08-31).
     */
    private function applyStatusChange(ExpenseRequest $expenseRequest, string $to, ?string $note): void
    {
        $from = $expenseRequest->status;

        $expenseRequest->update(['status' => $to]);

        ExpenseRequestStatusHistory::create([
            'expense_request_id' => $expenseRequest->id,
            'agency_id'          => $expenseRequest->agency_id,
            'user_id'            => auth()->id(),
            'from_status'        => $from,
            'to_status'          => $to,
            'note'               => $note,
        ]);

        // Approved agent-charged items become Paid entries in the agent report
        // (Deductions & Paid tab); cancelling removes them so cancelled items
        // don't linger (Toybits 2026-08-29).
        $this->syncPaidEntriesFromApproval($expenseRequest, $to);
    }

    /**
     * Only admin/super_admin may change expense request statuses (single or batch).
     */
    private function authorizeStatusChange(): void
    {
        if (! in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Only admin can change expense request status.');
        }
    }

    /**
     * On approval, agent-charged items become Paid entries in the agent report's
     * Deductions & Paid tab (net = amount − payment, converted to PHP). On cancel,
     * linked Paid entries are removed.
     */
    private function syncPaidEntriesFromApproval(ExpenseRequest $expenseRequest, string $to): void
    {
        if ($to === ExpenseRequest::STATUS_CANCELLED) {
            AgentDeduction::whereIn(
                'expense_request_item_id',
                $expenseRequest->items()->pluck('id')
            )->delete();

            return;
        }

        if ($to !== ExpenseRequest::STATUS_APPROVED) {
            return;
        }

        $converter = new CurrencyConverter();

        foreach ($expenseRequest->items as $item) {
            if ($item->charge !== 'agent' || ! $item->agent_id) {
                continue; // only agent-charged items flow into the agent report
            }

            $net = (float) $item->amount - (float) ($item->payment ?? 0);

            AgentDeduction::updateOrCreate(
                ['expense_request_item_id' => $item->id],
                [
                    'agency_id'    => $expenseRequest->agency_id,
                    'user_id'      => auth()->id(),
                    'agent_id'     => $item->agent_id,
                    'applicant_id' => $item->applicant_id,
                    'date'         => $expenseRequest->date->toDateString(),
                    'account'      => AgentDeduction::ACCOUNT_PAID,
                    'amount'       => round($converter->toPhp($net, $item->currency), 2),
                    'particular'   => $item->particular
                        ? 'Expense #' . $expenseRequest->reference_no . ': ' . $item->particular
                        : 'Expense #' . $expenseRequest->reference_no,
                ]
            );
        }
    }

    /**
     * Ensure the request belongs to the caller's agency (isolation).
     */
    private function authorizeAgency(ExpenseRequest $expenseRequest): void
    {
        if ((int) $expenseRequest->agency_id !== (int) auth()->user()->agency_id) {
            abort(404);
        }
    }
}
