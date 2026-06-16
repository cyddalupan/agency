@extends('layouts.app')

@section('title', 'Edit Agent - ' . $marketingAgent->name)

@section('content')
<div class="max-w-xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('marketing-agencies.marketing-agents.index', $marketingAgency) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Agents
        </a>
    </div>

    <div class="card bg-gradient-to-r from-amber-500/10 to-yellow-500/10 border border-amber-500/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>✏️</span> Edit Marketing Agent
        </h2>
        <p class="opacity-60 text-sm mt-1">Agency: <strong>{{ $marketingAgency->name }}</strong> · Agent: <strong>{{ $marketingAgent->name }}</strong></p>
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

    <form action="{{ route('marketing-agencies.marketing-agents.update', [$marketingAgency, $marketingAgent]) }}" method="POST" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf @method('PUT')

            <fieldset class="fieldset">
                <legend class="fieldset-legend">👤 Agent Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name', $marketingAgent->name) }}" required
                    class="input w-full">
                @error('name') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
            </fieldset>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">📱 Contact</legend>
                    <input type="text" name="contact" value="{{ old('contact', $marketingAgent->contact) }}"
                        class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend">✉️ Email</legend>
                    <input type="email" name="email" value="{{ old('email', $marketingAgent->email) }}"
                        class="input w-full">
                    @error('email') <p class="text-error text-sm mt-1">{{ $message }}</p> @enderror
                </fieldset>
            </div>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📊 Status</legend>
                <select name="status" class="select w-full">
                    <option value="active" @selected(old('status', $marketingAgent->status) === 'active')>✅ Active</option>
                    <option value="inactive" @selected(old('status', $marketingAgent->status) === 'inactive')>⏸️ Inactive</option>
                </select>
            </fieldset>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Update Agent
                </button>
                <a href="{{ route('marketing-agencies.marketing-agents.index', $marketingAgency) }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>

            {{-- Delete row --}}
            <div class="pt-4 border-t border-base-200">
                <details class="collapse collapse-arrow bg-base-200/50 rounded-lg">
                    <summary class="collapse-title text-sm font-medium text-error">⚠️ Delete this agent</summary>
                    <div class="collapse-content">
                        <p class="text-sm opacity-70 mb-3">This action cannot be undone. All data associated with this agent will be removed.</p>
                        <form action="{{ route('marketing-agencies.marketing-agents.destroy', [$marketingAgency, $marketingAgent]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete {{ $marketingAgent->name }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-error btn-sm">
                                <span>🗑️</span> Delete Agent
                            </button>
                        </form>
                    </div>
                </details>
            </div>
        </div>
    </form>
</div>
@endsection
