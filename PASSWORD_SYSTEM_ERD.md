# 🔐 Password Management System - ERD

## Entity Relationship Diagram

```
┌─────────────────┐    ┌──────────────────────┐    ┌─────────────────┐
│     USERS       │    │  PASSWORD_ACCOUNTS   │    │ PASSWORD_HISTORY│
├─────────────────┤    ├──────────────────────┤    ├─────────────────┤
│ id (PK)         │    │ id (PK)              │    │ id (PK)         │
│ name            │    │ name                 │    │ account_id (FK) │
│ name_ar         │    │ name_ar              │    │ old_password    │
│ email           │    │ email                │    │ new_password    │
│ role_id (FK)    │    │ password (encrypted) │    │ changed_by (FK) │
│ department_id   │    │ url                  │    │ change_reason   │
│ ...             │    │ notes                │    │ changed_at      │
└─────────────────┘    │ notes_ar             │    └─────────────────┘
         │              │ requires_2fa         │             │
         │              │ expires_at           │             │
         │              │ is_shared            │             │
         │              │ is_active            │             │
         │              │ category             │             │
         │              │ category_ar          │             │
         │              │ icon                 │             │
         │              │ metadata             │             │
         │              │ created_by (FK)      │             │
         │              │ created_at           │             │
         │              │ updated_at           │             │
         │              └──────────────────────┘             │
         │                           │                       │
         │                           │                       │
         │              ┌──────────────────────┐             │
         │              │ PASSWORD_ASSIGNMENTS │             │
         │              ├──────────────────────┤             │
         │              │ id (PK)              │             │
         │              │ account_id (FK)      │─────────────┘
         │              │ user_id (FK)         │
         │              │ access_level         │
         │              │ can_view_password    │
         │              │ can_edit_password    │
         │              │ can_delete_account   │
         │              │ assigned_at          │
         │              │ assigned_by (FK)     │
         │              │ revoked_at           │
         │              │ revoked_by (FK)      │
         │              │ revoke_reason        │
         │              │ created_at           │
         │              │ updated_at           │
         │              └──────────────────────┘
         │                           │
         │                           │
         │              ┌──────────────────────┐
         │              │ PASSWORD_AUDIT_LOGS  │
         │              ├──────────────────────┤
         │              │ id (PK)              │
         │              │ account_id (FK)      │
         │              │ user_id (FK)         │
         │              │ action               │
         │              │ description          │
         │              │ description_ar       │
         │              │ old_values           │
         │              │ new_values           │
         │              │ ip_address           │
         │              │ user_agent           │
         │              │ performed_at         │
         │              │ created_at           │
         │              │ updated_at           │
         │              └──────────────────────┘
         │
         │
         └─────────────────────────────────────┘
```

## العلاقات

### 1. Users → Password Accounts
- **One-to-Many**: مستخدم واحد يمكنه إنشاء عدة حسابات
- **Foreign Key**: `password_accounts.created_by` → `users.id`

### 2. Users → Password Assignments
- **One-to-Many**: مستخدم واحد يمكنه الحصول على عدة تخصيصات
- **Foreign Key**: `password_assignments.user_id` → `users.id`

### 3. Password Accounts → Password Assignments
- **One-to-Many**: حساب واحد يمكن تخصيصه لعدة مستخدمين
- **Foreign Key**: `password_assignments.account_id` → `password_accounts.id`

### 4. Password Accounts → Password History
- **One-to-Many**: حساب واحد يمكن أن يكون له عدة سجلات تاريخ
- **Foreign Key**: `password_history.account_id` → `password_accounts.id`

### 5. Password Accounts → Password Audit Logs
- **One-to-Many**: حساب واحد يمكن أن يكون له عدة سجلات تدقيق
- **Foreign Key**: `password_audit_logs.account_id` → `password_accounts.id`

### 6. Users → Password Audit Logs
- **One-to-Many**: مستخدم واحد يمكن أن يقوم بعدة عمليات
- **Foreign Key**: `password_audit_logs.user_id` → `users.id`

### 7. Users → Password History
- **One-to-Many**: مستخدم واحد يمكنه تغيير عدة كلمات مرور
- **Foreign Key**: `password_history.changed_by` → `users.id`

## الفهارس (Indexes)

### password_accounts
- `PRIMARY KEY (id)`
- `INDEX (is_active, created_by)`
- `INDEX (category, is_active)`
- `INDEX (expires_at)`

### password_assignments
- `PRIMARY KEY (id)`
- `UNIQUE (account_id, user_id)`
- `INDEX (user_id, access_level)`
- `INDEX (account_id, assigned_at)`
- `INDEX (revoked_at)`

### password_audit_logs
- `PRIMARY KEY (id)`
- `INDEX (account_id, action)`
- `INDEX (user_id, performed_at)`
- `INDEX (performed_at)`
- `INDEX (action)`

### password_history
- `PRIMARY KEY (id)`
- `INDEX (account_id, changed_at)`
- `INDEX (changed_at)`

## القيود (Constraints)

### Foreign Key Constraints
- `password_accounts.created_by` → `users.id` (CASCADE DELETE)
- `password_assignments.account_id` → `password_accounts.id` (CASCADE DELETE)
- `password_assignments.user_id` → `users.id` (CASCADE DELETE)
- `password_assignments.assigned_by` → `users.id` (CASCADE DELETE)
- `password_assignments.revoked_by` → `users.id` (SET NULL)
- `password_audit_logs.account_id` → `password_accounts.id` (CASCADE DELETE)
- `password_audit_logs.user_id` → `users.id` (CASCADE DELETE)
- `password_history.account_id` → `password_accounts.id` (CASCADE DELETE)
- `password_history.changed_by` → `users.id` (CASCADE DELETE)

### Check Constraints
- `password_assignments.access_level` IN ('read_only', 'manage')
- `password_audit_logs.action` IN ('viewed', 'created', 'updated', 'deleted', 'assigned', 'unassigned', 'password_changed', 'expired', 'expiring_soon')
- `password_accounts.requires_2fa` BOOLEAN
- `password_accounts.is_shared` BOOLEAN
- `password_accounts.is_active` BOOLEAN
- `password_assignments.can_view_password` BOOLEAN
- `password_assignments.can_edit_password` BOOLEAN
- `password_assignments.can_delete_account` BOOLEAN

## ملاحظات التصميم

### الأمان
- جميع كلمات المرور مشفرة في قاعدة البيانات
- لا يمكن استرجاع كلمات المرور إلا عند العرض
- يتم تسجيل جميع عمليات الوصول

### الأداء
- فهارس محسنة للاستعلامات المتكررة
- فصل البيانات الحساسة عن البيانات العامة
- استخدام JSON للبيانات الإضافية

### القابلية للتوسع
- تصميم مرن يدعم إضافة حقول جديدة
- دعم الترجمة المدمج
- نظام صلاحيات قابل للتخصيص
