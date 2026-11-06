(() => {
    class TelegramMiniAppChat {
        constructor({ root, initData, labels, onAuthError }) {
            Object.assign(this, { root, initData, labels, onAuthError });
            const id = name => document.getElementById('telegram-chat-' + name);
            this.panel = document.getElementById('telegram-backpack-chat');
            for (const name of ['channel', 'status', 'messages', 'empty', 'error', 'mute', 'form', 'input', 'send']) this[name] = id(name);
            this.selected = 'main'; this.records = new Map(); this.generation = 0; this.live = false;
            this.channel.addEventListener('change', () => {
                this.selected = this.channel.value;
                this.restart();
            });
            this.form.addEventListener('submit', event => { event.preventDefault(); this.submit(); });
            this.input.addEventListener('input', () => this.controls());
            this.input.addEventListener('keydown', event => {
                if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) { event.preventDefault(); this.submit(); }
            });
            // A hidden WebView must not keep subscriptions or polling alive.
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) { this.resume = this.active; this.stop(); }
                else if (this.resume) { this.resume = false; this.start(); }
            });
            window.addEventListener('pagehide', () => this.stop());
            window.visualViewport?.addEventListener('resize', () => this.measure());
        }

        start() {
            if (this.active) { this.schedule(true); return; }
            this.active = true; this.terminal = false; this.sending = false;
            this.cursor = 0; this.records.clear(); this.render(true);
            this.error.hidden = true; this.mute.hidden = true; this.muted = false;
            this.input.placeholder = this.labels().chat_placeholder;
            this.input.setAttribute('aria-label', this.labels().chat_placeholder);
            this.setStatus(false); this.controls(); this.measure();
            this.sync(true);
        }

        restart() { this.stop(); this.start(); }

        stop() {
            this.active = false; this.generation++;
            clearTimeout(this.syncTimer); clearTimeout(this.pollTimer);
            this.syncTimer = this.pollTimer = null;
            this.readRequest?.abort(); this.sendRequest?.abort();
            this.stopRealtime?.(); this.stopRealtime = null; this.subscriptionsKey = '';
            this.inFlight = false; this.pending = false; this.pendingFull = false;
        }

        measure() {
            if (!this.active) return;
            const nav = document.getElementById('telegram-backpack-navigation');
            const viewport = window.visualViewport?.height || innerHeight;
            this.panel.style.height = Math.max(240, viewport - this.panel.getBoundingClientRect().top - nav.offsetHeight - 16) + 'px';
        }

        controls() {
            this.input.disabled = !!this.terminal || !!this.muted;
            this.send.disabled = !this.active || this.sending || this.input.disabled || !this.input.value.trim();
        }

        setStatus(live) {
            this.live = live;
            this.status.textContent = live ? this.labels().chat_live : this.labels().chat_polling;
            this.status.classList.toggle('is-live', live);
        }

        url(endpoint) {
            const url = new URL(endpoint, location.href);
            if (url.origin !== location.origin) throw new Error('Invalid chat origin');
            return url;
        }

        async request(endpoint, { method = 'GET', body, signal } = {}) {
            const response = await fetch(this.url(endpoint), {
                method, credentials: 'omit', cache: 'no-store', signal,
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-Telegram-Init-Data': this.initData },
                ...(body ? { body: JSON.stringify(body) } : {}),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.ok !== true) {
                const error = new Error(Object.values(data.errors || {}).flat()[0] || this.labels().chat_error);
                error.status = response.status; error.data = data;
                throw error;
            }
            return data;
        }

        schedule(full = false) {
            if (!this.active || this.terminal) return;
            this.pendingFull = this.pendingFull || full;
            if (this.inFlight) { this.pending = true; return; }
            if (this.syncTimer) return;
            this.syncTimer = setTimeout(() => { this.syncTimer = null; this.sync(this.pendingFull); }, 250);
        }

        async sync(full = false) {
            if (!this.active || this.terminal) return;
            if (this.inFlight) { this.pending = true; this.pendingFull ||= full; return; }
            full ||= this.pendingFull; this.pendingFull = false; this.inFlight = true;
            clearTimeout(this.pollTimer);
            const generation = this.generation;
            const controller = new AbortController(); this.readRequest = controller;
            const timeout = setTimeout(() => controller.abort(), 15000);
            try {
                const url = this.url(this.root.dataset.chatMessages);
                url.searchParams.set('channel', this.selected);
                if (!full && this.cursor) url.searchParams.set('after_id', String(this.cursor));
                for (const id of this.records.keys()) url.searchParams.append('ids[]', String(id));
                const data = await this.request(url, { signal: controller.signal });
                if (!this.active || generation !== this.generation) return;
                this.channel.replaceChildren(...data.channels.map(group => {
                    const option = document.createElement('option'); option.value = group.key; option.textContent = group.label; return option;
                }));
                this.channel.value = this.selected;
                const atBottom = full || this.messages.scrollHeight - this.messages.scrollTop - this.messages.clientHeight < 80;
                if (full) this.records.clear();
                else {
                    const valid = new Set(data.valid_ids);
                    for (const id of this.records.keys()) if (!valid.has(id)) this.records.delete(id);
                }
                for (const message of data.messages) this.records.set(message.id, message);
                this.cursor = data.cursor;
                while (this.records.size > 200) this.records.delete(this.records.keys().next().value);
                this.render(atBottom);
                this.muted = !!data.mute; this.mute.hidden = !data.mute;
                this.mute.textContent = data.mute?.message || '';
                this.controls();
                this.error.hidden = true;
                this.realtime(data, generation);
                if (data.more) { this.pending = true; }
            } catch (error) {
                if (this.active && generation === this.generation) {
                    if (error.status === 422 && error.data?.errors?.channel && this.selected !== 'main') {
                        this.selected = 'main'; this.restart(); return;
                    }
                    this.handleError(error);
                }
            } finally {
                clearTimeout(timeout);
                if (this.active && generation === this.generation) {
                    this.inFlight = false;
                    if (this.pending) { this.pending = false; this.schedule(); }
                    this.pollTimer = setTimeout(() => this.schedule(), this.live ? 30000 : 5000);
                }
            }
        }

        async realtime(data, generation) {
            if (!data.realtime?.enabled || !data.realtime.key) { this.setStatus(false); return; }
            const key = JSON.stringify([data.realtime.key, data.subscriptions]);
            if (key === this.subscriptionsKey) return;
            this.stopRealtime?.(); this.stopRealtime = null; this.subscriptionsKey = key;
            try {
                const module = await import(this.url(this.root.dataset.chatRealtime).href);
                if (!this.active || generation !== this.generation || this.subscriptionsKey !== key) return;
                this.stopRealtime = module.connectChat({
                    key: data.realtime.key, authUrl: this.url(this.root.dataset.chatAuth), initData: this.initData,
                    subscriptions: data.subscriptions,
                    onChange: full => this.schedule(full), onStatus: live => {
                        if (this.active && generation === this.generation) this.setStatus(live);
                    },
                });
            } catch (error) { if (this.active && generation === this.generation) this.setStatus(false); }
        }

        async submit() {
            const text = this.input.value.trim();
            if (!this.active || this.terminal || this.muted || this.sending || !text) return;
            this.sending = true; this.controls(); this.error.hidden = true;
            const generation = this.generation;
            const controller = new AbortController(); this.sendRequest = controller;
            const timeout = setTimeout(() => controller.abort(), 15000);
            try {
                await this.request(this.root.dataset.chatSend, { method: 'POST', body: { channel: this.selected, message: text }, signal: controller.signal });
                if (this.active && generation === this.generation) {
                    if (this.input.value.trim() === text) this.input.value = '';
                    // Fetch by the last read cursor, never by the sent ID: other messages may precede it.
                    this.schedule();
                }
            } catch (error) {
                if (this.active && generation === this.generation) this.handleError(error);
            } finally {
                clearTimeout(timeout);
                if (this.active && generation === this.generation) { this.sending = false; this.controls(); }
            }
        }

        handleError(error) {
            if ([401, 403].includes(error.status)) {
                this.stop(); this.terminal = true; this.records.clear(); this.render(false);
                this.input.value = ''; this.controls();
                this.onAuthError(error.status === 401 ? this.labels().mini_app_session_expired : this.labels().not_linked);
                return;
            }
            this.error.textContent = error.status === 429 ? this.labels().too_many_attempts : error.message;
            this.error.hidden = false;
        }

        address(name) {
            const current = this.input.value;
            const match = /^(to|prv)\[([^\]]+)\]\s*/i.exec(current);
            const type = match && match[2] === name && match[1].toLowerCase() === 'to' ? 'prv' : 'to';
            this.input.value = type + '[' + name + '] ' + current.replace(/^(to|prv)\[[^\]]+\]\s*/i, '');
            this.input.focus(); this.controls();
        }

        safeUrl(value, image = false) {
            try {
                const url = new URL(value, location.href);
                if (!['http:', 'https:'].includes(url.protocol) || url.origin !== location.origin) return null;
                if (image && !/^\/(data\/smiles|img\/icon|storage\/clan_icons)\//.test(url.pathname)) return null;
                if (!image && !/^\/(?:ru\/|uk\/)?(?:info\/|clan-info\/|map\/|library(?:\/|$)|forum(?:\/|$))/.test(url.pathname)) return null;
                return url.href;
            } catch { return null; }
        }

        // Game HTML contains iframe handlers. Rebuild a small display-only subset for this client.
        content(html) {
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const visit = node => {
                if (node.nodeType === Node.TEXT_NODE) return document.createTextNode(node.textContent);
                if (node.nodeType !== Node.ELEMENT_NODE || ['SCRIPT', 'STYLE', 'IFRAME', 'OBJECT'].includes(node.tagName)) return document.createTextNode('');
                const allowed = ['B', 'STRONG', 'I', 'EM', 'SPAN', 'BR', 'A', 'IMG'];
                const result = document.createElement(allowed.includes(node.tagName) ? node.tagName.toLowerCase() : 'span');
                if (node.tagName === 'IMG') {
                    const src = this.safeUrl(node.getAttribute('src'), true);
                    if (!src) return document.createTextNode(node.getAttribute('alt') || '');
                    result.src = src; result.alt = node.getAttribute('alt') || ''; result.loading = 'lazy';
                }
                if (node.tagName === 'A') {
                    const href = this.safeUrl(node.getAttribute('href'));
                    if (href) { result.href = href; result.target = '_blank'; result.rel = 'noopener noreferrer'; }
                    else if (node.dataset.name) {
                        result.href = '#'; result.addEventListener('click', event => { event.preventDefault(); this.address(node.dataset.name); });
                    }
                }
                if (node.style.color) result.style.color = node.style.color;
                for (const child of node.childNodes) result.append(visit(child));
                return result;
            };
            const fragment = document.createDocumentFragment();
            for (const child of parsed.body.childNodes) fragment.append(visit(child));
            return fragment;
        }

        render(scroll) {
            const previousTop = this.messages.scrollTop;
            this.messages.replaceChildren();
            for (const item of [...this.records.values()].sort((a, b) => a.id - b.id)) {
                const row = document.createElement('article'); row.className = 'telegram-chat__message';
                if (['private', 'clan_boss', 'system', 'mention', 'information', 'loot'].includes(item.type)) row.classList.add('is-' + item.type);
                row.dataset.id = item.id;
                const time = document.createElement('time'); time.textContent = item.time;
                row.append(time, document.createTextNode(' '));
                if (item.sender_id) {
                    const icon = this.safeUrl(item.sender_clan_icon, true);
                    if (icon) { const image = document.createElement('img'); image.src = icon; image.alt = ''; image.width = image.height = 13; row.append(image, document.createTextNode(' ')); }
                    const name = document.createElement('button'); name.type = 'button'; name.className = 'telegram-chat__nickname';
                    name.textContent = item.sender_name + (item.sender_level ? ' [' + item.sender_level + ']' : '');
                    name.addEventListener('click', () => this.address(item.sender_name));
                    row.append(name, document.createTextNode(' '));
                    if (item.type === 'private' && item.target_name) {
                        const target = document.createElement('button'); target.type = 'button'; target.className = 'telegram-chat__nickname'; target.textContent = '→ ' + item.target_name;
                        target.addEventListener('click', () => this.address(item.target_name)); row.append(target, document.createTextNode(' '));
                    }
                }
                const content = document.createElement('span'); content.append(this.content(item.content)); row.append(content);
                this.messages.append(row);
            }
            this.empty.textContent = this.labels().chat_empty;
            this.empty.hidden = this.records.size > 0;
            this.messages.scrollTop = scroll ? this.messages.scrollHeight : previousTop;
        }
    }
    window.TelegramMiniAppChat = TelegramMiniAppChat;
})();
