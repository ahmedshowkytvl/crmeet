<?php
/**
 * سكريبت استيراد بيانات الموظفين من ملف Excel
 * مع ضمان التعامل الصحيح مع النص العربي
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

class StaffExcelImporter
{
    private $departments = [];
    private $roles = [];
    private $users = [];
    private $errors = [];
    private $successCount = 0;
    private $failedCount = 0;

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
            $this->departments = DB::table('departments')->get()->keyBy('name');
            
            // تحميل الأدوار
            $this->roles = DB::table('roles')->get()->keyBy('name');
            
            // تحميل المستخدمين (للمديرين)
            $this->users = DB::table('users')->get()->keyBy('name');
            
            echo "✅ تم تحميل البيانات المرجعية بنجاح\n";
            echo "   - الأقسام: " . count($this->departments) . "\n";
            echo "   - الأدوار: " . count($this->roles) . "\n";
            echo "   - المستخدمين: " . count($this->users) . "\n\n";
            
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
            
            if (empty($data) || count($data) < 2) {
                throw new Exception('الملف فارغ أو لا يحتوي على بيانات صحيحة');
            }

            // استخراج العناوين
            $headers = array_shift($data);
            $this->displayHeaders($headers);
            
            // معالجة البيانات
            $this->processData($data, $headers);
            
            // عرض النتائج
            $this->displayResults();
            
        } catch (Exception $e) {
            echo "❌ خطأ في معالجة الملف: " . $e->getMessage() . "\n";
            return false;
        }
        
        return true;
    }

    /**
     * عرض العناوين المكتشفة
     */
    private function displayHeaders($headers)
    {
        echo "📋 العناوين المكتشفة في الملف:\n";
        foreach ($headers as $index => $header) {
            $cleanHeader = $this->cleanText($header);
            echo "   " . ($index + 1) . ". $cleanHeader\n";
        }
        echo "\n";
    }

    /**
     * معالجة البيانات
     */
    private function processData($data, $headers)
    {
        $totalRows = count($data);
        echo "🔄 معالجة $totalRows صف من البيانات...\n\n";

        foreach ($data as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2; // +2 لأننا بدأنا من الصف الثاني
            
            // تخطي الصفوف الفارغة
            if (empty(array_filter($row))) {
                continue;
            }

            try {
                // تحويل الصف إلى مصفوفة مرتبطة
                $employeeData = [];
                foreach ($headers as $colIndex => $header) {
                    $cleanHeader = $this->cleanText($header);
                    $employeeData[$cleanHeader] = $this->cleanText($row[$colIndex] ?? '');
                }

                // معالجة بيانات الموظف
                $this->processEmployee($employeeData, $rowNumber);
                
            } catch (Exception $e) {
                $this->errors[] = "الصف $rowNumber: " . $e->getMessage();
                $this->failedCount++;
            }
        }
    }

    /**
     * معالجة بيانات موظف واحد
     */
    private function processEmployee($data, $rowNumber)
    {
        echo "👤 معالجة الصف $rowNumber...\n";
        
        // التحقق من البيانات المطلوبة
        $requiredFields = ['name', 'email'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw new Exception("الحقل المطلوب '$field' فارغ");
            }
        }

        // تنظيف البيانات
        $cleanData = $this->cleanEmployeeData($data);
        
        // التحقق من عدم وجود المستخدم مسبقاً
        $existingUser = DB::table('users')->where('email', $cleanData['email'])->first();
        if ($existingUser) {
            echo "   ⚠️  المستخدم موجود مسبقاً: {$cleanData['email']}\n";
            return;
        }

        // إنشاء المستخدم
        $this->createUser($cleanData, $rowNumber);
    }

    /**
     * تنظيف بيانات الموظف
     */
    private function cleanEmployeeData($data)
    {
        $clean = [];
        
        // البيانات الأساسية
        $clean['name'] = $this->cleanText($data['name'] ?? '');
        $clean['name_ar'] = $this->cleanText($data['name_ar'] ?? $data['name_arabic'] ?? $clean['name']);
        $clean['email'] = strtolower(trim($data['email'] ?? ''));
        $clean['work_email'] = strtolower(trim($data['work_email'] ?? $clean['email']));
        
        // أرقام الهواتف
        $clean['phone_work'] = $this->cleanPhoneNumber($data['phone_work'] ?? $data['work_phone'] ?? '');
        $clean['phone_personal'] = $this->cleanPhoneNumber($data['phone_personal'] ?? $data['personal_phone'] ?? '');
        
        // الوظيفة والمنصب
        $clean['job_title'] = $this->cleanText($data['job_title'] ?? $data['position'] ?? '');
        $clean['position'] = $this->cleanText($data['position'] ?? $clean['job_title']);
        $clean['position_ar'] = $this->cleanText($data['position_ar'] ?? $clean['position']);
        
        // القسم
        $clean['department_id'] = $this->resolveDepartment($data['department'] ?? '');
        
        // الدور
        $clean['role_id'] = $this->resolveRole($data['role'] ?? '');
        
        // المدير
        $clean['manager_id'] = $this->resolveManager($data['manager'] ?? '');
        
        // العنوان
        $clean['address'] = $this->cleanText($data['address'] ?? '');
        $clean['address_ar'] = $this->cleanText($data['address_ar'] ?? $clean['address']);
        
        // تاريخ الميلاد
        $clean['birth_date'] = $this->parseDate($data['birth_date'] ?? $data['birthday'] ?? '');
        
        // معلومات إضافية
        $clean['bio'] = $this->cleanText($data['bio'] ?? '');
        $clean['notes'] = $this->cleanText($data['notes'] ?? '');
        $clean['nationality'] = $this->cleanText($data['nationality'] ?? '');
        $clean['city'] = $this->cleanText($data['city'] ?? '');
        $clean['country'] = $this->cleanText($data['country'] ?? '');
        
        // Microsoft Teams
        $clean['microsoft_teams_id'] = $clean['email'];
        
        // كلمة المرور الافتراضية
        $clean['password'] = 'TempPass123!';
        
        return $clean;
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
     * تنظيف رقم الهاتف
     */
    private function cleanPhoneNumber($phone)
    {
        if (empty($phone)) return null;
        
        // إزالة جميع الأحرف غير الرقمية
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        
        // إضافة +20 للمصرية إذا لم تكن موجودة
        if (strlen($phone) == 10 && !str_starts_with($phone, '+')) {
            $phone = '+20' . $phone;
        }
        
        return $phone;
    }

    /**
     * حل القسم
     */
    private function resolveDepartment($departmentName)
    {
        if (empty($departmentName)) return null;
        
        $cleanName = $this->cleanText($departmentName);
        
        // البحث المباشر
        if (isset($this->departments[$cleanName])) {
            return $this->departments[$cleanName]->id;
        }
        
        // البحث الجزئي
        foreach ($this->departments as $dept) {
            if (str_contains($dept->name, $cleanName) || str_contains($cleanName, $dept->name)) {
                return $dept->id;
            }
        }
        
        echo "   ⚠️  قسم غير موجود: $cleanName\n";
        return null;
    }

    /**
     * حل الدور
     */
    private function resolveRole($roleName)
    {
        if (empty($roleName)) return null;
        
        $cleanName = $this->cleanText($roleName);
        
        // البحث المباشر
        if (isset($this->roles[$cleanName])) {
            return $this->roles[$cleanName]->id;
        }
        
        // البحث الجزئي
        foreach ($this->roles as $role) {
            if (str_contains($role->name, $cleanName) || str_contains($cleanName, $role->name)) {
                return $role->id;
            }
        }
        
        echo "   ⚠️  دور غير موجود: $cleanName\n";
        return null;
    }

    /**
     * حل المدير
     */
    private function resolveManager($managerName)
    {
        if (empty($managerName)) return null;
        
        $cleanName = $this->cleanText($managerName);
        
        // البحث المباشر
        if (isset($this->users[$cleanName])) {
            return $this->users[$cleanName]->id;
        }
        
        // البحث الجزئي
        foreach ($this->users as $user) {
            if (str_contains($user->name, $cleanName) || str_contains($cleanName, $user->name)) {
                return $user->id;
            }
        }
        
        echo "   ⚠️  مدير غير موجود: $cleanName\n";
        return null;
    }

    /**
     * تحليل التاريخ
     */
    private function parseDate($dateString)
    {
        if (empty($dateString)) return null;
        
        try {
            // محاولة تحليل التاريخ
            $date = \DateTime::createFromFormat('Y-m-d', $dateString);
            if ($date) return $date->format('Y-m-d');
            
            $date = \DateTime::createFromFormat('d/m/Y', $dateString);
            if ($date) return $date->format('Y-m-d');
            
            $date = \DateTime::createFromFormat('m/d/Y', $dateString);
            if ($date) return $date->format('Y-m-d');
            
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
            
            $userId = DB::table('users')->insertGetId([
                'name' => $data['name'],
                'name_ar' => $data['name_ar'],
                'email' => $data['email'],
                'work_email' => $data['work_email'],
                'password' => Hash::make($data['password']),
                'phone_work' => $data['phone_work'],
                'phone_personal' => $data['phone_personal'],
                'job_title' => $data['job_title'],
                'position' => $data['position'],
                'position_ar' => $data['position_ar'],
                'department_id' => $data['department_id'],
                'role_id' => $data['role_id'],
                'manager_id' => $data['manager_id'],
                'address' => $data['address'],
                'address_ar' => $data['address_ar'],
                'birth_date' => $data['birth_date'],
                'bio' => $data['bio'],
                'notes' => $data['notes'],
                'nationality' => $data['nationality'],
                'city' => $data['city'],
                'country' => $data['country'],
                'microsoft_teams_id' => $data['microsoft_teams_id'],
                'created_by' => 1, // System Administrator
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            echo "   ✅ تم إنشاء المستخدم: {$data['name']} (ID: $userId)\n";
            $this->successCount++;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw new Exception("خطأ في إنشاء المستخدم: " . $e->getMessage());
        }
    }

    /**
     * عرض النتائج
     */
    private function displayResults()
    {
        echo "\n" . str_repeat("=", 50) . "\n";
        echo "📊 نتائج الاستيراد\n";
        echo str_repeat("=", 50) . "\n";
        echo "✅ نجح: {$this->successCount}\n";
        echo "❌ فشل: {$this->failedCount}\n";
        echo "📝 إجمالي: " . ($this->successCount + $this->failedCount) . "\n\n";
        
        if (!empty($this->errors)) {
            echo "❌ الأخطاء:\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
        }
        
        echo "\n🎉 تم الانتهاء من عملية الاستيراد!\n";
    }
}

// تشغيل السكريبت
if ($argc < 2) {
    echo "استخدام: php import_staff_excel.php <مسار_ملف_Excel>\n";
    echo "مثال: php import_staff_excel.php 'staff list 2025.xlsx'\n";
    exit(1);
}

$filePath = $argv[1];

if (!file_exists($filePath)) {
    echo "❌ الملف غير موجود: $filePath\n";
    exit(1);
}

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// تشغيل الاستيراد
$importer = new StaffExcelImporter();
$importer->processExcelFile($filePath);
?>
