@extends('layouts.app')

@section('title', 'Agents Report')

@section('content')
<div class="p-4 sm:p-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h1 class="text-2xl font-extrabold" style="color:#0b1f3a;">Agents Report</h1>
            <p class="opacity-80 mt-1">Receivable — Tab 3</p>
        </div>
    </div>

    {{-- Tab switcher: Tab 1 Receivable / Tab 2 Expenses & Payments / Tab 3 Agents Report --}}
    <div class="tabs tabs-boxed bg-base-200/60 mb-6 w-fit">
        <a href="{{ route('receivable.index') }}" class="tab text-sm">Tab 1 · Receivable</a>
        <a href="{{ route('expense_request.index') }}" class="tab text-sm">Tab 2 · Expenses &amp; Payments</a>
        <span class="tab tab-active text-sm font-semibold">Tab 3 · Agents Report</span>
    </div>

    {{-- Date filter --}}
    <form method="GET" action="{{ route('agent_report.index') }}" class="card bg-base-200/40 border border-base-300 mb-6">
        <div class="card-body py-4">
            <div class="flex flex-wrap items-end gap-3">
                <div class="form-control">
                    <label class="label"><span class="label-text">From</span></label>
                    <input type="date" name="from" value="{{ $from ?? '' }}" class="input input-bordered input-sm">
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">To</span></label>
                    <input type="date" name="to" value="{{ $to ?? '' }}" class="input input-bordered input-sm">
                </div>
                <button type="submit" class="btn btn-primary btn-sm shadow-md">Filter</button>
                <a href="{{ route('agent_report.index') }}" class="btn btn-ghost btn-sm">Reset</a>
            </div>
        </div>
    </form>

    {{-- Ledger table --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-base-200/70">
                        <tr>
                            <th>Agent</th>
                            <th>Branch</th>
                            <th class="text-center"># Receivables</th>
                            <th class="text-right">Total Receivable</th>
                            <th class="text-right">Total Paid/Collected</th>
                            <th class="text-center"># Expenses</th>
                            <th class="text-right">Expenses (PHP)</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $row)
                            <tr class="hover">
                                <td class="font-semibold">{{ $row['agent'] }}</td>
                                <td>{{ $row['branch'] }}</td>
                                <td class="text-center">{{ $row['receive_count'] }}</td>
                                <td class="text-right">₱{{ number_format($row['receive_total'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($row['collected'], 2) }}</td>
                                <td class="text-center">{{ $row['expense_count'] }}</td>
                                <td class="text-right">₱{{ number_format($row['expense_total'], 2) }}</td>
                                <td class="text-right font-bold {{ $row['balance'] < 0 ? 'text-error' : '' }}">
                                    ₱{{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-8 opacity-60">No agents found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($agents->isNotEmpty())
                        <tfoot class="bg-base-200/70 font-bold">
                            <tr>
                                <td colspan="2">Totals</td>
                                <td class="text-center">{{ $totals['receive_count'] }}</td>
                                <td class="text-right">₱{{ number_format($totals['receive_total'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($totals['collected'], 2) }}</td>
                                <td class="text-center">{{ $totals['expense_count'] }}</td>
                                <td class="text-right">₱{{ number_format($totals['expense_total'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($totals['balance'], 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    @if($agents->isNotEmpty())
        <p class="text-xs opacity-60 mt-3">
            USD expense lines are converted to PHP at rate {{ number_format($usdToPhp, 2) }} for the ledger balance.
            Balance = Total Receivable − Total Paid/Collected − Expenses.
        </p>
    @endif
</div>
@endsection
