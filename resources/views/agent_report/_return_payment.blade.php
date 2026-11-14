{{-- Tab: Return Payment (Cyd 2026-11-04) — released expenses charged to the agent on the "Return" account --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <div class="flex items-center justify-between mb-3">
            <h3 class="font-bold">↩️ Return Payment <span class="badge badge-sm badge-ghost">{{ $returnPaymentItems->count() }}</span></h3>
            <span class="badge badge-primary badge-sm">Total: ₱{{ number_format($returnPaymentTotal, 2) }}</span>
        </div>
        @if($returnPaymentItems->count())
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
                                <td class="text-right font-semibold">
                                    {{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $item->amount, 2) }}
                                    @if($item->currency === 'USD')
                                        <span class="block text-xs font-normal opacity-60">≈ ₱{{ number_format($phpEquivalent, 2) }}</span>
                                    @endif
                                </td>
                                <td class="max-w-[18rem] truncate" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-base-200/70 font-bold">
                            <td colspan="4" class="text-right">Total Return Payment</td>
                            <td class="text-right">₱{{ number_format($returnPaymentTotal, 2) }}</td>
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
