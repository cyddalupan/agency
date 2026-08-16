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
            ->with('branch')
            ->orderBy('name')
            ->get();

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

        // Only admin / super_admin may change status (per card: "Only Admin Account can change Status")
        if (! in_array(auth()->user()->user_type, ['super_admin', 'admin'])) {
            abort(403, 'Only admin can change receivable status.');
        }

        $validated = $request->validate([
            'status' => ['required', 'in:' . Receivable::STATUS_PENDING . ',' . Receivable::STATUS_RECEIVED],
            'note'   => ['nullable', 'string'],
        ]);

        $from = $receivable->status;
        $to   = $validated['status'];

        if ($from !== $to) {
            DB::transaction(function () use ($receivable, $from, $to, $request) {
                $receivable->update(['status' => $to]);

                ReceivableHistory::create([
                    'receivable_id' => $receivable->id,
                    'agency_id'     => $receivable->agency_id,
                    'user_id'       => auth()->id(),
                    'from_status'   => $from,
                    'to_status'     => $to,
                    'note'          => $request->input('note'),
                ]);
            });
        }

        return redirect()->route('receivable.show', $receivable)
            ->with('success', "Status updated to {$to}.");
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
