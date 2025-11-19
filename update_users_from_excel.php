<?php
/**
 * سكريبت تحديث بيانات المستخدمين من ملف Excel
 * - يحدث المستخدمين الموجودين فقط
 * - يضيف البيانات المفقودة
 * - لا ينشئ مستخدمين جدد
 */

require_once __DIR__ . '/vendor/autoload.php';

// إعداد Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Department;
use App\Models\UserPhone;
use App\Models\PhoneType;

// إعداد الترميز للنص العربي
mb_internal_encoding('UTF-8');
ini_set('default_charset', 'UTF-8');

class UsersExcelUpdater
{
    private $departments = [];
    private $phoneTypes = [];
    private $errors = [];
    private $successCount = 0;
    private $updatedCount = 0;
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
            $this->departments = Department::all()->keyBy(function($dept) {
                return strtolower(trim($dept->name ?? ''));
            });
            
            // تحميل نوع الهاتف "work"
            $workPhoneType = PhoneType::firstOrCreate(
                ['slug' => 'work'],
                ['name' => 'Work', 'name_ar' => 'عمل', 'is_active' => true, 'sort_order' => 1]
            );
            $this->phoneTypes['work'] = $workPhoneType;
            
            echo "✅ تم تحميل البيانات المرجعية:\n";
            echo "   - الأقسام: " . $this->departments->count() . "\n";
            echo "   - نوع الهاتف (عمل): موجود\n\n";
            
        } catch (Exception $e) {
            echo "❌ خطأ في تحميل البيانات المرجعية: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * تنظيف النص من المسافات الزائدة والأحرف غير المرغوب فيها
     */
    private function cleanText($text)
    {
        if (empty($text)) {
            return null;
        }
        
        $text = trim($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return $text ?: null;
    }

    /**
     * تحويل الأسماء الإنجليزية إلى العربية
     */
    private function convertEnglishToArabic($englishName)
    {
        $conversions = [
            'ahmed' => 'أحمد',
            'mohamed' => 'محمد',
            'mahmoud' => 'محمود',
            'ali' => 'علي',
            'hassan' => 'حسن',
            'hussain' => 'حسين',
            'saeed' => 'سعيد',
            'omar' => 'عمر',
            'youssef' => 'يوسف',
            'karim' => 'كريم',
            'amr' => 'عمرو',
            'yasser' => 'ياسر',
            'ashraf' => 'أشرف',
            'emad' => 'عماد',
            'salah' => 'صلاح',
            'tarek' => 'طارق',
            'gamal' => 'جمال',
            'alaa' => 'علاء',
            'hani' => 'هاني',
            'wael' => 'وائل',
            'nader' => 'نادر',
            'mostafa' => 'مصطفى',
            'khaled' => 'خالد',
            'ibrahim' => 'إبراهيم',
            'osama' => 'أسامة',
            'walid' => 'وليد',
            'sameh' => 'سامح',
            'rami' => 'رامي',
            'adel' => 'عادل',
            'farouk' => 'فاروق',
            'hanan' => 'حنان',
            'heba' => 'هبة',
            'nour' => 'نور',
            'mai' => 'مي',
            'aya' => 'آية',
            'salma' => 'سلمى',
            'radwa' => 'راضية',
            'mariam' => 'مريم',
            'sarah' => 'سارة',
            'fatma' => 'فاطمة',
            'eman' => 'إيمان',
            'hind' => 'هند',
            'rawan' => 'روان',
            'yasmin' => 'ياسمين',
            'amira' => 'أميرة',
            'alia' => 'علياء',
            'hager' => 'هاجر',
            'tasneem' => 'تسنيم',
            'shaimaa' => 'شيماء',
            'wafaa' => 'وفاء',
            'rania' => 'رانيا',
            'reham' => 'رحام',
            'mousad' => 'مسعد',
            'essam' => 'عصام',
            'abdel' => 'عبد',
            'abd' => 'عبد',
            'el' => 'ال',
            'sayeed' => 'سيد',
            'sayed' => 'سيد',
            'soliman' => 'سليمان',
            'solaiman' => 'سليمان',
            'anwar' => 'أنور',
            'morsi' => 'مرسي',
            'nazmi' => 'نظمي',
            'naguib' => 'نجيب',
            'osman' => 'عثمان',
            'toukhy' => 'توقي',
            'fathy' => 'فتحي',
            'mohsen' => 'محسن',
            'ghany' => 'غني',
            'maged' => 'ماجد',
            'ezzat' => 'عزت',
            'hal' => 'حال',
            'swilam' => 'سويلم',
            'saad' => 'سعد',
            'el-sayed' => 'السيد',
            'refai' => 'رفاعي',
            'elrefai' => 'الرفاعي',
            'madbouly' => 'مدبولي',
            'shafie' => 'شافعي',
            'labib' => 'لبيب',
            'fahmy' => 'فهمي',
            'motelab' => 'مطلب',
            'nazmi' => 'نظمي',
            'abd el' => 'عبد ال',
            'abd el mohsen' => 'عبد المحسن',
            'abd el gawad' => 'عبد الجواد',
        ];
        
        // تقسيم الاسم إلى كلمات
        $words = explode(' ', strtolower($englishName));
        $arabicWords = [];
        
        foreach ($words as $word) {
            $converted = false;
            foreach ($conversions as $english => $arabic) {
                if (stripos($word, $english) !== false) {
                    $arabicWords[] = str_ireplace($english, $arabic, $word);
                    $converted = true;
                    break;
                }
            }
            if (!$converted && strlen($word) > 2) {
                // إذا لم نجد ترجمة، نترك الكلمة كما هي (قد تكون اسم عائلة)
                $arabicWords[] = $word;
            }
        }
        
        // دمج الكلمات المترجمة مع الكلمات الأصلية
        $result = implode(' ', $arabicWords);
        
        // إذا لم يتم التحويل، نستخدم الاسم الإنجليزي
        if (empty($result) || $result == strtolower($englishName)) {
            return $englishName;
        }
        
        return $result;
    }

    /**
     * البحث عن قسم بالاسم (مع مراعاة الاختلافات)
     */
    private function findDepartment($departmentName)
    {
        if (empty($departmentName)) {
            return null;
        }
        
        $searchName = strtolower(trim($departmentName));
        
        // البحث المباشر
        $dept = $this->departments->get($searchName);
        if ($dept) {
            return $dept;
        }
        
        // البحث الجزئي
        foreach ($this->departments as $dept) {
            if (stripos($dept->name ?? '', $departmentName) !== false || 
                stripos($departmentName, $dept->name ?? '') !== false) {
                return $dept;
            }
        }
        
        return null;
    }

    /**
     * البحث عن مستخدم بالإيميل أو Employee ID
     */
    private function findUser($email, $employeeId = null)
    {
        if (empty($email)) {
            return null;
        }
        
        // البحث بالإيميل أولاً
        $user = User::where('email', $email)->first();
        if ($user) {
            return $user;
        }
        
        // البحث بـ Employee ID
        if (!empty($employeeId)) {
            $user = User::where('employee_id', $employeeId)->first();
            if ($user) {
                return $user;
            }
        }
        
        return null;
    }

    /**
     * استخراج أول إيميل من سلسلة إيميلات
     */
    private function extractFirstEmail($emailString)
    {
        if (empty($emailString)) {
            return null;
        }
        
        // تقسيم الإيميلات بالسطر الجديد أو المسافة
        $emails = preg_split('/[\r\n\s]+/', trim($emailString));
        foreach ($emails as $email) {
            $email = trim($email);
            if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return strtolower($email);
            }
        }
        
        return null;
    }

    /**
     * معالجة بيانات موظف واحد
     */
    private function processEmployee($data, $rowNumber)
    {
        // استخراج الإيميل (أول إيميل في القائمة إذا كانت متعددة)
        $emailString = $data['Email'] ?? $data['email'] ?? null;
        $email = $this->extractFirstEmail($emailString);
        
        // البحث عن المستخدم باستخدام Code (Employee ID)
        $code = $this->cleanText($data['Code'] ?? null);
        
        // البحث عن المستخدم
        $user = null;
        if (!empty($email)) {
            $user = $this->findUser($email, $code);
        }
        
        // إذا لم نجده بالإيميل، نبحث بـ Code فقط
        if (!$user && !empty($code)) {
            $user = User::where('employee_id', $code)->first();
        }
        
        // إذا لم نجده بـ Code، نبحث بالاسم
        if (!$user && !empty($data['Emp. Name'])) {
            $name = $this->cleanText($data['Emp. Name']);
            $user = User::where('name', 'LIKE', "%{$name}%")->first();
        }

        if (!$user) {
            echo "⚠️  الصف $rowNumber: المستخدم غير موجود ({$email}) - تم التخطي (لا إنشاء مستخدمين جدد)\n";
            $this->skippedCount++;
            return;
        }

        echo "🔄 الصف $rowNumber: تحديث المستخدم {$user->name} ({$email})\n";
        
        $updated = false;
        $updates = [];

        // تحديث Position (job_title)
        $position = $this->cleanText($data['Position'] ?? null);
        if (!empty($position) && ($user->job_title != $position)) {
            $updates['job_title'] = $position;
            $updated = true;
            echo "   ✓ المسمى الوظيفي: {$position}\n";
        }

        // تحديث Department
        $departmentName = $this->cleanText($data['Department'] ?? $data['Departme'] ?? null);
        if (!empty($departmentName)) {
            $department = $this->findDepartment($departmentName);
            if ($department && $user->department_id != $department->id) {
                $updates['department_id'] = $department->id;
                $updated = true;
                echo "   ✓ القسم: {$department->name}\n";
            } elseif (!$department) {
                echo "   ⚠️  القسم '{$departmentName}' غير موجود\n";
            }
        }

        // تحديث Ext.NO (رقم التمديد) كرقم هاتف عمل
        $extension = $this->cleanText($data['Ext.NO'] ?? $data['Ext NO'] ?? $data['extension'] ?? null);
        if (!empty($extension)) {
            // إزالة أي أحرف غير رقمية
            $extension = preg_replace('/[^0-9+]/', '', $extension);
            
            if (!empty($extension)) {
                // البحث عن رقم هاتف عمل موجود
                $workPhone = $user->phones()->whereHas('phoneType', function($query) {
                    $query->where('slug', 'work');
                })->first();
                
                if (!$workPhone) {
                    // إنشاء رقم هاتف عمل جديد
                    UserPhone::create([
                        'user_id' => $user->id,
                        'phone_type_id' => $this->phoneTypes['work']->id,
                        'phone_number' => $extension,
                        'is_primary' => true,
                        'is_active' => true,
                    ]);
                    echo "   ✓ رقم الهاتف (Ext.NO): {$extension}\n";
                    $updated = true;
                } elseif ($workPhone->phone_number != $extension) {
                    // تحديث الرقم الموجود
                    $workPhone->update(['phone_number' => $extension]);
                    echo "   ✓ رقم الهاتف (Ext.NO): تم التحديث إلى {$extension}\n";
                    $updated = true;
                }
                
                // تحديث phone_work أيضاً للتوافق
                if ($user->phone_work != $extension) {
                    $updates['phone_work'] = $extension;
                    $updated = true;
                }
            }
        }

        // تحديث Employee ID من Code (إذا لم يكن موجوداً)
        if (!empty($code)) {
            if (empty($user->employee_id)) {
                // التحقق من عدم وجود employee_id آخر بنفس القيمة
                $existing = User::where('employee_id', $code)->where('id', '!=', $user->id)->first();
                if (!$existing) {
                    $updates['employee_id'] = $code;
                    $updated = true;
                    echo "   ✓ رقم الموظف: {$code}\n";
                } else {
                    echo "   ⚠️  رقم الموظف '{$code}' مستخدم لمستخدم آخر\n";
                }
            } elseif ($user->employee_id != $code) {
                // التحقق قبل التحديث
                $existing = User::where('employee_id', $code)->where('id', '!=', $user->id)->first();
                if (!$existing) {
                    $updates['employee_id'] = $code;
                    $updated = true;
                    echo "   ✓ رقم الموظف: تم التحديث إلى {$code}\n";
                }
            }
        }

        // تحديث الاسم (إذا كان مختلفاً)
        $empName = $this->cleanText($data['Emp. Name'] ?? null);
        if (!empty($empName) && $user->name != $empName) {
            $updates['name'] = $empName;
            $updated = true;
            echo "   ✓ الاسم: تم التحديث إلى {$empName}\n";
        }

        // تحديث الاسم بالعربي (إذا لم يكن موجوداً)
        if (empty($user->name_ar) && !empty($empName)) {
            $nameAr = $this->convertEnglishToArabic($empName);
            $updates['name_ar'] = $nameAr;
            $updated = true;
            echo "   ✓ الاسم بالعربي: {$nameAr}\n";
        }

        // تحديث الإيميل إذا كان مختلفاً
        if (!empty($email) && $user->email != $email) {
            // التحقق من عدم استخدام الإيميل من قبل مستخدم آخر
            $existing = User::where('email', $email)->where('id', '!=', $user->id)->first();
            if (!$existing) {
                $updates['email'] = $email;
                $updated = true;
                echo "   ✓ الإيميل: تم التحديث إلى {$email}\n";
            }
        }

        // إضافة جميع الإيميلات إلى جدول employee_emails
        if (!empty($emailString) && !empty($email)) {
            $this->addEmployeeEmails($user, $emailString);
        }

        // تطبيق التحديثات
        if (!empty($updates)) {
            try {
                $user->update($updates);
                $this->updatedCount++;
                $this->successCount++;
                echo "   ✅ تم التحديث بنجاح\n\n";
            } catch (Exception $e) {
                $this->errors[] = "الصف $rowNumber: خطأ في التحديث - " . $e->getMessage();
                $this->skippedCount++;
                echo "   ❌ خطأ: " . $e->getMessage() . "\n\n";
            }
        } else {
            echo "   ℹ️  لا توجد تحديثات مطلوبة\n\n";
        }
    }

    /**
     * استيراد البيانات من ملف Excel
     */
    public function import($excelFile)
    {
        if (!file_exists($excelFile)) {
            die("❌ ملف Excel غير موجود: $excelFile\n");
        }

        echo "📂 قراءة ملف Excel: $excelFile\n\n";

        try {
            $spreadsheet = IOFactory::load($excelFile);
            $sheet = $spreadsheet->getActiveSheet();
            
            // قراءة العناوين من الصف الثاني (البنية: الصف 1 فارغ/معلومات، الصف 2 العناوين)
            $headers = [];
            $highestColumn = $sheet->getHighestColumn();
            
            // قراءة العناوين من الصف 2
            for ($col = 'A'; $col <= $highestColumn; $col++) {
                $header = $sheet->getCell($col . '2')->getValue();
                if (!empty($header)) {
                    $cleanHeader = $this->cleanText($header);
                    $headers[$col] = $cleanHeader;
                }
            }

            echo "📋 العناوين الموجودة:\n";
            foreach ($headers as $col => $header) {
                echo "   $col: $header\n";
            }
            echo "\n";

            // قراءة البيانات من الصف الثالث فما بعد
            $highestRow = $sheet->getHighestRow();
            $data = [];
            
            for ($row = 3; $row <= $highestRow; $row++) {
                $rowData = [];
                $hasData = false;
                
                foreach ($headers as $col => $header) {
                    $value = $sheet->getCell($col . $row)->getValue();
                    if (!empty($value)) {
                        $hasData = true;
                    }
                    // معالجة الإيميلات المتعددة (مفصولة بسطر جديد)
                    if ($header == 'Email' && !empty($value)) {
                        $rowData[$header] = $value; // نتركها كاملة للتعامل معها لاحقاً
                    } else {
                        $rowData[$header] = $this->cleanText($value);
                    }
                }
                
                if ($hasData) {
                    $data[] = $rowData;
                }
            }

            echo "📊 تم قراءة " . count($data) . " صف من البيانات\n\n";
            echo "🔄 بدء التحديث...\n\n";

            // معالجة كل صف
            foreach ($data as $index => $row) {
                $rowNumber = $index + 3; // +3 لأننا بدأنا من الصف الثالث (الصف 2 هو العناوين)
                $this->processEmployee($row, $rowNumber);
            }

            // عرض النتائج النهائية
            $this->displayResults();

        } catch (Exception $e) {
            echo "❌ خطأ في قراءة ملف Excel: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    /**
     * إضافة الإيميلات إلى جدول employee_emails
     */
    private function addEmployeeEmails($user, $emailString)
    {
        if (empty($emailString)) {
            return;
        }

        // تقسيم الإيميلات
        $emails = preg_split('/[\r\n\s]+/', trim($emailString));
        $addedCount = 0;
        
        foreach ($emails as $email) {
            $email = strtolower(trim($email));
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                continue;
            }
            
            // التحقق من وجود الإيميل
            try {
                $existingEmail = \App\Models\EmployeeEmail::where('employee_id', $user->id)
                    ->where('email_address', $email)
                    ->first();
                
                if (!$existingEmail) {
                    // تحديد إذا كان هذا الإيميل الأساسي (أول إيميل)
                    $isPrimary = \App\Models\EmployeeEmail::where('employee_id', $user->id)
                        ->where('is_primary', true)
                        ->count() == 0;
                    
                    \App\Models\EmployeeEmail::create([
                        'employee_id' => $user->id,
                        'email_address' => $email,
                        'email_type' => 'work',
                        'is_primary' => $isPrimary,
                        'is_active' => true,
                        'notes' => null
                    ]);
                    
                    $addedCount++;
                }
            } catch (Exception $e) {
                // تجاهل الأخطاء (مثل الجدول غير موجود)
                // echo "   ⚠️  خطأ في إضافة الإيميل {$email}: " . $e->getMessage() . "\n";
            }
        }
        
        if ($addedCount > 0) {
            echo "   ✓ تم إضافة {$addedCount} إيميل إلى جدول employee_emails\n";
        }
    }

    /**
     * عرض النتائج النهائية
     */
    private function displayResults()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 ملخص النتائج:\n";
        echo str_repeat("=", 60) . "\n";
        echo "✅ تم التحديث بنجاح: {$this->successCount} مستخدم\n";
        echo "🔄 تم التحديث: {$this->updatedCount} مستخدم\n";
        echo "⚠️  تم التخطي: {$this->skippedCount} صف\n";
        echo "❌ الأخطاء: " . count($this->errors) . "\n";

        if (count($this->errors) > 0) {
            echo "\n🔴 قائمة الأخطاء:\n";
            foreach ($this->errors as $error) {
                echo "   - $error\n";
            }
        }

        echo "\n";
    }
}

// تنفيذ الاستيراد
try {
    $importer = new UsersExcelUpdater();
    $excelFile = __DIR__ . '/Copy of Employee Contact Data Oct.2025_FIXED.xlsx';
    $importer->import($excelFile);
} catch (Exception $e) {
    echo "❌ خطأ عام: " . $e->getMessage() . "\n";
    exit(1);
}

