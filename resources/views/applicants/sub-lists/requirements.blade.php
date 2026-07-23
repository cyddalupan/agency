<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Type</th>
                <th>Reference No.</th>
                <th>Status</th>
                <th>Submitted</th>
                <th>File</th>
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
                    @if($r->file_path)
                        <a href="{{ Storage::url($r->file_path) }}" target="_blank" class="link link-primary text-xs flex items-center gap-1">
                            @php $ext = pathinfo($r->file_path, PATHINFO_EXTENSION); @endphp
                            @if(in_array($ext, ['jpg','jpeg','png','webp','gif']))
                                <img src="{{ Storage::url($r->file_path) }}" class="w-10 h-10 object-cover rounded" alt="Preview">
                            @else
                                📎
                            @endif
                            <span>View</span>
                        </a>
                    @else
                        <span class="text-xs opacity-40">—</span>
                    @endif
                </td>
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
