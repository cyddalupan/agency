@extends('layouts.app')

@section('title', 'Create Report Template')

@php
    $columns = old('columns', []);
    $groupBy = old('group_by');
    $sortBy = old('sort_by');
    $sortOrder = old('sort_order');
    $datePreset = old('date_preset');
@endphp

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('report-templates.index') }}" class="text-sm opacity-70 hover:opacity-100">
            &larr; Back to Templates
        </a>
        <h1 class="text-2xl font-bold mt-2">Create Report Template</h1>
    </div>

    <form action="{{ route('report-templates.store') }}" method="POST" class="card bg-base-100 shadow-sm">
        @csrf

        <div class="card-body space-y-5">
            {{-- Name --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Template Name</span>
                </label>
                <input type="text" name="name" class="input input-bordered @error('name') input-error @enderror"
                       value="{{ old('name') }}" placeholder="e.g. Monthly Applicant Summary" required>
                @error('name')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Type --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Report Type</span>
                </label>
                <select name="type" class="select select-bordered @error('type') select-error @enderror" required
                        onchange="toggleColumnOptions(this.value)">
                    <option value="">Select type...</option>
                    <option value="applicant_report" @selected(old('type') === 'applicant_report')>Applicant Report</option>
                    <option value="statistics" @selected(old('type') === 'statistics')>Statistics Dashboard</option>
                    <option value="transactions" @selected(old('type') === 'transactions')>Transactions</option>
                </select>
                @error('type')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Separator --}}
            <hr class="border-base-300">

            <p class="text-sm font-medium opacity-70">📊 Configure Report Layout</p>

            {{-- Columns --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Columns to Include</span>
                    <span class="label-text-alt opacity-50">Check the fields you want in the report</span>
                </label>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 mt-1">
                    @php
                        $allColumns = [
                            'name' => 'Name',
                            'email' => 'Email',
                            'phone' => 'Phone',
                            'gender' => 'Gender',
                            'country' => 'Country',
                            'status' => 'Status',
                            'position' => 'Position',
                            'employer' => 'Employer',
                            'salary' => 'Expected Salary',
                            'source' => 'Source',
                            'agent' => 'Agent',
                            'created_at' => 'Date Created',
                            'updated_at' => 'Last Updated',
                        ];
                    @endphp
                    @foreach($allColumns as $key => $label)
                        <label class="label cursor-pointer justify-start gap-2 border rounded-lg px-3 py-2 hover:bg-base-200/50">
                            <input type="checkbox" name="columns[]" value="{{ $key }}"
                                   class="checkbox checkbox-sm checkbox-primary"
                                   {{ in_array($key, old('columns', ['name', 'status', 'country', 'created_at'])) ? 'checked' : '' }}>
                            <span class="label-text text-sm">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('columns')
                    <label class="label"><span class="label-text-alt text-error">{{ $message }}</span></label>
                @enderror
            </div>

            {{-- Group By --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Group By</span>
                    <span class="label-text-alt opacity-50">Optional — group rows by a field</span>
                </label>
                <select name="group_by" class="select select-bordered">
                    <option value="">None</option>
                    @foreach($allColumns as $key => $label)
                        <option value="{{ $key }}" @selected($groupBy === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Sort --}}
            <div class="grid grid-cols-2 gap-4">
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Sort By</span>
                    </label>
                    <select name="sort_by" class="select select-bordered">
                        <option value="created_at" @selected(!$sortBy || $sortBy === 'created_at')>Date Created</option>
                        @foreach($allColumns as $key => $label)
                            @if($key !== 'created_at')
                            <option value="{{ $key }}" @selected($sortBy === $key)>{{ $label }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="form-control">
                    <label class="label">
                        <span class="label-text font-medium">Sort Order</span>
                    </label>
                    <select name="sort_order" class="select select-bordered">
                        <option value="desc" @selected(!$sortOrder || $sortOrder === 'desc')>⬇️ Descending</option>
                        <option value="asc" @selected($sortOrder === 'asc')>⬆️ Ascending</option>
                    </select>
                </div>
            </div>

            {{-- Date Preset --}}
            <div class="form-control">
                <label class="label">
                    <span class="label-text font-medium">Date Range</span>
                    <span class="label-text-alt opacity-50">Filter by a preset time period</span>
                </label>
                <select name="date_preset" class="select select-bordered">
                    <option value="">All Time</option>
                    <option value="today" @selected($datePreset === 'today')>Today</option>
                    <option value="this_week" @selected($datePreset === 'this_week')>This Week</option>
                    <option value="this_month" @selected($datePreset === 'this_month')>This Month</option>
                    <option value="last_month" @selected($datePreset === 'last_month')>Last Month</option>
                    <option value="this_quarter" @selected($datePreset === 'this_quarter')>This Quarter</option>
                    <option value="this_year" @selected($datePreset === 'this_year')>This Year</option>
                </select>
            </div>

            {{-- Is Active --}}
            <div class="form-control">
                <label class="label cursor-pointer justify-start gap-3">
                    <input type="checkbox" name="is_active" class="checkbox checkbox-primary" value="1" checked>
                    <span class="label-text">Active</span>
                </label>
            </div>

            <div class="card-actions justify-end pt-2 border-t border-base-200">
                <a href="{{ route('report-templates.index') }}" class="btn btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-primary">Create Template</button>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleColumnOptions(type) {
    const checkboxes = document.querySelectorAll('input[name="columns[]"]');
    if (type === 'statistics') {
        // For statistics, show fewer options
        checkboxes.forEach(cb => {
            const val = cb.value;
            cb.closest('label').style.display = ['name', 'status', 'created_at', 'month'].includes(val) ? '' : 'none';
        });
    } else if (type === 'transactions') {
        checkboxes.forEach(cb => {
            const val = cb.value;
            cb.closest('label').style.display = ['name', 'email', 'phone', 'amount', 'status', 'created_at'].includes(val) ? '' : 'none';
        });
    } else {
        checkboxes.forEach(cb => cb.closest('label').style.display = '');
    }
}
</script>
@endpush
