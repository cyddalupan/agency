@extends('layouts.app')

@section('title', 'Accounts')
@section('header', 'Accounts')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>📒</span> Accounts
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage Main accounts and their Sub accounts</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                <span>➕</span> New Account
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @error('children')
        <div role="alert" class="alert alert-error mb-4 text-sm shadow-sm">
            <span>⚠️</span>
            <span>{{ $message }}</span>
        </div>
    @enderror

    @if($mains->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Main Account</th>
                            <th>Type</th>
                            <th>Sub Accounts</th>
                            <th>Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($mains as $main)
                        <tr class="border-b border-base-200">
                            <td class="font-semibold">{{ $main->name }}</td>
                            <td>
                                @if($main->type === 'income')
                                    <span class="badge badge-sm badge-info">💰 Income</span>
                                @else
                                    <span class="badge badge-sm badge-warning">💸 Expense</span>
                                @endif
                            </td>
                            <td>
                                @if($main->children->count())
                                    <ul class="space-y-1">
                                        @foreach($main->children as $sub)
                                            <li class="flex items-center gap-2">
                                                <span class="opacity-50">└─</span>
                                                <span>{{ $sub->name }}</span>
                                                <a href="{{ route('accounts.edit', $sub) }}" class="link link-secondary text-xs" title="Edit">✏️</a>
                                                <form action="{{ route('accounts.destroy', $sub) }}" method="POST"
                                                      onsubmit="return confirm('Delete Sub account {{ $sub->name }}?');" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="link text-error text-xs" title="Delete">🗑️</button>
                                                </form>
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <span class="opacity-40 text-sm">—</span>
                                @endif
                            </td>
                            <td>
                                @if($main->is_active)
                                    <span class="badge badge-sm badge-success">Active</span>
                                @else
                                    <span class="badge badge-sm badge-ghost">Inactive</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('accounts.edit', $main) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('accounts.destroy', $main) }}" method="POST"
                                          onsubmit="return confirm('Delete {{ $main->name }} and all its Sub accounts?')">
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
            {{ $mains->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">📒</span>
                <h3 class="text-xl font-bold mb-2">No Accounts Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">Create Main accounts (like Agents or Office Expenses) and add Sub accounts under them.</p>
                <a href="{{ route('accounts.create') }}" class="btn btn-primary">
                    <span>➕</span> New Account
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
