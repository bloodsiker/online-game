import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/player-trade-realtime.js', import.meta.url), 'utf8');

function fixture(sync = async () => {}) {
    let nextId = 1;
    const timeouts = new Map();
    const intervals = new Map();
    const clock = {
        timeouts, intervals,
        async fire(delay) {
            const entry = [...timeouts.entries()].find(([, timer]) => timer.delay === delay);
            assert.ok(entry, `Expected a ${delay} ms timer`);
            timeouts.delete(entry[0]);
            await entry[1].callback();
        },
    };
    const context = {
        window: {},
        setTimeout(callback, delay) { const id = nextId++; timeouts.set(id, { callback, delay }); return id; },
        clearTimeout(id) { timeouts.delete(id); },
        setInterval(callback, delay) { const id = nextId++; intervals.set(id, { callback, delay }); return id; },
        clearInterval(id) { intervals.delete(id); },
    };
    vm.runInNewContext(source, context);
    const bindings = new Map();
    const events = new Map();
    const channel = {
        listen(name, callback) { events.set(name, callback); return this; },
        stopListening(name, callback) { assert.equal(events.get(name), callback); events.delete(name); return this; },
        subscribed(callback) { this.onSubscribed = callback; return this; },
        error(callback) { this.onError = callback; return this; },
    };
    const left = [];
    const echo = {
        connector: { pusher: { connection: {
            bind(name, callback) { bindings.set(name, callback); },
            unbind(name, callback) { assert.equal(bindings.get(name), callback); bindings.delete(name); },
        } } },
        private(name) { assert.equal(name, 'player-trade.example'); return channel; },
        leave(name) { left.push(name); },
    };
    const transport = new context.window.PlayerTradeRealtime({ uuid: 'example', sync, getEcho: () => echo });
    return { transport, clock, channel, events, bindings, echo, left };
}

test('uses the existing Echo channel, coalesces changes and reduces healthy polling to once per minute', async () => {
    let syncs = 0;
    const f = fixture(async () => { syncs++; });
    f.transport.start();
    await f.clock.fire(0);
    f.channel.onSubscribed();
    await f.clock.fire(0);
    assert.equal([...f.clock.intervals.values()][0].delay, 60000);
    f.events.get('.player-trade.changed')();
    f.events.get('.player-trade.changed')();
    f.events.get('.player-trade.changed')();
    assert.equal(f.clock.timeouts.size, 1);
    await f.clock.fire(75);
    assert.equal(syncs, 3);
    f.transport.stop();
    assert.deepEqual(f.left, ['player-trade.example']);
    assert.equal(f.bindings.size, 0);
    assert.equal(f.clock.intervals.size, 0);
});

test('falls back during disconnection and reloads current state after resubscription', async () => {
    let syncs = 0;
    const f = fixture(async () => { syncs++; });
    f.transport.start();
    await f.clock.fire(0);
    f.channel.onSubscribed();
    await f.clock.fire(0);
    const stateChange = f.bindings.get('state_change');
    stateChange({ current: 'unavailable' });
    assert.equal([...f.clock.intervals.values()][0].delay, 10000);
    stateChange({ current: 'connected' });
    f.channel.onSubscribed();
    await f.clock.fire(0);
    assert.equal(syncs, 3);
    assert.equal([...f.clock.intervals.values()][0].delay, 60000);
    f.transport.stop();
});

test('does not lose an update that arrives while the previous state request is in flight', async () => {
    let syncs = 0;
    let release;
    const pending = new Promise((resolve) => { release = resolve; });
    const f = fixture(async () => { if (++syncs === 1) await pending; });
    f.transport.start();
    const first = f.clock.fire(0);
    f.events.get('.player-trade.changed')();
    f.events.get('.player-trade.changed')();
    assert.equal(syncs, 1);
    release();
    await first;
    await f.clock.fire(75);
    assert.equal(syncs, 2);
    f.transport.stop();
});

test('waits for parent Echo, retries subscription errors and cancels retries on navigation', async () => {
    const f = fixture();
    let available = null;
    f.transport.getEcho = () => available;
    f.transport.start();
    await f.clock.fire(0);
    available = f.echo;
    await f.clock.fire(1000);
    f.channel.onError();
    assert.equal([...f.clock.intervals.values()][0].delay, 10000);
    assert.ok([...f.clock.timeouts.values()].some((timer) => timer.delay === 5000));
    f.transport.stop();
    assert.equal(f.clock.timeouts.size, 0);
    assert.equal(f.clock.intervals.size, 0);
    assert.equal(f.events.size, 0);
});
