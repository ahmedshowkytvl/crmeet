-- فئات الأصول المستخرجة من تحليل البيانات
-- Asset Categories extracted from data analysis

-- 1. أجهزة الكمبيوتر
INSERT INTO asset_categories (name, name_ar, description, description_ar, icon, is_active, created_at, updated_at) VALUES
('Desktop PC', 'أجهزة كمبيوتر مكتبية', 'Desktop computers and workstations', 'أجهزة كمبيوتر مكتبية ومحطات عمل', 'fas fa-desktop', 1, NOW(), NOW()),
('Laptop', 'أجهزة لابتوب', 'Portable laptop computers', 'أجهزة لابتوب محمولة', 'fas fa-laptop', 1, NOW(), NOW()),
('Server', 'خوادم', 'Server computers and workstations', 'خوادم وأجهزة خادم', 'fas fa-server', 1, NOW(), NOW());

-- 2. أجهزة الشبكة
INSERT INTO asset_categories (name, name_ar, description, description_ar, icon, is_active, created_at, updated_at) VALUES
('Modem', 'أجهزة مودم', 'Internet modems and connectivity devices', 'أجهزة مودم الإنترنت وأجهزة الاتصال', 'fas fa-wifi', 1, NOW(), NOW()),
('Access Point', 'نقاط الوصول', 'Wireless access points', 'نقاط الوصول اللاسلكية', 'fas fa-broadcast-tower', 1, NOW(), NOW()),
('Camera', 'كاميرات المراقبة', 'Security cameras and surveillance equipment', 'كاميرات الأمان وأجهزة المراقبة', 'fas fa-video', 1, NOW(), NOW()),
('Camera-DVR', 'أجهزة DVR', 'Digital Video Recorders for surveillance', 'مسجلات الفيديو الرقمية للمراقبة', 'fas fa-hdd', 1, NOW(), NOW());

-- 3. أجهزة الاتصالات
INSERT INTO asset_categories (name, name_ar, description, description_ar, icon, is_active, created_at, updated_at) VALUES
('Avaya Phone', 'هواتف أفايا', 'Avaya telephone systems', 'أنظمة الهواتف أفايا', 'fas fa-phone', 1, NOW(), NOW()),
('Land Line', 'خطوط أرضية', 'Landline telephone connections', 'اتصالات الهاتف الأرضي', 'fas fa-phone-alt', 1, NOW(), NOW());

-- 4. أجهزة الطاقة
INSERT INTO asset_categories (name, name_ar, description, description_ar, icon, is_active, created_at, updated_at) VALUES
('UPS', 'مصادر طاقة احتياطية', 'Uninterruptible Power Supply units', 'وحدات إمداد الطاقة غير المنقطعة', 'fas fa-battery-full', 1, NOW(), NOW());

-- 5. الأثاث والمعدات
INSERT INTO asset_categories (name, name_ar, description, description_ar, icon, is_active, created_at, updated_at) VALUES
('Furniture', 'أثاث', 'Office furniture and fixtures', 'أثاث مكتبي وتركيبات', 'fas fa-chair', 1, NOW(), NOW()),
('Office Equipment', 'معدات مكتبية', 'General office equipment and supplies', 'معدات مكتبية عامة ومستلزمات', 'fas fa-print', 1, NOW(), NOW());

-- إضافة خصائص مخصصة لكل فئة
-- Desktop PC Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(1, 'CPU', 'المعالج', 'text', 1, 1, NOW(), NOW()),
(1, 'RAM', 'الذاكرة العشوائية', 'text', 1, 2, NOW(), NOW()),
(1, 'HDD', 'القرص الصلب', 'text', 1, 3, NOW(), NOW()),
(1, 'Mother Board', 'اللوحة الأم', 'text', 0, 4, NOW(), NOW()),
(1, 'Operating System', 'نظام التشغيل', 'text', 1, 5, NOW(), NOW()),
(1, 'IP Address', 'عنوان IP', 'text', 0, 6, NOW(), NOW()),
(1, 'Domain Status', 'حالة النطاق', 'select', 0, 7, NOW(), NOW()),
(1, 'Antivirus', 'برنامج الحماية', 'text', 0, 8, NOW(), NOW()),
(1, 'Office Version', 'إصدار الأوفيس', 'text', 0, 9, NOW(), NOW()),
(1, 'Monitor Brand', 'ماركة الشاشة', 'text', 0, 10, NOW(), NOW()),
(1, 'Port Number', 'رقم المنفذ', 'text', 0, 11, NOW(), NOW()),
(1, 'UPS Number', 'رقم الـ UPS', 'text', 0, 12, NOW(), NOW());

-- Laptop Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(2, 'CPU', 'المعالج', 'text', 1, 1, NOW(), NOW()),
(2, 'RAM', 'الذاكرة العشوائية', 'text', 1, 2, NOW(), NOW()),
(2, 'HDD', 'القرص الصلب', 'text', 1, 3, NOW(), NOW()),
(2, 'Operating System', 'نظام التشغيل', 'text', 1, 4, NOW(), NOW()),
(2, 'Battery Life', 'مدة البطارية', 'text', 0, 5, NOW(), NOW()),
(2, 'Screen Size', 'حجم الشاشة', 'text', 0, 6, NOW(), NOW()),
(2, 'Weight', 'الوزن', 'text', 0, 7, NOW(), NOW()),
(2, 'Brand', 'الماركة', 'text', 1, 8, NOW(), NOW()),
(2, 'Model', 'الموديل', 'text', 1, 9, NOW(), NOW());

-- Server Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(3, 'CPU', 'المعالج', 'text', 1, 1, NOW(), NOW()),
(3, 'RAM', 'الذاكرة العشوائية', 'text', 1, 2, NOW(), NOW()),
(3, 'HDD', 'القرص الصلب', 'text', 1, 3, NOW(), NOW()),
(3, 'Operating System', 'نظام التشغيل', 'text', 1, 4, NOW(), NOW()),
(3, 'IP Address', 'عنوان IP', 'text', 1, 5, NOW(), NOW()),
(3, 'Service Tag', 'رقم الخدمة', 'text', 0, 6, NOW(), NOW()),
(3, 'End of Support', 'انتهاء الدعم', 'date', 0, 7, NOW(), NOW()),
(3, 'RAID Configuration', 'إعدادات RAID', 'text', 0, 8, NOW(), NOW()),
(3, 'Power Rating', 'القدرة الكهربائية', 'text', 0, 9, NOW(), NOW());

-- Modem Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(4, 'Model', 'الموديل', 'text', 1, 1, NOW(), NOW()),
(4, 'Color', 'اللون', 'text', 0, 2, NOW(), NOW()),
(4, 'Phone Number', 'رقم الهاتف', 'text', 0, 3, NOW(), NOW()),
(4, 'Circle Number', 'رقم الدائرة', 'text', 0, 4, NOW(), NOW()),
(4, 'Contract Date', 'تاريخ العقد', 'date', 0, 5, NOW(), NOW()),
(4, 'Renewal Date', 'تاريخ التجديد', 'date', 0, 6, NOW(), NOW()),
(4, 'Contractor Name', 'اسم المقاول', 'text', 0, 7, NOW(), NOW());

-- Access Point Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(5, 'Model', 'الموديل', 'text', 1, 1, NOW(), NOW()),
(5, 'IP Address', 'عنوان IP', 'text', 1, 2, NOW(), NOW()),
(5, 'MAC Address', 'عنوان MAC', 'text', 1, 3, NOW(), NOW()),
(5, 'Speed', 'السرعة', 'text', 0, 4, NOW(), NOW()),
(5, 'Location', 'الموقع', 'text', 1, 5, NOW(), NOW()),
(5, 'Installation Date', 'تاريخ التثبيت', 'date', 0, 6, NOW(), NOW()),
(5, 'End of Support', 'انتهاء الدعم', 'date', 0, 7, NOW(), NOW());

-- Camera Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(6, 'Model', 'الموديل', 'text', 1, 1, NOW(), NOW()),
(6, 'IP Address', 'عنوان IP', 'text', 1, 2, NOW(), NOW()),
(6, 'Resolution', 'الدقة', 'text', 0, 3, NOW(), NOW()),
(6, 'Location', 'الموقع', 'text', 1, 4, NOW(), NOW()),
(6, 'NVR Connection', 'اتصال NVR', 'text', 0, 5, NOW(), NOW()),
(6, 'Installation Date', 'تاريخ التثبيت', 'date', 0, 6, NOW(), NOW()),
(6, 'Power Source', 'مصدر الطاقة', 'text', 0, 7, NOW(), NOW());

-- Camera-DVR Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(7, 'Model', 'الموديل', 'text', 1, 1, NOW(), NOW()),
(7, 'Channels', 'عدد القنوات', 'number', 0, 2, NOW(), NOW()),
(7, 'Storage Capacity', 'سعة التخزين', 'text', 0, 3, NOW(), NOW()),
(7, 'Location', 'الموقع', 'text', 1, 4, NOW(), NOW()),
(7, 'Installation Date', 'تاريخ التثبيت', 'date', 0, 5, NOW(), NOW());

-- Avaya Phone Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(8, 'Model', 'الموديل', 'text', 1, 1, NOW(), NOW()),
(8, 'Extension', 'الرقم الداخلي', 'text', 1, 2, NOW(), NOW()),
(8, 'MAC Address', 'عنوان MAC', 'text', 0, 3, NOW(), NOW()),
(8, 'User Name', 'اسم المستخدم', 'text', 0, 4, NOW(), NOW()),
(8, 'Password', 'كلمة المرور', 'text', 0, 5, NOW(), NOW()),
(8, 'Number of Ports', 'عدد المنافذ', 'number', 0, 6, NOW(), NOW()),
(8, 'Number of Lines', 'عدد الخطوط', 'number', 0, 7, NOW(), NOW()),
(8, 'Contract Date', 'تاريخ العقد', 'date', 0, 8, NOW(), NOW()),
(8, 'Renewal Date', 'تاريخ التجديد', 'date', 0, 9, NOW(), NOW());

-- Land Line Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(9, 'Phone Number', 'رقم الهاتف', 'text', 1, 1, NOW(), NOW()),
(9, 'Location', 'الموقع', 'text', 1, 2, NOW(), NOW()),
(9, 'Contract Period', 'فترة العقد', 'text', 0, 3, NOW(), NOW()),
(9, 'Monthly Cost', 'التكلفة الشهرية', 'number', 0, 4, NOW(), NOW()),
(9, 'Service Provider', 'مقدم الخدمة', 'text', 0, 5, NOW(), NOW()),
(9, 'Installation Date', 'تاريخ التثبيت', 'date', 0, 6, NOW(), NOW());

-- UPS Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(10, 'Model', 'الموديل', 'text', 1, 1, NOW(), NOW()),
(10, 'Power Rating', 'القدرة الكهربائية', 'text', 1, 2, NOW(), NOW()),
(10, 'Voltage', 'الجهد', 'text', 0, 3, NOW(), NOW()),
(10, 'Battery Type', 'نوع البطارية', 'text', 0, 4, NOW(), NOW()),
(10, 'Runtime', 'مدة التشغيل', 'text', 0, 5, NOW(), NOW()),
(10, 'Manufacture Date', 'تاريخ التصنيع', 'date', 0, 6, NOW(), NOW()),
(10, 'Next Battery Replacement', 'تاريخ استبدال البطارية التالي', 'date', 0, 7, NOW(), NOW()),
(10, 'Health Status', 'حالة الصحة', 'text', 0, 8, NOW(), NOW()),
(10, 'End of Sale', 'انتهاء البيع', 'date', 0, 9, NOW(), NOW()),
(10, 'End of Support', 'انتهاء الدعم', 'date', 0, 10, NOW(), NOW());

-- Furniture Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(11, 'Material', 'المادة', 'text', 0, 1, NOW(), NOW()),
(11, 'Color', 'اللون', 'text', 0, 2, NOW(), NOW()),
(11, 'Size', 'الحجم', 'text', 0, 3, NOW(), NOW()),
(11, 'Condition', 'الحالة', 'select', 1, 4, NOW(), NOW()),
(11, 'Purchase Date', 'تاريخ الشراء', 'date', 0, 5, NOW(), NOW()),
(11, 'Warranty Period', 'فترة الضمان', 'text', 0, 6, NOW(), NOW());

-- Office Equipment Properties
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, created_at, updated_at) VALUES
(12, 'Type', 'النوع', 'text', 1, 1, NOW(), NOW()),
(12, 'Brand', 'الماركة', 'text', 1, 2, NOW(), NOW()),
(12, 'Model', 'الموديل', 'text', 1, 3, NOW(), NOW()),
(12, 'Serial Number', 'الرقم التسلسلي', 'text', 0, 4, NOW(), NOW()),
(12, 'Purchase Date', 'تاريخ الشراء', 'date', 0, 5, NOW(), NOW()),
(12, 'Warranty Expiry', 'انتهاء الضمان', 'date', 0, 6, NOW(), NOW());

-- إضافة خيارات للخانات المنسدلة
-- Domain Status options
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, options, created_at, updated_at) VALUES
(1, 'Domain Status', 'حالة النطاق', 'select', 0, 7, '["Join","Not Join"]', NOW(), NOW());

-- Condition options for Furniture
INSERT INTO asset_category_properties (category_id, name, name_ar, type, is_required, sort_order, options, created_at, updated_at) VALUES
(11, 'Condition', 'الحالة', 'select', 1, 4, '["Excellent","Good","Fair","Poor","Damaged"]', NOW(), NOW());
