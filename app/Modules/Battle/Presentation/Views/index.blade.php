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

        /* Action panel */
        .act-panel {
            min-width: 198px;
            padding: 7px;
            color: #3b260d;
            background: #f4dc9b;
            border: 1px solid #a8762d;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 238, .78),
                inset 0 10px 18px rgba(255, 255, 245, .38),
                0 2px 0 rgba(112, 73, 21, .35);
            box-sizing: border-box;
        }
        .act-title {
            position: relative;
            margin: 0 0 7px;
            padding: 0 0 5px 18px;
            color: #4b2b08;
            font-size: 11px;
            font-weight: bold;
            line-height: 1.25;
            text-shadow: 0 1px 0 rgba(255, 242, 194, .8);
            border-bottom: 1px solid rgba(141, 90, 24, .55);
        }
        .act-title:before {
            content: "";
            position: absolute;
            left: 1px;
            top: -1px;
            width: 12px;
            height: 12px;
            background: radial-gradient(circle at 50% 45%, #ffe08a 0 22%, #ad5a20 24% 48%, #2b1309 51% 100%);
            border: 1px solid #9a6b2c;
            transform: rotate(45deg);
            box-shadow: 0 0 6px rgba(255, 245, 172, .5);
        }
        .act-btn {
            position: relative;
            display: block;
            width: 100%;
            min-height: 25px;
            margin-bottom: 4px;
            padding: 5px 7px 5px 28px;
            color: #2f1b07;
            font-family: Tahoma;
            font-size: 12px;
            font-weight: bold;
            line-height: 1.2;
            text-align: left;
            text-decoration: none;
            text-shadow: 0 1px 0 rgba(255, 232, 168, .75);
            cursor: pointer;
            border: 1px solid #8c5f22;
            border-radius: 2px;
            background:
                linear-gradient(to bottom, rgba(255, 255, 242, .78), rgba(255, 246, 210, .12) 44%),
                linear-gradient(to bottom, #ffe49b, #dca558);
            box-shadow:
                inset 0 0 0 1px rgba(255, 252, 213, .45),
                0 1px 0 rgba(81, 45, 10, .5);
            box-sizing: border-box;
        }
        .act-btn:before {
            position: absolute;
            left: 7px;
            top: 50%;
            width: 14px;
            height: 14px;
            margin-top: -7px;
            color: #1c0b04;
            font-size: 11px;
            font-weight: bold;
            line-height: 13px;
            text-align: center;
            text-shadow: 0 1px 0 rgba(255, 230, 166, .4);
            border: 1px solid rgba(255, 219, 128, .42);
            border-radius: 50%;
            background: linear-gradient(to bottom, #d69b3d, #8a411b);
            box-shadow: inset 0 1px 0 rgba(255, 244, 192, .45);
        }
        .act-btn:hover {
            color: #1f1003;
            border-color: #9b4d18;
            background:
                linear-gradient(to bottom, rgba(255, 255, 235, .82), rgba(255, 244, 190, 0) 42%),
                linear-gradient(to bottom, #ffe28f, #dda047);
            box-shadow:
                inset 0 0 0 1px rgba(255, 250, 205, .6),
                0 0 8px rgba(173, 93, 21, .28);
        }
        .act-btn-attack { border-left: 3px solid #d3311a; }
        .act-btn-attack:before { content: "\2694"; }
        .act-btn-spell  {
            border-left: 3px solid #6f5ae8;
            background:
                linear-gradient(to bottom, rgba(245, 240, 255, .68), rgba(212, 205, 255, 0) 42%),
                linear-gradient(to bottom, #c5b2f0, #8567c9);
        }
        .act-btn-spell:before { content: "\2726"; }
        .act-btn-spell:hover {
            background:
                linear-gradient(to bottom, rgba(255, 252, 255, .82), rgba(225, 219, 255, 0) 42%),
                linear-gradient(to bottom, #dacdff, #9677da);
        }
        .act-btn-flee {
            color: #493826;
            font-weight: normal;
            border-color: #9c8466;
            border-left: 3px solid #9a8878;
            background:
                linear-gradient(to bottom, rgba(255, 255, 255, .55), rgba(255, 255, 255, 0) 45%),
                linear-gradient(to bottom, #e7d9be, #bca27f);
        }
        .act-btn-flee:before { content: "\2190"; background: linear-gradient(to bottom, #b9aa90, #766454); }
        .act-btn-flee:hover { color: #2f2113; background: linear-gradient(to bottom, #f4e8cf, #cbb18c); }
        .act-spell-label {
            margin: 6px 0 3px;
            padding-left: 3px;
            color: #553987;
            font-size: 11px;
            font-weight: bold;
            text-shadow: 0 1px 0 rgba(255, 245, 220, .85);
        }
        .act-mana {
            display: inline-block;
            min-width: 16px;
            margin-right: 5px;
            padding: 1px 5px;
            color: #eef5ff;
            font-size: 10px;
            font-weight: normal;
            text-align: center;
            vertical-align: middle;
            border: 1px solid #516ead;
            border-radius: 8px;
            background: linear-gradient(to bottom, #254baf, #132a71);
            box-shadow: inset 0 1px 0 rgba(194, 218, 255, .35);
        }
        .act-stats {
            margin-top: 7px;
            padding-top: 5px;
            border-top: 1px solid rgba(141, 90, 24, .45);
        }
        .act-stat-row { display: flex; align-items: center; gap: 5px; margin-bottom: 4px; }
        .act-stat-label { width: 54px; flex-shrink: 0; color: #5a3711; font-size: 11px; text-shadow: 0 1px 0 rgba(255, 238, 190, .8); }
        .act-stat-bar {
            flex: 1;
            min-width: 56px;
            height: 8px;
            padding: 1px;
            overflow: hidden;
            background: #8b6429;
            border: 1px solid #9b6d2b;
            box-shadow: inset 0 1px 3px rgba(36, 18, 4, .65);
            box-sizing: border-box;
        }
        .act-stat-hp { height: 100%; background: linear-gradient(to right, #811306, #e23b1d 62%, #ff8c4a); box-shadow: 0 0 5px rgba(224, 42, 20, .45); }
        .act-stat-mp { height: 100%; background: linear-gradient(to right, #16398f, #2b78e8 62%, #82c6ff); box-shadow: 0 0 5px rgba(48, 116, 232, .45); }
        .act-stat-val { min-width: 44px; color: #4c3212; font-size: 10px; text-align: right; white-space: nowrap; }

        /* Battle side panel */
        .bp-wrap { min-width: 140px; padding: 6px 8px; }
        .bp-hdr {
            font-size: 11px;
            font-weight: bold;
            padding: 2px 0 3px;
            margin-bottom: 4px;
            border-bottom: 1px solid #8b6914;
            white-space: nowrap;
        }
        .bp-hdr-enemy { color: #cc2200; }
        .bp-hdr-ally  { color: #1a6a1a; margin-top: 8px; }
        .bp-unit {
            font-size: 11px;
            padding: 3px 0;
            border-bottom: 1px dotted #c8a87040;
            white-space: nowrap;
        }
        .bp-unit-target { background: rgba(180,0,0,0.07); border-radius: 2px; padding-left: 2px; }
        .bp-target-arrow { color: #cc2200; font-size: 10px; margin-right: 1px; }
        .bp-unit-name { font-size: 11px; }
        .bp-unit-lvl { color: #777; font-size: 10px; margin-left: 3px; }
        .bp-hp-row { display: flex; align-items: center; gap: 4px; margin-top: 2px; }
        .bp-hp-row + .bp-hp-row { margin-top: 0px; }
        .bp-hp-bar {
            flex: 1;
            height: 5px;
            background: #3a2a1a;
            border-radius: 3px;
            overflow: hidden;
            min-width: 50px;
        }
        .bp-hp-fill {
            height: 100%;
            background: linear-gradient(to right, #b81a00, #ff4422);
            border-radius: 3px;
        }
        .bp-hp-fill.hp-high { background: linear-gradient(to right, #c43000, #ff5533); }
        .bp-hp-fill.hp-mid  { background: linear-gradient(to right, #cc6600, #ff9900); }
        .bp-hp-fill.hp-low  { background: linear-gradient(to right, #660000, #cc0000); }
        .bp-hp-fill.hp-ally { background: linear-gradient(to right, #1a6600, #33aa00); }
        .bp-mp-fill { height: 100%; background: linear-gradient(to right, #1133aa, #2255dd); border-radius: 3px; }
        .bp-hp-text { font-size: 8px; color: #888; white-space: nowrap; }
        .bp-unit-time { font-size: 10px; color: #888; margin-left: 4px; }
        .bp-footer {
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px dotted #8b6914;
            font-size: 10px;
            text-align: center;
        }

    </style>
</head>
<body>

<table class="main-table" cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        @if($battle)
            <td>
                @if($battle->status->isActive())
                    <p><u><b>Раунд N {{ $battle->rounds }}</b></u> - <a href="{{ route('info.monster', ['id' => $randomAttackedMonster->locationMonster->id]) }}" target="_blank">{{ $randomAttackedMonster->locationMonster->monster->name }}</a> {{ $randomAttackedMonster->locationMonster->monster->lvl }} ({{ $randomAttackedMonster->locationMonster->hp_now }}/{{ $randomAttackedMonster->locationMonster->hp_max }})</p>

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
                                <div class="act-panel">
                                    <div class="act-title">Выберите действие для следующего раунда:</div>

                                    <a href="#" class="act-btn act-btn-attack" onclick="actionAttack('{{ $battle->id }}', '{{ $randomAttackedMonster->locationMonster->id }}', 0); return false;">Атаковать оружием в руках</a>

                                    @if($player->hasEquippedMagicSkill())
                                        <div class="act-spell-label">Сотворить заклинание:</div>
                                        @foreach($player->activeMagicSkills as $magicSkill)
                                            <a href="#" class="act-btn act-btn-spell" id="s{{ $loop->index }}" onclick="actionAttack('{{ $battle->id }}', '{{ $randomAttackedMonster->locationMonster->id }}', '{{ $magicSkill->id }}'); return false;"><span class="act-mana">{{ $magicSkill->mana_cost }}</span>{{ $magicSkill->name }}</a>
                                        @endforeach
                                    @endif

                                    <a href="{{ route('fight.run-away', ['id' => $battle->id]) }}" class="act-btn act-btn-flee">Убежать</a>

                                    @php
                                        $hpPct = $playerDecorator->getHpMax() > 0 ? round(($player->hp_now / $playerDecorator->getHpMax()) * 100) : 0;
                                        $mpPct = $playerDecorator->getMpMax() > 0 ? round(($player->mp_now / $playerDecorator->getMpMax()) * 100) : 0;
                                    @endphp
                                    <div class="act-stats">
                                        <div class="act-stat-row">
                                            <span class="act-stat-label">Здоровье:</span>
                                            <div class="act-stat-bar"><div class="act-stat-hp" style="width:{{ $hpPct }}%"></div></div>
                                            <span class="act-stat-val">{{ $player->hp_now }}/{{ $playerDecorator->getHpMax() }}</span>
                                        </div>
                                        <div class="act-stat-row">
                                            <span class="act-stat-label">Энергия:</span>
                                            <div class="act-stat-bar"><div class="act-stat-mp" style="width:{{ $mpPct }}%"></div></div>
                                            <span class="act-stat-val">{{ $player->mp_now }}/{{ $playerDecorator->getMpMax() }}</span>
                                        </div>
                                    </div>
                                </div>
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

                @if($battle->status->isFinish())
                    <p><a href="{{ route('location') }}" id="finish-fight">Сражение завершено... Далее</a> »</p>
                @endif
            </td>

            @if($battle->status->isActive())
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
                                <div class="bp-wrap">

                                    {{-- Enemies --}}
                                    <div class="bp-hdr bp-hdr-enemy">Противники ({{ $battle->detailsWithMonsters->count() }})</div>
                                    @foreach($battle->detailsWithMonsters as $details)
                                        @if($details->status->isLife())
                                            @php
                                                $isTarget = $randomAttackedMonster->locationMonster->id === $details->locationMonster->id;
                                                $hpPct    = $details->locationMonster->hp_max > 0
                                                    ? round(($details->locationMonster->hp_now / $details->locationMonster->hp_max) * 100)
                                                    : 0;
                                                $hpClass  = $hpPct > 60 ? 'hp-high' : ($hpPct > 30 ? 'hp-mid' : 'hp-low');
                                            @endphp
                                            <div class="bp-unit{{ $isTarget ? ' bp-unit-target' : '' }}">
                                                <div class="bp-unit-name">
                                                    @if($isTarget)<span class="bp-target-arrow">&#9658;</span>@endif
                                                    <a href="{{ route('info.monster', ['id' => $details->locationMonster->id]) }}" target="_blank" class="{{ $isTarget ? 'color-red' : '' }}">{{ $details->locationMonster->monster->name }}</a><span class="bp-unit-lvl">{{ $details->locationMonster->monster->lvl }}</span>
                                                </div>
                                                <div class="bp-hp-row">
                                                    <div class="bp-hp-bar"><div class="bp-hp-fill {{ $hpClass }}" style="width:{{ $hpPct }}%"></div></div>
                                                    <span class="bp-hp-text">{{ $details->locationMonster->hp_now }}/{{ $details->locationMonster->hp_max }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    {{-- Allies --}}
                                    <div class="bp-hdr bp-hdr-ally">Союзники ({{ $battle->detailsWithUsers->count() }})</div>
                                    @foreach($battle->detailsWithUsers as $details)
                                        @if($details->user)
                                            @php
                                                $allyHpPct = $details->user->player->hp_max > 0 ? round(($details->user->player->hp_now / $details->user->player->hp_max) * 100) : 0;
                                                $allyMpPct = $details->user->player->mp_max > 0 ? round(($details->user->player->mp_now / $details->user->player->mp_max) * 100) : 0;
                                            @endphp
                                            <div class="bp-unit" @if($details->user->id === auth()->id()) id="bp-unit-me" @endif>
                                                <div class="bp-unit-name">
                                                    <b><a href="{{ route('info.user', ['id' => $details->user->id]) }}" target="_blank">{{ $details->user->name }}</a></b><span class="bp-unit-lvl">[{{ $details->user->player->lvl }}]</span><span class="bp-unit-time">{{ $details->updated_at->format('H:i:s') }}</span>
                                                </div>
                                                <div class="bp-hp-row">
                                                    <div class="bp-hp-bar"><div class="bp-hp-fill hp-ally" style="width:{{ $allyHpPct }}%"></div></div>
                                                    <span class="bp-hp-text">{{ $details->user->player->hp_now }}/{{ $details->user->player->hp_max }}</span>
                                                </div>
                                                <div class="bp-hp-row">
                                                    <div class="bp-hp-bar"><div class="bp-mp-fill" style="width:{{ $allyMpPct }}%"></div></div>
                                                    <span class="bp-hp-text">{{ $details->user->player->mp_now }}/{{ $details->user->player->mp_max }}</span>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach

                                    <div class="bp-footer">
                                        <a href="/battle/?bid={{ $battle->id }}" target="_blank">История сражения &raquo;</a>
                                    </div>

                                </div>
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

<script>
    function actionAttack(id, monsterId, action) {
        if (parent.isCooldown) {
            console.log('⏳ Attack is blocked, cooldown is active...');
            return;
        }

        parent.attackMonster(id, monsterId, action);
        parent.startCooldown();
    }
</script>

</body>
</html>
