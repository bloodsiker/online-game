import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/payment-order.js', import.meta.url), 'utf8');

function setup(result) {
    const elements = new Map();
    const timers = [];
    const walletUpdates = [];
    const root = { querySelector(selector) {
        if (!elements.has(selector)) elements.set(selector, { hidden: false, textContent: '', addEventListener() {} });
        return elements.get(selector);
    } };
    const context = {
        window: { paymentOrderTracking: { url: '/status', status: 'pending', error: 'Status failed' }, parent: { sendToFrame(...args) { walletUpdates.push(args); } } },
        document: { querySelector() { return root; } },
        fetch: async () => ({ ok: true, status: 200, json: async () => result }),
        clearTimeout() {}, setTimeout(callback, delay) { timers.push({ callback, delay }); },
    };
    vm.runInNewContext(source, context);
    return { elements, timers, walletUpdates };
}

test('confirmed order updates status and wallet and stops polling', async () => {
    const state = setup({ status: 'paid', status_label: 'Paid', credited_at: '2026-10-21', note: 'Confirmed', money: 100, diamond: 87 });
    await new Promise(setImmediate);
    assert.equal(state.elements.get('[data-order-status]').textContent, 'Paid');
    assert.equal(state.elements.get('[data-order-cancel]').hidden, true);
    assert.equal(state.elements.get('[data-order-paid]').hidden, false);
    assert.equal(state.timers.length, 0);
    assert.equal(state.walletUpdates.length, 1);
    assert.equal(state.walletUpdates[0][0], 'character-frame');
    assert.equal(state.walletUpdates[0][1].diamond, 87);
});

test('pending order keeps cancellation available and checks again in ten seconds', async () => {
    const state = setup({ status: 'pending', status_label: 'Pending', credited_at: null, note: null });
    await new Promise(setImmediate);
    assert.equal(state.elements.get('[data-order-cancel]').hidden, false);
    assert.equal(state.elements.get('[data-order-paid]').hidden, true);
    assert.equal(state.timers.length, 1);
    assert.equal(state.timers[0].delay, 10000);
    assert.equal(state.walletUpdates.length, 0);
});
