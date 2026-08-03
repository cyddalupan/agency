@extends('layouts.app')

@section('title', 'Settings')

@section('content')
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">⚙️ Settings</h1>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg">👤 Profile</h2>
                <p class="text-sm opacity-70">Manage your account details and preferences.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg">🔐 Security</h2>
                <p class="text-sm opacity-70">Password, two-factor authentication, and session management.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg">🔔 Notifications</h2>
                <p class="text-sm opacity-70">Configure notification preferences and email alerts.</p>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm">
            <div class="card-body">
                <h2 class="card-title text-lg">🎨 Branding</h2>
                <p class="text-sm opacity-70">Agency logo, colors, and customizations.</p>
            </div>
        </div>

        <a href="{{ route('settings.applicant-form-defaults') }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body">
                <h2 class="card-title text-lg">📋 Applicant Form Defaults</h2>
                <p class="text-sm opacity-70">Choose which positions, statuses, and sources appear on the Add Applicant form.</p>
            </div>
        </a>
    </div>
</div>
@endsection
