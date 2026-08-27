<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клан</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 11px;
        }
        a, a:link, a:visited, a:active {
            text-decoration: none;
        }
        .p10h, .p10h td {
            padding-left: 10px;
            padding-right: 10px;
        }
        .p4v, .p4v td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd, .brd td {
            border: 1px solid #C49485;
        }
        .brd2-all {
            border: 1px solid #DB9F73;
        }
        .bg_l {
            background-image: url(/img/bg/info/bg_l.gif);
        }
        .bg_l2 {
            background-image: url(/img/bg/info/bg_l2.gif);
            cursor: pointer;
        }
        .brd2, .brd2 td {
            border: 1px solid #DB9F73;
        }
        .dbgl2 {
            background-color: #FFFBD6;
        }
        .grnn {
            color: #114d01 !important;
        }

    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.member'])

<table class="coll" width="100%" height="100%" border="0">
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27">
                                    <img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22">
                                </td>
                                <td align="center" class="tbl-usi_label-center">Состав клана</td>
                                <td width="27"
                                ><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">

                        @php
                            $memberCount = $rows->where('type', 'member')->count();
                            $canPromote  = $membership->role->hasPermission(\App\Modules\Clan\Domain\Enums\ClanPermission::CHANGE_RANKS);
                        @endphp

                        <table class="coll w100 p10h p2v brd2-all">
                            <tbody>
                            <tr class="bg_l">
                                <td align="left" nowrap=""><b>Игроков в клане:</b>&nbsp;&nbsp;&nbsp;
                                    <b>всего:&nbsp;<b class="redd">{{ $memberCount }}</b> из <b class="redd">40</b> / </b>
                                    <b>online:&nbsp;<b class="redd">{{ $onlineCount }}</b> / </b>
                                    <b>опыт клана:&nbsp;<b class="redd">{{ $clan->experience }}</b></b>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <form id="form_kick_member" method="post" action="" style="display:none">
                            @csrf
                            @method('DELETE')
                        </form>

                        <form method="post" action="{{ route('clan.member.save-roles') }}" id="form_check_leader_change">
                            @csrf
                            <table class="coll w100 p10h p4v brd2" style="margin-top:12px;margin-bottom:12px">
                                <tbody>
                                <tr>
                                    <td class="b" align="center" title="Ник члена клана">Ник игрока</td>
                                    <td class="b" align="center" width="185" title="Звание игрока в вашем клане">Звание в клане</td>
                                    <td class="b" align="center" title="Местоположение игрока">Местоположение</td>
                                    <td class="b" align="center" title="Состояние">Состояние</td>
                                    <td class="b" align="center" title="Дата и время последнего входа в игру">Последний вход</td>
                                    <td class="b" align="center" title="Статус игрока (Online/Offline)">Статус</td>
                                    <td class="b" align="center" title="Опыт, внесённый игроком в развитие клана">Вклад опыта</td>
                                    <td class="b" align="center" title="Бонусные очки, заработанные для клана">Бонусы</td>
                                    <td class="b" align="center" title="Доступные действия над членом клана">Действия</td>
                                </tr>

                                @foreach($rows as $row)
                                    @if($row['type'] === 'member')
                                        <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                                            <td width="10%" nowrap="">
                                                <a href="#" title="Приватное сообщение">
                                                    <img src="{{ asset('img/icon/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle">
                                                </a>
                                                @if($clan->icon)<a href="{{ route('clan.public', ['clan' => $clan->id]) }}" title="Открыть публичную информацию о клане" onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ Storage::disk('public')->url($clan->icon) }}" alt="logo" border="0" width="13" height="13" align="absmiddle"></a>&nbsp;@endif
                                                <b class="kser0">{{ $row['user']->name }}&nbsp;[{{ $row['user']->player->lvl }}]</b>
                                                &nbsp;
                                            </td>
                                            <td nowrap="" height="27" width="165">
                                                @if($canPromote)
                                                    <select name="form[mem][{{ $row['user']->id }}][grade]" class="dbgl2 b small" style="width:165px" data-original="{{ $row['role']->id }}" onchange="leader_change(this);">
                                                        @foreach($allRoles as $role)
                                                            <option value="{{ $role->id }}" {{ $row['role']->id === $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <b>{{ $row['role']->name }}</b>
                                                @endif
                                            </td>
                                            <td width="26%" align="center">
                                                @php($location = $row['user']->currentLocation)
                                                @if($location?->map?->slug)
                                                    <a href="{{ route('map.public', ['slug' => $location->map->slug]) }}#{{ $location->id }}" target="_blank" title="Открыть карту и расположение игрока"><b>{{ $location->name }}</b></a>
                                                @else
                                                    <b>{{ $location?->name ?? '--' }}</b>
                                                @endif
                                            </td>
                                            <td width="26%" align="center"><b>принят в клан</b></td>
                                            <td width="16%" align="center">
                                                <b>{{ $row['user']->last_online_at?->format('j M Y H:i') ?? '--' }}</b>
                                            </td>
                                            <td width="18%" align="center">
                                                @if($row['is_online'])
                                                    <b><span class="grnn">online</span></b>
                                                @else
                                                    <b>offline</b>
                                                @endif
                                            </td>
                                            <td align="center" nowrap="">
                                                <b class="grnn">{{ $row['membership']->experience_contributed }}</b>
                                            </td>
                                            <td align="center" nowrap="">
                                                <b class="grnn">{{ $row['membership']->points }}</b>
                                            </td>
                                            <td align="right" nowrap="">
                                                @if($row['user']->id === auth()->id() && !$row['role']->is_leader)
                                                    <b class="butt2 pointer"><b>
                                                        <input value="Выйти" type="button" style="width:60px"
                                                               onclick="return top.systemConfirm('Вы действительно хотите покинуть клан?','Действие',false,function(){ document.getElementById('form_leave_clan').submit() });">
                                                    </b></b>
                                                @elseif($canKick && !$row['role']->is_leader && $row['user']->id !== auth()->id())
                                                    <b class="butt2 pointer"><b>
                                                        <input value="Исключить" type="button" style="width:60px"
                                                               onclick="return top.systemConfirm('Вы действительно хотите исключить игрока?','Действие',false,function(){ submitKickMember({{ $row['user']->id }}) });">
                                                    </b></b>
                                                @endif
                                            </td>
                                        </tr>
                                    @else
                                        <tr class="bg_l">
                                            <td width="10%" nowrap="">
                                                <a href="#" title="Приватное сообщение">
                                                    <img src="{{ asset('img/icon/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle">
                                                </a>
                                                <b class="kser0">{{ $row['user']->name }}&nbsp;[{{ $row['user']->player->lvl }}]</b>
                                                &nbsp;
                                            </td>
                                            <td nowrap="" height="27" width="165">
                                                <b>--</b>
                                            </td>
                                            <td width="26%" align="center"><b>--</b></td>
                                            <td width="26%" align="center"><b>{{ $row['status']->label() }}</b></td>
                                            <td width="16%" align="center"><b>--</b></td>
                                            <td width="18%" align="center"><b>--</b></td>
                                            <td align="center"><b>--</b></td>
                                            <td align="center"><b>--</b></td>
                                            <td align="right">
                                                <b class="butt2 pointer"><b>
                                                    <input value="Удалить" type="button" style="width:100px"
                                                           onclick="return top.systemConfirm('Вы действительно хотите отменить запрос на вступление?','Действие',false,function(){location.href='{{ route('clan.request.cancel', $row['id']) }}'});">
                                                </b></b>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                                </tbody>
                            </table>

                            @if($canPromote)
                                <script type="text/javascript">
                                    var leaderRoleId = {{ $leaderRole?->id ?? 0 }};

                                    function leader_change(obj) {}

                                    function check_leader_change() {
                                        if (!leaderRoleId) return true;

                                        var selects = document.querySelectorAll('select.dbgl2');
                                        var hasLeaderTransfer = false;
                                        selects.forEach(function(s) {
                                            if (parseInt(s.value) === leaderRoleId && parseInt(s.dataset.original) !== leaderRoleId) {
                                                hasLeaderTransfer = true;
                                            }
                                        });

                                        if (!hasLeaderTransfer) return true;

                                        return top.systemConfirm(
                                            'Передать руководство',
                                            '<span>Вы уверены, что хотите передать руководство кланом?</span><br><br>После подтверждения вы потеряете права главы клана.',
                                            document.getElementById('form_check_leader_change')
                                        );
                                    }
                                </script>
                                <div align="center">
                                    <span class="butt1 pointer">
                                        <span>
                                            <input value="Сохранить изменения" type="submit"
                                                   onclick="return check_leader_change();"
                                                   class="grnn"
                                                   style="width:160px;">
                                        </span>
                                    </span>
                                </div>
                            @endif
                        </form>


                        @if($canInvite)
                            <form method="post" action="{{ route('clan.invite') }}">
                                @csrf
                                <table cellspacing="0" cellpadding="5" border="0">
                                    <tbody><tr>
                                        <td valign="middle">Ник игрока</td>
                                        <td valign="middle" width="100"><input type="text" name="invite_nick" value="{{ old('invite_nick') }}" class="w100 dbgl2 brd b small"></td>
                                        <td valign="middle"><b class="butt2 pointer"><b><input value="Пригласить в клан" type="submit" style="width:130px"></b></b></td>
                                    </tr></tbody>
                                </table>
                            </form>
                        @endif

                        @if(!$membership->role->is_leader)
                            <form method="post" action="{{ route('clan.member.leave') }}" id="form_leave_clan">
                                @csrf
                            </form>
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

    </tr>
    </tbody>
</table>

<script>
    function submitKickMember(userId) {
        var form = document.getElementById('form_kick_member');
        form.action = '{{ url('clan/member') }}/' + userId;
        form.submit();
    }


    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
