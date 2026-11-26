{{-- Tab 5: Agent Ledger — per-agent ledger, downloadable/printable --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">📑 Agent Ledger <span class="badge badge-sm badge-ghost">{{ $reportRows->count() }}</span></h3>
        @if($reportRows->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>{{ agent_term() }}</th>
                            <th>Branch</th>
                            <th class="text-right">Total Commission</th>
                            <th class="text-right">Total Cash Advance</th>
                            <th class="text-right">Total Backout and Repat</th>
                            <th class="text-right">Total Receivable (AR)</th>
                            <th class="text-right">Total Return Payment</th>
                            <th class="text-right">Total Payments</th>
                            <th class="text-right">Agent's Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRows as $row)
                            <tr>
                                <td class="font-semibold"><a href="{{ route('agent_report.show', $row['id']) }}" class="link link-primary hover:underline">{{ $row['agent'] }}</a></td>
                                <td>{{ $row['branch'] }}</td>
                                <td class="text-right">{{ peso($row['commission']) }}</td>
                                <td class="text-right">{{ peso($row['cash_advance']) }}</td>
                                <td class="text-right">{{ peso($row['backout_repat']) }}</td>
                                <td class="text-right">{{ peso($row['receivable']) }}</td>
                                <td class="text-right">{{ peso($row['return_payment']) }}</td>
                                <td class="text-right">{{ peso($row['payments']) }}</td>
                                <td class="text-right font-extrabold {{ $row['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                    {{ peso($row['balance']) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-base-200/70 font-bold">
                            <td colspan="2" class="text-right">Totals</td>
                            <td class="text-right">{{ peso($totals['commission']) }}</td>
                            <td class="text-right">{{ peso($totals['cash_advance']) }}</td>
                            <td class="text-right">{{ peso($totals['backout_repat']) }}</td>
                            <td class="text-right">{{ peso($totals['receivable']) }}</td>
                            <td class="text-right">{{ peso($totals['return_payment']) }}</td>
                            <td class="text-right">{{ peso($totals['payments']) }}</td>
                            <td class="text-right {{ $totals['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                {{ peso($totals['balance']) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3 text-xs opacity-60">
                <p>{{ agent_term() }}'s Balance = Total Cash Advance + Total Backout and Repat − Total Receivable (AR) + Total Return Payment − Total Payments</p>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No agents to report.</p>
        @endif
    </div>
</div>
