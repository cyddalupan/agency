<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Applicant;
use App\Models\Receivable;
use App\Models\ReceivableHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    /**
     * Tab 1 — Receivable list.
     */
    public function index(): View
    {
        $agencyId = auth()->user()->agency_id;

        $receivables = Receivable::with(['agent', 'applicant', 'encoder'])
            ->where('agency_id', $agencyId)
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->get();

        return view('receivable.index', [
            'receivables'   => $receivables,
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

        // Only the caller's own agency's receivables are ever touched.
        $receivables = Receivable::where('agency_id', auth()->user()->agency_id)
            ->whereIn('id', $validated['ids'])
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
}
