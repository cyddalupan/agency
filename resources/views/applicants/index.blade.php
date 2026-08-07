@extends('layouts.app')

@section('title', 'Applicants')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>👥</span> Applicants
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage and track all applicants in the pipeline</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('applicants.create') }}" class="btn btn-primary">
                <span>➕</span> Add Applicant
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Status Pipeline Chips --}}
    <div class="flex flex-wrap gap-2 mb-3">
        <a href="{{ route('applicants.index') }}"
           class="btn btn-xs gap-1 {{ request()->query('status') === null ? 'btn-primary' : 'btn-ghost' }}">
            All
            <span class="badge badge-xs {{ request()->query('status') === null ? 'badge-outline' : '' }}">{{ $statusCounts->sum() }}</span>
        </a>
        @foreach($statusCodes as $sc)
            @php $count = $statusCounts->get($sc->code, 0); @endphp
            @if($count > 0)
            <a href="{{ route('applicants.index', ['status' => $sc->code]) }}"
               class="btn btn-xs gap-1 {{ request('status') === (string)$sc->code ? 'btn-primary' : 'btn-ghost' }}"
               @if(request('status') !== (string)$sc->code) style="background-color: {{ $sc->color ?? '#e5e7eb' }}15;" @endif>
                {{ $sc->label }}
                <span class="badge badge-xs {{ request('status') === (string)$sc->code ? 'badge-outline' : '' }}">{{ $count }}</span>
            </a>
            @endif
        @endforeach
    </div>

    @if($applicants->count())
        {{-- Search & Filters --}}
        <form method="GET" action="{{ route('applicants.index') }}" class="mb-4" id="filter-form">
            {{-- Row 1: Search bar full width --}}
            <div class="flex gap-3 items-end mb-3">
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <span>🔍</span>
                        <input type="text" name="search" class="grow" placeholder="Search by name, email, or contact..."
                               value="{{ request('search') }}" />
                        @if(request('search') || request('status') || request('gender') || request('employer') || request('country'))
                            <a href="{{ route('applicants.index') }}" class="btn btn-ghost btn-xs btn-square" title="Clear filters">✕</a>
                        @endif
                    </label>
                </div>
            </div>

            {{-- Row 2: Filters in a responsive grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-5 gap-3 items-end">
                {{-- Status filter (from pipeline chips) --}}
                <div class="form-control w-full">
                    <select name="status" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">📊 All Status</option>
                        @foreach($statusCodes as $sc)
                            <option value="{{ $sc->code }}" {{ request('status') === (string)$sc->code ? 'selected' : '' }}>
                                {{ $sc->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender filter --}}
                <div class="form-control w-full">
                    <select name="gender" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">⚤ Gender</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>👨 Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>👩 Female</option>
                    </select>
                </div>

                {{-- Employer filter --}}
                <div class="form-control w-full">
                    <select name="employer" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">🏢 Employer</option>
                        @foreach($employers as $employer)
                            <option value="{{ $employer->id }}" {{ request('employer') == $employer->id ? 'selected' : '' }}>
                                {{ $employer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Country filter --}}
                <div class="form-control w-full">
                    <select name="country" class="select select-bordered select-sm" onchange="this.form.submit()">
                        <option value="">🌍 Country</option>
                        @foreach($countries as $country)
                            <option value="{{ $country->id }}" {{ request('country') == $country->id ? 'selected' : '' }}>
                                {{ $country->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Submit / Clear --}}
                <div class="flex gap-2 items-end">
                    <button type="submit" class="btn btn-primary btn-sm">🔍 Search</button>
                    @if(request('search') || request('status') || request('gender') || request('employer') || request('country'))
                        <a href="{{ route('applicants.index') }}" class="btn btn-ghost btn-sm">✕ Clear</a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Stats summary + Export --}}
        <div class="flex flex-wrap items-center justify-between gap-2 mb-4 text-sm">
            <div class="flex flex-wrap gap-2">
                <span class="badge badge-ghost badge-lg">📋 {{ $applicants->total() }} total</span>
                @if(request('search') || request('status') || request('gender') || request('employer') || request('country'))
                    <span class="badge badge-warning badge-lg">🔍 {{ $applicants->count() }} matching</span>
                @endif
            </div>
            <a href="{{ route('applicants.export', request()->only(['search', 'status', 'gender', 'employer', 'country'])) }}"
               class="btn btn-sm btn-outline btn-success gap-1">
                <span>📥</span> Export to CSV
            </a>
        </div>

        {{-- Table --}}
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th class="w-10"></th>
                            <th>📅 Date Applied</th>
                            <th>Name</th>
                            <th>📊 Status</th>
                            <th>🎂 Age</th>
                            <th>📞 Contact#</th>
                            <th>💼 Position</th>
                            <th>🏢 Branch</th>
                            <th>🎯 Agent</th>
                            <th>📄 Contract</th>
                            <th>✅ Contract Received</th>
                            <th>🧑‍💻 Encoder</th>
                            <th>🧑🎤 Created By</th>
                            <th>🗓️ Created At</th>
                            <th class="text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applicants as $applicant)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="avatar">
                                    <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold overflow-hidden">
                                        @if ($applicant->photo)
                                            <img src="{{ $applicant->photo_url }}" class="w-full h-full object-cover" alt="{{ $applicant->full_name }}">
                                        @else
                                            {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-sm whitespace-nowrap">{{ $applicant->created_at->format('M d, Y') }}</td>
                            <td>
                                <a href="{{ route('applicants.show', $applicant) }}" class="link link-primary font-medium">
                                    {{ $applicant->first_name }} {{ $applicant->last_name }}
                                </a>
                                @if($applicant->middle_name)
                                    <span class="opacity-40 text-sm"> {{ $applicant->middle_name }}</span>
                                @endif
                            </td>
                            <td>
                                @if($applicant->statusCode)
                                    <span class="badge badge-sm whitespace-nowrap"
                                        style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
                                        {{ $applicant->statusCode->label }}
                                    </span>
                                @else
                                    <span class="badge badge-sm badge-ghost whitespace-nowrap">📋 Pending</span>
                                @endif
                            </td>
                            <td class="text-sm opacity-70">
                                {{ $applicant->age ?? '—' }}
                            </td>
                            <td class="text-sm">
                                @if($applicant->contact)
                                    {{ $applicant->contact }}
                                @elseif($applicant->email)
                                    {{ $applicant->email }}
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                {{ $applicant->position?->name ?? '—' }}
                            </td>
                            <td class="text-sm">
                                {{ $applicant->branch?->name ?? '—' }}
                            </td>
                            <td class="text-sm">
                                {{ $applicant->agent?->name ?? '—' }}
                            </td>
                            <td class="text-sm">
                                @if ($applicant->contract)
                                    <a href="{{ Storage::url($applicant->contract) }}" target="_blank"
                                       class="link link-primary text-sm" title="View contract">📄 View</a>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                @if ($applicant->contract_received_date)
                                    <span class="text-success font-medium">{{ $applicant->contract_received_date->format('M d, Y') }}</span>
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                {{ $applicant->encoder ?? '—' }}
                            </td>
                            <td class="text-sm">{{ $applicant->creator?->name ?? '—' }}</td>
                            <td class="text-sm whitespace-nowrap">{{ $applicant->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <button type="button" class="btn btn-ghost btn-xs btn-square text-error" title="Delete"
                                        onclick="document.getElementById('delete-applicant-{{ $applicant->id }}').showModal()">🗑️</button>
                                </div>
                                <dialog id="delete-applicant-{{ $applicant->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="font-bold text-lg mb-2">Delete Applicant</h3>
                                        <p>Are you sure you want to delete <strong>{{ $applicant->first_name }} {{ $applicant->last_name }}</strong>?</p>
                                        <p class="text-sm opacity-60 mt-2">This action cannot be undone. All associated data (education, work history, documents, etc.) will also be removed.</p>
                                        <div class="modal-action">
                                            <form method="POST" action="{{ route('applicants.destroy', $applicant) }}">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-error">🗑️ Delete</button>
                                            </form>
                                            <button class="btn btn-ghost" onclick="document.getElementById('delete-applicant-{{ $applicant->id }}').close()">Cancel</button>
                                        </div>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $applicants->links() }}
        </div>
    @else
        {{-- Empty state --}}
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">👤</span>
                <h3 class="text-xl font-bold mb-2">No Applicants Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Get started by adding your first applicant. You'll be able to track their entire deployment journey from registration to exit clearance.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('applicants.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Add Your First Applicant
                    </a>
                    <a href="{{ route('employers.create') }}" class="btn btn-outline btn-lg">
                        <span>🏢</span> Add an Employer First
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick tips --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📝</span>
                    <h4 class="font-semibold text-sm">Fill Details</h4>
                    <p class="text-xs opacity-60 mt-1">Add personal info, education, work history</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📄</span>
                    <h4 class="font-semibold text-sm">Upload Docs</h4>
                    <p class="text-xs opacity-60 mt-1">Passport, certificates, requirements</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">✈️</span>
                    <h4 class="font-semibold text-sm">Track Status</h4>
                    <p class="text-xs opacity-60 mt-1">Follow the pipeline from pending to deployed</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection