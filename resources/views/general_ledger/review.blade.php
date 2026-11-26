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
                    · <span class="font-mono">{{ peso($total['amount']) }}</span>
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
                    /* General Ledger review (Kchanz Josh, 2026-11-05):
                       - headers line up with the data in their column
                       - no ellipsis truncation: Particular / Description must
                         show the whole detail, wrapping onto extra lines
                         instead of being cut off with "…"
                       - fixed layout keeps all 12 columns inside the content
                         area (no horizontal scroll), so nothing hides
                         off-screen to the right. */
                    /* min-width: below ~1000px the 12 columns cannot hold their labels
                       any more — let the card scroll horizontally instead of
                       shredding the header words. */
                    .gl-review { table-layout: fixed; width: 100%; min-width: 1330px; }
                    .gl-review :is(td, th) {
                        vertical-align: middle;
                        /* DaisyUI's .table forces white-space: nowrap; that is
                           exactly what pushed header text into the next column
                           and cut Particular off. Wrap by default here. */
                        white-space: normal;
                        /* break only at spaces — no more "BACOL OD" or
                           "ACCOU NT" inside data cells */
                        overflow-wrap: normal;
                        word-break: normal;
                        line-height: 1.35;
                        /* breathing room between neighbouring columns so
                           amounts / currency / account never touch */
                        padding-left: 0.6rem;
                        padding-right: 0.6rem;
                    }
                    /* Headers must never be hyphen-split mid word
                       ("ACCOU / NT", "COUN / TRY"). 11px keeps every label on a
                       single line inside its column from 1280px up. */
                    .gl-review thead th {
                        font-size: 11px;
                        font-weight: 700;
                        letter-spacing: 0.01em;
                        padding-left: 0.4rem;
                        padding-right: 0.4rem;
                        white-space: nowrap;
                        overflow-wrap: normal;
                        word-break: normal;
                    }
                    /* "Particular / Description" is two words — let it wrap
                       between them rather than overflow the column. */
                    .gl-review thead th:last-child { white-space: normal; }
                    .gl-review .gl-nowrap { white-space: nowrap; }
                    /* Applicant: whole name on one line (Kchanz Josh) */
                    .gl-review td.col-applicant,
                    .gl-review td.col-applicant span { white-space: nowrap; }
                    /* Particular: always wrap, never a single stretched line */
                    .gl-review td.gl-particular {
                        white-space: normal;
                        overflow-wrap: break-word;
                        word-break: normal;
                    }
                </style>
                <table class="table table-xs gl-review">
                    <colgroup>
                        {{-- Pixel widths: each column is wide enough for its widest
                             word (Applicant for a full name on ONE line). Particular
                             takes what is left and wraps. --}}
                        <col style="width:48px"><col style="width:98px"><col style="width:86px">
                        <col style="width:124px"><col style="width:86px"><col style="width:200px">
                        <col style="width:110px"><col style="width:46px"><col style="width:98px">
                        <col style="width:88px"><col style="width:46px">
                    </colgroup>
                    <thead>
                        <tr class="bg-base-200/70">
                            <th class="text-left align-middle">Ref#</th>
                            <th class="text-left align-middle">Date</th>
                            <th class="text-left align-middle">User</th>
                            <th class="text-left align-middle">Status</th>
                            <th class="text-left align-middle">Offices</th>
                            <th class="text-left align-middle">Applicant</th>
                            <th class="text-left align-middle">Agent</th>
                            <th class="text-left align-middle">Currency</th>
                            <th class="text-right align-middle">Amount</th>
                            <th class="text-left align-middle">Account</th>
                            <th class="text-left align-middle">Country</th>
                            <th class="text-left align-middle">Particular / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr class="hover">
                                <td class="font-mono text-xs align-middle gl-nowrap">{{ $item->expenseRequest->reference_no ?? '—' }}</td>
                                <td class="align-middle gl-nowrap">{{ optional($item->expenseRequest->date)->format('Y-m-d') ?? '—' }}</td>
                                <td class="align-middle">{{ $item->expenseRequest->user->name ?? '—' }}</td>
                                <td class="align-middle">
                                    <span class="badge badge-sm whitespace-nowrap {{ $item->statusBadge() }}">{{ $item->statusLabel() }}</span>
                                </td>
                                <td class="align-middle">{{ $item->branch->name ?? $item->expenseRequest->branch->name ?? '—' }}</td>
                                <td class="align-middle col-applicant">
                                    @php $applicantName = $item->applicant ? trim(implode(' ', array_filter([$item->applicant->first_name, $item->applicant->middle_name, $item->applicant->last_name, $item->applicant->suffix]))) : '—'; @endphp
                                    <span class="block">{{ $applicantName }}</span>
                                </td>
                                <td class="align-middle">{{ $item->agent->name ?? '—' }}</td>
                                <td class="align-middle gl-nowrap">{{ $item->currency }}</td>
                                <td class="text-right font-mono align-middle gl-nowrap">{{ number_format((float) $item->amount, 2) }}</td>
                                <td class="align-middle">{{ $item->account->name ?? '—' }}</td>
                                <td class="align-middle">{{ $item->country->name ?? '—' }}</td>
                                <td class="align-middle gl-particular">{{ $item->particular ?: '—' }}</td>                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-8 opacity-60">No transactions under this sub account.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if ($items->isNotEmpty())
                        <tfoot>
                            <tr class="bg-base-200/60 font-semibold">
                                <td colspan="8" class="text-right align-middle">Total (page)</td>
                                <td class="text-right align-middle font-mono gl-nowrap">{{ number_format((float) $items->sum('amount'), 2) }}</td>
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
