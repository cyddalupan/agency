@extends('layouts.app')

@section('title', 'Receivable Module')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6 flex flex-row items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold">🧾 Receivable</h1>
                <p class="opacity-80 mt-1">Receivable — Tab 1</p>
            </div>
            <a href="{{ route('receivable.create') }}" class="btn btn-secondary btn-sm shadow-md">+ New Receivable</a>
        </div>
    </div>

    {{-- Tab switcher: Tab 1 Receivable / Tab 2 Expenses & Payments --}}
    @if(in_array(auth()->user()->user_type ?? '', ['super_admin', 'admin', 'billing']))
        <div class="tabs tabs-boxed bg-base-200/60 mb-6 w-fit">
            <span class="tab tab-active text-sm font-semibold">Tab 1 · Receivable</span>
            <a href="{{ route('expense_request.index') }}" class="tab text-sm">Tab 2 · Expenses &amp; Payments</a>
            <a href="{{ route('agent_report.index') }}" class="tab text-sm">Tab 3 · Agents Report</a>
        </div>
    @endif

    {{-- Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">📄 Transactions</p>
                <p class="text-2xl font-bold">{{ $receivables->count() }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">💰 Total Amount</p>
                <p class="text-2xl font-bold">₱{{ number_format($totalAmount, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">✅ Received</p>
                <p class="text-2xl font-bold text-success">{{ $receivables->where('status', 'received')->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Receivables table --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3">Transactions</h3>
            @if($receivables->count())
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="bg-base-200/70">
                                <th>Code</th>
                                <th>Date</th>
                                <th>User</th>
                                <th>Status</th>
                                <th>Ref#/AR#</th>
                                <th>Applicant</th>
                                <th>Agent</th>
                                <th class="text-right">Amount</th>
                                <th>Account</th>
                                <th>Particular</th>
                                <th>Mode</th>
                                <th>Type</th>
                                <th>Deposit Account</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivables as $r)
                            <tr class="{{ $r->status === 'received' ? 'opacity-70' : '' }}">
                                <td class="font-mono font-semibold">{{ $r->code }}</td>
                                <td>{{ $r->date->format('M d, Y') }}</td>
                                <td>{{ $r->encoder->name ?? '—' }}</td>
                                <td>
                                    @if($r->status === 'received')
                                        <span class="badge badge-success badge-sm">RECEIVED</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">PENDING</span>
                                    @endif
                                </td>
                                <td>{{ $r->ref_ar ?? '—' }}</td>
                                <td>
                                    @if($r->applicant)
                                        {{ $r->applicant->first_name }} {{ $r->applicant->last_name }}
                                    @else —
                                    @endif
                                </td>
                                <td>{{ $r->agent->name ?? '—' }}</td>
                                <td class="text-right font-semibold">₱{{ number_format($r->amount, 2) }}</td>
                                <td>{{ $r->account ?? '—' }}</td>
                                <td class="max-w-[16rem] truncate" title="{{ $r->particular }}">{{ $r->particular ?? '—' }}</td>
                                <td>{{ $r->mode ?? '—' }}</td>
                                <td>{{ $r->type ?? '—' }}</td>
                                <td>{{ $r->debit_account ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('receivable.show', $r->id) }}" class="btn btn-xs btn-ghost">Review →</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="opacity-50 text-sm py-6 text-center">No receivable transactions yet.</p>
            @endif
        </div>
    </div>

</div>
@endsection
