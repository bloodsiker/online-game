<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .p6v, .p6v td { padding-top: 6px; padding-bottom: 6px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .redd, .redd * { color: #ba0000 !important; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-usi_bg { background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .relic-frame {
            position: relative;
            width: 60px;
            height: 60px;
            padding: 5px 6px 6px;
            background: url({{ asset('main/images/user-reward-frame.png') }}) 0 0 no-repeat;
        }
        .relic-img { display: block; width: 60px; height: 60px; object-fit: contain; cursor: pointer; }
        .relic-name { color: #006699; font-weight: 700; cursor: pointer; text-decoration: underline; }
        .pointer, .pointer input { cursor: pointer; }
        .gamble-resource-row { cursor: pointer; }
        .gamble-options { display: none; }
        .gamble-options.open { display: table-row; }

        .gamble-option {
            position: relative;
            display: inline-block;
            width: 150px;
            margin: 8px 10px 4px 0;
            vertical-align: top;
            border: 1px solid #b99a7c;
            box-shadow: 0 1px 0 #fff inset, 0 1px 3px rgba(67, 37, 17, .25);
            background: #ead8ba url({{ asset('img/bg/common-bg.png') }}) repeat;
            overflow: hidden;
            text-align: center;
        }
        .gamble-option.disabled { opacity: .5; }
        .gamble-option__head {
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
            font-weight: bold;
            font-size: 11px;
            line-height: 20px;
            text-align: center;
            text-shadow: 0 1px 1px #4b160c;
        }
        .gamble-option__head::before,
        .gamble-option__head::after {
            position: absolute;
            top: 0;
            width: 27px;
            height: 22px;
            content: '';
        }
        .gamble-option__head::before { left: -23px; background: url({{ asset('img/bg/info/tbl-usi_label-left.gif') }}) no-repeat; }
        .gamble-option__head::after { right: -23px; background: url({{ asset('img/bg/info/tbl-usi_label-right.gif') }}) no-repeat; }
        .gamble-option__body { padding: 10px 10px 12px; }
        .gamble-option .chance { margin: 2px 0 6px; font-size: 22px; font-weight: 700; text-shadow: 0 1px 0 #fff4d6; }
        .gamble-option .chance.high { color: #2a7a2a; }
        .gamble-option .chance.mid { color: #a06a00; }
        .gamble-option .chance.low { color: #ba0000; }
        .gamble-option__divider {
            position: relative;
            width: 88%;
            height: 11px;
            margin: 4px auto 8px;
        }
        .gamble-option__divider::before {
            position: absolute;
            top: 5px;
            right: 0;
            left: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #9a704f 18%, #9a704f 82%, transparent);
            content: '';
        }
        .gamble-option__divider::after {
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
        .gamble-option__row { padding: 1px 4px; color: #5c4433; }
        .gamble-option__footer { margin-top: 8px; }
    </style>
    {!! $itemTooltipScript !!}
    <script>
        window.gebi = window.gebi || function (id) { return document.getElementById(id); };
    </script>
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body leftmargin="0" rightmargin="0">
<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0;top: 0"></div>

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    @php
        $btnLeft1 = 'img/bg/btn/btn-left1.gif';
        $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
        $btnRight1 = 'img/bg/btn/btn-right1.gif';
        $btnLeft2 = 'img/bg/btn/btn-left2.gif';
        $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
        $btnRight2 = 'img/bg/btn/btn-right2.gif';
    @endphp
    <tr height="21">
        <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="120" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('reputation_exchange', ['id' => $page->structureId]) }}" class="btn_2">Обмен ресурсов</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Выход</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left"></td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10 6 10 6">

            <table class="coll w100 p10h p2v brd2-all" border="0" width="100%">
                <tbody>
                <tr class="bg_l">
                    <td align="left" width="50%" nowrap="">
                        <b>Репутация «{{ $page->reputationName }}»:</b>
                        <b class="redd">{{ number_format($page->currentPoints, 0, '', ' ') }}</b>
                    </td>
                </tr>
                </tbody>
            </table>

            <p style="padding: 0 4px;">Выберите ресурс, затем — один из вариантов обмена. Ресурс расходуется в любом случае, независимо от исхода.</p>

            @if ($page->offeringCooldown)
                <table class="coll w100 p10h p6v brd2-all" border="0" width="100%">
                    <tbody>
                    <tr class="bg_l">
                        <td align="left" class="redd"><b>Подношение уже сделано.</b> Следующее будет доступно через: {{ $page->offeringCooldown }}.</td>
                    </tr>
                    </tbody>
                </table>
            @endif

            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                @foreach($page->resources as $resource)
                    <tr class="gamble-resource-row" onclick="toggleGambleOptions({{ $resource->shareItemId }})">
                        <td colspan="3">
                            <table class="coll w100 p10h p6v brd2-all">
                                <tbody>
                                <tr class="bg_l">
                                    <td width="60">
                                        <div class="relic-frame">
                                            <img src="{{ asset($resource->image) }}" class="relic-img" alt="{{ $resource->name }}"
                                                 data-id="{{ $resource->shareItemId }}"
                                                 onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                 onclick="event.stopPropagation(); window.open('{{ route('items.info.share', ['id' => $resource->shareItemId]) }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">
                                        </div>
                                    </td>
                                    <td>
                                        <div style="margin-bottom: 3px;">
                                            <span class="relic-name" style="color: {{ $resource->rarityColor }};">{{ $resource->name }}</span>
                                        </div>
                                        <div>У вас: <b class="redd">{{ $resource->availableCount }}</b> шт.</div>
                                    </td>
                                    <td align="right" width="60">
                                        <span class="butt1 pointer"><span><input type="button" value="Выбрать" style="width: 70px;"></span></span>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr id="gamble-options-{{ $resource->shareItemId }}" class="gamble-options">
                        <td colspan="3" style="padding: 4px 10px 10px;">
                            @foreach($resource->options as $option)
                                @php
                                    $chanceClass = $option->successChance >= 80 ? 'high' : ($option->successChance >= 40 ? 'mid' : 'low');
                                    $optionDisabled = ! $option->canAfford || $page->offeringCooldown;
                                @endphp
                                <form action="{{ route('reputation_exchange.apply', ['id' => $page->structureId]) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    <input type="hidden" name="option_id" value="{{ $option->id }}">
                                    <div class="gamble-option {{ $optionDisabled ? 'disabled' : '' }}">
                                        <div class="gamble-option__head">Обмен</div>
                                        <div class="gamble-option__body">
                                            <div class="chance {{ $chanceClass }}">{{ $option->successChance }}%</div>
                                            <div class="gamble-option__divider"></div>
                                            <div class="gamble-option__row">Отдать: <b>{{ $option->resourceCost }}</b> шт.</div>
                                            <div class="gamble-option__row">Награда: <b class="redd">+{{ $option->rewardPoints }}</b> реп.</div>
                                            <div class="gamble-option__footer">
                                                <b class="butt1 pointer"><b>
                                                    <button type="submit" class="butt1" {{ $optionDisabled ? 'disabled' : '' }}>Обменять</button>
                                                </b></b>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            @endforeach
                        </td>
                    </tr>
                @endforeach
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

<script>
    function toggleGambleOptions(shareItemId) {
        const row = document.getElementById('gamble-options-' + shareItemId);
        if (row) {
            row.classList.toggle('open');
        }
    }

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
