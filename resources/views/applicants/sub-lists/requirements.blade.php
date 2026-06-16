<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Type</th>
                <th>Reference No.</th>
                <th>Status</th>
                <th>Submitted</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ ucfirst($r->type) }}</td>
                <td>{{ $r->reference_no ?? '—' }}</td>
                <td><span class="badge badge-sm">{{ ucfirst($r->status) }}</span></td>
                <td>{{ $r->submitted_date?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'requirements', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this requirement?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
