<?php

namespace App\Http\Controllers;

use App\Models\AssetLog;
use App\Models\Asset;
use App\Models\User;
use Illuminate\Http\Request;

class AssetLogController extends Controller
{
    /**
     * Display a listing of logs
     */
    public function index(Request $request)
    {
        $query = AssetLog::with(['asset.category', 'user']);

        // Apply filters
        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(50);
        $assets = Asset::with('category')->get();
        $users = User::all();

        return view('assets.logs.index', compact('logs', 'assets', 'users'));
    }

    /**
     * Display logs for a specific asset
     */
    public function asset(Asset $asset)
    {
        $logs = $asset->logs()
            ->with(['user', 'cabinet', 'shelf', 'assignedToUser'])
            ->orderBy('action_timestamp', 'desc')
            ->paginate(20);
        
        return view('assets.logs.asset', compact('asset', 'logs'));
    }

    /**
     * Export logs
     */
    public function export(Request $request)
    {
        $query = AssetLog::with(['asset.category', 'user', 'cabinet', 'shelf', 'assignedToUser']);

        // Apply same filters as index
        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        // Generate CSV
        $filename = 'asset_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Date',
                'Asset Code',
                'Asset Name',
                'Action',
                'User',
                'Notes'
            ]);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->date ? $log->date->format('Y-m-d H:i:s') : '-',
                    $log->asset->asset_code,
                    $log->asset->display_name,
                    $log->action_label,
                    $log->user ? $log->user->name : 'User Not Found',
                    $log->notes
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}

