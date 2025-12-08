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
            <td width="52" class="tbl-main_chat-btn"><a href="#"
                                                        onclick="if (!window.__cfRLUnblockHandlers) return false; chatSend();return false;"><img
                        id="send_btn" src="{{ asset('img/bg/chat/send-reg.gif') }}" width="52" height="34" border="0"
                        title="Отправить сообщение в чат"></a></td>
            <td width="67" class="tbl-main_chat-btn"><a href="#"
                                                        onclick="if (!window.__cfRLUnblockHandlers) return false; chatClearText();return false;"><img
                        id="clear_btn" src="{{ asset('img/bg/chat/clear-reg.gif') }}" width="67" height="34" border="0"
                        title="Очистить окно чата"></a></td>
            <td width="68" class="tbl-main_chat-btn"><a href="#"
                                                        onclick="if (!window.__cfRLUnblockHandlers) return false; chatShowSmiles(this);return false;"><img
                        id="smile_btn" src="{{ asset('img/bg/chat/smile-reg.gif') }}" width="68" height="34" border="0"
                        title="Смайлики"></a></td>
            <td width="68" class="tbl-main_chat-btn"><a href="#"
                                                        onclick="if (!window.__cfRLUnblockHandlers) return false; return false;"><img
                        id="priv_btn" src="{{ asset('img/bg/chat/priv-reg.gif') }}" width="68" height="34" border="0"
                        title="Получать только сообщения адресованные вам" title1="Получать только приватные сообщения"
                        title2="Получать все сообщения"></a></td>
            <td width="74" class="tbl-main_chat-btn"><a href="#"
                                                        onclick="if (!window.__cfRLUnblockHandlers) return false; chatRefreshUsers();return false;"><img
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
