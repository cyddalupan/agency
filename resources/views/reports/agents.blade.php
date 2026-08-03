@extends('layouts.app')

@section('title', 'Agent Report')

@section('content')
<div class="container mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold mb-6">Agent Report</h1>

    {{-- Filters --}}
    <div class="card bg-base-100 shadow-xl mb-6 no-print">
        <div class="card-body">
            <h2 class="card-title text-lg mb-4">Filters</h2>
            <form method="GET" action="{{ route('reports.agents') }}" class="flex flex-wrap gap-4 items-end">
                <div class="form-control w-full sm:w-auto flex-1">
                    <label class="label"><span class="label-text">Search</span></label>
                    <input type="text" name="search" class="input input-bordered w-full" placeholder="Name or email..."
                           value="{{ request('search') }}" />
                </div>
                <div class="form-control w-full sm:w-auto">
                    <label class="label"><span class="label-text">Status</span></label>
                    <select name="status" class="select select-bordered w-full sm:w-36">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="form-control">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
                <div class="form-control">
                    <a href="{{ route('reports.agents') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Results --}}
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <div class="flex justify-between items-center mb-4">
                <h2 class="card-title">Results ({{ $agents->total() }})</h2>
                <div class="flex gap-2 no-print">
                    <a href="{{ route('reports.agents.export', request()->only(['search', 'status'])) }}"
                       class="btn btn-outline btn-sm gap-1">
                        <span>📥</span> Export CSV
                    </a>
                    <button onclick="window.print()" class="btn btn-outline btn-sm">🖨️ Print</button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Contact</th>
                            <th>Commission Rate</th>
                            <th>Agency</th>
                            <th>Status</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $agent)
                            <tr>
                                <td class="font-medium">{{ $agent->name }}</td>
                                <td>{{ $agent->email }}</td>
                                <td>{{ $agent->contact ?? '—' }}</td>
                                <td>{{ $agent->commission_rate ? $agent->commission_rate . '%' : '—' }}</td>
                                <td>{{ $agent->agency?->name ?? '—' }}</td>
                                <td>
                                    @if ($agent->status === 'active')
                                        <span class="badge badge-success badge-sm">Active</span>
                                    @else
                                        <span class="badge badge-error badge-sm">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $agent->created_at->format('Y-m-d') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-base-content/50 py-8">
                                    No agents found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $agents->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
