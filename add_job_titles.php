<?php
// إعدادات قاعدة البيانات
$host = '127.0.0.1';
$port = '5432';
$dbname = 'CRM_ALL';
$username = 'postgres';
$password = '';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "تم الاتصال بقاعدة البيانات بنجاح\n";
} catch (PDOException $e) {
    die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage() . "\n");
}

// المسميات الوظيفية الجديدة
$jobTitles = [
    // إدارة عليا
    'الرئيس التنفيذي' => 'Chief Executive Officer',
    'الرئيس التنفيذي للعمليات' => 'Chief Operating Officer',
    'الرئيس التنفيذي للمالية' => 'Chief Financial Officer',
    'الرئيس التنفيذي للتكنولوجيا' => 'Chief Technology Officer',
    'الرئيس التنفيذي للموارد البشرية' => 'Chief Human Resources Officer',
    
    // إدارة
    'مدير عام' => 'General Manager',
    'مدير تنفيذي' => 'Executive Manager',
    'مدير العمليات' => 'Operations Manager',
    'مدير المبيعات' => 'Sales Manager',
    'مدير التسويق' => 'Marketing Manager',
    'مدير الموارد البشرية' => 'Human Resources Manager',
    'مدير المحاسبة' => 'Accounting Manager',
    'مدير تقنية المعلومات' => 'IT Manager',
    'مدير المشاريع' => 'Project Manager',
    'مدير الجودة' => 'Quality Manager',
    'مدير الأمن' => 'Security Manager',
    'مدير التدريب' => 'Training Manager',
    'مدير العلاقات العامة' => 'Public Relations Manager',
    'مدير التطوير' => 'Development Manager',
    'مدير الإنتاج' => 'Production Manager',
    'مدير المبيعات الإقليمي' => 'Regional Sales Manager',
    'مدير التسويق الرقمي' => 'Digital Marketing Manager',
    'مدير العمليات اللوجستية' => 'Logistics Operations Manager',
    
    // قيادة الفرق
    'قائد فريق' => 'Team Leader',
    'قائد فريق المبيعات' => 'Sales Team Leader',
    'قائد فريق التطوير' => 'Development Team Leader',
    'قائد فريق التسويق' => 'Marketing Team Leader',
    'قائد فريق الدعم الفني' => 'Technical Support Team Leader',
    'قائد فريق المحاسبة' => 'Accounting Team Leader',
    'قائد فريق الموارد البشرية' => 'HR Team Leader',
    'قائد فريق العمليات' => 'Operations Team Leader',
    
    // أخصائيين
    'أخصائي مبيعات' => 'Sales Specialist',
    'أخصائي تسويق' => 'Marketing Specialist',
    'أخصائي موارد بشرية' => 'Human Resources Specialist',
    'أخصائي محاسبة' => 'Accounting Specialist',
    'أخصائي تقنية معلومات' => 'IT Specialist',
    'أخصائي جودة' => 'Quality Specialist',
    'أخصائي تدريب' => 'Training Specialist',
    'أخصائي علاقات عامة' => 'Public Relations Specialist',
    'أخصائي تطوير' => 'Development Specialist',
    'أخصائي أمن' => 'Security Specialist',
    'أخصائي تسويق رقمي' => 'Digital Marketing Specialist',
    'أخصائي تحليل بيانات' => 'Data Analysis Specialist',
    'أخصائي دعم فني' => 'Technical Support Specialist',
    'أخصائي توظيف' => 'Recruitment Specialist',
    'أخصائي علاقات عملاء' => 'Customer Relations Specialist',
    
    // مطورين ومبرمجين
    'مطور برمجيات' => 'Software Developer',
    'مطور ويب' => 'Web Developer',
    'مطور تطبيقات' => 'Application Developer',
    'مطور قاعدة بيانات' => 'Database Developer',
    'مطور أندرويد' => 'Android Developer',
    'مطور iOS' => 'iOS Developer',
    'مطور Full Stack' => 'Full Stack Developer',
    'مطور Frontend' => 'Frontend Developer',
    'مطور Backend' => 'Backend Developer',
    'مطور DevOps' => 'DevOps Developer',
    'مهندس برمجيات' => 'Software Engineer',
    'مهندس أنظمة' => 'Systems Engineer',
    'مهندس شبكات' => 'Network Engineer',
    'مهندس أمن' => 'Security Engineer',
    'مهندس قاعدة بيانات' => 'Database Engineer',
    
    // محللين
    'محلل أنظمة' => 'Systems Analyst',
    'محلل بيانات' => 'Data Analyst',
    'محلل أعمال' => 'Business Analyst',
    'محلل مالي' => 'Financial Analyst',
    'محلل تسويق' => 'Marketing Analyst',
    'محلل أمن' => 'Security Analyst',
    'محلل جودة' => 'Quality Analyst',
    'محلل عمليات' => 'Operations Analyst',
    
    // موظفين
    'موظف مبيعات' => 'Sales Employee',
    'موظف تسويق' => 'Marketing Employee',
    'موظف موارد بشرية' => 'HR Employee',
    'موظف محاسبة' => 'Accounting Employee',
    'موظف تقنية معلومات' => 'IT Employee',
    'موظف عمليات' => 'Operations Employee',
    'موظف خدمة عملاء' => 'Customer Service Employee',
    'موظف إداري' => 'Administrative Employee',
    'موظف مالي' => 'Financial Employee',
    'موظف لوجستي' => 'Logistics Employee',
    'موظف أمن' => 'Security Employee',
    'موظف صيانة' => 'Maintenance Employee',
    'موظف مخازن' => 'Warehouse Employee',
    'موظف شحن' => 'Shipping Employee',
    'موظف استقبال' => 'Reception Employee',
    
    // مساعدين
    'مساعد مدير' => 'Assistant Manager',
    'مساعد مبيعات' => 'Sales Assistant',
    'مساعد تسويق' => 'Marketing Assistant',
    'مساعد موارد بشرية' => 'HR Assistant',
    'مساعد محاسبة' => 'Accounting Assistant',
    'مساعد تقنية معلومات' => 'IT Assistant',
    'مساعد عمليات' => 'Operations Assistant',
    'مساعد إداري' => 'Administrative Assistant',
    'مساعد تنفيذي' => 'Executive Assistant',
    'مساعد مدير عام' => 'General Manager Assistant',
    
    // مندوبين
    'مندوب مبيعات' => 'Sales Representative',
    'مندوب تسويق' => 'Marketing Representative',
    'مندوب خدمة عملاء' => 'Customer Service Representative',
    'مندوب ميداني' => 'Field Representative',
    'مندوب خارجي' => 'External Representative',
    'مندوب داخلي' => 'Internal Representative',
    
    // مشرفين
    'مشرف مبيعات' => 'Sales Supervisor',
    'مشرف تسويق' => 'Marketing Supervisor',
    'مشرف موارد بشرية' => 'HR Supervisor',
    'مشرف محاسبة' => 'Accounting Supervisor',
    'مشرف تقنية معلومات' => 'IT Supervisor',
    'مشرف عمليات' => 'Operations Supervisor',
    'مشرف إنتاج' => 'Production Supervisor',
    'مشرف جودة' => 'Quality Supervisor',
    'مشرف أمن' => 'Security Supervisor',
    'مشرف مخازن' => 'Warehouse Supervisor',
    
    // استشاريين
    'استشاري مبيعات' => 'Sales Consultant',
    'استشاري تسويق' => 'Marketing Consultant',
    'استشاري موارد بشرية' => 'HR Consultant',
    'استشاري تقنية معلومات' => 'IT Consultant',
    'استشاري مالي' => 'Financial Consultant',
    'استشاري إداري' => 'Administrative Consultant',
    'استشاري جودة' => 'Quality Consultant',
    'استشاري أمن' => 'Security Consultant',
    
    // منسقين
    'منسق مشاريع' => 'Project Coordinator',
    'منسق مبيعات' => 'Sales Coordinator',
    'منسق تسويق' => 'Marketing Coordinator',
    'منسق موارد بشرية' => 'HR Coordinator',
    'منسق محاسبة' => 'Accounting Coordinator',
    'منسق تقنية معلومات' => 'IT Coordinator',
    'منسق عمليات' => 'Operations Coordinator',
    'منسق تدريب' => 'Training Coordinator',
    'منسق أحداث' => 'Events Coordinator',
    'منسق علاقات عامة' => 'Public Relations Coordinator',
    
    // مسؤولين
    'مسؤول مبيعات' => 'Sales Officer',
    'مسؤول تسويق' => 'Marketing Officer',
    'مسؤول موارد بشرية' => 'HR Officer',
    'مسؤول محاسبة' => 'Accounting Officer',
    'مسؤول تقنية معلومات' => 'IT Officer',
    'مسؤول عمليات' => 'Operations Officer',
    'مسؤول أمن' => 'Security Officer',
    'مسؤول جودة' => 'Quality Officer',
    'مسؤول مالي' => 'Financial Officer',
    'مسؤول إداري' => 'Administrative Officer',
    
    // تقنيين
    'تقني مبيعات' => 'Sales Technician',
    'تقني تسويق' => 'Marketing Technician',
    'تقني موارد بشرية' => 'HR Technician',
    'تقني محاسبة' => 'Accounting Technician',
    'تقني تقنية معلومات' => 'IT Technician',
    'تقني عمليات' => 'Operations Technician',
    'تقني إنتاج' => 'Production Technician',
    'تقني صيانة' => 'Maintenance Technician',
    'تقني أمن' => 'Security Technician',
    'تقني جودة' => 'Quality Technician',
    
    // متدربين
    'متدرب مبيعات' => 'Sales Trainee',
    'متدرب تسويق' => 'Marketing Trainee',
    'متدرب موارد بشرية' => 'HR Trainee',
    'متدرب محاسبة' => 'Accounting Trainee',
    'متدرب تقنية معلومات' => 'IT Trainee',
    'متدرب عمليات' => 'Operations Trainee',
    'متدرب إداري' => 'Administrative Trainee',
    'متدرب مالي' => 'Financial Trainee',
    
    // موظفين مؤقتين
    'موظف مؤقت' => 'Temporary Employee',
    'موظف بدوام جزئي' => 'Part-time Employee',
    'موظف بدوام كامل' => 'Full-time Employee',
    'موظف بعقد' => 'Contract Employee',
    'موظف مستقل' => 'Freelance Employee',
    
    // موظفين خارجيين
    'مستشار خارجي' => 'External Consultant',
    'مورد خارجي' => 'External Supplier',
    'شريك خارجي' => 'External Partner',
    'وكيل خارجي' => 'External Agent',
    'مقاول خارجي' => 'External Contractor'
];

// إنشاء جدول المسميات الوظيفية إذا لم يكن موجوداً
$createTableSql = "
CREATE TABLE IF NOT EXISTS job_titles (
    id SERIAL PRIMARY KEY,
    name_ar VARCHAR(255) NOT NULL,
    name_en VARCHAR(255) NOT NULL,
    description_ar TEXT,
    description_en TEXT,
    category VARCHAR(100),
    level VARCHAR(50),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(name_ar),
    UNIQUE(name_en)
);
";

try {
    $pdo->exec($createTableSql);
    echo "تم إنشاء جدول المسميات الوظيفية بنجاح\n";
} catch (PDOException $e) {
    echo "خطأ في إنشاء جدول المسميات الوظيفية: " . $e->getMessage() . "\n";
}

// إدراج المسميات الوظيفية
$successCount = 0;
$errorCount = 0;

foreach ($jobTitles as $nameAr => $nameEn) {
    try {
        // تحديد الفئة والمستوى
        $category = 'عام';
        $level = 'موظف';
        
        if (strpos($nameAr, 'رئيس') !== false || strpos($nameAr, 'مدير عام') !== false) {
            $category = 'إدارة عليا';
            $level = 'إدارة عليا';
        } elseif (strpos($nameAr, 'مدير') !== false) {
            $category = 'إدارة';
            $level = 'إدارة';
        } elseif (strpos($nameAr, 'قائد') !== false) {
            $category = 'قيادة فرق';
            $level = 'قائد فريق';
        } elseif (strpos($nameAr, 'أخصائي') !== false) {
            $category = 'أخصائيين';
            $level = 'أخصائي';
        } elseif (strpos($nameAr, 'مطور') !== false || strpos($nameAr, 'مهندس') !== false) {
            $category = 'تطوير وهندسة';
            $level = 'مطور/مهندس';
        } elseif (strpos($nameAr, 'محلل') !== false) {
            $category = 'تحليل';
            $level = 'محلل';
        } elseif (strpos($nameAr, 'مساعد') !== false) {
            $category = 'مساعدين';
            $level = 'مساعد';
        } elseif (strpos($nameAr, 'مندوب') !== false) {
            $category = 'مندوبين';
            $level = 'مندوب';
        } elseif (strpos($nameAr, 'مشرف') !== false) {
            $category = 'مشرفين';
            $level = 'مشرف';
        } elseif (strpos($nameAr, 'استشاري') !== false) {
            $category = 'استشاريين';
            $level = 'استشاري';
        } elseif (strpos($nameAr, 'منسق') !== false) {
            $category = 'منسقين';
            $level = 'منسق';
        } elseif (strpos($nameAr, 'مسؤول') !== false) {
            $category = 'مسؤولين';
            $level = 'مسؤول';
        } elseif (strpos($nameAr, 'تقني') !== false) {
            $category = 'تقنيين';
            $level = 'تقني';
        } elseif (strpos($nameAr, 'متدرب') !== false) {
            $category = 'متدربين';
            $level = 'متدرب';
        } elseif (strpos($nameAr, 'مؤقت') !== false || strpos($nameAr, 'بدوام') !== false) {
            $category = 'موظفين مؤقتين';
            $level = 'موظف مؤقت';
        } elseif (strpos($nameAr, 'خارجي') !== false) {
            $category = 'موظفين خارجيين';
            $level = 'موظف خارجي';
        }
        
        $sql = "INSERT INTO job_titles (name_ar, name_en, category, level, created_at, updated_at) 
                VALUES (:name_ar, :name_en, :category, :level, :created_at, :updated_at)
                ON CONFLICT (name_ar) DO NOTHING";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name_ar' => $nameAr,
            'name_en' => $nameEn,
            'category' => $category,
            'level' => $level,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($stmt->rowCount() > 0) {
            $successCount++;
            echo "تم إضافة المسمى الوظيفي: $nameAr\n";
        } else {
            echo "المسمى الوظيفي موجود مسبقاً: $nameAr\n";
        }
        
    } catch (PDOException $e) {
        $errorCount++;
        echo "خطأ في إضافة المسمى الوظيفي $nameAr: " . $e->getMessage() . "\n";
    }
}

echo "\n=== ملخص النتائج ===\n";
echo "تم إضافة $successCount مسمى وظيفي جديد\n";
echo "فشل في إضافة $errorCount مسمى وظيفي\n";

// عرض إحصائيات المسميات الوظيفية
$stmt = $pdo->query("SELECT COUNT(*) as total_titles FROM job_titles");
$totalTitles = $stmt->fetch(PDO::FETCH_ASSOC)['total_titles'];
echo "إجمالي المسميات الوظيفية في النظام: $totalTitles\n";

// عرض المسميات حسب الفئة
$stmt = $pdo->query("
    SELECT category, COUNT(*) as title_count 
    FROM job_titles 
    GROUP BY category 
    ORDER BY title_count DESC
");
$categoryStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== المسميات الوظيفية حسب الفئة ===\n";
foreach ($categoryStats as $stat) {
    echo "{$stat['category']}: {$stat['title_count']} مسمى\n";
}

// عرض المسميات حسب المستوى
$stmt = $pdo->query("
    SELECT level, COUNT(*) as title_count 
    FROM job_titles 
    GROUP BY level 
    ORDER BY title_count DESC
");
$levelStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== المسميات الوظيفية حسب المستوى ===\n";
foreach ($levelStats as $stat) {
    echo "{$stat['level']}: {$stat['title_count']} مسمى\n";
}
?>
