<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Clinic Name</th>
                <th>Issue Date</th>
                <th>Expire Date</th>
                <th>Remarks</th>
                <th>Medical</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->clinic_name ?? '—' }}</td>
                <td>{{ $r->issue_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->expiry_date?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->remarks ?? '—' }}</td>
                <td>
                    @if ($r->file_path)
                        @php $ext = pathinfo($r->file_path, PATHINFO_EXTENSION); @endphp
                        @if (in_array($ext, ['jpg','jpeg','png','webp','gif']))
                            <a href="{{ Storage::url($r->file_path) }}" target="_blank">
                                <img src="{{ Storage::url($r->file_path) }}" alt="medical file" class="w-12 h-12 object-cover rounded">
                            </a>
                        @else
                            <a href="{{ Storage::url($r->file_path) }}" target="_blank" class="link link-primary text-xs">
                                {{ basename($r->file_path) }}
                            </a>
                        @endif
                    @else
                        <span class="text-base-300 text-xs">—</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'medical', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this medical record?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
