<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Чат</title>
    <style>
        html {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        html::-webkit-scrollbar { display: none; }
        body {
            margin: 0;
            background-color: #ffe4aa;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        body::-webkit-scrollbar { display: none; }
        .chat-area {
            width: 100%;
            min-height: 100vh;
            padding: 5px;
            box-sizing: border-box;
        }
        .message {
            margin-bottom: 0px;
            line-height: 1.4;
            word-break: break-word;
        }
        small { font-size: 11px; color: #666; }
        a { color: navy; text-decoration: none; }
        a:hover { text-decoration: underline; }
        a.n { text-decoration: none; }

        /* Message type styles */
        .msg-message   { border-left: 2px solid transparent; padding-left: 3px; }
        .msg-private   { border-left: 2px solid #cc6600; padding-left: 3px; }
        .msg-system    { border-left: 2px solid #006600; padding-left: 3px; }
        .msg-mention   { border-left: 2px solid #cc9900; padding-left: 3px; }

        .msg-system            { border-left: 2px solid #cc00ff; padding-left: 3px; color: #cc00ff; font-weight: bold; }
        .msg-system small      { color: #cc00ff; }
        .msg-system-icon       { font-weight: bold; }
        .msg-information       { border-left: 2px solid #df5d03; padding-left: 3px; color: #df5d03; font-weight: bold; }
        .msg-information small { color: #df5d03; }
        .msg-information-icon  { font-weight: bold; }
        .msg-world_event       { border-left: 2px solid #000; padding-left: 3px; color: #000; font-weight: bold; }
        .msg-world_event small { color: #000; }
        .msg-world_event em    { font-style: italic; }
        .msg-party_invite      { border-left: 2px solid #000; padding-left: 3px; color: #000; font-style: italic; }
        .msg-party_invite small { color: #000; }
        .msg-party_invite .party-invite-action { font-weight: bold; }
        .msg-party_notice      { border-left: 2px solid #000; padding-left: 3px; color: #000; font-style: italic; }
        .msg-party_notice small { color: #000; }
        .msg-quest             { border-left: 2px solid #000000; padding-left: 3px; color: #000000; font-style: italic; }
        .msg-quest small       { color: #000000; font-style: normal; }
        .msg-quest_item            { border-left: 2px solid #009900; padding-left: 3px; color: #009900; font-weight: bold; }
        .msg-quest_item small      { color: #009900; }
        .msg-quest_item-icon       { font-weight: bold; }
        .msg-loot                  { border-left: 2px solid #000; padding-left: 3px; color: #000; font-style: italic; }
        .msg-loot small            { color: #000; }
        .prv-name         { color: #ff0000; font-weight: bold; }
        .msg-time-reply   { cursor: pointer; text-decoration: underline dotted #999; color: #ff0000; }
        .msg-time-reply:hover { opacity: 0.75; }
        .chat-to          { color: #996600; font-weight: bold; }
        .chat-item        { color: #006699; font-weight: bold; cursor: pointer; text-decoration: underline; }
        .chat-item:hover  { text-decoration: underline; }
        .chat-item-unknown{ color: #999; }
        .chat-user        { color: inherit; text-decoration: underline; }
        .chat-user:hover  { color: #990000; }
        .player-link      { color: #990000; font-weight: bold; }
        .player-link:hover { text-decoration: underline; }
        .chat-clan-icon   { vertical-align: middle; margin-right: 3px; }
        .chat-level       { color: #666; font-weight: normal; }

        .world-event-widget {
            position: fixed;
            z-index: 100;
            top: 6px;
            right: 20px;
            width: 270px;
            max-height: calc(100vh - 20px);
            box-sizing: border-box;
            overflow-y: auto;
            padding: 7px 9px;
            border: 1px solid rgba(255, 255, 255, .28);
            border-radius: 4px;
            background: rgba(0, 0, 0, .76);
            box-shadow: 0 2px 8px rgba(0, 0, 0, .45);
            color: #fff;
            scrollbar-width: none;
        }
        .world-event-widget[hidden] { display: none; }
        .world-event-widget::-webkit-scrollbar { display: none; }
        .world-event-widget__heading {
            padding-bottom: 4px;
            border-bottom: 1px solid rgba(255, 255, 255, .3);
            text-align: center;
        }
        .world-event-widget__event {
            display: flex;
            align-items: center;
            gap: 8px;
            padding-top: 5px;
        }
        .world-event-widget__event + .world-event-widget__event {
            margin-top: 5px;
            border-top: 1px solid rgba(255, 255, 255, .2);
        }
        .world-event-widget__details {
            flex: 1 1 auto;
            min-width: 0;
        }
        .world-event-widget__title {
            display: block;
            overflow: hidden;
            color: #ffd88a;
            font-style: italic;
            text-align: left;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .world-event-widget__progress {
            margin-top: 3px;
            color: #fff;
            font-size: 11px;
            text-align: left;
        }
        .world-event-widget__timer {
            flex: 0 0 66px;
            box-sizing: border-box;
            padding: 4px 3px;
            border: 1px solid rgba(255, 255, 255, .25);
            border-radius: 3px;
            background: rgba(255, 255, 255, .09);
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }
        .world-event-widget__timer-label,
        .world-event-widget__timer-value { display: block; }
        .world-event-widget__timer-label {
            color: #ccc;
            font-size: 10px;
            font-weight: normal;
        }
        .world-event-widget__timer-value { margin-top: 2px; }

        /* Clan channel */
        .msg-ch-clan small         { color: #007a03; }
        .msg-ch-clan .player-link  { color: #007a03; }
        .msg-ch-clan .msg-time-reply { color: #007a03; }

        /* Trade channel */
        .msg-ch-trade small        { color: #ff7800; }
        .msg-ch-trade .player-link { color: #ff7800; }

        /* Location channel */
        .msg-ch-location small        { color: #0055cc; }
        .msg-ch-location .player-link { color: #0055cc; }

        /* Party channel */
        .msg-ch-party small            { color: #009999; }
        .msg-ch-party .player-link     { color: #009999; }
        .msg-ch-party .msg-time-reply  { color: #009999; }

        .lgb       { background-image: url({{ asset('img/bg/lgb.gif') }}); background-repeat: repeat; }
        .lgb-left  { background-image: url({{ asset('img/icon/lgb-left.gif') }}); background-repeat: repeat-y; width: 14px; }
        .lgb-right { background-image: url({{ asset('img/icon/lgb-right.gif') }}); background-repeat: repeat-y; width: 15px; }
    </style>
</head>
<body>
<aside id="world-event-widget" class="world-event-widget" aria-live="polite" hidden></aside>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td width="1%" class="lgb-left"><img src="{{ asset('img/icon/d.gif') }}" width="14" height="1"><br></td>
        <td width="100%" class="lgb" valign="top">
            <div id="content" class="chat-area lgb">

                @foreach ($messages as $msg)
                    @php
                        $chClass = match($msg->channel) {
                            'clan'     => ' msg-ch-clan',
                            'trade'    => ' msg-ch-trade',
                            'location' => ' msg-ch-location',
                            'party'    => ' msg-ch-party',
                            default    => '',
                        };
                        $showArrow = in_array($msg->type, ['private', 'mention']);
                    @endphp
                    <div
                        class="message msg-{{ $msg->type }}{{ $chClass }}"
                        data-id="{{ $msg->id }}"
                        @if($msg->expires_at) data-expires-at="{{ $msg->expires_at }}" @endif
                    >

                        @if ($msg->type === 'private' && $msg->reply_to)
                            <small class="msg-time-reply"
                                   onclick="replyToUser('{{ addslashes($msg->reply_to) }}')"
                                   title="Ответить {{ $msg->reply_to }}">{{ $msg->time }}</small>
                        @else
                            <small>{{ $msg->time }}</small>
                        @endif
                        @if ($showArrow) » @endif

                        @if ($msg->type === 'system')
                            <span class="msg-system-icon">★</span> {!! $msg->content !!}

                        @elseif ($msg->type === 'information')
                            <span class="msg-information-icon">✔</span> {!! $msg->content !!}

                        @elseif ($msg->type === 'world_event')
                            {!! $msg->content !!}

                        @elseif (in_array($msg->type, ['party_invite', 'party_notice'], true))
                            {!! $msg->content !!}

                        @elseif ($msg->type === 'quest')
                            {!! $msg->content !!}

                        @elseif ($msg->type === 'quest_item')
                            <span class="msg-quest_item-icon">✦</span> {!! $msg->content !!}

                        @elseif ($msg->type === 'loot')
                            {!! $msg->content !!}

                        @elseif ($msg->type === 'private')
                            @if ($msg->sender_clan_icon && $msg->sender_clan_id)
                                <a href="{{ route('clan.public', ['clan' => $msg->sender_clan_id]) }}" title="Информация о клане" onclick="chatOpenClanInfo(this.href); return false;"><img src="{{ $msg->sender_clan_icon }}" class="chat-clan-icon" width="13" height="13" alt=""></a>
                            @endif
                            <span class="prv-name">{{ $msg->sender_name }}</span>@if ($msg->sender_level) <small class="chat-level">[{{ $msg->sender_level }}]</small>@endif
                            <a href="#" title="Информация о персонаже" onclick="chatOpenUserInfo({{ $msg->sender_id }}); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a>
                            »
                            <span class="prv-name">{{ $msg->target_name ?? '?' }}</span> {!! $msg->content !!}

                        @else
                            @if ($msg->sender_clan_icon && $msg->sender_clan_id)
                                <a href="{{ route('clan.public', ['clan' => $msg->sender_clan_id]) }}" title="Информация о клане" onclick="chatOpenClanInfo(this.href); return false;"><img src="{{ $msg->sender_clan_icon }}" class="chat-clan-icon" width="13" height="13" alt=""></a>
                            @endif
                            <a href="#"
                               class="player-link n"
                               data-uid="{{ $msg->sender_id }}"
                               data-name="{{ $msg->sender_name }}"
                               onclick="chatPlayerClick({{ $msg->sender_id }}, '{{ addslashes($msg->sender_name) }}'); return false;"
                            >{{ $msg->sender_name }}</a>@if ($msg->sender_level) <small class="chat-level">[{{ $msg->sender_level }}]</small>@endif
                            <a href="#" title="Информация о персонаже" onclick="chatOpenUserInfo({{ $msg->sender_id }}); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a>
                            {!! $msg->content !!}
                        @endif
                    </div>
                @endforeach

            </div>
        </td>
        <td width="1%" class="lgb-right"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    </tbody>
</table>

<script>
    var channel   = '{{ $channel->value }}';
    var pollUrl   = '{{ route('chat.messages') }}';
    var worldEventsUrl = '{{ route('chat.world-events') }}';
    var worldEventsPageUrl = @json(route('events', ['mode' => 'events']));
    var activeWorldEvents = @json($activeWorldEvents ?? []);
    var ignoreUrl = '{{ route('chat.ignore.add') }}';
    var csrfToken = '{{ csrf_token() }}';
    var lastMessageId = getLastMessageId();
    var realtime = @json($realtime);
    var realtimeInitialized = false;
    var realtimeCurrentChannelName = null;
    var realtimeCurrentChannel = null;
    var realtimePersonalChannel = null;
    var realtimeSystemChannel = null;
    var realtimeSyncTimer = null;
    var realtimeFullSyncPending = false;
    var realtimeFallbackTimer = null;
    var realtimeReconnectRequired = false;
    var messageExpirationTimer = null;
    var worldEventSyncTimer = null;
    var worldEventCountdownTimer = null;

    function openWorldEvent(runId) {
        var url = worldEventsPageUrl + '#event-run-' + parseInt(runId, 10);
        try {
            window.top.toggleMap(false);
            window.top.toLocation(url, true);
        } catch (e) {
            window.location.href = url;
        }

        return false;
    }

    function renderWorldEventWidget(events) {
        var widget = document.getElementById('world-event-widget');
        widget.innerHTML = '';

        if (!Array.isArray(events) || events.length === 0) {
            widget.hidden = true;
            return;
        }

        var heading = document.createElement('div');
        heading.className = 'world-event-widget__heading';
        heading.textContent = 'Активные события';
        widget.appendChild(heading);

        events.forEach(function (event) {
            var item = document.createElement('div');
            item.className = 'world-event-widget__event';

            var details = document.createElement('div');
            details.className = 'world-event-widget__details';

            var title = document.createElement('a');
            title.className = 'world-event-widget__title';
            title.href = worldEventsPageUrl + '#event-run-' + parseInt(event.run_id, 10);
            title.textContent = '«' + event.title + '»';
            title.onclick = function () { return openWorldEvent(event.run_id); };

            var progress = document.createElement('div');
            progress.className = 'world-event-widget__progress';
            progress.textContent = 'Этап ' + parseInt(event.stage_position, 10) + ': '
                + event.stage_title + '. ' + event.progress_label + ': '
                + parseInt(event.collected_count, 10) + ' / '
                + parseInt(event.global_limit, 10);

            var timer = document.createElement('div');
            timer.className = 'world-event-widget__timer';
            timer.dataset.deadline = String(Date.now() + Math.max(0, parseInt(event.remaining_seconds, 10)) * 1000);

            var timerLabel = document.createElement('span');
            timerLabel.className = 'world-event-widget__timer-label';
            timerLabel.textContent = 'Осталось:';

            var timerValue = document.createElement('span');
            timerValue.className = 'world-event-widget__timer-value';

            details.appendChild(title);
            details.appendChild(progress);
            timer.appendChild(timerLabel);
            timer.appendChild(timerValue);
            item.appendChild(details);
            item.appendChild(timer);
            widget.appendChild(item);
        });

        widget.hidden = false;
        updateWorldEventTimers();
    }

    function formatWorldEventRemaining(totalSeconds) {
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var seconds = totalSeconds % 60;
        var parts = [];

        if (days > 0) parts.push(days + 'д');
        if (days > 0 || hours > 0) parts.push(hours + 'ч');
        parts.push(minutes + 'м');
        parts.push(seconds + 'с');

        return parts.join(' ');
    }

    function updateWorldEventTimers() {
        document.querySelectorAll('.world-event-widget__timer').forEach(function (timer) {
            var remaining = Math.max(0, Math.ceil((parseInt(timer.dataset.deadline, 10) - Date.now()) / 1000));
            var value = timer.querySelector('.world-event-widget__timer-value');
            if (value) value.textContent = formatWorldEventRemaining(remaining);

            if (remaining === 0 && timer.dataset.syncRequested !== '1') {
                timer.dataset.syncRequested = '1';
                scheduleWorldEventSync();
            }
        });
    }

    function syncActiveWorldEvents() {
        fetch(worldEventsUrl, {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Не удалось обновить события.');
                return response.json();
            })
            .then(function (events) {
                activeWorldEvents = events;
                renderWorldEventWidget(events);
            })
            .catch(function () {});
    }

    function scheduleWorldEventSync() {
        if (worldEventSyncTimer) window.clearTimeout(worldEventSyncTimer);
        worldEventSyncTimer = window.setTimeout(function () {
            worldEventSyncTimer = null;
            syncActiveWorldEvents();
        }, 50);
    }

    function handleWorldEventStateChanged() {
        scheduleWorldEventSync();
    }

    function scrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }

    // Scroll to bottom on load
    window.addEventListener('load', scrollToBottom);
    renderWorldEventWidget(activeWorldEvents);

    function escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function safeAttr(str) {
        return escapeHtml(String(str || '')).replace(/'/g, '&#39;');
    }

    function clanIconHtml(msg) {
        if (!msg.sender_clan_icon || !msg.sender_clan_id) return '';
        var clanUrl = '{{ url('/clan-info') }}/' + parseInt(msg.sender_clan_id, 10);
        return '<a href="' + clanUrl + '" title="Информация о клане" onclick="chatOpenClanInfo(this.href); return false;">'
             + '<img src="' + safeAttr(msg.sender_clan_icon) + '" class="chat-clan-icon" width="13" height="13" alt=""></a>';
    }

    function levelHtml(msg) {
        if (!msg.sender_level) return '';
        return ' <small class="chat-level">[' + parseInt(msg.sender_level, 10) + ']</small>';
    }

    function playerInfoIconHtml(userId) {
        return '<a href="#" title="Информация о персонаже" onclick="chatOpenUserInfo(' + parseInt(userId, 10) + '); return false;">'
             + '<img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a>';
    }

    // Иконка информации о персонаже возле ника — открывает карточку игрока в отдельном окне
    function chatOpenUserInfo(userId) {
        window.open('{{ url('/info/u') }}/' + userId, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }

    function chatOpenClanInfo(url) {
        window.open(url, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }

    function buildMessageHtml(msg) {
        // Time — clickable for private messages
        var timeHtml;
        if (msg.type === 'private' && msg.reply_to) {
            timeHtml = '<small class="msg-time-reply"'
                     + ' onclick="replyToUser(\'' + safeAttr(msg.reply_to) + '\')"'
                     + ' title="Ответить ' + safeAttr(msg.reply_to) + '">'
                     + escapeHtml(msg.time) + '</small>';
        } else {
            timeHtml = '<small>' + escapeHtml(msg.time) + '</small>';
        }

        var showArrow = (msg.type === 'private' || msg.type === 'mention');
        var html = timeHtml + (showArrow ? ' » ' : ' ');

        if (msg.type === 'system') {
            html += '<span class="msg-system-icon">★</span> ' + msg.content;
        } else if (msg.type === 'information') {
            html += '<span class="msg-information-icon">✔</span> ' + msg.content;
        } else if (msg.type === 'world_event') {
            html += msg.content;
        } else if (msg.type === 'party_invite' || msg.type === 'party_notice') {
            html += msg.content;
        } else if (msg.type === 'quest') {
            html += msg.content;
        } else if (msg.type === 'quest_item') {
            html += '<span class="msg-quest_item-icon">✦</span> ' + msg.content;
        } else if (msg.type === 'loot') {
            html += msg.content;
        } else if (msg.type === 'private') {
            html += clanIconHtml(msg)
                  + '<span class="prv-name">' + escapeHtml(msg.sender_name) + '</span>'
                  + levelHtml(msg)
                  + ' ' + playerInfoIconHtml(msg.sender_id)
                  + ' » '
                  + '<span class="prv-name">' + escapeHtml(msg.target_name || '?') + '</span>'
                  + '&nbsp;' +msg.content;
        } else {
            html += clanIconHtml(msg)
                  + '<a href="#" class="player-link n" data-uid="' + msg.sender_id + '" data-name="' + safeAttr(msg.sender_name) + '" '
                  + 'onclick="chatPlayerClick(' + msg.sender_id + ', \'' + safeAttr(msg.sender_name) + '\'); return false;">'
                  + escapeHtml(msg.sender_name) + '</a>' + levelHtml(msg) + ' ' + playerInfoIconHtml(msg.sender_id) + ' ' + msg.content;
        }

        return html;
    }

    function handlePartyInviteAction(link) {
        if (!link || link.dataset.pending === '1') return false;

        link.dataset.pending = '1';
        fetch(link.href, {
            credentials: 'same-origin',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    if (!response.ok) throw new Error(data.message || 'Не удалось обработать приглашение.');
                    return data;
                });
            })
            .then(function (data) {
                try {
                    if (window.top.refreshChatChannels) window.top.refreshChatChannels();
                    if (window.top.openGameMessageModal) {
                        window.top.openGameMessageModal({ title: 'Группа', message: data.message });
                    }
                } catch (e) {}
            })
            .catch(function (error) {
                link.dataset.pending = '0';
                try {
                    if (window.top.openGameMessageModal) {
                        window.top.openGameMessageModal({ title: 'Группа', message: error.message });
                    }
                } catch (e) {}
            });

        return false;
    }

    function replyToUser(name) {
        try {
            var actionFrame = parent.document.getElementById('bottom-frame');
            if (actionFrame && actionFrame.contentWindow) {
                actionFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
            }
        } catch (e) {}
    }

    var chClass = { clan: ' msg-ch-clan', trade: ' msg-ch-trade', location: ' msg-ch-location', party: ' msg-ch-party' };

    function insertSorted(content, div, id) {
        var nodes = content.querySelectorAll('[data-id]');
        for (var i = 0; i < nodes.length; i++) {
            if (parseInt(nodes[i].getAttribute('data-id'), 10) > id) {
                content.insertBefore(div, nodes[i]);
                return;
            }
        }
        content.appendChild(div);
    }

    function buildDiv(msg) {
        var div = document.createElement('div');
        div.className = 'message msg-' + msg.type + (chClass[msg.channel] || '');
        div.setAttribute('data-id', String(msg.id));
        if (msg.expires_at) div.setAttribute('data-expires-at', msg.expires_at);
        div.innerHTML = buildMessageHtml(msg);
        return div;
    }

    function getLastMessageId() {
        var lastId = 0;
        document.querySelectorAll('#content [data-id]').forEach(function (el) {
            var id = parseInt(el.getAttribute('data-id'), 10) || 0;
            if (id > lastId) lastId = id;
        });
        return lastId;
    }

    function trimMessages(maxMessages) {
        var nodes = document.querySelectorAll('#content [data-id]');
        var removeCount = nodes.length - maxMessages;
        for (var i = 0; i < removeCount; i++) {
            nodes[i].remove();
        }
    }

    function removeExpiredMessages() {
        var now = Date.now();
        var removed = false;

        document.querySelectorAll('#content [data-expires-at]').forEach(function (el) {
            var expiresAt = Date.parse(el.getAttribute('data-expires-at'));
            if (!Number.isNaN(expiresAt) && expiresAt <= now) {
                el.remove();
                removed = true;
            }
        });

        if (removed) lastMessageId = getLastMessageId();
    }

    function syncMessages(serverMessages) {
        var content = document.getElementById('content');
        var atBottom = (window.innerHeight + window.pageYOffset) >= document.body.scrollHeight - 80;

        // Build lookup of server IDs
        var serverMap = {};
        serverMessages.forEach(function (msg) { serverMap[msg.id] = msg; });

        // Remove DOM messages no longer in server response
        content.querySelectorAll('[data-id]').forEach(function (el) {
            if (!serverMap[parseInt(el.getAttribute('data-id'), 10)]) el.remove();
        });

        // Build lookup of remaining DOM IDs
        var domIds = {};
        content.querySelectorAll('[data-id]').forEach(function (el) {
            domIds[parseInt(el.getAttribute('data-id'), 10)] = true;
        });

        // Insert missing messages in sorted position
        var added = false;
        serverMessages.forEach(function (msg) {
            if (!domIds[msg.id]) {
                insertSorted(content, buildDiv(msg), msg.id);
                added = true;
            }
        });

        lastMessageId = getLastMessageId();
        trimMessages(120);
        if (added && atBottom) scrollToBottom();
    }

    function appendMessages(serverMessages) {
        var content = document.getElementById('content');
        var atBottom = (window.innerHeight + window.pageYOffset) >= document.body.scrollHeight - 80;
        var domIds = {};
        var added = false;

        content.querySelectorAll('[data-id]').forEach(function (el) {
            domIds[parseInt(el.getAttribute('data-id'), 10)] = true;
        });

        serverMessages.forEach(function (msg) {
            if (!domIds[msg.id]) {
                insertSorted(content, buildDiv(msg), msg.id);
                domIds[msg.id] = true;
                added = true;
            }
            if (msg.id > lastMessageId) lastMessageId = msg.id;
        });

        trimMessages(120);
        if (added && atBottom) scrollToBottom();
    }

    function fetchMessages(onDone, incremental) {
        var url = pollUrl + '?channel=' + encodeURIComponent(channel);
        if (incremental && lastMessageId > 0) {
            url += '&after_id=' + encodeURIComponent(lastMessageId);
        }

        fetch(url, { headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (incremental) {
                    appendMessages(data);
                } else {
                    syncMessages(data);
                }
                if (onDone) onDone();
            })
            .catch(function () {});
    }

    function fullSync() { fetchMessages(null, false); }

    function realtimeEcho() {
        try {
            return window.top && window.top.Echo ? window.top.Echo : null;
        } catch (e) {
            return null;
        }
    }

    function realtimeChannelName(selectedChannel) {
        if (selectedChannel === 'main') return 'chat.main';
        if (selectedChannel === 'trade') return 'chat.trade';
        if (selectedChannel === 'location' && realtime.mapId) return 'chat.location.' + realtime.mapId;
        if (selectedChannel === 'clan' && realtime.clanId) return 'chat.clan.' + realtime.clanId;
        if (selectedChannel === 'party' && realtime.partyId) return 'chat.party.' + realtime.partyId;
        return null;
    }

    function scheduleRealtimeSync(full) {
        realtimeFullSyncPending = realtimeFullSyncPending || full;
        if (realtimeSyncTimer) window.clearTimeout(realtimeSyncTimer);
        realtimeSyncTimer = window.setTimeout(function () {
            realtimeSyncTimer = null;
            var runFullSync = realtimeFullSyncPending;
            realtimeFullSyncPending = false;
            fetchMessages(null, !runFullSync);
        }, 50);
    }

    function handleRealtimeMessage() {
        scheduleRealtimeSync(false);
    }

    function handleRealtimeInvalidation() {
        scheduleRealtimeSync(true);
    }

    function handleRealtimeExpiration(event) {
        if (!event || !event.message_id) return;
        if (event.preserve_in_private && channel === 'private') return;

        var message = document.querySelector('#content [data-id="' + parseInt(event.message_id, 10) + '"]');
        if (!message) return;

        message.remove();
        lastMessageId = getLastMessageId();
    }

    function stopFallbackPolling() {
        if (!realtimeFallbackTimer) return;
        window.clearInterval(realtimeFallbackTimer);
        realtimeFallbackTimer = null;
    }

    function startFallbackPolling() {
        if (realtimeFallbackTimer) return;
        realtimeFallbackTimer = window.setInterval(function () {
            fetchMessages(null, true);
            syncActiveWorldEvents();
        }, 5000);
    }

    function subscribeCurrentRealtimeChannel() {
        var echo = realtimeEcho();
        if (!echo) return;

        var nextName = realtimeChannelName(channel);
        if (nextName === realtimeCurrentChannelName) return;

        if (realtimeCurrentChannel) {
            realtimeCurrentChannel.stopListening('.chat.message.created', handleRealtimeMessage);
            echo.leave(realtimeCurrentChannelName);
        }

        realtimeCurrentChannelName = nextName;
        realtimeCurrentChannel = nextName
            ? echo.private(nextName).listen('.chat.message.created', handleRealtimeMessage)
            : null;
    }

    function initializeRealtime() {
        if (realtimeInitialized) return;

        var echo = realtimeEcho();
        if (!echo || !realtime.userId) {
            startFallbackPolling();
            window.setTimeout(initializeRealtime, 1000);
            return;
        }

        realtimeInitialized = true;
        realtimePersonalChannel = echo.private('App.Models.User.' + realtime.userId)
            .listen('.chat.message.created', handleRealtimeMessage)
            .listen('.chat.message.expired', handleRealtimeExpiration)
            .listen('.chat.messages.invalidated', handleRealtimeInvalidation);
        realtimeSystemChannel = echo.private('chat.system')
            .listen('.chat.message.created', handleRealtimeMessage)
            .listen('.chat.message.expired', handleRealtimeExpiration)
            .listen('.world-event.state.changed', handleWorldEventStateChanged);
        subscribeCurrentRealtimeChannel();

        var connection = echo.connector && echo.connector.pusher
            ? echo.connector.pusher.connection
            : null;

        if (!connection) {
            startFallbackPolling();
            return;
        }

        connection.bind('connected', function () {
            stopFallbackPolling();
            scheduleRealtimeSync(realtimeReconnectRequired);
            scheduleWorldEventSync();
            realtimeReconnectRequired = false;
        });
        ['disconnected', 'unavailable', 'failed'].forEach(function (state) {
            connection.bind(state, function () {
                realtimeReconnectRequired = true;
                startFallbackPolling();
            });
        });

        if (connection.state === 'connected') {
            stopFallbackPolling();
            scheduleRealtimeSync(false);
            scheduleWorldEventSync();
        } else {
            window.setTimeout(function () {
                if (connection.state !== 'connected') startFallbackPolling();
            }, 3000);
        }
    }

    initializeRealtime();
    removeExpiredMessages();
    messageExpirationTimer = window.setInterval(removeExpiredMessages, 1000);
    worldEventCountdownTimer = window.setInterval(updateWorldEventTimers, 1000);

    // Switch channel without reloading the iframe
    window.addEventListener('message', function (event) {
        if (!event.data || event.data.type !== 'changeChannel') return;
        var newChannel = event.data.channel;
        if (newChannel === channel) return;

        channel = newChannel;
        document.getElementById('content').innerHTML = '';
        lastMessageId = 0;

        subscribeCurrentRealtimeChannel();
        fetchMessages(scrollToBottom, false);
    });

    window.addEventListener('beforeunload', function () {
        var echo = realtimeEcho();
        stopFallbackPolling();
        if (realtimeSyncTimer) window.clearTimeout(realtimeSyncTimer);
        if (worldEventSyncTimer) window.clearTimeout(worldEventSyncTimer);
        if (messageExpirationTimer) window.clearInterval(messageExpirationTimer);
        if (worldEventCountdownTimer) window.clearInterval(worldEventCountdownTimer);

        if (realtimePersonalChannel) {
            realtimePersonalChannel.stopListening('.chat.message.created', handleRealtimeMessage);
            realtimePersonalChannel.stopListening('.chat.message.expired', handleRealtimeExpiration);
            realtimePersonalChannel.stopListening('.chat.messages.invalidated', handleRealtimeInvalidation);
        }
        if (realtimeSystemChannel) {
            realtimeSystemChannel.stopListening('.chat.message.created', handleRealtimeMessage);
            realtimeSystemChannel.stopListening('.chat.message.expired', handleRealtimeExpiration);
            realtimeSystemChannel.stopListening('.world-event.state.changed', handleWorldEventStateChanged);
            if (echo) echo.leave('chat.system');
        }
        if (realtimeCurrentChannel) {
            realtimeCurrentChannel.stopListening('.chat.message.created', handleRealtimeMessage);
            if (echo && realtimeCurrentChannelName) echo.leave(realtimeCurrentChannelName);
        }
    });

    // Click on player name → insert prv[NAME] or to[NAME] prefix into the message input
    function chatPlayerClick(uid, name) {
        try {
            var actionFrame = parent.document.getElementById('bottom-frame');
            if (actionFrame && actionFrame.contentWindow) {
                actionFrame.contentWindow.postMessage({ type: 'insertName', name: name }, '*');
            }
        } catch (e) {}
    }
</script>
<script src="{{ asset('js/player_menu.js') }}?v={{ filemtime(public_path('js/player_menu.js')) }}"></script>
<script>
    // Контекстное меню персонажа (ПКМ по нику в чате)
    initPlayerMenu({
        myUserId: {{ (int) auth()->id() }},
        csrfToken: '{{ csrf_token() }}',
        ignoredIds: @json(array_map('intval', $ignoredUserIds)),
        friendsAddUrl: '{{ route('friends.add') }}',
        ignoreAddUrl: '{{ route('chat.ignore.add') }}',
        ignoreRemoveBase: '{{ url('/chat/ignore') }}/',
        infoUrlBase: '{{ url('/info/u') }}/',
        sendPrivate: replyToUser,
        selector: 'a.player-link[data-uid]',
    });
</script>
</body>
</html>
