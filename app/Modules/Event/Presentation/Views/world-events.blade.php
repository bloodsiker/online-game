<div class="world-events">
@forelse($worldEventCards as $card)
	    @php($event = $card['event'])
	    @php($run = $card['run'])
	    @php($stage = $card['stage'])
	    @php($targets = $stage?->targets ?? collect())
	    @php($primaryTarget = $targets->first())
	    @php($isCollect = $stage?->objective_type === \App\Modules\Event\Domain\Enums\WorldEventObjectiveType::COLLECT)
	    @php($isActiveRun = $run && $run->status === \App\Modules\Event\Infrastructure\Persistence\Models\WorldEventRun::STATUS_ACTIVE && $run->ends_at->isFuture())
    <table class="world-event-card" @if($run) id="event-run-{{ $run->id }}" data-event-run="{{ $run->id }}" data-event-stage="{{ $stage?->id }}" @endif width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr height="22">
            <td width="20" class="tbl-shp-sml lt"></td>
            <td class="tbl-shp-sml tt world-event-card__head"><b>{{ $event->title }}</b></td>
            <td width="20" class="tbl-shp-sml rt"></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg">
                <div class="world-event-card__body">
                <div class="world-event-card__image user-rewards__item-pic">
                    @if($event->image)<img class="user-rewards__item-image" src="{{ $event->image }}" alt="">@else
                        <img class="user-rewards__item-image" src="{{ $isCollect ? $primaryTarget?->item?->image : $primaryTarget?->monster?->image }}" alt="{{ $isCollect ? $primaryTarget?->item?->name : $primaryTarget?->monster?->name }}">
                    @endif
                </div>
                <div class="world-event-card__content{{ $run ? '' : ' world-event-card__content--future' }}">
                    <div class="world-event-card__content-main">
                    <h3 class="world-event-card__title">
                        <button
                            type="button"
                            class="world-event-card__favorite{{ $card['isFavorite'] ? ' world-event-card__favorite--active' : '' }}"
                            data-event-favorite
                            data-favorite="{{ $card['isFavorite'] ? '1' : '0' }}"
                            data-url="{{ route('events.favorite', $event) }}"
                            title="{{ $card['isFavorite'] ? 'Удалить из избранного' : 'Добавить в избранное' }}"
                            aria-label="{{ $card['isFavorite'] ? 'Удалить из избранного' : 'Добавить в избранное' }}"
                        ></button>
                        {{ $event->title }}
                    </h3>
                    @if($stage && $card['stageCount'] > 1)
                        <p class="world-event-card__stage"><b>Этап {{ $stage->position }}</b></p>
                    @endif
                    @if($targets->isNotEmpty())
                        <p class="world-event-card__targets"><b>{{ $isCollect ? 'Предметы для сбора' : 'Монстры этапа' }}:</b> {{ $targets->map(fn($target) => $isCollect ? $target->item?->name : $target->monster?->name)->filter()->join(', ') }}</p>
                        <div class="world-event-card__target-list">
                            @foreach($targets as $target)
                                @if($isCollect && $target->item)
                                    <div class="world-event-card__target">
                                        <div class="user-rewards__item-pic" data-id="{{ $target->item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" onclick="window.open('{{ route('items.info.share', ['id' => $target->item->id]) }}', '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');">
                                            <img class="user-rewards__item-image" src="{{ $target->item->image }}" alt="{{ $target->item->name }}">
                                        </div>
                                    </div>
                                @elseif(!$isCollect && $target->monster)
                                    <a class="world-event-card__target" href="{{ route('info.monster.catalog', ['id' => $target->monster->id]) }}" onclick="window.open(this.href, '', 'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">
                                        <span class="user-rewards__item-pic"><img class="user-rewards__item-image" src="{{ $target->monster->image }}" alt="{{ $target->monster->name }}"></span>
                                    </a>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    <div>{!! $event->description !!}</div>
                    <p class="world-event-card__meta"><b>Карта:</b>
                        @if($event->map?->slug)<a href="{{ route('map.public', ['slug' => $event->map->slug]) }}" target="_blank">{{ $event->map->name }}</a>@else {{ $event->map?->name }} @endif
                    </p>
                    <p class="world-event-card__locations"><b>Локации:</b> {{ $event->locations->isEmpty() ? 'случайные локации по всей карте' : $event->locations->map(fn($location) => $location->name.' ['.$location->id.']')->join(', ') }}</p>
                    @if($run)
                    <div class="world-event-card__influence">
                        Участвуя в этом событии, вы будете увеличивать ваше влияние в {{ $event->influence_name ?: ($event->influenceMap?->name ?? $event->map?->name) }}.<br>
                        Ваш текущий уровень влияния: <b data-event-influence>{{ $card['mapInfluence'] }}</b>.
                        <small>(+{{ $stage?->influence_per_item }} за {{ $isCollect ? 'предмет' : 'монстра' }})</small>
                    </div>
	                    @endif
	                    @if($isActiveRun)
	                        <p><b>{{ $isCollect ? 'Осталось собрать' : 'Осталось уничтожить' }}:</b> <span data-event-remaining>{{ max(0, $stage->global_limit - $run->collected_count) }}</span> из {{ $stage->global_limit }}</p>
                        <div class="world-event-progress"><div class="world-event-progress__label"><span>Общий прогресс этапа</span><span data-event-global-text>{{ $run->collected_count }} / {{ $stage->global_limit }}</span></div>
                            @include('event::progress-bar', ['percent' => $card['globalPercent'], 'title' => $run->collected_count.' из '.$stage->global_limit, 'kind' => 'global'])
                        </div>
                        <div class="world-event-progress"><div class="world-event-progress__label"><span>Ваш прогресс этапа</span><span data-event-player-text>{{ $card['progress'] }} / {{ $stage->player_limit }}</span></div>
                            @include('event::progress-bar', ['percent' => $card['playerPercent'], 'title' => $card['progress'].' из '.$stage->player_limit, 'kind' => 'player'])
                        </div>
	                        <div class="world-event-timer" title="Завершение: {{ $run->ends_at->format('d.m.Y H:i:s') }}">
	                            <span class="world-event-timer__label">До завершения</span>
	                            <span class="world-event-timer__value" data-event-countdown="{{ $run->ends_at->timestamp }}">--:--:--</span>
	                        </div>
	                    @elseif($run)
	                        <p><b>Завершено:</b> {{ ($run->finished_at ?? $run->ends_at)->format('d.m.Y H:i') }}</p>
	                    @endif
                    </div>
                    @if(!$run)
                    <div class="world-event-card__future-side">
                        <aside class="world-event-card__future-summary">
                            @if($event->next_start_at)
                                <div class="world-event-timer world-event-timer--start" title="Начало: {{ $event->next_start_at->format('d.m.Y H:i:s') }}">
                                    <span class="world-event-timer__label">До начала</span>
                                    <span class="world-event-timer__value" data-event-start-countdown="{{ $event->next_start_at->timestamp }}">--</span>
                                </div>
                            @else
                                <p class="world-event-card__future-limits"><b>Следующий запуск:</b> вручную.</p>
                            @endif
                            <p class="world-event-card__future-limits"><b>Общая цель этапа:</b> {{ $stage?->global_limit }}.<br><b>Личный лимит этапа:</b> {{ $stage?->player_limit }}.</p>
                        </aside>
                        <div class="world-event-card__influence">
                            Участвуя в этом событии, вы будете увеличивать ваше влияние в {{ $event->influence_name ?: ($event->influenceMap?->name ?? $event->map?->name) }}.<br>
                            Ваш текущий уровень влияния: <b data-event-influence>{{ $card['mapInfluence'] }}</b>.
                            <small>(+{{ $stage?->influence_per_item }} за {{ $isCollect ? 'предмет' : 'монстра' }})</small>
                        </div>
                    </div>
                    @endif
                </div>
                </div>
            </td>
            <td class="tbl-shp-sides rs">&nbsp;</td>
        </tr>
        <tr height="18"><td class="tbl-shp-sml lb"></td><td class="tbl-shp-sml bb"></td><td class="tbl-shp-sml rb"></td></tr>
    </table>
@empty
    <div style="text-align:center;padding:40px 20px;color:#685142;">
        {{ $mode === 'events_my' ? 'Вы ещё не добавили события в избранное.' : 'В этом разделе пока нет событий.' }}
    </div>
@endforelse
</div>
<script>
(function () {
    var favoriteCsrf = document.querySelector('meta[name="csrf-token"]').content;

    document.querySelectorAll('[data-event-favorite]').forEach(function (button) {
        button.addEventListener('click', function () {
            var favorite = button.dataset.favorite !== '1';
            button.disabled = true;
            fetch(button.dataset.url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': favoriteCsrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({favorite: favorite})
            })
                .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
                .then(function (payload) {
                    button.dataset.favorite = payload.favorite ? '1' : '0';
                    button.classList.toggle('world-event-card__favorite--active', payload.favorite);
                    button.title = payload.favorite ? 'Удалить из избранного' : 'Добавить в избранное';
                    button.setAttribute('aria-label', button.title);
                    if (!payload.favorite && @json($mode === 'events_my')) {
                        window.location.reload();
                    }
                })
                .catch(function () { window.alert('Не удалось изменить избранное. Попробуйте ещё раз.'); })
                .finally(function () { button.disabled = false; });
        });
    });

    function highlightLinkedEvent() {
        if (!/^#event-run-\d+$/.test(window.location.hash)) return;
        var card = document.querySelector(window.location.hash);
        if (!card) return;
        card.classList.remove('world-event-card--highlighted');
        window.requestAnimationFrame(function () {
            card.classList.add('world-event-card--highlighted');
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
        });
        window.setTimeout(function () { card.classList.remove('world-event-card--highlighted'); }, 4200);
    }

    highlightLinkedEvent();
    window.addEventListener('hashchange', highlightLinkedEvent);

    function updateCountdowns() {
        document.querySelectorAll('[data-event-countdown]').forEach(function (node) {
            var seconds = Math.max(0, Number(node.dataset.eventCountdown) - Math.floor(Date.now() / 1000));
            var hours = Math.floor(seconds / 3600);
            var minutes = Math.floor((seconds % 3600) / 60);
            var rest = seconds % 60;
            node.textContent = String(hours).padStart(2, '0') + ':' + String(minutes).padStart(2, '0') + ':' + String(rest).padStart(2, '0');
            if (seconds === 0 && !node.dataset.expired) {
                node.dataset.expired = '1';
                window.setTimeout(function () { window.location.reload(); }, 1000);
            }
        });
    }
    updateCountdowns();
    window.setInterval(updateCountdowns, 1000);

    function updateStartCountdowns() {
        document.querySelectorAll('[data-event-start-countdown]').forEach(function (node) {
            var seconds = Math.max(0, Number(node.dataset.eventStartCountdown) - Math.floor(Date.now() / 1000));
            var totalMinutes = Math.ceil(seconds / 60);
            var days = Math.floor(totalMinutes / 1440);
            var hours = Math.floor((totalMinutes % 1440) / 60);
            var minutes = totalMinutes % 60;
            var parts = [];
            if (days > 0) parts.push(days + 'д');
            if (hours > 0 || days > 0) parts.push(hours + 'ч');
            parts.push(minutes + 'м');
            node.textContent = parts.join(' ');
            if (seconds === 0 && !node.dataset.started) {
                node.dataset.started = '1';
                window.setTimeout(function () { window.location.reload(); }, 1000);
            }
        });
    }
    updateStartCountdowns();
    window.setInterval(updateStartCountdowns, 30000);

    @if($mode === 'events')
    var initialIds = Array.from(document.querySelectorAll('[data-event-run]')).map(function (node) { return Number(node.dataset.eventRun); });
    window.setInterval(function () {
        fetch(@json(route('events.state')), {headers: {'Accept': 'application/json'}})
            .then(function (response) { return response.ok ? response.json() : Promise.reject(); })
            .then(function (payload) {
                var events = payload.events || [];
                var ids = events.map(function (event) { return Number(event.runId); });
                if (ids.join(',') !== initialIds.join(',')) { window.location.reload(); return; }
                events.forEach(function (event) {
                    var card = document.querySelector('[data-event-run="' + event.runId + '"]');
                    if (!card) return;
                    if (Number(card.dataset.eventStage) !== Number(event.stageId)) { window.location.reload(); return; }
                    card.querySelector('[data-event-remaining]').textContent = event.remaining;
                    card.querySelector('[data-event-global-text]').textContent = event.collected + ' / ' + event.globalLimit;
                    card.querySelector('[data-event-player-text]').textContent = event.playerProgress + ' / ' + event.playerLimit;
                    card.querySelector('[data-event-influence]').textContent = event.mapInfluence;
                    card.querySelector('[data-progress-kind="global"] .rep-progress-bar__fill').style.width = event.globalPercent + '%';
                    card.querySelector('[data-progress-kind="global"] .rep-progress-bar__text').textContent = event.globalPercent + '%';
                    card.querySelector('[data-progress-kind="player"] .rep-progress-bar__fill').style.width = event.playerPercent + '%';
                    card.querySelector('[data-progress-kind="player"] .rep-progress-bar__text').textContent = event.playerPercent + '%';
                });
            })
            .catch(function () {});
    }, 10000);
    @endif
})();
</script>
