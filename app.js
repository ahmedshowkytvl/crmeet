/**
 * ========================================
 * Laravel App.js - ملف JavaScript الرئيسي
 * ========================================
 */

import './bootstrap';
import Alpine from 'alpinejs';
import './notifications'; // نظام الإشعارات

// تفعيل Alpine.js
window.Alpine = Alpine;
Alpine.start();

console.log('✓ Laravel app initialized');
console.log('✓ Alpine.js loaded');
console.log('✓ Notifications system ready');

