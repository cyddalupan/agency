@extends('layouts.app')

@section('title', 'Edit Agent')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('agents.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Agents
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Agent
        </h2>
        <p class="opacity-60 text-sm mt-1">{{ $agent->name }}</p>
    </div>

    <form method="POST" action="{{ route('agents.update', $agent) }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $agent->name) }}" required
                    class="input w-full" placeholder="Full name">
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📧 Email <span class="text-error">*</span></legend>
                    <input type="email" name="email" value="{{ old('email', $agent->email) }}" required
                        class="input w-full" placeholder="agent@email.com">
                    @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Contact</legend>
                    <input type="text" name="contact" value="{{ old('contact', $agent->contact) }}"
                        class="input w-full" placeholder="09XX-XXX-XXXX">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔑 New Password <span class="text-xs opacity-60">(leave blank to keep)</span></legend>
                    <input type="password" name="password"
                        class="input w-full" placeholder="Min. 8 characters">
                    @error('password') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">🔑 Confirm Password</legend>
                    <input type="password" name="password_confirmation"
                        class="input w-full" placeholder="Repeat password">
                </fieldset>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">💰 Commission Rate (%)</legend>
                    <input type="number" name="commission_rate" value="{{ old('commission_rate', $agent->commission_rate) }}"
                        class="input w-full" placeholder="e.g. 10" min="0" max="100" step="0.01">
                    @error('commission_rate') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📊 Status</legend>
                    <select name="status" class="select w-full">
                        <option value="active" @selected(old('status', $agent->status) === 'active')>✅ Active</option>
                        <option value="inactive" @selected(old('status', $agent->status) === 'inactive')>❌ Inactive</option>
                    </select>
                </fieldset>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Agent
                </button>
                <a href="{{ route('agents.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
