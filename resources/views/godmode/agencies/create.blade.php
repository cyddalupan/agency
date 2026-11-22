@extends('godmode.layouts.app')

@section('title', 'New agency')

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold">New agency</h1>
        <p class="text-sm opacity-70">Creates the tenant. Its owner accounts are a separate step.</p>
    </div>
    <a href="{{ route('godmode.agencies.index') }}" class="btn btn-sm btn-ghost">Back to agencies</a>
</div>

@include('godmode.agencies._form', ['agency' => $agency])
@endsection
