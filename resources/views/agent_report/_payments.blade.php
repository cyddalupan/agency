{{-- Tab 2: Payments — data source: Receivable module --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">💳 Payments <span class="badge badge-sm badge-ghost">{{ $payments->count() }}</span></h3>
        @if($payments->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Code</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Ref#/AR#</th>
                            <th>Applicant</th>
                            <th>Agent</th>
                            <th class="text-right">Amount</th>
                            <th>Account</th>
                            <th>Particular</th>
                            <th>Mode</th>
                            <th>Type</th>
                            <th>Deposit Account</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $r)
                            <tr class="{{ $r->status === 'received' ? 'opacity-70' : '' }}">
                                <td class="font-mono font-semibold">{{ $r->code }}</td>
                                <td>{{ $r->date->format('M d, Y') }}</td>
                                <td>{{ $r->encoder->name ?? '—' }}</td>
                                <td>
                                    @if($r->status === 'received')
                                        <span class="badge badge-success badge-sm">RECEIVED</span>
                                    @else
                                        <span class="badge badge-warning badge-sm">PENDING</span>
                                    @endif
                                </td>
                                <td>{{ $r->ref_ar ?? '—' }}</td>
                                <td>
                                    @if($r->applicant)
                                        {{ $r->applicant->first_name }} {{ $r->applicant->last_name }}
                                    @else —
                                    @endif
                                </td>
                                <td>{{ $r->agent->name ?? '—' }}</td>
                                <td class="text-right font-semibold">₱{{ number_format($r->amount, 2) }}</td>
                                <td>{{ $r->account ?? '—' }}</td>
                                <td class="max-w-[16rem] truncate" title="{{ $r->particular }}">{{ $r->particular ?? '—' }}</td>
                                <td>{{ $r->mode ?? '—' }}</td>
                                <td>{{ $r->type ?? '—' }}</td>
                                <td>{{ $r->debit_account ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('receivable.show', $r->id) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No payment transactions yet.</p>
        @endif
    </div>
</div>
