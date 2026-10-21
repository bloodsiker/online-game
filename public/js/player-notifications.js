(function (root) {
    'use strict';

    // Acknowledgement is sent only after the chat has inserted the message.
    root.createPlayerNotificationDelivery = function (options) {
        var busy = false;
        var stopped = false;
        var requested = false;
        var fetcher = options.fetch || root.fetch.bind(root);

        async function sync() {
            if (stopped) return;
            if (busy) { requested = true; return; }
            busy = true;
            try {
                var response = await fetcher(options.pendingUrl, {
                    credentials: 'same-origin', headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('Notification delivery failed');
                var notifications = await response.json();
                if (stopped || !Array.isArray(notifications) || notifications.length === 0) return;
                options.show(notifications.map(function (entry) { return entry.message; }));
                var receipts = notifications.filter(function (entry) {
                    return options.isDisplayed(entry.message.id);
                }).map(function (entry) { return { id: entry.id, message_id: entry.message.id }; });
                if (receipts.length === 0) return;
                var acknowledged = await fetcher(options.acknowledgeUrl, {
                    method: 'POST', credentials: 'same-origin',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': options.csrfToken },
                    body: JSON.stringify({ receipts: receipts })
                });
                if (!acknowledged.ok) throw new Error('Notification acknowledgement failed');
                if (notifications.length === 50) requested = true;
            } catch (error) {
                // Keep unread on the server; reconnect or the safety poll retries delivery.
            } finally {
                busy = false;
                if (requested && !stopped) {
                    requested = false;
                    void sync();
                }
            }
        }

        return { sync: sync, stop: function () { stopped = true; } };
    };
}(typeof window === 'undefined' ? globalThis : window));
