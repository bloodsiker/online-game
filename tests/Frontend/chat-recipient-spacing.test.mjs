import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const source = fs.readFileSync(new URL('../../app/Modules/Chat/Presentation/Views/chat.blade.php', import.meta.url), 'utf8');

test('recipient names have a space after the arrow, not a margin before it', () => {
    assert.ok(!source.includes('margin-left: 8px'));
    assert.ok(source.includes('<span class="chat-private-recipient">» @if($msg->target_user_id'));
    const renderer = fs.readFileSync(new URL('../../app/Modules/Chat/Domain/Services/MessageRenderer.php', import.meta.url), 'utf8');
    assert.ok(renderer.includes('<span class="chat-to">» <a'));
});

test('realtime private messages wrap the arrow and second nickname together', () => {
    const start = source.indexOf('    function buildMessageHtml(');
    const end = source.indexOf('    function handlePartyInviteAction(', start);
    assert.ok(start >= 0 && end > start);
    const context = {
        escapeHtml: value => String(value), safeAttr: value => String(value),
        clanIconHtml: () => '', levelHtml: () => '', playerInfoIconHtml: () => '',
    };
    vm.runInNewContext(source.slice(start, end), context);
    const html = context.buildMessageHtml({ type: 'private', time: '12:22', sender_name: 'Admin', sender_id: 1, target_name: 'BlooDSikeR', content: 'хай' });
    assert.ok(html.includes('<span class="chat-private-recipient">» <span class="prv-name">BlooDSikeR</span></span>'));
});

test('both private nicknames are clickable and use the same player-click handler as public chat', () => {
    const start = source.indexOf('    function buildMessageHtml(');
    const end = source.indexOf('    function handlePartyInviteAction(', start);
    const context = {
        escapeHtml: value => String(value), safeAttr: value => String(value),
        clanIconHtml: () => '', levelHtml: () => '', playerInfoIconHtml: () => '',
    };
    vm.runInNewContext(source.slice(start, end), context);
    const html = context.buildMessageHtml({ type: 'private', time: '12:22', sender_name: 'Admin', sender_id: 1, target_name: 'BlooDSikeR', target_user_id: 2, content: 'хай' });
    assert.equal((html.match(/class="player-link n prv-name"/g) || []).length, 2);
    assert.equal((html.match(/chatPlayerClick\(Number\(this.dataset.uid\), this.dataset.name\)/g) || []).length, 2);
    assert.ok(html.includes('data-uid="1" data-name="Admin"'));
    assert.ok(html.includes('data-uid="2" data-name="BlooDSikeR"'));
    assert.ok(html.includes('<span class="chat-private-recipient">» <a'));
});
