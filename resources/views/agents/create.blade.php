@extends('layouts.app')

@section('title', 'Create ' . agent_term())

@section('content')
<div class="w-full">
    <div class="mb-6">
        <a href="{{ route('agents.index') }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to {{ agent_terms() }}
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>➕</span> New {{ agent_term() }}
        </h2>
        <p class="opacity-60 text-sm mt-1">Register a new referral agent.</p>
    </div>

    {{-- Duplicate guard (Cyd 2026-11-08, LANDAS card): shown after a blocked submit --}}
    @if(session('duplicate_agents') && count(session('duplicate_agents')))
        <div id="duplicate-warning" role="alert" class="alert alert-warning mb-6 shadow-md items-start">
            <div class="w-full">
                <p class="font-bold text-base">⚠️ Possible duplicate agent</p>
                <p class="text-sm opacity-80 mt-1">
                    We found {{ count(session('duplicate_agents')) }} existing agent record(s) that look like this one.
                    Check the match(es) first — open a record below before creating a duplicate.
                </p>
                <ul class="mt-3 space-y-3">
                    @foreach(session('duplicate_agents') as $dupe)
                        <li class="text-sm">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ $dupe['url'] }}" target="_blank" class="link link-primary font-semibold">{{ $dupe['name'] }}</a>
                                <span class="opacity-70">— {{ implode(' · ', $dupe['reasons']) }}</span>
                            </div>
                            <table class="table table-xs mt-2 mb-1 w-full bg-base-100/60 rounded">
                                <tbody class="text-xs">
                                    <tr>
                                        <th class="w-24 font-semibold">Branch</th><td>{{ $dupe['branch'] }}</td>
                                        <th class="w-24 font-semibold">Email</th><td>{{ $dupe['email'] }}</td>
                                    </tr>
                                    <tr>
                                        <th class="font-semibold">Contact</th><td>{{ $dupe['contact'] }}</td>
                                        <th class="font-semibold">Created By</th><td>{{ $dupe['created_by'] }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </li>
                    @endforeach
                </ul>
                <p class="text-xs opacity-70 mt-3">
                    If this is a different person, click <b>Create anyway</b> to proceed.
                </p>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('agents.store') }}" class="card bg-base-100 shadow-sm card-lift">
        <div class="card-body space-y-4">
            @csrf
            <input type="hidden" name="confirm_duplicate" id="confirm_duplicate" value="{{ old('confirm_duplicate') }}">

            {{-- Form fills the screen instead of sitting in a narrow column
                 (client request 2026-11-05). Fields are grouped three-up on
                 wide screens so nothing looks stretched. --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <fieldset class="fieldset lg:col-span-2">
                <legend class="fieldset-legend">👤 Name <span class="text-error">*</span></legend>
                <input type="text" name="name" value="{{ old('name') }}" required
                    class="input w-full" placeholder="Full name">
                @error('name') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>

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
                <legend class="fieldset-legend">📧 Email</legend>
                <input type="email" name="email" value="{{ old('email') }}"
                    class="input w-full" placeholder="agent@email.com (optional)">
                @error('email') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">📱 Contact</legend>
                <input type="text" name="contact" value="{{ old('contact') }}"
                    class="input w-full" placeholder="09XX-XXX-XXXX">
            </fieldset>

            <fieldset class="fieldset">
                <legend class="fieldset-legend">💰 Commission Rate (%)</legend>
                <input type="number" name="commission_rate" value="{{ old('commission_rate') }}"
                    class="input w-full" placeholder="e.g. 10" min="0" max="100" step="0.01">
                @error('commission_rate') <span class="text-error text-xs mt-1">{{ $message }}</span> @enderror
            </fieldset>
            </div>

            <div class="flex items-center gap-4 pt-4 border-t border-base-200">
                <button type="submit" class="btn btn-primary">
                    <span>💾</span> Create {{ agent_term() }}
                </button>
                @if(session('duplicate_agents') && count(session('duplicate_agents')))
                    <button type="submit" class="btn btn-warning" onclick="document.getElementById('confirm_duplicate').value = '1';">
                        <span>⚠️</span> Create anyway
                    </button>
                @endif
                <a href="{{ route('agents.index') }}" class="link link-neutral text-sm">❌ Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
