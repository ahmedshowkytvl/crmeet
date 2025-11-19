<?php

return [
    // General
    'password_management' => 'إدارة كلمات المرور',
    'password_accounts' => 'حسابات كلمات المرور',
    'password_account' => 'حساب كلمة المرور',
    'create_account' => 'إنشاء حساب جديد',
    'add_new_credentials' => 'إضافة بيانات اعتماد جديدة',
    'edit_account' => 'تعديل الحساب',
    'delete_account' => 'حذف الحساب',
    'view_password' => 'عرض كلمة المرور',
    'hide_password' => 'إخفاء كلمة المرور',
    'copy_password' => 'نسخ كلمة المرور',
    'password_copied' => 'تم نسخ كلمة المرور',
    
    // Account Fields
    'account_name' => 'اسم الحساب',
    'account_name_ar' => 'اسم الحساب (عربي)',
    'email_username' => 'البريد الإلكتروني / اسم المستخدم',
    'email_username_placeholder' => 'أدخل البريد الإلكتروني أو اسم المستخدم',
    'email_username_hint' => 'يمكنك إدخال البريد الإلكتروني أو اسم المستخدم أو أي معرف آخر',
    'password' => 'كلمة المرور',
    'confirm_password' => 'تأكيد كلمة المرور',
    'login_url' => 'رابط تسجيل الدخول',
    'notes' => 'ملاحظات',
    'notes_ar' => 'ملاحظات (عربي)',
    'category' => 'الفئة',
    'category_ar' => 'الفئة (عربي)',
    'icon' => 'الأيقونة',
    'requires_2fa' => 'يتطلب المصادقة الثنائية',
    'expires_at' => 'تاريخ انتهاء الصلاحية',
    'is_shared' => 'حساب مشترك',
    'is_active' => 'نشط',
    
    // Categories
    'social_media' => 'وسائل التواصل الاجتماعي',
    'work_tools' => 'أدوات العمل',
    'email_services' => 'خدمات البريد الإلكتروني',
    'cloud_storage' => 'التخزين السحابي',
    'development' => 'التطوير',
    'design' => 'التصميم',
    'marketing' => 'التسويق',
    'finance' => 'المالية',
    'other' => 'أخرى',
    
    // Status
    'active' => 'نشط',
    'inactive' => 'غير نشط',
    'expired' => 'منتهي الصلاحية',
    'expiring_soon' => 'ينتهي قريباً',
    'shared' => 'مشترك',
    'private' => 'خاص',
    
    // Access Levels
    'read_only' => 'قراءة فقط',
    'manage' => 'إدارة',
    'can_view_password' => 'يمكن عرض كلمة المرور',
    'can_edit_password' => 'يمكن تعديل كلمة المرور',
    'can_delete_account' => 'يمكن حذف الحساب',
    
    // Actions
    'assign' => 'تخصيص',
    'unassign' => 'إلغاء التخصيص',
    'assign_to_users' => 'تخصيص للمستخدمين',
    'assigned_users' => 'المستخدمون المخصصون',
    'available_users' => 'المستخدمون المتاحون',
    'search_users' => 'البحث عن المستخدمين',
    
    // Audit Logs
    'audit_logs' => 'سجل التدقيق',
    'action' => 'الإجراء',
    'performed_by' => 'تم بواسطة',
    'performed_at' => 'تم في',
    'ip_address' => 'عنوان IP',
    'user_agent' => 'وكيل المستخدم',
    'old_values' => 'القيم القديمة',
    'new_values' => 'القيم الجديدة',
    
    // Audit Actions
    'viewed' => 'تم العرض',
    'created' => 'تم الإنشاء',
    'updated' => 'تم التحديث',
    'deleted' => 'تم الحذف',
    'assigned' => 'تم التخصيص',
    'unassigned' => 'تم إلغاء التخصيص',
    'password_changed' => 'تم تغيير كلمة المرور',
    'expired' => 'انتهت الصلاحية',
    'expiring_soon' => 'تنتهي قريباً',
    
    // Messages
    'account_created_successfully' => 'تم إنشاء الحساب بنجاح',
    'account_updated_successfully' => 'تم تحديث الحساب بنجاح',
    'account_deleted_successfully' => 'تم حذف الحساب بنجاح',
    'password_updated_successfully' => 'تم تحديث كلمة المرور بنجاح',
    'assignment_created_successfully' => 'تم إنشاء التخصيص بنجاح',
    'assignment_revoked_successfully' => 'تم إلغاء التخصيص بنجاح',
    'no_accounts_found' => 'لم يتم العثور على حسابات',
    'no_assignments_found' => 'لم يتم العثور على تخصيصات',
    'no_audit_logs_found' => 'لم يتم العثور على سجلات تدقيق',
    
    // Validation Messages
    'name_required' => 'اسم الحساب مطلوب',
    'password_required' => 'كلمة المرور مطلوبة',
    'password_min_length' => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل',
    'email_invalid' => 'البريد الإلكتروني غير صحيح',
    'url_invalid' => 'الرابط غير صحيح',
    'expires_at_after_today' => 'تاريخ انتهاء الصلاحية يجب أن يكون بعد اليوم',
    
    // Filters
    'filter_by_category' => 'تصفية حسب الفئة',
    'filter_by_status' => 'تصفية حسب الحالة',
    'search_accounts' => 'البحث في الحسابات',
    'clear_filters' => 'مسح المرشحات',
    'all_categories' => 'جميع الفئات',
    'all_statuses' => 'جميع الحالات',
    
    // Statistics
    'total_accounts' => 'إجمالي الحسابات',
    'active_accounts' => 'الحسابات النشطة',
    'expired_accounts' => 'الحسابات المنتهية الصلاحية',
    'expiring_soon_accounts' => 'الحسابات التي تنتهي قريباً',
    'shared_accounts' => 'الحسابات المشتركة',
    'private_accounts' => 'الحسابات الخاصة',
    
    // Security
    'password_security_warning' => 'تحذير أمني: كلمات المرور مشفرة ومحمية',
    'view_password_warning' => 'تحذير: عرض كلمة المرور سيتم تسجيله في سجل التدقيق',
    'password_expiry_warning' => 'تحذير: كلمة المرور تنتهي صلاحيتها قريباً',
    'password_expired_warning' => 'تحذير: كلمة المرور منتهية الصلاحية',
    
    // Notifications
    'password_expiry_notification' => 'إشعار انتهاء صلاحية كلمة المرور',
    'password_changed_notification' => 'إشعار تغيير كلمة المرور',
    'account_assigned_notification' => 'إشعار تخصيص حساب',
    'account_unassigned_notification' => 'إشعار إلغاء تخصيص حساب',
    
    // Export
    'export_accounts' => 'تصدير الحسابات',
    'export_audit_logs' => 'تصدير سجلات التدقيق',
    'export_format' => 'تنسيق التصدير',
    'csv_format' => 'CSV',
    'excel_format' => 'Excel',
    'pdf_format' => 'PDF',
    
    // Batch Actions
    'batch_actions' => 'إجراءات مجمعة',
    'select_all' => 'تحديد الكل',
    'deselect_all' => 'إلغاء تحديد الكل',
    'batch_delete' => 'حذف مجمع',
    'batch_export' => 'تصدير مجمع',
    'batch_assign' => 'تخصيص مجمع',
    'no_accounts_selected' => 'لم يتم تحديد أي حسابات',
    'confirm_batch_delete' => 'هل أنت متأكد من حذف',
    'accounts' => 'حسابات',
    'batch_delete_success' => 'تم حذف :count حساب بنجاح',
    'batch_assign_success' => 'تم تخصيص :count حساب بنجاح',
    'no_accounts_or_users_selected' => 'لم يتم تحديد حسابات أو مستخدمين',
    'unsupported_export_format' => 'تنسيق التصدير غير مدعوم',
    'shared' => 'مشترك',
    'private' => 'خاص',
    'yes' => 'نعم',
    'no' => 'لا',
    'select_users_to_assign' => 'اختر المستخدمين للتخصيص',
    'access_level' => 'مستوى الوصول',
    'select_users' => 'اختر المستخدمين',
    'selected_accounts' => 'الحسابات المحددة',
    'batch_assign_info' => 'سيتم تخصيص :count حساب للمستخدمين المحددين',
    'assign_accounts' => 'تخصيص الحسابات',
    'please_select_at_least_one_user' => 'يرجى اختيار مستخدم واحد على الأقل',
    
    // AI Notes Generation
    'generate_ai_notes' => 'توليد ملاحظات ذكية',
    'ai_notes_generated_successfully' => 'تم توليد الملاحظات الذكية بنجاح!',
    'failed_to_generate_notes' => 'فشل في توليد الملاحظات',
    'an_error_occurred_while_generating_notes' => 'حدث خطأ أثناء توليد الملاحظات',
    'please_enter_account_name_first' => 'يرجى إدخال اسم الحساب أولاً',
    'generating' => 'جاري التوليد...',
    'ai_notes_description' => 'انقر على الزر لتوليد ملاحظات أمنية ذكية بناءً على تفاصيل حسابك.',
    'please_log_in_to_use_this_feature' => 'يرجى تسجيل الدخول لاستخدام هذه الميزة',
];
