import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/prelaunch.js', import.meta.url), 'utf8');

function setup(fetchResult = { ok: true, status: 200, json: async () => ({ ok: true, message: 'Subscribed' }) }) {
    function node() {
        const classes = new Set();
        return { hidden: false, disabled: false, textContent: '', isConnected: true, handlers: new Map(),
            addEventListener(name, fn) { this.handlers.set(name, fn); },
            focus() { this.focused = true; },
            classList: { add: value => classes.add(value), remove: value => classes.delete(value), contains: value => classes.has(value) },
        };
    }
    const button = Object.assign(node(), { textContent: 'Subscribe' });
    const email = node();
    const result = Object.assign(node(), { hidden: true });
    const link = node();
    const close = node();
    const form = Object.assign(node(), { hidden: true, action: '/homepage/launch-subscription',
        dataset: { sending: 'Saving', error: 'Try again', expired: 'Reload page', rateLimited: 'Wait a minute' },
        reportValidity: () => true,
        querySelector: selector => selector.includes('email') ? email : button,
    });
    const modal = Object.assign(node(), { open: false, dataset: { openOnLoad: '0' },
        showModal() { this.open = true; },
        close() { this.open = false; this.handlers.get('close')(); },
        querySelector: selector => ({ form, '[role="status"]': result, '[data-prelaunch-signup]': link, '[data-prelaunch-close]': close })[selector],
    });
    const document = Object.assign(node(), { getElementById: () => modal, body: node(), activeElement: button });
    const requests = [];
    vm.runInNewContext(source, { document, FormData: class {}, fetch: async (url, options) => {
        requests.push({ url, options });
        if (fetchResult instanceof Error) throw fetchResult;
        return fetchResult;
    } });
    function event(target) {
        return { target, prevented: false, stopped: false,
            preventDefault() { this.prevented = true; }, stopImmediatePropagation() { this.stopped = true; },
        };
    }
    return { document, modal, form, result, button, link, email, close, requests, event };
}

test('login/registration opens modal and blocks legacy handlers without making a request', () => {
    const app = setup();
    const e = app.event({ closest: () => app.button });
    app.document.handlers.get('click')(e);
    assert.equal(e.prevented, true);
    assert.equal(e.stopped, true);
    assert.equal(app.modal.open, true);
    assert.equal(app.document.body.classList.contains('prelaunch-modal-open'), true);
    assert.equal(app.requests.length, 0);
    app.close.handlers.get('click')();
    assert.equal(app.modal.open, false);
    assert.equal(app.button.focused, true);
});

test('Enter in login form opens the notice instead of submitting credentials', () => {
    const app = setup();
    const e = app.event({ matches: () => true });
    app.document.handlers.get('submit')(e);
    assert.equal(e.prevented, true);
    assert.equal(e.stopped, true);
    assert.equal(app.modal.open, true);
    assert.equal(app.requests.length, 0);
});

test('subscription link reveals email form and successful subscription stays in dialog', async () => {
    const app = setup();
    app.link.handlers.get('click')(app.event(app.link));
    assert.equal(app.form.hidden, false);
    assert.equal(app.email.focused, true);
    await app.form.handlers.get('submit')(app.event(app.form));
    assert.equal(app.requests.length, 1);
    assert.equal(app.requests[0].options.method, 'POST');
    assert.equal(app.result.textContent, 'Subscribed');
    assert.equal(app.result.hidden, false);
    assert.equal(app.form.hidden, true);
    assert.equal(app.button.disabled, false);
});

for (const [status, expected] of [[419, 'Reload page'], [429, 'Wait a minute'], [500, 'Try again']]) {
    test(`HTTP ${status} keeps form available and shows localized error`, async () => {
        const app = setup({ ok: false, status, json: async () => ({ message: 'Internal details' }) });
        app.form.hidden = false;
        await app.form.handlers.get('submit')(app.event(app.form));
        assert.equal(app.form.hidden, false);
        assert.equal(app.result.textContent, expected);
        assert.equal(app.result.classList.contains('is-error'), true);
        assert.equal(app.button.disabled, false);
    });
}

test('network failure shows localized retry message', async () => {
    const app = setup(new Error('Failed to fetch'));
    await app.form.handlers.get('submit')(app.event(app.form));
    assert.equal(app.result.textContent, 'Try again');
});
