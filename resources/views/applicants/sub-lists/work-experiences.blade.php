<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Company</th>
                <th>Position</th>
                <th>From</th>
                <th>To</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->company ?? '—' }}</td>
                <td>{{ $r->position ?? '—' }}</td>
                <td>{{ $r->from_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->to_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'work-experiences', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this work experience?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
