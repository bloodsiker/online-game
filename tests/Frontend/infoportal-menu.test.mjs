import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const read = path => fs.readFileSync(new URL(`../../${path}`, import.meta.url), 'utf8');
const xml = read('public/data/locale/ru/topMenu.xml');
const view = read('app/Modules/Interface/Presentation/Views/menu.blade.php');
const engine = read('public/main/js/canvas/canvas.all.js');

test('infoportal contains three submenu commands with matching labels', () => {
    const portal = xml.match(/<item id="13"[^>]*>([\s\S]*?)<\/item>/);
    assert.ok(portal);
    assert.equal((portal[1].match(/<item /g) || []).length, 3);
    const labels = view.match(/labels: '([^']+)'/)[1].split('|');
    for (const [label, command, title] of [
        [19, 'm_forum', 'форум'], [20, 'm_library', 'библиотека'], [21, 'm_telegram', 'Telegram'],
    ]) {
        assert.ok(portal[1].includes(`label="${label}" command="${command}"`));
        assert.equal(labels[label], title);
        assert.ok(view.includes(`${command}:`));
    }
    assert.ok(view.includes("topMenu.xml?v=7"));
});

test('existing canvas engine opens infoportal left and character right', () => {
    const start = engine.indexOf('canvas.app.topMenu.View.prototype.testSubMenuSides = function()');
    const end = engine.indexOf('canvas.app.topMenu.View.prototype.changeBg', start);
    const context = { canvas: { app: { topMenu: { View: function () {} } } } };
    vm.runInNewContext(engine.slice(start, end), context);
    const rootIds = [...xml.matchAll(/^\t<item id="(\d+)"/gm)].map(match => Number(match[1]));
    const items = rootIds.map(id => ({ id, items: [3, 13].includes(id) ? [] : null }));
    context.canvas.app.topMenu.View.prototype.testSubMenuSides.call({ items });
    assert.equal(items.find(item => item.id === 3).menuSide, true);
    assert.equal(items.find(item => item.id === 13).menuSide, false);
});

test('forum and library open separately without navigating the game frame', () => {
    assert.match(view, /m_forum:\s+function \(\) \{ window\.open\(@json\(route\('forum\.index'\)\), '_blank', 'noopener'\)/);
    assert.match(view, /m_library:\s+function \(\) \{ window\.open\(@json\(route\('library\.index'\)\), '_blank', 'noopener'\)/);
    assert.ok(view.includes('flex-direction: row-reverse'));
});

test('infoportal uses local icons for all three submenu items', () => {
    for (const icon of ['forum', 'library', 'telegram']) {
        assert.ok(xml.includes(`pict="${icon}"`));
        assert.ok(view.includes(`'top/${icon}': '/data/canvas/ui/${icon}.webp'`));
        assert.ok(fs.existsSync(new URL(`../../public/data/canvas/ui/${icon}.webp`, import.meta.url)));
    }
});
