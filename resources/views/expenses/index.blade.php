@extends('layouts.app')

@section('title', 'Expenses and Payments')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>💸</span> Expenses and Payments
            </h2>
            <p class="opacity-60 text-sm mt-1">Track money going out, classified by account</p>
        </div>
        <a href="{{ route('expenses.create') }}" class="btn btn-primary">
            <span>➕</span> Create Request
        </a>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('expenses.index') }}" class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs">Account</legend>
                    <select name="account_id" class="select w-full">
                        <option value="">All Accounts</option>
                        @foreach($accounts as $main)
                            <option value="{{ $main->id }}" @selected(request('account_id') == $main->id)>{{ $main->name }}</option>
                            @foreach($main->children as $sub)
                                <option value="{{ $sub->id }}" @selected(request('account_id') == $sub->id)>&nbsp;&nbsp;└ {{ $sub->name }}</option>
                            @endforeach
                        @endforeach
                    </select>
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs">Date From</legend>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="input w-full">
                </fieldset>
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs">Date To</legend>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="input w-full">
                </fieldset>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-neutral">Filter</button>
                    <a href="{{ route('expenses.index') }}" class="btn btn-ghost">Reset</a>
                </div>
            </div>
        </div>
    </form>

    {{-- Summary card --}}
    <div class="card bg-base-100 shadow-sm mb-6">
        <div class="card-body py-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs uppercase tracking-wider opacity-50 font-semibold">Total in filtered range</p>
                    <p class="text-2xl font-bold text-error mt-1">₱ {{ number_format($total, 2) }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs opacity-50">Recorded Expenses</p>
                    <p class="text-lg font-semibold">{{ $expenses->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    @if($expenses->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Date</th>
                            <th>Account</th>
                            <th>Payee</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-right">Amount</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expenses as $expense)
                        <tr>
                            <td class="whitespace-nowrap">{{ $expense->date->format('M d, Y') }}</td>
                            <td>
                                <span class="font-medium">{{ $expense->account->name }}</span>
                                @if($expense->account->parent)
                                    <span class="opacity-40">· {{ $expense->account->parent->name }}</span>
                                @endif
                            </td>
                            <td>{{ $expense->payee ?? '—' }}</td>
                            <td><span class="badge badge-sm badge-ghost">{{ ucwords(str_replace('_',' ',$expense->method)) }}</span></td>
                            <td class="opacity-70">{{ $expense->reference_no ?? '—' }}</td>
                            <td class="text-right font-semibold">₱ {{ number_format($expense->amount, 2) }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                    <form action="{{ route('expenses.destroy', $expense) }}" method="POST"
                                          onsubmit="return confirm('Delete this expense?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-ghost btn-xs btn-square text-error" title="Delete">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $expenses->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">💸</span>
                <h3 class="text-xl font-bold mb-2">No Expenses Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">Record agency spending (salaries, office bills, agent advances) to start your Expenses and Payments.</p>
                <a href="{{ route('expenses.create') }}" class="btn btn-primary">
                    <span>➕</span> Create Request
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
