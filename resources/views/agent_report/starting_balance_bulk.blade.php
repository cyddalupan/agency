@extends('layouts.app')

@section('title', 'Bulk Upload Starting Balance')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <a href="{{ route('agent_report.index', ['tab' => 'starting-balances']) }}" class="link link-secondary text-sm flex items-center gap-1">
            <span>←</span> Back to Starting Balance
        </a>
    </div>

    <div class="card bg-gradient-to-br from-primary/10 to-secondary/10 border border-primary/20 mb-6 p-4">
        <h2 class="text-2xl font-bold flex items-center gap-2">
            <span>📥</span> Bulk Upload Starting Balance
        </h2>
        <p class="opacity-60 text-sm mt-1">
            Download the template, fill it in (one row = one agent's starting balance), then upload the CSV.
            Nothing is saved unless every row is valid.
        </p>
    </div>

    @if($errors->any())
        <div role="alert" class="alert alert-error mb-6 shadow-sm items-start">
            <span>⛔</span>
            <div class="w-full">
                <ul class="list-disc pl-4">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                @if(session('bulk_errors'))
                    <div class="overflow-x-auto mt-3">
                        <table class="table table-sm table-zebra w-full text-sm">
                            <thead>
                                <tr><th>CSV Line</th><th>Problems</th></tr>
                            </thead>
                            <tbody>
                                @foreach(session('bulk_errors') as $line => $problems)
                                    <tr>
                                        <td class="font-mono align-top">Line {{ $line }}</td>
                                        <td>
                                            <ul class="list-disc pl-4 space-y-0.5">
                                                @foreach($problems as $msg)<li>{{ $msg }}</li>@endforeach
                                            </ul>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <div class="card bg-base-100 shadow-md mb-6">
        <div class="card-body">
            <h3 class="font-bold mb-2">1 · Get the template</h3>
            <p class="text-sm opacity-70 mb-3">
                Columns: <strong>Date</strong>, <strong>Account</strong>, <strong>{{ agent_term() }}</strong>, <strong>Amount</strong>, <strong>Particular</strong>.
                Date blank = today. Account is fixed to <strong>Starting Balance</strong> (leave it blank or type that).
                Column order does not matter.
            </p>
            <a href="{{ route('agent_report.starting_balance.bulk.template') }}" class="btn btn-outline btn-sm">⬇️ Download CSV template</a>
        </div>
    </div>

    <form method="POST" action="{{ route('agent_report.starting_balance.bulk.import') }}" enctype="multipart/form-data" class="card bg-base-100 shadow-md">
        @csrf
        <div class="card-body">
            <h3 class="font-bold mb-2">2 · Upload your filled-in CSV</h3>
            <input type="file" name="csv_file" accept=".csv,text/csv" class="file-input file-input-bordered w-full" required>
            <p class="text-xs opacity-60 mt-2">
                Agent names must match an agent in your agency exactly. Each agent can only have one starting
                balance — an agent that already has one, or that appears twice in the same file, is reported as an error.
            </p>
            <div class="card-actions justify-end mt-3">
                <button type="submit" class="btn btn-primary">Import starting balances</button>
            </div>
        </div>
    </form>
</div>
@endsection
