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
            ->with(['items', 'user', 'branch'])
            ->when($activeEncoder, fn ($q) => $q->where('user_id', $activeEncoder))
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
            'cancelledPhpTotal'     => $totals['status']['cancelled']['PHP'],
            'cancelledUsdTotal'     => $totals['status']['cancelled']['USD'],
            'statusTotals'     => $statusTotals,
            'duplicateKeys'    => $duplicateKeys,
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
        $headers = [
            'Date', 'Charge', 'Account', 'Applicant', 'Agent', 'Country',
            'Currency', 'Amount', 'Payment', 'Particular', 'Branch', 'Notes',
        ];

        $today = now()->toDateString();
        $sampleRows = [
            [
                $today, 'Office', 'Rent', '', '', '',
                'PHP', '1000.00', '0', 'Monthly office rent', '', '',
            ],
            [
                $today, 'Agent', 'Commission', '', 'Juan Dela Cruz', '',
                'PHP', '2500.00', '', 'Agent commission payout', '', '',
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
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
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

            // ---- Branch (by name; optional) ----
            $branchId = null;
            $branchRaw = trim((string) ($d['branch'] ?? ''));
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

            if (! empty($rowErrors)) {
                $errors[] = ['line' => $line, 'errors' => $rowErrors];
                continue;
            }

            $validatedRows[] = [
                'date'         => $date,
                'charge'       => $charge,
                'branch_id'    => $branchId,
                'agent_id'     => $agentId,
                'applicant_id' => $applicantId,
                'country_id'   => $countryId,
                'account_id'   => $accountId,
                'currency'     => $currency,
                'amount'       => $amountRaw,
                'payment'      => $payment,
                'particular'   => trim((string) ($d['particular'] ?? '')) !== '' ? trim((string) $d['particular']) : null,
                'notes'        => trim((string) ($d['notes'] ?? '')) !== '' ? trim((string) $d['notes']) : null,
            ];
        }

        if (count($validatedRows) > 2000) {
            return back()->withErrors(['csv_file' => 'Too many rows: the file has more than 2,000 expense request rows. Split it into smaller files.']);
        }

        if (! empty($errors)) {
            return back()->with('bulk_errors', $errors)->withInput();
        }

        // ---- All rows valid: insert in one transaction (all-or-nothing) ----
        $count = count($validatedRows);
        DB::transaction(function () use ($validatedRows, $agencyId) {
            foreach ($validatedRows as $data) {
                $parent = ExpenseRequest::create([
                    'agency_id'    => $agencyId,
                    'user_id'      => auth()->id(),
                    'reference_no' => $this->nextReference($agencyId),
                    'date'         => $data['date'],
                    'status'       => ExpenseRequest::STATUS_PENDING,
                    'branch_id'    => $data['branch_id'],
                    'notes'        => $data['notes'],
                ]);

                ExpenseRequestStatusHistory::create([
                    'expense_request_id' => $parent->id,
                    'agency_id'          => $agencyId,
                    'user_id'            => auth()->id(),
                    'from_status'        => null,
                    'to_status'          => ExpenseRequest::STATUS_PENDING,
                    'note'               => 'Request created',
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
                    'account_id'         => $data['account_id'],
                    'particular'         => $data['particular'],
                    'file_path'          => null,
                ]);
            }
        });

        return redirect()->route('expense_request.index')
            ->with('success', "Bulk upload complete: {$count} expense request(s) imported from CSV.");
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

        // ---- Try each plausible delimiter; the first one that yields a valid
        // header row wins. ----
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
            if (in_array('charge', $fields, true) && in_array('account', $fields, true)) {
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

            return 'No header row found. The file must have a header row containing "Charge" and "Account" columns (use the downloaded template and keep its first row unchanged). What the parser saw first: "'.$preview.'". If you added a title or blank line above the header, remove it, or just re-download the template and fill it in.';
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
                return 'Too many rows: the file exceeds 2,000 expense request rows. Split it into smaller files.';
            }
            $rows[] = ['line' => $line, 'data' => $data];
        }

        if (empty($rows)) {
            return 'The file has no data rows below the header.';
        }

        return $rows;
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

        // Only the caller's own agency's requests are ever touched (plus, for
        // branch-locked users, only their own branch's requests).
        $requests = ExpenseRequest::where('agency_id', auth()->user()->agency_id)
            ->whereIn('id', $validated['ids'])
            ->when($this->branchLocked(), fn ($q) => $q->where('branch_id', auth()->user()->branch_id))
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
        if (! auth()->user()->canChangeExpenseStatus()) {
            abort(403, 'Only admin and the privileged accounts can change expense request status.');
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
}
