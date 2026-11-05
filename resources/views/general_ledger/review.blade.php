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
            <div class="flex flex-wrap items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0 whitespace-nowrap">
                    <span class="font-bold">{{ number_format($total['count']) }}</span> transactions
                    · <span class="font-mono">₱{{ number_format($total['amount'], 2) }}</span>
                </span>
                <a href="{{ route('general_ledger.review.export', $account) }}" class="btn btn-sm btn-secondary shadow-md whitespace-nowrap">⬇️ Export CSV</a>
                <a href="{{ route('general_ledger.index') }}" class="btn btn-sm btn-ghost bg-white/20 hover:bg-white/30 whitespace-nowrap">← Back</a>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md">
        <div class="card-body p-0">
            <div class="overflow-x-auto">
                <style>
                    /* General Ledger review: 12 columns must fit the content
                       area instead of scrolling off to the right
                       (Kchanz Josh "Fix this too", 2026-11-05). */
                    .gl-review { table-layout: fixed; width: 100%; }
                    .gl-review :is(td, th) {
                        overflow: hidden;
                        text-overflow: ellipsis;
                        white-space: nowrap;
                    }
                </style>
                <table class="table table-xs gl-review">
                    <colgroup><col style="width:6%"><col style="width:8%"><col style="width:9%"><col style="width:8%"><col style="width:8%"><col style="width:9%"><col style="width:9%"><col style="width:6%"><col style="width:9%"><col style="width:9%"><col style="width:7%"><col style="width:12%"></colgroup>
                    <thead>
                        <tr class="bg-base-200/70">
                            <th class="whitespace-nowrap">Ref#</th>
                            <th class="whitespace-nowrap">Date</th>
                            <th class="whitespace-nowrap">User</th>
                            <th class="whitespace-nowrap">Status</th>
                            <th class="whitespace-nowrap">Offices</th>
                            <th class="whitespace-nowrap">Applicant</th>
                            <th class="whitespace-nowrap">Agent</th>
                            <th class="whitespace-nowrap">Currency</th>
                            <th class="text-right whitespace-nowrap">Amount</th>
                            <th class="whitespace-nowrap">Account</th>
                            <th class="whitespace-nowrap">Country</th>
                            <th class="whitespace-nowrap">Particular / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="hover">
                                <td class="font-mono text-xs truncate" title="{{ $item->expenseRequest->reference_no ?? '' }}">{{ $item->expenseRequest->reference_no ?? '—' }}</td>
                                <td class="whitespace-nowrap">{{ optional($item->expenseRequest->date)->format('Y-m-d') ?? '—' }}</td>
                                <td class="truncate" title="{{ $item->expenseRequest->user->name ?? '' }}">{{ $item->expenseRequest->user->name ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $item->statusBadge() }}">{{ $item->statusLabel() }}</span>
                                </td>
                                <td class="truncate">{{ $item->branch->name ?? $item->expenseRequest->branch->name ?? '—' }}</td>
                                <td>
                                    @php $applicantName = $item->applicant ? trim(implode(' ', array_filter([$item->applicant->first_name, $item->applicant->middle_name, $item->applicant->last_name, $item->applicant->suffix]))) : '—'; @endphp
                                    <span class="block truncate" title="{{ $applicantName }}">{{ $applicantName }}</span>
                                </td>
                                <td class="truncate" title="{{ $item->agent->name ?? '' }}">{{ $item->agent->name ?? '—' }}</td>
                                <td class="truncate">{{ $item->currency }}</td>
                                <td class="text-right font-mono truncate">{{ number_format((float) $item->amount, 2) }}</td>
                                <td class="truncate" title="{{ $item->account->name ?? '' }}">{{ $item->account->name ?? '—' }}</td>
                                <td class="truncate" title="{{ $item->country->name ?? '' }}">{{ $item->country->name ?? '—' }}</td>
                                <td class="truncate" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
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
