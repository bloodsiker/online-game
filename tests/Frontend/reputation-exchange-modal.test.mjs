import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../app/Modules/Structure/ReputationExchange/Presentation/Views/gamble.blade.php', import.meta.url), 'utf8');

function setup() {
    const node = () => ({
        children: [],
        appendChild(child) {
            if (child.parentElement) child.parentElement.children.splice(child.parentElement.children.indexOf(child), 1);
            this.children.push(child);
            child.parentElement = this;
        },
        get firstElementChild() { return this.children[0] || null; },
    });
    const listeners = {};
    const modal = {
        open: false,
        showModal() { this.open = true; },
        close() { this.open = false; listeners.close(); },
        addEventListener(type, listener) { listeners[type] = listener; },
    };
    const holder = node();
    const title = {};
    const tooltip = { style: { display: 'block' } };
    const ids = { 'gamble-modal': modal, 'gamble-modal-options': holder, 'gamble-modal-title': title, artifact_alt: tooltip };
    const originals = [];
    for (const [id, name] of [[1, 'Агат'], [2, 'Пыльца']]) {
        const origin = node();
        const content = { optionId: id, csrf: 'unchanged', disabled: id === 2 };
        origin.appendChild(content);
        ids[`gamble-options-${id}`] = {
            querySelector: () => origin.firstElementChild,
            previousElementSibling: { dataset: { resourceName: name } },
        };
        originals.push({ origin, content });
    }
    const context = { document: { getElementById: id => ids[id] || null } };
    const start = source.indexOf('    const gambleModal =');
    const end = source.indexOf("    @if (session()->has('message'))", start);
    vm.runInNewContext(source.slice(start, end), context);
    return { context, modal, holder, title, tooltip, originals };
}

test('resource click opens the modal with original exchange forms and hides tooltip', () => {
    const state = setup();
    state.context.openGambleOptions(1);
    assert.equal(state.modal.open, true);
    assert.equal(state.title.textContent, 'Агат');
    assert.equal(state.holder.firstElementChild, state.originals[0].content);
    assert.equal(state.holder.firstElementChild.csrf, 'unchanged');
    assert.equal(state.tooltip.style.display, 'none');
});

test('closing restores options and another resource opens without stale options', () => {
    const state = setup();
    state.context.openGambleOptions(1);
    state.context.closeGambleOptions();
    assert.equal(state.modal.open, false);
    assert.equal(state.holder.firstElementChild, null);
    assert.equal(state.originals[0].origin.firstElementChild, state.originals[0].content);
    state.context.openGambleOptions(2);
    assert.equal(state.title.textContent, 'Пыльца');
    assert.equal(state.holder.firstElementChild.disabled, true);
    state.context.closeGambleOptions();
    state.context.openGambleOptions(1);
    assert.equal(state.holder.firstElementChild, state.originals[0].content);
});

test('options no longer expand underneath resource rows', () => {
    assert.ok(!source.includes('toggleGambleOptions'));
    assert.ok(!source.includes('.gamble-options.open'));
    assert.ok(source.includes('.gamble-options { display: none; }'));
    assert.ok(source.includes('aria-labelledby="gamble-modal-title"'));
});
