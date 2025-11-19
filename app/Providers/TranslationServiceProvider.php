<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Helpers\TranslationHelper;

class TranslationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share translation helper with all views
        View::share('trans', function($key, $replace = [], $locale = null) {
            return TranslationHelper::trans($key, $replace, $locale);
        });
        
        View::share('currentLocale', function() {
            return TranslationHelper::getCurrentLocale();
        });
        
        View::share('isRTL', function() {
            return TranslationHelper::isRTL();
        });
        
        View::share('availableLocales', function() {
            return TranslationHelper::getAvailableLocales();
        });
    }
}