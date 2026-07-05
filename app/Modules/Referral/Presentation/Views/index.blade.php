<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Реферальная программа</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            color: #955c4a;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            background: url({{ asset('img/bg/bgg.gif') }});
        }

        a, a:link, a:visited, a:active { color: #5a1f00; text-decoration: none; }
        a:hover { color: #8b2f00; text-decoration: underline; }

        /* Вертикальные отступы ячеек, как p4v на проде */
        .p4v, .p4v td {
            padding-top: 4px;
            padding-bottom: 4px;
        }

        .black, .black * { color: #2f1f0b; }

        .ref-page { padding: 8px 10px; }

        .ref-window { margin-bottom: 10px; }

        .ref-link-input {
            width: 100%;
            box-sizing: border-box;
            font: normal 9pt Tahoma;
            color: #461c0b;
            border-radius: 3px;
            border: 1px solid #a86d4d;
            background: #fdfde3;
            padding: 3px 6px;
            cursor: pointer;
        }

        .online-dot, .offline-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            margin-right: 3px;
            box-shadow: 0 0 0 1px rgba(66, 45, 15, .3);
        }
        .online-dot { background: #2a9a00; }
        .offline-dot { background: #9c8d78; }

        .user_offline, .user_offline * { color: #857767; }

        .ref-note {
            padding: 6px 2px 0;
            line-height: 1.45;
            text-align: left;
        }
        .ref-note b { color: #461c0b; }

        .ref-empty {
            padding: 10px;
            text-align: center;
            color: #857767;
        }
    </style>
</head>
<body>

@php
    $referralsCount = count($page->referred);
    $onlineCount = count(array_filter($page->referred, static fn ($r) => $r->isOnline));
    $claimedCount = array_sum(array_map(static fn ($r) => $r->claimedRewardsCount, $page->referred));

    $byLevel = [];
    foreach ($page->referred as $r) {
        $lvl = $r->level ?? 0;
        $byLevel[$lvl] = ($byLevel[$lvl] ?? 0) + 1;
    }
    ksort($byLevel);
@endphp

<div class="ref-page">

    {{-- ======== СТАТИСТИКА ======== --}}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ref-window">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr height="22">
                        <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                        <td align="center" class="tbl-usi-hdr mbg">Статистика</td>
                        <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 10px 10px 12px;">
                <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr class="bg_l">
                        <td class="b" width="30%" align="right" nowrap>Приглашено игроков:</td>
                        <td class="b redd">{{ $referralsCount }}</td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Сейчас в игре:</td>
                        <td class="b redd">{{ $onlineCount }}</td>
                    </tr>
                    <tr class="bg_l brd2-top">
                        <td class="b" align="right" nowrap>Этапов наград:</td>
                        <td class="b redd">{{ count($page->stages) }}</td>
                    </tr>
                    <tr class="brd2-top">
                        <td class="b" align="right" nowrap>Наград получено:</td>
                        <td class="b redd">{{ $claimedCount }}</td>
                    </tr>
                    @if($page->invitedByName !== null)
                        <tr class="bg_l brd2-top">
                            <td class="b" align="right" nowrap>Вас пригласил:</td>
                            <td class="b black">{{ $page->invitedByName }}</td>
                        </tr>
                    @endif
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

    {{-- ======== ИНФОРМАЦИЯ ======== --}}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ref-window">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr height="22">
                        <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                        <td align="center" class="tbl-usi-hdr mbg">Информация</td>
                        <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 10px 10px 12px;">
                <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                    <tbody>
                    <tr class="bg_l">
                        <td class="b" width="30%" align="right" nowrap>Ваша реферальная ссылка:</td>
                        <td>
                            <input class="ref-link-input" type="text" readonly value="{{ $page->referralLink }}" onclick="this.select()" id="ref-link">
                        </td>
                        <td width="120" align="center">
                            <b class="butt1 pointer"><b><input value="Копировать" type="button" onclick="copyReferralLink()" style="width: 100px;" class="redd"></b></b>
                        </td>
                    </tr>
                    </tbody>
                </table>

                <div class="ref-note">
                    <b>Реферал</b> — игрок, привлечённый вами в игру при помощи вашей реферальной ссылки.
                    Друг должен зарегистрироваться по ней, чтобы попасть в список приглашённых.
                    Когда ваш реферал достигает очередного уровня из таблицы ниже, вы получаете награду —
                    она начисляется автоматически и отображается в разделе «Статистика».
                </div>

                <br>

                @if($page->stages === [])
                    <div class="ref-empty">Этапы наград не настроены.</div>
                @else
                    <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                        <tbody>
                        <tr>
                            <td class="b" width="30%">Уровень реферала</td>
                            <td class="b" width="30%">Награда</td>
                            <td class="b">Описание</td>
                        </tr>
                        @foreach($page->stages as $stage)
                            <tr class="{{ $loop->odd ? 'bg_l' : '' }}">
                                <td class="brd2-top brd2-bt b">{{ $stage->levelThreshold }}</td>
                                <td class="brd2-top brd2-bt">
                                    @if($stage->rewardType === \App\Modules\Referral\Domain\Enums\ReferralRewardType::GOLD->value)
                                        <span title="Золото"><img src="{{ asset('img/icon/m_game.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;<b class="redd">{{ $stage->rewardValue }}</b>
                                    @elseif($stage->rewardType === \App\Modules\Referral\Domain\Enums\ReferralRewardType::DIAMOND->value)
                                        <span title="Бриллианты"><img src="{{ asset('img/icon/m_dmd.gif') }}" border="0" width="11" height="11" align="absmiddle"></span>&nbsp;<b class="redd">{{ $stage->rewardValue }}</b>
                                    @else
                                        <b class="redd">{{ $stage->rewardValue }}x</b> {{ $stage->rewardItemName ?? '?' }}
                                    @endif
                                </td>
                                <td class="brd2-top brd2-bt">{{ $stage->description }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
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

    {{-- ======== КОЛИЧЕСТВО РЕФЕРАЛОВ ======== --}}
    <table width="100%" border="0" cellspacing="0" cellpadding="0" class="ref-window">
        <tbody>
        <tr height="22">
            <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
            <td class="tbl-shp-sml tt" valign="top" align="center">
                <table border="0" cellspacing="0" cellpadding="0">
                    <tbody>
                    <tr height="22">
                        <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                        <td align="center" class="tbl-usi-hdr mbg">Количество рефералов</td>
                        <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                    </tr>
                    </tbody>
                </table>
            </td>
            <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" valign="top" style="padding: 10px 10px 12px;">
                @if($page->referred === [])
                    <div class="ref-empty">Вы ещё никого не пригласили.</div>
                @else
                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                        <tbody>
                        <tr valign="top">
                            <td width="170">
                                <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                    <tr>
                                        <td class="b" width="30%">Уровень</td>
                                        <td class="b">Количество</td>
                                    </tr>
                                    @foreach($byLevel as $lvl => $count)
                                        <tr class="{{ $loop->odd ? 'bg_l' : '' }}">
                                            <td class="brd2-top brd2-bt b">{{ $lvl > 0 ? $lvl : '-' }}</td>
                                            <td class="brd2-top brd2-bt">{{ $count }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </td>
                            <td width="10"></td>
                            <td>
                                <table class="coll w100 p10h p4v brd2-all" cellspacing="0" cellpadding="0" border="0">
                                    <tbody>
                                    <tr>
                                        <td class="b">Игрок</td>
                                        <td class="b" width="58" align="center">Уровень</td>
                                        <td class="b" width="68" align="center">Статус</td>
                                        <td class="b" width="104" align="center">Был онлайн</td>
                                        <td class="b" width="86" align="center">Награды</td>
                                    </tr>
                                    @foreach($page->referred as $referral)
                                        <tr class="{{ $loop->odd ? 'bg_l' : '' }}">
                                            <td class="brd2-top brd2-bt">
                                                @if($referral->userId > 0)
                                                    <a href="{{ route('info.user', ['id' => $referral->userId]) }}" target="_blank"
                                                       class="{{ $referral->isOnline ? '' : 'user_offline' }}">
                                                        <b>{{ $referral->name }}</b>
                                                    </a>
                                                @else
                                                    <b class="user_offline">{{ $referral->name }}</b>
                                                @endif
                                            </td>
                                            <td class="brd2-top brd2-bt b" align="center">{{ $referral->level ?? '-' }}</td>
                                            <td class="brd2-top brd2-bt" align="center">
                                                @if($referral->isOnline)
                                                    <span class="online-dot"></span>Да
                                                @else
                                                    <span class="offline-dot"></span>Нет
                                                @endif
                                            </td>
                                            <td class="brd2-top brd2-bt user_offline" align="center">{{ $referral->lastOnlineLabel ?? '-' }}</td>
                                            <td class="brd2-top brd2-bt" align="center"><b class="redd">{{ $referral->claimedRewardsCount }}</b> / {{ $referral->totalStagesCount }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                        </tbody>
                    </table>
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

</div>

<script>
    function copyReferralLink() {
        var input = document.getElementById('ref-link');
        if (!input) {
            return;
        }

        input.select();

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value);
            return;
        }

        document.execCommand('copy');
    }
</script>
</body>
</html>