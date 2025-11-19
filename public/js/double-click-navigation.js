/**
 * Double Click Navigation System
 * يوفر وظيفة النقر المزدوج للانتقال إلى صفحة العرض للعناصر في الجداول والبطاقات
 */

document.addEventListener('DOMContentLoaded', function() {
    // إعداد النقر المزدوج للجداول
    setupTableDoubleClick();
    
    // إعداد النقر المزدوج للبطاقات
    setupCardDoubleClick();
    
    console.log('Double Click Navigation System initialized');
});

/**
 * إعداد النقر المزدوج للجداول
 */
function setupTableDoubleClick() {
    // البحث عن جميع الجداول التي تحتوي على بيانات قابلة للنقر
    const tables = document.querySelectorAll('table.table tbody');
    
    tables.forEach(table => {
        const rows = table.querySelectorAll('tr');
        
        rows.forEach(row => {
            // تحقق من وجود معرف للعنصر في الصف
            const entityId = getEntityIdFromRow(row);
            const viewRoute = getViewRouteFromTable(table);
            
            if (entityId && viewRoute) {
                // إضافة مؤشر لإظهار أن الصف قابل للنقر
                row.style.cursor = 'pointer';
                row.title = 'انقر نقرتين للعرض';
                
                // إضافة تأثير hover
                row.addEventListener('mouseenter', function() {
                    if (!this.classList.contains('table-active')) {
                        this.style.backgroundColor = '#f8f9fa';
                    }
                });
                
                row.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('table-active')) {
                        this.style.backgroundColor = '';
                    }
                });
                
                // إضافة وظيفة النقر المزدوج
                row.addEventListener('dblclick', function(e) {
                    // تجاهل النقر المزدوج على الأزرار والروابط
                    if (isClickableElement(e.target)) {
                        return;
                    }
                    
                    const url = buildViewUrl(viewRoute, entityId);
                    if (url) {
                        window.location.href = url;
                    }
                });
            }
        });
    });
}

/**
 * إعداد النقر المزدوج للبطاقات
 */
function setupCardDoubleClick() {
    // البحث عن البطاقات القابلة للنقر
    const cards = document.querySelectorAll('.card[data-entity-id], .list-group-item[data-entity-id]');
    
    cards.forEach(card => {
        const entityId = card.getAttribute('data-entity-id');
        const viewRoute = card.getAttribute('data-view-route');
        
        if (entityId && viewRoute) {
            card.style.cursor = 'pointer';
            card.title = 'انقر نقرتين للعرض';
            
            // إضافة تأثير hover
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
                this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
                this.style.transition = 'all 0.2s ease';
            });
            
            card.addEventListener('mouseleave', function() {
                this.style.transform = '';
                this.style.boxShadow = '';
            });
            
            // إضافة وظيفة النقر المزدوج
            card.addEventListener('dblclick', function(e) {
                if (isClickableElement(e.target)) {
                    return;
                }
                
                const url = buildViewUrl(viewRoute, entityId);
                if (url) {
                    window.location.href = url;
                }
            });
        }
    });
}

/**
 * استخراج معرف العنصر من صف الجدول
 */
function getEntityIdFromRow(row) {
    // البحث عن معرف العنصر في عدة أماكن محتملة
    
    // 1. البحث في data attribute
    if (row.dataset.entityId) {
        return row.dataset.entityId;
    }
    
    // معالجة خاصة لـ asset logs - البحث عن asset_id
    const parentTable = row.closest('table');
    if (parentTable && parentTable.dataset.viewRoute === 'assets.logs.asset') {
        // البحث عن رابط يحتوي على asset_id
        const assetLink = row.querySelector('a[href*="/assets/"], a[href*="asset"]');
        if (assetLink) {
            const href = assetLink.getAttribute('href');
            const matches = href.match(/\/(\d+)(?:\/|$)/);
            if (matches) {
                return matches[1];
            }
        }
        
        // محاولة أخرى: البحث في محتوى الصف عن asset ID
        // في asset logs، يمكن أن يكون asset_id مخفي في البيانات
        const assetData = row.querySelector('[data-asset-id]');
        if (assetData) {
            return assetData.dataset.assetId;
        }
    }
    
    // 2. البحث في checkbox value
    const checkbox = row.querySelector('input[type="checkbox"][value]');
    if (checkbox && checkbox.value && !isNaN(checkbox.value)) {
        return checkbox.value;
    }
    
    // 3. البحث في روابط العرض
    const viewLink = row.querySelector('a[href*="/show"], a[href*="/edit"], a[title*="عرض"], a[title*="View"]');
    if (viewLink) {
        const href = viewLink.getAttribute('href');
        const matches = href.match(/\/(\d+)(?:\/|$)/);
        if (matches) {
            return matches[1];
        }
    }
    
    // 4. البحث في forms
    const form = row.querySelector('form[action]');
    if (form) {
        const action = form.getAttribute('action');
        const matches = action.match(/\/(\d+)(?:\/|$)/);
        if (matches) {
            return matches[1];
        }
    }
    
    return null;
}

/**
 * الحصول على route العرض من الجدول
 */
function getViewRouteFromTable(table) {
    // البحث عن route في data attribute للجدول أو أحد عناصره الأبوية
    let element = table;
    while (element && element !== document.body) {
        if (element.dataset && element.dataset.viewRoute) {
            return element.dataset.viewRoute;
        }
        element = element.parentElement;
    }
    
    // محاولة استنتاج الـ route من الـ URL الحالي
    const currentPath = window.location.pathname;
    
    // قاموس المسارات المعروفة
    const routeMappings = {
        '/users': 'users.show',
        '/password-accounts': 'password-accounts.show',
        '/password-categories': 'password-categories.show',
        '/suppliers': 'suppliers.show',
        '/contacts': 'contacts.show',
        '/contact-categories': 'contact-categories.show',
        '/tasks': 'tasks.show',
        '/departments': 'departments.show',
        '/requests': 'requests.show',
        '/assets/assets': 'assets.assets.show',
        '/assets/categories': 'assets.asset-categories.show',
        '/assets/locations': 'assets.locations.show',
        '/assets/assignments': 'assets.assignments.show',
        '/assets/logs': 'assets.logs.asset'
    };
    
    for (const [path, route] of Object.entries(routeMappings)) {
        if (currentPath.includes(path)) {
            return route;
        }
    }
    
    return null;
}

/**
 * بناء URL للعرض
 */
function buildViewUrl(route, entityId) {
    // قاموس تحويل routes إلى URLs
    const routeToUrl = {
        'users.show': `/users/${entityId}`,
        'password-accounts.show': `/password-accounts/${entityId}`,
        'password-categories.show': `/password-categories/${entityId}`,
        'suppliers.show': `/suppliers/${entityId}`,
        'contacts.show': `/contacts/${entityId}`,
        'contact-categories.show': `/contact-categories/${entityId}`,
        'tasks.show': `/tasks/${entityId}`,
        'departments.show': `/departments/${entityId}`,
        'requests.show': `/requests/${entityId}`,
        'assets.assets.show': `/assets/assets/${entityId}`,
        'assets.asset-categories.show': `/assets/categories/${entityId}`,
        'assets.locations.show': `/assets/locations/${entityId}`,
        'assets.assignments.show': `/assets/assignments/${entityId}`,
        'assets.logs.asset': `/assets/logs/asset/${entityId}`
    };
    
    return routeToUrl[route] || null;
}

/**
 * تحقق من كون العنصر قابل للنقر (button, link, input, etc.)
 */
function isClickableElement(element) {
    const clickableTags = ['A', 'BUTTON', 'INPUT', 'SELECT', 'TEXTAREA'];
    const clickableClasses = ['btn', 'form-check-input', 'form-control', 'dropdown-toggle'];
    
    // تحقق من نوع العنصر
    if (clickableTags.includes(element.tagName)) {
        return true;
    }
    
    // تحقق من الفئات (classes)
    if (element.className) {
        const classes = element.className.split(' ');
        if (classes.some(cls => clickableClasses.includes(cls))) {
            return true;
        }
    }
    
    // تحقق من العناصر الأبوية
    let parent = element.parentElement;
    while (parent && parent.tagName !== 'TR') {
        if (clickableTags.includes(parent.tagName)) {
            return true;
        }
        if (parent.className) {
            const classes = parent.className.split(' ');
            if (classes.some(cls => clickableClasses.includes(cls))) {
                return true;
            }
        }
        parent = parent.parentElement;
    }
    
    return false;
}

/**
 * إضافة تأثيرات بصرية للنقر المزدوج
 */
function addDoubleClickEffect(element) {
    element.style.transition = 'all 0.1s ease';
    element.style.transform = 'scale(0.98)';
    
    setTimeout(() => {
        element.style.transform = '';
    }, 100);
}

// تصدير الوظائف للاستخدام في ملفات أخرى
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        setupTableDoubleClick,
        setupCardDoubleClick,
        getEntityIdFromRow,
        getViewRouteFromTable,
        buildViewUrl,
        isClickableElement
    };
}
