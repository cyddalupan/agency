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
                    <div class="avatar placeholder">
                        <div class="w-16 h-16 rounded-full bg-white/20 text-white flex items-center justify-center text-2xl font-bold">
                            {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
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
                    </div>
                </div>
                <a href="{{ route('applicants.edit', $applicant) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                    ✏️ Edit
                </a>
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
        <span class="badge badge-lg"
            style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
            📊 {{ $applicant->statusCode->label }}
        </span>
    </div>
    @endif

    {{-- Personal Information Card --}}
    <div class="card bg-base-100 shadow-sm mb-6 card-lift">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2">
                <span>📋</span> Personal Information
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-2">
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Full Name</dt>
                    <dd class="font-medium mt-1">{{ $applicant->full_name }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">⚤ Gender</dt>
                    <dd class="font-medium mt-1">{{ $applicant->gender ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🎂 Birthdate</dt>
                    <dd class="font-medium mt-1">{{ $applicant->birthdate?->format('M d, Y') ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🎂 Age</dt>
                    <dd class="font-medium mt-1">{{ $applicant->birthdate ? $applicant->birthdate->age . ' years old' : '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🌍 Preferred Country</dt>
                    <dd class="font-medium mt-1">{{ $applicant->country?->name ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">💼 Preferred Position</dt>
                    <dd class="font-medium mt-1">{{ $applicant->position?->name ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">📱 Source</dt>
                    <dd class="font-medium mt-1">{{ $applicant->source ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50 md:col-span-2">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">🏠 Address</dt>
                    <dd class="font-medium mt-1">{{ $applicant->address ?? '—' }}</dd>
                </div>
            </dl>
            @if($applicant->remarks)
            <div class="mt-4 pt-4 border-t border-base-200">
                <dt class="text-sm opacity-60">📝 Remarks</dt>
                <dd class="mt-1">{{ $applicant->remarks }}</dd>
            </div>
            @endif
        </div>
    </div>

    {{-- ======================== --}}
    {{-- SUB-TABLE SECTIONS       --}}
    {{-- Custom Fields --}}
    <div class="card bg-base-100 shadow-sm mb-6 card-lift">
        <div class="card-body">
            @include('partials.custom-fields-display', ['model' => $applicant, 'modelType' => 'Applicant'])
        </div>
    </div>

    {{-- ======================== --}}

    @php
        $subTables = [
            ['passport',         '🛂 Passport',         true,  'passport'],
            ['education',        '🎓 Education',        false, 'education'],
            ['certificates',     '📜 Certificates',     false, 'certificates'],
            ['requirements',     '📄 Requirements',     false, 'requirements'],
            ['work-experiences', '💼 Work Experience',  false, 'workExperiences'],
            ['skills',           '🛠️ Skills',           false, 'skills'],
            ['references',       '👥 References',       false, 'references'],
            ['salary-records',   '💰 Salary Records',   false, 'salaryRecords'],
            ['documents',        '📁 Documents',         false, 'documents'],
        ];

        $data = $applicant->load([
            'passport', 'education', 'certificates', 'requirements',
            'workExperiences', 'skills', 'references', 'salaryRecords',
            'documents', 'employer',
        ]);
    @endphp

    @foreach ($subTables as [$routeKey, $label, $isSingle, $relationName])
    @php
        $related = $data->$relationName ?? null;
        $records = $isSingle ? collect($related ? [$related] : []) : (collect($related) ?? collect());
    @endphp
    <div class="card bg-base-100 shadow-sm mb-6 card-lift">
        <div class="card-body">
            <div class="flex items-center justify-between mb-3">
                <h3 class="card-title text-lg">{{ $label }}</h3>
                @if (!$isSingle || !$related)
                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('form-{{ $routeKey }}').classList.toggle('hidden')">
                        ➕ Add
                    </button>
                @else
                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById('form-{{ $routeKey }}').classList.toggle('hidden')">
                        ✏️ Edit
                    </button>
                @endif
            </div>

            {{-- Add Form (hidden by default) --}}
            <div id="form-{{ $routeKey }}" class="hidden border rounded-lg p-4 bg-base-200 mb-4">
                @php
                    $actionRoute = $routeKey === 'documents'
                        ? route('applicants.documents.store', $applicant)
                        : route('applicants.sub.store', [$applicant, $routeKey]);
                @endphp
                <form action="{{ $actionRoute }}" method="POST"
                      @if(in_array($routeKey, ['passport', 'certificates', 'requirements', 'documents'])) enctype="multipart/form-data" @endif>
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        @include("applicants.sub-forms.{$routeKey}", ['record' => null])
                    </div>
                    <div class="flex items-center gap-2 mt-3">
                        <button type="submit" class="btn btn-primary btn-sm">💾 Save</button>
                        <button type="button" class="btn btn-ghost btn-sm"
                            onclick="document.getElementById('form-{{ $routeKey }}').classList.add('hidden')">❌ Cancel</button>
                    </div>
                </form>
            </div>

            {{-- List existing records --}}
            @if ($records->count() > 0)
                @include("applicants.sub-lists.{$routeKey}", ['records' => $records, 'routeKey' => $routeKey])
            @else
                <div class="text-center py-6 opacity-50">
                    <span class="text-3xl block mb-2">📭</span>
                    <p class="text-sm">No records yet.</p>
                </div>
            @endif
        </div>
    </div>
    @endforeach
</div>
@endsection