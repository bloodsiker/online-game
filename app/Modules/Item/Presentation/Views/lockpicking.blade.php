<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Взлом замка</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    <style>
        body { margin: 0; padding: 12px; color: #955c4a; background: #ead8bc; font: 11px Tahoma, Arial, sans-serif; }
        .lock-frame { max-width: 520px; margin: 0 auto; }
        .lock-panel { width: 100%; border-collapse: collapse; border-spacing: 0; }
        .gp-frame-tl, .gp-frame-tr, .gp-frame-bl, .gp-frame-br { background: url('/img/bg/common-corners.png') no-repeat; font-size: 0; line-height: 0; }
        .gp-frame-t, .gp-frame-b { background: url('/img/bg/common-tb.png') repeat-x; font-size: 0; line-height: 0; }
        .gp-frame-l, .gp-frame-r { background: url('/img/bg/common-lr.png') repeat-y; font-size: 0; line-height: 0; }
        .gp-frame-tl { background-position: 0 0; }
        .gp-frame-tr { background-position: 100% 0; }
        .gp-frame-bl { background-position: 0 100%; }
        .gp-frame-br { background-position: 100% 100%; }
        .gp-frame-b { background-position: 0 100%; }
        .gp-frame-r { background-position: 100% 0; }
        .gp-frame-bg { padding: 12px 18px; background: url('/img/bg/common-bg.png'); text-align: center; }
        .lock-chest { text-align: center; }
        .lock-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('{{ asset('main/images/user-reward-frame.png') }}') no-repeat; }
        .lock-icon img { width: 60px; height: 60px; object-fit: contain; }
        .lock-name { color: #651a0c; font-size: 14px; font-weight: bold; }
        .lock-stats { margin-top: 4px; color: #5c4a37; line-height: 1.55; }
        .lockpick-choice { max-width: 430px; margin: 9px auto 0; padding: 7px 9px; border: 1px solid #c29a69; background: rgba(255, 242, 215, .62); text-align: center; }
        .lockpick-choice__title { margin-bottom: 6px; color: #5d230e; font-weight: bold; }
        .lockpick-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 6px; }
        .lockpick-card { position: relative; width: 62px; height: 62px; padding: 3px; color: #5b321d; border: 1px solid #b98b55; background: rgba(255, 246, 222, .92); box-shadow: inset 0 0 5px rgba(112, 65, 25, .18); cursor: pointer; font: 10px Tahoma, Arial, sans-serif; }
        .lockpick-card:hover { border-color: #7f4b20; background: #fff8e7; }
        .lockpick-card.selected { border: 2px solid #7d390e; padding: 2px; background: #f5ddb1; box-shadow: 0 0 5px rgba(91, 42, 10, .55), inset 0 0 5px rgba(112, 65, 25, .2); }
        .lockpick-card.unavailable { cursor: default; background: rgba(220, 211, 193, .8); }
        .lockpick-card.unavailable > img, .lockpick-card.unavailable > .lockpick-card__count { opacity: .42; filter: grayscale(.85); }
        .lockpick-card img { display: block; width: 54px; height: 54px; margin: 0 auto; object-fit: contain; }
        .lockpick-card__count { position: absolute; top: 3px; right: 4px; padding: 0 2px; color: #fff; background: rgba(67, 35, 14, .8); font-weight: bold; }
        .lock-difficulty { font-weight: bold; text-shadow: 0 1px rgba(255,255,255,.65); }
        .lock-difficulty--easy { color: #287a32; }
        .lock-difficulty--medium { color: #9b7300; }
        .lock-difficulty--hard { color: #b0520b; }
        .lock-difficulty--very-hard { color: #a11c16; }
        .lock-warning { margin-top: 10px; padding: 6px 8px; color: #762116; border: 1px solid #d0a472; background: rgba(255, 239, 213, .72); }
        .lock-progress { display: none; margin-top: 12px; }
        .lock-progress.active { display: block; }
        .lock-progress-label { margin-bottom: 2px; color: #5c4a37; font-weight: bold; }
        .rep-progress-bar { position: relative; width: 100%; height: 31px; margin: 4px 0 6px; overflow: hidden; }
        .rep-progress-bar__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; border-radius: 5px; background: url('{{ asset('img/progressbar/progress-bar-1-bg.png') }}') 0 -54px repeat-x; }
        .rep-progress-bar__fill { width: 0; height: 27px; margin-right: auto; margin-left: 0; background: url('{{ asset('img/progressbar/progress-bar-1-bg.png') }}') 0 -27px repeat-x; transform-origin: left center; }
        .rep-progress-bar__border { position: absolute; top: 0; left: 0; width: 100%; height: 31px; }
        .rep-progress-bar__border-left, .rep-progress-bar__border-right, .rep-progress-bar__border-center { height: 31px; background: url('{{ asset('img/progressbar/progress-bar-1-border.png') }}') no-repeat; }
        .rep-progress-bar__border-left, .rep-progress-bar__border-right { position: absolute; top: 0; width: 20px; }
        .rep-progress-bar__border-left { left: 0; }
        .rep-progress-bar__border-right { right: 0; background-position: 0 -31px; }
        .rep-progress-bar__border-center { margin: 0 20px; background-position: 0 -62px; background-repeat: repeat-x; }
        .rep-progress-bar__text { position: absolute; top: 0; left: 0; width: 100%; color: #fff; font-size: 11px; font-weight: bold; line-height: 31px; text-align: center; text-shadow: -1px 0 2px #444, 0 1px 2px #444, 1px 0 2px #444, 0 -1px 2px #444; }
        .lock-actions { margin-top: 12px; text-align: center; }
        .lock-actions .butt1 input { min-width: 150px; }
        .lock-result { display: none; margin-top: 10px; color: #5c351d; text-align: center; }
        .lock-result.visible { display: block; }
        .lock-result-block { padding: 8px; border: 1px solid #d0a472; background: rgba(255, 240, 208, .8); }
        .lock-result-block + .lock-result-block { margin-top: 7px; }
        .lock-result-title { color: #5d230e; font-weight: bold; }
        .lock-loot-grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; margin-top: 7px; }
        .lock-loot-item { position: relative; display: block; width: 72px; height: 71px; cursor: pointer; background-image: url('{{ asset('main/images/user-reward-frame.png') }}'); background-size: 72px 71px; background-repeat: no-repeat; }
        .lock-loot-item img { position: absolute; top: 5px; left: 6px; width: 60px; height: 60px; object-fit: contain; }
        .lock-loot-count { position: absolute; right: 5px; bottom: 4px; z-index: 1; min-width: 14px; padding: 0 2px; color: #fff; background: rgba(55, 25, 8, .82); font-weight: bold; line-height: 14px; text-align: center; }
        .lock-effect-card { position: relative; display: inline-flex; align-items: center; gap: 8px; margin-top: 7px; padding: 5px 9px; border: 1px solid #b88955; background: rgba(247, 221, 179, .8); text-align: left; }
        .lock-effect-card img { width: 40px; height: 40px; object-fit: contain; }
        .lock-effect-tooltip { position: absolute; z-index: 5; bottom: calc(100% + 6px); left: 50%; display: none; width: 230px; padding: 7px 9px; color: #f5e5bd; border: 1px solid #5f3a20; background: rgba(45, 26, 15, .96); box-shadow: 0 2px 7px rgba(0, 0, 0, .45); transform: translateX(-50%); text-align: left; }
        .lock-effect-card:hover .lock-effect-tooltip { display: block; }
        .lock-effect-tooltip b, .lock-effect-tooltip span { display: block; }
        .lock-effect-tooltip span { margin-top: 4px; color: #dcc9a8; }
        .lock-damage-block { color: #8f1710; border-color: #c77a68; background: rgba(248, 211, 194, .82); font-weight: bold; }
        .lock-no-lockpick { margin-top: 7px; color: #8f1710; font-weight: bold; }
        .lock-links { margin-top: 12px; text-align: center; }
        .lock-links a { color: #5f190d; }
        @if(request()->boolean('modal'))
        body { padding: 0; background: transparent; }
        .lock-frame { max-width: none; }
        .lock-links { display: none; }
        @endif
    </style>
</head>
<body>
<main class="lock-frame">
    <table class="lock-panel" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td width="8" height="10" class="gp-frame-tl"></td>
            <td height="10" class="gp-frame-t"></td>
            <td width="8" height="10" class="gp-frame-tr"></td>
        </tr>
        <tr>
            <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
            <td class="gp-frame-bg">
                <div class="lock-chest">
                    <span class="lock-icon"><img src="{{ $page['item']->itemInfo->image }}" alt=""></span><br>
                <div class="lock-name">{{ $page['item']->itemInfo->name }}</div>
                <div class="lock-stats">
                    Навык: <b>{{ $page['skillLevel'] }}</b>, сложность замка: <b>{{ $page['requiredSkill'] }}</b><br>
                    Сложность: <span id="lock-difficulty" class="lock-difficulty lock-difficulty--{{ $page['difficulty']['level'] }}">{{ $page['difficulty']['label'] }}</span>
                </div>
                </div>
                @if($page['lockpicks'] !== [])
                    <div class="lockpick-choice">
                        <div class="lockpick-choice__title">Выберите отмычку</div>
                        <div class="lockpick-grid" id="lockpick-grid">
                            @foreach($page['lockpicks'] as $lockpick)
                                <button type="button"
                                        class="lockpick-card{{ $lockpick['shareItemId'] === $page['selectedLockpickId'] ? ' selected' : '' }}{{ $lockpick['canUse'] ? '' : ' unavailable' }}"
                                        data-lockpick-id="{{ $lockpick['shareItemId'] }}"
                                        data-id="{{ $lockpick['shareItemId'] }}"
                                        data-can-use="{{ $lockpick['canUse'] ? '1' : '0' }}"
                                        aria-disabled="{{ $lockpick['canUse'] ? 'false' : 'true' }}"
                                        onmouseover="showItemInfo(this,event,2)"
                                        onmouseout="showItemInfo(this,event,0)">
                                    <img src="{{ $lockpick['image'] }}" alt="">
                                    <span class="lockpick-card__count">×<span>{{ $lockpick['count'] }}</span></span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($page['competitors'] > 0)
                    <div class="lock-warning" id="competitor-warning">Сундук уже пытается открыть {{ $page['competitors'] }} игрок(а). Вы всё равно можете начать взлом.</div>
                @else
                    <div class="lock-warning" id="competitor-warning" hidden></div>
                @endif
                @unless($page['hasLockpick'])
                    <div class="lock-warning">В рюкзаке нет доступной по уровню отмычки. Она расходуется только при неудаче.</div>
                @endunless
                <div class="lock-progress" id="lock-progress">
                    <div class="lock-progress-label">Вскрытие замка</div>
                    <div class="rep-progress-bar" title="Выполняется взлом">
                        <div class="rep-progress-bar__bg"><div class="rep-progress-bar__fill" id="lock-fill"></div></div>
                        <div class="rep-progress-bar__border">
                            <div class="rep-progress-bar__border-left"></div>
                            <div class="rep-progress-bar__border-right"></div>
                            <div class="rep-progress-bar__border-center"></div>
                        </div>
                        <div class="rep-progress-bar__text" id="lock-percent">0%</div>
                    </div>
                </div>
                <div class="lock-actions">
                    <span class="butt1 pointer" id="lock-start-action"><span><input value="Начать взлом" id="lock-start" type="button" class="grnn" @disabled(!$page['hasLockpick'])></span></span>
                    <span class="butt1 pointer" id="lock-cancel-action" style="display:none;"><span><input value="Отменить" id="lock-cancel" type="button" class="grnn"></span></span>
                    <span class="butt1 pointer" id="lock-close-action" style="display:none;"><span><input value="Закрыть" id="lock-close" type="button" class="grnn"></span></span>
                </div>
                <div class="lock-result" id="lock-result"></div>
                <div class="lock-links">
                    <a href="{{ route('backpack') }}">Рюкзак</a> · <a href="{{ route('location') }}" target="game">Локация</a>
                </div>
            </td>
            <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
        </tr>
        <tr>
            <td width="8" height="10" class="gp-frame-bl"></td>
            <td height="10" class="gp-frame-b"></td>
            <td width="8" height="10" class="gp-frame-br"></td>
        </tr>
        </tbody>
    </table>
</main>
{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
<script>
(function () {
    'use strict';
    const id = @js($page['item']->id);
    const urls = {
        start: @js(route('items.lockpick.start', ['id' => $page['item']->id])),
        complete: @js(route('items.lockpick.complete', ['id' => $page['item']->id])),
        cancel: @js(route('items.lockpick.cancel', ['id' => $page['item']->id]))
    };
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const el = {
        start: document.getElementById('lock-start'), cancel: document.getElementById('lock-cancel'),
        progress: document.getElementById('lock-progress'), fill: document.getElementById('lock-fill'),
        percent: document.getElementById('lock-percent'), result: document.getElementById('lock-result'),
        competitors: document.getElementById('competitor-warning'),
        startAction: document.getElementById('lock-start-action'),
        cancelAction: document.getElementById('lock-cancel-action'),
        close: document.getElementById('lock-close'),
        closeAction: document.getElementById('lock-close-action')
    };
    const lockpicks = @json($page['lockpicks']);
    const lockpickCards = Array.from(document.querySelectorAll('[data-lockpick-id]'));
    const difficulty = document.getElementById('lock-difficulty');
    let selectedLockpickId = @json($page['selectedLockpickId']);
    let attempt = @json($page['attempt'] ? [
        'started_at' => $page['attempt']->started_at->toIso8601String(),
        'completes_at' => $page['attempt']->completes_at->toIso8601String(),
    ] : null);
    let timer = null;
    let completing = false;
    let modalResizeFrame = null;

    function notifyModalSize() {
        if (! @json(request()->boolean('modal'))) return;

        try {
            cancelAnimationFrame(modalResizeFrame);
            modalResizeFrame = requestAnimationFrame(function () {
                const content = document.querySelector('.lock-frame');
                const contentHeight = content
                    ? Math.ceil(content.getBoundingClientRect().height)
                    : document.body.scrollHeight;
                window.parent.resizeLockpickingModal(contentHeight);
            });
        } catch (error) {
            // При отдельном открытии страница сохраняет обычную высоту.
        }
    }

    async function request(url, payload = null) {
        const response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: {
            'X-CSRF-TOKEN': csrf, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
            'Content-Type': 'application/json'
        }, body: payload === null ? null : JSON.stringify(payload) });
        const data = await response.json();
        if (!response.ok) throw data;
        return data;
    }
    function selectedLockpick() {
        return lockpicks.find(function (pick) { return String(pick.shareItemId) === String(selectedLockpickId); }) || null;
    }
    function renderLockpickDetails() {
        const pick = selectedLockpick();
        if (!pick) return;
        difficulty.textContent = pick.difficulty.label;
        difficulty.className = 'lock-difficulty lock-difficulty--' + pick.difficulty.level;
    }
    function selectLockpick(id) {
        const pick = lockpicks.find(function (candidate) { return String(candidate.shareItemId) === String(id); });
        if (!pick || pick.canUse !== true) return;
        selectedLockpickId = pick.shareItemId;
        lockpickCards.forEach(function (card) {
            card.classList.toggle('selected', String(card.dataset.lockpickId) === String(selectedLockpickId));
        });
        renderLockpickDetails();
    }
    lockpickCards.forEach(function (card) {
        card.addEventListener('click', function () { selectLockpick(card.dataset.lockpickId); });
    });
    function consumeSelectedLockpickInUi() {
        const pick = selectedLockpick();
        if (!pick) return;
        pick.count -= 1;
        const card = lockpickCards.find(function (candidate) { return String(candidate.dataset.lockpickId) === String(pick.shareItemId); });
        if (pick.count > 0) {
            card?.querySelector('.lockpick-card__count span')?.replaceChildren(String(pick.count));
            return;
        }
        const index = lockpicks.indexOf(pick);
        if (index >= 0) lockpicks.splice(index, 1);
        card?.remove();
        const next = lockpicks.find(function (candidate) { return candidate.canUse === true; });
        selectedLockpickId = next?.shareItemId ?? null;
        if (selectedLockpickId !== null) selectLockpick(selectedLockpickId);
    }
    renderLockpickDetails();
    function resultBlock(className = '') {
        const block = document.createElement('div');
        block.className = 'lock-result-block' + (className ? ' ' + className : '');
        el.result.appendChild(block);

        return block;
    }
    function message(text) {
        el.result.replaceChildren();
        const block = resultBlock();
        block.textContent = text || '';
        el.result.classList.add('visible');
        notifyModalSize();
    }
    function clearMessage() { el.result.textContent = ''; el.result.classList.remove('visible'); notifyModalSize(); }
    function renderSuccess(data) {
        el.result.replaceChildren();
        const block = resultBlock();
        const title = document.createElement('div');
        title.className = 'lock-result-title';
        title.textContent = Array.isArray(data.loot) && data.loot.length
            ? 'Сундук успешно открыт. Найдено:'
            : 'Сундук открыт, но он оказался пуст.';
        block.appendChild(title);

        if (Array.isArray(data.loot) && data.loot.length) {
            const grid = document.createElement('div');
            grid.className = 'lock-loot-grid';
            data.loot.forEach(function (item) {
                const icon = document.createElement('span');
                icon.className = 'lock-loot-item';
                icon.dataset.id = String(item.share_item_id);
                icon.title = item.name;
                icon.addEventListener('mouseenter', function (event) { showItemInfo(icon, event, 2); });
                icon.addEventListener('mouseleave', function (event) { showItemInfo(icon, event, 0); });

                const image = document.createElement('img');
                image.src = item.image;
                image.alt = item.name;
                icon.appendChild(image);

                if (item.count > 1 || item.reward_type === 'money') {
                    const count = document.createElement('span');
                    count.className = 'lock-loot-count';
                    count.textContent = item.reward_type === 'money'
                        ? '+' + Number(item.count).toLocaleString('ru-RU')
                        : '×' + item.count;
                    icon.appendChild(count);
                }
                grid.appendChild(icon);
            });
            block.appendChild(grid);
        }
        if (data.event_progress) {
            const progress = document.createElement('div');
            progress.className = 'lock-effect-card';
            progress.textContent = 'Событие: +' + data.event_progress.influence_awarded
                + ' влияния. Ваш прогресс: ' + data.event_progress.player
                + '/' + data.event_progress.limit + '.';
            block.appendChild(progress);
        }
        el.result.classList.add('visible');
        notifyModalSize();
    }
    function renderFailure(data) {
        el.result.replaceChildren();
        const summary = resultBlock();
        const title = document.createElement('div');
        title.className = 'lock-result-title';
        title.textContent = data.lockpick_broken === false
            ? 'Замок не поддался, но отмычка сохранилась.'
            : 'Замок не поддался, отмычка сломана.';
        summary.appendChild(title);

        if (data.trap_avoided === true) {
            const avoided = document.createElement('div');
            avoided.className = 'lock-effect-card';
            avoided.textContent = 'Ловушка обезврежена.';
            summary.appendChild(avoided);
        }

        if (data.trap_effect) {
            const effect = document.createElement('div');
            effect.className = 'lock-effect-card';
            if (data.trap_effect.image) {
                const image = document.createElement('img');
                image.src = data.trap_effect.image;
                image.alt = data.trap_effect.name;
                effect.appendChild(image);
            }
            const text = document.createElement('span');
            text.append('Получен эффект: ');
            const name = document.createElement('b');
            name.textContent = data.trap_effect.name;
            text.appendChild(name);
            effect.appendChild(text);

            const tooltip = document.createElement('span');
            tooltip.className = 'lock-effect-tooltip';
            const tooltipName = document.createElement('b');
            tooltipName.textContent = data.trap_effect.name;
            const description = document.createElement('span');
            description.textContent = data.trap_effect.description || 'Негативный эффект ловушки.';
            const duration = document.createElement('span');
            duration.textContent = 'Длительность: ' + data.trap_effect.duration_seconds + ' сек.';
            tooltip.append(tooltipName, description, duration);
            effect.appendChild(tooltip);
            summary.appendChild(effect);
        }

        if (data.trap_damage > 0) {
            const damage = resultBlock('lock-damage-block');
            damage.textContent = 'Получено повреждений: ' + data.trap_damage + ' HP';
        }
        if (data.has_lockpick !== true) {
            const noLockpick = document.createElement('div');
            noLockpick.className = 'lock-no-lockpick';
            noLockpick.textContent = 'Отмычек больше нет.';
            summary.appendChild(noLockpick);
        }
        el.result.classList.add('visible');
        notifyModalSize();
    }
    function synchronizePlayerState() {
        try {
            if (typeof window.parent.syncPlayerState === 'function') {
                window.parent.syncPlayerState();
                return;
            }
            if (window.opener && typeof window.opener.syncPlayerState === 'function') {
                window.opener.syncPlayerState();
            }
        } catch (error) {
            // Ближайший heartbeat всё равно синхронизирует состояние.
        }
    }
    function setRunning(running) {
        el.startAction.style.display = running ? 'none' : '';
        el.cancelAction.style.display = running ? '' : 'none';
        el.closeAction.style.display = 'none';
        el.progress.classList.toggle('active', running);
        lockpickCards.forEach(function (card) { card.disabled = running || card.dataset.canUse !== '1'; });
        notifyModalSize();
    }
    function setCompleted() {
        el.startAction.style.display = 'none';
        el.cancelAction.style.display = 'none';
        el.closeAction.style.display = '';
        el.progress.classList.remove('active');

        try {
            if (typeof window.parent.refreshGameFrameAfterLockpicking === 'function') {
                window.parent.refreshGameFrameAfterLockpicking();
            }
        } catch (error) {
            // При отдельном открытии список обновится при следующем переходе.
        }
        notifyModalSize();
    }
    function tick() {
        if (!attempt || completing) return;
        const start = Date.parse(attempt.started_at);
        const end = Date.parse(attempt.completes_at);
        const percent = Math.max(0, Math.min(100, (Date.now() - start) / Math.max(1, end - start) * 100));
        el.fill.style.width = percent + '%'; el.percent.textContent = Math.floor(percent) + '%';
        if (percent >= 100) complete();
    }
    async function complete() {
        if (completing) return;
        completing = true;
        try {
            const data = await request(urls.complete);
            attempt = null; clearInterval(timer); setRunning(false);
            if (data.status === 'failure') {
                if (data.lockpick_broken === true) consumeSelectedLockpickInUi();
                renderFailure(data);
                el.start.value = 'Повторить попытку';
                el.start.disabled = data.has_lockpick !== true;
                el.fill.style.width = '0%';
                el.percent.textContent = '0%';
                synchronizePlayerState();
            }
            if (data.status === 'success') {
                renderSuccess(data);
                setCompleted();
                if (data.money !== undefined) {
                    try {
                        window.top.sendToFrame('character-frame', { money: data.money }, window.location.origin);
                    } catch (error) {
                        console.error('Не удалось обновить баланс в character-frame:', error);
                    }
                }
            }
            if (data.status !== 'failure' && data.status !== 'success') {
                message(data.message);
            }
            if (data.status === 'taken') {
                el.start.disabled = true;
                setCompleted();
            }
            if (data.status === 'success') el.start.disabled = true;
        } catch (error) { clearInterval(timer); message(error.message || 'Не удалось завершить взлом.'); }
        finally { completing = false; }
    }
    function run() { setRunning(true); tick(); clearInterval(timer); timer = setInterval(tick, 100); }
    el.start.addEventListener('click', async function () {
        el.start.disabled = true;
        clearMessage();
        try {
            const data = await request(urls.start, { lockpick_share_item_id: Number(selectedLockpickId || 0) || null });
            attempt = { started_at: new Date().toISOString(), completes_at: data.completes_at };
            if (data.competitors > 0) { el.competitors.hidden = false; el.competitors.textContent = 'Сундук уже открывают. Победит первый успешный взлом.'; }
            run();
        } catch (error) { message(error.message || 'Не удалось начать взлом.'); el.start.disabled = false; }
    });
    el.cancel.addEventListener('click', async function () {
        el.cancel.disabled = true;
        try { const data = await request(urls.cancel); attempt = null; clearInterval(timer); setRunning(false); message(data.message); el.start.disabled = false; }
        catch (error) { message(error.message || 'Не удалось отменить взлом.'); }
        finally { el.cancel.disabled = false; }
    });
    el.close.addEventListener('click', function () {
        try {
            window.parent.closeLockpickingModal();
        } catch (error) {
            window.close();
        }
    });
    if (attempt) run();
    if (typeof ResizeObserver !== 'undefined') {
        const modalResizeObserver = new ResizeObserver(notifyModalSize);
        modalResizeObserver.observe(document.querySelector('.lock-frame'));
    }
    window.addEventListener('load', notifyModalSize);
    notifyModalSize();
})();
</script>
</body>
</html>
