<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Notification;

class NotificationApiController extends Controller
{
    /**
     * Get user's notifications
     */
    public function index()
    {
        $user = Auth::user();

        $notifications = $user->notifications()
            ->recent(20)
            ->get();

        $unreadCount = $user->unreadNotificationsCount();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications,
                'unreadCount' => $unreadCount
            ]
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request)
    {
        $request->validate([
            'notification_id' => 'required|exists:notifications,id'
        ]);

        $notification = Auth::user()->notifications()->find($request->notification_id);
        
        if (!$notification) {
            return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
            'data' => [
                'unreadCount' => Auth::user()->unreadNotificationsCount()
            ]
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()->unread()->update([
            'read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
            'data' => [
                'unreadCount' => 0
            ]
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadCount()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'unreadCount' => Auth::user()->unreadNotificationsCount()
            ]
        ]);
    }
}
