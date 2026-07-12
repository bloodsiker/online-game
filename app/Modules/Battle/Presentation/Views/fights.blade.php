<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Бои</title>
    <style>
        html, body { height: 100%; margin: 0; font-family: Tahoma; font-size: 11px; }
        .regcolor, .regcolor * { color: #955C4A; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        a { text-decoration: none; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        table.vatop td { vertical-align: top; }
        .b { font-weight: bold; }
        .w100 { width: 100%; }
        .h100 { height: 100%; }
        .blue, .blue * { color: #0000d0; }
        .redd, .redd * { color: #BA0000 !important; }
        .grnn, .grnn * { color: #114d01 !important; }
        .dim, .dim * { color: #c49485; }
        .small, .small * { font-size: 10px; }
        .dbgl { background-color: #FFE7C5; }
        .brd, .brd td { border: 1px solid #C49485; }
        .brd-top { border-top: 1px solid #C49485; }
        .brd-bt { border-bottom: 1px solid #C49485; }
        .nobrd, .nobrd td { border: none !important; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .p0, .p0 td { padding: 0; }
        .pointer, .pointer input { cursor: pointer; }
        img { border: 0; }

        /* ── Стандартная рамка (tbl-shp-sml / tbl-usi), как на рейтинге и событиях ── */
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-usi_bg { background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .tbl-usi-hdr { background: url({{ asset('img/bg/tbl-usi-hdr.gif') }}) no-repeat; font-family: tahoma, sans-serif; height: 22px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px 10px; line-height: 16px; vertical-align: middle; }

        /* ── Кнопка «Поиск» ── */
        b.butt1 { height: 36px; font-size: 26px; cursor: pointer; background: url({{ asset('main/images/tbl-btn2_c-left.png') }}) left top no-repeat; display: inline-block; }
        b.butt1 b { height: 36px; font-size: 26px; cursor: pointer; background: url({{ asset('main/images/tbl-btn2_c-right.png') }}) right top no-repeat; display: inline-block; }
        b.butt1 input { height: 36px; border: 0; color: #f8dea4 !important; cursor: pointer; font-family: Tahoma; font-size: 11px !important; font-weight: bold; text-decoration: none; margin: 0 33px; background: transparent url({{ asset('main/images/tbl-btn2_center.png') }}) center top repeat-x; padding-bottom: 2px; outline: none; }

        /* Поля формы */
        input[type=text], select { border: 1px solid #C49485; background-color: #FFE7C5; color: #BA0000; font-family: Tahoma; font-size: 10px; font-weight: bold; padding: 1px 2px; }

        /* Пагинация — как на странице рейтинга */
        .pg, .pg td { color: #8D2616; height: 17px; text-align: center; vertical-align: center !important; padding-left: 1px; padding-right: 1px; }
        .pg-inact { margin: 1px; padding: 1px 0 0 2px; text-align: center; background: url({{ asset('img/bg/pg-inact.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-act { margin: 1px; padding: 1px 0 0 2px; text-align: center; background: url({{ asset('img/bg/pg-act.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-inact_lnk { color: #C50000 !important; font-size: 9px; font-weight: bold; }
        .pg-act_lnk { color: #FFF3D2 !important; font-size: 9px; font-weight: bold; }
    </style>
</head>
<body class="regcolor" leftmargin="0" rightmargin="0">
<table class="coll w100 h100">
<tbody><tr><td valign="top">

    {{-- ── Табы: Текущие бои / Завершенные бои / Вернуться (стиль страницы событий) ── --}}
    @php
        $tabs = [
            'running'  => 'Текущие бои',
            'finished' => 'Завершенные бои',
            'all'      => 'Все бои',
        ];
        $btnLeft1   = 'img/bg/btn/btn-left1.gif';
        $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
        $btnRight1  = 'img/bg/btn/btn-right1.gif';
        $btnLeft2   = 'img/bg/btn/btn-left2.gif';
        $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
        $btnRight2  = 'img/bg/btn/btn-right2.gif';
    @endphp
    <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
        <tbody>
        <tr height="21">
            @foreach($tabs as $tabKey => $tabLabel)
                @php $isActive = $page->mode === $tabKey; @endphp
                <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
                <td align="center" nowrap style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                    <a href="{{ route('fights', ['mode' => $tabKey]) }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tabLabel }}</a>
                </td>
                <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
            @endforeach

            <td width="100%"></td>

            <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
            <td align="center" nowrap style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="#" class="btn_1" onclick="window.parent.sendDataToGame('{{ route('location') }}'); return false;">Вернуться</a>
            </td>
            <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
        </tr>
        </tbody>
    </table>

    <div style="padding: 10px 6px;">
    <table cellspacing="0" cellpadding="0" width="100%">
    <tbody><tr valign="top">
      {{-- ────────── Левая панель: список боев ────────── --}}
      <td width="54%" valign="top">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tbody>
          <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                  <tbody><tr height="22">
                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                    <td align="center" class="tbl-usi-hdr mbg">Список боев</td>
                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                  </tr></tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
          </tr>
          <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 6px 10px;">

                <div class="dim" style="padding-bottom: 6px;">{{ $page->mode === 'all' ? 'Все бои на сервере' : 'Бои на этой локации' }}</div>

                @if($page->fights->isEmpty())
                    <div class="brd-top brd-bt" style="padding: 1px 0px 1px 0px;">Список пуст!</div>
                @else
                    @foreach($page->fights as $fight)
                        <table class="brd coll p6h p2v dbgl w100">
                        <tbody>
                        <tr class="nobrd small">
                            <td class="dim" width="1%" nowrap>{{ $fight->startedAt }}</td>
                            <td class="redd b">
                                <a href="{{ route('fight.log', $fight->id) }}" onclick="fightsOpenWindow(this.href, 990, 700); return false;">{{ $fight->title }}</a>
                                @if($fight->locationName)
                                    <small class="dim">&mdash; {{ $fight->locationName }} [{{ $fight->locationId }}]</small>
                                @endif
                            </td>
                            <td align="right">раундов: <b class="redd">{{ $fight->rounds }}</b></td>
                        </tr>
                        <tr><td colspan="3" style="padding-top: 6px; padding-bottom: 6px;">
                            <table class="nobrd w100 vatop"><colgroup><col width="50%"><col width="50%"></colgroup>
                            <tbody><tr>
                              <td>
                                @foreach($fight->players as $p)
                                    <a href="#" title="Приватное сообщение" onclick="fightsInsertPrivate({{ \Illuminate\Support\Js::from($p['name']) }}); return false;"><img src="{{ asset('main/images/users-arrow.gif') }}" width="12" height="10" align="absmiddle"></a>&nbsp;<a><b class="grnn" @if(!$p['alive']) style="text-decoration: line-through;" @endif>{{ $p['name'] }}&nbsp;[{{ $p['level'] }}]</b></a>&nbsp;<a href="{{ route('info.user', $p['userId']) }}" title="Информация о персонаже" onclick="fightsOpenWindow(this.href, 930, 700); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a><br>
                                @endforeach
                              </td>
                              <td>
                                @foreach($fight->monsters as $m)
                                    <a><b class="blue" @if(!$m['alive']) style="text-decoration: line-through;" @endif>{{ $m['name'] }}&nbsp;[{{ $m['level'] }}]</b></a>&nbsp;<a href="{{ route('info.monster', $m['locationMonsterId']) }}" title="Информация о существе" onclick="fightsOpenWindow(this.href, 915, 700); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a><br>
                                @endforeach
                              </td>
                            </tr></tbody>
                            </table>
                        </td></tr>
                        <tr class="nobrd small">
                            <td colspan="3">
                                Тип: <b class="redd">{{ $fight->typeLabel }}</b>&nbsp;&nbsp;&nbsp;
                                Длительность: <b class="redd">{{ $fight->duration }}</b>&nbsp;&nbsp;&nbsp;
                                @if($fight->winnerLabel)<b>{{ $fight->winnerLabel }}</b>@endif
                            </td>
                        </tr>
                        </tbody>
                        </table>
                        @if(!$loop->last)<img src="{{ asset('main/images/d.gif') }}" width="1" height="4"><br>@endif
                    @endforeach

                    @if($page->fights->lastPage() > 1)
                        @php
                            $currentPage = $page->fights->currentPage();
                            $lastPage = $page->fights->lastPage();
                            $pageFrom = max(1, $currentPage - 5);
                            $pageTo = min($lastPage, $currentPage + 5);
                        @endphp
                        <table border="0" cellpadding="0" cellspacing="0" class="pg w100">
                        <tbody>
                        <tr>
                            <td class="b" width="1%" nowrap><nobr>Страницы:&nbsp;</nobr></td>
                            @if($pageFrom > 1)
                                <td class="pg-inact" width="1%" nowrap><a href="{{ $page->fights->url(1) }}" class="pg-inact_lnk">1</a></td>
                                @if($pageFrom > 2)<td class="b" width="1%" nowrap style="padding:0 2px">…</td>@endif
                            @endif
                            @for($p = $pageFrom; $p <= $pageTo; $p++)
                                <td class="{{ $p === $currentPage ? 'pg-act' : 'pg-inact' }}" width="1%" nowrap>
                                    <a href="{{ $page->fights->url($p) }}" class="{{ $p === $currentPage ? 'pg-act_lnk' : 'pg-inact_lnk' }}">{{ $p }}</a>
                                </td>
                            @endfor
                            @if($pageTo < $lastPage)
                                @if($pageTo < $lastPage - 1)<td class="b" width="1%" nowrap style="padding:0 2px">…</td>@endif
                                <td class="pg-inact" width="1%" nowrap><a href="{{ $page->fights->url($lastPage) }}" class="pg-inact_lnk">{{ $lastPage }}</a></td>
                            @endif
                            <td style="text-align: left;"></td>
                            <td width="1%" style="text-align:right" nowrap>
                                @if($page->fights->onFirstPage())
                                    <img src="{{ asset('img/bg/p-left-gray.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                                @else
                                    <a href="{{ $page->fights->previousPageUrl() }}"><img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая"></a>
                                @endif
                                <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
                                @if($page->fights->hasMorePages())
                                    <a href="{{ $page->fights->nextPageUrl() }}"><img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая"></a>
                                @else
                                    <img src="{{ asset('img/bg/p-right-gray.gif') }}" border="0" width="29" height="17" title="Следующая">
                                @endif
                            </td>
                        </tr>
                        </tbody>
                        </table>
                    @endif
                @endif

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
      <td width="10"><img src="{{ asset('main/images/d.gif') }}" width="10" height="1"></td>

      {{-- ────────── Правая панель: поиск ────────── --}}
      <td width="46%" valign="top">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
          <tbody>
          <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                  <tbody><tr height="22">
                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                    <td align="center" class="tbl-usi-hdr mbg">Поиск</td>
                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                  </tr></tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
          </tr>
          <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" align="center" style="padding: 6px 10px;">

                @php
                    $months = [1=>'января',2=>'февраля',3=>'марта',4=>'апреля',5=>'мая',6=>'июня',7=>'июля',8=>'августа',9=>'сентября',10=>'октября',11=>'ноября',12=>'декабря'];
                    $today = \Illuminate\Support\Carbon::now();
                    [$fromD, $fromM, $fromY] = $page->filter->dateFrom
                        ? array_reverse(explode('-', $page->filter->dateFrom))
                        : [$today->day, $today->month, $today->year];
                    [$toD, $toM, $toY] = $page->filter->dateTo
                        ? array_reverse(explode('-', $page->filter->dateTo))
                        : [$today->day, $today->month, $today->year];
                @endphp

                <form method="get" action="{{ route('fights') }}">
                <input type="hidden" name="mode" value="{{ $page->mode }}">
                <table width="96%" border="0" cellspacing="0" cellpadding="4">
                <tbody>
                <tr>
                    <td colspan="100">
                        <table class="coll p0 w100">
                        <tbody><tr><td><b>Название боя</b></td></tr>
                        <tr><td class="brd-top brd-bt"><input name="form[title]" value="{{ $page->filter->monsterName }}" type="text" maxlength="16" class="w100"></td></tr>
                        </tbody></table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table class="coll p0 w100">
                        <tbody><tr><td><b>Ник персонажа</b></td></tr>
                        <tr><td class="brd-top brd-bt"><input name="form[nick]" value="{{ $page->filter->nick }}" type="text" maxlength="16" style="width:140px"></td></tr>
                        </tbody></table>
                    </td>
                    <td width="99%"></td>
                </tr>
                <tr>
                    <td colspan="100">
                        <table class="coll p0">
                        <tbody><tr><td><b>От</b></td></tr>
                        <tr><td class="brd-top brd-bt" nowrap>
                            <select name="form[start_dd]">
                                @for($d = 1; $d <= 31; $d++)<option value="{{ $d }}" @selected((int)$fromD === $d)>{{ sprintf('%02d', $d) }}</option>@endfor
                            </select><select name="form[start_mm]">
                                @foreach($months as $mNum => $mName)<option value="{{ $mNum }}" @selected((int)$fromM === $mNum)>{{ $mName }}</option>@endforeach
                            </select><select name="form[start_yy]">
                                @for($y = $today->year - 1; $y <= $today->year; $y++)<option value="{{ $y }}" @selected((int)$fromY === $y)>{{ $y }}</option>@endfor
                            </select>
                        </td></tr>
                        </tbody></table>
                        <table class="coll p0">
                        <tbody><tr><td><b>До</b></td></tr>
                        <tr><td class="brd-top brd-bt" nowrap>
                            <select name="form[end_dd]">
                                @for($d = 1; $d <= 31; $d++)<option value="{{ $d }}" @selected((int)$toD === $d)>{{ sprintf('%02d', $d) }}</option>@endfor
                            </select><select name="form[end_mm]">
                                @foreach($months as $mNum => $mName)<option value="{{ $mNum }}" @selected((int)$toM === $mNum)>{{ $mName }}</option>@endforeach
                            </select><select name="form[end_yy]">
                                @for($y = $today->year - 1; $y <= $today->year; $y++)<option value="{{ $y }}" @selected((int)$toY === $y)>{{ $y }}</option>@endfor
                            </select>
                        </td></tr>
                        </tbody></table>
                    </td>
                </tr>
                <tr>
                    <td colspan="100" align="center">
                        <b class="butt1 pointer"><b><input value="Поиск" type="submit"></b></b><br>
                    </td>
                </tr>
                </tbody>
                </table>
                </form>

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
    </tr></tbody>
    </table>
    </div>

</td></tr></tbody>
</table>

<script>
    // Открыть ссылку (лог боя, инфо о персонаже/существе) в отдельном окне браузера
    function fightsOpenWindow(url, width, height) {
        window.open(url, '', 'width=' + width + ',height=' + height + ',location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }

    // Клик по стрелке рядом с игроком — вставляет шорткод to[NAME] в поле чата для приватного сообщения
    function fightsInsertPrivate(name) {
        try {
            var chatFrame = window.parent.document.getElementById('chat-frame');
            var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
            bottomFrame.contentWindow.postMessage({ type: 'insertName', name: name }, '*');
        } catch (e) {}
        return false;
    }
</script>
</body>
</html>
