{{-- Tab 1: Commission — data source: ExpenseRequest items charged to agent --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">💼 Commission <span class="badge badge-sm badge-ghost">{{ $commissionItems->count() }}</span></h3>
        @if($commissionItems->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Ref#</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Offices</th>
                            <th>Applicant</th>
                            <th>{{ agent_term() }}</th>
                            <th>Currency</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Convert</th>
                            <th>Account</th>
                            <th>Country</th>
                            <th>Particular / Description</th>
                            <th>Charge</th>
                            @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']))
                                <th>Review</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($commissionItems as $item)
                            @php
                                $req = $item->expenseRequest;
                                $phpEquivalent = $converter->toPhp((float) $item->amount, $item->currency);
                            @endphp
                            <tr>
                                <td class="font-mono">{{ $req->reference_no }}</td>
                                <td>{{ $req->date?->format('Y-m-d') }}</td>
                                <td>{{ $req->user?->name ?? $req->user?->username ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $req->statusBadge() }}">{{ $req->statusLabel() }}</span>
                                </td>
                                <td>{{ $req->branch?->name ?? '—' }}</td>
                                <td>{{ $item->applicant ? $item->applicant->last_name . ', ' . $item->applicant->first_name : '—' }}</td>
                                <td>{{ $item->agent?->name ?? '—' }}</td>
                                <td>{{ $item->currency }}</td>
                                <td class="text-right font-semibold align-top whitespace-nowrap">
                                    {{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $item->amount, 2) }}
                                </td>
                                <td class="text-right opacity-70 align-top whitespace-nowrap">
                                    @if($item->currency === 'USD')
                                        ≈ ₱{{ number_format($phpEquivalent, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->account?->name ?? '—' }}</td>
                                <td>{{ $item->country?->name ?? '—' }}</td>
                                <td class="max-w-[26rem] whitespace-normal break-words align-top" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                                <td class="align-top">
                                    <span class="badge badge-sm {{ $item->charge === 'office' ? 'badge-ghost' : 'badge-info' }}">{{ $item->charge }}</span>
                                </td>
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']))
                                    <td class="align-top">
                                        <a href="{{ route('expense_request.show', $req) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No commission transactions yet.</p>
        @endif
    </div>
</div>

{{-- Released expenses on the Fit to work / Deployed accounts (Cyd 2026-11-01).
     These never appeared on any tab: Receivables only lists PENDING rows and the
     Report tab only aggregates them into the Commission column. --}}
<div class="card bg-base-100 shadow-md mt-4">
    <div class="card-body">
        <h3 class="font-bold mb-1">✅ Released Expenses — Fit to work &amp; Deployed
            <span class="badge badge-sm badge-ghost">{{ $releasedCommissionItems->count() }}</span>
        </h3>
        <p class="text-xs opacity-60 mb-3">All released agent expenses charged to the <strong>Fit to work</strong> and <strong>Deployed</strong> accounts.</p>
        @if($releasedCommissionItems->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Ref#</th>
                            <th>Date</th>
                            <th>User</th>
                            <th>Status</th>
                            <th>Offices</th>
                            <th>Applicant</th>
                            <th>{{ agent_term() }}</th>
                            <th>Currency</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Convert</th>
                            <th>Account</th>
                            <th>Country</th>
                            <th>Particular / Description</th>
                            <th>Charge</th>
                            @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']))
                                <th>Review</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($releasedCommissionItems as $item)
                            @php
                                $req = $item->expenseRequest;
                                $phpEquivalent = $converter->toPhp((float) $item->amount, $item->currency);
                            @endphp
                            <tr>
                                <td class="font-mono">{{ $req->reference_no }}</td>
                                <td>{{ $req->date?->format('Y-m-d') }}</td>
                                <td>{{ $req->user?->name ?? $req->user?->username ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-sm {{ $req->statusBadge() }}">{{ $req->statusLabel() }}</span>
                                </td>
                                <td>{{ $req->branch?->name ?? '—' }}</td>
                                <td>{{ $item->applicant ? $item->applicant->last_name . ', ' . $item->applicant->first_name : '—' }}</td>
                                <td>{{ $item->agent?->name ?? '—' }}</td>
                                <td>{{ $item->currency }}</td>
                                <td class="text-right font-semibold align-top whitespace-nowrap">
                                    {{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $item->amount, 2) }}
                                </td>
                                <td class="text-right opacity-70 align-top whitespace-nowrap">
                                    @if($item->currency === 'USD')
                                        ≈ ₱{{ number_format($phpEquivalent, 2) }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $item->account?->name ?? '—' }}</td>
                                <td>{{ $item->country?->name ?? '—' }}</td>
                                <td class="max-w-[26rem] whitespace-normal break-words align-top" title="{{ $item->particular }}">{{ $item->particular ?? '—' }}</td>
                                <td class="align-top">
                                    <span class="badge badge-sm {{ $item->charge === 'office' ? 'badge-ghost' : 'badge-info' }}">{{ $item->charge }}</span>
                                </td>
                                @if(in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']))
                                    <td class="align-top">
                                        <a href="{{ route('expense_request.show', $req) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-base-200/70 font-semibold">
                            <td colspan="8">Total ({{ $releasedCommissionItems->count() }} released)</td>
                            <td class="text-right">—</td>
                            <td class="text-right">₱{{ number_format($releasedCommissionTotal, 2) }}</td>
                            <td colspan="{{ in_array(auth()->user()->user_type, ['super_admin', 'admin', 'billing']) ? 5 : 4 }}"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No released expenses on the Fit to work / Deployed accounts yet.</p>
        @endif
    </div>
</div>
