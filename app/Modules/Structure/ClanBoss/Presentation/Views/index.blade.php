<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $structure->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma, Geneva, sans-serif; font-size: 11px; }
        body { margin: 0; color: #461c0b; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .w100 { width: 100%; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p6h, .p6h td { padding-left: 6px; padding-right: 6px; }
        .p6v, .p6v td { padding-top: 6px; padding-bottom: 6px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2, .brd2 td { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .pointer, .pointer input { cursor: pointer; }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .grnn { color: #114d01 !important; }
        .redd { color: #a02020 !important; }

        .message-bar { margin-bottom: 8px; padding: 5px 9px; border: 1px solid; font-weight: bold; }
        .message-bar.success { color: #247327; border-color: #8eaa78; background: #eaf6df; }
        .message-bar.error { color: #a02020; border-color: #DB9F73; background: #fdeee9; }

        .section-title {
            display: block;
            box-sizing: border-box;
            width: 100%;
            margin: 10px 0 6px;
            padding: 4px 8px;
            border: 1px solid #c49485;
            background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat;
            color: #955c4a;
            font-weight: bold;
            font-size: 11px;
            line-height: 14px;
            text-align: left;
        }
        .section-title:first-child { margin-top: 0; }

        .empty-state {
            margin-bottom: 7px;
            padding: 7px 9px;
            border: 1px solid #DB9F73;
            background: #fff8ec url(/img/bg/info/bg_l.gif) repeat;
            color: #955c4a;
            font-style: italic;
        }

        .info-bar { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
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
            margin-bottom: 10px;
            padding: 8px 10px;
            border: 1px solid #DB9F73;
            background: #fffbd6 url(/img/bg/info/bg_l.gif) repeat;
        }
        .boss-card .boss-name {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            font-weight: bold;
            font-size: 12px;
            color: #461c0b;
        }
        .boss-card .boss-hp-numbers { color: #a02020; }
        .hpbar { height: 15px; background: #3a0d0d; border: 1px solid #000; border-radius: 3px; overflow: hidden; margin-bottom: 7px; }
        .hpbar > div { height: 100%; background: linear-gradient(#e74c3c, #922b21); transition: width .4s ease; }
        .boss-card .boss-sub-info { display: flex; flex-wrap: wrap; gap: 10px; color: #666; }
        .boss-card .boss-sub-info .lbl { color: #955c4a; }

        .action-row { margin-bottom: 10px; }

        table.history-tbl { border-top: 1px solid #DB9F73; border-left: 1px solid #DB9F73; border-collapse: separate; width: 100%; margin-bottom: 10px; }
        .history-tbl td, .history-tbl th { border-right: 1px solid #DB9F73; border-bottom: 1px solid #DB9F73; height: 22px; padding: 3px 6px; color: #461c0b; vertical-align: middle; }
        .history-tbl th { color: #955c4a; white-space: nowrap; background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat; }
        .history-tbl tr.bg_l td { background-image: url(/img/bg/info/bg_l.gif); }
        .history-tbl tr.me-row td { background-image: url(/img/bg/bg_l3.gif); cursor: pointer; }
        .history-tbl a { color: #461c0b; text-decoration: underline dotted; }
        .history-tbl a:hover { color: #a02020; }

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

        .reward-badge { font-weight: bold; }
        .reward-badge.given { color: #114d01; }
        .reward-badge.pending { color: #999; font-weight: normal; }

        .cd-available { color: #114d01; font-weight: bold; }
        .cd-waiting { color: #a05000; }

        .clan-boss-layout { display: flex; gap: 12px; align-items: flex-start; }
        .clan-boss-main { flex: 1 1 auto; min-width: 0; }
        .clan-boss-art {
            flex: 0 0 260px;
            width: 260px;
            height: 260px;
            padding: 17px;
            box-sizing: border-box;
            overflow: hidden;
            background: url({{ asset('main/images/user-reward-frame.png') }}) center / 100% 100% no-repeat;
        }
        .clan-boss-art img { display: block; width: 100%; height: 100%; object-fit: contain; }
        @media (max-width: 760px) {
            .clan-boss-layout { flex-direction: column; }
            .clan-boss-art { flex: 0 0 auto; width: 100%; }
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @php
                $btnLeft1 = 'img/bg/btn/btn-left1.gif'; $btnCenter1 = 'img/bg/btn/btn-cent1.gif'; $btnRight1 = 'img/bg/btn/btn-right1.gif';
                $btnLeft2 = 'img/bg/btn/btn-left2.gif'; $btnCenter2 = 'img/bg/btn/btn-cent2.gif'; $btnRight2 = 'img/bg/btn/btn-right2.gif';
            @endphp
            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
                <tbody>
                <tr height="21">
                    <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
                    <td align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 8px 6px; white-space: nowrap;">
                        <a href="{{ route('clan_boss', ['id' => $structure->id]) }}" class="btn_2" style="white-space: nowrap;">{{ $structure->name }}</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>
                    <td width="100%"></td>
                    <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
                    <td align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 8px 6px; white-space: nowrap;">
                        <a href="{{ route('location') }}" class="btn_1" style="white-space: nowrap;">Выход</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" align="left" style="padding: 10px 6px">

            <div class="clan-boss-layout">
            <div class="clan-boss-main">

            @if(session('success'))<div class="message-bar success">{{ session('success') }}</div>@endif
            @if(session('message'))<div class="message-bar error">{{ session('message') }}</div>@endif

            @if($dungeon === null)
                <div class="empty-state">Логово ещё не привязано к данжу. Попросите администрацию настроить структуру.</div>
            @elseif($clan === null)
                <div class="empty-state">Вы не состоите в клане. Клан-босс доступен только кланам.</div>
            @else
                @if($run === null)
                    <div class="info-bar">
                        <span class="info-pill"><span class="lbl">Окно похода:</span>&nbsp;<b class="redd">{{ $dungeon->time_limit_seconds !== null ? format_game_duration((int) $dungeon->time_limit_seconds) : 'без ограничения' }}</b></span>
                        <span class="info-pill"><span class="lbl">Требуемый уровень:</span>&nbsp;<b class="redd">{{ $dungeon->min_level }}</b></span>
                        @if($dungeon->entry_share_item_id !== null)
                            <span class="info-pill">Для открытия нужен ключ</span>
                        @endif
                    </div>

                    <div class="empty-state">
                        <b>Поход не открыт.</b> Боссы идут по очереди: от слабого к сильному.
                    </div>

                    <div class="action-row">
                        @if($canOpen)
                            <form method="post" class="clan-boss-csrf-refresh-form" action="{{ route('clan_boss.open', ['id' => $structure->id]) }}">
                                @csrf
                                <span class="butt1 pointer"><span><input value="Открыть поход" type="submit" class="redd" style="width:140px;"></span></span>
                            </form>
                        @else
                            <div class="empty-state" style="margin-bottom:0;">
                                @if(!$canLeadOpen)
                                    У вас нет прав начинать бой с клан-боссом.
                                @elseif(!$onLocation)
                                    Подойдите к логову, чтобы открыть поход.
                                @elseif(!$meetsLevel)
                                    Для открытия похода нужен уровень {{ $dungeon->min_level }}.
                                @endif
                            </div>
                        @endif
                    </div>
                @else
                    <div class="info-bar">
                        <span class="info-pill"><span class="lbl">Клан:</span>&nbsp;<b class="redd">{{ $clan->name }}</b></span>
                        <span class="info-pill"><span class="lbl">Этап:</span>&nbsp;<b class="redd">{{ $run->currentStage?->number ?? '—' }}</b>&nbsp;из&nbsp;<b class="redd">{{ $stages->count() }}</b></span>
                        <span class="info-pill"><span class="lbl">До конца похода:</span>&nbsp;<b class="redd">{{ $run->expires_at !== null ? format_game_duration((int) max(0, now()->diffInSeconds($run->expires_at, false))) : 'без ограничения' }}</b></span>
                        <span class="info-pill"><span class="lbl">Пакет:</span>&nbsp;<b class="redd">{{ $dungeon->clan_boss_hit_batch_size }}</b>&nbsp;ударов, восстановление через <b class="redd">{{ (int) ($dungeon->clan_boss_hit_batch_cooldown_seconds / 3600) }} ч.</b></span>
                    </div>

                    @if($currentState !== null && $currentState->bossInstance !== null && $bossMonster !== null)
                        @php
                            $hpNow = max(0, (int) $currentState->bossInstance->hp_now);
                            $hpMax = max(1, (int) $currentState->hp_max);
                            $pct = (int) round($hpNow / $hpMax * 100);
                        @endphp
                        <div class="boss-card">
                            <div class="boss-name">
                                <span>⚔ {{ $bossMonster->name }}</span>
                                <span class="boss-hp-numbers">{{ number_format($hpNow, 0, '', ' ') }} / {{ number_format($hpMax, 0, '', ' ') }} HP ({{ $pct }}%)</span>
                            </div>
                            <div class="hpbar"><div style="width: {{ $pct }}%"></div></div>
                            <div class="boss-sub-info">
                                <span><span class="lbl">Фаза:</span>&nbsp;<b>{{ $currentState->bossInstance->current_phase ?? 1 }}</b></span>
                                <span>
                                    <span class="lbl">Босс исчезнет:</span>&nbsp;
                                    @if($currentState->expires_at !== null)
                                        <b class="redd">через {{ format_game_duration((int) max(0, now()->diffInSeconds($currentState->expires_at, false))) }}</b>
                                    @else
                                        <b>по общему таймеру похода</b>
                                    @endif
                                </span>
                                @if($hitsLeft !== null)
                                    <span><span class="lbl">Ударов в пакете осталось:</span>&nbsp;<b class="redd">{{ $hitsLeft }}/{{ $run->dungeon->clan_boss_hit_batch_size }}</b></span>
                                    <span>
                                        <span class="lbl">Следующий пакет:</span>&nbsp;
                                        @if($personalAvailableAt !== null)
                                            <b class="redd" data-countdown-cooldown-until="{{ $personalAvailableAt->timestamp }}" data-countdown-fallback="доступен">через {{ format_game_duration((int) max(0, now()->diffInSeconds($personalAvailableAt, false))) }}</b>
                                        @else
                                            <b class="grnn">доступен</b>
                                        @endif
                                    </span>
                                @endif
                                @if($myContribution !== null)
                                    <span><span class="lbl">Ваш урон:</span>&nbsp;<b class="redd">{{ number_format($myContribution->damage, 0, '', ' ') }}</b>&nbsp;(ударов: <b>{{ $myContribution->hits }}</b>)</span>
                                @endif
                            </div>
                        </div>

                        <div class="action-row">
                            @if($onLocation && $currentState->isActive() && $canAttack)
                                <form method="post" action="{{ route('clan_boss.attack', ['id' => $structure->id]) }}">
                                    <span class="butt1 pointer"><span><input value="Атаковать босса" type="submit" class="redd" style="width:140px;"></span></span>
                                </form>
                            @elseif(!$onLocation)
                                <div class="empty-state" style="margin-bottom:0;">Подойдите к логову, чтобы атаковать.</div>
                            @endif
                        </div>
                    @else
                        <div class="empty-state"><b>Босс сейчас недоступен.</b></div>
                    @endif

                    <span class="section-title">Рейтинг урона</span>
                    @if(count($rating) > 0)
                        <table class="history-tbl" cellspacing="0" cellpadding="0">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th title="Ник члена клана">Ник игрока</th>
                                <th title="Суммарный урон по текущему боссу">Урон</th>
                                <th title="Суммарное число ударов">Удары</th>
                                <th title="Выдана ли награда за этап">Награда</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rating as $i => $row)
                                @php
                                    $isMe = auth()->id() === $row['user']?->id;
                                @endphp
                                <tr class="{{ $isMe ? 'me-row' : ($i % 2 === 1 ? 'bg_l' : '') }}">
                                    <td class="rank-cell rank-{{ $i + 1 }}">{{ $i + 1 }}</td>
                                    <td class="player-cell">
                                        @if($row['user'] !== null)
                                            <span class="player-row">
                                                <a href="#" class="icon-link" title="Приватное сообщение" onclick="clanBossInsertPrivate({{ \Illuminate\Support\Js::from($row['user']->name) }}); return false;"><img src="{{ asset('img/icon/users-arrow.gif') }}" width="12" height="10" border="0"></a>
                                                <a href="{{ route('info.user', $row['user']->id) }}" class="player-name" onclick="clanBossOpenWindow(this.href, 930, 700); return false;"><b class="kser0">{{ $row['user']->name }}&nbsp;[{{ $row['user']->player?->lvl ?? '?' }}]</b></a>
                                                <a href="{{ route('info.user', $row['user']->id) }}" class="icon-link" title="Информация о персонаже" onclick="clanBossOpenWindow(this.href, 930, 700); return false;"><img src="{{ asset('main/images/player_info.gif') }}" width="10" height="10" border="0"></a>
                                                @if($isMe)<b class="grnn me-badge">(вы)</b>@endif
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td align="center"><b class="redd">{{ number_format($row['damage'], 0, '', ' ') }}</b></td>
                                    <td align="center"><b>{{ $row['hits'] }}</b></td>
                                    <td align="center">
                                        <span class="reward-badge {{ $row['rewarded'] ? 'given' : 'pending' }}">{{ $row['rewarded'] ? 'выдана' : '—' }}</span>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state">Пока никто не бил босса.</div>
                    @endif
                @endif
            @endif

            @if($stages->count() > 0)
                <span class="section-title">Боссы похода</span>
                <table class="history-tbl" cellspacing="0" cellpadding="0">
                    <thead>
                    <tr>
                        <th>№</th>
                        <th>Этап</th>
                        <th>Ур.</th>
                        <th title="Время, за которое клан должен победить босса этапа">Окно босса</th>
                        <th title="Когда клан снова сможет сразиться с этим этапом">КД клана</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($stages as $stage)
                        @php
                            $cooldown = $cooldowns->get($stage->id);
                            $cooldownAt = $cooldown?->available_at;
                            $isOnCooldown = $cooldownAt !== null && now()->lt($cooldownAt);
                            $stageMonster = $stage->monsters->first()?->monster;
                        @endphp
                        <tr class="{{ $loop->odd ? 'bg_l' : '' }}">
                            <td align="center">{{ $stage->number }}</td>
                            <td>
                                @if($stageMonster !== null)
                                    <a href="{{ route('info.monster.catalog', ['id' => $stageMonster->id]) }}" onclick="return clanBossOpenWindow(this.href, 730, 850)">{{ $stage->name }}</a>
                                @else
                                    {{ $stage->name }}
                                @endif
                            </td>
                            <td align="center">{{ $stageMonster?->lvl ?? '—' }}</td>
                            <td align="center">{{ $stage->time_limit_seconds !== null ? format_game_duration((int) $stage->time_limit_seconds) : '—' }}</td>
                            <td align="center">
                                @if($isOnCooldown)
                                    <span class="cd-waiting" data-countdown-cooldown-until="{{ $cooldownAt->timestamp }}" data-countdown-fallback="доступен">через {{ format_game_duration((int) max(0, now()->diffInSeconds($cooldownAt, false))) }}</span>
                                @else
                                    <span class="cd-available">доступен</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            @endif

            </div>

            @if($structure->image)
                <div class="clan-boss-art">
                    <img src="{{ $structure->image }}" alt="{{ $structure->name }}">
                </div>
            @endif
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

<script>
    (function () {
        var tokenEndpoint = @json(route('player.csrf-token'));

        document.querySelectorAll('.clan-boss-csrf-refresh-form').forEach(function (form) {
            form.addEventListener('submit', async function (event) {
                if (form.dataset.csrfReady === '1') return;

                event.preventDefault();
                if (form.dataset.csrfRefreshing === '1') return;
                form.dataset.csrfRefreshing = '1';

                try {
                    var response = await fetch(tokenEndpoint, {
                        method: 'GET',
                        credentials: 'same-origin',
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    var payload = response.ok ? await response.json() : null;

                    if (!payload || !payload.token) {
                        window.location.reload();
                        return;
                    }

                    var tokenInput = form.querySelector('input[name="_token"]');
                    if (tokenInput) tokenInput.value = payload.token;

                    form.dataset.csrfReady = '1';
                    HTMLFormElement.prototype.submit.call(form);
                } catch (error) {
                    form.dataset.csrfRefreshing = '0';
                    window.location.reload();
                }
            });
        });
    })();

    function clanBossFormatCompactCooldown(seconds) {
        if (seconds <= 0) return null;
        if (seconds < 60) return seconds + 'с';

        // Round up so the displayed countdown never says the cooldown ended early.
        seconds = Math.ceil(seconds / 60) * 60;
        var days = Math.floor(seconds / 86400);
        var hours = Math.floor((seconds % 86400) / 3600);
        var minutes = Math.floor((seconds % 3600) / 60);
        var parts = [];
        if (days > 0) parts.push(days + 'д');
        if (hours > 0) parts.push(hours + 'ч');
        if (minutes > 0) parts.push(minutes + 'м');
        return parts.join(' ');
    }

    document.querySelectorAll('[data-countdown-cooldown-until]').forEach(function (element) {
        var deadline = parseInt(element.dataset.countdownCooldownUntil, 10);
        var fallback = element.dataset.countdownFallback || '';
        function tick() {
            var remaining = deadline - Math.floor(Date.now() / 1000);
            var formatted = clanBossFormatCompactCooldown(remaining);
            element.textContent = formatted !== null ? 'через ' + formatted : fallback;
        }
        tick();
        window.setInterval(tick, 1000);
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

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>

</body>
</html>
