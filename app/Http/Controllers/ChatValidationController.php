<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatValidationController extends Controller
{
    /**
     * التحقق من صحة المحادثة الخاصة (1-to-1)
     * 
     * @param int $roomId
     * @return array
     */
    public function validatePrivateChat($roomId)
    {
        try {
            // التحقق من وجود الغرفة
            $room = DB::table('chat_rooms')
                ->where('id', $roomId)
                ->where('is_active', true)
                ->first();

            if (!$room) {
                return [
                    'is_valid' => false,
                    'message' => 'الغرفة غير موجودة أو غير نشطة',
                    'room_id' => $roomId,
                    'participant_count' => 0,
                    'room_type' => null
                ];
            }

            // التحقق من نوع الغرفة
            if ($room->type !== 'private') {
                return [
                    'is_valid' => false,
                    'message' => 'هذه محادثة جماعية. يرجى استخدام تدفق إنشاء الغرف.',
                    'room_id' => $roomId,
                    'participant_count' => 0,
                    'room_type' => $room->type
                ];
            }

            // جلب عدد المشاركين
            $participantCount = DB::table('chat_participants')
                ->where('chat_room_id', $roomId)
                ->where('is_archived', false)
                ->count();

            // التحقق من عدد المشاركين
            if ($participantCount !== 2) {
                return [
                    'is_valid' => false,
                    'message' => 'هذه محادثة جماعية. يرجى استخدام تدفق إنشاء الغرف.',
                    'room_id' => $roomId,
                    'participant_count' => $participantCount,
                    'room_type' => $room->type,
                    'participants' => $this->getRoomParticipants($roomId)
                ];
            }

            // التحقق من صحة معرفات المستخدمين
            $participants = $this->getRoomParticipants($roomId);
            $validUserIds = $this->validateUserIds($participants);

            if (!$validUserIds['all_valid']) {
                return [
                    'is_valid' => false,
                    'message' => 'يوجد مستخدمون غير صالحين في المحادثة',
                    'room_id' => $roomId,
                    'participant_count' => $participantCount,
                    'room_type' => $room->type,
                    'invalid_users' => $validUserIds['invalid_ids']
                ];
            }

            return [
                'is_valid' => true,
                'message' => 'هذه محادثة خاصة صالحة (1-to-1)',
                'room_id' => $roomId,
                'participant_count' => $participantCount,
                'room_type' => $room->type,
                'participants' => $participants
            ];

        } catch (\Exception $e) {
            return [
                'is_valid' => false,
                'message' => 'خطأ في التحقق من المحادثة: ' . $e->getMessage(),
                'room_id' => $roomId,
                'participant_count' => 0,
                'room_type' => null
            ];
        }
    }

    /**
     * جلب قائمة المشاركين في الغرفة
     * 
     * @param int $roomId
     * @return array
     */
    private function getRoomParticipants($roomId)
    {
        return DB::table('chat_participants as cp')
            ->leftJoin('users as u', 'cp.user_id', '=', 'u.id')
            ->where('cp.chat_room_id', $roomId)
            ->where('cp.is_archived', false)
            ->select(
                'cp.user_id',
                'u.username',
                'cp.role',
                'cp.joined_at',
                'cp.is_muted'
            )
            ->orderBy('cp.joined_at')
            ->get()
            ->toArray();
    }

    /**
     * التحقق من صحة معرفات المستخدمين
     * 
     * @param array $participants
     * @return array
     */
    private function validateUserIds($participants)
    {
        $userIds = array_column($participants, 'user_id');
        $invalidIds = [];

        foreach ($userIds as $userId) {
            $userExists = DB::table('users')
                ->where('id', $userId)
                ->where('is_active', true)
                ->exists();

            if (!$userExists) {
                $invalidIds[] = $userId;
            }
        }

        return [
            'all_valid' => empty($invalidIds),
            'invalid_ids' => $invalidIds
        ];
    }

    /**
     * إنشاء محادثة خاصة جديدة (1-to-1)
     * 
     * @param Request $request
     * @return array
     */
    public function createPrivateChat(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user1_id' => 'required|integer|exists:users,id',
            'user2_id' => 'required|integer|exists:users,id|different:user1_id',
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ];
        }

        $user1Id = $request->user1_id;
        $user2Id = $request->user2_id;

        // التحقق من وجود محادثة خاصة بين المستخدمين
        $existingChat = $this->findExistingPrivateChat($user1Id, $user2Id);
        if ($existingChat) {
            return [
                'success' => true,
                'message' => 'المحادثة الخاصة موجودة بالفعل',
                'room_id' => $existingChat->id,
                'is_existing' => true
            ];
        }

        try {
            DB::beginTransaction();

            // إنشاء الغرفة
            $roomId = DB::table('chat_rooms')->insertGetId([
                'name' => 'دردشة خاصة',
                'type' => 'private',
                'created_by' => $user1Id,
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => true
            ]);

            // إضافة المشاركين
            $now = now();
            DB::table('chat_participants')->insert([
                [
                    'chat_room_id' => $roomId,
                    'user_id' => $user1Id,
                    'role' => 'member',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'is_muted' => false,
                    'is_archived' => false
                ],
                [
                    'chat_room_id' => $roomId,
                    'user_id' => $user2Id,
                    'role' => 'member',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'is_muted' => false,
                    'is_archived' => false
                ]
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم إنشاء المحادثة الخاصة بنجاح',
                'room_id' => $roomId,
                'is_existing' => false
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء المحادثة: ' . $e->getMessage()
            ];
        }
    }

    /**
     * البحث عن محادثة خاصة موجودة بين مستخدمين
     * 
     * @param int $user1Id
     * @param int $user2Id
     * @return object|null
     */
    private function findExistingPrivateChat($user1Id, $user2Id)
    {
        return DB::table('chat_rooms as cr')
            ->join('chat_participants as cp1', function($join) use ($user1Id) {
                $join->on('cr.id', '=', 'cp1.chat_room_id')
                     ->where('cp1.user_id', '=', $user1Id);
            })
            ->join('chat_participants as cp2', function($join) use ($user2Id) {
                $join->on('cr.id', '=', 'cp2.chat_room_id')
                     ->where('cp2.user_id', '=', $user2Id);
            })
            ->where('cr.type', 'private')
            ->where('cr.is_active', true)
            ->where('cp1.is_archived', false)
            ->where('cp2.is_archived', false)
            ->select('cr.id', 'cr.name', 'cr.created_at')
            ->first();
    }

    /**
     * إنشاء غرفة جماعية
     * 
     * @param Request $request
     * @return array
     */
    public function createGroupRoom(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'user_ids' => 'required|array|min:2',
            'user_ids.*' => 'integer|exists:users,id'
        ]);

        if ($validator->fails()) {
            return [
                'success' => false,
                'message' => 'بيانات غير صالحة',
                'errors' => $validator->errors()
            ];
        }

        try {
            DB::beginTransaction();

            // إنشاء الغرفة
            $roomId = DB::table('chat_rooms')->insertGetId([
                'name' => $request->name,
                'description' => $request->description,
                'type' => 'group',
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
                'is_active' => true
            ]);

            // إضافة المشاركين
            $participants = [];
            $now = now();
            foreach ($request->user_ids as $userId) {
                $participants[] = [
                    'chat_room_id' => $roomId,
                    'user_id' => $userId,
                    'role' => 'member',
                    'joined_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                    'is_muted' => false,
                    'is_archived' => false
                ];
            }

            DB::table('chat_participants')->insert($participants);

            DB::commit();

            return [
                'success' => true,
                'message' => 'تم إنشاء الغرفة الجماعية بنجاح',
                'room_id' => $roomId,
                'participant_count' => count($request->user_ids)
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'خطأ في إنشاء الغرفة: ' . $e->getMessage()
            ];
        }
    }
}



