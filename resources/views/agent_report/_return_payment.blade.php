{{-- Tab: Return Payment (Cyd 2026-11-04) — released expenses charged to the agent on the "Return" account --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold">↩️ Return Payment <span class="badge badge-sm badge-ghost">{{ $returnPaymentItems->count() }}</span></h3>
            {{-- Card 6b056f91: the header badge is gone; the sticky total line below
                 (and the tfoot) both read $tabTotals['return_payment'] so all three
                 numbers that used to sit on this card are one number in two places. --}}
        </div>
        @if($returnPaymentItems->count())
            <x-agent-report-total :amount="$tabTotals['return_payment']" />
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Date</th>
                            <th>Account</th>
                            <th>{{ agent_term() }}</th>
                            <th>Applicant</th>
                            <th class="text-right">Amount</th>
                            <th>Particular / Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($returnPaymentItems as $item)
                            @php
                                $req = $item->expenseRequest;
                                $phpEquivalent = $converter->toPhp((float) $item->amount, $item->currency);
                            @endphp
                            <tr>
                                <td class="whitespace-nowrap">{{ $req?->date?->format('M d, Y') ?? '—' }}</td>
                                <td>{{ $item->account?->name ?? 'Return' }}</td>
                                <td>{{ $item->agent?->name ?? '—' }}</td>
                                <td>{{ $item->applicant ? $item->applicant->last_name . ', ' . $item->applicant->first_name : '—' }}</td>
                                <td class="text-right font-semibold align-top whitespace-nowrap">
                                    {{ $item->currency === 'USD' ? '$' : peso((float) $item->amount) }}
                                    @if($item->currency === 'USD')
                                        <span class="block text-xs font-normal opacity-60">≈ {{ peso($phpEquivalent) }}</span>
                                    @endif
                                </td>
                                <td class="max-w-[26rem] whitespace-normal break-words align-top" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-base-200/70 font-bold">
                            <td colspan="4" class="text-right">Total Return Payment</td>
                            <td class="text-right">{{ peso($tabTotals['return_payment']) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No return payment transactions yet.</p>
        @endif
    </div>
</div>
