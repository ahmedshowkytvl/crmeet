<?php

namespace App\Services;

use App\Models\User;
use App\Models\Department;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class EmployeeDataService
{
    protected $dateParser;

    public function __construct(DateParserService $dateParser)
    {
        $this->dateParser = $dateParser;
    }

    /**
     * إنشاء موظف واحد
     */
    public function createEmployee($employeeData, $defaultValues, $rowNumber)
    {
        try {
            $cleanData = $this->cleanEmployeeData($employeeData, $defaultValues);
            $validation = $this->validateEmployeeData($cleanData);
            
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error'],
                    'row' => $employeeData['_row_number'] ?? $rowNumber
                ];
            }

            // التحقق من البريد الإلكتروني المكرر فقط إذا لم يتم السماح بذلك
            $allowDuplicateEmails = $defaultValues['allowDuplicateEmails'] ?? false;
            if (!$allowDuplicateEmails && User::where('email', $cleanData['email'])->exists()) {
                return [
                    'success' => false,
                    'error' => 'البريد الإلكتروني مستخدم مسبقاً',
                    'row' => $employeeData['_row_number'] ?? $rowNumber
                ];
            }
            
            // في حالة السماح بالتكرار، يمكن تعديل البريد الإلكتروني لتجنب التضارب
            $originalEmail = null;
            if ($allowDuplicateEmails && User::where('email', $cleanData['email'])->exists()) {
                // إضافة timestamp + رقم عشوائي للبريد الإلكتروني المكرر لتجنب التضارب
                $originalEmail = $cleanData['email'];
                $timestamp = time();
                $randomNumber = rand(1000, 9999); // رقم عشوائي من 4 أرقام
                $parts = explode('@', $originalEmail);
                $cleanData['email'] = $parts[0] . '+' . $timestamp . $randomNumber . '@' . $parts[1];
                
                // التحقق من عدم تكرار البريد المعدل أيضاً
                while (User::where('email', $cleanData['email'])->exists()) {
                    $randomNumber = rand(1000, 9999);
                    $cleanData['email'] = $parts[0] . '+' . $timestamp . $randomNumber . '@' . $parts[1];
                }
                
                // تسجيل التعديل في السجل
                Log::info("تم تعديل البريد الإلكتروني المكرر من {$originalEmail} إلى {$cleanData['email']} في الصف {$rowNumber}");
            }

            // التحقق من تكرار كود الموظف (employee_id) - لا يُسمح بالتكرار أبداً
            if (!empty($cleanData['employee_id']) && $cleanData['employee_id'] !== '' && User::where('employee_id', $cleanData['employee_id'])->exists()) {
                return [
                    'success' => false,
                    'error' => 'كود الموظف (employee_id) مستخدم مسبقاً - لا يُسمح بالتكرار',
                    'row' => $employeeData['_row_number'] ?? $rowNumber
                ];
            }

            // التحقق من تكرار كود الموظف (EmployeeCode) - لا يُسمح بالتكرار أبداً
            if (!empty($cleanData['EmployeeCode']) && $cleanData['EmployeeCode'] !== '' && User::where('EmployeeCode', $cleanData['EmployeeCode'])->exists()) {
                return [
                    'success' => false,
                    'error' => 'كود الموظف (.emp code) مستخدم مسبقاً - لا يُسمح بالتكرار',
                    'row' => $employeeData['_row_number'] ?? $rowNumber
                ];
            }

            $user = $this->createUserRecord($cleanData);

            return [
                'success' => true,
                'employee' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone_work
                ],
                'row' => $employeeData['_row_number'] ?? $rowNumber,
                'email_modified' => $allowDuplicateEmails && $originalEmail !== null && $originalEmail !== $cleanData['email'],
                'original_email' => $originalEmail
            ];

        } catch (\Exception $e) {
            Log::error("خطأ في إنشاء الموظف في الصف {$rowNumber}: " . $e->getMessage());
            
            return [
                'success' => false,
                'error' => 'خطأ في إنشاء الموظف: ' . $e->getMessage(),
                'row' => $employeeData['_row_number'] ?? $rowNumber
            ];
        }
    }

    /**
     * تنظيف بيانات الموظف
     */
    private function cleanEmployeeData($employeeData, $defaultValues)
    {
        $cleaned = [];
        
        // البيانات الأساسية
        $cleaned['name'] = trim($employeeData['name'] ?? '');
        $cleaned['email'] = trim(strtolower($employeeData['email'] ?? ''));
        $cleaned['employee_id'] = !empty($employeeData['employee_id']) ? trim($employeeData['employee_id']) : 
                                 (!empty($employeeData['employee_code']) ? trim($employeeData['employee_code']) : 
                                 (!empty($employeeData['employee_number']) ? trim($employeeData['employee_number']) : null));
        $cleaned['EmployeeCode'] = !empty($employeeData['EmployeeCode']) ? trim($employeeData['EmployeeCode']) : 
                                  (!empty($employeeData['emp_code']) ? trim($employeeData['emp_code']) : 
                                  (!empty($employeeData['empcode']) ? trim($employeeData['empcode']) : null));
        
        // معالجة أرقام الهاتف - استخدام work_phone أو mobile_phone
        $phoneValue = $employeeData['work_phone'] ?? 
                     $employeeData['mobile_phone'] ?? 
                     $employeeData['phone'] ?? 
                     null;
        $cleaned['phone'] = !empty($phoneValue) ? $this->cleanPhoneNumber($phoneValue) : null;
        
        // معالجة كلمة المرور
        $cleaned['password'] = $employeeData['password'] ?? 'TempPass123!';
        
        // $cleaned['position'] = trim($employeeData['job_title'] ?? $employeeData['position'] ?? $defaultValues['position'] ?? '');
        $cleaned['address'] = !empty($employeeData['address']) ? trim($employeeData['address']) : null;
        $cleaned['notes'] = !empty($employeeData['notes']) ? trim($employeeData['notes']) : null;
        
        // معالجة التواريخ
        $cleaned['hiring_date'] = $this->dateParser->parseDate($employeeData['hiring_date'] ?? null);
        
        // معالجة القسم
        $cleaned['department_id'] = $this->resolveDepartment(
            $employeeData['department'] ?? null, 
            $defaultValues['department_id'] ?? null
        );
        
        // معالجة الدور
        $cleaned['role_id'] = $this->resolveRole(
            $employeeData['role'] ?? null, 
            $defaultValues['role_id'] ?? null
        );
        
        // أرقام الهاتف الإضافية
        $cleaned['phone_home'] = !empty($employeeData['phone_home']) ? $this->cleanPhoneNumber($employeeData['phone_home']) : null;
        $cleaned['phone_personal'] = !empty($employeeData['phone_personal']) ? $this->cleanPhoneNumber($employeeData['phone_personal']) : null;
        
        // الحقول الجديدة
        $cleaned['name_arabic'] = !empty($employeeData['name_arabic']) ? trim($employeeData['name_arabic']) : 
                                 (!empty($employeeData['name_ar']) ? trim($employeeData['name_ar']) : null);
        $cleaned['nationality'] = !empty($employeeData['nationality']) ? trim($employeeData['nationality']) : null;
        $cleaned['city'] = !empty($employeeData['city']) ? trim($employeeData['city']) : null;
        $cleaned['country'] = !empty($employeeData['country']) ? trim($employeeData['country']) : null;
        $cleaned['birth_date'] = $this->dateParser->parseDate($employeeData['birth_date'] ?? $employeeData['birthday'] ?? null);
        $cleaned['bio'] = !empty($employeeData['bio']) ? trim($employeeData['bio']) : null;
        
        // الترجمة العربية
        $cleaned['name_ar'] = !empty($cleaned['name_arabic']) ? $cleaned['name_arabic'] : $cleaned['name'];
        // $cleaned['position_ar'] = $cleaned['position'];
        $cleaned['address_ar'] = $cleaned['address'];
        
        return $cleaned;
    }

    /**
     * إنشاء سجل المستخدم
     */
    private function createUserRecord($cleanData)
    {
        return User::create([
            'name' => $cleanData['name'],
            'name_ar' => !empty($cleanData['name_ar']) ? $cleanData['name_ar'] : $cleanData['name'],
            'email' => $cleanData['email'],
            'password' => Hash::make($cleanData['password']),
            'employee_id' => !empty($cleanData['employee_id']) ? $cleanData['employee_id'] : null,
            'EmployeeCode' => !empty($cleanData['EmployeeCode']) ? $cleanData['EmployeeCode'] : null,
            'phone_work' => !empty($cleanData['phone']) ? $cleanData['phone'] : null,
            'phone_home' => !empty($cleanData['phone_home']) ? $cleanData['phone_home'] : null,
            'phone_personal' => !empty($cleanData['phone_personal']) ? $cleanData['phone_personal'] : null,
            'phone_mobile' => !empty($cleanData['phone']) ? $cleanData['phone'] : null,
            'department_id' => !empty($cleanData['department_id']) ? $cleanData['department_id'] : null,
            'address' => !empty($cleanData['address']) ? $cleanData['address'] : null,
            'role_id' => $cleanData['role_id'] ?? $this->getDefaultRoleId(),
            'hiring_date' => $cleanData['hiring_date'] ?? now(),
            'hire_date' => $cleanData['hiring_date'] ?? now(),
            'birth_date' => !empty($cleanData['birth_date']) ? $cleanData['birth_date'] : null,
            'nationality' => !empty($cleanData['nationality']) ? $cleanData['nationality'] : null,
            'city' => !empty($cleanData['city']) ? $cleanData['city'] : null,
            'country' => !empty($cleanData['country']) ? $cleanData['country'] : null,
            'bio' => !empty($cleanData['bio']) ? $cleanData['bio'] : null,
            'notes' => !empty($cleanData['notes']) ? $cleanData['notes'] : null
        ]);
    }

    /**
     * حل الدور
     */
    private function resolveRole($roleName, $defaultRoleId = null)
    {
        if ($defaultRoleId) {
            return $defaultRoleId;
        }
        
        if (empty($roleName) || $roleName === '') {
            return $this->getDefaultRoleId();
        }
        
        // البحث عن الدور بالاسم
        $role = \App\Models\Role::where('name', 'like', '%' . $roleName . '%')
            ->orWhere('name_ar', 'like', '%' . $roleName . '%')
            ->first();
            
        return $role ? $role->id : $this->getDefaultRoleId();
    }

    /**
     * تنظيف رقم الهاتف
     */
    private function cleanPhoneNumber($phone)
    {
        if (empty($phone) || $phone === '') return null;
        
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        if (empty($phone) || $phone === '') return null;
        
        if (strlen($phone) == 9 && !str_starts_with($phone, '966')) {
            $phone = '966' . $phone;
        }
        
        return $phone;
    }

    /**
     * حل القسم
     */
    private function resolveDepartment($departmentName, $defaultDepartmentId)
    {
        if (!empty($departmentName) && $departmentName !== '') {
            $department = Department::where('name', 'like', "%{$departmentName}%")
                ->orWhere('name_ar', 'like', "%{$departmentName}%")
                ->first();
            
            if ($department) {
                return $department->id;
            }
        }
        
        return $defaultDepartmentId;
    }

    /**
     * الحصول على معرف الدور الافتراضي
     */
    private function getDefaultRoleId()
    {
        $defaultRole = Role::where('slug', 'employee')->first();
        return $defaultRole ? $defaultRole->id : 1;
    }

    /**
     * التحقق من صحة بيانات الموظف
     */
    private function validateEmployeeData($data)
    {
        if (empty($data['name'])) {
            return ['valid' => false, 'error' => 'الاسم مطلوب'];
        }
        
        if (empty($data['email'])) {
            return ['valid' => false, 'error' => 'البريد الإلكتروني مطلوب'];
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['valid' => false, 'error' => 'البريد الإلكتروني غير صحيح'];
        }
        
        // Phone is optional now
        // if (empty($data['phone'])) {
        //     return ['valid' => false, 'error' => 'رقم الهاتف مطلوب'];
        // }
        
        if (strlen($data['name']) > 255) {
            return ['valid' => false, 'error' => 'الاسم طويل جداً (أكثر من 255 حرف)'];
        }
        
        if (strlen($data['email']) > 255) {
            return ['valid' => false, 'error' => 'البريد الإلكتروني طويل جداً'];
        }
        
        if (!empty($data['hiring_date'])) {
            $dateValidation = $this->dateParser->validateDate($data['hiring_date']);
            if (!$dateValidation['valid']) {
                return ['valid' => false, 'error' => 'تاريخ التعيين غير صحيح: ' . $dateValidation['error']];
            }
        }
        
        return ['valid' => true];
    }
}
