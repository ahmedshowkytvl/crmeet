<?php

namespace App\Http\Controllers;

use App\Models\WarehouseCabinet;
use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WarehouseCabinetController extends Controller
{
    /**
     * عرض قائمة الدولاب
     */
    public function index(Request $request)
    {
        $query = WarehouseCabinet::with(['warehouse', 'shelves']);

        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('cabinet_number', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%");
            });
        }

        $cabinets = $query->orderBy('cabinet_number')->paginate(20);
        $warehouses = Warehouse::active()->get();

        return view('warehouse.cabinets.index', compact('cabinets', 'warehouses'));
    }

    /**
     * عرض تفاصيل دولاب
     */
    public function show(WarehouseCabinet $cabinet)
    {
        $cabinet->load(['warehouse', 'shelves', 'assets.category']);
        
        return view('warehouse.cabinets.show', compact('cabinet'));
    }

    /**
     * عرض نموذج إنشاء دولاب
     */
    public function create()
    {
        $warehouses = Warehouse::active()->get();
        return view('warehouse.cabinets.create', compact('warehouses'));
    }

    /**
     * حفظ دولاب جديد
     */
    public function store(Request $request)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'cabinet_number' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'location_in_warehouse' => 'nullable|string|max:255',
            'total_shelves' => 'required|integer|min:0',
        ]);

        // التحقق من عدم تكرار رقم الدولاب في نفس المخزن
        $existingCabinet = WarehouseCabinet::where('warehouse_id', $request->warehouse_id)
            ->where('cabinet_number', $request->cabinet_number)
            ->first();

        if ($existingCabinet) {
            return back()->withErrors(['cabinet_number' => 'رقم الدولاب موجود بالفعل في هذا المخزن']);
        }

        $cabinet = WarehouseCabinet::create($request->all());

        return redirect()->route('warehouse.cabinets.show', $cabinet)
            ->with('success', 'تم إنشاء الدولاب بنجاح');
    }

    /**
     * عرض نموذج تعديل دولاب
     */
    public function edit(WarehouseCabinet $cabinet)
    {
        $warehouses = Warehouse::active()->get();
        return view('warehouse.cabinets.edit', compact('cabinet', 'warehouses'));
    }

    /**
     * تحديث دولاب
     */
    public function update(Request $request, WarehouseCabinet $cabinet)
    {
        $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'cabinet_number' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'location_in_warehouse' => 'nullable|string|max:255',
            'total_shelves' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        // التحقق من عدم تكرار رقم الدولاب في نفس المخزن (عدا الدولاب الحالي)
        $existingCabinet = WarehouseCabinet::where('warehouse_id', $request->warehouse_id)
            ->where('cabinet_number', $request->cabinet_number)
            ->where('id', '!=', $cabinet->id)
            ->first();

        if ($existingCabinet) {
            return back()->withErrors(['cabinet_number' => 'رقم الدولاب موجود بالفعل في هذا المخزن']);
        }

        $cabinet->update($request->all());

        return redirect()->route('warehouse.cabinets.show', $cabinet)
            ->with('success', 'تم تحديث الدولاب بنجاح');
    }

    /**
     * حذف دولاب
     */
    public function destroy(WarehouseCabinet $cabinet)
    {
        // التحقق من وجود أصول في الدولاب
        if ($cabinet->assets()->count() > 0) {
            return back()->withErrors(['error' => 'لا يمكن حذف الدولاب لوجود أصول مرتبطة به']);
        }

        $cabinet->delete();

        return redirect()->route('warehouse.cabinets.index')
            ->with('success', 'تم حذف الدولاب بنجاح');
    }

    /**
     * إدارة الرفوف
     */
    public function manageShelves(WarehouseCabinet $cabinet)
    {
        $cabinet->load(['shelves', 'warehouse']);
        return view('warehouse.cabinets.shelves', compact('cabinet'));
    }

    /**
     * إضافة رف جديد
     */
    public function addShelf(Request $request, WarehouseCabinet $cabinet)
    {
        $request->validate([
            'shelf_code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
        ]);

        // التحقق من عدم تكرار كود الرف في نفس الدولاب
        $existingShelf = WarehouseShelf::where('cabinet_id', $cabinet->id)
            ->where('shelf_code', $request->shelf_code)
            ->first();

        if ($existingShelf) {
            return back()->withErrors(['shelf_code' => 'كود الرف موجود بالفعل في هذا الدولاب']);
        }

        WarehouseShelf::create([
            'cabinet_id' => $cabinet->id,
            'shelf_code' => $request->shelf_code,
            'name' => $request->name,
            'name_ar' => $request->name_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar,
            'capacity' => $request->capacity,
        ]);

        return back()->with('success', 'تم إضافة الرف بنجاح');
    }

    /**
     * تحديث رف
     */
    public function updateShelf(Request $request, WarehouseShelf $shelf)
    {
        $request->validate([
            'shelf_code' => 'required|string|max:10',
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'capacity' => 'required|integer|min:1',
            'is_active' => 'boolean',
        ]);

        // التحقق من عدم تكرار كود الرف في نفس الدولاب (عدا الرف الحالي)
        $existingShelf = WarehouseShelf::where('cabinet_id', $shelf->cabinet_id)
            ->where('shelf_code', $request->shelf_code)
            ->where('id', '!=', $shelf->id)
            ->first();

        if ($existingShelf) {
            return back()->withErrors(['shelf_code' => 'كود الرف موجود بالفعل في هذا الدولاب']);
        }

        $shelf->update($request->all());

        return back()->with('success', 'تم تحديث الرف بنجاح');
    }

    /**
     * حذف رف
     */
    public function deleteShelf(WarehouseShelf $shelf)
    {
        // التحقق من وجود أصول في الرف
        if ($shelf->assets()->count() > 0) {
            return back()->withErrors(['error' => 'لا يمكن حذف الرف لوجود أصول مرتبطة به']);
        }

        $shelf->delete();

        return back()->with('success', 'تم حذف الرف بنجاح');
    }
}
