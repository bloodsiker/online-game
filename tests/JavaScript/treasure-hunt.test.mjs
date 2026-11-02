import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../public/js/treasure-hunt.js', import.meta.url), 'utf8');
const flush = async () => { for (let i = 0; i < 12; i++) await Promise.resolve(); };

function setup(responses, initialState = {}) {
    let now = 100000;
    let interval;
    const nodes = new Map();
    const calls = [];
    const node = name => {
        if (!nodes.has(name)) nodes.set(name, { hidden: true, style: {}, dataset: {}, textContent: '', handlers: {}, addEventListener(type, callback) { this.handlers[type] = callback; } });
        return nodes.get(name);
    };
    const config = { state: initialState, startUrl: '/start', completeUrl: '/complete', cancelUrl: '/cancel', refreshTokenUrl: '/csrf', csrf: 'original', labels: { error: 'Network error' } };
    const root = { dataset: { config: JSON.stringify(config) }, querySelector: selector => node(selector.slice(15, -1)) };
    const openedChests = [];
    const window = { top: { openLockpickingModal: url => openedChests.push(url) }, parent: {}, addEventListener() {} };
    vm.runInNewContext(source, {
        document: { querySelectorAll: () => [root] }, window, clearInterval: () => { interval = null; },
        setInterval: fn => { interval = fn; return 1; },
        Date: class extends Date { static now() { return now; } },
        fetch: async (url, options) => {
            calls.push({ url, options });
            const response = responses.shift();
            if (response instanceof Error) throw response;
            if (!response) throw new Error('Unexpected fetch');
            return { ok: (response.status || 200) < 400, status: response.status || 200, json: async () => JSON.parse(JSON.stringify(response.data)) };
        }
    });
    return { node, calls, openedChests, click: async name => { node(name).handlers.click(); await flush(); }, advance: async ms => { now += ms; interval?.(); await flush(); } };
}

const attempt = { ok: true, token: 'attempt-1', started_at: 100000, server_time: 100000, completes_at: 112000, expires_at: 232000 };

test('search shows progress then failure with retry and clears the previous result', async () => {
    const ui = setup([{ data: attempt }, { data: { ok: true, found: false, message: 'No treasure' } }, { data: { ...attempt, token: 'attempt-2', started_at: 112500, server_time: 112500, completes_at: 124500 } }]);
    await ui.click('open');
    assert.equal(ui.node('cancel-wrap').hidden, false);
    await ui.advance(12500);
    assert.equal(ui.node('message').textContent, 'No treasure');
    assert.equal(ui.node('start-wrap').hidden, false);
    assert.equal(ui.node('cancel-wrap').hidden, true);
    await ui.click('start');
    assert.equal(ui.node('message').textContent, '');
    assert.equal(ui.calls[2].url, '/start');
});

test('success hides search and exposes the found closed chest', async () => {
    const ui = setup([{ data: attempt }, { data: { ok: true, found: true, message: 'Found', reward: { share_item_id: 2, image: '/chest.png', name: 'Chest' }, open_url: '/items/lockpick/42?modal=1' } }]);
    await ui.click('open');
    await ui.advance(12500);
    assert.equal(ui.node('panel').hidden, true);
    assert.equal(ui.node('start-wrap').hidden, true);
    assert.equal(ui.node('chest-wrap').hidden, false);
    assert.equal(ui.node('reward-image').src, '/chest.png');
    assert.equal(ui.node('progress').hidden, true);
    await ui.click('chest');
    assert.equal(ui.node('panel').hidden, true);
    assert.equal(ui.node('modal').hidden, true);
    assert.deepEqual(ui.openedChests, ['/items/lockpick/42?modal=1']);
    assert.equal(ui.calls.filter(call => call.url === '/complete').length, 1);
});

test('modal cross cancels an active search without allowing completion', async () => {
    const ui = setup([{ data: attempt }, { data: { ok: true, message: 'Cancelled' } }]);
    await ui.click('open');
    await ui.click('dismiss');
    await ui.advance(12500);
    assert.equal(ui.calls[1].url, '/cancel');
    assert.equal(ui.calls.length, 2);
    assert.equal(ui.node('message').textContent, 'Cancelled');
    await ui.click('dismiss');
    assert.equal(ui.node('modal').hidden, true);
});

test('cancel sends the current token and prevents timed completion', async () => {
    const ui = setup([{ data: attempt }, { data: { ok: true, message: 'Cancelled' } }]);
    await ui.click('open');
    await ui.click('cancel');
    assert.equal(JSON.parse(ui.calls[1].options.body).token, 'attempt-1');
    await ui.advance(12500);
    assert.equal(ui.calls.length, 2);
    assert.equal(ui.node('message').textContent, 'Cancelled');
});

test('lost completion response retries the same token without starting a new search', async () => {
    const ui = setup([{ data: attempt }, new Error('Disconnected'), { data: { ok: true, found: false, message: 'No treasure' } }]);
    await ui.click('open');
    await ui.advance(12500);
    await ui.click('start');
    assert.equal(ui.calls[2].url, '/complete');
    assert.equal(JSON.parse(ui.calls[2].options.body).token, 'attempt-1');
});

test('expired CSRF token is refreshed before retrying the same action', async () => {
    const ui = setup([{ status: 419, data: {} }, { data: { token: 'fresh' } }, { data: attempt }]);
    await ui.click('open');
    assert.deepEqual(ui.calls.map(call => call.url), ['/start', '/csrf', '/start']);
    assert.equal(ui.calls[2].options.headers['X-CSRF-TOKEN'], 'fresh');
});

test('opening an existing search later preserves its original progress', async () => {
    const ui = setup([{ data: { ok: true, found: false, message: 'No treasure' } }], attempt);
    await ui.advance(6000);
    await ui.click('open');
    assert.equal(ui.node('percent').textContent, '50%');
    assert.equal(ui.calls.length, 0);
    await ui.advance(6500);
    assert.equal(ui.calls[0].url, '/complete');
});
