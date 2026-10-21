import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/backpack-drop.js', import.meta.url), 'utf8');

for (const count of [0, 1, 2]) {
    test(`AJAX drop updates quantity ${count} without frame navigation`, async () => {
        let removed = false;
        let badgeRemoved = false;
        let closed = false;
        const badge = { textContent: 3, remove() { badgeRemoved = true; } };
        const cell = {
            setAttribute(name, value) { this[name] = value; },
            querySelector: () => badge,
            closest: () => ({ remove() { removed = true; } }),
        };
        const capacity = { textContent: 1 };
        const button = { disabled: false };
        const context = {
            window: { location: { href: '/backpack/bag?group=quest' }, top: { showErrorIframe: assert.fail } },
            document: { querySelector: () => button, querySelectorAll: () => [cell], getElementById: () => capacity },
            closeDropPopup() { closed = true; },
            gameTranslate: text => text,
            fetch: async (url, options) => {
                assert.equal(url, '/items/drop/105');
                assert.equal(options.method, 'POST');
                assert.equal(options.headers['X-CSRF-TOKEN'], 'csrf');
                assert.deepEqual(JSON.parse(options.body), { c: 1, qty: 1 });
                return { ok: true, json: async () => ({ ok: true, backpack_update: { count, removed: count === 0, capacity_used: count === 0 ? 0 : 1 } }) };
            },
        };
        vm.runInNewContext(source, context);
        await context.window.dropBackpackItem(105, '/items/drop/105', 1, 'csrf');
        assert.equal(context.window.location.href, '/backpack/bag?group=quest');
        assert.equal(removed, count === 0);
        assert.equal(closed, true);
        assert.equal(button.disabled, false);
        assert.equal(capacity.textContent, count === 0 ? 0 : 1);
        if (count > 0) assert.equal(cell['data-count'], count);
        if (count === 1) assert.equal(badgeRemoved, true);
        if (count > 1) assert.equal(badge.textContent, count);
    });
}

test('failed AJAX drop keeps item and confirmation open', async () => {
    let error = '';
    const context = {
        window: { top: { showErrorIframe(message) { error = message; } } },
        document: { querySelector: () => ({ disabled: false }), querySelectorAll: assert.fail },
        closeDropPopup: assert.fail,
        gameTranslate: text => text,
        fetch: async () => ({ ok: false, json: async () => ({ ok: false, message: 'Нельзя выбросить' }) }),
    };
    vm.runInNewContext(source, context);
    await context.window.dropBackpackItem(105, '/items/drop/105', 1, 'csrf');
    assert.equal(error, 'Нельзя выбросить');
});
