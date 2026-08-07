<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
    h1 { font-size: 20px; margin: 0 0 4px; }
    .meta { color: #6b7280; font-size: 11px; margin-bottom: 16px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
    th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
    th { background: #f3f4f6; }
    td.r, th.r { text-align: right; }
    .summary td { font-weight: bold; }
</style>
</head>
<body>
    <h1>📊 Agency Accounting Report</h1>
    <div class="meta">
        Generated {{ now()->format('Y-m-d H:i') }}
        @if($from) · From {{ $from->format('Y-m-d') }}@endif
        @if($to) · To {{ $to->format('Y-m-d') }}@endif
    </div>

    <table class="summary">
        <tr><td>Money In</td><td class="r">₱{{ number_format($moneyIn, 2) }}</td></tr>
        <tr><td>Money Out</td><td class="r">₱{{ number_format($moneyOut, 2) }} (expenses ₱{{ number_format($totalExpenses,2) }} + commissions ₱{{ number_format($totalCommissionsPaid,2) }})</td></tr>
        <tr><td>Balance</td><td class="r">₱{{ number_format($balance, 2) }}</td></tr>
    </table>

    <h2>Income by Category</h2>
    <table>
        <tr><th>Category</th><th class="r">Amount</th></tr>
        @forelse($incomeByAccount as $row)
            <tr><td>{{ $row['account_name'] }}</td><td class="r">₱{{ number_format($row['total'], 2) }}</td></tr>
        @empty
            <tr><td colspan="2">No income.</td></tr>
        @endforelse
    </table>

    <h2>Expenses by Account</h2>
    <table>
        <tr><th>Account</th><th class="r">Amount</th></tr>
        @forelse($expensesByAccount as $row)
            <tr><td>{{ $row['account_name'] }}</td><td class="r">₱{{ number_format($row['total'], 2) }}</td></tr>
        @empty
            <tr><td colspan="2">No expenses.</td></tr>
        @endforelse
    </table>

    <h2>Breakdown by Entity (Employer)</h2>
    <table>
        <tr><th>Employer</th><th class="r">Money In</th></tr>
        @forelse($byEntity as $row)
            <tr><td>{{ $row['employer_name'] }}</td><td class="r">₱{{ number_format($row['in'], 2) }}</td></tr>
        @empty
            <tr><td colspan="2">No entity data.</td></tr>
        @endforelse
    </table>
</body>
</html>
