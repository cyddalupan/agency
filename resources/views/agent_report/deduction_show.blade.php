@extends('layouts.app')

@section('title', 'Deduction / Paid — ' . $deduction->agent?->name)

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('agent_report.index', ['tab' => 'deductions']) }}" class="opacity-60 text-xs hover:opacity-100 transition-opacity">← Back to Deductions &amp; Paid</a>
            <h1 class="text-2xl font-bold mt-2">🧾 {{ $deduction->account }} Entry</h1>
            <p class="opacity-80 mt-1">Agent: {{ $deduction->agent?->name ?? '—' }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Date</p>
                    <p class="font-bold mt-1">{{ $deduction->date->format('M d, Y') }}</p>
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
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Amount</p>
                    <p class="font-bold mt-1 text-error">₱{{ number_format((float) $deduction->amount, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Encoded by</p>
                    <p class="font-bold mt-1">{{ $deduction->encoder?->name ?? $deduction->encoder?->username ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Particular / Description</p>
                    <p class="mt-1">{{ $deduction->particular ?? '—' }}</p>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-base-200 mt-4">
                <a href="{{ route('agent_report.index', ['tab' => 'deductions']) }}" class="btn btn-ghost btn-sm">← Back</a>
            </div>
        </div>
    </div>

</div>
@endsection
