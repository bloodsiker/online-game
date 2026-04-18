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

        .menu-wrap {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            gap: 2px;
            padding: 0 8px;
        }

        /* Sprite-based button: left cap | center repeat | right cap */
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
            width: 1px;
            height: 14px;
            background: #b08060;
            margin: 0 2px;
            flex-shrink: 0;
        }

        .menu-btn.logout {
            color: #8b1a0a;
        }
        .menu-btn.logout:hover {
            color: #ffb090;
        }
    </style>
</head>
<body>

<div class="menu-wrap">
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(true); window.top.toLocation('{{ route('location') }}'); return false;">Перемещение</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(true); window.top.toLocation('{{ route('character') }}', true); return false;">Персонаж</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('backpack') }}', true); return false;">Вещи</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('clan') }}', true); return false;">Клан</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('clan.member') }}', true); return false;">Состав клана</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(true); window.top.toLocation('{{ route('quests') }}', true); return false;">Квесты</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('premium.shop') }}', true); return false;">Премиум</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(true); window.top.toLocation('{{ route('rating') }}', true); return false;">Рейтинг</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('friends') }}', true); return false;">Друзья</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('dungeon.index') }}', true); return false;">Данжи</a>
    <a class="menu-btn" href="#" onclick="window.top.toggleMap(false); window.top.toLocation('{{ route('referral') }}', true); return false;">Рефералы</a>
    <div class="menu-sep"></div>
    <a class="menu-btn logout" href="{{ route('logout') }}">Выход</a>
</div>

</body>
</html>
