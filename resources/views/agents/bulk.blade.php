@extends('layouts.app')

@section('title', 'Bulk Upload ' . agent_terms())

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('agents.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to {{ agent_terms() }}
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📥</span> Bulk Upload {{ agent_terms() }}
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Download the template, fill it in (one {{ strtolower(agent_term()) }} per row), then upload the CSV to create them all at once.
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
                CSV file with the exact columns and sample rows (Active and Inactive statuses included)
                so you can follow the format. Open it in Excel or Google Sheets.
            </p>
            <a href="{{ route('agents.bulk.template') }}" class="btn btn-primary gap-2 self-start">
                <span>⬇️</span> Download CSV Template
            </a>
        </div>

        {{-- Step 2: upload --}}
        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">2️⃣ Fill it in &amp; upload</h3>
            <p class="opacity-60 text-sm mb-4">
                One {{ strtolower(agent_term()) }} per row. Only <strong>Name</strong> is required — <strong>Email</strong> is optional. When an
                email is given it must be unique (not repeated in the file and not already used by an existing {{ strtolower(agent_term()) }}).
                Everything else is optional: Branch by name, Commission Rate 0–100, and Status (<em>Active</em> or
                <em>Inactive</em>; blank = Active). Up to 2,000 rows per file.
            </p>
            <form action="{{ route('agents.bulk.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <input type="file" name="csv_file" accept=".csv,.txt,text/csv" class="file-input file-input-bordered w-full" required>
                <button type="submit" class="btn btn-success gap-2 self-start">
                    <span>🚀</span> Upload &amp; Import
                </button>
            </form>
        </div>
    </div>

    {{-- Reference lists --}}
    <details class="card bg-base-100 shadow-md border border-base-300 p-5 mb-6">
        <summary class="cursor-pointer font-semibold">📋 Reference: branch names &amp; statuses (to avoid row errors)</summary>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4 text-sm">
            <div>
                <strong class="block mb-1">Branches</strong>
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
            <div>
                <strong class="block mb-1">Status (use the label)</strong>
                <div class="flex flex-wrap gap-1">
                    <span class="badge badge-outline">Active</span>
                    <span class="badge badge-outline">Inactive</span>
                </div>
            </div>
        </div>
    </details>

    <div class="text-xs opacity-50">
        Notes: matching is case/space-insensitive. Emails are optional; when given they must be unique — rows with a
        duplicate or already-registered email are rejected and nothing is imported until every row is valid
        (all-or-nothing). {{ agent_terms() }} created here follow the same rules as the Add {{ agent_term() }} form.
    </div>
</div>
@endsection
