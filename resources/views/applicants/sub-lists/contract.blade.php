<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>RFP</th>
                <th>Sponsor</th>
                <th>Sponsor ID#</th>
                <th>Contact#</th>
                <th>Received</th>
                <th>Signed</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->rfp }}</td>
                <td>{{ $r->sponsor }}</td>
                <td>{{ $r->sponsor_id }}</td>
                <td>{{ $r->contact }}</td>
                <td>{{ $r->contract_received?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->contract_signed?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'contract', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this contract record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
