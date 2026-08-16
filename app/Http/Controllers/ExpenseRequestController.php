<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Branch;
use App\Models\Country;
use App\Models\ExpenseRequest;
use App\Models\ExpenseRequestItem;
use App\Models\ExpenseRequestStatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ExpenseRequestController extends Controller
{
    /**
     * Tab 2 — Expenses & Payments: request list.
     */
    public function index(): View
    {
        $agencyId = auth()->user()->agency_id;

        $requests = ExpenseRequest::with(['items', 'user', 'branch'])
            ->where('agency_id', $agencyId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        $totals = $this->currencyTotals($requests);

        return view('expense_request.index', [
            'requests'         => $requests,
            'phpTotal'         => $totals['PHP'],
            'usdTotal'         => $totals['USD'],
            'totalAmount'      => round($totals['PHP'] + $totals['USD'] * config('expense.usd_to_php', 56), 2),
            'chargeTotals'     => $totals['charge'],
            'pendingPhpTotal'  => $totals['status']['pending']['PHP'],
            'pendingUsdTotal'  => $totals['status']['pending']['USD'],
            'receivedPhpTotal' => $totals['status']['received']['PHP'],
            'receivedUsdTotal' => $totals['status']['received']['USD'],
        ]);
    }

    /**
     * Create form.
     */
    public function create(): View
    {
        $agencyId = auth()->user()->agency_id;

        $branches = Branch::where('agency_id', $agencyId)->orderBy('name')->get();
        $agents = Agent::where('agency_id', $agencyId)->with('branch')->orderBy('name')->get();
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
            'lines.*.agent_id'        => ['nullable', 'integer', 'exists:agents,id'],
            'lines.*.applicant_id' => ['nullable', 'integer', 'exists:applicants,id'],
            'lines.*.country_id'   => ['nullable', 'integer', 'exists:countries,id'],
            'lines.*.currency'     => ['required', 'in:PHP,USD'],
            'lines.*.amount'       => ['required', 'numeric', 'min:0.01'],
            'lines.*.particular'   => ['nullable', 'string'],
            'lines.*.file'         => ['nullable', 'file', 'max:5120'],
        ]);

        $branchId = $validated['branch_id'] ?? null;
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

                foreach ($validated['lines'] as $index => $line) {
                    // Main account is auto-resolved from the charge when the
                    // dropdown is omitted (Charge and Main Account are the same).
                    if (! empty($line['main_account_id'])) {
                        $main = Account::where('agency_id', $agencyId)->find($line['main_account_id']);
                    } else {
                        $main = Account::mains()
                            ->where('agency_id', $agencyId)
                            ->where('charge_type', $line['charge'])
                            ->orderBy('name')
                            ->first();
                    }
                    if (! $main || ! $main->isMain()) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "lines.$index.main_account_id" => 'Selected Main Account is invalid.',
                        ]);
                    }

                    // The sub-account picker was removed: the item's account IS the selected Main Account.
                    $account = $main;

                    // CoA gating: office charge -> office account only; agent charge -> agent account only.
                    if ($account->charge_type !== $line['charge']) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "lines.$index.main_account_id" => 'Account type must match the charge (office/agent).',
                        ]);
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
                        'account_id'         => $main->id,
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
            'pending'  => ['PHP' => 0.0, 'USD' => 0.0],
            'received' => ['PHP' => 0.0, 'USD' => 0.0],
        ];

        foreach ($requests as $request) {
            $statusKey = $request->status === ExpenseRequest::STATUS_RECEIVED ? 'received' : 'pending';

            foreach ($request->items as $item) {
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
                'pending'  => ['PHP' => round($status['pending']['PHP'], 2), 'USD' => round($status['pending']['USD'], 2)],
                'received' => ['PHP' => round($status['received']['PHP'], 2), 'USD' => round($status['received']['USD'], 2)],
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
     * Admin-only status change (pending <-> received) + history log.
     */
    public function updateStatus(Request $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorizeAgency($expenseRequest);

        if (! in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Only admin can change expense request status.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . ExpenseRequest::STATUS_PENDING . ',' . ExpenseRequest::STATUS_RECEIVED],
            'note'   => ['nullable', 'string'],
        ]);

        $from = $expenseRequest->status;
        $to   = $validated['status'];

        if ($from !== $to) {
            DB::transaction(function () use ($expenseRequest, $from, $to, $request) {
                $expenseRequest->update(['status' => $to]);

                ExpenseRequestStatusHistory::create([
                    'expense_request_id' => $expenseRequest->id,
                    'agency_id'          => $expenseRequest->agency_id,
                    'user_id'            => auth()->id(),
                    'from_status'        => $from,
                    'to_status'          => $to,
                    'note'               => $request->input('note'),
                ]);
            });
        }

        return redirect()->route('expense_request.show', $expenseRequest)
            ->with('success', "Status updated to {$to}.");
    }

    /**
     * Admin-only soft delete with a mandatory reason (stored on the history row).
     */
    public function destroy(Request $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorizeAgency($expenseRequest);

        if (! in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Only admin can delete expense requests.');
        }

        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($expenseRequest, $validated) {
            ExpenseRequestStatusHistory::create([
                'expense_request_id' => $expenseRequest->id,
                'agency_id'          => $expenseRequest->agency_id,
                'user_id'            => auth()->id(),
                'from_status'        => $expenseRequest->status,
                'to_status'          => 'deleted',
                'note'               => $validated['reason'],
            ]);

            $expenseRequest->delete(); // soft delete
        });

        return redirect()->route('expense_request.index')
            ->with('success', 'Expense request deleted.');
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
