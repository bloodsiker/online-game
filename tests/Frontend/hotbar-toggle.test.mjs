import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const view = readFileSync(new URL('../../app/Modules/Interface/Presentation/Views/index.blade.php', import.meta.url), 'utf8');
const css = readFileSync(new URL('../../public/css/main.css', import.meta.url), 'utf8');
const handler = view.match(/toggleButton\.addEventListener\('click', \(e\) => \{[\s\S]*?localStorage\.setItem\('hotbar-collapsed', isCollapsed\);\s*\}\);/)?.[0];

function setup({ left = 300, top = 220, collapsed = false } = {}) {
    let click;
    const stored = new Map();
    const bodyContent = {
        clientLeft: 2, clientTop: 2, clientWidth: 1000,
        getBoundingClientRect: () => ({ left: 100, top: 50 }),
    };
    const hotbar = {
        style: {},
        classList: {
            add: () => { collapsed = true; },
            remove: () => { collapsed = false; },
        },
        getBoundingClientRect() {
            const width = collapsed ? 39 : 458;
            const x = Number.parseFloat(this.style.left ?? left) + 102;
            const y = Number.parseFloat(this.style.top ?? top) + 52;
            return { left: x, right: x + width, top: y, width, height: 58 };
        },
    };
    vm.runInNewContext(handler, {
        isCollapsed: collapsed, hotbar, bodyContent, toggleIcon: {},
        toggleButton: { addEventListener: (_, callback) => { click = callback; } },
        localStorage: { setItem: (key, value) => stored.set(key, String(value)) },
    });
    return { hotbar, stored, click: () => click({ stopPropagation() {} }) };
}

test('toggle keeps the right edge and top stable across repeated clicks', () => {
    const { hotbar, stored, click } = setup();
    const before = hotbar.getBoundingClientRect();
    for (let i = 0; i < 40; i++) {
        click();
        const after = hotbar.getBoundingClientRect();
        assert.equal(after.right, before.right);
        assert.equal(after.top, before.top);
        assert.equal(hotbar.style.transform, 'none');
    }
    assert.equal(stored.get('hotbar-collapsed'), 'false');
    assert.deepEqual(JSON.parse(stored.get('hotbar-position')), { left: '300px', top: '220px' });
});

test('expanding a collapsed panel near the left edge stays within its frame', () => {
    const { hotbar, click } = setup({ left: 0, collapsed: true });
    click();
    assert.equal(hotbar.style.left, '0px');
});

test('collapse retains panel height and centers the icon without layout transitions', () => {
    const container = css.match(/\.hotbar-container \{([^}]+)\}/)[1];
    const toggle = css.match(/\.hotbar-toggle \{([^}]+)\}/)[1];
    assert.match(container, /min-height: 44px/);
    assert.match(toggle, /transition: background-color 0\.2s, color 0\.2s/);
    const collapsedToggle = css.match(/\.hotbar-container\.collapsed \.hotbar-toggle \{([^}]+)\}/)[1];
    assert.match(collapsedToggle, /right: 50%/);
    assert.match(collapsedToggle, /transform: translate\(50%, -50%\)/);
    assert.match(collapsedToggle, /width: 25px/);
    assert.match(collapsedToggle, /height: 25px/);
    assert.doesNotMatch(collapsedToggle, /position: relative|transition:/);
    assert.doesNotMatch(css.match(/\.hotbar-toggle:hover \{([^}]+)\}/)[1], /width:/);
});
