<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Онлайн Игра</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100vh;
            margin: 0;
            background-color: #ffe4aa;
            overflow-y: hidden;
            font-family: Tahoma;
            font-size: 14px;
        }

        .tbl-main_top-bg {
            background-image: url({{ asset('img/bg/tbl-main_top-bg.gif') }});
            background-repeat: repeat-x;
            height: 73px;
        }
        .bg {
            background-color: #000;
            background-image: url({{ asset('img/bg/bg.gif') }});
            background-attachment: fixed;
            background-position: 0 5px;
        }
        .tbl-sts_top {
            background-image: url({{ asset('img/bg/tbl-sts_top.gif') }});
            background-repeat: repeat-x;
            background-position: bottom;
            height: 19px;
        }
        .tbl-sts-bb {
            background: url({{ asset('img/bg/tbl-sts.png') }}) left top repeat-x;
        }
        .tbl-sts b {
            background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-lb b {
            background-position: 0 -170px;
        }
        .tbl-sts-rb b {
            background-position: 0 -219px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-lbt b {
            background-position: 0 -150px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }
        .tbl-sts-rbt b {
            background-position: 0 -200px;
            height: 20px;
        }
        .tbl-sts_left {
            background-image: url({{ asset('img/bg/tbl-sts_left.gif') }});
            background-repeat: repeat-y;
            width: 19px;
            background-position: right;
        }
        .tbl-sts_right {
            background-image: url({{ asset('img/bg/tbl-sts_right.gif') }});
            background-repeat: repeat-y;
            width: 19px;
        }
        .bgg {
            background-image: url({{ asset('img/bg/bgg.gif') }});
        }


        /** red line **/
        .tbl-main_left-bottom-bg {
            background: url({{ asset('img/bg/tbl-main_left-bottom-bg.gif') }}) no-repeat bottom;
            height: 9px;
        }
        .tbl-main_center-bottom {
            background: url({{ asset('img/bg/tbl-main_center-bottom.gif') }}) repeat-x bottom;
            height: 9px;
        }
        .tbl-main_right-bottom-bg {
            background: url({{ asset('img/bg/tbl-main_right-bottom-bg.gif') }}) no-repeat bottom;
            height: 9px;
        }

        .error_div {
            position: absolute;
            height: 100%;
            width: 100%;
            filter: "progid:DXImageTransform.Microsoft.Alpha(opacity=80)";
            moz-opacity: .8;
            opacity: .8;
            background-image: url({{ asset('img/bg/error_bg.gif') }});
            left: 0;
            top: 0;
        }
    </style>
</head>
<body class="bg">
<table cellpadding="0" cellspacing="0" width="100%" height="70%" border="0">
    <tbody>
    <tr class="tbl-main_top-bg" style="height: 73px">
        <td colspan="3"><iframe id="menu-frame" width="100%" height="60px" frameborder="0" src="{{ route('menu') }}"></iframe></td>
    </tr>
    <tr>
        <td width="250px">
{{--            <iframe width="250px" height="100%" frameborder="0" id="character-frame" src="{{ route('hero') }}"></iframe>--}}
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr height="33">
                    <td width="19" valign="bottom" align="right" class="tbl-sts tbl-sts-lt"><b></b></td>
                    <td class="tbl-sts_top" align="left" valign="top"></td>
                    <td width="19" valign="bottom" class="tbl-sts tbl-sts-rt"><b></b></td>
                </tr>
                <tr height="100%">
                    <td class="tbl-sts_left" valign="top">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" align="right" class="tbl-sts tbl-sts-ltb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" align="right" class="tbl-sts tbl-sts-lbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td id="hero_content" class="bgg" align="left" valign="top" width="100%">

                        <iframe width="250px" name="hero" height="100%" frameborder="0" id="character-frame" src="{{ route('hero') }}"></iframe>

                    </td>
                    <td valign="top" class="tbl-sts_right">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" class="tbl-sts tbl-sts-rtb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" class="tbl-sts tbl-sts-rbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr height="19">
                    <td align="right" class="tbl-sts tbl-sts-lb"><b></b></td>
                    <td class="tbl-sts tbl-sts-bb">&nbsp;</td>
                    <td class="tbl-sts tbl-sts-rb"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="100%" height="100%">
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr height="33">
                    <td width="19" valign="bottom" align="right" class="tbl-sts tbl-sts-lt"><b></b></td>
                    <td class="tbl-sts_top" align="left" valign="top"></td>
                    <td width="19" valign="bottom" class="tbl-sts tbl-sts-rt"><b></b></td>
                </tr>
                <tr height="100%">
                    <td class="tbl-sts_left" valign="top">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" align="right" class="tbl-sts tbl-sts-ltb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" align="right" class="tbl-sts tbl-sts-lbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td id="body_content" class="bgg" align="left" valign="top" width="100%">

                        <iframe width="100%" name="game" height="100%" frameborder="0" id="game-frame" src="{{ route('location') }}"></iframe>
{{--                        <iframe width="100%" height="100%" frameborder="0" id="game-frame" src="{{ route('fight') }}"></iframe>--}}

                    </td>
                    <td valign="top" class="tbl-sts_right">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" class="tbl-sts tbl-sts-rtb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" class="tbl-sts tbl-sts-rbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr height="19">
                    <td align="right" class="tbl-sts tbl-sts-lb"><b></b></td>
                    <td class="tbl-sts tbl-sts-bb">&nbsp;</td>
                    <td class="tbl-sts tbl-sts-rb"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>

        <td width="300px">
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr height="33">
                    <td width="19" valign="bottom" align="right" class="tbl-sts tbl-sts-lt"><b></b></td>
                    <td class="tbl-sts_top" align="left" valign="top"></td>
                    <td width="19" valign="bottom" class="tbl-sts tbl-sts-rt"><b></b></td>
                </tr>
                <tr height="100%">
                    <td class="tbl-sts_left" valign="top">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" align="right" class="tbl-sts tbl-sts-ltb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" align="right" class="tbl-sts tbl-sts-lbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td id="map_content" class="bgg" align="left" valign="top" width="100%">

                        <iframe width="300px" name="map" height="100%" frameborder="0" id="map-frame" src="{{ route('on_map', ['hide' => 1]) }}"></iframe>

                    </td>
                    <td valign="top" class="tbl-sts_right">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" class="tbl-sts tbl-sts-rtb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" class="tbl-sts tbl-sts-rbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr height="19">
                    <td align="right" class="tbl-sts tbl-sts-lb"><b></b></td>
                    <td class="tbl-sts tbl-sts-bb">&nbsp;</td>
                    <td class="tbl-sts tbl-sts-rb"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td colspan="3" height="9">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                <tr>
                    <td height="9" width="66" class="tbl-main_left-bottom-bg"></td>
                    <td height="9" class="tbl-main_center-bottom"><img src="{{ asset('img/icon/d.gif') }}" alt="" height="9"></td>
                    <td height="9" width="64" class="tbl-main_right-bottom-bg"></td>
                </tr>
                <tr>
                    <td colspan="3" height="3"></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<table cellpadding="0" cellspacing="0" width="100%" height="30%" border="0">
    <tbody>
    <tr>
        <td width="100%"><iframe id="chat-frame" height="100%" width="100%" frameborder="0" src="{{ route('chat') }}"></iframe></td>
        <td width="335px"><iframe id="who-frame" height="100%" width="335px" frameborder="0" src="{{ route('who') }}"></iframe></td>
    </tr>
    <tr style="height: 40px">
        <td colspan="2"><iframe height="45px" width="100%" frameborder="0" id="bottom-frame" src="{{ route('chat-action') }}"></iframe></td>
    </tr>
    </tbody>
</table>

<div id="error_div" class="error_div" style="display: none; z-index: 1000; width: 100%; height: 100vh;"></div>
<iframe width="1" height="1" frameborder="0" id="error" name="error" src="" scrolling="no" style="display: none; position: absolute; left: 0px; top: 0px; z-index: 1001;" allowtransparency="true"></iframe>

<script>
    function showErrorIframe(message) {
        var iframe = document.getElementById('error');
        var overlay = document.getElementById('error_div');
        var urlError = '{{ route('error') }}';

        iframe.src = urlError + '?message=' + message;

        iframe.style.width = '480px';
        iframe.style.height = '300px';

        iframe.style.left = (window.innerWidth / 2 - 200) + 'px';
        iframe.style.top = (window.innerHeight / 2 - 100) + 'px';

        iframe.style.display = 'block';
        overlay.style.display = 'block';
    }

    function hideErrorIframe() {
        var iframe = document.getElementById('error');
        var overlay = document.getElementById('error_div');

        iframe.src = '';

        iframe.style.display = 'none';
        overlay.style.display = 'none';
    }
</script>
<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'arrowup':--}}
    {{--            document.getElementById('move-north').click();--}}
    {{--            break;--}}
    {{--        case 'arrowdown':--}}
    {{--            document.getElementById('move-south').click();--}}
    {{--            break;--}}
    {{--        case 'arrowleft':--}}
    {{--            document.getElementById('move-west').click();--}}
    {{--            break;--}}
    {{--        case 'arrowright':--}}
    {{--            document.getElementById('move-east').click();--}}
    {{--            break;--}}
    {{--        case 'f':--}}
    {{--            document.getElementById('take-item').click();--}}
    {{--            break;--}}
    {{--        case 'i':--}}
    {{--            toLocation('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            toLocation('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            toLocation('{{ route('location') }}');--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}

    // Обработка сообщений от игрового iframe
    window.addEventListener('message', function(event) {
        // Проверка полученных данных
        // if (event.data.health !== undefined || event.data.mp !== undefined || event.data.experience !== undefined || event.data.lvl !== undefined) {
        //     // Пересылка данных в character-frame
        //     document.getElementById('character-frame').contentWindow.postMessage(event.data, '*');
        // }

        if (event.data.url) {
            toLocation(event.data.url);
        }
    });

    function sendDataToGame(url) {
        window.postMessage({ url: url }, '*');
    }

    function toLocation(url) {
        document.getElementById('game-frame').contentWindow.location.href = url;
    }

    function updateIframeWho() {
        const iframeWho = document.getElementById('who-frame');
        iframeWho.src = iframeWho.src;
    }
</script>

<script>
    function goTo(direction) {
        const directions = ['up', 'down', 'north', 'west', 'east', 'south'];

        if (!directions.includes(direction)) {
            console.error('Invalid direction:', direction);
            return;
        }

        const routes = {
            up: "{{ route('move-to', ['direction' => 'up']) }}",
            down: "{{ route('move-to', ['direction' => 'down']) }}",
            north: "{{ route('move-to', ['direction' => 'north']) }}",
            west: "{{ route('move-to', ['direction' => 'west']) }}",
            east: "{{ route('move-to', ['direction' => 'east']) }}",
            south: "{{ route('move-to', ['direction' => 'south']) }}",
        };

        toLocation(routes[direction]);
    }

    function attackMonster(id, monsterId, action) {
        let routeTemplate = "{{ route('fight.attack', ['id' => ':id', 'monsterId' => ':monsterId', 'action' => ':action']) }}";
        document.getElementById('game-frame').contentWindow.location.href = routeTemplate
            .replace(':id', id)
            .replace(':monsterId', monsterId)
            .replace(':action', action);
    }

    // Глобальні змінні в головному вікні (parent)
    if (typeof parent.isCooldown === 'undefined') {
        parent.isCooldown = false;
    }
    if (typeof parent.cooldownDuration === 'undefined') {
        parent.cooldownDuration = 1000;
    }

    function startCooldown() {
        const heroFrame = document.getElementById('character-frame');
        if (!heroFrame) return console.error('Iframe #character-frame не знайдено');

        const heroWindow = heroFrame.contentWindow;
        if (!heroWindow || !heroWindow.document) return console.error('Не вдалося отримати hero iframe');

        const heroDocument = heroWindow.document;
        const cooldownBar = heroDocument.getElementById('cooldownBar');

        if (!cooldownBar) return console.error('Не знайдено елемент cooldownBar');

        parent.isCooldown = true;
        parent.window.dispatchEvent(new CustomEvent('cooldown:start'));

        cooldownBar.classList.remove('filling');
        cooldownBar.style.transition = 'none';
        cooldownBar.style.width = '0%';

        setTimeout(() => {
            cooldownBar.style.transition = `width ${parent.cooldownDuration}ms linear`;
            cooldownBar.classList.add('filling');
            cooldownBar.style.width = '100%';

            setTimeout(() => {
                parent.isCooldown = false;
                parent.window.dispatchEvent(new CustomEvent('cooldown:end'));
            }, parent.cooldownDuration);
        }, 50);

        return parent.isCooldown;
    }


    function sendToFrame(frameIdOrName, data, origin = '*') {
        try {
            // 1️⃣ Получаем родительский контекст (верхнее окно)
            const parentWin = window.parent || window.top;
            if (!parentWin) {
                console.error('❌ Не удалось получить parent окно');
                return false;
            }

            // 2️⃣ Пытаемся найти iframe по id
            let targetFrame = parentWin.document.getElementById(frameIdOrName);
            if (targetFrame && targetFrame.contentWindow) {
                targetFrame.contentWindow.postMessage(data, origin);
                console.debug(`📤 Сообщение отправлено в iframe #${frameIdOrName}`, data);
                return true;
            }

            // 3️⃣ Если не найден по id — пробуем по name
            const frameByName = parentWin.frames[frameIdOrName];
            if (frameByName) {
                frameByName.postMessage(data, origin);
                console.debug(`📤 Сообщение отправлено в iframe [name=${frameIdOrName}]`, data);
                return true;
            }

            console.error(`❌ iframe "${frameIdOrName}" не найден`);
            return false;
        } catch (err) {
            console.error('⚠️ Ошибка при отправке сообщения в iframe:', err);
            return false;
        }
    }

    const currentLocationId = {{ auth()->user()->location_id }};
    sendToFrame('map-frame', { currentLocationId });
</script>


</body>
</html>
