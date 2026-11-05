@extends('layouts.app')

@section('title', 'General Ledger')

@section('content')
<div class="p-4 sm:p-6 space-y-6">

    {{-- Header --}}
    <div class="card bg-gradient-to-br from-primary via-primary/80 to-secondary text-primary-content shadow-lg card-lift">
        <div class="card-body py-5 px-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <span class="text-3xl">📚</span>
                <div>
                    <h1 class="text-2xl font-extrabold">General Ledger</h1>
                    <p class="opacity-70 text-sm mt-0.5">Transactions grouped by main account and sub account</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="badge badge-lg badge-primary gap-2 py-4 border-0">
                    <span class="font-bold">{{ number_format($grandCount) }}</span> transactions
                </span>
                <a href="{{ route('general_ledger.export') }}" class="btn btn-sm btn-secondary shadow-md">⬇️ Export CSV</a>
            </div>
        </div>
    </div>

    @forelse ($groups as $group)
        <div class="card bg-base-100 shadow-md">
            <div class="card-body p-0">
                <div class="overflow-x-auto">
                    {{-- Format follows the client's reference sheet
                         (Kchanz Josh, 2026-11-05): Main Account / Type are
                         repeated on every row and every header is aligned
                         exactly like its column, so the columns line up
                         row-to-row and table-to-table. --}}
                    <table class="table table-sm gl-table w-full">
                        <colgroup>
                            <col style="width:24%"><col style="width:12%"><col style="width:38%">
                            <col style="width:16%"><col style="width:10%">
                        </colgroup>
                        <thead>
                            <tr class="bg-base-200/70">
                                <th class="text-left align-middle">Main Account</th>
                                <th class="text-left align-middle">Type</th>
                                <th class="text-left align-middle">Sub Accounts</th>
                                <th class="text-right align-middle">Amount</th>
                                <th class="text-center align-middle">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($group['subs'] as $sub)
                                <tr class="hover">
                                    <td class="text-left align-middle font-medium whitespace-normal break-words">{{ $group['main']->name }}</td>
                                    <td class="text-left align-middle capitalize whitespace-normal break-words">{{ $group['main']->type }}</td>
                                    <td class="text-left align-middle whitespace-normal break-words">{{ $sub['account']->name }}</td>
                                    <td class="text-right align-middle font-mono whitespace-nowrap">{{ number_format($sub['amount'], 2) }}</td>
                                    <td class="text-center align-middle">
                                        <a href="{{ route('general_ledger.review', $sub['account']) }}"
                                           class="btn btn-xs btn-outline btn-primary whitespace-nowrap">Review</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-base-200/60 font-semibold">
                                <td colspan="3" class="text-right align-middle">Total</td>
                                <td class="text-right align-middle font-mono whitespace-nowrap">{{ number_format($group['total'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="card bg-base-100 shadow-md">
            <div class="card-body items-center text-center py-12">
                <span class="text-4xl">📭</span>
                <p class="font-semibold mt-2">No accounts yet</p>
                <p class="text-sm opacity-60">Create main and sub accounts in the Accounts module first.</p>
            </div>
        </div>
    @endforelse

    @if ($groups->isNotEmpty())
        <div class="card bg-base-100 shadow-md">
            <div class="card-body py-4 px-6 flex flex-row items-center justify-between">
                <span class="text-sm uppercase tracking-wider font-semibold opacity-60">Total Amount</span>
                <span class="text-2xl font-extrabold font-mono">₱{{ number_format($grandTotal, 2) }}</span>
            </div>
        </div>
    @endif
</div>
@endsection
