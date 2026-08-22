<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $npc->name }} - {{ $node->title }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        a { color: #000000; }
        a:hover { color: #353434; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .bg_l2 { background-image: url(/img/bg/info/bg_l2.gif); cursor: pointer; }
        .pointer { cursor: pointer; }
        .redd2, .redd2 * { color: #8a0108 !important; }
        .b { font-weight: bold; }
        .fs-12 { font-size: 12px; }
        .fs-13 { font-size: 13px; }
        .ajustify, .ajustify * { text-align: justify; }
        .p6h > tr > td, .p6h > tbody > tr > td { padding-left: 6px; padding-right: 6px; }
        .p6v > tr > td, .p6v > tbody > tr > td { padding-top: 6px; padding-bottom: 6px; }
        .tbl-usi_label-center {
            background-image: url(/img/bg/info/tbl-usi_label-center.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-weight: bold;
            font-size: 11px;
            color: #FCF5B7;
            padding: 0 10px 3px;
        }
    </style>
</head>
<body>

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    @php
        $btnLeft1 = 'img/bg/btn/btn-left1.gif';
        $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
        $btnRight1 = 'img/bg/btn/btn-right1.gif';

        $btnLeft2 = 'img/bg/btn/btn-left2.gif';
        $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
        $btnRight2 = 'img/bg/btn/btn-right2.gif';
    @endphp
    <tr height="21">
        <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="120" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="" class="btn_2">{{ $npc->name }}</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>
        <td></td>
        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="140" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('npc', ['id' => $npc->id]) }}" class="btn_1">Вернуться к НПС</a>
        </td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="5" width="100%" height="100%">
    <tbody>
    <tr><td height="10"></td></tr>
    <tr>
        <td valign="top">
            <table class="coll" width="100%">
                <tbody>
                <tr>
                    {{-- Диалог: заголовок узла, текст, варианты ответов --}}
                    <td valign="top" width="100%">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                            <td align="center" class="tbl-usi_label-center">Диалог</td>
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" align="left" style="padding: 6px 4px;">

                                    <table class="coll w100 p6h p6v" width="100%">
                                        <tbody>
                                        {{-- Заголовок узла --}}
                                        <tr class="bg_l brd2-all">
                                            <td width="1%"><img src="{{ asset('img/icon/qst_dlg_m.gif') }}" width="46" height="28"></td>
                                            <td class="redd2 b fs-13">{{ $node->title }}</td>
                                            <td align="right">&nbsp;</td>
                                        </tr>
                                        {{-- Текст узла --}}
                                        <tr class="fs-12">
                                            <td class="ajustify" colspan="3" style="line-height: 1.45;">
                                                {!! nl2br(e($node->description)) !!}
                                            </td>
                                        </tr>
                                        {{-- Варианты ответов --}}
                                        @php $rowIdx = 0; @endphp
                                        @foreach($node->activeOptions as $option)
                                            @if($option->toNode)
                                                <tr class="{{ $rowIdx++ % 2 === 0 ? 'bg_l brd2-all' : '' }} fs-12 pointer"
                                                    onclick="location.href='{{ route('npc.dialogue', ['id' => $npc->id, 'node' => $option->toNode->id, 'from' => $node->id]) }}'"
                                                    onmouseover="this.className += ' bg_l2';" onmouseout="this.className = this.className.split(' bg_l2').join('');">
                                                    <td width="1%"><img src="{{ asset('img/icon/qst_talk.gif') }}" alt=""></td>
                                                    <td class="ajustify" colspan="2"><b>{{ $option->button_text }}</b></td>
                                                </tr>
                                            @endif
                                        @endforeach
                                        {{-- Назад — к узлу, из которого пришли --}}
                                        @if(!empty($backNode))
                                            <tr class="{{ $rowIdx++ % 2 === 0 ? 'bg_l brd2-all' : '' }} fs-12 pointer"
                                                onclick="location.href='{{ route('npc.dialogue', ['id' => $npc->id, 'node' => $backNode->id]) }}'"
                                                onmouseover="this.className += ' bg_l2';" onmouseout="this.className = this.className.split(' bg_l2').join('');">
                                                <td width="1%"><img src="{{ asset('img/icon/qst_talk.gif') }}" alt=""></td>
                                                <td class="ajustify" colspan="2"><b>« Назад</b></td>
                                            </tr>
                                        @endif
                                        {{-- Завершение разговора --}}
                                        <tr class="{{ $rowIdx++ % 2 === 0 ? 'bg_l brd2-all' : '' }} fs-12 pointer"
                                            onclick="location.href='{{ route('npc', ['id' => $npc->id]) }}'"
                                            onmouseover="this.className += ' bg_l2';" onmouseout="this.className = this.className.split(' bg_l2').join('');">
                                            <td width="1%"><img src="{{ asset('img/icon/qst_talk.gif') }}" alt=""></td>
                                            <td class="ajustify" colspan="2"><b>Закончить разговор.</b></td>
                                        </tr>
                                        </tbody>
                                    </table>

                                </td>
                                <td class="tbl-shp-sides rs">&nbsp;</td>
                            </tr>
                            <tr height="18">
                                <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
                                <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
                                <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>

                    <td width="16"><img src="{{ asset('img/icon/d.gif') }}" width="16" height="1"></td>

                    {{-- Карточка НПС: портрет + описание --}}
                    <td valign="top" width="202">
                        <table width="240" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">&nbsp;</td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 2px;">
                                    @if($npc->image)
                                        <img src="{{ $npc->image }}" alt="{{ $npc->name }}" width="190"><br>
                                    @endif
                                    <div class="p2v" style="padding: 4px 6px;">{!! $npc->description !!}</div>
                                </td>
                                <td class="tbl-shp-sides rs">&nbsp;</td>
                            </tr>
                            <tr height="18">
                                <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
                                <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
                                <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
