<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Меню</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            height: 100%;
            overflow: hidden;
            font-family: Tahoma, sans-serif;
            font-size: 11px;
        }

        .canvas-menu-wrap {
            display: flex;
            align-items: flex-start;
            justify-content: center;
            height: 100%;
        }
        #top_mnu_cont {
            width: 670px;
            height: 78px;
        }

        /* Fallback HTML menu (shown only if the canvas menu fails to start) */
        .menu-wrap {
            display: none;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 2px;
            padding: 0 8px;
        }

        .menu-btn {
            display: inline-flex;
            align-items: center;
            height: 19px;
            cursor: pointer;
            text-decoration: none;
            white-space: nowrap;

            background-image:
                url({{ asset('img/bg/btn/tbl-shp_menu-left-inact.gif') }}),
                url({{ asset('img/bg/btn/tbl-shp_menu-right-inact.gif') }}),
                url({{ asset('img/bg/btn/tbl-shp_menu-center-inact.gif') }});
            background-repeat: no-repeat, no-repeat, repeat-x;
            background-position: left center, right center, center center;
            padding: 0 20px;

            color: #461c0b;
            font-size: 11px;
            font-weight: bold;
            text-shadow: 0 1px 0 rgba(255,255,255,0.3);
        }

        .menu-btn:hover, .menu-btn:active {
            background-image:
                url({{ asset('img/bg/btn/tbl-shp_menu-left-act.gif') }}),
                url({{ asset('img/bg/btn/tbl-shp_menu-right-act.gif') }}),
                url({{ asset('img/bg/btn/tbl-shp_menu-center-act.gif') }});
            color: #ffe4aa;
            text-shadow: 0 1px 2px rgba(0,0,0,0.6);
        }

        .menu-sep {
            width: 5px;
            height: 19px;
            background: url({{ asset('img/bg/separator_v.gif') }}) center center no-repeat;
            margin: 0 3px;
            flex-shrink: 0;
        }
    </style>

    <script src="{{ asset('main/js/canvas/pixi.js') }}"></script>
    <script src="{{ asset('main/js/canvas/pixi-filters.js') }}"></script>
    <script src="{{ asset('main/js/canvas/canvas.all.js') }}"></script>
</head>
<body>

<div class="canvas-menu-wrap">
    <div id="top_mnu_cont"></div>
</div>

{{-- Fallback: обычное HTML-меню, включается если канвас не стартовал --}}
<div class="menu-wrap" id="html-menu">
    <a class="menu-btn" href="#" onclick="menuGo(true, '{{ route('location') }}'); return false;">Перемещение</a>
    <a class="menu-btn" href="#" onclick="menuGo(true, '{{ route('character') }}', true); return false;">Персонаж</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('backpack') }}', true); return false;">Вещи</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('clan') }}', true); return false;">Клан</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('clan.member') }}', true); return false;">Состав клана</a>
    <a class="menu-btn" href="#" onclick="menuGo(true, '{{ route('quests') }}', true); return false;">Квесты</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('dungeon.index') }}', true); return false;">Данжи</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('friends') }}', true); return false;">Друзья</a>
    <a class="menu-btn" href="#" onclick="menuGo(true, '{{ route('rating') }}', true); return false;">Рейтинг</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('referral') }}', true); return false;">Рефералы</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('premium.shop') }}', true); return false;">Премиум</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('events') }}', true); return false;">События</a>
    <a class="menu-btn" href="#" onclick="menuGo(false, '{{ route('post') }}', true); return false;">Почта</a>
    <a class="menu-btn" href="#" onclick="try { window.top.systemInfo('Инфопортал в разработке.', 'Инфопортал'); } catch (e) {} return false;">Инфопортал</a>
</div>

<script>
    function menuGo(showMap, url, hideHero) {
        window.top.toggleMap(showMap);
        window.top.toLocation(url, hideHero);
    }

    // Команды из public/data/locale/ru/topMenu.xml → роуты игры
    var menuCommands = {
        m_location:    function () { menuGo(true,  '{{ route('location') }}'); },
        m_character:   function () { menuGo(true,  '{{ route('character') }}', true); },
        m_backpack:    function () { menuGo(false, '{{ route('backpack') }}', true); },
        m_clan:        function () { menuGo(false, '{{ route('clan') }}', true); },
        m_clan_member: function () { menuGo(false, '{{ route('clan.member') }}', true); },
        m_quests:      function () { menuGo(true,  '{{ route('quests') }}', true); },
        m_dungeon:     function () { menuGo(false, '{{ route('dungeon.index') }}', true); },
        m_friends:     function () { menuGo(false, '{{ route('friends') }}', true); },
        m_rating:      function () { menuGo(true,  '{{ route('rating') }}', true); },
        m_referral:    function () { menuGo(false, '{{ route('referral') }}', true); },
        m_premium:     function () { menuGo(false, '{{ route('premium.shop') }}', true); },
        m_events:      function () { menuGo(false, '{{ route('events') }}', true); },
        m_post:        function () { menuGo(false, '{{ route('post') }}', true); },
        m_infoportal:  function () { try { window.top.systemInfo('Инфопортал в разработке.', 'Инфопортал'); } catch (e) {} }
    };

    // Канвас-меню вызывает глобальный processMenu(command) при клике по пункту
    function processMenu(command) {
        if (menuCommands[command]) menuCommands[command]();
    }

    // Пункты подменю «Персонаж» (21-25) мигать не умеют — вместо них мигает родитель (id 2)
    var buttonIds = {
        location: 1, character: 2, backpack: 3, clan: 2, clan_member: 2,
        quests: 4, dungeon: 5, friends: 2, rating: 6, referral: 2, premium: 7,
        events: 8, post: 9, infoportal: 10
    };

    // Мигание кнопки из родителя:
    // document.getElementById('menu-frame').contentWindow.blinkButton('quests', true)
    function blinkButton(name, status) {
        if (window.top_mnu && buttonIds[name]) {
            window.top_mnu.blinkButton(buttonIds[name], !!status);
        }
    }

    function showFallbackMenu() {
        document.querySelector('.canvas-menu-wrap').style.display = 'none';
        document.getElementById('html-menu').style.display = 'flex';
    }

    try {
        // canvas.Config.init() строит пути от домена (/images/data/...);
        // задаём их заранее, чтобы ресурсы читались из public/data/
        var C = canvas.Config;
        C.domain = location.origin;
        C.wwwPath = '/';
        C.dataPath = '/data/';
        C.imgPath = '/data/canvas/';
        C.soundsPath = C.imgPath + 'sounds/';
        C.fontsPath = C.imgPath + 'fonts/';
        C.ui = C.imgPath + 'ui/';
        C.effectsAtlasPath = C.imgPath + 'effects/';
        C.effectsPath = C.imgPath + 'effects/mci/';
        C.localePath = C.imgPath + 'locale/';
        C.effects = C.imgPath + 'effects/effects.json';
        C.entryPoint = '/entry_point.php';
        C.isMobile = canvas.isMobile();
        C.initLang('ru');

        // Иконки почты нет в наборе top/ атласа — подменяем на иконку
        // из правой панели прода (right/mail_image)
        var getImage = canvas.ResourceLoader.getImage;
        canvas.ResourceLoader.getImage = function (atlas, frame) {
            if (frame === 'top/post') {
                return getImage.call(canvas.ResourceLoader, 'ui', 'right/mail_image');
            }
            return getImage.apply(canvas.ResourceLoader, arguments);
        };

        // В атласе иконки двух размеров (57x59 и 45x51 — прод использовал мелкие
        // только в подменю); растягиваем иконку до подложки item_back
        var ItemView = canvas.app.topMenu.view.ItemView;
        var itemViewInit = ItemView.prototype.init;
        ItemView.prototype.init = function () {
            itemViewInit.call(this);
            if (!this.isSmall && this.image) {
                this.image.width = 57;
                this.image.height = 59;
                this.image.position.set(6, 0);
            }
        };

        // Ряд иконок центрируется по ширине канваса (движок прижимает его влево)
        var topMenuReady = canvas.app.CanvasTopMenu.prototype.ready;
        canvas.app.CanvasTopMenu.prototype.ready = function () {
            topMenuReady.call(this);
            this.main.x = Math.round((this.par.width - this.main.view.items.length * 55) / 2);
        };

        window.top_mnu = new canvas.app.CanvasTopMenu({
            labels: 'локация|персонаж|вещи|клан|состав клана|квесты|данжи|друзья|рейтинг|рефералы|премиум|события|инфопортал|почта',
            dragDropItems: '0',
            configXml: '/data/locale/ru/topMenu.xml',
            blink: '',
            js_popup: '1',
            lang: 'ru',
            locale_file: 'data/locale/ru/flash_translate.xml',
            width: 670,
            height: 78
        }, document.getElementById('top_mnu_cont'));
    } catch (e) {
        console.error('Canvas top menu failed, falling back to HTML menu:', e);
        showFallbackMenu();
    }
</script>

</body>
</html>