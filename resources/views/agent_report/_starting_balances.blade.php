{{-- Tab 4: Starting Balance — new transaction type (StartingBalance), one per agent --}}
<div class="card bg-base-100 shadow-md">
    <div class="card-body">
        <h3 class="font-bold mb-3">⚖️ Starting Balance <span class="badge badge-sm badge-ghost">{{ $startingBalances->count() }}</span></h3>
        @if($startingBalances->count())
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
                        @foreach($startingBalances as $sb)
                            <tr>
                                <td>{{ $sb->date->format('M d, Y') }}</td>
                                <td>
                                    <span class="badge badge-sm badge-primary">{{ $sb->account }}</span>
                                </td>
                                <td>{{ $sb->agent->name ?? '—' }}</td>
                                <td>
                                    @if($sb->applicant)
                                        {{ $sb->applicant->last_name }}, {{ $sb->applicant->first_name }}
                                    @else —
                                    @endif
                                </td>
                                <td class="text-right font-semibold">₱{{ number_format((float) $sb->amount, 2) }}</td>
                                <td class="max-w-[16rem] truncate" title="{{ $sb->particular }}">{{ $sb->particular ?? '—' }}</td>
                                <td>
                                    <a href="{{ route('agent_report.starting_balance.show', $sb) }}" class="btn btn-xs btn-ghost whitespace-nowrap inline-flex items-center gap-1">Review →</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="opacity-50 text-sm py-6 text-center">No starting balance entries yet.</p>
        @endif
    </div>
</div>
