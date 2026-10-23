{{-- Tab 2: Payments — data source: Expenses & Payments module only --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">💳 Payments <span class="badge badge-sm badge-ghost">{{ $payments->count() }}</span></h3>
        <p class="text-xs opacity-60 mb-2">Payment amounts recorded on transactions in Expenses &amp; Payments. Receivables from the Receivable module are not listed here.</p>
        @if($payments->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Date</th>
                            <th>Ref#</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Applicant</th>
                            <th>Agent</th>
                            <th class="text-right">Payment</th>
                            <th class="text-right">Gross</th>
                            <th>Account</th>
                            <th>Particular</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $p)
                            @php($er = $p->expenseRequest)
                            <tr>
                                <td>{{ $er?->date?->format('M d, Y') ?? '—' }}</td>
                                <td class="font-mono font-semibold">{{ $er->reference_no ?? '—' }}</td>
                                <td>{{ $er->user->name ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm
                                        @switch($er->status ?? '')
                                            @case('approved') badge-info @break
                                            @case('for_releasing') badge-primary @break
                                            @case('released') badge-success @break
                                            @case('cancelled') badge-error @break
                                            @default badge-warning
                                        @endswitch">{{ \App\Models\ExpenseRequest::STATUS_LABELS[$er->status ?? 'pending'] ?? ($er->status ?? '—') }}</span>
                                </td>
                                <td>
                                    @if($p->applicant)
                                        {{ $p->applicant->last_name }}, {{ $p->applicant->first_name }}
                                    @else —
                                    @endif
                                </td>
                                <td>{{ $p->agent->name ?? '—' }}</td>
                                <td class="text-right font-semibold">{{ $p->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $p->payment, 2) }}</td>
                                <td class="text-right opacity-60">{{ $p->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $p->amount, 2) }}</td>
                                <td>{{ $p->account->name ?? '—' }}</td>
                                <td class="max-w-[14rem] truncate" title="{{ $p->particular }}">{{ $p->particular ?? '—' }}</td>
                                <td>
                                    @if($er)
                                        <a href="{{ route('expense_request.show', $er) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No payments recorded in Expenses &amp; Payments yet.</p>
        @endif
    </div>
</div>
