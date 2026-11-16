@extends('layouts.app')

@section('title', 'Edit Risk ' . $risk->ref_no)

@section('content')
<div class="p-4 sm:p-6 space-y-6">
    @if ($errors->any())
        <div class="alert alert-error" id="form-errors">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
        </div>
    @endif

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg">
        <div class="card-body py-5 px-6 flex flex-row items-center justify-between">
            <div class="flex items-center gap-3">
                <span class="text-3xl">⚠️</span>
                <h1 class="text-2xl font-extrabold">
                    <span class="font-mono">{{ $risk->ref_no }}</span> {{ $risk->title }}
                </h1>
            </div>
            <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost">← Register</a>
        </div>
    </div>

    <form method="POST" action="{{ route('risks.update', $risk) }}" class="card bg-base-100 shadow" id="risk-form">
        @csrf @method('PUT')
        <div class="card-body">
            @include('risks._form', ['risk' => $risk])
            <div class="card-actions justify-end mt-2">
                <a href="{{ route('risks.index') }}" class="btn btn-sm btn-ghost">Cancel</a>
                <button type="submit" class="btn btn-sm btn-primary" id="risk-submit">Save changes</button>
            </div>
        </div>
    </form>
</div>
@endsection
