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

// بيانات المستخدمين التجريبية
$sampleUsers = [
    // قسم IT
    [
        'name' => 'أحمد محمد علي',
        'name_ar' => 'أحمد محمد علي',
        'email' => 'ahmed.ali@egybell.com',
        'employee_id' => 'EMP001',
        'EmployeeCode' => 1001,
        'job_title' => 'مدير تقنية المعلومات',
        'department_id' => 7, // IT
        'role_id' => 3, // Manager
        'phone_work' => '01234567890',
        'phone_mobile' => '01234567891',
        'work_email' => 'ahmed.ali@egybell.com',
        'hire_date' => '2020-01-15',
        'birthday' => '1985-03-15',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    [
        'name' => 'فاطمة أحمد حسن',
        'name_ar' => 'فاطمة أحمد حسن',
        'email' => 'fatma.hassan@egybell.com',
        'employee_id' => 'EMP002',
        'EmployeeCode' => 1002,
        'job_title' => 'مطور برمجيات',
        'department_id' => 7, // IT
        'role_id' => 5, // Employee
        'phone_work' => '01234567892',
        'phone_mobile' => '01234567893',
        'work_email' => 'fatma.hassan@egybell.com',
        'hire_date' => '2021-06-01',
        'birthday' => '1990-07-20',
        'nationality' => 'مصري',
        'address' => 'الإسكندرية، مصر',
        'city' => 'الإسكندرية',
        'country' => 'مصر'
    ],
    [
        'name' => 'محمد إبراهيم سعد',
        'name_ar' => 'محمد إبراهيم سعد',
        'email' => 'mohamed.saad@egybell.com',
        'employee_id' => 'EMP003',
        'EmployeeCode' => 1003,
        'job_title' => 'محلل أنظمة',
        'department_id' => 7, // IT
        'role_id' => 5, // Employee
        'phone_work' => '01234567894',
        'phone_mobile' => '01234567895',
        'work_email' => 'mohamed.saad@egybell.com',
        'hire_date' => '2022-03-10',
        'birthday' => '1988-11-12',
        'nationality' => 'مصري',
        'address' => 'الجيزة، مصر',
        'city' => 'الجيزة',
        'country' => 'مصر'
    ],
    
    // قسم HR
    [
        'name' => 'نور الدين محمود',
        'name_ar' => 'نور الدين محمود',
        'email' => 'nour.mahmoud@egybell.com',
        'employee_id' => 'EMP004',
        'EmployeeCode' => 1004,
        'job_title' => 'مدير الموارد البشرية',
        'department_id' => 9, // HR
        'role_id' => 3, // Manager
        'phone_work' => '01234567896',
        'phone_mobile' => '01234567897',
        'work_email' => 'nour.mahmoud@egybell.com',
        'hire_date' => '2019-09-01',
        'birthday' => '1982-05-08',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    [
        'name' => 'سارة محمد عبدالله',
        'name_ar' => 'سارة محمد عبدالله',
        'email' => 'sara.abdullah@egybell.com',
        'employee_id' => 'EMP005',
        'EmployeeCode' => 1005,
        'job_title' => 'أخصائي توظيف',
        'department_id' => 9, // HR
        'role_id' => 5, // Employee
        'phone_work' => '01234567898',
        'phone_mobile' => '01234567899',
        'work_email' => 'sara.abdullah@egybell.com',
        'hire_date' => '2021-02-15',
        'birthday' => '1992-12-03',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    
    // قسم المبيعات
    [
        'name' => 'خالد أحمد مصطفى',
        'name_ar' => 'خالد أحمد مصطفى',
        'email' => 'khaled.mostafa@egybell.com',
        'employee_id' => 'EMP006',
        'EmployeeCode' => 1006,
        'job_title' => 'مدير المبيعات',
        'department_id' => 4, // BTC - Sales
        'role_id' => 3, // Manager
        'phone_work' => '01234567900',
        'phone_mobile' => '01234567901',
        'work_email' => 'khaled.mostafa@egybell.com',
        'hire_date' => '2018-11-20',
        'birthday' => '1980-08-25',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    [
        'name' => 'مريم علي حسن',
        'name_ar' => 'مريم علي حسن',
        'email' => 'mariam.hassan@egybell.com',
        'employee_id' => 'EMP007',
        'EmployeeCode' => 1007,
        'job_title' => 'مندوب مبيعات',
        'department_id' => 4, // BTC - Sales
        'role_id' => 5, // Employee
        'phone_work' => '01234567902',
        'phone_mobile' => '01234567903',
        'work_email' => 'mariam.hassan@egybell.com',
        'hire_date' => '2020-07-10',
        'birthday' => '1987-04-18',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    
    // قسم المحاسبة
    [
        'name' => 'عبدالرحمن محمد إبراهيم',
        'name_ar' => 'عبدالرحمن محمد إبراهيم',
        'email' => 'abdelrahman.ibrahim@egybell.com',
        'employee_id' => 'EMP008',
        'EmployeeCode' => 1008,
        'job_title' => 'مدير المحاسبة',
        'department_id' => 3, // Accounts
        'role_id' => 3, // Manager
        'phone_work' => '01234567904',
        'phone_mobile' => '01234567905',
        'work_email' => 'abdelrahman.ibrahim@egybell.com',
        'hire_date' => '2017-05-01',
        'birthday' => '1978-09-30',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    [
        'name' => 'هند محمد السيد',
        'name_ar' => 'هند محمد السيد',
        'email' => 'hind.elsayed@egybell.com',
        'employee_id' => 'EMP009',
        'EmployeeCode' => 1009,
        'job_title' => 'محاسب',
        'department_id' => 3, // Accounts
        'role_id' => 5, // Employee
        'phone_work' => '01234567906',
        'phone_mobile' => '01234567907',
        'work_email' => 'hind.elsayed@egybell.com',
        'hire_date' => '2021-01-10',
        'birthday' => '1991-06-14',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    
    // قسم التسويق
    [
        'name' => 'ياسمين أحمد فؤاد',
        'name_ar' => 'ياسمين أحمد فؤاد',
        'email' => 'yasmin.fouad@egybell.com',
        'employee_id' => 'EMP010',
        'EmployeeCode' => 1010,
        'job_title' => 'مدير التسويق',
        'department_id' => 15, // Marketing
        'role_id' => 3, // Manager
        'phone_work' => '01234567908',
        'phone_mobile' => '01234567909',
        'work_email' => 'yasmin.fouad@egybell.com',
        'hire_date' => '2019-03-15',
        'birthday' => '1985-01-22',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ],
    [
        'name' => 'أحمد محمود رشاد',
        'name_ar' => 'أحمد محمود رشاد',
        'email' => 'ahmed.rashad@egybell.com',
        'employee_id' => 'EMP011',
        'EmployeeCode' => 1011,
        'job_title' => 'أخصائي تسويق رقمي',
        'department_id' => 15, // Marketing
        'role_id' => 5, // Employee
        'phone_work' => '01234567910',
        'phone_mobile' => '01234567911',
        'work_email' => 'ahmed.rashad@egybell.com',
        'hire_date' => '2022-08-01',
        'birthday' => '1993-10-05',
        'nationality' => 'مصري',
        'address' => 'القاهرة، مصر',
        'city' => 'القاهرة',
        'country' => 'مصر'
    ]
];

// دالة لإنشاء كلمة مرور آمنة
function generatePassword($length = 12) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

// إدراج المستخدمين في قاعدة البيانات
$successCount = 0;
$errorCount = 0;

foreach ($sampleUsers as $userData) {
    try {
        // إنشاء كلمة مرور مؤقتة
        $password = generatePassword();
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // إعداد البيانات للإدراج
        $insertData = [
            'name' => $userData['name'],
            'name_ar' => $userData['name_ar'],
            'email' => $userData['email'],
            'password' => $hashedPassword,
            'employee_id' => $userData['employee_id'],
            'EmployeeCode' => $userData['EmployeeCode'],
            'job_title' => $userData['job_title'],
            'department_id' => $userData['department_id'],
            'role_id' => $userData['role_id'],
            'phone_work' => $userData['phone_work'],
            'phone_mobile' => $userData['phone_mobile'],
            'work_email' => $userData['work_email'],
            'hire_date' => $userData['hire_date'],
            'birthday' => $userData['birthday'],
            'nationality' => $userData['nationality'],
            'address' => $userData['address'],
            'city' => $userData['city'],
            'country' => $userData['country'],
            'language' => 'ar',
            'timezone' => 'Africa/Cairo',
            'user_type' => 'employee',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        // إدراج المستخدم
        $sql = "INSERT INTO users (name, name_ar, email, password, employee_id, \"EmployeeCode\", job_title, department_id, role_id, phone_work, phone_mobile, work_email, hire_date, birthday, nationality, address, city, country, language, timezone, user_type, created_at, updated_at) 
                VALUES (:name, :name_ar, :email, :password, :employee_id, :EmployeeCode, :job_title, :department_id, :role_id, :phone_work, :phone_mobile, :work_email, :hire_date, :birthday, :nationality, :address, :city, :country, :language, :timezone, :user_type, :created_at, :updated_at)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($insertData);
        
        $userId = $pdo->lastInsertId();
        $successCount++;
        
        echo "تم إنشاء المستخدم: {$userData['name']} (ID: $userId) - كلمة المرور: $password\n";
        
    } catch (PDOException $e) {
        $errorCount++;
        echo "خطأ في إنشاء المستخدم {$userData['name']}: " . $e->getMessage() . "\n";
    }
}

echo "\n=== ملخص النتائج ===\n";
echo "تم إنشاء $successCount مستخدم بنجاح\n";
echo "فشل في إنشاء $errorCount مستخدم\n";

// عرض إحصائيات المستخدمين
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
echo "إجمالي المستخدمين في النظام: $totalUsers\n";

// عرض المستخدمين حسب القسم
$stmt = $pdo->query("
    SELECT d.name_ar as department_name, COUNT(u.id) as user_count 
    FROM departments d 
    LEFT JOIN users u ON d.id = u.department_id 
    GROUP BY d.id, d.name_ar 
    ORDER BY user_count DESC
");
$departmentStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "\n=== المستخدمين حسب القسم ===\n";
foreach ($departmentStats as $stat) {
    echo "{$stat['department_name']}: {$stat['user_count']} مستخدم\n";
}
?>
