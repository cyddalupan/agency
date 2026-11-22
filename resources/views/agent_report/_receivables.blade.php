{{-- Tab 1c: Receivables — Receivable module rows on the 'Agents & Applicant Payment'
     account, status RECEIVED (card 6afb09e9, Cyd 2026-11-16).

     Read-only. This tab used to list pending agent-charged expense requests,
     which are a different table with no link to receivables at all. Editing a
     receivable belongs to the Receivable module, so there is no action column
     here — only a link to the transaction, for the roles allowed to see it. --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">
            📊 Receivables
            <span class="badge badge-sm badge-ghost">{{ $receivableItems->count() }}</span>
            <span class="text-xs font-normal opacity-60 ml-1">
                {{ \App\Models\Receivable::statusLabels()[\App\Models\Receivable::STATUS_RECEIVED] }}
                · Agents &amp; Applicant Payment
            </span>
        </h3>
        @if($receivableItems->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Code</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Account</th>
                            <th>Type</th>
                            <th>Mode</th>
                            <th>Debit Account</th>
                            <th>Applicant</th>
                            <th>{{ agent_term() }}</th>
                            <th class="text-right">Amount</th>
                            <th>Particular / Description</th>
                            <th>Encoded By</th>
                            @if($canViewReceivable)
                                <th>Open</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receivableItems as $receivable)
                            <tr>
                                <td class="font-mono">{{ $receivable->code }}</td>
                                <td>{{ $receivable->date?->format('Y-m-d') ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm badge-success">
                                        {{ \App\Models\Receivable::statusLabels()[$receivable->status] ?? $receivable->status }}
                                    </span>
                                </td>
                                <td>{{ $receivable->account ?? '—' }}</td>
                                <td>{{ $receivable->type ?? '—' }}</td>
                                <td>{{ $receivable->mode ?? '—' }}</td>
                                <td>{{ $receivable->debit_account ?? '—' }}</td>
                                <td>
                                    {{ $receivable->applicant
                                        ? $receivable->applicant->last_name . ', ' . $receivable->applicant->first_name
                                        : '—' }}
                                </td>
                                <td>{{ $receivable->agent?->name ?? '—' }}</td>
                                <td class="text-right font-semibold align-top whitespace-nowrap">₱{{ number_format((float) $receivable->amount, 2) }}</td>
                                <td class="max-w-[26rem] whitespace-normal break-words align-top" title="{{ $receivable->particular }}">{{ $receivable->particular ?? '—' }}</td>
                                <td>{{ $receivable->encoder?->name ?? '—' }}</td>
                                @if($canViewReceivable)
                                    <td class="align-top">
                                        <a href="{{ route('receivable.show', $receivable) }}"
                                           class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">View →</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No received receivables on the Agents &amp; Applicant Payment account yet.</p>
        @endif
    </div>
</div>
