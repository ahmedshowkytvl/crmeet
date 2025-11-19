document.addEventListener('DOMContentLoaded', function() {
    const orgChart = document.getElementById('orgChart');
    
    if (!orgChart) {
        console.error('Org chart container not found');
        return;
    }
    
    let employees = [];
    try {
        const dataAttr = orgChart.getAttribute('data-employees');
        if (dataAttr) {
            employees = JSON.parse(dataAttr);
        }
    } catch (e) {
        console.error('Error parsing employees data:', e);
        employees = [];
    }
    
    // Create organizational chart
    createOrganizationalChart(employees);
    
    function createOrganizationalChart(employees) {
        // Clear existing content
        orgChart.innerHTML = '';
        
        console.log('Employees data:', employees);
        
        if (!employees || employees.length === 0) {
            orgChart.innerHTML = '<div style="text-align: center; padding: 50px; color: #666;">لا يوجد موظفين في هذا القسم</div>';
            return;
        }
        
        // Group employees by hierarchy level
        globalHierarchy = buildHierarchy(employees);
        
        console.log('Hierarchy:', globalHierarchy);
        
        // Calculate positions and draw chart
        drawChart(globalHierarchy);
    }
    
    function buildHierarchy(employees) {
        const hierarchy = {
            managers: [],
            teamLeaders: [],
            employees: []
        };
        
        employees.forEach(employee => {
            if (employee.role) {
                switch(employee.role.slug) {
                    case 'admin':
                    case 'manager':
                    case 'ceo':
                    case 'head_manager':
                        hierarchy.managers.push(employee);
                        break;
                    case 'team_leader':
                        hierarchy.teamLeaders.push(employee);
                        break;
                    default:
                        hierarchy.employees.push(employee);
                }
            } else {
                // If no role, check if they have subordinates (likely a manager)
                if (employee.subordinates && employee.subordinates.length > 0) {
                    hierarchy.managers.push(employee);
                } else {
                    hierarchy.employees.push(employee);
                }
            }
        });
        
        // If no managers found, put the first employee as manager
        if (hierarchy.managers.length === 0 && hierarchy.employees.length > 0) {
            hierarchy.managers.push(hierarchy.employees.shift());
        }
        
        return hierarchy;
    }
    
    function drawChart(hierarchy) {
        const containerWidth = orgChart.offsetWidth;
        const containerHeight = 600;
        const nodeSize = 120;
        const managerSize = 140;
        const levelHeight = 200;
        
        console.log('Drawing chart - Container width:', containerWidth);
        console.log('Managers:', hierarchy.managers.length);
        console.log('Team Leaders:', hierarchy.teamLeaders.length);
        console.log('Employees:', hierarchy.employees.length);
        
        // Draw managers (top level) - المديرين في الأعلى
        if (hierarchy.managers.length > 0) {
            const managerX = (containerWidth - managerSize) / 2;
            const managerY = 50;
            hierarchy.managers.forEach((manager, index) => {
                createEmployeeNode(manager, managerX, managerY, 'manager');
                
                // إضافة خط ربط واحد من المدير
                if (hierarchy.teamLeaders.length > 0 || hierarchy.employees.length > 0) {
                    const startX = managerX + managerSize/2;
                    const startY = managerY + managerSize;
                    const endY = managerY + levelHeight;
                    drawConnectionLine(startX, startY, startX, endY);
                }
            });
        }
        
        // Draw team leaders (middle level) - قادة الفرق في الوسط
        if (hierarchy.teamLeaders.length > 0) {
            const teamLeaderX = (containerWidth - (hierarchy.teamLeaders.length * nodeSize)) / 2;
            const teamLeaderY = 250;
            hierarchy.teamLeaders.forEach((teamLeader, index) => {
                const x = teamLeaderX + (index * nodeSize);
                createEmployeeNode(teamLeader, x, teamLeaderY, 'team-leader');
                
                // إضافة خطوط ربط من قادة الفرق
                if (hierarchy.employees.length > 0) {
                    const startX = x + nodeSize/2;
                    const startY = teamLeaderY + nodeSize;
                    const endY = teamLeaderY + levelHeight;
                    drawConnectionLine(startX, startY, startX, endY);
                }
            });
        }
        
        // Draw employees (bottom level) - الموظفين في الأسفل
        if (hierarchy.employees.length > 0) {
            const employeesPerRow = Math.min(3, hierarchy.employees.length);
            const employeeX = (containerWidth - (employeesPerRow * nodeSize)) / 2;
            const employeeY = 450;
            
            hierarchy.employees.forEach((employee, index) => {
                const col = index % employeesPerRow;
                const x = employeeX + (col * nodeSize);
                createEmployeeNode(employee, x, employeeY, 'employee');
            });
        }
    }
    
    function drawConnectionLine(startX, startY, endX, endY) {
        // خط عمودي من المدير
        const verticalLine = document.createElement('div');
        verticalLine.className = 'connection-line';
        verticalLine.style.position = 'absolute';
        verticalLine.style.left = (startX - 2) + 'px';
        verticalLine.style.top = startY + 'px';
        verticalLine.style.width = '4px';
        verticalLine.style.height = (endY - startY) + 'px';
        verticalLine.style.backgroundColor = '#28a745';
        verticalLine.style.zIndex = '1';
        orgChart.appendChild(verticalLine);
        
        // إذا كان هناك موظفين، أضف خطوط أفقية
        if (globalHierarchy && globalHierarchy.employees && globalHierarchy.employees.length > 0) {
            const horizontalY = endY - 50;
            const employeeCount = Math.min(3, globalHierarchy.employees.length);
            const totalWidth = (employeeCount - 1) * 120;
            const horizontalStartX = startX - totalWidth / 2;
            
            // خط أفقي
            const horizontalLine = document.createElement('div');
            horizontalLine.className = 'connection-line';
            horizontalLine.style.position = 'absolute';
            horizontalLine.style.left = horizontalStartX + 'px';
            horizontalLine.style.top = (horizontalY - 2) + 'px';
            horizontalLine.style.width = totalWidth + 'px';
            horizontalLine.style.height = '4px';
            horizontalLine.style.backgroundColor = '#28a745';
            horizontalLine.style.zIndex = '1';
            orgChart.appendChild(horizontalLine);
            
            // خطوط عمودية للموظفين
            for (let i = 0; i < employeeCount; i++) {
                const employeeX = horizontalStartX + (i * 120) + 60;
                const verticalEmployeeLine = document.createElement('div');
                verticalEmployeeLine.className = 'connection-line';
                verticalEmployeeLine.style.position = 'absolute';
                verticalEmployeeLine.style.left = (employeeX - 2) + 'px';
                verticalEmployeeLine.style.top = horizontalY + 'px';
                verticalEmployeeLine.style.width = '4px';
                verticalEmployeeLine.style.height = '50px';
                verticalEmployeeLine.style.backgroundColor = '#28a745';
                verticalEmployeeLine.style.zIndex = '1';
                orgChart.appendChild(verticalEmployeeLine);
            }
        }
    }
    
    // Store hierarchy globally for connection lines
    let globalHierarchy = null;
    
    function createEmployeeNode(employee, x, y, roleClass) {
        console.log('Creating employee node:', employee.name, 'at position:', x, y, 'role:', roleClass);
        
        const node = document.createElement('div');
        node.className = 'employee-node';
        node.style.left = x + 'px';
        node.style.top = y + 'px';
        
        // Get profile picture or create initials
        const profilePicture = employee.profile_picture || employee.profile_photo;
        const initials = getInitials(employee.name);
        
        node.innerHTML = `
            <div class="employee-card ${roleClass}">
                <div class="employee-circle">
                    ${profilePicture ? 
                        '<img src="/storage/' + profilePicture + '" alt="' + employee.name + '" onerror="this.style.display=\'none\'; this.nextElementSibling.style.display=\'flex\';"><div class="initials" style="display: none;">' + initials + '</div>' :
                        '<div class="initials">' + initials + '</div>'
                    }
                </div>
                <div class="employee-info">
                    <div class="employee-name">${employee.name}</div>
                    <div class="employee-title">${employee.job_title || employee.position || 'موظف'}</div>
                </div>
            </div>
        `;
        
        // Add click event for modal
        node.addEventListener('click', function() {
            showEmployeeDetails(employee);
        });
        
        orgChart.appendChild(node);
    }
    
    function getInitials(name) {
        return name.split(' ').map(word => word.charAt(0)).join('').toUpperCase().substring(0, 2);
    }
    
    function showEmployeeDetails(employee) {
        const modal = new bootstrap.Modal(document.getElementById('employeeModal'));
        const detailsContainer = document.getElementById('employeeDetails');
        
        const profilePicture = employee.profile_picture || employee.profile_photo;
        const initials = getInitials(employee.name);
        
        detailsContainer.innerHTML = `
            <div class="row">
                <div class="col-md-4 text-center">
                    <div class="employee-circle ${getRoleClass(employee)} mb-3" style="width: 100px; height: 100px; margin: 0 auto;">
                        ${profilePicture ? 
                            '<img src="/storage/' + profilePicture + '" alt="' + employee.name + '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">' :
                            '<div class="initials" style="font-size: 36px; line-height: 100px;">' + initials + '</div>'
                        }
                    </div>
                </div>
                <div class="col-md-8">
                    <h5>${employee.name}</h5>
                    <p class="text-muted">${employee.job_title || employee.position || 'موظف'}</p>
                    <hr>
                    <div class="row">
                        <div class="col-sm-6">
                            <strong>البريد الإلكتروني:</strong><br>
                            <a href="mailto:${employee.email}">${employee.email}</a>
                        </div>
                        <div class="col-sm-6">
                            <strong>هاتف العمل:</strong><br>
                            ${employee.phone_work || 'غير محدد'}
                        </div>
                        <div class="col-sm-6 mt-2">
                            <strong>المدير المباشر:</strong><br>
                            ${employee.manager ? employee.manager.name : 'غير محدد'}
                        </div>
                        <div class="col-sm-6 mt-2">
                            <strong>الدور:</strong><br>
                            <span class="badge bg-${getRoleBadgeClass(employee)}">${employee.role ? employee.role.name_ar || employee.role.name : 'موظف'}</span>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        modal.show();
    }
    
    function getRoleClass(employee) {
        if (!employee.role) return 'default';
        switch(employee.role.slug) {
            case 'admin':
            case 'manager':
            case 'head_manager':
                return 'manager';
            case 'team_leader':
                return 'team-leader';
            default:
                return 'employee';
        }
    }
    
    function getRoleBadgeClass(employee) {
        if (!employee.role) return 'secondary';
        switch(employee.role.slug) {
            case 'admin':
                return 'danger';
            case 'manager':
            case 'head_manager':
                return 'warning';
            case 'team_leader':
                return 'info';
            default:
                return 'success';
        }
    }
});
