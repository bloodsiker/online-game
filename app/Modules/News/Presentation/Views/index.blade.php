<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" class="lang-{{ app()->getLocale() }} {{ app()->getLocale() }} {{ auth()->check() ? 'isUser' : 'isGuest' }}">
<head>
    <title>{{ __('homepage.game_name') }}</title>
    @vite('resources/js/app.js')

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ __('homepage.game_name') }}">
    <meta name="twitter:description" content="{{ __('homepage.game_name') }}">
    <meta name="twitter:image" content="{{ asset('main/img/logo_tshare.png') }}">
    <meta name="twitter:domain" content="">
    <meta name="keywords"
          content=""/>
    <meta name="description"
          content="{{ __('homepage.game_name') }}"/>

    <meta property="og:title" content="{{ __('homepage.game_name') }}"/>
    <meta property="og:image" content="{{ asset('main/img/logo_tshare.png') }}"/>
    <meta property="og:type" content="article"/>
    <meta property="og:url" content="https://dwar.ltd/"/>
    <meta property="og:site_name" content="{{ __('homepage.game_name') }}"/>
    <meta property="fb:admins" content="1"/>
    <meta property="og:description" content="{{ __('homepage.game_name') }}"/>
    <link rel="icon" type="image/png" href="favicon.ico"/>
    <link rel="alternate" type="application/rss+xml" title="{{ __('homepage.game_name') }}." href=""/>


    <link rel="image_src" href="{{ asset('main/img/logo_tshare.png') }}"/>
    <meta name="viewport" content="width=device-width, minimum-scale=1.0, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/>


    <script src="{{ asset('main/js/common.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('main/css/main.css') }}">
    @if(config('game.prelaunch'))
        <link rel="stylesheet" href="{{ asset('css/prelaunch.css') }}?v={{ filemtime(public_path('css/prelaunch.css')) }}">
    @endif
    <link rel="stylesheet" type="text/css" href="{{ asset('main/css/timer.css') }}">
    <script language="javaScript" src="{{ asset('main/js/simple_alt.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('main/css/art_alt.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('css/item_tooltip.css') }}?v={{ filemtime(public_path('css/item_tooltip.css')) }}">
    <script src="{{ asset('main/js/jquery-1.7.1.min.js') }}"></script>

    <link rel="stylesheet" href="{{ asset('main/css/portal_desktop.min.css') }}"/>

    <style type="text/css">@font-face {
            font-family: PT Serif;
            font-style: normal;
            font-weight: 400;
            src: url(cf-fonts/s/pt-serif/5.0.19/cyrillic-ext/400/normal.woff2);
            unicode-range: U+0460-052F, U+1C80-1C88, U+20B4, U+2DE0-2DFF, U+A640-A69F, U+FE2E-FE2F;
            font-display: swap;
        }

        @font-face {
            font-family: PT Serif;
            font-style: normal;
            font-weight: 400;
            src: url(cf-fonts/s/pt-serif/5.0.19/cyrillic/400/normal.woff2);
            unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116;
            font-display: swap;
        }

        @font-face {
            font-family: PT Serif;
            font-style: normal;
            font-weight: 400;
            src: url(cf-fonts/s/pt-serif/5.0.19/latin/400/normal.woff2);
            unicode-range: U+0000-00FF, U+0131, U+0152-0153, U+02BB-02BC, U+02C6, U+02DA, U+02DC, U+0304, U+0308, U+0329, U+2000-206F, U+2074, U+20AC, U+2122, U+2191, U+2193, U+2212, U+2215, U+FEFF, U+FFFD;
            font-display: swap;
        }

        @font-face {
            font-family: PT Serif;
            font-style: normal;
            font-weight: 400;
            src: url(cf-fonts/s/pt-serif/5.0.19/latin-ext/400/normal.woff2);
            unicode-range: U+0100-02AF, U+0304, U+0308, U+0329, U+1E00-1E9F, U+1EF2-1EFF, U+2020, U+20A0-20AB, U+20AD-20CF, U+2113, U+2C60-2C7F, U+A720-A7FF;
            font-display: swap;
        }</style>

    <link rel="stylesheet" href="{{ asset('main/css/index.min.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/ru_fonts.css') }}"/>
    <link rel="stylesheet" href="{{ asset('main/css/i18n_ru.css') }}"/>


    <script type="text/javascript" src="{{ asset('main/js/html5shiv-3.7.3.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/js/modernizr.custom.min.js') }}"></script>

    <script type="text/javascript" src="{{ asset('main/js/vendors.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/js/fancybox.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/js/lib.min.js') }}"></script>


    <script type="text/javascript" src="{{ asset('main/js/cufon-1.09i.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/js/Cambria_400.font.js') }}"></script>


    <script type="text/javascript">
        if (typeof Cufon != 'undefined') {
            Cufon.replace('.js-font-nav-lvl-1', {
                fontFamily: 'Cambria',
                color: '-linear-gradient(0.5=#fffb6a, 0.8=#F0A043)',
                textShadow: '0 1px 1px black, 0 -1px 1px black, 1px 0 rgba(0, 0, 0, 0.5), -1px 0 rgba(0, 0, 0, 0.5)'
            });

            Cufon.replace('.js-font-nav-lvl-2', {
                fontFamily: 'Cambria',
                color: '-linear-gradient(0.5=#fffb6a, 0.8=#F0A043)',
                textShadow: '0 1px 1px black, 0 -1px 1px black, 1px 0 rgba(0, 0, 0, 0.5), -1px 0 rgba(0, 0, 0, 0.5)'
            });

            Cufon.replace('.cufon-mp', {
                fontFamily: 'Cambria',
                fontSize: '18px',
                color: '-linear-gradient(0.7=#a87f4f, #ffffb9, #a87f4f)',
                textShadow: '1px 1px rgba(0,0,0,0.9), -1px 1px rgba(0, 0, 0, 0.6), 1px -1px rgba(0, 0, 0, 0.6)'
            });

            Cufon.replace('.cufon-common', {
                fontFamily: 'Cambria',
                fontSize: '14px',
                color: '-linear-gradient(0.5=#fffb6a, 0.8=#F0A043)',
                textShadow: '0 1px 1px black, 0 -1px 1px black, 1px 0 rgba(0, 0, 0, 0.5), -1px 0 rgba(0, 0, 0, 0.5)'
            });
            @if(app()->getLocale() === 'ru')
                Cufon.replace('.homepage-heading--reference', {
                    fontFamily: 'Cambria',
                    fontSize: '18px',
                    color: '-linear-gradient(0.7=#a87f4f, #ffffb9, #a87f4f)',
                    textShadow: '1px 1px rgba(0,0,0,0.9), -1px 1px rgba(0,0,0,0.6), 1px -1px rgba(0,0,0,0.6)'
                });
            @endif
        }
    </script>


    <script type="text/javascript" src="{{ asset('main/js/'.app()->getLocale().'.js') }}"></script>

    <script type="text/javascript" src="{{ asset('main/js/underscore.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/js/backbone.js') }}"></script>
    <script type="text/javascript" src="{{ asset('main/js/main.js') }}"></script>

    {{-- PIXI/canvas здесь не используются: канвас нужен только iframe меню (Interface/menu.blade.php) --}}

    <link rel="stylesheet" href="{{ asset('main/css/index_glav.css') }}"/>

    <style>
        .homepage-heading { color: #edcb86; font: bold 16px/24px Tahoma, Arial, sans-serif; text-align: center; text-shadow: 1px 1px #281b10; }
        .homepage-heading--reference { position: relative; top: -2px; font: normal 18px/26px Cambria, Georgia, 'Times New Roman', serif; }
        html[lang="uk"] .homepage-heading--reference {
            background: linear-gradient(to bottom, #a87f4f 10%, #ffffb9 55%, #a87f4f 100%);
            background-clip: text;
            -webkit-background-clip: text;
            color: transparent;
            text-shadow: none;
            filter: drop-shadow(1px 1px 0 rgba(0,0,0,.9));
        }
        .homepage-forum__content { padding: 6px 9px 9px; background: #211b15; border: 1px solid #554537; }
        .homepage-forum__topic { display: block; padding: 7px 2px; border-bottom: 1px solid #4b3d30; text-decoration: none; }
        .homepage-forum__title { display: block; color: #e69934; font: bold 11px/16px Tahoma, Arial, sans-serif; overflow-wrap: anywhere; }
        .homepage-forum__topic:hover .homepage-forum__title { color: #ffe5ab; }
        .homepage-forum__meta { display: flex; justify-content: space-between; gap: 5px; margin-top: 3px; color: #b59b7a; font: 10px/14px Tahoma, Arial, sans-serif; }
        .homepage-forum__all { display: block; padding-top: 8px; color: #dfbc7d; text-align: right; font-size: 11px; }
        .homepage-forum__empty { padding: 6px 0; margin: 0; color: #b59b7a; font-size: 11px; }
        .homepage-language {
            position: absolute;
            right: 12px;
            top: 68px;
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 5px;
            margin: 0;
            padding: 5px 7px;
            border: 1px solid #796043;
            border-radius: 5px;
            background: rgba(35, 27, 21, .94);
            box-shadow: 0 2px 5px rgba(0, 0, 0, .35);
            color: #edcb86;
            font: 11px/24px Tahoma, Arial, sans-serif;
            white-space: nowrap;
        }
        .homepage-language button {
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 24px;
            margin: 0;
            padding: 0;
            border: 1px solid #796043;
            border-radius: 3px;
            background: linear-gradient(#4a3d31, #2c241d);
            color: #cfb58b;
            font: bold 11px/1 Tahoma, Arial, sans-serif;
            text-shadow: 0 1px #1b140e;
            cursor: pointer;
        }
        .homepage-language button:hover,
        .homepage-language button[aria-pressed="true"] {
            border-color: #d3a655;
            background: linear-gradient(#806232, #49351f);
            color: #ffe2a2;
        }
        .homepage-language button:focus-visible { outline: 2px solid #ffe2a2; outline-offset: 2px; }
        input[type=text], input[type=password], textarea, select {
            font: normal 9pt Tahoma;
            border-radius: 3px;
            border: 1px solid #a86d4d;
            background: #fdfde3;
            margin: 2px;
            padding: 3px 2px;
            width: 100%;
        }

        .superman {
            display: inline-block;
        }

        .superman td {
            width: 60px;
            height: 60px;
        }

        .bpdig {
            border: solid 1px #6f4a24 !important;
            background-color: #6e534c !important;
            width: 38px !important;
            height: 15px !important;
            color: #f6d9a6 !important;
            font-weight: bold !important;
            margin: 2px !important;
            text-align: center !important;
            top: 22px;
            position: relative;
            left: -2px;
            font-size: 11px;
        }

        .b-post--index .b-post__content {
            font-size: 11px;
        }

        @include('partials.content-entity-styles')

        .b-great-battles__rating-inner {
            display: inline-block;
            min-width: 60px;
            height: 18px;
            padding: 0 11px 0 0;
            font-size: 0.9em;
            line-height: 19px;
            text-align: center;
            text-shadow: 0 0 1px #000, 0 0 1px #000;
            color: #dcaa4d;
            background: url(images/bg-great-battles-numbers.png) 100% -18px no-repeat;
        }

        b.butt1, b.butt1.disabled {
            height: 36px;
            font-size: 26px;
            cursor: pointer;
            background: url(images/tbl-btn2_c-left.png) left top no-repeat;
            display: inline-block;
        }

        b.butt1 b, b.butt1.disabled b {
            height: 36px;
            font-size: 26px;
            cursor: pointer;
            background: url(images/tbl-btn2_c-right.png) right top no-repeat;
            display: inline-block;
        }

        b.butt1 input, b.butt1 button.butt1, b.butt1.disabled input, b.butt1.disabled button {
            height: 36px;
            border: 0;
            color: #f8dea4 !important;
            cursor: pointer;
            font-family: Tahoma;
            font-size: 11px !important;
            font-weight: bold;
            text-decoration: none;
            margin: 0 33px;
            background: transparent url(images/tbl-btn2_center.png) center top repeat-x;
            padding-bottom: 4px;
            outline: none;
        }
    </style>


</head>
<body class="b-page  b-page--home b-page--theme-home">
<div id="artifact_alt" style="left:0px;top:0px;width:300px; display:none; position:absolute; z-index: 1001;" onmouseover="this.style.display='none';"></div>
<script language="javascript" type="text/javascript">var art_alt = new Array();</script>
{!! $newsItemTooltipScript ?? '' !!}
<script>window.gebi = window.gebi || function (id) { return document.getElementById(id); };</script>
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
<script>
    var temp_effects = {};
    var strings = @json(__('homepage.effects'));
    top.frames['main_frame'] = {};
    top.frames['main_frame'].temp_effects = {};
    top.frames['main_frame'].strings = strings;
    top.noIframeAlt = true;
</script>

<div class="b-page__header">
    <form class="homepage-language" method="post" action="{{ route('homepage.language') }}" aria-label="{{ __('homepage.language') }}">
        @csrf
        @if($selectedNews)<input type="hidden" name="news_id" value="{{ $selectedNews->id }}">@endif
        <button type="submit" name="language" value="ru" title="Русский" aria-label="Русский" aria-pressed="{{ app()->getLocale() === 'ru' ? 'true' : 'false' }}">RU</button>
        <button type="submit" name="language" value="uk" title="Українська" aria-label="Українська" aria-pressed="{{ app()->getLocale() === 'uk' ? 'true' : 'false' }}">UK</button>
    </form>
    <header class="b-nav-lvl-1" style=" width: 900px; ">

        <div class="b-nav-lvl-1__decor-border"></div>
        <div class="b-nav-lvl-1__decor-menu-side"></div>
        <div class="b-nav-lvl-1__decor-menu-top"></div>

        <div class="b-nav-lvl-1__auth">
            <div id="userHeadBlock">
                <div class="b-nav-lvl-1__create">
                    <a class="b-auth-button" href="{{ config('game.prelaunch') ? route('index', ['launch' => 1]) : route('register') }}" @if(config('game.prelaunch')) data-prelaunch-open @endif>
				<span class="b-auth-button__content">
					<span class="b-aside__impo-link" style="font-size: 15px;font-weight: bold;position: relative;color: #d49b2c;left: 1px;top: -3px;">{{ __('homepage.registration') }}</span>
				</span>
                    </a>
                </div>
            </div>

        </div>
        <div class="b-nav-lvl-1__logo">
            <a class="b-auth-button" style="position: relative;left: 24px;top: 7px;">
					<span class="b-auth-button__content">
					<span class="b-aside__impo-link" style="font-size: 15px;font-weight: bold;position: relative;color: #d49b2c;left: 1px;top: -3px;">{{ __('homepage.online') }}: <span data-online-count>{{ $onlineCount }}</span></span>
				</span>
            </a>

        </div>

        <div class="b-nav-lvl-1__menu">
            <ul class="b-nav-lvl-1__menu-list">
                <li class="b-nav-lvl-1__menu-item">
                    <a class="b-aside__impo-link" href="{{ route('index') }}"
                       style="font-size: 13px;font-weight: bold;position: relative;color: #d49b2c;left: 23px;top: 8px;"><span>{{ __('homepage.home') }}</span></a>
                </li>

                <li class="b-nav-lvl-1__menu-item">
                    <a class="b-aside__impo-link" href="{{ route('library.index') }}"
                       style="font-size: 13px;font-weight: bold;position: relative;color: #d49b2c;left: 8px;top: 8px;"><span>{{ __('homepage.library') }}</span></a>
                </li>

                <li class="b-nav-lvl-1__menu-item">
                    <a class="b-aside__impo-link" href="{{ route('forum.index') }}"
                       style="font-size: 13px;font-weight: bold;position: relative;color: #d49b2c;left: 23px;top: 8px;"><span>{{ __('homepage.forum') }}</span></a>
                </li>

                <li class="b-nav-lvl-1__menu-item">
                    <a class="b-aside__impo-link" href="#"
                       style="font-size: 13px;font-weight: bold;position: relative;color: #d49b2c;left: 10px;top: 8px;"><span>{{ __('homepage.telegram') }}</span></a>
                </li>

                <li class="b-nav-lvl-1__menu-item">
                    <a class="b-aside__impo-link" href="#homepage-support"
                       style="font-size: 13px;font-weight: bold;position: relative;color: #d49b2c;left: 11px;top: 8px;"><span>{{ __('homepage.contacts') }}</span></a>
                </li>

            </ul>


        </div>
    </header>



</div>

<div class="b-page__nav">

</div>

<div class="b-page__container">

    <div class="b-page__content">
        <aside class="b-page__sidebar b-page__sidebar--left">
            <div class="b-aside b-aside--left">
                <div class="b-aside__column" style=" position: relative; top: -110px; ">
                    <div style="text-align: left;position: absolute;top: -142px;left: -2px;">
                        <div align="center">
                            <script language="javaScript" src="{{ asset('main/js/counter.js') }}"></script>
                        </div>
                    </div>

                    <div class="b-aside__shape b-shape--try-fight">
                        <div class="b-shape__header">
                            <div class="b-shape__decor"></div>
                            <div class="homepage-heading homepage-heading--reference">{{ __('homepage.login_form') }}</div>
                        </div>
                        <div class="b-shape__content">
                            <div class="">

                                <div style="position: relative;top: -51px;left: 24px;">
                                    <div class="flex-popap">
                                        <div class="popap">
                                            <div id="ifAuthDiv" class="pop-reg">
                                                <div id="mainContent">
                                                    <div class="pop-block ">
                                                        <div id="popup" class="popup" style="display: none;">
                                                            <div class="popup-title nonselectable"></div>
                                                            <div class="popup-text"></div>
                                                            <span class="btn fixed okButton">
                                                            <span class="btn-link fixed yellow">ок</span>
                                                        </span>
                                                        </div>

                                                        <div class="pop-content">

                                                            <div class="pop-overlay" style="display: none;"></div>
                                                            <div id="loading" class="pop-loader"
                                                                 style="display: none;"></div>
                                                            <div id="viewContent">

                                                                <form style="opacity: 1;" action="{{ config('game.prelaunch') ? route('index', ['launch' => 1]) : route('login') }}" method="{{ config('game.prelaunch') ? 'get' : 'post' }}" @if(config('game.prelaunch')) data-prelaunch-login @endif>
                                                                    @if(config('game.prelaunch'))
                                                                        <input type="hidden" name="launch" value="1">
                                                                    @else
                                                                        @csrf
                                                                    @endif
                                                                    <div class="form-wrapper">
                                                                        <fieldset class="p-v-20">

                                                                        </fieldset>
                                                                        <fieldset class="lt pt-10">

                                                                            <div class="input-block">
                                                                                <div style="display: none;" class="hint hint_top emailHint" name="email-hint">
                                                                                    <div class="hint-text">{{ __('homepage.email_hint') }}
                                                                                    </div>
                                                                                </div>
                                                                                <input class="form-input" type="text"
                                                                                       @if(!config('game.prelaunch')) name="email" @endif id="userEmail" value=""
                                                                                       placeholder="{{ __('homepage.email_hint') }}">
                                                                            </div>
                                                                        </fieldset>
                                                                        <fieldset class="lt pt-20 pb-30">
                                                                            <input class="form-input" type="password"
                                                                                   @if(!config('game.prelaunch')) name="password" @endif id="userPassword"
                                                                                   maxlength="50"
                                                                                   placeholder="{{ __('homepage.password_hint') }}">
                                                                        </fieldset>

                                                                    </div>

                                                                <span style="position: relative;left: 5px;top: -22px;">
                                                                    <button type="{{ config('game.prelaunch') ? 'button' : 'submit' }}" @if(config('game.prelaunch')) data-prelaunch-open @endif><span class="btn big fll quickStartButton">
                                                                        <span class="btn-link yellow"><span class="title">{{ __('homepage.login') }}</span></span>
                                                                    </span></button>
                                                                    <span @if(config('game.prelaunch')) data-prelaunch-open role="button" tabindex="0" @else onclick="window.location='{{ route('register') }}'" @endif class="btn big flr createAccountButton" style="position: relative;left: -13px;">
                                                                        <span class="btn-link yellow"><span class="title">{{ __('homepage.create_account') }}</span></span>
                                                                    </span>
                                                                </span>
                                                                <input type="submit" style="width: 0px; height: 0px; visibility: hidden;">
                                                                </form>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>


                    <div class="b-shape__header">
                        <div class="b-shape__decor"></div>
                        <div class="homepage-heading homepage-heading--reference">{{ __('homepage.screenshots') }}</div>
                    </div>
                    <div class="b-shape__content b-shape__content--column">
                        <div class="b-slider b-slider--default">
                            <div class="b-slider__preview">
                                <div class="b-slider__move js-preview-slider">
                                    <a href="{{ asset('main/images/screen/1.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/1.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/2.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/2.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/3.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/3.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/4.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/4.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/5.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/5.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/6.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/6.png') }}">
                                    </a>

                                    <a href="{{ asset('main/images/screen/7.png') }}" rel="sidebar-screens"
                                       class="b-slider__element js-fancybox">
                                        <img class="b-slider__image" src="" data-lazy="{{ asset('main/images/screen/7.png') }}">
                                    </a>
                                </div>
                            </div>
                            <div class="b-slider__thumbs">
                                <div class="b-slider__move js-thumbs-slick">
                                    <a href="{{ asset('main/images/screen/1.png') }}" rel="sidebar-screens" class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/1.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/2.png') }}" rel="sidebar-screens"
                                       class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/2.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/3.png') }}" rel="sidebar-screens"
                                       class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/3.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/4.png') }}" rel="sidebar-screens"
                                       class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/4.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/5.png') }}" rel="sidebar-screens"
                                       class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/5.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/6.png') }}" rel="sidebar-screens"
                                       class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/6.png') }}">
                                    </a>
                                    <a href="{{ asset('main/images/screen/7.png') }}" rel="sidebar-screens"
                                       class="b-slider__thumb fancybox">
                                        <img width="78" height="60" class="b-slider__image" src="index.html"
                                             data-lazy="{{ asset('main/images/screen/7.png') }}">
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    @include('news::partials.hot-forum')

                    <div class="b-aside__discuss">
                        <div class="b-shape__content">
                            <div class="b-shape__header">
                                <div class="b-shape__decor"></div>
                                <div class="homepage-heading homepage-heading--reference">{{ __('homepage.game_opened') }}</div>
                            </div>
                            <br>

                            <div class="timer">
                                <div class="timer-countdown">
                                    <div class="timer-block">
                                        <span id="days" class="title-count"></span>
                                        <span id="days-word" class="sub-count">{{ __('homepage.days') }}</span>
                                    </div>
                                    <div class="timer-block">
                                        <span id="hours" class="title-count"></span>
                                        <span id="hours-word" class="sub-count">{{ __('homepage.hours') }}</span>
                                    </div>
                                    <div class="timer-block">
                                        <span id="minutes" class="title-count"></span>
                                        <span id="minutes-word" class="sub-count">{{ __('homepage.minutes') }}</span>
                                    </div>
                                    <div class="timer-block">
                                        <span id="seconds" class="title-count"></span>
                                        <span id="seconds-word" class="sub-count">{{ __('homepage.seconds') }}</span>
                                    </div>
                                </div>
                            </div>
                            <script>
                                var endDate = new Date("2025-07-01T00:00:00Z");

                                function updateTimer() {
                                    var now = new Date();
                                    var timeDifference = endDate - now;

                                    var days = Math.abs(Math.floor(timeDifference / (1000 * 60 * 60 * 24)));
                                    var hours = Math.abs(Math.floor((timeDifference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)));
                                    var minutes = Math.abs(Math.floor((timeDifference % (1000 * 60 * 60)) / (1000 * 60)));
                                    var seconds = Math.abs(Math.floor((timeDifference % (1000 * 60)) / 1000));

                                    document.getElementById("days").innerText = (timeDifference < 0 ? "" : "-") + days;
                                    document.getElementById("hours").innerText = (timeDifference < 0 ? "" : "-") + hours;
                                    document.getElementById("minutes").innerText = (timeDifference < 0 ? "" : "-") + minutes;
                                    document.getElementById("seconds").innerText = (timeDifference < 0 ? "" : "-") + seconds;
                                }
                                setInterval(updateTimer, 1000);
                            </script>
                        </div>


                    </div>

                    <div class="b-aside__support" id="homepage-support">
                        <div class="b-aside__support-content">
                            <div class="b-aside__support-title">
                                <div class="homepage-heading homepage-heading--reference">{{ __('homepage.support') }}</div>
                            </div>
                            <a href="mailto:maldini2@ukr.net" class="b-aside__support-link">Email:
                                <br>
                                <span class="__cf_email__" >maldini2@ukr.net</span></a>

                        </div>
                    </div>
                </div>
            </div>
        </aside>
        <main class="b-page__index">


            <div class="b-index">
                <div class="b-index__header u-style-bg-gradient">

                </div>
                <div class="b-index__content u-style-bg-gradient">

                    <div id="post-list">
                        @if(isset($selectedNews) && $selectedNews)
                            <div class="b-news-item">
                                <div class="b-post__container">
                                    <article class="b-post b-post--index">
                                        <div class="b-post__header">
                                            <h2 class="b-post__footer">
                                                <a class="b-post__title-link c-red" href="{{ route('index', ['n' => $selectedNews->id]) }}">
                                                    <b>{{ $selectedNews->title }}</b>
                                                </a>
                                            </h2>
                                        </div>
                                    </article>
                                </div>
                                <article class="b-post b-post--index">
                                    <div class="b-post__container">
                                        <div class="b-post__content js-content">
                                            {!! $selectedNews->rendered_description ?? $selectedNews->description !!}
                                            <div class="clear"></div>
                                        </div>
                                    </div>
                                    <div class="b-post__footer">
                                        <div class="b-post__more">
                                            <a href="{{ route('index') }}" class="b-link b-link--default">{{ __('homepage.back_to_news') }}</a>
                                        </div>
                                        <div class="b-post__comments">
                                            <a class="b-link b-link--default" href="{{ route('index', ['n' => $selectedNews->id]) }}">
                                                <span class="b-icon b-icon--quant">{{ $selectedNews->visibleComments->count() }}</span>{{ __('homepage.comment') }}
                                            </a>
                                            <span align="right"
                                                  style="font-size: 9px;color: #a56262;position: absolute;top: 1px;right: 121px;">
                                                <b style='color:darkred;'><img src='{{ asset('main/images/news/date.png') }}' style='width: 10px;position: relative;top: -1px;'> {{ $selectedNews->created_at?->format('d.m.Y H:i') }}</b> | {{ __('homepage.views') }}:
                                                <img src='{{ asset('main/images/news/view.png') }}' style='width: 10px;position: relative;top: -1px;'>
                                                <b style='color:green;'>{{ $selectedNews->views_count }}</b>
                                            </span>
                                        </div>
                                    </div>
                                </article>

                                @if($selectedNews->allow_comments)
                                    <div class="b-post__container" style="padding: 8px 12px;">
                                        @auth
                                            <form method="post" action="{{ route('news.comment', $selectedNews->id) }}">
                                                {{ csrf_field() }}
                                                <textarea name="message" rows="3" style="width: 100%; box-sizing: border-box;" required></textarea>
                                                <div style="text-align: right; margin-top: 6px;">
                                                    <button type="submit">{{ __('homepage.add_comment') }}</button>
                                                </div>
                                            </form>
                                        @else
                                            <div style="color:#8a5a2b;">{{ __('homepage.comments_login') }}</div>
                                        @endauth
                                    </div>
                                @endif

                                @if($selectedNews->visibleComments->isNotEmpty())
                                    <div class="b-post__container" style="padding: 8px 12px;">
                                        @foreach($selectedNews->visibleComments as $comment)
                                            <div style="border-top: 1px solid #d7b787; padding: 6px 0;">
                                                <b style="color:#7a3010;">{{ $comment->user?->player?->name ?? $comment->user?->name ?? __('homepage.player_fallback') }}</b>
                                                <span style="color:#8a8a8a; font-size: 10px;">{{ $comment->created_at?->format('d.m.Y H:i') }}</span>
                                                <div>{{ $comment->message }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <div class="b-divider-2">
                                <div class="b-divider-2__l"><div class="b-divider-2__l-inner"></div></div>
                                <div class="b-divider-2__r"><div class="b-divider-2__r-inner"></div></div>
                                <div class="b-divider-2__c"></div>
                            </div>
                        @endif

                        @if(!$selectedNews && isset($newsPosts) && $newsPosts->isNotEmpty())
                            @foreach($newsPosts as $newsPost)
                                @if(!$selectedNews || $selectedNews->id !== $newsPost->id)
                                    <div class="b-news-item">
                                        <div class="b-post__container">
                                            <article class="b-post b-post--index">
                                                <div class="b-post__header">
                                                    <h2 class="b-post__footer">
                                                        <a class="b-post__title-link c-red" href="{{ route('index', ['n' => $newsPost->id]) }}">
                                                            <b>{{ $newsPost->title }}</b>
                                                        </a>
                                                    </h2>
                                                </div>
                                            </article>
                                        </div>
                                        <article class="b-post b-post--index">
                                            <div class="b-post__container">
                                                <div class="b-post__content js-content">
                                                    {!! $newsPost->rendered_description ?? $newsPost->description !!}
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                            <div class="b-post__footer">
                                                <div class="b-post__more">
                                                    <a href="{{ route('index', ['n' => $newsPost->id]) }}" class="b-link b-link--default">{{ __('homepage.details') }}</a>
                                                </div>
                                                <div class="b-post__comments">
                                                    <a class="b-link b-link--default" href="{{ route('index', ['n' => $newsPost->id]) }}">
                                                        <span class="b-icon b-icon--quant">{{ $newsPost->visible_comments_count }}</span>{{ __('homepage.comment') }}
                                                    </a>
                                                    <span align="right"
                                                          style="font-size: 9px;color: #a56262;position: absolute;top: 1px;right: 121px;">
                                                        <b style='color:darkred;'><img src='{{ asset('main/images/news/date.png') }}' style='width: 10px;position: relative;top: -1px;'> {{ $newsPost->created_at?->format('d.m.Y H:i') }}</b> | {{ __('homepage.views') }}:
                                                        <img src='{{ asset('main/images/news/view.png') }}' style='width: 10px;position: relative;top: -1px;'>
                                                        <b style='color:green;'>{{ $newsPost->views_count }}</b>
                                                    </span>
                                                </div>
                                            </div>
                                        </article>
                                        <br>
                                    </div>
                                    <div class="b-divider-2">
                                        <div class="b-divider-2__l"><div class="b-divider-2__l-inner"></div></div>
                                        <div class="b-divider-2__r"><div class="b-divider-2__r-inner"></div></div>
                                        <div class="b-divider-2__c"></div>
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        @if(!$selectedNews && $newsPosts->isEmpty())
                            <p>{{ __('homepage.no_news') }}</p>
                        @endif


                    </div>
                    <center>
                        <div class="b-pager b-pager--default pull-right">
                            <div class="b-pager__container">
                                <ul class="b-pager__list">
                                </ul>
                            </div>
                        </div>
                    </center>
                </div>
                <div class="b-index__footer text-center u-style-bg-gradient">


                    <a class="b-link b-link--default" href="#">{{ __('homepage.all_news') }}</a>
                </div>
            </div>

            <div class="b-index">
                <div class="b-index__content u-style-bg-gradient">
                    <div class="b-shape__header">
                        <div class="b-shape__decor"></div>
                        <div class="homepage-heading">{{ __('homepage.game_name') }}</div>
                    </div>
                    <div class="b-shape__content" style="padding: 10px 14px; line-height: 1.6; color: #4b3423; background-image: linear-gradient(90deg, #dcaa68 10%, #ffcc89 50%, #dcaa68 90%); font-size: 11px;">
                        {!! __('homepage.lore') !!}
                    </div>
                </div>
            </div>

            <footer class="b-footer">
                <div class="b-footer__content">
                    <a href="#" target="_blank"><b>{{ __('homepage.license') }}</b></a><br>
                    2024-{{ date('Y') }}
                </div>
            </footer>
        </main>
        <aside class="b-page__sidebar b-page__sidebar--right">

            <div class="b-aside b-aside--right" style=" top: -110px; ">



                <div class="b-aside__column">

                    @include('news::partials.rankings')


                    <div class="b-shape__header">
                        <div class="b-shape__decor"></div>
                        <div class="homepage-heading homepage-heading--reference">{{ __('homepage.payments') }}</div>
                    </div>
                    <div>
                        <span style="position: relative;left: 33px;top: 3px;">
                            <a href="#" class="b-aside__support-link">{{ __('homepage.payments_description') }}<br>Portmone / VISA / Mastercard</a>
                            <img src="{{ asset('main/images/payment/apple.png') }}" width="80px" alt="apple">
                            <img src="{{ asset('main/images/payment/google.png') }}" width="80px" alt="google"><br>
                            <img src="{{ asset('main/images/payment/visa.png') }}" width="80px" alt="viza">
						</span>
                    </div>
                </div>

            </div>
        </aside>
    </div>
</div>


<script>
    document.addEventListener('click', function (event) {
        var link = event.target.closest('[data-library-info-popup]');
        if (!link) return;
        event.preventDefault();
        window.open(link.href, '', 'width=730,height=800,resizable=yes,scrollbars=yes');
    });
</script>
@if(config('game.prelaunch'))
    @include('news::partials.prelaunch-modal')
@endif
</body>
</html>
