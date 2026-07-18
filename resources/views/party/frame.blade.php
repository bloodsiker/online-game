<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Группа</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        .grnn, .grnn * { color: #114d01 !important; }
        html { height: 100%; width: 100%; }
        body { height: 100%; margin: 0; background-color: #ffe4aa; color: #64523A; font-family: Tahoma; font-size: 11px; }
        a { color: #000; text-decoration: none; }
        a:hover { color: #353434; }
        .lgb { background-image: url({{ asset('img/bg/lgb.gif') }}); background-repeat: repeat; }
        .lgb-left { background-image: url({{ asset('img/icon/lgb-left.gif') }}); background-repeat: repeat-y; width: 14px; }
        .lgb-right { background-image: url({{ asset('img/icon/lgb-right.gif') }}); background-repeat: repeat-y; width: 15px; }
        .pnick { font-family: Tahoma; font-size: 11px; font-weight: bold; text-decoration: none; color: #674F3D; cursor: pointer; }
        .redd { color: #BA0000; font-weight: bold; }
        .brdtb { border-top: 1px solid #B28D70; border-bottom: 1px solid #B28D70; }
        .rules { margin-top: 8px; line-height: 1.5; color: #64523A; }
        .member-row { padding: 2px 2px; }
        .clan-icon { width: 13px; height: 13px; vertical-align: middle; margin-right: 3px; }
        .clan-tag { font-size: 11px; color: #5B4736; margin-right: 3px; }
        .kick-link { color: #8a0108; font-weight: bold; cursor: pointer; text-decoration: none; }
        .msg-success { color: #2a7a2a; font-weight: bold; padding: 4px 0; }
        .msg-error { color: #a00000; font-weight: bold; padding: 4px 0; }
        /* Фигурная плашка-заголовок, как на проде */
        .curly_label_c { background: url({{ asset('main/images/tbl-aft_label-center.gif') }}) repeat-x; height: 18px; }
        /* Поле ввода ника, как на проде */
        .party_sel { width: 132px; border: solid 1px #B28D70; background-color: #FFFECE; font-weight: bold; font-size: 11px; }
        form { margin: 0; }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
    <tbody>
    <tr class="lgb" width="100%" height="100%" style="vertical-align: top">
        <td width="14" class="lgb-left" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="14" height="1"><br></td>
        <td valign="top" style="padding: 6px 4px 22px;">

            @if(session('party_success'))
                <div class="msg-success">{{ session('party_success') }}</div>
            @endif
            @if(session('party_error'))
                <div class="msg-error">{{ session('party_error') }}</div>
            @endif
            @error('name')
                <div class="msg-error">{{ $message }}</div>
            @enderror

            @if(!$party)
                {{-- Нет группы: кнопка создания + правила --}}
                <form method="POST" action="{{ route('party.create') }}">
                    @csrf
                    <input type="hidden" name="max_size" value="{{ $maxSize }}">
                    <div align="center" style="width:100%;">
                        <span class="butt1 pointer">
                            <span><input value="Создать группу на {{ $maxSize }} воинов" type="submit" onclick="if(document._submit)return false;document._submit=true;" class="grnn"></span>
                        </span>
                    </div>
                </form>
                <div class="rules">
                    Принимать новых членов в группу может только лидер.<br>
                    Расформировать группу может только лидер группы.<br>
                    Для решения спорных вопросов, таких как распределение вещей, напишите в канал группы команду <b>/rand</b>
                </div>
            @else
                @php $isLeader = $party->isLeader($myUserId); @endphp

                {{-- Список членов группы --}}
                @foreach($party->members as $member)
                    @php
                        $mUser = $member->user;
                        $mLevel = (int) ($mUser->player?->lvl ?? 0);
                        $mIsLeader = $party->isLeader($mUser->id);
                        $mClan = $mUser->clanMembership?->clan;
                        $mClanIcon = $mClan?->icon ? \Illuminate\Support\Facades\Storage::disk('public')->url($mClan->icon) : null;
                    @endphp
                    <div class="member-row">
                        <a href="#" onclick="sendPrivate('{{ addslashes($mUser->name) }}'); return false;" title="Написать в приват"><img src="{{ asset('main/images/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle"></a>@if($mClanIcon) <img class="clan-icon" src="{{ $mClanIcon }}" title="{{ $mClan->name }}" alt="{{ $mClan->name }}">@elseif($mClan) <span class="clan-tag">[{{ $mClan->name }}]</span>@endif &nbsp;<a class="pnick" onclick="whoOpenUserInfo({{ $mUser->id }}); return false;" title="Информация о персонаже"><b>{{ $mUser->name }}&nbsp;[{{ $mLevel }}]</b></a>&nbsp;<a href="#" onclick="whoOpenUserInfo({{ $mUser->id }}); return false;" title="Информация о персонаже"><img src="{{ asset('main/images/player_info.gif') }}" border="0" width="10" height="10" align="absmiddle"></a>@if($mIsLeader)&nbsp;<img src="{{ asset('main/images/party_leader.gif') }}" alt="Лидер группы" title="Лидер группы" width="13" height="13" align="absbottom">@endif
                        @if($isLeader && !$mIsLeader)
                            &nbsp;<form method="POST" action="{{ route('party.kick', ['partyId' => $party->id]) }}" style="display:inline;">
                                @csrf
                                <input type="hidden" name="user_id" value="{{ $mUser->id }}">
                                <a href="#" class="kick-link" title="Выгнать из группы" onclick="if(confirm('Выгнать игрока {{ addslashes($mUser->name) }} из группы?')) this.parentNode.submit(); return false;">[выгнать]</a>
                            </form>
                        @endif
                    </div>
                @endforeach
                <br>

                {{-- Лидер: форма приглашения (как на проде) --}}
                @if($isLeader && $party->members->count() < $party->max_players)
                    <form method="POST" action="{{ route('party.invite') }}">
                        @csrf
                        <input type="hidden" name="party_id" value="{{ $party->id }}">
                        <table class="coll p0 w100" height="18" cellspacing="0" cellpadding="0" border="0"><tr valign="top">
                            <td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" align="absbottom"></td>
                            <td class="curly_label_c b redd" align="center">пригласить в группу</td>
                            <td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" align="absbottom"></td>
                        </tr></table>
                        <center>
                            <table cellspacing="0" cellpadding="0" border="0" style="margin-top: 3px;">
                                <tr>
                                    <td><!-- --></td>
                                    <td rowspan="3" align="right" width="73">
                                        <input type="image"
                                               src="{{ asset('main/images/btn_ok_off.gif') }}" width="70" height="23"
                                               onmouseover="this.src='{{ asset('main/images/btn_ok_on.gif') }}';"
                                               onmouseout="this.src='{{ asset('main/images/btn_ok_off.gif') }}';">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="brdtb" height="24"><input type="text" name="name" value="" class="party_sel" maxlength="50"></td>
                                </tr>
                                <tr>
                                    <td><!-- --></td>
                                </tr>
                            </table>
                        </center>
                    </form>
                @endif

                {{-- Действия --}}
                <div align="center" style="margin-top: 10px;">
                    @if($isLeader)
                        <form method="POST" action="{{ route('party.disband', ['partyId' => $party->id]) }}">
                            @csrf
                            @method('DELETE')
                            <input type="image"
                                   src="{{ asset('main/images/btn_disband_off.gif') }}" width="204" height="23"
                                   onmouseover="this.src='{{ asset('main/images/btn_disband_on.gif') }}';"
                                   onmouseout="this.src='{{ asset('main/images/btn_disband_off.gif') }}';">
                        </form>
                    @else
                        <form method="POST" action="{{ route('party.leave', ['partyId' => $party->id]) }}">
                            @csrf
                            <span class="butt1 pointer">
                                <span><input value="Покинуть группу" type="submit" class="grnn" onclick="return confirm('Покинуть группу?');"></span>
                            </span>
                        </form>
                    @endif
                </div>
            @endif

        </td>
        <td width="15" class="lgb-right" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    @if($party)
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3">
            <img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1">
        </td>
        <td width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);">
            <b>Группа:</b> <b style="color:#FF0000;">{{ $party->members->count() }}/{{ $party->max_players }}</b>
        </td>
    </tr>
    @endif
    </tbody>
</table>

<script>
    // Написать в приват — вставляем ник в поле ввода чата (bottom-frame)
    function sendPrivate(name) {
        try {
            var bottomFrame = window.parent.document.getElementById('bottom-frame');
            if (bottomFrame && bottomFrame.contentWindow) {
                bottomFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
            }
        } catch (e) {}
    }

    // Карточка игрока в отдельном окне
    function whoOpenUserInfo(userId) {
        window.open('{{ url('/info/u') }}/' + userId, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }
</script>
</body>
</html>
