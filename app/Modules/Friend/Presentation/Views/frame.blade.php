<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Друзья</title>
    <style>
        html { height: 100%; width: 100%; }
        body { height: 100%; margin: 0; background-color: #ffe4aa; color: #000; font-family: Tahoma; font-size: 12px; }
        .info { margin-bottom: 2px; color: #554848; }
        .pnick { font-family: Tahoma; font-size: 11px; font-weight: bold; text-decoration: none; color: #674F3D; }
        .time { font-size: 11px; }
        a { color: #000; }
        a:hover { color: #000; }
        .lgb { background-image: url({{ asset('img/bg/lgb.gif') }}); background-repeat: repeat; }
        .lgb-left { background-image: url({{ asset('img/icon/lgb-left.gif') }}); background-repeat: repeat-y; width: 14px; }
        .lgb-right { background-image: url({{ asset('img/icon/lgb-right.gif') }}); background-repeat: repeat-y; width: 15px; }
        .tbl-main_separator-v { background-image: url({{ asset('img/bg/separator_v.gif') }}); background-repeat: repeat-y; width: 3px; }
        .user_offline { color: #B09A8B !important; }
        .clan-icon { width: 13px; height: 13px; vertical-align: middle; margin-left: 2px; }
        .clan-tag { font-size: 11px; color: #5B4736; margin-left: 2px; }
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
            <center><b style="color:green">Друзья: {{ count($frame->friends) }}</b></center><br>
            @forelse($frame->friends as $rel)
                <div class="info">
                    <span class="{{ $rel->isOnline ? '' : 'user_offline' }}">
                        <span class="time">{{ $rel->lastOnlineTime ?? '—' }}</span>
                        <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($rel->userName) }}')" alt="Приват">
                        <a href="{{ route('info.user', ['id' => $rel->userId]) }}" target="_blank" class="pnick {{ $rel->isOnline ? '' : 'user_offline' }}" title="Информация о персонаже"><b>{{ $rel->userName }} [{{ $rel->level }}]</b></a>
                        @if($rel->clanName)
                            @if($rel->clanIconUrl)
                                <img class="clan-icon" src="{{ $rel->clanIconUrl }}" title="{{ $rel->clanName }}" alt="{{ $rel->clanName }}" style="{{ $rel->isOnline ? '' : 'opacity:.6' }}">
                            @else
                                <span class="clan-tag">[{{ $rel->clanName }}]</span>
                            @endif
                        @endif
                    </span>
                </div>
            @empty
                <div class="empty">Список друзей пуст</div>
            @endforelse
        </td>
        <td width="1%" class="lgb-right" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3"><img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1"></td>
        <td width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);"><b>Друзей:</b> <b style="color:#FF0000;">{{ count($frame->friends) }}</b></td>
    </tr>
    </tbody>
</table>
<script>
function sendPrivate(name){try{var bottomFrame=window.parent.document.getElementById('bottom-frame');if(bottomFrame&&bottomFrame.contentWindow){bottomFrame.contentWindow.postMessage({ type:'insertPrivate', name:name }, '*');}}catch(e){console.error('[friends] error:', e);}}
</script>
</body>
</html>
