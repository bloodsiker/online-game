<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рейтинг</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 11px; }
        a, a:link, a:visited, a:active { color: #BA0000; text-decoration: none; }
        .b { font-weight: bold; }
        img { vertical-align: middle; }
        .regblk, .regblk * { color: #49382D; }
        .t0 { background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left; background-color: #EDD5C3; }
        .t1 { background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left; background-color: #DFBBA3; }
        .l0 { background-color: #FFF8EA; }
        .l1 { background-color: #FFFBF5; }
        .tbgr { background-color: #FADCC2; }
        .w100 { width: 100%; }
        .tbl-shp-sides.ls, .tbl-shp-sides_0.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs, .tbl-shp-sides_0.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt, .tbl-shp-sml_0.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt, .tbl-shp-sml_0.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lt, .tbl-shp-sml_0.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.lb, .tbl-shp-sml_0.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb, .tbl-shp-sml_0.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb, .tbl-shp-sml_0.rb { background-position: 0 -100px; }
        .tbl-shp-sml { background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat; font-size: 0; }
        .tbl-shp-sides { background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat; font-size: 0; }
        .tbl-usi_bg { background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }}); background-repeat: repeat; }
        .regcolor, .regcolor * { color: #955c4a; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .tbl-usi-hdr.lc { background-position: left -25px; width: 27px; }
        .tbl-usi-hdr { background: url({{ asset('img/bg/tbl-usi-hdr.gif') }}) no-repeat; font-family: tahoma, sans-serif; height: 22px; }
        .c-s-n-fon { background: url({{ asset('img/bg/table/c-fon-n-s.gif') }}) left top repeat; }
        .tbl-usi-hdr.lc b, .tbl-usi-hdr.rc b { display: block; height: 22px; font-size: 0; overflow: hidden; width: 27px; }
        .tbl-usi-hdr.mbg { background-position: center -50px; background-repeat: repeat-x; color: #FCF5B7; font-size: 11px; font-weight: bold; height: 16px; padding: 1px 10px 5px 10px; line-height: 16px; vertical-align: middle; }
        .tbl-usi-hdr.rc { background-position: right 0; width: 27px; }
        .ach_menu td, .ach_menu td a { color: #775d42 !important; font-size: 11px; font-weight: bold; text-decoration: none; }
        .ach_menu td { height: 22px; padding-left: 5px; }
        .ach_menu_act { border: 1px solid #d4a182; border-left-width: 0; border-right-width: 0; color: #775d42; font-size: 11px; font-weight: bold; height: 22px; padding-left: 5px; }
        .ach_menu td img { margin-right: 6px; border: 0; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .pg, .pg td { color: #8D2616; height: 17px; text-align: center; vertical-align: center !important; padding-left: 1px; padding-right: 1px; }
        .pg-inact { margin: 1px; padding: 1px 0 0 2px; text-align: center; background: url({{ asset('img/bg/pg-inact.gif') }}) no-repeat center center; background-repeat: no-repeat; height: 17px; width: 17px; }
        .pg-act { margin: 1px; padding: 1px 0 0 2px; text-align: center; background: url({{ asset('img/bg/pg-act.gif') }}) no-repeat center center; height: 17px; width: 17px; }
        .pg-inact_lnk { color: #C50000 !important; font-size: 9px; font-weight: bold; }
        .pg-act_lnk { color: #FFF3D2 !important; font-size: 9px; font-weight: bold; }
        .brd, .brd td { border: 1px solid #C49485; }
        .dbgl { background-color: #FFE7C5; }
        table.user-rating { border-top: #db9f73 1px solid; border-left: #db9f73 1px solid; border-collapse: separate !important; }
        .user-rating td, .user-rating th { height: 15px; padding: 4px; color: #631c0b; border-right: #db9f73 1px solid; border-bottom: #db9f73 1px solid; }
        .user-rating th, .user-rating th span { color: #955c4a; }
        .user-rating-red, .user-rating-red b { font-weight: bold; color: #ba0000 !important; }
        .user-rating th span { padding: 0 0 1px 20px; background: url({{ asset('img/icon/rating_headers_sprite.png') }}) 0 0 no-repeat; }
        .user-rating th .user-rating-reputation { background-position: 0 -44px; }
        .user-rating th .user-rating-progress { background-position: 0 -67px; }
        .bg_l3 { background-image: url({{ asset('img/bg/bg_l3.gif') }}); cursor: pointer; }
    </style>
</head>
<body class="regblk">
<table cellspacing="0" cellpadding="0" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td width="1%" valign="top">
            <table width="250px" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">Рейтинги</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                        <table class="w100 ach_menu" cellpadding="0" cellspacing="0">
                            <tbody>
                            @php $firstSkillSeen = false; @endphp
                            @foreach($page->menu as $menuItem)
                                @if(!$firstSkillSeen && $menuItem->isSkill)
                                    @php $firstSkillSeen = true; @endphp
                                    <tr><td height="6"></td></tr>
                                @endif
                                <tr>
                                    <td height="10" class="@if($menuItem->isActive) c-s-n-fon ach_menu_act @else ach_menu_pas @endif">
                                        <a href="{{ route('rating', ['type' => $menuItem->key]) }}">
                                            <img src="{{ asset('img/icon/users-arrow.gif') }}" alt="{{ $menuItem->title }}" align="absMiddle">{{ $menuItem->title }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
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
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                                <td align="center" class="tbl-usi-hdr mbg">{{ $page->title }}</td>
                                <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" align="center" style="padding: 4px 0 4px 0">
                        <table class="coll" cellspacing="0">
                            <tbody>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" class="pg w100">
                                        <tbody>
                                        <tr>
                                            <td class="b" width="10"><nobr>Страницы:&nbsp;</nobr></td>
                                            @if($page->pagination['pageFrom'] > 1)
                                                <td class="pg-inact"><a href="{{ $page->pagination['urls'][1] }}" class="pg-inact_lnk">1</a></td>
                                                @if($page->pagination['pageFrom'] > 2)<td class="b" style="padding:0 2px">…</td>@endif
                                            @endif
                                            @for($p = $page->pagination['pageFrom']; $p <= $page->pagination['pageTo']; $p++)
                                                <td class="{{ $p === $page->pagination['currentPage'] ? 'pg-act' : 'pg-inact' }}">
                                                    <a href="{{ $page->pagination['urls'][$p] }}" class="{{ $p === $page->pagination['currentPage'] ? 'pg-act_lnk' : 'pg-inact_lnk' }}">{{ $p }}</a>
                                                </td>
                                            @endfor
                                            @if($page->pagination['pageTo'] < $page->pagination['lastPage'])
                                                @if($page->pagination['pageTo'] < $page->pagination['lastPage'] - 1)<td class="b" style="padding:0 2px">…</td>@endif
                                                <td class="pg-inact"><a href="{{ $page->pagination['urls'][$page->pagination['lastPage']] }}" class="pg-inact_lnk">{{ $page->pagination['lastPage'] }}</a></td>
                                            @endif
                                            <td style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>Найти игрока:</strong>&nbsp;
                                                <input id="user_nick" value="" onkeydown="if(event.keyCode==13)search_user(this.value);" class="brd dbgl" style="width: 128px;">&nbsp;&nbsp;
                                                <b class="butt2 pointer "><b><input value="Поиск" type="button" onclick="return search_user(gebi('user_nick').value);" style="width:50px"></b></b>&nbsp;&nbsp;&nbsp;&nbsp;
                                                <b class="butt2 pointer "><b><input value="Найти себя" type="button" onclick="return search_me();" style="width:100px"></b></b>
                                            </td>
                                            <td width="1%" style="text-align:right" nowrap="">
                                                @if($page->pagination['onFirstPage'])
                                                    <img src="{{ asset('img/bg/p-left-gray.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                                                @else
                                                    <a href="{{ $page->pagination['previousPageUrl'] }}"><img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая"></a>
                                                @endif
                                                <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
                                                @if($page->pagination['hasMorePages'])
                                                    <a href="{{ $page->pagination['nextPageUrl'] }}"><img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая"></a>
                                                @else
                                                    <img src="{{ asset('img/bg/p-right-gray.gif') }}" border="0" width="29" height="17" title="Следующая">
                                                @endif
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr><td height="100%" style="padding: 4px 0 4px 0;" align="left" valign="top"></td></tr>
                            </tbody>
                        </table>

                        <table width="60%" class="coll user-rating" cellspacing="0">
                            <thead>
                            <tr>
                                <th align="left" width="50">№</th>
                                <th align="left">Ник игрока</th>
                                <th width="140"><span class="user-rating-progress">{{ $page->columnName }}</span></th>
                            </tr>
                            <tr></tr>
                            </thead>
                            <tbody>
                            @foreach($page->entries as $entry)
                                @php
                                    $userNameJs = \Illuminate\Support\Js::from($entry->userName);
                                @endphp
                                <tr class="{{ $loop->even ? 'bg_l' : '' }}" id="pr-{{ $entry->userId }}" data-name="{{ mb_strtolower($entry->userName) }}">
                                    <td class="rating-nowrap" align="center"><span class="user-rating-red">{{ $entry->position }}</span></td>
                                    <td>
                                        <span class="user-rating-red">
                                            <a href="#" onclick="userPrvTag({{ $userNameJs }});return false;" title="Приватное сообщение">
                                                <img src="{{ asset('img/icon/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle">
                                            </a>
                                            @if($entry->hasClan && $entry->clanIconUrl)
                                                <img src="{{ $entry->clanIconUrl }}"
                                                     border="0"
                                                     width="13"
                                                     height="13"
                                                     align="absmiddle"
                                                     title="{{ $entry->clanName }}"
                                                     alt="{{ $entry->clanName }}">
                                            @endif
                                            <a>
                                                <b onclick="userToTag({{ $userNameJs }});return false;" title="Персональное сообщение" style="cursor:hand">
                                                    <b class="kser0" title="">{{ $entry->userName }}&nbsp;[{{ $entry->level }}]</b>
                                                </b>
                                            </a>
                                            <a href="#" onclick="showRatingUserInfo({{ $entry->userId }});return false;" title="Информация о персонаже">
                                                <img src="{{ asset('img/icon/player_info.gif') }}" border="0" align="absmiddle">
                                            </a>
                                        </span>
                                    </td>
                                    <td align="center"><span>{{ $entry->value }}</span></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>

                        <table class="coll" cellspacing="0">
                            <tbody>
                            <tr><td height="100%" style="padding: 4px 0 4px 0;" align="left" valign="top"></td></tr>
                            <tr>
                                <td>
                                    <table border="0" cellpadding="0" cellspacing="0" class="pg w100">
                                        <tbody>
                                        <tr>
                                            <td class="b" width="10"><nobr>Страницы:&nbsp;</nobr></td>
                                            @if($page->pagination['pageFrom'] > 1)
                                                <td class="pg-inact"><a href="{{ $page->pagination['urls'][1] }}" class="pg-inact_lnk">1</a></td>
                                                @if($page->pagination['pageFrom'] > 2)<td class="b" style="padding:0 2px">…</td>@endif
                                            @endif
                                            @for($p = $page->pagination['pageFrom']; $p <= $page->pagination['pageTo']; $p++)
                                                <td class="{{ $p === $page->pagination['currentPage'] ? 'pg-act' : 'pg-inact' }}">
                                                    <a href="{{ $page->pagination['urls'][$p] }}" class="{{ $p === $page->pagination['currentPage'] ? 'pg-act_lnk' : 'pg-inact_lnk' }}">{{ $p }}</a>
                                                </td>
                                            @endfor
                                            @if($page->pagination['pageTo'] < $page->pagination['lastPage'])
                                                @if($page->pagination['pageTo'] < $page->pagination['lastPage'] - 1)<td class="b" style="padding:0 2px">…</td>@endif
                                                <td class="pg-inact"><a href="{{ $page->pagination['urls'][$page->pagination['lastPage']] }}" class="pg-inact_lnk">{{ $page->pagination['lastPage'] }}</a></td>
                                            @endif
                                            <td style="text-align: left;"></td>
                                            <td width="1%" style="text-align:right" nowrap="">
                                                @if($page->pagination['onFirstPage'])
                                                    <img src="{{ asset('img/bg/p-left-gray.gif') }}" border="0" width="29" height="17" title="Предыдущая">
                                                @else
                                                    <a href="{{ $page->pagination['previousPageUrl'] }}"><img src="{{ asset('img/bg/p-left-red.gif') }}" border="0" width="29" height="17" title="Предыдущая"></a>
                                                @endif
                                                <img src="{{ asset('img/bg/pg-act.gif') }}" border="0" width="17" height="17">
                                                @if($page->pagination['hasMorePages'])
                                                    <a href="{{ $page->pagination['nextPageUrl'] }}"><img src="{{ asset('img/bg/p-right-red.gif') }}" border="0" width="29" height="17" title="Следующая"></a>
                                                @else
                                                    <img src="{{ asset('img/bg/p-right-gray.gif') }}" border="0" width="29" height="17" title="Следующая">
                                                @endif
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr><td height="100%" style="padding: 4px 0 4px 0;" align="left" valign="top"></td></tr>
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
    </tr>
    </tbody>
</table>

<script>
    var currentUserName = '{{ $page->currentUserName }}';
    var searchUrl = '{{ route('rating.search') }}';
    var ratingType = '{{ $page->type }}';
    var currentPage = {{ $page->pagination['currentPage'] }};
    var userInfoUrlBase = '{{ url('/info/u') }}/';

    function gebi(id) { return document.getElementById(id); }
    function ratingChatActionFrame() {
        var parents = [];
        try { parents.push(window.parent); } catch (e) {}
        try { if (window.top !== window.parent) parents.push(window.top); } catch (e) {}

        for (var i = 0; i < parents.length; i++) {
            try {
                var bottomFrame = parents[i].document.getElementById('bottom-frame');
                if (bottomFrame && bottomFrame.contentWindow) return bottomFrame.contentWindow;

                var chatFrame = parents[i].document.getElementById('chat-frame');
                if (chatFrame && chatFrame.contentDocument) {
                    bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
                    if (bottomFrame && bottomFrame.contentWindow) return bottomFrame.contentWindow;
                }
            } catch (e) {}
        }

        return null;
    }
    function ratingInsertChatTag(type, name) {
        var actionFrame = ratingChatActionFrame();
        if (actionFrame) {
            actionFrame.postMessage({ type: type, name: name }, '*');
        }

        return false;
    }
    function userPrvTag(name) {
        return ratingInsertChatTag('insertPrivate', name);
    }
    function userToTag(name) {
        return ratingInsertChatTag('insertName', name);
    }
    function showRatingUserInfo(userId) {
        window.open(userInfoUrlBase + userId, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');

        return false;
    }
    function highlightRow(row) {
        var prev = document.querySelector('.bg_l3');
        if (prev) prev.classList.remove('bg_l3');
        row.classList.add('bg_l3');
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
    function findOnPage(nick) {
        var rows = document.querySelectorAll('tr[data-name]');
        var lc = nick.toLowerCase();
        for (var i = 0; i < rows.length; i++) {
            if (rows[i].getAttribute('data-name') === lc) return rows[i];
        }
        return null;
    }
    function search_user(nick) {
        nick = (nick || '').trim();
        if (!nick) return false;
        fetch(searchUrl + '?nick=' + encodeURIComponent(nick) + '&type=' + ratingType)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.error) { alert(data.error); return; }
                if (data.page === currentPage) {
                    var row = findOnPage(nick);
                    if (row) highlightRow(row);
                } else {
                    window.location.href = '{{ route('rating') }}?type=' + ratingType + '&page=' + data.page + '&highlight=' + encodeURIComponent(nick);
                }
            });
        return false;
    }
    function search_me() { return search_user(currentUserName); }
    (function() {
        var params = new URLSearchParams(window.location.search);
        var highlight = params.get('highlight');
        var target = highlight ? highlight : currentUserName;
        var row = findOnPage(target);
        if (row) setTimeout(function() { highlightRow(row); }, 150);
    })();
    document.addEventListener('keydown', function(event) {
        switch (event.key.toLowerCase()) {
            case 'i': window.parent.sendDataToGame('{{ route('backpack') }}'); break;
            case 'c': window.parent.sendDataToGame('{{ route('character') }}'); break;
            case ' ': window.parent.sendDataToGame('{{ route('location') }}'); break;
            default: return;
        }
        event.preventDefault();
    });
    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>
</body>
</html>
