<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * الحصول على إشعارات المستخدم
     * 
     * GET /api/notifications
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $validator = Validator::make($request->all(), [
            'limit' => 'integer|min:1|max:100',
            'offset' => 'integer|min:0',
            'type' => 'in:message,task,asset,birthday',
            'unread_only' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);
        $type = $request->input('type');
        $unreadOnly = $request->boolean('unread_only');

        $query = Notification::with('actor')
            ->forUser($user->id)
            ->orderBy('created_at', 'desc');

        if ($type) {
            $query->ofType($type);
        }

        if ($unreadOnly) {
            $query->unread();
        }

        $total = $query->count();
        $notifications = $query->skip($offset)->take($limit)->get();
        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => $unreadCount,
            'data' => [
                'notifications' => $notifications,
                'unread_count' => $unreadCount,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total,
                ]
            ]
        ]);
    }

    /**
     * الحصول على عدد الإشعارات غير المقروءة
     * 
     * GET /api/notifications/count
     */
    public function count(): JsonResponse
    {
        $user = Auth::user();
        
        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'data' => [
                'unread_count' => $unreadCount
            ]
        ]);
    }

    /**
     * إنشاء إشعار جديد
     * 
     * POST /api/notifications
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:message,task,asset',
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'actor_id' => 'nullable|exists:users,id',
            'resource_type' => 'nullable|string|max:100',
            'resource_id' => 'nullable|integer',
            'link' => 'nullable|string|max:500',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = Notification::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'title' => $request->title,
            'body' => $request->body,
            'actor_id' => $request->actor_id ?? Auth::id(),
            'resource_type' => $request->resource_type,
            'resource_id' => $request->resource_id,
            'link' => $request->link,
            'metadata' => $request->metadata,
        ]);

        // تحميل العلاقات
        $notification->load('actor');

        // بث الإشعار عبر Broadcasting
        event(new \App\Events\NotificationCreated($notification));

        return response()->json([
            'success' => true,
            'data' => [
                'notification' => $notification
            ]
        ], 201);
    }

    /**
     * تحديد إشعار/إشعارات كمقروءة
     * 
     * POST /api/notifications/mark-read
     */
    public function markAsRead(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'notification_ids' => 'required_without:mark_all|array',
            'notification_ids.*' => 'integer',
            'mark_all' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->boolean('mark_all')) {
            // تحديد جميع الإشعارات كمقروءة
            $affectedRows = Notification::forUser($user->id)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        } else {
            // تحديد إشعارات محددة
            $notificationIds = $request->input('notification_ids', []);
            
            $affectedRows = Notification::forUser($user->id)
                ->whereIn('id', $notificationIds)
                ->unread()
                ->update([
                    'is_read' => true,
                    'read_at' => now(),
                ]);
        }

        // بث تحديث العدد
        $unreadCount = Notification::forUser($user->id)->unread()->count();
        event(new \App\Events\NotificationCountUpdated($user->id, $unreadCount));

        return response()->json([
            'success' => true,
            'data' => [
                'affected_rows' => $affectedRows,
                'unread_count' => $unreadCount,
            ]
        ]);
    }

    /**
     * حذف إشعار
     * 
     * DELETE /api/notifications/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();

        $notification = Notification::forUser($user->id)->find($id);

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'الإشعار غير موجود أو غير مصرح به'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف الإشعار بنجاح'
        ]);
    }

    /**
     * تحديث تفضيلات الإشعارات
     * 
     * POST /api/notifications/preferences
     */
    public function updatePreferences(Request $request): JsonResponse
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:message,task,asset',
            'enabled' => 'required|boolean',
            'sound_enabled' => 'boolean',
            'browser_enabled' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $preference = NotificationPreference::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $request->type,
            ],
            [
                'enabled' => $request->enabled,
                'sound_enabled' => $request->boolean('sound_enabled', true),
                'browser_enabled' => $request->boolean('browser_enabled', true),
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التفضيلات بنجاح',
            'data' => ['preference' => $preference]
        ]);
    }

    /**
     * الحصول على تفضيلات الإشعارات
     * 
     * GET /api/notifications/preferences
     */
    public function getPreferences(): JsonResponse
    {
        $user = Auth::user();
        
        $preferences = NotificationPreference::getAllForUser($user->id);

        return response()->json([
            'success' => true,
            'data' => ['preferences' => $preferences]
        ]);
    }
    
    /**
     * الحصول على عدد الإشعارات غير المقروءة
     * 
     * GET /api/notifications/unread-count
     */
    public function getUnreadCount(): JsonResponse
    {
        $user = Auth::user();
        
        $unreadCount = Notification::forUser($user->id)->unread()->count();

        return response()->json([
            'success' => true,
            'unread_count' => $unreadCount
        ]);
    }
    
    /**
     * حذف جميع الإشعارات
     * 
     * POST /api/notifications/clear-all
     */
    public function clearAll(): JsonResponse
    {
        $user = Auth::user();
        
        $deletedCount = Notification::forUser($user->id)->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف جميع الإشعارات بنجاح',
            'deleted_count' => $deletedCount
        ]);
    }
    
    /**
     * إنشاء إشعار جديد (للاستخدام العام)
     * 
     * POST /api/notifications/create
     */
    public function create(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'string|in:info,success,warning,error',
            'user_id' => 'nullable|exists:users,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $notification = Notification::create([
            'user_id' => $request->user_id ?? Auth::id(),
            'type' => $request->type ?? 'message',
            'title' => $request->title,
            'body' => $request->message,
            'actor_id' => Auth::id(),
        ]);

        return response()->json([
            'success' => true,
            'data' => ['notification' => $notification]
        ], 201);
    }
}

