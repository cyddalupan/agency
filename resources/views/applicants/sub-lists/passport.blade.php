<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Passport No.</th>
                <th>Issue Date</th>
                <th>Expiry Date</th>
                <th>Place of Issue</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->passport_no ?? '—' }}</td>
                <td>{{ $r->issue_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->expiry_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->place_of_issue ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'passport', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete passport record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
