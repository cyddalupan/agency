@extends('layouts.app')

@section('title', 'Notifications')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold">Notifications</h1>
        <div class="flex gap-2">
            @if ($notifications->whereNull('read_at')->count() > 0)
            <form method="POST" action="{{ route('notifications.mark-all-as-read') }}" class="inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline btn-primary">
                    Mark all as read
                </button>
            </form>
            @endif
        </div>
    </div>

    @if ($notifications->count() === 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-12">
            <div class="text-5xl mb-4 opacity-30">🔔</div>
            <p class="text-lg opacity-70">No notifications</p>
            <p class="text-sm opacity-50 mt-1">You're all caught up!</p>
        </div>
    </div>
    @else
    <div class="space-y-2">
        @foreach ($notifications as $notification)
        <div class="card bg-base-100 shadow-sm border {{ $notification->read_at ? 'border-base-200' : 'border-primary/20 bg-primary/5' }}">
            <div class="card-body p-4">
                <div class="flex items-start gap-3">
                    {{-- Icon based on type --}}
                    <span class="text-xl mt-0.5">
                        @switch($notification->type)
                            @case('status_change')
                                🔄
                                @break
                            @case('approval')
                                ✅
                                @break
                            @case('bill_due')
                                💰
                                @break
                            @case('message')
                                💬
                                @break
                            @default
                                🔔
                        @endswitch
                    </span>

                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="text-sm {{ $notification->read_at ? 'opacity-70' : 'font-semibold' }}">
                                    {{ $notification->data['message'] ?? 'No message' }}
                                </p>
                                @if (isset($notification->data['link']))
                                <a href="{{ $notification->data['link'] }}" class="text-xs text-primary hover:underline mt-1 inline-block">
                                    View details →
                                </a>
                                @endif
                            </div>

                            <div class="flex items-center gap-2 shrink-0">
                                <span class="text-xs opacity-50">{{ $notification->created_at->diffForHumans() }}</span>

                                @if (!$notification->read_at)
                                <form method="POST" action="{{ route('notifications.mark-as-read', $notification) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="btn btn-ghost btn-xs text-primary" title="Mark as read">
                                        ✓
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mt-1">
                            @if ($notification->read_at)
                            <span class="badge badge-ghost badge-xs">read</span>
                            @else
                            <span class="badge badge-primary badge-xs">unread</span>
                            @endif
                            <span class="text-xs opacity-50">{{ $notification->type }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection
