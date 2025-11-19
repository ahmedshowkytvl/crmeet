<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\WarehouseCabinet;
use App\Models\WarehouseShelf;
use App\Models\User;
use App\Services\AssetLocationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetMovementController extends Controller
{
    protected $assetLocationService;

    public function __construct(AssetLocationService $assetLocationService)
    {
        $this->assetLocationService = $assetLocationService;
    }

    /**
     * عرض صفحة إدارة حركة الأصول
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'currentCabinet', 'currentShelf', 'assignedTo']);

        // فلترة حسب المخزن
        if ($request->filled('warehouse_id')) {
            $query->where('warehouse_id', $request->warehouse_id);
        }

        // فلترة حسب حالة التوفر
        if ($request->filled('availability_status')) {
            $query->where('current_availability_status', $request->availability_status);
        }

        // فلترة حسب الدولاب
        if ($request->filled('cabinet_id')) {
            $query->where('current_cabinet_id', $request->cabinet_id);
        }

        // البحث
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('asset_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        $assets = $query->orderBy('asset_code')->paginate(20);

        // البيانات المساعدة للفلترة
        $cabinets = WarehouseCabinet::with('warehouse')->get();
        $warehouses = \App\Models\Warehouse::active()->get();

        return view('assets.movement.index', compact('assets', 'cabinets', 'warehouses'));
    }

    /**
     * عرض تفاصيل أصل مع سجل الحركة
     */
    public function show(Asset $asset)
    {
        $asset->load(['category', 'currentCabinet', 'currentShelf', 'assignedTo', 'warehouse']);
        $movementHistory = $this->assetLocationService->getAssetMovementHistory($asset);
        
        return view('assets.movement.show', compact('asset', 'movementHistory'));
    }

    /**
     * عرض نموذج تخزين أصل
     */
    public function storeForm(Asset $asset)
    {
        $asset->load(['warehouse']);
        $cabinets = WarehouseCabinet::where('warehouse_id', $asset->warehouse_id)
            ->with('shelves')
            ->get();
        
        return view('assets.movement.store', compact('asset', 'cabinets'));
    }

    /**
     * تخزين أصل
     */
    public function store(Request $request, Asset $asset)
    {
        $request->validate([
            'cabinet_id' => 'required|exists:warehouse_cabinets,id',
            'shelf_id' => 'required|exists:warehouse_shelves,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->assetLocationService->storeAsset(
                $asset,
                $request->cabinet_id,
                $request->shelf_id,
                Auth::id(),
                $request->notes
            );

            return redirect()->route('assets.movement.show', $asset)
                ->with('success', 'تم تخزين الأصل بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض نموذج تسليم أصل
     */
    public function checkoutForm(Asset $asset)
    {
        $users = User::where('is_active', true)->get();
        return view('assets.movement.checkout', compact('asset', 'users'));
    }

    /**
     * تسليم أصل لموظف
     */
    public function checkout(Request $request, Asset $asset)
    {
        $request->validate([
            'assigned_to_user' => 'required|exists:users,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->assetLocationService->checkOutAsset(
                $asset,
                Auth::id(),
                $request->assigned_to_user,
                $request->notes
            );

            return redirect()->route('assets.movement.show', $asset)
                ->with('success', 'تم تسليم الأصل بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض نموذج إرجاع أصل
     */
    public function returnForm(Asset $asset)
    {
        $asset->load(['warehouse']);
        $cabinets = WarehouseCabinet::where('warehouse_id', $asset->warehouse_id)
            ->with('shelves')
            ->get();
        
        return view('assets.movement.return', compact('asset', 'cabinets'));
    }

    /**
     * إرجاع أصل للمخزن
     */
    public function return(Request $request, Asset $asset)
    {
        $request->validate([
            'cabinet_id' => 'nullable|exists:warehouse_cabinets,id',
            'shelf_id' => 'nullable|exists:warehouse_shelves,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->assetLocationService->returnAsset(
                $asset,
                Auth::id(),
                $request->cabinet_id,
                $request->shelf_id,
                $request->notes
            );

            return redirect()->route('assets.movement.show', $asset)
                ->with('success', 'تم إرجاع الأصل بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * عرض نموذج نقل أصل
     */
    public function moveForm(Asset $asset)
    {
        $asset->load(['warehouse', 'currentCabinet', 'currentShelf']);
        $cabinets = WarehouseCabinet::where('warehouse_id', $asset->warehouse_id)
            ->with('shelves')
            ->get();
        
        return view('assets.movement.move', compact('asset', 'cabinets'));
    }

    /**
     * نقل أصل
     */
    public function move(Request $request, Asset $asset)
    {
        $request->validate([
            'new_cabinet_id' => 'required|exists:warehouse_cabinets,id',
            'new_shelf_id' => 'required|exists:warehouse_shelves,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->assetLocationService->moveAsset(
                $asset,
                Auth::id(),
                $request->new_cabinet_id,
                $request->new_shelf_id,
                $request->notes
            );

            return redirect()->route('assets.movement.show', $asset)
                ->with('success', 'تم نقل الأصل بنجاح');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * وضع أصل في الصيانة
     */
    public function maintenance(Request $request, Asset $asset)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $this->assetLocationService->setAssetMaintenance(
                $asset,
                Auth::id(),
                $request->notes
            );

            return redirect()->route('assets.movement.show', $asset)
                ->with('success', 'تم وضع الأصل في الصيانة');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * الحصول على الرفوف المتاحة لدولاب معين
     */
    public function getAvailableShelves(Request $request)
    {
        $cabinetId = $request->cabinet_id;
        $shelves = WarehouseShelf::where('cabinet_id', $cabinetId)
            ->where('is_active', true)
            ->whereRaw('current_usage < capacity')
            ->get();

        return response()->json($shelves);
    }

    /**
     * تصدير سجل الحركة
     */
    public function exportHistory(Asset $asset)
    {
        $movementHistory = $this->assetLocationService->getAssetMovementHistory($asset);
        
        // هنا يمكن إضافة منطق التصدير لـ Excel أو PDF
        // سأتركه للتنفيذ لاحقاً
        
        return response()->json($movementHistory);
    }
}
