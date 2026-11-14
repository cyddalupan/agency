@extends('layouts.app')

@section('title', 'Welfare')

@section('content')
<div class="w-full">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🤝</span> Welfare
            </h2>
            <p class="opacity-60 text-sm mt-1">
                Welfare monitoring for applicants — {{ number_format($total) }} record{{ $total === 1 ? '' : 's' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('applicants.withdrawn') }}" class="btn btn-outline btn-sm">
                <span>🗂️</span> Backout, Cancelled &amp; Repat
            </a>
            <a href="{{ route('welfare.export', request()->query()) }}" class="btn btn-ghost btn-sm">
                <span>⬇️</span> Extract to CSV
            </a>
            <a href="{{ route('welfare.create') }}" class="btn btn-primary">
                <span>➕</span> Create Welfare
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span><span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filters: Name, Passport, DMW E-Reg ID, Country, Position, FRA --}}
    <form method="GET" action="{{ route('welfare.index') }}" class="card bg-base-100 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3">
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Name</span>
                <input type="text" name="name" value="{{ request('name') }}" class="input input-bordered input-sm w-full">
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Passport No.</span>
                <input type="text" name="passport" value="{{ request('passport') }}" class="input input-bordered input-sm w-full">
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">DMW E-Reg ID</span>
                <input type="text" name="dmw_ereg_id" value="{{ request('dmw_ereg_id') }}" class="input input-bordered input-sm w-full">
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Country</span>
                <input type="text" name="country" value="{{ request('country') }}" list="country-list"
                       class="input input-bordered input-sm w-full">
                <datalist id="country-list">
                    @foreach($countries as $c)<option value="{{ $c->name }}"></option>@endforeach
                </datalist>
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Position</span>
                <input type="text" name="position" value="{{ request('position') }}" class="input input-bordered input-sm w-full">
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">FRA</span>
                <input type="text" name="fra" value="{{ request('fra') }}" list="fra-list" class="input input-bordered input-sm w-full">
                <datalist id="fra-list">
                    @foreach($employers as $e)<option value="{{ $e->name }}"></option>@endforeach
                </datalist>
            </label>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
            <a href="{{ route('welfare.index') }}" class="btn btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    @if($records->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Name</th>
                            <th>Passport No.</th>
                            <th>DMW E-Reg ID</th>
                            <th>Country</th>
                            <th>Job Site City</th>
                            <th>Position</th>
                            <th>FRA</th>
                            <th>Remarks / Issues / Actions Taken</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($records as $w)
                        <tr class="hover align-top">
                            <td class="font-medium">
                                {{ $w->ofw_full_name ?: '—' }}
                                @if($w->applicant_id)
                                    <a href="{{ route('applicants.show', $w->applicant_id) }}"
                                       class="link link-primary text-xs ml-1" title="Open applicant">↗</a>
                                @endif
                                <div class="text-[11px] opacity-50">
                                    {{ optional($w->date_of_contact)->format('M d, Y') }}
                                    · {{ $w->encoder ?: ($w->logged_by ?: '—') }}
                                </div>
                            </td>
                            <td class="font-mono text-xs">{{ $w->passport_number ?: '—' }}</td>
                            <td class="font-mono text-xs">{{ $w->dmw_ereg_id ?: '—' }}</td>
                            <td>{{ $w->country_of_destination ?: '—' }}</td>
                            <td>{{ $w->job_site_city ?: '—' }}</td>
                            <td>{{ $w->position_designation ?: '—' }}</td>
                            <td>{{ $w->foreign_recruitment_agency ?: '—' }}</td>
                            <td class="text-xs max-w-md">
                                <div class="whitespace-pre-line">{{ \Illuminate\Support\Str::limit($w->remarks, 180) ?: '—' }}</div>
                                @if($w->document_path)
                                    <a href="{{ route('welfare.document', $w) }}" class="link link-primary text-xs" target="_blank">
                                        📎 {{ $w->document_name ?: 'document' }}
                                    </a>
                                @endif
                            </td>
                            <td class="text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('welfare.edit', $w) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('welfare.destroy', $w) }}" method="POST"
                                          onsubmit="return confirm('Delete this welfare record?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-base-300">{{ $records->links() }}</div>
        </div>
    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-14 opacity-60">
                <div class="text-5xl mb-3">🤝</div>
                <p class="font-semibold">No welfare records{{ request()->hasAny(['name','passport','dmw_ereg_id','country','position','fra']) ? ' for this filter' : ' yet' }}</p>
                <p class="text-sm">Create one to log a welfare contact, issue or action taken.</p>
                <div class="mt-4">
                    <a href="{{ route('welfare.create') }}" class="btn btn-primary btn-sm">➕ Create Welfare</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
