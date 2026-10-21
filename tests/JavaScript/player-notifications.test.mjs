import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/player-notifications.js', import.meta.url), 'utf8');
function fixture(fetch, show = () => {}, isDisplayed = () => true) {
    const context = {};
    vm.runInNewContext(source, context);
    return context.createPlayerNotificationDelivery({ fetch, show, isDisplayed,
        pendingUrl: '/pending', acknowledgeUrl: '/ack', csrfToken: 'token' });
}
const entries = [{ id: 1, message: { id: 7 } }];
const response = (body, ok = true) => ({ ok, json: async () => body });

test('acknowledges only after rendering, with CSRF and matching chat ID', async () => {
    const calls = [];
    const delivery = fixture(async (url, options) => {
        calls.push(url);
        if (url === '/ack') {
            assert.equal(options.headers['X-CSRF-TOKEN'], 'token');
            assert.deepEqual(JSON.parse(options.body), { receipts: [{ id: 1, message_id: 7 }] });
        }
        return response(entries);
    }, () => calls.push('render'));
    await delivery.sync();
    assert.deepEqual(calls, ['/pending', 'render', '/ack']);
});

test('does not acknowledge failed render or a missing message', async () => {
    for (const show of [() => { throw Error('render failed'); }, () => {}]) {
        const calls = [];
        await fixture(async url => { calls.push(url); return response(entries); }, show, () => false).sync();
        assert.deepEqual(calls, ['/pending']);
    }
});

test('failed acknowledgement can be retried and stopped delivery does nothing', async () => {
    let acknowledgements = 0;
    const delivery = fixture(async url => {
        if (url === '/ack') return response({}, ++acknowledgements > 1);
        return response(entries);
    });
    await delivery.sync();
    await delivery.sync();
    assert.equal(acknowledgements, 2);
    delivery.stop();
    await delivery.sync();
    assert.equal(acknowledgements, 2);
});

test('concurrent sync signals do not run parallel requests', async () => {
    let release;
    let requests = 0;
    const delivery = fixture(async () => {
        requests++;
        if (requests === 1) await new Promise(resolve => { release = resolve; });
        return response([]);
    });
    const first = delivery.sync();
    await delivery.sync();
    await delivery.sync();
    assert.equal(requests, 1);
    release();
    await first;
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(requests, 2);
});
