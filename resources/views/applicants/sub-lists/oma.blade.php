<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>From</th>
                <th>To</th>
                <th>OMA Released</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->from_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->to_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->released_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'oma', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this OMA record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
