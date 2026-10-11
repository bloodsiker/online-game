import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const script = fs.readFileSync(new URL('../../public/js/settings-tooltips.js', import.meta.url), 'utf8');
function setup() {
    const events = {};
    const attributes = new Map([['aria-describedby', 'existing-help']]);
    const control = {
        getAttribute: key => attributes.get(key) ?? null,
        setAttribute: (key, value) => attributes.set(key, value),
        removeAttribute: key => attributes.delete(key),
    };
    const option = {
        dataset: { settingHint: '<b>Подсказка</b>' },
        querySelector: () => control,
        getBoundingClientRect: () => ({ left: 20, bottom: 50 }),
        contains: el => el === control,
        addEventListener: (name, handler) => { events[name] = handler; },
    };
    const tooltip = { style: {}, offsetWidth: 350, offsetHeight: 60, setAttribute() {} };
    const pageEvents = {};
    const windowEvents = {};
    const formEvents = {};
    vm.runInNewContext(script, {
        document: {
            querySelectorAll: () => [option],
            createElement: () => tooltip,
            body: { appendChild() {} },
            querySelector: () => ({ addEventListener: (name, handler) => { formEvents[name] = handler; } }),
            addEventListener: (name, handler) => { pageEvents[name] = handler; },
        },
        window: { innerWidth: 800, innerHeight: 600, addEventListener: (name, handler) => { windowEvents[name] = handler; } },
    });
    return { events, tooltip, attributes, pageEvents, windowEvents, formEvents, control };
}

test('hover renders hint as plain text and keeps it inside viewport', () => {
    const { events, tooltip } = setup();
    events.mouseenter({ type: 'mouseenter', clientX: 790, clientY: 590 });
    assert.equal(tooltip.hidden, false);
    assert.equal(tooltip.textContent, '<b>Подсказка</b>');
    assert.equal(tooltip.style.left, '430px');
    assert.equal(tooltip.style.top, '520px');
    events.mouseleave();
    assert.equal(tooltip.hidden, true);
});

test('keyboard focus adds description and restores existing accessibility attributes', () => {
    const { events, tooltip, attributes, control } = setup();
    events.focusin({ type: 'focusin' });
    assert.equal(tooltip.hidden, false);
    assert.equal(attributes.get('aria-describedby'), 'existing-help game-settings-tooltip');
    events.focusout({ relatedTarget: control });
    assert.equal(tooltip.hidden, false);
    events.focusout({ relatedTarget: null });
    assert.equal(tooltip.hidden, true);
    assert.equal(attributes.get('aria-describedby'), 'existing-help');
});

test('escape, scroll, navigation and form submission dismiss the hint', () => {
    const state = setup();
    for (const dismiss of [
        () => state.pageEvents.keydown({ key: 'Escape' }),
        state.pageEvents.scroll, state.windowEvents.pagehide, state.formEvents.submit, state.events.click,
    ]) {
        state.events.focusin({ type: 'focusin' });
        dismiss();
        assert.equal(state.tooltip.hidden, true);
    }
});
