<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zoho API Credentials
    |--------------------------------------------------------------------------
    |
    | These credentials are used to authenticate with Zoho Desk API.
    | You can get these from your Zoho Developer Console.
    |
    */

    'client_id' => env('ZOHO_CLIENT_ID'),
    'client_secret' => env('ZOHO_CLIENT_SECRET'),
    'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
    'org_id' => env('ZOHO_ORG_ID'),

    /*
    |--------------------------------------------------------------------------
    | Zoho API URLs
    |--------------------------------------------------------------------------
    */

    'token_url' => 'https://accounts.zoho.com/oauth/v2/token',
    'base_url' => 'https://desk.zoho.com/api/v1',

    /*
    |--------------------------------------------------------------------------
    | Sync Configuration
    |--------------------------------------------------------------------------
    |
    | Configure how often data should be synced from Zoho
    |
    */

    'sync' => [
        'enabled' => env('ZOHO_SYNC_ENABLED', true),
        'interval_minutes' => 10,
        'tickets_per_batch' => 100,
        'max_days_back' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configure caching settings to avoid API rate limits
    |
    */

    'cache' => [
        'enabled' => env('ZOHO_CACHE_ENABLED', true),
        'expiry_minutes' => env('ZOHO_CACHE_EXPIRY_MINUTES', 10),
        'force_refresh_after_hours' => env('ZOHO_CACHE_FORCE_REFRESH_HOURS', 24),
    ],

    /*
    |--------------------------------------------------------------------------
    | Achievement Thresholds
    |--------------------------------------------------------------------------
    |
    | Define the thresholds for different achievement levels
    |
    */

    'achievements' => [
        'speed_demon' => [
            'bronze' => 30,  // متوسط وقت الرد بالدقائق
            'silver' => 15,
            'gold' => 5,
            'platinum' => 2,
        ],
        'ticket_master' => [
            'bronze' => 50,  // عدد التذاكر في الشهر
            'silver' => 100,
            'gold' => 200,
            'platinum' => 500,
        ],
        'consistency_king' => [
            'bronze' => 3,   // عدد الأسابيع متتالية بأداء عالي
            'silver' => 5,
            'gold' => 8,
            'platinum' => 12,
        ],
        'night_owl' => [
            'bronze' => 10,  // عدد التذاكر بعد منتصف الليل
            'silver' => 25,
            'gold' => 50,
            'platinum' => 100,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Performance Score Weights
    |--------------------------------------------------------------------------
    |
    | Weights for calculating performance score (should sum to 100)
    |
    */

    'performance_weights' => [
        'tickets_count' => 40,      // وزن عدد التذاكر
        'response_time' => 40,      // وزن سرعة الرد
        'tickets_per_hour' => 20,   // وزن TPH
    ],
];

