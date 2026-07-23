@extends('layouts.employer-app-fra')

@section('title', __('messages.line_up') . ' — ' . $applicant->full_name)

@section('head')
<style>
/* ── Single view page ── */
.sv-container { max-width: 800px; margin: 0 auto; }

/* Photo circle */
.sv-photo-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin-bottom: 24px;
}
.sv-photo-circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background: transparent;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    border: 3px solid #fff;
}


/* Cards */
.sv-card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    padding: 24px;
    margin-bottom: 16px;
}
.sv-card h2 {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 16px 0;
    padding-bottom: 12px;
    border-bottom: 1px solid #e2e8f0;
}
.sv-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    font-size: 13px;
    border-bottom: 1px solid #f1f5f9;
}
.sv-row:last-child { border-bottom: none; }
.sv-label { color: #64748b; font-weight: 500; }
.sv-value { color: #1e293b; font-weight: 600; }

/* Back link */
.sv-back {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: #29A1C4;
    font-weight: 600;
    font-size: 13px;
    text-decoration: none;
    margin-bottom: 20px;
    padding: 4px 0;
}
.sv-back:hover { text-decoration: underline; }

/* Select button in single view */
.btn-select-sv {
    padding: 8px 20px;
    background: #29A1C4;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-select-sv:hover { background: #1e7e9a; }
.btn-select-sv:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}
.btn-select-sv.selecting { background: #15803d; }

/* Mobile */
@media (max-width: 767px) {
    .sv-card { padding: 16px; }
    .sv-row { flex-direction: column; align-items: flex-start; gap: 2px; padding: 10px 0; }
    .sv-label { font-size: 11px; }
    .sv-value { font-size: 14px; }
}
</style>
<script>
function selectApplicantSV(btn, applicantName) {
    btn.disabled = true;
    btn.classList.add('selecting');
    btn.innerHTML = '{{ __('messages.selected_done') }}';
    var toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;top:16px;right:16px;background:#15803d;color:#fff;padding:12px 20px;border-radius:8px;font-size:14px;font-weight:600;box-shadow:0 4px 12px rgba(0,0,0,0.15);z-index:9999;display:flex;align-items:center;gap:8px;';
    toast.innerHTML = '{{ __('messages.selected_done') }} <strong>' + applicantName + '</strong> {{ __('messages.selected_tab_content') }}. <em style="opacity:0.8;font-size:12px;">Refreshing...</em>';
    document.body.appendChild(toast);
    setTimeout(function() {
        var form = btn.closest('form');
        if (form) form.submit();
    }, 600);
}
</script>
@endsection

@section('content')
<div class="sv-container">
    <a href="{{ route('fra.lineup') }}" class="sv-back">&larr; {{ __('messages.back_to_line_up') }}</a>

    {{-- Photo --}}
@php $photoUrl = $applicant->photo ?? 'https://picsum.photos/seed/' . $applicant->id . '/160/160'; @endphp
    <div class="sv-photo-wrapper">
        <div class="sv-photo-circle">
            <img src="{{ $photoUrl }}" alt="{{ $applicant->full_name }}" style="width:120px;height:120px;border-radius:50%;object-fit:cover;" loading="lazy">
        </div>
    </div>

    {{-- Details card --}}
    <div class="sv-card">
        <h2>{{ $applicant->full_name }}</h2>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.first_name') }}</span>
            <span class="sv-value">{{ $applicant->first_name }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.last_name') }}</span>
            <span class="sv-value">{{ $applicant->last_name }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.passport_no') }}</span>
            <span class="sv-value">{{ $applicant->passport?->passport_no ?? '—' }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.position') }}</span>
            <span class="sv-value">{{ $applicant->position?->name ?? '—' }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.status') }}</span>
            <span class="sv-value">{{ $applicant->statusCode?->description ?? $applicant->statusCode?->label ?? '—' }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.experience') }}</span>
            <span class="sv-value">{{ $applicant->is_exabroad ? __('messages.exabroad') : __('messages.firstimer') }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.total_experience') }}</span>
            <span class="sv-value">{{ $applicant->total_experience_years ?? 0 }} {{ __('messages.years') }}</span>
        </div>
        <div class="sv-row">
            <span class="sv-label">{{ __('messages.date_added') }}</span>
            <span class="sv-value">{{ $applicant->created_at ? $applicant->created_at->format('d-M-Y') : '—' }}</span>
        </div>
    </div>

    {{-- Actions card --}}
    <div class="sv-card" style="display:flex;justify-content:flex-end;gap:8px;padding:16px 24px;">
        <a href="{{ route('fra.lineup') }}" style="padding:8px 16px;background:transparent;color:#475569;border:1px solid #cbd5e1;border-radius:6px;font-size:13px;font-weight:600;text-decoration:none;">{{ __('messages.back_to_list') }}</a>
        <form method="POST" action="{{ route('fra.lineup.select', $applicant) }}" style="display:inline">
            @csrf
            <button type="button" class="btn-select-sv" onclick="selectApplicantSV(this, '{{ addslashes($applicant->full_name) }}')">{{ __('messages.select') }}</button>
        </form>
    </div>
</div>
@endsection
