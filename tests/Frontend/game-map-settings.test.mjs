import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

function setup(visible, mapExists = true, mapOnLeft = false) {
    const blade = fs.readFileSync(new URL('../../app/Modules/Interface/Presentation/Views/index.blade.php', import.meta.url), 'utf8');
    const start = blade.indexOf('let mapVisible =');
    const end = blade.indexOf('</script>', start);
    const script = blade.slice(start, end).replace(/@json\([^\n]*\)/, String(visible));
    const classes = () => {
        const values = new Set();
        return { add: value => values.add(value), remove: value => values.delete(value), contains: value => values.has(value) };
    };
    const wrapper = { classList: classes() };
    const arrow = { classList: classes() };
    const button = { style: {}, closest: () => mapOnLeft ? {} : null };
    const context = vm.createContext({ document: {
        getElementById: id => mapExists ? (id === 'map-wrapper' ? wrapper : arrow) : null,
        querySelector: () => mapExists ? button : null,
        addEventListener: (name, handler) => handler(),
    } });
    vm.runInContext(script, context);
    return { context, wrapper, arrow, button };
}

test('initial map state and temporary toggle work', () => {
    const { context, wrapper, arrow } = setup(false);
    assert.equal(wrapper.classList.contains('hidden'), true);
    context.toggleMap(true);
    assert.equal(wrapper.classList.contains('hidden'), false);
    assert.equal(arrow.classList.contains('open'), true);
    context.toggleMap(false);
    assert.equal(wrapper.classList.contains('hidden'), true);
});

test('gathering temporarily hides map without overwriting saved preference', () => {
    const { context, wrapper } = setup(true);
    context.setMapHiddenForGathering(true);
    context.toggleMap(true);
    assert.equal(wrapper.classList.contains('hidden'), true);
    context.setMapHiddenForGathering(false);
    assert.equal(wrapper.classList.contains('hidden'), false);
    context.toggleMap(false);
    context.setMapHiddenForGathering(true);
    context.setMapHiddenForGathering(false);
    assert.equal(wrapper.classList.contains('hidden'), true);
});

test('manual map toggle remains available', () => {
    const { context, wrapper } = setup(false);
    context.toggleMap();
    assert.equal(wrapper.classList.contains('hidden'), false);
    context.toggleMap(false);
    assert.equal(wrapper.classList.contains('hidden'), true);
});

test('left map uses the right edge for its collapse button', () => {
    const { context, button } = setup(true, true, true);
    assert.equal(button.style.right, '-3px');
    assert.equal(button.style.left, undefined);
    context.toggleMap(false);
    assert.equal(button.style.right, '-8px');
    context.setMapHiddenForGathering(true);
    context.toggleMap(true);
    assert.equal(button.style.right, '-8px');
    context.setMapHiddenForGathering(false);
    assert.equal(button.style.right, '-3px');
});

test('missing sidebar map does not break gathering or map hotkey handlers', () => {
    const { context } = setup(false, false);
    assert.doesNotThrow(() => {
        context.toggleMap(false);
        context.setMapHiddenForGathering(true);
        context.setMapHiddenForGathering(false);
    });
});
