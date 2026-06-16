<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Commission Statement</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f4f4f4; font-weight: bold; }
        .amount { text-align: right; }
        .total-row td { font-weight: bold; }
        .footer { position: fixed; bottom: 20px; text-align: center; font-size: 10px; color: #999; width: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Commission Statement</h1>
        <p>Date: {{ now()->format('F d, Y') }}</p>
    </div>

    <table>
        <tr>
            <td style="width: 25%; border: none;"><strong>Employer:</strong></td>
            <td style="border: none;">{{ $commission->employer?->name ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Type:</strong></td>
            <td style="border: none;">{{ ucfirst(str_replace('_', ' ', $commission->commissionable_type ?? '')) }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Status:</strong></td>
            <td style="border: none;">{{ ucfirst($commission->status) }}</td>
        </tr>
        @if($commission->due_date)
        <tr>
            <td style="border: none;"><strong>Due Date:</strong></td>
            <td style="border: none;">{{ \Carbon\Carbon::parse($commission->due_date)->format('F d, Y') }}</td>
        </tr>
        @endif
        <tr>
            <td style="border: none;"><strong>Notes:</strong></td>
            <td style="border: none;">{{ $commission->notes ?? 'N/A' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="amount">Amount (₱)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Commission Amount</td>
                <td class="amount">{{ number_format($commission->amount, 2) }}</td>
            </tr>
            @if($commission->paid_amount > 0)
            <tr>
                <td>Paid Amount</td>
                <td class="amount">-{{ number_format($commission->paid_amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Balance</strong></td>
                <td class="amount"><strong>₱{{ number_format($commission->balance, 2) }}</strong></td>
            </tr>
            @else
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="amount"><strong>₱{{ number_format($commission->amount, 2) }}</strong></td>
            </tr>
            @endif
        </tbody>
    </table>

    <div class="footer">
        This is a computer-generated document. No signature required.
    </div>
</body>
</html>
