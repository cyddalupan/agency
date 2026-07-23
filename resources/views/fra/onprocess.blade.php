@extends('layouts.employer-app-fra')

@section('title', __('messages.on_process_applicants'))

@section('head')
<style>
/* ── Header area ── */
.lc-header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}
.lc-header h1 {
    font-size: 22px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
}
.lc-export-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    background: #29A1C4;
    color: #fff;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    border: none;
    cursor: pointer;
}
.lc-export-btn:hover {
    background: #1e7e9a;
}

/* ── Filter badges ── */
.lc-filter-group {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}
.lc-filter {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 14px;
    border-radius: 999px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
}
.lc-filter.active {
    background: #29A1C4;
    color: #fff;
}
.lc-filter:not(.active) {
    background: #f1f5f9;
    color: #475569;
}
.lc-filter:not(.active):hover {
    background: #e2e8f0;
}
.lc-filter .lc-count {
    font-weight: 400;
    opacity: 0.7;
}

/* ── Table ── */
.lc-table-wrap {
    overflow-x: auto;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.lc-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.lc-table thead {
    background: #f8fafc;
    border-bottom: 2px solid #e2e8f0;
}
.lc-table th {
    text-align: left;
    padding: 12px 14px;
    font-weight: 700;
    color: #475569;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    white-space: nowrap;
}
.lc-table td {
    padding: 10px 14px;
    color: #1e293b;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.lc-table tbody tr:hover {
    background: #fafbff;
}
.lc-table tbody tr:last-child td {
    border-bottom: none;
}

/* ── Table photo ── */
.lc-table img.lc-photo {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
}

/* ── Exp badges ── */
.exp-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
}
.exp-badge.firstimer {
    background: #dcfce7;
    color: #15803d;
}
.exp-badge.exabroad {
    background: #f3e8ff;
    color: #7c3aed;
}

/* ── Status badge ── */
.status-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    font-size: 11px;
    font-weight: 600;
    color: #fff;
    white-space: nowrap;
}

/* ── Buttons ── */
.btn-select {
    padding: 5px 12px;
    background: #29A1C4;
    color: #fff;
    border: none;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-select:hover {
    background: #1e7e9a;
}
.btn-select:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.btn-view {
    padding: 5px 12px;
    background: transparent;
    color: #475569;
    border: 1px solid #cbd5e1;
    border-radius: 5px;
    font-size: 12px;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
}
.btn-view:hover {
    background: #f1f5f9;
}
.lc-actions {
    display: flex;
    gap: 6px;
    align-items: center;
}

/* ── Empty state ── */
.lc-empty {
    text-align: center;
    padding: 48px 0;
    color: #94a3b8;
    font-size: 14px;
}

/* ── Export icon svg ── */
.lc-icon-svg {
    width: 16px;
    height: 16px;
    display: inline-block;
    vertical-align: middle;
}

/* ── Responsive ── */
@media (max-width: 767px) {
    .lc-table th, .lc-table td {
        padding: 8px 10px;
        font-size: 12px;
    }
    .lc-table img.lc-photo {
        width: 32px;
        height: 32px;
    }
}
</style>
@endsection

@section('content')
<div>
    {{-- Header --}}
    <div class="lc-header">
        <h1>{{ __('messages.on_process_applicants') }}</h1>
        <a href="{{ route('fra.onprocess.export') }}" class="lc-export-btn">
            <svg class="lc-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            {{ __('messages.export_excel') }}
        </a>
    </div>

    {{-- Filter badges --}}
    <div class="lc-filter-group" role="group" aria-label="filter">
        <a href="{{ route('fra.onprocess') }}"
           class="lc-filter {{ !request('position') ? 'active' : '' }}"
           aria-current="{{ !request('position') ? 'true' : 'false' }}">
            {{ __('messages.all') }}
            <span class="lc-count">({{ $applicants->count() }})</span>
        </a>
        @foreach($applicants->pluck('position')->filter()->unique('id') as $pos)
            @php $count = $applicants->where('position_id', $pos->id)->count(); @endphp
            <a href="{{ route('fra.onprocess', ['position' => $pos->name]) }}"
               class="lc-filter {{ request('position') === $pos->name ? 'active' : '' }}"
               aria-current="{{ request('position') === $pos->name ? 'true' : 'false' }}">
                {{ strtoupper($pos->name) }}
                <span class="lc-count">({{ $count }})</span>
            </a>
        @endforeach
    </div>

    @php
        $filtered = !request('position')
            ? $applicants
            : $applicants->filter(function($a) {
                return $a->position && $a->position->name === request('position');
            });
    @endphp

    {{-- Table --}}
    <div class="lc-table-wrap">
        <table class="lc-table">
            <thead>
                <tr>
                    <th>{{ __('messages.hash') }}</th>
                    <th>{{ __('messages.photo') }}</th>
                    <th>{{ __('messages.name') }}</th>
                    <th>{{ __('messages.position') }}</th>
                    <th>{{ __('messages.passport') }}</th>
                    <th>{{ __('messages.experience') }}</th>
                    <th>{{ __('messages.status') }}</th>
                    <th>{{ __('messages.medical') }}</th>
                    <th>{{ __('messages.medical_expiry') }}</th>
                    <th>{{ __('messages.wakala') }}</th>
                    <th>{{ __('messages.contract_received') }}</th>
                    <th>{{ __('messages.contract_signed') }}</th>
                    <th>{{ __('messages.action') }}</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filtered as $index => $applicant)
                @php
                    $photoUrl = $applicant->photo
                        ?? 'https://picsum.photos/seed/' . $applicant->id . '/80/80';
                    $fullName = $applicant->full_name;
                @endphp
                <tr class="{{ $index % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                    <td class="text-gray-500">{{ $index + 1 }}</td>
                    <td>
                        <img src="{{ $photoUrl }}" alt="{{ $fullName }}" class="lc-photo" loading="lazy">
                    </td>
                    <td><strong>{{ $fullName }}</strong></td>
                    <td>{{ $applicant->position?->name ?? '—' }}</td>
                    <td>{{ $applicant->passport?->passport_no ?? '—' }}</td>
                    <td>
                        <span class="{{ $applicant->is_exabroad ? 'exp-badge exabroad' : 'exp-badge firstimer' }}">
                            {{ $applicant->is_exabroad ? __('messages.exabroad') : __('messages.firstimer') }}
                        </span>
                    </td>
                    <td>
                        @if($applicant->statusCode)
                        <span class="status-badge" style="background: {{ $applicant->statusCode->color }};">
                            {{ $applicant->statusCode->description }}
                        </span>
                        @else
                        —
                        @endif
                    </td>
                    <td>
                        @if($applicant->medical_status)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $applicant->medical_status === 'fit' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $applicant->medical_status === 'fit' ? __('messages.fit') : ucfirst($applicant->medical_status) }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td>{{ $applicant->medical_expiry ? \Carbon\Carbon::parse($applicant->medical_expiry)->format('d-M-Y') : '—' }}</td>
                    <td>
                        @if($applicant->wakala_status)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            {{ $applicant->wakala_status === 'paid' ? 'bg-green-100 text-green-800' : ($applicant->wakala_status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                            {{ $applicant->wakala_status === 'paid' ? __('messages.paid') : ($applicant->wakala_status === 'pending' ? __('messages.pending') : strtoupper($applicant->wakala_status)) }}
                        </span>
                        @else
                        <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td>
                        @if($applicant->contract_received)
                        <span class="inline-flex items-center gap-1 text-green-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                            {{ __('messages.yes') }}
                        </span>
                        @else
                        <span class="text-red-400">—</span>
                        @endif
                    </td>
                    <td>
                        @if($applicant->contract_signed)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            {{ __('messages.signed') }}
                        </span>
                        @elseif($applicant->contract_received)
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            {{ __('messages.pending') }}
                        </span>
                        @else
                        <span class="text-red-400">—</span>
                        @endif
                    </td>
                    <td>
                        <div class="lc-actions">
                            <a href="{{ route('fra.lineup.view', $applicant) }}" class="btn-view">{{ __('messages.view') }}</a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="lc-empty">{{ __('messages.no_applicants_onprocess') }}</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
