@extends('layouts.app')

@section('title', 'Expenses & Payments')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6 flex flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">💸 Expenses &amp; Payments</h1>
                <p class="opacity-80 mt-1">Receivable — Tab 2</p>
            </div>
            <a href="{{ route('expense_request.create') }}" class="btn btn-secondary btn-sm shadow-md">+ New Expense Request</a>
        </div>
    </div>

    {{-- Tab switcher: Tab 1 Receivable / Tab 2 Expenses & Payments --}}
    @if(in_array(auth()->user()->user_type ?? '', ['super_admin', 'admin', 'billing']))
        <div class="tabs tabs-boxed bg-base-200/60 mb-6 w-fit">
            <a href="{{ route('receivable.index') }}" class="tab text-sm">Tab 1 · Receivable</a>
            <span class="tab tab-active text-sm font-semibold">Tab 2 · Expenses &amp; Payments</span>
            <a href="{{ route('agent_report.index') }}" class="tab text-sm">Tab 3 · Agents Report</a>
        </div>
    @endif

    {{-- Summary --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">🧾 Requests</p>
                <p class="text-2xl font-bold">{{ $requests->count() }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">💰 PHP Total</p>
                <p class="text-2xl font-bold">₱{{ number_format($phpTotal, 2) }}</p>
                <p class="text-xs opacity-60">USD: ${{ number_format($usdTotal, 2) }} ≈ ₱{{ number_format($totalAmount, 2) }}</p>
                <div class="mt-2 pt-2 border-t border-base-200 text-xs opacity-70 space-y-0.5">
                    <p>⏳ Pending: ₱{{ number_format($pendingPhpTotal, 2) }} <span class="opacity-50">(USD ${{ number_format($pendingUsdTotal, 2) }})</span></p>
                    <p>✅ Received: ₱{{ number_format($receivedPhpTotal, 2) }} <span class="opacity-50">(USD ${{ number_format($receivedUsdTotal, 2) }})</span></p>
                    <p>🏢 Office: ₱{{ number_format($chargeTotals['office'] ?? 0, 2) }} · 🧑 Agent: ₱{{ number_format($chargeTotals['agent'] ?? 0, 2) }}</p>
                </div>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">✅ Received</p>
                <p class="text-2xl font-bold text-success">{{ $requests->where('status', 'received')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Success flash --}}
    @if(session('success'))
        <div class="alert alert-success shadow-md mb-4">
            <span>✅ {{ session('success') }}</span>
        </div>
    @endif

    {{-- Requests table --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3">Expense Requests</h3>
            @if($requests->count())
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="bg-base-200/70">
                                <th>Ref#</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Branch</th>
                                <th>Applicant</th>
                                <th>Agent</th>
                                <th>Currency</th>
                                <th class="text-right">Amount</th>
                                <th>Account</th>
                                <th>Country</th>
                                <th>Particular</th>
                                <th>Charge</th>
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']))
                                    <th>Review</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $request)
                                @foreach($request->items as $item)
                                    <tr>
                                        @if($loop->first)
                                            <td class="font-mono" rowspan="{{ $request->items->count() }}">{{ $request->reference_no }}</td>
                                            <td rowspan="{{ $request->items->count() }}">{{ $request->date?->format('Y-m-d') }}</td>
                                            <td rowspan="{{ $request->items->count() }}">{{ $request->user?->name ?? $request->user?->username ?? '—' }}</td>
                                            <td rowspan="{{ $request->items->count() }}">
                                                <span class="badge badge-sm {{ $request->status === 'received' ? 'badge-success' : 'badge-warning' }}">
                                                    {{ $request->status }}
                                                </span>
                                            </td>
                                        @endif
                                        @if($loop->first)
                                            <td rowspan="{{ $request->items->count() }}">{{ $request->branch?->name ?? '—' }}</td>
                                        @endif
                                        <td>{{ $item->applicant ? $item->applicant->last_name . ', ' . $item->applicant->first_name : '—' }}</td>
                                        <td>{{ $item->agent?->name ?? '—' }}</td>
                                        <td>{{ $item->currency }}</td>
                                        <td class="text-right font-semibold">
                                            {{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $item->amount, 2) }}
                                        </td>
                                        <td>{{ $item->account?->name ?? '—' }}</td>
                                        <td>{{ $item->country?->name ?? '—' }}</td>
                                        <td>{{ $item->particular ?? '—' }}</td>
                                        <td>
                                            <span class="badge badge-sm {{ $item->charge === 'office' ? 'badge-ghost' : 'badge-info' }}">
                                                {{ $item->charge }}
                                            </span>
                                        </td>
                                        @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']) && $loop->first)
                                            <td rowspan="{{ $request->items->count() }}">
                                                <div class="flex items-center gap-2">
                                                    <a href="{{ route('expense_request.show', $request) }}" class="link link-primary">Review</a>
                                                    @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
                                                        <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Delete"
                                                            onclick="document.getElementById('delete-expense-{{ $request->id }}').showModal()">🗑️</button>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @endforeach
                                @if($request->items->isEmpty())
                                    <tr>
                                        <td class="font-mono">{{ $request->reference_no }}</td>
                                        <td>{{ $request->date?->format('Y-m-d') }}</td>
                                        <td>{{ $request->user?->name ?? $request->user?->username ?? '—' }}</td>
                                        <td colspan="9" class="opacity-50">No line items</td>
                                    </tr>
                                @endif
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
                                    <dialog id="delete-expense-{{ $request->id }}" class="modal">
                                        <div class="modal-box">
                                            <h3 class="font-bold text-lg mb-2">Delete Expense Request</h3>
                                            <p>Are you sure you want to delete Ref# <strong>{{ $request->reference_no }}</strong>?</p>
                                            <p class="text-sm opacity-60 mt-2">A reason is required and will be recorded in the history log.</p>
                                            <form method="POST" action="{{ route('expense_request.destroy', $request) }}" class="mt-4 space-y-3">
                                                @csrf
                                                @method('DELETE')
                                                <textarea name="reason" rows="3" class="textarea textarea-bordered w-full"
                                                    placeholder="Reason for deletion (required)" required></textarea>
                                                <div class="modal-action">
                                                    <button class="btn btn-error">🗑️ Delete</button>
                                                    <button type="button" class="btn btn-ghost" onclick="document.getElementById('delete-expense-{{ $request->id }}').close()">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                        <form method="dialog" class="modal-backdrop">
                                            <button>close</button>
                                        </form>
                                    </dialog>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="opacity-60">No expense requests yet. <a href="{{ route('expense_request.create') }}" class="link link-primary">Create one</a>.</p>
            @endif
        </div>
    </div>
</div>
@endsection
