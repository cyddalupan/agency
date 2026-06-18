@extends('layouts.app')

@section('title', 'Custom Fields')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>⚙️</span> Custom Fields
            </h2>
            <p class="opacity-60 text-sm mt-1">Define extra fields for employers, applicants, and other entities</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('custom-fields.create') }}" class="btn btn-primary">
                <span>➕</span> New Custom Field
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($definitions->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Name</th>
                            <th>🔤 Key</th>
                            <th>📋 Model</th>
                            <th>📐 Type</th>
                            <th>🔘 Required</th>
                            <th>🔢 Order</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($definitions as $field)
                        <tr class="hover transition-colors">
                            <td class="font-medium">{{ $field->name }}</td>
                            <td class="font-mono text-sm opacity-70">{{ $field->key }}</td>
                            <td>
                                <span class="badge badge-sm badge-outline">{{ $field->model_type }}</span>
                            </td>
                            <td>
                                @php
                                    $typeIcons = [
                                        'text' => '🔤', 'textarea' => '📝', 'number' => '🔢',
                                        'date' => '📅', 'select' => '📋', 'checkbox' => '✅', 'url' => '🔗',
                                    ];
                                @endphp
                                <span class="badge badge-sm badge-ghost">
                                    {{ $typeIcons[$field->type] ?? '🔤' }} {{ $field->type }}
                                </span>
                            </td>
                            <td>
                                @if($field->required)
                                    <span class="badge badge-sm badge-error">Yes</span>
                                @else
                                    <span class="badge badge-sm badge-ghost">No</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $field->order }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('custom-fields.edit', $field) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('custom-fields.destroy', $field) }}" method="POST"
                                          onsubmit="return confirm('Delete {{ $field->name }}? Existing values will also be removed.')">
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
        </div>

        <div class="mt-4">
            {{ $definitions->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">⚙️</span>
                <h3 class="text-xl font-bold mb-2">No Custom Fields Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Create custom fields to collect extra information from employers, applicants, and other records.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('custom-fields.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Create Your First Field
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
