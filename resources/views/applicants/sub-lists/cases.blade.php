{{-- Cases sub-list — full CRUD: table + per-row inline edit toggle + delete. --}}
<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Case No.</th>
                <th>Case Title</th>
                <th>Date Received</th>
                <th>Date Hearing</th>
                <th>FRA/Employer</th>
                <th>Status</th>
                <th>Court</th>
                <th>Message</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td class="whitespace-nowrap">{{ $r->case_number ?? '—' }}</td>
                <td class="font-medium">{{ $r->title ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $r->date_received?->format('M d, Y') ?? '—' }}</td>
                <td class="whitespace-nowrap">{{ $r->date_hearing?->format('M d, Y') ?? '—' }}</td>
                <td>{{ $r->employer?->name ?? '—' }}</td>
                <td>
                    @if (($r->status ?? 'open') === 'closed')
                        <span class="badge badge-ghost badge-sm">Closed</span>
                    @else
                        <span class="badge badge-success badge-sm">Open</span>
                    @endif
                </td>
                <td>{{ $r->court ?? '—' }}</td>
                <td>
                    @php $msg = trim((string) ($r->description ?? '')); @endphp
                    @if ($msg === '')
                        <span class="opacity-40">—</span>
                    @else
                        <span title="{{ e($msg) }}">{{ \Illuminate\Support\Str::limit($msg, 60) }}</span>
                    @endif
                </td>
                <td class="flex gap-1">
                    <button type="button" class="btn btn-ghost btn-xs"
                            onclick="document.getElementById('edit-case-{{ $r->id }}').classList.toggle('hidden')">✏️ Edit</button>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'cases', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this case?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            <tr id="edit-case-{{ $r->id }}" class="hidden bg-base-200/40">
                <td colspan="9" class="p-3">
                    <form action="{{ route('applicants.sub.update', [$r->applicant_id, 'cases', $r->id]) }}" method="POST">
                        @csrf @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            @include('applicants.sub-forms.cases', ['record' => $r])
                        </div>
                        <div class="flex items-center gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-sm">💾 Save Changes</button>
                            <button type="button" class="btn btn-ghost btn-sm"
                                    onclick="document.getElementById('edit-case-{{ $r->id }}').classList.add('hidden')">❌ Cancel</button>
                        </div>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
