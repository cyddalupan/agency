{{-- Status badge. Three values only: no `suspended` exists (§1, criterion 6). --}}
@php
    $status = (string) ($status ?? '');
    $classes = [
        'active'   => 'badge-success',
        'pending'  => 'badge-warning',
        'inactive' => 'badge-ghost',
    ][$status] ?? 'badge-ghost';
@endphp
<span class="badge badge-sm {{ $classes }}" data-status="{{ $status }}">{{ ucfirst($status ?: 'unknown') }}</span>
