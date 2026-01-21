<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Get user's notifications
     */
    public function getNotifications()
    {
        if (!Auth::check()) {
            return response()->json(['notifications' => [], 'unreadCount' => 0]);
        }

        $notifications = Auth::user()->notifications()
            ->recent(10)
            ->get();

        $unreadCount = Auth::user()->unreadNotificationsCount();

        return response()->json([
            'notifications' => $notifications,
            'unreadCount' => $unreadCount
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated']);
        }

        $request->validate([
            'notification_id' => 'required|exists:notifications,id'
        ]);

        $notification = Auth::user()->notifications()->find($request->notification_id);
        
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found']);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'unreadCount' => Auth::user()->unreadNotificationsCount()
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        if (!Auth::check()) {
            return response()->json(['success' => false, 'message' => 'User not authenticated']);
        }

        Auth::user()->notifications()->unread()->update([
            'read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'unreadCount' => 0
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        if (!Auth::check()) {
            return response()->json(['unreadCount' => 0]);
        }

        return response()->json([
            'unreadCount' => Auth::user()->unreadNotificationsCount()
        ]);
    }
}
