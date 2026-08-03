<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Partner's Name</th>
                <th>Number of Children</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->partner_name ?? '—' }}</td>
                <td>{{ $r->number_of_children ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'spouse', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this spouse record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
