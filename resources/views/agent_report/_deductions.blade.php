{{-- Tab 3: Deductions & Paid — new transaction type (AgentDeduction) --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">🧾 Deductions &amp; Paid <span class="badge badge-sm badge-ghost">{{ $deductions->count() }}</span></h3>
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
                                <td>
                                    <a href="{{ route('agent_report.deduction.show', $d) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No deductions or paid entries yet.</p>
        @endif
    </div>
</div>
