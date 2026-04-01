<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            margin-left: 2px;
        }
        .clan-tag {
            font-size: 11px;
            color: #5B4736;
            margin-left: 2px;
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
            <center><b style="color:green">На локации: <span id="location-count">{{ $countOnlineLocation }}</span></b></center>
            <br>

            <div id="location-users">
            @foreach($onlineOnLocation as $user)
                @php $clan = $user->clanMembership?->clan; @endphp
                <div class="info">
                    @php $isOnline = $user->last_online_at?->timestamp > $tenMinutesAgo->timestamp; @endphp
                    <span class="{{ $isOnline ? '' : 'user_offline' }}">
                        <span class="time">{{ $user->last_online_at->format('H:i') }}</span>
                        <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват"
                             onclick="sendPrivate('{{ addslashes($user->name) }}')" alt="Приватное сообщение">
                        <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank"
                           class="pnick {{ $isOnline ? '' : 'user_offline' }}"
                           title="Информация о персонаже"><b>{{ $user->name }} [{{ $user->player->lvl }}]</b></a>
                        @if($clan)
                            @if($clan->icon)
                                <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}"
                                     title="{{ $clan->name }}" alt="{{ $clan->name }}"
                                     style="{{ $isOnline ? '' : 'opacity:.6' }}">
                            @else
                                <span class="clan-tag">[{{ $clan->name }}]</span>
                            @endif
                        @endif
                    </span>
                </div>
            @endforeach
            </div>

            <br>
            <hr>
            <center><b style="color:green">Онлайн: {{ $onlineInGame->count() }}</b></center>

            @foreach($onlineInGame as $user)
                @php $clan = $user->clanMembership?->clan; @endphp
                <div class="info">
                    <span class="time">{{ $user->last_online_at->format('H:i') }}</span>
                    <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват"
                         onclick="sendPrivate('{{ addslashes($user->name) }}')" alt="Приватное сообщение">
                    <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank"
                       class="pnick" title="Информация о персонаже"><b>{{ $user->name }} [{{ $user->player->lvl }}]</b></a>
                    @if($clan)
                        @if($clan->icon)
                            <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}" title="{{ $clan->name }}" alt="{{ $clan->name }}">
                        @else
                            <span class="clan-tag">[{{ $clan->name }}]</span>
                        @endif
                    @endif
                </div>
            @endforeach
        </td>
        <td width="1%" class="lgb-right" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3">
            <img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1">
        </td>
        <td id="td_user_count" width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);">
            <b>Онлайн:</b> <b style="color:#FF0000;" id="chat_user_count">{{ $onlineInGame->count() }}</b>
        </td>
    </tr>
    </tbody>
</table>

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

    function renderLocationUsers(users) {
        var container = document.getElementById('location-users');
        var countEl   = document.getElementById('location-count');
        if (!container) return;

        var online = users.filter(function (u) { return u.is_online; });
        if (countEl) countEl.textContent = online.length;

        var html = '';
        users.forEach(function (u) {
            var offCls = u.is_online ? '' : ' user_offline';
            var opacity = u.is_online ? '' : 'opacity:.6;';

            var clan = '';
            if (u.clan_icon) {
                clan = '<img class="clan-icon" src="' + u.clan_icon + '" title="' + u.clan_name + '" alt="' + u.clan_name + '" style="' + opacity + '">';
            } else if (u.clan_name) {
                clan = '<span class="clan-tag">[' + u.clan_name + ']</span>';
            }

            html += '<div class="info">'
                +   '<span class="' + offCls.trim() + '">'
                +     '<span class="time">' + u.time + '</span> '
                +     '<img src="' + prvArrowSrc + '" class="prv-btn" title="Написать в приват" onclick="sendPrivate(\'' + u.name.replace(/'/g, "\\'") + '\')" alt="Приват"> '
                +     '<a href="' + u.info_url + '" target="_blank" class="pnick' + offCls + '" title="Информация о персонаже"><b>' + u.name + ' [' + u.lvl + ']</b></a>'
                +     clan
                +   '</span>'
                + '</div>';
        });

        container.innerHTML = html;
    }

    window.addEventListener('message', function (e) {
        if (e.data && e.data.type === 'locationUsers') {
            renderLocationUsers(e.data.users);
        }
    });
</script>
</body>
</html>
