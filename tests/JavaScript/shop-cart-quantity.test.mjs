import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/shop-cart-quantity.js', import.meta.url), 'utf8');

function fixture(send) {
    let nextId = 0;
    const timers = new Map();
    const context = {
        setTimeout(callback) { timers.set(++nextId, callback); return nextId; },
        clearTimeout(id) { timers.delete(id); },
    };
    vm.runInNewContext(source, context);
    const saved = [];
    const errors = [];
    return {
        queue: context.createShopCartQuantityQueue({
            send, onSaved: (id, response) => saved.push([id, response]), onError: error => errors.push(error)
        }),
        timers, saved, errors
    };
}

test('rapid clicks are coalesced and flush saves the last quantity before checkout', async () => {
    const calls = [];
    const { queue, timers } = fixture(async (id, quantity) => { calls.push([id, quantity]); });
    queue.set('11', 2);
    queue.set('11', 3);
    queue.set('11', 7);
    assert.equal(timers.size, 1);
    await queue.flush();
    assert.deepEqual(calls, [['11', 7]]);
    assert.equal(timers.size, 0);
});

test('writes are serialized and changes made during a request are not lost', async () => {
    let release;
    const calls = [];
    const { queue } = fixture(async (id, quantity) => {
        calls.push([id, quantity]);
        if (calls.length === 1) await new Promise(resolve => { release = resolve; });
    });
    queue.set('11', 2);
    const first = queue.flush();
    queue.set('12', 4);
    queue.set('11', 5);
    assert.deepEqual(calls, [['11', 2]]);
    release();
    await first;
    assert.deepEqual(calls, [['11', 2], ['11', 5], ['12', 4]]);
    await queue.flush();
    assert.equal(calls.length, 3);
});

test('failed saves reject checkout and can safely be retried', async () => {
    let attempts = 0;
    const { queue } = fixture(async () => {
        if (++attempts === 1) throw new Error('network error');
        return { quantity: 6 };
    });
    queue.set('11', 6);
    await assert.rejects(queue.flush(), /network error/);
    await queue.flush();
    assert.equal(attempts, 2);
});

test('automatic save errors are reported without discarding the pending quantity', async () => {
    let attempts = 0;
    const { queue, timers, errors } = fixture(async () => {
        if (++attempts === 1) throw new Error('save failed');
    });
    queue.set('11', 9);
    const callback = [...timers.values()][0];
    callback();
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(errors.length, 1);
    await queue.flush();
    assert.equal(attempts, 2);
});
