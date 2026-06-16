<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Level</th>
                <th>School</th>
                <th>Degree</th>
                <th>Year</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ ucfirst(str_replace('_', ' ', $r->level ?? '')) ?: '—' }}</td>
                <td>{{ $r->school ?? '—' }}</td>
                <td>{{ $r->degree ?? '—' }}</td>
                <td>{{ $r->year_graduated ?? '—' }}</td>
                <td class="flex gap-1">
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'education', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
