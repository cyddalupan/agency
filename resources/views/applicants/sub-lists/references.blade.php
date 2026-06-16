<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Name</th>
                <th>Contact</th>
                <th>Relation</th>
                <th>Position</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->name }}</td>
                <td>{{ $r->contact ?? '—' }}</td>
                <td>{{ $r->relation ?? '—' }}</td>
                <td>{{ $r->position ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'references', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this reference?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
