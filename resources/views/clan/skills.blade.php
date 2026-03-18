<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Навыки клана</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma; font-size: 11px; }
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd, .brd td { border: 1px solid #C49485; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2, .brd2 td { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .skill-card {
            border: 1px solid #DB9F73;
            border-radius: 4px;
            margin-bottom: 6px;
            padding: 6px 8px;
            background-image: url(/img/bg/info/bg_l.gif);
        }
        .skill-card.skill-disabled { opacity: 0.65; }
        .skill-name { font-weight: bold; font-size: 12px; color: #461c0b; }
        .skill-level { color: #8D2616; font-weight: bold; }
        .skill-desc { color: #555; margin: 2px 0 4px; }
        .req-row { color: #666; margin-top: 2px; }
        .req-ok { color: #2a7a2a; font-weight: bold; }
        .req-fail { color: #a02020; font-weight: bold; }
        .effect-tag { color: #1a4d8a; font-weight: bold; }
        .msg-success { color: #2a7a2a; font-weight: bold; padding: 4px 8px; border: 1px solid #2a7a2a; display: inline-block; margin-bottom: 8px; }
        .msg-error   { color: #a02020; font-weight: bold; padding: 4px 8px; border: 1px solid #a02020; display: inline-block; margin-bottom: 8px; }
        .w100 { width: 100%; }
    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.skills'])

<table class="coll" width="100%" height="100%" border="0">
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                <td align="center" class="tbl-usi_label-center">Навыки клана</td>
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px;">

                        {{-- Clan info bar --}}
                        <table class="coll w100 p10h brd2-all" border="0" style="margin-bottom: 10px;">
                            <tbody>
                            <tr class="bg_l">
                                <td align="left" nowrap="" style="padding: 4px 0;">
                                    <b>Клан:</b> {{ $clan->name }}
                                    &nbsp;&nbsp;
                                    <b>Уровень:</b> {{ $clan->lvl }}
                                </td>
                                <td align="right" nowrap="" style="padding: 4px 0;">
                                    <b>Бонусные очки:</b>
                                    <b class="redd">{{ number_format($clan->points) }}</b>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        {{-- Flash messages --}}
                        @if(session('success'))
                            <div class="msg-success">{{ session('success') }}</div>
                        @endif
                        @if(session('message'))
                            <div class="msg-error">{{ session('message') }}</div>
                        @endif

                        {{-- Skills list --}}
                        @forelse($definitions as $def)
                            @php
                                $currentLevel = $learnedMap[$def->id] ?? 0;
                                $nextLevel    = $currentLevel + 1;
                                $isMaxed      = $currentLevel >= $def->max_level;
                                $nextLevelData = !$isMaxed ? $def->levels->firstWhere('level', $nextLevel) : null;

                                // Check requirements for next level
                                $canLearnThis = false;
                                $reqClanLvl   = false;
                                $reqPoints    = false;
                                $reqStone     = false;

                                if ($nextLevelData && !$isMaxed) {
                                    $reqClanLvl = $clan->lvl >= $nextLevelData->required_clan_level;
                                    $reqPoints  = $clan->points >= $nextLevelData->required_bonus_points;
                                    $reqStone   = !$nextLevelData->stone_share_item_id
                                        || $backpackShareItemIds->contains($nextLevelData->stone_share_item_id);
                                    $canLearnThis = $reqClanLvl && $reqPoints && $reqStone;
                                }

                                $currentLevelData = $currentLevel > 0 ? $def->levels->firstWhere('level', $currentLevel) : null;
                            @endphp

                            <div class="skill-card {{ (!$canLearnThis && !$currentLevel) ? 'skill-disabled' : '' }}">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        {{-- Icon --}}
                                        <td width="40" valign="top" style="padding-right: 8px;">
                                            @if($def->icon)
                                                <img src="{{ $def->icon }}" style="width:36px;height:36px;" alt="">
                                            @else
                                                <div style="width:36px;height:36px;background:#c8a06a;border:1px solid #db9f73;"></div>
                                            @endif
                                        </td>

                                        {{-- Info --}}
                                        <td valign="top">
                                            <span class="skill-name">{{ $def->name }}</span>
                                            &nbsp;
                                            <span class="skill-level">
                                                Ур. {{ $currentLevel }} / {{ $def->max_level }}
                                            </span>

                                            @if($currentLevelData)
                                                &nbsp;—&nbsp;
                                                <span class="effect-tag">
                                                    +{{ $currentLevelData->effect_value }}
                                                    {{ $currentLevelData->effect_type->label() }}
                                                </span>
                                            @endif

                                            <div class="skill-desc">{{ $def->description }}</div>

                                            @if($isMaxed)
                                                <span style="color:#2a7a2a;font-weight:bold;">&#10003; Максимальный уровень</span>
                                            @elseif($nextLevelData)
                                                <div class="req-row">
                                                    <b>Следующий уровень {{ $nextLevel }}:</b>
                                                    +{{ $nextLevelData->effect_value }}
                                                    {{ $nextLevelData->effect_type->label() }}
                                                </div>
                                                <div class="req-row">
                                                    Уровень клана:
                                                    <span class="{{ $reqClanLvl ? 'req-ok' : 'req-fail' }}">
                                                        {{ $clan->lvl }} / {{ $nextLevelData->required_clan_level }}
                                                    </span>
                                                    &nbsp;&nbsp;
                                                    Бонусные очки:
                                                    <span class="{{ $reqPoints ? 'req-ok' : 'req-fail' }}">
                                                        {{ number_format($clan->points) }} / {{ number_format($nextLevelData->required_bonus_points) }}
                                                    </span>
                                                    @if($nextLevelData->stoneItem)
                                                        &nbsp;&nbsp;
                                                        Камень «{{ $nextLevelData->stoneItem->name }}»:
                                                        <span class="{{ $reqStone ? 'req-ok' : 'req-fail' }}">
                                                            {{ $reqStone ? 'есть' : 'нет' }}
                                                        </span>
                                                    @endif
                                                </div>

                                                @if($canLearn)
                                                    <form method="post" action="{{ route('clan.skills.learn', $def->id) }}" style="margin-top:4px; display:inline;">
                                                        @csrf
                                                        <b class="butt2 pointer">
                                                            <b>
                                                                <input
                                                                    type="submit"
                                                                    value="Изучить"
                                                                    style="width:70px;"
                                                                    {{ !$canLearnThis ? 'disabled' : '' }}
                                                                >
                                                            </b>
                                                        </b>
                                                    </form>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div align="center" style="padding: 20px; color: #49382d;">
                                <b>Навыки клана пока не добавлены.</b>
                            </div>
                        @endforelse

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
    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case 'i': window.parent.sendDataToGame('{{ route('backpack') }}'); break;
            case 'c': window.parent.sendDataToGame('{{ route('character') }}'); break;
            case ' ': window.parent.sendDataToGame('{{ route('location') }}'); break;
            default: return;
        }
        event.preventDefault();
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>