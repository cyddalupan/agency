<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /**
     * Show the full notification center page.
     */
    public function index(): View
    {
        $notifications = Notification::forUser(auth()->id())
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    /**
     * Return JSON with unread notification count for AJAX badge updates.
     */
    public function unreadCount(): JsonResponse
    {
        $count = Notification::forUser(auth()->id())
            ->unread()
            ->count();

        return response()->json(['count' => $count]);
    }

    /**
     * Mark a single notification as read.
     */
    public function markAsRead(Notification $notification): RedirectResponse|JsonResponse
    {
        if ($notification->user_id !== auth()->id()) {
            if (request()->expectsJson() || request()->input('Accept') === 'application/json') {
                return response()->json(['success' => false], 403);
            }
            abort(403);
        }

        $notification->markAsRead();

        if (request()->expectsJson() || request()->input('Accept') === 'application/json') {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }

    /**
     * Mark all of the current user's notifications as read.
     */
    public function markAllAsRead(): RedirectResponse|JsonResponse
    {
        Notification::forUser(auth()->id())
            ->unread()
            ->update(['read_at' => now()]);

        if (request()->expectsJson() || request()->input('Accept') === 'application/json') {
            return response()->json(['success' => true]);
        }

        return redirect()->back();
    }
}
