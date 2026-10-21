@if($clanRequests->isNotEmpty())
    <table id="clan-member-requests" width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:16px;">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody><tr height="22">
                        <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                        <td align="center" class="tbl-usi_label-center">{{ __('clan.requests.heading') }} ({{ $clanRequests->count() }})</td>
                        <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                    </tr></tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding:4px 0;">
                <table class="coll w100 p10h p4v brd2" cellspacing="0" cellpadding="0" style="margin-top:12px;margin-bottom:12px;">
                    <tbody>
                    <tr>
                        <td class="b" align="center" title="{{ __('Ник игрока') }}">{{ __('Ник игрока') }}</td>
                        <td class="b" align="center" title="{{ __('Местоположение игрока') }}">{{ __('Местоположение') }}</td>
                        <td class="b" align="center" title="{{ __('Статус') }}">{{ __('Статус') }}</td>
                        <td class="b clan-requests-actions" align="center" title="{{ __('Доступные действия над заявкой') }}">{{ __('Действия') }}</td>
                    </tr>
                    @foreach($clanRequests as $row)
                        <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                            <td nowrap height="27">
                                @php($requestClan = $row['user']->clanMembership?->clan)
                                <a href="#" title="{{ __('Приватное сообщение') }}" onclick="return clanMemberSendPrivate(@js($row['user']->name));"><img src="{{ asset('img/icon/users-arrow.gif') }}" border="0" width="12" height="10" align="absmiddle"></a>
                                @if($requestClan?->icon)
                                    <a href="{{ route('clan.public', ['clan' => $requestClan->id]) }}" title="{{ $requestClan->name }}" onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ Storage::disk('public')->url($requestClan->icon) }}" alt="{{ $requestClan->name }}" border="0" width="13" height="13" align="absmiddle"></a>&nbsp;
                                @endif
                                <b class="kser0">{{ $row['user']->name }}&nbsp;[{{ $row['user']->player?->lvl ?? '?' }}]</b>
                                <a href="{{ route('info.user', ['id' => $row['user']->id]) }}" title="{{ __('Информация о персонаже') }}" onclick="window.open(this.href, '', 'width=730,height=800,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ asset('main/images/player_info.gif') }}" border="0" width="10" height="10" align="absmiddle"></a>
                            </td>
                            <td align="center">
                                @php($requestLocation = $row['user']->currentLocation)
                                @if($requestLocation?->map?->slug)
                                    <a href="{{ route('map.public', ['slug' => $requestLocation->map->slug]) }}#{{ $requestLocation->id }}" target="_blank" title="{{ __('Открыть карту и расположение игрока') }}"><b>{{ $requestLocation->name }}</b></a>
                                @else
                                    <b>{{ $requestLocation?->name ?? '--' }}</b>
                                @endif
                            </td>
                            <td align="center"><b>{{ $row['status']->label() }}</b></td>
                            <td class="clan-requests-actions" align="center">
                                @if($row['status'] === \App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus::REQUEST && $canInvite)
                                    <form method="post" action="{{ route('clan.application.accept', ['application' => $row['id']]) }}" style="display:inline-block;">
                                        @csrf
                                        <span class="butt1 pointer"><span><input type="submit" class="grnn" value="{{ __('clan.application.accept') }}"></span></span>
                                    </form>
                                    <form method="post" action="{{ route('clan.application.decline', ['application' => $row['id']]) }}" style="display:inline-block;">
                                        @csrf
                                        <span class="butt1 pointer"><span><input type="submit" class="grnn" value="{{ __('clan.application.decline') }}"></span></span>
                                    </form>
                                @elseif($row['status'] === \App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus::INVITE && $canInvite)
                                    <b class="butt2 pointer"><b>
                                        <input value="{{ __('Удалить') }}" type="button" style="width:100px"
                                               onclick="return top.systemConfirm({!! \Illuminate\Support\Js::from(__('Вы действительно хотите отменить запрос на вступление?')) !!},{!! \Illuminate\Support\Js::from(__('Действие')) !!},false,function(){location.href='{{ route('clan.request.cancel', $row['id']) }}'});">
                                    </b></b>
                                @else
                                    —
                                @endif
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
@endif
