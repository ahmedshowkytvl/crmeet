/**
 * ========================================
 * Laravel Echo & Pusher Bootstrap
 * ========================================
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Laravel Echo للإشعارات الفورية
 */
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ? import.meta.env.VITE_PUSHER_HOST : `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? 80,
    wssPort: import.meta.env.VITE_PUSHER_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    
    // Authentication
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
    }
});

// للتطوير - عرض أحداث Echo في Console
if (import.meta.env.DEV) {
    window.Echo.connector.pusher.connection.bind('state_change', function(states) {
        console.log('Pusher state:', states.current);
    });
    
    window.Echo.connector.pusher.connection.bind('connected', function() {
        console.log('✓ Connected to Pusher');
    });
    
    window.Echo.connector.pusher.connection.bind('error', function(err) {
        console.error('✗ Pusher error:', err);
    });
}

