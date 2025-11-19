<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\User;
use App\Services\AssetService;
use Illuminate\Http\Request;

class AssetAssignmentController extends Controller
{
    protected $assetService;

    public function __construct(AssetService $assetService)
    {
        $this->assetService = $assetService;
    }

    /**
     * Display a listing of assignments
     */
    public function index(Request $request)
    {
        $query = AssetAssignment::with(['asset.category', 'user', 'assignedBy']);

        // Apply filters
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'returned') {
                $query->returned();
            }
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('asset_id')) {
            $query->where('asset_id', $request->asset_id);
        }

        $assignments = $query->orderBy('created_at', 'desc')->paginate(20);
        $assets = Asset::unassigned()->with('category')->get();
        $users = User::all();

        return view('assets.assignments.index', compact('assignments', 'assets', 'users'));
    }

    /**
     * Show the form for creating a new assignment
     */
    public function create()
    {
        $assets = Asset::unassigned()->with('category')->get();
        $users = User::all();

        return view('assets.assignments.create', compact('assets', 'users'));
    }

    /**
     * Store a newly created assignment
     */
    public function store(Request $request)
    {
        $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'user_id' => 'required|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::findOrFail($request->asset_id);
        
        if ($asset->assigned_to) {
            return redirect()->back()
                ->with('error', __('Asset is already assigned to another user'));
        }

        $assignment = $this->assetService->assignAsset($asset, $request->user_id, $request->notes);

        return redirect()->route('asset-assignments.show', $assignment)
            ->with('success', __('Asset assigned successfully'));
    }

    /**
     * Display the specified assignment
     */
    public function show(AssetAssignment $assignment)
    {
        $assignment->load(['asset.category', 'user', 'assignedBy']);
        
        return view('assets.assignments.show', compact('assignment'));
    }

    /**
     * Return an asset
     */
    public function return(AssetAssignment $assignment, Request $request)
    {
        $request->validate([
            'notes' => 'nullable|string',
        ]);

        if (!$assignment->isActive()) {
            return redirect()->back()
                ->with('error', __('Asset is already returned'));
        }

        $this->assetService->returnAsset($assignment->asset, $request->notes);

        return redirect()->route('asset-assignments.index')
            ->with('success', __('Asset returned successfully'));
    }

    /**
     * Show return form
     */
    public function showReturnForm(AssetAssignment $assignment)
    {
        $assignment->load(['asset.category', 'user']);
        
        return view('assets.assignments.return', compact('assignment'));
    }

    /**
     * Get assets for assignment (AJAX)
     */
    public function getAssets(Request $request)
    {
        $query = Asset::unassigned()->with('category');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('asset_code', 'like', "%{$search}%");
            });
        }

        $assets = $query->limit(20)->get();

        return response()->json($assets);
    }
}

