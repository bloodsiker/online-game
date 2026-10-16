<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('interface::partials.game-translations')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __("Персонаж") }}</title>
    <style>
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 14px; }
        a { color: #000000; }
        a:hover { color: #353434 }
        ul { padding-left: 15px; }
        li { list-style-type: none; padding-left: 20px; background-image: url('{{ asset('img/icon/users-arrow.gif') }}'); background-repeat: no-repeat; background-position: left center; background-size: 14px 12px; margin-bottom: 3px; }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="10" width="100%" height="100%"><tbody><tr valign="top"><td>
    @if($page->isHandedItem)
        <p>{{ __('Вы передали предмет') }} "<b>{{ $page->itemName }}</b>" {{ __('персонажу') }} <a href="">{{ $page->toUserName }}</a></p>
        <p><a href="{{ $page->backpackUrl }}">{{ __('История передачи предметов') }}</a> »</p>
        <p>« <a href="{{ $page->sameItemsUrl }}">{{ __('Аналогичные предметы в мешке') }}</a><br>« <a href="{{ $page->backpackUrl }}">{{ __("Список ваших вещей") }}</a></p>
    @else
        @if($page->isUserMoved && $page->toUserName !== null)
            <p>{{ __("Персонаж") }} <a href="">{{ $page->toUserName }}</a> {{ __('не находиться рядом возле вас') }}</p>
        @endif
        @if($page->candidates !== [])
            {{ __('Вы можете передать предмет') }} "<b>{{ $page->itemName }}</b>" {{ __('персонажам, которые находятся поблизости:') }}
            <ul>
                @foreach($page->candidates as $user)
                    <li><a href="{{ $user->url }}">{{ $user->name }}</a></li>
                @endforeach
            </ul>
        @else
            <p>{{ __('Поблизости нет персонажей, которым вы могли бы передать предмет') }} "<b>{{ $page->itemName }}</b>"</p>
        @endif
        <p>{{ __('Примечание:') }}<br>{{ __('Невозможно передать предмет персонажу неактивному более 10') }} {{ __("минут") }}.</p>
        <p><a href="{{ $page->backpackUrl }}">{{ __("Список ваших вещей") }}</a> »</p>
        <p>« <a href="{{ $page->locationUrl }}" target="game">{{ __("Описание местности") }}</a></p>
    @endif
</td></tr></tbody></table>
<script>
@if (session()->has('message'))
window.parent.showErrorIframe('{{ session('message') }}')
@endif
</script>
</body>
</html>
