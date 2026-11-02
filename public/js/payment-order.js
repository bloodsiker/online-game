(function () {
    'use strict';
    const options = window.paymentOrderTracking;
    const root = document.querySelector('[data-payment-order]');
    if (!options || !root) return;
    const button = root.querySelector('[data-order-check]');
    const error = root.querySelector('[data-order-error]');
    let status = options.status;
    let busy = false;
    let timer;
    async function check() {
        if (busy) return;
        clearTimeout(timer);
        busy = true;
        button.disabled = true;
        try {
            const response = await fetch(options.url, { credentials: 'same-origin', cache: 'no-store', headers: { Accept: 'application/json' } });
            if (response.status === 401) { window.top.location.href = options.loginUrl; return; }
            if (!response.ok) throw new Error(options.error);
            const result = await response.json();
            status = result.status;
            root.querySelector('[data-order-status]').textContent = result.status_label;
            root.querySelector('[data-order-note]').textContent = result.note || '';
            root.querySelector('[data-order-pending]').hidden = status !== 'pending';
            root.querySelector('[data-order-cancel]').hidden = status !== 'pending';
            root.querySelector('[data-order-paid]').hidden = !result.credited_at;
            error.textContent = '';
            if (result.credited_at) window.parent.sendToFrame?.('character-frame', { money: result.money, diamond: result.diamond });
        } catch (failure) {
            error.textContent = options.error;
        } finally {
            busy = false;
            button.disabled = false;
            if (status === 'pending') timer = setTimeout(check, 10000);
        }
    }
    button.addEventListener('click', check);
    check();
})();
