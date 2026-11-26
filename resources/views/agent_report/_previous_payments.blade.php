{{-- Tab 2b: Previous Payment (Gulf only, card 6b056b38) — append-only pre-go-live history --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-bold">💵 Previous Payment <span class="badge badge-sm badge-ghost">{{ $previousPayments->count() }}</span>
                <span class="ml-2 text-sm font-semibold opacity-80">Total: {{ peso((float) $previousPaymentsTotal) }}</span>
            </h3>
            @if($canManagePayments ?? false)
                <a href="{{ route('agent_report.previous_payment.bulk') }}" class="btn btn-outline btn-sm">📤 Bulk Upload</a>
            @endif
        </div>
        <p class="text-xs opacity-60 mb-2">Payments made to {{ agent_terms() }} before go-live. Uploaded as history; counted in Total Payment and Balance on the {{ agent_term() }} Ledger.</p>
        @if($previousPayments->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Date</th>
                            <th>Applicant</th>
                            <th>{{ agent_term() }}</th>
                            <th>Currency</th>
                            <th>Particular / Description</th>
                            <th class="text-right">Payment</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($previousPayments as $p)
                            <tr>
                                <td>{{ $p->date?->format('M d, Y') ?? '—' }}</td>
                                <td>
                                    @if($p->applicant)
                                        {{ $p->applicant->last_name }}, {{ $p->applicant->first_name }}
                                    @else —
                                    @endif
                                </td>
                                <td>{{ $p->agent->name ?? '—' }}</td>
                                <td>{{ $p->currency ?: 'PHP' }}</td>
                                <td class="max-w-[26rem] whitespace-normal break-words align-top" title="{{ $p->particular }}">{{ $p->particular ?? '—' }}</td>
                                <td class="text-right font-semibold align-top whitespace-nowrap">{{ ($p->currency === 'USD') ? '$' : peso((float) $p->amount) }}</td>
                                <td class="whitespace-nowrap align-top">
                                    @if($canDeleteTransactions ?? false)
                                        <button type="button" class="btn btn-xs btn-error btn-outline"
                                                onclick="openTxDelete(@js(route('agent_report.previous_payment.destroy', $p)), @js('Previous Payment · ' . ($p->date?->format('M d, Y') ?? 'no date') . ' · ' . ($p->currency === 'USD' ? '$' : '₱') . number_format((float) $p->amount, 2) . ' · ' . ($p->agent->name ?? 'no agent')))">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No previous payments recorded.</p>
        @endif
    </div>
</div>

@if($canDeleteTransactions ?? false)
<dialog id="txDeleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete previous payment</h3>
        <p class="text-sm opacity-70 mt-1" id="txDeleteSummary"></p>
        <p class="text-xs opacity-60 mt-1">The row is removed from this tab and from the {{ agent_term() }} Ledger. Nothing else is changed.</p>
        <form method="POST" id="txDeleteForm" class="mt-3">
            @csrf
            @method('DELETE')
            <label class="label" for="txDeleteReason"><span class="label-text">Reason (required)</span></label>
            <textarea id="txDeleteReason" name="reason" class="textarea textarea-bordered w-full" rows="3" maxlength="1000" required></textarea>
            <div class="modal-action">
                <button type="button" class="btn btn-ghost" onclick="document.getElementById('txDeleteModal').close()">Cancel</button>
                <button type="submit" class="btn btn-error">Delete</button>
            </div>
        </form>
    </div>
</dialog>
<script>
function openTxDelete(url, label) {
    document.getElementById('txDeleteForm').action = url;
    document.getElementById('txDeleteSummary').textContent = label;
    document.getElementById('txDeleteModal').showModal();
}
</script>
@endif
