import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const entries = [
    { file: '../../public/js/item_tooltip.js', start: 'let showItemInfo =', end: '// Сортировка', name: 'showItemInfo' },
    { file: '../../public/main/js/common.js', start: 'function artifactAlt(obj, evnt, show)', end: '// Сортировка', name: 'artifactAlt' },
    { file: '../../public/main/js/simple_alt.js', start: 'function artifactAltSimple(', end: "var artifact_alt = top.gebi", name: 'artifactAltSimple' },
];

function setup(entry, enabled) {
    const source = fs.readFileSync(new URL(entry.file, import.meta.url), 'utf8');
    const start = source.indexOf(entry.start);
    const end = source.indexOf(entry.end, start);
    assert.ok(start >= 0 && end > start);
    const tooltip = { style: { display: 'block' } };
    const root = { gameSettings: { show_item_tooltips: enabled }, gebi: () => tooltip };
    const document = { onmousemove: () => {} };
    const context = vm.createContext({
        document, top: root, _top: () => root, itemTooltipTopWindow: () => root,
        hideItemTooltip: () => { tooltip.style.display = 'none'; document.onmousemove = null; },
    });
    vm.runInContext(source.slice(start, end) + '\nreturn "enabled";\n};\nglobalThis.tooltipEntry = ' + entry.name + ';', context);
    return { call: context.tooltipEntry, tooltip, document };
}

for (const entry of entries) {
    test(entry.name + ' suppresses item hover without altering item click', () => {
        const { call, tooltip, document } = setup(entry, false);
        const onClick = () => 'item info';
        const obj = { onclick: onClick };
        assert.equal(call(obj, {}, 2), undefined);
        assert.equal(tooltip.style.display, 'none');
        assert.equal(document.onmousemove, null);
        assert.equal(obj.onclick, onClick);
    });
    test(entry.name + ' allows normal handler when setting is enabled or absent', () => {
        for (const enabled of [true, undefined]) {
            const { call } = setup(entry, enabled);
            assert.equal(call({}, {}, 2), 'enabled');
        }
    });
}

test('effect tooltips are not disabled with item tooltips', () => {
    const { call } = setup(entries[2], false);
    assert.equal(call(null, 2, {}, true), 'enabled');
});
