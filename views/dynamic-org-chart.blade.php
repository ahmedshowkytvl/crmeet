<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الهيكل التنظيمي - {{ config('app.name') }}</title>
    <meta name="description" content="مخطط هيكلي تفاعلي يعرض الهيكل التنظيمي للشركة من الرئيس التنفيذي وصولاً إلى الموظفين الأفراد">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * { 
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --background: hsl(0, 0%, 100%);
            --foreground: hsl(0, 0%, 0%);
            --border: hsl(0, 0%, 88%);
            --card: hsl(0, 0%, 100%);
            --card-foreground: hsl(0, 0%, 0%);
            --card-border: hsl(0, 0%, 96%);
            --muted-foreground: hsl(0, 0%, 40%);
            --primary: hsl(220, 92%, 35%);
            --primary-foreground: hsl(0, 0%, 98%);
            --destructive: hsl(0, 84%, 45%);
            --destructive-foreground: hsl(0, 0%, 98%);
            --popover: hsl(0, 0%, 98%);
            --popover-foreground: hsl(0, 0%, 0%);
            --popover-border: hsl(0, 0%, 93%);
            --elevate-1: rgba(0,0,0, .03);
            --elevate-2: rgba(0,0,0, .08);
            --radius: 0.375rem;
        }

        body {
            font-family: Inter, Roboto, sans-serif;
            background-color: var(--background);
            color: var(--foreground);
            line-height: 1.5;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }

        /* Header Styles */
        .header {
            border-bottom: 1px solid var(--border);
            background-color: var(--background);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 1rem 0;
        }

        .header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--foreground);
            margin-bottom: 0.25rem;
        }

        .header p {
            color: var(--muted-foreground);
            font-size: 0.875rem;
        }

        /* Organization Chart Container */
        .org-chart {
            padding: 2rem 1rem;
            min-height: calc(100vh - 120px);
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Employee Card Styles */
        .employee-card {
            background-color: var(--card);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 1rem;
            width: 280px;
            transition: all 0.2s ease;
            position: relative;
            cursor: pointer;
        }

        .employee-card:hover {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .employee-card:hover::after {
            content: "";
            position: absolute;
            inset: -1px;
            background-color: var(--elevate-1);
            border-radius: inherit;
            pointer-events: none;
            z-index: 1;
        }

        .card-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.75rem;
            position: relative;
            z-index: 2;
        }

        .employee-avatar {
            width: 4rem;
            height: 4rem;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--card-border);
        }

        .employee-info {
            text-align: center;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .employee-name {
            font-weight: 600;
            color: var(--card-foreground);
            font-size: 1rem;
            line-height: 1.25;
        }

        .employee-title {
            color: var(--muted-foreground);
            font-size: 0.875rem;
            line-height: 1.25;
        }

        /* Menu Button */
        .menu-button {
            background: transparent;
            border: none;
            border-radius: 0.25rem;
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            position: relative;
        }

        .menu-button:hover {
            background-color: var(--elevate-1);
        }

        .menu-button svg {
            width: 1rem;
            height: 1rem;
            color: var(--muted-foreground);
        }

        /* Dropdown Menu */
        .dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background-color: var(--popover);
            min-width: 160px;
            border: 1px solid var(--popover-border);
            border-radius: var(--radius);
            z-index: 1000;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            top: 100%;
            margin-top: 0.25rem;
        }

        .dropdown-content.show {
            display: block;
        }

        .dropdown-item {
            color: var(--popover-foreground);
            padding: 0.5rem 0.75rem;
            text-decoration: none;
            display: block;
            font-size: 0.875rem;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: right;
        }

        .dropdown-item:hover {
            background-color: var(--elevate-1);
        }

        .dropdown-item.destructive {
            color: var(--destructive);
        }

        .dropdown-item:first-child {
            border-top-left-radius: var(--radius);
            border-top-right-radius: var(--radius);
        }

        .dropdown-item:last-child {
            border-bottom-left-radius: var(--radius);
            border-bottom-right-radius: var(--radius);
        }

        /* Organization Chart Layout - النظام الجديد */
        .org-level {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 0;
            position: relative;
        }

        .level-employees {
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            justify-content: center;
            flex-wrap: wrap;
            position: relative;
        }

        .employee-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* خطوط الاتصال - النظام المحسن */
        .connection-lines {
            position: relative;
            height: 60px;
            width: 100%;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .vertical-line-up {
            position: absolute;
            width: 2px;
            height: 30px;
            background-color: var(--primary);
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        .vertical-line-up::after {
            content: "↑";
            color: black;
            font-size: 14px;
            line-height: 24px;
            text-align: center;
            display: inline-block;
            width: 24px;
            height: 24px;
            background: yellow;
            border-radius: 50%;
            position: absolute;
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
        }

        .horizontal-line {
            position: absolute;
            height: 2px;
            background-color: var(--primary);
            top: 30px;
            z-index: 1;
        }

        .vertical-line-down {
            position: absolute;
            width: 2px;
            height: 30px;
            background-color: var(--primary);
            top: 30px;
            z-index: 1;
        }

        /* خطوط ربط الموظفين بالمدير */
        .employee-container {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* خط عمودي من كل موظف إلى المدير - فقط للمستويات بعد الأول */
        .org-level:not(:first-child) .employee-container::before {
            content: '';
            position: absolute;
            width: 2px;
            height: 30px;
            background-color: var(--primary);
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        /* خط أفقي يربط الموظفين في نفس المستوى - فقط إذا كان هناك أكثر من موظف */
        .level-employees:has(.employee-container:nth-child(2))::before {
            content: '';
            position: absolute;
            height: 2px;
            background-color: var(--primary);
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
            width: calc(100% - 280px);
            min-width: 200px;
        }

        /* خط عمودي من المدير إلى الخط الأفقي - فقط إذا كان هناك أكثر من موظف */
        .level-employees:has(.employee-container:nth-child(2))::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 30px;
            background-color: var(--primary);
            top: -60px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        /* خط عمودي بسيط للموظف الواحد - فقط للمستويات بعد الأول */
        .org-level:not(:first-child) .level-employees:not(:has(.employee-container:nth-child(2)))::after {
            content: '';
            position: absolute;
            width: 2px;
            height: 30px;
            background-color: var(--primary);
            top: -30px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 1;
        }

        /* تحسين مواضع الخطوط */
        .org-level {
            position: relative;
        }

        .org-level:not(:first-child) {
            margin-top: 20px;
        }

        /* تحسين الخطوط للشاشات الصغيرة */
        @media (max-width: 768px) {
            .employee-container::before {
                height: 20px;
                top: -20px;
            }
            
            .level-employees::before {
                top: -20px;
            }
            
            .level-employees::after {
                height: 20px;
                top: -40px;
            }
        }

        /* Toast Notification */
        .toast {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            background-color: var(--card);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 1rem;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            max-width: 300px;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--foreground);
        }

        .toast-message {
            color: var(--muted-foreground);
            font-size: 0.875rem;
        }

        /* Loading State */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            color: var(--muted-foreground);
            font-size: 1.125rem;
        }

        .loading::before {
            content: '';
            width: 24px;
            height: 24px;
            border: 2px solid var(--border);
            border-top: 2px solid var(--primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Error State */
        .error-state {
            text-align: center;
            padding: 2rem;
            color: var(--muted-foreground);
        }

        .error-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--destructive);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .level-employees {
                gap: 1rem;
            }
            
            .employee-card {
                width: 240px;
            }
            
            .org-chart {
                padding: 1rem 0.5rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-sitemap me-2"></i>الهيكل التنظيمي للشركة</h1>
            <p>هيكل الشركة وعلاقات التبعية الإدارية - بيانات ديناميكية</p>
        </div>
    </header>

    <main>
        <div id="orgChart" class="org-chart">
            <div class="loading">جاري تحميل الهيكل التنظيمي...</div>
        </div>
    </main>

    <!-- Toast notification container -->
    <div id="toast" class="toast">
        <div class="toast-title" id="toastTitle"></div>
        <div class="toast-message" id="toastMessage"></div>
    </div>

    <script>
        // Toast notification functions
        function showToast(title, message) {
            const toast = document.getElementById('toast');
            const toastTitle = document.getElementById('toastTitle');
            const toastMessage = document.getElementById('toastMessage');
            
            toastTitle.textContent = title;
            toastMessage.textContent = message;
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }

        // Dropdown menu functionality
        function toggleDropdown(dropdownId) {
            document.querySelectorAll('.dropdown-content').forEach(content => {
                if (content.id !== dropdownId) {
                    content.classList.remove('show');
                }
            });
            
            const dropdown = document.getElementById(dropdownId);
            dropdown.classList.toggle('show');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            if (!event.target.matches('.menu-button') && !event.target.closest('.menu-button')) {
                document.querySelectorAll('.dropdown-content').forEach(content => {
                    content.classList.remove('show');
                });
            }
        });

        // Employee action handlers
        function viewProfile(employee) {
            console.log('View profile:', employee.name);
            showToast('عرض الملف الشخصي', `تم عرض ملف ${employee.name} الشخصي`);
        }

        function editEmployee(employee) {
            console.log('Edit employee:', employee.name);
            showToast('تعديل الموظف', `تم فتح نموذج تعديل ${employee.name}`);
        }

        function removeEmployee(employee) {
            console.log('Remove employee:', employee.name);
            showToast('حذف الموظف', `تم طلب حذف ${employee.name} من النظام`);
        }

        // Get initials from name
        function getInitials(name) {
            return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2);
        }

        // Create employee card HTML
        function createEmployeeCard(employee) {
            const initials = getInitials(employee.name);
            const avatarSrc = employee.profile_picture ? 
                `/${employee.profile_picture}` : 
                `/storage/profile_pictures/1757589898_476653860_2660576597666150_4039160244521826942_n.jpg`;
            
            return `
                <div class="employee-card" data-testid="card-employee-${employee.id}">
                    <div class="card-content">
                        <img src="${avatarSrc}" 
                             alt="${employee.name} profile" 
                             class="employee-avatar"
                             data-testid="img-avatar-${employee.id}"
                             onerror="this.src='/storage/profile_pictures/1757589898_476653860_2660576597666150_4039160244521826942_n.jpg'; this.nextElementSibling.style.display='flex'; this.onerror=null;">
                        <div class="employee-avatar" style="display: none; align-items: center; justify-content: center; background: #F3F4F6; color: #6B7280; font-weight: 600;">
                            ${initials}
                        </div>
                        
                        <div class="employee-info">
                            <h3 class="employee-name" data-testid="text-name-${employee.id}">
                                ${employee.name}
                            </h3>
                            <p class="employee-title" data-testid="text-title-${employee.id}">
                                ${employee.position || employee.job_title || 'موظف'}
                            </p>
                            ${employee.department ? `<small style="color: var(--muted-foreground); font-size: 0.75rem;">${employee.department.name || employee.department}</small>` : ''}
                        </div>

                        <div class="dropdown">
                            <button class="menu-button" 
                                    onclick="toggleDropdown('dropdown-${employee.id}')"
                                    data-testid="button-menu-${employee.id}">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <circle cx="12" cy="12" r="1"/>
                                    <circle cx="19" cy="12" r="1"/>
                                    <circle cx="5" cy="12" r="1"/>
                                </svg>
                            </button>
                            <div id="dropdown-${employee.id}" class="dropdown-content">
                                <button class="dropdown-item" 
                                        onclick="viewProfile(${JSON.stringify(employee).replace(/"/g, '&quot;')})"
                                        data-testid="button-view-profile-${employee.id}">
                                    <i class="fas fa-user me-2"></i>عرض الملف الشخصي
                                </button>
                                <button class="dropdown-item" 
                                        onclick="editEmployee(${JSON.stringify(employee).replace(/"/g, '&quot;')})"
                                        data-testid="button-edit-${employee.id}">
                                    <i class="fas fa-edit me-2"></i>تعديل الموظف
                                </button>
                                <button class="dropdown-item" 
                                        onclick="viewTasks(${JSON.stringify(employee).replace(/"/g, '&quot;')})"
                                        data-testid="button-tasks-${employee.id}">
                                    <i class="fas fa-tasks me-2"></i>عرض المهام
                                </button>
                                <button class="dropdown-item destructive" 
                                        onclick="removeEmployee(${JSON.stringify(employee).replace(/"/g, '&quot;')})"
                                        data-testid="button-remove-${employee.id}">
                                    <i class="fas fa-trash me-2"></i>حذف
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }

        // النظام المحسن لرسم الهيكل التنظيمي مع خطوط متصلة
        function renderOrganizationChart(organizationData) {
            const container = document.getElementById('orgChart');
            
            if (!organizationData || !organizationData.length) {
                container.innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-triangle"></i>
                        <h3>لا توجد بيانات للعرض</h3>
                        <p>لم يتم العثور على موظفين في النظام</p>
                    </div>
                `;
                return;
            }

            let html = '';

            // دالة لرسم مستوى واحد مع الخطوط
            function renderLevel(employees, isFirstLevel = false, hasChildren = false) {
                if (!employees || employees.length === 0) return '';
                
                let levelHtml = '<div class="org-level">';
                
                // لا نضيف أي خطوط للمستوى الأول (الرئيس التنفيذي)
                if (!isFirstLevel) {
                    levelHtml += '<div class="connection-lines">';
                    levelHtml += '<div class="vertical-line-up"></div>';
                    levelHtml += '</div>';
                }
                
                levelHtml += '<div class="level-employees">';
                
                employees.forEach((employee, index) => {
                    levelHtml += `
                        <div class="employee-container">
                            ${createEmployeeCard(employee)}
                        </div>
                    `;
                });
                
                levelHtml += '</div>';
                levelHtml += '</div>';
                return levelHtml;
            }

            // رسم المستويات - الهيكل الجديد
            const ceo = organizationData.filter(emp => !emp.manager_id || emp.manager_id === null);
            const level2 = organizationData.filter(emp => emp.manager_id && ceo.some(ceoEmp => ceoEmp.id === emp.manager_id));
            const level3 = organizationData.filter(emp => emp.manager_id && level2.some(level2Emp => level2Emp.id === emp.manager_id));

            // المستوى الأول - الرئيس التنفيذي
            if (ceo.length > 0) {
                html += renderLevel(ceo, true, level2.length > 0);
            }
            
            // المستوى الثاني - الموظفين المباشرين
            if (level2.length > 0) {
                html += renderLevel(level2, false, level3.length > 0);
            }
            
            // المستوى الثالث - الموظفين تحت كل مدير
            if (level3.length > 0) {
                html += renderLevel(level3, false, false);
            }

            container.innerHTML = html;
        }

        // Load organizational data from server
        async function loadOrganizationData() {
            try {
                const response = await fetch('/api/organizational-chart');
                const data = await response.json();
                
                if (data.success) {
                    renderOrganizationChart(data.data);
                } else {
                    throw new Error(data.message || 'خطأ في تحميل البيانات');
                }
            } catch (error) {
                console.error('Error loading organizational data:', error);
                document.getElementById('orgChart').innerHTML = `
                    <div class="error-state">
                        <i class="fas fa-exclamation-circle"></i>
                        <h3>خطأ في تحميل البيانات</h3>
                        <p>${error.message}</p>
                        <button onclick="loadOrganizationData()" style="margin-top: 1rem; padding: 0.5rem 1rem; background: var(--primary); color: white; border: none; border-radius: var(--radius); cursor: pointer;">
                            إعادة المحاولة
                        </button>
                    </div>
                `;
            }
        }

        // Additional action handlers
        function viewTasks(employee) {
            console.log('View tasks:', employee.name);
            showToast('عرض المهام', `تم عرض مهام ${employee.name}`);
        }

        // Initialize the chart when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadOrganizationData();
        });

        // Handle image loading errors with a default avatar
        document.addEventListener('error', function(e) {
            if (e.target.tagName === 'IMG' && e.target.classList.contains('employee-avatar')) {
                e.target.style.display = 'none';
                const initialsDiv = e.target.nextElementSibling;
                if (initialsDiv && initialsDiv.classList.contains('employee-avatar')) {
                    initialsDiv.style.display = 'flex';
                }
            }
        }, true);
    </script>
</body>
</html>
