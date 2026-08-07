<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Airline</th>
                <th>Flight Date</th>
                <th>Flight Time</th>
                <th>Remarks</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->airline }}</td>
                <td>{{ $r->flight_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->flight_time ?? '—' }}</td>
                <td>{{ $r->flight_remarks ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'ticket', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this ticket record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
