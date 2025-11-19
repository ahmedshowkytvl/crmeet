<?php

namespace App\Http\Controllers;

use App\Models\ContactCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContactCategoryController extends Controller
{
    /**
     * عرض قائمة التصنيفات
     */
    public function index()
    {
        $categories = ContactCategory::ordered()->get();
        return view('contact-categories.index', compact('categories'));
    }

    /**
     * عرض نموذج إنشاء تصنيف جديد
     */
    public function create()
    {
        return view('contact-categories.create');
    }

    /**
     * حفظ تصنيف جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:contact_categories,name',
            'name_en' => 'required|string|max:255|unique:contact_categories,name_en',
            'description' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        ContactCategory::create($request->all());

        return redirect()->route('contact-categories.index')
            ->with('success', 'تم إنشاء التصنيف بنجاح');
    }

    /**
     * عرض تفاصيل التصنيف
     */
    public function show(ContactCategory $contactCategory)
    {
        // التحقق من وجود جدول contacts قبل استخدام العلاقة
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'contact_type')) {
            $contacts = $contactCategory->contacts()->with('contactInteractions')->paginate(10);
        } else {
            // إرجاع paginator فارغ لتجنب الأخطاء
            $contacts = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);
        }
        
        return view('contact-categories.show', compact('contactCategory', 'contacts'));
    }

    /**
     * عرض نموذج تعديل التصنيف
     */
    public function edit(ContactCategory $contactCategory)
    {
        return view('contact-categories.edit', compact('contactCategory'));
    }

    /**
     * تحديث التصنيف
     */
    public function update(Request $request, ContactCategory $contactCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:contact_categories,name,' . $contactCategory->id,
            'name_en' => 'required|string|max:255|unique:contact_categories,name_en,' . $contactCategory->id,
            'description' => 'nullable|string|max:1000',
            'description_en' => 'nullable|string|max:1000',
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'icon' => 'required|string|max:50',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $contactCategory->update($request->all());

        return redirect()->route('contact-categories.index')
            ->with('success', 'تم تحديث التصنيف بنجاح');
    }

    /**
     * حذف التصنيف
     */
    public function destroy(ContactCategory $contactCategory)
    {
        // التحقق من وجود جهات اتصال مرتبطة فقط إذا كان جدول contacts موجود
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'contact_type')) {
            if ($contactCategory->contacts()->count() > 0) {
                return redirect()->back()
                    ->with('error', 'لا يمكن حذف التصنيف لوجود جهات اتصال مرتبطة به');
            }
        }

        $contactCategory->delete();

        return redirect()->route('contact-categories.index')
            ->with('success', 'تم حذف التصنيف بنجاح');
    }

    /**
     * تبديل حالة التصنيف
     */
    public function toggleStatus(ContactCategory $contactCategory)
    {
        $contactCategory->update(['is_active' => !$contactCategory->is_active]);
        
        $status = $contactCategory->is_active ? 'تفعيل' : 'إلغاء تفعيل';
        return redirect()->back()
            ->with('success', "تم {$status} التصنيف بنجاح");
    }

    /**
     * تحديث ترتيب التصنيفات
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*.id' => 'required|exists:contact_categories,id',
            'categories.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->categories as $category) {
                ContactCategory::where('id', $category['id'])
                    ->update(['sort_order' => $category['sort_order']]);
            }
        });

        return response()->json(['success' => true]);
    }

    /**
     * الحصول على التصنيفات للاستخدام في API
     */
    public function getCategories()
    {
        $categories = ContactCategory::active()->ordered()->get();
        return response()->json($categories);
    }
}
