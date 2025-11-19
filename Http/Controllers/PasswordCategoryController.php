<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PasswordCategory;
use Illuminate\Support\Facades\DB;

class PasswordCategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PasswordCategory::query();

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('name_ar', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->active();
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->ordered()->paginate(15);

        return view('password-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('password-categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            PasswordCategory::create([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'icon' => $request->icon,
                'color' => $request->color ?: '#6c757d',
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->sort_order ?: 0,
            ]);

            return redirect()->route('password-categories.index')
                           ->with('success', __('Category created successfully'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('Failed to create category')]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PasswordCategory $passwordCategory)
    {
        $passwordAccounts = $passwordCategory->passwordAccounts()
            ->with(['creator', 'assignments.user'])
            ->paginate(10);

        return view('password-categories.show', compact('passwordCategory', 'passwordAccounts'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PasswordCategory $passwordCategory)
    {
        return view('password-categories.edit', compact('passwordCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PasswordCategory $passwordCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'description_ar' => 'nullable|string',
            'icon' => 'nullable|string|max:255',
            'color' => 'nullable|string|regex:/^#[0-9A-Fa-f]{6}$/',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $passwordCategory->update([
                'name' => $request->name,
                'name_ar' => $request->name_ar,
                'description' => $request->description,
                'description_ar' => $request->description_ar,
                'icon' => $request->icon,
                'color' => $request->color ?: '#6c757d',
                'is_active' => $request->boolean('is_active', true),
                'sort_order' => $request->sort_order ?: 0,
            ]);

            return redirect()->route('password-categories.index')
                           ->with('success', __('Category updated successfully'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('Failed to update category')]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PasswordCategory $passwordCategory)
    {
        try {
            // Check if category has password accounts
            if ($passwordCategory->passwordAccounts()->count() > 0) {
                return back()->withErrors(['error' => __('Cannot delete category with existing password accounts')]);
            }

            $passwordCategory->delete();

            return redirect()->route('password-categories.index')
                           ->with('success', __('Category deleted successfully'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => __('Failed to delete category')]);
        }
    }

    /**
     * Get categories for API/select options
     */
    public function getCategories()
    {
        $categories = PasswordCategory::active()
            ->ordered()
            ->select('id', 'name', 'name_ar', 'color', 'icon')
            ->get()
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->display_name,
                    'color' => $category->color,
                    'icon' => $category->icon,
                ];
            });

        return response()->json($categories);
    }
}
