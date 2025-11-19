<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\User;
use App\Services\AssetService;
use App\Services\BarcodeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AssetController extends Controller
{
    protected $assetService;
    protected $barcodeService;

    public function __construct(AssetService $assetService, BarcodeService $barcodeService)
    {
        $this->assetService = $assetService;
        $this->barcodeService = $barcodeService;
    }

    /**
     * Display a listing of assets
     */
    public function index(Request $request)
    {
        $query = Asset::with(['category', 'location', 'assignedTo', 'warehouse']);

        // Apply filters
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('location_id')) {
            $query->where('location_id', $request->location_id);
        }

        if ($request->filled('assigned_to')) {
            $query->where('assigned_to', $request->assigned_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        // Handle per_page parameter
        $perPage = $request->get('per_page', 20);
        
        if ($perPage === 'all') {
            $assets = $query->orderBy('created_at', 'desc')->get();
            // Create a custom paginator for "all" option
            $assets = new \Illuminate\Pagination\LengthAwarePaginator(
                $assets,
                $assets->count(),
                $assets->count(),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
        } else {
            $assets = $query->orderBy('created_at', 'desc')->paginate($perPage);
        }
        
        $categories = AssetCategory::active()->get();
        $locations = AssetLocation::active()->get();
        $warehouses = \App\Models\Warehouse::active()->get();
        $users = User::all();

        return view('assets.index', compact('assets', 'categories', 'locations', 'warehouses', 'users'));
    }

    /**
     * Show the form for creating a new asset
     */
    public function create()
    {
        $categories = AssetCategory::active()->get();
        $locations = AssetLocation::active()->get();
        $warehouses = \App\Models\Warehouse::active()->get();
        $users = User::all();

        return view('assets.create', compact('categories', 'locations', 'warehouses', 'users'));
    }

    /**
     * Store a newly created asset
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,maintenance,retired',
            'location_id' => 'nullable|exists:asset_locations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'inventory_status' => 'nullable|in:in_stock,out_of_stock,low_stock,reserved,damaged,expired',
            'quantity' => 'nullable|integer|min:1',
            'store_code' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:EGP,USD,EUR,SAR,AED',
            'properties' => 'nullable|array',
            'properties.*' => 'nullable|string',
        ]);

        $asset = $this->assetService->createAsset($request->all());

        return redirect()->route('assets.show', $asset)
            ->with('success', __('Asset created successfully'));
    }

    /**
     * Display the specified asset
     */
    public function show(Asset $asset)
    {
        $asset->load(['category', 'location', 'assignedTo', 'warehouse', 'assignments.user', 'logs.user', 'propertyValues.property']);
        
        return view('assets.show', compact('asset'));
    }

    /**
     * Show the form for editing the asset
     */
    public function edit(Asset $asset)
    {
        $categories = AssetCategory::active()->get();
        $locations = AssetLocation::active()->get();
        $warehouses = \App\Models\Warehouse::active()->get();
        $users = User::all();
        $asset->load('propertyValues.property');

        return view('assets.edit', compact('asset', 'categories', 'locations', 'warehouses', 'users'));
    }

    /**
     * Update the specified asset
     */
    public function update(Request $request, Asset $asset)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'category_id' => 'required|exists:asset_categories,id',
            'serial_number' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date|after:purchase_date',
            'cost' => 'nullable|numeric|min:0',
            'status' => 'required|in:active,maintenance,retired',
            'location_id' => 'nullable|exists:asset_locations,id',
            'assigned_to' => 'nullable|exists:users,id',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'inventory_status' => 'nullable|in:in_stock,out_of_stock,low_stock,reserved,damaged,expired',
            'quantity' => 'nullable|integer|min:1',
            'store_code' => 'nullable|string|max:255',
            'price' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|in:EGP,USD,EUR,SAR,AED',
            'properties' => 'nullable|array',
            'properties.*' => 'nullable|string',
        ]);

        $this->assetService->updateAsset($asset, $request->all());

        return redirect()->route('assets.show', $asset)
            ->with('success', __('Asset updated successfully'));
    }

    /**
     * Remove the specified asset
     */
    public function destroy(Asset $asset)
    {
        // Delete barcode image
        if ($asset->barcode_image) {
            $this->barcodeService->deleteBarcodeImage($asset->barcode_image);
        }

        $asset->delete();

        return redirect()->route('assets.index')
            ->with('success', __('Asset deleted successfully'));
    }

    /**
     * Print barcode
     */
    public function printBarcode(Asset $asset)
    {
        return view('assets.print-barcode', compact('asset'));
    }

    /**
     * Download barcode as PDF
     */
    public function downloadBarcode(Asset $asset)
    {
        // This would typically use a PDF library like DomPDF or similar
        // For now, we'll return the barcode image
        if ($asset->barcode_image && Storage::disk('public')->exists($asset->barcode_image)) {
            return response()->download(Storage::disk('public')->path($asset->barcode_image));
        }

        return redirect()->back()->with('error', __('Barcode image not found'));
    }
}

