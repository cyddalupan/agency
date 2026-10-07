@extends('layouts.app')

@section('title', 'Bulk Upload FRAs')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employers.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to FRAs
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📥</span> Bulk Upload FRAs
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Download the template, fill it in (one FRA per row), then upload the CSV to create them all at once.
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
            <a href="{{ route('employers.bulk.template') }}" class="btn btn-primary gap-2 self-start">
                <span>⬇️</span> Download CSV Template
            </a>
        </div>

        {{-- Step 2: upload --}}
        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">2️⃣ Fill it in &amp; upload</h3>
            <p class="opacity-60 text-sm mb-4">
                One FRA per row. Company Name is required; everything else is optional.
                Case and spacing don't matter — <em>ACTIVE</em> or <em>In active</em> both work for status,
                and country names are matched the same way. Up to 2,000 rows per file.
            </p>
            <form action="{{ route('employers.bulk.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
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
        <summary class="cursor-pointer font-semibold">📋 Reference: exact country names (to avoid row errors)</summary>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-sm">
            @if($countries->isNotEmpty())
                <div>
                    <strong class="block mb-1">Countries</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($countries as $c)
                            <span class="badge badge-ghost">{{ $c->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="mt-4">
            <strong class="block mb-1">Status (use the label)</strong>
            <div class="flex flex-wrap gap-1">
                <span class="badge badge-outline">Active</span>
                <span class="badge badge-outline">Inactive</span>
            </div>
        </div>
    </details>

    <div class="text-xs opacity-50">
        Notes: matching is case/space-insensitive. Duplicate FRAs are <strong>not</strong> blocked — the import
        creates every row as-is. A row with an email also gets an FRA login account auto-created (one per email,
        matching the Add FRA form). Commission and custom fields are managed per FRA after creation.
    </div>
</div>
@endsection
