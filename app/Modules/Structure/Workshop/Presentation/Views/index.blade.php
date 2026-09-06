<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $workshop->name }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        * { box-sizing: border-box; }
@if($isToolWorkshop ?? false)
        html, body { min-height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
@else
        body { margin: 0; color: #49382d; font: 11px Tahoma, Arial, sans-serif; background: #000 url({{ asset('img/bg/bg.gif') }}) fixed; }
@endif
        .workshop { max-width: 980px; margin: 0 auto; padding: 12px; }
        .frame { border: 2px ridge #9c815b; background: #eee3ca; box-shadow: inset 0 0 0 2px #e8ddc4; }
        .head { display: flex; justify-content: space-between; padding: 7px 10px; border-bottom: 1px solid #826c4f; background: linear-gradient(#efe5cf, #cdbb98); }
        .head b { color: #6e160f; }
        .content { padding: 10px; }
        .message { margin-bottom: 8px; padding: 6px 8px; border: 1px solid #8eaf58; background: #e6f1d2; }
        .message.error { border-color: #b46d61; background: #f2d8d2; }
        .empty { padding: 20px; color: #75644d; text-align: center; }
        .tabs { display: flex; gap: 6px; margin-bottom: 10px; flex-wrap: wrap; }
        .tab { padding: 4px 12px; border: 1px solid #826c4f; background: #dfcfaa; color: #461c0b; font-weight: bold; text-decoration: none; }
        .tab.active { background: #6e160f; color: #ffe9ba; }

        /* Блок рецепта: стили коллекций с feo-dwar (collection.css), один в один. */
        .ws-plain table.coll { border-collapse: collapse; border-spacing: 0; }
        .ws-plain .w100 { width: 100%; }
        .ws-plain .p10h, .ws-plain .p10h td { padding-left: 10px; padding-right: 10px; }
        .ws-plain .p2v, .ws-plain .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .ws-plain .brd2-all { border: 1px solid #db9f73; }
        .ws-plain .bg_l { background: linear-gradient(#f3e7cb, #e2d0a6); }
        .ws-plain b.butt2 { height: 22px; font-size: 15px; background: linear-gradient(#e8c87e, #b98a3e); border: 1px solid #7a4d16; border-radius: 3px; display: inline-block; }
        .ws-plain b.butt2 b { height: 20px; font-size: 15px; display: inline-block; }
        .ws-plain b.butt2 input { height: 20px; border: 0; color: #2d1600; font-family: Tahoma; font-size: 11px; font-weight: bold; margin: 0 8px; background: transparent; cursor: pointer; }
        .ws-plain b.butt2.disabled { cursor: default; filter: grayscale(1); opacity: .6; }
        .ws-plain b.butt2.disabled input { cursor: default; }

        .collections-form label, .collections-form .label { color: #000; }
        .collections-divider { display: block; height: 5px; margin: 0 0 5px; font-size: 0; border-bottom: #db9f73 1px solid; }
        .collections-title, .collection-body { padding: 5px; }
        .collection-name { font-size: 12px; margin-left: 6px; }
        .collection-name a { text-decoration: none; }
        .collection-name a:hover { text-decoration: underline; }
        .collection-status { font-size: 12px; color: #489200; }
        .collection-status.disabled { color: #c00000; }
        .collection-slot { display: inline-block; position: relative; width: 62px; height: 82px; overflow: hidden; vertical-align: top; }
        .collection-slot__img { display: block; width: 60px; height: 60px; padding: 1px; background: url('{{ asset('img/bg/slot-empty.png') }}') no-repeat; }
        .collection-slot__img img { width: 60px; height: 60px; }
        .collection-slot__img.grayscale { background: #000; }
        .collection-slot__img.grayscale img { opacity: 0.3; }
        .collection-slot__qty { display: block; font-weight: bold; text-align: center; }
        .collection-slot__qty-current { color: #c00000; }
        .collection-slot.active .collection-slot__qty,
        .collection-slot.active .collection-slot__qty-current { color: #489200; }
        .collection-ico { display: inline-block; height: 75px; padding: 5px 0 0; vertical-align: top; font-weight: bold; font-size: 40px; }
        .collect-btn { margin: 0; }
        .collection-slot__img a { display: block; cursor: pointer; }

        /* Тема мастерской инструментов — тот же хром, что у апгрейда. */
        .upgrade-summary { margin-bottom: 10px; }
        .recipe-filter { margin-bottom: 8px; }
        .tool-recipes { text-align: left; }
        .tool-recipes .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid; font-weight: bold; }
        .tool-recipes .message.success { color: #247327; border-color: #247327; }
        .tool-recipes .message.error { color: #a02020; border-color: #a02020; }
        .tool-recipes .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }
    </style>
</head>
<body class="{{ ($isToolWorkshop ?? false) ? 'ws-tool' : 'ws-plain' }}">
<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0; top: 0"></div>
@php
                $craftRouteName = $craftRouteName ?? 'workshop.craft';
                $tabSuffix = $tabSuffix ?? (isset($activeTab) ? '?tab='.$activeTab : '');
    $activeTabLabel = '';
    foreach (($tabs ?? []) as $tabItem) {
        if (($activeTab ?? '') === ($tabItem['key'] ?? null)) {
            $activeTabLabel = $tabItem['label'];
        }
    }
@endphp
@if($isToolWorkshop ?? false)
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('blacksmith::_tabs', [
                'activeTab' => $activeTab ?? 'tool-workshop',
                'tabs' => $tabs ?? [],
                'blacksmith' => $workshop,
            ])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h p2v upgrade-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>Мастерская инструментов:</b> {{ $activeTabLabel }}</td>
                    <td align="right" style="color:#955c4a;">
                        <b>Монеты:</b>
                        <b class="redd"><img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->money) }}</b>
                        &nbsp;&nbsp;<b>Бриллианты:</b>
                        <b class="redd"><img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle" alt=""> {{ format_money($user->diamond) }}</b>
                    </td>
                </tr></tbody>
            </table>

            <div class="tool-recipes">
                @isset($showFilter)
                    <div class="recipe-filter">
                        <table border="0" cellspacing="0" cellpadding="0" align="center">
                            <tr height="22">
                                @foreach(['learned' => ['label' => 'Изученные рецепты', 'url' => $showFilter['learnedUrl']], 'all' => ['label' => 'Все рецепты', 'url' => $showFilter['allUrl']]] as $filterKey => $filter)
                                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                    <td align="center" class="tbl-usi-hdr mbg">
                                        <a href="{{ $filter['url'] }}"
                                           class="tbl-shp_menu-link_{{ $showFilter['current'] === $filterKey ? 'act' : 'inact' }}"
                                           style="color:#FFE9BA !important;text-decoration:@if($showFilter['current'] === $filterKey) underline @else none @endif;">{{ $filter['label'] }}</a>
                                    </td>
                                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                                @endforeach
                            </tr>
                        </table>
                    </div>
                @endisset
                @if(session('message'))<div class="message success">{{ session('message') }}</div>@endif
                @if(session('error'))<div class="message error">{{ session('error') }}</div>@endif
                @forelse($recipes as $recipe)
                    @include('workshop::_recipe-block', ['recipe' => $recipe])
                @empty
                    <div class="empty-list"><b>Нет изученных рецептов. Используйте книгу рецепта из рюкзака.</b></div>
                @endforelse
            </div>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb">&nbsp;</td>
        <td width="20" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>
@else
<main class="workshop">
    <section class="frame">
        <header class="head"><b>{{ $workshop->name }} · мирные профессии</b><a href="{{ route('location') }}">Вернуться на локацию</a></header>
        <div class="content">
            @isset($tabs)
                <nav class="tabs">
                    @foreach($tabs as $tab)
                        <a href="{{ $tab['route'] }}" class="tab @if(($activeTab ?? '') === $tab['key']) active @endif">{{ $tab['label'] }}</a>
                    @endforeach
                </nav>
            @endisset
            @if(session('message'))<div class="message">{{ session('message') }}</div>@endif
            @if(session('error'))<div class="message error">{{ session('error') }}</div>@endif
            @forelse($recipes as $recipe)
                @include('workshop::_recipe-block', ['recipe' => $recipe])
            @empty
                <div class="empty">Нет изученных рецептов. Используйте книгу рецепта из рюкзака.</div>
            @endforelse
        </div>
    </section>
</main>
@endif
<script src="{{ asset('js/item_tooltip.js') }}"></script>
{!! $itemTooltipScript ?? '' !!}
</body>
</html>
