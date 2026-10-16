<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('clan.directory.title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; margin: 0; color: #000; font-family: Tahoma, sans-serif; font-size: 11px; }
        .clan-directory { padding: 12px 10px 20px; }
        .clan-directory__title { margin: 8px 0 14px; color: #6f1d0b; font-size: 16px; text-align: center; }
        .clan-directory__flash { margin: 8px auto 12px; color: #8b0000; font-weight: bold; text-align: center; }
        .clan-directory table { border-collapse: collapse; border-spacing: 0; }
        .clan-directory__table { width: 100%; border-top: 1px solid #db9f73; border-left: 1px solid #db9f73; }
        .clan-directory__table th, .clan-directory__table td { padding: 7px 6px; border-right: 1px solid #db9f73; border-bottom: 1px solid #db9f73; text-align: center; }
        .clan-directory__table th { color: #955c4a; font-weight: bold; }
        .clan-directory__table tr:nth-child(even) { background: url({{ asset('img/bg/info/bg_l.gif') }}); }
        .clan-directory__identity { text-align: left !important; white-space: nowrap; }
        .clan-directory__identity img { margin-right: 5px; vertical-align: middle; }
        .clan-directory__leader { white-space: nowrap; }
        .clan-directory__leader-message { width: 12px; height: 12px; margin-right: 3px !important; cursor: pointer; }
        .clan-directory__identity a { color: #8b0000; font-weight: bold; text-decoration: none; }
        .clan-directory__identity a:hover { text-decoration: underline; }
        .clan-directory__action { min-width: 130px; white-space: nowrap; }
        .clan-directory__empty { padding: 20px !important; color: #806c57; text-align: center !important; }
        .clan-directory__footer { display: flex; align-items: center; justify-content: center; gap: 12px; margin-top: 14px; }
        .clan-directory__pagination { text-align: center; }
        .clan-directory__pagination nav > div:first-child { display: none; }
        .clan-directory__pagination a, .clan-directory__pagination span { margin: 0 3px; color: #7b2d13; }
        @media (max-width: 760px) {
            .clan-directory { overflow-x: auto; }
            .clan-directory__table { min-width: 760px; }
            .clan-directory__footer { min-width: 760px; }
        }
    </style>
</head>
<body class="regblk">
<div class="clan-directory">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
                    <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                    <td align="center" class="tbl-usi_label-center">{{ __('clan.directory.title') }}</td>
                    <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
                </tr></tbody></table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px 14px;">
                @if(session('message'))
                    <div class="clan-directory__flash">{{ session('message') }}</div>
                @endif

                <table class="clan-directory__table" cellspacing="0" cellpadding="0">
                    <thead><tr>
                        <th>{{ __('clan.directory.rank') }}</th>
                        <th>{{ __('clan.directory.clan') }}</th>
                        <th>{{ __('clan.directory.level') }}</th>
                        <th>{{ __('clan.directory.members') }}</th>
                        <th>{{ __('clan.directory.experience') }}</th>
                        <th>{{ __('clan.directory.leader') }}</th>
                        <th>{{ __('clan.directory.actions') }}</th>
                    </tr></thead>
                    <tbody>
                    @forelse($clans as $clan)
                        @php($application = $applications->get($clan->id))
                        <tr>
                            <td>{{ $clans->firstItem() + $loop->index }}</td>
                            <td class="clan-directory__identity">
                                @if($clan->icon)
                                    <a href="{{ route('clan.public', ['clan' => $clan->id]) }}" onclick="window.open(this.href,'','width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">
                                        <img src="{{ Storage::disk('public')->url($clan->icon) }}" width="20" height="20" alt="">
                                    </a>
                                @endif
                                <a href="{{ route('clan.public', ['clan' => $clan->id]) }}" onclick="window.open(this.href,'','width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $clan->name }}</a>
                            </td>
                            <td>{{ $clan->lvl }}</td>
                            <td>{{ $clan->members_count }} / 40</td>
                            <td>{{ number_format((float) $clan->experience, 0, '.', ' ') }}</td>
                            <td class="clan-directory__leader">
                                @if($clan->owner)
                                    <img class="clan-directory__leader-message" src="{{ asset('img/icon/users-arrow.gif') }}" alt="{{ __('Написать в приват') }}" title="{{ __('Написать в приват') }}" onclick="clanDirectorySendPrivate(@js($clan->owner->name))">
                                    <b>{{ $clan->owner->name }} [{{ $clan->owner->lvl }}]</b>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="clan-directory__action">
                                @if($application?->status === \App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus::REQUEST)
                                    <form method="post" action="{{ route('clan.application.withdraw', ['application' => $application->id]) }}">
                                        @csrf
                                        <span class="butt1 pointer"><span><input type="submit" class="grnn" value="{{ __('clan.directory.withdraw') }}"></span></span>
                                    </form>
                                @elseif($application?->status === \App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus::INVITE)
                                    <span>{{ __('clan.directory.invited') }}</span>
                                @elseif($applications->isNotEmpty())
                                    <span>{{ __('clan.directory.pending_elsewhere') }}</span>
                                @elseif($clan->members_count >= 40)
                                    <span>{{ __('clan.directory.full') }}</span>
                                @else
                                    <form method="post" action="{{ route('clan.application.apply', ['clan' => $clan->id]) }}">
                                        @csrf
                                        <span class="butt1 pointer"><span><input type="submit" class="grnn" value="{{ __('clan.directory.apply') }}"></span></span>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="clan-directory__empty">{{ __('clan.directory.empty') }}</td></tr>
                    @endforelse
                    </tbody>
                </table>

                <div class="clan-directory__footer">
                    <div class="clan-directory__pagination">{{ $clans->links() }}</div>
                </div>
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
</div>
<script>
    function clanDirectorySendPrivate(name) {
        try {
            var chatFrame = window.parent.document.getElementById('chat-frame');
            var bottomFrame = chatFrame && chatFrame.contentDocument.getElementById('bottom-frame');
            if (bottomFrame && bottomFrame.contentWindow) {
                bottomFrame.contentWindow.postMessage({ type: 'insertPrivate', name: name }, '*');
            }
        } catch (error) {
            console.error('[clan-directory] private message target unavailable', error);
        }
    }
</script>
</body>
</html>
