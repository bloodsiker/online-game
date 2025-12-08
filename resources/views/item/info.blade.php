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
        .color-red {
            color: red;
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
    </style>
</head>
<body>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <table cellspacing="0" cellpadding="0" border="0" width="1%" align="right"
                   style="margin:0px 0px 10px 10px;">
                <tbody>
                <tr>
                    <td align="right" valign="bottom">
                        <img src="{{ asset('img/bg/item/border-1x1.gif') }}" width="10" height="10" border="0" hspace="0" vspace="0">
                    </td>
                    <td style="background:url({{ asset('img/bg/item/border-h.gif') }}) repeat-x bottom left"></td>
                    <td align="left" valign="bottom">
                        <img src="{{ asset('img/bg/item/border-3x1.gif') }}" width="10" height="10" border="0" hspace="0" vspace="0">
                    </td>
                </tr>
                <tr>
                    <td style="background:url({{ asset('img/bg/item/border-v.gif') }}) repeat-y top right"></td>
                    <td style="padding: 0px;" width="100%" bgcolor="#FFFEFA">

                        <a href="/info/itus.php?iid=116001041" target="_blank" title="Открыть изображение в новом окне"><img
                                src="/img-item/2de37a8f4d3607b.jpg" border="0" vspace="0" hspace="0"
                                alt="Открыть изображение в новом окне"></a>

                    </td>
                    <td style="background:url({{ asset('img/bg/item/border-v.gif') }}) repeat-y top left"></td>
                </tr>
                <tr>
                    <td align="right" valign="top">
                        <img src="{{ asset('img/bg/item/border-1x3.gif') }}" width="10" height="10" border="0" hspace="0" vspace="0">
                    </td>
                    <td style="background:url({{ asset('img/bg/item/border-h.gif') }}) repeat-x top left"></td>
                    <td align="left" valign="top">
                        <img src="{{ asset('img/bg/item/border-3x3.gif') }}" width="10" height="10" border="0" hspace="0" vspace="0">
                    </td>
                </tr>
                </tbody>
            </table>
            <span id="sp1"><b>{{ $item->getName() }}</b></span>
            <p></p>
            <div id="sp2">Представляет собой грубо отесанный камень округлой формы. Силач, ухватившись двумя руками,
                может запустить во врага этим снарядом.
                <p>
                    @if($item->itemInfo->min_attack || $item->itemInfo->max_attack)
                        Базовое повреждение: <b>{{ $item->itemInfo->min_attack }}-{{ $item->itemInfo->max_attack }}</b>
                    @endif

                    @if($item->itemInfo->slot === App\Models\ShareItem::SLOT_HAND)
                        <br>
                        @if($item->itemInfo->is_two_hand)
                            Использование: <b>требуются обе руки</b>
                        @else
                            Использование: <b>требуются одна рука</b>
                        @endif
                    @endif
                </p>
                <p>Минимальные требования:<br>
                - <span class="color-red">сила: <b>200</b></span><br>
                - <span class="color-red">ловкость:<b>100</b></span><br>
                @if($item->itemInfo->skill_id)
                    - <span class="color-red">навык "{{ $item->itemInfo->skill->name }}": <b>{{ $item->itemInfo->skill_lvl }}</b></span><br>
                @endif
                </p>
                <p><small>Минимальным требованиям должны удовлетворять <a href="/help/character/" target="_blank">базовые значения характеристик</a> (см. раздел "Персонаж").</small></p></div>
            <p>
                Тип предмета: <b>{{ $item->itemInfo->getTypeName() }}</b><br>
                @if($item->itemInfo->skill_id)
                    Базовый навык: <b>{{ $item->itemInfo->skill->name }}</b>
                @endif
            </p>
            <p>Вы можете: </p>
            <ul>
                @if($item->itemInfo->slot === App\Models\ShareItem::SLOT_HAND)
                    <li><a href="itemto.php?iid=116001041">взять в руки</a> »</li>
                @endif
                <li><a href="{{ route('items.hand_over', array_merge(request()->query(), ['id' => $item->id])) }}">передать</a> »</li>
                <li><a href="{{ route('items.drop', array_merge(request()->query(), ['id' => $item->id])) }}">выбросить</a> »</li>
            </ul>

            <p>
                « <a href="{{ route('backpack', array_merge(request()->query(), ['sid' => $item->itemInfo->id])) }}">Аналогичные предметы в мешке</a><br>
                <br>
                « <a href="{{ route('backpack', request()->query()) }}">Список вещей</a>
            </p>
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
