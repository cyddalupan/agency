<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Contact Number</th>
                <th>Type</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->contact }}</td>
                <td>{{ $r->type ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'contacts', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this contact?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
