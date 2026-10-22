@extends('layouts.app')

@section('title', 'Bulk Upload Positions')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('positions.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Positions
        </a>
    </div>

    <div class="card bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📥</span> Bulk Upload Positions
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Download the template, fill it in (one position per row), then upload the CSV to create them all at once.
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
        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">1️⃣ Download the template</h3>
            <p class="opacity-60 text-sm mb-4">
                CSV file with the exact columns and two sample rows (one household, one skilled)
                so you can follow the format. Open it in Excel or Google Sheets.
            </p>
            <a href="{{ route('positions.bulk.template') }}" class="btn btn-primary gap-2 self-start">
                <span>⬇️</span> Download CSV Template
            </a>
        </div>

        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">2️⃣ Fill it in &amp; upload</h3>
            <p class="opacity-60 text-sm mb-4">
                One position per row. <strong>Name</strong> is required; Description and Category are optional.
                Category accepts <em>household</em> or <em>skilled</em> (blank = skilled).
                Existing names are rejected so the file is all-or-nothing. Up to 2,000 rows per file.
            </p>
            <form action="{{ route('positions.bulk.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
                @csrf
                <input type="file" name="csv_file" accept=".csv,.txt,text/csv" class="file-input file-input-bordered w-full" required>
                <button type="submit" class="btn btn-success gap-2 self-start">
                    <span>🚀</span> Upload &amp; Import
                </button>
            </form>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md border border-base-300 p-5">
        <h3 class="font-semibold mb-3">📋 Expected columns</h3>
        <div class="overflow-x-auto">
            <table class="table table-sm">
                <thead>
                    <tr><th>Column</th><th>Required</th><th>Notes</th></tr>
                </thead>
                <tbody>
                    <tr><td class="font-mono">Name</td><td><span class="badge badge-error badge-sm">Yes</span></td><td>Must be unique across all positions.</td></tr>
                    <tr><td class="font-mono">Description</td><td><span class="badge badge-ghost badge-sm">No</span></td><td>Max 255 characters.</td></tr>
                    <tr><td class="font-mono">Category</td><td><span class="badge badge-ghost badge-sm">No</span></td><td><em>household</em> or <em>skilled</em>. Blank defaults to skilled.</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
