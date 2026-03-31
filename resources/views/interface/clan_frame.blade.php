<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клан</title>
    <style>
        html { height: 100%; width: 100%; }
        body {
            height: 100%;
            margin: 0;
            background-color: #ffe4aa;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        .info { margin-bottom: 2px; color: #554848; }
        .pnick {
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            color: #674F3D;
        }
        .time { font-size: 11px; }
        .role { font-size: 10px; color: #7a5c3e; }
        a { color: #000; }
        a:hover { color: #000; }
        .lgb { background-image: url({{ asset('img/bg/lgb.gif') }}); background-repeat: repeat; }
        .lgb-left { background-image: url({{ asset('img/icon/lgb-left.gif') }}); background-repeat: repeat-y; width: 14px; }
        .lgb-right { background-image: url({{ asset('img/icon/lgb-right.gif') }}); background-repeat: repeat-y; width: 15px; }
        .tbl-main_separator-v { background-image: url({{ asset('img/bg/separator_v.gif') }}); background-repeat: repeat-y; width: 3px; }
        .user_offline { color: #B09A8B !important; }
        .prv-btn { cursor: pointer; vertical-align: middle; }
        .empty { padding: 6px; color: #7a6050; }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
    <tbody>
    <tr class="lgb" width="100%" height="100%" style="vertical-align: top">
        <td width="1%" class="lgb-left" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
        <td>
            @if($clan)
                <center><b style="color:green">{{ $clan->name }}: {{ $members->count() }}</b></center>
                <br>

                @foreach($members as $member)
                    @php
                        $user = $member->user;
                        $isOnline = isset($tenMinutesAgo) && $user->last_online_at?->timestamp > $tenMinutesAgo->timestamp;
                    @endphp
                    <div class="info">
                        <span class="{{ $isOnline ? '' : 'user_offline' }}">
                            <span class="time">{{ $user->last_online_at?->format('H:i') }}</span>
                            <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват"
                                 onclick="sendPrivate('{{ addslashes($user->name) }}')" alt="Приват">
                            <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank"
                               class="pnick {{ $isOnline ? '' : 'user_offline' }}"
                               title="Информация о персонаже"><b>{{ $user->name }} [{{ $user->player->lvl }}]</b></a>
                            @if($member->role)
                                <span class="role">({{ $member->role->name }})</span>
                            @endif
                        </span>
                    </div>
                @endforeach
            @else
                <div class="empty">Вы не состоите в клане</div>
            @endif
        </td>
        <td width="1%" class="lgb-right" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3">
            <img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1">
        </td>
        <td width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);">
            <b>Клан:</b> <b style="color:#FF0000;">{{ $members->count() }}</b>
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
            console.error('[clan] error:', e);
        }
    }
</script>
</body>
</html>