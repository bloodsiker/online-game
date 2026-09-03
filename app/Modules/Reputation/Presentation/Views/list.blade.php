<!DOCTYPE html>
<html lang="ru">
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

        .rep-rating-summary {
            margin: 8px 12px 0;
            padding: 6px 10px;
            border: 1px solid #c9a16d;
            background: rgba(255, 237, 188, .72);
            color: #5d3b28;
            font-size: 11px;
            text-align: center;
        }
        .rep-rating-summary img { margin-right: 5px; vertical-align: middle; }
        .rep-rating-summary b { color: #9a2517; font-size: 12px; }

        .rep-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 14px;
            padding: 12px;
        }
        .rep-card {
            position: relative;
            min-height: 360px;
            border: 1px solid #b99a7c;
            box-shadow: 0 1px 0 #fff inset, 0 1px 3px rgba(67, 37, 17, .25);
            background: #ead8ba url({{ asset('img/bg/common-bg.png') }}) repeat;
            overflow: hidden;
            font-size: 11px;
            font-family: Tahoma, sans-serif;
        }
        .rep-card-header {
            position: relative;
            z-index: 2;
            display: table;
            width: fit-content;
            max-width: calc(100% - 52px);
            height: 22px;
            margin: 8px auto 0;
            padding: 0 24px;
            box-sizing: border-box;
            background: url({{ asset('img/bg/info/tbl-usi_label-center.gif') }}) repeat-x;
            color: #ffe9ba;
            line-height: 20px;
            text-align: center;
            text-shadow: 0 1px 1px #4b160c;
        }
        .rep-card-header::before,
        .rep-card-header::after {
            position: absolute;
            top: 0;
            width: 27px;
            height: 22px;
            content: '';
        }
        .rep-card-header::before {
            left: -23px;
            background: url({{ asset('img/bg/info/tbl-usi_label-left.gif') }}) no-repeat;
        }
        .rep-card-header::after {
            right: -23px;
            background: url({{ asset('img/bg/info/tbl-usi_label-right.gif') }}) no-repeat;
        }
        .rep-card-header a {
            position: relative;
            z-index: 1;
            display: block;
            overflow: hidden;
            color: #ffe9ba;
            font-weight: bold;
            font-size: 12px;
            text-decoration: none;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .rep-card-header a:hover { color: #fff4d6; }
        .rep-card-body {
            display: flex;
            min-height: 317px;
            padding: 13px 16px 12px;
            flex-direction: column;
            align-items: center;
        }
        .rep-emblem {
            display: flex;
            width: 60px;
            height: 60px;
            margin: 4px auto 8px;
            padding: 5px 6px 6px;
            align-items: center;
            justify-content: center;
            background: url({{ asset('main/images/user-reward-frame.png') }}) 0 0 no-repeat;
        }
        .rep-emblem img {
            display: block;
            max-width: 60px;
            max-height: 60px;
        }
        .rep-emblem-fallback {
            width: 52px;
            height: 52px;
            border: 2px solid #8b542e;
            border-radius: 50%;
            background: #ca9e64;
            color: #6b321e;
            font-size: 25px;
            font-weight: bold;
            line-height: 52px;
            text-align: center;
            text-shadow: 0 1px #f6deb0;
        }
        .rep-description {
            display: -webkit-box;
            min-height: 32px;
            margin-top: 4px;
            overflow: hidden;
            color: #5c4433;
            line-height: 16px;
            text-align: center;
            -webkit-box-orient: vertical;
            -webkit-line-clamp: 2;
        }
        .rep-divider {
            position: relative;
            width: 88%;
            height: 11px;
            margin: 7px 0 5px;
        }
        .rep-divider::before {
            position: absolute;
            top: 5px;
            right: 0;
            left: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #9a704f 18%, #9a704f 82%, transparent);
            content: '';
        }
        .rep-divider::after {
            position: absolute;
            top: 2px;
            left: 50%;
            width: 7px;
            height: 7px;
            border: 1px solid #895936;
            background: #d4aa70;
            content: '';
            transform: translateX(-50%) rotate(45deg);
        }
        .rep-progress-title {
            margin-bottom: 5px;
            color: #6c2f1f;
            font-size: 12px;
            font-weight: bold;
        }
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
            top: 0;
            left: 0;
            width: 100%;
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
        .rep-progress-bar__border-left {
            left: 0;
        }
        .rep-progress-bar__border-right {
            right: 0;
            background-position: 0 -31px;
        }
        .rep-progress-bar__border-center {
            margin: 0 20px;
            background-position: 0 -62px;
            background-repeat: repeat-x;
        }
        .rep-progress-bar__text {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            color: #fff;
            font-size: 11px;
            font-weight: bold;
            line-height: 31px;
            text-align: center;
            text-shadow: -1px 0 2px #444, 0 1px 2px #444, 1px 0 2px #444, 0 -1px 2px #444;
        }
        .rep-points {
            margin-top: 4px;
            color: #52392b;
            font-size: 10px;
        }
        .rep-points b { color: #8b2f1e; }
        .rep-status {
            width: 100%;
            margin-top: 7px;
            color: #594133;
            line-height: 16px;
            text-align: center;
        }
        .rep-status-current {
            color: #8b3a1a;
            font-weight: bold;
        }
        .rep-status-next { color: #745744; }
        .rep-npc { color: #745744; font-size: 10px; }
        .rep-btn {
            margin-top: auto;
            padding-top: 9px;
            white-space: nowrap;
        }
        @media (max-width: 680px) {
            .rep-list { grid-template-columns: 1fr; }
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
                        @include('player::partials.tabs', ['group' => 'reputation'])
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">

                        <div class="rep-rating-summary">
                            <img src="{{ asset('main/images/data/rating/rat_rep.png') }}" width="20" height="16" alt="">
                            Репутационный рейтинг: <b>{{ number_format($page->reputationRating, 0, '.', ' ') }}</b>
                        </div>

                        <div class="rep-list">
                            @forelse($page->playerReputations as $entry)
                                @php
                                    $rep         = $entry['reputation'];
                                    $pr          = $entry['pr'];
                                    $currentTier = $entry['currentTier'];
                                    $nextTier    = $entry['nextTier'];
                                    $maxPoints   = $nextTier?->min_points ?? $rep->tiers->max('min_points');
                                    $startPoints = $currentTier?->min_points ?? 0;
                                    $tierRange   = max(0, (int) $maxPoints - (int) $startPoints);
                                    $tierPoints  = max(0, (int) $pr->points - (int) $startPoints);
                                    $pct         = $nextTier && $tierRange > 0
                                        ? min((int) round($tierPoints * 100 / $tierRange), 100)
                                        : 100;
                                    $currentRank = $currentTier?->medal_name ?? 'Нейтральный';
                                    $emblemPath  = $rep->icon;
                                    if (! $emblemPath || ! is_file(public_path(ltrim($emblemPath, '/')))) {
                                        $emblemPath = $currentTier?->medal_icon ?? $nextTier?->medal_icon;
                                    }
                                @endphp
                                <div class="rep-card">
                                    <div class="rep-card-header">
                                        <a href="{{ route('reputation.index', $rep->id) }}">{{ $rep->name }}</a>
                                    </div>
                                    <div class="rep-card-body">
                                        <div class="rep-emblem">
                                            @if($emblemPath)
                                                <img src="{{ asset(ltrim($emblemPath, '/')) }}" alt="{{ $rep->name }}">
                                            @else
                                                <div class="rep-emblem-fallback">{{ mb_substr($rep->name, 0, 1) }}</div>
                                            @endif
                                        </div>

                                        @if($rep->description)
                                            <div class="rep-description" title="{{ $rep->description }}">{{ $rep->description }}</div>
                                        @endif

                                        <div class="rep-divider"></div>
                                        <div class="rep-progress-title">Прогресс репутации</div>

                                        <div class="rep-progress-bar" title="{{ $pr->points }} из {{ $maxPoints ?? '∞' }}">
                                            <div class="rep-progress-bar__bg">
                                                <div class="rep-progress-bar__fill" style="width: {{ $pct }}%;"></div>
                                            </div>
                                            <div class="rep-progress-bar__border">
                                                <div class="rep-progress-bar__border-left"></div>
                                                <div class="rep-progress-bar__border-right"></div>
                                                <div class="rep-progress-bar__border-center"></div>
                                            </div>
                                            <div class="rep-progress-bar__text">{{ $pct }}%</div>
                                        </div>

                                        <div class="rep-points"><b>{{ $pr->points }}</b> / {{ $maxPoints ?? '∞' }}</div>
                                        <div class="rep-status">
                                            <div class="rep-status-current">{{ $currentRank }}</div>
                                            @if($nextTier)
                                                <div class="rep-status-next">Следующий ранг: {{ $nextTier->medal_name }}</div>
                                            @else
                                                <div class="rep-status-next">Достигнут высший ранг</div>
                                            @endif
                                            @if($rep->npc)
                                                <div class="rep-npc">Наставник: {{ $rep->npc->name }}</div>
                                            @endif
                                        </div>

                                        <div class="rep-btn">
                                            <b class="butt1 pointer"><b>
                                                <input value="Подробнее" type="button"
                                                       onclick="location.href='{{ route('reputation.index', $rep->id) }}'"
                                                       style="width: 100px;">
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
