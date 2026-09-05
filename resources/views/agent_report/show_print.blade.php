<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Agent Ledger — {{ $agent->name }} ({{ $printedAt->format('Y-m-d') }})</title>
    <style>
        body { font-family: -apple-system, 'Segoe UI', Roboto, sans-serif; color: #111; padding: 24px; }
        .header { display: flex; justify-content: space-between; align-items: flex-end; border-bottom: 2px solid #111; padding-bottom: 10px; margin-bottom: 14px; }
        h1 { font-size: 22px; margin: 0 0 2px; }
        h2 { font-size: 14px; margin: 20px 0 6px; padding: 6px 8px; background: #111; color: #fff; border-radius: 3px; }
        .sub { color: #555; font-size: 11px; }
        .agent-meta { font-size: 13px; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { border: 1px solid #ccc; padding: 5px 7px; text-align: left; }
        th { background: #f3f4f6; font-weight: 700; text-transform: uppercase; font-size: 10px; }
        .num, th.num { text-align: right; font-variant-numeric: tabular-nums; }
        tfoot td { font-weight: 700; background: #f3f4f6; }
        .summary { width: auto; margin-top: 6px; margin-left: auto; }
        .summary th { background: #111; color: #fff; }
        .neg { color: #b91c1c; }
        .pos { color: #15803d; }
        .note { margin-top: 12px; font-size: 9px; color: #666; border-top: 1px solid #ddd; padding-top: 6px; }
        .badge { display: inline-block; font-size: 10px; padding: 1px 6px; border-radius: 8px; }
        .page-break { page-break-before: always; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>AGENT LEDGER</h1>
            <div class="agent-meta">
                <strong>{{ $agent->name }}</strong>
                @if($agent->branch)
                    · <span class="badge">{{ $agent->branch->name }}</span>
                @endif
            </div>
        </div>
        <div class="sub">Generated {{ $printedAt->format('M d, Y H:i') }}<br>Currency: USD converted at {{ number_format($usdToPhp, 2) }} PHP/USD</div>
    </div>

    @php
        $hasCommission = $releasedCommission->isNotEmpty();
        $hasCashAdvance = $cashAdvance->isNotEmpty();
        $hasBackout = $backoutRepat->isNotEmpty();
        $hasReceivables = $receivables->isNotEmpty();
        $hasPayments = $payments->isNotEmpty();
        $count = collect([$hasCommission, $hasCashAdvance, $hasBackout, $hasReceivables, $hasPayments])->filter()->count();
    @endphp

    @if($hasCommission)
    <h2>💰 Released Commission — <span style="text-transform:none">Total: ₱{{ number_format($totalCommission, 2) }}</span></h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher # (Ref #)</th>
                <th>Applicant</th>
                <th>Account</th>
                <th>Particular / Description</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($releasedCommission as $item)
                <tr>
                    <td>{{ $item->expenseRequest?->date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $item->expenseRequest?->reference_no ?? '—' }}</td>
                    <td>{{ $item->applicant?->full_name ?? '—' }}</td>
                    <td>{{ $item->account?->name ?? '—' }}</td>
                    <td>{{ $item->particular ?? '—' }}</td>
                    <td class="num">{{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format($item->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No released commission transactions.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="5">Total Commission (Return excluded)</td><td class="num">₱{{ number_format($totalCommission, 2) }}</td></tr>
        </tfoot>
    </table>
    @endif

    @if($hasCashAdvance)
    <h2>💵 Cash Advance — <span style="text-transform:none">Total: ₱{{ number_format($totalCashAdvance, 2) }}</span></h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Voucher # (Ref #)</th>
                <th>Applicant</th>
                <th>Account</th>
                <th>Particular / Description</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cashAdvance as $item)
                <tr>
                    <td>{{ $item->expenseRequest?->date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $item->expenseRequest?->reference_no ?? '—' }}</td>
                    <td>{{ $item->applicant?->full_name ?? '—' }}</td>
                    <td>{{ $item->account?->name ?? '—' }}</td>
                    <td>{{ $item->particular ?? '—' }}</td>
                    <td class="num">{{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format($item->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No cash advance transactions.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="5">Total Cash Advance</td><td class="num">₱{{ number_format($totalCashAdvance, 2) }}</td></tr>
        </tfoot>
    </table>
    @endif

    @if($hasBackout)
    <h2>↩️ Backout and Repat — <span style="text-transform:none">Total: ₱{{ number_format($totalBackoutRepat, 2) }}</span></h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Agent</th>
                <th>Applicant</th>
                <th>Particular</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($backoutRepat as $d)
                <tr>
                    <td>{{ $d->date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $d->account ?? '—' }}</td>
                    <td>{{ $d->agent?->name ?? '—' }}</td>
                    <td>{{ $d->applicant?->full_name ?? '—' }}</td>
                    <td>{{ $d->particular ?? '—' }}</td>
                    <td class="num">₱{{ number_format($d->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No backout and repat entries.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="5">Total Backout and Repat</td><td class="num">₱{{ number_format($totalBackoutRepat, 2) }}</td></tr>
        </tfoot>
    </table>
    @endif

    @if($hasReceivables)
    <h2>📄 Receivables — <span style="text-transform:none">Total: ₱{{ number_format($totalReceivables, 2) }}</span></h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Ref #</th>
                <th>Applicant</th>
                <th>Mode</th>
                <th>Account</th>
                <th>Particular</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($receivables as $r)
                <tr>
                    <td>{{ $r->date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $r->reference_no ?? '—' }}</td>
                    <td>{{ $r->applicant?->full_name ?? '—' }}</td>
                    <td>{{ $r->mode ?? '—' }}</td>
                    <td>{{ $r->account ?? '—' }}</td>
                    <td>{{ $r->particular ?? '—' }}</td>
                    <td class="num">₱{{ number_format($r->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7">No receivable entries.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="6">Total Receivables</td><td class="num">₱{{ number_format($totalReceivables, 2) }}</td></tr>
        </tfoot>
    </table>
    @endif

    @if($hasPayments)
    <h2>💳 Payment — <span style="text-transform:none">Total: ₱{{ number_format($totalPayment, 2) }}</span></h2>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Account</th>
                <th>Agent</th>
                <th>Applicant</th>
                <th>Particular</th>
                <th class="num">Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $p)
                <tr>
                    <td>{{ $p->date?->format('M d, Y') ?? '—' }}</td>
                    <td>{{ $p->account ?? '—' }}</td>
                    <td>{{ $p->agent?->name ?? '—' }}</td>
                    <td>{{ $p->applicant?->full_name ?? '—' }}</td>
                    <td>{{ $p->particular ?? '—' }}</td>
                    <td class="num">₱{{ number_format($p->amount, 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="6">No payment entries.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="5">Total Payment</td><td class="num">₱{{ number_format($totalPayment, 2) }}</td></tr>
        </tfoot>
    </table>
    @endif

    <h2 class="page-break">📋 Summary Report</h2>
    <table class="summary">
        <tbody>
            <tr><th>Total Commission (Return excluded)</th><td class="num">₱{{ number_format($totalCommission, 2) }}</td></tr>
            <tr><th>Total Cash Advance</th><td class="num">₱{{ number_format($totalCashAdvance, 2) }}</td></tr>
            <tr><th>Total Backout and Repat</th><td class="num">₱{{ number_format($totalBackoutRepat, 2) }}</td></tr>
            <tr><th>Total Receivable (AR)</th><td class="num">₱{{ number_format($totalReceivables, 2) }}</td></tr>
            <tr><th>Total Payment</th><td class="num">₱{{ number_format($totalPayment, 2) }}</td></tr>
            <tr><th>Agent's Balance</th><td class="num {{ $balance < 0 ? 'neg' : 'pos' }}">₱{{ number_format($balance, 2) }}</td></tr>
        </tbody>
    </table>

    <div class="note">
        Balance = Total Cash Advance + Total Backout and Repat − Total Receivable (AR) − Total Payment ·
        Released Commission transactions only (Return rows are displayed but excluded from the Total Commission) ·
        Generated {{ $printedAt->format('M d, Y H:i') }} · {{ $agent->name }}
    </div>
</body>
</html>
