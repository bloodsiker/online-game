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
        .b {
            background-color: #CEBBAA;
        }
        .tbgr {
            background-color: #FADCC2;
        }
        .l0 {
            background-color: #FFF8EA;
        }
        .l1 {
            background-color: #FFFBF5;
        }
        .t0 {
            background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left;
            background-color: #EDD5C3;
        }
        .t1 {
            background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left;
            background-color: #DFBBA3;
        }
        td.itm {
            padding: 0px 10px 0px 0px;
            white-space: nowrap;
        }
        img.itm {
            width: 50px;
            height: 50px;
            border: 0px;
            margin: 0px 10px 0px 0px;
            padding: 0px;
            vertical-align: middle;
            border-right: 1px solid #CEBBAA;
        }
    </style>
</head>
<body>

@if($itemsOnLocation->count())
    <p>Здeсь коe-чтo ecть: </p>
    <table cellspacing="1" cellpadding="1" border="0" class="b">
        <tbody>
            <tr>
                <td class="tbgr">
                    <table cellspacing="1" cellpadding="5" border="0" width="100%" class="b">
                        <tbody>
                        @foreach($itemsOnLocation as $item)
                            <tr class="l0">
                                <td class="itm">
                                    <img src="{{ $item->item->itemInfo->image }}" class="itm">{{ $item->item->getName() }}
                                </td>
                                <td width="30" align="center">
                                    x{{ $item->count }}
                                </td>
                                <td>
                                    @if($item->item->itemInfo->type === \App\Models\ShareItem::TYPE_CHEST)
                                        @if($item->item->is_open)
                                            <a href="{{ route('items.view_chest', ['id' => $item->item->id]) }}">заглянуть</a>&nbsp;»
                                        @else
                                            <a href="{{ route('items.open_chest', ['id' => $item->item->id]) }}">открыть</a>&nbsp;»
                                        @endif
                                    @else
                                        <a href="{{ route('items.pick_up', ['id' => $item->item->id]) }}">поднять</a>&nbsp;»
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        <tr class="t0">
                            <td colspan="3"><b>Bceгo: {{ $itemsOnLocation->count() }}</b></td>
                        </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
@else
    <p>Bы нe нашли здeсь ничeгo стоящегo...</p>
@endif

<p>« <a href="{{ route('location') }}" target="game">Описание местности</a></p>

<script>
    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case 'i':
                window.parent.sendDataToGame('{{ route('backpack') }}');
                break;
            case 'c':
                window.parent.sendDataToGame('{{ route('character') }}');
                break;
            case ' ':
                window.parent.sendDataToGame('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
    });
</script>

</body>
</html>
