@extends('layouts.app')

@section('title', 'Agency Accounting Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <h1 class="text-3xl font-bold">📊 Agency Accounting Dashboard</h1>
            <p class="opacity-80 mt-1">Income, expenses &amp; cash position</p>
            <form method="GET" action="{{ route('accounting.dashboard') }}" class="mt-4 flex flex-wrap items-end gap-3">
                <div>
                    <label class="text-xs opacity-80 block mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}" class="input input-sm bg-white/90 text-base-content">
                </div>
                <div>
                    <label class="text-xs opacity-80 block mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}" class="input input-sm bg-white/90 text-base-content">
                </div>
                <button type="submit" class="btn btn-sm btn-ghost bg-white/20 hover:bg-white/30">Filter</button>
                @if(request('from') || request('to'))
                    <a href="{{ route('accounting.dashboard') }}" class="btn btn-sm btn-ghost bg-white/20 hover:bg-white/30">Reset</a>
                @endif
                <span class="flex-1"></span>
                <a href="{{ route('accounting.export', array_filter(['format' => 'csv', 'from' => request('from'), 'to' => request('to')])) }}"
                   class="btn btn-sm btn-ghost bg-white/20 hover:bg-white/30">⬇️ CSV</a>
                <a href="{{ route('accounting.export', array_filter(['format' => 'pdf', 'from' => request('from'), 'to' => request('to')])) }}"
                   class="btn btn-sm bg-white text-primary font-semibold hover:bg-white/90">⬇️ PDF</a>
            </form>
        </div>
    </div>

    {{-- Cash position cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60 flex items-center gap-1">💰 Money In</p>
                <p class="text-2xl font-bold text-success">₱{{ number_format($moneyIn, 2) }}</p>
                <p class="text-xs opacity-50 mt-1">Payments received</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60 flex items-center gap-1">💸 Money Out</p>
                <p class="text-2xl font-bold text-error">₱{{ number_format($moneyOut, 2) }}</p>
                <p class="text-xs opacity-50 mt-1">Expenses + commissions paid</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60 flex items-center gap-1">⚖️ Balance</p>
                <p class="text-2xl font-bold {{ $balance >= 0 ? 'text-primary' : 'text-error' }}">₱{{ number_format($balance, 2) }}</p>
                <p class="text-xs opacity-50 mt-1">Net cash position</p>
            </div>
        </div>
    </div>

    {{-- P&L by category --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="font-bold mb-3 flex items-center gap-2">💰 Income by Category</h3>
                @if($incomeByAccount->count())
                    <table class="table table-sm">
                        <thead><tr class="bg-base-200/70"><th>Category</th><th class="text-right">Amount</th></tr></thead>
                        <tbody>
                            @foreach($incomeByAccount as $row)
                            <tr>
                                <td>{{ $row['account_name'] }}</td>
                                <td class="text-right font-semibold text-success">₱{{ number_format($row['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="opacity-50 text-sm py-4 text-center">No income in range.</p>
                @endif
            </div>
        </div>

        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <h3 class="font-bold mb-3 flex items-center gap-2">💸 Expenses by Account</h3>
                @if($expensesByAccount->count())
                    <table class="table table-sm">
                        <thead><tr class="bg-base-200/70"><th>Account</th><th>Main</th><th class="text-right">Amount</th></tr></thead>
                        <tbody>
                            @foreach($expensesByAccount as $row)
                            <tr>
                                <td class="font-medium">{{ $row['account_name'] }}</td>
                                <td class="opacity-50">{{ $row['parent_name'] ?? '—' }}</td>
                                <td class="text-right font-semibold text-error">₱{{ number_format($row['total'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p class="opacity-50 text-sm py-4 text-center">No expenses in range.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Entity breakdown --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="font-bold mb-3 flex items-center gap-2">🏢 Breakdown by Entity (Employer)</h3>
            @if($moneyInByEntity->count())
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr class="bg-base-200/70"><th>Employer</th><th class="text-right">Money In</th></tr></thead>
                        <tbody>
                            @foreach($moneyInByEntity as $row)
                            <tr>
                                <td class="font-medium">{{ $row['employer_name'] }}</td>
                                <td class="text-right font-semibold text-success">₱{{ number_format($row['in'], 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="opacity-50 text-sm py-4 text-center">No entity data in range.</p>
            @endif
        </div>
    </div>

    {{-- Monthly trend --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="font-bold mb-4 flex items-center gap-2">📈 Monthly Trend</h3>
            @if(count($monthlyTrend))
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                    @foreach($monthlyTrend as $m)
                    <div class="rounded-xl border border-base-300 p-3 text-center">
                        <p class="text-xs font-semibold opacity-60">{{ \Carbon\Carbon::parse($m['month'] . '-01')->format('M Y') }}</p>
                        <p class="text-success text-sm font-semibold mt-2">₱{{ number_format($m['in'], 0) }}</p>
                        <p class="text-error text-sm font-semibold">-₱{{ number_format($m['out'], 0) }}</p>
                        <p class="text-sm font-bold mt-1 {{ $m['net'] >= 0 ? 'text-primary' : 'text-error' }}">₱{{ number_format($m['net'], 0) }}</p>
                        <p class="text-[10px] opacity-40">net</p>
                    </div>
                    @endforeach
                </div>
            @else
                <p class="opacity-50 text-sm py-4 text-center">No data.</p>
            @endif
        </div>
    </div>

</div>
@endsection
