<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Предметы на локации</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; }
        body {
            margin: 0;
            padding: 10px;
            background: transparent;
            color: #2d1600;
            font-family: Tahoma, sans-serif;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <x-game.loot-panel
        :items="$page->items"
        :count="$page->count"
        :location-url="$page->backUrl"
        empty-message="Вы не нашли здесь ничего стоящего."
    />
</body>
</html>
