<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Репутации</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
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

        .rep-list { padding: 10px; display: flex; flex-direction: column; gap: 8px; }
        .rep-card {
            border: 1px solid #CEBBAA;
            border-radius: 4px;
            overflow: hidden;
            font-size: 11px;
            font-family: Tahoma, sans-serif;
        }
        .rep-card-header {
            background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left #DFBBA3;
            border-bottom: 1px solid #ccc;
            padding: 5px 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .rep-card-header a {
            color: #461c0b;
            font-weight: bold;
            font-size: 12px;
            text-decoration: none;
        }
        .rep-card-header a:hover { text-decoration: underline; }
        .rep-card-header .npc-name { color: #5a3a2a; font-size: 10px; }
        .rep-card-body {
            background-image: url({{ asset('img/bg/common-bg.png') }});
            background-repeat: repeat;
            padding: 7px 10px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .rep-card-body .progress-bar { flex: 1; }
        .rep-medal {
            font-size: 10px;
            color: #8b3a1a;
            font-weight: bold;
            white-space: nowrap;
        }
        .rep-btn {
            white-space: nowrap;
        }
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

                        <div class="rep-list">
                            @forelse($playerReputations as $entry)
                                @php
                                    $rep         = $entry['reputation'];
                                    $pr          = $entry['pr'];
                                    $currentTier = $entry['currentTier'];
                                    $nextTier    = $entry['nextTier'];
                                    $maxPoints   = $nextTier?->min_points ?? $rep->tiers->max('min_points');
                                    $pct = $maxPoints > 0 ? min(round($pr->points * 100 / $maxPoints), 100) : 100;
                                @endphp
                                <div class="rep-card">
                                    <div class="rep-card-header">
                                        <a href="{{ route('reputation.index', $rep->id) }}">{{ $rep->name }}</a>
                                        @if($rep->npc)
                                            <span class="npc-name">{{ $rep->npc->name }}</span>
                                        @endif
                                    </div>
                                    <div class="rep-card-body">
                                        <div class="progress-bar">
                                            <div class="progress-bar__bg"></div>
                                            <div class="progress-bar__red" style="width: {{ 100 - $pct }}%;"></div>
                                            <div class="progress-bar__cover"></div>
                                            <div class="progress-bar__left"></div>
                                            <div class="progress-bar__right"></div>
                                            <div class="progress-bar__marker" style="right: {{ 100 - $pct }}%;"></div>
                                            <div class="progress-bar__txt" title="{{ $currentTier?->medal_name ?? 'Нейтральный' }}">{{ $currentTier?->medal_name ?? 'Нейтральный' }}: <span>{{ $pr->points }}/{{ $maxPoints ?? '∞' }}</span></div>
                                        </div>

                                        <style>
                                            .progress-bar {
                                                position: relative;
                                                width: 100%;
                                                height: 17px;
                                            }
                                            .progress-bar__bg {
                                                position: absolute;
                                                right: 3px;
                                                left: 3px;
                                                top: 0;
                                                height: 17px;
                                                background: url(/img/bg/progress-bar.png) 0 -51px repeat-x;
                                            }
                                            .progress-bar__red {
                                                position: absolute;
                                                right: 3px;
                                                top: 0;
                                                height: 17px;
                                                background: url(/img/bg/progress-bar.png) 0 -68px repeat-x;
                                            }
                                            .progress-bar__cover {
                                                position: absolute;
                                                left: 20px;
                                                right: 20px;
                                                top: 0;
                                                height: 17px;
                                                background: url(/img/bg/progress-bar.png) 0 0 repeat-x;
                                            }
                                            .progress-bar__left {
                                                position: absolute;
                                                left: 0;
                                                top: 0;
                                                width: 20px;
                                                height: 17px;
                                                background: url(/img/bg/progress-bar.png) 0 -17px no-repeat;
                                            }
                                            .progress-bar__right {
                                                position: absolute;
                                                right: 0;
                                                top: 0;
                                                width: 20px;
                                                height: 17px;
                                                background: url(/img/bg/progress-bar.png) 0 -34px no-repeat;
                                            }
                                            .progress-bar__marker {
                                                position: absolute;
                                                top: 0;
                                                width: 5px;
                                                height: 17px;
                                                background: url(/img/bg/progress-bar.png) 0 -85px no-repeat;
                                            }
                                            .progress-bar__txt {
                                                position: absolute;
                                                left: 3px;
                                                right: 3px;
                                                top: 3px;
                                                color: #fff;
                                                font-size: 10px;
                                                text-align: center;
                                                text-shadow: -1px 0 2px #444444, 0 1px 2px #444444, 1px 0 2px #444444, 0 -1px 2px #444444, -1px 0 1px #640303, 0 1px 1px #640303, 1px 0 1px #640303, 0 -1px 1px #640303;
                                            }
                                            .user-pets-detail-exp .progress-bar .progress-bar__txt span {
                                                font-weight: normal;
                                                color: #fff;
                                                font-size: 10px;
                                            }
                                        </style>


                                        <div class="rep-btn">
                                            <b class="butt2 pointer"><b>
                                                <input value="Подробнее" type="button"
                                                       onclick="location.href='{{ route('reputation.index', $rep->id) }}'">
                                            </b></b>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="padding:10px; color:#888; font-size:11px;">Репутации не найдены.</div>
                            @endforelse
                        </div>

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

</body>
</html>
