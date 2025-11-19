<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>الهيكل التنظيمي</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Cairo', sans-serif;
            background: #FFFFFF;
            overflow-x: auto;
            overflow-y: auto;
            height: 100vh;
            width: 100vw;
        }
        
        /* Full Screen Container */
        .org-chart-fullscreen {
            width: 100vw;
            height: 100vh;
            background: #FFFFFF;
            position: relative;
            padding: 40px 20px;
            overflow: auto;
        }
        
        /* Header */
        .chart-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 20px 0;
        }
        
        .chart-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 10px;
        }
        
        .chart-subtitle {
            font-size: 1.1rem;
            color: #6B7280;
            font-weight: 400;
        }
        
        /* Employee Cards - Clean Modern Design */
        .employee-card {
            background: white;
            border: 2px solid #E5E7EB;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            width: 220px;
            min-height: 200px;
            position: absolute;
            cursor: pointer;
        }
        
        .employee-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            border-color: #D1D5DB;
        }
        
        /* CEO Card Special Styling */
        .employee-card.ceo {
            border-color: #3B82F6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
        }
        
        .employee-card.ceo:hover {
            border-color: #2563EB;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.2);
        }
        
        /* Manager Card Special Styling */
        .employee-card.manager {
            border-color: #10B981;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.15);
        }
        
        .employee-card.manager:hover {
            border-color: #059669;
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.2);
        }
        
        /* Employee Card Special Styling */
        .employee-card.employee {
            border-color: #6B7280;
            box-shadow: 0 4px 12px rgba(107, 114, 128, 0.1);
        }
        
        .employee-card.employee:hover {
            border-color: #4B5563;
            box-shadow: 0 8px 25px rgba(107, 114, 128, 0.15);
        }
        
        /* Avatar Styles */
        .employee-avatar {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 20px;
            overflow: hidden;
            border: 3px solid #F3F4F6;
            background: #F9FAFB;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .employee-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .employee-avatar .initials {
            font-size: 32px;
            font-weight: 600;
            color: #6B7280;
        }
        
        /* CEO Avatar */
        .employee-card.ceo .employee-avatar {
            border-color: #DBEAFE;
            background: #EFF6FF;
        }
        
        .employee-card.ceo .employee-avatar .initials {
            color: #3B82F6;
        }
        
        /* Manager Avatar */
        .employee-card.manager .employee-avatar {
            border-color: #D1FAE5;
            background: #ECFDF5;
        }
        
        .employee-card.manager .employee-avatar .initials {
            color: #10B981;
        }
        
        /* Text Styles */
        .employee-name {
            font-size: 18px;
            font-weight: 600;
            color: #000000;
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .employee-title {
            font-size: 14px;
            color: #6B7280;
            font-weight: 400;
            line-height: 1.4;
        }
        
        /* Options Menu */
        .options-menu {
            position: absolute;
            top: 20px;
            right: 20px;
            color: #9CA3AF;
            cursor: pointer;
            font-size: 16px;
            transition: color 0.2s ease;
            z-index: 10;
        }
        
        .options-menu:hover {
            color: #6B7280;
        }
        
        /* Connection Lines */
        .connection-line {
            position: absolute;
            background: #D1D5DB;
            z-index: 1;
        }
        
        .connection-line.vertical {
            width: 2px;
        }
        
        .connection-line.horizontal {
            height: 2px;
        }
        
        /* Loading Animation */
        .loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 400px;
            color: #6B7280;
            font-size: 18px;
        }
        
        .loading::before {
            content: '';
            width: 24px;
            height: 24px;
            border: 2px solid #E5E7EB;
            border-top: 2px solid #3B82F6;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-left: 10px;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            background: white;
            border: 1px solid #E5E7EB;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 8px 0;
            z-index: 1000;
        }
        
        .dropdown-item {
            display: block;
            padding: 10px 16px;
            color: #374151;
            text-decoration: none;
            transition: background-color 0.2s ease;
            font-size: 14px;
        }
        
        .dropdown-item:hover {
            background-color: #F9FAFB;
            color: #111827;
        }
        
        .dropdown-item i {
            width: 16px;
            text-align: center;
        }
        
        /* Modal Enhancements */
        .modal-content {
            border-radius: 12px;
            border: 1px solid #E5E7EB;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .modal-header {
            background: #F9FAFB;
            border-bottom: 1px solid #E5E7EB;
            border-radius: 12px 12px 0 0;
        }
        
        .modal-title {
            color: #111827;
            font-weight: 600;
        }
        
        /* Badge Styles */
        .badge {
            font-size: 12px;
            font-weight: 500;
            padding: 6px 12px;
            border-radius: 6px;
        }
        
        /* Responsive Design */
        @media (max-width: 1200px) {
            .employee-card {
                width: 200px;
                min-height: 180px;
                padding: 20px;
            }
            
            .employee-avatar {
                width: 80px;
                height: 80px;
            }
            
            .employee-avatar .initials {
                font-size: 28px;
            }
        }
        
        @media (max-width: 768px) {
            .org-chart-fullscreen {
                padding: 20px 10px;
            }
            
            .chart-title {
                font-size: 2rem;
            }
            
            .employee-card {
                width: 180px;
                min-height: 160px;
                padding: 18px;
            }
            
            .employee-avatar {
                width: 70px;
                height: 70px;
            }
            
            .employee-avatar .initials {
                font-size: 24px;
            }
            
            .employee-name {
                font-size: 16px;
            }
            
            .employee-title {
                font-size: 13px;
            }
        }
        
        @media (max-width: 576px) {
            .employee-card {
                width: 160px;
                min-height: 140px;
                padding: 15px;
            }
            
            .employee-avatar {
                width: 60px;
                height: 60px;
            }
            
            .employee-avatar .initials {
                font-size: 20px;
            }
            
            .employee-name {
                font-size: 14px;
            }
            
            .employee-title {
                font-size: 12px;
            }
        }
        
        /* Animation for card appearance */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .employee-card {
            animation: fadeInUp 0.6s ease forwards;
        }
        
        /* Stagger animation for multiple cards */
        .employee-card:nth-child(1) { animation-delay: 0.1s; }
        .employee-card:nth-child(2) { animation-delay: 0.2s; }
        .employee-card:nth-child(3) { animation-delay: 0.3s; }
        .employee-card:nth-child(4) { animation-delay: 0.4s; }
        .employee-card:nth-child(5) { animation-delay: 0.5s; }
        .employee-card:nth-child(6) { animation-delay: 0.6s; }
        .employee-card:nth-child(7) { animation-delay: 0.7s; }
        .employee-card:nth-child(8) { animation-delay: 0.8s; }
    </style>
</head>
<body>
    <!-- Full Screen Organizational Chart -->
    <div class="org-chart-fullscreen">
        <!-- Header -->
        <div class="chart-header">
            <h1 class="chart-title">
                <i class="fas fa-sitemap me-3"></i>
                الهيكل التنظيمي
            </h1>
            <p class="chart-subtitle">عرض شامل للهيكل التنظيمي للشركة</p>
        </div>
        
        <!-- Chart Container -->
        <div id="organizationalChart" style="position: relative; min-height: 600px;">
            <div class="loading">جاري تحميل الهيكل التنظيمي...</div>
        </div>
    </div>

    <!-- Employee Details Modal -->
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">تفاصيل الموظف</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="employeeDetails">
                    <!-- Employee details will be loaded here -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إغلاق</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Organizational Chart Data
        const orgChartData = {
            "hierarchy": {
                "CEO": {
                    "name": "Ronald Richards",
                    "role": "CEO",
                    "reports_to": null,
                    "children": [
                        {
                            "name": "Wade Warren",
                            "role": "Computer and Information Research Scientist",
                            "children": [
                                {
                                    "name": "Cody Fisher",
                                    "role": "Senior Network Engineer"
                                },
                                {
                                    "name": "Bessie Cooper",
                                    "role": "Account Representative"
                                },
                                {
                                    "name": "Savannah Nguyen",
                                    "role": "System Architect"
                                },
                                {
                                    "name": "Arlene McCoy",
                                    "role": "Senior Network Architect"
                                }
                            ]
                        },
                        {
                            "name": "Annette Black",
                            "role": "Data Center Support Specialist"
                        },
                        {
                            "name": "Theresa Webb",
                            "role": "Systems Designer"
                        }
                    ]
                }
            }
        };

        // Profile picture path
        const profilePicturePath = '/storage/profile_pictures/1757589898_476653860_2660576597666150_4039160244521826942_n.jpg';

        document.addEventListener('DOMContentLoaded', function() {
            renderOrganizationalChart();
        });

        function renderOrganizationalChart() {
            const container = document.getElementById('organizationalChart');
            
            setTimeout(() => {
                container.innerHTML = '';
                createChart();
            }, 1000);
        }

        function createChart() {
            const container = document.getElementById('organizationalChart');
            const hierarchy = orgChartData.hierarchy;
            
            // Calculate positions
            const containerWidth = container.offsetWidth;
            const levelHeight = 280;
            const cardWidth = 220;
            const cardHeight = 200;
            
            // CEO Level (Top)
            const ceo = hierarchy.CEO;
            const ceoX = (containerWidth - cardWidth) / 2;
            const ceoY = 50;
            
            // Create CEO card
            createEmployeeCard(ceo, ceoX, ceoY, 'ceo');
            
            // Level 1 - Direct reports to CEO
            const level1Employees = ceo.children;
            const level1Y = ceoY + cardHeight + 120;
            const level1Spacing = Math.max(320, (containerWidth - (level1Employees.length * cardWidth)) / (level1Employees.length + 1));
            
            level1Employees.forEach((employee, index) => {
                const x = level1Spacing + (index * (cardWidth + level1Spacing));
                createEmployeeCard(employee, x, level1Y, 'manager');
                
                // Draw connection line from CEO
                drawConnectionLine(
                    ceoX + cardWidth/2, ceoY + cardHeight,
                    x + cardWidth/2, level1Y,
                    'vertical'
                );
                
                // Level 2 - Subordinates (only for Wade Warren)
                if (employee.children && employee.children.length > 0) {
                    const level2Y = level1Y + cardHeight + 120;
                    const level2Spacing = Math.max(280, (containerWidth - (employee.children.length * cardWidth)) / (employee.children.length + 1));
                    
                    employee.children.forEach((subordinate, subIndex) => {
                        const subX = level2Spacing + (subIndex * (cardWidth + level2Spacing));
                        createEmployeeCard(subordinate, subX, level2Y, 'employee');
                        
                        // Draw connection line from manager
                        drawConnectionLine(
                            x + cardWidth/2, level1Y + cardHeight,
                            subX + cardWidth/2, level2Y,
                            'vertical'
                        );
                    });
                }
            });
        }

        function createEmployeeCard(employee, x, y, roleClass) {
            const container = document.getElementById('organizationalChart');
            
            const card = document.createElement('div');
            card.className = 'employee-card';
            card.style.left = x + 'px';
            card.style.top = y + 'px';
            
            const initials = getInitials(employee.name);
            
            card.innerHTML = `
                <div class="options-menu" title="خيارات">
                    <i class="fas fa-ellipsis-v"></i>
                </div>
                <div class="employee-avatar">
                    <img src="${profilePicturePath}" alt="${employee.name}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="initials" style="display: none;">${initials}</div>
                </div>
                <div class="employee-name">${employee.name}</div>
                <div class="employee-title">${employee.role}</div>
            `;
            
            // Add click event
            card.addEventListener('click', function(e) {
                if (!e.target.closest('.options-menu')) {
                    showEmployeeDetails(employee);
                }
            });
            
            // Add options menu click event
            const optionsMenu = card.querySelector('.options-menu');
            optionsMenu.addEventListener('click', function(e) {
                e.stopPropagation();
                showEmployeeOptions(employee, optionsMenu);
            });
            
            // Add hover effects
            card.addEventListener('mouseenter', function() {
                this.style.zIndex = '20';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.zIndex = '10';
            });
            
            container.appendChild(card);
        }

        function drawConnectionLine(startX, startY, endX, endY, type) {
            const container = document.getElementById('organizationalChart');
            
            if (type === 'vertical') {
                const line = document.createElement('div');
                line.className = 'connection-line vertical';
                line.style.left = startX + 'px';
                line.style.top = startY + 'px';
                line.style.height = (endY - startY) + 'px';
                container.appendChild(line);
            } else {
                const line = document.createElement('div');
                line.className = 'connection-line horizontal';
                line.style.left = Math.min(startX, endX) + 'px';
                line.style.top = startY + 'px';
                line.style.width = Math.abs(endX - startX) + 'px';
                container.appendChild(line);
            }
        }

        function getInitials(name) {
            return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2);
        }

        function showEmployeeDetails(employee) {
            const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
            const detailsContainer = document.getElementById('employeeDetails');
            
            const initials = getInitials(employee.name);
            
            detailsContainer.innerHTML = `
                <div class="row">
                    <div class="col-md-4 text-center">
                        <div class="employee-avatar mx-auto mb-3" style="width: 120px; height: 120px;">
                            <img src="${profilePicturePath}" alt="${employee.name}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="initials" style="font-size: 48px; display: none;">${initials}</div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h4 class="mb-2">${employee.name}</h4>
                        <p class="text-muted mb-3">${employee.role}</p>
                        <hr>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <strong>المنصب:</strong><br>
                                <span class="text-muted">${employee.role}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>المستوى:</strong><br>
                                <span class="badge bg-primary">${getRoleLevel(employee)}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>التقارير إلى:</strong><br>
                                <span class="text-muted">${employee.reports_to || 'لا يوجد'}</span>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <strong>عدد المرؤوسين:</strong><br>
                                <span class="text-muted">${employee.children ? employee.children.length : 0}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            modal.show();
        }

        function getRoleLevel(employee) {
            if (employee.role === 'CEO') return 'الرئيس التنفيذي';
            if (employee.role.includes('Scientist') || employee.role.includes('Specialist') || employee.role.includes('Designer')) {
                return 'مستوى إداري';
            }
            return 'موظف';
        }

        function showEmployeeOptions(employee, menuElement) {
            // Remove existing dropdown
            const existingDropdown = menuElement.querySelector('.dropdown-menu');
            if (existingDropdown) {
                existingDropdown.remove();
            }
            
            // Create dropdown menu
            const dropdown = document.createElement('div');
            dropdown.className = 'dropdown-menu show';
            dropdown.style.position = 'absolute';
            dropdown.style.top = '100%';
            dropdown.style.right = '0';
            dropdown.style.minWidth = '150px';
            dropdown.style.zIndex = '1000';
            
            dropdown.innerHTML = `
                <a class="dropdown-item" href="#" onclick="showEmployeeDetails('${employee.name}')">
                    <i class="fas fa-eye me-2"></i>عرض التفاصيل
                </a>
                <a class="dropdown-item" href="#" onclick="sendMessage('${employee.name}')">
                    <i class="fas fa-envelope me-2"></i>إرسال رسالة
                </a>
                <a class="dropdown-item" href="#" onclick="scheduleMeeting('${employee.name}')">
                    <i class="fas fa-calendar me-2"></i>جدولة اجتماع
                </a>
                <a class="dropdown-item" href="#" onclick="viewContactCard('${employee.name}')">
                    <i class="fas fa-id-card me-2"></i>البطاقة الشخصية
                </a>
            `;
            
            // Position the dropdown
            menuElement.style.position = 'relative';
            menuElement.appendChild(dropdown);
            
            // Close dropdown when clicking outside
            setTimeout(() => {
                document.addEventListener('click', function closeDropdown(e) {
                    if (!menuElement.contains(e.target)) {
                        dropdown.remove();
                        document.removeEventListener('click', closeDropdown);
                    }
                });
            }, 100);
        }

        function sendMessage(employeeName) {
            alert(`إرسال رسالة إلى ${employeeName}`);
        }

        function scheduleMeeting(employeeName) {
            alert(`جدولة اجتماع مع ${employeeName}`);
        }

        function viewContactCard(employeeName) {
            alert(`عرض البطاقة الشخصية لـ ${employeeName}`);
        }
    </script>
</body>
</html>
