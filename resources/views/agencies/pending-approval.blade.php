@extends('layouts.app')

@section('title', 'Registration Submitted')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm text-center">
                <div class="card-body py-5">
                    <div class="mb-4">
                        <span class="display-1 text-warning">⏳</span>
                    </div>

                    <h3 class="mb-3">Registration Submitted!</h3>

                    <p class="lead text-muted mb-4">
                        Thank you for registering your agency. Your account is currently
                        <strong>pending approval</strong>.
                    </p>

                    <div class="alert alert-info" role="alert">
                        <i class="bi bi-info-circle"></i>
                        An administrator will review your application and activate your
                        account. You will receive a notification once your agency is approved.
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <hr class="my-4">

                    <p class="text-muted">
                        Questions? Contact the system administrator directly.
                    </p>

                    <a href="{{ route('agency.login') }}" class="btn btn-primary mt-3">
                        Go to Agency Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
