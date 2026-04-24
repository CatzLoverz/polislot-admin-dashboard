import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Otomatis mendeteksi environment:
// - HTTPS (Cloudflare Tunnel): WSS port 443, domain sama
// - HTTP  (Lokal/Laragon):     WS port dari .env
const isSecure = window.location.protocol === 'https:';

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: isSecure ? 443 : (import.meta.env.VITE_REVERB_PORT ?? 80),
    wssPort: 443,
    forceTLS: isSecure,
    enabledTransports: ['ws', 'wss'],
});
