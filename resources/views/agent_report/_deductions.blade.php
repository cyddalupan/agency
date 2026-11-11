{{-- Tab 3: Deductions — AgentDeduction rows with account = Deduction (Cyd 2026-10-23) --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="font-bold">🧾 Deductions <span class="badge badge-sm badge-ghost">{{ $deductions->count() }}</span></h3>
            @if($canManageDeductions)
                <div class="flex items-center gap-2">
                    <a href="{{ route('agent_report.deduction.bulk') }}" class="btn btn-outline btn-sm">📥 Bulk Upload</a>
                    <a href="{{ route('agent_report.deduction.create') }}" class="btn btn-primary btn-sm">➕ New Deduction</a>
                </div>
            @endif
        </div>
        <p class="text-xs opacity-60 mb-2">Deductions only. Payments from Expenses &amp; Payments are listed on the Payments tab.</p>
        @if($deductions->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Date</th>
                            <th>Account</th>
                            <th>Agent</th>
                            <th>Applicant</th>
                            <th class="text-right">Amount</th>
                            <th>Particular / Description</th>
                            <th>Review</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($deductions as $d)
                            <tr>
                                <td>{{ $d->date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $d->account === \App\Models\AgentDeduction::ACCOUNT_PAID ? 'badge-success' : 'badge-error' }}">
                                        {{ $d->account }}
                                    </span>
                                </td>
                                <td>{{ $d->agent->name ?? '—' }}</td>
                                <td>
                                    @if($d->applicant)
                                        {{ $d->applicant->last_name }}, {{ $d->applicant->first_name }}
                                    @else —
                                    @endif
                                </td>
                                <td class="text-right font-semibold">₱{{ number_format((float) $d->amount, 2) }}</td>
                                <td class="max-w-[16rem] truncate" title="{{ $d->particular }}">{{ $d->particular ?? '—' }}</td>
                                <td class="whitespace-nowrap">
                                    <a href="{{ route('agent_report.deduction.show', $d) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                    {{-- (card 6af170f5) Admin and Accounting can correct a posted deduction. --}}
                                    @if($canManageDeductions ?? false)
                                        <a href="{{ route('agent_report.deduction.edit', $d) }}" class="btn btn-xs btn-outline btn-primary whitespace-nowrap inline-flex items-center gap-1">Edit</a>
                                    @endif
                                    @if($canDeleteTransactions ?? false)
                                        <button type="button" class="btn btn-xs btn-error btn-outline"
                                                onclick="openTxDelete(@js(route('agent_report.deduction.destroy', $d)), @js('Deduction · ' . $d->date->format('M d, Y') . ' · ₱' . number_format((float) $d->amount, 2) . ' · ' . ($d->agent->name ?? 'no agent')))">Delete</button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-base-200/70 font-bold">
                            <td colspan="4" class="text-right">Total Deductions</td>
                            <td class="text-right">₱{{ number_format((float) $deductions->sum('amount'), 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No deductions yet.</p>
        @endif
    </div>
</div>

@if($canDeleteTransactions ?? false)
<dialog id="txDeleteModal" class="modal">
    <div class="modal-box">
        <h3 class="font-bold text-lg">Delete deduction transaction</h3>
        <p class="text-sm opacity-70 mt-1" id="txDeleteSummary"></p>
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
