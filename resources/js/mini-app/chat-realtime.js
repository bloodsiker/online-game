import Pusher from 'pusher-js';

export function connectChat({ key, authUrl, initData, subscriptions, onChange, onStatus }) {
    let stopped = false;
    const authorized = new Set();
    const requests = new Set();
    const secure = location.protocol === 'https:';
    const port = Number(location.port || (secure ? 443 : 80));
    const client = new Pusher(key, {
        cluster: 'mt1', wsHost: location.hostname, wsPort: port, wssPort: port,
        forceTLS: secure, enabledTransports: [secure ? 'wss' : 'ws'], enableStats: false,
        channelAuthorization: {
            customHandler: async (params, callback) => {
                const controller = new AbortController();
                requests.add(controller);
                const timeout = setTimeout(() => controller.abort(), 10000);
                try {
                    const response = await fetch(authUrl, {
                        method: 'POST', credentials: 'omit', cache: 'no-store', signal: controller.signal,
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Telegram-Init-Data': initData },
                        body: JSON.stringify({ socket_id: params.socketId, channel_name: params.channelName }),
                    });
                    if (!response.ok) throw new Error('Chat subscription denied');
                    const data = await response.json();
                    if (!stopped) callback(null, data);
                } catch (error) {
                    if (!stopped) { callback(error, null); onStatus(false); onChange(true); }
                } finally {
                    clearTimeout(timeout);
                    requests.delete(controller);
                }
            },
        },
    });
    client.connection.bind('state_change', ({ current }) => {
        if (stopped) return;
        if (current !== 'connected') { authorized.clear(); onStatus(false); }
        else onChange(true);
    });
    for (const name of subscriptions) {
        const channel = client.subscribe('private-' + name);
        channel.bind('pusher:subscription_succeeded', () => {
            authorized.add(name);
            if (!stopped) onStatus(authorized.size === subscriptions.length);
        });
        channel.bind('pusher:subscription_error', () => { if (!stopped) onStatus(false); });
        channel.bind('chat.message.created', () => { if (!stopped) onChange(false); });
        channel.bind('chat.message.expired', () => { if (!stopped) onChange(false); });
        channel.bind('chat.messages.invalidated', () => { if (!stopped) onChange(true); });
    }
    return () => {
        stopped = true;
        for (const controller of requests) controller.abort();
        client.disconnect();
    };
}
