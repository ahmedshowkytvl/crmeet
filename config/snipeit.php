<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Snipe-IT API Configuration
    |--------------------------------------------------------------------------
    |
    | إعدادات التكامل مع Snipe-IT Asset Management System
    |
    */

    'api_url' => env('SNIPEIT_API_URL', 'http://127.0.0.1:8010'),
    'api_token' => env('SNIPEIT_API_TOKEN', 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiMWQyZDBlYTY4ZTM3Y2UxYzhmOWRmZDk2NWM3ZWY5MjczYjIyZjNkOGEzNmJlYWNlNTI2ZGI1YzhlMTVlZTRhZDg2NGIwY2E4ZWYzYmMxODgiLCJpYXQiOjE3NjA4ODI1MzkuMDY3NTg2LCJuYmYiOjE3NjA4ODI1MzkuMDY3NTg4LCJleHAiOjIyMzQyNjgxMzkuMDUxNjc2LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.svwV_eD-2-U616XSRnuapaecC1DvYzU5WGiFT6RfZZBaju2ZuE9HHhy6T28M0hxaEmyP80_YNeRsFDg--x1PpWqckUuckgNXZSdWYFuSxsQFKRt_FMju7Hopi6gyflEGiX7AO9M_Z0OLnSiUqwSo9N_WJVWeZTDNEhtVJUoLlhDpiZG-MAPZVZbkCuW4PYFNhb5Iu_-i4QDrkBCZhrWr144M8FiU6ZxugWvnxXZQGxmLlJso7svyMRc0f39O6Ej2dTKGY6ZLWk_wMMulhyBJXAikMxjFw2uAds2nNG6K6uImL0UUc2Qnv0gNtvGOe5N-i5CzDr5Z6X-XBxPhOT6u1FfZtp88EE3BxKp0MOpaand3moAIRw78fUJusIFikrsCJHS7FOA6Pb-sD8oxrccaVFjl_4qNvTJAE3-UViUkuTJkJlDdDsEivFb7_C0aBI_xBcnkGkrgMWK_v0CAJNl97h1kchTCJg_jE2kwpLHNIkOtTxcfuMOYW43qxP7q2_YGMyJ4i0TJB_FU2jdgi4WgZO5zDIgc5QyOaEbotZwfYCQFRAN88fRwOGRrLIQeEpcr1wSyvkZk4DdCMQAFtaMyt3fSqjsLkNL7kB7xZvLVjUK_R5lO8A2fy6ZBjnndINxAW8bNnjVa58msAMBi8Z77iKFvlJ7y1JdpfUTHHocWpoo'),
    
    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات المزامنة التلقائية
    |
    */
    
    'auto_sync_enabled' => env('SNIPEIT_AUTO_SYNC_ENABLED', false),
    'sync_interval' => env('SNIPEIT_SYNC_INTERVAL', 60), // دقائق
    
    /*
    |--------------------------------------------------------------------------
    | Sync Options
    |--------------------------------------------------------------------------
    |
    | خيارات المزامنة
    |
    */
    
    'sync_assets' => env('SNIPEIT_SYNC_ASSETS', true),
    'sync_users' => env('SNIPEIT_SYNC_USERS', true),
    'sync_categories' => env('SNIPEIT_SYNC_CATEGORIES', true),
    'sync_locations' => env('SNIPEIT_SYNC_LOCATIONS', true),
    'sync_models' => env('SNIPEIT_SYNC_MODELS', true),
    'sync_suppliers' => env('SNIPEIT_SYNC_SUPPLIERS', true),
    
    /*
    |--------------------------------------------------------------------------
    | Webhook Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات Webhook
    |
    */
    
    'webhook_enabled' => env('SNIPEIT_WEBHOOK_ENABLED', false),
    'webhook_url' => env('SNIPEIT_WEBHOOK_URL'),
    
    /*
    |--------------------------------------------------------------------------
    | Request Settings
    |--------------------------------------------------------------------------
    |
    | إعدادات الطلبات
    |
    */
    
    'timeout' => env('SNIPEIT_TIMEOUT', 30),
    'retry_attempts' => env('SNIPEIT_RETRY_ATTEMPTS', 3),
    'retry_delay' => env('SNIPEIT_RETRY_DELAY', 1000), // milliseconds
];