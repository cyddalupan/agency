@extends('layouts.app')

@section('title', 'Expense Request ' . $request->reference_no)

@section('content')
<div class="max-w-5xl mx-auto">

    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg mb-6 card-lift">
        <div class="card-body p-6 flex flex-row items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">🧾 Expense Request {{ $request->reference_no }}</h1>
                <p class="opacity-80 mt-1">
                    {{ $request->date?->format('Y-m-d') }} •
                    <span class="badge badge-sm {{ $request->status === 'received' ? 'badge-warning' : 'badge-ghost' }}">{{ $request->status }}</span>
                    • {{ $request->branch?->name ?? 'All branches' }} • by {{ $request->user?->name ?? $request->user?->username ?? '—' }}
                </p>
            </div>
            <a href="{{ route('expense_request.index') }}" class="btn btn-ghost btn-sm text-primary-content">← Back</a>
        </div>
    </div>

    @if(in_array(auth()->user()->user_type ?? '', ['super_admin', 'admin', 'billing']))
        <div class="tabs tabs-boxed bg-base-200/60 mb-6 w-fit">
            <a href="{{ route('receivable.index') }}" class="tab text-sm">Tab 1 · Receivable</a>
            <a href="{{ route('expense_request.index') }}" class="tab text-sm">Tab 2 · Expenses &amp; Payments</a>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success shadow-md mb-4"><span>✅ {{ session('success') }}</span></div>
    @endif

    {{-- Line items --}}
    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="font-bold mb-3">Line Items</h3>
            <div class="overflow-x-auto">
                <table class="table table-sm">
                    <thead>
                        <tr class="bg-base-200/70">
                            <th>Charge</th>
                            <th>Applicant</th>
                            <th>Agent</th>
                            <th>Currency</th>
                            <th class="text-right">Amount</th>
                            <th>Account</th>
                            <th>Country</th>
                            <th>Particular</th>
                            <th>Attachment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($request->items as $item)
                            <tr>
                                <td><span class="badge badge-sm {{ $item->charge === 'office' ? 'badge-ghost' : 'badge-info' }}">{{ $item->charge }}</span></td>
                                <td>{{ $item->applicant ? $item->applicant->last_name . ', ' . $item->applicant->first_name : '—' }}</td>
                                <td>{{ $item->agent?->name ?? '—' }}</td>
                                <td>{{ $item->currency }}</td>
                                <td class="text-right font-semibold">{{ $item->currency === 'USD' ? '$' : '₱' }}{{ number_format((float) $item->amount, 2) }}</td>
                                <td>{{ $item->account?->name ?? '—' }}</td>
                                <td>{{ $item->country?->name ?? '—' }}</td>
                                <td>{{ $item->particular ?? '—' }}</td>
                                <td>
                                    @if($item->file_path)
                                        <a href="{{ \Illuminate\Support\Facades\Storage::url($item->file_path) }}" target="_blank" class="link link-primary">View file</a>
                                    @else
                                        <span class="opacity-50">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($request->notes)
                <p class="text-sm opacity-70 mt-3"><strong>Notes:</strong> {{ $request->notes }}</p>
            @endif
        </div>
    </div>

    {{-- Admin review: status change + upload --}}
    @if(in_array(auth()->user()->user_type, ['super_admin', 'admin']))
        <div class="card bg-base-100 shadow-md mb-6">
            <div class="card-body">
                <h3 class="font-bold mb-3">🛠 Review</h3>
                <form method="POST" action="{{ route('expense_request.status', $request) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-control">
                        <label class="label"><span class="label-text">Status</span></label>
                        <select name="status" class="select select-bordered select-sm">
                            <option value="pending" {{ $request->status === 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="received" {{ $request->status === 'received' ? 'selected' : '' }}>Received</option>
                        </select>
                    </div>
                    <div class="form-control flex-1 min-w-40">
                        <label class="label"><span class="label-text">Note</span></label>
                        <input type="text" name="note" class="input input-bordered input-sm" placeholder="e.g. Docs verified">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Update Status</button>
                </form>
            </div>
        </div>

        {{-- History --}}
        @if($request->histories->count())
            <div class="card bg-base-100 shadow-md mb-6">
                <div class="card-body">
                    <h3 class="font-bold mb-3">📜 History</h3>
                    <ul class="timeline timeline-vertical timeline-compact">
                        @foreach($request->histories as $h)
                            <li>
                                <div class="timeline-middle">●</div>
                                <div class="timeline-end">
                                    <p class="text-sm">
                                        <strong>{{ $h->from_status }}</strong> → <strong>{{ $h->to_status }}</strong>
                                        <span class="opacity-60">{{ $h->created_at?->format('M d, H:i') }}</span>
                                    </p>
                                    <p class="text-xs opacity-70">{{ $h->actor?->name ?? $h->actor?->username ?? '—' }}
                                        @if($h->note) — "{{ $h->note }}" @endif
                                    </p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
