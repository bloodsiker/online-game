<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>История сражения #{{ $battle->id }}</title>
    <style>
        html, body { height: 100%; margin: 0; }
        body { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; background-image: url({{ asset('main/images/bg2.gif') }}); }
        .regcolor, .regcolor * { color: #955C4A; }
        a { text-decoration: none; }
        a:hover { text-decoration: underline; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .b { font-weight: bold; }
        .red, .red * { color: #d00000; }

        .common-block { position: relative; }
        .common-block .common-content { position: relative; z-index: 2; }
        .common-block .corner-tl { position: absolute; top: -19px; left: -23px; width: 141px; height: 176px; background: url({{ asset('main/images/common-block-red-tl.png') }}) 0 0 no-repeat; }
        .common-block .corner-tr { position: absolute; top: -19px; right: -24px; width: 146px; height: 176px; background: url({{ asset('main/images/common-block-red-tr.png') }}) 0 0 no-repeat; }
        .common-block .corner-bl { position: absolute; bottom: -19px; left: -19px; width: 238px; height: 127px; background: url({{ asset('main/images/common-block-red-bl.png') }}) 0 0 no-repeat; }
        .common-block .corner-br { position: absolute; bottom: -19px; right: -21px; width: 241px; height: 128px; background: url({{ asset('main/images/common-block-red-br.png') }}) 0 0 no-repeat; }
        .common-block .bg-inner   { background: url({{ asset('main/images/bgg2.gif') }}) repeat; }
        .common-block .bg-inner-l { background: url({{ asset('main/images/common-block-inner-red-l.png') }}) 0 0 repeat-y; }
        .common-block .bg-inner-r { background: url({{ asset('main/images/common-block-inner-red-r.png') }}) 100% 0 repeat-y; }
        .common-block .bg-inner-t { margin: 0 12px; background: url({{ asset('main/images/common-block-inner-t.png') }}) 0 0 repeat-x; }
        .common-block .bg-inner-b { padding: 20px 18px; background: url({{ asset('main/images/common-block-inner-b.png') }}) 0 100% repeat-x; }
        .common-block .bg-t { height: 41px; margin: 0 39px; text-align: center; background: url({{ asset('main/images/common-block-t.png') }}) 0 100% repeat-x; }
        .common-block .bg-b { height: 41px; margin: 0 39px; background: url({{ asset('main/images/common-block-b.png') }}) 0 0 repeat-x; }
        .common-block .bg-l { background: url({{ asset('main/images/common-block-l.png') }}) 0 0 repeat-y; }
        .common-block .bg-r { padding: 0 39px; background: url({{ asset('main/images/common-block-r.png') }}) 100% 0 repeat-y; }

        .common-header__small, .common-header__small .h-inner, .common-header__small .h-txt { display: inline-block; }
        .common-header__small { position: relative; top: 11px; z-index: 1; height: 39px; padding: 0 0 0 87px; background: url({{ asset('main/images/common-header-small.png') }}) 0 0 no-repeat; }
        .common-header__small .h-inner { height: 39px; padding: 0 97px 0 10px; background: url({{ asset('main/images/common-header-small.png') }}) 100% -39px no-repeat; }
        .common-header__small .h-txt { padding: 10px 0 0; font-weight: bold; font-size: 11px; text-align: center; color: #faf7b9; }

        .tbl-shp_sml-top    { background-image: url({{ asset('main/images/tbl-shp_sml-top.gif') }}); background-repeat: repeat-x; height: 22px; }
        .tbl-shp_sml-bottom { background-image: url({{ asset('main/images/tbl-shp_sml-bottom.gif') }}); background-repeat: repeat-x; height: 18px; }
        .tbl-usi_left  { background-image: url({{ asset('main/images/tbl-usi_left.gif') }}); background-repeat: repeat-y; background-position: right top; width: 20px; }
        .tbl-usi_right { background-image: url({{ asset('main/images/tbl-usi_right.gif') }}); background-repeat: repeat-y; width: 20px; }
        .tbl-usi_bg    { background-image: url({{ asset('main/images/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .tbl-usi_label-center {
            background-image: url({{ asset('main/images/tbl-usi_label-center.gif') }});
            background-repeat: repeat-x; height: 22px;
            font-family: Tahoma; font-weight: bold; font-size: 11px; color: #FCF5B7; padding: 0 10px 3px;
        }
        .tbl-ati_brd-all { border: 1px solid #DB9F73; font-family: Tahoma; font-size: 11px; color: #201610; }
        .tbl-sts_bg-light { background-image: url({{ asset('main/images/tbl-usi_bg-light.gif') }}); background-repeat: repeat; }
        .tbl-usi_brd-bottom { border-bottom: 1px solid #DB9F73; }

        /* Заголовок раунда */
        .round-hdr td {
            padding: 4px 8px;
            font-weight: bold;
            color: #BA0000;
            background: url({{ asset('main/images/tbl-usi_bg-light.gif') }}) repeat;
            border-bottom: 1px solid #c09070;
        }
        .round-hdr .round-ts { font-weight: normal; color: #777; font-size: 10px; }

        /* Строки участников */
        .hit-row td { padding: 3px 8px; border-bottom: 1px solid #e8d4bc; vertical-align: top; }
        .hit-who { white-space: nowrap; min-width: 130px; }
        .hit-who-player { color: #114d01; font-weight: bold; }
        .hit-who-monster { color: #BA0000; font-weight: bold; }
        .hit-hp { color: #555; font-weight: normal; }
        .hit-action { color: #201610; }
        .hit-action p { margin: 1px 0; }

        /* цвета из боёвки */
        .color-red    { color: red; }
        .color-green  { color: green; }
        .color-info   { color: #129df0; }
        .color-purple { color: purple; }
        .color-buff   { color: #16a085; font-weight: bold; }
        .color-boss   { color: #ff4444; font-weight: bold; }
        .color-shield { color: #4da6ff; }
        .color-damage { color: #e74c3c; }
        .color-debuff { color: #e67e22; }
        .color-skill  { color: #9b59b6; }
        .color-life-drain    { color: #8b0000; }
        .color-reflect       { color: #4169e1; font-weight: bold; }
        .color-immunity      { color: #c2a402; }
        .color-berserk       { color: #dc143c; font-weight: bold; }
        .color-mirror        { color: #9370db; font-style: italic; }
        .color-damage-to-heal{ color: #32cd32; font-weight: bold; }
        .msg-levelup         { color: #c2a402; font-weight: bold; }
    </style>
</head>
<body class="regcolor">

<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
<tbody><tr><td align="center" valign="middle">

<table width="680" border="0" cellspacing="0" cellpadding="0" align="center">
<tbody><tr><td>
<div class="common-block">
    <div class="corner-tl"></div><div class="corner-tr"></div>
    <div class="corner-bl"></div><div class="corner-br"></div>

    <div class="bg-t">
        <div class="common-header__small">
            <div class="h-inner"><div class="h-txt">История сражения</div></div>
        </div>
    </div>

    <div class="bg-l"><div class="bg-r"><div class="bg-inner"><div class="bg-inner-l"><div class="bg-inner-r"><div class="bg-inner-t"><div class="bg-inner-b">
    <div class="common-content">

        <table width="100%" border="0" cellspacing="0" cellpadding="0" align="center">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt=""></td>
            <td class="tbl-shp_sml-top" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
                    <td><img src="{{ asset('data/img/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                    <td class="tbl-usi_label-center">
                        Бой #{{ $battle->id }} &mdash; {{ $battle->location->name ?? '—' }} &mdash; {{ $battle->status->isActive() ? 'В процессе' : 'Завершён' }} &mdash; Раундов: {{ $battle->rounds }}
                    </td>
                    <td><img src="{{ asset('data/img/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                </tr></tbody></table>
            </td>
            <td width="20" align="left" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt=""></td>
        </tr>
        <tr>
            <td class="tbl-usi_left">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" align="left">
                <img src="{{ asset('main/images/d.gif') }}" width="1" height="7" alt=""><br>

                @if($rounds->isEmpty())
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl-ati_brd-all" style="border-collapse:collapse">
                        <tbody><tr><td class="tbl-sts_bg-light" style="padding:4px 8px;">История сражения пуста.</td></tr></tbody>
                    </table>
                @else
                    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="tbl-ati_brd-all" style="border-collapse:collapse">
                    <tbody>
                    @foreach($rounds as $round)
                        @php
                            $userHit    = $round->hits->firstWhere('participant_type', 'user');
                            $monsterHit = $round->hits->firstWhere('participant_type', 'monster');
                        @endphp

                        {{-- Заголовок раунда --}}
                        <tr class="round-hdr">
                            <td colspan="2">
                                Раунд {{ $round->round_number }}
                                <span class="round-ts">&mdash; {{ $round->created_at->format('d.m.Y H:i:s') }}</span>
                            </td>
                        </tr>

                        @if($round->hits->count())

                            {{-- Атака игрока --}}
                            @if($userHit)
                                <tr class="hit-row">
                                    <td class="hit-who">
                                        <span class="hit-who-player">{{ $round->user?->name ?? '—' }}</span>
                                        @if($userHit->hp_after !== null)
                                            @php $playerHpMax = $round->user?->player?->hp_max; @endphp
                                            <span class="hit-hp">({{ $userHit->hp_after }}{{ $playerHpMax ? '/'.$playerHpMax : '' }})</span>
                                        @endif
                                    </td>
                                    <td class="hit-action">{!! $userHit->action !!}</td>
                                </tr>
                            @endif

                            {{-- Контратака монстра --}}
                            @if($monsterHit)
                                @php
                                    $mName   = $round->locationMonster?->monster?->name ?? '—';
                                    $mHpMax  = $round->locationMonster?->hp_max;
                                @endphp
                                <tr class="hit-row">
                                    <td class="hit-who">
                                        <span class="hit-who-monster">{{ $mName }}</span>
                                        @if($monsterHit->hp_after !== null && $mHpMax !== null)
                                            <span class="hit-hp">({{ $monsterHit->hp_after }}/{{ $mHpMax }})</span>
                                        @endif
                                    </td>
                                    <td class="hit-action">{!! $monsterHit->action !!}</td>
                                </tr>
                            @endif

                        @elseif($round->action)
                            {{-- Старые раунды без структурированных хитов --}}
                            <tr class="hit-row">
                                <td class="hit-who">
                                    @if($round->user)
                                        <span class="hit-who-player">{{ $round->user->name }}</span>
                                    @endif
                                    @if($round->locationMonster?->monster)
                                        <br><span class="hit-who-monster">{{ $round->locationMonster->monster->name }}</span>
                                    @endif
                                </td>
                                <td class="hit-action">{!! $round->action !!}</td>
                            </tr>
                        @endif

                    @endforeach
                    </tbody>
                    </table>
                @endif

                <img src="{{ asset('main/images/d.gif') }}" width="1" height="7" alt=""><br>
            </td>
            <td class="tbl-usi_right">&nbsp;</td>
        </tr>
        <tr height="18">
            <td width="20" align="right" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt=""></td>
            <td class="tbl-shp_sml-bottom" valign="top" align="center">&nbsp;</td>
            <td width="20" align="left" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt=""></td>
        </tr>
        </tbody>
        </table>

    </div>
    </div></div></div></div></div></div>
    <div class="bg-b"></div>
</div>
</td></tr></tbody>
</table>

</td></tr></tbody>
</table>
</body>
</html>