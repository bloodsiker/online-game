<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('interface::partials.game-translations')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __("Предметы на локации") }}</title>
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
        :message="$page->message"
        :location-url="$page->locationUrl"
        :backpack-url="$page->backpackUrl"
        empty-message="{{ __('На земле больше ничего нет.') }}"
    />
    <script>
        try {
            window.top.sendToFrame('character-frame', { money: @json($page->money) }, window.location.origin);
        } catch (error) {
            console.error('Не удалось обновить баланс в character-frame:', error);
        }
    </script>
</body>
</html>
