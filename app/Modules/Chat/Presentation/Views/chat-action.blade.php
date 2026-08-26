<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Чат</title>
    <style>
        body {
            margin: 0;
            font-family: tahoma;
            font-size: 14px;
            overflow: hidden;
        }
        form { margin: 0; }
        .tbl-main_chat-btn { padding-top: 9px; }
        .game-time {
            position: relative;
            color: rgb(249, 223, 161);
            font-size: 10px;
            font-weight: bold;
            top: 3px;
            left: -3px;
        }
        .channel-badge {
            display: inline-block;
            color: #ffe4aa;
            font-size: 10px;
            font-weight: bold;
            background: rgba(0,0,0,0.3);
            border-radius: 3px;
            padding: 1px 4px;
            margin-left: 4px;
            vertical-align: middle;
        }
        #chat-error {
            display: none;
            position: fixed;
            bottom: 48px;
            left: 0;
            right: 0;
            background: #6a0000;
            color: #ffcccc;
            font-size: 11px;
            padding: 3px 8px;
            z-index: 99;
        }
    </style>
</head>
<body>

<div id="chat-error"></div>
<div>
    <table width="100%" height="45" border="0" cellspacing="0" cellpadding="0"
           style="background: url({{ asset('img/bg/chat/tbl-main_chat-bg.gif') }}) repeat-x;">
        <tbody>
        <tr valign="top">
            <td width="106" id="chat_clock_container"
                style="background-image: url({{ asset('img/bg/chat/tbl-main_chat-clock-bg.gif') }})">
                <img src="{{ asset('img/bg/chat/tbl-main_chat-clock-btn.gif') }}" style="cursor: pointer; position: relative; top: 12px;">
                <span class="game-time" id="chat_clock">00:00:00</span>
            </td>
            <td>
                <form id="chat-form" onsubmit="chatSend(); return false;">
                    <input type="hidden" id="channel" name="channel" value="{{ $channel->value }}">
                    <input type="image" src="{{ asset('img/icon/d.gif') }}" width="1" height="1"><br>
                    <input type="text" name="message" id="message" maxlength="500" autocomplete="off" style="width:100%; color:#FFFFCC; background: transparent; border: 0px solid red; cursor:text;" placeholder="Введите сообщение..."><br>
                </form>
            </td>
            <td width="30"><img src="{{ asset('img/bg/chat/tbl-main_chat-bg-left.gif') }}" width="30" height="43"></td>
            <td width="52" class="tbl-main_chat-btn">
                <a href="#" onclick="chatSend(); return false;">
                    <img id="send_btn" src="{{ asset('img/bg/chat/send-reg.gif') }}" width="52" height="34" border="0" title="Отправить сообщение в чат">
                </a>
            </td>
            <td width="67" class="tbl-main_chat-btn">
                <a href="#" onclick="chatClearText(); return false;">
                    <img id="clear_btn" src="{{ asset('img/bg/chat/clear-reg.gif') }}" width="67" height="34" border="0" title="Очистить окно чата">
                </a>
            </td>
            <td width="68" class="tbl-main_chat-btn">
                <a href="#" onclick="chatShowSmiles(this); return false;">
                    <img id="smile_btn" src="{{ asset('img/bg/chat/smile-reg.gif') }}" width="68" height="34" border="0" title="Смайлики">
                </a>
            </td>
            <td width="68" class="tbl-main_chat-btn">
                <a href="#" onclick="chatTogglePrivateMode(); return false;">
                    <img id="priv_btn" src="{{ asset('img/bg/chat/priv-reg.gif') }}" width="68" height="34"
                         border="0"
                         title="Получать только сообщения адресованные вам"
                         title1="Получать только приватные сообщения"
                         title2="Получать все сообщения">
                </a>
            </td>
            <td width="74" class="tbl-main_chat-btn">
                <a href="#" onclick="chatRefreshUsers(); return false;">
                    <img id="refresh_btn" src="{{ asset('img/bg/chat/refresh-reg.gif') }}" width="74" height="34" border="0" title="Обновить список игроков в этой локации">
                </a>
            </td>
            <td width="15">
                <img src="{{ asset('img/bg/chat/tbl-main_chat-bg-right.gif') }}" width="15" height="43">
            </td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    var sendUrl   = '{{ route('chat.send') }}';
    var csrfToken = '{{ csrf_token() }}';

    // ---- Helpers ----
    function gebi(id) { return document.getElementById(id); }

    function chatSend() {
        var msg     = gebi('message').value.trim();
        var channel = gebi('channel').value;

        if (!msg) return;

        // Закрыть панель смайлов и отжать её кнопку
        try { window.top.hideSmiles(); } catch (e) {}

        var btn = gebi('send_btn');
        if (btn) btn.src = '{{ asset('img/bg/chat/send-pressed.gif') }}';

        fetch(sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({ message: msg, channel: channel }),
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.ok) {
                gebi('message').value = '';
                chatHideError();
            } else if (data.error) {
                try {
                    window.top.systemInfo(data.error);
                } catch (e) {
                    chatShowError(data.error);
                }
            }
        })
        .catch(function () {})
        .finally(function () {
            if (btn) btn.src = '{{ asset('img/bg/chat/send-reg.gif') }}';
        });
    }

    var chatErrorTimer = null;
    function chatShowError(msg) {
        var el = gebi('chat-error');
        if (!el) return;
        el.textContent = msg;
        el.style.display = 'block';
        clearTimeout(chatErrorTimer);
        chatErrorTimer = setTimeout(chatHideError, 4000);
    }
    function chatHideError() {
        var el = gebi('chat-error');
        if (el) el.style.display = 'none';
    }

    function chatClearText() {
        try {
            var iframe = parent.document.querySelector('iframe[name="chat_text"]');
            if (iframe && iframe.contentDocument) {
                var obj = iframe.contentDocument.getElementById('content');
                if (obj) obj.innerHTML = '';
            }
        } catch (e) {}
    }

    function chatRefreshUsers() {
        try {
            var whoFrame = parent.document.getElementById('who-frame');
            if (whoFrame) whoFrame.src = whoFrame.src;
        } catch (e) {}
    }

    function chatShowSmiles(el) {
        try { window.top.toggleSmiles(); } catch (e) {}
    }

    // Вернуть кнопку смайлов в отжатое состояние (вызывается и из главного окна)
    function chatResetSmileBtn() {
        chatButtonState['smile_btn'] = false;
        var btn = gebi('smile_btn');
        if (btn) btn.src = '{{ asset('img/bg/chat/smile-reg.gif') }}';
    }

    var channelSlugToCode = { main: 1, location: 2, trade: 8, clan: 4, party: 16, private: 32 };
    var prevChannelCode = 1;

    function chatTogglePrivateMode() {
        try {
            var btn = gebi('priv_btn');
            if (!btn) return;

            var currentChannel = gebi('channel').value || 'main';

            if (currentChannel !== 'private') {
                prevChannelCode = channelSlugToCode[currentChannel] || 1;
                btn.src = '{{ asset('img/bg/chat/priv-pressed-on.gif') }}';
                window.parent.chatChangePreset(32);
            } else {
                btn.src = '{{ asset('img/bg/chat/priv-reg.gif') }}';
                window.parent.chatChangePreset(prevChannelCode);
            }
        } catch (e) {}
    }

    // ---- Receive messages from parent / sibling frames ----
    window.addEventListener('message', function (event) {
        if (!event.data || !event.data.type) return;

        // Parent changed channel tab → update our hidden channel field
        if (event.data.type === 'setChannel') {
            gebi('channel').value = event.data.channel;
        }

        // Click on player name → to[NAME]
        if (event.data.type === 'insertName') {
            var input = gebi('message');
            var prefix = 'to[' + event.data.name + '] ';
            input.value = prefix;
            input.focus();
            input.setSelectionRange(prefix.length, prefix.length);
        }

        if (event.data.type === 'insertItem') {
            var input = gebi('message');
            var code  = event.data.code;
            var pos   = input.selectionStart;
            input.value = input.value.slice(0, pos) + code + input.value.slice(pos);
            input.focus();
            input.setSelectionRange(pos + code.length, pos + code.length);
        }

        // Click on timestamp of private message → prv[NAME]
        if (event.data.type === 'insertPrivate') {
            var input = gebi('message');
            var prefix = 'prv[' + event.data.name + '] ';
            input.value = prefix;
            input.focus();
            input.setSelectionRange(prefix.length, prefix.length);
        }
    });

    // Enter key sends message
    gebi('message').addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); chatSend(); }
    });

    // ---- Button hover/press effects ----
    var chatButtons = {
        'send_btn':    ['{{ asset('img/bg/chat/send-reg.gif') }}',    '{{ asset('img/bg/chat/send-roll.gif') }}',    '{{ asset('img/bg/chat/send-pressed.gif') }}'],
        'clear_btn':   ['{{ asset('img/bg/chat/clear-reg.gif') }}',   '{{ asset('img/bg/chat/clear-roll.gif') }}',   '{{ asset('img/bg/chat/clear-pressed.gif') }}'],
        'smile_btn':   ['{{ asset('img/bg/chat/smile-reg.gif') }}',   '{{ asset('img/bg/chat/smile-roll.gif') }}',   '{{ asset('img/bg/chat/smile-pressed.gif') }}',   '{{ asset('img/bg/chat/smile-pressed.gif') }}'],
        'priv_btn':    ['{{ asset('img/bg/chat/priv-reg.gif') }}',    '{{ asset('img/bg/chat/priv-roll.gif') }}',    '{{ asset('img/bg/chat/priv-pressed.gif') }}',    '{{ asset('img/bg/chat/priv-pressed-on.gif') }}'],
        'refresh_btn': ['{{ asset('img/bg/chat/refresh-reg.gif') }}', '{{ asset('img/bg/chat/refresh-roll.gif') }}', '{{ asset('img/bg/chat/refresh-pressed.gif') }}'],
    };
    var chatButtonState = {};

    (function () {
        for (var id in chatButtons) {
            (function (btnId) {
                var obj = gebi(btnId);
                if (!obj) return;
                var imgs = chatButtons[btnId];

                obj.onmouseout  = function () { this.src = chatButtonState[this.id] ? imgs[3] : imgs[0]; };
                obj.onmouseover = function () { this.src = chatButtonState[this.id] ? imgs[3] : imgs[1]; };
                obj.onmousedown = function () { this.src = chatButtonState[this.id] ? imgs[3] : imgs[2]; };
                obj.onmouseup   = function () {
                    if (imgs[3] !== undefined) {
                        chatButtonState[this.id] = !chatButtonState[this.id];
                    }
                    this.src = chatButtonState[this.id] ? imgs[3] : imgs[1];
                };
            })(id);
        }
    })();

    // ---- Clock ----
    (function () {
        var ts = {{ now()->timestamp }};
        function tick() {
            var d = new Date(ts * 1000);
            var h = String(d.getHours()).padStart(2, '0');
            var m = String(d.getMinutes()).padStart(2, '0');
            var s = String(d.getSeconds()).padStart(2, '0');
            var el = gebi('chat_clock');
            if (el) el.textContent = h + ':' + m + ':' + s;
            ts++;
        }
        tick();
        setInterval(tick, 1000);
    })();
</script>

</body>
</html>
