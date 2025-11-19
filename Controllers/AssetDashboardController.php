<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetLocation;
use App\Models\AssetAssignment;
use App\Models\AssetLog;
use Illuminate\Http\Request;

class AssetDashboardController extends Controller
{
    /**
     * Display the asset dashboard
     */
    public function index()
    {
        // Asset statistics
        $totalAssets = Asset::count();
        $activeAssets = Asset::active()->count();
        $maintenanceAssets = Asset::maintenance()->count();
        $retiredAssets = Asset::retired()->count();
        $assignedAssets = Asset::assigned()->count();
        $unassignedAssets = Asset::unassigned()->count();

        // Assets by category
        $assetsByCategory = AssetCategory::withCount('assets')
            ->orderBy('assets_count', 'desc')
            ->limit(10)
            ->get();

        // Assets by location
        $assetsByLocation = AssetLocation::withCount('assets')
            ->orderBy('assets_count', 'desc')
            ->limit(10)
            ->get();

        // Recent assignments
        $recentAssignments = AssetAssignment::with(['asset.category', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Recent logs
        $recentLogs = AssetLog::with(['asset', 'user'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Assets expiring warranty (next 30 days)
        $expiringWarranty = Asset::whereNotNull('warranty_expiry')
            ->where('warranty_expiry', '<=', now()->addDays(30))
            ->where('warranty_expiry', '>=', now())
            ->with('category')
            ->orderBy('warranty_expiry')
            ->get();

        // Monthly asset creation trend
        $monthlyTrend = Asset::selectRaw('TO_CHAR(created_at, \'YYYY-MM\') as month, COUNT(*) as count')
            ->where('created_at', '>=', now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        return view('assets.dashboard', compact(
            'totalAssets',
            'activeAssets',
            'maintenanceAssets',
            'retiredAssets',
            'assignedAssets',
            'unassignedAssets',
            'assetsByCategory',
            'assetsByLocation',
            'recentAssignments',
            'recentLogs',
            'expiringWarranty',
            'monthlyTrend'
        ));
    }

    /**
     * Get dashboard statistics (AJAX)
     */
    public function statistics()
    {
        $stats = [
            'total_assets' => Asset::count(),
            'active_assets' => Asset::active()->count(),
            'maintenance_assets' => Asset::maintenance()->count(),
            'retired_assets' => Asset::retired()->count(),
            'assigned_assets' => Asset::assigned()->count(),
            'unassigned_assets' => Asset::unassigned()->count(),
            'total_categories' => AssetCategory::count(),
            'total_locations' => AssetLocation::count(),
            'total_assignments' => AssetAssignment::count(),
            'active_assignments' => AssetAssignment::active()->count(),
        ];

        return response()->json($stats);
    }
}

