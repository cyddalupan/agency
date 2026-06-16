@extends('layouts.app')

@section('title', 'Commission #' . $commission->id)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="card bg-gradient-to-br from-purple-600 via-purple-500/90 to-violet-600 text-white shadow-lg mb-6 card-lift">
        <div class="card-body p-6">
            <a href="{{ route('commissions.index') }}" class="text-sm opacity-80 hover:opacity-100 flex items-center gap-1 mb-2">
                <span>←</span> Back to Commissions
            </a>
            <div class="flex items-start justify-between">
                <div class="flex items-center gap-4">
                    <span class="text-5xl">🤝</span>
                    <div>
                        <h2 class="text-2xl lg:text-3xl font-bold">Commission #{{ $commission->id }}</h2>
                        <p class="opacity-80 text-sm mt-1">
                            ₱{{ number_format($commission->amount, 2) }}
                            @if($commission->employer)
                                · {{ $commission->employer->name }}
                            @endif
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('commissions.edit', $commission) }}" class="btn btn-ghost btn-sm text-white border border-white/30 hover:bg-white/20">
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
                <h3 class="card-title text-lg flex items-center gap-2">
                    <span>💰</span> Commission Details
                </h3>
                <div class="overflow-x-auto mt-2">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">Amount</td>
                                <td class="text-right font-mono font-bold text-lg">₱{{ number_format($commission->amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Paid Amount</td>
                                <td class="text-right font-mono">₱{{ number_format($commission->paid_amount, 2) }}</td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Balance</td>
                                <td class="text-right font-mono font-semibold {{ $commission->balance > 0 ? 'text-warning' : 'text-success' }}">
                                    ₱{{ number_format($commission->balance, 2) }}
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Status</td>
                                <td class="text-right">
                                    @php
                                        $statusColors = [
                                            'pending' => 'badge-warning',
                                            'partial' => 'badge-info',
                                            'paid' => 'badge-success',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$commission->status] ?? 'badge-ghost' }}">
                                        {{ ucfirst($commission->status) }}
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Due Date</td>
                                <td class="text-right">{{ $commission->due_date ? \Carbon\Carbon::parse($commission->due_date)->format('F d, Y') : '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body">
                <h3 class="card-title text-lg flex items-center gap-2">
                    <span>🏢</span> Related Parties
                </h3>
                <div class="overflow-x-auto mt-2">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td class="font-medium opacity-60">Employer</td>
                                <td class="text-right">
                                    @if($commission->employer)
                                        <a href="{{ route('employers.show', $commission->employer) }}" class="link link-primary">
                                            {{ $commission->employer->name }}
                                        </a>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="font-medium opacity-60">Source</td>
                                <td class="text-right">
                                    @if($commission->commissionable_type)
                                        <span class="badge badge-sm badge-outline">
                                            {{ ucfirst(str_replace('_', ' ', $commission->commissionable_type)) }}
                                        </span>
                                        <span class="text-sm ml-1">#{{ $commission->commissionable_id }}</span>
                                    @else
                                        <span class="opacity-40">—</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    @if($commission->notes)
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body">
            <h3 class="card-title text-lg flex items-center gap-2 mb-2">
                <span>📝</span> Notes
            </h3>
            <p class="opacity-70">{{ $commission->notes }}</p>
        </div>
    </div>
    @endif

    <div class="card bg-base-100 shadow-sm">
        <div class="card-body flex-row items-center justify-between py-4">
            <span class="text-sm opacity-60">Created {{ $commission->created_at->format('M d, Y \a\t g:i A') }}</span>
            <form action="{{ route('commissions.destroy', $commission) }}" method="POST"
                  onsubmit="return confirm('Delete this commission?')">
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
