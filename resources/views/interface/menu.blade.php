<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        body {
            margin: 0;
            /*background-color: #97e0f6;*/
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
        <li><a href="#" onclick="sendDataToGame('{{ route('location') }}')">Перемещение</a></li>
        <li><a href="#" onclick="sendDataToGame('{{ route('character') }}')">Персонаж</a></li>
        <li><a href="#" onclick="sendDataToGame('{{ route('backpack') }}')">Вещи</a></li>
        <li><a href="#" onclick="sendDataToGame('{{ route('clan') }}')">Клан</a></li>
        <li><a href="#" onclick="sendDataToGame('{{ route('clan.member') }}')">Состав клана</a></li>
        <li><a href="#" onclick="sendDataToGame('{{ route('quests') }}')">Квесты</a></li>
        <li>Премиум</li>
        <li><a href="{{ route('logout') }}">Выход</a></li>
    </ul>
</div>

<script>
    function sendDataToGame(url) {
        window.parent.postMessage({ url: url }, '*');
    }
</script>

</body>
</html>
