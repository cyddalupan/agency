<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Name</th>
                <th>Relation</th>
                <th>Occupation</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->name ?? '—' }}</td>
                <td>{{ $r->relation ?? '—' }}</td>
                <td>{{ $r->occupation ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'family', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this family member?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
