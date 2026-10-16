import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import test from 'node:test';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/js/change-password-modal.js', import.meta.url), 'utf8');

function setup(translations = {}) {
    function element() {
        return {
            value: '', hidden: true, disabled: false, validityMessage: '', attributes: {}, handlers: {},
            addEventListener(name, fn) { this.handlers[name] = fn; },
            setCustomValidity(message) { this.validityMessage = message; },
            setAttribute(name, value) { this.attributes[name] = value; },
            focus() {},
            classList: { toggle() {} },
        };
    }
    const ids = Object.fromEntries([
        'change-password-open', 'password-modal', 'password-overlay', 'change-password-form',
        'password-message', 'password-save', 'password-cancel', 'password-modal-close', 'password-confirmation-error',
    ].map(id => [id, element()]));
    const form = ids['change-password-form'];
    form.dataset = { translations: JSON.stringify(translations) };
    form.elements = { current_password: element(), password: element(), password_confirmation: element() };
    form.reset = () => Object.values(form.elements).forEach(input => { input.value = ''; });
    vm.runInNewContext(source, { document: { getElementById: id => ids[id], addEventListener() {} } });
    return {
        ids, form,
        type(name, value) { form.elements[name].value = value; form.elements[name].handlers.input(); },
    };
}

test('mismatch is displayed immediately and blocks save', () => {
    const { ids, form, type } = setup();
    type('password', 'new-password');
    type('password_confirmation', 'other-password');
    assert.equal(ids['password-confirmation-error'].hidden, false);
    assert.equal(ids['password-save'].disabled, true);
    assert.equal(form.elements.password_confirmation.attributes['aria-invalid'], 'true');
    assert.equal(form.elements.password_confirmation.validityMessage, 'Пароли не совпадают.');
});

test('confirmation validation uses the selected language supplied by the server', () => {
    const { form, type } = setup({ password_mismatch: 'Паролі не збігаються.' });
    type('password', 'new-password');
    type('password_confirmation', 'other-password');
    assert.equal(form.elements.password_confirmation.validityMessage, 'Паролі не збігаються.');
});

test('matching confirmation clears the error and unlocks save', () => {
    const { ids, form, type } = setup();
    type('password', 'new-password');
    type('password_confirmation', 'other-password');
    type('password_confirmation', 'new-password');
    assert.equal(ids['password-confirmation-error'].hidden, true);
    assert.equal(ids['password-save'].disabled, false);
    assert.equal(form.elements.password_confirmation.validityMessage, '');
});

test('editing the new password revalidates an existing confirmation', () => {
    const { ids, type } = setup();
    type('password', 'new-password');
    type('password_confirmation', 'new-password');
    type('password', 'changed-password');
    assert.equal(ids['password-confirmation-error'].hidden, false);
});

test('empty confirmation does not show mismatch before typing', () => {
    const { ids, type } = setup();
    type('password', 'new-password');
    assert.equal(ids['password-confirmation-error'].hidden, true);
});

test('reopening the modal resets passwords and validation', () => {
    const { ids, form, type } = setup();
    type('password', 'new-password');
    type('password_confirmation', 'other-password');
    ids['change-password-open'].handlers.click();
    assert.equal(form.elements.password_confirmation.value, '');
    assert.equal(form.elements.password_confirmation.validityMessage, '');
    assert.equal(ids['password-confirmation-error'].hidden, true);
    assert.equal(ids['password-save'].disabled, false);
});
