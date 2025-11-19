<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    public function tempManagement()
    {
        return view('employees.temp-management');
    }
    
    public function getTempData()
    {
        $employees = User::with(['department', 'phones'])
            ->get()
            ->map(function($user) {
                // Try to get phone from phones relationship first
                $phone = $user->phones->first();
                $phoneNumber = $phone ? $phone->phone_number : '';
                
                // If no phone from phones table, try phone_work field
                if (empty($phoneNumber) && isset($user->phone_work)) {
                    $phoneNumber = $user->phone_work;
                }
                
                // Fallback to phone_personal or phone_home
                if (empty($phoneNumber)) {
                    $phoneNumber = $user->phone_personal ?? $user->phone_home ?? '';
                }
                
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'work_number' => $user->work_number ?? '',
                    'id_number' => $user->id_number ?? '',
                    'email' => $user->email,
                    'phone' => $phoneNumber,
                    'department' => $user->department ? $user->department->name_en : 'N/A',
                    'status' => $user->is_active ? 'active' : 'inactive'
                ];
            });
            
        return response()->json($employees);
    }
    
    public function updateTempData(Request $request, $id)
    {
        $field = $request->input('field');
        $value = $request->input('value');
        
        $user = User::find($id);
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        $user->$field = $value;
        $user->save();
        
        return response()->json(['success' => true, 'message' => 'Updated successfully']);
    }
    
    public function bulkColumnTransfer(Request $request)
    {
        $ids = $request->input('ids', []);
        $sourceColumn = $request->input('source_column');
        $targetColumn = $request->input('target_column');
        $overwrite = $request->input('overwrite', false);
        
        if (empty($ids) || !$sourceColumn || !$targetColumn) {
            return response()->json(['error' => 'Invalid parameters'], 400);
        }
        
        if ($sourceColumn === $targetColumn) {
            return response()->json(['error' => 'Source and target cannot be the same'], 400);
        }
        
        $transferred = 0;
        $skipped = 0;
        $empty = 0;
        
        foreach ($ids as $id) {
            $user = User::find($id);
            if (!$user) continue;
            
            $sourceValue = $user->$sourceColumn ?? null;
            $targetValue = $user->$targetColumn ?? null;
            
            // Normalize empty values
            $sourceEmpty = empty($sourceValue) || $sourceValue === '-' || trim($sourceValue) === '';
            $targetEmpty = empty($targetValue) || $targetValue === '-' || trim($targetValue) === '';
            
            // Skip if source is empty
            if ($sourceEmpty) {
                $empty++;
                continue;
            }
            
            // Check if target has value and overwrite is false
            if (!$overwrite && !$targetEmpty) {
                $skipped++;
                continue;
            }
            
            // Transfer value
            try {
                $user->$targetColumn = $sourceValue;
                $user->save();
                $transferred++;
            } catch (\Exception $e) {
                // Log error but continue
                Log::error('Error transferring column: ' . $e->getMessage());
            }
        }
        
        return response()->json([
            'success' => true,
            'transferred' => $transferred,
            'skipped' => $skipped,
            'empty' => $empty,
            'total' => count($ids)
        ]);
    }
}
