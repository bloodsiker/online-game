<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Игра</title>
    <style>
        html {
            height: 100%;
            width: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            background-color: #ffe4aa;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        .info-area {
            width: 100%;
            height: 100%;
            position: relative;
            padding: 2px;
        }
        .info {
            margin-bottom: 2px;
            color: #554848 !important;
        }
        .pnick {
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            color: #674F3D !important;
        }
        .time {
            font-size: 11px;
        }
        a {
            color: #000;
        }
        a.n {
            text-decoration: none;
        }
        a:hover{
            color: #000
        }
        .lgb {
            background-image: url({{ asset('img/bg/lgb.gif') }});
            background-repeat: repeat;
        }
        .lgb-left {
            background-image: url({{ asset('img/icon/lgb-left.gif') }});
            background-repeat: repeat-y;
            width: 14px;
        }
        .lgb-right {
            background-image: url({{ asset('img/icon/lgb-right.gif') }});
            background-repeat: repeat-y;
            width: 15px;
        }

        .tbl-main_separator-v {
            background-image: url({{ asset('img/bg/separator_v.gif') }});
            background-repeat: repeat-y;
            width: 3px;
        }
        .user_offline {
            color: #B09A8B!important;
        }
        .clan-icon {
            width: 13px;
            height: 13px;
            vertical-align: middle;
            margin-right: 3px;
        }
        .clan-tag {
            font-size: 11px;
            color: #5B4736;
            margin-left: 2px;
            margin-right: 3px;
        }
        .info-icon-link {
            margin-left: 3px;
        }
        .prv-btn {
            cursor: pointer;
            vertical-align: middle;
        }

    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
    <tbody>
    <tr class="lgb" width="100%" height="100%" style="vertical-align: top">
        <td width="1%" class="lgb-left" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
        <td>
            <center><b style="color:green">На локации: <span id="location-count">{{ $page->countOnlineLocation }}</span></b></center>
            <br>

            <div id="location-users">
            @foreach($page->onlineOnLocation as $user)
                <div class="info">
                    <span class="{{ $user->isOnline ? '' : 'user_offline' }}">
                        <span class="time">{{ $user->time }}</span>
                        <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват"
                             onclick="sendPrivate('{{ addslashes($user->name) }}')" alt="Приватное сообщение">
                        @if($user->clanName)
                            @if($user->clanIcon && $user->clanId)
                                <a href="{{ route('clan.public', ['clan' => $user->clanId]) }}" title="Информация о клане" onclick="whoOpenClanInfo(this.href); return false;"><img class="clan-icon" src="{{ $user->clanIcon }}"
                                     title="{{ $user->clanName }}" alt="{{ $user->clanName }}"
                                     style="{{ $user->isOnline ? '' : 'opacity:.6' }}"></a>
                            @else
                                <span class="clan-tag">[{{ $user->clanName }}]</span>
                            @endif
                        @endif
                        <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank"
                           class="pnick {{ $user->isOnline ? '' : 'user_offline' }}"
                           data-uid="{{ $user->id }}" data-name="{{ $user->name }}"
                           title="Информация о персонаже"><b>{{ $user->name }} [{{ $user->lvl }}]</b></a>
                        <a href="#" class="info-icon-link" title="Информация о персонаже" onclick="whoOpenUserInfo({{ $user->id }}); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a>
                    </span>
                </div>
            @endforeach
            </div>

            <br>
            <hr>
            <center><b style="color:green">Онлайн: <span id="global-online-count">{{ $page->countOnlineInGame }}</span></b></center>

            <div id="online-users">
                @foreach($page->onlineInGame as $user)
                    <div class="info">
                    <span class="time">{{ $user->time }}</span>
                    <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват"
                         onclick="sendPrivate('{{ addslashes($user->name) }}')" alt="Приватное сообщение">
                    @if($user->clanName)
                        @if($user->clanIcon && $user->clanId)
                            <a href="{{ route('clan.public', ['clan' => $user->clanId]) }}" title="Информация о клане" onclick="whoOpenClanInfo(this.href); return false;"><img class="clan-icon" src="{{ $user->clanIcon }}" title="{{ $user->clanName }}" alt="{{ $user->clanName }}"></a>
                        @else
                            <span class="clan-tag">[{{ $user->clanName }}]</span>
                        @endif
                    @endif
                    <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank"
                       class="pnick" data-uid="{{ $user->id }}" data-name="{{ $user->name }}"
                       title="Информация о персонаже"><b>{{ $user->name }} [{{ $user->lvl }}]</b></a>
                    <a href="#" class="info-icon-link" title="Информация о персонаже" onclick="whoOpenUserInfo({{ $user->id }}); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" align="absmiddle"></a>
                    </div>
                @endforeach
            </div>
        </td>
        <td width="1%" class="lgb-right" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3">
            <img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1">
        </td>
        <td id="td_user_count" width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);">
            <b>Онлайн:</b> <b style="color:#FF0000;" id="chat_user_count">{{ $page->countOnlineInGame }}</b>
        </td>
    </tr>
    </tbody>
</table>

<script src="{{ asset('js/player_menu.js') }}?v={{ filemtime(public_path('js/player_menu.js')) }}"></script>
<script>
    function sendPrivate(name) {
        try {
            var bottomFrame = window.parent.document.getElementById('bottom-frame');
            if (bottomFrame && bottomFrame.contentWindow) {
                bottomFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
            }
        } catch (e) {
            console.error('[who] error:', e);
        }
    }

    var prvArrowSrc = '{{ asset('img/icon/users-arrow.gif') }}';
    var infoUrlBase = '{{ url('/info/user') }}/';
    var playerInfoIconSrc = '{{ asset('main/images/player_info.gif') }}';

    // Иконка информации о персонаже возле ника — открывает карточку игрока в отдельном окне
    function whoOpenUserInfo(userId) {
        window.open('{{ url('/info/u') }}/' + userId, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }

    function whoOpenClanInfo(url) {
        window.open(url, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function buildUsersHtml(users) {
        var html = '';

        users.forEach(function (u) {
            var name = String(u.name || '');
            var safeName = escapeHtml(name);
            var safeClanName = escapeHtml(u.clan_name || '');
            var safeTime = escapeHtml(u.time || '');
            var safeInfoUrl = escapeHtml(u.info_url || '');
            var privateArgument = escapeHtml(JSON.stringify(name));
            var offCls = u.is_online ? '' : ' user_offline';
            var opacity = u.is_online ? '' : 'opacity:.6;';

            var clan = '';
            if (u.clan_icon && u.clan_id) {
                clan = '<a href="{{ url('/clan-info') }}/' + parseInt(u.clan_id, 10) + '" title="Информация о клане" onclick="whoOpenClanInfo(this.href); return false;">'
                    + '<img class="clan-icon" src="' + escapeHtml(u.clan_icon) + '" title="' + safeClanName + '" alt="' + safeClanName + '" style="' + opacity + '"></a>';
            } else if (u.clan_name) {
                clan = '<span class="clan-tag">[' + safeClanName + ']</span>';
            }

            html += '<div class="info">'
                +   '<span class="' + offCls.trim() + '">'
                +     '<span class="time">' + safeTime + '</span> '
                +     '<img src="' + prvArrowSrc + '" class="prv-btn" title="Написать в приват" onclick="sendPrivate(' + privateArgument + ')" alt="Приват"> '
                +     clan
                +     '<a href="' + safeInfoUrl + '" target="_blank" class="pnick' + offCls + '" data-uid="' + Number(u.id) + '" data-name="' + safeName + '" title="Информация о персонаже"><b>' + safeName + ' [' + Number(u.lvl || 0) + ']</b></a>'
                +     '<a href="#" class="info-icon-link" title="Информация о персонаже" onclick="whoOpenUserInfo(' + Number(u.id) + '); return false;"><img src="' + playerInfoIconSrc + '" width="10" height="10" align="absmiddle"></a>'
                +   '</span>'
                + '</div>';
        });

        return html;
    }

    function renderLocationUsers(users) {
        var container = document.getElementById('location-users');
        var countEl   = document.getElementById('location-count');
        if (!container) return;

        var online = users.filter(function (u) { return u.is_online; });
        if (countEl) countEl.textContent = online.length;

        container.innerHTML = buildUsersHtml(users);
    }

    function renderOnlineUsers(users, count) {
        var container = document.getElementById('online-users');
        var globalCount = document.getElementById('global-online-count');
        var bottomCount = document.getElementById('chat_user_count');
        if (container) container.innerHTML = buildUsersHtml(users);
        if (globalCount) globalCount.textContent = count;
        if (bottomCount) bottomCount.textContent = count;
    }

    window.addEventListener('message', function (e) {
        if (e.data && e.data.type === 'locationUsers') {
            renderLocationUsers(e.data.users);
        } else if (e.data && e.data.type === 'onlinePresenceSnapshot') {
            var users = Array.isArray(e.data.users) ? e.data.users : [];
            var currentLocationId = Number(e.data.viewerLocationId || {{ (int) auth()->user()->location_id }});
            renderOnlineUsers(users, Number(e.data.count ?? users.length));
            renderLocationUsers(users.filter(function (u) { return Number(u.location_id) === currentLocationId; }));
        }
    });

    window.top.postMessage({ type: 'requestOnlinePresence' }, window.location.origin);

    // ── Контекстное меню персонажа (как на проде: ПКМ по нику) ──────────────
    initPlayerMenu({
        myUserId: {{ (int) auth()->id() }},
        csrfToken: document.querySelector('meta[name="csrf-token"]').content,
        ignoredIds: @json(array_map('intval', $ignoredUserIds)),
        friendsAddUrl: '{{ route('friends.add') }}',
        ignoreAddUrl: '{{ route('chat.ignore.add') }}',
        ignoreRemoveBase: '{{ url('/chat/ignore') }}/',
        infoUrlBase: '{{ url('/info/u') }}/',
        sendPrivate: sendPrivate,
        selector: 'a.pnick[data-uid]',
    });
</script>
</body>
</html>
