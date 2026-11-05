@extends('layouts.app')

@section('title', 'Agents')

@section('content')
<div class="w-full">
    <div class="flex items-center justify-between mb-6">
        <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 p-4 flex-1">
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🎯</span> Agents
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage referral agents and commission tracking.</p>
        </div>
        <a href="{{ route('agents.bulk') }}" class="btn btn-outline ml-4" title="Bulk upload agents from CSV">
            <span>📥</span> Bulk Upload
        </a>
        @php
            // Gulf-only destructive action (CYD 2026-11-04).
            $manageAgency = auth()->user()->agency ?? request()->attributes->get('tenant_agency');
            $canDeleteAll = $manageAgency
                && strtolower((string) $manageAgency->subdomain) === 'gulf'
                && in_array(auth()->user()->user_type, ['admin', 'super_admin'], true);

            // Per-row delete is available to administrators only (CYD 2026-11-04 card).
            $canDeleteAgent = in_array(auth()->user()->user_type, ['admin', 'super_admin'], true);
        @endphp
        @if($canDeleteAll)
            <button type="button" class="btn btn-error btn-outline ml-2"
                    onclick="document.getElementById('delete-all-agents').showModal()">
                <span>🗑️</span> Delete all agents
            </button>
        @endif
        <a href="{{ route('agents.create') }}" class="btn btn-primary ml-2">
            <span>➕</span> New Agent
        </a>
    </div>

    @if($canDeleteAll)
        <dialog id="delete-all-agents" class="modal">
            <div class="modal-box">
                <h3 class="font-bold text-lg">Delete all registered agents?</h3>
                <p class="text-sm opacity-70 mt-1">
                    This permanently deletes all <b>{{ $agents->total() }}</b> registered agent(s) of
                    <b>{{ $manageAgency->name }}</b>. This cannot be undone.
                </p>
                <form method="POST" action="{{ route('agents.delete_all') }}" class="mt-4 space-y-3">
                    @csrf
                    <label class="form-control w-full">
                        <span class="label-text mb-1">Type <b>DELETE ALL</b> (in capitals) to confirm</span>
                        <input type="text" name="confirmation" class="input input-bordered w-full"
                               required autocomplete="off" placeholder="DELETE ALL">
                    </label>
                    @error('confirmation')
                        <p class="text-error text-sm">{{ $message }}</p>
                    @enderror
                    <div class="modal-action">
                        <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Cancel</button>
                        <button type="submit" class="btn btn-error">Delete all agents</button>
                    </div>
                </form>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
    @endif

    @if (session('success'))
    <div role="alert" class="alert alert-success text-sm shadow-sm mb-4">
        <span>✅</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if (session('error'))
    <div role="alert" class="alert alert-error text-sm shadow-sm mb-4">
        <span>⛔</span>
        <span>{{ session('error') }}</span>
    </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('agents.index') }}" class="flex flex-wrap gap-3 items-end mb-4">
        <div class="form-control flex-1 min-w-40">
            <label class="input input-bordered flex items-center gap-2">
                <span>🔍</span>
                <input type="text" name="search" class="grow" placeholder="Search by name or email..."
                       value="{{ request('search') }}" />
            </label>
        </div>
        <div class="form-control w-full sm:w-36">
            <select name="status" class="select select-bordered" onchange="this.form.submit()">
                <option value="">📊 All Status</option>
                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>✅ Active</option>
                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>❌ Inactive</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
        @if(request('search') || request('status'))
            <a href="{{ route('agents.index') }}" class="btn btn-ghost btn-sm">✕ Clear</a>
        @endif
    </form>

    <div class="card bg-base-100 shadow-sm">
        <div class="flex justify-between items-center px-4 pt-4">
            <span class="text-sm opacity-60">{{ $agents->total() }} agent(s)</span>
            <a href="{{ route('reports.agents', request()->only(['search', 'status'])) }}" class="btn btn-sm btn-outline btn-info gap-1">
                <span>📊</span> View Report
            </a>
        </div>
        <div class="card-body p-0">
            @if ($agents->count() > 0)
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr>
                            <th>Name</th>
                            {{-- Branch sits right after Name (client request, 2026-11-05). --}}
                            <th>Branch</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Commission Rate</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($agents as $agent)
                        <tr>
                            <td class="font-medium">{{ $agent->name }}</td>
                            <td class="whitespace-nowrap">{{ $agent->branch?->name ?? '—' }}</td>
                            <td>{{ $agent->email }}</td>
                            <td>{{ $agent->contact ?? 'N/A' }}</td>
                            <td>{{ $agent->commission_rate ? $agent->commission_rate . '%' : 'N/A' }}</td>
                            <td>
                                @if ($agent->status === 'active')
                                    <span class="badge badge-success badge-sm">Active</span>
                                @else
                                    <span class="badge badge-error badge-sm">Inactive</span>
                                @endif
                            </td>
                            <td>{{ $agent->creator?->full_name ?? $agent->creator?->name ?? '—' }}</td>
                            <td class="whitespace-nowrap">{{ $agent->created_at?->format('M d, Y g:i A') ?? '—' }}</td>
                            <td>
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('agents.edit', $agent) }}" class="btn btn-ghost btn-xs">✏️ Edit</a>
                                    {{-- Admin-only delete (CYD 2026-11-04 card). --}}
                                    @if($canDeleteAgent)
                                        <button type="button" class="btn btn-xs btn-error btn-outline"
                                                onclick="document.getElementById('delete-agent-{{ $agent->id }}').showModal()">Delete</button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                {{ $agents->links() }}
            </div>
            @else
            <div class="text-center py-10 opacity-50">
                <span class="text-4xl block mb-3">📭</span>
                <p class="text-lg">No agents yet.</p>
                <p class="text-sm mt-1">Create an agent to start tracking referrals.</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Admin-only delete dialogs (CYD 2026-11-04 card). An agent that is still
         referenced by applicants/commissions/expenses is refused server-side. --}}
    @if($canDeleteAgent)
        @foreach ($agents as $agent)
            <dialog id="delete-agent-{{ $agent->id }}" class="modal">
                <div class="modal-box">
                    <h3 class="font-bold text-lg">Delete {{ $agent->name }}?</h3>
                    <p class="text-sm opacity-70 mt-1">
                        This permanently removes the agent. Agents that already have applicants,
                        commissions, deductions, expense lines, receivables or starting balances
                        cannot be deleted — set them to Inactive instead.
                    </p>
                    <form method="POST" action="{{ route('agents.destroy', $agent) }}" class="mt-4">
                        @csrf
                        @method('DELETE')
                        <div class="modal-action">
                            <button type="button" class="btn btn-ghost" onclick="this.closest('dialog').close()">Cancel</button>
                            <button type="submit" class="btn btn-error">Delete agent</button>
                        </div>
                    </form>
                </div>
                <form method="dialog" class="modal-backdrop"><button>close</button></form>
            </dialog>
        @endforeach
    @endif
</div>
@endsection
