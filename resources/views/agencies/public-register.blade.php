@extends('layouts.app')

@section('title', 'Register Your Agency')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Register Your Agency</h4>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">
                        Fill in the details below to register your recruitment agency.
                        An administrator will review and activate your account.
                    </p>

                    <form method="POST" action="{{ route('agency.register.post') }}">
                        @csrf

                        {{-- Agency Name --}}
                        <div class="mb-3">
                            <label for="agency_name" class="form-label">Agency Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('agency_name') is-invalid @enderror"
                                   id="agency_name"
                                   name="agency_name"
                                   value="{{ old('agency_name') }}"
                                   placeholder="e.g. Manila Global Recruitment"
                                   required>
                            @error('agency_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Subdomain --}}
                        <div class="mb-3">
                            <label for="subdomain" class="form-label">Subdomain <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error('subdomain') is-invalid @enderror"
                                       id="subdomain"
                                       name="subdomain"
                                       value="{{ old('subdomain') }}"
                                       placeholder="youragency"
                                       required>
                                <span class="input-group-text">.{{ request()->getHost() }}</span>
                                @error('subdomain')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-text">Only letters, numbers, and hyphens. Your agency will be accessible at <strong>youragency.{{ request()->getHost() }}</strong></div>
                        </div>

                        <hr class="my-4">

                        <h5 class="mb-3">Administrator Account</h5>

                        {{-- Admin Name --}}
                        <div class="mb-3">
                            <label for="admin_name" class="form-label">Admin Name <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control @error('admin_name') is-invalid @enderror"
                                   id="admin_name"
                                   name="admin_name"
                                   value="{{ old('admin_name') }}"
                                   placeholder="e.g. Juan Dela Cruz"
                                   required>
                            @error('admin_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Admin Email --}}
                        <div class="mb-3">
                            <label for="admin_email" class="form-label">Admin Email <span class="text-danger">*</span></label>
                            <input type="email"
                                   class="form-control @error('admin_email') is-invalid @enderror"
                                   id="admin_email"
                                   name="admin_email"
                                   value="{{ old('admin_email') }}"
                                   placeholder="admin@example.com"
                                   required>
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Admin Password --}}
                        <div class="mb-3">
                            <label for="admin_password" class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control @error('admin_password') is-invalid @enderror"
                                   id="admin_password"
                                   name="admin_password"
                                   placeholder="At least 8 characters"
                                   required>
                            @error('admin_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Minimum 8 characters.</div>
                        </div>

                        {{-- Confirm Password --}}
                        <div class="mb-3">
                            <label for="admin_password_confirmation" class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password"
                                   class="form-control"
                                   id="admin_password_confirmation"
                                   name="admin_password_confirmation"
                                   placeholder="Repeat your password"
                                   required>
                        </div>

                        <div class="d-grid gap-2 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Register Agency
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
