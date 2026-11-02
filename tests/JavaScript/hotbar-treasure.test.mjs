import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../app/Modules/Interface/Presentation/Views/index.blade.php', import.meta.url), 'utf8');
const start = source.indexOf('function useAbility(slotNumber)');
const end = source.indexOf('// Загрузка хотбара', start);
assert.ok(start >= 0 && end > start);
const script = source.slice(start, end)
    .replace("@json(route('location'))", JSON.stringify('/location'))
    .replace(/\{\{.*?\}\}/g, '/endpoint');

async function click(payload) {
    const calls = { messages: [], refreshes: 0, reloads: 0, stats: [], frame: [], navigations: [], cooldowns: [] };
    const count = { textContent: '1' };
    const classes = new Set();
    const slot = {
        dataset: { entityType: 'item', entityId: '101', cooldown: '0' },
        classList: { contains: key => classes.has(key), add: key => classes.add(key), remove: key => classes.delete(key) },
        querySelector: () => count,
    };
    const context = vm.createContext({
        hotbarSlots: { querySelector: () => slot },
        hotbarCsrf: 'csrf',
        fetch: async (_url, options) => {
            assert.deepEqual(JSON.parse(options.body), { slot: 1 });
            return { json: async () => payload };
        },
        setTimeout: callback => callback(),
        refreshHotbar: () => { calls.refreshes++; },
        startHotbarCooldown: (_slot, until) => calls.cooldowns.push(until),
        showHotbarMessage: message => calls.messages.push(message),
        gameFrame: {
            set src(url) { calls.navigations.push(url); },
            contentWindow: { location: { href: '/location/move/east', reload: () => { calls.reloads++; } }, postMessage: data => calls.frame.push(data) },
        },
        sendToFrame: (_name, data) => calls.stats.push(data),
        gameTranslate: value => value,
    });
    vm.runInContext(script, context);
    context.useAbility(1);
    await new Promise(resolve => setImmediate(resolve));
    return { calls, count };
}

test('successful map check opens the current location without repeating the previous movement action', async () => {
    const { calls, count } = await click({ status: 'success', action: 'treasure_check', message: 'Карта активирована.', count: 1, slot_cleared: false });
    assert.deepEqual(calls.messages, ['Карта активирована.']);
    assert.equal(calls.refreshes, 1);
    assert.equal(calls.reloads, 0);
    assert.deepEqual(calls.navigations, ['/location']);
    assert.equal(count.textContent, '1');
    assert.equal(calls.stats.length, 0);
    assert.equal(calls.frame.length, 0);
});

test('wrong location shows its error without refreshing or consuming the map', async () => {
    const { calls, count } = await click({ status: 'error', action: 'treasure_check', message: 'Место не совпадает.' });
    assert.deepEqual(calls.messages, ['Место не совпадает.']);
    assert.equal(calls.refreshes, 0);
    assert.equal(calls.reloads, 0);
    assert.equal(count.textContent, '1');
    assert.equal(calls.stats.length, 0);
});

test('ordinary item use continues updating count and health', async () => {
    const { calls, count } = await click({ status: 'success', count: 2, slot_cleared: false, hp_now: 50, hp_max: 100, mp_now: 20, mp_max: 50 });
    assert.equal(count.textContent, 2);
    assert.equal(calls.stats.length, 1);
    assert.equal(calls.frame.length, 1);
    assert.equal(calls.stats[0].hp.current, 50);
    assert.equal(calls.reloads, 0);
});

test('potion use displays the server cooldown and refreshes other slots sharing the limit', async () => {
    const until = Math.floor(Date.now() / 1000) + 60;
    const { calls } = await click({ status: 'success', count: 2, cooldown_until: until });
    assert.deepEqual(calls.cooldowns, [until]);
    assert.equal(calls.refreshes, 1);
});

test('rejected potion reuse displays the actual cooldown without updating stats', async () => {
    const until = Math.floor(Date.now() / 1000) + 40;
    const { calls } = await click({ status: 'error', message: 'Wait', cooldown_until: until });
    assert.deepEqual(calls.cooldowns, [until]);
    assert.deepEqual(calls.messages, ['Wait']);
    assert.equal(calls.stats.length, 0);
});
