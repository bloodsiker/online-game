<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 14px; }
        a { color: #000000; }
        a:hover { color: #353434 }
        ul { padding-left: 0; }
        ul li { list-style-type: none; margin-bottom: 3px; }
        ul li a img { margin-right: 6px; border: 0; }
        .main-table { width: 100%; height: 100%; }
        .center { text-align: center; }
        .location-description { margin-top: 15px; margin-bottom: 10px; }
        .battle-description { margin-top: 15px; }
        .side-move { margin-top: 30px; }
        .side-move::after { content: ""; display: block; clear: both; }
        .color-red { color: red; }
        .bg { background-color: #000; background-image: url({{ asset('img/bg/bg.gif') }}); background-attachment: fixed; background-position: 0 5px; }
        .tbl-sts_top { background-image: url({{ asset('img/bg/tbl-sts_top.gif') }}); background-repeat: repeat-x; background-position: bottom; height: 19px; }
        .tbl-sts-bb { background: url({{ asset('img/bg/tbl-sts.png') }}) left top repeat-x; }
        .tbl-sts b { background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat; display: block; height: 19px; overflow: hidden; width: 19px; }
        .tbl-sts-lt b { background-position: 0 -50px; }
        .tbl-sts-rt b { background-position: 0 -100px; }
        .tbl-sts-lb b { background-position: 0 -170px; }
        .tbl-sts-rb b { background-position: 0 -219px; }
        .tbl-sts-ltb b { background-position: 0 -69px; height: 20px; }
        .tbl-sts-lbt b { background-position: 0 -150px; height: 20px; }
        .tbl-sts-rtb b { background-position: 0 -119px; height: 20px; }
        .tbl-sts-rbt b { background-position: 0 -200px; height: 20px; }
        .tbl-sts_left { background-image: url({{ asset('img/bg/tbl-sts_left.gif') }}); background-repeat: repeat-y; width: 19px; background-position: right; }
        .tbl-sts_right { background-image: url({{ asset('img/bg/tbl-sts_right.gif') }}); background-repeat: repeat-y; width: 19px; }
        .bgg { background-image: url({{ asset('img/bg/bgg.gif') }}); }
        .location-frame-top { background-image: url({{ asset('img/bg/info/tbl-shp_sml-top.gif') }}); background-repeat: repeat-x; height: 22px; font-size: 1px; }
        .location-frame-left { background-image: url({{ asset('img/bg/info/tbl-usi_left.gif') }}); background-repeat: repeat-y; background-position: right; width: 20px; }
        .location-frame-bg { background-image: url({{ asset('img/bg/info/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .location-frame-right { background-image: url({{ asset('img/bg/info/tbl-usi_right.gif') }}); background-repeat: repeat-y; width: 20px; }
        .location-frame-bottom { background-image: url({{ asset('img/bg/info/tbl-shp_sml-bottom.gif') }}); background-repeat: repeat-x; height: 18px; font-size: 1px; }
        span.butt1 a.button, span.butt1.disabled a.button {
            height: 38px;
            display: inline-block;
            line-height: 35px;
            border: 0;
            color: #f8dea4 !important;
            cursor: pointer;
            font-family: Tahoma;
            font-size: 11px !important;
            font-weight: 700;
            text-decoration: none;
            margin: 0 33px;
            background: transparent url({{ asset('img/bg/btn/tbl-btn2_center.png') }}) center top repeat-x;
            padding-bottom: 3px;
            outline: none;
        }
    </style>
</head>
<body>
@php($moves = $page->moves)
<table class="main-table" width="100%" height="100%" style="height: 100%" cellpadding="10" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td style="vertical-align: top">
            <div class="center">
                <b><a href="" class="location-name">{{ $page->name }}</a></b> <span>({{ $page->locationId }})</span>
            </div>

            <div style="overflow:hidden;">
                @if($page->image)
                    <table width="420" border="0" cellspacing="0" cellpadding="0" style="float:right; margin:0 0 8px 10px;">
                        <tbody>
                        <tr height="22">
                            <td width="20" align="right" valign="bottom"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt="" style="display:block;"></td>
                            <td class="location-frame-top" valign="top" align="center">&nbsp;</td>
                            <td width="20" align="left" valign="bottom"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt="" style="display:block;"></td>
                        </tr>
                        <tr>
                            <td class="location-frame-left">&nbsp;</td>
                            <td class="location-frame-bg" align="center" valign="middle" style="padding:4px;">
                                <img src="{{ $page->image }}" width="100%" alt="{{ $page->name }}" style="display:block;width:100%;height:auto;">
                            </td>
                            <td class="location-frame-right">&nbsp;</td>
                        </tr>
                        <tr height="18">
                            <td width="20" align="right" valign="top"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt="" style="display:block;"></td>
                            <td class="location-frame-bottom" valign="top" align="center">&nbsp;</td>
                            <td width="20" align="left" valign="top"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt="" style="display:block;"></td>
                        </tr>
                        </tbody>
                    </table>
                @endif

                <div class="location-description">{{ $page->description }}</div>

            @if($page->dungeonSession !== null)
                <div style="background:#ffe8b0; border:1px solid #c90; padding:4px 6px; margin-bottom:6px; font-size:11px;">
                    <b>Данж:</b> {{ $page->dungeonSession->name }}
                    @if($page->dungeonSession->isSurvival && $page->dungeonSession->waveCount)
                        &nbsp;|&nbsp;
                        @if($page->dungeonSession->allCleared)
                            <b style="color:#2a8a2a;">Все волны пройдены! ✓</b>
                        @else
                            Волна <b>{{ $page->dungeonSession->currentWave }}</b> / {{ $page->dungeonSession->waveCount }}
                        @endif
                    @endif
                    @if($page->dungeonSession->expiresAtTimestamp !== null)
                        — осталось <b id="dungeon-timer-loc"></b>
                        <script>
                            (function() {
                                const exp = {{ $page->dungeonSession->expiresAtTimestamp }};
                                function tick() {
                                    const left = exp - Math.floor(Date.now() / 1000);
                                    const el = document.getElementById('dungeon-timer-loc');
                                    if (!el) return;
                                    if (left <= 0) { el.textContent = '00:00'; window.location.reload(); return; }
                                    const m = String(Math.floor(left / 60)).padStart(2, '0');
                                    const s = String(left % 60).padStart(2, '0');
                                    el.textContent = m + ':' + s;
                                }
                                tick();
                                setInterval(tick, 1000);
                            })();
                        </script>
                    @endif
                    @if($page->dungeonSession->canExit)
                        &nbsp;
                        <form method="POST" action="{{ $page->dungeonSession->exitUrl }}" style="display:inline;">
                            @csrf
                            <button type="submit" style="background:#a33; color:#fff; border:none; padding:2px 8px; cursor:pointer; font-size:11px;">Выйти из данжа</button>
                        </form>
                    @endif
                </div>
            @endif

            @if($page->hasBattle)
                <div class="battle-description">
                    <p><span class="color-red"><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>
                    <p><a href="{{ $page->battleUrl }}" id="attack">Продолжить</a> »</p>
                </div>
            @else
                @if($page->monsters !== [])
                    <div class="battle-description">
                        @foreach($page->monsters as $monster)
                            <div>
                                @if($monster->isBoss)
                                    <b>
                                        <a href="{{ $monster->infoUrl }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="color-red">{{ $monster->name }}</a>
                                    </b>
                                @else
                                    <a href="{{ $monster->infoUrl }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="color-red">{{ $monster->name }}</a>
                                @endif
                                [<a href="{{ $monster->attackUrl }}">атаковать</a>]
                            </div>
                        @endforeach
                    </div>
                @endif

                @foreach($page->npcs as $npc)
                    <div><a href="{{ $npc->infoUrl }}" onclick="window.open(this.href,'','width=730,height=650,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="color-red">{{ $npc->name }}</a> [<a href="{{ $npc->talkUrl }}">говорить</a>]</div>
                @endforeach

                <div class="side-move">
                    <table cellspacing="0" cellpadding="0" align="left" border="0">
                        <tbody>
                        <tr>
                            <td width="250px" style="vertical-align: top">
                                <table cellspacing="0" cellpadding="0" align="left" border="0" style="background:url({{ asset('img/dimension.gif') }}) no-repeat 39px 22px">
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        <td align="center" valign="top" height="37">
                                            @if($moves['up']->available)
                                                <a href="#" onclick="actionGoTo(this, 'up')" target="game">{{ $moves['up']->label }}</a>
                                            @else
                                                <font color="#B09A8B">{{ $moves['up']->label }}</font>
                                            @endif
                                        </td>
                                        <td valign="bottom">
                                            @if($moves['north']->available)
                                                <a href="#" onclick="actionGoTo(this, 'north')" id="{{ $moves['north']->elementId }}" target="game">{{ $moves['north']->label }}</a>
                                            @else
                                                <font color="#B09A8B">{{ $moves['north']->label }}</font>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="left" width="57">
                                            @if($moves['west']->available)
                                                <a href="#" onclick="actionGoTo(this, 'west')" id="{{ $moves['west']->elementId }}" target="game">{{ $moves['west']->label }}</a>
                                            @else
                                                <font color="#B09A8B">{{ $moves['west']->label }}</font>
                                            @endif
                                        </td>
                                        <td align="center" valign="middle" width="59" height="54"></td>
                                        <td width="68" align="right" style="padding:0px 10px 0px 0px">
                                            @if($moves['east']->available)
                                                <a href="#" onclick="actionGoTo(this, 'east')" id="{{ $moves['east']->elementId }}" target="game">{{ $moves['east']->label }}</a>
                                            @else
                                                <font color="#B09A8B">{{ $moves['east']->label }}</font>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td align="right" valign="top" height="29">
                                            @if($moves['south']->available)
                                                <a href="#" onclick="actionGoTo(this, 'south')" id="{{ $moves['south']->elementId }}" target="game">{{ $moves['south']->label }}</a>
                                            @else
                                                <font color="#B09A8B">{{ $moves['south']->label }}</font>
                                            @endif
                                        </td>
                                        <td align="center" valign="bottom">
                                            @if($moves['down']->available)
                                                <a href="#" onclick="actionGoTo(this, 'down')" target="game">{{ $moves['down']->label }}</a>
                                            @else
                                                <font color="#B09A8B">{{ $moves['down']->label }}</font>
                                            @endif
                                        </td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="padding:7px 0px 4px 0px">
                                            <a href="{{ $page->takeItemsUrl }}" id="take-item" target="game">
                                                Искать здесь @if($page->itemsOnLocationCount)({{ $page->itemsOnLocationCount }}) @endif
                                            </a> »
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td style="vertical-align: top">
                                @if($page->structures !== [] || $page->gateActions !== [])
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                            <td class="tbl-shp-sml tt" valign="top" align="center"></td>
                                            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                                        </tr>
                                        <tr>
                                            <td class="tbl-shp-sides ls">&nbsp;</td>
                                            <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 14px 0">
                                                @foreach($page->gateActions as $gateAction)
                                                    <div class="structures" style="margin: 5px">
                                                        <span class="butt1 pointer"><span><a href="{{ $gateAction->url }}" target="game" class="button">{{ $gateAction->label }}</a></span></span>
                                                    </div>
                                                @endforeach
                                                @foreach($page->structures as $structure)
                                                    <div class="structures" style="margin: 5px">
                                                        <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ $structure->entryUrl }}" type="submit">{{ $structure->name }}</button></span></span>
                                                        @if($structure->actions !== [])
                                                            <ul>
                                                                @foreach($structure->actions as $action)
                                                                    <li><a href="{{ $action->url }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->label }}</a></li>
                                                                @endforeach
                                                            </ul>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td class="tbl-shp-sides rs">&nbsp;</td>
                                        </tr>
                                        <tr height="18">
                                            <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
                                            <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
                                            <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                @endif
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            @endif
            </div>
        </td>
    </tr>
    </tbody>
</table>

<script>
    function handleKeydown(event) {
        if (event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        switch (event.key.toLowerCase()) {
            case 'arrowup':
                document.getElementById('move-north')?.click();
                break;
            case 'arrowdown':
                document.getElementById('move-south')?.click();
                break;
            case 'arrowleft':
                document.getElementById('move-west')?.click();
                break;
            case 'arrowright':
                document.getElementById('move-east')?.click();
                break;
            case 'f':
                document.getElementById('take-item')?.click();
                break;
            case 'i':
                sendDataToGame('{{ route('backpack') }}');
                break;
            case 'c':
                sendDataToGame('{{ route('character') }}');
                break;
            case ' ':
                document.getElementById('attack')?.click();
                break;
            default:
                return;
        }

        event.preventDefault();
    }

    document.addEventListener('keydown', handleKeydown);

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }

    const currentLocationId = {{ $page->locationId }};
    parent.sendToFrame('map-frame', { currentLocationId });

    (function () {
        const users = {!! $page->locationUsersJson !!};

        try {
            const chatFrame = window.parent.document.getElementById('chat-frame');
            if (!chatFrame) return;
            const whoFrame = chatFrame.contentDocument.getElementById('who-frame');
            if (!whoFrame) return;
            whoFrame.contentWindow.postMessage({ type: 'locationUsers', users: users }, '*');
        } catch (e) {}
    })();

    function actionGoTo(button, direction) {
        parent.queueAction(() => {
            parent.goTo(direction);
            parent.startCooldown();
        });
    }

    document.querySelectorAll('.shop').forEach(function(button) {
        button.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const buttons = document.querySelectorAll('.butt1.shop');
        const actions = document.querySelectorAll('.actions');
        let maxWidth = 0;

        buttons.forEach(function(button) {
            button.style.width = 'auto';
            maxWidth = Math.max(maxWidth, button.offsetWidth);
        });

        actions.forEach(function(action) {
            maxWidth = Math.max(maxWidth, action.offsetWidth);
        });

        buttons.forEach(function(button) {
            button.style.width = (maxWidth + 1) + 'px';
        });
    });

    let hp = { current: parseInt('{{ $page->playerFrame->hpCurrent }}'), max: parseInt('{{ $page->playerFrame->hpMax }}') };
    let mp = { current: parseInt('{{ $page->playerFrame->mpCurrent }}'), max: parseInt('{{ $page->playerFrame->mpMax }}') };
    let experience = parseFloat('{{ $page->playerFrame->experience }}');
    let lvl = parseInt('{{ $page->playerFrame->level }}');

    parent.sendToFrame('character-frame', { hp, mp, experience, lvl });

    @if(session('message'))
        try { window.parent.showErrorIframe('{{ session('message') }}'); } catch (e) {}
    @endif
</script>
</body>
</html>
