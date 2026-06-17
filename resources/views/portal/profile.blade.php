@extends('portal.layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6">
        <div class="card-body p-6">
            <div class="flex items-center gap-4">
                <div class="avatar placeholder">
                    <div class="w-16 rounded-full bg-white/20 text-white text-2xl font-bold">
                        {{ strtoupper(substr($applicant->first_name, 0, 1)) }}{{ strtoupper(substr($applicant->last_name, 0, 1)) }}
                    </div>
                </div>
                <div>
                    <h1 class="text-2xl lg:text-3xl font-bold">{{ $applicant->full_name }}</h1>
                    @if($applicant->email)
                        <p class="opacity-80 text-sm mt-1">✉️ {{ $applicant->email }}</p>
                    @endif
                    @if($applicant->contact)
                        <p class="opacity-80 text-sm">📱 {{ $applicant->contact }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Status --}}
    @if($applicant->statusCode)
    <div class="mb-6">
        <span class="badge badge-lg"
            style="background-color: {{ $applicant->statusCode->color ?? '#e5e7eb' }}20; color: {{ $applicant->statusCode->color ?? '#374151' }}">
            📊 {{ $applicant->statusCode->labelForCountry($applicant->country?->name) }}
        </span>
    </div>
    @endif

    {{-- Personal Information --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>📋</span> Personal Information
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
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
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Age</dt>
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

    {{-- Employment Details --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>🏢</span> Employment Details
            </h3>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Employer</dt>
                    <dd class="font-medium mt-1">{{ $applicant->employer?->name ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Job Title</dt>
                    <dd class="font-medium mt-1">{{ $applicant->job?->title ?? '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Expected Salary</dt>
                    <dd class="font-medium mt-1">{{ $applicant->expected_salary ? '₱' . number_format($applicant->expected_salary, 2) : '—' }}</dd>
                </div>
                <div class="p-3 rounded-lg bg-base-200/50">
                    <dt class="text-xs opacity-60 uppercase tracking-wider">Type</dt>
                    <dd class="font-medium mt-1">{{ $applicant->source ?? '—' }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Documents --}}

    {{-- Education --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>🎓</span> Education
            </h3>
            @if($applicant->education->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>School</th>
                                <th>Course</th>
                                <th>Year Graduated</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applicant->education as $edu)
                                <tr>
                                    <td>{{ $edu->level ?? '—' }}</td>
                                    <td>{{ $edu->school ?? '—' }}</td>
                                    <td>{{ $edu->course ?? '—' }}</td>
                                    <td>{{ $edu->year_graduated ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm opacity-50 text-center py-4">No education records yet.</p>
            @endif
        </div>
    </div>

    {{-- Passport --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>🛂</span> Passport
            </h3>
            @if($applicant->passport)
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">Passport Number</dt>
                        <dd class="font-medium mt-1">{{ $applicant->passport->passport_number }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">Place of Issue</dt>
                        <dd class="font-medium mt-1">{{ $applicant->passport->place_issue ?? '—' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">Issue Date</dt>
                        <dd class="font-medium mt-1">{{ $applicant->passport->issue_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                    <div class="p-3 rounded-lg bg-base-200/50">
                        <dt class="text-xs opacity-60 uppercase tracking-wider">Expiry Date</dt>
                        <dd class="font-medium mt-1">{{ $applicant->passport->expiry_date?->format('M d, Y') ?? '—' }}</dd>
                    </div>
                </dl>
            @else
                <p class="text-sm opacity-50 text-center py-4">No passport information yet.</p>
            @endif
        </div>
    </div>

    {{-- Certificates --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>🏅</span> Certificates
            </h3>
            @if($applicant->certificates->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Certificate</th>
                                <th>Institution</th>
                                <th>Date Obtained</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applicant->certificates as $cert)
                                <tr>
                                    <td>{{ $cert->certificate_name }}</td>
                                    <td>{{ $cert->institution ?? '—' }}</td>
                                    <td>{{ $cert->date_obtained?->format('M d, Y') ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm opacity-50 text-center py-4">No certificates recorded yet.</p>
            @endif
        </div>
    </div>

    {{-- Work Experience --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>💼</span> Work Experience
            </h3>
            @if($applicant->workExperiences->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Position</th>
                                <th>Start Date</th>
                                <th>End Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applicant->workExperiences as $exp)
                                <tr>
                                    <td>{{ $exp->company }}</td>
                                    <td>{{ $exp->position ?? '—' }}</td>
                                    <td>{{ $exp->start_date?->format('M d, Y') ?? '—' }}</td>
                                    <td>{{ $exp->end_date?->format('M d, Y') ?? 'Present' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm opacity-50 text-center py-4">No work experience recorded yet.</p>
            @endif
        </div>
    </div>

    {{-- Requirements --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>📋</span> Requirements
            </h3>
            @if($applicant->requirements->isNotEmpty())
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($applicant->requirements as $req)
                                <tr>
                                    <td>{{ $req->type }}</td>
                                    <td>
                                        <span class="badge badge-sm {{ $req->status === 'submitted' ? 'badge-success' : 'badge-warning' }}">
                                            {{ ucfirst($req->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm opacity-50 text-center py-4">No requirements submitted yet.</p>
            @endif
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title flex items-center gap-2 mb-4">
                <span>📄</span> Documents
            </h3>

            {{-- Upload Form --}}
            <form action="{{ route('portal.documents.upload') }}" method="POST" enctype="multipart/form-data" class="mb-6 p-4 bg-base-200 rounded-lg">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="label">
                            <span class="label-text">Document Type</span>
                        </label>
                        <select name="document_type" class="select select-bordered w-full">
                            <option value="">Select type...</option>
                            <option value="resume">Resume / CV</option>
                            <option value="passport">Passport</option>
                            <option value="education">Educational Certificate</option>
                            <option value="training">Training Certificate</option>
                            <option value="medical">Medical Result</option>
                            <option value="nbi">NBI Clearance</option>
                            <option value="other">Other</option>
                        </select>
                        @error('document_type')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="label">
                            <span class="label-text">File (PDF, JPG, PNG - max 5MB)</span>
                        </label>
                        <input type="file" name="document" accept=".pdf,.jpg,.jpeg,.png" class="file-input file-input-bordered w-full" />
                        @error('document')
                            <span class="text-error text-xs mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="btn btn-primary w-full">
                            Upload
                        </button>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="label">
                        <span class="label-text">Notes (optional)</span>
                    </label>
                    <input type="text" name="notes" class="input input-bordered w-full" placeholder="e.g. Updated resume" />
                </div>
            </form>

            {{-- Documents List --}}
            @if($applicant->relationLoaded('documents') ? $applicant->documents->isNotEmpty() : $applicant->documents()->count() > 0)
                <div class="overflow-x-auto">
                    <table class="table table-zebra">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>File</th>
                                <th>Size</th>
                                <th>Uploaded</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(($applicant->relationLoaded('documents') ? $applicant->documents : $applicant->documents()->get()) as $document)
                                <tr>
                                    <td>
                                        <span class="badge badge-outline">{{ ucfirst($document->document_type) }}</span>
                                    </td>
                                    <td>
                                        <div class="flex items-center gap-2">
                                            @php
                                                $ext = strtolower(pathinfo($document->file_name, PATHINFO_EXTENSION));
                                                $icon = in_array($ext, ['jpg', 'jpeg', 'png']) ? '🖼️' : '📄';
                                            @endphp
                                            <span>{{ $icon }}</span>
                                            <span class="text-sm">{{ $document->file_name }}</span>
                                        </div>
                                        @if($document->notes)
                                            <p class="text-xs opacity-50 mt-1">{{ $document->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="text-sm">
                                        @if($document->file_size > 1024 * 1024)
                                            {{ number_format($document->file_size / 1024 / 1024, 1) }} MB
                                        @else
                                            {{ number_format($document->file_size / 1024, 0) }} KB
                                        @endif
                                    </td>
                                    <td class="text-sm opacity-70">{{ $document->created_at->diffForHumans() }}</td>
                                    <td>
                                        <a href="{{ route('portal.documents.download', $document) }}" class="btn btn-ghost btn-xs">
                                            Download
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-sm opacity-50 text-center py-4">No documents uploaded yet.</p>
            @endif
        </div>
    </div>

    <div class="text-center mt-8">
        <a href="{{ route('portal.dashboard') }}" class="btn btn-ghost">
            ← Back to Dashboard
        </a>
    </div>
</div>
@endsection
