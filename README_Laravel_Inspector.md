# Laravel Project Inspector

A comprehensive inspection tool for Laravel projects that automatically detects and fixes common issues including JavaScript errors, missing translations, and broken links.

## Features

- 🔍 **Complete URL Inspection** - Tests all provided URLs in both English and Arabic
- 🟡 **JavaScript Error Detection** - Finds console errors, warnings, and failed network requests
- 🔤 **Translation Key Analysis** - Detects missing translation keys and generates translations
- 🌐 **Language Consistency Check** - Ensures proper language switching
- 🔗 **Broken Link Detection** - Identifies 404, 403, and other HTTP errors
- 🚨 **Laravel Error Detection** - Finds database, authentication, and validation errors
- 📊 **Comprehensive Reporting** - Detailed reports with fix suggestions

## Installation

1. Install required packages:
```bash
pip install -r requirements_laravel.txt
```

## Usage

### Quick Start
```bash
python run_laravel_inspection.py
```

### Advanced Usage
```python
from laravel_project_inspector import LaravelProjectInspector

# Create inspector
inspector = LaravelProjectInspector()

# Inspect specific URLs
urls = ["http://127.0.0.1:8000/", "http://127.0.0.1:8000/dashboard"]
inspector.inspect_all_urls(urls)
```

## What It Inspects

### 1. Console Inspection
- `console.error()` messages
- `console.warn()` messages
- Failed network requests (404, 500, etc.)
- JavaScript syntax errors
- Uncaught exceptions

### 2. Translation Key Check
- Detects Laravel translation keys: `__('key')`, `@lang('key')`, `trans('key')`
- Identifies missing translations
- Generates automatic translations for common keys
- Suggests file locations for new translations

### 3. Language Consistency
- Verifies English pages show English text
- Ensures Arabic pages display Arabic text
- Detects language switching issues
- Reports mixed language content

### 4. Page Behavior & Broken Links
- Tests all internal links for accessibility
- Identifies 403, 404, and other HTTP errors
- Checks route availability
- Suggests middleware and permission fixes

### 5. Laravel-Specific Issues
- Database connection errors
- Authentication failures
- Validation errors
- CSRF token mismatches
- Session expiration issues

## Sample Output

```
╔══════════════════════════════════════════════════════════╗
║           🔍 Laravel Project Inspector                   ║
╚══════════════════════════════════════════════════════════╝

🔍 Inspecting: http://127.0.0.1:8000/dashboard (Language: EN)
================================================================================

✅ http://127.0.0.1:8000/dashboard
   Status: 200
   Title: Dashboard - StaffTobia CRM
   Size: 45,230 bytes
   🔤 Translation keys: 12
   🟡 JS Errors: 2
      • Failed to load resource: 404 (Not Found)
      • Uncaught TypeError: Cannot read property 'id' of undefined
   ⚠️  Issues: 1
      • Arabic text found in English page: ['تسجيل الدخول', 'لوحة التحكم']

📋 COMPREHENSIVE INSPECTION REPORT
================================================================================

📊 SUMMARY STATISTICS:
   • Total URLs inspected: 54
   • Successful requests: 48
   • URLs with issues: 12
   • Total translation keys found: 156

📋 URL STATUS BREAKDOWN:
   ✅ No issues - http://127.0.0.1:8000/
   ⚠️ Has issues - http://127.0.0.1:8000/dashboard
   ❌ Failed to load - http://127.0.0.1:8000/users/123

🔤 TRANSLATION RECOMMENDATIONS:
   Create/update these language files:
   • resources/lang/en/messages.php
   • resources/lang/ar/messages.php
   • resources/lang/en/validation.php
   • resources/lang/ar/validation.php

🟡 JAVASCRIPT ERROR SUMMARY:
   • Total JS errors: 8
   • Common issues:
      • Failed to load resource: 4 occurrences
      • TypeError: 2 occurrences
      • ReferenceError: 2 occurrences

🔗 BROKEN LINKS SUMMARY:
   • Total broken links: 5
   • Common status codes:
      • 404: 3 links
      • 403: 2 links
```

## Generated Files

The inspector will suggest creating/updating these files:

### Language Files
- `resources/lang/en/messages.php`
- `resources/lang/ar/messages.php`
- `resources/lang/en/validation.php`
- `resources/lang/ar/validation.php`

### Sample Translation Output
```php
// resources/lang/en/messages.php
<?php
return [
    'login_field_hint' => 'Please enter your login credentials',
    'validation_failed' => 'The given data was invalid',
    'auth_failed' => 'These credentials do not match our records',
];

// resources/lang/ar/messages.php
<?php
return [
    'login_field_hint' => 'يرجى إدخال بيانات تسجيل الدخول',
    'validation_failed' => 'البيانات المدخلة غير صحيحة',
    'auth_failed' => 'هذه البيانات لا تطابق سجلاتنا',
];
```

## Configuration

### Custom Cookies
```python
custom_cookies = {
    'laravel-session': 'your_session_token',
    'XSRF-TOKEN': 'your_csrf_token'
}

inspector = LaravelProjectInspector(cookies=custom_cookies)
```

### Custom Base URL
```python
inspector = LaravelProjectInspector(base_url="https://your-domain.com")
```

## Error Types Detected

### JavaScript Errors
- `TypeError: Cannot read property 'x' of undefined`
- `ReferenceError: 'x' is not defined`
- `SyntaxError: Unexpected token`
- `Failed to load resource: 404 (Not Found)`
- `Uncaught Error: Network request failed`

### Laravel Errors
- `SQLSTATE[42S02]: Base table or view doesn't exist`
- `Illuminate\Database\QueryException`
- `Illuminate\Auth\AuthenticationException`
- `CSRF token mismatch`
- `419 Page Expired`

### Translation Keys
- `messages.login_field_hint`
- `validation.failed`
- `auth.failed`
- `common.save`
- `common.cancel`

## Requirements

- Python 3.6+
- requests
- beautifulsoup4
- lxml (optional, for better HTML parsing)

## Use Cases

- **Pre-deployment Testing** - Ensure all pages work before going live
- **Multilingual Support** - Verify translation completeness
- **Error Monitoring** - Catch JavaScript and server errors
- **Link Validation** - Ensure all internal links work
- **Quality Assurance** - Comprehensive site health check


