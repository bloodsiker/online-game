(() => {
    const form = document.querySelector('[data-telegram-link-form]');
    const modal = document.getElementById('telegram-link-modal');
    const overlay = document.getElementById('telegram-link-overlay');
    if (!form || !modal || !overlay) return;

    const opener = form.querySelector('[type="submit"]');
    const message = document.getElementById('telegram-link-message');
    const ready = document.getElementById('telegram-link-ready');
    const link = document.getElementById('telegram-bot-link');
    const texts = JSON.parse(modal.dataset.translations || '{}');
    let previousFocus;
    let activeRequest = null;

    function open() {
        if (modal.hidden) previousFocus = document.activeElement;
        modal.hidden = overlay.hidden = false;
        modal.querySelector('[data-telegram-close]').focus();
    }

    function close() {
        activeRequest?.abort();
        activeRequest = null;
        opener.disabled = false;
        modal.removeAttribute('aria-busy');
        modal.hidden = overlay.hidden = true;
        previousFocus?.focus();
    }

    function showMessage(text) {
        message.textContent = text;
        message.hidden = false;
        ready.hidden = true;
    }

    function showLink(value) {
        const url = new URL(value);
        if (url.origin !== 'https://t.me' || url.username || url.password) throw new Error('Invalid bot link');
        link.href = url.href;
        ready.hidden = false;
        message.hidden = true;
    }

    modal.querySelectorAll('[data-telegram-close]').forEach(button => button.addEventListener('click', close));
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', event => {
        if (modal.hidden) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            close();
        }
        if (event.key === 'Tab') {
            const controls = [...modal.querySelectorAll('button,input,a[href]')]
                .filter(control => !control.disabled && control.getClientRects().length);
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            }
        }
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (activeRequest) return;
        open();
        showMessage(texts.preparing);
        link.removeAttribute('href');
        const current = new AbortController();
        activeRequest = current;
        opener.disabled = true;
        modal.setAttribute('aria-busy', 'true');
        const timeout = setTimeout(() => current.abort(), 15000);
        try {
            const response = await fetch(form.action, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, body: new FormData(form), signal: current.signal,
            });
            const data = await response.json().catch(() => ({}));
            if (activeRequest !== current) return;
            if (!response.ok || data.ok !== true || typeof data.url !== 'string') {
                const fallback = response.status === 419 ? texts.session_expired
                    : response.status === 401 ? texts.login_again
                    : response.status === 429 ? texts.too_many_attempts : texts.failed;
                showMessage(Object.values(data.errors || {}).flat().join(' ') || fallback);
                return;
            }
            showLink(data.url);
        } catch (error) {
            if (activeRequest === current) showMessage(texts.connection_failed);
        } finally {
            clearTimeout(timeout);
            if (activeRequest === current) {
                activeRequest = null;
                opener.disabled = false;
                modal.removeAttribute('aria-busy');
            }
        }
    });

    if (link.hasAttribute('href')) {
        try {
            showLink(link.href);
            open();
        } catch (error) {
            link.removeAttribute('href');
        }
    }
})();
