@extends('layouts.app')

@section('title', 'Bulk Upload Applicants')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('applicants.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Applicants
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📥</span> Bulk Upload Applicants
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Download the template, fill it in (one applicant per row), then upload the CSV to create them all at once.
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
                CSV file with the exact columns and sample rows (different statuses included)
                so you can follow the format. Open it in Excel or Google Sheets.
            </p>
            <a href="{{ route('applicants.bulk.template') }}" class="btn btn-primary gap-2 self-start">
                <span>⬇️</span> Download CSV Template
            </a>
        </div>

        {{-- Step 2: upload --}}
        <div class="card bg-base-100 shadow-md border border-base-300 p-5">
            <h3 class="text-lg font-bold mb-1">2️⃣ Fill it in &amp; upload</h3>
            <p class="opacity-60 text-sm mb-4">
                One applicant per row. First Name and Last Name are required; everything else is optional.
                Case and spacing don't matter for lookups — <em>PENDING</em> or <em>For  Interview</em> both work.
                Age (1–120), Experience (<em>Firstimer</em> / <em>Ex-Abroad</em>), Date Applied and passport
                details (Passport #, Date Issued, Place Issued, Expiration) are optional.
                Up to 2,000 rows per file.
            </p>
            <form action="{{ route('applicants.bulk.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col gap-3">
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
        <summary class="cursor-pointer font-semibold">📋 Reference: exact names for lookups (to avoid row errors)</summary>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 text-sm">
            @if($agents->isNotEmpty())
                <div>
                    <strong class="block mb-1">Agents</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($agents as $agent)
                            <span class="badge badge-ghost">{{ $agent->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($branches->isNotEmpty())
                <div>
                    <strong class="block mb-1">Branches</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($branches as $branch)
                            <span class="badge badge-ghost">{{ $branch->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($employers->isNotEmpty())
                <div>
                    <strong class="block mb-1">Employers</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($employers as $employer)
                            <span class="badge badge-ghost">{{ $employer->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($nationalities->isNotEmpty())
                <div>
                    <strong class="block mb-1">Nationalities</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($nationalities as $n)
                            <span class="badge badge-ghost">{{ $n->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($religions->isNotEmpty())
                <div>
                    <strong class="block mb-1">Religions</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($religions as $r)
                            <span class="badge badge-ghost">{{ $r->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
            @if($civilStatuses->isNotEmpty())
                <div>
                    <strong class="block mb-1">Civil Status</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($civilStatuses as $cs)
                            <span class="badge badge-ghost">{{ $cs->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
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
            @if($positions->isNotEmpty())
                <div>
                    <strong class="block mb-1">Positions</strong>
                    <div class="flex flex-wrap gap-1">
                        @foreach($positions as $p)
                            <span class="badge badge-ghost">{{ $p->name }}</span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <div class="mt-4">
            <strong class="block mb-1">Statuses (use the label or the code number)</strong>
            <div class="flex flex-wrap gap-1">
                @foreach($statusCodes as $sc)
                    <span class="badge badge-outline">{{ $sc->code }} - {{ $sc->label }}</span>
                @endforeach
            </div>
        </div>
    </details>

    <div class="text-xs opacity-50">
        Notes: matching is case/space-insensitive. Duplicate applicants are <strong>not</strong> blocked — the import
        creates every row as-is. Age, Experience (Firstimer / Ex-Abroad), Date Applied, and passport details
        (Passport #, Date Issued, Place Issued, Expiration) can be imported too when filled in. Age is derived
        from Birthdate — when a row has an Age but no Birthdate, the system fills an approximate Birthdate so the
        Age still shows. Other family and document details are managed per applicant after creation.
    </div>
</div>
@endsection
