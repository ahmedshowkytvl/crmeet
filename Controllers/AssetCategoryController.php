<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetCategoryProperty;
use Illuminate\Http\Request;

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of categories
     */
    public function index()
    {
        $categories = AssetCategory::with(['properties', 'assets'])->orderBy('name')->paginate(20);
        
        return view('assets.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        return view('assets.categories.create');
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'properties.*.name' => 'required_with:properties|string|max:255',
            'properties.*.name_ar' => 'nullable|string|max:255',
            'properties.*.type' => 'required_with:properties|in:text,number,date,select,boolean,image',
            'properties.*.options' => 'nullable|string',
            'properties.*.is_required' => 'boolean',
            'properties.*.sort_order' => 'integer|min:0',
        ]);

        $category = AssetCategory::create($request->only([
            'name', 'name_ar', 'description', 'description_ar', 'is_active', 'price'
        ]));

        // Handle properties
        if ($request->has('properties')) {
            foreach ($request->properties as $propertyData) {
                if (!empty($propertyData['name']) && !empty($propertyData['type'])) {
                    $options = null;
                    if ($propertyData['type'] === 'select' && !empty($propertyData['options'])) {
                        $options = array_filter(array_map('trim', explode("\n", $propertyData['options'])));
                    }
                    
                    $category->properties()->create([
                        'name' => $propertyData['name'],
                        'name_ar' => $propertyData['name_ar'] ?? null,
                        'type' => $propertyData['type'],
                        'options' => $options,
                        'is_required' => isset($propertyData['is_required']),
                        'sort_order' => $propertyData['sort_order'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('assets.asset-categories.show', $category)
            ->with('success', __('messages.category_created_successfully'));
    }

    /**
     * Display the specified category
     */
    public function show(AssetCategory $category)
    {
        $category->load('propertiesOrdered', 'assets');
        
        return view('assets.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the category
     */
    public function edit(AssetCategory $category)
    {
        $category->load('propertiesOrdered');
        return view('assets.categories.edit', compact('category'));
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, AssetCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'is_active' => 'boolean',
            'price' => 'nullable|numeric|min:0',
            'properties.*.name' => 'required_with:properties|string|max:255',
            'properties.*.name_ar' => 'nullable|string|max:255',
            'properties.*.type' => 'required_with:properties|in:text,number,date,select,boolean,image',
            'properties.*.options' => 'nullable|string',
            'properties.*.is_required' => 'boolean',
            'properties.*.sort_order' => 'integer|min:0',
        ]);

        $category->update($request->only([
            'name', 'name_ar', 'description', 'description_ar', 'is_active', 'price'
        ]));

        // Handle properties
        if ($request->has('properties')) {
            // Get existing property IDs
            $existingPropertyIds = collect($request->properties)
                ->whereNotNull('id')
                ->pluck('id')
                ->filter()
                ->toArray();

            // Delete properties that are not in the request
            $category->properties()
                ->whereNotIn('id', $existingPropertyIds)
                ->delete();

            // Update or create properties
            foreach ($request->properties as $propertyData) {
                if (!empty($propertyData['name']) && !empty($propertyData['type'])) {
                    $options = null;
                    if ($propertyData['type'] === 'select' && !empty($propertyData['options'])) {
                        $options = array_filter(array_map('trim', explode("\n", $propertyData['options'])));
                    }
                    
                    $propertyDataArray = [
                        'name' => $propertyData['name'],
                        'name_ar' => $propertyData['name_ar'] ?? null,
                        'type' => $propertyData['type'],
                        'options' => $options,
                        'is_required' => isset($propertyData['is_required']),
                        'sort_order' => $propertyData['sort_order'] ?? 0,
                    ];

                    if (isset($propertyData['id']) && $propertyData['id']) {
                        // Update existing property
                        $category->properties()
                            ->where('id', $propertyData['id'])
                            ->update($propertyDataArray);
                    } else {
                        // Create new property
                        $category->properties()->create($propertyDataArray);
                    }
                }
            }
        } else {
            // If no properties in request, delete all existing properties
            $category->properties()->delete();
        }

        return redirect()->route('assets.asset-categories.show', $category)
            ->with('success', __('messages.category_updated_successfully'));
    }

    /**
     * Remove the specified category
     */
    public function destroy(AssetCategory $category)
    {
        if ($category->assets()->count() > 0) {
            return redirect()->back()
                ->with('error', __('Cannot delete category with existing assets'));
        }

        $category->delete();

        return redirect()->route('assets.asset-categories.index')
            ->with('success', __('messages.category_deleted_successfully'));
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(AssetCategory $category)
    {
        $category->update(['is_active' => !$category->is_active]);

        return redirect()->back()
            ->with('success', __('Category status updated successfully'));
    }

    /**
     * Show properties for a category
     */
    public function showProperties(AssetCategory $category)
    {
        $properties = $category->properties()->orderBy('sort_order')->get();
        
        // Add display_name to each property
        $properties->each(function ($property) {
            $property->display_name = $property->getDisplayNameAttribute();
        });
        
        return response()->json($properties);
    }

    /**
     * Show a single property
     */
    public function showProperty(AssetCategoryProperty $property)
    {
        // Convert options string to array if it exists
        if ($property->options) {
            $property->options = is_string($property->options) 
                ? explode("\n", $property->options) 
                : $property->options;
        }
        
        return response()->json($property);
    }

    /**
     * Store a new property for the category
     */
    public function storeProperty(Request $request, AssetCategory $category)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'type' => 'required|in:text,number,date,select,boolean,image',
            'options' => 'nullable|array',
            'is_required' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $data = $request->only(['name', 'name_ar', 'type', 'is_required', 'sort_order']);
        
        // Handle options field
        if ($request->has('options') && $request->options) {
            $data['options'] = $request->options;
        }
        
        $category->properties()->create($data);

        return redirect()->back()
            ->with('success', __('Property added successfully'));
    }

    /**
     * Update a property
     */
    public function updateProperty(Request $request, AssetCategoryProperty $property)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'name_ar' => 'nullable|string|max:255',
                'type' => 'required|in:text,number,date,select,boolean,image',
                'options' => 'nullable|string',
                'is_required' => 'boolean',
                'sort_order' => 'integer|min:0',
            ]);

            // Handle options for select type
            $options = null;
            if ($request->type === 'select' && !empty($request->options)) {
                $options = array_filter(array_map('trim', explode("\n", $request->options)));
            }

            $property->update([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'type' => $request->type,
                'options' => $options,
                'is_required' => $request->has('is_required'),
                'sort_order' => $request->sort_order ?? 0,
            ]);

            // Always return JSON for this endpoint
            return response()->json(['success' => true, 'message' => 'Property updated successfully']);
        } catch (\Exception $e) {
            // Always return JSON for this endpoint
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Delete a property
     */
    public function destroyProperty(AssetCategoryProperty $property)
    {
        $property->delete();

        return redirect()->back()
            ->with('success', __('Property deleted successfully'));
    }
}

