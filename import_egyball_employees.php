<?php
/**
 * سكريبت استيراد بيانات الموظفين من ملف Egyball 2025.xlsx
 * إضافة/تحديث المستخدمين بناءً على employee_id (emp_000)
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

class EgyballEmployeeImporter
{
    private $departments = [];
    private $roles = [];
    private $users = [];
    private $errors = [];
    private $successCount = 0;
    private $updatedCount = 0;
    private $failedCount = 0;
    private $skippedCount = 0;

    public function __construct()
    {
        $this->loadReferenceData();
    }

    /**
     * تحميل البيانات المرجعية من قاعدة البيانات
     */
    private function loadReferenceData()
    {
        try {
            // تحميل الأقسام
            $this->departments = DB::table('departments')->get()->keyBy(function($dept) {
                return strtolower(trim($dept->name));
            });
            
            // تحميل الأدوار
            $this->roles = DB::table('roles')->get()->keyBy(function($role) {
                return strtolower(trim($role->name));
            });
            
            echo "✅ تم تحميل البيانات المرجعية بنجاح\n";
            echo "   - الأقسام: " . count($this->departments) . "\n";
            echo "   - الأدوار: " . count($this->roles) . "\n\n";
            
        } catch (Exception $e) {
            echo "❌ خطأ في تحميل البيانات المرجعية: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * معالجة ملف Excel
     */
    public function processExcelFile($filePath)
    {
        try {
            echo "📁 بدء معالجة ملف Excel: $filePath\n\n";
            
            // إعداد PhpSpreadsheet للتعامل مع UTF-8
            Settings::setLocale('ar');
            
            // قراءة الملف
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // الحصول على البيانات
            $data = $worksheet->toArray();
            
            if (empty($data) || count($data) < 3) {
                throw new Exception('الملف فارغ أو لا يحتوي على بيانات صحيحة');
            }

            // تخطي الصف الأول والثاني (العناوين)
            // الصف 1: EgyBell
            // الصف 2: Code, Emp.Name, Position, Dep, Hiring Date, Employer
            // الصف 3+: البيانات
            
            echo "📋 عدد الصفوف في الملف: " . count($data) . "\n";
            echo "📋 بدء معالجة البيانات من الصف 3...\n\n";
            
            // معالجة البيانات
            $this->processData($data);
            
            // عرض النتائج
            $this->displayResults();
            
        } catch (Exception $e) {
            echo "❌ خطأ في معالجة الملف: " . $e->getMessage() . "\n";
            echo "   Stack trace: " . $e->getTraceAsString() . "\n";
            return false;
        }
        
        return true;
    }

    /**
     * معالجة البيانات
     */
    private function processData($data)
    {
        // تخطي الصف الأول والثاني (العناوين)
        for ($i = 2; $i < count($data); $i++) {
            $rowNumber = $i + 1; // رقم الصف في Excel
            
            $row = $data[$i];
            
            // تخطي الصفوف الفارغة
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                // استخراج البيانات من الصف
                // [0] => رقم متسلسل
                // [1] => Code (فارغ عادة)
                // [2] => Emp.Name
                // [3] => Position
                // [4] => Dep
                // [5] => Hiring Date
                // [6] => Employer
                
                $employeeData = [
                    'serial_number' => $this->cleanText($row[0] ?? ''),
                    'code' => $this->cleanText($row[1] ?? ''),
                    'name' => $this->cleanText($row[2] ?? ''),
                    'position' => $this->cleanText($row[3] ?? ''),
                    'department' => $this->cleanText($row[4] ?? ''),
                    'hiring_date' => $this->cleanText($row[5] ?? ''),
                    'employer' => $this->cleanText($row[6] ?? ''),
                ];

                // معالجة بيانات الموظف
                $this->processEmployee($employeeData, $rowNumber);
                
            } catch (Exception $e) {
                $this->errors[] = "الصف $rowNumber: " . $e->getMessage();
                $this->failedCount++;
                echo "   ❌ خطأ في الصف $rowNumber: " . $e->getMessage() . "\n";
            }
        }
    }

    /**
     * معالجة بيانات موظف واحد
     */
    private function processEmployee($data, $rowNumber)
    {
        echo "👤 معالجة الصف $rowNumber: {$data['name']}\n";
        
        // التحقق من البيانات المطلوبة
        if (empty($data['name'])) {
            throw new Exception("اسم الموظف فارغ");
        }

        // إنشاء employee_id من الرقم المتسلسل
        $employeeId = null;
        if (!empty($data['serial_number'])) {
            // تنسيق emp_000 + الرقم (مثل emp_0001, emp_0002)
            $serialNum = str_pad($data['serial_number'], 3, '0', STR_PAD_LEFT);
            $employeeId = 'emp_' . $serialNum;
        } else {
            throw new Exception("الرقم المتسلسل فارغ - لا يمكن إنشاء employee_id");
        }

        // تنظيف البيانات
        $cleanData = $this->cleanEmployeeData($data, $employeeId);
        
        // البحث عن المستخدم باستخدام employee_id
        $existingUser = DB::table('users')->where('employee_id', $employeeId)->first();
        
        if ($existingUser) {
            // تحديث المستخدم الموجود
            echo "   🔄 المستخدم موجود - تحديث: {$cleanData['name']} ($employeeId)\n";
            $this->updateUser($existingUser->id, $cleanData, $rowNumber);
            $this->updatedCount++;
        } else {
            // إنشاء مستخدم جديد
            echo "   ➕ إنشاء مستخدم جديد: {$cleanData['name']} ($employeeId)\n";
            $this->createUser($cleanData, $rowNumber);
            $this->successCount++;
        }
    }

    /**
     * تنظيف بيانات الموظف
     */
    private function cleanEmployeeData($data, $employeeId)
    {
        $clean = [];
        
        // البيانات الأساسية
        $clean['name'] = $this->cleanText($data['name'] ?? '');
        $clean['name_ar'] = $clean['name']; // استخدام نفس الاسم للعربي
        $clean['employee_id'] = $employeeId;
        
        // إنشاء email من الاسم و employee_id
        $clean['email'] = $this->generateEmail($clean['name'], $employeeId);
        $clean['work_email'] = $clean['email'];
        $clean['username'] = str_replace(' ', '_', strtolower($clean['name']));
        
        // الوظيفة والمنصب
        $clean['job_title'] = $this->cleanText($data['position'] ?? '');
        $clean['position'] = $clean['job_title'];
        $clean['position_ar'] = $clean['job_title'];
        
        // القسم
        $clean['department_id'] = $this->resolveDepartment($data['department'] ?? '');
        
        // الدور الافتراضي
        $clean['role_id'] = $this->resolveRole('employee'); // دور افتراضي
        
        // تاريخ التوظيف
        $clean['hire_date'] = $this->parseDate($data['hiring_date'] ?? '');
        
        // معلومات إضافية
        $clean['notes'] = 'Employer: ' . $this->cleanText($data['employer'] ?? '');
        
        // Microsoft Teams
        $clean['microsoft_teams_id'] = $clean['email'];
        
        // كلمة المرور الافتراضية
        $clean['password'] = 'TempPass123!';
        
        return $clean;
    }

    /**
     * إنشاء email من الاسم و employee_id
     */
    private function generateEmail($name, $employeeId)
    {
        // تنظيف الاسم للاستخدام في email
        $nameParts = explode(' ', trim($name));
        $firstName = strtolower($nameParts[0] ?? 'user');
        $lastName = strtolower($nameParts[count($nameParts) - 1] ?? '');
        
        // استخدام employee_id كجزء من email
        $email = $firstName . '.' . $lastName . '.' . $employeeId . '@egyball.local';
        
        // إزالة الأحرف الخاصة
        $email = preg_replace('/[^a-z0-9._@-]/', '', $email);
        
        return $email;
    }

    /**
     * تنظيف النص العربي
     */
    private function cleanText($text)
    {
        if (empty($text)) return '';
        
        // تحويل الترميز إلى UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'auto');
        
        // إزالة المسافات الزائدة
        $text = trim($text);
        
        // إزالة الأحرف غير المرغوب فيها
        $text = preg_replace('/[\x00-\x1F\x7F]/', '', $text);
        
        return $text;
    }

    /**
     * حل القسم
     */
    private function resolveDepartment($departmentName)
    {
        if (empty($departmentName)) return null;
        
        $cleanName = strtolower(trim($this->cleanText($departmentName)));
        
        // البحث المباشر
        foreach ($this->departments as $dept) {
            if (strtolower(trim($dept->name)) === $cleanName) {
                return $dept->id;
            }
        }
        
        // البحث الجزئي
        foreach ($this->departments as $dept) {
            $deptNameLower = strtolower(trim($dept->name));
            if (str_contains($deptNameLower, $cleanName) || str_contains($cleanName, $deptNameLower)) {
                echo "   ⚠️  تم العثور على قسم مشابه: {$dept->name}\n";
                return $dept->id;
            }
        }
        
        echo "   ⚠️  قسم غير موجود: $departmentName\n";
        return null;
    }

    /**
     * حل الدور
     */
    private function resolveRole($roleName)
    {
        if (empty($roleName)) return null;
        
        $cleanName = strtolower(trim($this->cleanText($roleName)));
        
        // البحث المباشر
        foreach ($this->roles as $role) {
            if (strtolower(trim($role->name)) === $cleanName) {
                return $role->id;
            }
        }
        
        // البحث الجزئي
        foreach ($this->roles as $role) {
            $roleNameLower = strtolower(trim($role->name));
            if (str_contains($roleNameLower, $cleanName) || str_contains($cleanName, $roleNameLower)) {
                return $role->id;
            }
        }
        
        // البحث عن دور "employee" أو "موظف"
        foreach ($this->roles as $role) {
            if (str_contains(strtolower($role->name), 'employee') || 
                str_contains(strtolower($role->name), 'موظف')) {
                return $role->id;
            }
        }
        
        echo "   ⚠️  دور غير موجود: $roleName - استخدام دور افتراضي\n";
        return null;
    }

    /**
     * تحليل التاريخ
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) return null;
        
        try {
            // محاولة تحليل التاريخ بصيغ مختلفة
            $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'Y/m/d', 'd-m-Y', 'm-d-Y'];
            
            foreach ($formats as $format) {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            }
            
            // محاولة strtotime
            $timestamp = strtotime($dateString);
            if ($timestamp) {
                return date('Y-m-d', $timestamp);
            }
            
        } catch (Exception $e) {
            echo "   ⚠️  خطأ في تحليل التاريخ: $dateString\n";
        }
        
        return null;
    }

    /**
     * إنشاء المستخدم
     */
    private function createUser($data, $rowNumber)
    {
        try {
            DB::beginTransaction();
            
            // التحقق من عدم وجود email مكرر
            $existingEmail = DB::table('users')->where('email', $data['email'])->first();
            if ($existingEmail) {
                // إضافة employee_id إلى email إذا كان مكرر
                $data['email'] = str_replace('@egyball.local', '.' . $data['employee_id'] . '@egyball.local', $data['email']);
                $data['work_email'] = $data['email'];
            }
            
            // البحث عن أول مستخدم موجود لاستخدامه كـ created_by
            $createdBy = DB::table('users')->value('id');
            if (!$createdBy) {
                $createdBy = null; // إذا لم يوجد أي مستخدم، استخدم null
            }
            
            $userId = DB::table('users')->insertGetId([
                'name' => $data['name'],
                'name_ar' => $data['name_ar'],
                'email' => $data['email'],
                'username' => $data['username'],
                'work_email' => $data['work_email'],
                'password' => Hash::make($data['password']),
                'employee_id' => $data['employee_id'],
                'job_title' => $data['job_title'],
                'position' => $data['position'],
                'position_ar' => $data['position_ar'],
                'department_id' => $data['department_id'],
                'role_id' => $data['role_id'],
                'hire_date' => $data['hire_date'],
                'microsoft_teams_id' => $data['microsoft_teams_id'],
                'notes' => $data['notes'],
                'created_by' => $createdBy,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            echo "   ✅ تم إنشاء المستخدم: {$data['name']} (ID: $userId, Employee ID: {$data['employee_id']})\n";
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("خطأ في إنشاء المستخدم: " . $e->getMessage());
        }
    }

    /**
     * تحديث المستخدم الموجود
     */
    private function updateUser($userId, $data, $rowNumber)
    {
        try {
            DB::beginTransaction();
            
            $updateData = [
                'name' => $data['name'],
                'name_ar' => $data['name_ar'],
                'job_title' => $data['job_title'],
                'position' => $data['position'],
                'position_ar' => $data['position_ar'],
                'department_id' => $data['department_id'],
                'role_id' => $data['role_id'],
                'hire_date' => $data['hire_date'],
                'notes' => $data['notes'],
                'updated_at' => now(),
            ];
            
            // تحديث email فقط إذا كان فارغًا
            $existingUser = DB::table('users')->where('id', $userId)->first();
            if (empty($existingUser->email)) {
                $updateData['email'] = $data['email'];
                $updateData['work_email'] = $data['work_email'];
            }
            
            DB::table('users')
                ->where('id', $userId)
                ->update($updateData);
            
            DB::commit();
            
            echo "   ✅ تم تحديث المستخدم: {$data['name']} (ID: $userId, Employee ID: {$data['employee_id']})\n";
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("خطأ في تحديث المستخدم: " . $e->getMessage());
        }
    }

    /**
     * عرض النتائج
     */
    private function displayResults()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 نتائج الاستيراد\n";
        echo str_repeat("=", 60) . "\n";
        echo "✅ تم إنشاء: {$this->successCount}\n";
        echo "🔄 تم التحديث: {$this->updatedCount}\n";
        echo "❌ فشل: {$this->failedCount}\n";
        echo "📝 إجمالي المعالجة: " . ($this->successCount + $this->updatedCount + $this->failedCount) . "\n\n";
        
        if (!empty($this->errors)) {
            echo "❌ الأخطاء:\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
            echo "\n";
        }
        
        echo "🎉 تم الانتهاء من عملية الاستيراد!\n";
    }
}

// تشغيل السكريبت
$filePath = 'Egyball 2025.xlsx';

if (!file_exists($filePath)) {
    echo "❌ الملف غير موجود: $filePath\n";
    exit(1);
}

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// تشغيل الاستيراد
$importer = new EgyballEmployeeImporter();
$importer->processExcelFile($filePath);
?>

