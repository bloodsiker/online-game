<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вольный торг: {{ $post->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    @include('trade::styles')
    {!! $itemTooltipScript ?? '' !!}
    <script>window.gebi = window.gebi || function (id) { return document.getElementById(id); };</script>
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</head>
<body>
@php
    $activeTab = $activeTab ?? 'purchase';
@endphp
<div id="artifact_alt" style="width:300px;display:none;position:fixed;z-index:10000001;left:0;top:0"></div>
<table class="trade-tabs" border="0" cellspacing="0" cellpadding="0" width="100%">
    <tbody><tr height="21">
        @foreach(['purchase' => 'Купить товар', 'sale' => 'Продать товар'] as $tab => $label)
            @php
                $tabStyle = $activeTab === $tab ? 2 : 1;
            @endphp
            <td class="trade-tab-edge"><img src="{{ asset('img/bg/btn/btn-left'.$tabStyle.'.gif') }}" width="19" height="21" alt=""></td>
            <td class="trade-tab-title" style="background-image:url('{{ asset('img/bg/btn/btn-cent'.$tabStyle.'.gif') }}')"><a href="{{ route('trade.show', ['post' => $post->id, 'tab' => $tab]) }}" class="btn_{{ $tabStyle }}" @if($activeTab === $tab) aria-current="page" @endif>{{ $label }}</a></td>
            <td class="trade-tab-edge"><img src="{{ asset('img/bg/btn/btn-right'.$tabStyle.'.gif') }}" width="19" height="21" alt=""></td>
        @endforeach
        <td class="trade-tab-spacer"></td>
        <td class="trade-tab-edge"><img src="{{ asset('img/bg/btn/btn-left1.gif') }}" width="19" height="21" alt=""></td>
        <td class="trade-tab-title" style="background-image:url('{{ asset('img/bg/btn/btn-cent1.gif') }}')"><a href="{{ route('npc', $post->npc_id) }}" class="btn_1">Выход</a></td>
        <td class="trade-tab-edge"><img src="{{ asset('img/bg/btn/btn-right1.gif') }}" width="19" height="21" alt=""></td>
    </tr></tbody>
</table>
<table class="trade-frame" width="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22"><td width="20" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt"></td><td width="20" class="tbl-shp-sml rt"><b></b></td></tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg trade-content" valign="top">
            <table class="coll w100 p10h p2v brd2-all trade-summary"><tbody><tr class="bg_l">
                <td><b>Навык Вольный торг:</b> <b class="redd">{{ $level }}</b>
                    <div class="rep-progress-bar rep-progress-bar--trade" role="progressbar" aria-label="Прогресс навыка Вольный торг"
                          aria-valuemin="0" aria-valuemax="100" aria-valuenow="{{ $skillProgress }}" title="{{ $skillProgressTitle }}">
                        <div class="rep-progress-bar__bg"><div class="rep-progress-bar__fill" style="width:{{ $skillProgress }}%"></div></div>
                        <div class="rep-progress-bar__border">
                            <div class="rep-progress-bar__border-left"></div>
                            <div class="rep-progress-bar__border-right"></div>
                            <div class="rep-progress-bar__border-center"></div>
                        </div>
                        <div class="rep-progress-bar__text">{{ $skillProgress }}%</div>
                    </div>
                    <span style="margin-left:10px;white-space:nowrap">Груз: <b class="redd">{{ $cargoCount }}/{{ $capacity }}</b></span>
                </td>
                <td align="right">Монеты: <b>{{ number_format($money, 0, '', ' ') }}</b></td>
            </tr></tbody></table>
            @if($errors->any())<p class="trade-message redd" role="alert">{{ $errors->first() }}</p>@endif
            @if(session('trade_result'))<p class="trade-message" role="status">{{ session('trade_result.message') }}</p>@endif
            @unless($licensed)
                <table class="coll w100 p10h p6v brd2-all trade-license"><tbody><tr class="bg_l">
                    <td>Для покупки и продажи этому торговцу требуется постоянная лицензия.<br>Стоимость: <b>{{ number_format($post->license_price, 0, '', ' ') }}</b> монет.</td>
                    <td align="right"><form method="post" action="{{ route('trade.act', [$post->id, 'license']) }}">
                        @csrf
                        <input name="request_key" type="hidden" value="{{ (string) Illuminate\Support\Str::uuid() }}">
                        <input name="quantity" type="hidden" value="1">
                        <input name="tab" type="hidden" value="{{ $activeTab }}">
                        <span class="butt1 pointer"><span><input type="submit" value="Купить лицензию" @disabled($money < $post->license_price)></span></span>
                    </form></td>
                </tr></tbody></table>
            @endunless
            @foreach(['purchase' => 'Купить товары', 'sale' => 'Продать груз'] as $action => $heading)
                @continue($action !== $activeTab)
                @php
                    $entries = ($action === 'purchase' ? $goods : $offers)->filter(function ($entry) use ($items, $counts, $action) {
                        return $items->has($entry->share_item_id) && ($action !== 'sale' || ($counts[$entry->share_item_id] ?? 0) > 0);
                    });
                @endphp
                <h3 class="trade-heading">{{ $heading }}</h3>
                <table class="w100 trade-list" border="0" cellspacing="0" cellpadding="0" data-trade-section="{{ $action }}"><tbody>
                @forelse($entries as $entry)
                    @include('trade::item-row')
                    <tr><td class="trade-row-gap"></td></tr>
                @empty
                    <tr><td class="trade-empty">{{ $action === 'sale' ? 'В рюкзаке нет товаров, которые принимает этот торговец.' : 'Товары пока недоступны.' }}</td></tr>
                @endforelse
                </tbody></table>
            @endforeach
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18"><td width="20" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb">&nbsp;</td><td width="20" class="tbl-shp-sml rb"><b></b></td></tr>
    </tbody>
</table>
@include('trade::scripts')
</body>
</html>
