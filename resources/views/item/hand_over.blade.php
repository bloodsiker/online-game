<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Персонаж</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        ul {
            padding-left: 15px;
        }
        li {
            list-style-type: none;
            padding-left: 20px;
            background-image: url('{{ asset('img/icon/users-arrow.gif') }}');
            background-repeat: no-repeat;
            background-position: left center;
            background-size: 14px 12px;
            margin-bottom: 3px;
        }
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            @if($isHandedItem)
                <p>Вы передали предмет "<b>{{ $item->getName() }}</b>" персонажу <a href="">{{ $toUser->name }}</a></p>

                <p><a href="{{ route('backpack') }}">История передачи предметов</a>  »</p>

                <p>
                    « <a href="{{ route('backpack', array_merge(request()->query(), ['sid' => $item->itemInfo->id])) }}">Аналогичные предметы в мешке</a><br>
                    « <a href="{{ route('backpack', request()->query()) }}">Список ваших вещей</a>
                </p>
            @else
                @if($isUserMoved)
                    <p>Персонаж <a href="">{{ $toUser->name }}</a> не находиться рядом возле вас</p>
                @endif

                @if(count($onlineOnLocation))
                    Вы можете передать предмет "<b>{{ $item->getName() }}</b>" персонажам, которые находятся поблизости:
                    <ul>
                        @foreach($onlineOnLocation as $user)
                            <li><a href="{{ route('items.hand_over_to_user', ['id' => $item->id, 'uid' => $user->id]) }}">{{ $user->name }}</a></li>
                        @endforeach
                    </ul>
                @else
                    <p>Поблизости нет персонажей, которым вы могли бы передать предмет "<b>{{ $item->getName() }}</b>"</p>
                @endif

                <p>Примечание:<br>Невозможно передать предмет персонажу неактивному более 10 минут.</p>

                <p><a href="{{ route('backpack') }}">Список ваших вещей</a>  »</p>
                <p>« <a href="{{ route('location') }}" target="game">Описание местности</a></p>
            @endif
        </td>
    </tr>
    </tbody>
</table>

<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'i':--}}
    {{--            window.parent.sendDataToGame('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            window.parent.sendDataToGame('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            window.parent.sendDataToGame('{{ route('location') }}');--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
