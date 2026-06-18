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

    @if($applicants->count())
        {{-- Search & Filters --}}
        <form method="GET" action="{{ route('applicants.index') }}" class="mb-4" id="filter-form">
            <div class="flex flex-col sm:flex-row gap-3 items-end">
                {{-- Search --}}
                <div class="form-control flex-1">
                    <label class="input input-bordered flex items-center gap-2">
                        <span>🔍</span>
                        <input type="text" name="search" class="grow" placeholder="Search by name..."
                               value="{{ request('search') }}" />
                        @if(request('search') || request('status') || request('gender'))
                            <a href="{{ route('applicants.index') }}" class="btn btn-ghost btn-xs btn-square" title="Clear filters">✕</a>
                        @endif
                    </label>
                </div>

                {{-- Status filter --}}
                <div class="form-control w-full sm:w-48">
                    <select name="status" class="select select-bordered" onchange="this.form.submit()">
                        <option value="">📊 All Statuses</option>
                        @foreach($statusCodes as $sc)
                            <option value="{{ $sc->code }}" {{ request('status') == $sc->code ? 'selected' : '' }}>
                                {{ $sc->label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Gender filter --}}
                <div class="form-control w-full sm:w-36">
                    <select name="gender" class="select select-bordered" onchange="this.form.submit()">
                        <option value="">⚤ All Genders</option>
                        <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>👨 Male</option>
                        <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>👩 Female</option>
                    </select>
                </div>

                {{-- Submit button (visible on mobile, hidden when selects auto-submit) --}}
                <button type="submit" class="btn btn-primary sm:hidden">🔍 Search</button>
            </div>
        </form>

        {{-- Stats summary --}}
        <div class="flex flex-wrap gap-2 mb-4 text-sm">
            <span class="badge badge-ghost badge-lg">📋 {{ $applicants->total() }} total</span>
            @if(request('search') || request('status') || request('gender'))
                <span class="badge badge-warning badge-lg">🔍 {{ $applicants->count() }} matching</span>
            @endif
        </div>

        {{-- Table --}}
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th class="w-10"></th>
                            <th>Name</th>
                            <th>📞 Contact</th>
                            <th>📊 Status</th>
                            <th>📅 Date Added</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($applicants as $applicant)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="avatar placeholder">
                                    <div class="w-10 h-10 rounded-full bg-primary/20 text-primary flex items-center justify-center text-xs font-bold">
                                        {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('applicants.show', $applicant) }}" class="link link-primary font-medium">
                                    {{ $applicant->first_name }} {{ $applicant->last_name }}
                                </a>
                                @if($applicant->middle_name)
                                    <span class="opacity-40 text-sm"> {{ $applicant->middle_name }}</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                @if($applicant->contact)
                                    📱 {{ $applicant->contact }}
                                @elseif($applicant->email)
                                    ✉️ {{ $applicant->email }}
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td>
                                @if($applicant->statusCode)
                                    <span class="badge badge-sm"
                                        style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
                                        {{ $applicant->statusCode->label }}
                                    </span>
                                @else
                                    <span class="badge badge-sm badge-ghost">📋 Pending</span>
                                @endif
                            </td>
                            <td class="text-sm opacity-60">{{ $applicant->created_at->format('M d, Y') }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('applicants.show', $applicant) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
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