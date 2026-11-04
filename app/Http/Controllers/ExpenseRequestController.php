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
use App\Models\User;
use App\Services\CurrencyConverter;
use App\Support\ModuleAccess;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
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

        // (Toybits 2026-10-07) Encoder filter — optional ?encoder=<userId>.
        $activeEncoder = request()->query('encoder');

        // (Cyd 2026-11-04 card) Search bar — free text over the request header
        // (ref#, date, encoder) and every transaction line (agent, applicant,
        // particular, account, country, currency, amount, payment). A request
        // stays in the list when either its header or one of its lines matches,
        // so the result is every transaction related to the typed text.
        $activeSearch = trim((string) request()->query('q'));

        // Base scope = agency + row-visibility + branch rules. Both the table
        // and the encoder dropdown derive from it.
        $base = ExpenseRequest::query()
            ->where('agency_id', $agencyId)
            // (Cyd 2026-09-28 #4) Everyone except the privileged accounts
            // (Mae/Evelyn/Angel) and admins sees ONLY the requests they
            // created. Status changes still show on their own rows.
            ->when(! auth()->user()->seesAllAccountingData(),
                fn ($q) => $q->where('user_id', auth()->id()))
            // (Branch feature) Branch-locked users only see their own branch's requests.
            ->when($this->branchLocked(), fn ($q) => $q->where('branch_id', auth()->user()->branch_id));

        $encoderIds = (clone $base)->whereNotNull('user_id')->pluck('user_id')->unique()->values()->all();
        $encoders   = User::whereIn('id', $encoderIds)->orderBy('name')->get(['id', 'name']);

        $allRequests = (clone $base)
            ->with([
                'items.applicant', 'items.agent', 'items.account', 'items.country', 'items.branch',
                'items.expenseRequest',
                'user', 'branch',
            ])
            ->when($activeEncoder, fn ($q) => $q->where('user_id', $activeEncoder))
            ->when($activeSearch !== '', fn ($q) => $this->applySearch($q, $activeSearch))
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

        // Consolidated per-status dashboard totals (Toybits 2026-09-23): amount
        // split by currency + request count for every status, so the summary
        // card and the right-side status selector can render/toggle them.
        $statusTotals = [];
        foreach (ExpenseRequest::STATUSES as $statusKey) {
            $statusTotals[$statusKey] = [
                'PHP'   => $totals['status'][$statusKey]['PHP'] ?? 0.0,
                'USD'   => $totals['status'][$statusKey]['USD'] ?? 0.0,
                'count' => $statusCounts[$statusKey],
            ];
        }

        // Duplicate detection (Cyd 2026-10-23): colour ONLY the repeated entries.
        // The old amount+applicant key flagged 125 of 130 Gulf rows (every NULL
        // applicant matched every other one) and lit up whole multi-entry
        // requests. Now the key is date + charge + branch + agent + applicant +
        // account + currency + amount + payment, the first copy stays
        // clean, and the other transactions of a multi-entry request are never
        // dragged into the highlight.
        $itemsByFingerprint = [];
        foreach ($allRequests as $requestModel) {
            // A cancelled transaction must not flag a live one as a duplicate.
            if ($requestModel->status === ExpenseRequest::STATUS_CANCELLED) {
                continue;
            }
            foreach ($requestModel->items as $item) {
                $itemsByFingerprint[$this->itemFingerprint($requestModel, $item)][] = $item;
            }
        }

        $duplicateItemIds = [];
        // Josh 2026-11-02: the badge must say WHICH transaction it repeats, so
        // store the original line's reference for every flagged repeat.
        $duplicateOrigins = [];   // repeat item id => ['ref' => ..., 'same_request' => bool]
        // Josh 2026-11-04: clicking the badge opens the transactions behind the
        // duplicate, so keep each fingerprint group with its member lines.
        $duplicateGroups = [];    // group key => ['ref' => ..., 'items' => [...]]
        $duplicateGroupByItem = []; // repeat item id => group key
        $groupNo = 0;
        foreach ($itemsByFingerprint as $group) {
            if (count($group) < 2) {
                continue;
            }
            usort($group, fn ($a, $b) => [$a->created_at, $a->id] <=> [$b->created_at, $b->id]);
            $first = $group[0];
            $groupKey = 'duplicate-group-' . (++$groupNo);

            $members = [];
            foreach ($group as $index => $member) {
                $parent = $member->expenseRequest;
                $members[] = [
                    'id'             => (int) $member->id,
                    'ref'            => $parent?->reference_no,
                    'date'           => $parent?->date?->format('Y-m-d'),
                    'user'           => $parent?->user?->name ?? $parent?->user?->username,
                    'status_label'   => $member->statusLabel(),
                    'status_badge'   => $member->statusBadge(),
                    'branch'         => $member->branch?->name ?? $parent?->branch?->name,
                    'applicant'      => $member->applicant
                        ? $member->applicant->last_name . ', ' . $member->applicant->first_name
                        : null,
                    'agent'          => $member->agent?->name,
                    'currency'       => $member->currency,
                    'amount'         => (float) $member->amount,
                    'payment'        => (float) ($member->payment ?? 0),
                    'account'        => $member->account?->name,
                    'country'        => $member->country?->name,
                    'particular'     => $member->particular,
                    'charge'         => $member->charge,
                    'created_at'     => $member->created_at?->format('Y-m-d H:i'),
                    'is_original'    => $index === 0,
                ];
            }

            $duplicateGroups[$groupKey] = [
                'ref'   => $first->expenseRequest?->reference_no,
                'items' => $members,
            ];

            foreach (array_slice($group, 1) as $repeat) {
                $duplicateItemIds[] = (int) $repeat->id;
                $duplicateOrigins[(int) $repeat->id] = [
                    'ref'          => $first->expenseRequest?->reference_no,
                    'same_request' => (int) $first->expense_request_id === (int) $repeat->expense_request_id,
                ];
                $duplicateGroupByItem[(int) $repeat->id] = $groupKey;
            }
        }

        return view('expense_request.index', [
            'requests'         => $requests,
            'allRequests'      => $allRequests,
            'activeStatus'     => $activeStatus,
            'activeSearch'     => $activeSearch,
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
            'cancelledPhpTotal'     => $totals['status']['cancelled']['PHP'],
            'cancelledUsdTotal'     => $totals['status']['cancelled']['USD'],
            'statusTotals'     => $statusTotals,
            'duplicateItemIds' => $duplicateItemIds,
            'duplicateOrigins' => $duplicateOrigins,
            'duplicateGroups'  => $duplicateGroups,
            'duplicateGroupByItem' => $duplicateGroupByItem,
            'encoders'         => $encoders,
            'activeEncoder'    => $activeEncoder,
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
     * Fingerprint for the list's duplicate highlight (Cyd 2026-10-23): same
     * date, charge, branch, agent, applicant, account, currency, amount and
     * payment. Description and country are deliberately NOT part of the key:
     * the re-uploaded batch in Gulf arrived with those two columns blank, so
     * matching on them hid the 62 genuine duplicate rows.
     */
    private function itemFingerprint(ExpenseRequest $requestModel, ExpenseRequestItem $item): string
    {
        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        return implode('|', [
            $requestModel->date?->format('Y-m-d') ?? '',
            $item->charge,
            $requestModel->branch_id ?? 'null',
            $item->agent_id ?? 'null',
            $item->applicant_id ?? 'null',
            $item->account_id,
            strtoupper((string) $item->currency),
            number_format((float) $item->amount, 2, '.', ''),
            number_format((float) ($item->payment ?? 0), 2, '.', ''),
        ]);
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
        if ($this->branchLocked($user)) {
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
        ) + [
            // CYD 2026-11-04: Main Office / Main Branch users can tag each line
            // with its own branch. Branch-locked users never see the field.
            'showLineBranch' => ! $this->branchLocked($user),
        ]);
    }

    /**
     * Edit form for an existing expense request (Cyd 2026-11-01: Admin +
     * Accounting may edit Receivable and Expenses & Payments transactions).
     * Reuses the create screen — it is pre-filled with the saved lines.
     */
    public function edit(ExpenseRequest $expenseRequest): View
    {
        $this->authorizeAgency($expenseRequest);

        $user = auth()->user();
        if (! $user->canEditTransactions()) {
            abort(403, 'Only Admin and Accounting can edit transactions.');
        }
        if ($this->branchLocked($user) && $expenseRequest->branch_id !== null
            && (int) $expenseRequest->branch_id !== (int) $user->branch_id) {
            abort(403, 'You can only edit expense requests for your own branch.');
        }

        $agencyId = auth()->user()->agency_id;

        $branches = Branch::where('agency_id', $agencyId);
        $agents = Agent::where('agency_id', $agencyId)->with('branch');
        if ($this->branchLocked($user)) {
            $branches->where('id', $user->branch_id);
            $agents->where(function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id)->orWhereNull('branch_id');
            });
        }
        $branches = $branches->orderBy('name')->get();
        $agents = $agents->orderBy('name')->get();
        $applicants = Applicant::where('agency_id', $agencyId)->orderBy('last_name')->get(['id', 'agent_id', 'first_name', 'last_name']);
        $countries = Country::orderBy('name')->get();

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

        $existingLines = $expenseRequest->items->map(function (ExpenseRequestItem $item) {
            return [
                'id'           => $item->id,
                'charge'       => $item->charge,
                'agent_id'     => $item->agent_id,
                'applicant_id' => $item->applicant_id,
                'country_id'   => $item->country_id,
                'currency'     => $item->currency,
                'amount'       => $item->amount,
                'payment'      => $item->payment,
                'particular'   => $item->particular,
                'account_id'   => $item->account_id,
                // Toybits 2026-11-04: the line partial reads sub_account_id, so the
                // Account Type picker (and the Payments visibility) now preselects
                // correctly when editing an existing transaction.
                'sub_account_id' => $item->account_id,
                'branch_id'    => $item->branch_id,
                'has_file'     => (bool) $item->file_path,
            ];
        })->all();

        return view('expense_request.create', compact(
            'branches', 'agents', 'applicants', 'countries', 'mains', 'allAccounts'
        ) + [
            'record'         => $expenseRequest,
            'isEdit'         => true,
            'existingLines'  => $existingLines,
            'showLineBranch' => ! $this->branchLocked($user),
        ]);
    }

    /**
     * Save an edited expense request: header + line items (add / update / remove).
     * The reference number, encoder and status roll-up stay untouched.
     */
    public function update(Request $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorizeAgency($expenseRequest);

        $user = auth()->user();
        if (! $user->canEditTransactions()) {
            abort(403, 'Only Admin and Accounting can edit transactions.');
        }
        if ($this->branchLocked($user) && $expenseRequest->branch_id !== null
            && (int) $expenseRequest->branch_id !== (int) $user->branch_id) {
            abort(403, 'You can only edit expense requests for your own branch.');
        }

        $agencyId = $user->agency_id;

        $validated = $request->validate([
            'date'      => ['nullable', 'date'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'notes'     => ['nullable', 'string'],
            'lines'     => ['required', 'array', 'min:1'],
            'lines.*.id'              => ['nullable', 'integer'],
            'lines.*.charge'          => ['required', 'in:office,agent'],
            'lines.*.branch_id'       => ['nullable', 'integer', Rule::exists('branches', 'id')->where('agency_id', $agencyId)],
            'lines.*.main_account_id' => ['nullable', 'integer', 'exists:accounts,id'],
            'lines.*.sub_account_id'  => ['nullable', 'integer', 'exists:accounts,id'],
            'lines.*.agent_id'        => ['nullable', 'integer', 'exists:agents,id'],
            'lines.*.applicant_id'    => ['nullable', 'integer', 'exists:applicants,id'],
            'lines.*.country_id'      => ['nullable', 'integer', 'exists:countries,id'],
            'lines.*.currency'        => ['required', 'in:PHP,USD'],
            'lines.*.amount'          => ['required', 'numeric', 'min:0.01'],
            'lines.*.payment'         => ['nullable', 'numeric', 'min:0'],
            'lines.*.particular'      => ['nullable', 'string'],
            'lines.*.file'            => ['nullable', 'file', 'max:5120'],
        ]);

        $branchId = $validated['branch_id'] ?? null;

        if ($this->branchLocked($user)) {
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
            DB::transaction(function () use ($request, $validated, $agencyId, $branchId, $expenseRequest) {
                $expenseRequest->update([
                    'date'      => $validated['date'] ?? $expenseRequest->date,
                    'branch_id' => $branchId,
                    'notes'     => $validated['notes'] ?? null,
                ]);

                $keptIds = [];

                foreach ($validated['lines'] as $index => $line) {
                    // Same account-group rules as store(): the account must match
                    // the charge (office / agent) and, for office lines, the
                    // presence of an applicant.
                    $group = $line['charge'] === 'agent'
                        ? 'agent'
                        : (! empty($line['applicant_id']) ? 'applicant' : 'office');

                    $main = null;
                    if (! empty($line['main_account_id'])) {
                        $main = Account::where('agency_id', $agencyId)->find($line['main_account_id']);
                    } else {
                        // No explicit main in the payload: derive it from the
                        // sub-account (or the group's main) so edits that only
                        // touch the sub-account still resolve.
                        if (! empty($line['sub_account_id'])) {
                            $sub = Account::where('agency_id', $agencyId)->find($line['sub_account_id']);
                            $main = $sub?->parent_id
                                ? Account::where('agency_id', $agencyId)->find($sub->parent_id)
                                : null;
                        }
                        $main = $main ?: Account::mains()
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

                    if ($main->charge_type !== $group) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            "lines.$index.main_account_id" => 'Account type must match the charge (office/agent).',
                        ]);
                    }

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

                    if (! empty($line['applicant_id'])) {
                        $applicant = Applicant::where('agency_id', $agencyId)->find($line['applicant_id']);
                        $agentId = $line['agent_id'] ?? null;
                        if (! $applicant || ($agentId && $applicant->agent_id !== (int) $agentId)) {
                            throw \Illuminate\Validation\ValidationException::withMessages([
                                'lines.*.applicant_id' => 'Applicant must belong to the selected agent.',
                            ]);
                        }
                    }

                    $payload = [
                        // Per-line branch (main office users); defaults to the request's branch.
                        'branch_id'    => $line['branch_id'] ?? $branchId,
                        'charge'       => $line['charge'],
                        'agent_id'     => $line['agent_id'] ?? null,
                        'applicant_id' => $line['applicant_id'] ?? null,
                        'country_id'   => $line['country_id'] ?? null,
                        'currency'     => $line['currency'],
                        'amount'       => $line['amount'],
                        'payment'      => $line['payment'] ?? 0,
                        'account_id'   => $account->id,
                        'particular'   => $line['particular'] ?? null,
                    ];

                    $filePath = $this->storeLineFile($request, $index);
                    if ($filePath) {
                        $payload['file_path'] = $filePath;
                    }

                    $itemId = $line['id'] ?? null;
                    $item = $itemId
                        ? $expenseRequest->items()->whereKey($itemId)->first()
                        : null;

                    if ($item) {
                        $item->update($payload);
                        $keptIds[] = $item->id;
                    } else {
                        $created = $expenseRequest->items()->create($payload + [
                            'status' => ExpenseRequest::STATUS_PENDING,
                        ]);
                        $keptIds[] = $created->id;
                    }
                }

                // Lines dropped from the form are deleted from the request.
                $expenseRequest->items()->whereNotIn('id', $keptIds)->delete();
            });
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('expense_request.show', $expenseRequest)
            ->with('success', "Expense request {$expenseRequest->reference_no} updated.");
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
            'lines.*.branch_id'       => ['nullable', 'integer', Rule::exists('branches', 'id')->where('agency_id', $agencyId)],
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
        if ($this->branchLocked($user)) {
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
                        // Per-line branch (main office users); defaults to the request's branch.
                        'branch_id'          => $line['branch_id'] ?? $branchId,
                        'charge'             => $line['charge'],
                        'agent_id'           => $line['agent_id'] ?? null,
                        'applicant_id'       => $line['applicant_id'] ?? null,
                        'country_id'         => $line['country_id'] ?? null,
                        'currency'           => $line['currency'],
                        'amount'             => $line['amount'],
                        'payment'            => $line['payment'] ?? 0,
                        'status'             => ExpenseRequest::STATUS_PENDING,
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

    // -------------------------------------------------------------------
    // Bulk CSV upload (mirrors the Agent/Employer bulk flows): one CSV row
    // = one expense request with a single line item, created as Pending.
    // -------------------------------------------------------------------

    public function bulkUpload(): View
    {
        $agencyId = auth()->user()->agency_id;
        $user = auth()->user();

        // Same branch scope as create(): branch-locked users may only file
        // for their own branch.
        $branches = Branch::where('agency_id', $agencyId);
        if ($this->branchLocked($user)) {
            $branches->where('id', $user->branch_id);
        }
        $branches = $branches->orderBy('name')->get();

        // Reference account lists (grouped by charge) for the upload page.
        $mains = Account::mains()->with('children')
            ->where('agency_id', $agencyId)
            ->orderBy('name')
            ->get();

        return view('expense_request.bulk', compact('branches', 'mains'));
    }

    public function bulkTemplate()
    {
        // Column set + order mirror the agency's own Expense sheet
        // (Expense Template.csv on the Trello card "LANDAS: Expense &
        // Receivable Bulk Upload - Formats").
        //  - Ref #          : auto-generated per agency, ignored on import.
        //  - User           : encoder (matched by name/email; blank = uploading user).
        //  - Office         : same as the Branch column.
        //  - Request Status : pending | approved | for_releasing | released | cancelled
        //                     (blank = Pending).
        $headers = [
            'Ref #', 'User', 'Date', 'Charge', 'Account', 'Applicant', 'Agent',
            'Country', 'Currency', 'Amount', 'Payment', 'Particular/Description',
            'Office', 'Request Status',
        ];

        $today = now()->toDateString();
        $sampleRows = [
            [
                '', '', $today, 'Office', 'Rent', '', '',
                '', 'PHP', '1000.00', '0', 'Monthly office rent', '', 'Pending',
            ],
            [
                '', '', $today, 'Agent', 'Commission', '', 'Juan Dela Cruz',
                '', 'PHP', '2500.00', '', 'Agent commission payout', '', 'Pending',
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

            // Blank filler rows, like the agency's sheet, so there is room to
            // type without inserting rows in Excel.
            $blankRow = array_fill(0, count($headers), '');
            for ($i = 0; $i < 25; $i++) {
                fputcsv($file, $blankRow);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => 'attachment; filename=expense_requests_bulk_template.csv',
        ]);
    }

    public function bulkImport(Request $request): RedirectResponse
    {
        $agencyId = auth()->user()->agency_id;
        $user = auth()->user();

        if (! $agencyId) {
            return back()->withErrors(['csv_file' => 'No agency context. Please log in with an agency account to import expense requests.']);
        }

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
        ], [
            'csv_file.max' => 'The CSV file must be 20 MB or smaller. Split the file into smaller files if it is larger.',
        ]);

        $file = $request->file('csv_file');
        $rows = $this->parseBulkCsv($file->getRealPath());
        if (is_string($rows)) {
            return back()->withErrors(['csv_file' => $rows]);
        }

        // ---- Reference lookups (exact normalized matches, agency-scoped) ----
        $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

        $branchMap = [];
        foreach (Branch::where('agency_id', $agencyId)->get() as $branch) {
            $branchMap[$norm($branch->name)] = $branch->id;
        }

        // Selectable accounts, mirroring create()'s flat picker: children of
        // mains (labeled Main -> Child) + bare mains that have no children.
        $accounts = Account::where('agency_id', $agencyId)->get();
        $accountByKey = [];      // norm(key) => account id (scoped: 'main child' + bare names)
        $accountKeySource = [];  // norm(key) => display label
        $labelFor = function (Account $a) use ($accounts) {
            if ($a->isMain()) {
                return $a->name;
            }
            $main = $accounts->firstWhere('id', $a->parent_id);
            return $main ? $main->name.' → '.$a->name : $a->name;
        };
        foreach ($accounts as $account) {
            if ($account->isMain()) {
                if ($account->children->isEmpty()) {
                    $key = $norm($account->name);
                    if ($key !== '' && ! array_key_exists($key, $accountByKey)) {
                        $accountByKey[$key] = $account->id;
                        $accountKeySource[$key] = $account->name;
                    }
                }
                continue;
            }
            // Child: accept both "Main → Child" and the bare child name.
            foreach ([$labelFor($account), $account->name] as $label) {
                $key = $norm($label);
                if ($key === '' || array_key_exists($key, $accountByKey)) {
                    continue;
                }
                $accountByKey[$key] = $account->id;
                $accountKeySource[$key] = $label;
            }
        }
        // Preload for charge_type gating (child accounts carry their own charge_type).
        $accountById = $accounts->keyBy('id');

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
        $countryRows = Country::orderBy('name')->get(['id', 'name']);
        $countryByName = [];
        foreach ($countryRows as $country) {
            $countryByName[$norm($country->name)] = $country->id;
        }

        $errors = [];
        $validatedRows = [];
        $lockedBranchId = $this->branchLocked($user) ? (int) $user->branch_id : null;
        $lastBranchRaw = '';
        $inheritedBranchRows = 0;

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

            // ---- Charge (required: Office | Agent) ----
            $charge = trim((string) ($d['charge'] ?? ''));
            $chargeKey = mb_strtolower(preg_replace('/\s+/', '', $charge));
            if ($chargeKey === '' || ! in_array($chargeKey, ['office', 'agent'], true)) {
                $rowErrors[] = "Charge '{$charge}' not found. Use 'Office' or 'Agent'.";
                $charge = 'office';
            } else {
                $charge = $chargeKey;
            }

            // ---- Request Status (optional; blank = Pending) ----
            $status = $this->resolveBulkStatus($d['status'] ?? '', $rowErrors);

            // ---- User / encoder (optional; blank or unknown = importing user) ----
            $encoderId = $this->resolveBulkUser($d['user'] ?? '', (int) $agencyId);

            // ---- Branch (by name; optional) ----
            // Excel merged cells export the branch only on the first row of the
            // group, leaving the rest blank. Carry the last non-blank Office
            // value down so those rows keep their branch instead of silently
            // importing with none. (Cyd 2026-11-02)
            $branchId = null;
            $branchRaw = trim((string) ($d['branch'] ?? ''));
            if ($branchRaw === '' && $lastBranchRaw !== '') {
                $branchRaw = $lastBranchRaw;
                $inheritedBranchRows++;
            } elseif ($branchRaw !== '') {
                $lastBranchRaw = $branchRaw;
            }
            if ($branchRaw !== '') {
                $key = $norm($branchRaw);
                if (isset($branchMap[$key])) {
                    $branchId = $branchMap[$key];
                } else {
                    $available = collect(array_keys($branchMap))->take(6)->map(fn ($k) => ucwords($k))->implode(', ');
                    $rowErrors[] = "Branch '{$branchRaw}' not found.".($available !== '' ? " Available: {$available}" : '');
                }
            }
            if ($lockedBranchId) {
                if (! $branchId) {
                    $branchId = $lockedBranchId;
                } elseif ((int) $branchId !== $lockedBranchId) {
                    $rowErrors[] = 'You can only file expense requests for your own branch.';
                }
            }

            // ---- Agent (by name; optional) ----
            $agentId = null;
            $agentRaw = trim((string) ($d['agent'] ?? ''));
            if ($agentRaw !== '') {
                $matches = $agentByName[$norm($agentRaw)] ?? [];
                if (count($matches) === 0) {
                    $rowErrors[] = "Agent '{$agentRaw}' not found.";
                } elseif (count($matches) > 1) {
                    $rowErrors[] = "Multiple agents match '{$agentRaw}'. Use the exact full name.";
                } else {
                    $agent = $matches[0];
                    if ($branchId && $agent->branch_id && (int) $agent->branch_id !== (int) $branchId) {
                        $rowErrors[] = "Agent '{$agentRaw}' does not belong to the selected branch.";
                    } else {
                        $agentId = $agent->id;
                    }
                }
            }

            // ---- Applicant (by name; optional) ----
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

            // ---- Country (by name; optional) ----
            $countryId = null;
            $countryRaw = trim((string) ($d['country'] ?? ''));
            if ($countryRaw !== '') {
                $key = $norm($countryRaw);
                if (isset($countryByName[$key])) {
                    $countryId = $countryByName[$key];
                } else {
                    $rowErrors[] = "Country '{$countryRaw}' not found. Leave blank if not applicable.";
                }
            }

            // ---- Account (required; resolves the group's sub-account) ----
            // Group mirrors store(): charge=agent -> agent accounts;
            // charge=office + applicant -> applicant accounts; else office.
            $group = $charge === 'agent' ? 'agent' : ($applicantId ? 'applicant' : 'office');
            $accountId = null;
            $accountRaw = trim((string) ($d['account'] ?? ''));
            if ($accountRaw === '') {
                $rowErrors[] = 'Account is required.';
            } else {
                $key = $norm($accountRaw);
                if (isset($accountByKey[$key])) {
                    $account = $accountById[$accountByKey[$key]];
                    $chargeType = $account->charge_type ?? 'office';
                    if ($chargeType !== $group) {
                        $rowErrors[] = "Account type must match the charge (office/agent). Account '{$accountRaw}' is a {$chargeType} account.";
                    } else {
                        $accountId = $account->id;
                    }
                } else {
                    $available = collect(array_keys($accountKeySource))->take(6)->map(fn ($k) => $accountKeySource[$k])->implode(', ');
                    $rowErrors[] = "Account '{$accountRaw}' not found.".($available !== '' ? " Available: {$available}..." : '');
                }
            }

            // ---- Currency (required: PHP | USD) ----
            $currency = strtoupper(trim((string) ($d['currency'] ?? '')));
            if (! in_array($currency, ['PHP', 'USD'], true)) {
                $rowErrors[] = "Currency '{$currency}' not found. Use 'PHP' or 'USD'.";
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

            // ---- Payment (optional, >= 0) ----
            $payment = null;
            $paymentRaw = trim((string) ($d['payment'] ?? ''));
            if ($paymentRaw !== '') {
                if (! is_numeric($paymentRaw)) {
                    $rowErrors[] = "Payment '{$paymentRaw}' must be a number.";
                } elseif ((float) $paymentRaw < 0) {
                    $rowErrors[] = 'Payment cannot be negative.';
                } else {
                    $payment = $paymentRaw;
                }
            }

            // ---- Particular / Notes (free text, but never unbounded) ----
            // Cyd 2026-11-01: a 300+ char particular used to hit MySQL's
            // "Data too long for column" and surface as a 500 during import.
            $particular = trim((string) ($d['particular'] ?? ''));
            $notes = trim((string) ($d['notes'] ?? ''));
            if (mb_strlen($particular) > 5000) {
                $rowErrors[] = 'Particular is too long (' . mb_strlen($particular) . ' characters) — keep it under 5,000.';
            }
            if (mb_strlen($notes) > 5000) {
                $rowErrors[] = 'Notes are too long (' . mb_strlen($notes) . ' characters) — keep them under 5,000.';
            }

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $validatedRows[] = [
                'date'         => $date,
                'status'       => $status,
                'user_id'      => $encoderId,
                'charge'       => $charge,
                'branch_id'    => $branchId,
                'agent_id'     => $agentId,
                'applicant_id' => $applicantId,
                'country_id'   => $countryId,
                'account_id'   => $accountId,
                'currency'     => $currency,
                'amount'       => $amountRaw,
                'payment'      => $payment,
                'particular'   => $particular !== '' ? $particular : null,
                'notes'        => $notes !== '' ? $notes : null,
            ];
        }

        if (count($validatedRows) > 2000) {
            return back()->withErrors(['csv_file' => 'Too many rows: the file has more than 2,000 expense request rows. Split it into smaller files.']);
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        // ---- Duplicate protection (Cyd 2026-10-23) ----
        // Re-uploading the same sheet used to create a second full copy of every
        // row (Jerry's 62-row JERRY.xlsx became 124 rows after a repeat upload).
        // Every row is fingerprinted and count-matched against what already
        // exists for the agency, so a repeat upload is a no-op instead of a
        // double entry. Count-matching (instead of a flat "already exists" test)
        // keeps genuinely repeated rows inside one file importable.
        $fingerprint = function (array $data, ?string $date = null): string {
            $norm = fn ($v) => mb_strtolower(preg_replace('/\s+/', ' ', trim((string) $v)));

            return implode('|', [
                $date ?? $data['date'],
                $data['charge'],
                $data['branch_id'] ?? 'null',
                $data['agent_id'] ?? 'null',
                $data['applicant_id'] ?? 'null',
                $data['country_id'] ?? 'null',
                $data['account_id'],
                strtoupper((string) $data['currency']),
                number_format((float) $data['amount'], 2, '.', ''),
                number_format((float) ($data['payment'] ?? 0), 2, '.', ''),
                $norm($data['particular'] ?? ''),
                $norm($data['notes'] ?? ''),
            ]);
        };

        $fileDates = array_values(array_unique(array_column($validatedRows, 'date')));
        $fileAccounts = array_values(array_unique(array_column($validatedRows, 'account_id')));

        // Only rows that could collide with this file (same agency, same dates,
        // same accounts) — cancelled requests never block a re-import.
        $existingCounts = [];
        $existingItems = ExpenseRequestItem::query()
            ->whereHas('expenseRequest', fn ($q) => $q
                ->where('agency_id', $agencyId)
                // DATE() so this matches on MySQL datetimes and SQLite alike.
                ->whereIn(DB::raw('DATE(date)'), $fileDates)
                ->where('status', '!=', ExpenseRequest::STATUS_CANCELLED))
            ->whereIn('account_id', $fileAccounts)
            ->get(['expense_request_id', 'charge', 'agent_id', 'applicant_id', 'country_id', 'account_id', 'currency', 'amount', 'payment', 'particular']);

        if ($existingItems->isNotEmpty()) {
            $itemDates = ExpenseRequest::whereIn('id', $existingItems->pluck('expense_request_id'))
                ->pluck('date', 'id')
                ->map(fn ($d) => $d instanceof \DateTimeInterface ? $d->format('Y-m-d') : mb_substr((string) $d, 0, 10));

            foreach ($existingItems as $item) {
                $key = $fingerprint([
                    'charge'       => $item->charge,
                    'branch_id'    => null,
                    'agent_id'     => $item->agent_id,
                    'applicant_id' => $item->applicant_id,
                    'country_id'   => $item->country_id,
                    'account_id'   => $item->account_id,
                    'currency'     => $item->currency,
                    'amount'       => $item->amount,
                    'payment'      => $item->payment,
                    'particular'   => $item->particular,
                    'notes'        => null,
                ], (string) ($itemDates[$item->expense_request_id] ?? ''));

                $existingCounts[$key] = ($existingCounts[$key] ?? 0) + 1;
            }
        }

        $incomingByKey = [];
        foreach ($validatedRows as $data) {
            $incomingByKey[$fingerprint($data)][] = $data;
        }

        $rowsToInsert = [];
        $skipped = 0;
        foreach ($incomingByKey as $key => $rowsForKey) {
            $alreadyOnFile = $existingCounts[$key] ?? 0;
            $matched = 0;
            foreach ($rowsForKey as $data) {
                if ($matched < $alreadyOnFile) {
                    $matched++;
                    $skipped++;
                    continue;
                }
                $rowsToInsert[] = $data;
            }
        }

        if (empty($rowsToInsert)) {
            $total = count($validatedRows);

            return redirect()->route('expense_request.index')
                ->with('error', "Nothing imported: all {$total} row(s) in this file already exist and were skipped as duplicates.");
        }

        // ---- All valid, non-duplicate rows: insert in one transaction (all-or-nothing) ----
        $count = count($rowsToInsert);
        try {
            DB::transaction(function () use ($rowsToInsert, $agencyId) {
            foreach ($rowsToInsert as $data) {
                $parent = ExpenseRequest::create([
                    'agency_id'    => $agencyId,
                    'user_id'      => $data['user_id'] ?? auth()->id(),
                    'reference_no' => $this->nextReference($agencyId),
                    'date'         => $data['date'],
                    'status'       => $data['status'],
                    'branch_id'    => $data['branch_id'],
                    'notes'        => $data['notes'],
                ]);

                ExpenseRequestStatusHistory::create([
                    'expense_request_id' => $parent->id,
                    'agency_id'          => $agencyId,
                    'user_id'            => auth()->id(),
                    'from_status'        => null,
                    'to_status'          => $data['status'],
                    'note'               => $data['status'] === ExpenseRequest::STATUS_PENDING
                        ? 'Request created'
                        : 'Imported from CSV with status '.ExpenseRequest::STATUS_LABELS[$data['status']],
                ]);

                ExpenseRequestItem::create([
                    'expense_request_id' => $parent->id,
                    'charge'             => $data['charge'],
                    'agent_id'           => $data['agent_id'],
                    'applicant_id'       => $data['applicant_id'],
                    'country_id'         => $data['country_id'],
                    'currency'           => $data['currency'],
                    'amount'             => $data['amount'],
                    'payment'            => $data['payment'] ?? 0,
                    'status'             => $data['status'],
                    'account_id'         => $data['account_id'],
                    'particular'         => $data['particular'],
                    'file_path'          => null,
                ]);
                }
            });
        } catch (\Illuminate\Database\QueryException $e) {
            // Never leave the user on a bare 500: report the database reason.
            report($e);

            return back()->withErrors([
                'csv_file' => 'Nothing was imported — the database rejected a row. '
                    . 'Check for text that is too long or a missing account, then try again.',
            ])->withInput();
        }

        $message = "Bulk upload complete: {$count} expense request(s) imported from CSV.";
        if ($skipped > 0) {
            $message .= " {$skipped} duplicate row(s) skipped (already on file).";
        }
        if ($inheritedBranchRows > 0) {
            $message .= " {$inheritedBranchRows} row(s) had a blank Office cell and inherited the branch from the row above.";
        }

        return redirect()->route('expense_request.index')->with('success', $message);
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

        $aliases = $this->bulkHeaderAliases();

        // Stream the file row by row instead of materialising every row first.
        // A 20 MB export used to build a multi-hundred-MB array here and kill
        // PHP with "Allowed memory size exhausted" (HTTP 500). (Fixed 2026-11-03)
        $headerIndex = null;
        $fieldByIndex = [];
        $rows = [];
        $rowIndex = 0;
        $dataLines = 0;
        $sawAnyRow = false;
        $firstNonBlank = null;

        while (($cells = fgetcsv($handle, 0, $delimiter)) !== false) {
            $sawAnyRow = true;
            $isBlank = empty(array_filter(array_map('trim', $cells), fn ($c) => $c !== ''));

            // ---- Find the header row: skip blank lines and stray title lines ----
            if ($headerIndex === null) {
                if ($rowIndex >= 30) {
                    break; // header must appear in the first 30 rows
                }
                if (! $isBlank) {
                    if ($firstNonBlank === null && $rowIndex < 5) {
                        $firstNonBlank = implode(' | ', array_map(fn ($c) => trim((string) $c), $cells));
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
                    if (in_array('charge', $fields, true) && in_array('account', $fields, true)) {
                        $headerIndex = $rowIndex;
                        $fieldByIndex = $candidate;
                    }
                }
                $rowIndex++;
                continue;
            }

            // ---- Data rows = everything after the header row ----
            $rowIndex++;
            $data = [];
            foreach ($fieldByIndex as $idx => $field) {
                $data[$field] = trim((string) ($cells[$idx] ?? ''));
            }
            if (implode('', array_values($data)) === '') {
                continue; // skip blank lines
            }
            $dataLines++;
            if ($dataLines > 2000) {
                fclose($handle);

                return 'Too many rows: the file exceeds 2,000 expense request rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $rowIndex, 'data' => $data];
        }
        fclose($handle);

        if (! $sawAnyRow) {
            return 'The file has no readable rows.';
        }

        if ($headerIndex === null) {
            $preview = mb_substr($firstNonBlank ?? '(empty file)', 0, 160);

            return 'No header row found. The file must have a header row containing "Charge" and "Account" columns (use the downloaded template and keep its first row unchanged). What the parser saw first: "'.$preview.'". If you added a title or blank line above the header, remove it, or just re-download the template and fill it in.';
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
    }

    /**
     * Map the template's Request Status column onto an expense request status.
     * Blank means Pending, the default for a freshly imported row.
     */
    private function resolveBulkStatus($raw, array &$rowErrors): string
    {
        $raw = trim((string) $raw);
        if ($raw === '') {
            return ExpenseRequest::STATUS_PENDING;
        }

        $key = mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', $raw));
        $map = [
            'pending'      => ExpenseRequest::STATUS_PENDING,
            'open'         => ExpenseRequest::STATUS_PENDING,
            'approved'     => ExpenseRequest::STATUS_APPROVED,
            'forreleasing' => ExpenseRequest::STATUS_FOR_RELEASING,
            'releasing'    => ExpenseRequest::STATUS_FOR_RELEASING,
            'released'     => ExpenseRequest::STATUS_RELEASED,
            'paid'         => ExpenseRequest::STATUS_RELEASED,
            'cancelled'    => ExpenseRequest::STATUS_CANCELLED,
            'canceled'     => ExpenseRequest::STATUS_CANCELLED,
        ];

        if (isset($map[$key])) {
            return $map[$key];
        }

        $rowErrors[] = "Request Status '{$raw}' not found. Use Pending, Approved, For Releasing, Released or Cancelled (or leave blank for Pending).";

        return ExpenseRequest::STATUS_PENDING;
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

    /**
     * Map normalized header labels to canonical expense request fields.
     *
     * @return array<string, string>
     */
    private function bulkHeaderAliases(): array
    {
        return [
            'date' => 'date',
            'transactiondate' => 'date',
            // Display columns from the agency's sheet: Ref # is generated by the
            // app; User / Request Status / Office are honoured when filled.
            'ref' => 'ref',
            'refno' => 'ref',
            'referenceno' => 'ref',
            'user' => 'user',
            'encoder' => 'user',
            'encodedby' => 'user',
            'status' => 'status',
            'requeststatus' => 'status',
            'office' => 'branch',
            'officename' => 'branch',
            'charge' => 'charge',
            'chargetype' => 'charge',
            'account' => 'account',
            'accountname' => 'account',
            'subaccount' => 'account',
            'applicant' => 'applicant',
            'applicantname' => 'applicant',
            'agent' => 'agent',
            'agentname' => 'agent',
            'country' => 'country',
            'countryname' => 'country',
            'currency' => 'currency',
            'cur' => 'currency',
            'amount' => 'amount',
            'payment' => 'payment',
            'paid' => 'payment',
            'particular' => 'particular',
            'particulars' => 'particular',
            'particulardescription' => 'particular',
            'particularsdescription' => 'particular',
            'description' => 'particular',
            'branch' => 'branch',
            'branchname' => 'branch',
            'notes' => 'notes',
            'note' => 'notes',
            'remarks' => 'notes',
        ];
    }

    private function normKey($value): string
    {
        return mb_strtolower(preg_replace('/[^a-z0-9]+/i', '', (string) $value));
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
            // Toybits 2026-09-23: cancelled now gets its own per-status total so
            // the dashboard can display it, but it never counts toward the
            // grand (PHP/USD/charge) totals.
            'cancelled'     => ['PHP' => 0.0, 'USD' => 0.0],
        ];

        foreach ($requests as $requestModel) {
            $isCancelled = $requestModel->status === ExpenseRequest::STATUS_CANCELLED;

            $statusKey = in_array($requestModel->status, ['approved', 'for_releasing', 'released'], true)
                ? $requestModel->status
                : 'pending';

            foreach ($requestModel->items as $item) {
                $isUsd = $item->currency === 'USD';
                $amount = (float) $item->amount;

                // Cancelled transactions are rejected from the grand totals but
                // still accumulate into their own status bucket.
                if ($isCancelled) {
                    $status['cancelled'][$isUsd ? 'USD' : 'PHP'] += $amount;
                    continue;
                }

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
                'cancelled'     => ['PHP' => round($status['cancelled']['PHP'], 2), 'USD' => round($status['cancelled']['USD'], 2)],
            ],
        ];
    }

    /**
     * Review page: admin-only status change + transaction history.
     */
    public function show(ExpenseRequest $expenseRequest): View
    {
        $this->authorizeAgency($expenseRequest);
        $this->authorizeBranch($expenseRequest);

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
        $this->authorizeBranch($expenseRequest);
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
     * Admin-only batch status change (Toybits 2026-08-31), per transaction
     * (Cyd 2026-10-23): one expense request can hold several transactions and
     * they are approved one at a time, so the list posts the selected line-item
     * ids (`item_ids[]`) instead of request ids. Request ids are still accepted
     * (and used by the "no line items" rows) and mean "every transaction of
     * that request". Each touched request gets one history entry and the
     * request status is rolled up from its transactions afterwards.
     */
    public function bulkUpdateStatus(Request $request): RedirectResponse
    {
        $this->authorizeStatusChange();

        $validated = $request->validate([
            'ids'        => ['required_without:item_ids', 'array', 'min:1'],
            'ids.*'      => ['integer'],
            'item_ids'   => ['required_without:ids', 'array', 'min:1'],
            'item_ids.*' => ['integer'],
            'status'     => ['required', 'in:' . implode(',', ExpenseRequest::STATUSES)],
            'note'       => ['nullable', 'string'],
        ]);

        $to   = $validated['status'];
        $note = $validated['note'] ?? null;

        $itemIds    = array_values(array_filter((array) ($validated['item_ids'] ?? [])));
        $requestIds = array_values(array_filter((array) ($validated['ids'] ?? [])));

        if (empty($itemIds) && empty($requestIds)) {
            return redirect()->route('expense_request.index')
                ->with('error', 'Select at least one transaction.');
        }

        // Only the caller's own agency's transactions are ever touched (plus,
        // for branch-locked users, only their own branch's).
        $items = ExpenseRequestItem::query()
            ->whereHas('expenseRequest', function ($q) {
                $q->where('agency_id', auth()->user()->agency_id)
                    ->when($this->branchLocked(), fn ($qq) => $qq->where('branch_id', auth()->user()->branch_id));
            })
            ->where(function ($q) use ($itemIds, $requestIds) {
                if (! empty($itemIds)) {
                    $q->whereIn('id', $itemIds);
                }
                if (! empty($requestIds)) {
                    // Request-level selection (rows with no line items yet) means
                    // "every transaction of this request".
                    $q->orWhereIn('expense_request_id', $requestIds);
                }
            })
            ->get();

        if ($items->isEmpty()) {
            return redirect()->route('expense_request.index')
                ->with('error', 'No matching transactions selected.');
        }

        $changed = 0;
        $wholeRequests = true;

        DB::transaction(function () use ($items, $to, $note, &$changed, &$wholeRequests) {
            foreach ($items->groupBy('expense_request_id') as $requestId => $group) {
                $expenseRequest = ExpenseRequest::find($requestId);
                if (! $expenseRequest) {
                    continue;
                }

                $from = $expenseRequest->status;

                // Request already in the target status: nothing to do. Repair any
                // drifted line-item statuses silently (no history, no counter).
                if ($from === $to) {
                    $expenseRequest->items()->where('status', '!=', $to)->update(['status' => $to]);

                    continue;
                }

                $touched = 0;

                if ($group->count() < $expenseRequest->items()->count()) {
                    $wholeRequests = false; // only part of this request's transactions
                }

                foreach ($group as $item) {
                    if ($item->status === $to) {
                        continue;
                    }
                    $item->update(['status' => $to]);
                    $this->syncPaidEntryForItem($expenseRequest, $item, $to);
                    $touched++;
                }

                if ($touched === 0) {
                    continue;
                }

                $changed += $touched;
                $this->rollupRequestStatus($expenseRequest);

                ExpenseRequestStatusHistory::create([
                    'expense_request_id' => $expenseRequest->id,
                    'agency_id'          => $expenseRequest->agency_id,
                    'user_id'            => auth()->id(),
                    'from_status'        => $from,
                    'to_status'          => $expenseRequest->status,
                    'note'               => $note ?: ($touched === $group->count()
                        ? 'All transactions set to ' . ExpenseRequest::STATUS_LABELS[$to]
                        : $touched . ' transaction(s) set to ' . ExpenseRequest::STATUS_LABELS[$to]),
                ]);
            }
        });

        $message = $changed === 0
            ? "All selected requests were already {$to}."
            : ($wholeRequests
                ? "Updated {$changed} expense request(s) to {$to}."
                : "Updated {$changed} transaction(s) to " . (ExpenseRequest::STATUS_LABELS[$to] ?? $to) . '.');

        // Back to the same status tab the admin was viewing.
        return redirect()->back()
            ->with('success', $message);
    }

    /**
     * Roll a request's status up from its transactions (Cyd 2026-10-23).
     * Cancelled rows are ignored; if everything is cancelled the request is
     * cancelled; a mixed request shows the least-advanced remaining status so
     * it still reads as work in progress on the dashboards.
     */
    private function rollupRequestStatus(ExpenseRequest $expenseRequest): void
    {
        $statuses = $expenseRequest->items()->pluck('status')->all();

        if (empty($statuses)) {
            return;
        }

        $open = array_values(array_filter($statuses, fn ($s) => $s !== ExpenseRequest::STATUS_CANCELLED));

        if (empty($open)) {
            $rolled = ExpenseRequest::STATUS_CANCELLED;
        } else {
            $rank = array_flip([
                ExpenseRequest::STATUS_PENDING,
                ExpenseRequest::STATUS_APPROVED,
                ExpenseRequest::STATUS_FOR_RELEASING,
                ExpenseRequest::STATUS_RELEASED,
            ]);
            $rolled = $open[0];
            foreach ($open as $status) {
                if (($rank[$status] ?? 0) < ($rank[$rolled] ?? 0)) {
                    $rolled = $status;
                }
            }
        }

        if ($expenseRequest->status !== $rolled) {
            $expenseRequest->update(['status' => $rolled]);
        }
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

        // Whole-request changes (Review page) apply to every transaction, so the
        // line items never drift from the request. (Cyd 2026-10-23)
        $expenseRequest->items()->where('status', '!=', $to)->update(['status' => $to]);

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
     * Admin-only soft delete (Cyd 2026-10-24, card "Transactions: Expense and
     * Receivable 'Delete'"). The reason is kept on the status history so the
     * transaction can still be explained after it disappears from the lists.
     */
    public function destroy(Request $request, ExpenseRequest $expenseRequest): RedirectResponse
    {
        $this->authorizeAgency($expenseRequest);

        if (! in_array((string) auth()->user()->user_type, ['super_admin', 'admin'], true)) {
            abort(403, 'Only admin can delete expense transactions.');
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

            // Line items stay in place: agent paid entries reference them, and
            // the soft-deleted request already hides them from every list.
            $expenseRequest->delete();
        });

        return redirect()->route('expense_request.index')
            ->with('success', 'Expense transaction deleted.');
    }

    /**
     * Only admin/super_admin may change expense request statuses (single or batch).
     */
    private function authorizeStatusChange(): void
    {
        if (! auth()->user()->canChangeExpenseStatus()) {
            abort(403, 'Only admin and the privileged accounts can change expense request status.');
        }
    }

    /**
     * On approval, agent-charged transactions become Paid entries in the agent
     * report's Deductions & Paid tab (net = amount − payment, converted to PHP).
     * Cancelling removes them again. Synced per transaction (Cyd 2026-10-23) so
     * approving one line item of a multi-line request only pays that one out.
     */
    private function syncPaidEntriesFromApproval(ExpenseRequest $expenseRequest, string $to): void
    {
        foreach ($expenseRequest->items as $item) {
            $this->syncPaidEntryForItem($expenseRequest, $item, $to);
        }
    }

    /**
     * Sync a single transaction to the agent report's Paid/Deductions tab.
     */
    private function syncPaidEntryForItem(ExpenseRequest $expenseRequest, ExpenseRequestItem $item, string $to): void
    {
        if ($to === ExpenseRequest::STATUS_CANCELLED) {
            // forceDelete: the row is system-generated from the approval, so
            // cancelling must really remove it (AgentDeduction is soft-deleting
            // since Cyd 2026-11-01, which is only meant for manual admin deletes).
            AgentDeduction::withTrashed()
                ->where('expense_request_item_id', $item->id)
                ->forceDelete();

            return;
        }

        if ($to !== ExpenseRequest::STATUS_APPROVED) {
            return;
        }

        if ($item->charge !== 'agent' || ! $item->agent_id) {
            return; // only agent-charged transactions flow into the agent report
        }

        $converter = new CurrencyConverter();

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

    /**
     * Ensure the request belongs to the caller's agency (isolation).
     */
    private function authorizeAgency(ExpenseRequest $expenseRequest): void
    {
        if ((int) $expenseRequest->agency_id !== (int) auth()->user()->agency_id) {
            abort(404);
        }
    }

    /**
     * (Cyd 2026-09-26) Whether the current user is branch-locked for Expenses.
     * Main Office (head-office) users are NOT locked — they may pick any
     * branch. Everyone else assigned to a branch stays locked to their own.
     */
    private function branchLocked(?\App\Models\User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user) {
            return false;
        }

        if ($user->isMainOffice()) {
            return false;
        }

        return $user->isBranchLocked();
    }

    /**
     * (Branch feature) Branch-locked users may only view their own branch's
     * requests. Admins/main-office users pass regardless.
     */
    private function authorizeBranch(ExpenseRequest $expenseRequest): void
    {
        $user = auth()->user();
        if (! $user || ! $this->branchLocked($user)) {
            return;
        }

        if ((int) $expenseRequest->branch_id !== (int) $user->branch_id) {
            abort(404);
        }
    }

    /**
     * (Cyd 2026-11-04 card) Expense & Payment search.
     *
     * Matches the request header (reference, date, encoder) and any transaction
     * line (agent, applicant, particular, account, country, currency, amount,
     * payment). Header-only matches keep all their lines; so the row that is
     * highlighted by the search is always a full transaction, never half of it.
     */
    private function applySearch($query, string $term)
    {
        $like = '%' . addcslashes($term, '%_\\') . '%';

        return $query->where(function ($q) use ($like, $term) {
            $q->where('reference_no', 'like', $like)
                ->orWhereHas('user', function ($u) use ($like) {
                    $u->where('name', 'like', $like)->orWhere('username', 'like', $like);
                })
                ->orWhereHas('items', function ($i) use ($like) {
                    $i->where('particular', 'like', $like)
                        ->orWhere('currency', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('amount', 'like', $like)
                        ->orWhere('payment', 'like', $like)
                        ->orWhereHas('agent', fn ($a) => $a->where('name', 'like', $like))
                        ->orWhereHas('applicant', function ($a) use ($like) {
                            $a->where('first_name', 'like', $like)
                                ->orWhere('last_name', 'like', $like);
                        })
                        ->orWhereHas('account', fn ($acc) => $acc->where('name', 'like', $like))
                        ->orWhereHas('country', fn ($c) => $c->where('name', 'like', $like));
                });

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
