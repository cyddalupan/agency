@extends('layouts.app')

@section('title', 'Agent Ledger — ' . $agent->name)

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header: agent name + branch --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('agent_report.index') }}" class="opacity-60 text-xs hover:opacity-100 transition-opacity">← Back to Agents Report</a>
                <div class="flex items-center gap-4 mt-2">
                    <div class="avatar placeholder">
                        <div class="bg-base-100 text-primary rounded-full w-14 h-14">
                            <span class="text-xl font-extrabold">{{ strtoupper(substr($agent->name, 0, 1)) }}</span>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-extrabold">{{ $agent->name }}</h1>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="badge badge-sm badge-primary border-0">🏢 {{ $agent->branch?->name ?? '—' }}</span>
                            <span class="opacity-60 text-sm">Agent Ledger</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary report --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm">
        <div class="card-body p-5">
            <h2 class="font-bold text-lg mb-3 flex items-center gap-2">📋 Summary Report</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Total Commission</p>
                    <p class="text-lg font-extrabold text-primary mt-1">₱{{ number_format($totalCommission, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Total Cash Advance</p>
                    <p class="text-lg font-extrabold mt-1">₱{{ number_format($totalCashAdvance, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Total Backout &amp; Repat</p>
                    <p class="text-lg font-extrabold text-warning mt-1">₱{{ number_format($totalBackoutRepat, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Total Receivables</p>
                    <p class="text-lg font-extrabold text-error mt-1">₱{{ number_format($totalReceivables, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Total Payment</p>
                    <p class="text-lg font-extrabold text-info mt-1">₱{{ number_format($totalPayment, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Total Balance</p>
                    <p class="text-lg font-extrabold mt-1 {{ $balance < 0 ? 'text-error' : 'text-success' }}">₱{{ number_format($balance, 2) }}</p>
                </div>
            </div>
            <p class="text-xs opacity-50 mt-3">Balance = Cash Advance + Backout &amp; Repat − Total Receivables − Total Payment · USD converted at {{ number_format($usdToPhp, 2) }} PHP/USD</p>
        </div>
    </div>

    {{-- Table 1: Released Commission --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="px-5 py-4 flex items-center justify-between border-b border-base-200">
                <h2 class="font-bold flex items-center gap-2">💰 Released Commission</h2>
                <span class="badge badge-primary badge-sm">Total: ₱{{ number_format($totalCommission, 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-base-200/80 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="py-3">Date</th>
                            <th>Voucher # (From Reference #)</th>
                            <th>Applicant</th>
                            <th>Mode</th>
                            <th>Account</th>
                            <th>Particular / Description</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($releasedCommission as $item)
                            <tr class="hover transition-colors">
                                <td class="whitespace-nowrap">{{ $item->expenseRequest?->date?->format('M d, Y') ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ $item->expenseRequest?->reference_no ?? '—' }}</td>
                                <td>{{ $item->applicant?->full_name ?? '—' }}</td>
                                <td class="opacity-40">—</td>
                                <td>{{ $item->account?->name ?? '—' }}</td>
                                <td class="max-w-xs truncate" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                                <td class="text-right font-mono">{{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 opacity-50">
                                    <span class="text-3xl block mb-1">🪙</span>
                                    No released commission transactions.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-base-200/80 font-bold">
                        <tr>
                            <td colspan="6" class="py-3">Total Commission</td>
                            <td class="text-right font-mono">₱{{ number_format($totalCommission, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Table 2: Cash Advance --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="px-5 py-4 flex items-center justify-between border-b border-base-200">
                <h2 class="font-bold flex items-center gap-2">💵 Cash Advance</h2>
                <span class="badge badge-primary badge-sm">Total: ₱{{ number_format($totalCashAdvance, 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-base-200/80 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="py-3">Date</th>
                            <th>Voucher # (From Reference #)</th>
                            <th>Applicant</th>
                            <th>Mode</th>
                            <th>Account</th>
                            <th>Particular / Description</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cashAdvance as $item)
                            <tr class="hover transition-colors">
                                <td class="whitespace-nowrap">{{ $item->expenseRequest?->date?->format('M d, Y') ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ $item->expenseRequest?->reference_no ?? '—' }}</td>
                                <td>{{ $item->applicant?->full_name ?? '—' }}</td>
                                <td class="opacity-40">—</td>
                                <td>{{ $item->account?->name ?? '—' }}</td>
                                <td class="max-w-xs truncate" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                                <td class="text-right font-mono">{{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format($item->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 opacity-50">
                                    <span class="text-3xl block mb-1">🫙</span>
                                    No cash advance transactions.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-base-200/80 font-bold">
                        <tr>
                            <td colspan="6" class="py-3">Total Cash Advance</td>
                            <td class="text-right font-mono">₱{{ number_format($totalCashAdvance, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Table 3: Backout and Repat --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="px-5 py-4 flex items-center justify-between border-b border-base-200">
                <h2 class="font-bold flex items-center gap-2">↩️ Backout and Repat</h2>
                <span class="badge badge-warning badge-sm">Total: ₱{{ number_format($totalBackoutRepat, 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-base-200/80 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="py-3">Date</th>
                            <th>Account</th>
                            <th>Agent</th>
                            <th>Applicant</th>
                            <th class="text-right">Amount</th>
                            <th>Particular / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($backoutRepat as $item)
                            <tr class="hover transition-colors">
                                <td class="whitespace-nowrap">{{ $item->expenseRequest?->date?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $item->account?->name ?? '—' }}</td>
                                <td>{{ $item->agent?->name ?? '—' }}</td>
                                <td>{{ $item->applicant?->full_name ?? '—' }}</td>
                                <td class="text-right font-mono">{{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format($item->amount, 2) }}</td>
                                <td class="max-w-xs truncate" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 opacity-50">
                                    <span class="text-3xl block mb-1">↩️</span>
                                    No backout / repatriation transactions.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-base-200/80 font-bold">
                        <tr>
                            <td colspan="4" class="py-3">Total Backout and Repat</td>
                            <td class="text-right font-mono">₱{{ number_format($totalBackoutRepat, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Table 4: Receivables --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="px-5 py-4 flex items-center justify-between border-b border-base-200">
                <h2 class="font-bold flex items-center gap-2">📄 Receivables</h2>
                <span class="badge badge-primary badge-sm">Total: ₱{{ number_format($totalReceivables, 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-base-200/80 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="py-3">Date</th>
                            <th>Voucher # (From Reference #)</th>
                            <th>Applicant</th>
                            <th>Mode</th>
                            <th>Account</th>
                            <th>Particular / Description</th>
                            <th class="text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivables as $r)
                            <tr class="hover transition-colors">
                                <td class="whitespace-nowrap">{{ $r->date?->format('M d, Y') ?? '—' }}</td>
                                <td class="font-mono text-xs">{{ $r->ref_ar ?? $r->code ?? '—' }}</td>
                                <td>{{ $r->applicant?->full_name ?? '—' }}</td>
                                <td class="opacity-40">—</td>
                                <td>{{ $r->account ?? '—' }}</td>
                                <td class="max-w-xs truncate" title="{{ $r->particular }}">{{ $r->particular ?? '—' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($r->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-10 opacity-50">
                                    <span class="text-3xl block mb-1">📭</span>
                                    No receivables recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-base-200/80 font-bold">
                        <tr>
                            <td colspan="6" class="py-3">Total Receivables</td>
                            <td class="text-right font-mono">₱{{ number_format($totalReceivables, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- Table 5: Payment --}}
    <div class="card bg-base-100 border border-base-300 shadow-sm overflow-hidden">
        <div class="card-body p-0">
            <div class="px-5 py-4 flex items-center justify-between border-b border-base-200">
                <h2 class="font-bold flex items-center gap-2">💳 Payment</h2>
                <span class="badge badge-success badge-sm">Total: ₱{{ number_format($totalPayment, 2) }}</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead class="bg-base-200/80 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="py-3">Date</th>
                            <th>Account</th>
                            <th>Agent</th>
                            <th>Applicant</th>
                            <th class="text-right">Amount</th>
                            <th>Particular / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr class="hover transition-colors">
                                <td class="whitespace-nowrap">{{ $p->date?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $p->account ?? '—' }}</td>
                                <td>{{ $p->agent?->name ?? '—' }}</td>
                                <td>{{ $p->applicant?->full_name ?? '—' }}</td>
                                <td class="text-right font-mono">₱{{ number_format($p->amount, 2) }}</td>
                                <td class="max-w-xs truncate" title="{{ $p->particular }}">{{ $p->particular ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-10 opacity-50">
                                    <span class="text-3xl block mb-1">💤</span>
                                    No payments recorded.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-base-200/80 font-bold">
                        <tr>
                            <td colspan="4" class="py-3">Total Paid</td>
                            <td class="text-right font-mono">₱{{ number_format($totalPayment, 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
