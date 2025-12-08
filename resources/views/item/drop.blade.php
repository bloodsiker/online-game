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
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            @if($droppedItem)
                Вы выбросили предмет "<b>{{ $item->getName() }}</b>"...
            @else
                Вы действительно хотите выбросить предмет "<b>{{ $item->getName() }}</b>"?<br>
                - <a href="{{ route('items.drop', array_merge(request()->query(), ['id' => $item->id, 'c' => 1])) }}">Да</a>.
            @endif
            <p>
                « <a href="{{ route('backpack', array_merge(request()->query(), ['sid' => $item->itemInfo->id])) }}">Аналогичные предметы в мешке</a><br>
                « <a href="{{ route('backpack', request()->query()) }}">Список вещей</a>
            </p>
            <p>« <a href="{{ route('location') }}" target="game">Описание местности</a></p>
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
</script>

</body>
</html>
