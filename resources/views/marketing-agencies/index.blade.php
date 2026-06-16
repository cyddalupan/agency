@extends('layouts.app')

@section('title', 'Marketing Agencies')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>📢</span> Marketing Agencies
            </h2>
            <p class="opacity-60 text-sm mt-1">Manage third-party agencies that refer applicants and employers</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('marketing-agencies.create') }}" class="btn btn-primary">
                <span>➕</span> Add Agency
            </a>
        </div>
    </div>

    @if (session('success'))
        <div role="alert" class="alert alert-success mb-4 text-sm shadow-sm">
            <span>✅</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if($agencies->count())
        <div class="card bg-base-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr class="bg-base-200/80">
                            <th>Agency</th>
                            <th>👤 Contact</th>
                            <th>💰 Commission</th>
                            <th>📊 Status</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($agencies as $agency)
                        <tr class="hover transition-colors">
                            <td>
                                <div class="flex items-center gap-3">
                                    <span class="text-2xl">📢</span>
                                    <a href="{{ route('marketing-agencies.show', $agency) }}" class="link link-primary font-medium">
                                        {{ $agency->name }}
                                    </a>
                                </div>
                            </td>
                            <td class="text-sm">
                                @if($agency->contact_person)
                                    👤 {{ $agency->contact_person }}
                                @else
                                    <span class="opacity-40">—</span>
                                @endif
                            </td>
                            <td class="text-sm">
                                <span class="font-mono">{{ $agency->commission_rate }}%</span>
                            </td>
                            <td>
                                @if($agency->status === 'active')
                                    <span class="badge badge-sm badge-success">✅ Active</span>
                                @else
                                    <span class="badge badge-sm badge-ghost">⏸️ {{ ucfirst($agency->status) }}</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('marketing-agencies.show', $agency) }}" class="btn btn-ghost btn-xs btn-square" title="View">👁️</a>
                                    <a href="{{ route('marketing-agencies.edit', $agency) }}" class="btn btn-ghost btn-xs btn-square" title="Edit">✏️</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4">
            {{ $agencies->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm card-lift">
            <div class="card-body items-center text-center py-16">
                <span class="text-6xl mb-4">📢</span>
                <h3 class="text-xl font-bold mb-2">No Marketing Agencies Yet</h3>
                <p class="opacity-60 mb-6 max-w-md">
                    Marketing agencies refer applicants and employers in exchange for commission. Add one to start tracking referrals.
                </p>
                <div class="flex flex-wrap justify-center gap-3">
                    <a href="{{ route('marketing-agencies.create') }}" class="btn btn-primary btn-lg">
                        <span>➕</span> Add Your First Marketing Agency
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">📋</span>
                    <h4 class="font-semibold text-sm">Agency Details</h4>
                    <p class="text-xs opacity-60 mt-1">Name, contact, and commission rate</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">👥</span>
                    <h4 class="font-semibold text-sm">Marketing Agents</h4>
                    <p class="text-xs opacity-60 mt-1">Individual agents under each agency</p>
                </div>
            </div>
            <div class="card bg-base-100 shadow-sm">
                <div class="card-body text-center py-6">
                    <span class="text-3xl mb-2">💰</span>
                    <h4 class="font-semibold text-sm">Commission Tracking</h4>
                    <p class="text-xs opacity-60 mt-1">Rate-based commission per referral</p>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection
