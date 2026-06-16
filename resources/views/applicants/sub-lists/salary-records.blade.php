<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Amount</th>
                <th>Currency</th>
                <th>Type</th>
                <th>Notes</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ number_format($r->amount, 2) }}</td>
                <td>{{ $r->currency }}</td>
                <td>{{ ucfirst($r->type) ?? '—' }}</td>
                <td>{{ $r->notes ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'salary-records', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this salary record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
