<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Башня Восхождения') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #461c0b; font-family: Tahoma; font-size: 11px; }
        a, a:link, a:visited, a:active { text-decoration: none; color: #461C0B; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2, .brd2 td { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .w100 { width: 100%; }
        .grnn { color: #114d01 !important; }
        .redd { color: #a02020 !important; }

        .section-title {
            display: block;
            box-sizing: border-box;
            width: 100%;
            margin: 0 0 6px;
            padding: 4px 8px;
            border: 1px solid #c49485;
            background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat;
            color: #955c4a;
            font-weight: bold;
            font-size: 11px;
            line-height: 14px;
            text-align: left;
        }

        .empty-state {
            margin-bottom: 7px;
            padding: 7px 9px;
            border: 1px solid #DB9F73;
            background: #fff8ec url(/img/bg/info/bg_l.gif) repeat;
            color: #955c4a;
            font-style: italic;
        }

        .info-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-bottom: 10px;
        }
        .info-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 8px;
            border: 1px solid #DB9F73;
            border-radius: 3px;
            background: #fff8ec url(/img/bg/info/bg_l.gif) repeat;
            color: #461c0b;
        }
        .info-pill .lbl { color: #955c4a; }

        .boss-card {
            position: relative;
            box-sizing: border-box;
            width: 100%;
            max-width: 390px;
            min-height: 360px;
            margin: 10px auto 14px;
            overflow: hidden;
            border: 1px solid #b99a7c;
            background: #ead8ba url({{ asset('img/bg/common-bg.png') }}) repeat;
            box-shadow: 0 1px 0 #fff inset, 0 1px 3px rgba(67, 37, 17, .25);
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            text-align: center;
        }
        .boss-card__header {
            position: relative;
            z-index: 2;
            display: table;
            width: fit-content;
            max-width: calc(100% - 52px);
            height: 22px;
            margin: 8px auto 0;
            padding: 0 24px;
            box-sizing: border-box;
            background: url({{ asset('img/bg/info/tbl-usi_label-center.gif') }}) repeat-x;
            color: #ffe9ba;
            line-height: 20px;
            text-align: center;
            text-shadow: 0 1px 1px #4b160c;
        }
        .boss-card__header::before,
        .boss-card__header::after {
            position: absolute;
            top: 0;
            width: 27px;
            height: 22px;
            content: '';
        }
        .boss-card__header::before {
            left: -23px;
            background: url({{ asset('img/bg/info/tbl-usi_label-left.gif') }}) no-repeat;
        }
        .boss-card__header::after {
            right: -23px;
            background: url({{ asset('img/bg/info/tbl-usi_label-right.gif') }}) no-repeat;
        }
        .boss-card__body {
            display: flex;
            min-height: 317px;
            padding: 13px 16px 12px;
            flex-direction: column;
            align-items: center;
        }
        .boss-card__portrait {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 160px;
            height: 160px;
            margin: 1px auto 5px;
            padding: 7px;
            box-sizing: border-box;
            overflow: hidden;
            background: url({{ asset('main/images/user-reward-frame.png') }}) center / 100% 100% no-repeat;
        }
        .boss-card__portrait img {
            display: block;
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
        .boss-card .boss-name {
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 14px;
            color: #8b3a1a;
        }
        .boss-card__divider {
            position: relative;
            width: 88%;
            height: 11px;
            margin: 3px 0 5px;
        }
        .boss-card__divider::before {
            position: absolute;
            top: 5px;
            right: 0;
            left: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #9a704f 18%, #9a704f 82%, transparent);
            content: '';
        }
        .boss-card__divider::after {
            position: absolute;
            top: 2px;
            left: 50%;
            width: 7px;
            height: 7px;
            border: 1px solid #895936;
            background: #d4aa70;
            content: '';
            transform: translateX(-50%) rotate(45deg);
        }
        .boss-card__progress-title {
            margin-bottom: 3px;
            color: #6c2f1f;
            font-size: 11px;
            font-weight: bold;
        }
        .hpbar {
            height: 15px;
            width: 100%;
            max-width: 560px;
            margin: 0 auto 5px;
            background: #3a0d0d;
            border: 1px solid #000;
            border-radius: 3px;
            overflow: hidden;
        }
        .hpbar > div {
            height: 100%;
            background: linear-gradient(#e74c3c, #922b21);
            transition: width .4s ease;
        }
        .boss-card__details {
            width: 100%;
            margin-top: 2px;
            color: #594133;
            line-height: 16px;
        }
        .boss-card__detail {
            padding: 2px 0;
        }
        .boss-card__detail .lbl { color: #745744; }
        @media (max-width: 520px) {
            .boss-card { max-width: 100%; }
            .boss-card__body { padding: 10px 12px; }
        }

        .boss-run-layout { max-width: 930px; margin: 0 auto 14px; }
        .boss-carousel {
            position: relative;
            max-width: 930px;
            min-height: 410px;
            margin: 0 auto 12px;
        }
        .boss-carousel__viewport { position: relative; height: 410px; overflow: hidden; }
        .boss-carousel__slide {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 211px;
            opacity: 0;
            pointer-events: none;
            transform: translate(-50%, -50%) scale(.78);
            transition: transform .38s ease, opacity .38s ease;
        }
        .boss-carousel__slide--center {
            z-index: 2;
            opacity: 1;
            pointer-events: auto;
            transform: translate(-50%, -50%) scale(1.3);
        }
        .boss-carousel__slide--left,
        .boss-carousel__slide--right {
            z-index: 1;
            opacity: .58;
            pointer-events: auto;
            transform: translate(calc(-50% - 275px), -50%) scale(1);
        }
        .boss-carousel__slide--right { transform: translate(calc(-50% + 275px), -50%) scale(1); }
        .boss-carousel__slide--hidden { visibility: hidden; }
        .boss-carousel__slide .boss-history__card {
            width: 200px;
            min-height: 270px;
            padding: 13px 10px;
            flex: 0 0 200px;
            transform: none;
        }
        .boss-carousel__slide--center .boss-history__card {
            border-color: #a77b56;
            box-shadow: 0 1px 0 #fff inset, 0 2px 9px rgba(67, 37, 17, .4);
        }
        .boss-carousel__link { display: flex; width: 100%; flex-direction: column; align-items: center; color: inherit; text-decoration: none; }
        .boss-carousel__link:hover .boss-history__name { text-decoration: underline; }
        .boss-carousel__state { margin-top: 7px; color: #8b3a1a; font-size: 11px; font-weight: bold; }
        .boss-run-arrow {
            position: absolute;
            z-index: 4;
            top: 50%;
            display: flex;
            width: 30px;
            height: 36px;
            align-items: center;
            justify-content: center;
            padding: 0;
            border: 0;
            background: transparent;
            cursor: pointer;
            transform: translateY(-50%);
        }
        .boss-run-arrow img { display: block; width: 30px; height: 36px; }
        .boss-run-arrow:hover { filter: brightness(1.08); }
        /* Одинаковый отступ от увеличенной центральной карточки с обеих сторон. */
        .boss-run-arrow--prev { left: calc(50% - 175px); }
        .boss-run-arrow--next { right: calc(50% - 160px); }
        .boss-run-arrow:disabled { opacity: .35; cursor: default; }
        .boss-history__card {
            display: flex;
            box-sizing: border-box;
            width: 211px;
            min-height: 299px;
            padding: 13px 10px;
            flex: 0 0 211px;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border: 1px solid #b99a7c;
            background: #ead8ba url({{ asset('img/bg/common-bg.png') }}) repeat;
            box-shadow: 0 1px 0 #fff inset, 0 1px 3px rgba(67, 37, 17, .25);
            text-align: center;
            transform-origin: center center;
        }
        .boss-history__portrait {
            display: flex;
            width: 170px;
            height: 170px;
            flex: 0 0 170px;
            margin-bottom: 9px;
            padding: 11px;
            box-sizing: border-box;
            align-items: center;
            justify-content: center;
            background: url({{ asset('main/images/user-reward-frame.png') }}) center / 100% 100% no-repeat;
        }
        .boss-history__portrait img { display: block; max-width: 100%; max-height: 100%; object-fit: contain; }
        .boss-history__name { color: #8b3a1a; font-size: 12px; font-weight: bold; line-height: 16px; }
        .boss-history__stage { margin-top: 4px; color: #745744; font-size: 11px; }
        .boss-history__defeated { margin-top: 6px; color: #387629; font-size: 11px; font-weight: bold; }
        .boss-current-info {
            height: auto;
            min-height: 0;
            margin: 8px auto 14px;
        }
        .boss-current-info .boss-card__body {
            height: auto;
            min-height: 0;
            flex: 0 0 auto;
            padding: 10px 16px;
        }
        @media (max-width: 680px) {
            .boss-carousel { min-height: 350px; }
            .boss-carousel__viewport { height: 350px; }
            .boss-carousel__slide--left { transform: translate(calc(-50% - clamp(145px, 38vw, 275px)), -50%) scale(.9); }
            .boss-carousel__slide--right { transform: translate(calc(-50% + clamp(145px, 38vw, 275px)), -50%) scale(.9); }
            .boss-carousel__slide--center { transform: translate(-50%, -50%) scale(1.08); }
            .boss-run-arrow--prev { left: 2px; }
            .boss-run-arrow--next { right: 2px; }
        }

        table.history-tbl {
            border-top: 1px solid #DB9F73;
            border-left: 1px solid #DB9F73;
            border-collapse: separate;
            width: 100%;
        }
        .history-tbl td, .history-tbl th {
            border-right: 1px solid #DB9F73;
            border-bottom: 1px solid #DB9F73;
            height: 22px;
            padding: 3px 6px;
            color: #461c0b;
            vertical-align: middle;
        }
        .history-tbl th { color: #955c4a; white-space: nowrap; background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat; }
        .history-tbl tr.bg_l td { background-image: url(/img/bg/info/bg_l.gif); }
        .history-tbl tr.me-row td { background-image: url(/img/bg/bg_l3.gif); cursor: pointer; }

        .rank-cell { text-align: center; width: 28px; color: #955c4a; font-weight: bold; }
        .rank-cell.rank-1 { color: #a0790a; }
        .rank-cell.rank-2 { color: #6b6b6b; }
        .rank-cell.rank-3 { color: #8a5a2a; }

        .player-cell { white-space: nowrap; }
        .player-row { display: inline-flex; align-items: center; gap: 5px; }
        .player-row .icon-link { display: inline-flex; align-items: center; flex: 0 0 auto; }
        .player-row .icon-link img { display: block; }
        .player-row .player-name { display: inline-flex; align-items: center; flex: 0 0 auto; }
        .player-row .me-badge { flex: 0 0 auto; margin-left: 1px; }

        .archive-status { font-weight: bold; white-space: nowrap; }
        .archive-status--completed { color: #267019; }
        .archive-status--expired { color: #a02020; }
        .archive-meta { color: #777; white-space: nowrap; }
        #clan-boss-history .history-tbl th:nth-child(2),
        #clan-boss-history .history-tbl th:nth-child(3),
        #clan-boss-history .history-tbl th:nth-child(4),
        #clan-boss-history .history-tbl td:nth-child(2),
        #clan-boss-history .history-tbl td:nth-child(3),
        #clan-boss-history .history-tbl td:nth-child(4) { text-align: center; }
    </style>
</head>
<body class="regblk">

@include('clan::partials.tabs', ['activeTab' => 'clan.boss'])

<table class="coll" width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:20px;">
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" id="clan-boss-current">
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
                                <td align="center" class="tbl-usi_label-center">{{ $archivedRun !== null ? __('Результаты похода') : __('Башня Восхождения') }}</td>
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
                    <td class="tbl-usi_bg" valign="top" style="padding: 6px 4px;">

                        @if($archivedRun === null)
                        @if($run === null)
                            <div class="empty-state">
                                <b>{{ __('Поход не открыт.') }}</b> {{ __('Лидер клана может открыть поход в логове кланового босса.') }}
                            </div>
                        @else
                            @php
                                function clanBossRuPlural(int $n, string $one, string $few, string $many): string
                                {
                                    $n = abs($n) % 100;
                                    $n1 = $n % 10;
                                    if ($n > 10 && $n < 20) return $many;
                                    if ($n1 > 1 && $n1 < 5) return $few;
                                    if ($n1 === 1) return $one;
                                    return $many;
                                }

                                function clanBossFormatRemaining(int $totalSeconds): string
                                {
                                    if ($totalSeconds <= 0) {
                                        return __('истекло');
                                    }

                                    $days = intdiv($totalSeconds, 86400);
                                    $hours = intdiv($totalSeconds % 86400, 3600);
                                    $minutes = intdiv($totalSeconds % 3600, 60);

                                    $parts = [];
                                    if ($days > 0) {
                                        $parts[] = $days . ' ' . clanBossRuPlural($days, __('день'), __('дня'), __('дней'));
                                    }
                                    if ($days > 0 || $hours > 0) {
                                        $parts[] = $hours . ' ' . clanBossRuPlural($hours, __('час'), __('часа'), __('часов'));
                                    }
                                    $parts[] = $minutes . ' ' . clanBossRuPlural($minutes, __('минута'), __('минуты'), __('минут'));

                                    return implode(' ', $parts);
                                }

                                $runExpiresTs = $run->expires_at?->timestamp;
                                $runRemainingSeconds = $runExpiresTs !== null ? max(0, $runExpiresTs - time()) : null;

                                $batchAvailableTs = $personalAvailableAt?->timestamp;
                            @endphp

                            @if($currentState !== null && $currentState->bossInstance !== null)
                                @php
                                    $hpNow = max(0, (int) $currentState->bossInstance->hp_now);
                                    $hpMax = max(1, (int) $currentState->hp_max);
                                    $pct = (int) round($hpNow / $hpMax * 100);
                                    $bossMonster = $currentState->bossInstance->monster;
                                    $bossImage = $bossMonster?->image
                                        ? (\Illuminate\Support\Str::startsWith($bossMonster->image, ['http://', 'https://'])
                                            ? $bossMonster->image
                                            : asset(ltrim($bossMonster->image, '/')))
                                        : 'https://fun-dwar.com/images/data/bots/skelet.jpg';
                                @endphp
                                @php $currentSlideIndex = $previousBosses->count(); @endphp
                                <div class="boss-run-layout">
                                    <div class="boss-carousel" data-start-index="{{ $currentSlideIndex }}">
                                        <button type="button" class="boss-run-arrow boss-run-arrow--prev" aria-label="Предыдущий босс" onclick="clanBossMoveSlider(this, -1)">
                                            <img src="{{ asset('main/images/p-left-red-new.gif') }}" alt="">
                                        </button>
                                        <div class="boss-carousel__viewport">
                                            @foreach($previousBosses as $previousBoss)
                                                @php
                                                    $slideMonster = $previousBoss->bossInstance?->monster;
                                                    $slideImage = $slideMonster?->image
                                                        ? (\Illuminate\Support\Str::startsWith($slideMonster->image, ['http://', 'https://'])
                                                            ? $slideMonster->image
                                                            : asset(ltrim($slideMonster->image, '/')))
                                                        : 'https://fun-dwar.com/images/data/bots/skelet.jpg';
                                                @endphp
                                                <div class="boss-carousel__slide {{ $loop->last ? 'boss-carousel__slide--left' : 'boss-carousel__slide--hidden' }}" data-index="{{ $loop->index }}">
                                                    <div class="boss-history__card">
                                                        @if($slideMonster?->id)
                                                            <a class="boss-carousel__link" href="{{ route('info.monster.catalog', ['id' => $slideMonster->id]) }}" onclick="return clanBossOpenWindow(this.href, 730, 850)">
                                                                <div class="boss-history__portrait"><img src="{{ $slideImage }}" alt="{{ $slideMonster->name }}"></div>
                                                                <div class="boss-history__name">{{ $slideMonster->name }}</div>
                                                            </a>
                                                        @else
                                                            <div class="boss-history__portrait"><img src="{{ $slideImage }}" alt="{{ $previousBoss->stage?->name ?? 'Поверженный босс' }}"></div>
                                                            <div class="boss-history__name">{{ $previousBoss->stage?->name ?? 'Босс' }}</div>
                                                        @endif
                                                        <div class="boss-history__stage">{{ __('Этап') }} {{ $previousBoss->stage?->number ?? '—' }}</div>
                                                        <div class="boss-history__defeated">✓ Повержен</div>
                                                        @if(($killSeconds = $previousBoss->killDurationSeconds()) !== null)
                                                            <div class="boss-history__stage" title="{{ __('От появления босса до его убийства, включая паузы') }}">{{ __('Время на убийство') }}: {{ \Carbon\CarbonInterval::seconds($killSeconds)->cascade()->locale(app()->getLocale())->forHumans(['parts' => 3]) }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            <div class="boss-carousel__slide boss-carousel__slide--center" data-index="{{ $currentSlideIndex }}">
                                                <div class="boss-history__card">
                                                    @if($bossMonster?->id)
                                                        <a class="boss-carousel__link" href="{{ route('info.monster.catalog', ['id' => $bossMonster->id]) }}" onclick="return clanBossOpenWindow(this.href, 730, 850)">
                                                            <div class="boss-history__portrait"><img src="{{ $bossImage }}" alt="{{ $bossMonster->name }}"></div>
                                                            <div class="boss-history__name">{{ $bossMonster->name }}</div>
                                                        </a>
                                                    @else
                                                        <div class="boss-history__portrait"><img src="{{ $bossImage }}" alt="Клановый босс"></div>
                                                        <div class="boss-history__name">{{ __('Клановый босс') }}</div>
                                                    @endif
                                                    <div class="boss-history__stage">{{ __('Этап') }} {{ $currentState->stage?->number ?? $run->currentStage?->number ?? '—' }}</div>
                                                    <div class="boss-carousel__state">{{ __('Сейчас в бою') }}</div>
                                                </div>
                                            </div>

                                            @foreach($upcomingBosses as $upcomingBoss)
                                                @php
                                                    $slideMonster = $upcomingBoss->monster;
                                                    $slideImage = $slideMonster->image
                                                        ? (\Illuminate\Support\Str::startsWith($slideMonster->image, ['http://', 'https://'])
                                                            ? $slideMonster->image
                                                            : asset(ltrim($slideMonster->image, '/')))
                                                        : 'https://fun-dwar.com/images/data/bots/skelet.jpg';
                                                    $slideIndex = $currentSlideIndex + $loop->iteration;
                                                @endphp
                                                <div class="boss-carousel__slide {{ $loop->first ? 'boss-carousel__slide--right' : 'boss-carousel__slide--hidden' }}" data-index="{{ $slideIndex }}">
                                                    <div class="boss-history__card">
                                                        <a class="boss-carousel__link" href="{{ route('info.monster.catalog', ['id' => $slideMonster->id]) }}" onclick="return clanBossOpenWindow(this.href, 730, 850)">
                                                            <div class="boss-history__portrait"><img src="{{ $slideImage }}" alt="{{ $slideMonster->name }}"></div>
                                                            <div class="boss-history__name">{{ $slideMonster->name }}</div>
                                                        </a>
                                                        <div class="boss-history__stage">{{ __('Этап') }} {{ $upcomingBoss->stage?->number ?? '—' }}</div>
                                                        <div class="boss-history__defeated">{{ __('Впереди') }}</div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <button type="button" class="boss-run-arrow boss-run-arrow--next" aria-label="Следующий босс" onclick="clanBossMoveSlider(this, 1)">
                                            <img src="{{ asset('main/images/p-right-red-new.gif') }}" alt="">
                                        </button>
                                    </div>

                                    <div class="boss-card boss-current-info">
                                        <div class="boss-card__header">{{ $bossMonster?->name ?? 'Клановый босс' }} — состояние боя</div>
                                        <div class="boss-card__body">
                                            <div class="boss-card__progress-title">{{ __('Здоровье босса:') }} {{ number_format($hpNow, 0, '', ' ') }} / {{ number_format($hpMax, 0, '', ' ') }} HP ({{ $pct }}%)</div>
                                            <div class="hpbar"><div style="width: {{ $pct }}%"></div></div>
                                            <div class="boss-card__details">
                                                <div class="boss-card__detail"><span class="lbl">{{ __('Данж:') }}</span>&nbsp;<b class="redd">{{ $run->dungeon->name ?? '—' }}</b></div>
                                                <div class="boss-card__detail">
                                                    <span class="lbl">{{ __('До конца похода:') }}</span>&nbsp;
                                                    @if($runRemainingSeconds !== null)
                                                        <b class="redd" data-countdown-until="{{ $runExpiresTs }}" data-countdown-fallback="{{ __('истекло') }}">{{ clanBossFormatRemaining($runRemainingSeconds) }}</b>
                                                    @else
                                                        <b class="redd">{{ __('без ограничения') }}</b>
                                                    @endif
                                                </div>
                                                <div class="boss-card__detail"><span class="lbl">{{ __('Ударов в пакете осталось:') }}</span>&nbsp;<b class="redd">{{ $hitsLeft ?? '—' }}/{{ $run->dungeon->clan_boss_hit_batch_size }}</b></div>
                                                <div class="boss-card__detail">
                                                    <span class="lbl">{{ __('Следующий пакет:') }}</span>&nbsp;
                                                    @if($batchAvailableTs !== null)
                                                        <b class="redd" data-countdown-seconds-until="{{ $batchAvailableTs }}" data-countdown-fallback="{{ __('доступен') }}">{{ __('через') }} {{ max(0, $batchAvailableTs - time()) }} {{ __('сек.') }}</b>
                                                    @else
                                                        <b class="grnn">{{ __('доступен') }}</b>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="empty-state">
                                    <b>{{ __('Босс сейчас недоступен.') }}</b>
                                </div>
                                <div class="info-bar">
                                    <span class="info-pill"><span class="lbl">{{ __('Данж:') }}</span>&nbsp;<b class="redd">{{ $run->dungeon->name ?? '—' }}</b></span>
                                    <span class="info-pill"><span class="lbl">{{ __('Этап:') }}</span>&nbsp;<b class="redd">{{ $run->currentStage?->number ?? '—' }}</b></span>
                                    <span class="info-pill"><span class="lbl">{{ __('До конца данжа:') }}</span>&nbsp;<b class="redd" @if($runExpiresTs !== null) data-countdown-until="{{ $runExpiresTs }}" data-countdown-fallback="{{ __('истекло') }}" @endif>{{ $runRemainingSeconds !== null ? clanBossFormatRemaining($runRemainingSeconds) : __('без ограничения') }}</b></span>
                                </div>
                            @endif

                            <span class="section-title">{{ __('Урон по боссу за данж') }}</span>
                            <table class="history-tbl" cellspacing="0" cellpadding="0">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th title="{{ __('Ник члена клана') }}">{{ __('Ник игрока') }}</th>
                                    <th title="{{ __('Суммарный урон по боссам за данж') }}">{{ __('Урон за данж') }}</th>
                                    <th title="{{ __('Суммарное число ударов за данж') }}">{{ __('Ударов') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($damageRows as $row)
                                    @php
                                        $isMe = auth()->id() === $row['user']?->id;
                                    @endphp
                                    <tr class="{{ $isMe ? 'me-row' : ($loop->even ? 'bg_l' : '') }}">
                                        <td class="rank-cell rank-{{ $loop->iteration }}">{{ $loop->iteration }}</td>
                                        <td class="player-cell">
                                            @if($row['user'] !== null)
                                                <span class="player-row">
                                                    <a href="#" class="icon-link" title="{{ __('Приватное сообщение') }}" onclick="clanBossInsertPrivate({{ \Illuminate\Support\Js::from($row['user']->name) }}); return false;"><img src="{{ asset('img/icon/users-arrow.gif') }}" width="12" height="10" border="0"></a>
                                                    <a href="{{ route('info.user', $row['user']->id) }}" class="player-name" onclick="clanBossOpenWindow(this.href, 930, 700); return false;"><b class="kser0">{{ $row['user']->name }}&nbsp;[{{ $row['user']->player?->lvl ?? '?' }}]</b></a>
                                                    <a href="{{ route('info.user', $row['user']->id) }}" class="icon-link" title="{{ __('Информация о персонаже') }}" onclick="clanBossOpenWindow(this.href, 930, 700); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" border="0"></a>
                                                    @if($isMe)<b class="grnn me-badge">(вы)</b>@endif
                                                </span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td align="center"><b class="redd">{{ number_format($row['damage'], 0, '', ' ') }}</b></td>
                                        <td align="center"><b>{{ $row['hits'] }}</b></td>
                                    </tr>
                                @empty
                                    <tr class="bg_l">
                                        <td colspan="4" align="center"><b>{{ __('Пока никто не бил босса.') }}</b></td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        @endif

                        @if($run !== null)
                            @include('clan::partials.boss-reward-receipts')
                        @endif

                        @endif

                        @if($archivedRun !== null)
                            @php
                                $archiveStatusLabel = $archivedRun->status === \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun::STATUS_COMPLETED
                                    ? __('Все боссы повержены')
                                    : __('Время похода вышло');
                                $archiveStatusClass = $archivedRun->status === \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun::STATUS_COMPLETED
                                    ? 'archive-status--completed'
                                    : 'archive-status--expired';
                            @endphp
                            <div style="margin-top: 14px;">
                                <span class="section-title">{{ __('Результаты похода') }} #{{ $archivedRun->id }}</span>
                                <div class="info-bar">
                                    <span class="info-pill"><span class="lbl">{{ __('Данж:') }}</span>&nbsp;<b>{{ $archivedRun->dungeon->name ?? '—' }}</b></span>
                                    <span class="info-pill"><span class="lbl">{{ __('Итог:') }}</span>&nbsp;<b class="archive-status {{ $archiveStatusClass }}">{{ $archiveStatusLabel }}</b></span>
                                    <span class="info-pill"><span class="lbl">{{ __('Начат:') }}</span>&nbsp;<b>{{ $archivedRun->started_at?->format('d.m.Y H:i') ?? '—' }}</b></span>
                                    <span class="info-pill"><span class="lbl">{{ __('Завершён:') }}</span>&nbsp;<b>{{ $archivedRun->finished_at?->format('d.m.Y H:i') ?? '—' }}</b></span>
                                </div>

                                <span class="section-title">{{ __('Этапы похода') }}</span>
                                <table class="history-tbl" cellspacing="0" cellpadding="0">
                                    <thead>
                                    <tr>
                                        <th>{{ __('Этап') }}</th>
                                        <th>{{ __('Босс') }}</th>
                                        <th>{{ __('Результат') }}</th>
                                        <th>{{ __('Урон клана') }}</th>
                                        <th>{{ __('Убит') }}</th>
                                        <th title="{{ __('От появления босса до его убийства, включая паузы') }}">{{ __('Время на убийство') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @forelse($archivedStages as $state)
                                        @php
                                            $stageResult = match ($state->status) {
                                                \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage::STATUS_KILLED => __('Побеждён'),
                                                \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage::STATUS_SKIPPED => __('Пропущен по времени этапа'),
                                                default => __('Время похода вышло'),
                                            };
                                            $stageDamage = (int) $archivedStageDamage->get($state->dungeon_stage_id, 0);
                                            $killSeconds = $state->killDurationSeconds();
                                        @endphp
                                        <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                                            <td align="center"><b>{{ $state->stage?->number ?? '—' }}. {{ $state->stage?->name ?? __('Этап удалён') }}</b></td>
                                            <td>
                                                @if($state->bossInstance?->monster !== null)
                                                    <a href="{{ route('info.monster.catalog', ['id' => $state->bossInstance->monster->id]) }}" onclick="return clanBossOpenWindow(this.href, 730, 850)">{{ $state->bossInstance->monster->name }}</a>
                                                @else
                                                    {{ $state->stage?->name ?? '—' }}
                                                @endif
                                            </td>
                                            <td><b class="{{ $state->status === \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage::STATUS_KILLED ? 'grnn' : 'redd' }}">{{ $stageResult }}</b></td>
                                            <td align="center"><b>{{ number_format($stageDamage, 0, '', ' ') }}</b></td>
                                            <td align="center">{{ $state->killed_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                            <td align="center">{{ $killSeconds !== null ? \Carbon\CarbonInterval::seconds($killSeconds)->cascade()->locale(app()->getLocale())->forHumans(['parts' => 3]) : '—' }}</td>
                                        </tr>
                                    @empty
                                        <tr class="bg_l"><td colspan="6" align="center">{{ __('В походе не было завершённых этапов.') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>

                                <span class="section-title" style="margin-top: 10px;">{{ __('Вклад участников') }}</span>
                                <table class="history-tbl" cellspacing="0" cellpadding="0">
                                    <thead><tr><th>#</th><th>{{ __('Игрок') }}</th><th>{{ __('Урон') }}</th><th>{{ __('Ударов') }}</th><th>{{ __('Награда за этапы') }}</th></tr></thead>
                                    <tbody>
                                    @forelse($archivedDamageRows as $row)
                                        <tr class="{{ auth()->id() === $row['user']?->id ? 'me-row' : ($loop->even ? 'bg_l' : '') }}">
                                            <td class="rank-cell">{{ $loop->iteration }}</td>
                                            <td class="player-cell">
                                                @if($row['user'] !== null)
                                                    <span class="player-row">
                                                        <a href="#" class="icon-link" title="{{ __('Приватное сообщение') }}" onclick="clanBossInsertPrivate({{ \Illuminate\Support\Js::from($row['user']->name) }}); return false;"><img src="{{ asset('img/icon/users-arrow.gif') }}" width="12" height="10" border="0"></a>
                                                        <a href="{{ route('info.user', $row['user']->id) }}" class="player-name" onclick="clanBossOpenWindow(this.href, 930, 700); return false;"><b class="kser0">{{ $row['user']->name }}&nbsp;[{{ $row['user']->player?->lvl ?? '?' }}]</b></a>
                                                        <a href="{{ route('info.user', $row['user']->id) }}" class="icon-link" title="{{ __('Информация о персонаже') }}" onclick="clanBossOpenWindow(this.href, 930, 700); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" border="0"></a>
                                                        @if(auth()->id() === $row['user']->id)<b class="grnn me-badge">(вы)</b>@endif
                                                    </span>
                                                @else
                                                    Игрок удалён
                                                @endif
                                            </td>
                                            <td align="center"><b class="redd">{{ number_format($row['damage'], 0, '', ' ') }}</b></td>
                                            <td align="center">{{ $row['hits'] }}</td>
                                            <td align="center"><b class="{{ $row['rewarded'] ? 'grnn' : 'redd' }}">{{ $row['rewarded'] ? 'Получена' : 'Не получена' }}</b></td>
                                        </tr>
                                    @empty
                                        <tr class="bg_l"><td colspan="5" align="center">{{ __('Участники не наносили урон.') }}</td></tr>
                                    @endforelse
                                    </tbody>
                                </table>
                                @include('clan::partials.boss-reward-receipts', ['rewardReceipts' => $archivedRewardReceipts, 'rewardsFinal' => true])
                            </div>
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

            <table width="100%" border="0" cellspacing="0" cellpadding="0" id="clan-boss-history" style="margin-top: 14px;">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                <td align="center" class="tbl-usi_label-center">{{ __('История походов') }}</td>
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 6px 4px;">
                            <table class="history-tbl" cellspacing="0" cellpadding="0">
                                <thead><tr><th>{{ __('Данж') }}</th><th>{{ __('Начат') }}</th><th>{{ __('Завершён') }}</th><th>{{ __('Итог') }}</th><th>{{ __('Результаты') }}</th></tr></thead>
                                <tbody>
                                @forelse($archiveRuns as $pastRun)
                                    <tr class="{{ $loop->even ? 'bg_l' : '' }}">
                                        <td><b>{{ $pastRun->dungeon?->name ?? 'Данж удалён' }}</b></td>
                                        <td class="archive-meta">{{ $pastRun->started_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                        <td class="archive-meta">{{ $pastRun->finished_at?->format('d.m.Y H:i') ?? '—' }}</td>
                                        <td><b class="archive-status {{ $pastRun->status === \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun::STATUS_COMPLETED ? 'archive-status--completed' : 'archive-status--expired' }}">{{ $pastRun->status === \App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun::STATUS_COMPLETED ? 'Победа' : 'Время вышло' }}</b></td>
                                        <td align="center">
                                            <span class="butt1 pointer"><span>
                                                <input type="button" value="{{ __('Посмотреть') }}" onclick="window.location.href = @js(route('clan.boss', ['run' => $pastRun->id]))">
                                            </span></span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg_l"><td colspan="5" align="center">{{ __('Завершённых походов пока нет.') }}</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                            @if($archiveRuns->hasPages())
                                <div style="margin-top: 7px;">{{ $archiveRuns->links() }}</div>
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

@php
    $clanBossTimerWords = [
        'dayOne' => __('день'), 'dayFew' => __('дня'), 'dayMany' => __('дней'),
        'hourOne' => __('час'), 'hourFew' => __('часа'), 'hourMany' => __('часов'),
        'minuteOne' => __('минута'), 'minuteFew' => __('минуты'), 'minuteMany' => __('минут'),
        'after' => __('через'), 'dayShort' => __('д'), 'hourShort' => __('ч'), 'minuteShort' => __('м'), 'secondShort' => __('сек.'),
    ];
@endphp
<script>
    function clanBossRenderSlider(slider, activeIndex) {
        var slides = Array.from(slider.querySelectorAll('.boss-carousel__slide'));
        if (!slides.length) return;

        activeIndex = Math.max(0, Math.min(activeIndex, slides.length - 1));
        slides.forEach(function (slide, index) {
            slide.classList.remove('boss-carousel__slide--center', 'boss-carousel__slide--left', 'boss-carousel__slide--right', 'boss-carousel__slide--hidden');
            if (index === activeIndex) slide.classList.add('boss-carousel__slide--center');
            else if (index === activeIndex - 1) slide.classList.add('boss-carousel__slide--left');
            else if (index === activeIndex + 1) slide.classList.add('boss-carousel__slide--right');
            else slide.classList.add('boss-carousel__slide--hidden');
        });

        slider.querySelector('.boss-run-arrow--prev').disabled = activeIndex === 0;
        slider.querySelector('.boss-run-arrow--next').disabled = activeIndex === slides.length - 1;
        slider.dataset.activeIndex = activeIndex;
    }

    function clanBossMoveSlider(button, direction) {
        var slider = button.closest('.boss-carousel');
        if (slider) clanBossRenderSlider(slider, Number(slider.dataset.activeIndex || 0) + direction);
    }

    document.querySelectorAll('.boss-carousel').forEach(function (slider) {
        clanBossRenderSlider(slider, Number(slider.dataset.startIndex || 0));
    });

    function clanBossOpenWindow(url, width, height) {
        window.open(url, '', 'width=' + width + ',height=' + height + ',location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
        return false;
    }

    // Вставляет шорткод to[NAME] в поле чата для приватного сообщения
    function clanBossInsertPrivate(name) {
        try {
            var chatFrame = window.parent.document.getElementById('chat-frame');
            var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
            bottomFrame.contentWindow.postMessage({ type: 'insertName', name: name }, '*');
        } catch (e) {}
        return false;
    }

    var clanBossTimerWords = @json($clanBossTimerWords);

    function clanBossRuPluralJs(n, one, few, many) {
        n = Math.abs(n) % 100;
        var n1 = n % 10;
        if (n > 10 && n < 20) return many;
        if (n1 > 1 && n1 < 5) return few;
        if (n1 === 1) return one;
        return many;
    }

    function clanBossFormatRemainingJs(totalSeconds) {
        if (totalSeconds <= 0) return null;
        var days = Math.floor(totalSeconds / 86400);
        var hours = Math.floor((totalSeconds % 86400) / 3600);
        var minutes = Math.floor((totalSeconds % 3600) / 60);
        var parts = [];
        if (days > 0) parts.push(days + ' ' + clanBossRuPluralJs(days, clanBossTimerWords.dayOne, clanBossTimerWords.dayFew, clanBossTimerWords.dayMany));
        if (days > 0 || hours > 0) parts.push(hours + ' ' + clanBossRuPluralJs(hours, clanBossTimerWords.hourOne, clanBossTimerWords.hourFew, clanBossTimerWords.hourMany));
        parts.push(minutes + ' ' + clanBossRuPluralJs(minutes, clanBossTimerWords.minuteOne, clanBossTimerWords.minuteFew, clanBossTimerWords.minuteMany));
        return parts.join(' ');
    }

    document.querySelectorAll('[data-countdown-until]').forEach(function (el) {
        var deadline = parseInt(el.dataset.countdownUntil, 10);
        var fallback = el.dataset.countdownFallback || '';
        function tick() {
            var left = deadline - Math.floor(Date.now() / 1000);
            var text = clanBossFormatRemainingJs(left);
            el.textContent = text !== null ? text : fallback;
        }
        tick();
        setInterval(tick, 1000);
    });

    document.querySelectorAll('[data-countdown-seconds-until]').forEach(function (el) {
        var deadline = parseInt(el.dataset.countdownSecondsUntil, 10);
        var fallback = el.dataset.countdownFallback || '';
        function tick() {
            var left = deadline - Math.floor(Date.now() / 1000);
            if (left <= 0) {
                el.textContent = fallback;
                return;
            }

            if (left < 60) {
                el.textContent = clanBossTimerWords.after + ' ' + left + ' ' + clanBossTimerWords.secondShort;
                return;
            }

            // Show a compact, rounded-up countdown (for example: 5ч 34м).
            left = Math.ceil(left / 60) * 60;
            var days = Math.floor(left / 86400);
            var hours = Math.floor((left % 86400) / 3600);
            var minutes = Math.floor((left % 3600) / 60);
            var parts = [];
            if (days > 0) parts.push(days + clanBossTimerWords.dayShort);
            if (hours > 0) parts.push(hours + clanBossTimerWords.hourShort);
            if (minutes > 0) parts.push(minutes + clanBossTimerWords.minuteShort);
            el.textContent = clanBossTimerWords.after + ' ' + parts.join(' ');
        }
        tick();
        setInterval(tick, 1000);
    });

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
