<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Информация о клане: {{ $clan->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, sans-serif; font-size: 11px; }
        html, body { min-height: 100%; }
        body { margin: 0; background-color: #a0a0a0; background-image: url('{{ asset('main/images/bg2.gif') }}'); }
        body, body * { color: #955c4a; }
        a, a:link, a:visited, a:active { color: #ba0000; text-decoration: none; }
        a:hover { color: #ba0000; text-decoration: underline; }
        img { vertical-align: middle; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .w100 { width: 100%; }
        .b { font-weight: bold; }
        .redd, .redd * { color: #ba0000 !important; }
        .bg_l { background-image: url('{{ asset('main/images/bg_l.gif') }}'); }
        .p6v, .p6v td { padding-top: 6px; padding-bottom: 6px; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .brd2-all { border: 1px solid #db9f73; }
        .brd2-top { border-top: 1px solid #db9f73; }
        .brd2-bt { border-bottom: 1px solid #db9f73; }
        .brd2-l { border-left: 1px solid #db9f73; }
        .brd2-r { border-right: 1px solid #db9f73; }

        .clan-public-shell { width: 610px; height: 10%; margin: 24px auto 0; }

        .common-block { position: relative; }
        .common-block .common-content { position: relative; z-index: 2; }
        .common-block .corner-tl { position: absolute; top: -19px; left: -23px; width: 141px; height: 176px; background: url('{{ asset('main/images/common-block-red-tl.png') }}') no-repeat; }
        .common-block .corner-tr { position: absolute; top: -19px; right: -24px; width: 146px; height: 176px; background: url('{{ asset('main/images/common-block-red-tr.png') }}') no-repeat; }
        .common-block .corner-bl { position: absolute; bottom: -19px; left: -19px; width: 238px; height: 127px; background: url('{{ asset('main/images/common-block-red-bl.png') }}') no-repeat; }
        .common-block .corner-br { position: absolute; bottom: -19px; right: -21px; width: 241px; height: 128px; background: url('{{ asset('main/images/common-block-red-br.png') }}') no-repeat; }
        .common-block .bg-t { height: 41px; margin: 0 39px; text-align: center; background: url('{{ asset('main/images/common-block-t.png') }}') 0 100% repeat-x; }
        .common-block .bg-b { height: 41px; margin: 0 39px; background: url('{{ asset('main/images/common-block-b.png') }}') repeat-x; }
        .common-block .bg-l { background: url('{{ asset('main/images/common-block-l.png') }}') repeat-y; }
        .common-block .bg-r { padding: 0 39px; background: url('{{ asset('main/images/common-block-r.png') }}') 100% 0 repeat-y; }
        .common-block .bg-inner { background: url('{{ asset('main/images/bgg2.gif') }}') repeat; }
        .common-block .bg-inner-l { background: url('{{ asset('main/images/common-block-inner-red-l.png') }}') repeat-y; }
        .common-block .bg-inner-r { background: url('{{ asset('main/images/common-block-inner-red-r.png') }}') 100% 0 repeat-y; }
        .common-block .bg-inner-t { margin: 0 12px; background: url('{{ asset('main/images/common-block-inner-t.png') }}') repeat-x; }
        .common-block .bg-inner-b { padding: 20px 18px; background: url('{{ asset('main/images/common-block-inner-b.png') }}') 0 100% repeat-x; }

        .common-header, .common-header .h-inner, .common-header .h-txt { display: inline-block; }
        .common-header__small { position: relative; top: 11px; z-index: 1; height: 39px; padding-left: 87px; background: url('{{ asset('main/images/common-header-small.png') }}') no-repeat; }
        .common-header__small .h-inner { height: 39px; padding: 0 97px 0 10px; background: url('{{ asset('main/images/common-header-small.png') }}') 100% -39px no-repeat; }
        .common-header__small .h-txt { padding-top: 10px; color: #faf7b9; font-weight: bold; font-size: 11px; text-align: center; }

        .tbl-shp_sml-top { height: 22px; background: url('{{ asset('main/images/tbl-shp_sml-top.gif') }}') repeat-x; font-size: 1px; }
        .tbl-shp_sml-bottom { height: 18px; background: url('{{ asset('main/images/tbl-shp_sml-bottom.gif') }}') repeat-x; font-size: 1px; }
        .tbl-usi_left { width: 20px; background: url('{{ asset('main/images/tbl-usi_left.gif') }}') right top repeat-y; }
        .tbl-usi_right { width: 20px; background: url('{{ asset('main/images/tbl-usi_right.gif') }}') repeat-y; }
        .tbl-usi_bg { background: url('{{ asset('main/images/tbl-usi_bg.gif') }}') repeat; }

        .btn_1 { color: #461c0b !important; font-weight: bold; }
        .btn_2 { color: #ffe9ba !important; font-weight: bold; }
        .clan-tabs { position: relative; top: -2px; }
        .clan-tabs a { white-space: nowrap; }

        .section-title { width: 100%; height: 18px; margin: 7px 0 3px; border-collapse: collapse; border-spacing: 0; }
        .section-title img { display: block; }
        .section-title__center { height: 18px; color: #ba0000; font-weight: bold; background: url('{{ asset('main/images/tbl-aft_label-center.gif') }}') repeat-x; }

        table.user-rating { width: 100%; border-top: 1px solid #db9f73; border-left: 1px solid #db9f73; border-collapse: separate; border-spacing: 0; text-align: center; }
        .user-rating td, .user-rating th { height: 15px; padding: 4px; color: #631c0b; border-right: 1px solid #db9f73; border-bottom: 1px solid #db9f73; }
        .user-rating thead td { color: #955c4a; font-weight: normal; }
        .user-rating-container { display: inline-flex; align-items: center; }
        .user-rating-container img { margin-right: 4px; }
        .clan-user-link { color: #004f91 !important; font-weight: bold; }
        .clan-member-online { color: #114d01 !important; }
        .clan-description { line-height: 1.4; white-space: pre-line; }
        .clan-empty { padding: 12px !important; text-align: center; }
        .clan-members, .clan-history { width: 100%; border-top: 1px solid #db9f73; border-left: 1px solid #db9f73; border-collapse: separate; border-spacing: 0; }
        .clan-members td, .clan-members th, .clan-history td, .clan-history th { padding: 4px; border-right: 1px solid #db9f73; border-bottom: 1px solid #db9f73; }
        .clan-members th, .clan-history th { color: #955c4a; font-weight: normal; text-align: center; }
        .pagination { padding: 8px 0 2px; text-align: center; }
        .pagination a, .pagination span { margin: 0 2px; }
    </style>
</head>
<body class="bg2 regcolor">
@php
    $tabs = [
        'information' => 'Информация',
        'members' => 'Состав',
        'history' => 'История',
    ];
    $publicUrl = route('clan.public', ['clan' => $clan->id]);
    $iconUrl = $clan->icon ? Storage::disk('public')->url($clan->icon) : null;
@endphp

<table width="100%" height="100%" cellpadding="0" cellspacing="0" border="0">
    <tbody><tr><td align="center" valign="top">
        <table class="clan-public-shell" border="0" cellspacing="0" cellpadding="0" align="center">
            <tbody>
            <tr><td>
                <div class="common-block common-block__red">
                    <div class="corner-tl"></div><div class="corner-tr"></div><div class="corner-bl"></div><div class="corner-br"></div>
                    <div class="bg-t"><div class="common-header common-header__small"><div class="h-inner"><div class="h-txt">{{ $clan->name }}</div></div></div></div>
                    <div class="bg-l"><div class="bg-r"><div class="bg-inner"><div class="bg-inner-l"><div class="bg-inner-r"><div class="bg-inner-t"><div class="bg-inner-b"><div class="common-content">
                        <table width="500" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt=""></td>
                                <td class="tbl-shp_sml-top" valign="top" align="center">
                                    <table class="clan-tabs" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="21">
                                    @foreach($tabs as $tabMode => $tabLabel)
                                        @php
                                            $active = $mode === $tabMode;
                                            $suffix = $active ? '2' : '1';
                                            $queryMode = $tabMode === 'information' ? 'news' : $tabMode;
                                            $tabUrl = $publicUrl.'?mode='.$queryMode;
                                        @endphp
                                        <td width="19" valign="top"><img src="{{ asset('main/images/btn-left'.$suffix.'.png') }}" width="19" height="21" alt=""></td>
                                        <td align="center" style="background: url('{{ asset('main/images/btn-cent'.$suffix.'.png') }}') repeat-x top; padding: 0 4px 3px;"><a href="{{ $tabUrl }}" class="btn_{{ $suffix }}">{{ $tabLabel }}</a></td>
                                        <td width="19" valign="top"><img src="{{ asset('main/images/btn-right'.$suffix.'.png') }}" width="19" height="21" alt=""></td>
                                    @endforeach
                                    </tr></tbody></table>
                                </td>
                                <td width="20" align="left" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt=""></td>
                            </tr>
                            <tr>
                                <td class="tbl-usi_left">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" align="left" style="padding: 0 0 3px;">
                                    @if($mode === 'information')
                                        <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">Глава</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                        <table class="coll w100 p6v p10h brd2-all"><tbody><tr class="bg_l"><td align="center">
                                            @if($iconUrl)<img src="{{ $iconUrl }}" width="13" height="13" alt="{{ $clan->name }}">&nbsp;@endif
                                            @if($clan->owner)
                                                <a href="{{ route('info.user', ['id' => $clan->owner->id]) }}" class="clan-user-link" onclick="window.open(this.href, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $clan->owner->name }}&nbsp;[{{ $clan->owner->player?->lvl ?? '?' }}]</a>&nbsp;<a href="{{ route('info.user', ['id' => $clan->owner->id]) }}" title="Информация о персонаже" onclick="window.open(this.href, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" border="0" alt=""></a>
                                            @else
                                                Не назначен
                                            @endif
                                        </td></tr></tbody></table>

                                        <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">Статус</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                        <table class="coll w100 p6v p10h brd2-all"><tbody><tr class="bg_l"><td align="center">
                                            @if($iconUrl)<img src="{{ $iconUrl }}" width="13" height="13" alt="{{ $clan->name }}">&nbsp;@endif
                                            <b>{{ $clan->name }} [{{ $clan->lvl }}]</b>&nbsp;·&nbsp;{{ $membersCount }} {{ trans_choice('участник|участника|участников', $membersCount) }}
                                        </td></tr></tbody></table>

                                        <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">Рейтинг клана</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                        <table class="user-rating"><thead><tr><td>Название рейтинга</td><td>Значение в рейтинге</td><td>Место в глобальном рейтинге</td></tr></thead><tbody>
                                            <tr class="bg_l"><td><span class="user-rating-container"><img src="{{ asset('main/images/data/rating/rat_honor.png') }}" alt="">Уровень клана</span></td><td>{{ $clan->lvl }}</td><td>{{ $levelRank }}-е место</td></tr>
                                            <tr><td><span class="user-rating-container"><img src="{{ asset('main/images/data/rating/rat_exp.png') }}" alt="">Опыт</span></td><td>{{ number_format((float) $clan->experience, 2, '.', ' ') }}</td><td>{{ $experienceRank }}-е место</td></tr>
                                            <tr class="bg_l"><td><span class="user-rating-container"><img src="{{ asset('main/images/data/rating/rat_rep.png') }}" alt="">Бонусные очки</span></td><td>{{ number_format((int) $clan->points, 0, '.', ' ') }}</td><td>—</td></tr>
                                        </tbody></table>

                                        <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">Описание клана</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                        <table class="coll w100 p6v p10h brd2-all"><tbody><tr class="bg_l"><td class="clan-description">{{ $clan->description ?: 'Описание клана ещё не добавлено.' }}</td></tr></tbody></table>

                                        @php
                                            $news = collect([$clan->news_1, $clan->news_2, $clan->news_3])->filter();
                                        @endphp
                                        @if($news->isNotEmpty())
                                            <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">Новости клана</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                            <table class="coll w100 p6v p10h brd2-all"><tbody>@foreach($news as $newsItem)<tr class="{{ $loop->odd ? 'bg_l' : '' }}"><td class="clan-description">{{ $newsItem }}</td></tr>@endforeach</tbody></table>
                                        @endif
                                    @elseif($mode === 'members')
                                        <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">Состав клана</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                        <table class="clan-members"><thead><tr><th>Игрок</th><th>Звание</th><th>Уровень</th><th>Статус</th></tr></thead><tbody>
                                        @forelse($members as $member)
                                            <tr class="{{ $loop->odd ? 'bg_l' : '' }}"><td>@if($iconUrl)<img src="{{ $iconUrl }}" width="13" height="13" alt="">&nbsp;@endif<a href="{{ route('info.user', ['id' => $member['user']->id]) }}" class="clan-user-link" onclick="window.open(this.href, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $member['user']->name }}</a></td><td align="center">{{ $member['role']?->name ?? '—' }}</td><td align="center">{{ $member['user']->player?->lvl ?? '—' }}</td><td align="center">@if($member['is_online'])<b class="clan-member-online">online</b>@else<b>offline</b>@endif</td></tr>
                                        @empty
                                            <tr><td colspan="4" class="clan-empty">В клане пока нет участников.</td></tr>
                                        @endforelse
                                        </tbody></table>
                                    @else
                                        <table class="section-title"><tbody><tr valign="top"><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-left.gif') }}" width="47" height="18" alt=""></td><td class="section-title__center" align="center">История клана</td><td width="47"><img src="{{ asset('main/images/tbl-aft_label-c-right.gif') }}" width="47" height="18" alt=""></td></tr></tbody></table>
                                        <table class="clan-history"><thead><tr><th width="105">Дата</th><th>Событие</th><th width="105">Участник</th></tr></thead><tbody>
                                        @forelse($logs as $log)
                                            <tr class="{{ $loop->odd ? 'bg_l' : '' }}"><td align="center">{{ $log->created_at?->format('d.m.Y H:i') }}</td><td>{{ $log->details ?: $log->action->label() }}</td><td align="center">@if($log->user)<a href="{{ route('info.user', ['id' => $log->user->id]) }}" class="clan-user-link" onclick="window.open(this.href, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">{{ $log->user->name }}</a>@else—@endif</td></tr>
                                        @empty
                                            <tr><td colspan="3" class="clan-empty">История клана пока пуста.</td></tr>
                                        @endforelse
                                        </tbody></table>
                                        @if($logs->hasPages())<div class="pagination">{{ $logs->links() }}</div>@endif
                                    @endif
                                </td>
                                <td class="tbl-usi_right">&nbsp;</td>
                            </tr>
                            <tr height="18"><td width="20" align="right" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt=""></td><td class="tbl-shp_sml-bottom">&nbsp;</td><td width="20" align="left" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt=""></td></tr>
                            </tbody>
                        </table>
                    </div></div></div></div></div></div></div></div>
                    <div class="bg-b"></div>
                </div>
            </td></tr>
            </tbody>
        </table>
    </td></tr></tbody>
</table>
</body>
</html>
