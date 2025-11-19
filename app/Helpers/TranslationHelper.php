<?php

namespace App\Helpers;

class TranslationHelper
{
    /**
     * Get translated text with fallback
     */
    public static function trans($key, $replace = [], $locale = null)
    {
        $locale = $locale ?: app()->getLocale();
        
        // Try to get translation from messages file
        $translation = trans("messages.{$key}", $replace, $locale);
        
        // If translation not found, return the key
        if ($translation === "messages.{$key}") {
            return $key;
        }
        
        return $translation;
    }
    
    /**
     * Get current locale
     */
    public static function getCurrentLocale()
    {
        return app()->getLocale();
    }
    
    /**
     * Check if current locale is RTL
     */
    public static function isRTL()
    {
        return in_array(app()->getLocale(), ['ar', 'he', 'fa', 'ur']);
    }
    
    /**
     * Get available locales
     */
    public static function getAvailableLocales()
    {
        return [
            'en' => 'English',
            'ar' => 'العربية',
        ];
    }
}
