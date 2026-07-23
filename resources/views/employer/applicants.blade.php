@extends('layouts.employer-app')

@section('title', 'Applicants')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold">Applicants</h1>
            <p class="text-base-content/60">Applicants assigned to your job positions</p>
        </div>
    </div>

    @if($applicants->isEmpty())
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-12">
            <span class="text-5xl mb-4 block">👥</span>
            <h3 class="text-lg font-semibold">No Applicants Yet</h3>
            <p class="text-base-content/60">Applicants will appear here once they are assigned to your job positions.</p>
        </div>
    </div>
    @else
    <div class="overflow-x-auto">
        <table class="table w-full">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Contact</th>
                    <th>Position</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($applicants as $applicant)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="font-medium">{{ $applicant->full_name }}</td>
                    <td>{{ $applicant->email }}</td>
                    <td>{{ $applicant->contact }}</td>
                    <td>{{ $applicant->position?->name ?? ($applicant->job?->name ?? '—') }}</td>
                    <td>
                        @php
                            $statusColors = [
                                'pending' => 'badge-warning',
                                'processing' => 'badge-info',
                                'deployed' => 'badge-success',
                                'rejected' => 'badge-error',
                            ];
                            $color = $statusColors[$applicant->status] ?? 'badge-ghost';
                        @endphp
                        <span class="badge {{ $color }} badge-sm">{{ ucfirst($applicant->status ?? 'pending') }}</span>
                    </td>
                    <td>
                        <a href="{{ route('employer.billing.applicant', $applicant) }}" class="btn btn-ghost btn-xs">💰 Billing</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $applicants->links() }}
    </div>
    @endif
</div>
@endsection
