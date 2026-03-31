<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Социальный список</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 11px; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        .grnn { color: #114d01 !important; }
        .redd { color: #8b1a0a !important; }

        .tabs-row { display: flex; gap: 2px; padding: 6px 8px 0; }
        .tab-btn {
            padding: 3px 10px; cursor: pointer; font-size: 11px; font-family: Tahoma;
            background: #e8d5b5; border: 1px solid #C49485; border-bottom: none;
            color: #461c0b; font-weight: bold; user-select: none;
        }
        .tab-btn.active { background: #FFFBD6; border-color: #DB9F73; }
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        .add-row { display: flex; align-items: center; gap: 8px; padding: 6px 10px 4px; }
        .add-row label { font-weight: bold; white-space: nowrap; }

        .msg-ok  { background: #e6f4e6; border: 1px solid #5a9a5a; color: #1a5a1a; padding: 4px 10px; margin: 4px 8px; }
        .msg-err { background: #fde8e8; border: 1px solid #c06060; color: #7a1010; padding: 4px 10px; margin: 4px 8px; }

        td.pending-status { color: #8b6a00; font-style: italic; }
        .b-input input { font-size: 11px; padding-top: 4px; }
        .pnick { font-family: Tahoma; font-size: 11px; font-weight: bold; text-decoration: none; color: #674F3D !important; }
        .user_offline { color: #B09A8B !important; }
        .clan-icon { width: 13px; height: 13px; vertical-align: middle; margin-left: 2px; }
        .clan-tag { font-size: 11px; color: #5B4736; margin-left: 2px; }
        .prv-btn { cursor: pointer; vertical-align: middle; }

        table.fr {
            border-top: #db9f73 1px solid;
            border-left: #db9f73 1px solid;
            border-collapse: separate !important;
            width: 100%;
        }
        .fr td, .fr th {
            height: 15px;
            padding: 4px 8px;
            color: #631c0b;
            border-right: #db9f73 1px solid;
            border-bottom: #db9f73 1px solid;
        }
        .fr th { color: #955c4a; font-weight: bold; }

        .online-dot  { display:inline-block; width:7px; height:7px; border-radius:50%; background:#2a9a00; margin-right:3px; vertical-align:middle; }
        .offline-dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:#bbb; margin-right:3px; vertical-align:middle; }
        .last-online { color: #999; font-size: 10px; }
    </style>
</head>
<body class="regblk">

<table class="coll" width="100%" height="100%" border="0">
<tbody><tr>
<td valign="top" width="100%">
<table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
<tbody>
<tr height="22">
    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
    <td class="tbl-shp-sml tt" valign="top" align="center">
        <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
            <td align="center" class="tbl-usi_label-center">Социальный список</td>
            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
        </tr></tbody></table>
    </td>
    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
</tr>
<tr>
    <td class="tbl-shp-sides ls">&nbsp;</td>
    <td class="tbl-usi_bg" valign="top">

        @if(session('success'))
            <div class="msg-ok">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="msg-err">{{ session('error') }}</div>
        @endif

        @php
            $btnL1 = asset('img/bg/btn/btn-left1.gif');
            $btnC1 = asset('img/bg/btn/btn-cent1.gif');
            $btnR1 = asset('img/bg/btn/btn-right1.gif');
            $btnL2 = asset('img/bg/btn/btn-left2.gif');
            $btnC2 = asset('img/bg/btn/btn-cent2.gif');
            $btnR2 = asset('img/bg/btn/btn-right2.gif');
            $tabs = [
                ['id' => 'friends', 'label' => 'Друзья (' . ($friends->count() + $outgoing->count()) . ')', 'active' => true],
                ['id' => 'incoming', 'label' => 'Дружат со мной' . ($incoming->count() > 0 ? ' (' . $incoming->count() . ')' : ''), 'active' => false],
                ['id' => 'enemies', 'label' => 'Враги (' . $enemies->count() . ')', 'active' => false],
                ['id' => 'ignores', 'label' => 'Игнор-лист (' . $ignores->count() . ')', 'active' => false],
            ];
            $onlineThreshold = \Carbon\Carbon::now()->subMinutes(10);
        @endphp
        <table border="0" cellspacing="0" cellpadding="0" style="margin: 6px 8px 0;">
            <tbody>
            <tr height="21">
                @foreach($tabs as $tab)
                    <td width="19"><img class="tab-img-l" data-tab="{{ $tab['id'] }}" src="{{ $tab['active'] ? $btnL2 : $btnL1 }}" width="19" height="21"></td>
                    <td class="tab-img-c" data-tab="{{ $tab['id'] }}" align="center" style="background: url({{ $tab['active'] ? $btnC2 : $btnC1 }}) center top repeat-x; padding: 0 2px 6px; white-space: nowrap;">
                        <a href="#" onclick="showTab('{{ $tab['id'] }}'); return false;" class="tab-link {{ $tab['active'] ? 'btn_2' : 'btn_1' }}" data-tab="{{ $tab['id'] }}">{{ $tab['label'] }}</a>
                    </td>
                    <td width="19"><img class="tab-img-r" data-tab="{{ $tab['id'] }}" src="{{ $tab['active'] ? $btnR2 : $btnR1 }}" width="19" height="21"></td>
                @endforeach
                <td></td>
            </tr>
            </tbody>
        </table>

        {{-- ДРУЗЬЯ --}}
        <div id="tab-friends" class="tab-pane active">
            <div class="add-row">
                <label>Добавить в друзья:</label>
                <form method="post" action="{{ route('friends.add') }}">
                    @csrf
                    <span class="b-input"><span class="b-input__inner">
                        <input type="text" name="name" placeholder="Ник персонажа" maxlength="32" style="width:140px">
                    </span></span>
                    <b class="butt2 pointer"><b>
                        <button type="submit" class="pointer">Отправить запрос</button>
                    </b></b>
                </form>
            </div>
            <table class="fr" cellspacing="0" style="margin-top:4px">
                <thead>
                <tr>
                    <th>Персонаж</th>
                    <th width="70" align="center">Онлайн</th>
                    <th width="120" align="center">Последний раз</th>
                    <th width="100" align="center">Статус</th>
                    <th width="70" align="center">Действия</th>
                </tr>
                </thead>
                <tbody>
                @foreach($friends as $rel)
                    @php $u = $rel->target->user; $clan = $u->clanMembership?->clan; $isOnline = $u->last_online_at && $u->last_online_at > $onlineThreshold; @endphp
                    <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                        <td>
                            <span class="{{ $isOnline ? '' : 'user_offline' }}">
                                <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($u->name) }}')" alt="">
                                <a href="{{ route('info.user', ['id' => $u->id]) }}" target="_blank" class="pnick {{ $isOnline ? '' : 'user_offline' }}" title="Информация о персонаже"><b>{{ $u->name }} [{{ $rel->target->lvl }}]</b></a>
                                @if($clan)
                                    @if($clan->icon)
                                        <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}" title="{{ $clan->name }}" alt="{{ $clan->name }}" style="{{ $isOnline ? '' : 'opacity:.6' }}">
                                    @else
                                        <span class="clan-tag">[{{ $clan->name }}]</span>
                                    @endif
                                @endif
                            </span>
                        </td>
                        <td align="center" class="b">
                            @if($isOnline) <span class="online-dot"></span><span class="grnn">Да</span>
                            @else <span class="offline-dot"></span>Нет @endif
                        </td>
                        <td align="center" class="last-online b">
                            {{ $u->last_online_at ? $u->last_online_at->diffForHumans() : '—' }}
                        </td>
                        <td align="center" class="grnn"><b>друг</b></td>
                        <td align="center">
                            <form method="post" action="{{ route('friends.remove', $rel->id) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <b class="butt2 pointer"><b>
                                    <button type="submit" class="pointer" onclick="return confirm('Удалить из друзей?')">Удалить</button>
                                </b></b>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @foreach($outgoing as $rel)
                    @php $u = $rel->target->user; $clan = $u->clanMembership?->clan; $isOnline = $u->last_online_at && $u->last_online_at > $onlineThreshold; @endphp
                    <tr class="{{ ($loop->index + $friends->count()) % 2 === 0 ? '' : 'bg_l' }}">
                        <td>
                            <span class="{{ $isOnline ? '' : 'user_offline' }}">
                                <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($u->name) }}')" alt="">
                                <a href="{{ route('info.user', ['id' => $u->id]) }}" target="_blank" class="pnick {{ $isOnline ? '' : 'user_offline' }}" title="Информация о персонаже"><b>{{ $u->name }} [{{ $rel->target->lvl }}]</b></a>
                                @if($clan)
                                    @if($clan->icon)
                                        <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}" title="{{ $clan->name }}" alt="{{ $clan->name }}" style="{{ $isOnline ? '' : 'opacity:.6' }}">
                                    @else
                                        <span class="clan-tag">[{{ $clan->name }}]</span>
                                    @endif
                                @endif
                            </span>
                        </td>
                        <td align="center">
                            @if($isOnline) <span class="online-dot"></span><span class="grnn">Да</span>
                            @else <span class="offline-dot"></span>Нет @endif
                        </td>
                        <td align="center" class="last-online">
                            {{ $u->last_online_at ? $u->last_online_at->diffForHumans() : '—' }}
                        </td>
                        <td align="center" class="pending-status">ожидает ответа…</td>
                        <td align="center">
                            <form method="post" action="{{ route('friends.remove', $rel->id) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <b class="butt2 pointer"><b>
                                    <button type="submit" class="pointer" onclick="return confirm('Отменить запрос?')">Отменить</button>
                                </b></b>
                            </form>
                        </td>
                    </tr>
                @endforeach
                @if($friends->isEmpty() && $outgoing->isEmpty())
                    <tr><td colspan="5" align="center" style="color:#999;padding:8px">Список друзей пуст</td></tr>
                @endif
                </tbody>
            </table>
        </div>

        {{-- ДРУЖАТ СО МНОЙ --}}
        <div id="tab-incoming" class="tab-pane">
            <table class="fr" cellspacing="0" style="margin-top:8px">
                <thead>
                <tr>
                    <th>Персонаж</th>
                    <th width="70" align="center">Онлайн</th>
                    <th width="120" align="center">Последний раз</th>
                    <th width="180" align="center">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse($incoming as $rel)
                    @php $u = $rel->player->user; $clan = $u->clanMembership?->clan; $isOnline = $u->last_online_at && $u->last_online_at > $onlineThreshold; @endphp
                    <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                        <td>
                            <span class="{{ $isOnline ? '' : 'user_offline' }}">
                                <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($u->name) }}')" alt="">
                                <a href="{{ route('info.user', ['id' => $u->id]) }}" target="_blank" class="pnick {{ $isOnline ? '' : 'user_offline' }}"><b>{{ $u->name }} [{{ $rel->player->lvl }}]</b></a>
                                @if($clan)
                                    @if($clan->icon)
                                        <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}" title="{{ $clan->name }}" alt="{{ $clan->name }}" style="{{ $isOnline ? '' : 'opacity:.6' }}">
                                    @else
                                        <span class="clan-tag">[{{ $clan->name }}]</span>
                                    @endif
                                @endif
                            </span>
                        </td>
                        <td align="center">
                            @if($isOnline) <span class="online-dot"></span><span class="grnn">Да</span>
                            @else <span class="offline-dot"></span>Нет @endif
                        </td>
                        <td align="center" class="last-online">
                            {{ $u->last_online_at ? $u->last_online_at->diffForHumans() : '—' }}
                        </td>
                        <td align="center">
                            <form method="post" action="{{ route('friends.accept', $rel->id) }}" style="display:inline">
                                @csrf
                                <b class="butt2 pointer"><b>
                                    <button type="submit" class="pointer">Принять</button>
                                </b></b>
                            </form>
                            &nbsp;
                            <form method="post" action="{{ route('friends.decline', $rel->id) }}" style="display:inline">
                                @csrf
                                <b class="butt2 pointer"><b>
                                    <button type="submit" class="pointer">Отклонить</button>
                                </b></b>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" align="center" style="color:#999;padding:8px">Нет входящих запросов</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- ВРАГИ --}}
        <div id="tab-enemies" class="tab-pane">
            <div class="add-row">
                <label>Добавить врага:</label>
                <form method="post" action="{{ route('enemies.add') }}">
                    @csrf
                    <span class="b-input"><span class="b-input__inner">
                        <input type="text" name="name" placeholder="Ник персонажа" maxlength="32" style="width:140px">
                    </span></span>
                    <b class="butt2 pointer"><b>
                        <button type="submit" class="pointer">Добавить</button>
                    </b></b>
                </form>
            </div>
            <table class="fr" cellspacing="0" style="margin-top:4px">
                <thead>
                <tr>
                    <th>Персонаж</th>
                    <th width="70" align="center">Онлайн</th>
                    <th width="120" align="center">Последний раз</th>
                    <th width="70" align="center">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse($enemies as $rel)
                    @php $u = $rel->target->user; $clan = $u->clanMembership?->clan; $isOnline = $u->last_online_at && $u->last_online_at > $onlineThreshold; @endphp
                    <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                        <td>
                            <span class="{{ $isOnline ? '' : 'user_offline' }}">
                                <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($u->name) }}')" alt="">
                                <a href="{{ route('info.user', ['id' => $u->id]) }}" target="_blank" class="pnick {{ $isOnline ? '' : 'user_offline' }}"><b>{{ $u->name }} [{{ $rel->target->lvl }}]</b></a>
                                @if($clan)
                                    @if($clan->icon)
                                        <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}" title="{{ $clan->name }}" alt="{{ $clan->name }}" style="{{ $isOnline ? '' : 'opacity:.6' }}">
                                    @else
                                        <span class="clan-tag">[{{ $clan->name }}]</span>
                                    @endif
                                @endif
                            </span>
                        </td>
                        <td align="center">
                            @if($isOnline) <span class="online-dot"></span><span class="grnn">Да</span>
                            @else <span class="offline-dot"></span>Нет @endif
                        </td>
                        <td align="center" class="last-online">
                            {{ $u->last_online_at ? $u->last_online_at->diffForHumans() : '—' }}
                        </td>
                        <td align="center">
                            <form method="post" action="{{ route('enemies.remove', $rel->id) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <b class="butt2 pointer"><b>
                                    <button type="submit" class="pointer" onclick="return confirm('Убрать из врагов?')">Удалить</button>
                                </b></b>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" align="center" style="color:#999;padding:8px">Список врагов пуст</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- ИГНОР-ЛИСТ --}}
        <div id="tab-ignores" class="tab-pane">
            <div class="add-row">
                <label>Добавить в игнор:</label>
                <form method="post" action="{{ route('ignores.add') }}">
                    @csrf
                    <span class="b-input"><span class="b-input__inner">
                        <input type="text" name="name" placeholder="Ник персонажа" maxlength="32" style="width:140px">
                    </span></span>
                    <b class="butt2 pointer"><b>
                        <button type="submit" class="pointer">Игнорировать</button>
                    </b></b>
                </form>
            </div>
            <table class="fr" cellspacing="0" style="margin-top:4px">
                <thead>
                <tr>
                    <th>Персонаж</th>
                    <th width="70" align="center">Онлайн</th>
                    <th width="120" align="center">Последний раз</th>
                    <th width="70" align="center">Действия</th>
                </tr>
                </thead>
                <tbody>
                @forelse($ignores as $rel)
                    @php $u = $rel->target->user; $clan = $u->clanMembership?->clan; $isOnline = $u->last_online_at && $u->last_online_at > $onlineThreshold; @endphp
                    <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                        <td>
                            <span class="{{ $isOnline ? '' : 'user_offline' }}">
                                <img src="{{ asset('img/icon/users-arrow.gif') }}" class="prv-btn" title="Написать в приват" onclick="sendPrivate('{{ addslashes($u->name) }}')" alt="">
                                <a href="{{ route('info.user', ['id' => $u->id]) }}" target="_blank" class="pnick {{ $isOnline ? '' : 'user_offline' }}"><b>{{ $u->name }} [{{ $rel->target->lvl }}]</b></a>
                                @if($clan)
                                    @if($clan->icon)
                                        <img class="clan-icon" src="{{ Storage::disk('public')->url($clan->icon) }}" title="{{ $clan->name }}" alt="{{ $clan->name }}" style="{{ $isOnline ? '' : 'opacity:.6' }}">
                                    @else
                                        <span class="clan-tag">[{{ $clan->name }}]</span>
                                    @endif
                                @endif
                            </span>
                        </td>
                        <td align="center">
                            @if($isOnline) <span class="online-dot"></span><span class="grnn">Да</span>
                            @else <span class="offline-dot"></span>Нет @endif
                        </td>
                        <td align="center" class="last-online">
                            {{ $u->last_online_at ? $u->last_online_at->diffForHumans() : '—' }}
                        </td>
                        <td align="center">
                            <form method="post" action="{{ route('ignores.remove', $rel->id) }}" style="display:inline">
                                @csrf @method('DELETE')
                                <b class="butt2 pointer"><b>
                                    <button type="submit" class="pointer" onclick="return confirm('Убрать из игнора?')">Удалить</button>
                                </b></b>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" align="center" style="color:#999;padding:8px">Игнор-лист пуст</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </td>
    <td class="tbl-shp-sides rs">&nbsp;</td>
</tr>
<tr height="10">
    <td class="tbl-shp-sml lb"><b></b></td>
    <td class="tbl-shp-sml bb"></td>
    <td class="tbl-shp-sml rb"><b></b></td>
</tr>
</tbody></table>
</td>
</tr></tbody></table>

<script>
var btnL1 = '{{ asset('img/bg/btn/btn-left1.gif') }}';
var btnC1 = '{{ asset('img/bg/btn/btn-cent1.gif') }}';
var btnR1 = '{{ asset('img/bg/btn/btn-right1.gif') }}';
var btnL2 = '{{ asset('img/bg/btn/btn-left2.gif') }}';
var btnC2 = '{{ asset('img/bg/btn/btn-cent2.gif') }}';
var btnR2 = '{{ asset('img/bg/btn/btn-right2.gif') }}';

function sendPrivate(name) {
    try {
        var chatFrame = window.parent.document.getElementById('chat-frame');
        if (!chatFrame) return;
        var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
        if (bottomFrame && bottomFrame.contentWindow) {
            bottomFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
        }
    } catch (e) {
        console.error('[friends] error:', e);
    }
}

function showTab(name) {
    document.querySelectorAll('.tab-pane').forEach(el => el.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');

    document.querySelectorAll('.tab-img-l').forEach(function(img) {
        var active = img.dataset.tab === name;
        img.src = active ? btnL2 : btnL1;
    });
    document.querySelectorAll('.tab-img-r').forEach(function(img) {
        var active = img.dataset.tab === name;
        img.src = active ? btnR2 : btnR1;
    });
    document.querySelectorAll('.tab-img-c').forEach(function(td) {
        var active = td.dataset.tab === name;
        td.style.backgroundImage = 'url(' + (active ? btnC2 : btnC1) + ')';
    });
    document.querySelectorAll('.tab-link').forEach(function(a) {
        var active = a.dataset.tab === name;
        a.className = 'tab-link ' + (active ? 'btn_2' : 'btn_1');
    });
}
</script>
</body>
</html>
