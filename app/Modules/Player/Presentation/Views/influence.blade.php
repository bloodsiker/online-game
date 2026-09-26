<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Влияние</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; }
        body {
            margin: 0;
            color: #45382f;
            font: 11px Tahoma, Arial, sans-serif;
        }
        a { color: #765039; }
        a:hover { color: #4e3425; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt { height: 22px; background-position: 0 -25px; }
        .tbl-shp-sml.tt { height: 22px; background-position: center -50px; background-repeat: repeat-x; }
        .tbl-shp-sml.lt { height: 22px; background-position: 0 0; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { height: 18px; background-position: center -125px; background-repeat: repeat-x; }
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
            background: #e8dac7 url({{ asset('img/bg/tbl-usi_bg.gif') }}) repeat;
        }
        .btn_1, .btn_2 {
            font-size: 11px;
            font-weight: 700;
            text-decoration: none;
        }
        .btn_1 { color: #461c0b !important; }
        .btn_2 { color: #ffe9ba !important; }
        .influence-intro {
            margin: 8px 12px 0;
            padding: 7px 10px;
            border: 1px solid #c9a16d;
            background: rgba(255, 237, 188, .72);
            color: #5d3b28;
            text-align: center;
        }
        .influence-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
            padding: 12px;
        }
        .influence-card {
            min-height: 292px;
            overflow: hidden;
            border: 1px solid #b99a7c;
            background: #ead8ba url({{ asset('img/bg/common-bg.png') }}) repeat;
            box-shadow: inset 0 1px #fff, 0 1px 3px rgba(67, 37, 17, .25);
        }
        .influence-card__title {
            position: relative;
            display: table;
            max-width: calc(100% - 54px);
            height: 22px;
            margin: 8px auto 0;
            padding: 0 25px;
            box-sizing: border-box;
            background: url({{ asset('img/bg/info/tbl-usi_label-center.gif') }}) repeat-x;
            color: #ffe9ba;
            font-size: 12px;
            font-weight: bold;
            line-height: 20px;
            text-align: center;
            text-shadow: 0 1px #4b160c;
            white-space: nowrap;
        }
        .influence-card__title::before,
        .influence-card__title::after {
            position: absolute;
            top: 0;
            width: 27px;
            height: 22px;
            content: '';
        }
        .influence-card__title::before {
            left: -23px;
            background: url({{ asset('img/bg/info/tbl-usi_label-left.gif') }}) no-repeat;
        }
        .influence-card__title::after {
            right: -23px;
            background: url({{ asset('img/bg/info/tbl-usi_label-right.gif') }}) no-repeat;
        }
        .influence-card__title a {
            position: relative;
            z-index: 1;
            display: block;
            overflow: hidden;
            color: #ffe9ba;
            text-decoration: none;
            text-overflow: ellipsis;
        }
        .influence-card__body {
            display: flex;
            min-height: 245px;
            padding: 13px 16px;
            box-sizing: border-box;
            flex-direction: column;
            align-items: center;
        }
        .influence-medal {
            display: flex;
            width: 60px;
            height: 60px;
            margin: 3px auto 8px;
            padding: 5px 6px 6px;
            align-items: center;
            justify-content: center;
            background: url({{ asset('main/images/user-reward-frame.png') }}) no-repeat;
        }
        .influence-medal img {
            display: block;
            max-width: 60px;
            max-height: 60px;
        }
        .influence-level {
            color: #8b3a1a;
            font-size: 12px;
            font-weight: bold;
            text-align: center;
        }
        .influence-points {
            margin: 5px 0 7px;
            color: #5b4333;
        }
        .influence-points b { color: #8b2f1e; font-size: 12px; }
        .rep-progress-bar {
            position: relative;
            width: 100%;
            height: 31px;
            overflow: hidden;
        }
        .rep-progress-bar__bg {
            height: 27px;
            margin: 2px 5px 0;
            overflow: hidden;
            border-radius: 5px;
            background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -54px repeat-x;
        }
        .rep-progress-bar__fill {
            height: 27px;
            background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -27px repeat-x;
        }
        .rep-progress-bar__border {
            position: absolute;
            inset: 0;
            height: 31px;
        }
        .rep-progress-bar__border-left,
        .rep-progress-bar__border-right,
        .rep-progress-bar__border-center {
            height: 31px;
            background: url({{ asset('img/progressbar/progress-bar-1-border.png') }}) no-repeat;
        }
        .rep-progress-bar__border-left,
        .rep-progress-bar__border-right {
            position: absolute;
            top: 0;
            width: 20px;
        }
        .rep-progress-bar__border-left { left: 0; }
        .rep-progress-bar__border-right { right: 0; background-position: 0 -31px; }
        .rep-progress-bar__border-center {
            margin: 0 20px;
            background-position: 0 -62px;
            background-repeat: repeat-x;
        }
        .rep-progress-bar__text {
            position: absolute;
            inset: 0;
            color: #fff;
            font-weight: bold;
            line-height: 31px;
            text-align: center;
            text-shadow: -1px 0 2px #444, 0 1px 2px #444, 1px 0 2px #444, 0 -1px 2px #444;
        }
        .influence-next {
            margin-top: 7px;
            color: #745744;
            text-align: center;
        }
        .influence-actions { margin-top: auto; padding-top: 10px; text-align: center; }
        .influence-actions a { font-weight: bold; }
        .influence-empty {
            margin: 18px;
            padding: 30px 15px;
            border: 1px solid #c2aa8d;
            background: rgba(255, 248, 231, .55);
            color: #725a49;
            text-align: center;
        }
        @media (max-width: 680px) {
            .influence-list { grid-template-columns: 1fr; }
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
                        @include('player::partials.tabs', ['group' => 'influence'])
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0;">
                        <div class="influence-intro">
                            Влияние отражает ваши заслуги на территориях и открывает местные награды и возможности.
                        </div>

                        @if($mapInfluences->isEmpty())
                            <div class="influence-empty">
                                Вы ещё не получали влияние. Участвуйте в событиях на картах, чтобы заработать первые очки.
                            </div>
                        @else
                            <div class="influence-list">
                                @foreach($mapInfluences as $mapInfluence)
                                    @php
                                        $currentLevel = $mapInfluence->currentLevel;
                                        $nextLevel = $mapInfluence->nextLevel;
                                        $start = (int) ($currentLevel?->required_influence ?? 0);
                                        $end = (int) ($nextLevel?->required_influence ?? $mapInfluence->influence);
                                        $range = max(0, $end - $start);
                                        $progress = max(0, (int) $mapInfluence->influence - $start);
                                        $percent = $nextLevel && $range > 0
                                            ? min(100, (int) round($progress * 100 / $range))
                                            : 100;
                                        $medalUrl = $currentLevel?->medal?->iconUrl();
                                    @endphp
                                    <div class="influence-card">
                                        <div class="influence-card__title">
                                            @if($mapInfluence->map)
                                                <a href="{{ route('character.influence.show', $mapInfluence->map) }}">{{ $mapInfluence->map->name }}</a>
                                            @else
                                                Карта удалена
                                            @endif
                                        </div>
                                        <div class="influence-card__body">
                                            <div class="influence-medal" title="{{ $currentLevel?->medal?->description ?? $currentLevel?->name ?? 'Медаль ещё не получена' }}">
                                                <img src="{{ $medalUrl ?: asset('img/bg/empty_slot.gif') }}" width="60" height="60" alt="">
                                            </div>
                                            <div class="influence-level">{{ $currentLevel?->name ?? 'Без уровня' }}</div>
                                            <div class="influence-points">Влияние: <b>{{ number_format($mapInfluence->influence, 0, '', ' ') }}</b></div>

                                            <div class="rep-progress-bar" title="{{ $mapInfluence->influence }} из {{ $nextLevel?->required_influence ?? $mapInfluence->influence }}">
                                                <div class="rep-progress-bar__bg">
                                                    <div class="rep-progress-bar__fill" style="width: {{ $percent }}%;"></div>
                                                </div>
                                                <div class="rep-progress-bar__border">
                                                    <div class="rep-progress-bar__border-left"></div>
                                                    <div class="rep-progress-bar__border-right"></div>
                                                    <div class="rep-progress-bar__border-center"></div>
                                                </div>
                                                <div class="rep-progress-bar__text">{{ $percent }}%</div>
                                            </div>

                                            <div class="influence-next">
                                                @if($nextLevel)
                                                    Следующий уровень: <b>{{ $nextLevel->name }}</b><br>
                                                    Нужно влияния: {{ number_format($nextLevel->required_influence, 0, '', ' ') }}
                                                @else
                                                    Достигнут максимальный уровень влияния
                                                @endif
                                            </div>
                                            @if($mapInfluence->map)
                                                <div class="influence-actions">
                                                    <a href="{{ route('character.influence.show', $mapInfluence->map) }}">Подробнее об уровнях и бонусах »</a>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
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
