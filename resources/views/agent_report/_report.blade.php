{{-- Tab 5: Agent Report — per-agent ledger, downloadable/printable --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">📑 Agent Report <span class="badge badge-sm badge-ghost">{{ $reportRows->count() }}</span></h3>
        @if($reportRows->count())
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Agent</th>
                            <th>Branch</th>
                            <th class="text-right">Starting Balance</th>
                            <th class="text-right">Commission</th>
                            <th class="text-right">Payments</th>
                            <th class="text-right">Deductions</th>
                            <th class="text-right">Paid</th>
                            <th class="text-right">Net Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reportRows as $row)
                            <tr>
                                <td class="font-semibold">{{ $row['agent'] }}</td>
                                <td>{{ $row['branch'] }}</td>
                                <td class="text-right">₱{{ number_format($row['starting_balance'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($row['commission'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($row['payments'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($row['deductions'], 2) }}</td>
                                <td class="text-right">₱{{ number_format($row['paid'], 2) }}</td>
                                <td class="text-right font-extrabold {{ $row['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                    ₱{{ number_format($row['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-base-200/70 font-bold">
                            <td colspan="2" class="text-right">Totals</td>
                            <td class="text-right">₱{{ number_format($totals['starting_balance'], 2) }}</td>
                            <td class="text-right">₱{{ number_format($totals['commission'], 2) }}</td>
                            <td class="text-right">₱{{ number_format($totals['payments'], 2) }}</td>
                            <td class="text-right">₱{{ number_format($totals['deductions'], 2) }}</td>
                            <td class="text-right">₱{{ number_format($totals['paid'], 2) }}</td>
                            <td class="text-right {{ $totals['balance'] >= 0 ? 'text-success' : 'text-error' }}">
                                ₱{{ number_format($totals['balance'], 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="mt-3 text-xs opacity-60">
                <p>Net Balance = Starting Balance + Commission + Payments − Deductions − Paid</p>
                <p>Positive = agency owes the agent (credit) · Negative = agent owes the agency (debit)</p>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No agents to report.</p>
        @endif
    </div>
</div>
