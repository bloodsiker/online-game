(() => {
    'use strict';
    document.querySelectorAll('[data-treasure-search]').forEach(root => {
        if (root.dataset.initialized) return;
        root.dataset.initialized = '1';
        const config = JSON.parse(root.dataset.config);
        const find = name => root.querySelector('[data-treasure-' + name + ']');
        let state = config.state;
        let clockOffset = state.server_time ? state.server_time - Date.now() : null;
        let timer = null;
        let busy = false;
        let finished = false;
        let chestUrl = null;
        const show = (name, visible) => { find(name).hidden = !visible; };
        const stop = () => { clearInterval(timer); timer = null; };
        const message = text => { find('message').textContent = text || ''; };
        const buttons = running => {
            show('cancel-wrap', running);
            show('close-wrap', !running);
            show('start-wrap', !running && !finished);
        };
        async function request(url, body = {}) {
            let response = await fetch(url, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf },
                body: JSON.stringify(body)
            });
            if (response.status === 419) {
                const token = await fetch(config.refreshTokenUrl, { credentials: 'same-origin', headers: { Accept: 'application/json' } }).then(r => r.json());
                if (token.token) {
                    config.csrf = token.token;
                    response = await fetch(url, {
                        method: 'POST', credentials: 'same-origin',
                        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': config.csrf }, body: JSON.stringify(body)
                    });
                }
            }
            const data = await response.json();
            if (!response.ok || !data.ok) {
                const error = new Error(data.message || config.labels.error);
                error.status = response.status;
                throw error;
            }
            return data;
        }
        async function complete() {
            if (busy) return;
            busy = true;
            show('cancel-wrap', false);
            try {
                const data = await request(config.completeUrl, { token: state.token });
                state.token = null;
                finished = Boolean(data.found);
                message(data.message);
                if (finished) {
                    find('reward-image').src = data.reward.image;
                    find('reward-image').alt = data.reward.name;
                    show('reward', true);
                    show('chest-wrap', true);
                    chestUrl = data.open_url;
                    show('panel', false);
                    show('progress', false);
                    try { window.parent.refreshHotbar?.(); } catch (_) {}
                }
            } catch (error) {
                if (error.status === 422 || error.status === 200) state.token = null;
                message(error.message || config.labels.error);
            } finally {
                busy = false;
                buttons(false);
            }
        }
        function run(data) {
            stop();
            if (data !== state || clockOffset === null) clockOffset = data.server_time - Date.now();
            state = data;
            const offset = clockOffset;
            buttons(true);
            show('progress', true);
            show('reward', false);
            show('chest-wrap', false);
            message('');
            const tick = () => {
                const now = Date.now() + offset;
                const percent = Math.min(100, Math.max(0, (now - data.started_at) / Math.max(1, data.completes_at - data.started_at) * 100));
                find('fill').style.width = percent + '%';
                find('percent').textContent = Math.floor(percent) + '%';
                if (now >= data.completes_at + 350) { stop(); complete(); }
            };
            timer = setInterval(tick, 100);
            tick();
        }
        async function start() {
            if (busy || finished) return;
            busy = true;
            message('');
            show('start-wrap', false);
            show('progress', false);
            try {
                const data = await request(config.startUrl);
                busy = false;
                run(data);
            }
            catch (error) { message(error.message || config.labels.error); buttons(false); }
            finally { busy = false; }
        }
        find('open').addEventListener('click', () => {
            show('modal', true);
            if (state.token && state.expires_at > Date.now() + (clockOffset || 0)) run(state);
            else start();
        });
        find('start').addEventListener('click', () => state.token ? run(state) : start());
        async function cancel() {
            if (busy) return;
            busy = true;
            stop();
            try {
                const data = await request(config.cancelUrl, { token: state.token });
                message(data.message);
                state.token = null;
            } catch (error) { message(error.message || config.labels.error); }
            finally { busy = false; buttons(false); }
        }
        find('cancel').addEventListener('click', cancel);
        find('close').addEventListener('click', () => { if (!busy) show('modal', false); });
        function dismiss() {
            if (busy) return;
            if (timer !== null) cancel();
            else show('modal', false);
        }
        find('dismiss').addEventListener('click', dismiss);
        find('dismiss').addEventListener('keydown', event => {
            if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); dismiss(); }
        });
        function openChest() {
            if (!chestUrl) return;
            show('modal', false);
            try {
                if (window.top.openLockpickingModal) window.top.openLockpickingModal(chestUrl);
                else window.location.href = chestUrl.replace('modal=1', 'modal=0');
            } catch (_) { window.location.href = chestUrl.replace('modal=1', 'modal=0'); }
        }
        find('chest').addEventListener('click', openChest);
        window.addEventListener('pagehide', stop, { once: true });
    });
})();
