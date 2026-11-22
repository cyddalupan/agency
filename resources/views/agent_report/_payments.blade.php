{{-- Tab 2: Payments — data source: Expenses & Payments module only --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-bold">💳 Payments <span class="badge badge-sm badge-ghost">{{ $payments->count() }}</span></h3>
            @if($canManagePayments ?? false)
                <a href="{{ route('agent_report.payment.bulk') }}" class="btn btn-outline btn-sm">📤 Bulk Upload</a>
            @endif
        </div>
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
                            <th>{{ agent_term() }}</th>
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
                                <td class="text-right font-semibold align-top whitespace-nowrap">{{ $p->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $p->payment, 2) }}</td>
                                <td class="text-right opacity-60 align-top whitespace-nowrap">{{ $p->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $p->amount, 2) }}</td>
                                <td>{{ $p->account->name ?? '—' }}</td>
                                <td class="max-w-[26rem] whitespace-normal break-words align-top" title="{{ $p->particular }}">{{ $p->particular ?? '—' }}</td>
                                <td class="whitespace-nowrap align-top">
                                    @if($er)
                                        <a href="{{ route('expense_request.show', $er) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                    @endif
                                    @if($canDeleteTransactions ?? false)
                                        <button type="button" class="btn btn-xs btn-error btn-outline"
                                                onclick="openTxDelete(@js(route('agent_report.payment.destroy', $p)), @js('Payment · ' . ($er->reference_no ?? '') . ' · ₱' . number_format((float) $p->payment, 2) . ' · ' . ($p->agent->name ?? 'no agent')))">Delete</button>
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

@if($canDeleteTransactions ?? false)
<dialog id="txDeleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete payment transaction</h3>
        <p class="text-sm opacity-70 mt-1" id="txDeleteSummary"></p>
        <p class="text-xs opacity-60 mt-1">The payment amount is cleared from the expense transaction; the expense itself stays in the ledger.</p>
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
