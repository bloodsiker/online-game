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
        .color-red { color: red; }
        ul { padding-left: 15px; }
        li { list-style-type: none; padding-left: 20px; background-image: url('{{ asset('img/icon/users-arrow.gif') }}'); background-repeat: no-repeat; background-position: left center; background-size: 14px 12px; margin-bottom: 3px; }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="10" width="100%" height="100%"><tbody><tr valign="top"><td>
    @if($page->image)
        <table cellspacing="0" cellpadding="0" border="0" width="1%" align="right" style="margin:0px 0px 10px 10px;"><tbody>
        <tr><td align="right" valign="bottom"><img src="{{ asset('img/bg/item/border-1x1.gif') }}" width="10" height="10" border="0"></td><td style="background:url({{ asset('img/bg/item/border-h.gif') }}) repeat-x bottom left"></td><td align="left" valign="bottom"><img src="{{ asset('img/bg/item/border-3x1.gif') }}" width="10" height="10" border="0"></td></tr>
        <tr><td style="background:url({{ asset('img/bg/item/border-v.gif') }}) repeat-y top right"></td><td style="padding: 0px;" width="100%" bgcolor="#FFFEFA"><img src="{{ $page->image }}" border="0" vspace="0" hspace="0"></td><td style="background:url({{ asset('img/bg/item/border-v.gif') }}) repeat-y top left"></td></tr>
        <tr><td align="right" valign="top"><img src="{{ asset('img/bg/item/border-1x3.gif') }}" width="10" height="10" border="0"></td><td style="background:url({{ asset('img/bg/item/border-h.gif') }}) repeat-x top left"></td><td align="left" valign="top"><img src="{{ asset('img/bg/item/border-3x3.gif') }}" width="10" height="10" border="0"></td></tr>
        </tbody></table>
    @endif
    <span id="sp1"><b>{{ $page->name }}</b></span>
    <p></p>
    <div id="sp2">
        <p>
            @if($page->minAttack || $page->maxAttack)
                Базовое повреждение: <b>{{ $page->minAttack }}-{{ $page->maxAttack }}</b>
            @endif
            @if($page->isHandSlot)
                <br>
                Использование: <b>{{ $page->isTwoHand ? 'требуются обе руки' : 'требуются одна рука' }}</b>
            @endif
        </p>
        @if($page->skillName)
            <p>Минимальные требования:<br>- <span class="color-red">навык "{{ $page->skillName }}": <b>{{ $page->skillLevel }}</b></span><br></p>
        @endif
    </div>
    <p>
        Тип предмета: <b>{{ $page->typeName }}</b><br>
        @if($page->skillName)Базовый навык: <b>{{ $page->skillName }}</b>@endif
    </p>
    <p>Вы можете: </p>
    <ul>
        <li><a href="{{ $page->handOverUrl }}">передать</a> »</li>
        <li><a href="{{ $page->dropUrl }}">выбросить</a> »</li>
    </ul>
    <p>« <a href="{{ $page->sameItemsUrl }}">Аналогичные предметы в мешке</a><br><br>« <a href="{{ $page->backpackUrl }}">Список вещей</a></p>
</td></tr></tbody></table>
</body>
</html>
