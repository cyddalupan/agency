<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>OR #{{ $or->or_no }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; font-size: 18px; }
        .header p { margin: 2px 0; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 6px 8px; text-align: left; border: 1px solid #ddd; }
        th { background: #f4f4f4; font-weight: bold; }
        .amount { text-align: right; }
        .footer { position: fixed; bottom: 20px; text-align: center; font-size: 10px; color: #999; width: 100%; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Official Receipt</h1>
        <p>OR #: <strong>{{ $or->or_no }}</strong></p>
        <p>Issue Date: {{ \Carbon\Carbon::parse($or->issue_date)->format('F d, Y') }}</p>
    </div>

    <table>
        <tr>
            <td style="width: 25%; border: none;"><strong>Issued To:</strong></td>
            <td style="border: none;">{{ $or->issued_to_name }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Type:</strong></td>
            <td style="border: none;">{{ ucfirst($or->issued_to) }}</td>
        </tr>
        @if($or->payment)
        <tr>
            <td style="border: none;"><strong>Payment Type:</strong></td>
            <td style="border: none;">{{ ucfirst(str_replace('_', ' ', $or->payment->type ?? '')) }}</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>Reference:</strong></td>
            <td style="border: none;">{{ $or->payment->reference_no ?? 'N/A' }}</td>
        </tr>
        @endif
        <tr>
            <td style="border: none;"><strong>Notes:</strong></td>
            <td style="border: none;">{{ $or->notes ?? 'N/A' }}</td>
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
                <td>Official Receipt - {{ $or->or_no }}</td>
                <td class="amount">{{ number_format($or->amount, 2) }}</td>
            </tr>
            <tr class="total-row">
                <td><strong>Total</strong></td>
                <td class="amount"><strong>₱{{ number_format($or->amount, 2) }}</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        This is a computer-generated document. No signature required.
    </div>
</body>
</html>
