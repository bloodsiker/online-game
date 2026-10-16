<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('interface::partials.game-translations')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Бой с клановым боссом') }}</title>
    @include('battle::partials.fight_styles')
</head>
<body data-sound-context="battle" data-sound-battle="{{ $battle->status->isActive() ? $battle->id : '' }}">
<div class="battle-layout">
    @if($battle->status->isActive())
        <div class="battle-col-actions">
            @if($randomAttackedMonster?->locationMonster)
                <p>
                    <u><b>{{ __('Раунд N') }} {{ $battle->rounds + 1 }}</b></u> —
                    <a href="{{ route('info.monster', ['id' => $randomAttackedMonster->locationMonster->id]) }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">
                        {{ $randomAttackedMonster->locationMonster->monster->name }}
                    </a>
                    {{ $randomAttackedMonster->locationMonster->monster->lvl }}
                    ({{ $randomAttackedMonster->locationMonster->hp_now }}/{{ $randomAttackedMonster->locationMonster->hp_max }})
                </p>
                @include('battle::partials.action_panel')
            @else
                <p><a href="{{ route('clan_boss', ['id' => $lair->id]) }}">{{ __('Босс побеждён. Вернуться к походу.') }}</a></p>
            @endif
        </div>

        <div class="battle-col-log">
            @if($round)
                <p>
                    <u><b>{{ __('Раунд N') }} {{ $round->round_number }}</b></u>
                    @if($round->locationMonster?->monster)
                        — <a href="{{ route('info.monster', ['id' => $round->locationMonster->id]) }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $round->locationMonster->monster->name }}</a>
                        {{ $round->locationMonster->monster->lvl }}
                    @endif
                </p>
                {!! \App\Modules\Battle\Application\Services\BattleLogTranslator::render($round->action) !!}
                {!! \App\Modules\Battle\Application\Services\BattleLogTranslator::render($fightDTO?->getSideLog() ?? '') !!}
            @endif
        </div>

        <aside class="battle-col-participants">
            @include('battle::partials.side_panel')
        </aside>
    @else
        <div class="battle-col-finished">
            @if($lastRound)
                <p>
                    <u><b>{{ __('Раунд N') }} {{ $lastRound->round_number }}</b></u>
                    @if($lastRound->locationMonster?->monster)
                        — <a href="{{ route('info.monster', ['id' => $lastRound->locationMonster->id]) }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">{{ $lastRound->locationMonster->monster->name }}</a>
                        {{ $lastRound->locationMonster->monster->lvl }}
                    @endif
                </p>
                {!! \App\Modules\Battle\Application\Services\BattleLogTranslator::render($lastRound->action) !!}
            @endif
            <p><a href="{{ route('clan_boss', ['id' => $lair->id]) }}" id="finish-fight">{{ __('Вернуться к походу') }}</a> »</p>
        </div>
    @endif
</div>

<script>
    const hp = { current: {{ (int) $player->hp_now }}, max: {{ (int) $playerDecorator->getHpMax() }} };
    const mp = { current: {{ (int) $player->mp_now }}, max: {{ (int) $playerDecorator->getMpMax() }} };
    const experience = {{ (float) $player->getPercentExp() }};
    const lvl = {{ (int) $player->lvl }};
    const money = {{ (int) $player->user->money }};
    const diamond = {{ (int) $player->user->diamond }};
    const playerPayload = { hp, mp, experience, lvl, money, diamond };
    @if($fightDTO)
        playerPayload.appliedEffects = @json($fightDTO->getPlayerEffects());
    @endif

    function sendClanBossPlayerState() {
        parent.sendToFrame('character-frame', playerPayload);
    }
    sendClanBossPlayerState();

    function actionAttack(action) {
        if (typeof parent.queueAttack === 'function') return parent.queueAttack(action);
        if (parent.isCooldown || !window.battleTarget) return;
        if (parent.attackMonster(window.battleTarget.battleId, window.battleTarget.monsterId, action) !== false) parent.startCooldown();
    }

    window.addEventListener('message', function (event) {
        if (!event.data?.hp || !event.data?.mp) return;

        hp.current = event.data.hp.current;
        hp.max = event.data.hp.max;
        mp.current = event.data.mp.current;
        mp.max = event.data.mp.max;

        const hpPercent = hp.max > 0 ? Math.round(hp.current / hp.max * 100) : 0;
        const mpPercent = mp.max > 0 ? Math.round(mp.current / mp.max * 100) : 0;
        const hpFill = document.querySelector('.act-stat-hp');
        const mpFill = document.querySelector('.act-stat-mp');
        if (hpFill) hpFill.style.width = hpPercent + '%';
        if (mpFill) mpFill.style.width = mpPercent + '%';

        const values = document.querySelectorAll('.act-stat-val');
        if (values[0]) values[0].textContent = hp.current + '/' + hp.max;
        if (values[1]) values[1].textContent = mp.current + '/' + mp.max;

        const currentPlayer = document.getElementById('bp-unit-me');
        if (currentPlayer) {
            const fills = currentPlayer.querySelectorAll('.bp-hp-fill, .bp-mp-fill');
            const texts = currentPlayer.querySelectorAll('.bp-hp-text');
            if (fills[0]) fills[0].style.width = hpPercent + '%';
            if (fills[1]) fills[1].style.width = mpPercent + '%';
            if (texts[0]) texts[0].textContent = hp.current + '/' + hp.max;
            if (texts[1]) texts[1].textContent = mp.current + '/' + mp.max;
        }
    });

    @if($randomAttackedMonster?->locationMonster && $battle->status->isActive())
        if (window.battleTarget) {
            window.battleTarget.attackUrlTemplate = @js(route('fight.clan-boss.attack', [
                'id' => $battle->id,
                'monsterId' => '__MONSTER__',
                'action' => '__ACTION__',
            ]));
        }
        try {
            const chatFrame = window.parent.document.getElementById('chat-frame');
            const chatLog = chatFrame?.contentWindow?.frames['chat_log'];
            chatLog?.addBattleEntry?.({
                battleId: {{ $battle->id }},
                battleUrl: @js(route('fight.log', ['id' => $battle->id])),
                monsterName: @js($randomAttackedMonster->locationMonster->monster->name),
            });
        } catch (error) {}
    @endif
</script>
</body>
</html>
