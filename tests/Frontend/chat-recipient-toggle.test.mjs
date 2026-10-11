import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../app/Modules/Chat/Presentation/Views/chat-action.blade.php', import.meta.url), 'utf8');
const start = source.indexOf('    function setChatRecipient(');
const end = source.indexOf('    // ---- Receive messages', start);
assert.ok(start >= 0 && end > start);
function setup(value = '') {
    const input = { value, focus() {}, setSelectionRange(start, end) { this.selection = [start, end]; } };
    const context = { gebi: () => input };
    vm.runInNewContext(source.slice(start, end), context);
    return { input, set: context.setChatRecipient };
}

test('clicks alternate public/private prefixes without losing the message', () => {
    const { input, set } = setup('Привет! [[item_1]]');
    set('BlooDSikeR');
    assert.equal(input.value, 'to[BlooDSikeR] Привет! [[item_1]]');
    set('BlooDSikeR');
    assert.equal(input.value, 'prv[BlooDSikeR] Привет! [[item_1]]');
    set('BlooDSikeR');
    assert.equal(input.value, 'to[BlooDSikeR] Привет! [[item_1]]');
    assert.deepEqual(input.selection, [input.value.length, input.value.length]);
});

test('switching recipient replaces the prefix and preserves text and dash separator', () => {
    const { input, set } = setup('prv[Admin] - Сообщение');
    set('Other');
    assert.equal(input.value, 'to[Other] - Сообщение');
    set('other');
    assert.equal(input.value, 'prv[other] - Сообщение');
});

test('explicit private action remains private and does not erase message', () => {
    const { input, set } = setup('to[Admin] Текст');
    set('Admin', 'prv');
    set('Admin', 'prv');
    assert.equal(input.value, 'prv[Admin] Текст');
    set('');
    assert.equal(input.value, 'prv[Admin] Текст');
});
