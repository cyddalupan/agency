<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Agency;
use App\Models\Branch;
use App\Models\User;
use App\Services\SensitiveActionLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AgentController extends Controller
{
    public function index(Request $request)
    {
        $query = Agent::with('agency');

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

        $agents = $query->orderBy('name')->paginate(print_per_page(20));

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
            'email'           => 'required|email|max:255|unique:agents,email',
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

        // Super admin can create agents without an agency (they can assign later)
        if (!$validated['agency_id'] && auth()->user()->user_type !== 'super_admin') {
            return back()->withErrors(['agency' => 'No agency context.'])->withInput();
        }

        // (Branch feature) Branch accounts may only create agents in their own
        // branch: omitted defaults to their branch, a different branch is rejected.
        $this->applyBranchDefaults($validated);

        Agent::create($validated);

        return redirect()->route('agents.index')
            ->with('success', 'Agent created successfully.');
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
            'email'           => ['required', 'email', 'max:255', Rule::unique('agents', 'email')->ignore($agent->id)],
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
}
