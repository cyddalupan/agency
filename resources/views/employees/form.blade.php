@extends('layouts.app')

@section('title', $employee ? 'Edit Employee' : 'New Employee')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="link link-secondary text-sm">← Back to Employees</a>
        <h2 class="text-2xl font-bold mt-2">
            {{ $employee ? '✏️ Edit Employee' : '➕ New Employee' }}
        </h2>
        <p class="opacity-60 text-sm mt-1">
            {{ $employee ? $employee->full_name : 'Staff record, requirements and document status' }}
        </p>
    </div>

    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-4 text-sm shadow-sm">
            <span>⚠️</span>
            <div>
                <p class="font-semibold">Please fix the following:</p>
                <ul class="list-disc list-inside text-xs mt-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST"
          action="{{ $employee ? route('employees.update', $employee) : route('employees.store') }}"
          enctype="multipart/form-data">
        @csrf
        @if($employee)
            @method('PUT')
        @endif

        {{-- Basic details --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">🧑‍💼 Employee details</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Employee ID</span>
                        <input type="text" name="employee_no" value="{{ old('employee_no', $employee->employee_no ?? '') }}"
                               placeholder="e.g. EMP-0001" class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Full Name <span class="text-error">*</span></span>
                        <input type="text" name="full_name" required value="{{ old('full_name', $employee->full_name ?? '') }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Position / Designation</span>
                        <input type="text" name="position" list="position-options"
                               value="{{ old('position', $employee->position ?? '') }}"
                               class="input input-bordered input-sm w-full">
                        <datalist id="position-options">
                            @foreach($positions as $position)
                                <option value="{{ $position }}"></option>
                            @endforeach
                        </datalist>
                        <span class="text-[11px] opacity-50 mt-1">List maintained in Employee Settings.</span>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Department</span>
                        <input type="text" name="department" list="department-options"
                               value="{{ old('department', $employee->department ?? '') }}"
                               class="input input-bordered input-sm w-full">
                        <datalist id="department-options">
                            @foreach($departments as $department)
                                <option value="{{ $department }}"></option>
                            @endforeach
                        </datalist>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Branch Assignment</span>
                        <select name="branch_id" class="select select-bordered select-sm w-full">
                            <option value="">— none —</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}"
                                    @selected((string) old('branch_id', $employee->branch_id ?? '') === (string) $branch->id)>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Employment Status</span>
                        <select name="employment_status" class="select select-bordered select-sm w-full">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}"
                                    @selected(old('employment_status', $employee->employment_status ?? 'Active') === $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Date Hired</span>
                        <input type="date" name="date_hired"
                               value="{{ old('date_hired', optional($employee->date_hired ?? null)->format('Y-m-d')) }}"
                               class="input input-bordered input-sm w-full">
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Document Status</span>
                        <select name="document_status" class="select select-bordered select-sm w-full">
                            @foreach($docStatuses as $status)
                                <option value="{{ $status }}"
                                    @selected(old('document_status', $employee->document_status ?? 'Incomplete') === $status)>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text text-xs opacity-60 mb-1">Educational Background</span>
                        <textarea name="educational_background" rows="3" class="textarea textarea-bordered w-full"
                                  placeholder="Degree and qualifications">{{ old('educational_background', $employee->educational_background ?? '') }}</textarea>
                    </label>
                    <label class="form-control md:col-span-2">
                        <span class="label-text text-xs opacity-60 mb-1">Job Description</span>
                        <textarea name="job_description" rows="3" class="textarea textarea-bordered w-full"
                                  placeholder="Duties and responsibilities">{{ old('job_description', $employee->job_description ?? '') }}</textarea>
                    </label>
                </div>
            </div>
        </div>

        {{-- Requirements & documents --}}
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">📎 Requirements &amp; documents</h3>
                <p class="text-xs opacity-60 -mt-2">PDF, JPG, PNG, DOC or DOCX · up to 10 MB each.</p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    {{-- Appointment Document --}}
                    <div class="border border-base-300 rounded-lg p-3">
                        <p class="font-semibold text-sm mb-2">Appointment Document</p>
                        @include('employees._file', ['field' => 'appointment_document', 'nameField' => 'appointment_document_name'])
                    </div>

                    {{-- NBI Clearance --}}
                    <div class="border border-base-300 rounded-lg p-3">
                        <p class="font-semibold text-sm mb-2">NBI Clearance</p>
                        <div class="grid grid-cols-1 gap-2">
                            <label class="form-control">
                                <span class="label-text text-xs opacity-60 mb-1">Reference</span>
                                <input type="text" name="nbi_reference" value="{{ old('nbi_reference', $employee->nbi_reference ?? '') }}"
                                       class="input input-bordered input-sm w-full">
                            </label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="form-control">
                                    <span class="label-text text-xs opacity-60 mb-1">Issue date</span>
                                    <input type="date" name="nbi_issued_at"
                                           value="{{ old('nbi_issued_at', optional($employee->nbi_issued_at ?? null)->format('Y-m-d')) }}"
                                           class="input input-bordered input-sm w-full">
                                </label>
                                <label class="form-control">
                                    <span class="label-text text-xs opacity-60 mb-1">Expiry</span>
                                    <input type="date" name="nbi_expires_at"
                                           value="{{ old('nbi_expires_at', optional($employee->nbi_expires_at ?? null)->format('Y-m-d')) }}"
                                           class="input input-bordered input-sm w-full">
                                </label>
                            </div>
                            @include('employees._file', ['field' => 'nbi_document', 'nameField' => 'nbi_document_name'])
                        </div>
                    </div>

                    {{-- Training Records --}}
                    <div class="border border-base-300 rounded-lg p-3">
                        <p class="font-semibold text-sm mb-2">Training Records</p>
                        <label class="form-control">
                            <span class="label-text text-xs opacity-60 mb-1">Trainings attended</span>
                            <textarea name="training_records" rows="3" class="textarea textarea-bordered w-full"
                                      placeholder="One per line">{{ old('training_records', $employee->training_records ?? '') }}</textarea>
                        </label>
                        @include('employees._file', ['field' => 'training_document', 'nameField' => 'training_document_name'])
                    </div>

                    {{-- QMS Orientation --}}
                    <div class="border border-base-300 rounded-lg p-3">
                        <p class="font-semibold text-sm mb-2">QMS Orientation</p>
                        <label class="form-control">
                            <span class="label-text text-xs opacity-60 mb-1">Attendance &amp; acknowledgment</span>
                            <textarea name="qms_orientation" rows="3" class="textarea textarea-bordered w-full"
                                      placeholder="Date attended, acknowledged by">{{ old('qms_orientation', $employee->qms_orientation ?? '') }}</textarea>
                        </label>
                        @include('employees._file', ['field' => 'qms_document', 'nameField' => 'qms_document_name'])
                    </div>

                    {{-- Competency Assessment --}}
                    <div class="border border-base-300 rounded-lg p-3 md:col-span-2">
                        <p class="font-semibold text-sm mb-2">Competency Assessment</p>
                        <label class="form-control">
                            <span class="label-text text-xs opacity-60 mb-1">Skills and qualification assessment</span>
                            <textarea name="competency_assessment" rows="3" class="textarea textarea-bordered w-full">{{ old('competency_assessment', $employee->competency_assessment ?? '') }}</textarea>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-2 mb-10">
            <a href="{{ route('employees.index') }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary">
                {{ $employee ? '💾 Save changes' : '💾 Create employee' }}
            </button>
        </div>
    </form>
</div>
@endsection
