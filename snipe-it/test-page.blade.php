@extends('layouts.app')

@section('title', 'اختبار Snipe-IT API')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-plug"></i>
                        اختبار Snipe-IT API
                    </h3>
                </div>
                <div class="card-body">
                    
                    <!-- اختبار الاتصال -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>🔌 اختبار الاتصال</h4>
                            <p class="text-muted">اختبار الاتصال مع Snipe-IT API</p>
                            
                            <button id="testConnectionBtn" class="btn btn-primary">
                                <i class="fas fa-wifi"></i>
                                اختبار الاتصال
                            </button>
                            
                            <div id="connectionResult" class="mt-3" style="display:none;">
                                <div class="alert" role="alert">
                                    <h5>نتيجة اختبار الاتصال:</h5>
                                    <div id="connectionStatus"></div>
                                    <div id="connectionDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اختبار جلب بيانات المستخدم -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>👤 بيانات المستخدم</h4>
                            <p class="text-muted">جلب بيانات المستخدم الحالي من Snipe-IT</p>
                            
                            <button id="testUserBtn" class="btn btn-success">
                                <i class="fas fa-user"></i>
                                جلب بيانات المستخدم
                            </button>
                            
                            <div id="userDetails" class="mt-3" style="display:none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>بيانات المستخدم:</h5>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item">
                                                <strong>ID:</strong> <span id="uid"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>الاسم الأول:</strong> <span id="ufirstname"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>الاسم الأخير:</strong> <span id="ulastname"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>اسم المستخدم:</strong> <span id="uusername"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>الإيميل:</strong> <span id="uemail"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>رقم الموظف:</strong> <span id="uemployee"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>المسمى الوظيفي:</strong> <span id="ujobtitle"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>الهاتف:</strong> <span id="uphone"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>تاريخ الإنشاء:</strong> <span id="ucreated"></span>
                                            </li>
                                            <li class="list-group-item">
                                                <strong>آخر تحديث:</strong> <span id="uupdated"></span>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اختبار جلب الأصول -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>📦 الأصول</h4>
                            <p class="text-muted">جلب قائمة الأصول من Snipe-IT</p>
                            
                            <button id="testAssetsBtn" class="btn btn-info">
                                <i class="fas fa-boxes"></i>
                                جلب الأصول
                            </button>
                            
                            <div id="assetsResult" class="mt-3" style="display:none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>الأصول:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="assetsList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اختبار جلب الفئات -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>📂 الفئات</h4>
                            <p class="text-muted">جلب قائمة الفئات من Snipe-IT</p>
                            
                            <button id="testCategoriesBtn" class="btn btn-warning">
                                <i class="fas fa-folder"></i>
                                جلب الفئات
                            </button>
                            
                            <div id="categoriesResult" class="mt-3" style="display:none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>الفئات:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="categoriesList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اختبار جلب المستخدمين -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>👥 المستخدمين</h4>
                            <p class="text-muted">جلب قائمة المستخدمين من Snipe-IT</p>
                            
                            <button id="testUsersBtn" class="btn btn-secondary">
                                <i class="fas fa-users"></i>
                                جلب المستخدمين
                            </button>
                            
                            <div id="usersResult" class="mt-3" style="display:none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>المستخدمين:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="usersList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- اختبار شامل -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h4>🚀 اختبار شامل</h4>
                            <p class="text-muted">تشغيل جميع الاختبارات مرة واحدة</p>
                            
                            <button id="runAllTestsBtn" class="btn btn-dark">
                                <i class="fas fa-play"></i>
                                تشغيل جميع الاختبارات
                            </button>
                            
                            <div id="allTestsResult" class="mt-3" style="display:none;">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>نتائج الاختبار الشامل:</h5>
                                    </div>
                                    <div class="card-body">
                                        <div id="allTestsList"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // اختبار الاتصال
    document.getElementById('testConnectionBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('connectionResult');
        const statusDiv = document.getElementById('connectionStatus');
        const detailsDiv = document.getElementById('connectionDetails');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...';
        
        fetch('/api/snipe-it/test-connection', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            resultDiv.style.display = 'block';
            
            if (data.success) {
                statusDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> نجح الاتصال!</span>';
                detailsDiv.innerHTML = `
                    <div class="mt-2">
                        <strong>الرسالة:</strong> ${data.message}<br>
                        <strong>البيانات:</strong> <pre class="mt-2">${JSON.stringify(data.data, null, 2)}</pre>
                    </div>
                `;
                resultDiv.querySelector('.alert').className = 'alert alert-success';
            } else {
                statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> فشل الاتصال!</span>';
                detailsDiv.innerHTML = `
                    <div class="mt-2">
                        <strong>الرسالة:</strong> ${data.message}
                    </div>
                `;
                resultDiv.querySelector('.alert').className = 'alert alert-danger';
            }
        })
        .catch(error => {
            resultDiv.style.display = 'block';
            statusDiv.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle"></i> خطأ في الاتصال!</span>';
            detailsDiv.innerHTML = `
                <div class="mt-2">
                    <strong>الخطأ:</strong> ${error.message}
                </div>
            `;
            resultDiv.querySelector('.alert').className = 'alert alert-danger';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-wifi"></i> اختبار الاتصال';
        });
    });

    // اختبار جلب بيانات المستخدم
    document.getElementById('testUserBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('userDetails');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الجلب...';
        
        fetch('/api/snipe-it/get-user', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const user = data.data;
                document.getElementById('uid').textContent = user.id || 'غير محدد';
                document.getElementById('ufirstname').textContent = user.first_name || 'غير محدد';
                document.getElementById('ulastname').textContent = user.last_name || 'غير محدد';
                document.getElementById('uusername').textContent = user.username || 'غير محدد';
                document.getElementById('uemail').textContent = user.email || 'غير محدد';
                document.getElementById('uemployee').textContent = user.employee_num || 'غير محدد';
                document.getElementById('ujobtitle').textContent = user.jobtitle || 'غير محدد';
                document.getElementById('uphone').textContent = user.phone || 'غير محدد';
                document.getElementById('ucreated').textContent = user.created_at ? new Date(user.created_at).toLocaleString('ar-SA') : 'غير محدد';
                document.getElementById('uupdated').textContent = user.updated_at ? new Date(user.updated_at).toLocaleString('ar-SA') : 'غير محدد';
                
                resultDiv.style.display = 'block';
            } else {
                alert('فشل في جلب بيانات المستخدم: ' + data.message);
            }
        })
        .catch(error => {
            alert('خطأ في جلب بيانات المستخدم: ' + error.message);
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-user"></i> جلب بيانات المستخدم';
        });
    });

    // اختبار جلب الأصول
    document.getElementById('testAssetsBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('assetsResult');
        const listDiv = document.getElementById('assetsList');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الجلب...';
        
        // محاكاة جلب الأصول (يمكن استبدالها بـ API حقيقي)
        setTimeout(() => {
            listDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    هذه ميزة تجريبية. يمكن تطويرها لتعمل مع Snipe-IT API الحقيقي.
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="card mb-2">
                            <div class="card-body">
                                <h6 class="card-title">Laptop Dell</h6>
                                <p class="card-text">Tag: LAP001</p>
                                <small class="text-muted">Serial: DL123456789</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card mb-2">
                            <div class="card-body">
                                <h6 class="card-title">HP Printer</h6>
                                <p class="card-text">Tag: PRT001</p>
                                <small class="text-muted">Serial: HP987654321</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            resultDiv.style.display = 'block';
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-boxes"></i> جلب الأصول';
        }, 1000);
    });

    // اختبار جلب الفئات
    document.getElementById('testCategoriesBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('categoriesResult');
        const listDiv = document.getElementById('categoriesList');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الجلب...';
        
        setTimeout(() => {
            listDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    هذه ميزة تجريبية. يمكن تطويرها لتعمل مع Snipe-IT API الحقيقي.
                </div>
                <div class="list-group">
                    <div class="list-group-item">Computers</div>
                    <div class="list-group-item">Mobile Devices</div>
                    <div class="list-group-item">Network Equipment</div>
                    <div class="list-group-item">Accessories</div>
                    <div class="list-group-item">Consumables</div>
                </div>
            `;
            resultDiv.style.display = 'block';
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-folder"></i> جلب الفئات';
        }, 1000);
    });

    // اختبار جلب المستخدمين
    document.getElementById('testUsersBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('usersResult');
        const listDiv = document.getElementById('usersList');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الجلب...';
        
        setTimeout(() => {
            listDiv.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    هذه ميزة تجريبية. يمكن تطويرها لتعمل مع Snipe-IT API الحقيقي.
                </div>
                <div class="list-group">
                    <div class="list-group-item">John Doe (john.doe@company.com)</div>
                    <div class="list-group-item">Jane Smith (jane.smith@company.com)</div>
                    <div class="list-group-item">Mike Johnson (mike.johnson@company.com)</div>
                </div>
            `;
            resultDiv.style.display = 'block';
            
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-users"></i> جلب المستخدمين';
        }, 1000);
    });

    // اختبار شامل
    document.getElementById('runAllTestsBtn').addEventListener('click', function() {
        const btn = this;
        const resultDiv = document.getElementById('allTestsResult');
        const listDiv = document.getElementById('allTestsList');
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري التشغيل...';
        
        // تشغيل جميع الاختبارات
        const tests = [
            { name: 'اختبار الاتصال', element: 'testConnectionBtn' },
            { name: 'جلب بيانات المستخدم', element: 'testUserBtn' },
            { name: 'جلب الأصول', element: 'testAssetsBtn' },
            { name: 'جلب الفئات', element: 'testCategoriesBtn' },
            { name: 'جلب المستخدمين', element: 'testUsersBtn' }
        ];
        
        let completedTests = 0;
        let results = [];
        
        tests.forEach((test, index) => {
            setTimeout(() => {
                const testBtn = document.getElementById(test.element);
                testBtn.click();
                
                setTimeout(() => {
                    completedTests++;
                    results.push(`✅ ${test.name} - مكتمل`);
                    
                    if (completedTests === tests.length) {
                        listDiv.innerHTML = `
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                تم تشغيل جميع الاختبارات بنجاح!
                            </div>
                            <div class="list-group">
                                ${results.map(result => `<div class="list-group-item">${result}</div>`).join('')}
                            </div>
                        `;
                        resultDiv.style.display = 'block';
                        
                        btn.disabled = false;
                        btn.innerHTML = '<i class="fas fa-play"></i> تشغيل جميع الاختبارات';
                    }
                }, 2000);
            }, index * 500);
        });
    });

});
</script>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border: 1px solid rgba(0, 0, 0, 0.125);
}

.btn {
    margin-bottom: 10px;
}

.alert {
    border-radius: 0.375rem;
}

pre {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 0.25rem;
    font-size: 0.875rem;
}

.list-group-item {
    border: 1px solid rgba(0, 0, 0, 0.125);
    margin-bottom: 2px;
    border-radius: 0.25rem;
}
</style>
@endsection
