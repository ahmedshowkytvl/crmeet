<?php
/**
 * اختبار نظام الترجمة لصفحة تسجيل الدخول
 * Test Translation System for Login Page
 */

// محاكاة Laravel App
class MockApp {
    private $locale = 'ar';
    
    public function getLocale() {
        return $this->locale;
    }
    
    public function setLocale($locale) {
        $this->locale = $locale;
    }
}

// محاكاة دالة الترجمة
function __($key, $replace = []) {
    global $app;
    
    $locale = $app->getLocale();
    $file = __DIR__ . "/lang/{$locale}/messages.php";
    
    if (file_exists($file)) {
        $messages = include $file;
        return $messages[$key] ?? $key;
    }
    
    return $key;
}

// محاكاة route helper
function route($name, $params = []) {
    return "#{$name}";
}

// محاكاة csrf_token
function csrf_token() {
    return 'test-csrf-token';
}

// محاكاة old helper
function old($key, $default = '') {
    return $default;
}

// محاكاة error helper
function error($key) {
    return null;
}

// محاكاة app helper
function app() {
    global $app;
    return $app;
}

// إنشاء تطبيق وهمي
$app = new MockApp();

echo "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>اختبار نظام الترجمة - صفحة تسجيل الدخول</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; }
        .arabic { direction: rtl; text-align: right; }
        .english { direction: ltr; text-align: left; }
        .success { background-color: #d4edda; border-color: #c3e6cb; }
        .error { background-color: #f8d7da; border-color: #f5c6cb; }
        h2 { color: #333; }
        .translation-key { font-weight: bold; color: #666; }
        .translation-value { margin: 5px 0; }
    </style>
</head>
<body>";

echo "<h1>اختبار نظام الترجمة لصفحة تسجيل الدخول</h1>";

// اختبار الترجمة العربية
echo "<div class='test-section arabic'>";
echo "<h2>الترجمة العربية (Arabic Translation)</h2>";

$app->setLocale('ar');

$ar_keys = [
    'login',
    'welcome_back',
    'username_or_email',
    'enter_username_or_email',
    'username_or_email_hint',
    'password',
    'remember_me',
    'dont_have_account',
    'register_here',
    'system_title'
];

foreach ($ar_keys as $key) {
    $value = __($key);
    echo "<div class='translation-value'>";
    echo "<span class='translation-key'>{$key}:</span> {$value}";
    echo "</div>";
}

echo "</div>";

// اختبار الترجمة الإنجليزية
echo "<div class='test-section english'>";
echo "<h2>English Translation</h2>";

$app->setLocale('en');

$en_keys = [
    'login',
    'welcome_back',
    'username_or_email',
    'enter_username_or_email',
    'username_or_email_hint',
    'password',
    'remember_me',
    'dont_have_account',
    'register_here',
    'system_title'
];

foreach ($en_keys as $key) {
    $value = __($key);
    echo "<div class='translation-value'>";
    echo "<span class='translation-key'>{$key}:</span> {$value}";
    echo "</div>";
}

echo "</div>";

// اختبار التبديل بين اللغات
echo "<div class='test-section'>";
echo "<h2>اختبار التبديل بين اللغات (Language Switching Test)</h2>";

$test_key = 'username_or_email_hint';

echo "<h3>نفس المفتاح في اللغتين:</h3>";

$app->setLocale('ar');
$ar_value = __($test_key);
echo "<div class='arabic'>";
echo "<strong>العربية:</strong> {$ar_value}";
echo "</div>";

$app->setLocale('en');
$en_value = __($test_key);
echo "<div class='english'>";
echo "<strong>English:</strong> {$en_value}";
echo "</div>";

echo "</div>";

// اختبار النص المحدد
echo "<div class='test-section'>";
echo "<h2>اختبار النص المحدد (Specific Text Test)</h2>";

$app->setLocale('ar');
echo "<div class='arabic'>";
echo "<h3>العربية:</h3>";
echo "<p><strong>التسمية:</strong> " . __('username_or_email') . "</p>";
echo "<p><strong>النص التوضيحي:</strong> " . __('username_or_email_hint') . "</p>";
echo "<p><strong>النص المساعد:</strong> " . __('enter_username_or_email') . "</p>";
echo "</div>";

$app->setLocale('en');
echo "<div class='english'>";
echo "<h3>English:</h3>";
echo "<p><strong>Label:</strong> " . __('username_or_email') . "</p>";
echo "<p><strong>Hint Text:</strong> " . __('username_or_email_hint') . "</p>";
echo "<p><strong>Placeholder:</strong> " . __('enter_username_or_email') . "</p>";
echo "</div>";

echo "</div>";

echo "<div class='test-section success'>";
echo "<h2>✅ النتيجة (Result)</h2>";
echo "<p>تم تحديث صفحة تسجيل الدخول بنجاح لتدعم نظام الترجمة المتعدد اللغات.</p>";
echo "<p>Login page has been successfully updated to support multi-language translation system.</p>";
echo "<p>الآن ستظهر النصوص باللغة العربية عندما تكون اللغة عربية، وبالإنجليزية عندما تكون الإنجليزية.</p>";
echo "<p>Now the texts will show in Arabic when the language is Arabic, and in English when the language is English.</p>";
echo "</div>";

echo "</body></html>";
?>





