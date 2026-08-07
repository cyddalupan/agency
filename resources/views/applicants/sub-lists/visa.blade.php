<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Visa No.</th>
                <th>Received</th>
                <th>Stamped</th>
                <th>Expiry</th>
                <th>Musaned</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->visa_no }}</td>
                <td>{{ $r->received_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->stamped_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->expiry_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->approved_musaned ? ucfirst($r->approved_musaned) : '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'visa', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this VISA record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
