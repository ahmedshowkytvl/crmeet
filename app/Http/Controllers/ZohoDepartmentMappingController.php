<?php

namespace App\Http\Controllers;

use App\Models\ZohoDepartmentMapping;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ZohoDepartmentMappingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $mappings = ZohoDepartmentMapping::with('localDepartment')->orderBy('zoho_department_name')->get();
        $departments = Department::orderBy('name')->get();
        
        return view('zoho.department-mappings.index', compact('mappings', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::orderBy('name')->get();
        return view('zoho.department-mappings.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'zoho_department_id' => 'required|string|unique:zoho_department_mappings,zoho_department_id',
            'zoho_department_name' => 'required|string|max:255',
            'local_department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string'
        ]);

        $localDepartment = Department::findOrFail($request->local_department_id);

        ZohoDepartmentMapping::create([
            'zoho_department_id' => $request->zoho_department_id,
            'zoho_department_name' => $request->zoho_department_name,
            'local_department_id' => $request->local_department_id,
            'local_department_name' => $localDepartment->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('zoho.department-mappings.index')
            ->with('success', 'تم إضافة mapping القسم بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(ZohoDepartmentMapping $departmentMapping)
    {
        $departmentMapping->load('localDepartment');
        return view('zoho.department-mappings.show', compact('departmentMapping'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ZohoDepartmentMapping $departmentMapping)
    {
        $departments = Department::orderBy('name')->get();
        return view('zoho.department-mappings.edit', compact('departmentMapping', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ZohoDepartmentMapping $departmentMapping)
    {
        $request->validate([
            'zoho_department_id' => 'required|string|unique:zoho_department_mappings,zoho_department_id,' . $departmentMapping->id,
            'zoho_department_name' => 'required|string|max:255',
            'local_department_id' => 'required|exists:departments,id',
            'description' => 'nullable|string'
        ]);

        $localDepartment = Department::findOrFail($request->local_department_id);

        $departmentMapping->update([
            'zoho_department_id' => $request->zoho_department_id,
            'zoho_department_name' => $request->zoho_department_name,
            'local_department_id' => $request->local_department_id,
            'local_department_name' => $localDepartment->name,
            'description' => $request->description,
            'is_active' => $request->has('is_active')
        ]);

        return redirect()->route('zoho.department-mappings.index')
            ->with('success', 'تم تحديث mapping القسم بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ZohoDepartmentMapping $departmentMapping)
    {
        $departmentMapping->delete();

        return redirect()->route('zoho.department-mappings.index')
            ->with('success', 'تم حذف mapping القسم بنجاح');
    }

    /**
     * Bulk update mappings from provided data
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'mappings_json' => 'required|string'
        ]);

        try {
            $mappings = json_decode($request->mappings_json, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON format غير صحيح: ' . json_last_error_msg());
            }

            if (!is_array($mappings)) {
                throw new \Exception('يجب أن يكون التنسيق array');
            }

            $updated = 0;
            $created = 0;

            foreach ($mappings as $mappingData) {
                // Validate each mapping
                if (!isset($mappingData['zoho_department_id']) || !isset($mappingData['zoho_department_name']) || !isset($mappingData['local_department_id'])) {
                    continue; // Skip invalid mappings
                }

                $localDepartment = Department::find($mappingData['local_department_id']);
                if (!$localDepartment) {
                    continue; // Skip if local department doesn't exist
                }
                
                $mapping = ZohoDepartmentMapping::updateOrCreate(
                    ['zoho_department_id' => $mappingData['zoho_department_id']],
                    [
                        'zoho_department_name' => $mappingData['zoho_department_name'],
                        'local_department_id' => $mappingData['local_department_id'],
                        'local_department_name' => $localDepartment->name,
                        'description' => $mappingData['description'] ?? "Mapping for {$mappingData['zoho_department_name']}",
                        'is_active' => true
                    ]
                );

                if ($mapping->wasRecentlyCreated) {
                    $created++;
                } else {
                    $updated++;
                }
            }

            return redirect()->route('zoho.department-mappings.index')
                ->with('success', "تم إنشاء {$created} mapping جديد وتحديث {$updated} mapping موجود");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'خطأ في معالجة البيانات: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(ZohoDepartmentMapping $departmentMapping)
    {
        $departmentMapping->update(['is_active' => !$departmentMapping->is_active]);
        
        $status = $departmentMapping->is_active ? 'مفعل' : 'معطل';
        return redirect()->back()->with('success', "تم {$status} mapping القسم");
    }
}