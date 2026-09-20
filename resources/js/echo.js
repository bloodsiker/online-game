import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const secure = window.location.protocol === 'https:';
const currentPort = Number(window.location.port || (secure ? 443 : 80));

function parentEcho() {
    if (window.parent === window) return null;

    try {
        if (window.parent.location.origin !== window.location.origin) return null;

        return window.parent.Echo || null;
    } catch {
        return null;
    }
}

window.Echo = parentEcho() || new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: window.location.hostname,
    wsPort: currentPort,
    wssPort: currentPort,
    forceTLS: secure,
    enabledTransports: [secure ? 'wss' : 'ws'],
});
