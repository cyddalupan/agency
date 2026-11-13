{{-- Upload / replace one stored document. $field = column, $nameField = original filename column. --}}
@php
    $existing = $employee->{$field} ?? null;
    $existingName = $employee->{$nameField} ?? null;
@endphp
<div class="mt-2">
    <span class="label-text text-xs opacity-60">File</span>
    <input type="file" name="{{ $field }}" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
           class="file-input file-input-bordered file-input-sm w-full mt-1">
    @if($existing)
        <div class="flex items-center gap-2 mt-1 text-xs">
            <span class="badge badge-success badge-sm">Uploaded</span>
            <a href="{{ route('employees.document', ['employee' => $employee->id, 'field' => $field]) }}"
               class="link link-primary truncate" target="_blank">
                {{ $existingName ?: basename($existing) }}
            </a>
            <span class="opacity-40">— choosing a new file replaces it</span>
        </div>
    @else
        <div class="text-xs opacity-40 mt-1">No file uploaded yet</div>
    @endif
</div>
