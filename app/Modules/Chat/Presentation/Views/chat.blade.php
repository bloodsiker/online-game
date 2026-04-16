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
        .msg-quest             { border-left: 2px solid #000000; padding-left: 3px; color: #000000; font-style: italic; }
        .msg-quest small       { color: #000000; font-style: normal; }
        .prv-name         { color: #ff0000; font-weight: bold; }
        .msg-time-reply   { cursor: pointer; text-decoration: underline dotted #999; color: #ff0000; }
        .msg-time-reply:hover { opacity: 0.75; }
        .chat-to          { color: #996600; font-weight: bold; }
        .chat-item        { color: #006699; font-weight: bold; cursor: help; }
        .chat-item-unknown{ color: #999; }
        .player-link      { color: #990000; font-weight: bold; }
        .player-link:hover { text-decoration: underline; }

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

        .lgb       { background-image: url({{ asset('img/bg/lgb.gif') }}); background-repeat: repeat; }
        .lgb-left  { background-image: url({{ asset('img/icon/lgb-left.gif') }}); background-repeat: repeat-y; width: 14px; }
        .lgb-right { background-image: url({{ asset('img/icon/lgb-right.gif') }}); background-repeat: repeat-y; width: 15px; }
    </style>
</head>
<body>
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
                            default    => '',
                        };
                        $showArrow = in_array($msg->type, ['private', 'mention']);
                    @endphp
                    <div class="message msg-{{ $msg->type }}{{ $chClass }}" data-id="{{ $msg->id }}">

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

                        @elseif ($msg->type === 'quest')
                            {!! $msg->content !!}

                        @elseif ($msg->type === 'private')
                            <span class="prv-name">{{ $msg->sender_name }}</span>
                            »
                            <span class="prv-name">{{ $msg->target_name ?? '?' }}</span> {!! $msg->content !!}

                        @else
                            <a href="#"
                               class="player-link n"
                               data-uid="{{ $msg->sender_id }}"
                               onclick="chatPlayerClick({{ $msg->sender_id }}, '{{ addslashes($msg->sender_name) }}'); return false;"
                            >{{ $msg->sender_name }}</a> {!! $msg->content !!}
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
    var ignoreUrl = '{{ route('chat.ignore.add') }}';
    var csrfToken = '{{ csrf_token() }}';

    function scrollToBottom() {
        window.scrollTo(0, document.body.scrollHeight);
    }

    // Scroll to bottom on load
    window.addEventListener('load', scrollToBottom);

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
        } else if (msg.type === 'quest') {
            html += msg.content;
        } else if (msg.type === 'private') {
            html += '<span class="prv-name">' + escapeHtml(msg.sender_name) + '</span>'
                  + ' » '
                  + '<span class="prv-name">' + escapeHtml(msg.target_name || '?') + '</span>'
                  + '&nbsp;' +msg.content;
        } else {
            html += '<a href="#" class="player-link n" data-uid="' + msg.sender_id + '" '
                  + 'onclick="chatPlayerClick(' + msg.sender_id + ', \'' + safeAttr(msg.sender_name) + '\'); return false;">'
                  + escapeHtml(msg.sender_name) + '</a> ' + msg.content;
        }

        return html;
    }

    function replyToUser(name) {
        try {
            var actionFrame = parent.document.getElementById('bottom-frame');
            if (actionFrame && actionFrame.contentWindow) {
                actionFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
            }
        } catch (e) {}
    }

    var chClass = { clan: ' msg-ch-clan', trade: ' msg-ch-trade', location: ' msg-ch-location' };

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
        div.innerHTML = buildMessageHtml(msg);
        return div;
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

        if (added && atBottom) scrollToBottom();
    }

    function fetchMessages(onDone) {
        fetch(pollUrl + '?channel=' + channel, { headers: { 'X-CSRF-TOKEN': csrfToken } })
            .then(function (r) { return r.json(); })
            .then(function (data) { syncMessages(data); if (onDone) onDone(); })
            .catch(function () {});
    }

    function poll() { fetchMessages(null); }

    setInterval(poll, 5000);

    // Switch channel without reloading the iframe
    window.addEventListener('message', function (event) {
        if (!event.data || event.data.type !== 'changeChannel') return;
        var newChannel = event.data.channel;
        if (newChannel === channel) return;

        channel = newChannel;
        document.getElementById('content').innerHTML = '';

        fetchMessages(scrollToBottom);
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
</body>
</html>
