import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import vm from 'node:vm';

const script = fs.readFileSync(new URL('../../public/js/notification-sounds.js', import.meta.url), 'utf8');
function setup(settings = {}, frames = []) {
    const events = {};
    const select = { value: '0', addEventListener: (key, handler) => { events['select:' + key] = handler; } };
    const volume = { value: '50', addEventListener: (key, handler) => { events['volume:' + key] = handler; } };
    const button = { dataset: { soundPreview: 'sound_private' }, addEventListener: (key, handler) => { events[key] = handler; } };
    const audio = { pauses: 0, plays: 0, pause() { this.pauses++; }, play() { this.plays++; return Promise.reject(new Error('autoplay blocked')); } };
    const window = { gameSettings: settings, addEventListener: (key, handler) => { events[key] = handler; } };
    const document = {
            currentScript: { dataset: { soundBase: '/audio/notifications/' } },
            querySelectorAll: selector => selector === 'iframe' ? frames : [button],
            querySelector: selector => selector.startsWith('select') ? select : selector.startsWith('input') ? volume : { addEventListener: (key, handler) => { events['form:' + key] = handler; } },
            addEventListener: (key, handler) => { events['document:' + key] = handler; },
    };
    window.document = document;
    vm.runInNewContext(script, {
        Audio: function () { return audio; }, window, document,
    });
    return { window, audio, button, select, volume, events, document };
}

test('preview uses selected local sound and chosen volume', async () => {
    const { button, select, volume, audio, events } = setup();
    assert.equal(button.disabled, true);
    select.value = '6';
    events['select:change']();
    assert.equal(button.disabled, false);
    volume.value = '75';
    events.click();
    assert.equal(audio.src, '/audio/notifications/notification-6.mp3');
    assert.equal(audio.volume, .75);
    assert.equal(audio.plays, 1);
    await Promise.resolve();
    events.pagehide();
    assert.equal(audio.currentTime, 0);
});

test('notifications respect enabled flag, channel selection and silent volume', () => {
    const { window, audio } = setup({ chat_sound_enabled: false, sound_private: 2, volume: 50 });
    window.playChatNotification('private');
    assert.equal(audio.plays, 0);
    window.gameSettings.chat_sound_enabled = true;
    window.playChatNotification('private');
    assert.equal(audio.plays, 1);
    assert.equal(audio.src, '/audio/notifications/notification-2.mp3');
    window.playChatNotification('clan');
    window.playChatNotification('invalid');
    window.gameSettings.volume = 0;
    window.playChatNotification('private');
    assert.equal(audio.plays, 1);
});

test('all six files contain actual MPEG audio', () => {
    for (let id = 1; id <= 6; id++) {
        const data = fs.readFileSync(new URL('../../public/audio/notifications/notification-' + id + '.mp3', import.meta.url));
        assert.ok(data.length > 10000);
        assert.ok(data.subarray(0, 3).toString() === 'ID3' || (data[0] === 255 && (data[1] & 224) === 224));
    }
});

test('every enabled game event uses master volume and disabled events remain silent', () => {
    const { window, audio } = setup({ volume: 25, sound_battle: true, sound_dungeon: true, sound_mail: true, sound_gathering: true });
    ['battle', 'dungeon', 'mail', 'gathering'].forEach((kind, index) => {
        window.GameAudio.event(kind);
        assert.equal(audio.src, '/audio/notifications/notification-' + (index + 1) + '.mp3');
        assert.equal(audio.volume, .25);
    });
    window.gameSettings.volume = 0;
    window.GameAudio.event('gathering');
    window.gameSettings.volume = 100;
    window.gameSettings.sound_battle = false;
    window.GameAudio.event('battle');
    window.GameAudio.event('unknown');
    assert.equal(audio.plays, 4);
});

test('events play their chosen sound rather than the old fixed sound', () => {
    const { window, audio } = setup({ volume: 35, sound_battle: 6, sound_dungeon: 5, sound_mail: 2, sound_gathering: 3 });
    for (const [kind, id] of [['battle', 6], ['dungeon', 5], ['mail', 2], ['gathering', 3]]) {
        window.GameAudio.event(kind);
        assert.equal(audio.src, '/audio/notifications/notification-' + id + '.mp3');
        assert.equal(audio.volume, .35);
    }
    window.gameSettings.sound_gathering = 0;
    window.GameAudio.event('gathering');
    assert.equal(audio.plays, 4);
});

test('chat websocket and polling delivery are deduplicated and ignore own/ignored/unaddressed messages', () => {
    const { window, audio, document } = setup({ volume: 60, chat_sound_enabled: true, sound_private: 6, sound_mention: 2, sound_clan: 3, sound_trade: 4 });
    const channels = [];
    const echo = { private: name => { channels.push(name); return { listen() { return this; }, stopListening() {} }; } };
    window.GameAudio.watchChat(echo, { userId: 1, clanId: 5 }, [9]);
    window.GameAudio.watchChat(echo, { userId: 1, clanId: 5 }, [9]);
    assert.deepEqual(channels, ['App.Models.User.1', 'chat.trade', 'chat.clan.5']);
    const msg = { message_id: 1, sender_user_id: 2, target_user_id: 1, type: 'private', channel: 'private' };
    window.GameAudio.message(msg);
    window.GameAudio.message(msg);
    assert.equal(audio.plays, 1);
    assert.equal(audio.src, '/audio/notifications/notification-6.mp3');
    window.GameAudio.message({ ...msg, message_id: 2, sender_user_id: 1 });
    window.GameAudio.message({ ...msg, message_id: 3, sender_user_id: 9 });
    window.GameAudio.message({ ...msg, message_id: 4, type: 'mention', target_user_id: 3 });
    assert.equal(audio.plays, 1);
    window.gameSettings.mute_open_chat = true;
    document.activeElement = { id: 'chat-frame' };
    window.GameAudio.message({ ...msg, message_id: 5 });
    assert.equal(audio.plays, 1);
    document.activeElement = { id: 'game-frame' };
    window.GameAudio.message({ ...msg, message_id: 6, type: 'message', channel: 'clan', target_user_id: null });
    assert.equal(audio.plays, 2);
});

test('to[Admin] sounds for its recipient over websocket and polling, once per message', () => {
    const { window, audio } = setup({ volume: 50, chat_sound_enabled: true, sound_mention: 5 });
    const echo = { private: () => ({ listen() { return this; }, stopListening() {} }) };
    window.GameAudio.watchChat(echo, { userId: 11, userName: 'Admin' }, []);
    window.GameAudio.message({ message_id: 1, sender_user_id: 7, target_user_id: 11, type: 'mention', channel: 'main' });
    window.GameAudio.message({ message_id: 1, sender_user_id: 7, target_name: 'Admin', type: 'mention', channel: 'main' });
    assert.equal(audio.plays, 1);
    assert.equal(audio.src, '/audio/notifications/notification-5.mp3');
    window.GameAudio.message({ message_id: 2, sender_user_id: 7, target_name: ' admin ', type: 'mention', channel: 'main' });
    assert.equal(audio.plays, 2);
    window.GameAudio.message({ message_id: 3, sender_user_id: 7, target_name: 'Other', type: 'mention', channel: 'main' });
    assert.equal(audio.plays, 2);
});

test('battle and dungeon sounds do not repeat on attacks and moves within the same session', () => {
    const childDoc = { body: { dataset: { soundContext: 'battle', soundBattle: '42' } }, addEventListener() {}, querySelectorAll: () => [] };
    let load;
    const frame = { contentWindow: { document: childDoc }, addEventListener: (name, handler) => { load = handler; } };
    const { audio } = setup({ volume: 50, sound_battle: true, sound_dungeon: true }, [frame]);
    assert.equal(audio.plays, 1);
    load(); load();
    assert.equal(audio.plays, 1);
    childDoc.body.dataset = { soundContext: 'location', soundBattle: '', soundDungeon: '8' };
    load(); load();
    assert.equal(audio.plays, 2);
    childDoc.body.dataset.soundDungeon = '';
    load();
    childDoc.body.dataset.soundDungeon = '9';
    load();
    assert.equal(audio.plays, 3);
});
