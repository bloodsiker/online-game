<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            padding: 0;
            color: #000000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        li {
            list-style-type: none;
            padding-left: 20px;
            background-image: url('{{ asset('img/icon/users-arrow.gif') }}');
            background-repeat: no-repeat;
            background-position: left center;
            background-size: 14px 12px;
        }
        .mb-5 {
            margin-bottom: 5px;
        }
        .color-red {
            color: red;
        }
        .color-green {
            color: green;
        }
        .color-info {
            color: #129df0;
        }
        .color-purple {
            color: purple;
        }
        a.r:link, a.r:visited {
            color: red;
        }
        .t1 {
            background: url({{ asset('img/bg/bg_l.gif') }});
        }
        .t0 {
            /*background-color: #ffffff;*/
            background: url({{ asset('img/bg/bg_l.gif') }});
            {{--background-image: url({{ asset('img/bg/tbl-main_chat-top.gif') }});--}}
            {{--background-repeat: repeat-x;--}}
            {{--height: 35px;--}}
        }
        .l0 {
            /*background-color: #a7a7a7;*/
            {{--background: url({{ asset('img/bg/bg_l.gif') }}) left top;--}}
            background: url({{ asset('img/bg/common-bg.png') }});
        }
        .b {
            /*background-color: #db9f73;*/
        }
        .tbgr {
{{--            background: url({{ asset('img/bg/bg_l.gif') }});--}}
        }
        .main-table {
            width: 100%;
            height: 100%;
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
        .achieve_bg {
            background: url({{ asset('img/bg/bg_l.gif') }}) left top;
        }
        .achieve_bg_lt {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_lt.jpg') }}) no-repeat left top;
        }
        .achieve_bg_tr {
            width: 100%;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_tr.jpg') }}) repeat-x left top;
        }
        .achieve_bg_rt {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_rt.jpg') }}) no-repeat left top;
        }
        .achieve_bg_lb {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_lb.jpg') }}) no-repeat left top;
        }
        .achieve_bg_br {
            width: 100%;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_br.jpg') }}) repeat-x left top;
        }
        .achieve_bg_rb {
            width: 10px;
            height: 10px;
            background: url({{ asset('img/bg/achieve_bg_rb.jpg') }}) no-repeat left top;
        }
        .achieve_bg_lr {
            background: url({{ asset('img/bg/achieve_bg_lr.jpg') }}) repeat-y left top;
        }
        .achieve_bg_rr {
            background: url({{ asset('img/bg/achieve_bg_rr.jpg') }}) repeat-y left top;
        }

        .brd2-all {
            /*border: 1px solid #db9f73;*/
        }



        .common-inset-2-tl, .common-inset-2-tr, .common-inset-2-bl, .common-inset-2-br {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-corners.png') }}) no-repeat;
        }
        .common-inset-2-t, .common-inset-2-b {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-tb.png') }}) repeat-x;
        }
        .common-inset-2-tl, .common-inset-2-tr, .common-inset-2-bl, .common-inset-2-br {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-corners.png') }}) no-repeat;
        }
        .common-inset-2-l, .common-inset-2-r {
            font-size: 0;
            letter-spacing: -1em;
            word-spacing: -1em;
            background: url({{ asset('img/bg/common-lr.png') }}) repeat-y;
        }
        .common-inset-2-tr {
            background-position: 100% 0;
        }
        .common-inset-2-t {
            background-position: 0 0;
        }
        .common-inset-2-tr {
            background-position: 100% 0;
        }
        .common-inset-2-bl {
            background-position: 0 100%;
        }
        .common-inset-2-br {
            background-position: 100% 100%;
        }

    </style>
</head>
<body>

<table class="main-table" cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        @if($battle)
            <td>
                @if($battle->status === 1)
                    <p><u><b>Раунд N {{ $battle->rounds++ }}</b></u> - <a href="{{ route('info.monster', ['id' => $randomAttackedMonster->locationMonster->id]) }}" target="_blank">{{ $randomAttackedMonster->locationMonster->monster->name }}</a> {{ $randomAttackedMonster->locationMonster->monster->lvl }} ({{ $randomAttackedMonster->locationMonster->hp_now }}/{{ $randomAttackedMonster->locationMonster->hp_max }})</p>

                    <table class="coll">
                        <tbody>
                        <tr>
                            <td width="8" height="10" class="common-inset-2-tl"></td>
                            <td height="10" class="common-inset-2-t"></td>
                            <td width="8" height="10" class="common-inset-2-tr"></td>
                        </tr>
                        <tr>
                            <td width="8" class="common-inset-2-l"></td>
                            <td class="common-inset-2-bg" valign="top">
                                <table cellspacing="0" cellpadding="0" border="0" class="b">
                                    <tbody>
                                    <tr>
                                        <td class="tbgr">
                                            <table cellspacing="1" cellpadding="5" border="0" width="100%" class="B">
                                                <tbody>
                                                <tr class="t1">
                                                    <td colspan="2">Выбeритe дeйствие для cлeдующeго paундa:</td>
                                                </tr>
                                                <tr valign="top" class="l0">
                                                    <td>
                                                        <li class="mb-5"><a href="#" onclick="actionAttack('{{ $battle->id }}', '{{ $randomAttackedMonster->locationMonster->id }}', 0)">Атакoвать oружием в рукaх</a>&nbsp;»</li>
                                                        @if($player->hasEquippedMagicSkill())
                                                            <li class="mb-5">Сотвopить заклинание:</li>
                                                            <div style="margin:0px 0px 0px 20px;">
                                                                @foreach($player->activeMagicSkills as $magicSkill)
                                                                    <li class="mb-5"> [{{ $magicSkill->mana_cost }}] <a href="#" onclick="actionAttack('{{ $battle->id }}', '{{ $randomAttackedMonster->locationMonster->id }}', '{{ $magicSkill->id }}')" id="s{{ $loop->index }}">{{ $magicSkill->name }}</a>&nbsp;»</li>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    </td>
                                                    <td>
                                                        <li><a href="{{ route('fight.run-away', ['id' => $battle->id]) }}">Убежать</a>&nbsp;»</li>
                                                    </td>
                                                </tr>
                                                <tr class="t0">
                                                    <td colspan="2">
                                                        3дoрoвьe: <b>{{ $player->hp_now }}/{{ $player->hp_max }}</b>, Энергия: <b>{{ $player->mp_now }}/{{ $player->mp_max }}</b>
                                                    </td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td width="8" class="common-inset-2-r"></td>
                        </tr>
                        <tr>
                            <td width="8" height="10" class="common-inset-2-bl"></td>
                            <td height="10" class="common-inset-2-b"></td>
                            <td width="8" height="10" class="common-inset-2-br"></td>
                        </tr>
                        </tbody>
                    </table>
                @endif

                <p><u><b>Раунд N {{ $round->round_number }}</b></u> - <a href="/info/?mid=491678816" target="_blank">{{ $round->locationMonster->monster->name }}</a> {{ $round->locationMonster->monster->lvl }}</p>

                {!! $round->action !!}

                @if($battle->status === 0)
                    <p><a href="{{ route('location') }}" id="finish-fight">Сражение завершено... Далее</a> »</p>
                @endif
            </td>

            @if($battle->status == 1)
                <td width="1%">
                    <table cellspacing="0" cellpadding="0" border="0" width="100%" height="100%" class="achieve_bg">
                        <tbody>
                        <tr>
                            <td class="achieve_bg_lt"></td>
                            <td class="achieve_bg_tr"></td>
                            <td class="achieve_bg_rt"></td>
                        </tr>
                        <tr>
                            <td class="achieve_bg_lr"></td>
                            <td style="vertical-align: top">
                                <table cellspacing="0" cellpadding="2" border="0">
                                    <tbody>
                                    <tr>
                                        <td><b>Противники</b> ({{ $battle->detailsWithMonsters->count() }}):</td>
                                    </tr>
                                    <tr>
                                        <td align="right">
                                            <nobr>
                                                @foreach($battle->detailsWithMonsters as $details)
                                                    @if($details->status == 1)
                                                        <small><a href="{{ route('info.monster', ['id' => $details->locationMonster->id]) }}" class="@if($randomAttackedMonster->locationMonster->id === $details->locationMonster->id) color-red @endif" target="_blank">{{ $details->locationMonster->monster->name }}</a> {{ $details->locationMonster->monster->lvl }} ({{ $details->locationMonster->hp_now }}/{{ $details->locationMonster->hp_max }})
                                                            {{ $details->updated_at->format('H:i:s') }}<br></small>
                                                    @endif
                                                @endforeach
                                            </nobr>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><br><b>Союзники</b> ({{ $battle->detailsWithUsers->count() }}):</td>
                                    </tr>
                                    <tr>
                                        <td align="right">
                                            <nobr>
                                                <small>
                                                    @foreach($battle->detailsWithUsers as $details)
                                                        @if($details->user)
                                                            <b><a href="{{ route('info.user', ['id' => $details->user->id]) }}" target="_blank">{{ $details->user->name }}</a> {{ $details->user->player->lvl }}</b> 15:40:39
                                                            <br>
                                                        @endif
                                                    @endforeach
                                                    <br>
                                                    <p><a href="/battle/?bid=53132233" target="_blank">История сражения</a> »</p>
                                                </small>
                                            </nobr>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td class="achieve_bg_rr"></td>
                        </tr>
                        <tr>
                            <td class="achieve_bg_lb"><img src="{{ asset('img/bg/null.gif') }}" width="10" height="10"></td>
                            <td class="achieve_bg_br"></td>
                            <td class="achieve_bg_rb"><img src="{{ asset('img/bg/null.gif') }}" width="10" height="10"></td>
                        </tr>
                        </tbody>
                    </table>

                    {{--            <script>--}}
                    {{--                function p72(h) {--}}
                    {{--                    if (h < 3648 * 0.25) {--}}
                    {{--                        return "<font color=#AA0000>" + Math.floor(h) + "</font>";--}}
                    {{--                    } else {--}}
                    {{--                        return Math.floor(h);--}}
                    {{--                    }--}}
                    {{--                }--}}

                    {{--                if (window.parent.hero) {--}}
                    {{--                    window.parent.hero.i7(100, 100, p72(3648), "756");--}}
                    {{--                    window.top.hero.spl5reset();--}}
                    {{--                }</script>--}}
                </td>
            @endif
        @else
            <p><a href="{{ route('location') }}" id="finish-fight">Сражение завершено... Далее</a> »</p>
        @endif
    </tr>
    </tbody>
</table>

<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'i':--}}
    {{--            window.parent.sendDataToGame('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            window.parent.sendDataToGame('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            var finishFightButton = document.getElementById('finish-fight');--}}
    {{--            if (finishFightButton) {--}}
    {{--                finishFightButton.click();--}}
    {{--            } else {--}}
    {{--                window.parent.sendDataToGame('{{ route('location') }}');--}}
    {{--            }--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}
</script>

<script>
    function actionAttack(id, monsterId, action) {
        if (parent.isCooldown) {
            console.log('⏳ Attack is blocked, cooldown is active...');
            return;
        }

        parent.attackMonster(id, monsterId, action);
        parent.startCooldown();
    }

    function buffOrDebuff() {
        if (Math.random() > 0.7) {
            const blessings = [
                { id: 'crit_' + Date.now(), name: 'Кровь Берсерка', duration: 15 },
                { id: 'rage_' + Date.now(), name: 'Покров Небес', duration: 20 },
                { id: 'regen_' + Date.now(), name: 'Регенерация', duration: 19 },
                { id: 'dux_' + Date.now(), name: 'Дыхание Леса', duration: 14 },
                { id: 'dobl' + Date.now(), name: 'Свет Доблести', duration: 21 }
            ];
            const blessing = blessings[Math.floor(Math.random() * blessings.length)];

            parent.sendToFrame('character-frame', { blessing });
        }

        if (Math.random() > 0.8) {
            const curses = [
                { id: 'burn_' + Date.now(), name: 'Горение', duration: 10 },
                { id: 'slow_' + Date.now(), name: 'Замедление', duration: 15 },
                { id: 'blind_' + Date.now(), name: 'Проклятие Хрупкости', duration: 21 },
                { id: 'slip_' + Date.now(), name: 'Слепота', duration: 8 },
                { id: 'book_' + Date.now(), name: 'Крига Страху', duration: 18 }
            ];
            const curse = curses[Math.floor(Math.random() * curses.length)];

            parent.sendToFrame('character-frame', { curse });
        }
    }

    buffOrDebuff();
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
    let money = parseInt('{{ $player->user->money }}');
    let diamond = parseInt('{{ $player->user->diamond }}');

    function playerAction() {
        parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
    }
    playerAction();
</script>

</body>
</html>
