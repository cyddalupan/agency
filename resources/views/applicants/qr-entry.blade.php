@extends('layouts.guest')

@section('title', 'Add Applicant')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-lg">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body items-center text-center gap-4">
                <div class="text-5xl">📝</div>
                <h1 class="card-title text-2xl">Add Applicant</h1>
                <p class="text-sm opacity-70">
                    You scanned a QR code for <span class="font-semibold">{{ $agencyName }}</span>.
                </p>
                <p class="text-sm opacity-60">
                    Fill in your details and our staff will complete the rest.
                </p>

                <div class="w-full mt-2">
                    <a href="{{ route('applicants.create') }}" class="btn btn-primary w-full">
                        Open the Applicant Form →
                    </a>
                </div>

                <p class="text-xs opacity-50 mt-2">
                    If you were not expecting this, simply close the page.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
