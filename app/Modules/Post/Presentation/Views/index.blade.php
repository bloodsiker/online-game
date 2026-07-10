<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Почта</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
    {!! $itemTooltipScript !!}
    <style>
        html, body {
            height: 100%;
            margin: 0;
            color: #955c4a;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            background: url({{ asset('img/bg/bgg.gif') }});
        }

        a, a:link, a:visited, a:active { color: #5a1f00; text-decoration: none; }
        a:hover { color: #8b2f00; text-decoration: underline; }

        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        .black, .black * { color: #2f1f0b; }
        .grn { color: #1e7a00; }
        .rdd { color: #a40001; }

        .tbl-sts_bg-light { background: url({{ asset('main/images/tbl-usi_bg-light.gif') }}) repeat; font-family: Tahoma; font-size: 11px; }

        .post-capacity { border: 1px solid #e2b25e; padding: 3px 8px; margin-bottom: 8px; font-weight: bold; color: #2f1f0b; }
        .post-flash-ok { color: #1e7a00; font-weight: bold; margin-bottom: 6px; text-align: center; }
        .post-flash-err { color: #a40001; font-weight: bold; margin-bottom: 6px; text-align: center; }
        .post-unread, .post-unread a { font-weight: bold; color: #2f1f0b; }
        .post-read, .post-read td, .post-read a { font-weight: normal; color: #b09a8b !important; }
        .post-row-active td { background: rgba(255, 233, 186, .6); }
        .post-input { border: 1px solid #955c4a; background: #fff7dd; font-family: Tahoma; font-size: 11px; padding: 1px 3px; }
        .brd2-rgt { border-right: 1px solid #DB9F73; }
        .bpdig {
            border: solid 1px #6f4a24;
            background-color: #6e534c;
            width: 32px;
            height: 14px;
            color: #f6d9a6;
            font-weight: bold;
            margin: 2px;
            text-align: center;
            position: relative;
            top: 2px;
            left: -1px;
            font-size: 11px;
        }
        .post-letter-text {
            margin-top: 8px;
            padding: 8px 10px;
            min-height: 180px;
            border: 1px solid #DB9F73;
            background: #FDF3CE;
            white-space: pre-wrap;
        }
        .pointer { cursor: pointer; }
    </style>
</head>
<body>

{{-- ── Меню (кнопки как на странице событий) ───────────────────────────── --}}
@php
    $tabs = [
        'inbox'   => 'Входящие сообщения',
        'outpost' => 'Отправленные сообщения',
        'outbox'  => 'Отправка письма',
    ];

    $btnLeft1   = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1  = 'img/bg/btn/btn-right1.gif';
    $btnLeft2   = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2  = 'img/bg/btn/btn-right2.gif';
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr height="21">
        @foreach($tabs as $tabMode => $tabTitle)
            @php $isActive = $mode === $tabMode && ! $letter; @endphp
            <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td align="center" nowrap style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ route('post', ['mode' => $tabMode]) }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tabTitle }}</a>
            </td>
            <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
        @endforeach

        <td width="100%"></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td align="center" nowrap style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Выход</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
</table>

{{-- ── Контент ─────────────────────────────────────────────────────────── --}}
<div style="padding: 8px 10px; height: calc(100% - 43px);">

    @if(session('post_success'))
        <div class="post-flash-ok">{{ session('post_success') }}</div>
    @endif
    @if(session('post_error'))
        {{-- Ошибки — через игровое модальное окно (popup_global_container) --}}
        <script>
            try {
                window.top.systemInfo(@json(session('post_error')), 'Ошибка');
            } catch (e) {
                alert(@json(session('post_error')));
            }
        </script>
    @endif

    <table class="coll w100" border="0" cellspacing="0" cellpadding="0" style="height: 100%;">
        <tr>
            <td width="49%" valign="top">
                @if($mode === 'inbox')
                    @include('post::partials.inbox')
                @elseif($mode === 'outpost')
                    @include('post::partials.outpost')
                @else
                    @include('post::partials.outbox')
                @endif
            </td>
            <td width="2%">&nbsp;</td>
            <td width="49%" valign="top">
                {{-- Письмо открывается в правом окне, слева остаётся список --}}
                @if($letter)
                    @include('post::partials.letter')
                @else
                    @include('post::partials.info')
                @endif
            </td>
        </tr>
    </table>

</div>

</body>
</html>