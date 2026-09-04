@extends('layouts.app')

@section('title', 'Starting Balance — ' . $startingBalance->agent?->name)

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('agent_report.index', ['tab' => 'starting-balances']) }}" class="opacity-60 text-xs hover:opacity-100 transition-opacity">← Back to Starting Balance</a>
            <h1 class="text-2xl font-bold mt-2">⚖️ Starting Balance Entry</h1>
            <p class="opacity-80 mt-1">Agent: {{ $startingBalance->agent?->name ?? '—' }}</p>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Date</p>
                    <p class="font-bold mt-1">{{ $startingBalance->date->format('M d, Y') }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Account</p>
                    <p class="font-bold mt-1">
                        <span class="badge badge-sm badge-primary">{{ $startingBalance->account }}</span>
                    </p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Agent</p>
                    <p class="font-bold mt-1">{{ $startingBalance->agent?->name ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Applicant</p>
                    <p class="font-bold mt-1">
                        {{ $startingBalance->applicant ? $startingBalance->applicant->last_name . ', ' . $startingBalance->applicant->first_name : '—' }}
                    </p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Amount</p>
                    <p class="font-bold mt-1 text-primary">₱{{ number_format((float) $startingBalance->amount, 2) }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Encoded by</p>
                    <p class="font-bold mt-1">{{ $startingBalance->encoder?->name ?? $startingBalance->encoder?->username ?? '—' }}</p>
                </div>
                <div class="rounded-xl bg-base-200/60 p-3 sm:col-span-2">
                    <p class="text-xs uppercase tracking-wide opacity-60 font-semibold">Particular / Description</p>
                    <p class="mt-1">{{ $startingBalance->particular ?? '—' }}</p>
                </div>
            </div>

            <div class="flex gap-3 pt-4 border-t border-base-200 mt-4">
                <a href="{{ route('agent_report.index', ['tab' => 'starting-balances']) }}" class="btn btn-ghost btn-sm">← Back</a>
            </div>
        </div>
    </div>

</div>
@endsection
