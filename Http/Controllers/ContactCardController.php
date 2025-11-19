<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ContactCardController extends Controller
{
    /**
     * عرض بطاقة الاتصال الشاملة للموظف
     */
    public function show(User $user)
    {
        // التأكد من أن المستخدم يمكنه الوصول لبطاقة الاتصال
        // يمكن للمستخدم رؤية بطاقته الشخصية
        if (Auth::id() !== $user->id) {
            // يمكن إضافة تحققات إضافية هنا لاحقاً
            // abort(403, 'غير مسموح لك بعرض هذه البطاقة');
        }

        // جلب البيانات المطلوبة
        $user->load([
            'department',
            'manager',
            'subordinates',
            'role',
            'assignedTasks' => function($query) {
                $query->latest()->take(5);
            },
            'employeeRequests' => function($query) {
                $query->latest()->take(5);
            },
            'activeEmails',
            'employeeEmails'
        ]);

        // جلب زملاء العمل في نفس القسم
        $colleagues = User::where('department_id', $user->department_id)
            ->where('id', '!=', $user->id)
            ->take(10)
            ->get();

        // جلب المهام المشتركة
        $sharedTasks = collect();
        if ($user->manager) {
            $sharedTasks = $user->manager->assignedTasks()
                ->where(function($query) use ($user) {
                    $query->where('assigned_to', $user->id)
                          ->orWhere('created_by', $user->id);
                })
                ->latest()
                ->take(5)
                ->get();
        }

        return view('users.contact-card', compact('user', 'colleagues', 'sharedTasks'));
    }

    /**
     * تحديث إعدادات الخصوصية
     */
    public function updatePrivacySettings(Request $request, User $user)
    {
        $request->validate([
            'show_phone_work' => 'boolean',
            'show_phone_personal' => 'boolean',
            'show_phone_mobile' => 'boolean',
            'show_email' => 'boolean',
            'show_address' => 'boolean',
            'show_social_media' => 'boolean',
        ]);

        $user->update($request->only([
            'show_phone_work',
            'show_phone_personal',
            'show_phone_mobile',
            'show_email',
            'show_address',
            'show_social_media'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث إعدادات الخصوصية بنجاح'
        ]);
    }

    /**
     * إرسال رسالة سريعة
     */
    public function sendQuickMessage(Request $request, User $user)
    {
        $currentUser = Auth::user();
        
        // البحث عن دردشة موجودة بين المستخدمين
        $existingChatRoom = \App\Models\ChatRoom::where('type', 'private')
            ->whereHas('users', function($query) use ($currentUser) {
                $query->where('user_id', $currentUser->id);
            })
            ->whereHas('users', function($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->first();

        // إذا لم توجد دردشة، إنشاء واحدة جديدة
        if (!$existingChatRoom) {
            $existingChatRoom = \App\Models\ChatRoom::create([
                'name' => 'دردشة خاصة',
                'type' => 'private',
                'is_active' => true,
                'created_by' => $currentUser->id
            ]);

            // إضافة المستخدمين للدردشة
            $existingChatRoom->users()->attach([
                $currentUser->id => ['joined_at' => now()],
                $user->id => ['joined_at' => now()]
            ]);
        }

        // إعادة التوجيه إلى صفحة الدردشة مع المحادثة المحددة
        return redirect()->route('chat.index', ['conversation' => $existingChatRoom->id])
            ->with('success', 'تم فتح الدردشة مع ' . $user->name);
    }

    /**
     * جدولة اجتماع
     */
    public function scheduleMeeting(Request $request, User $user)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date|after:now',
            'time' => 'required',
            'duration' => 'required|integer|min:15|max:480',
            'notes' => 'nullable|string|max:1000'
        ]);

        // هنا يمكن إضافة منطق جدولة الاجتماعات
        // مثل إنشاء مهمة أو إرسال دعوة

        return response()->json([
            'success' => true,
            'message' => 'تم جدولة الاجتماع بنجاح'
        ]);
    }

    /**
     * عرض صفحة تعديل بطاقة الاتصال
     */
    public function edit(User $user)
    {
        // Load all phones with phone types
        $user->load(['phones.phoneType']);
        return view('users.edit-contact-card', compact('user'));
    }

    /**
     * تحديث بطاقة الاتصال
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'phone_home' => 'nullable|string|max:20',
            'phone_personal' => 'nullable|string|max:20',
            'phone_mobile' => 'nullable|string|max:20',
            'phone_emergency' => 'nullable|string|max:20',
            'extension' => 'nullable|string|max:10',
            // Work phones
            'work_phones' => 'nullable|array',
            'work_phones.*.number' => 'nullable|string|max:20',
            'work_phones.*.id' => 'nullable|exists:user_phones,id',
            'main_work_phone' => 'nullable|integer|min:0',
            'whatsapp' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'skype' => 'nullable|string|max:50',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:50',
            'linkedin_url' => 'nullable|url|max:255',
            'job_title' => 'nullable|string|max:100',
            'employee_id' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'work_location' => 'nullable|string|max:100',
            'office_room' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:100',
            'birth_date' => 'nullable|date',
            'nationality' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string|max:1000',
            'interests' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:500',
            'timezone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'show_phone_work' => 'boolean',
            'show_phone_personal' => 'boolean',
            'show_phone_mobile' => 'boolean',
            'show_email' => 'boolean',
            'show_address' => 'boolean',
            'show_social_media' => 'boolean',
        ]);

        $data = $request->except(['profile_photo', 'skills', 'interests', 'languages', 'work_phones', 'main_work_phone']);

        // معالجة المصفوفات
        if ($request->skills) {
            $data['skills'] = array_map('trim', explode(',', $request->skills));
        }
        if ($request->interests) {
            $data['interests'] = array_map('trim', explode(',', $request->interests));
        }
        if ($request->languages) {
            $data['languages'] = array_map('trim', explode(',', $request->languages));
        }

        // معالجة صورة الملف الشخصي
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $filename = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_photos', $filename, 'public');
            $data['profile_photo'] = $path;
        }

        $user->update($data);

        // Handle work phones using the same method from UserController
        $this->handleWorkPhones($request, $user);

        return redirect()->route('users.contact-card', $user)
            ->with('success', 'تم تحديث بطاقة الاتصال بنجاح');
    }

    /**
     * Handle multiple work phones for a user
     */
    private function handleWorkPhones(Request $request, User $user)
    {
        // Get or create work phone type
        $workPhoneType = \App\Models\PhoneType::firstOrCreate(
            ['slug' => 'work'],
            ['name' => 'Work', 'name_ar' => 'عمل', 'is_active' => true, 'sort_order' => 1]
        );

        $workPhones = $request->input('work_phones', []);
        $mainPhoneIndex = $request->input('main_work_phone', 0);
        $existingPhoneIds = [];
        $primaryPhoneNumber = null;

        // Process each work phone
        foreach ($workPhones as $index => $phoneData) {
            if (empty($phoneData['number'])) {
                continue; // Skip empty phones
            }

            $phoneId = isset($phoneData['id']) && !str_starts_with($phoneData['id'], 'temp_') ? $phoneData['id'] : null;
            $isPrimary = $index == $mainPhoneIndex;

            if ($phoneId) {
                // Update existing phone
                $phone = \App\Models\UserPhone::find($phoneId);
                if ($phone && $phone->user_id == $user->id) {
                    $phone->update([
                        'phone_number' => $phoneData['number'],
                        'phone_type_id' => $workPhoneType->id,
                        'is_primary' => $isPrimary,
                    ]);
                    $existingPhoneIds[] = $phoneId;
                    if ($isPrimary) {
                        $primaryPhoneNumber = $phoneData['number'];
                    }
                }
            } else {
                // Create new phone
                $phone = \App\Models\UserPhone::create([
                    'user_id' => $user->id,
                    'phone_number' => $phoneData['number'],
                    'phone_type_id' => $workPhoneType->id,
                    'is_primary' => $isPrimary,
                ]);
                $existingPhoneIds[] = $phone->id;
                if ($isPrimary) {
                    $primaryPhoneNumber = $phoneData['number'];
                }
            }
        }

        // Remove phones that are no longer in the list
        $user->phones()
            ->whereHas('phoneType', function($query) {
                $query->where('slug', 'work');
            })
            ->whereNotIn('id', $existingPhoneIds)
            ->delete();

        // Ensure only one primary phone
        if ($primaryPhoneNumber) {
            // Set all work phones to non-primary first
            $user->phones()
                ->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })
                ->update(['is_primary' => false]);

            // Set the main phone as primary (use first to avoid duplicates)
            $primaryPhone = $user->phones()
                ->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })
                ->where('phone_number', $primaryPhoneNumber)
                ->first();
            
            if ($primaryPhone) {
                $primaryPhone->update(['is_primary' => true]);
            }

            // Update phone_work field for backward compatibility
            $user->update(['phone_work' => $primaryPhoneNumber]);
        } else {
            // If no phones, clear phone_work and remove primary flag
            $user->phones()
                ->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })
                ->update(['is_primary' => false]);
            $user->update(['phone_work' => null]);
        }
    }
}
