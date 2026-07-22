<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Акции</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    {!! $itemTooltipScript !!}
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 12px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2, .brd2 td { border: 1px solid #db9f73; }
        .w100 { width: 100%; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .p6v, .p6v td { padding-top: 6px; padding-bottom: 6px; }
        .p4v { padding: 4px; }
        .bg_l { background-image: url(/img/bg/bg_l.gif); }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .redd, .redd * { color: #BA0000 !important; }
        .grnn { color: #2a7a2a; }
        .tbl-usi-hdr { background: url({{ asset('img/bg/tbl-usi-hdr.gif') }}) no-repeat; font-family: tahoma, sans-serif; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px 10px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }

        /* Прогресс-бар акции */
        .b-progress-bar { height: 31px; overflow: hidden; position: relative; }
        .b-progress-bar__bg { border-radius: 5px; margin: 2px 5px 0 5px; overflow: hidden; }
        .b-progress-bar__bg, .b-progress-bar__bg-bright { background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) repeat-x; height: 27px; }
        .b-progress-bar__bg-bright { background-position: 0 -27px; float: left; }
        .b-progress-bar.fill_red .b-progress-bar__bg { background-position: 0 -54px; }
        .b-progress-bar.fill_red .b-progress-bar__bg-bright { background-position: 0 -27px; }
        .b-progress-bar__border { position: absolute; left: 0; top: 0; height: 31px; width: 100%; }
        .b-progress-bar__border-l, .b-progress-bar__border-r, .b-progress-bar__border-c { background: url({{ asset('img/progressbar/progress-bar-1-border.png') }}) no-repeat; height: 100%; }
        .b-progress-bar__border-l, .b-progress-bar__border-r { position: absolute; top: 0; }
        .b-progress-bar__border-l { left: 0; width: 20px; }
        .b-progress-bar__border-r { background-position: 0 -31px; right: 0; width: 20px; }
        .b-progress-bar__border-c { background-position: 0 -62px; background-repeat: repeat-x; margin: 0 20px; }
        .b-progress-bar__text { line-height: 32px; position: absolute; left: 0; top: 0; width: 100%; text-align: center; }
        .bank-premium-progress { padding: 20px 8px 34px; }
        .bank-premium-progress__cont { position: relative; }
        .bank-premium-progress__bar { padding: 0 5px; }
        .bank-premium-progress__levels { height: 40px; width: 100%; position: absolute; left: 0; top: -4px; }
        .bank-premium-progress__levels-cost { font-weight: bold; text-align: center; position: absolute; left: -10px; top: 46px; white-space: nowrap; width: 60px; font-size: 10px; }
        .bank-premium-progress__label { font-size: 12px; font-family: Tahoma, Geneva, sans-serif; color: #FFF; font-weight: bold; position: absolute; line-height: 32px; top: 0; text-align: center; }
        .bank-stock-info {
            width: 30px; height: 27px; font-size: 18px; font-weight: bold; color: #ffffff;
            position: absolute; top: 8px; left: 5px;
            background: #9e484980; border: 1px #e2242245 solid; border-radius: 15px;
            text-align: center; display: table-cell; padding-top: 3px;
            text-shadow: 0px 1px 0 rgb(0,0,0), 0px -1px 0 rgb(0,0,0), 1px 0px 0 rgb(0,0,0), -1px 0px 0 rgb(0,0,0), 1px 1px 0 rgb(0,0,0), 1px -1px 0 rgb(0,0,0), -1px 1px 0 rgb(0,0,0), -1px -1px 0 rgb(0,0,0);
            font-family: Verdana, Geneva, sans-serif;
        }
        .bank-info-money {
            z-index: 10; color: #ffffff;
            text-shadow: 0px 1px 0 rgb(25,94,122), 0px -1px 0 rgb(25,94,122), 1px 0px 0 rgb(25,94,122), -1px 0px 0 rgb(25,94,122), 1px 1px 0 rgb(25,94,122), 1px -1px 0 rgb(25,94,122), -1px 1px 0 rgb(25,94,122), -1px -1px 0 rgb(25,94,122);
        }

        /* Таблица наград */
        .step_info_awards { width: 100%; border: 1px #e3b766 solid; background: #ffedbcc4; }
        .artifact-table { display: inline-table; vertical-align: middle; margin: 1px; }
        .artifact-table td { width: 59px; height: 59px; cursor: pointer; position: relative; border: none !important; padding: 0 !important; }
        .bpdig {
            border: solid 1px #6f4a24 !important; background-color: #6e534c !important;
            min-width: 34px !important; height: 12px !important; color: #f6d9a6 !important;
            font-weight: bold !important; margin: 2px !important; text-align: center !important;
            font-size: 10px; position: absolute; bottom: 0; right: 0;
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0">

@include('premium_shop::_nav', ['activeMenu' => 'stock'])

<table class="coll w100" height="100%">
    <tbody>
    <tr>
        <td valign="top" width="100%" style="padding: 10px 6px">

            <table class="coll w100" height="100%">
                <tbody>
                <tr>
                    <td valign="top" width="100%">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                            <td align="center" class="tbl-usi-hdr mbg">{{ $stock ? $stock->name : 'Акция '.now()->locale('ru')->getTranslatedMonthName('Do MMMM') }}</td>
                                            <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" align="left" style="padding: 6px 4px">

                                    @if($stock)
                                    @php
                                        $tiers       = $stock->tiers;
                                        $tierCount   = $tiers->count();
                                        $maxThreshold = $tierCount > 0 ? $tiers->last()->diamond_threshold : 1;
                                        $progressPct  = $tierCount > 0 ? min(100, ($userTotal / $maxThreshold) * 100) : 0;
                                        $stepPercent  = $tierCount > 0 ? 100 / $tierCount : 100;
                                        $nextTier       = $tiers->firstWhere(fn($t) => $userTotal < $t->diamond_threshold);
                                        $toNext         = $nextTier ? ($nextTier->diamond_threshold - $userTotal) : 0;
                                        $nextTierNumber = $nextTier ? ($tiers->search(fn($t) => $t->id === $nextTier->id) + 1) : null;
                                    @endphp

                                    <div style="text-align: center;">
                                        <b class="grnn">Акция действует с {{ $stock->starts_at->locale('ru')->translatedFormat('j F Y H:i') }} по {{ $stock->ends_at->locale('ru')->translatedFormat('j F Y H:i') }}</b><br>
                                        <div><strong><span style="color:#B22222;">Сумма пополнений накапливается, награда выдаётся автоматически</span></strong></div>
                                        <div><strong><span style="color:#008000;">Акции банка накапливаются до конца срока, и </span><span style="color:#0000FF;">по окончании</span><span style="color:#008000;"> акции накопленная сумма </span><span style="color:#0000FF;">обнуляется</span></strong></div>
                                    </div>

                                    {{-- Прогресс-бар --}}
                                    <div class="bank-premium-progress" id="bank-premium-progress">
                                        <div class="bank-premium-progress__cont">
                                            <div class="bank-premium-progress__bar">
                                                <div class="b-progress-bar fill_red">
                                                    <div class="b-progress-bar__bg">
                                                        <div class="b-progress-bar__bg-bright" style="width: {{ number_format($progressPct, 2) }}%;"></div>
                                                    </div>
                                                    <div class="b-progress-bar__border">
                                                        <div class="b-progress-bar__border-l"></div>
                                                        <div class="b-progress-bar__border-r"></div>
                                                        <div class="b-progress-bar__border-c"></div>
                                                    </div>
                                                    <div class="b-progress-bar__text">&nbsp;</div>
                                                </div>
                                                <div class="bank-premium-progress__label bank-info-money" style="width:{{ $stepPercent }}%; left: 0%;">
                                                    {{ number_format($userTotal, 2) }} / {{ number_format($tiers->first()?->diamond_threshold ?? 0, 2) }}
                                                </div>
                                            </div>
                                            <div class="bank-premium-progress__levels">
                                                <div class="bank-premium-progress__levels" style="position: relative;">
                                                    <div>
                                                        <div class="bank-stock-info">-</div>
                                                        <div class="bank-premium-progress__levels-cost"></div>
                                                    </div>
                                                    @foreach($tiers as $i => $tier)
                                                        <div style="left:{{ ($i + 1) * $stepPercent }}%; margin-left:-40px;position: absolute;">
                                                            <div class="bank-stock-info" style="{{ $i <= $reachedTierIndex ? 'background:#2a7a2a80;border-color:#2a7a2a45;' : '' }}">{{ $i + 1 }}</div>
                                                            <div class="bank-premium-progress__levels-cost">
                                                                <span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ number_format($tier->diamond_threshold, 2) }}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <br>
                                    @if($nextTier)
                                    <div style="text-align: center;">
                                        Пополните ещё на <b><span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ number_format($toNext, 2) }}</b> и получите <b class="redd">{{ $nextTierNumber }}</b> Бонус!
                                    </div>
                                    @else
                                    <div style="text-align: center;"><b class="grnn">Вы достигли максимального уровня акции!</b></div>
                                    @endif
                                    <br>

                                    <div class="step_info_awards">
                                        <table class="coll brd2 w100 p6v p6h">
                                            <tr style="background: #ff000021;">
                                                <td width="1%" align="center"><b style="color: #284310 !important;">#</b></td>
                                                <td align="center"><b style="color: #284310 !important;">Сумма пополнения</b></td>
                                                <td width="100%" align="center"><b style="color: #284310 !important;">Награда</b></td>
                                            </tr>
                                            @foreach($tiers as $i => $tier)
                                                <tr style="{{ $i <= $reachedTierIndex ? 'background:#e8f5e980;' : '' }}">
                                                    <td width="1%" align="center"><b class="redd">{{ $i + 1 }}</b></td>
                                                    <td align="center" nowrap>
                                                        <span title="Бриллиант"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;{{ number_format($tier->diamond_threshold, 2) }}
                                                    </td>
                                                    <td width="100%">
                                                        @foreach($tier->items as $item)
                                                            <table width="59" height="59" cellpadding="0" cellspacing="0" border="0" class="artifact-table"
                                                                   @if($item->shareItem?->image) background="{{ $item->shareItem->image }}" style="background-repeat: no-repeat; background-position: center; background-size: cover;" @endif>
                                                                <tr>
                                                                    <td data-id="{{ $item->share_item_id }}"
                                                                        onmouseover="showItemInfo(this,event,2)"
                                                                        onmouseout="showItemInfo(this,event,0)"
                                                                        @if($item->share_item_id)
                                                                            onclick="window.open('{{ route('items.info.share', ['id' => $item->share_item_id]) }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"
                                                                            style="cursor: pointer;"
                                                                        @endif>
                                                                        @if($item->count > 0)
                                                                            <div class="bpdig">{{ $item->count }}</div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            </table>
                                                        @endforeach
                                                        @if($tier->items->isEmpty())
                                                            <span style="color:#999;font-size:11px;">—</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>

                                    @else
                                    <div style="text-align:center;padding:30px;color:#888;">Акция сейчас не проводится</div>
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

        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
