<?php
/**
 * سكريبت استيراد بيانات الموظفين الحقيقية من ملف Excel
 * مع ضمان التعامل الصحيح مع النص العربي
 */

require_once 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Settings;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

// إعداد Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RealStaffImporter
{
    private $departments = [];
    private $roles = [];
    private $users = [];
    private $errors = [];
    private $successCount = 0;
    private $failedCount = 0;
    private $skippedCount = 0;

    public function __construct()
    {
        $this->loadReferenceData();
    }

    /**
     * تحميل البيانات المرجعية
     */
    private function loadReferenceData()
    {
        try {
            // تحميل الأقسام
            $this->departments = DB::table('departments')->get()->keyBy('name');
            
            // تحميل الأدوار
            $this->roles = DB::table('roles')->get()->keyBy('name');
            
            // تحميل المستخدمين
            $this->users = DB::table('users')->get()->keyBy('name');
            
            echo "✅ تم تحميل البيانات المرجعية:\n";
            echo "   - الأقسام: " . count($this->departments) . "\n";
            echo "   - الأدوار: " . count($this->roles) . "\n";
            echo "   - المستخدمين: " . count($this->users) . "\n\n";
            
        } catch (Exception $e) {
            echo "❌ خطأ في تحميل البيانات المرجعية: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * معالجة ملف Excel الحقيقي
     */
    public function processRealExcelFile($filePath)
    {
        try {
            echo "📁 معالجة ملف Excel الحقيقي: $filePath\n\n";
            
            // إعداد PhpSpreadsheet
            Settings::setLocale('ar');
            
            // قراءة الملف
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            
            // الحصول على البيانات
            $data = $worksheet->toArray();
            
            if (empty($data)) {
                throw new Exception('الملف فارغ');
            }

            // استخراج العناوين
            $headers = array_shift($data);
            $this->displayHeaders($headers);
            
            // معالجة البيانات
            $this->processRealData($data, $headers);
            
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
        echo "📋 العناوين المكتشفة:\n";
        foreach ($headers as $index => $header) {
            $cleanHeader = $this->cleanText($header);
            $encoding = mb_detect_encoding($cleanHeader);
            echo "   " . ($index + 1) . ". '$cleanHeader' (ترميز: $encoding)\n";
        }
        echo "\n";
    }

    /**
     * معالجة البيانات الحقيقية
     */
    private function processRealData($data, $headers)
    {
        $totalRows = count($data);
        echo "🔄 معالجة $totalRows صف من البيانات...\n\n";

        foreach ($data as $rowIndex => $row) {
            $rowNumber = $rowIndex + 2;
            
            // تخطي الصفوف الفارغة
            if (empty(array_filter($row))) {
                $this->skippedCount++;
                continue;
            }

            try {
                // تحويل الصف إلى مصفوفة مرتبطة
                $employeeData = [];
                foreach ($headers as $colIndex => $header) {
                    $cleanHeader = $this->cleanText($header);
                    $value = $row[$colIndex] ?? '';
                    $employeeData[$cleanHeader] = $this->cleanText($value);
                }

                // معالجة بيانات الموظف
                $this->processRealEmployee($employeeData, $rowNumber);
                
            } catch (Exception $e) {
                $this->errors[] = "الصف $rowNumber: " . $e->getMessage();
                $this->failedCount++;
            }
        }
    }

    /**
     * معالجة بيانات موظف حقيقي
     */
    private function processRealEmployee($data, $rowNumber)
    {
        echo "👤 معالجة الصف $rowNumber...\n";
        
        // البحث عن الحقول المطلوبة في العناوين المختلفة
        $name = $this->findField($data, ['name', 'الاسم', 'Name', 'NAME', 'English Name/ الاسم بالانجليزية', 'Arabic Name/ الاسم بالعربية']);
        $email = $this->findField($data, ['email', 'البريد الإلكتروني', 'Email', 'EMAIL', 'Work Email / ايميل العمل']);
        
        if (empty($name)) {
            throw new Exception("الاسم غير موجود");
        }
        
        if (empty($email)) {
            throw new Exception("البريد الإلكتروني غير موجود");
        }

        // تنظيف البيانات
        $cleanData = $this->cleanRealEmployeeData($data, $name, $email);
        
        // التحقق من عدم وجود المستخدم مسبقاً (تخطي هذا الفحص للسماح بالبريد المكرر)
        // $existingUser = DB::table('users')->where('email', $cleanData['email'])->first();
        // if ($existingUser) {
        //     echo "   ⚠️  المستخدم موجود مسبقاً: {$cleanData['email']}\n";
        //     $this->skippedCount++;
        //     return;
        // }

        // إنشاء المستخدم
        $this->createRealUser($cleanData, $rowNumber);
    }

    /**
     * البحث عن حقل في البيانات
     */
    private function findField($data, $possibleNames)
    {
        foreach ($possibleNames as $name) {
            if (isset($data[$name]) && !empty($data[$name])) {
                return $data[$name];
            }
        }
        return null;
    }

    /**
     * تنظيف بيانات الموظف الحقيقي
     */
    private function cleanRealEmployeeData($data, $name, $email)
    {
        $clean = [];
        
        // البيانات الأساسية
        $clean['name'] = $this->cleanText($name);
        $clean['name_ar'] = $this->cleanText($this->findField($data, ['name_ar', 'الاسم بالعربية', 'Name_AR', 'Arabic Name/ الاسم بالعربية']) ?: $name);
        $clean['email'] = strtolower(trim($email));
        $clean['work_email'] = strtolower(trim($this->findField($data, ['work_email', 'البريد الوظيفي', 'Work_Email', 'Work Email / ايميل العمل']) ?: $email));
        
        // أرقام الهواتف
        $clean['phone_work'] = $this->cleanPhoneNumber($this->findField($data, ['phone_work', 'هاتف العمل', 'Phone_Work', 'work_phone']));
        $clean['phone_personal'] = $this->cleanPhoneNumber($this->findField($data, ['phone_personal', 'الهاتف الشخصي', 'Phone_Personal', 'personal_phone']));
        
        // الوظيفة والمنصب
        $jobTitle = $this->findField($data, ['job_title', 'المسمى الوظيفي', 'Job_Title', 'position', 'المنصب', 'Position', 'Job/ الوظبفة']);
        $clean['job_title'] = $this->cleanText($jobTitle);
        $clean['position'] = $this->cleanText($jobTitle);
        $clean['position_ar'] = $this->cleanText($this->findField($data, ['position_ar', 'المنصب بالعربية', 'Position_AR']) ?: $jobTitle);
        
        // القسم
        $department = $this->findField($data, ['department', 'القسم', 'Department', 'dept', 'Organization/ القسم']);
        $clean['department_id'] = $this->resolveDepartment($department);
        
        // الدور
        $role = $this->findField($data, ['role', 'الدور', 'Role', 'position', 'Roles Template/ نموذج القواعد']);
        $clean['role_id'] = $this->resolveRole($role);
        
        // المدير
        $manager = $this->findField($data, ['manager', 'المدير', 'Manager', 'supervisor', 'Report To/ رئيس العمل']);
        $clean['manager_id'] = $this->resolveManager($manager);
        
        // العنوان
        $address = $this->findField($data, ['address', 'العنوان', 'Address', 'location', 'Governorate / المحافظة']);
        $clean['address'] = $this->cleanText($address);
        $clean['address_ar'] = $this->cleanText($this->findField($data, ['address_ar', 'العنوان بالعربية', 'Address_AR']) ?: $address);
        
        // تاريخ الميلاد
        $birthDate = $this->findField($data, ['birth_date', 'تاريخ الميلاد', 'Birth_Date', 'birthday', 'تاريخ_الميلاد', 'Birth Date / تاريخ الميلاد']);
        $clean['birth_date'] = $this->parseDate($birthDate);
        
        // معلومات إضافية
        $clean['bio'] = $this->cleanText($this->findField($data, ['bio', 'نبذة شخصية', 'Bio', 'description']));
        $clean['notes'] = $this->cleanText($this->findField($data, ['notes', 'ملاحظات', 'Notes', 'comments']));
        $clean['nationality'] = $this->cleanText($this->findField($data, ['nationality', 'الجنسية', 'Nationality', 'Nationality / الجنسية']));
        $clean['city'] = $this->cleanText($this->findField($data, ['city', 'المدينة', 'City', 'City/ المدينة']));
        $clean['country'] = $this->cleanText($this->findField($data, ['country', 'البلد', 'Country']));
        
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
     * إنشاء المستخدم الحقيقي
     */
    private function createRealUser($data, $rowNumber)
    {
        try {
            DB::beginTransaction();
            
            // التحقق من وجود المستخدم مسبقاً
            $existingUser = DB::table('users')->where('email', $data['email'])->first();
            
            if ($existingUser) {
                // تحديث المستخدم الموجود
                DB::table('users')
                    ->where('email', $data['email'])
                    ->update([
                        'name' => $data['name'],
                        'name_ar' => $data['name_ar'],
                        'work_email' => $data['work_email'],
                        'phone_work' => $data['phone_work'],
                        'phone_personal' => $data['phone_personal'],
                        'job_title' => $data['job_title'],
                        'department_id' => $data['department_id'],
                        'role_id' => $data['role_id'],
                        'manager_id' => $data['manager_id'],
                        'address' => $data['address'],
                        'birth_date' => $data['birth_date'],
                        'bio' => $data['bio'],
                        'notes' => $data['notes'],
                        'nationality' => $data['nationality'],
                        'city' => $data['city'],
                        'country' => $data['country'],
                        'microsoft_teams_id' => $data['microsoft_teams_id'],
                        'updated_at' => now(),
                    ]);
                
                echo "   🔄 تم تحديث المستخدم الموجود: {$data['name']} (ID: {$existingUser->id})\n";
                $this->successCount++;
            } else {
                // إنشاء مستخدم جديد
                $userId = DB::table('users')->insertGetId([
                    'name' => $data['name'],
                    'name_ar' => $data['name_ar'],
                    'email' => $data['email'],
                    'work_email' => $data['work_email'],
                    'password' => Hash::make($data['password']),
                    'phone_work' => $data['phone_work'],
                    'phone_personal' => $data['phone_personal'],
                    'job_title' => $data['job_title'],
                    'department_id' => $data['department_id'],
                    'role_id' => $data['role_id'],
                    'manager_id' => $data['manager_id'],
                    'address' => $data['address'],
                    'birth_date' => $data['birth_date'],
                    'bio' => $data['bio'],
                    'notes' => $data['notes'],
                    'nationality' => $data['nationality'],
                    'city' => $data['city'],
                    'country' => $data['country'],
                    'microsoft_teams_id' => $data['microsoft_teams_id'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                
                echo "   ✅ تم إنشاء المستخدم: {$data['name']} (ID: $userId)\n";
                $this->successCount++;
            }
            
            DB::commit();
            
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
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 نتائج الاستيراد النهائية\n";
        echo str_repeat("=", 60) . "\n";
        echo "✅ نجح: {$this->successCount}\n";
        echo "⚠️  تم تخطيه: {$this->skippedCount}\n";
        echo "❌ فشل: {$this->failedCount}\n";
        echo "📝 إجمالي: " . ($this->successCount + $this->skippedCount + $this->failedCount) . "\n\n";
        
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
$filePath = 'staff list 2025.xlsx';

if (!file_exists($filePath)) {
    echo "❌ الملف غير موجود: $filePath\n";
    echo "💡 تأكد من وجود الملف في نفس مجلد السكريبت\n";
    exit(1);
}

// تشغيل الاستيراد
$importer = new RealStaffImporter();
$importer->processRealExcelFile($filePath);
?>
