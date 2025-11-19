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

echo "=== معالجة مشاكل البيانات ===\n\n";

$fixedCount = 0;
$errorCount = 0;

// 1. ربط المستخدمين بدون قسم بقسم افتراضي
echo "=== ربط المستخدمين بدون قسم ===\n";
try {
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email 
        FROM users u 
        WHERE u.department_id IS NULL 
        LIMIT 10
    ");
    $usersWithoutDept = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تعيين قسم افتراضي (Admin)
    $defaultDeptId = 8; // Admin
    
    foreach ($usersWithoutDept as $user) {
        try {
            $updateSql = "UPDATE users SET department_id = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$defaultDeptId, $user['id']]);
            
            echo "تم ربط المستخدم {$user['name']} بقسم Admin\n";
            $fixedCount++;
            
        } catch (PDOException $e) {
            echo "خطأ في ربط المستخدم {$user['name']}: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على المستخدمين بدون قسم: " . $e->getMessage() . "\n";
}

// 2. ربط المستخدمين بدون دور بدور افتراضي
echo "\n=== ربط المستخدمين بدون دور ===\n";
try {
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.department_id
        FROM users u 
        WHERE u.role_id IS NULL 
        LIMIT 20
    ");
    $usersWithoutRole = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // تعيين دور افتراضي حسب القسم
    $departmentRoleMapping = [
        3 => 5, // Accounts -> Employee
        4 => 5, // BTC - Sales -> Employee
        5 => 5, // Commercial -> Employee
        6 => 5, // Operation -> Employee
        7 => 5, // IT -> Employee
        8 => 5, // Admin -> Employee
        9 => 5, // HR -> Employee
        10 => 5, // Traffic -> Employee
        11 => 5, // Contracting Egypt -> Employee
        12 => 5, // Contracting International -> Employee
        13 => 5, // Contracting Middle East -> Employee
        14 => 5, // Internet -> Employee
        15 => 5  // Marketing -> Employee
    ];
    
    foreach ($usersWithoutRole as $user) {
        try {
            $roleId = $departmentRoleMapping[$user['department_id']] ?? 5; // Employee as default
            
            $updateSql = "UPDATE users SET role_id = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$roleId, $user['id']]);
            
            echo "تم ربط المستخدم {$user['name']} بدور Employee\n";
            $fixedCount++;
            
        } catch (PDOException $e) {
            echo "خطأ في ربط المستخدم {$user['name']}: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على المستخدمين بدون دور: " . $e->getMessage() . "\n";
}

// 3. إضافة كود موظف للمستخدمين الذين لا يملكونه
echo "\n=== إضافة كود موظف ===\n";
try {
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.\"EmployeeCode\"
        FROM users u 
        WHERE u.employee_id IS NULL OR u.employee_id = ''
        LIMIT 10
    ");
    $usersWithoutEmpId = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usersWithoutEmpId as $user) {
        try {
            // إنشاء كود موظف فريد
            $employeeId = 'EMP' . str_pad($user['id'], 3, '0', STR_PAD_LEFT);
            
            $updateSql = "UPDATE users SET employee_id = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$employeeId, $user['id']]);
            
            echo "تم إضافة كود موظف $employeeId للمستخدم {$user['name']}\n";
            $fixedCount++;
            
        } catch (PDOException $e) {
            echo "خطأ في إضافة كود موظف للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على المستخدمين بدون كود موظف: " . $e->getMessage() . "\n";
}

// 4. إضافة تاريخ تعيين للمستخدمين الذين لا يملكونه
echo "\n=== إضافة تاريخ تعيين ===\n";
try {
    $stmt = $pdo->query("
        SELECT u.id, u.name, u.email, u.created_at
        FROM users u 
        WHERE u.hire_date IS NULL
        LIMIT 20
    ");
    $usersWithoutHireDate = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($usersWithoutHireDate as $user) {
        try {
            // استخدام تاريخ الإنشاء كتاريخ تعيين
            $hireDate = date('Y-m-d', strtotime($user['created_at']));
            
            $updateSql = "UPDATE users SET hire_date = ? WHERE id = ?";
            $updateStmt = $pdo->prepare($updateSql);
            $updateStmt->execute([$hireDate, $user['id']]);
            
            echo "تم إضافة تاريخ تعيين $hireDate للمستخدم {$user['name']}\n";
            $fixedCount++;
            
        } catch (PDOException $e) {
            echo "خطأ في إضافة تاريخ تعيين للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
            $errorCount++;
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في الحصول على المستخدمين بدون تاريخ تعيين: " . $e->getMessage() . "\n";
}

// 5. إصلاح البريد الإلكتروني المكرر
echo "\n=== إصلاح البريد الإلكتروني المكرر ===\n";
try {
    $stmt = $pdo->query("
        SELECT email, COUNT(*) as count
        FROM users
        WHERE email IS NOT NULL AND email != ''
        GROUP BY email
        HAVING COUNT(*) > 1
    ");
    $duplicateEmails = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($duplicateEmails as $emailData) {
        $email = $emailData['email'];
        $count = $emailData['count'];
        
        // الحصول على المستخدمين الذين لديهم نفس البريد الإلكتروني
        $stmt = $pdo->prepare("
            SELECT id, name, email 
            FROM users 
            WHERE email = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$email]);
        $usersWithSameEmail = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // الاحتفاظ بالمستخدم الأول وتغيير البريد الإلكتروني للباقين
        for ($i = 1; $i < count($usersWithSameEmail); $i++) {
            $user = $usersWithSameEmail[$i];
            $newEmail = str_replace('@', '+' . $user['id'] . '@', $email);
            
            try {
                $updateSql = "UPDATE users SET email = ? WHERE id = ?";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([$newEmail, $user['id']]);
                
                echo "تم تغيير البريد الإلكتروني للمستخدم {$user['name']} إلى $newEmail\n";
                $fixedCount++;
                
            } catch (PDOException $e) {
                echo "خطأ في تغيير البريد الإلكتروني للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في إصلاح البريد الإلكتروني المكرر: " . $e->getMessage() . "\n";
}

// 6. إصلاح كود الموظف المكرر
echo "\n=== إصلاح كود الموظف المكرر ===\n";
try {
    $stmt = $pdo->query("
        SELECT employee_id, COUNT(*) as count
        FROM users
        WHERE employee_id IS NOT NULL AND employee_id != ''
        GROUP BY employee_id
        HAVING COUNT(*) > 1
    ");
    $duplicateEmployeeIds = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($duplicateEmployeeIds as $empIdData) {
        $employeeId = $empIdData['employee_id'];
        $count = $empIdData['count'];
        
        // الحصول على المستخدمين الذين لديهم نفس كود الموظف
        $stmt = $pdo->prepare("
            SELECT id, name, employee_id 
            FROM users 
            WHERE employee_id = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$employeeId]);
        $usersWithSameEmpId = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // الاحتفاظ بالمستخدم الأول وتغيير كود الموظف للباقين
        for ($i = 1; $i < count($usersWithSameEmpId); $i++) {
            $user = $usersWithSameEmpId[$i];
            $newEmployeeId = $employeeId . '_' . $user['id'];
            
            try {
                $updateSql = "UPDATE users SET employee_id = ? WHERE id = ?";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([$newEmployeeId, $user['id']]);
                
                echo "تم تغيير كود الموظف للمستخدم {$user['name']} إلى $newEmployeeId\n";
                $fixedCount++;
                
            } catch (PDOException $e) {
                echo "خطأ في تغيير كود الموظف للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في إصلاح كود الموظف المكرر: " . $e->getMessage() . "\n";
}

// 7. إصلاح EmployeeCode المكرر
echo "\n=== إصلاح EmployeeCode المكرر ===\n";
try {
    $stmt = $pdo->query("
        SELECT \"EmployeeCode\", COUNT(*) as count
        FROM users
        WHERE \"EmployeeCode\" IS NOT NULL
        GROUP BY \"EmployeeCode\"
        HAVING COUNT(*) > 1
    ");
    $duplicateEmployeeCodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($duplicateEmployeeCodes as $empCodeData) {
        $employeeCode = $empCodeData['EmployeeCode'];
        $count = $empCodeData['count'];
        
        // الحصول على المستخدمين الذين لديهم نفس EmployeeCode
        $stmt = $pdo->prepare("
            SELECT id, name, \"EmployeeCode\" 
            FROM users 
            WHERE \"EmployeeCode\" = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$employeeCode]);
        $usersWithSameEmpCode = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // الاحتفاظ بالمستخدم الأول وتغيير EmployeeCode للباقين
        for ($i = 1; $i < count($usersWithSameEmpCode); $i++) {
            $user = $usersWithSameEmpCode[$i];
            $newEmployeeCode = $employeeCode + $i;
            
            try {
                $updateSql = "UPDATE users SET \"EmployeeCode\" = ? WHERE id = ?";
                $updateStmt = $pdo->prepare($updateSql);
                $updateStmt->execute([$newEmployeeCode, $user['id']]);
                
                echo "تم تغيير EmployeeCode للمستخدم {$user['name']} إلى $newEmployeeCode\n";
                $fixedCount++;
                
            } catch (PDOException $e) {
                echo "خطأ في تغيير EmployeeCode للمستخدم {$user['name']}: " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
    }
    
} catch (PDOException $e) {
    echo "خطأ في إصلاح EmployeeCode المكرر: " . $e->getMessage() . "\n";
}

echo "\n=== ملخص النتائج ===\n";
echo "تم إصلاح $fixedCount مشكلة\n";
echo "فشل في إصلاح $errorCount مشكلة\n";

// عرض إحصائيات محدثة
echo "\n=== الإحصائيات المحدثة ===\n";
$stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users");
$totalUsers = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];
echo "إجمالي المستخدمين: $totalUsers\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE department_id IS NULL");
$noDepartment = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون قسم: $noDepartment\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE role_id IS NULL");
$noRole = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون دور: $noRole\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE employee_id IS NULL OR employee_id = ''");
$noEmployeeId = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون كود موظف: $noEmployeeId\n";

$stmt = $pdo->query("SELECT COUNT(*) as count FROM users WHERE hire_date IS NULL");
$noHireDate = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
echo "المستخدمين بدون تاريخ تعيين: $noHireDate\n";

echo "\n=== انتهاء المعالجة ===\n";
?>
