<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') — Библиотека</title>
    <script src="{{ asset('main/js/common.js') }}"></script>
    <script src="{{ asset('main/js/simple_alt.js') }}"></script>
    <link rel="stylesheet" href="{{ asset('main/css/main_register.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/register.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/index.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/css.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/mainnew.css') }}">
    <link rel="stylesheet" href="{{ asset('main/css/art_alt.css') }}">
    <link rel="stylesheet" href="{{ asset('css/item_tooltip.css') }}?v={{ filemtime(public_path('css/item_tooltip.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/library_game_frame.css') }}?v={{ filemtime(public_path('css/library_game_frame.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/library_injury_catalog.css') }}?v={{ filemtime(public_path('css/library_injury_catalog.css')) }}">
    <link rel="stylesheet" href="{{ asset('css/library_artifact_catalog.css') }}?v={{ filemtime(public_path('css/library_artifact_catalog.css')) }}">
    <style>
        html, body { min-height: 100%; }
        body {
            color: #5e3d29;
            background-image: url('{{ asset('main/images/theme_old/bg.gif') }}');
        }
        .library-main { top: 100px; padding-bottom: 100px; }
        .library-columns { position: relative; top: -40px; display: flex; min-height: calc(100vh - 60px); align-items: stretch; }
        .library-column-left { display: flex; float: none; width: 290px; align-self: flex-start; }
        .library-column-right { display: flex; float: none; width: 730px; margin-left: 0; }
        .library-sidebar-frame,
        .library-content-frame { box-sizing: border-box; display: flex; width: 100%; }
        .library-sidebar-frame .b-main-frame__cont { display: flex; flex: 1; }
        .library-content-frame .b-main-frame__cont { display: flex; min-height: 420px; flex: 1; }
        .library-forum-nav-shell { display: flex; width: 100%; margin: 0 3px; flex: 1; flex-direction: column; }
        .library-forum-nav-panel { box-sizing: border-box; display: flex; width: 100%; flex: 1; flex-direction: column; }
        .library-forum-nav-panel > .b-common-block__bgr { padding: 0; }
        .library-content-frame .b-common-block { box-sizing: border-box; display: flex; width: 100%; flex: 1; flex-direction: column; }
        .library-content-frame .b-common-block > .b-common-block__cont,
        .library-content-frame .b-common-block > .b-common-block__cont > .b-common-block__bgl { display: flex; width: 100%; flex: 1; flex-direction: column; }
        .library-content-frame .b-common-block__bgr,
        .library-forum-nav-panel > .b-common-block__bgr { box-sizing: border-box; width: 100%; flex: 1; }
        .library-nav-root {
            box-sizing: border-box;
            padding: 2px 0 3px 14px;
        }
        .library-nav-root.active { border-bottom: 1px solid #d59b6a; background: #fff3f3; }
        .library-nav-root-link {
            display: inline-block;
            max-width: calc(100% - 8px);
            padding-left: 16px;
            overflow: hidden;
            color: #700000;
            font-size: 14px;
            font-weight: bold;
            text-overflow: ellipsis;
            vertical-align: top;
            white-space: nowrap;
        }
        .library-nav-root-link:hover { color: #9b170d; }
        .library-nav-sub { margin: 0; padding: 1px 0 0; list-style: none; font-size: 11px; font-weight: bold; }
        .library-nav-sub .library-nav-sub { padding-left: 14px; }
        .library-nav-item { padding: 1px 0 2px; }
        .library-nav-link-wrapper { box-sizing: border-box; padding: 1px 4px 1px 30px; }
        .library-nav-link-wrapper.active { padding-top: 3px; padding-bottom: 3px; border-bottom: 1px solid #d59b6a; background: #fff3f3; }
        .library-nav-link {
            display: inline-block;
            max-width: calc(100% - 5px);
            padding-left: 16px;
            overflow: hidden;
            color: #7e1d0a;
            font-size: 11px;
            font-weight: bold;
            text-overflow: ellipsis;
            vertical-align: top;
            white-space: nowrap;
            background: url('{{ asset('main/images/theme_old/item_left1.gif') }}') 0 2px no-repeat;
        }
        .library-nav-link-wrapper.active .library-nav-link { color: #f00; }
        .library-common-content { padding: 14px 16px 18px; }
        .library-breadcrumbs { margin: 0 0 10px; color: #8d6950; }
        .library-breadcrumbs a { text-decoration: underline; }
        .library-header-select {
            position: relative;
            display: block;
            box-sizing: border-box;
            width: 100%;
            height: 22px;
            margin-top: 1px;
            color: #4f2715;
            font: 12px Tahoma, Arial, sans-serif;
            text-align: left;
            text-transform: none;
            border: 1px solid;
            border-color: #b6966d #d0b485 #e3cb9d;
            border-radius: 4px;
            outline: 0;
            cursor: default;
            background: #f9e4ab;
            box-shadow: 0 1px 1px rgba(255, 255, 255, .96), inset 0 1px 4px rgba(64, 11, 0, .33);
        }
        .library-header-select:focus,
        .library-header-select.default-select_focus {
            border-color: #ba9f86 #d5c0a3 #eadaba;
            background: #fff3d1;
        }
        .library-header-select:hover {
            border-color: #ba9f86 #d5c0a3 #eadaba;
            background: #feecbc;
            cursor: pointer;
        }
        .library-header-select .wrap_inner {
            position: absolute;
            width: 1px;
            height: 1px;
            overflow: hidden;
            clip: rect(0 0 0 0);
        }
        .library-header-select .value {
            position: absolute;
            top: 0;
            right: 29px;
            left: 0;
            height: 100%;
            padding: 0 5px;
            overflow: hidden;
            line-height: 20px;
            text-align: center;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .library-header-select .button {
            position: absolute;
            top: 0;
            right: 0;
            width: 26px;
            height: 100%;
        }
        .library-header-select .button_inner {
            position: absolute;
            z-index: 1;
            top: -1px;
            right: 3px;
            width: 23px;
            height: 23px;
            border: 0;
            cursor: pointer;
            background: url('{{ asset('main/images/gui/btn-select.png') }}') no-repeat;
        }
        .library-header-select.default-select_opened .button_inner {
            border-top: 0;
            border-bottom: 5px solid #fff;
            background-position: -46px 0;
        }
        .library-header-select .dropdown {
            position: absolute;
            z-index: 1000;
            top: 100%;
            left: -1px;
            display: none;
            box-sizing: content-box;
            width: 100%;
            color: #4f2715;
            text-align: left;
            border: 1px solid #e3b360;
            border-radius: 4px;
            background: #f8e5a8;
            box-shadow: 0 3px 3px 1px rgba(41, 13, 5, .4);
        }
        .library-header-select.default-select_opened .dropdown {
            display: block;
            margin-top: 2px;
        }
        .library-header-select .dropdown_list,
        .library-header-select .dropdown_list_inner { display: block; }
        .library-header-select .dropdown_list_inner {
            max-height: 320px;
            overflow-x: hidden;
            overflow-y: auto;
        }
        .library-header-select .option {
            display: block;
            padding: 3px 5px;
            color: #4f2715;
            font-weight: bold;
            line-height: 16px;
            text-align: center;
            text-decoration: none;
        }
        .library-header-select .option_selected,
        .library-header-select .option_hover,
        .library-header-select .option:hover,
        .library-header-select .option:focus {
            color: #a20000;
            outline: 0;
            background-color: #edd08f;
        }
        .library-search-table { width: 100%; border-collapse: collapse; border: 1px solid #db9f73; }
        .library-search-table td { padding: 7px; background: url('{{ asset('img/bg/info/bg_l.gif') }}') repeat; }
        .library-search-field {
            box-sizing: border-box;
            width: 100%;
            height: 25px;
            padding: 3px 6px;
            color: #7e1d0a;
            font-weight: bold;
            border: 1px solid #bf8a64;
            background: #f8e4bd;
        }
        .library-actions { margin-top: 6px; text-align: center; }
        .library-game-button {
            display: inline-block;
            min-width: 84px;
            height: 26px;
            margin: 0 2px;
            padding: 0 20px;
            border: 0;
            color: #f8dea4;
            font-weight: bold;
            line-height: 25px;
            text-align: center;
            text-shadow: 0 1px #2f1008;
            cursor: pointer;
            background: url('{{ asset('main/images/buttons/main-menu-red.png') }}') center -26px repeat-x;
        }
        .library-game-button:hover { color: #fff0b9; text-decoration: none; }
        .library-table { width: 100%; margin-top: 12px; border-collapse: collapse; table-layout: fixed; border: 1px solid #db9f73; }
        .library-table th { padding: 6px; color: #5e3d29; background: url('{{ asset('img/bg/info/bg_l.gif') }}') repeat; border: 1px solid #d5a173; }
        .library-table td { padding: 7px; vertical-align: middle; border: 1px solid #d5a173; }
        .library-table tr:nth-child(even) td { background: rgba(243, 207, 158, .28); }
        .library-list-image { width: 60px; height: 60px; object-fit: cover; border: 1px solid #a76b45; }
        .library-list-title { font-weight: bold; color: #7e1d0a; }
        .library-list-excerpt { margin-top: 5px; line-height: 1.35; }
        .library-muted { color: #98745b; font-size: 11px; }
        .library-empty { padding: 28px 10px; text-align: center; font-weight: bold; }
        .library-home-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 268px)); justify-content: center; gap: 10px; }
        .library-home-tile { position: relative; display: block; height: 150px; overflow: hidden; border: 1px solid #9f6039; background: #2f1c12 url('{{ asset('main/images/frames/main-bg.jpg') }}'); box-shadow: inset 0 0 0 3px rgba(228,171,101,.28); }
        .library-home-tile img { width: 100%; height: 100%; object-fit: cover; opacity: .84; transition: .2s; }
        .library-home-tile:hover img { opacity: 1; transform: scale(1.025); }
        .library-home-tile span { position: absolute; right: 0; bottom: 0; left: 0; padding: 8px; color: #f7d79a; font-weight: bold; font-size: 14px; text-align: center; text-transform: uppercase; text-shadow: 1px 1px 2px #000; background: linear-gradient(transparent, rgba(30,10,4,.92)); }
        .library-home-tile--empty { display: flex; align-items: center; justify-content: center; }
        .library-home-tile--empty span { position: static; background: none; }
        .library-article-content { font-size: 12px; line-height: 1.55; overflow-wrap: anywhere; }
        .library-article-content img { max-width: 100%; height: auto; }
        .library-article-content p { margin-top: 0; margin-bottom: 10px; }
        .library-article-content table { max-width: 100%; margin: 10px 0; border-collapse: collapse; }
        .library-article-content th,
        .library-article-content td { padding: 5px 7px; border: 1px solid #c79568; }
        .library-article-content th { color: #6f2919; background: rgba(231, 187, 128, .55); }
        .library-article-content blockquote { margin: 10px 0; padding: 7px 12px; color: #795743; border-left: 3px solid #ad754d; background: rgba(244, 220, 183, .45); }
        .library-info-block { margin: 12px 0; padding: 10px 12px; border: 1px solid #c79568; border-left: 5px solid #8a3c25; background: rgba(248, 224, 185, .62); box-shadow: inset 0 0 8px rgba(103, 48, 20, .08); }
        .library-info-block--important { border-left-color: #9c251d; background: rgba(255, 222, 212, .66); }
        .library-info-block--tip { border-left-color: #51732d; background: rgba(226, 239, 194, .68); }
        .library-info-block--warning { border-left-color: #bc791c; background: rgba(255, 235, 176, .72); }
        .library-entity-card {
            box-sizing: border-box;
            display: inline-flex;
            align-items: center;
            width: 290px;
            min-height: 72px;
            margin: 5px 7px 5px 0;
            padding: 5px;
            color: #5e3d29;
            vertical-align: middle;
            border: 1px solid #9f6039;
            background: rgba(241, 207, 159, .58) url('{{ asset('img/bg/info/bg_l.gif') }}') repeat;
            box-shadow: inset 0 0 0 2px rgba(255, 244, 210, .5);
        }
        .library-entity-card:hover { color: #72180e; border-color: #71351e; text-decoration: none; background-color: rgba(235, 188, 126, .72); }
        .library-entity-card__image { box-sizing: border-box; flex: 0 0 60px; width: 60px; height: 60px; object-fit: contain; border: 1px solid #ad754d; background: rgba(68, 35, 19, .16); }
        .library-entity-card__image--empty { display: flex; align-items: center; justify-content: center; color: #9c704f; font: bold 24px Georgia, serif; }
        .library-entity-card__body { display: flex; min-width: 0; padding-left: 8px; flex-direction: column; }
        .library-entity-card__type { color: #997053; font-size: 9px; line-height: 13px; text-transform: uppercase; }
        .library-entity-card__title { overflow: hidden; color: #7e1d0a; font-size: 12px; line-height: 17px; text-overflow: ellipsis; white-space: nowrap; }
        .library-entity-card__details { display: block; margin-top: 2px; color: #6c4e39; font-size: 10px; line-height: 13px; }
        .library-entity-card--npc {
            display: inline-flex;
            align-items: stretch;
            width: 209px;
            min-height: 0;
            flex-direction: column;
            vertical-align: top;
        }
        .library-entity-card--npc .library-entity-card__image {
            flex: 0 0 197px;
            width: 200px;
            height: 200px;
            object-fit: contain;
        }
        .library-entity-card--npc .library-entity-card__body {
            align-items: center;
            padding: 7px 4px 3px;
            text-align: center;
        }
        .library-entity-card--npc .library-entity-card__title {
            max-width: 100%;
            font-size: 13px;
            white-space: normal;
        }
        .library-entity-card--npc .library-entity-card__details { max-width: 100%; }
        .library-entity-line { display: inline-flex; max-width: 100%; margin: 2px 3px 2px 0; color: #7e1d0a; line-height: 20px; align-items: center; vertical-align: middle; }
        .library-entity-line:hover { color: #72180e; }
        .library-entity-line__image { width: 18px; height: 18px; margin-right: 4px; object-fit: contain; border: 1px solid #ad754d; background: rgba(68, 35, 19, .12); }
        .library-entity-line__type { margin-right: 3px; color: #997053; font-size: 10px; }
        .library-entity-line__title { color: #7e1d0a; }
        .library-entity-line__details { overflow: hidden; margin-left: 2px; color: #6c4e39; font-size: 10px; text-overflow: ellipsis; white-space: nowrap; }
        .library-cover { display: block; max-width: 100%; max-height: 330px; margin: 0 auto 15px; border: 1px solid #9f6039; }
        .library-related { margin-top: 17px; padding-top: 10px; border-top: 1px solid #d3a170; }
        .library-badge { display: inline-block; margin: 4px 4px 0 0; padding: 4px 7px; border: 1px solid #bd855c; background: #f1cfa0; }
        .library-pagination { margin-top: 10px; text-align: center; }
        .library-page-link { display: inline-block; min-width: 17px; height: 17px; margin: 0 1px; padding: 0 3px; line-height: 17px; color: #7e1d0a; border: 1px solid #bd855c; background: #f2d4a8; }
        .library-page-link:hover { background: #e7b97d; text-decoration: none; }
        .library-page-link.active { color: #f8dea4; border-color: #632614; background: #7e1d0a; }
        .library-page-link.disabled { color: #aa8d75; cursor: default; background: #e5cfad; }
        @media (max-width: 1050px) {
            html, body, .b-main-wrapper { min-width: 760px; }
            .library-main { left: auto; width: 760px; margin: 0 auto; }
            .b-column-wrapper { width: 760px; }
            .library-column-left { width: 230px; }
            .library-column-right { width: 550px; }
            .library-nav-link-wrapper { padding-left: 22px; }
        }
    </style>
</head>
<body>
<div id="artifact_alt" style="left:0;top:0;width:300px;display:none;position:fixed;z-index:10000001;pointer-events:none"></div>
<script>var art_alt = [];</script>
@yield('tooltip-script')
<script>window.gebi = window.gebi || function (id) { return document.getElementById(id); };</script>
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>

<div class="b-main-wrapper">
    <div class="b-main library-main">
        <div class="b-page__header" style="top:0;z-index:5;position:fixed">
            <header class="b-nav-lvl-1" style="width:990px">
                <div class="b-nav-lvl-1__decor-border"></div>
                <div class="b-nav-lvl-1__decor-menu-side"></div>
                <div class="b-nav-lvl-1__decor-menu-top"></div>

                <div class="b-nav-lvl-1__auth">
                    <div id="userHeadBlock">
                        <div class="b-nav-lvl-1__create">
                            @auth
                                <a class="b-auth-button" href="{{ route('game') }}">
                                    <span class="b-auth-button__content"><span class="b-aside__impo-link" style="font-size:15px;font-weight:bold;position:relative;color:#d49b2c;left:1px;top:-3px">В игру</span></span>
                                </a>
                            @else
                                <a class="b-auth-button" href="{{ route('register') }}">
                                    <span class="b-auth-button__content"><span class="b-aside__impo-link" style="font-size:15px;font-weight:bold;position:relative;color:#d49b2c;left:1px;top:-3px">Регистрация</span></span>
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>

                <div class="b-nav-lvl-1__logo">
                    <a class="b-auth-button" style="position:relative;left:24px;top:7px">
                        <span class="b-auth-button__content">
                            <span class="b-aside__impo-link" style="font-size:15px;font-weight:bold;position:relative;color:#d49b2c;left:1px;top:-3px">В игре сейчас: <span data-online-count>{{ $onlineCount }}</span></span>
                        </span>
                    </a>
                </div>

                <div class="b-nav-lvl-1__menu">
                    <ul class="b-nav-lvl-1__menu-list">
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none">
                            <a class="b-aside__impo-link" href="{{ route('index') }}" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:23px;top:8px"><span>Главная</span></a>
                        </li>
                        <li class="b-nav-lvl-1__menu-item is-active" style="list-style-type:none">
                            <a class="b-aside__impo-link" href="{{ route('library.index') }}" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:23px;top:8px"><span>Библиотека</span></a>
                        </li>
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none">
                            <a class="b-aside__impo-link" href="#" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:31px;top:8px"><span>Телеграм</span></a>
                        </li>
                        <li class="b-nav-lvl-1__menu-item" style="list-style-type:none">
                            <a class="b-aside__impo-link" href="#" style="font-size:14px;font-weight:bold;position:relative;color:#d49b2c;left:24px;top:8px"><span>Контакты</span></a>
                        </li>
                    </ul>
                </div>
            </header>
        </div>

        <div class="b-column-wrapper clearfix library-columns">
            <aside class="b-column-left library-column-left">
                <div class="b-main-frame b-main-frame_left-side library-sidebar-frame">
                    <span class="b-main-frame__l"></span><span class="b-main-frame__t"></span><span class="b-main-frame__b"></span>
                    <span class="b-main-frame__decor-tl"></span><span class="b-main-frame__decor-tl-2"></span><span class="b-main-frame__decor-bl"></span>
                    <div class="b-main-frame__cont">
                        <div class="b-common-block__bgl library-forum-nav-shell">
                            <div class="b-common-block__cont library-forum-nav-panel">
                                <div class="b-common-block__bgr clearfix">
                                    @forelse($categories as $category)
                                        @include('library::partials.sidebar-category', ['item' => $category, 'depth' => 0])
                                    @empty
                                        <div class="library-empty">Разделы пока не добавлены.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>

            <section class="b-column-right library-column-right">
                <div class="b-main-frame library-content-frame">
                    <span class="b-main-frame__l"></span><span class="b-main-frame__r"></span><span class="b-main-frame__t"></span><span class="b-main-frame__b"></span>
                    <span class="b-main-frame__decor-tr"></span><span class="b-main-frame__decor-tr-2"></span><span class="b-main-frame__decor-bl"></span><span class="b-main-frame__decor-br"></span>
                    <div class="b-main-frame__cont">
                        <div class="b-common-block">
                            <span class="b-common-block__l"></span><span class="b-common-block__r"></span><span class="b-common-block__t b-common-block__t_2"></span><span class="b-common-block__b"></span>
                            <span class="b-common-block__bl"></span><span class="b-common-block__br"></span><span class="b-common-block__header-decor-l"></span><span class="b-common-block__header-decor-r"></span>
                            <span class="b-common-block__header">
                                <span class="b-common-block__header-inner">
                                    @hasSection('panel-header')
                                        @yield('panel-header')
                                    @else
                                        <span>@yield('panel-title', 'Библиотека')</span>
                                    @endif
                                </span>
                            </span>
                            <div class="b-common-block__cont"><div class="b-common-block__bgl"><div class="b-common-block__bgr">
                                <div class="library-common-content">@yield('content')</div>
                            </div></div></div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script>
    (function () {
        var infoWindowOptions = 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no';

        document.addEventListener('click', function (event) {
            var link = event.target.closest('[data-library-info-popup]');
            if (!link) return;

            event.preventDefault();
            window.open(link.href, '', infoWindowOptions);
        });

        document.querySelectorAll('[data-library-header-select]').forEach(function (select) {
            var nativeSelect = select.querySelector('.library-header-native-select');
            var options = Array.prototype.slice.call(select.querySelectorAll('.option'));
            var highlightedIndex = Math.max(0, options.findIndex(function (option) {
                return option.classList.contains('option_selected');
            }));

            function setOpen(open) {
                select.classList.toggle('default-select_opened', open);
                select.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            function highlight(index) {
                if (!options.length) return;

                highlightedIndex = (index + options.length) % options.length;
                options.forEach(function (option, optionIndex) {
                    option.classList.toggle('option_hover', optionIndex === highlightedIndex);
                });
                options[highlightedIndex].scrollIntoView({ block: 'nearest' });
            }

            select.addEventListener('click', function (event) {
                if (event.target.closest('.option')) {
                    setOpen(false);
                    return;
                }

                if (!event.target.closest('.value, .button') && event.target !== select) return;

                setOpen(!select.classList.contains('default-select_opened'));
            });

            select.addEventListener('keydown', function (event) {
                if (event.key === 'Escape') {
                    setOpen(false);
                    return;
                }

                if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                    event.preventDefault();
                    setOpen(true);
                    highlight(highlightedIndex + (event.key === 'ArrowDown' ? 1 : -1));
                    return;
                }

                if (event.key === 'Enter' && select.classList.contains('default-select_opened')) {
                    event.preventDefault();
                    if (options[highlightedIndex]) window.location.href = options[highlightedIndex].href;
                    return;
                }

                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    setOpen(!select.classList.contains('default-select_opened'));
                }
            });

            nativeSelect.addEventListener('change', function () {
                if (nativeSelect.value) window.location.href = nativeSelect.value;
            });

            document.addEventListener('click', function (event) {
                if (!select.contains(event.target)) setOpen(false);
            });
        });
    })();
</script>
</body>
</html>
