import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: window.AppConfig?.reverbAppKey || 'anyrandomkeypolislot',
    wsHost: window.AppConfig?.reverbHost || window.location.hostname,
    wsPort: window.AppConfig?.reverbPort || 84,
    wssPort: window.AppConfig?.reverbPort || 84,
    forceTLS: false,
    enabledTransports: ['ws', 'wss'],
});
