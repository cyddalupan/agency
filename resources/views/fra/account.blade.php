@php
    $currentLang = app()->getLocale();
@endphp

@extends('layouts.employer-app-fra')

@section('title', __('messages.account'))
@section('head')

<style>
    .acct-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(0,0,0,.08);
        padding: 1.75rem 2rem;
        max-width: 560px;
        margin: 0 auto;
    }
    .acct-card h2 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: .25rem;
    }
    .acct-card .subtitle {
        font-size: .875rem;
        color: #64748b;
        margin-bottom: 1.5rem;
    }
    .acct-section-title {
        font-size: .95rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        padding-bottom: .5rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .acct-label {
        display: block;
        font-size: .75rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: .25rem;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .acct-value {
        display: block;
        padding: .55rem .75rem;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        font-size: .9rem;
        color: #1e293b;
        margin-bottom: .9rem;
        word-break: break-word;
    }
    .acct-note {
        font-size: .75rem;
        color: #64748b;
        margin-top: -.7rem;
        margin-bottom: .9rem;
        font-style: italic;
    }
    .acct-select {
        width: 100%;
        padding: .6rem .75rem;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        font-size: .9rem;
        background: #fff;
        color: #1e293b;
        margin-bottom: 1.25rem;
        cursor: pointer;
    }
    .acct-select:focus {
        outline: none;
        border-color: #29A1C4;
        box-shadow: 0 0 0 3px rgba(41,161,196,.15);
    }
    .acct-btn {
        background: #29A1C4;
        color: #fff;
        border: none;
        border-radius: 8px;
        padding: .65rem 2rem;
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: background .15s;
    }
    .acct-btn:hover {
        background: #218baa;
    }
    .acct-btn-danger {
        background: transparent;
        color: #ef4444;
        border: 1.5px solid #ef4444;
        border-radius: 8px;
        padding: .6rem 2rem;
        font-size: .85rem;
        font-weight: 600;
        cursor: pointer;
        transition: all .15s;
        text-decoration: none;
        display: inline-block;
    }
    .acct-btn-danger:hover {
        background: #fef2f2;
    }
    .acct-danger-zone {
        margin-top: 1.5rem;
        padding-top: 1.25rem;
        border-top: 1px solid #fca5a5;
    }
    .acct-danger-title {
        font-size: .8rem;
        font-weight: 700;
        color: #ef4444;
        text-transform: uppercase;
        letter-spacing: .03em;
        margin-bottom: .75rem;
    }
</style>
@endsection

@section('content')
<div class="acct-card">
    <h2>⚙️ {{ __('messages.account_settings') }}</h2>
    <p class="subtitle">{{ __('messages.language_settings') }}</p>

    {{-- Profile Section --}}
    <div class="acct-section-title">{{ __('messages.profile_information') }}</div>

    <label class="acct-label">{{ __('messages.name') }}</label>
    <div class="acct-value">{{ $user->name }}</div>

    <label class="acct-label">{{ __('messages.email') }}</label>
    <div class="acct-value">{{ $user->email }}</div>

    <label class="acct-label">{{ __('messages.username') }}</label>
    <div class="acct-value">{{ $user->username }}</div>

    <label class="acct-label">{{ __('messages.agency') }}</label>
    <div class="acct-value">{{ $userAgency?->name ?? '—' }}</div>
    <div class="acct-note">{{ __('messages.managed_by_agency_admin') }}</div>

    <label class="acct-label">{{ __('messages.company_name') }}</label>
    <div class="acct-value">{{ $employer?->name ?? '—' }}</div>

    <label class="acct-label">{{ __('messages.member_since') }}</label>
    <div class="acct-value">{{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</div>

    {{-- Language Section --}}
    <div class="acct-section-title" style="margin-top:1.5rem;">{{ __('messages.language_settings') }}</div>

    <form method="POST" action="{{ route('fra.account.language.update') }}">
        @csrf

        <label class="acct-label" for="language">{{ __('messages.preferred_language') }}</label>
        <select name="language" id="language" class="acct-select">
            @foreach ($languages as $code => $label)
                <option value="{{ $code }}" {{ $currentLang === $code ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="acct-btn">{{ __('messages.save_settings') }}</button>
    </form>

    {{-- Danger Zone --}}
    <div class="acct-danger-zone">
        <div class="acct-danger-title">⚠️ {{ __('messages.sign_out') }}</div>
        <form method="POST" action="{{ route('fra.logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="acct-btn-danger">{{ __('messages.sign_out') }}</button>
        </form>
    </div>
</div>
@endsection
