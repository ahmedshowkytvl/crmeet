<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TaskTemplateController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:manage-tasks')->except(['index', 'show', 'getTemplatesForDepartment']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TaskTemplate::query();

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // التصفية حسب القسم
        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        // التصفية حسب الحالة
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $templates = $query->paginate(20)->withQueryString();

        $departments = TaskTemplate::getAvailableDepartments();
        $stats = [
            'total' => TaskTemplate::count(),
            'active' => TaskTemplate::where('is_active', true)->count(),
            'inactive' => TaskTemplate::where('is_active', false)->count(),
            'departments' => TaskTemplate::selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->pluck('count', 'department')
                ->toArray(),
        ];

        return view('task-templates.index', compact('templates', 'departments', 'stats'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = TaskTemplate::getAvailableDepartments();
        return view('task-templates.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'estimated_time' => 'required|numeric|min:0',
            'department' => 'required|string|in:' . implode(',', array_keys(TaskTemplate::getAvailableDepartments())),
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // التحقق من عدم وجود قالب مكرر
        $existing = TaskTemplate::where('name', $request->name)
            ->where('department', $request->department)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['name' => 'يوجد قالب بهذا الاسم في نفس القسم بالفعل'])
                ->withInput();
        }

        TaskTemplate::create($request->all());

        return redirect()->route('task-templates.index')
            ->with('success', 'تم إنشاء القالب بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(TaskTemplate $taskTemplate)
    {
        $taskTemplate->load('tasks');
        return view('task-templates.show', compact('taskTemplate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TaskTemplate $taskTemplate)
    {
        $departments = TaskTemplate::getAvailableDepartments();
        return view('task-templates.edit', compact('taskTemplate', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TaskTemplate $taskTemplate)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'estimated_time' => 'required|numeric|min:0',
            'department' => 'required|string|in:' . implode(',', array_keys(TaskTemplate::getAvailableDepartments())),
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // التحقق من عدم وجود قالب مكرر (عدا الحالي)
        $existing = TaskTemplate::where('name', $request->name)
            ->where('department', $request->department)
            ->where('id', '!=', $taskTemplate->id)
            ->first();

        if ($existing) {
            return redirect()->back()
                ->withErrors(['name' => 'يوجد قالب بهذا الاسم في نفس القسم بالفعل'])
                ->withInput();
        }

        $taskTemplate->update($request->all());

        return redirect()->route('task-templates.index')
            ->with('success', 'تم تحديث القالب بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TaskTemplate $taskTemplate)
    {
        if (!$taskTemplate->canBeDeleted()) {
            return redirect()->back()
                ->with('error', 'لا يمكن حذف هذا القالب لأنه مستخدم في مهام موجودة');
        }

        $taskTemplate->delete();

        return redirect()->route('task-templates.index')
            ->with('success', 'تم حذف القالب بنجاح');
    }

    /**
     * استيراد قوالب من ملف CSV
     */
    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator);
        }

        $file = $request->file('csv_file');
        $filename = 'temp_import_' . time() . '.csv';
        $file->storeAs('temp', $filename);

        try {
            // تشغيل الأمر
            $output = shell_exec("php artisan import:task-templates storage/app/temp/{$filename} 2>&1");
            
            // حذف الملف المؤقت
            Storage::delete("temp/{$filename}");
            
            // تحليل النتيجة
            if (strpos($output, 'تم استيراد') !== false) {
                return redirect()->route('task-templates.index')
                    ->with('success', 'تم استيراد القوالب بنجاح');
            } else {
                return redirect()->back()
                    ->with('error', 'حدث خطأ أثناء الاستيراد: ' . $output);
            }
        } catch (\Exception $e) {
            Storage::delete("temp/{$filename}");
            return redirect()->back()
                ->with('error', 'حدث خطأ أثناء الاستيراد: ' . $e->getMessage());
        }
    }

    /**
     * الحصول على قوالب قسم معين (للاستخدام في AJAX)
     */
    public function getTemplatesForDepartment(Request $request)
    {
        $department = $request->get('department');
        
        if (!$department) {
            return response()->json([]);
        }

        $templates = TaskTemplate::active()
            ->byDepartment($department)
            ->select('id', 'name', 'name_ar', 'estimated_time', 'description', 'description_ar')
            ->orderBy('name')
            ->get();

        return response()->json($templates);
    }

    /**
     * تبديل حالة القالب (نشط/غير نشط)
     */
    public function toggleStatus(TaskTemplate $taskTemplate)
    {
        $taskTemplate->update(['is_active' => !$taskTemplate->is_active]);
        
        $status = $taskTemplate->is_active ? 'نشط' : 'غير نشط';
        return redirect()->back()
            ->with('success', "تم تغيير حالة القالب إلى: {$status}");
    }

    /**
     * عرض قوالب المهام في صفحة Zoho
     */
    public function zohoIndex(Request $request)
    {
        $query = TaskTemplate::query();

        // البحث
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // التصفية حسب القسم
        if ($request->filled('department')) {
            $query->byDepartment($request->department);
        }

        // التصفية حسب الحالة
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // الترتيب
        $sortBy = $request->get('sort_by', 'name');
        $sortOrder = $request->get('sort_order', 'asc');
        $query->orderBy($sortBy, $sortOrder);

        $templates = $query->paginate(20)->withQueryString();

        $departments = TaskTemplate::getAvailableDepartments();
        $stats = [
            'total' => TaskTemplate::count(),
            'active' => TaskTemplate::where('is_active', true)->count(),
            'inactive' => TaskTemplate::where('is_active', false)->count(),
            'departments' => TaskTemplate::selectRaw('department, COUNT(*) as count')
                ->groupBy('department')
                ->pluck('count', 'department')
                ->toArray(),
        ];

        return view('zoho.task-templates', compact('templates', 'departments', 'stats'));
    }
}
