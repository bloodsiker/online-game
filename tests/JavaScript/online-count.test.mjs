import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../resources/js/online-count.js', import.meta.url), 'utf8');

test('public online channel requests current count through Pusher and renders realtime updates', () => {
    const counters = [{ textContent: '1' }, { textContent: '1' }];
    let ready, subscribed, updated;
    const requests = [];
    const channel = {
        subscription: { trigger: (...args) => requests.push(args) },
        listen(name, callback) { assert.equal(name, '.online.count.updated'); updated = callback; return this; },
        subscribed(callback) { subscribed = callback; },
    };
    vm.runInNewContext(source, {
        document: { addEventListener: (name, callback) => { ready = callback; }, querySelectorAll: () => counters },
        window: { Echo: { channel: name => { assert.equal(name, 'online-count'); return channel; } } },
    });
    ready();
    subscribed();
    assert.equal(requests[0][0], 'client-online-count-request');
    updated({ count: 12 });
    assert.deepEqual(counters.map(x => x.textContent), ['12', '12']);
    updated({ count: -4 });
    assert.deepEqual(counters.map(x => x.textContent), ['0', '0']);
});
