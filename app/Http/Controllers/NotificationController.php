<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Notification::orderBy('created_at', 'desc')->paginate(20);
        
        if (request()->expectsJson()) {
            return response()->json($notifications);
        }
        
        return view('notifications.index', compact('notifications'));
    }

    public function unread()
    {
        $unreadCount = Notification::unread()->count();
        $unreadNotifications = Notification::unread()->orderBy('created_at', 'desc')->take(5)->get();
        
        return response()->json([
            'count' => $unreadCount,
            'notifications' => $unreadNotifications
        ]);
    }

    public function markAsRead($id)
    {
        $notification = Notification::findOrFail($id);
        $notification->markAsRead();
        
        return response()->json(['success' => true]);
    }

    public function markAllAsRead()
    {
        Notification::unread()->update([
            'is_read' => true,
            'read_at' => now()
        ]);
        
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Notification::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}
