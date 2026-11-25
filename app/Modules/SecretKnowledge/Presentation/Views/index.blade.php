<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('partials.image-tabs-fix')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('secret_knowledge.title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    {{-- Шрифты для рун и глаголицы в ленте манускрипта (системных шрифтов с этими письменностями часто нет). --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Noto+Sans+Glagolitic&family=Noto+Sans+Runic&display=swap">
    <style>
        html, body { min-height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .tbl-usi_bg { background: url('/img/bg/tbl-usi_bg.gif') repeat; }
        .brd2-all { border: 1px solid #db9f73; }
        .bg_l { background-image: url('/img/bg/info/bg_l.gif'); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .pointer, .pointer input { cursor: pointer; }

        .knowledge-summary { margin-bottom: 10px; }
        .knowledge-hint { margin: 0 0 10px; color: #49382d; text-align: center; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .req-ok { color: #247327 !important; font-weight: bold; }
        .req-fail { color: #a02020 !important; font-weight: bold; }
        .butt1.pointer.disabled { cursor: default; opacity: .5; }
        .butt1.pointer.disabled input { cursor: default; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid #8d2616; color: #8d2616; font-weight: bold; }
        .message.ok { border-color: #247327; color: #247327; }
        .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }

        .knowledge-container { width: 100%; table-layout: fixed; border-spacing: 0; }
        .knowledge-container .common-inner-block-tl { background: url('/main/images/common-inner-block-tl.png') no-repeat; }
        .knowledge-container .common-inner-block-tr { background: url('/main/images/common-inner-block-tr.png') no-repeat; }
        .knowledge-container .common-inner-block-bl { background: url('/main/images/common-inner-block-bl.png') no-repeat; }
        .knowledge-container .common-inner-block-br { background: url('/main/images/common-inner-block-br.png') no-repeat; }
        .knowledge-container .common-inner-block-t { background: url('/main/images/common-inner-block-t.png') repeat-x; }
        .knowledge-container .common-inner-block-b { background: url('/main/images/common-inner-block-b.png') repeat-x; }
        .knowledge-container .common-inner-block-l { background: url('/main/images/common-inner-block-l.png') repeat-y; }
        .knowledge-container .common-inner-block-r { background: url('/main/images/common-inner-block-r.png') repeat-y; }
        .knowledge-container .common-inner-block-bg { background: url('/img/bg/bg_l.gif') repeat; padding: 3px; vertical-align: top; }

        .knowledge-card { width: 100%; table-layout: fixed; border-spacing: 0; margin-bottom: 12px; }
        .knowledge-card .gp-frame-tl, .knowledge-card .gp-frame-tr, .knowledge-card .gp-frame-bl, .knowledge-card .gp-frame-br { background: url('/img/bg/common-corners.png') no-repeat; font-size: 0; line-height: 0; }
        .knowledge-card .gp-frame-t, .knowledge-card .gp-frame-b { background: url('/img/bg/common-tb.png') repeat-x; font-size: 0; line-height: 0; }
        .knowledge-card .gp-frame-l, .knowledge-card .gp-frame-r { background: url('/img/bg/common-lr.png') repeat-y; font-size: 0; line-height: 0; }
        .knowledge-card .gp-frame-tl, .knowledge-card .gp-frame-t, .knowledge-card .gp-frame-l { background-position: 0 0; }
        .knowledge-card .gp-frame-tr, .knowledge-card .gp-frame-r { background-position: 100% 0; }
        .knowledge-card .gp-frame-bl, .knowledge-card .gp-frame-b { background-position: 0 100%; }
        .knowledge-card .gp-frame-br { background-position: 100% 100%; }
        .knowledge-card .gp-frame-bg { background: url('/img/bg/common-bg.png') repeat; padding: 10px; }

        .knowledge-card__title { margin: 0 0 14px; font-size: 13px; font-weight: bold; text-align: center; }
        .knowledge-card__row { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: center; gap: 10px; }
        .knowledge-card__operand { flex: 0 0 80px; width: 80px; min-height: 80px; text-align: center; }
        .knowledge-card__count { display: block; margin-top: 4px; font-weight: bold; white-space: nowrap; }
        .knowledge-card__symbol { flex: 0 0 auto; align-self: flex-start; display: flex; align-items: center; height: 71px; color: #8d2616; font-size: 25px; font-weight: bold; }
        .knowledge-card__text { flex: 1 1 170px; min-width: 150px; max-width: 400px; text-align: left; color: #553e20; line-height: 16px; }
        .knowledge-card__text b { color: #49382d; }
        .knowledge-card__desc { margin-top: 4px; color: #6b513a; font-style: italic; }
        .knowledge-card__text a { text-decoration: none; }
        .knowledge-card__reqs { flex: 0 0 165px; width: 165px; padding: 5px; box-sizing: border-box; text-align: left; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .knowledge-card__reqs-title { color: #553e20; font-weight: bold; text-align: center; }
        .knowledge-card__req { display: flex; justify-content: space-between; gap: 6px; }
        .knowledge-card__action { flex: 0 0 auto; min-width: 120px; min-height: 80px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; text-align: center; }
        .knowledge-card__action input[type=number] { width: 60px; text-align: center; }
        .knowledge-card__done { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; color: #1e5e1e; font-weight: bold; background: #e3f3d9; border: 1px solid #7fa865; border-radius: 14px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.5); }
        .knowledge-card__done::before { content: '✓'; display: inline-flex; align-items: center; justify-content: center; width: 15px; height: 15px; color: #fff; font-size: 10px; font-weight: bold; background: #4c8f2f; border-radius: 50%; }
        .knowledge-section { margin: 4px 0 10px; color: #8d2616; font-size: 12px; font-weight: bold; text-align: center; }
    </style>
</head>
<body>
@php
    $btnLeft1 = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1 = 'img/bg/btn/btn-right1.gif';
    $btnLeft2 = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2 = 'img/bg/btn/btn-right2.gif';
    $bundle = $settings?->bundle;
    $itemLink = static fn (int $id): string => route('items.info.share', ['id' => $id]);
@endphp

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
                <tbody>
                <tr height="21">
                    <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21" alt=""></td>
                    <td width="130" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 4px;">
                        <a href="{{ route('secret_knowledge.index', ['npc' => $npcId]) }}" class="btn_2">{{ __('secret_knowledge.title') }}</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21" alt=""></td>

                    <td></td>

                    <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21" alt=""></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 4px;">
                        <a href="{{ route('location') }}" class="btn_1">{{ __('Выход') }}</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21" alt=""></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h p2v knowledge-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>{{ __('secret_knowledge.npc_name') }}</b></td>
                    <td align="right" style="color:#955c4a;">
                        <b>{{ __('secret_knowledge.points_short') }}:</b>
                        <b class="redd" data-knowledge-points>{{ number_format($points, 0, '', ' ') }}</b>
                        @if($bundle)
                            &nbsp;&nbsp;<b>{{ __('secret_knowledge.bundles_short') }}:</b>
                            <b class="redd" data-knowledge-bundles>{{ $bundleCount }}</b>
                        @endif
                    </td>
                </tr></tbody>
            </table>

            @if(session('message'))<div class="message ok">{{ session('message') }}</div>@endif
            @if($errors->any())<div class="message">{{ $errors->first() }}</div>@endif

            <table class="knowledge-container" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td width="10" height="10" class="common-inner-block-tl"></td>
                    <td height="10" class="common-inner-block-t"></td>
                    <td width="10" height="10" class="common-inner-block-tr"></td>
                </tr>
                <tr>
                    <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                    <td class="common-inner-block-bg">
                        <div class="knowledge-hint">{{ __('secret_knowledge.explain') }}</div>

                        {{-- Изучение ворохов --}}
                        <div class="knowledge-section">{{ __('secret_knowledge.analyze_title') }}</div>
                        @if($bundle)
                            <table class="knowledge-card" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr><td width="8" height="10" class="gp-frame-tl"></td><td height="10" class="gp-frame-t"></td><td width="8" height="10" class="gp-frame-tr"></td></tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg" align="center">
                                        <div class="knowledge-card__title" style="color:{{ $bundle->rarity?->color() }}">{{ $bundle->name }}</div>
                                        <div class="knowledge-card__row">
                                            <div class="knowledge-card__operand">
                                                <a class="upgrade-icon" href="{{ $itemLink((int) $bundle->id) }}"
                                                   data-id="{{ $bundle->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                   onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                                                    <img src="{{ $bundle->image }}" alt="{{ $bundle->name }}">
                                                </a>
                                                <span class="knowledge-card__count" data-knowledge-bundle-count>×{{ $bundleCount }}</span>
                                            </div>

                                            <span class="knowledge-card__symbol" aria-hidden="true">→</span>

                                            <div class="knowledge-card__text">
                                                <div>{{ __('secret_knowledge.analyze_hint') }}</div>
                                                <div>{{ __('secret_knowledge.gain_per_success') }}:
                                                    <b>{{ $settings->knowledge_gain_min === $settings->knowledge_gain_max ? $settings->knowledge_gain_min : $settings->knowledge_gain_min.'–'.$settings->knowledge_gain_max }}</b>
                                                </div>
                                            </div>

                                            <div class="knowledge-card__action">
                                                <span class="butt1 pointer {{ $bundleCount < 1 && ! $activeStudy ? 'disabled' : '' }}"><span>
                                                    <input type="button" class="grnn" data-study-open value="{{ __('secret_knowledge.analyze_button') }}" @disabled($bundleCount < 1 && ! $activeStudy)>
                                                </span></span>
                                                @if($bundleCount < 1)
                                                    <span class="req-fail">{{ __('secret_knowledge.no_bundles') }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr><td width="8" height="10" class="gp-frame-bl"></td><td height="10" class="gp-frame-b"></td><td width="8" height="10" class="gp-frame-br"></td></tr>
                                </tbody>
                            </table>
                        @else
                            <div class="empty-list"><b>{{ __('secret_knowledge.no_bundle_config') }}</b></div>
                        @endif

                        {{-- Пособия о боссах --}}
                        <div class="knowledge-section">{{ __('secret_knowledge.manuals_title') }}</div>
                        <div class="knowledge-hint">{{ __('secret_knowledge.manuals_hint') }}</div>

                        @forelse($manuals as $manual)
                            @php
                                $book = $manual->manual;
                                $part = $manual->bossPart;
                                $enough = $points >= $manual->required_knowledge;
                                $canStudy = ! $manual->is_studied && $manual->in_backpack && $enough;
                            @endphp
                            <table class="knowledge-card" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr><td width="8" height="10" class="gp-frame-tl"></td><td height="10" class="gp-frame-t"></td><td width="8" height="10" class="gp-frame-tr"></td></tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg" align="center">
                                        <div class="knowledge-card__title" style="color:{{ $book?->rarity?->color() }}">{{ $book?->name ?? __('secret_knowledge.boss_manual_type') }}</div>
                                        <div class="knowledge-card__row">
                                            @if($book)
                                                <div class="knowledge-card__operand">
                                                    <a class="upgrade-icon" href="{{ $itemLink((int) $book->id) }}"
                                                       data-id="{{ $book->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                       onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                                                        <img src="{{ $book->image }}" alt="{{ $book->name }}">
                                                    </a>
                                                </div>
                                            @endif

                                            <span class="knowledge-card__symbol" aria-hidden="true">→</span>

                                            @if($part)
                                                <div class="knowledge-card__operand">
                                                    <a class="upgrade-icon" href="{{ $itemLink((int) $part->id) }}"
                                                       data-id="{{ $part->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                       onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                                                        <img src="{{ $part->image }}" alt="{{ $part->name }}">
                                                    </a>
                                                </div>
                                            @endif

                                            <div class="knowledge-card__text">
                                                <div>{{ __('secret_knowledge.boss') }}:
                                                    @if($manual->monster)
                                                        <a href="{{ route('info.monster.catalog', ['id' => $manual->monster->id]) }}" target="_blank"><b>{{ $manual->monster->name }}</b> [{{ $manual->monster->lvl }}]</a>
                                                    @else
                                                        <b>—</b>
                                                    @endif
                                                </div>
                                                @if($part)
                                                    <div>{{ __('secret_knowledge.boss_part') }}: <b style="color:{{ $part->rarity?->color() }}">{{ $part->name }}</b></div>
                                                @endif
                                                <div class="knowledge-card__desc">{{ __('secret_knowledge.manual_effect') }}</div>
                                            </div>

                                            <div class="knowledge-card__reqs">
                                                <div class="knowledge-card__reqs-title">{{ __('Требования') }}</div>
                                                <div class="knowledge-card__req">
                                                    <span>{{ __('secret_knowledge.points_short') }}</span>
                                                    <span class="{{ $enough ? 'req-ok' : 'req-fail' }}">{{ number_format($points, 0, '', ' ') }} / {{ number_format($manual->required_knowledge, 0, '', ' ') }}</span>
                                                </div>
                                                <div class="knowledge-card__req">
                                                    <span>{{ __('secret_knowledge.manual_in_backpack') }}</span>
                                                    <span class="{{ $manual->in_backpack || $manual->is_studied ? 'req-ok' : 'req-fail' }}">{{ $manual->in_backpack || $manual->is_studied ? __('Да') : __('Нет') }}</span>
                                                </div>
                                            </div>

                                            <div class="knowledge-card__action">
                                                @if($manual->is_studied)
                                                    <span class="knowledge-card__done">{{ __('secret_knowledge.studied') }}</span>
                                                @else
                                                    <form method="post" action="{{ route('secret_knowledge.study', ['npc' => $npcId, 'manual' => $manual->id]) }}">
                                                        @csrf
                                                        <span class="butt1 pointer {{ $canStudy ? '' : 'disabled' }}"><span>
                                                            <input type="submit" class="grnn" value="{{ __('secret_knowledge.study') }}" @disabled(! $canStudy)>
                                                        </span></span>
                                                    </form>
                                                    @if(! $manual->in_backpack)
                                                        <span class="req-fail">{{ __('secret_knowledge.not_owned') }}</span>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr><td width="8" height="10" class="gp-frame-bl"></td><td height="10" class="gp-frame-b"></td><td width="8" height="10" class="gp-frame-br"></td></tr>
                                </tbody>
                            </table>
                        @empty
                            <div class="empty-list"><b>{{ __('secret_knowledge.no_manuals') }}</b></div>
                        @endforelse
                    </td>
                    <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                </tr>
                <tr>
                    <td width="10" height="10" class="common-inner-block-bl"></td>
                    <td height="10" class="common-inner-block-b"></td>
                    <td width="10" height="10" class="common-inner-block-br"></td>
                </tr>
                </tbody>
            </table>
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

@if($bundle)
    @php
        $studyConfig = [
            'state' => $activeStudy,
            'bundles' => $bundleCount,
            'startUrl' => route('secret_knowledge.bundle.start', ['npc' => $npcId]),
            'completeUrl' => route('secret_knowledge.bundle.complete', ['npc' => $npcId]),
            'cancelUrl' => route('secret_knowledge.bundle.cancel', ['npc' => $npcId]),
            'csrf' => csrf_token(),
            'refreshTokenUrl' => route('player.csrf-token'),
            'labels' => ['running' => __('secret_knowledge.study_running'), 'error' => __('secret_knowledge.network_error')],
        ];
    @endphp
    <style>
        .study-modal { position: fixed; inset: 0; z-index: 9000; display: flex; align-items: center; justify-content: center; background: rgba(24,18,12,.58); }
        .study-modal[hidden], [data-study] [hidden] { display: none !important; }
        .study-modal__body { width: 440px; max-width: calc(100% - 24px); }
        .study-modal__content { padding: 12px 18px 8px; color: #5c351d; text-align: center; }
        .study-modal__item { position: relative; width: 72px; height: 71px; margin: 4px auto 10px; background: url('{{ asset('main/images/user-reward-frame.png') }}') no-repeat; }
        .study-modal__item img { position: absolute; top: 5px; left: 6px; width: 60px; height: 60px; }
        .study-modal__message { min-height: 16px; margin: 6px 0; font-weight: bold; }
        /* «Манускрипт», который Финко читает вслух: строки на латыни и иврите печатаются по буквам. */
        .study-script { height: 34px; margin: 0 4px 6px; overflow: hidden; padding: 4px 8px; border: 1px solid #d8b781; border-radius: 4px; background: rgba(255, 248, 225, .7); box-shadow: inset 0 1px 3px rgba(92, 53, 29, .25); color: #6b3f1d; font: 13px/26px Georgia, 'Times New Roman', 'Noto Sans Runic', 'Noto Sans Glagolitic', serif; white-space: nowrap; text-overflow: clip; }
        .study-script[data-italic='1'] { font-style: italic; }
        .study-script:is([lang=ar], [lang=he], [lang=sa]) { font-size: 15px; }
        .study-script:is([lang=non], [lang=cu]) { font-size: 14px; letter-spacing: .5px; }
        .study-script::after { content: '▌'; margin-inline-start: 1px; color: #8d2616; font-style: normal; animation: study-caret .8s steps(1) infinite; }
        @keyframes study-caret { 50% { opacity: 0; } }
        @media (prefers-reduced-motion: reduce) { .study-script::after { animation: none; } }
        .study-modal__message.is-found { color: #247327; }
        .study-modal__actions { display: flex; justify-content: center; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .study-progress { position: relative; height: 31px; margin: 4px 0 8px; overflow: hidden; }
        .study-progress__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; background: url('{{ asset('img/progressbar/progress-bar-1-bg.png') }}') 0 -54px repeat-x; }
        .study-progress__fill { width: 0; height: 27px; background: url('{{ asset('img/progressbar/progress-bar-1-bg.png') }}') 0 -27px repeat-x; }
        .study-progress__border { position: absolute; inset: 0; height: 31px; pointer-events: none; }
        .study-progress__border::before, .study-progress__border::after { content: ''; position: absolute; top: 0; width: 20px; height: 31px; background: url('{{ asset('img/progressbar/progress-bar-1-border.png') }}') no-repeat; }
        .study-progress__border::before { left: 0; }
        .study-progress__border::after { right: 0; background-position: 0 -31px; }
        .study-progress__center { position: absolute; inset: 0 20px; background: url('{{ asset('img/progressbar/progress-bar-1-border.png') }}') 0 -62px repeat-x; }
        .study-progress__text { position: absolute; inset: 0; line-height: 31px; font-weight: bold; color: #fff; text-shadow: 0 1px 2px #321a11; }
    </style>
    <div data-study data-config="{{ json_encode($studyConfig, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">
        <div class="study-modal" data-study-modal hidden role="dialog" aria-modal="true" aria-label="{{ __('secret_knowledge.study_title') }}">
            <div class="study-modal__body popup_global_container">
                <div class="popup-top-left">
                    <div class="popup-top-right"><div class="popup-top-center"><div class="popup_global_title">{{ __('secret_knowledge.study_title') }}</div></div></div>
                    <div class="popup_global_close_btn" data-study-dismiss role="button" tabindex="0" aria-label="{{ __('secret_knowledge.close') }}"></div>
                </div>
                <div class="popup-left-center"><div class="popup-right-center"><div class="popup_global_content" style="padding:8px 12px 3px;">
                    <div class="study-modal__content">
                        <div class="study-modal__item"><img src="{{ $bundle->image }}" width="60" height="60" alt="{{ $bundle->name }}"></div>
                        <div class="study-progress" data-study-progress hidden>
                            <div class="study-progress__bg"><div class="study-progress__fill" data-study-fill></div></div>
                            <div class="study-progress__border"><div class="study-progress__center"></div></div>
                            <div class="study-progress__text" data-study-percent>0%</div>
                        </div>
                        <div class="study-modal__message" data-study-message role="status"></div>
                        <div class="study-script" data-study-script aria-hidden="true" hidden></div>
                        <div class="study-modal__actions">
                            <span class="butt1 pointer" data-study-again-wrap hidden><span><input type="button" class="grnn" data-study-again value="{{ __('secret_knowledge.study_again') }}"></span></span>
                            <span class="butt1 pointer" data-study-cancel-wrap hidden><span><input type="button" class="grnn" data-study-cancel value="{{ __('secret_knowledge.study_cancel') }}"></span></span>
                            <span class="butt1 pointer" data-study-close-wrap hidden><span><input type="button" class="grnn" data-study-close value="{{ __('secret_knowledge.close') }}"></span></span>
                        </div>
                    </div>
                </div></div></div>
                <div class="popup-left-bottom"><div class="popup-right-bottom"><div class="popup-bottom-center"></div></div></div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/secret-knowledge.js') }}?v={{ filemtime(public_path('js/secret-knowledge.js')) }}" defer></script>
@endif

{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</body>
</html>
