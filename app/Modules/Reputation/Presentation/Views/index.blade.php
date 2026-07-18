<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->reputation->name }}</title>
    <style>
        html { height: 100%; }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        .w100 {width: 100%}
        a { color: #000000; }
        a:hover { color: #353434; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml {
            background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-shp-sides {
            background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-usi_bg {
            background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }});
            background-repeat: repeat;
        }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .tbl-usi_label-center {
            background-image: url(/img/bg/info/tbl-usi_label-center.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-weight: bold;
            font-size: 11px;
            color: #FCF5B7;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 6px;
        }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .bg_l2 { background-image: url(/img/bg/info/bg_l2.gif); cursor: pointer; }
        .brd2-bt { border-bottom: 1px solid #DB9F73; }
        .brd2-top { border-top: 1px solid #DB9F73; }
        .progress-bar { position: relative; width: 100%; height: 17px; margin: 4px 0; }
        .progress-bar__bg { position: absolute; right: 3px; left: 3px; top: 0; height: 17px; background: url(/img/bg/progress-bar.png) 0 -51px repeat-x; }
        .progress-bar__red { position: absolute; right: 3px; top: 0; height: 17px; background: url(/img/bg/progress-bar.png) 0 -68px repeat-x; }
        .progress-bar__cover { position: absolute; left: 20px; right: 20px; top: 0; height: 17px; background: url(/img/bg/progress-bar.png) 0 0 repeat-x; }
        .progress-bar__left { position: absolute; left: 0; top: 0; width: 20px; height: 17px; background: url(/img/bg/progress-bar.png) 0 -17px no-repeat; }
        .progress-bar__right { position: absolute; right: 0; top: 0; width: 20px; height: 17px; background: url(/img/bg/progress-bar.png) 0 -34px no-repeat; }
        .progress-bar__marker { position: absolute; top: 0; width: 5px; height: 17px; background: url(/img/bg/progress-bar.png) 0 -85px no-repeat; }
        .progress-bar__txt { position: absolute; left: 3px; right: 3px; top: 3px; color: #fff; font-size: 10px; text-align: center; text-shadow: -1px 0 2px #444444, 0 1px 2px #444444, 1px 0 2px #444444, 0 -1px 2px #444444, -1px 0 1px #640303, 0 1px 1px #640303, 1px 0 1px #640303, 0 -1px 1px #640303; }
        .medal-earned {
            display: inline-block;
            background: #f5e4a0;
            border: 1px solid #c8a430;
            border-radius: 3px;
            padding: 2px 6px;
            margin: 2px;
            font-size: 11px;
            font-weight: bold;
            color: #7a4e00;
        }
        .medal-locked {
            display: inline-block;
            background: #e0d8d0;
            border: 1px solid #b0a898;
            border-radius: 3px;
            padding: 2px 6px;
            margin: 2px;
            font-size: 11px;
            color: #999;
        }
        .msg-success { color: #2a7a2a; font-weight: bold; padding: 4px 6px; }
        .msg-error   { color: #a00000; font-weight: bold; padding: 4px 6px; }
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="left">
                        @include('player::partials.tabs', ['group' => 'reputation'])
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">

                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr height="22">
                                <td align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr height="22">
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                            <td align="center" class="tbl-usi_label-center">{{ $page->reputation->name }}</td>
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="coll w100 p6h p2v brd2-all">
                            <tbody>

                            @if($page->message)
                                <tr>
                                    <td colspan="3" class="{{ $page->messageType === 'success' ? 'msg-success' : 'msg-error' }}">
                                        {{ $page->message }}
                                    </td>
                                </tr>
                            @endif

                            {{-- Reputation Points & Progress --}}
                            <tr class="bg_l">
                                <td class="brd2-top brd2-bt" colspan="3" style="padding: 6px 10px;">
                                    <b>Очки репутации: {{ $page->pr->points }}</b>
                                    @if($page->currentTier)
                                        &nbsp;—&nbsp;<span style="color:#461C0B;">{{ $page->currentTier->medal_name ?? 'Без звания' }}</span>
                                        @php
                                            $nextTierItem = $page->reputation->tiers->firstWhere('min_points', '>', $page->pr->points);
                                            $tierMin  = $page->currentTier->min_points;
                                            $tierMax  = $nextTierItem ? $nextTierItem->min_points : ($page->currentTier->max_points ?? $page->pr->points);
                                            $range    = $tierMax - $tierMin;
                                            $pct      = $range > 0 ? min(100, round((($page->pr->points - $tierMin) / $range) * 100)) : 100;
                                        @endphp
                                        <div class="progress-bar">
                                            <div class="progress-bar__bg"></div>
                                            <div class="progress-bar__red" style="width: {{ 100 - $pct }}%;"></div>
                                            <div class="progress-bar__cover"></div>
                                            <div class="progress-bar__left"></div>
                                            <div class="progress-bar__right"></div>
                                            <div class="progress-bar__marker" style="right: {{ 100 - $pct }}%;"></div>
                                            <div class="progress-bar__txt" title="{{ $page->currentTier->medal_name ?? 'Без звания' }}">{{ $page->currentTier->medal_name ?? 'Без звания' }}: <span>{{ $page->pr->points - $tierMin }}/{{ $range > 0 ? $range : '∞' }}</span></div>
                                        </div>
                                        <small style="color:#555;">
                                            @if($nextTierItem)
                                                до «{{ $nextTierItem->medal_name ?? 'следующего уровня' }}»: {{ $nextTierItem->min_points - $page->pr->points }}
                                            @else
                                                (максимальный уровень)
                                            @endif
                                        </small>
                                    @else
                                        <small style="color:#888;">Нет доступного уровня</small>
                                    @endif
                                </td>
                            </tr>

                            {{-- Medals --}}
                            @php $tiersWithMedals = $page->reputation->tiers->filter(fn($t) => $t->medal_name); @endphp
                            @if($tiersWithMedals->count())
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" colspan="3" style="padding: 5px 10px;">
                                        <b style="font-size:11px; color:#461C0B;">Медали:</b><br>
                                        @foreach($tiersWithMedals as $tier)
                                            @php $isEarned = $page->earnedMedals->contains('id', $tier->id); @endphp
                                            @if($isEarned)
                                                <span class="medal-earned" title="Получена при {{ $tier->min_points }} очках{{ $tier->feat_quest_id ? ' и выполненном подвиге' : '' }}">
                                                    @if($tier->medal_icon)🏅 @endif{{ $tier->medal_name }}
                                                </span>
                                            @elseif($tier->feat_quest_id && $page->pr->points >= $tier->min_points)
                                                <span class="medal-locked" title="{{ $tier->feat_description ?? 'Выполните подвиг у НПС' }}">
                                                    ⚔ {{ $tier->medal_name }} — требуется подвиг
                                                </span>
                                            @else
                                                <span class="medal-locked" title="Откроется при {{ $tier->min_points }} очках{{ $tier->feat_quest_id ? '. Подвиг: ' . ($tier->feat_description ?? '') : '' }}">
                                                    🔒 {{ $tier->medal_name }} ({{ $tier->min_points }}@if($tier->feat_quest_id) + подвиг @endif)
                                                </span>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endif

                            {{-- Active Quest (read-only, no take button) --}}
                            @if($page->activeQuest)
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" width="1%" style="padding: 4px 8px;">
                                        <img src="{{ asset('img/icon/qst_start_ro.gif') }}" width="46" height="28">
                                    </td>
                                    <td class="brd2-top brd2-bt" style="padding: 4px 8px;">
                                        <b>{{ $page->activeQuest->quest->title }}</b>
                                        @foreach($page->activeQuest->quest->objectives as $obj)
                                            @php
                                                $done   = $page->progressMap[$obj->id] ?? 0;
                                                $isDone = $done >= $obj->required_amount;
                                            @endphp
                                            <br><small style="color: {{ $isDone ? '#2a7a2a' : '#555' }};">
                                                {{ $obj->description }} — {{ $done }}/{{ $obj->required_amount }}
                                            </small>
                                        @endforeach
                                    </td>
                                    <td class="brd2-top brd2-bt" align="right" style="padding: 4px 8px;">
                                        <span style="color:#888; font-size:10px;">В процессе</span>
                                    </td>
                                </tr>
                            @elseif($page->cooldownDiff)
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" width="1%" style="padding: 4px 8px;">
                                        <img src="{{ asset('img/icon/qst_start.gif') }}" width="46" height="28" style="opacity:0.45;">
                                    </td>
                                    <td class="brd2-top brd2-bt" style="color:#888; padding: 4px 8px;">
                                        Задание на перезарядке
                                        <br><small style="color:#999;">Доступно через: {{ $page->cooldownDiff }}</small>
                                    </td>
                                    <td class="brd2-top brd2-bt" align="right" style="padding: 4px 8px;">
                                        <span style="color:#aaa; font-size:10px;">Перезарядка</span>
                                    </td>
                                </tr>
                            @endif

                            {{-- Back to list --}}
                            <tr class="bg_l"
                                onclick="location.href='{{ route('reputation.list') }}'"
                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                <td class="brd2-top brd2-bt" width="1%" height="28"></td>
                                <td class="brd2-top brd2-bt">« Все репутации</td>
                                <td class="brd2-top brd2-bt" align="right"></td>
                            </tr>

                            </tbody>
                        </table>

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
        </td>
    </tr>
    </tbody>
</table>

<script>
    @if($page->messageType === 'success' && $page->message)
        try {
            let experience = parseFloat('{{ $page->player->getPercentExp() }}');
            let lvl = parseInt('{{ $page->player->lvl }}');
            let hp = { current: parseInt('{{ $page->player->hp_now }}'), max: parseInt('{{ $page->player->hp_max }}') };
            let mp = { current: parseInt('{{ $page->player->mp_now }}'), max: parseInt('{{ $page->player->mp_max }}') };
            let money = parseInt('{{ $page->player->user->money }}');
            let diamond = parseInt('{{ $page->player->user->diamond }}');
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
        } catch (e) {}
    @endif
</script>

</body>
</html>
