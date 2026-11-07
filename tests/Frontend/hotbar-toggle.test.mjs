import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const view = readFileSync(new URL('../../app/Modules/Interface/Presentation/Views/index.blade.php', import.meta.url), 'utf8');
const css = readFileSync(new URL('../../public/css/main.css', import.meta.url), 'utf8');
const start = view.indexOf('// Позиция панели задаётся относительно всего окна');
const script = view.slice(start, view.indexOf('</script>', start));
assert.ok(start >= 0);

function setup({ left = 300, top = 220, collapsed = false, saved, blockedStorage = false } = {}) {
    const events = new Map(), captured = new Set(), stored = new Map();
    const listen = key => (name, callback) => events.set(key + ':' + name, callback);
    const element = key => ({ addEventListener: listen(key), style: {}, classList: {
        values: new Set(),
        add(name) { this.values.add(name); },
        remove(name) { this.values.delete(name); },
        toggle(name, active) { active ? this.add(name) : this.remove(name); },
        contains(name) { return this.values.has(name); },
    } });
    stored.set('hotbar-collapsed', String(collapsed));
    stored.set('hotbar-position', saved === undefined ? JSON.stringify({ left: left + 'px', top: top + 'px', coordinateSpace: 'viewport' }) : saved);
    const bodyContent = {
        clientLeft: 2, clientTop: 2,
        getBoundingClientRect: () => ({ left: 100, top: 50, width: 1000, height: 500, bottom: 550 }),
    };
    const hotbar = element('hotbar');
    hotbar.getBoundingClientRect = () => {
        const width = hotbar.classList.contains('collapsed') ? 50 : 458;
        const x = Number.parseFloat(hotbar.style.left || 0), y = Number.parseFloat(hotbar.style.top || 0);
        return { left: x, right: x + width, top: y, bottom: y + 58, width, height: 58 };
    };
    const dragHandle = element('handle'), toggleButton = element('toggle'), dragOverlay = element('overlay');
    dragHandle.setPointerCapture = pointer => captured.add(pointer);
    dragHandle.hasPointerCapture = pointer => captured.has(pointer);
    dragHandle.releasePointerCapture = pointer => captured.delete(pointer);
    const window = { innerWidth: 1440, innerHeight: 900, addEventListener: listen('window') };
    const document = { addEventListener: listen('document'), body: { append: (...nodes) => { for (const node of nodes) node.parentNode = document.body; } } };
    let onResize;
    vm.runInNewContext(script, {
        hotbar, dragHandle, toggleButton, dragOverlay, bodyContent, toggleIcon: {}, window, document,
        localStorage: {
            getItem(key) { if (blockedStorage) throw Error('Storage blocked'); return stored.get(key) ?? null; },
            setItem(key, value) { if (blockedStorage) throw Error('Storage blocked'); stored.set(key, String(value)); },
        },
        ResizeObserver: class { constructor(callback) { onResize = callback; } observe() {} },
    });
    const fire = (key, data = {}) => events.get(key)?.({ preventDefault() {}, stopPropagation() {}, ...data });
    fire('document:DOMContentLoaded');
    return { hotbar, stored, captured, window, document, dragOverlay, fire, resize: () => onResize() };
}

test('collapse keeps viewport right edge and top stable across repeated clicks', () => {
    const { hotbar, stored, fire } = setup();
    const before = hotbar.getBoundingClientRect();
    for (let i = 0; i < 40; i++) {
        fire('toggle:click');
        assert.equal(hotbar.getBoundingClientRect().right, before.right);
        assert.equal(hotbar.getBoundingClientRect().top, before.top);
    }
    assert.equal(stored.get('hotbar-collapsed'), 'false');
    assert.deepEqual(JSON.parse(stored.get('hotbar-position')), { left: '300px', top: '220px', coordinateSpace: 'viewport' });
});

test('dragging crosses the game boundaries and covers the character, map and chat frames', () => {
    const { hotbar, fire, captured, dragOverlay, document, stored } = setup({ collapsed: true });
    assert.equal(hotbar.parentNode, document.body);
    assert.equal(dragOverlay.parentNode, document.body);
    const rect = hotbar.getBoundingClientRect();
    fire('handle:pointerdown', { button: 0, pointerId: 7, clientX: rect.left - 10, clientY: rect.top + 20 });
    assert.ok(captured.has(7));
    assert.ok(dragOverlay.classList.contains('active'));
    fire('document:pointermove', { pointerId: 8, clientX: 900, clientY: 200 });
    assert.equal(hotbar.getBoundingClientRect().left, rect.left);
    fire('document:pointermove', { pointerId: 7, clientX: 20, clientY: 760 });
    assert.ok(hotbar.getBoundingClientRect().left < 100);
    assert.ok(hotbar.getBoundingClientRect().top > 550);
    fire('document:pointermove', { pointerId: 7, clientX: 1300, clientY: 200 });
    assert.ok(hotbar.getBoundingClientRect().left > 1100);
    fire('document:pointerup', { pointerId: 7 });
    assert.equal(captured.size, 0);
    assert.equal(dragOverlay.classList.contains('active'), false);
    assert.equal(JSON.parse(stored.get('hotbar-position')).coordinateSpace, 'viewport');
});

test('viewport bounds, expanding at the edge and resize leave the drag handle accessible', () => {
    const { hotbar, window, fire } = setup({ left: 18, collapsed: true });
    fire('toggle:click');
    assert.ok(hotbar.getBoundingClientRect().left >= 15);
    window.innerWidth = 800; window.innerHeight = 400;
    fire('window:resize');
    const rect = hotbar.getBoundingClientRect();
    assert.ok(rect.right <= window.innerWidth - 15);
    assert.ok(rect.bottom <= window.innerHeight);
});

test('legacy positions are migrated from game coordinates and invalid storage has a safe default', () => {
    const { hotbar, stored } = setup({ saved: '{"left":"300px","top":"220px"}' });
    assert.equal(hotbar.style.left, '402px');
    assert.equal(hotbar.style.top, '272px');
    assert.equal(JSON.parse(stored.get('hotbar-position')).coordinateSpace, 'viewport');
    for (const options of [{ saved: '{broken' }, { saved: null }, { blockedStorage: true }]) {
        const fallback = setup(options).hotbar.getBoundingClientRect();
        assert.equal(fallback.left, 100 + (1000 - 458) / 2);
        assert.equal(fallback.bottom, 550);
    }
});

test('losing focus or cancelling the pointer ends dragging and unblocks every frame', () => {
    for (const event of ['window:blur', 'document:pointercancel', 'handle:lostpointercapture']) {
        const { hotbar, dragOverlay, fire, captured } = setup();
        fire('handle:pointerdown', { button: 0, pointerId: 7, clientX: 290, clientY: 240 });
        fire(event, event === 'window:blur' ? {} : { pointerId: 7 });
        assert.equal(captured.size, 0);
        assert.equal(dragOverlay.classList.contains('active'), false);
        assert.equal(hotbar.classList.contains('dragging'), false);
    }
});

test('panel uses fixed positioning and stays above the iframe overlay only while dragging', () => {
    assert.match(css.match(/\.hotbar-container \{([^}]+)\}/)[1], /position: fixed/);
    assert.match(css.match(/\.hotbar-container\.dragging \{([^}]+)\}/)[1], /z-index: 9999/);
    assert.match(css.match(/\.hotbar-drag-handle \{([^}]+)\}/)[1], /touch-action: none/);
    const toggle = css.match(/\.hotbar-container\.collapsed \.hotbar-toggle \{([^}]+)\}/)[1];
    assert.match(toggle, /right: 50%/);
    assert.match(toggle, /transform: translate\(50%, -50%\)/);
    assert.doesNotMatch(toggle, /position: relative|transition:/);
    assert.doesNotMatch(css.match(/\.hotbar-toggle:hover \{([^}]+)\}/)[1], /width:/);
});
