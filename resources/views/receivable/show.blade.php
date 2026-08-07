@extends('layouts.app')

@section('title', 'Receivable ' . $receivable->code)

@section('content')
<div class="max-w-4xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6 flex flex-row items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold font-mono">🧾 {{ $receivable->code }}</h1>
                <p class="opacity-80 mt-1">Receivable Review — {{ $receivable->date->format('M d, Y') }}</p>
            </div>
            <span class="badge {{ $receivable->status === 'received' ? 'badge-success' : 'badge-warning' }} badge-lg">
                {{ strtoupper($receivable->status) }}
            </span>
        </div>
    </div>

    {{-- Details --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="font-bold mb-3">Transaction Details</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div><span class="opacity-60">Agent:</span> <strong>{{ $receivable->agent->name ?? '—' }}</strong></div>
                <div><span class="opacity-60">Applicant:</span> {{ $receivable->applicant ? $receivable->applicant->first_name . ' ' . $receivable->applicant->last_name : '—' }}</div>
                <div><span class="opacity-60">Encoded by:</span> {{ $receivable->encoder->name ?? '—' }}</div>
                <div><span class="opacity-60">Ref# / AR#:</span> {{ $receivable->ref_ar ?? '—' }}</div>
                <div><span class="opacity-60">Amount:</span> <strong class="text-primary text-lg">₱{{ number_format($receivable->amount, 2) }}</strong></div>
                <div><span class="opacity-60">Account:</span> {{ $receivable->account ?? '—' }}</div>
                <div><span class="opacity-60">Deposit / Debit Acct:</span> {{ $receivable->debit_account ?? '—' }}</div>
                <div><span class="opacity-60">Type:</span> {{ $receivable->type ?? '—' }}</div>
                <div><span class="opacity-60">Mode:</span> {{ $receivable->mode ?? '—' }}</div>
                <div class="sm:col-span-2"><span class="opacity-60">Particular:</span> {{ $receivable->particular ?? '—' }}</div>
            </div>
        </div>
    </div>

    {{-- Admin status change --}}
    @if($canChangeStatus)
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="font-bold mb-1">Change Status</h3>
            <p class="text-xs opacity-60 mb-3">Only admin can change the status (pending ⇄ received). Recorded to history.</p>
            <form method="POST" action="{{ route('receivable.status', $receivable->id) }}" class="flex flex-wrap gap-3 items-end">
                @csrf
                @method('PATCH')
                <div class="form-control">
                    <label class="label"><span class="label-text">New Status</span></label>
                    <select name="status" class="select select-bordered select-sm">
                        @foreach($statuses as $s)
                            <option value="{{ $s }}" {{ $receivable->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-control flex-1 min-w-[12rem]">
                    <label class="label"><span class="label-text">Note</span></label>
                    <input type="text" name="note" class="input input-bordered input-sm" placeholder="Optional note">
                </div>
                <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
            </form>
        </div>
    </div>
    @endif

    {{-- History --}}
    <div class="card bg-base-100 shadow-md">
        <div class="card-body">
            <h3 class="font-bold mb-3">🕘 History</h3>
            @if($history->count())
                <div class="overflow-x-auto">
                    <table class="table table-sm">
                        <thead><tr class="bg-base-200/70"><th>Status change</th><th>Actor</th><th>Note</th><th>When</th></tr></thead>
                        <tbody>
                            @foreach($history as $h)
                            <tr>
                                <td>
                                    <span class="badge badge-ghost badge-sm">{{ $h->from_status ?? '—' }}</span>
                                    → <span class="badge badge-primary badge-sm">{{ $h->to_status }}</span>
                                </td>
                                <td>{{ $h->actor->name ?? '—' }}</td>
                                <td>{{ $h->note ?? '—' }}</td>
                                <td>{{ $h->created_at->format('M d, Y H:i') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="opacity-50 text-sm">No status changes recorded yet.</p>
            @endif
        </div>
    </div>

    <div class="mt-6">
        <a href="{{ route('receivable.index') }}" class="btn btn-ghost btn-sm">← Back to Receivable list</a>
    </div>

</div>
@endsection
