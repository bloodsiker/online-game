import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/game-session.js', import.meta.url), 'utf8');

function setup({ status = 200, authenticated = true, checkFails = false } = {}) {
    const redirects = [];
    let probes = 0;
    class XHR {
        open() {}
        send() { this.status = status; this.listener.call(this); }
        addEventListener(event, listener) { this.listener = listener; }
        getResponseHeader() { return null; }
    }
    const events = {};
    const document = {
        currentScript: { dataset: { homeUrl: '/', sessionStatusUrl: '/session/status' } },
        addEventListener(name, listener) { events[name] = listener; },
        querySelectorAll: () => [],
    };
    const window = {
        document, XMLHttpRequest: XHR,
        location: { href: 'http://game.test/game', origin: 'http://game.test', pathname: '/game', replace: url => redirects.push(url) },
        fetch: async url => {
            if (url === '/session/status') {
                probes++;
                if (checkFails) throw new Error('Network down');
                return { ok: true, status: 200, json: async () => ({ authenticated }) };
            }
            return { ok: status < 400, status, headers: { get: () => null } };
        },
    };
    window.top = window;
    vm.runInNewContext(source, { window, document, URL, Promise });
    return { window, document, redirects, events, probes: () => probes };
}

test('401 redirects whole game without an extra request', async () => {
    const state = setup({ status: 401 });
    const response = await state.window.fetch('/items/use/1');
    assert.equal(response.status, 401);
    assert.deepEqual(state.redirects, ['/']);
    assert.equal(state.probes(), 0);
});

for (const status of [405, 419, 500]) {
    test(status + ' redirects only after confirming expired session', async () => {
        const state = setup({ status, authenticated: false });
        await state.window.fetch('/action');
        await state.window.gameSessionGuard.check();
        assert.deepEqual(state.redirects, ['/']);
        assert.equal(state.probes(), 1);
    });

    test(status + ' preserves authenticated session', async () => {
        const state = setup({ status, authenticated: true });
        await state.window.fetch('/action');
        await state.window.gameSessionGuard.check();
        assert.deepEqual(state.redirects, []);
    });
}

test('XMLHttpRequest errors use same session handling', async () => {
    const state = setup({ status: 500, authenticated: false });
    const xhr = new state.window.XMLHttpRequest();
    xhr.open('POST', '/action');
    xhr.send();
    await state.window.gameSessionGuard.check();
    assert.deepEqual(state.redirects, ['/']);
});

test('failed connection does not log player out', async () => {
    const state = setup({ status: 500, checkFails: true });
    await state.window.fetch('/action');
    await state.window.gameSessionGuard.check();
    assert.deepEqual(state.redirects, []);
});

test('third-party response does not log player out', async () => {
    const state = setup({ status: 401 });
    await state.window.fetch('https://external.test/action');
    assert.deepEqual(state.redirects, []);
});

test('repeated script includes do not install wrappers twice', () => {
    const state = setup();
    const fetch = state.window.fetch;
    vm.runInNewContext(source, { window: state.window, document: state.document, URL, Promise });
    assert.equal(state.window.fetch, fetch);
});

test('homepage error cannot cause redirect loop', async () => {
    const state = setup({ status: 500, authenticated: false });
    state.window.location.pathname = '/';
    await state.window.fetch('/action');
    await state.window.gameSessionGuard.check();
    assert.deepEqual(state.redirects, []);
});

test('iframe AJAX error redirects parent game window', async () => {
    const state = setup();
    const child = {
        document: { addEventListener() {}, querySelectorAll: () => [] },
        fetch: async () => ({ status: 401, headers: { get: () => null } }),
    };
    state.events.load({ target: { tagName: 'IFRAME', contentWindow: child } });
    await child.fetch('/action');
    assert.deepEqual(state.redirects, ['/']);
    assert.equal(child.gameSessionGuard, state.window.gameSessionGuard);
});
