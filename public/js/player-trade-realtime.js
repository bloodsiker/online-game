(function () {
    'use strict';

    class PlayerTradeRealtime {
        constructor({ uuid, sync, getEcho }) {
            this.name = 'player-trade.' + uuid;
            this.sync = sync;
            this.getEcho = getEcho || (() => {
                try { return window.top.Echo || null; }
                catch (error) { return null; }
            });
            this.running = false;
            this.inFlight = false;
            this.pending = false;
            this.syncTimer = null;
            this.retryTimer = null;
            this.pollTimer = null;
            this.pollInterval = null;
            this.echo = null;
            this.channel = null;
            this.connection = null;
            this.onChanged = () => this.scheduleSync();
            this.onSubscribed = () => {
                if (!this.running) return;
                clearTimeout(this.retryTimer);
                this.retryTimer = null;
                this.setPolling(60000);
                this.scheduleSync(0);
            };
            this.onSubscriptionError = () => {
                if (!this.running) return;
                this.setPolling(10000);
                this.retry(5000);
            };
            this.onConnectionState = ({ current }) => {
                if (!this.running) return;
                this.setPolling(10000);
                if (current === 'connected') this.scheduleSync(0);
            };
        }

        start() {
            if (this.running) return;
            this.running = true;
            this.setPolling(10000);
            this.scheduleSync(0);
            this.subscribe();
        }

        subscribe() {
            if (!this.running) return;
            const echo = this.getEcho();
            if (!echo) { this.retry(1000); return; }
            this.detach();
            this.echo = echo;
            this.connection = echo.connector?.pusher?.connection || null;
            this.connection?.bind('state_change', this.onConnectionState);
            try {
                this.channel = echo.private(this.name)
                    .listen('.player-trade.changed', this.onChanged)
                    .subscribed(this.onSubscribed)
                    .error(this.onSubscriptionError);
                if (this.channel.subscription?.subscribed) this.onSubscribed();
            } catch (error) {
                this.onSubscriptionError();
            }
        }

        retry(delay) {
            clearTimeout(this.retryTimer);
            this.retryTimer = setTimeout(() => {
                this.retryTimer = null;
                this.subscribe();
            }, delay);
        }

        setPolling(interval) {
            if (this.pollInterval === interval && this.pollTimer !== null) return;
            clearInterval(this.pollTimer);
            this.pollInterval = interval;
            this.pollTimer = setInterval(() => this.scheduleSync(0), interval);
        }

        scheduleSync(delay = 75) {
            if (!this.running) return;
            this.pending = true;
            if (this.syncTimer !== null || this.inFlight) return;
            this.syncTimer = setTimeout(() => this.flush(), delay);
        }

        async flush() {
            this.syncTimer = null;
            if (!this.running || this.inFlight) return;
            this.pending = false;
            this.inFlight = true;
            try { await this.sync(); }
            catch (error) { /* Keep the fallback available for transient failures. */ }
            finally {
                this.inFlight = false;
                if (this.pending && this.running) this.scheduleSync();
            }
        }

        detach() {
            this.connection?.unbind('state_change', this.onConnectionState);
            if (this.channel) {
                this.channel.stopListening('.player-trade.changed', this.onChanged);
                this.echo?.leave(this.name);
            }
            this.channel = null;
            this.connection = null;
            this.echo = null;
        }

        stop() {
            this.running = false;
            this.pending = false;
            clearTimeout(this.syncTimer);
            clearTimeout(this.retryTimer);
            clearInterval(this.pollTimer);
            this.syncTimer = null;
            this.retryTimer = null;
            this.pollTimer = null;
            this.pollInterval = null;
            this.detach();
        }
    }

    window.PlayerTradeRealtime = PlayerTradeRealtime;
})();
