<div class="overflow-x-auto">
    <table class="table table-sm">
        <thead>
            <tr>
                <th>Skill</th>
                <th>Proficiency</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach ($records as $r)
            <tr>
                <td>{{ $r->skill_name }}</td>
                <td>{{ ucfirst($r->proficiency) ?? '—' }}</td>
                <td>
                    <form action="{{ route('applicants.sub.destroy', [$r->applicant_id, 'skills', $r->id]) }}" method="POST"
                          onsubmit="return confirm('Delete this skill?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-ghost btn-xs text-error">Delete</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
