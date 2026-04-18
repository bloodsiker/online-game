<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Персонаж</title>
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
        <p>Вы передали предмет "<b>{{ $page->itemName }}</b>" персонажу <a href="">{{ $page->toUserName }}</a></p>
        <p><a href="{{ $page->backpackUrl }}">История передачи предметов</a> »</p>
        <p>« <a href="{{ $page->sameItemsUrl }}">Аналогичные предметы в мешке</a><br>« <a href="{{ $page->backpackUrl }}">Список ваших вещей</a></p>
    @else
        @if($page->isUserMoved && $page->toUserName !== null)
            <p>Персонаж <a href="">{{ $page->toUserName }}</a> не находиться рядом возле вас</p>
        @endif
        @if($page->candidates !== [])
            Вы можете передать предмет "<b>{{ $page->itemName }}</b>" персонажам, которые находятся поблизости:
            <ul>
                @foreach($page->candidates as $user)
                    <li><a href="{{ $user->url }}">{{ $user->name }}</a></li>
                @endforeach
            </ul>
        @else
            <p>Поблизости нет персонажей, которым вы могли бы передать предмет "<b>{{ $page->itemName }}</b>"</p>
        @endif
        <p>Примечание:<br>Невозможно передать предмет персонажу неактивному более 10 минут.</p>
        <p><a href="{{ $page->backpackUrl }}">Список ваших вещей</a> »</p>
        <p>« <a href="{{ $page->locationUrl }}" target="game">Описание местности</a></p>
    @endif
</td></tr></tbody></table>
<script>
@if (session()->has('message'))
window.parent.showErrorIframe('{{ session('message') }}')
@endif
</script>
</body>
</html>
