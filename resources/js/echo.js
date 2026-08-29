import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const secure = window.location.protocol === 'https:';
const currentPort = Number(window.location.port || (secure ? 443 : 80));

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: currentPort,
    wssPort: currentPort,
    forceTLS: secure,
    enabledTransports: ['ws', 'wss'],
});
