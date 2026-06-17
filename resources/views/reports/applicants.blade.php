@extends('layouts.app')

@section('title', 'Applicant Reports')

@push('styles')
<style>
@media print {
    .no-print { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; }
    .container { max-width: 100% !important; padding: 0 !important; }
    body { font-size: 11pt; }
    table { font-size: 9pt; width: 100%; }
    th { background: #f5f5f5 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
@endpush

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Applicant Reports</h1>

    <!-- Filters -->
    <div class="card bg-base-100 shadow-xl mb-6 no-print">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Filters</h2>
            <form method="GET" action="{{ route('reports.applicants') }}" class="flex flex-wrap gap-4 items-end">
                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status_code" class="select select-bordered w-full sm:w-48">
                        <option value="">All Statuses</option>
                        @foreach($statusCodes as $code)
                            <option value="{{ $code->code }}" {{ request('status_code') == $code->code ? 'selected' : '' }}>
                                {{ $code->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">Country</span></label>
                    <select name="country_id" class="select select-bordered w-full sm:w-48">
                        <option value="">All Countries</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->id }}" {{ request('country_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">Date From</span></label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                           class="input input-bordered w-full sm:w-44">
                </div>

                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">Date To</span></label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                           class="input input-bordered w-full sm:w-44">
                </div>

                <div class="form-control">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>

                <div class="form-control">
                    <a href="{{ route('reports.applicants') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Results -->
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h2 class="card-title">Results ({{ $applicants->count() }})</h2>
                <div class="flex gap-2 no-print">
                    <a href="{{ route('applicants.export') }}" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Export CSV
                    </a>
                    <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Country</th>
                            <th>Contact</th>
                            <th>Email</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $applicant)
                            <tr>
                                <td class="font-medium">
                                    <a href="{{ route('applicants.show', $applicant) }}" class="link link-hover">
                                        {{ $applicant->first_name }} {{ $applicant->last_name }}
                                    </a>
                                </td>
                                <td>
                                    <span class="badge badge-ghost">{{ $applicant->statusCode?->label ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $applicant->country?->name ?? '—' }}</td>
                                <td>{{ $applicant->contact ?? '—' }}</td>
                                <td>{{ $applicant->email }}</td>
                                <td>{{ $applicant->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-base-content/50 py-8">
                                    No applicants found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
