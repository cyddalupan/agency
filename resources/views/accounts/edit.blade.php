@extends('layouts.app')

@section('title', 'Edit Account')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('accounts.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Accounts
        </a>
    </div>

    <div class="card bg-gradient-to-br from-indigo-500/10 to-purple-500/10 border border-indigo-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📒</span> Edit Account
        </h2>
        <p class="opacity-60 text-sm mt-1">{{ $account->isMain() ? 'Main account' : 'Sub account' }} · {{ $account->name }}</p>
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

    <form action="{{ route('accounts.update', $account) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏷️ Account Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $account->name) }}" required
                    class="input w-full" placeholder="e.g. Office Expenses, Electric Bills">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🔗 Parent Account (empty = Main)</legend>
                <p class="text-xs opacity-50 mb-1">
                    @if($account->isMain() && $account->children->count())
                        ⚠️ This Main account has Sub accounts — it cannot become a Sub account.
                    @else
                        Leave blank to make it a Main account.
                    @endif
                </p>
                <select name="parent_id" class="select w-full" {{ $account->isMain() && $account->children->count() ? 'disabled' : '' }}>
                    <option value="">— Make it a Main Account —</option>
                    @foreach($mains as $main)
                        <option value="{{ $main->id }}" @selected(old('parent_id', $account->parent_id) == $main->id)>
                            {{ $main->name }} ({{ $main->type }})
                        </option>
                    @endforeach
                </select>
                @error('parent_id') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset" data-hides-for-sub>
                <legend class="fieldset-legend">🔘 Type (Main accounts only)</legend>
                <select name="type" class="select w-full">
                    <option value="income" @selected(old('type', $account->type) === 'income')>💰 Income</option>
                    <option value="expense" @selected(old('type', $account->type) === 'expense')>💸 Expense</option>
                </select>
                @error('type') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Charge Type <span class="text-error">*</span></legend>
                <select name="charge_type" class="select w-full">
                    <option value="office" @selected(old('charge_type', $account->charge_type ?? 'office') === 'office')>🏢 Office</option>
                    <option value="agent" @selected(old('charge_type', $account->charge_type ?? 'office') === 'agent')>🤝 Agent</option>
                    <option value="applicant" @selected(old('charge_type', $account->charge_type ?? 'office') === 'applicant')>👤 Applicant</option>
                </select>
                <p class="text-xs opacity-50 mt-1">Which expense lines this account can be used for.</p>
                @error('charge_type') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">✅ Status</legend>
                <select name="is_active" class="select w-full">
                    <option value="1" @selected(old('is_active', $account->is_active))>🟢 Active</option>
                    <option value="0" @selected(! old('is_active', $account->is_active))>⚪ Inactive</option>
                </select>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Account
                </button>
                <a href="{{ route('accounts.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    const parentSelect = document.querySelector('select[name="parent_id"]');
    const typeFieldset = document.querySelector('[data-hides-for-sub]');
    function syncTypeVisibility() {
        if (parentSelect && typeFieldset && !parentSelect.disabled) {
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
