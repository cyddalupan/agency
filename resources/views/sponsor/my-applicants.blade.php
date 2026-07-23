@extends('layouts.sponsor-app')

@section('title', __('My Applicants'))

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>👥</span> {{ __('My Applicants') }}
            </h2>
            <p class="text-gray-500 text-sm mt-1">{{ __('Track the status of applicants you have selected') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sponsor.dashboard') }}" class="px-3 py-1.5 bg-gradient-to-r from-teal-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:from-teal-600 hover:to-blue-700 transition-all shadow-sm">
                📋 {{ __('Browse Line Up') }}
            </a>
        </div>
    </div>

    @if(isset($selectedApplicants) && $selectedApplicants->count())
    <div class="overflow-x-auto bg-white rounded-xl shadow-sm border border-gray-200">
        <table class="table w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100 bg-gray-50">
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Photo') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Name') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Position') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Passport') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Experience') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Status') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Requirements') }}</th>
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Selected Date') }}</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($selectedApplicants as $app)
                @php
                    $totalReqs = $app->requirements->count();
                    $completedReqs = $app->requirements->where('status', 'completed')->count();
                    $remainingReqs = $totalReqs - $completedReqs;
                @endphp
                <tr class="border-b border-gray-50 hover:bg-gray-50/50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-teal-100 to-blue-100 flex items-center justify-center text-sm font-bold text-teal-700">
                            {{ strtoupper(substr($app->first_name ?? $app->name ?? '?', 0, 1)) }}
                        </div>
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-800">
                        {{ $app->first_name ?? '' }} {{ $app->last_name ?? $app->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $app->position?->name ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $app->passport?->passport_no ?? '—' }}
                    </td>
                    <td class="px-4 py-3">
                        @if(($app->is_exabroad ?? false) && ($app->total_experience_years ?? 0) > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-100 text-amber-800 rounded-full text-xs font-medium">
                                🌍 {{ __('Ex-Abroad') }}
                                <span class="opacity-70">({{ $app->total_experience_years }}yr)</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full text-xs font-medium">
                                🆕 {{ __('Firstimer') }}
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if(in_array($app->status_code, [0,1,2,3])) bg-blue-100 text-blue-700
                            @elseif(in_array($app->status_code, [4,5,6])) bg-purple-100 text-purple-700
                            @elseif(in_array($app->status_code, [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,29,30,31,32,39,40,41,42])) bg-orange-100 text-orange-700
                            @elseif(in_array($app->status_code, [21,22])) bg-yellow-100 text-yellow-700
                            @elseif(in_array($app->status_code, [8,33,34])) bg-green-100 text-green-700
                            @elseif(in_array($app->status_code, [36,37,38])) bg-red-100 text-red-700
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $app->statusCode?->name ?? __('Pending') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($totalReqs > 0)
                            <div class="flex items-center gap-1">
                                <span class="text-xs font-medium
                                    @if($remainingReqs === 0) text-green-600
                                    @elseif($completedReqs > 0) text-amber-600
                                    @else text-gray-400
                                    @endif">
                                    @if($remainingReqs === 0)
                                        ✅ {{ __('Complete') }}
                                    @else
                                        {{ $completedReqs }}/{{ $totalReqs }}
                                        <span class="text-gray-400">{{ __('done') }}</span>
                                    @endif
                                </span>
                            </div>
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $app->pivot?->selected_at ? \Carbon\Carbon::parse($app->pivot->selected_at)->format('Y-m-d') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('sponsor.unselect') }}" class="inline" onsubmit="return confirm('Remove this applicant from your list?')">
                            @csrf
                            <input type="hidden" name="applicant_id" value="{{ $app->id }}">
                            <button type="submit"
                                class="px-3 py-1.5 bg-red-100 text-red-700 text-xs font-semibold rounded-lg hover:bg-red-200 transition-all">
                                ✕ {{ __('Remove') }}
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="text-center py-16 bg-white rounded-xl shadow-sm border border-gray-200">
        <div class="text-6xl mb-4 opacity-40">👥</div>
        <h3 class="text-lg font-semibold text-gray-600 mb-1">{{ __('No applicants selected yet') }}</h3>
        <p class="text-sm text-gray-400">{{ __('Browse the Line Up and select candidates to get started.') }}</p>
        <a href="{{ route('sponsor.dashboard') }}"
           class="inline-block mt-4 px-4 py-2 bg-gradient-to-r from-teal-500 to-blue-600 text-white text-sm font-semibold rounded-lg hover:from-teal-600 hover:to-blue-700 transition-all shadow-sm">
            📋 {{ __('Browse Line Up') }}
        </a>
    </div>
    @endif
</div>
@endsection
