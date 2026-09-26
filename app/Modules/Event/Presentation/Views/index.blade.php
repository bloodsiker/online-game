<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>События</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    {!! $itemTooltipScript ?? '' !!}
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
    <style>
        html, body { height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; }

        /* ── Внутренняя рамка контента (tbl-shp-sml) ── */
        .tbl-shp-sml { background: url(/img/bg/tbl-shp-sml.png) no-repeat; font-size: 0; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sides { background: url(/img/bg/tbl-shp-sides.png) no-repeat; font-size: 0; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-usi_bg { background-image: url(/img/bg/tbl-usi_bg.gif); background-repeat: repeat; }
        .tbl-usi-hdr { background: url(/main/images/tbl-usi-hdr.gif) no-repeat; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2-top { border-top: 1px solid #DB9F73; }
        .brd2-bt { border-bottom: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }

        /* ── Карточки активностей ── */
        .user-rewards__item-pic {
            position: relative;
            width: 60px;
            height: 60px;
            margin: 0 auto;
            padding: 5px 6px 6px;
            background: url(/main/images/user-reward-frame.png) 0 0 no-repeat;
        }
        .user-rewards__item-image {
            display: block;
            width: 60px;
            height: 60px;
            object-fit: contain;
        }
        .user-rewards__item-overlay {
            position: absolute;
            top: 5px;
            left: 6px;
            z-index: 1;
            width: 60px;
            height: 60px;
        }
        .store-grid { font-size: 0; margin: -6px; }
        .store-grid .store-item {
            display: inline-block;
            vertical-align: top;
            width: 360px;
            margin: 6px;
            box-sizing: border-box;
        }
        .store-item {
            display: inline-block;
            width: 360px;
            font-size: 11px;
            background-image: url(/img/bg/bgg.gif);
            background-repeat: repeat;
            border-radius: 5px;
            box-shadow: 0 0 3px rgba(0,0,0,.9);
            overflow: hidden;
        }
        .tab-content-items {
            position: relative;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 10px;
            padding: 5px;
            border: 3px solid #e3b360;
            border-radius: 6px;
            background: url(/img/bg/tbl-usi_bg.gif);
        }
        .activity-spoiler-row td { padding: 5px; }
        .activity-spoiler-btn {
            display: block;
            width: 100%;
            text-align: left;
            cursor: pointer;
            font-weight: bold;
            color: #553e20;
            text-decoration: none;
            font-size: 14px;
            line-height: 24px;
            background: url(/img/bg/tbl-usi_bg.gif);
            border: 1px solid #d0a35a;
            border-radius: 4px;
        }
        .activity-spoiler-btn:hover { filter: brightness(1.08); }
        .activity-spoiler-btn img { vertical-align: middle; margin-right: 6px; }
        @keyframes activityRewardReadyPulse {
            0%   { box-shadow: 0 0 3px rgba(0,0,0,.8), 0 0 6px rgba(0,180,40,.45); }
            50%  { box-shadow: 0 0 4px rgba(0,0,0,.8), 0 0 14px rgba(0,220,60,.95), 0 0 26px rgba(0,220,60,.65); }
            100% { box-shadow: 0 0 3px rgba(0,0,0,.8), 0 0 6px rgba(0,180,40,.45); }
        }
        .store-item.activity-reward-ready { animation: activityRewardReadyPulse 1.25s infinite ease-in-out; }
        .bpdig {
            border: solid 1px #6f4a24;
            background-color: #6e534c;
            width: 32px;
            height: 14px;
            color: #f6d9a6;
            font-weight: bold;
            margin: 2px;
            text-align: center;
            position: relative;
            top: 2px;
            left: -1px;
            font-size: 11px;
        }
        .world-events { width: 1020px; max-width: 100%; margin: 0 auto; }
        .world-event-card { margin: 0 0 14px; color: #3d2b20; scroll-margin: 16px; }
        .world-event-card--highlighted { animation: worldEventCardHighlight 4s ease-out; }
        @keyframes worldEventCardHighlight {
            0%, 35% { filter: brightness(1.13); box-shadow: 0 0 0 3px rgba(177, 42, 17, .72), 0 0 16px rgba(177, 42, 17, .55); }
            100% { filter: brightness(1); box-shadow: 0 0 0 0 rgba(177, 42, 17, 0); }
        }
        .world-event-card__head { height: 22px; text-align: center; }
        .world-event-card__favorite {
            display: inline-block;
            width: 19px;
            height: 18px;
            margin: 0 5px 0 0;
            padding: 0;
            border: 0;
            font-size: 0;
            line-height: 0;
            background: url(/main/images/favorite.png) center bottom no-repeat;
            cursor: pointer;
            vertical-align: -4px;
        }
        .world-event-card__favorite:hover,
        .world-event-card__favorite--active { background-position: center top; }
        .world-event-card__favorite:disabled { cursor: wait; opacity: .55; }
        .world-event-card__body { display: flex; gap: 14px; align-items: flex-start; padding: 12px 16px 16px; background: url(/img/bg/tbl-usi_bg.gif) repeat; }
        .world-event-card__image.user-rewards__item-pic {
            flex: 0 0 200px;
            width: 200px;
            height: 200px;
            min-height: 200px;
            padding: 10px 12px 12px;
            border: 0;
            background-size: 224px 222px;
            text-align: center;
        }
        .world-event-card__image img { display: block; width: 200px; height: 200px; object-fit: contain; }
        .world-event-card__content { flex: 1; min-width: 0; }
        .world-event-card__content--future { display: flex; align-items: flex-start; gap: 14px; }
        .world-event-card__content-main { flex: 1 1 auto; min-width: 0; }
        .world-event-card__title {
            margin: 0 0 9px;
            padding: 0 0 5px;
            border-bottom: 1px solid #c69862;
            color: #7d2415;
            font-size: 15px;
            line-height: 1.25;
            text-align: left;
            text-shadow: 0 1px rgba(255,255,255,.75);
        }
        .world-event-card__content p { margin: 0 0 7px; }
        .world-event-card__stage { color: #6f351f; }
        .world-event-card__target-list { display:flex; flex-wrap:wrap; gap:8px; margin:5px 0 9px; }
        .world-event-card__target { width:76px; color:#542f1c; text-align:center; text-decoration:none; cursor:pointer; }
        .world-event-card__target .user-rewards__item-pic { display:block; }
        .world-event-card__meta { color: #70401e; }
        .world-event-card__influence { margin: 9px 0; padding: 7px 9px; border: 1px solid #c69862; background: rgba(255,245,213,.6); }
        .world-event-card__locations { color: #685142; }
        .world-event-timer { display: inline-flex; align-items: stretch; min-width: 260px; margin: 8px 0 4px; overflow: hidden; border: 1px solid #6d3b1e; border-radius: 4px; background: #f3dfb2; box-shadow: inset 0 0 0 1px #efd394, 0 1px 3px rgba(48,24,10,.45); }
        .world-event-timer__label { padding: 7px 12px; color: #f9e8b8; font-weight: bold; background: linear-gradient(#8b3a1f, #5f2414); text-shadow: 0 1px #2c1009; }
        .world-event-timer__value { flex: 1; min-width: 96px; padding: 6px 13px; color: #6f1d12; font-size: 14px; font-weight: bold; letter-spacing: 1px; text-align: center; text-shadow: 0 1px #fff2ce; }
        .world-event-timer__value::before { content: '⌛'; margin-right: 6px; color: #8b5b25; font-size: 11px; }
        .world-event-timer--start .world-event-timer__label { background: linear-gradient(#647a2d, #394d19); }
        .world-event-timer--start .world-event-timer__value { color: #42551c; }
        .world-event-card__future-side {
            flex: 0 0 280px;
            min-width: 0;
        }
        .world-event-card__future-summary {
            box-sizing: border-box;
            width: 100%;
            padding: 9px;
            border: 1px solid #c69862;
            border-radius: 4px;
            background: rgba(255,245,213,.62);
            box-shadow: inset 0 0 0 1px rgba(255,255,255,.45);
            text-align: center;
        }
        .world-event-card__future-summary .world-event-timer {
            display: flex;
            width: 100%;
            min-width: 0;
            box-sizing: border-box;
            margin: 0 0 9px;
        }
        .world-event-card__future-limits {
            margin: 0 !important;
            color: #5f321d;
            line-height: 1.6;
        }
        .world-event-card__future-side > .world-event-card__influence {
            margin: 9px 0 0;
            text-align: left;
        }
        .event-influence-list { max-width: 650px; margin: 12px auto; color: #3d2b20; }
        .event-influence-list__intro { margin: 0 0 10px; text-align: center; color: #68452d; }
        .event-influence-table { width: 100%; border-collapse: collapse; background: url(/img/bg/tbl-usi_bg.gif) repeat; }
        .event-influence-table th { padding: 7px 10px; color: #f7e5ae; background: #75402a; border: 1px solid #5b2d1d; text-shadow: 0 1px #32170f; }
        .event-influence-table td { padding: 8px 12px; border: 1px solid #c99c69; }
        .event-influence-table tbody tr:nth-child(even) td { background: rgba(255,244,210,.35); }
        .event-influence-table__value { width: 170px; color: #7d2415; font-size: 13px; font-weight: bold; text-align: center; }
        .world-event-progress { margin: 7px 0 3px; }
        .world-event-progress__label { display: flex; justify-content: space-between; font-weight: bold; }
        .rep-progress-bar { position: relative; width: 100%; height: 31px; overflow: hidden; }
        .rep-progress-bar__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; border-radius: 5px; background: url(/img/progressbar/progress-bar-1-bg.png) 0 -54px repeat-x; }
        .rep-progress-bar__fill { height: 27px; background: url(/img/progressbar/progress-bar-1-bg.png) 0 -27px repeat-x; }
        .rep-progress-bar__border { position: absolute; inset: 0; height: 31px; }
        .rep-progress-bar__border-left,.rep-progress-bar__border-right,.rep-progress-bar__border-center { height: 31px; background: url(/img/progressbar/progress-bar-1-border.png) no-repeat; }
        .rep-progress-bar__border-left,.rep-progress-bar__border-right { position: absolute; top: 0; width: 20px; }
        .rep-progress-bar__border-left { left: 0; }.rep-progress-bar__border-right { right: 0; background-position: 0 -31px; }
        .rep-progress-bar__border-center { margin: 0 20px; background-position: 0 -62px; background-repeat: repeat-x; }
        .rep-progress-bar__text { position: absolute; inset: 0; color: #fff; font-weight: bold; line-height: 31px; text-align: center; text-shadow: 0 1px 2px #333; }
        @media(max-width:760px){
            .world-event-card__body{display:block}
            .world-event-card__image.user-rewards__item-pic{width:200px;margin:0 auto 10px}
            .world-event-card__content--future{display:block}
            .world-event-card__future-side{width:100%;margin-top:10px}
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0">

<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0; top: 0"></div>

@php
    $tabs = [
        'events'        => 'Текущие события',
        'events_future' => 'Будущие события',
        'events_my'     => 'Мои события',
        'influence'     => 'Влияние',
        'activity'      => 'Активности',
        'rewards'       => 'Подвиги',
    ];

    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $tabKey => $tabLabel)
            @php $isActive = $mode === $tabKey; @endphp
            <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td align="center" nowrap style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ route('events', ['mode' => $tabKey]) }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tabLabel }}</a>
            </td>
            <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
        @endforeach

        <td width="100%"></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td align="center" nowrap style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Вернуться</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<div style="padding: 10px 6px;">
    @if($mode === 'activity')
        @include('event::activity')
    @elseif(in_array($mode, ['events', 'events_future', 'events_my'], true))
        @include('event::world-events')
    @elseif($mode === 'influence')
        @include('event::influence')
    @else
        <div style="text-align: center; padding: 40px 20px; color: #49382d;">
            <h2 style="font-size: 16px; margin-bottom: 12px; color: #7a3010;">{{ $tabs[$mode] }}</h2>
            <p style="font-size: 12px; color: #888;">Раздел находится в разработке.</p>
        </div>
    @endif
</div>

</body>
</html>
