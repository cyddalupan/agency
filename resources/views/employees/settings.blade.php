@extends('layouts.app')

@section('title', 'Employee Settings')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('employees.index') }}" class="link link-secondary text-sm">← Back to Employees</a>
        <h2 class="text-2xl font-bold mt-2">⚙️ Employee Settings</h2>
        <p class="opacity-60 text-sm mt-1">
            The positions, departments and job descriptions offered on the Employee form.
            These lists are yours alone — they do not change Positions or any other core data.
        </p>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span><span>{{ session('success') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div role="alert" class="alert alert-error mb-4 text-sm shadow-sm">
            <span>⚠️</span>
            <ul class="list-disc list-inside text-xs">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- Quick add --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-base">➕ Add an entry</h3>
            <form method="POST" action="{{ route('employees.settings.store') }}">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-end">
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">List</span>
                        <select name="type" class="select select-bordered select-sm w-full">
                            @foreach(\App\Models\EmployeeSetting::TYPES as $type => $label)
                                <option value="{{ $type }}" @selected(old('type') === $type)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="form-control">
                        <span class="label-text text-xs opacity-60 mb-1">Name</span>
                        <input type="text" name="name" required value="{{ old('name') }}"
                               placeholder="e.g. Recruitment Officer" class="input input-bordered input-sm w-full">
                    </label>
                    <button type="submit" class="btn btn-primary btn-sm">Add</button>
                </div>
                <label class="form-control mt-3">
                    <span class="label-text text-xs opacity-60 mb-1">Description (used by Job Description entries)</span>
                    <textarea name="description" rows="2" class="textarea textarea-bordered w-full">{{ old('description') }}</textarea>
                </label>
            </form>
        </div>
    </div>

    @foreach($groups as $type => $items)
        <div class="card bg-base-100 shadow-sm mb-4">
            <div class="card-body">
                <h3 class="card-title text-base">{{ \App\Models\EmployeeSetting::TYPES[$type] }}
                    <span class="badge badge-ghost badge-sm">{{ $items->count() }}</span>
                </h3>
                @if($items->isEmpty())
                    <p class="text-sm opacity-50">Nothing here yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Name</th><th>Description</th><th>Status</th><th class="text-right">Actions</th></tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                <tr>
                                    <td class="font-medium">{{ $item->name }}</td>
                                    <td class="text-xs opacity-70">{{ \Illuminate\Support\Str::limit($item->description, 80) }}</td>
                                    <td>
                                        <span class="badge badge-sm {{ $item->status === 'active' ? 'badge-success' : 'badge-ghost' }}">
                                            {{ ucfirst($item->status) }}
                                        </span>
                                    </td>
                                    <td class="text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <form method="POST" action="{{ route('employees.settings.update', $item) }}">
                                                @csrf @method('PUT')
                                                <input type="hidden" name="status" value="{{ $item->status === 'active' ? 'inactive' : 'active' }}">
                                                <input type="hidden" name="name" value="{{ $item->name }}">
                                                <input type="hidden" name="description" value="{{ $item->description }}">
                                                <button type="submit" class="btn btn-ghost btn-xs">
                                                    {{ $item->status === 'active' ? 'Disable' : 'Enable' }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('employees.settings.destroy', $item) }}"
                                                  onsubmit="return confirm('Remove {{ addslashes($item->name) }}?')">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-ghost btn-xs text-error">🗑️</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
