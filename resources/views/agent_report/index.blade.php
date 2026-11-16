@extends('layouts.app')

@section('title', agent_terms() . ' Report')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <span class="text-3xl">📊</span>
                    <div>
                        <h1 class="text-2xl font-extrabold">{{ agent_terms() }} Report</h1>
                        <p class="opacity-70 text-sm mt-0.5">Commission · Cash Advance · Receivables · Payments · Deductions · Starting Balance · Report</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                @if($tab === 'report')
                    <a href="{{ route('agent_report.print', ['agent_id' => $agentId]) }}" class="btn btn-sm btn-secondary shadow-md" target="_blank">🖨️ Print</a>
                    <a href="{{ route('agent_report.export', ['agent_id' => $agentId]) }}" class="btn btn-sm btn-secondary shadow-md">⬇️ Export CSV</a>
                @endif
                @if($tab === 'deductions' && $canManageDeductions)
                    <a href="{{ route('agent_report.deduction.create') }}" class="btn btn-sm btn-secondary shadow-md">
                        ➕ New Deduction
                    </a>
                @endif
                @if($tab === 'starting-balances' && $canManageDeductions)
                    <a href="{{ route('agent_report.starting_balance.bulk') }}" class="btn btn-sm btn-secondary shadow-md">
                        📥 Bulk CSV
                    </a>
                @endif
                @if($tab === 'starting-balances')
                    <a href="{{ route('agent_report.starting_balance.create') }}" class="btn btn-sm btn-secondary shadow-md">
                        ➕ New Starting Balance
                    </a>
                @endif
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold">{{ $agents->count() }}</span> {{ strtolower(agent_terms()) }}
                </span>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-md mb-4">
            <span>✅ {{ session('success') }}</span>
        </div>
    @endif

    {{-- 5-Tab bar --}}
    <div class="tabs tabs-boxed bg-base-100 border border-base-300 shadow-sm p-1.5 overflow-x-auto">
        @php
            $tabDefs = [
                'cash-advance'      => ['label' => '💰 Cash Advance',      'url' => null],
                'receivables'       => ['label' => '📊 Receivables',       'url' => null],
                'return-payment'    => ['label' => '↩️ Return Payment',    'url' => null],
                'payments'          => ['label' => '💳 Payments',          'url' => null],
                'deductions'        => ['label' => '🧾 Deductions',          'url' => null],
                'starting-balances' => ['label' => '⚖️ Starting Balance',  'url' => null],
                'report'            => ['label' => '📑 ' . agent_term() . ' Ledger',      'url' => null],
            ];
        @endphp
        @foreach($tabDefs as $key => $def)
            @if($key === 'deductions' && ! $canManageDeductions)
                @continue
            @endif
            <a href="{{ route('agent_report.index', ['tab' => $key, 'agent_id' => $agentId]) }}"
               class="tab tab-lg whitespace-nowrap {{ $tab === $key ? 'tab-active bg-primary text-primary-content font-bold shadow-md' : '' }}">
                {{ $def['label'] }}
            </a>
        @endforeach
    </div>

    {{-- Agent filter (all tabs) --}}
    <form method="GET" action="{{ route('agent_report.index') }}" class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body py-4">
            <div class="flex flex-wrap items-end gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <div class="form-control">
                    <label class="label"><span class="label-text font-semibold">Agent</span></label>
                    <select name="agent_id" class="select select-bordered select-sm w-64" onchange="this.form.submit()">
                        <option value="">All agents</option>
                        @foreach($agents as $a)
                            <option value="{{ $a->id }}" {{ (string) $agentId === (string) $a->id ? 'selected' : '' }}>
                                {{ $a->name }}@if($a->branch) ({{ $a->branch->name }})@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm shadow-md">Filter</button>
                <a href="{{ route('agent_report.index', ['tab' => $tab]) }}" class="btn btn-ghost btn-sm">Reset</a>
            </div>
        </div>
    </form>

    {{-- Tab content --}}
    @if($tab === 'cash-advance')
        @include('agent_report._cash_advance')
    @elseif($tab === 'receivables')
        @include('agent_report._receivables')
    @elseif($tab === 'return-payment')
        @include('agent_report._return_payment')
    @elseif($tab === 'payments')
        @include('agent_report._payments')
    @elseif($tab === 'deductions')
        @include('agent_report._deductions')
    @elseif($tab === 'starting-balances')
        @include('agent_report._starting_balances')
    @else
        @include('agent_report._report')
    @endif

</div>
@endsection
