<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Type</th>
                <th>Certificate No.</th>
                <th>Issue Date</th>
                <th>Expiry Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ ucfirst($r->type) }}</td>
                <td>{{ $r->certificate_no ?? '—' }}</td>
                <td>{{ $r->issue_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->expiry_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'certificates', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this certificate?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
