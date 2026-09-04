<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Agents Report — {{ $printedAt->format('Y-m-d') }}</title>
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
    <h1>📊 Agents Report</h1>
    <div class="sub">
        Generated {{ $printedAt->format('M d, Y H:i') }}
        @if($agentId) · Agent filter applied @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Agent</th>
                <th>Branch</th>
                <th>Starting Balance</th>
                <th>Commission</th>
                <th>Payments</th>
                <th>Deductions</th>
                <th>Paid</th>
                <th>Net Balance</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportRows as $row)
                <tr>
                    <td>{{ $row['agent'] }}</td>
                    <td>{{ $row['branch'] }}</td>
                    <td>₱{{ number_format($row['starting_balance'], 2) }}</td>
                    <td>₱{{ number_format($row['commission'], 2) }}</td>
                    <td>₱{{ number_format($row['payments'], 2) }}</td>
                    <td>₱{{ number_format($row['deductions'], 2) }}</td>
                    <td>₱{{ number_format($row['paid'], 2) }}</td>
                    <td class="{{ $row['balance'] >= 0 ? 'pos' : 'neg' }}">₱{{ number_format($row['balance'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Totals</td>
                <td>₱{{ number_format($totals['starting_balance'], 2) }}</td>
                <td>₱{{ number_format($totals['commission'], 2) }}</td>
                <td>₱{{ number_format($totals['payments'], 2) }}</td>
                <td>₱{{ number_format($totals['deductions'], 2) }}</td>
                <td>₱{{ number_format($totals['paid'], 2) }}</td>
                <td class="{{ $totals['balance'] >= 0 ? 'pos' : 'neg' }}">₱{{ number_format($totals['balance'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="note">
        Net Balance = Starting Balance + Commission + Payments − Deductions − Paid.<br>
        Positive = agency owes the agent (credit) · Negative = agent owes the agency (debit).
    </div>

    <script>window.print();</script>
</body>
</html>
