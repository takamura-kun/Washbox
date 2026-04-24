<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Http\Request;

class AdminNotificationController extends Controller
{
    /**
     * Get all admin notifications
     */
    public function index(Request $request)
    {
        $query = AdminNotification::query()
            ->orderBy('created_at', 'desc');

        // Filter by unread only
        if ($request->boolean('unread_only')) {
            $query->whereNull('read_at');
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $notifications = $query->paginate($request->get('per_page', 20));

        // Transform the data to include safe date formatting
        $notifications->getCollection()->transform(function ($notification) {
            return [
                'id' => $notification->id,
                'type' => $notification->type,
                'title' => $notification->title,
                'message' => $notification->message,
                'icon' => $notification->icon,
                'color' => $notification->color,
                'link' => $notification->link,
                'data' => $notification->data,
                'branch_id' => $notification->branch_id,
                'user_id' => $notification->user_id,
                'is_read' => $notification->read_at !== null,
                'read_at' => $notification->read_at ? $notification->read_at->toISOString() : null,
                'created_at' => $notification->created_at ? $notification->created_at->toISOString() : null,
                'updated_at' => $notification->updated_at ? $notification->updated_at->toISOString() : null,
                'time_ago' => $notification->created_at ? $notification->created_at->diffForHumans() : 'Just now',
                'formatted_date' => $notification->created_at ? $notification->created_at->format('M d, Y h:i A') : 'Date unavailable',
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount()
    {
        $count = AdminNotification::whereNull('read_at')->count();

        return response()->json([
            'success' => true,
            'count' => $count,
        ]);
    }

    /**
     * Mark single notification as read
     */
    public function markAsRead($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read',
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        AdminNotification::whereNull('read_at')->update([
            'read_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read',
        ]);
    }

    /**
     * Delete a notification
     */
    public function delete($id)
    {
        $notification = AdminNotification::findOrFail($id);
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted',
        ]);
    }

    /**
     * Clear all read notifications
     */
    public function clearRead()
    {
        $deleted = AdminNotification::whereNotNull('read_at')->delete();

        return response()->json([
            'success' => true,
            'message' => "Cleared {$deleted} read notifications",
            'deleted_count' => $deleted,
        ]);
    }
}
