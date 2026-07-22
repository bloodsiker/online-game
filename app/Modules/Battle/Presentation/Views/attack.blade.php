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
        .color-buff {
            color: #16a085;
            font-weight: bold;
        }

        .color-boss {
            color: #ff4444;
            font-weight: bold;
        }

        .color-shield {
            color: #4da6ff;
        }

        .color-enrage {
            color: #ff0000;
        }

        .color-skill {
            color: #9b59b6;
        }

        .color-damage {
            color: #e74c3c;
            font-size: 1.1em;
        }

        .color-debuff {
            color: #e67e22;
        }

        .color-life-drain {
            color: #8b0000;
            text-shadow: 0 0 5px rgba(139, 0, 0, 0.5);
        }

        .color-reflect {
            color: #4169e1;
            font-weight: bold;
        }

        .color-immunity {
            color: #c2a402;
            text-shadow: 0 0 10px rgba(255, 215, 0, 0.6);
        }

        .color-berserk {
            color: #dc143c;
            font-weight: bold;
            animation: pulse 1s infinite;
        }

        .color-mirror {
            color: #9370db;
            font-style: italic;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .color-damage-to-heal {
            color: #32cd32;
            font-weight: bold;
            text-shadow: 0 0 8px rgba(50, 205, 50, 0.6);
            animation: heal-pulse 1.5s ease-in-out infinite;
        }

        @keyframes heal-pulse {
            0%, 100% {
                opacity: 1;
                text-shadow: 0 0 8px rgba(50, 205, 50, 0.6);
            }
            50% {
                opacity: 0.8;
                text-shadow: 0 0 15px rgba(50, 205, 50, 0.9);
            }
        }

        .color-damage-to-heal {
            color: #32cd32;
            font-weight: bold;
            text-shadow: 0 0 8px rgba(50, 205, 50, 0.6);
            animation: heal-pulse 1.5s ease-in-out infinite;
        }

        @keyframes heal-pulse {
            0%, 100% {
                opacity: 1;
                text-shadow: 0 0 8px rgba(50, 205, 50, 0.6);
            }
            50% {
                opacity: 0.8;
                text-shadow: 0 0 15px rgba(50, 205, 50, 0.9);
            }
        }

        .msg-levelup {
            border-left: 3px solid #cff44f;
            background: #f9ffe8;
            padding: 3px 6px;
            color: #3a5200;
            font-weight: bold;
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
    @include('battle::partials.fight_styles')
</head>
<body>

<table class="main-table" cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        @if($battle)
            <td>
                @if($battle->status->isActive())
                    <p><u><b>Раунд N {{ $battle->rounds + 1 }}</b></u> - <a href="{{ route('info.monster', ['id' => $randomAttackedMonster->locationMonster->id]) }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $randomAttackedMonster->locationMonster->monster->name }}</a> {{ $randomAttackedMonster->locationMonster->monster->lvl }} ({{ $randomAttackedMonster->locationMonster->hp_now }}/{{ $randomAttackedMonster->locationMonster->hp_max }})</p>

                    @include('battle::partials.action_panel')
                @endif

                <p><u><b>Раунд N {{ $round->round_number }}</b></u> - <a href="/info/?mid=491678816" target="_blank">{{ $round->locationMonster->monster->name }}</a> {{ $round->locationMonster->monster->lvl }}</p>

                {!! $round->action !!}

                {{-- Разовые уведомления (прогресс квестов и т.п.) — только сейчас, не сохраняются и не всплывают в истории боя --}}
                {!! $fightDTO->getSideLog() !!}

                @if($battle->status->isFinish())
                    <p><a href="{{ route('location') }}" id="finish-fight">Сражение завершено... Далее</a> »</p>
                @endif
            </td>

            @if($battle->status->isActive())
                <td width="30%" valign="top">
                    @include('battle::partials.side_panel')
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
        parent.queueAction(() => {
            parent.attackMonster(id, monsterId, action);
            parent.startCooldown();
        });
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
        max: parseInt('{{ $playerDecorator->getHpMax() }}')
    };
    let mp = {
        current: parseInt('{{ $player->mp_now }}'),
        max: parseInt('{{ $playerDecorator->getMpMax() }}')
    };
    let experience = parseFloat('{{ $player->getPercentExp() }}');
    let lvl = parseInt('{{ $player->lvl }}');
    let money = parseInt('{{ $player->user->money }}');
    let diamond = parseInt('{{ $player->user->diamond }}');

    function playerAction() {
        parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
    }
    playerAction();

    window.addEventListener('message', function(e) {
        if (!e.data || !e.data.hp || !e.data.mp) return;

        hp.current = e.data.hp.current;
        hp.max     = e.data.hp.max;
        mp.current = e.data.mp.current;
        mp.max     = e.data.mp.max;

        const hpPct = hp.max > 0 ? Math.round(hp.current / hp.max * 100) : 0;
        const mpPct = mp.max > 0 ? Math.round(mp.current / mp.max * 100) : 0;

        // Основной блок статов персонажа
        document.querySelector('.act-stat-hp').style.width = hpPct + '%';
        document.querySelector('.act-stat-mp').style.width = mpPct + '%';

        const vals = document.querySelectorAll('.act-stat-val');
        if (vals[0]) vals[0].textContent = hp.current + '/' + hp.max;
        if (vals[1]) vals[1].textContent = mp.current + '/' + mp.max;

        // Блок союзника (текущий игрок)
        const meUnit = document.getElementById('bp-unit-me');
        if (meUnit) {
            const fills = meUnit.querySelectorAll('.bp-hp-fill, .bp-mp-fill');
            const texts = meUnit.querySelectorAll('.bp-hp-text');
            if (fills[0]) fills[0].style.width = hpPct + '%';
            if (fills[1]) fills[1].style.width = mpPct + '%';
            if (texts[0]) texts[0].textContent = hp.current + '/' + hp.max;
            if (texts[1]) texts[1].textContent = mp.current + '/' + mp.max;
        }
    });
</script>

</body>
</html>
