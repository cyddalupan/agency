@extends('layouts.app')

@section('title', 'Edit Deduction')

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('agent_report.index', ['tab' => 'deductions']) }}" class="opacity-60 text-xs hover:opacity-100 transition-opacity">← Back to Deductions &amp; Paid</a>
            <h1 class="text-2xl font-bold mt-2">✏️ Edit Deduction</h1>
            <p class="opacity-80 mt-1">Agents Report · Tab 3 — Review &amp; correct</p>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-error shadow-md mb-6">
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- The agent / applicant / account identify who was charged. Correcting
         them is a different operation, so they are shown but not editable here. --}}
    <div class="bg-info/10 border border-info/40 text-info-content rounded-lg p-4 mb-5 text-sm">
        <p class="font-semibold">ℹ️ What you can change</p>
        <ul class="list-disc list-inside mt-2 opacity-90">
            <li><strong>Date</strong>, <strong>Amount</strong> and <strong>Particular / Description</strong> are editable.</li>
            <li><strong>{{ agent_term() }}</strong>, <strong>Applicant</strong> and <strong>Account</strong> are fixed — they record who was charged. Delete and re-encode if one of those is wrong.</li>
        </ul>
    </div>

    <form method="POST" action="{{ route('agent_report.deduction.update', $deduction) }}" class="card bg-base-100 shadow-md">
        @csrf
        @method('PUT')

        <div class="card-body space-y-6">
            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">📋 Transaction Details</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="form-control">
                        <label class="label"><span class="label-text">Date *</span></label>
                        <input type="date" name="date" class="input input-bordered w-full"
                               value="{{ old('date', $deduction->date?->toDateString()) }}" required>
                    </div>
                    <div class="form-control">
                        <label class="label"><span class="label-text">Amount * <span class="opacity-50 font-normal">(Amount in Peso)</span></span></label>
                        <div class="relative">
                            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-sm opacity-60 font-semibold">₱</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="input input-bordered w-full pl-8"
                                   value="{{ old('amount', number_format((float) $deduction->amount, 2, '.', '')) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">👤 Agent &amp; Applicant <span class="font-normal normal-case opacity-60">(fixed)</span></h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-xl bg-base-200/60 p-3">
                        <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Agent</p>
                        <p class="font-bold mt-1">{{ $deduction->agent?->name ?? '—' }}</p>
                    </div>
                    <div class="rounded-xl bg-base-200/60 p-3">
                        <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Applicant</p>
                        <p class="font-bold mt-1">
                            {{ $deduction->applicant ? $deduction->applicant->last_name . ', ' . $deduction->applicant->first_name : '—' }}
                        </p>
                    </div>
                    <div class="rounded-xl bg-base-200/60 p-3">
                        <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Account</p>
                        <p class="font-bold mt-1">
                            <span class="badge badge-sm {{ $deduction->account === \App\Models\AgentDeduction::ACCOUNT_PAID ? 'badge-success' : 'badge-error' }}">
                                {{ $deduction->account }}
                            </span>
                        </p>
                    </div>
                    <div class="rounded-xl bg-base-200/60 p-3">
                        <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Encoded by</p>
                        <p class="font-bold mt-1">{{ $deduction->encoder?->name ?? $deduction->encoder?->username ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <div>
                <h3 class="font-bold text-sm opacity-70 uppercase tracking-wider mb-3 pb-2 border-b border-base-200">📝 Particular / Description</h3>
                <div class="form-control">
                    <textarea name="particular" rows="4" class="textarea textarea-bordered w-full"
                              placeholder="What this deduction is for…">{{ old('particular', $deduction->particular) }}</textarea>
                    <label class="label"><span class="label-text-alt opacity-60">This is the text that goes missing when a bulk upload does not recognise the column name.</span></label>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-base-200 mt-4">
                <button type="submit" class="btn btn-primary btn-sm">💾 Save changes</button>
                <a href="{{ route('agent_report.index', ['tab' => 'deductions']) }}" class="btn btn-ghost btn-sm">Cancel</a>
            </div>
        </div>
    </form>
</div>
@endsection
