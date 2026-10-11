(() => {
    'use strict';

    const base = document.currentScript.dataset.soundBase;
    const audio = new Audio();
    audio.preload = 'none';
    const eventSounds = { battle: 1, dungeon: 2, mail: 3, gathering: 4 };
    let playbackVersion = 0;
    let unlocked = false;
    let activeBattle = '';
    let activeDungeon = '';
    const boundWindows = new WeakSet();
    const boundFrames = new WeakSet();
    const heardMessages = new Set();
    const chatSubscriptions = new Map();
    let chatViewer = { userId: 0, ignored: [] };

    function stop() {
        playbackVersion++;
        audio.pause();
        try { audio.currentTime = 0; } catch (error) {}
    }

    function play(id, volume) {
        stop();
        const sound = Number(id);
        const loudness = Number(volume);
        if (!Number.isInteger(sound) || sound < 1 || sound > 6 || !Number.isFinite(loudness) || loudness <= 0) return;
        audio.src = base + 'notification-' + sound + '.mp3';
        audio.muted = false;
        audio.volume = Math.min(100, loudness) / 100;
        audio.play()?.catch(() => {}); // Browsers may require a user gesture before playback.
    }

    function chat(kind) {
        const settings = window.gameSettings;
        if (!settings?.chat_sound_enabled) return;
        if (!['mention', 'private', 'clan', 'trade'].includes(kind)) return;
        if (settings.mute_open_chat && document.activeElement?.id === 'chat-frame') return;
        play(settings['sound_' + kind], settings.volume);
    }

    function event(kind) {
        if (!Object.hasOwn(eventSounds, kind) || !window.gameSettings?.['sound_' + kind]) return;
        const selected = window.gameSettings['sound_' + kind];
        play(selected === true ? eventSounds[kind] : selected, window.gameSettings.volume);
    }

    function message(data) {
        const id = Number(data?.message_id);
        if (!id || !data.type || heardMessages.has(id)) return;
        heardMessages.add(id);
        if (heardMessages.size > 500) heardMessages.delete(heardMessages.values().next().value);
        if (!data.sender_user_id || Number(data.sender_user_id) === chatViewer.userId || chatViewer.ignored.includes(Number(data.sender_user_id))) return;
        const addressed = Number(data.target_user_id) === chatViewer.userId
            || (data.type === 'mention' && !data.target_user_id && chatViewer.userName
                && String(data.target_name || '').trim().toLocaleLowerCase() === chatViewer.userName.toLocaleLowerCase());
        const kind = data.type === 'private' && addressed ? 'private'
            : data.type === 'mention' && addressed ? 'mention'
            : data.type === 'message' && ['clan', 'trade'].includes(data.channel) ? data.channel : null;
        if (kind) chat(kind);
    }

    function watchChat(echo, viewer, ignored) {
        chatViewer = { userId: Number(viewer.userId), userName: String(viewer.userName || ''), ignored: ignored || [] };
        const settings = window.gameSettings || {};
        const channels = [];
        if (settings.chat_sound_enabled) {
            if (settings.sound_private || settings.sound_mention) channels.push('App.Models.User.' + viewer.userId);
            if (settings.sound_trade) channels.push('chat.trade');
            if (viewer.clanId && settings.sound_clan) channels.push('chat.clan.' + viewer.clanId);
        }
        for (const [name, subscription] of chatSubscriptions) {
            if (!channels.includes(name)) {
                subscription.stopListening('.chat.message.created', message);
                chatSubscriptions.delete(name);
            }
        }
        for (const name of channels) {
            if (!chatSubscriptions.has(name)) chatSubscriptions.set(name, echo.private(name).listen('.chat.message.created', message));
        }
    }

    // Unlock the shared audio element on gestures in any same-origin game frame (Safari included).
    function unlock() {
        if (unlocked || !window.gameSettings || Number(window.gameSettings.volume) <= 0) return;
        const version = ++playbackVersion;
        audio.src = base + 'notification-1.mp3';
        audio.muted = true;
        audio.play()?.then(() => {
            unlocked = true;
            if (version === playbackVersion) { stop(); audio.muted = false; }
        }).catch(() => { if (version === playbackVersion) audio.muted = false; });
    }

    function inspectContext(doc) {
        const context = doc.body?.dataset;
        if (!context?.soundContext) return;
        const battle = context.soundBattle || '';
        if (battle && battle !== activeBattle) event('battle');
        activeBattle = battle;
        if (context.soundContext === 'location') {
            const dungeon = context.soundDungeon || '';
            if (dungeon && dungeon !== activeDungeon) event('dungeon');
            activeDungeon = dungeon;
        }
    }

    function bindFrames(win) {
        try {
            const doc = win.document;
            if (!boundWindows.has(doc)) {
                boundWindows.add(doc);
                doc.addEventListener('pointerdown', unlock, true);
                doc.addEventListener('keydown', unlock, true);
            }
            inspectContext(doc);
            doc.querySelectorAll('iframe').forEach(frame => {
                if (!boundFrames.has(frame)) {
                    boundFrames.add(frame);
                    frame.addEventListener('load', () => bindFrames(frame.contentWindow));
                }
                bindFrames(frame.contentWindow);
            });
        } catch (error) { /* Cross-origin or still-loading frames are not game audio sources. */ }
    }

    window.GameAudio = { chat, event, message, watchChat, stop };
    window.playChatNotification = chat;
    bindFrames(window);

    document.querySelectorAll('[data-sound-preview]').forEach(button => {
        const select = document.querySelector('select[name="' + button.dataset.soundPreview + '"]');
        const volume = document.querySelector('input[name="volume"]');
        if (!select || !volume) return;
        const update = () => { button.disabled = Number(select.value) === 0; };
        select.addEventListener('change', () => { stop(); update(); });
        button.addEventListener('click', () => play(select.value, volume.value));
        volume.addEventListener('input', () => { audio.volume = Math.max(0, Math.min(100, Number(volume.value))) / 100; });
        update();
    });
    window.addEventListener('pagehide', stop);
    document.querySelector('.game-settings')?.addEventListener('submit', stop);
})();
