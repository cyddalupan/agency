@extends('layouts.app')

@section('title', 'Bulk Upload Expense Requests')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('expense_request.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Expenses &amp; Payments
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📥</span> Bulk Upload Expense Requests
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Download the template, fill it in (one row = one expense request with a single line item),
            then upload the CSV to create them all at once as <em>Pending</em>.
        </p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>⛔</span>
            <ul class="list-disc pl-4">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('bulk_errors'))
        <div role="alert" class="alert alert-error mb-6 shadow-sm items-start">
            <span>⚠️</span>
            <div class="w-full">
                <strong class="block mb-2">
                    No rows were imported — {{ count(session('bulk_errors')) }} row(s) need fixing.
                    Fix them in your file, then upload again.
                </strong>
                <div class="overflow-x-auto">
                    <table class="table table-sm table-zebra w-full text-sm">
                        <thead>
                            <tr>
                                <th>CSV Line</th>
                                <th>Problems</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(session('bulk_errors') as $rowError)
                                <tr>
                                    <td class="font-mono align-top">Line {{ $rowError['line'] }}</td>
                                    <td>
                                        <ul class="list-disc pl-4 space-y-0.5">
                                            @foreach($rowError['errors'] as $msg)
                                                <li>{{ $msg }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        {{-- Step 1: template --}}
        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">1️⃣ Download the template</h3>
            <p class="opacity-60 text-sm mb-4">
                CSV file with the exact columns and sample rows so you can follow the format.
                Open it in Excel or Google Sheets.
            </p>
            <a href="{{ route('expense_request.bulk.template') }}" class="btn btn-primary gap-2 self-start">
                <span>⬇️</span> Download CSV Template
            </a>
        </div>

        {{-- Step 2: upload --}}
        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">2️⃣ Fill it in &amp; upload</h3>
            <p class="opacity-60 text-sm mb-4">
                One expense request per row, using the same columns as your Expense sheet.
                <strong>Charge</strong> (<em>Office</em> or <em>Agent</em>), <strong>Account</strong>
                (sub-account name), <strong>Currency</strong> (<em>PHP</em> or <em>USD</em>) and
                <strong>Amount</strong> are required. Date blank = today. Payment optional (already paid,
                default 0). <strong>Office</strong> is the branch, <strong>Request Status</strong> is honoured
                when filled (blank = Pending), and <strong>Ref #</strong> / <strong>User</strong> are the
                generated reference and the encoder (blank = you). Up to 2,000 rows per file.
            </p>
            <form action="{{ route('expense_request.bulk.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <input type="file" name="csv_file" accept=".csv,.txt,text/csv" class="file-input file-input-bordered w-full" required>
                <button type="submit" class="btn btn-success gap-2 self-start">
                    <span>🚀</span> Upload &amp; Import
                </button>
            </form>
        </div>
    </div>

    {{-- Reference lists --}}
    <details class="card bg-base-100 shadow-md border border-base-300 p-5 mb-6" open>
        <summary class="cursor-pointer font-semibold">📋 Reference: account names, branches &amp; charge rules (to avoid row errors)</summary>
        <div class="mt-4 text-sm space-y-4">
            <div>
                <strong class="block mb-1">Accounts (use the sub-account name, e.g. <em>Rent</em>)</strong>
                @if($mains->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                        @foreach($mains as $main)
                            @if($main->children->isEmpty())
                                <span class="badge badge-ghost">{{ $main->name }} ({{ $main->charge_type ?? 'office' }})</span>
                            @else
                                @foreach($main->children as $child)
                                    <span class="badge badge-ghost">{{ $main->name }} → {{ $child->name }} ({{ $child->charge_type ?? $main->charge_type ?? 'office' }})</span>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                    <p class="opacity-60 mt-1">Account type must match the Charge: Charge <em>Agent</em> → agent account; Charge <em>Office</em> → office account (or an applicant account when an Applicant is set).</p>
                @else
                    <span class="opacity-60">No accounts set up yet — create them under Accounting before bulk uploading.</span>
                @endif
            </div>
            <div>
                <strong class="block mb-1">Branches (optional column)</strong>
                @if($branches->isNotEmpty())
                    <div class="flex flex-wrap gap-1">
                        @foreach($branches as $b)
                            <span class="badge badge-ghost">{{ $b->name }}</span>
                        @endforeach
                    </div>
                @else
                    <span class="opacity-60">No branches set up yet — leave the Branch column blank.</span>
                @endif
            </div>
        </div>
    </details>

    <div class="text-xs opacity-50">
        Matching is case/space-insensitive. Applicant/Agent are matched by exact full name (rows with an ambiguous
        name are rejected, not silently picked). If any row has a problem, <strong>nothing</strong> is imported until
        every row is valid (all-or-nothing). Requests created here follow the same rules as the New Expense Request form.
    </div>
</div>
@endsection
