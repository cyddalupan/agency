@extends('layouts.app')

@section('title', 'New Account')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('accounts.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Accounts
        </a>
    </div>

    <div class="card bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📒</span> New Account
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Leave <em>Parent Account</em> empty to create a Main account. Pick a Main to add a Sub account under it.
        </p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm">
            <span>❌</span>
            <ul class="list-disc pl-4 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('accounts.store') }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Account Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input w-full" placeholder="e.g. Office Expenses, Electric Bills">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔗 Parent Account (empty = Main)</legend>
                <p class="text-xs opacity-50 mb-1">Leave blank to create a Main account. Choose a Main to create a Sub account.</p>
                <select name="parent_id" class="select w-full">
                    <option value="">— New Main Account —</option>
                    @foreach($mains as $main)
                        <option value="{{ $main->id }}" @selected(old('parent_id') == $main->id)>
                            {{ $main->name }} ({{ $main->type }})
                        </option>
                    @endforeach
                </select>
                @error('parent_id') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset" data-hides-for-sub>
                <legend class="fieldset-legend">🔘 Type (Main accounts only)</legend>
                <select name="type" class="select w-full">
                    <option value="income" @selected(old('type', 'expense') === 'income')>💰 Income</option>
                    <option value="expense" @selected(old('type', 'expense') === 'expense')>💸 Expense</option>
                </select>
                @error('type') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create Account
                </button>
                <a href="{{ route('accounts.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // When a Parent is selected, the account is a Sub and inherits type — hide the type selector.
    const parentSelect = document.querySelector('select[name="parent_id"]');
    const typeFieldset = document.querySelector('[data-hides-for-sub]');
    function syncTypeVisibility() {
        if (parentSelect && typeFieldset) {
            typeFieldset.style.display = parentSelect.value ? 'none' : '';
        }
    }
    if (parentSelect) {
        parentSelect.addEventListener('change', syncTypeVisibility);
        syncTypeVisibility();
    }
</script>
@endpush
@endsection
