<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Enums\NotificationTypeEnum;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated user
     */
    public function getNotifications(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $perPage = $request->get('per_page', 15);
        $search = $request->get('search');
        $category = $request->get('category');
        $isRead = $request->get('is_read');

        $query = Notification::where('user_id', $user->id)
            ->with(['user']);

        // Apply search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Apply category filter
        if ($category) {
            $query->where('category', $category);
        }

        // Apply read status filter
        if ($isRead !== null) {
            $query->where('is_read', (bool) $isRead);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        $notifications = $query->paginate($perPage);

        return response()->json([
            'data' => $notifications->items(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'per_page' => $notifications->perPage(),
                'total' => $notifications->total(),
                'last_page' => $notifications->lastPage(),
                'from' => $notifications->firstItem(),
                'to' => $notifications->lastItem(),
            ],
            'unread_count' => Notification::where('user_id', $user->id)
                ->where('is_read', false)
                ->count()
        ], 200);
    }

    /**
     * Get specific notification
     */
    public function getNotification(Request $request, $id)
    {
        $user = auth('sanctum')->user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->with(['user'])
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found.'
            ], 404);
        }

        return response()->json([
            'data' => [
                'id' => $notification->id,
                'user_id' => $notification->user_id,
                'title' => $notification->title,
                'message' => $notification->message,
                'category' => $notification->category->value,
                'is_read' => $notification->is_read,
                'sent_at' => $notification->sent_at?->format('Y-m-d H:i:s'),
                'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                'updated_at' => $notification->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead(Request $request, $id)
    {
        $user = auth('sanctum')->user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found.'
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notification marked as read.',
            'data' => [
                'id' => $notification->id,
                'is_read' => $notification->is_read,
                'updated_at' => $notification->updated_at->format('Y-m-d H:i:s'),
            ]
        ], 200);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsRead(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $updatedCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => "Marked {$updatedCount} notifications as read.",
            'updated_count' => $updatedCount
        ], 200);
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $totalNotifications = Notification::where('user_id', $user->id)->count();
        $unreadNotifications = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();
        $readNotifications = $totalNotifications - $unreadNotifications;

        // Get count by category
        $categoryStats = Notification::where('user_id', $user->id)
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->category->value => $item->count];
            });

        return response()->json([
            'data' => [
                'total' => $totalNotifications,
                'unread' => $unreadNotifications,
                'read' => $readNotifications,
                'by_category' => $categoryStats,
            ]
        ], 200);
    }

    /**
     * Delete notification
     */
    public function deleteNotification(Request $request, $id)
    {
        $user = auth('sanctum')->user();
        
        $notification = Notification::where('user_id', $user->id)
            ->where('id', $id)
            ->first();

        if (!$notification) {
            return response()->json([
                'message' => 'Notification not found.'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notification deleted successfully.'
        ], 200);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllReadNotifications(Request $request)
    {
        $user = auth('sanctum')->user();
        
        $deletedCount = Notification::where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'message' => "Deleted {$deletedCount} read notifications.",
            'deleted_count' => $deletedCount
        ], 200);
    }
}