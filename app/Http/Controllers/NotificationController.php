<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $query = auth()->user()->notifications();

        if ($request->filled('filter')) {
            $filter = $request->input('filter');
            $query->where('type', 'like', "%{$filter}%");
        }

        $notifications = $query->orderByDesc('created_at')->paginate(25);

        return view('notifications.index', compact('notifications'));
    }

    public function markRead(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return back()->with('status', 'Notification marked as read.');
    }

    public function markAllRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'All notifications marked as read.');
    }
}
