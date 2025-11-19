<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ChatRoom;
use App\Models\ChatParticipant;
use App\Models\ChatMessage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class CreateTestChat extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:create-test {--user-id=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'إنشاء حساب وهمي وإنشاء شات بينه وبين مستخدم محدد';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🚀 بدء إنشاء حساب وهمي وشات تجريبي...');

        // البحث عن المستخدم "Madonna 847" أو استخدام user-id المحدد
        $targetUserId = $this->option('user-id');
        
        if (!$targetUserId) {
            // البحث عن مستخدم يحتوي على "Madonna" في الاسم
            $targetUser = User::where('name', 'like', '%Madonna%')
                ->orWhere('email', 'like', '%madonna%')
                ->first();
            
            if (!$targetUser) {
                // البحث عن مستخدم برقم 847 في الاسم أو EmployeeCode
                $targetUser = User::where('name', 'like', '%847%')
                    ->orWhere('EmployeeCode', '847')
                    ->orWhere('employee_id', '847')
                    ->first();
            }
        } else {
            $targetUser = User::find($targetUserId);
        }

        if (!$targetUser) {
            $this->error('❌ لم يتم العثور على المستخدم المطلوب');
            $this->info('المستخدمون المتاحون:');
            User::select('id', 'name', 'email')->limit(10)->get()->each(function($user) {
                $this->line("  - ID: {$user->id}, Name: {$user->name}, Email: {$user->email}");
            });
            return 1;
        }

        $this->info("✅ تم العثور على المستخدم: {$targetUser->name} (ID: {$targetUser->id})");

        DB::beginTransaction();
        try {
            // إنشاء حساب وهمي
            $fakeUser = User::firstOrCreate(
                ['email' => 'test.chat.user@example.com'],
                [
                    'name' => 'Test Chat User',
                    'name_ar' => 'مستخدم تجريبي للشات',
                    'password' => Hash::make('password123'),
                    'user_type' => 'employee',
                    'job_title' => 'Test User',
                    'is_archived' => false,
                ]
            );

            if ($fakeUser->wasRecentlyCreated) {
                $this->info("✅ تم إنشاء حساب وهمي جديد: {$fakeUser->name} (ID: {$fakeUser->id})");
            } else {
                $this->info("ℹ️  تم العثور على حساب وهمي موجود: {$fakeUser->name} (ID: {$fakeUser->id})");
            }

            // البحث عن شات موجود بين المستخدمين
            $existingChat = ChatRoom::where('type', 'private')
                ->whereHas('participants', function($query) use ($fakeUser) {
                    $query->where('user_id', $fakeUser->id);
                })
                ->whereHas('participants', function($query) use ($targetUser) {
                    $query->where('user_id', $targetUser->id);
                })
                ->first();

            if ($existingChat) {
                $this->info("ℹ️  تم العثور على شات موجود بين المستخدمين (ID: {$existingChat->id})");
                
                // إضافة رسالة تجريبية
                $testMessage = ChatMessage::create([
                    'chat_room_id' => $existingChat->id,
                    'user_id' => $fakeUser->id,
                    'message' => 'هذه رسالة تجريبية من الحساب الوهمي - ' . now()->format('Y-m-d H:i:s'),
                    'type' => 'text'
                ]);

                $existingChat->update(['last_message_at' => now()]);

                $this->info("✅ تم إضافة رسالة تجريبية إلى الشات الموجود (Message ID: {$testMessage->id})");
                $this->info("📝 Chat Room ID: {$existingChat->id}");
                $this->info("👤 Fake User ID: {$fakeUser->id}");
                $this->info("👤 Target User ID: {$targetUser->id}");
                
                DB::commit();
                return 0;
            }

            // إنشاء شات جديد
            $chatRoom = ChatRoom::create([
                'name' => 'دردشة تجريبية',
                'type' => 'private',
                'is_active' => true,
                'created_by' => $fakeUser->id,
                'last_message_at' => now(),
            ]);

            // إضافة المشاركين
            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $fakeUser->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            ChatParticipant::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $targetUser->id,
                'role' => 'member',
                'joined_at' => now(),
            ]);

            // إضافة رسالة ترحيبية
            $welcomeMessage = ChatMessage::create([
                'chat_room_id' => $chatRoom->id,
                'user_id' => $fakeUser->id,
                'message' => 'مرحباً! هذه رسالة تجريبية من الحساب الوهمي - ' . now()->format('Y-m-d H:i:s'),
                'type' => 'text'
            ]);

            $this->info("✅ تم إنشاء شات جديد بنجاح!");
            $this->info("📝 Chat Room ID: {$chatRoom->id}");
            $this->info("👤 Fake User ID: {$fakeUser->id}");
            $this->info("👤 Target User ID: {$targetUser->id}");
            $this->info("💬 Message ID: {$welcomeMessage->id}");
            $this->info("");
            $this->info("🔗 رابط الشات: " . route('chat.static', ['conversation' => $chatRoom->id]));

            DB::commit();
            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("❌ حدث خطأ: " . $e->getMessage());
            $this->error("Stack trace: " . $e->getTraceAsString());
            return 1;
        }
    }
}

