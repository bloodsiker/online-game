<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        ul {
            padding-left: 0;
        }
        ul li {
            list-style-type: none;
            margin-bottom: 3px;
        }
        ul li a img {
            margin-right: 6px;
            border: 0;
        }
        .main-table {
            width: 100%;
            height: 100%;
        }
        .center {
            text-align: center;
        }
        .location-name {

        }
        .location-description {
            margin-top: 15px;
            margin-bottom: 10px;
        }
        .battle-description {
            margin-top: 15px;
        }
        .side-move {
            margin-top: 30px;
        }
        .mt-10 {
            margin-top: 10px;
        }
        .color-red {
            color: red;
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
    </style>
</head>
<body>

<table class="main-table" width="100%" height="100%" style="height: 100%" cellpadding="10" cellspacing="0" border="0">
    <tbody>
    <tr>
        <td style="vertical-align: top">
            <div class="center">
                <b><a href="" class="location-name">{{ $location->name }}</a></b> <span>({{ $location->id }})</span>
            </div>

            <div class="location-description">
                {{ $location->description }}
            </div>

            @if($battle)
                <div class="battle-description">
                    <p><span class="color-red"><b>ВНИМАНИЕ!</b></span> <b>Вы атакованы!</b></p>

                    <p><a href="{{ route('fight', ['id' => $battle->id]) }}" id="attack">Продолжить</a> »</p>
                </div>
            @else
                @if($monsterOnLocation->count())
                    <div class="battle-description">
                        @foreach($monsterOnLocation as $monsterLocation)
                            <div><a href="{{ route('info.monster', ['id' => $monsterLocation->id]) }}" target="_blank" class="color-red">{{ $monsterLocation->monster->name }}</a> [<a href="{{ route('fight.attack.monster', ['id' => $monsterLocation->id]) }}">атаковать</a>]</div>
                        @endforeach
                    </div>
                @endif

                @if($location->npcs->count())
                    @foreach($location->npcs as $npc)
                            <div><a href="{{ route('info.npc', ['id' => $npc->id]) }}" target="_blank" class="color-red">{{ $npc->name }}</a> [<a href="{{ route('npc', ['id' => $npc->id]) }}">говорить</a>]</div>
                    @endforeach
                @endif

                <div class="side-move">
                    <table width="100%" cellspacing="0" cellpadding="0" align="left" border="0">
                        <tbody>
                        <tr>
                            <td width="250px" style="vertical-align: top">
                                <table cellspacing="0" cellpadding="0" align="left" border="0" style="background:url({{ asset('img/dimension.gif') }}) no-repeat 39px 22px">
                                    <tbody>
                                    <tr>
                                        <td></td>
                                        @if($location->up)
                                            <td align="center" valign="top" height="37"><a href="#" onclick="actionGoTo(this, 'up')" target="game">вверх</a></td>
                                        @else
                                            <td align="center" valign="top" height="37"><font color="#B09A8B">вверх</font></td>
                                        @endif

                                        @if($location->north)
                                            <td valign="bottom"><a href="#" onclick="actionGoTo(this, 'north')" id="move-north" target="game">север</a></td>
                                        @else
                                            <td valign="bottom"><font color="#B09A8B">север</font></td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($location->west)
                                            <td align="left" width="57"><a href="#" onclick="actionGoTo(this, 'west')" id="move-west" target="game">запад</a></td>
                                        @else
                                            <td align="left" width="57"><font color="#B09A8B">запад</font></td>
                                        @endif

                                        <td align="center" valign="middle" width="59" height="54"></td>

                                        @if($location->east)
                                            <td width="68" align="right" style="padding:0px 10px 0px 0px"><a href="#" onclick="actionGoTo(this, 'east')" id="move-east" target="game">восток</a></td>
                                        @else
                                            <td width="68" align="right" style="padding:0px 10px 0px 0px"><font color="#B09A8B">восток</font></td>
                                        @endif
                                    </tr>
                                    <tr>
                                        @if($location->south)
                                            <td align="right" valign="top" height="29"><a href="#" onclick="actionGoTo(this, 'south')"  id="move-south" target="game">юг</a></td>
                                        @else
                                            <td align="right" valign="top" height="29"><font color="#B09A8B">юг</font></td>
                                        @endif

                                        @if($location->down)
                                            <td align="center" valign="bottom"><a href="#" onclick="actionGoTo(this, 'down')" target="game">вниз</a></td>
                                        @else
                                            <td align="center" valign="bottom"><font color="#B09A8B">вниз</font></td>
                                        @endif

                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="padding:7px 0px 4px 0px"><a href="{{ route('take_items') }}" id="take-item" target="game">Искать здесь @if($location->itemsOnLocation()->count())({{ $location->itemsOnLocation()->count() }}) @endif</a> »</td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td style="vertical-align: top">
                                @if($location->structures->count())
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
                                                @foreach($location->structures as $structure)
                                                    @if($structure->isShop())
                                                        <div class="structures" style="margin: 5px">
                                                            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('shop', ['id' => $structure->id]) }}" type="submit">{{ $structure->name }}</button></span></span>
                                                            @if($structure->actions->count())
                                                                <ul>
                                                                    @foreach($structure->actions as $action)
                                                                        @if($action->alias === 'buy')
                                                                            <li><a href="{{ route('shop', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif

                                                                        @if($action->alias === 'sell')
                                                                            <li><a href="{{ route('shop.sell_item', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($structure->isWarehouse())
                                                        <div class="structures" style="margin: 5px">
                                                            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('warehouse', ['id' => $structure->id]) }}" type="submit">{{ $structure->name }}</button></span></span>
                                                            @if($structure->actions->count())
                                                                <ul>
                                                                    @foreach($structure->actions as $action)
                                                                        @if($action->alias === 'put_item')
                                                                            <li><a href="{{ route('warehouse', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif

                                                                        @if($action->alias === 'take_item')
                                                                            <li><a href="{{ route('warehouse.take_item', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($structure->isAuction())
                                                        <div class="structures" style="margin: 5px">
                                                            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('auction', ['id' => $structure->id]) }}" type="submit">{{ $structure->name }}</button></span></span>
                                                            @if($structure->actions->count())
                                                                <ul>
                                                                    @foreach($structure->actions as $action)
                                                                        @if($action->alias === 'auction_buy')
                                                                            <li><a href="{{ route('auction', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif

                                                                        @if($action->alias === 'auction_my_lot')
                                                                            <li><a href="{{ route('auction.my_lot', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($structure->isBlacksmith())
                                                        <div class="structures" style="margin: 5px">
                                                            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('blacksmith', ['id' => $structure->id]) }}" type="submit">{{ $structure->name }}</button></span></span>
                                                            @if($structure->actions->count())
                                                                <ul>
                                                                    @foreach($structure->actions as $action)
                                                                        @if($action->alias === 'kraft_item')
                                                                            <li><a href="{{ route('blacksmith', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    @endif

                                                    @if($structure->isHeal())
                                                        <div class="structures" style="margin: 5px">
                                                            <span class="butt1 pointer"><span><button class="butt1 shop" data-href="{{ route('heal', ['id' => $structure->id]) }}" type="submit">{{ $structure->name }}</button></span></span>
                                                            @if($structure->actions->count())
                                                                <ul>
                                                                    @foreach($structure->actions as $action)
                                                                        <li><a href="{{ route('heal', ['id' => $structure->id]) }}" class="actions"><img src="{{ asset('img/icon/users-arrow.gif') }}" alt="" align="absMiddle">{{ $action->name }}</a></li>
                                                                    @endforeach
                                                                </ul>
                                                            @endif
                                                        </div>
                                                    @endif
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
        </td>
    </tr>
    </tbody>
</table>

<script>
    function handleKeydown(event) {
        switch (event.key.toLowerCase()) {
            case 'arrowup':
                document.getElementById('move-north').click();
                break;
            case 'arrowdown':
                document.getElementById('move-south').click();
                break;
            case 'arrowleft':
                document.getElementById('move-west').click();
                break;
            case 'arrowright':
                document.getElementById('move-east').click();
                break;
            case 'f':
                document.getElementById('take-item').click();
                break;
            case 'i':
                sendDataToGame('{{ route('backpack') }}');
                break;
            case 'c':
                sendDataToGame('{{ route('character') }}');
                break;
            case ' ':
                document.getElementById('attack').click();
                break;
            default:
                return;
        }
        event.preventDefault();
    }

    document.addEventListener('keydown', handleKeydown);

    // document.removeEventListener('keydown', handleKeydown);

    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }
</script>

<script>
    function actionGoTo(button, direction) {
        if (parent.isCooldown) {
            console.log('⏳ Movement is blocked, cooldown is active...');
            return;
        }

        // Блок кнопки, щоб не натискати повторно
        // button.disabled = true;
        // button.style.opacity = '0.5';

        parent.goTo(direction);

        // Запускаємо cooldown
        parent.startCooldown();

        // Після завершення cooldown — розблокувати кнопку
        // parent.window.addEventListener('cooldown:end', () => {
        //     button.disabled = false;
        //     button.style.opacity = '1';
        // }, { once: true });


        // example cooldown event listeners
        // parent.window.addEventListener('cooldown:start', () => {
        //     console.log('Cooldown почався');
        //     const attackButton = document.getElementById('attackButton');
        //     if (attackButton) attackButton.disabled = true;
        // });
        //
        // parent.window.addEventListener('cooldown:end', () => {
        //     console.log('Cooldown завершився');
        //     const attackButton = document.getElementById('attackButton');
        //     if (attackButton) attackButton.disabled = false;
        // });
    }
</script>

<script>
    document.querySelectorAll('.shop').forEach(function(button) {
        button.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });

    document.addEventListener("DOMContentLoaded", function () {
        // Находим все кнопки с классом shop
        const buttons = document.querySelectorAll('.butt1.shop');
        const actions = document.querySelectorAll('.actions');

        let maxWidth = 0;

        buttons.forEach(function(button) {
            button.style.width = 'auto';
            const buttonWidth = button.offsetWidth;
            if (buttonWidth > maxWidth) {
                maxWidth = buttonWidth;
            }
        });

        actions.forEach(function(action) {
            const actionWidth = action.offsetWidth;
            if (actionWidth > maxWidth) {
                maxWidth = actionWidth;
            }
        });

        buttons.forEach(function(button) {
            button.style.width = (maxWidth + 1) + 'px';
        });
    });
</script>

<script>

    let hp = {
        current: parseInt('{{ $player->hp_now }}'),
        max: parseInt('{{ $player->hp_max }}')
    };
    let mp = {
        current: parseInt('{{ $player->mp_now }}'),
        max: parseInt('{{ $player->mp_max }}')
    };
    let experience = parseFloat('{{ $player->getPercentExp() }}');
    let lvl = parseInt('{{ $player->lvl }}');

    function playerAction() {
        // Пример изменения состояния игрока
        health = Math.max(0, health - 10);
        mp = Math.max(0, mp - 3);
        if (experience < 100) {
            experience += 10;
        }

        parent.sendToFrame('character-frame', { hp, mp, experience, lvl });
    }

    const currentLocationId = {{ auth()->user()->location_id }};
    parent.sendToFrame('map-frame', { currentLocationId });
</script>

</body>
</html>
