@extends('layouts.app')

@section('title', 'Welfare Report')

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
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">🤝 Welfare Report</h1>
        <a href="{{ route('reports.welfare.export', request()->query()) }}"
           class="btn btn-outline btn-sm no-print">⬇️ Extract to CSV</a>
    </div>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-xl mb-6 no-print">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Filters</h2>
            <form method="GET" action="{{ route('reports.welfare') }}" class="flex flex-wrap gap-4 items-end">
                <div class="form-control w-full sm:w-auto flex-1">
                    <label class="label"><span class="label-text">Name</span></label>
                    <input type="text" name="name" class="input input-bordered w-full" placeholder="Name..."
                           value="{{ request('name') }}" />
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">Country</span></label>
                    <select name="country" class="select select-bordered w-full sm:w-44">
                        <option value="">All</option>
                        @foreach($countries as $c)
                            <option value="{{ $c->name }}" {{ request('country') == $c->name ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">FRA</span></label>
                    <input type="text" name="fra" class="input input-bordered w-full sm:w-48" placeholder="FRA..."
                           value="{{ request('fra') }}" />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">From</span></label>
                    <input type="date" name="from" class="input input-bordered" value="{{ request('from') }}" />
                </div>
                <div class="form-control">
                    <label class="label"><span class="label-text">To</span></label>
                    <input type="date" name="to" class="input input-bordered" value="{{ request('to') }}" />
                </div>
                <div class="form-control">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="form-control">
                    <a href="{{ route('reports.welfare') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex items-center justify-between mb-4">
                <h2 class="card-title text-lg">Results ({{ $records->count() }})</h2>
            </div>

            @if($records->isEmpty())
                <p class="text-sm opacity-60 py-8 text-center">No welfare records match these filters.</p>
            @else
            <div class="overflow-x-auto">
                <table class="table table-sm table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Passport No.</th>
                            <th>DMW E-Reg ID</th>
                            <th>Country</th>
                            <th>Job Site City</th>
                            <th>Position</th>
                            <th>FRA</th>
                            <th>Remarks / Issues / Actions Taken</th>
                            <th>Encoder</th>
                            <th>Date of Contact</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $w)
                        <tr>
                            <td class="font-medium">{{ $w->ofw_full_name }}</td>
                            <td>{{ $w->passport_number }}</td>
                            <td>{{ $w->dmw_ereg_id }}</td>
                            <td>{{ $w->country_of_destination }}</td>
                            <td>{{ $w->job_site_city }}</td>
                            <td>{{ $w->position_designation }}</td>
                            <td>{{ $w->foreign_recruitment_agency }}</td>
                            <td class="max-w-xs">{{ \Illuminate\Support\Str::limit($w->remarks, 90) }}</td>
                            <td>{{ $w->encoder ?: $w->logged_by }}</td>
                            <td>{{ optional($w->date_of_contact)->format('Y-m-d') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
