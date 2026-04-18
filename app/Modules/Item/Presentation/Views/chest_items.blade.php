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
        .b { background-color: #CEBBAA; }
        .tbgr { background-color: #FADCC2; }
        .l0 { background-color: #FFF8EA; }
        .t0 { background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left; background-color: #EDD5C3; }
        td.itm { padding: 0px 10px 0px 0px; white-space: nowrap; }
        img.itm { width: 50px; height: 50px; border: 0px; margin: 0px 10px 0px 0px; padding: 0px; vertical-align: middle; border-right: 1px solid #CEBBAA; }
    </style>
</head>
<body>
@if($page->message !== '')<p>{!! $page->message !!}</p>@endif
@if($page->count)
    <p>В сундуке вы обнаружили: </p>
    <table cellspacing="1" cellpadding="1" border="0" class="b"><tbody><tr><td class="tbgr">
        <table cellspacing="1" cellpadding="5" border="0" width="100%" class="b"><tbody>
        @foreach($page->items as $item)
            <tr class="l0">
                <td class="itm"><img src="{{ $item->image }}" class="itm">{{ $item->name }}</td>
                <td width="30" align="center">x{{ $item->count }}</td>
                <td><a href="{{ $item->pickupUrl }}">поднять</a>&nbsp;»</td>
            </tr>
        @endforeach
        <tr class="t0"><td colspan="3"><b>Bceгo: {{ $page->count }}</b></td></tr>
        </tbody></table>
    </td></tr></tbody></table>
@endif
<p><a href="{{ $page->backpackUrl }}">Список ваших вещей</a> »</p>
<p>« <a href="{{ $page->locationUrl }}" target="game">Описание местности</a></p>
<script>
document.addEventListener('keydown', function(event) {
    switch (event.key.toLowerCase()) {
        case 'i': window.parent.sendDataToGame('{{ route('backpack') }}'); break;
        case 'c': window.parent.sendDataToGame('{{ route('character') }}'); break;
        case ' ': window.parent.sendDataToGame('{{ route('location') }}'); break;
        default: return;
    }
    event.preventDefault();
});
</script>
</body>
</html>
