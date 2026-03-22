<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reputation->name }}</title>
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
        .progress-wrap {
            background: #c9a87c;
            border: 1px solid #8b5e30;
            height: 10px;
            border-radius: 3px;
            overflow: hidden;
            margin: 3px 0;
        }
        .progress-bar { background: #e8c04a; height: 100%; border-radius: 3px; }
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
                        @include('character.partials.tabs')
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
                                            <td align="center" class="tbl-usi_label-center">{{ $reputation->name }}</td>
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="coll w100 p6h p2v brd2-all">
                            <tbody>

                            @if($message)
                                <tr>
                                    <td colspan="3" class="{{ $messageType === 'success' ? 'msg-success' : 'msg-error' }}">
                                        {{ $message }}
                                    </td>
                                </tr>
                            @endif

                            {{-- Reputation Points & Progress --}}
                            <tr class="bg_l">
                                <td class="brd2-top brd2-bt" colspan="3" style="padding: 6px 10px;">
                                    <b>Очки репутации: {{ $pr->points }}</b>
                                    @if($currentTier)
                                        &nbsp;—&nbsp;<span style="color:#461C0B;">{{ $currentTier->medal_name ?? 'Без звания' }}</span>
                                        @php
                                            $nextTierItem = $reputation->tiers->firstWhere('min_points', '>', $pr->points);
                                            $tierMin  = $currentTier->min_points;
                                            $tierMax  = $nextTierItem ? $nextTierItem->min_points : ($currentTier->max_points ?? $pr->points);
                                            $range    = $tierMax - $tierMin;
                                            $progress = $range > 0 ? min(100, (($pr->points - $tierMin) / $range) * 100) : 100;
                                        @endphp
                                        <div class="progress-wrap">
                                            <div class="progress-bar" style="width: {{ round($progress) }}%"></div>
                                        </div>
                                        <small style="color:#555;">
                                            {{ $pr->points - $tierMin }} / {{ $range > 0 ? $range : '∞' }}
                                            @if($nextTierItem)
                                                &nbsp;(до «{{ $nextTierItem->medal_name ?? 'следующего уровня' }}»: {{ $nextTierItem->min_points - $pr->points }})
                                            @else
                                                &nbsp;(максимальный уровень)
                                            @endif
                                        </small>
                                    @else
                                        <small style="color:#888;">Нет доступного уровня</small>
                                    @endif
                                </td>
                            </tr>

                            {{-- Medals --}}
                            @php $tiersWithMedals = $reputation->tiers->filter(fn($t) => $t->medal_name); @endphp
                            @if($tiersWithMedals->count())
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" colspan="3" style="padding: 5px 10px;">
                                        <b style="font-size:11px; color:#461C0B;">Медали:</b><br>
                                        @foreach($tiersWithMedals as $tier)
                                            @if($pr->points >= $tier->min_points)
                                                <span class="medal-earned" title="Получена при {{ $tier->min_points }} очках">
                                                    @if($tier->medal_icon)🏅 @endif{{ $tier->medal_name }}
                                                </span>
                                            @else
                                                <span class="medal-locked" title="Откроется при {{ $tier->min_points }} очках">
                                                    🔒 {{ $tier->medal_name }} ({{ $tier->min_points }})
                                                </span>
                                            @endif
                                        @endforeach
                                    </td>
                                </tr>
                            @endif

                            {{-- Active Quest (read-only, no take button) --}}
                            @if($activeQuest)
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" width="1%">
                                        <img src="{{ asset('img/icon/qst_start_ro.gif') }}" width="46" height="28">
                                    </td>
                                    <td class="brd2-top brd2-bt">
                                        <b>{{ $activeQuest->quest->title }}</b>
                                        @foreach($activeQuest->quest->objectives as $obj)
                                            @php
                                                $done   = $progressMap[$obj->id] ?? 0;
                                                $isDone = $done >= $obj->required_amount;
                                            @endphp
                                            <br><small style="color: {{ $isDone ? '#2a7a2a' : '#555' }};">
                                                {{ $obj->description }} — {{ $done }}/{{ $obj->required_amount }}
                                            </small>
                                        @endforeach
                                    </td>
                                    <td class="brd2-top brd2-bt" align="right">
                                        <span style="color:#888; font-size:10px;">В процессе</span>
                                    </td>
                                </tr>
                            @elseif($cooldownDiff)
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" width="1%">
                                        <img src="{{ asset('img/icon/qst_start.gif') }}" width="46" height="28" style="opacity:0.45;">
                                    </td>
                                    <td class="brd2-top brd2-bt" style="color:#888;">
                                        Задание на перезарядке
                                        <br><small style="color:#999;">Доступно через: {{ $cooldownDiff }}</small>
                                    </td>
                                    <td class="brd2-top brd2-bt" align="right">
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
    @if($messageType === 'success' && $message)
        try {
            let experience = parseFloat('{{ $player->getPercentExp() }}');
            let lvl = parseInt('{{ $player->lvl }}');
            let hp = parseFloat('{{ $player->getPercentHp() }}');
            let mp = parseFloat('{{ $player->getPercentMp() }}');
            let money = parseInt('{{ $player->user->money }}');
            let diamond = parseInt('{{ $player->user->diamond }}');
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
        } catch (e) {}
    @endif
</script>

</body>
</html>
