# نموذج البحث المحسن - Enhanced Search Form

## نظرة عامة
نموذج بحث متقدم ومحسن يوفر تجربة مستخدم رائعة مع ميزات ذكية وواجهة مستخدم حديثة.

## الميزات الجديدة

### 🎨 التصميم المحسن
- **تأثيرات زجاجية (Glassmorphism)**: خلفية شفافة مع تأثير الضبابية
- **رسوم متحركة سلسة**: انتقالات وحركات متقدمة
- **تدرجات لونية ديناميكية**: خلفية متحركة مع ألوان متدرجة
- **تصميم متجاوب**: يعمل بشكل مثالي على جميع الأجهزة

### 🔍 البحث الذكي
- **اقتراحات تلقائية**: اقتراحات ذكية أثناء الكتابة
- **تاريخ البحث**: حفظ آخر عمليات البحث
- **فلترة متقدمة**: فلاتر سريعة للبحث
- **اختصارات لوحة المفاتيح**: تحكم سريع بالكيبورد

### ⌨️ اختصارات لوحة المفاتيح
- `Ctrl + K`: فتح البحث السريع
- `Esc`: إغلاق الاقتراحات
- `↑/↓`: التنقل في الاقتراحات
- `Enter`: تأكيد البحث

### 📱 التوافق
- **متجاوب بالكامل**: يعمل على الهواتف والأجهزة اللوحية
- **دعم الوضع المظلم**: تلقائي حسب إعدادات النظام
- **دعم الوضع عالي التباين**: للمستخدمين ذوي الاحتياجات الخاصة
- **تقليل الحركة**: للمستخدمين الذين يفضلون تقليل الرسوم المتحركة

## كيفية الاستخدام

### 1. تضمين الملفات
```html
<!-- CSS -->
<link rel="stylesheet" href="css/enhanced-search.css">

<!-- JavaScript -->
<script src="js/enhanced-search.js"></script>
```

### 2. HTML الأساسي
```html
<div class="enhanced-search-container">
    <form method="GET" action="/users" class="enhanced-search-form">
        <input type="text" 
               class="enhanced-search-input" 
               name="search" 
               placeholder="ابحث عن المستخدمين..." 
               autocomplete="off">
        <button class="enhanced-search-btn" type="submit">
            <i class="fas fa-search search-icon"></i>
        </button>
        <a href="/users" class="enhanced-search-clear">
            <i class="fas fa-times"></i>
        </a>
    </form>
    
    <!-- اقتراحات البحث -->
    <div class="search-suggestions">
        <!-- سيتم ملؤها تلقائياً -->
    </div>
</div>
```

### 3. الفلاتر (اختياري)
```html
<div class="search-filters">
    <div class="filter-chip active" data-filter="all">
        <i class="fas fa-globe"></i>
        <span>الكل</span>
    </div>
    <div class="filter-chip" data-filter="active">
        <i class="fas fa-user-check"></i>
        <span>نشط</span>
    </div>
</div>
```

### 4. تاريخ البحث (اختياري)
```html
<div class="recent-searches">
    <h6><i class="fas fa-history"></i>البحث الأخير</h6>
    <!-- سيتم ملؤها تلقائياً -->
</div>
```

## التخصيص

### الألوان
يمكن تخصيص الألوان من خلال متغيرات CSS:
```css
:root {
    --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    --glass-bg: rgba(255, 255, 255, 0.95);
    --shadow-soft: 0 8px 32px rgba(0, 0, 0, 0.1);
}
```

### الاقتراحات
يمكن تخصيص الاقتراحات من خلال تعديل دالة `loadSuggestions()` في JavaScript:
```javascript
loadSuggestions() {
    const searchTerm = this.searchInput.value.trim().toLowerCase();
    
    // استبدل هذا بمكالمة API حقيقية
    const suggestions = [
        { name: 'أحمد محمد', role: 'مدير تقنية المعلومات', icon: 'fas fa-user' },
        // المزيد من الاقتراحات...
    ];
    
    this.renderSuggestions(suggestions);
}
```

## الأحداث المخصصة

### searchPerformed
يتم تشغيله عند إجراء البحث:
```javascript
document.addEventListener('searchPerformed', (e) => {
    console.log('تم البحث عن:', e.detail.searchTerm);
});
```

### searchFilterChange
يتم تشغيله عند تغيير الفلتر:
```javascript
document.addEventListener('searchFilterChange', (e) => {
    console.log('تم تغيير الفلتر إلى:', e.detail.filter);
});
```

## API العامة

### setSearchValue(value)
تعيين قيمة البحث:
```javascript
window.enhancedSearch.setSearchValue('أحمد');
```

### getSearchValue()
الحصول على قيمة البحث الحالية:
```javascript
const currentSearch = window.enhancedSearch.getSearchValue();
```

### clearHistory()
مسح تاريخ البحث:
```javascript
window.enhancedSearch.clearHistory();
```

## المتطلبات
- Font Awesome 6.4.0+ للأيقونات
- Bootstrap 5.3.0+ (اختياري)
- متصفح حديث يدعم CSS Grid و Flexbox

## المتصفحات المدعومة
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## الترخيص
هذا المشروع مرخص تحت رخصة MIT.

## المساهمة
نرحب بالمساهمات! يرجى فتح issue أو pull request.

## الدعم
إذا واجهت أي مشاكل، يرجى فتح issue في المستودع.
