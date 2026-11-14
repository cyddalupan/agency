@extends('layouts.qr')

@section('title', 'Add Applicant')

@section('content')
<div class="min-h-screen flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-xl">
        <div class="card bg-base-100 shadow-xl">
            <div class="card-body gap-3">
                <div class="text-center mb-2">
                    <div class="text-4xl">📝</div>
                    <h1 class="text-2xl font-bold mt-2">Add Applicant</h1>
                    <p class="text-sm opacity-70 mt-1">
                        {{ $agencyName }} — please fill in your details.
                    </p>
                </div>

                @if(session('qr_success'))
                    <div class="alert alert-success">
                        <span>✅ {{ session('qr_success') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <div class="text-sm">
                            @foreach($errors->all() as $e)
                                <div>• {{ $e }}</div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('applicant-qr.store', ['token' => $token]) }}" class="flex flex-col gap-3">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="form-control w-full">
                            <span class="label-text">First Name <span class="text-error">*</span></span>
                            <input type="text" name="first_name" class="input input-bordered w-full"
                                   value="{{ old('first_name') }}" required>
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text">Last Name <span class="text-error">*</span></span>
                            <input type="text" name="last_name" class="input input-bordered w-full"
                                   value="{{ old('last_name') }}" required>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="form-control w-full">
                            <span class="label-text">Middle Name</span>
                            <input type="text" name="middle_name" class="input input-bordered w-full"
                                   value="{{ old('middle_name') }}">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text">Suffix</span>
                            <input type="text" name="suffix" class="input input-bordered w-full"
                                   value="{{ old('suffix') }}" placeholder="Jr., Sr., III">
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="form-control w-full">
                            <span class="label-text">Birthdate</span>
                            <input type="date" name="birthdate" class="input input-bordered w-full"
                                   value="{{ old('birthdate') }}">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text">Gender</span>
                            <select name="gender" class="select select-bordered w-full">
                                <option value="">—</option>
                                <option value="Male" @selected(old('gender')==='Male')>Male</option>
                                <option value="Female" @selected(old('gender')==='Female')>Female</option>
                            </select>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="form-control w-full">
                            <span class="label-text">Contact Number</span>
                            <input type="text" name="contact" class="input input-bordered w-full"
                                   value="{{ old('contact') }}">
                        </label>
                        <label class="form-control w-full">
                            <span class="label-text">Email</span>
                            <input type="email" name="email" class="input input-bordered w-full"
                                   value="{{ old('email') }}">
                        </label>
                    </div>

                    <label class="form-control w-full">
                        <span class="label-text">Address</span>
                        <textarea name="address" class="textarea textarea-bordered w-full" rows="2">{{ old('address') }}</textarea>
                    </label>

                    <label class="form-control w-full">
                        <span class="label-text">Position Applying For</span>
                        <select name="position_id" class="select select-bordered w-full">
                            <option value="">—</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}" @selected(old('position_id') == $pos->id)>
                                    {{ $pos->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <button type="submit" class="btn btn-primary w-full mt-2">
                        Submit Application
                    </button>

                    <p class="text-xs opacity-50 text-center mt-1">
                        Our staff will review the rest of your details.
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
