<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Реферальная программа</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body {
            min-height: 100%;
            margin: 0;
            color: #2f1f0b;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
        }

        a, a:link, a:visited, a:active { color: #5a1f00; text-decoration: none; }
        a:hover { color: #8b2f00; text-decoration: underline; }

        .ref-page {
            padding: 8px;
            box-sizing: border-box;
        }

        .ref-shell {
            max-width: 980px;
            margin: 0 auto;
            border: 1px solid #b77a32;
            background: #f4dc9b;
            box-shadow:
                inset 0 0 0 1px rgba(255, 255, 238, .75),
                0 2px 0 rgba(112, 73, 21, .3);
        }

        .ref-hero {
            padding: 10px 12px;
            border-bottom: 1px solid #b77a32;
            background: #e8bd67;
        }

        .ref-title {
            margin: 0;
            color: #3f2608;
            font-size: 16px;
            line-height: 1.2;
            text-shadow: 0 1px 0 rgba(255, 242, 194, .8);
        }

        .ref-subtitle {
            margin-top: 4px;
            color: #67431a;
            font-size: 11px;
        }

        .ref-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 6px;
            margin-top: 9px;
        }

        .ref-stat {
            padding: 6px 8px;
            border: 1px solid #b9853b;
            background: #fff0bd;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, .55);
        }

        .ref-stat-label {
            display: block;
            color: #6f5226;
            font-size: 10px;
        }

        .ref-stat-value {
            display: block;
            margin-top: 2px;
            color: #3c2308;
            font-size: 14px;
            font-weight: bold;
        }

        .ref-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.25fr) minmax(260px, .75fr);
            gap: 8px;
            padding: 8px;
        }

        .ref-panel {
            border: 1px solid #b9853b;
            background: #f8e6ad;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 238, .65);
        }

        .ref-panel + .ref-panel { margin-top: 8px; }

        .ref-panel-title {
            padding: 6px 9px;
            color: #3e2508;
            font-size: 12px;
            font-weight: bold;
            border-bottom: 1px solid #b9853b;
            background: #e5b865;
            text-shadow: 0 1px 0 rgba(255, 242, 194, .75);
        }

        .ref-panel-body { padding: 8px; }

        .ref-link-row {
            display: flex;
            gap: 6px;
            align-items: stretch;
        }

        .ref-link-input {
            flex: 1;
            min-width: 0;
            height: 28px;
            padding: 0 8px;
            color: #3a250d;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            border: 1px solid #a86f2d;
            background: #fff7d9;
            box-sizing: border-box;
            cursor: pointer;
        }

        .ref-copy-btn {
            width: 92px;
            color: #2f1b07;
            font-family: Tahoma, Arial, sans-serif;
            font-size: 11px;
            font-weight: bold;
            border: 1px solid #8c5f22;
            background: #f1c36a;
            cursor: pointer;
            box-shadow: inset 0 1px 0 rgba(255, 252, 213, .55);
        }

        .ref-copy-btn:hover { background: #ffd87e; }

        .ref-note {
            margin-top: 6px;
            color: #6b5029;
            line-height: 1.35;
        }

        .ref-inviter {
            margin-top: 7px;
            padding: 5px 7px;
            color: #4f3514;
            border: 1px solid #d3a55f;
            background: #fff2bf;
        }

        .ref-table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
        }

        .ref-table th,
        .ref-table td {
            padding: 6px 7px;
            border-bottom: 1px solid #dbbc7a;
            vertical-align: middle;
        }

        .ref-table th {
            color: #4c310f;
            font-size: 10px;
            text-align: left;
            background: #efd086;
        }

        .ref-table th.center,
        .ref-table td.center { text-align: center; }

        .ref-table tbody tr:nth-child(even) { background: rgba(255, 250, 221, .48); }
        .ref-table tbody tr:hover { background: #fff4c8; }
        .ref-table tbody tr:last-child td { border-bottom: 0; }

        .ref-player-name { font-weight: bold; }
        .user_offline { color: #857767; }

        .ref-status {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            white-space: nowrap;
        }

        .online-dot,
        .offline-dot {
            display: inline-block;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(66, 45, 15, .3);
        }

        .online-dot { background: #2a9a00; }
        .offline-dot { background: #9c8d78; }

        .ref-progress {
            display: inline-block;
            min-width: 42px;
            padding: 2px 6px;
            color: #51340f;
            font-weight: bold;
            border: 1px solid #bd8a3e;
            background: #fff0bd;
        }

        .ref-empty {
            padding: 12px 10px;
            color: #806c4c;
            text-align: center;
        }

        .reward-list {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .reward-item {
            display: grid;
            grid-template-columns: 42px minmax(0, 1fr);
            gap: 7px;
            align-items: center;
            padding: 7px;
            border-bottom: 1px solid #dbbc7a;
        }

        .reward-item:nth-child(even) { background: rgba(255, 250, 221, .45); }
        .reward-item:last-child { border-bottom: 0; }

        .reward-level {
            padding: 4px 0;
            color: #442708;
            font-size: 13px;
            font-weight: bold;
            text-align: center;
            border: 1px solid #a86f2d;
            background: #f1c36a;
            box-shadow: inset 0 1px 0 rgba(255, 252, 213, .5);
        }

        .reward-main {
            color: #35230c;
            font-weight: bold;
        }

        .reward-desc {
            margin-top: 2px;
            color: #7b6139;
            font-size: 10px;
            line-height: 1.3;
        }

        .reward-icon {
            margin-right: 4px;
            vertical-align: -1px;
        }

        @media (max-width: 760px) {
            .ref-grid,
            .ref-stats { grid-template-columns: 1fr; }

            .ref-link-row { display: block; }

            .ref-copy-btn {
                width: 100%;
                height: 27px;
                margin-top: 5px;
            }
        }
    </style>
</head>
<body>
@php
    $referralsCount = count($page->referred);
    $stagesCount = count($page->stages);
    $claimedCount = array_sum(array_map(static fn ($referral) => $referral->claimedRewardsCount, $page->referred));
@endphp

<div class="ref-page">
    <div class="ref-shell">
        <div class="ref-hero">
            <h1 class="ref-title">Реферальная программа</h1>
            <div class="ref-subtitle">Приглашайте игроков и получайте награды за их развитие.</div>

            <div class="ref-stats">
                <div class="ref-stat">
                    <span class="ref-stat-label">Приглашённых</span>
                    <span class="ref-stat-value">{{ $referralsCount }}</span>
                </div>
                <div class="ref-stat">
                    <span class="ref-stat-label">Этапов наград</span>
                    <span class="ref-stat-value">{{ $stagesCount }}</span>
                </div>
                <div class="ref-stat">
                    <span class="ref-stat-label">Наград выдано</span>
                    <span class="ref-stat-value">{{ $claimedCount }}</span>
                </div>
            </div>
        </div>

        <div class="ref-grid">
            <div>
                <div class="ref-panel">
                    <div class="ref-panel-title">Ваша реферальная ссылка</div>
                    <div class="ref-panel-body">
                        <div class="ref-link-row">
                            <input class="ref-link-input" type="text" readonly value="{{ $page->referralLink }}" onclick="this.select()" id="ref-link">
                            <button type="button" class="ref-copy-btn" onclick="copyReferralLink()">Копировать</button>
                        </div>
                        <div class="ref-note">Ссылка содержит имя вашего персонажа. Друг должен зарегистрироваться по ней, чтобы попасть в список приглашённых.</div>

                        @if($page->invitedByName !== null)
                            <div class="ref-inviter">Вас пригласил: <b>{{ $page->invitedByName }}</b></div>
                        @endif
                    </div>
                </div>

                <div class="ref-panel">
                    <div class="ref-panel-title">Приглашённые игроки</div>

                    @if($page->referred === [])
                        <div class="ref-empty">Вы ещё никого не пригласили.</div>
                    @else
                        <table class="ref-table">
                            <thead>
                                <tr>
                                    <th>Игрок</th>
                                    <th class="center" width="58">Уровень</th>
                                    <th class="center" width="68">Статус</th>
                                    <th class="center" width="104">Был онлайн</th>
                                    <th class="center" width="86">Награды</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($page->referred as $referral)
                                <tr>
                                    <td>
                                        @if($referral->userId > 0)
                                            <a href="{{ route('info.user', ['id' => $referral->userId]) }}" target="_blank"
                                               class="ref-player-name {{ $referral->isOnline ? '' : 'user_offline' }}">
                                                {{ $referral->name }}
                                            </a>
                                        @else
                                            <span class="ref-player-name user_offline">{{ $referral->name }}</span>
                                        @endif
                                    </td>
                                    <td class="center">{{ $referral->level ?? '-' }}</td>
                                    <td class="center">
                                        @if($referral->isOnline)
                                            <span class="ref-status"><span class="online-dot"></span>Да</span>
                                        @else
                                            <span class="ref-status"><span class="offline-dot"></span>Нет</span>
                                        @endif
                                    </td>
                                    <td class="center user_offline">{{ $referral->lastOnlineLabel ?? '-' }}</td>
                                    <td class="center">
                                        <span class="ref-progress">{{ $referral->claimedRewardsCount }} / {{ $referral->totalStagesCount }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

            <div>
                <div class="ref-panel">
                    <div class="ref-panel-title">Этапы наград</div>

                    @if($page->stages === [])
                        <div class="ref-empty">Этапы наград не настроены.</div>
                    @else
                        <ul class="reward-list">
                            @foreach($page->stages as $stage)
                                <li class="reward-item">
                                    <div class="reward-level">{{ $stage->levelThreshold }}</div>
                                    <div>
                                        <div class="reward-main">
                                            @if($stage->rewardType === \App\Modules\Referral\Domain\Enums\ReferralRewardType::GOLD->value)
                                                <img class="reward-icon" src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="">
                                                {{ $stage->rewardValue }} золота
                                            @elseif($stage->rewardType === \App\Modules\Referral\Domain\Enums\ReferralRewardType::DIAMOND->value)
                                                <img class="reward-icon" src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" alt="">
                                                {{ $stage->rewardValue }} алмазов
                                            @else
                                                {{ $stage->rewardValue }}x {{ $stage->rewardItemName ?? '?' }}
                                            @endif
                                        </div>
                                        @if($stage->description !== '')
                                            <div class="reward-desc">{{ $stage->description }}</div>
                                        @endif
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
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
