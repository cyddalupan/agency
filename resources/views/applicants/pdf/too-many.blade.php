<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Too many rows for PDF</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            background: #f3f4f6;
            color: #111827;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 24px;
        }
        .card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            max-width: 560px;
            padding: 32px;
        }
        h1 { font-size: 18pt; margin-bottom: 12px; color: #b91c1c; }
        p { font-size: 11pt; line-height: 1.6; margin-bottom: 12px; color: #374151; }
        .numbers {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 14px 16px;
            margin: 18px 0;
            font-size: 11pt;
        }
        .numbers strong { color: #111827; }
        .actions { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 20px; }
        a.btn {
            text-decoration: none;
            padding: 10px 18px;
            border-radius: 6px;
            font-size: 10.5pt;
            font-weight: 600;
            display: inline-block;
        }
        a.primary { background: #1a56db; color: #fff; }
        a.secondary { background: #fff; color: #1a56db; border: 1px solid #1a56db; }
        a.ghost { background: #fff; color: #6b7280; border: 1px solid #d1d5db; }
    </style>
</head>
<body>
    <div class="card">
        <h1>This view is too large for a PDF</h1>

        <div class="numbers">
            <strong>{{ number_format($total) }}</strong> applicants match your filters.
            A PDF is limited to <strong>{{ number_format($limit) }}</strong> rows.
        </div>

        <p>
            PDFs are built entirely in memory, so a document this size cannot be
            generated reliably. Rather than hand you a PDF that quietly leaves rows
            out, we stopped here.
        </p>
        <p>
            The CSV export has no row limit and contains exactly the same rows and
            columns, so it is the better choice for a set this large.
        </p>

        <div class="actions">
            <a href="{{ $csvUrl }}" class="btn primary">Download CSV instead</a>
            <a href="javascript:history.back()" class="btn secondary">Narrow the filters</a>
        </div>
    </div>
</body>
</html>
