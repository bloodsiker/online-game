<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Реферальная программа</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 11px; }
        a, a:link, a:visited, a:active { color: #5a1f00; text-decoration: none; }
        a:hover { text-decoration: underline; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2, .brd2 td { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }

        .section-title {
            background: #c17d46;
            color: #fff;
            font-weight: bold;
            padding: 4px 10px;
            font-size: 12px;
        }
        .ref-link-box {
            background: #fffbd6;
            border: 1px solid #DB9F73;
            padding: 8px 12px;
            margin: 8px;
            font-size: 12px;
            word-break: break-all;
        }
        .ref-link-box input {
            width: 100%;
            border: 1px solid #c49485;
            background: #fff;
            padding: 3px 6px;
            font-size: 11px;
            font-family: Tahoma;
            cursor: pointer;
        }
        .hint { color: #666; font-size: 10px; padding: 0 8px 6px; }

        .stage-done   { color: #1a7a00; font-weight: bold; }
        .stage-active { color: #8b4a00; }
        .stage-locked { color: #999; }
        .online-dot  { display:inline-block; width:7px; height:7px; border-radius:50%; background:#2a9a00; margin-right:3px; vertical-align:middle; }
        .offline-dot { display:inline-block; width:7px; height:7px; border-radius:50%; background:#999; margin-right:3px; vertical-align:middle; }
        .user_offline { color: #999; }
    </style>
</head>
<body>

<table width="100%" height="100%" border="0" cellspacing="4" cellpadding="0">
<tr valign="top">

{{-- Левая колонка: ссылка + кого пригласили --}}
<td width="55%">

    <div class="section-title">Ваша реферальная ссылка</div>
    <div class="ref-link-box">
        <input type="text" readonly value="{{ $referralLink }}" onclick="this.select()" id="ref-link">
    </div>
    <div class="hint">Поделитесь ссылкой с другом. Когда он будет прокачиваться — вы будете получать награды.</div>

    @if($invitedBy)
    <div style="padding: 4px 10px 8px; color: #555;">
        Вас пригласил: <b>{{ $invitedBy->name }}</b>
    </div>
    @endif

    <div class="section-title" style="margin-top:6px">Приглашённые игроки ({{ $referred->count() }})</div>

    @if($referred->isEmpty())
        <div style="padding: 8px 10px; color: #999;">Вы ещё никого не пригласили.</div>
    @else
    <table class="coll brd2" width="100%" cellspacing="0" cellpadding="0">
        <thead>
            <tr style="background:#e8c9a0; font-weight:bold;">
                <td class="p10h p4v">Игрок</td>
                <td class="p4v" align="center" width="60">Уровень</td>
                <td class="p4v" align="center" width="70">Онлайн</td>
                <td class="p4v" align="center" width="110">Был онлайн</td>
                <td class="p4v" align="center" width="90">Наград выдано</td>
            </tr>
        </thead>
        <tbody>
        @foreach($referred as $ref)
            @php
                $player   = $ref->referred?->player;
                $refUser  = $ref->referred;
                $isOnline = $refUser?->last_online_at && $refUser->last_online_at > $onlineThreshold;
            @endphp
            <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                <td class="p10h p4v">
                    <a href="{{ route('info.user', ['id' => $refUser?->id]) }}" target="_blank"
                       class="{{ $isOnline ? '' : 'user_offline' }}">
                        {{ $refUser?->name ?? '—' }}
                    </a>
                </td>
                <td class="p4v" align="center">{{ $player?->lvl ?? '—' }}</td>
                <td class="p4v" align="center">
                    @if($isOnline)
                        <span class="online-dot"></span>Да
                    @else
                        <span class="offline-dot"></span>Нет
                    @endif
                </td>
                <td class="p4v" align="center" class="user_offline">
                    {{ $refUser?->last_online_at ? $refUser->last_online_at->diffForHumans() : '—' }}
                </td>
                <td class="p4v" align="center">{{ $ref->claims->count() }} / {{ $stages->count() }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

</td>

{{-- Правая колонка: таблица этапов --}}
<td width="45%">

    <div class="section-title">Этапы наград</div>

    @if($stages->isEmpty())
        <div style="padding: 8px 10px; color: #999;">Этапы наград не настроены.</div>
    @else
    <table class="coll brd2" width="100%" cellspacing="0" cellpadding="0">
        <thead>
            <tr style="background:#e8c9a0; font-weight:bold;">
                <td class="p10h p4v" width="70">Уровень</td>
                <td class="p10h p4v">Награда рефереру</td>
            </tr>
        </thead>
        <tbody>
        @foreach($stages as $stage)
            <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                <td class="p10h p4v" align="center"><b>{{ $stage->level_threshold }}</b></td>
                <td class="p10h p4v">
                    @if($stage->reward_type === \App\Enums\ReferralRewardType::GOLD)
                        <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" align="absmiddle">
                        {{ $stage->reward_value }} золота
                    @elseif($stage->reward_type === \App\Enums\ReferralRewardType::DIAMOND)
                        <img src="{{ asset('img/icon/m_dmd.gif') }}" width="11" height="11" align="absmiddle">
                        {{ $stage->reward_value }} алмазов
                    @else
                        {{ $stage->reward_value }}x {{ $stage->rewardItem?->name ?? '?' }}
                    @endif
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

</td>
</tr>
</table>

</body>
</html>