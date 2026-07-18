<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
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
            width: 60px;
            height: 60px;
            margin: 0 auto;
            padding: 5px 6px 6px;
            background: url(/main/images/user-reward-frame.png) 0 0 no-repeat;
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
    </style>
</head>
<body leftmargin="0" rightmargin="0">

<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0; top: 0"></div>

@php
    $tabs = [
        'events'        => 'Текущие события',
        'events_future' => 'Будущие события',
        'events_my'     => 'Мои события',
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
    @else
        <div style="text-align: center; padding: 40px 20px; color: #49382d;">
            <h2 style="font-size: 16px; margin-bottom: 12px; color: #7a3010;">{{ $tabs[$mode] }}</h2>
            <p style="font-size: 12px; color: #888;">Раздел находится в разработке.</p>
        </div>
    @endif
</div>

</body>
</html>
