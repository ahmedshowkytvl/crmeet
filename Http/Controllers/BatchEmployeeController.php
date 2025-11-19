<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class BatchEmployeeController extends Controller
{
    public function create()
    {
        $departments = Department::all();
        $roles = Role::active()->ordered()->get();
        $users = User::all();
        $currentUser = Auth::user();
        
        return view('users.batch-create-advanced', compact('departments', 'roles', 'users', 'currentUser'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'mapped_data' => 'required|string',
            'column_mapping' => 'required|string',
            'default_department_id' => 'nullable|exists:departments,id',
            'default_role_id' => 'nullable|exists:roles,id',
        ]);

        try {
            // Parse JSON data
            $mappedData = json_decode($request->mapped_data, true);
            $columnMapping = json_decode($request->column_mapping, true);
            
            if (!$mappedData || !$columnMapping) {
                return redirect()->back()->with('error', 'بيانات غير صحيحة');
            }

            // Validate required fields
            $requiredFields = ['name', 'email'];
            $missingFields = [];
            foreach ($requiredFields as $field) {
                if (!isset($columnMapping[$field]) || empty($columnMapping[$field])) {
                    $missingFields[] = $field;
                }
            }

            if (!empty($missingFields)) {
                return redirect()->back()->with('error', 'الحقول المطلوبة مفقودة: ' . implode(', ', $missingFields));
            }

            $createdUsers = [];
            $errors = [];

            foreach ($mappedData as $index => $userData) {
                try {
                    // Skip empty rows
                    if (empty(array_filter($userData))) {
                        continue;
                    }

                    // Apply defaults
                    if ($request->default_department_id && !isset($userData['department_id'])) {
                        $userData['department_id'] = $request->default_department_id;
                    }
                    
                    if ($request->default_role_id && !isset($userData['role_id'])) {
                        $userData['role_id'] = $request->default_role_id;
                    }

                    // Generate password if not provided
                    if (empty($userData['password'])) {
                        $userData['password'] = 'TempPass123!';
                    }

                    // Set Microsoft Teams ID to email if not provided
                    if (empty($userData['microsoft_teams_id']) && !empty($userData['email'])) {
                        $userData['microsoft_teams_id'] = $userData['email'];
                    }

                    // Create user
                    $user = User::create([
                        'name' => $userData['name'],
                        'name_ar' => $userData['name_ar'] ?? $userData['name'],
                        'email' => $userData['email'],
                        'password' => Hash::make($userData['password']),
                        'department_id' => $userData['department_id'] ?? null,
                        'role_id' => $userData['role_id'] ?? null,
                        'phone_work' => $userData['phone_work'] ?? null,
                        'phone_personal' => $userData['phone_personal'] ?? null,
                        'work_email' => $userData['work_email'] ?? $userData['email'],
                        'avaya_extension' => $userData['avaya_extension'] ?? null,
                        'microsoft_teams_id' => $userData['microsoft_teams_id'] ?? null,
                        'job_title' => $userData['job_title'] ?? null,
                        'position' => $userData['position'] ?? $userData['job_title'],
                        'position_ar' => $userData['position_ar'] ?? $userData['job_title'],
                        'manager_id' => $userData['manager_id'] ?? null,
                        'office_address' => $userData['office_address'] ?? null,
                        'address' => $userData['address'] ?? null,
                        'address_ar' => $userData['address_ar'] ?? $userData['address'],
                        'linkedin_url' => $userData['linkedin_url'] ?? null,
                        'website_url' => $userData['website_url'] ?? null,
                        'birthday' => $userData['birthday'] ?? null,
                        'birth_date' => $userData['birthday'] ?? $userData['birth_date'],
                        'bio' => $userData['bio'] ?? null,
                        'notes' => $userData['notes'] ?? null,
                        'created_by' => Auth::id(),
                    ]);

                    $createdUsers[] = $user;

                } catch (\Exception $e) {
                    $errors[] = "الصف " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            $message = "تم إنشاء " . count($createdUsers) . " موظف بنجاح";
            if (!empty($errors)) {
                $message .= ". الأخطاء: " . count($errors) . " صف";
            }

            return redirect()->route('users.index')->with('success', $message)->with('errors', $errors);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'حدث خطأ أثناء معالجة البيانات: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Define headers
        $headers = [
            'A1' => 'Name',
            'B1' => 'Name Arabic',
            'C1' => 'Email',
            'D1' => 'Phone Work',
            'E1' => 'Phone Personal',
            'F1' => 'Work Email',
            'G1' => 'Job Title',
            'H1' => 'Position',
            'I1' => 'Position Arabic',
            'J1' => 'Address',
            'K1' => 'Address Arabic',
            'L1' => 'Birthday',
            'M1' => 'Bio',
            'N1' => 'Notes',
            'O1' => 'LinkedIn URL',
            'P1' => 'Website URL',
            'Q1' => 'AVAYA Extension',
            'R1' => 'Microsoft Teams ID',
            'S1' => 'Office Address'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add sample data
        $sampleData = [
            ['A2' => 'John Doe', 'B2' => 'جون دو', 'C2' => 'john.doe@company.com'],
            ['A3' => 'Jane Smith', 'B3' => 'جين سميث', 'C3' => 'jane.smith@company.com'],
        ];

        foreach ($sampleData as $rowData) {
            foreach ($rowData as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
        }

        // Auto-size columns
        foreach (range('A', 'S') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'employee_template_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}
