@extends('layouts.app')

@section('title', 'General Ledger — ' . $account->name)

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📑</span>
                <div>
                    <h1 class="text-2xl font-extrabold">{{ $account->name }}</h1>
                    <p class="opacity-70 text-sm mt-0.5">
                        {{ $main->name ?? '—' }} · {{ ucfirst((string) ($main->type ?? 'expense')) }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold">{{ number_format($total['count']) }}</span> transactions
                    · <span class="font-mono">₱{{ number_format($total['amount'], 2) }}</span>
                </span>
                <a href="{{ route('general_ledger.review.export', $account) }}" class="btn btn-sm btn-secondary shadow-md">⬇️ Export CSV</a>
                <a href="{{ route('general_ledger.index') }}" class="btn btn-sm btn-ghost bg-white/20 hover:bg-white/30">← Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Ref#</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Offices</th>
                            <th>Applicant</th>
                            <th>Agent</th>
                            <th>Currency</th>
                            <th class="text-right">Amount</th>
                            <th>Account</th>
                            <th>Country</th>
                            <th>Particular / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="hover">
                                <td class="font-mono text-xs">{{ $item->expenseRequest->reference_no ?? '—' }}</td>
                                <td class="whitespace-nowrap">{{ optional($item->expenseRequest->date)->format('Y-m-d') ?? '—' }}</td>
                                <td>{{ $item->expenseRequest->user->name ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $item->statusBadge() }}">{{ $item->statusLabel() }}</span>
                                </td>
                                <td>{{ $item->branch->name ?? $item->expenseRequest->branch->name ?? '—' }}</td>
                                <td>
                                    @if ($item->applicant)
                                        {{ trim(implode(' ', array_filter([$item->applicant->first_name, $item->applicant->middle_name, $item->applicant->last_name, $item->applicant->suffix]))) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->agent->name ?? '—' }}</td>
                                <td>{{ $item->currency }}</td>
                                <td class="text-right font-mono">{{ number_format((float) $item->amount, 2) }}</td>
                                <td>{{ $item->account->name ?? '—' }}</td>
                                <td>{{ $item->country->name ?? '—' }}</td>
                                <td class="max-w-xs truncate" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-8 opacity-60">No transactions under this sub account.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($items->isNotEmpty())
                        <tfoot>
                            <tr class="bg-base-200/60 font-semibold">
                                <td colspan="8" class="text-right">Total (page)</td>
                                <td class="text-right font-mono">{{ number_format((float) $items->sum('amount'), 2) }}</td>
                                <td colspan="3"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if ($items->hasPages())
            <div class="card-body py-3 px-6 border-t border-base-200">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
