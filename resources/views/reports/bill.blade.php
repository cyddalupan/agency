<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bill #{{ $bill->id }}</title>
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
        <h1>Billing Statement</h1>
        <p>Date: {{ now()->format('F d, Y') }}</p>
    </div>

    <table>
        <tr>
            <td style="width: 25%; border: none;"><strong>Employer:</strong></td>
            <td style="border: none;">{{ $bill->employer?->name ?? 'N/A' }}</td>
        </tr>
        @if($bill->applicant)
        <tr>
            <td style="border: none;"><strong>Applicant:</strong></td>
            <td style="border: none;">{{ $bill->applicant?->full_name ?? 'N/A' }}</td>
        </tr>
        @endif
        <tr>
            <td style="border: none;"><strong>Status:</strong></td>
            <td style="border: none;">{{ ucfirst(str_replace('_', ' ', $bill->status)) }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Notes:</strong></td>
            <td style="border: none;">{{ $bill->notes ?? 'N/A' }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th class="amount">Amount (₱)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Employer Cost</td>
                <td class="amount">{{ number_format($bill->employer_cost, 2) }}</td>
            </tr>
            <tr>
                <td>Applicant Cost</td>
                <td class="amount">{{ number_format($bill->applicant_cost, 2) }}</td>
            </tr>
            <tr>
                <td>Employer Deposit</td>
                <td class="amount">{{ number_format($bill->employer_deposit, 2) }}</td>
            </tr>
            <tr>
                <td>Applicant Deposit</td>
                <td class="amount">{{ number_format($bill->applicant_deposit, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="amount"><strong>₱{{ number_format($bill->employer_cost + $bill->applicant_cost + $bill->employer_deposit + $bill->applicant_deposit, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This is a computer-generated document. No signature required.
    </div>
</body>
</html>
