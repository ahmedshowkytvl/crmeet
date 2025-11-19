<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserStatusController extends Controller
{
    /**
     * تحديث حالة المستخدم (عبر الإنترنت/خارج الإنترنت)
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'is_online' => 'required|boolean'
        ]);

        $userId = Auth::id();
        $isOnline = $request->is_online;

        try {
            // تحديث حالة المستخدم
            DB::select('SELECT update_user_online_status(?, ?)', [$userId, $isOnline]);

            return response()->json([
                'success' => true,
                'message' => $isOnline ? 'تم تحديث حالتك إلى عبر الإنترنت' : 'تم تحديث حالتك إلى خارج الإنترنت',
                'is_online' => $isOnline
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث الحالة'
            ], 500);
        }
    }

    /**
     * الحصول على حالة المستخدم
     */
    public function getStatus($userId)
    {
        try {
            $status = DB::selectOne('SELECT * FROM get_user_online_status(?)', [$userId]);
            
            return response()->json([
                'success' => true,
                'is_online' => $status->is_online ?? false,
                'last_seen' => $status->last_seen ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على الحالة'
            ], 500);
        }
    }

    /**
     * تحديث آخر نشاط للمستخدم
     */
    public function updateActivity()
    {
        try {
            $userId = Auth::id();
            
            // تحديث آخر نشاط
            DB::table('users')
                ->where('id', $userId)
                ->update(['last_activity' => now()]);

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء تحديث النشاط'
            ], 500);
        }
    }

    /**
     * الحصول على جميع المستخدمين عبر الإنترنت
     */
    public function getOnlineUsers()
    {
        try {
            $onlineUsers = DB::table('user_online_status')
                ->join('users', 'user_online_status.user_id', '=', 'users.id')
                ->where('user_online_status.is_online', true)
                ->select('users.id', 'users.name', 'users.name_ar', 'users.profile_picture', 'user_online_status.last_seen')
                ->get();

            return response()->json([
                'success' => true,
                'users' => $onlineUsers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء الحصول على المستخدمين عبر الإنترنت'
            ], 500);
        }
    }
}
