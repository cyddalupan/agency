@extends('layouts.app')

@section('title', 'Receivables')

@section('content')
<div class="max-w-7xl mx-auto">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <h1 class="text-3xl font-bold">🧾 Receivables</h1>
            <p class="opacity-80 mt-1">Outstanding bill balances per employer / worker</p>
        </div>
    </div>

    {{-- Summary cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">🧾 Outstanding</p>
                <p class="text-2xl font-bold text-error">₱{{ number_format($totalOutstanding, 2) }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">⏰ Overdue bills</p>
                <p class="text-2xl font-bold text-warning">{{ $overdueCount }}</p>
            </div>
        </div>
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                <p class="text-sm opacity-60">👥 Employers with balance</p>
                <p class="text-2xl font-bold">{{ $receivables->pluck('employer_id')->unique()->count() }}</p>
            </div>
        </div>
    </div>

    {{-- Receivables table --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3 flex items-center gap-2">Outstanding Balances</h3>
            @if($receivables->count())
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead>
                            <tr class="bg-base-200/70">
                                <th>Employer</th>
                                <th>Worker</th>
                                <th class="text-right">Billed</th>
                                <th class="text-right">Paid</th>
                                <th class="text-right">Outstanding</th>
                                <th>Due Date</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($receivables as $r)
                            <tr class="{{ $r['status'] === 'overdue' ? 'bg-warning/10' : ($r['status'] === 'paid' ? 'opacity-60' : '') }}">
                                <td class="font-medium">
                                    <a href="{{ route('accounting.employer', $r['employer_id']) }}" class="link link-primary">
                                        {{ $r['employer_name'] }}
                                    </a>
                                </td>
                                <td>
                                    @if($r['applicant_id'])
                                        <a href="{{ route('accounting.worker', $r['applicant_id']) }}" class="link">
                                            {{ $r['applicant_name'] }}
                                        </a>
                                    @else —
                                    @endif
                                </td>
                                <td class="text-right">₱{{ number_format($r['billed'], 2) }}</td>
                                <td class="text-right text-success">₱{{ number_format($r['paid'], 2) }}</td>
                                <td class="text-right font-semibold text-error">₱{{ number_format($r['outstanding'], 2) }}</td>
                                <td>{{ $r['due_date'] ? \Illuminate\Support\Carbon::parse($r['due_date'])->format('M d, Y') : '—' }}</td>
                                <td>
                                    @if($r['status'] === 'overdue')
                                        <span class="badge badge-error badge-sm">OVERDUE</span>
                                    @else
                                        <span class="badge badge-success badge-sm">CURRENT</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('accounting.employer', $r['employer_id']) }}" class="btn btn-xs btn-ghost">View →</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="opacity-50 text-sm py-6 text-center">No outstanding receivables 🎉</p>
            @endif
        </div>
    </div>

</div>
@endsection
