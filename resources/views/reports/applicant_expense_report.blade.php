<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report - {{ $applicant->full_name }}</title>
    <style>
        @page { size: A4 portrait; margin: 8mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 9pt; color: #333; line-height: 1.4; }

        .page { width: 100%; }

        h1.title {
            font-size: 13pt;
            font-weight: 700;
            color: #1a365d;
            text-align: center;
            margin-bottom: 5mm;
            text-transform: uppercase;
        }

        /* ── HEADER ── */
        table.header { width: 100%; border-collapse: collapse; margin-bottom: 6mm; table-layout: fixed; }
        table.header td { padding: 1.5mm 2mm; font-size: 9pt; vertical-align: top; word-break: break-word; }
        table.header .label { font-weight: 700; color: #1a365d; width: 38mm; }

        /* ── SECTION TABLES ── */
        h2.section {
            font-size: 10.5pt;
            font-weight: 700;
            color: #1a365d;
            border-bottom: 1.5px solid #1a365d;
            padding-bottom: 1mm;
            margin: 6mm 0 3mm;
        }

        table.data { width: 100%; border-collapse: collapse; font-size: 8pt; table-layout: fixed; }
        table.data th {
            background: #1a365d;
            color: #fff;
            font-weight: 700;
            padding: 1.8mm 1.5mm;
            text-align: left;
            border: 0.5px solid #1a365d;
        }
        table.data td {
            padding: 1.5mm;
            border: 0.5px solid #cbd5e0;
            vertical-align: top;
            word-break: break-word;
        }
        table.data tr:nth-child(even) td { background: #f8f9fa; }
        table.data td.num, table.data th.num { text-align: right; }

        tr.total-row td {
            font-weight: 700;
            background: #eef2f7;
            border-top: 1px solid #1a365d;
        }

        .grand-total {
            font-size: 9pt;
            font-weight: 700;
            margin-top: 2mm;
            text-align: right;
        }

        .muted { color: #6b7280; }
    </style>
</head>
<body>
<div class="page">

    <h1 class="title">Expense Report</h1>

    {{-- ── HEADER ── --}}
    <table class="header">
        <tr>
            <td class="label">Date Applied:</td>
            <td>{{ $applicant->created_at?->format('Y-m-d') ?? '—' }}</td>
            <td class="label">Name - Applicant:</td>
            <td>{{ $applicant->full_name }}</td>
        </tr>
        <tr>
            <td class="label">Agent:</td>
            <td>{{ $applicant->agent?->name ?? '—' }}</td>
            <td class="label">Branch:</td>
            <td>{{ $applicant->branch?->name ?? '—' }}</td>
        </tr>
    </table>

    {{-- ── STATEMENT OF ACCOUNT ── --}}
    <h2 class="section">Statement of Account</h2>
    @php $converter = app(\App\Services\CurrencyConverter::class); @endphp
    <table class="data">
        <thead>
            <tr>
                <th style="width:6%">Date</th>
                <th style="width:10%">Account Type</th>
                <th style="width:44%">Description</th>
                <th style="width:8%">Charge To</th>
                <th style="width:7%">Currency</th>
                <th style="width:12%;text-align:center">Amount</th>
                <th style="width:13%;text-align:center">Converted</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($statementItems as $item)
                <tr>
                    <td>{{ $item->expenseRequest?->date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $item->account?->name ?? '—' }}</td>
                    <td>{{ $item->particular ?? '—' }}</td>
                    <td>{{ $item->charge === 'office' ? 'Office' : 'Agent' }}</td>
                    <td>{{ $item->currency }}</td>
                    <td class="num">{{ number_format((float) $item->amount, 2) }}</td>
                    <td class="num">{{ number_format($converter->toPhp((float) $item->amount, (string) $item->currency), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No expenses recorded.</td></tr>
            @endforelse
        </tbody>
        @if ($statementItems->isNotEmpty())
            <tr class="total-row">
                <td colspan="6" class="num">TOTAL</td>
                <td class="num">{{ number_format($statementGrandTotal, 2) }}</td>
            </tr>
        @endif
    </table>

    {{-- ── AGENT EXPENSES ── --}}
    <h2 class="section">Agent Expenses</h2>
    <table class="data">
        <thead>
            <tr>
                <th style="width:6%">Date</th>
                <th style="width:8%">Status</th>
                <th style="width:10%">Account Type</th>
                <th style="width:44%">Description</th>
                <th style="width:7%">Currency</th>
                <th style="width:12%;text-align:center">Amount</th>
                <th style="width:13%;text-align:center">Converted</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($agentItems as $item)
                <tr>
                    <td>{{ $item->expenseRequest?->date?->format('Y-m-d') ?? '—' }}</td>
                    <td>{{ $item->expenseRequest?->status ?? '—' }}</td>
                    <td>{{ $item->account?->name ?? '—' }}</td>
                    <td>{{ $item->particular ?? '—' }}</td>
                    <td>{{ $item->currency }}</td>
                    <td class="num">{{ number_format((float) $item->amount, 2) }}</td>
                    <td class="num">{{ number_format($converter->toPhp((float) $item->amount, (string) $item->currency), 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="muted">No agent expenses recorded.</td></tr>
            @endforelse
        </tbody>
        @if ($agentItems->isNotEmpty())
            <tr class="total-row">
                <td colspan="6" class="num">TOTAL</td>
                <td class="num">{{ number_format($agentGrandTotal, 2) }}</td>
            </tr>
        @endif
    </table>

</div>
</body>
</html>
