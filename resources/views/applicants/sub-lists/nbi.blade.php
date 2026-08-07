<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>NBI No.</th>
                <th>Date Issued</th>
                <th>Expiry Date</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->nbi_no }}</td>
                <td>{{ $r->issue_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->expiry_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'nbi', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this NBI record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
