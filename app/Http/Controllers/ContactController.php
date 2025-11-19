<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Contact;
use App\Models\ContactInteraction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ContactController extends Controller
{
    /**
     * عرض قائمة جهات الاتصال
     */
    public function index(Request $request)
    {
        $query = User::with(['department', 'manager']);

        // فلترة الموظفين/الموردين
        if ($request->get('hide_employees', '1') === '1') {
            // إخفاء الموظفين - إظهار الموردين فقط
            $query->whereHas('role', function($roleQuery) {
                $roleQuery->where('slug', 'supplier');
            });
        }
        // إذا كان hide_employees = '0'، لا نضيف أي فلترة - نعرض جميع المستخدمين

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_work', 'like', "%{$search}%");
                  
                // البحث في الحقول الموجودة فقط
                if (Schema::hasColumn('users', 'job_title')) {
                    $q->orWhere('job_title', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('users', 'phone_mobile')) {
                    $q->orWhere('phone_mobile', 'like', "%{$search}%");
                }
                if (Schema::hasColumn('users', 'phone_personal')) {
                    $q->orWhere('phone_personal', 'like', "%{$search}%");
                }
            });
        }

        // تصفية حسب القسم
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // تصفية حسب الدور
        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        // Handle per_page parameter
        $perPage = $request->get('per_page', 20);
        
        if ($perPage === 'all') {
            $contacts = $query->with(['role', 'department'])->get();
            // Create a custom paginator for "all" option
            $contacts = new \Illuminate\Pagination\LengthAwarePaginator(
                $contacts,
                $contacts->count(),
                $contacts->count(),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
        } else {
            $contacts = $query->with(['role', 'department'])->paginate($perPage);
        }
        
        $departments = \App\Models\Department::all();
        $roles = \App\Models\Role::active()->ordered()->get();

        return view('contacts.index', compact('contacts', 'departments', 'roles'));
    }

    /**
     * عرض تفاصيل جهة الاتصال
     */
    public function show(User $contact)
    {
        $contact->load(['department', 'manager', 'subordinates', 'role']);
        
        return view('contacts.show', compact('contact'));
    }

    /**
     * البحث السريع في جهات الاتصال
     */
    public function quickSearch(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $contacts = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('job_title', 'like', "%{$query}%")
            ->limit(10)
            ->get(['id', 'name', 'email', 'job_title', 'department_id'])
            ->load('department');

        return response()->json($contacts);
    }

    /**
     * عرض نموذج إنشاء جهة اتصال جديدة
     */
    public function create()
    {
        $departments = \App\Models\Department::all();
        $roles = \App\Models\Role::active()->ordered()->get();
        $managers = User::whereHas('role', function($query) {
            $query->whereIn('slug', ['manager', 'employee_manager']);
        })->get();
        
        return view('contacts.create', compact('departments', 'managers', 'roles'));
    }

    /**
     * حفظ جهة اتصال جديدة
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,manager,employee',
            'department_id' => 'nullable|exists:departments,id',
            'manager_id' => 'nullable|exists:users,id',
            
            // معلومات الاتصال
            'phone_work' => 'nullable|string|max:20',
            'phone_home' => 'nullable|string|max:20',
            'phone_personal' => 'nullable|string|max:20',
            'phone_mobile' => 'nullable|string|max:20',
            'phone_emergency' => 'nullable|string|max:20',
            'extension' => 'nullable|string|max:10',
            
            // وسائل التواصل الاجتماعي
            'whatsapp' => 'nullable|string|max:20',
            'telegram' => 'nullable|string|max:50',
            'skype' => 'nullable|string|max:50',
            'facebook' => 'nullable|url|max:255',
            'instagram' => 'nullable|string|max:50',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'website_url' => 'nullable|url|max:255',
            
            // معلومات العمل
            'job_title' => 'nullable|string|max:100',
            'employee_id' => 'nullable|string|max:50',
            'hire_date' => 'nullable|date',
            'work_location' => 'nullable|string|max:100',
            'office_room' => 'nullable|string|max:50',
            'company' => 'nullable|string|max:100',
            'personal_email' => 'nullable|email|max:255',
            'office_address' => 'nullable|string|max:500',
            
            // معلومات شخصية
            'birthday' => 'nullable|date',
            'birth_date' => 'nullable|date',
            'nationality' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'postal_code' => 'nullable|string|max:20',
            
            // معلومات إضافية
            'bio' => 'nullable|string|max:1000',
            'skills' => 'nullable|string|max:1000',
            'interests' => 'nullable|string|max:1000',
            'languages' => 'nullable|string|max:500',
            'timezone' => 'nullable|string|max:50',
            'notes' => 'nullable|string|max:1000',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            
            // إعدادات الخصوصية
            'show_phone_work' => 'boolean',
            'show_phone_personal' => 'boolean',
            'show_phone_mobile' => 'boolean',
            'show_email' => 'boolean',
            'show_address' => 'boolean',
            'show_social_media' => 'boolean',
        ]);

        $data = $request->except(['password_confirmation', 'profile_photo', 'skills', 'interests', 'languages']);
        $data['password'] = bcrypt($request->password);

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
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_photos', $filename, 'public');
            $data['profile_photo'] = $path;
        }

        $contact = User::create($data);

        return redirect()->route('contacts.show', $contact)
            ->with('success', 'تم إنشاء جهة الاتصال بنجاح');
    }

    /**
     * تصدير جهات الاتصال
     */
    public function export(Request $request)
    {
        $contacts = User::with(['department', 'manager'])->get();
        
        $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($contacts) {
            $file = fopen('php://output', 'w');
            
            // إضافة BOM للدعم العربي
            fwrite($file, "\xEF\xBB\xBF");
            
            // رؤوس الأعمدة
            fputcsv($file, [
                'الاسم',
                'البريد الإلكتروني',
                'المسمى الوظيفي',
                'القسم',
                'هاتف العمل',
                'الهاتف المحمول',
                'المدير المباشر'
            ]);

            // البيانات
            foreach ($contacts as $contact) {
                fputcsv($file, [
                    $contact->name,
                    $contact->email,
                    $contact->job_title ?? '',
                    $contact->department ? $contact->department->name : '',
                    $contact->phone_work ?? '',
                    $contact->phone_mobile ?? '',
                    $contact->manager ? $contact->manager->name : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
