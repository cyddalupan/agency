<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>OEC No.</th>
                <th>OEC Release</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->oec_no }}</td>
                <td>{{ $r->oec_release?->format('M d, Y') ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'oec', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this OEC record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
