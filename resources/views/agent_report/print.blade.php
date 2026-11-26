<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ agent_term() }} Ledger — {{ $printedAt->format('Y-m-d') }}</title>
    <style>
        body { font-family: -apple-system, 'Segoe UI', Roboto, sans-serif; color: #111; padding: 24px; }
        h1 { font-size: 20px; margin: 0 0 2px; }
        .sub { color: #666; font-size: 12px; margin-bottom: 16px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: right; }
        th { background: #f3f4f6; font-weight: 700; }
        td:first-child, th:first-child, td:nth-child(2), th:nth-child(2) { text-align: left; }
        tfoot td { font-weight: 700; background: #f3f4f6; }
        .neg { color: #b91c1c; }
        .pos { color: #15803d; }
        .note { margin-top: 14px; font-size: 10px; color: #666; }
        @media print { body { padding: 0; } }
    </style>
</head>
<body>
    <h1>📊 {{ agent_term() }} Ledger</h1>
    <div class="sub">
        Generated {{ $printedAt->format('M d, Y H:i') }}
        @if($agentId) · Agent filter applied @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ agent_term() }}</th>
                <th>Branch</th>
                <th>Total Commission</th>
                <th>Total Cash Advance</th>
                <th>Total Backout and Repat</th>
                <th>Total Receivable (AR)</th>
                <th>Total Return Payment</th>
                <th>Total Payments</th>
                <th>{{ agent_term() }}'s Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportRows as $row)
                <tr>
                    <td>{{ $row['agent'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>{{ peso($row['commission']) }}</td>
                    <td>{{ peso($row['cash_advance']) }}</td>
                    <td>{{ peso($row['backout_repat']) }}</td>
                    <td>{{ peso($row['receivable']) }}</td>
                    <td>{{ peso($row['return_payment']) }}</td>
                    <td>{{ peso($row['payments']) }}</td>
                    <td class="{{ $row['balance'] >= 0 ? 'pos' : 'neg' }}">{{ peso($row['balance']) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Totals</td>
                <td>{{ peso($totals['commission']) }}</td>
                <td>{{ peso($totals['cash_advance']) }}</td>
                <td>{{ peso($totals['backout_repat']) }}</td>
                <td>{{ peso($totals['receivable']) }}</td>
                <td>{{ peso($totals['return_payment']) }}</td>
                <td>{{ peso($totals['payments']) }}</td>
                <td class="{{ $totals['balance'] >= 0 ? 'pos' : 'neg' }}">{{ peso($totals['balance']) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="note">
        Agent's Balance = Total Cash Advance + Total Backout and Repat − Total Receivable (AR) + Total Return Payment − Total Payments.
    </div>

    <script>window.print();</script>
</body>
</html>
