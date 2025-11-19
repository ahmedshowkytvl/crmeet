<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\SupplierNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SupplierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = Supplier::activeNotArchived()->withCount('notes')->orderBy('name')->paginate(20);
        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('suppliers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'notes.*.type' => 'nullable|string|in:general,follow_up,issue,meeting,payment',
            'notes.*.note' => 'nullable|string|max:1000',
        ]);

        $supplier = Supplier::create($request->all());

        // Handle notes if provided
        if ($request->has('notes')) {
            foreach ($request->notes as $noteData) {
                if (!empty($noteData['note'])) {
                    SupplierNote::create([
                        'supplier_id' => $supplier->id,
                        'user_id' => Auth::id(),
                        'note' => $noteData['note'],
                        'type' => $noteData['type'] ?? 'general',
                    ]);
                }
            }
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'تم إنشاء المورد بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        $supplier->load(['supplierNotes.user']);
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'website' => 'nullable|url|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
            'notes.*.type' => 'nullable|string|in:general,follow_up,issue,meeting,payment',
            'notes.*.note' => 'nullable|string|max:1000',
        ]);

        $supplier->update($request->all());

        // Handle notes if provided
        if ($request->has('notes')) {
            foreach ($request->notes as $noteData) {
                if (!empty($noteData['note'])) {
                    SupplierNote::create([
                        'supplier_id' => $supplier->id,
                        'user_id' => Auth::id(),
                        'note' => $noteData['note'],
                        'type' => $noteData['type'] ?? 'general',
                    ]);
                }
            }
        }

        return redirect()->route('suppliers.index')
            ->with('success', 'تم تحديث المورد بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with('success', 'تم حذف المورد بنجاح');
    }

    /**
     * Archive a supplier instead of deleting
     */
    public function archive(Supplier $supplier)
    {
        $supplier->update([
            'is_archived' => true,
            'archived_at' => now()
        ]);

        return redirect()->route('suppliers.index')
            ->with('success', 'تم أرشفة المورد بنجاح');
    }

    /**
     * Restore an archived supplier
     */
    public function restore(Supplier $supplier)
    {
        $supplier->update([
            'is_archived' => false,
            'archived_at' => null
        ]);

        return redirect()->route('suppliers.archived')
            ->with('success', 'تم استعادة المورد بنجاح');
    }

    /**
     * Show archived suppliers
     */
    public function archived(Request $request)
    {
        $query = Supplier::archived();
        
        // Handle search functionality
        if ($request->filled('search')) {
            $searchTerm = $request->get('search');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('name_ar', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('contact_person', 'like', "%{$searchTerm}%")
                  ->orWhere('city', 'like', "%{$searchTerm}%");
            });
        }
        
        $suppliers = $query->latest('archived_at')->paginate(20);
        return view('suppliers.archived', compact('suppliers'));
    }

    /**
     * Store a new note for the supplier.
     */
    public function storeNote(Request $request, Supplier $supplier)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'type' => 'nullable|string|in:general,follow_up,issue,meeting,payment',
        ]);

        SupplierNote::create([
            'supplier_id' => $supplier->id,
            'user_id' => Auth::id(),
            'note' => $request->note,
            'type' => $request->type ?? 'general',
        ]);

        return redirect()->back()->with('success', 'تم إضافة الملاحظة بنجاح');
    }

    /**
     * Update a supplier note.
     */
    public function updateNote(Request $request, SupplierNote $note)
    {
        $request->validate([
            'note' => 'required|string|max:1000',
            'type' => 'nullable|string|in:general,follow_up,issue,meeting,payment',
        ]);

        $note->update([
            'note' => $request->note,
            'type' => $request->type ?? 'general',
        ]);

        return redirect()->back()->with('success', 'تم تحديث الملاحظة بنجاح');
    }

    /**
     * Delete a supplier note.
     */
    public function deleteNote(SupplierNote $note)
    {
        $note->delete();
        return redirect()->back()->with('success', 'تم حذف الملاحظة بنجاح');
    }
}
