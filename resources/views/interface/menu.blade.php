<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        body {
            margin: 0;
            color: #f4d06e;
            font-family: tahoma;
            font-size: 14px;
        }
        a {
            color: #f4d06e;
        }
        .game-area {
            width: 100%;
            height: 100%;
            position: relative;
        }
        ul > li {
            margin: 0;
            display: inline-block;
        }
    </style>
</head>
<body>

<div class="game-area">
    <ul>
        <li><a href="#" onclick="window.top.toLocation('{{ route('location') }}')">Перемещение</a></li>
        <li><a href="#" onclick="window.top.toLocation('{{ route('character') }}', true)">Персонаж</a></li>
        <li><a href="#" onclick="window.top.toLocation('{{ route('backpack') }}', true)">Вещи</a></li>
        <li><a href="#" onclick="window.top.toLocation('{{ route('clan') }}', true)">Клан</a></li>
        <li><a href="#" onclick="window.top.toLocation('{{ route('clan.member') }}', true)">Состав клана</a></li>
        <li><a href="#" onclick="window.top.toLocation('{{ route('quests') }}', true)">Квесты</a></li>
        <li><a href="#" onclick="window.top.toLocation('{{ route('premium.shop') }}', true)">Премиум</a></li>
        <li><a href="{{ route('logout') }}">Выход</a></li>
    </ul>
</div>

</body>
</html>
