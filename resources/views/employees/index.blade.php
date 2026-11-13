@extends('layouts.app')

@section('title', 'Employees')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>👔</span> Employees
            </h2>
            <p class="opacity-60 text-sm mt-1">
                Staff records, requirements and document status — {{ $employees->total() }} record{{ $employees->total() === 1 ? '' : 's' }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('employees.settings.index') }}" class="btn btn-ghost btn-sm">
                <span>⚙️</span> Employee Settings
            </a>
            <a href="{{ route('employees.export', request()->query()) }}" class="btn btn-ghost btn-sm">
                <span>⬇️</span> Export CSV
            </a>
            <a href="{{ route('employees.create') }}" class="btn btn-primary">
                <span>➕</span> New Employee
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span><span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('employees.index') }}" class="card bg-base-100 shadow-sm p-4 mb-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <label class="form-control lg:col-span-2">
                <span class="label-text text-xs opacity-60 mb-1">Search</span>
                <input type="text" name="q" value="{{ request('q') }}"
                       placeholder="Name, Employee ID, position, department"
                       class="input input-bordered input-sm w-full">
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Employment Status</span>
                <select name="employment_status" class="select select-bordered select-sm w-full">
                    <option value="">All</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('employment_status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Document Status</span>
                <select name="document_status" class="select select-bordered select-sm w-full">
                    <option value="">All</option>
                    @foreach($docStatuses as $status)
                        <option value="{{ $status }}" @selected(request('document_status') === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text text-xs opacity-60 mb-1">Branch</span>
                <select name="branch_id" class="select select-bordered select-sm w-full">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" @selected((string) request('branch_id') === (string) $branch->id)>{{ $branch->name }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filter</button>
            <a href="{{ route('employees.index') }}" class="btn btn-ghost btn-sm">Reset</a>
        </div>
    </form>

    @if($employees->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra table-sm">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Position / Designation</th>
                            <th>Department</th>
                            <th>Branch</th>
                            <th>Employment</th>
                            <th>Date Hired</th>
                            <th>NBI Expiry</th>
                            <th>Documents</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($employees as $employee)
                        <tr class="hover">
                            <td class="font-mono text-xs">{{ $employee->employee_no ?: '—' }}</td>
                            <td class="font-medium">{{ $employee->full_name }}</td>
                            <td>{{ $employee->position ?: '—' }}</td>
                            <td>{{ $employee->department ?: '—' }}</td>
                            <td>{{ $employee->branch->name ?? '—' }}</td>
                            <td>
                                @php
                                    $empBadge = match($employee->employment_status) {
                                        'Active' => 'badge-success',
                                        'Resigned' => 'badge-error',
                                        default => 'badge-ghost',
                                    };
                                @endphp
                                <span class="badge badge-sm {{ $empBadge }}">{{ $employee->employment_status }}</span>
                            </td>
                            <td class="text-xs">{{ optional($employee->date_hired)->format('M d, Y') ?: '—' }}</td>
                            <td class="text-xs {{ $employee->nbi_expires_at && $employee->nbi_expires_at->isPast() ? 'text-error font-semibold' : '' }}">
                                {{ optional($employee->nbi_expires_at)->format('M d, Y') ?: '—' }}
                            </td>
                            <td>
                                @php
                                    $docBadge = match($employee->document_status) {
                                        'Complete' => 'badge-success',
                                        'Expired' => 'badge-error',
                                        default => 'badge-warning',
                                    };
                                @endphp
                                <span class="badge badge-sm {{ $docBadge }}">{{ $employee->document_status }}</span>
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('employees.edit', $employee) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('employees.destroy', $employee) }}" method="POST"
                                          onsubmit="return confirm('Delete {{ addslashes($employee->full_name) }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-t border-base-300">
                {{ $employees->links() }}
            </div>
        </div>
    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-14 opacity-60">
                <div class="text-5xl mb-3">👔</div>
                <p class="font-semibold">No employees yet</p>
                <p class="text-sm">Add your first staff record to start tracking positions, requirements and document status.</p>
                <div class="mt-4">
                    <a href="{{ route('employees.create') }}" class="btn btn-primary btn-sm">➕ New Employee</a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
