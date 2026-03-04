<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        body {
            margin: 0;
            /*background-color: #97e0f6;*/
            font-family: tahoma;
            font-size: 14px;
            overflow: hidden;
        }
        form {
            margin: 0;
        }
        .tbl-main_chat-btn {
            padding-top: 9px;
        }
        .game-time {
            position: relative;
            color: rgb(249, 223, 161);
            font-size: 10px;
            font-weight: bold;
            top: 3px;
            left: -3px;
        }
    </style>
</head>
<body>

<div style="/* position: absolute; *//* bottom: 0px; *//* width: 100%; */">
    <table width="100%" height="45" border="0" cellspacing="0" cellpadding="0"
           style="background: url({{ asset('img/bg/chat/tbl-main_chat-bg.gif') }}) repeat-x;">
        <tbody>
        <tr valign="top">
            <td width="106" id="chat_clock_container" style="background-image: url({{ asset('img/bg/chat/tbl-main_chat-clock-bg.gif') }})">
                <img src="{{ asset('img/bg/chat/tbl-main_chat-clock-btn.gif') }}"
                     style="cursor: pointer; position: relative; top: 12px;">
                <span class="game-time" id="chat_clock">17:22:45</span>
            </td>
            <td>
                <form onsubmit="if (!window.__cfRLUnblockHandlers) return false; chatSend();return false;">
                    <input type="image" src="{{ asset('img/icon/d.gif') }}" width="1" height="1"><br>
                    <input type="text" name="message" id="message" maxlength="500"
                           style="width:100%; color:#FFFFCC; background: transparent; border: 0px solid red; cursor:text;"
                           placeholder="Введите сообщение..."><br>
                </form>
                <iframe name="chat_hidden" src="" width="1" height="1" marginwidth="0" marginheight="0" scrolling="no"
                        frameborder="0" style="display: block; visibility: hidden;"></iframe>
                <form name="chat_hidden_form" method="post" target="chat_hidden" action="cht_data.php">
                    <input name="text" type="hidden" value="0">
                    <input name="user" type="hidden" value="1">
                    <input name="msg_text" type="hidden" value="">
                    <input name="channel" type="hidden" value="1">
                    <input name="channel_talk" type="hidden" value="1">
                    <input name="msg_id" type="hidden" value="1673571">
                    <input name="loc_id" type="hidden" value="9">
                    <input name="private" type="hidden" value="0">
                    <input name="complain" type="hidden" value="">
                    <input name="crc" type="hidden" value="">
                </form>
            </td>
            <td width="30"><img src="{{ asset('img/bg/chat/tbl-main_chat-bg-left.gif') }}" width="30" height="43/"></td>
            <td width="52" class="tbl-main_chat-btn">
                <a href="#" onclick="chatSend();return false;"><img
                        id="send_btn" src="{{ asset('img/bg/chat/send-reg.gif') }}" width="52" height="34" border="0"
                        title="Отправить сообщение в чат"></a></td>
            <td width="67" class="tbl-main_chat-btn">
                <a href="#" onclick="chatClearText();return false;"><img
                        id="clear_btn" src="{{ asset('img/bg/chat/clear-reg.gif') }}" width="67" height="34" border="0"
                        title="Очистить окно чата"></a></td>
            <td width="68" class="tbl-main_chat-btn">
                <a href="#" onclick="chatShowSmiles(this);return false;"><img
                        id="smile_btn" src="{{ asset('img/bg/chat/smile-reg.gif') }}" width="68" height="34" border="0"
                        title="Смайлики"></a></td>
            <td width="68" class="tbl-main_chat-btn"><a href="#" onclick=" return false;"><img
                        id="priv_btn" src="{{ asset('img/bg/chat/priv-reg.gif') }}" width="68" height="34" border="0"
                        title="Получать только сообщения адресованные вам" title1="Получать только приватные сообщения"
                        title2="Получать все сообщения"></a></td>
            <td width="74" class="tbl-main_chat-btn">
                <a href="#" onclick="chatRefreshUsers();return false;"><img
                        id="refresh_btn" src="{{ asset('img/bg/chat/refresh-reg.gif') }}" width="74" height="34" border="0"
                        title="Обновить список игроков в этой локации"></a></td>
            <td width="15"><img src="{{ asset('img/bg/chat/tbl-main_chat-bg-right.gif') }}" width="15" height="43/"></td>
        </tr>
        </tbody>
    </table>
</div>

<script>
    // Функция для отправки URL в game iframe
    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    function gebi(id){
        return document.getElementById(id)
    }

    var chatButtons = {
        // 'party_btn': ['images/des/group_inact.png', 'images/des/group_act.png', 'images/des/group_act.png', 'images/des/group_act.png'],
        'send_btn': ['{{ asset('img/bg/chat/send-reg.gif') }}', '{{ asset('img/bg/chat/send-roll.gif') }}', '{{ asset('img/bg/chat/send-pressed.gif') }}'],
        'clear_btn': ['{{ asset('img/bg/chat/clear-reg.gif') }}', '{{ asset('img/bg/chat/clear-roll.gif') }}', '{{ asset('img/bg/chat/clear-pressed.gif') }}'],
        'smile_btn': ['{{ asset('img/bg/chat/smile-reg.gif') }}', '{{ asset('img/bg/chat/smile-roll.gif') }}', '{{ asset('img/bg/chat/smile-pressed.gif') }}', '{{ asset('img/bg/chat/smile-pressed.gif') }}'],
        'priv_btn': ['{{ asset('img/bg/chat/priv-reg.gif') }}', '{{ asset('img/bg/chat/priv-roll.gif') }}', '{{ asset('img/bg/chat/priv-pressed.gif') }}', '{{ asset('img/bg/chat/priv-pressed-on.gif') }}'],
        'refresh_btn': ['{{ asset('img/bg/chat/refresh-reg.gif') }}', '{{ asset('img/bg/chat/refresh-roll.gif') }}', '{{ asset('img/bg/chat/refresh-pressed.gif') }}']
    }
    var chatButtonState = {};

    var chatChButtons = {
        '1'  : ['{{ asset('img/bg/chat/chat_1_off.png') }}', '{{ asset('img/bg/chat/chat_1_inact.png') }}', '{{ asset('img/bg/chat/chat_1_act.png') }}'],
        '8' : ['{{ asset('img/bg/chat/chat_2_off.png') }}', '{{ asset('img/bg/chat/chat_2_inact.png') }}', '{{ asset('img/bg/chat/chat_2_act.png') }}'],
        '4'  : ['{{ asset('img/bg/chat/chat_3_off.png') }}', '{{ asset('img/bg/chat/chat_3_inact.png') }}', '{{ asset('img/bg/chat/chat_3_act.png') }}'],
        '16' : ['{{ asset('img/bg/chat/chat_4_off.png') }}', '{{ asset('img/bg/chat/chat_4_inact.png') }}', '{{ asset('img/bg/chat/chat_4_act.png') }}'],
        '32'  : ['{{ asset('img/bg/chat/chat_5_off.png') }}', '{{ asset('img/bg/chat/chat_5_inact.png') }}', '{{ asset('img/bg/chat/chat_5_act.png') }}'],
        '64'  : ['{{ asset('img/bg/chat/chat_6_off.png') }}', '{{ asset('img/bg/chat/chat_6_inact.png') }}', '{{ asset('img/bg/chat/chat_6_act.png') }}']
    };

    for (i in chatButtons) {
        for (j in chatButtons[i]) {
            preloadImages(chatButtons[i][j]);
        }
    }
    for (i in chatChButtons) {
        for (j in chatChButtons[i]) {
            preloadImages(chatChButtons[i][j]);
        }
    }

    preloadImages(
        'images/des/4_left_act.png',
        'images/des/4_center_act.png',
        'images/des/4_right_act.png',
        'images/des/4_left_inact.png',
        'images/des/4_center_inact.png',
        'images/des/4_right_inact.png'
    );

    function preloadImages() {
        var d = document;
        if(!d._prImg) d._prImg = new Array();
        var i, j = d._prImg.length, a = preloadImages.arguments;
        for (i=0; i<a.length; i++) {
            d._prImg[j] = new Image;
            d._prImg[j++].src = a[i];
        }
    }

    function chatSetButtonHandlers() {
        for (i in chatButtons) {
            obj = gebi(i);
            if (!obj) continue;
            obj.onmouseout = function() {
                this.src = !chatButtonState[this.id] ? chatButtons[this.id][0]: chatButtons[this.id][3];
            };
            obj.onmouseover = function() {
                this.src = !chatButtonState[this.id] ? chatButtons[this.id][1]: chatButtons[this.id][3];
            };
            obj.onmousedown = function() {
                this.src = !chatButtonState[this.id] ? chatButtons[this.id][2]: chatButtons[this.id][3];
            };
            obj.onmouseup = function() {
                if (chatButtons[this.id][3]) {
                    chatButtonState[this.id] = !chatButtonState[this.id];
                    this.title = chatButtonState[this.id] ?
                        this.getAttribute('title2') || this.title :
                        this.getAttribute('title1') || this.title ;
                }
                this.src = !chatButtonState[this.id] ? chatButtons[this.id][1]: chatButtons[this.id][3];
            };
        }
    }

    function chatClearText() {
        const iframe = parent.document.querySelector('iframe[name="chat_text"]');
        console.log(iframe);

        if (!iframe || !iframe.contentDocument) {
            setTimeout(chatClearText, 1000);
            return;
        }

        const obj = iframe.contentDocument.getElementById('content');
        console.log(obj);

        if (!obj) {
            setTimeout(chatClearText, 1000);
            return;
        }

        obj.innerHTML = '';
    }

    chatSetButtonHandlers();
</script>

<script>
    function updateClock(timestamp) {
        var now = new Date(timestamp * 1000);

        var hours = now.getHours();
        var minutes = now.getMinutes();
        var seconds = now.getSeconds();

        if (hours < 10) hours = '0' + hours;
        if (minutes < 10) minutes = '0' + minutes;
        if (seconds < 10) seconds = '0' + seconds;

        var timeString = hours + ':' + minutes + ':' + seconds;
        document.getElementById('chat_clock').textContent = timeString;
    }

    // var currentTimestamp = Math.floor(Date.now() / 1000); // Получаем текущий timestamp
    var currentTimestamp = {{ now()->timestamp }};
    updateClock(currentTimestamp);

    setInterval(function() {
        currentTimestamp++;
        updateClock(currentTimestamp);
    }, 1000);
</script>

</body>
</html>
