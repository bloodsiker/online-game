import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../public/js/game-shortcuts.js', import.meta.url), 'utf8');

function setup() {
    function doc() {
        return {
            documentElement: { dataset: {} }, handlers: {}, frames: [],
            addEventListener(type, handler) { this.handlers[type] = handler; },
            querySelectorAll() { return this.frames; },
            getElementById() { return null; },
        };
    }
    const main = doc();
    const game = doc();
    const chat = doc();
    const frame = contentDocument => ({ contentDocument, addEventListener() {} });
    const gameFrame = frame(game);
    main.frames = [gameFrame, frame(chat)];
    main.getElementById = () => gameFrame;
    const calls = [];
    const navigations = [];
    const window = {};
    vm.runInNewContext(source, { window, document: main });
    window.GameShortcuts.init({
        frameId: 'game-frame', routes: { backpack: '/backpack' },
        navigate: url => navigations.push(url),
        useHotbarSlot: slot => { calls.push(slot); return true; },
    });
    function press(document = main, overrides = {}) {
        const event = {
            key: '1', code: 'Digit1', target: { closest: () => null },
            prevented: false, stopped: false,
            preventDefault() { this.prevented = true; },
            stopImmediatePropagation() { this.stopped = true; },
            ...overrides,
        };
        document.handlers.keydown(event);
        return event;
    }
    return { main, game, chat, calls, navigations, press };
}

test('digits 1–9 select matching slots and zero selects the tenth slot in every game frame', () => {
    const ui = setup();
    for (const document of [ui.main, ui.game, ui.chat]) {
        for (const digit of '1234567890') {
            const event = ui.press(document, { key: digit, code: 'Digit' + digit });
            assert.equal(event.prevented, true);
            assert.equal(event.stopped, true);
        }
    }
    assert.deepEqual(ui.calls, Array(3).fill([1, 2, 3, 4, 5, 6, 7, 8, 9, 10]).flat());
});

test('numpad digits work with Num Lock and do not activate slots with Num Lock off', () => {
    const ui = setup();
    ui.press(ui.game, { code: 'Numpad7', key: '7' });
    ui.press(ui.game, { code: 'Numpad1', key: 'End' });
    assert.deepEqual(ui.calls, [7]);
});

test('form controls and their children, including editable regions, never activate hotbar slots', () => {
    const ui = setup();
    for (const tag of ['form', 'input', 'textarea', 'select', 'button', 'contenteditable', 'textbox', 'combobox']) {
        const event = ui.press(ui.chat, { target: { closest: selector => {
            assert.ok(selector.includes(tag));
            return {};
        } } });
        assert.equal(event.prevented, false);
    }
    ui.press(ui.game, { target: { isContentEditable: true } });
    assert.deepEqual(ui.calls, []);
});

test('modifiers, held keys, composition and previously handled events do not use items', () => {
    const ui = setup();
    for (const flag of ['ctrlKey', 'metaKey', 'altKey', 'shiftKey', 'repeat', 'isComposing', 'defaultPrevented']) {
        const event = ui.press(ui.game, { [flag]: true });
        assert.equal(event.prevented, false);
    }
    assert.deepEqual(ui.calls, []);
});

test('existing navigation shortcuts continue to work outside forms', () => {
    const ui = setup();
    ui.press(ui.game, { key: 'i', code: 'KeyI' });
    assert.deepEqual(ui.navigations, ['/backpack']);
    assert.deepEqual(ui.calls, []);
});
