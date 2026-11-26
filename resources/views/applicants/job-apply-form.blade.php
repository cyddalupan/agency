@extends('layouts.qr')

@section('title', 'Apply Now — ' . $positionName)

@section('content')
<div class="min-h-screen bg-base-200 py-8 px-4">
    <div class="max-w-2xl mx-auto">

        <div class="card bg-base-100 shadow-xl">
            <div class="card-body p-6 sm:p-8">

                @if ($submitted)
                    {{-- Thank-you state. Deliberately does not confirm which
                         record was created or echo the applicant's details back:
                         this URL is public, and anyone holding the link can
                         reload it. --}}
                    <div class="text-center py-8">
                        <div class="text-6xl mb-4">✅</div>
                        <h1 class="text-2xl font-bold mb-2">Application received</h1>
                        <p class="opacity-70">
                            Thank you — your application for
                            <strong>{{ $positionName }}</strong> has been submitted.
                        </p>
                        <p class="opacity-60 text-sm mt-4">
                            Our team will review it and contact you if you are shortlisted.
                        </p>
                    </div>
                @else
                    <div class="mb-6">
                        <p class="text-xs uppercase tracking-wider opacity-60 font-semibold mb-1">Apply Now</p>
                        <h1 class="text-2xl font-bold">{{ $positionName }}</h1>
                        @if ($employerName)
                            <p class="opacity-70 text-sm mt-1">🏢 {{ $employerName }}</p>
                        @endif
                        @if ($agencyName)
                            <p class="opacity-60 text-sm">via {{ $agencyName }}</p>
                        @endif
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-error mb-4">
                            <div>
                                <p class="font-semibold">Please check the form:</p>
                                <ul class="list-disc list-inside text-sm">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif

                    <form method="POST"
                          action="{{ route('job-apply.store', ['token' => $token]) }}"
                          enctype="multipart/form-data"
                          class="space-y-4">
                        @csrf

                        {{-- The position is shown, never submitted. The server
                             reads it from the Job Order row. --}}
                        <div class="p-3 rounded-lg bg-base-200/60">
                            <p class="text-xs uppercase tracking-wider opacity-60 font-semibold">Position applied for</p>
                            <p class="font-semibold mt-1">{{ $positionName }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">First name <span class="text-error">*</span></span>
                                <input type="text" name="first_name" class="input input-bordered w-full"
                                       value="{{ old('first_name') }}" required>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Last name <span class="text-error">*</span></span>
                                <input type="text" name="last_name" class="input input-bordered w-full"
                                       value="{{ old('last_name') }}" required>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Middle name</span>
                                <input type="text" name="middle_name" class="input input-bordered w-full"
                                       value="{{ old('middle_name') }}">
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Suffix</span>
                                <input type="text" name="suffix" class="input input-bordered w-full"
                                       value="{{ old('suffix') }}" placeholder="Jr., Sr., III…">
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Date of birth</span>
                                <input type="date" name="birthdate" class="input input-bordered w-full"
                                       value="{{ old('birthdate') }}">
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Gender</span>
                                <select name="gender" class="select select-bordered w-full">
                                    <option value="">Prefer not to say</option>
                                    <option value="male"   @selected(old('gender') === 'male')>Male</option>
                                    <option value="female" @selected(old('gender') === 'female')>Female</option>
                                </select>
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Contact number</span>
                                <input type="text" name="contact" class="input input-bordered w-full"
                                       value="{{ old('contact') }}">
                            </label>
                            <label class="form-control w-full">
                                <span class="label-text font-medium mb-1">Email</span>
                                <input type="email" name="email" class="input input-bordered w-full"
                                       value="{{ old('email') }}">
                            </label>
                        </div>

                        <label class="form-control w-full">
                            <span class="label-text font-medium mb-1">Address</span>
                            <textarea name="address" rows="2" class="textarea textarea-bordered w-full">{{ old('address') }}</textarea>
                        </label>

                        <label class="form-control w-full">
                            <span class="label-text font-medium mb-1">Resume</span>
                            <input type="file" name="resume" accept=".pdf,.jpg,.jpeg,.png"
                                   class="file-input file-input-bordered w-full">
                            <span class="label-text-alt opacity-60 mt-1">PDF, JPG or PNG · up to 5 MB</span>
                        </label>

                        <button type="submit" class="btn btn-primary w-full mt-2">
                            Submit application
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <p class="text-center text-xs opacity-50 mt-4">
            Your details are used only to consider your application.
        </p>
    </div>
</div>
@endsection
