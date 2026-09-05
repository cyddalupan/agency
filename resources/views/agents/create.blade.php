@extends('layouts.app')

@section('title', 'Create Agent')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('agents.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Agents
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> New Agent
        </h2>
        <p class="opacity-60 text-sm mt-1">Register a new referral agent.</p>
    </div>

    <form method="POST" action="{{ route('agents.store') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input w-full" placeholder="Full name">
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📧 Email <span class="text-error">*</span></legend>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="input w-full" placeholder="agent@email.com">
                    @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Contact</legend>
                    <input type="text" name="contact" value="{{ old('contact') }}"
                        class="input w-full" placeholder="09XX-XXX-XXXX">
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">🏢 Branch</legend>
                @if (auth()->user()->isBranchLocked())
                    {{-- Branch account: no dropdown — branch is auto-set to the user's own branch. --}}
                    <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                    <div class="select select-bordered w-full opacity-80" aria-readonly="true">
                        {{ $branches->firstWhere('id', auth()->user()->branch_id)?->name ?? '' }}
                    </div>
                @else
                    <select name="branch_id" class="select w-full">
                        <option value="">Select branch...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id') == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                @endif
                @error('branch_id') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">💰 Commission Rate (%)</legend>
                <input type="number" name="commission_rate" value="{{ old('commission_rate') }}"
                    class="input w-full" placeholder="e.g. 10" min="0" max="100" step="0.01">
                @error('commission_rate') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create Agent
                </button>
                <a href="{{ route('agents.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
