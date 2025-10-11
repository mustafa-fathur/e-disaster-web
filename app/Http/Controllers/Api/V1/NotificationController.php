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
     * @OA\Get(
     *     path="/notifications",
     *     summary="Get user notifications",
     *     description="Get paginated list of notifications for authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         required=false,
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search term for title or message",
     *         required=false,
     *         @OA\Schema(type="string", example="disaster")
     *     ),
     *     @OA\Parameter(
     *         name="category",
     *         in="query",
     *         description="Filter by notification category",
     *         required=false,
     *         @OA\Schema(type="string", enum={"volunteer_verification","new_disaster","new_disaster_report","new_disaster_victim_report","new_disaster_aid_report","disaster_status_changed"})
     *     ),
     *     @OA\Parameter(
     *         name="is_read",
     *         in="query",
     *         description="Filter by read status",
     *         required=false,
     *         @OA\Schema(type="boolean", example=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notifications retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *             @OA\Property(property="pagination", type="object")
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/notifications/{id}",
     *     summary="Get notification details",
     *     description="Get detailed information about a specific notification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="user_id", type="string"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="category", type="string"),
     *                 @OA\Property(property="is_read", type="boolean"),
     *                 @OA\Property(property="sent_at", type="string", format="date-time"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification not found.")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/notifications/{id}/read",
     *     summary="Mark notification as read",
     *     description="Mark a specific notification as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification marked as read."),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="is_read", type="boolean"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification not found.")
     *         )
     *     )
     * )
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
     * @OA\Put(
     *     path="/notifications/read-all",
     *     summary="Mark all notifications as read",
     *     description="Mark all user notifications as read",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="All notifications marked as read",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Marked 5 notifications as read."),
     *             @OA\Property(property="updated_count", type="integer", example=5)
     *         )
     *     )
     * )
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
     * @OA\Get(
     *     path="/notifications/stats",
     *     summary="Get notification statistics",
     *     description="Get notification statistics for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Notification statistics retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="total", type="integer", example=25),
     *                 @OA\Property(property="unread", type="integer", example=5),
     *                 @OA\Property(property="read", type="integer", example=20),
     *                 @OA\Property(property="by_category", type="object",
     *                     @OA\Property(property="volunteer_verification", type="integer", example=3),
     *                     @OA\Property(property="new_disaster", type="integer", example=8),
     *                     @OA\Property(property="new_disaster_report", type="integer", example=12),
     *                     @OA\Property(property="disaster_status_changed", type="integer", example=2)
     *                 )
     *             )
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/notifications/{id}",
     *     summary="Delete notification",
     *     description="Delete a specific notification",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Notification ID",
     *         required=true,
     *         @OA\Schema(type="string", example="0199cfbc-eab1-7262-936e-72f9a6c5f659")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notification deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification deleted successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Notification not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Notification not found.")
     *         )
     *     )
     * )
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
     * @OA\Delete(
     *     path="/notifications/read-all",
     *     summary="Delete all read notifications",
     *     description="Delete all read notifications for the authenticated user",
     *     tags={"Notifications"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Read notifications deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Deleted 15 read notifications."),
     *             @OA\Property(property="deleted_count", type="integer", example=15)
     *         )
     *     )
     * )
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