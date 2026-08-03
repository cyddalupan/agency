@extends('layouts.app')

@section('title', 'FRAs')

@section('content')
<div class="max-w-7xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>🏢</span> FRAs
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage client companies hiring overseas workers</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('employers.create') }}" class="btn btn-primary">
                <span>➕</span> Add FRA
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($employers->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Company</th>
                            <th>👤 Contact Person</th>
                            <th>🌍 Country</th>
                            <th>📊 Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employers as $employer)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">🏢</span>
                                    <a href="{{ route('employers.show', $employer) }}" class="link link-primary font-medium">
                                        {{ $employer->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="text-sm">
                                @if($employer->contact_person)
                                    👤 {{ $employer->contact_person }}
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                @if($employer->country)
                                    🌍 {{ $employer->country->name }}
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td>
                                @if(($employer->status ?? 'active') === 'active')
                                    <span class="badge badge-sm badge-success">✅ Active</span>
                                @else
                                    <span class="badge badge-sm badge-ghost">⏸️ {{ $employer->status }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('employers.show', $employer) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('employers.edit', $employer) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $employers->links() }}
        </div>
    @else
        {{-- Empty state --}}
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">🏢</span>
                <h3 class="text-xl font-bold mb-2">No FRAs Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    FRAs are the companies hiring workers. Add one to start creating job positions and matching applicants.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('employers.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Add Your First Employer
                    </a>
                    <a href="{{ route('applicants.create') }}" class="btn btn-outline btn-lg">
                        <span>👥</span> Add an Applicant First
                    </a>
                </div>
            </div>
        </div>

        {{-- Quick tips --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📝</span>
                    <h4 class="font-semibold text-sm">Company Details</h4>
                    <p class="text-xs opacity-60 mt-1">Name, contact, country info</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">💼</span>
                    <h4 class="font-semibold text-sm">Job Positions</h4>
                    <p class="text-xs opacity-60 mt-1">Add positions with salary and slots</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">💰</span>
                    <h4 class="font-semibold text-sm">Commission Setup</h4>
                    <p class="text-xs opacity-60 mt-1">Configure employer commissions</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection