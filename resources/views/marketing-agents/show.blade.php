@extends('layouts.app')

@section('title', $marketingAgent->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="card bg-gradient-to-br from-teal-600 via-emerald-500/90 to-teal-600 text-white shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('marketing-agencies.marketing-agents.index', $marketingAgency) }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Agents
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🕵️</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">{{ $marketingAgent->name }}</h2>
                        <p class="opacity-80 text-sm mt-1">{{ $marketingAgency->name }}</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('marketing-agencies.marketing-agents.edit', [$marketingAgency, $marketingAgent]) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
                        ✏️ Edit
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2 mb-2">
                    <span>👤</span> Contact Information
                </h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">Email</td>
                                <td class="text-right">
                                    @if($marketingAgent->email)
                                        <a href="mailto:{{ $marketingAgent->email }}" class="link link-primary">
                                            {{ $marketingAgent->email }}
                                        </a>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Contact</td>
                                <td class="text-right font-mono">{{ $marketingAgent->contact ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Status</td>
                                <td class="text-right">
                                    @php
                                        $statusColors = [
                                            'active' => 'badge-success',
                                            'inactive' => 'badge-ghost',
                                            'suspended' => 'badge-error',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$marketingAgent->status] ?? 'badge-ghost' }}">
                                        {{ ucfirst($marketingAgent->status) ?? '—' }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2 mb-2">
                    <span>🏢</span> Agency
                </h3>
                <div class="overflow-x-auto">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">Marketing Agency</td>
                                <td class="text-right">
                                    <a href="{{ route('marketing-agencies.show', $marketingAgency) }}" class="link link-primary">
                                        {{ $marketingAgency->name }}
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Joined</td>
                                <td class="text-right text-sm">
                                    {{ $marketingAgent->created_at->format('M d, Y') }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body flex-row items-center justify-between py-4">
            <span class="text-sm opacity-60">Created {{ $marketingAgent->created_at->format('M d, Y \a\t g:i A') }}</span>
            <form action="{{ route('marketing-agencies.marketing-agents.destroy', [$marketingAgency, $marketingAgent]) }}" method="POST"
                  onsubmit="return confirm('Delete this marketing agent?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-ghost btn-sm text-error">
                    🗑️ Delete
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
