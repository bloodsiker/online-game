import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../app/Modules/Backpack/Presentation/Views/bag.blade.php', import.meta.url), 'utf8');
const helper = source.match(/function gebi\(id\) \{[\s\S]*?\n    \}/)[0];
const dialog = source.match(/function showDropDialog\(itemId, count\) \{[\s\S]*?(?=\n    function doDropItem)/)[0];

for (const count of [1, 2]) {
    test(`drop confirmation opens without legacy scripts for quantity ${count}`, () => {
        const nodes = new Map();
        const getNode = id => {
            if (!nodes.has(id)) {
                nodes.set(id, {
                    style: {}, offsetHeight: 200, offsetWidth: 300,
                    setAttribute(name, value) { this[name] = value; },
                    parentNode: { querySelector: () => ({ classList: { add() {}, remove() {} } }) },
                });
            }
            return nodes.get(id);
        };
        let overlayShown = false;
        const context = {
            document: { getElementById: getNode },
            window: { innerHeight: 800, innerWidth: 1000 },
            gameTranslate: text => text,
            _ctxItemName: 'Тленоцвет', _ctxItemImage: '/herb.png',
            frame_content_hider: { show() { overlayShown = true; } },
        };
        vm.runInNewContext(`${helper}\n${dialog}\nshowDropDialog(12246, ${count});`, context);
        assert.equal(getNode('cart_amount_div').style.display, 'block');
        assert.equal(getNode('drop-qty-row').style.display, count > 1 ? '' : 'none');
        assert.equal(getNode('ask_confirm_title_amount').innerHTML, 'Тленоцвет');
        assert.equal(overlayShown, true);
        if (count > 1) {
            assert.equal(getNode('cart_amount')['data-max-value'], count);
            getNode('cart_amount_all').onclick();
            assert.equal(getNode('cart_amount').value, count);
        }
    });
}
