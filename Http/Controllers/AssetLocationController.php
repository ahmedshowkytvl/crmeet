<?php

namespace App\Http\Controllers;

use App\Models\AssetLocation;
use Illuminate\Http\Request;

class AssetLocationController extends Controller
{
    /**
     * Display a listing of locations
     */
    public function index()
    {
        $locations = AssetLocation::orderBy('name')->paginate(20);
        
        return view('assets.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location
     */
    public function create()
    {
        return view('assets.locations.create');
    }

    /**
     * Store a newly created location
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        AssetLocation::create($request->all());

        return redirect()->route('assets.locations.index')
            ->with('success', __('Location created successfully'));
    }

    /**
     * Display the specified location
     */
    public function show(AssetLocation $location)
    {
        $location->load('assets.category', 'assets.assignedTo');
        
        return view('assets.locations.show', compact('location'));
    }

    /**
     * Show the form for editing the location
     */
    public function edit(AssetLocation $location)
    {
        return view('assets.locations.edit', compact('location'));
    }

    /**
     * Update the specified location
     */
    public function update(Request $request, AssetLocation $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'address_ar' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $location->update($request->all());

        return redirect()->route('assets.locations.show', $location)
            ->with('success', __('Location updated successfully'));
    }

    /**
     * Remove the specified location
     */
    public function destroy(AssetLocation $location)
    {
        if ($location->assets()->count() > 0) {
            return redirect()->back()
                ->with('error', __('Cannot delete location with existing assets'));
        }

        $location->delete();

        return redirect()->route('assets.locations.index')
            ->with('success', __('Location deleted successfully'));
    }

    /**
     * Toggle location status
     */
    public function toggleStatus(AssetLocation $location)
    {
        $location->update(['is_active' => !$location->is_active]);

        return redirect()->back()
            ->with('success', __('Location status updated successfully'));
    }
}

