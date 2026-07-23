@extends('layouts.sponsor-app')

@section('title', 'Line Up')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                <span>📋</span> {{ __('Line Up') }}
            </h2>
            <p class="text-gray-500 text-sm mt-1">{{ __('Browse available applicants and select candidates') }}</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('sponsor.lineup.export') }}" class="px-3 py-1.5 bg-gradient-to-r from-teal-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:from-teal-600 hover:to-blue-700 transition-all shadow-sm">
                📥 {{ __('Export Excel') }}
            </a>
        </div>
    </div>

    {{-- Position filter pills --}}
    @if(isset($positions) && $positions->count() > 0)
    <div class="flex flex-wrap gap-2 mb-4">
        <a href="{{ route('sponsor.dashboard') }}"
           class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                  {{ !request('position') ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
            {{ __('All') }}
        </a>
        @foreach($positions as $pos)
            @php $count = $lineupApplicants->where('position_id', $pos->id)->count(); @endphp
            <a href="{{ route('sponsor.dashboard', ['position' => $pos->name]) }}"
               class="px-3 py-1.5 rounded-full text-xs font-semibold transition-colors
                      {{ request('position') === $pos->name ? 'bg-teal-500 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $pos->name }}
                <span class="opacity-70">({{ $count }})</span>
            </a>
        @endforeach
    </div>
    @endif

    @if($lineupApplicants->count())
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
                    <th class="text-left px-4 py-3 font-semibold text-gray-600">{{ __('Date') }}</th>
                    <th class="text-center px-4 py-3 font-semibold text-gray-600">{{ __('Action') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lineupApplicants as $app)
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
                            @else bg-gray-100 text-gray-600
                            @endif">
                            {{ $app->statusCode?->name ?? __('Pending') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-gray-500 text-xs">
                        {{ $app->created_at ? $app->created_at->format('Y-m-d') : '—' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('sponsor.select') }}" class="inline">
                            @csrf
                            <input type="hidden" name="applicant_id" value="{{ $app->id }}">
                            <button type="submit"
                                class="px-3 py-1.5 bg-gradient-to-r from-teal-500 to-blue-600 text-white text-xs font-semibold rounded-lg hover:from-teal-600 hover:to-blue-700 transition-all shadow-sm">
                                ✓ {{ __('Select') }}
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
        <div class="text-6xl mb-4 opacity-40">📋</div>
        <h3 class="text-lg font-semibold text-gray-600 mb-1">{{ __('No applicants in Line Up') }}</h3>
        <p class="text-sm text-gray-400">{{ __('Check back later for available candidates.') }}</p>
    </div>
    @endif
</div>
@endsection
