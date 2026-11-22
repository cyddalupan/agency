@extends('godmode.layouts.app')

@section('title', 'Edit '.$agency->name)

@section('content')
<div class="flex items-center justify-between gap-4">
    <div>
        <h1 class="text-2xl font-extrabold">Edit {{ $agency->name }}</h1>
        <p class="text-sm opacity-70">{{ $fullHost ?: 'No subdomain — this agency cannot be reached.' }}</p>
    </div>
    <a href="{{ route('godmode.agencies.show', $agency) }}" class="btn btn-sm btn-ghost">Back to agency</a>
</div>

@include('godmode.agencies._form', ['agency' => $agency])
@endsection
