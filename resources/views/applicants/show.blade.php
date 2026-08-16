@extends('layouts.app')

@section('title', $applicant->full_name)

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header with profile --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('applicants.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Applicants
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <div class="avatar">
                        <div class="w-16 h-16 rounded-full bg-white/20 text-white flex items-center justify-center text-2xl font-bold overflow-hidden">
                            @if ($applicant->photo)
                                <img src="{{ $applicant->photo_url }}" class="w-full h-full object-cover" alt="{{ $applicant->full_name }}">
                            @else
                                {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                            @endif
                        </div>
                    </div>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">{{ $applicant->full_name }}</h2>
                        @if($applicant->email)
                            <p class="opacity-80 text-sm mt-1">✉️ {{ $applicant->email }}</p>
                        @endif
                        @if($applicant->contact)
                            <p class="opacity-80 text-sm">📱 {{ $applicant->contact }}</p>
                        @endif
                        @if($applicant->employer)
                            <p class="opacity-80 text-sm mt-1">🏢 <span class="font-medium">{{ $applicant->employer->name }}</span></p>
                        @endif
                        @if($applicant->agent)
                            <p class="opacity-80 text-sm mt-1">🎯 <span class="font-medium">{{ $applicant->agent->name }}</span></p>
                        @endif
                    </div>
                </div>
                <div class="flex flex-col items-end gap-3">
                    <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        ✏️ Edit
                    </a>
                    <div class="flex flex-col items-end gap-1 print-group">
                        <span class="text-[10px] uppercase tracking-wider opacity-60">🖨️ Print</span>
                        <a href="{{ route('reports.resume', $applicant) }}" target="_blank" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                            📄 Generate CV
                        </a>
                        <a href="{{ route('reports.expense-report', $applicant) }}" target="_blank" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                            🧾 Expense Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-4 text-sm shadow-sm">
            <span>❌</span>
            <ul class="list-disc ml-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Status badge --}}
    @if($applicant->statusCode)
    <div class="mb-6">
        <span class="badge badge-lg whitespace-nowrap"
            style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
            📊 {{ $applicant->statusCode->label }}
        </span>
    </div>
    @endif

    {{-- Personal Information — 6-tab layout (LANDAS card, TDD) --}}
    @include('applicants._tabs_personal_information')
</div>
@endsection
