<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('interface::partials.game-translations')
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ $csrfToken }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('Торговля между игроками') }}</title>
    <link rel="stylesheet" href="{{ asset('css/main.css') }}">
    {!! $playerStatsScript !!}
    <script>window.itemTooltip = {};</script>
    <script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
    <script src="{{ asset('js/player-trade-realtime.js') }}?v={{ filemtime(public_path('js/player-trade-realtime.js')) }}"></script>
    <style>
        body { margin:0; color:#461c0b; font:11px Tahoma,Arial,sans-serif; }
        button, input { font-family:Tahoma,Arial,sans-serif; }
        .player-trade { padding:6px 10px 10px; }
        .player-trade__nav { display:flex; justify-content:space-between; align-items:flex-start; min-height:24px; margin-bottom:7px; border-bottom:1px solid #c4a37e; }
        .player-trade__tab { position:relative; display:inline-block; height:21px; box-sizing:border-box; margin:0 19px; padding:0 5px 4px; border:0; color:#461c0b; background:url('{{ asset('img/bg/btn/btn-cent1.gif') }}') repeat-x; font-size:11px; line-height:18px; font-weight:bold; white-space:nowrap; cursor:pointer; }
        .player-trade__tab::before, .player-trade__tab::after { position:absolute; top:0; width:19px; height:21px; content:''; }
        .player-trade__tab::before { left:-19px; background:url('{{ asset('img/bg/btn/btn-left1.gif') }}'); }
        .player-trade__tab::after { right:-19px; background:url('{{ asset('img/bg/btn/btn-right1.gif') }}'); }
        .player-trade__tab.is-active { color:#fcf5b7; background-image:url('{{ asset('img/bg/btn/btn-cent2.gif') }}'); cursor:default; }
        .player-trade__tab.is-active::before { background-image:url('{{ asset('img/bg/btn/btn-left2.gif') }}'); }
        .player-trade__tab.is-active::after { background-image:url('{{ asset('img/bg/btn/btn-right2.gif') }}'); }
        .player-trade__notice { margin:5px 0; color:#754c2b; text-align:center; }
        .player-trade__notice:empty { display:none; }
        .player-trade__notice.is-error { color:#a3261b; font-weight:bold; }
        .player-trade__proximity-notice { margin:8px 0; padding:9px 12px; border:1px solid #b06b50; background:#fff0d9; color:#a3261b; font-weight:bold; text-align:center; }
        .player-trade__proximity-notice[hidden] { display:none; }
        .player-trade.is-blocked .player-trade__inventory-list { opacity:.55; }
        .player-trade.is-blocked .player-trade__inventory-item { cursor:default; }
        .player-trade__layout { display:grid; grid-template-columns:minmax(220px,1fr) 240px 240px; gap:24px; min-width:760px; align-items:stretch; }
        .player-trade__frame { width:100%; height:100%; table-layout:fixed; border-collapse:separate; border-spacing:0; }
        .player-trade__frame > tbody > tr > td:first-child, .player-trade__frame > tbody > tr > td:last-child { width:20px; }
        .player-trade__frame-middle { height:100%; }
        .player-trade__frame-content { padding:7px 2px 8px; }
        .player-trade__label { margin:0 auto; width:auto; max-width:100%; white-space:nowrap; }
        .player-trade__label img { display:block; }
        .player-trade__label .tbl-usi_label-center { padding-left:0; padding-right:0; }
        .player-trade__label span { display:block; max-width:140px; overflow:hidden; text-overflow:ellipsis; }
        .player-trade__inventory-head { margin:0 0 12px; padding:3px 8px; border:1px solid #d3b692; background:rgba(255,248,220,.3); font-weight:bold; }
        .player-trade__inventory-list { display:grid; grid-template-columns:repeat(auto-fill,68px); gap:2px; align-content:start; height:calc(100dvh - 117px); min-height:280px; overflow-y:auto; padding:2px 4px 2px 2px; scrollbar-width:thin; }
        .player-trade__inventory-item { position:relative; display:block; width:68px; height:71px; cursor:pointer; }
        .player-trade__inventory-item[hidden] { display:none; }
        .player-trade__inventory-item > input[type=checkbox] { position:absolute; width:1px; height:1px; opacity:0; }
        .player-trade__inventory-item > img { position:absolute; top:5px; left:6px; z-index:1; display:block; width:60px; height:60px; object-fit:cover; }
        .player-trade__inventory-item::after { position:absolute; inset:0; z-index:2; width:72px; height:71px; content:''; pointer-events:none; background:url('{{ asset('main/images/user-reward-frame.png') }}') 0 0 no-repeat; }
        .player-trade__inventory-item:has(input:checked) { outline:2px solid #8d2d18; outline-offset:-2px; }
        .player-trade__inventory-item:focus-within { outline:2px solid #8d2d18; }
        .player-trade__item-count { position:absolute; z-index:3; bottom:8px; left:8px; width:34px; height:12px; padding:0; box-sizing:content-box; border:1px solid #6f4a24; color:#f6d9a6; background:#6e534c; text-align:center; font:700 10px Tahoma; }
        .player-trade__offer { display:grid; grid-template-columns:repeat(3,60px); grid-auto-rows:60px; gap:3px; justify-content:center; margin:0 0 12px; }
        .player-trade__offer-slot { position:relative; box-sizing:border-box; width:60px; height:60px; border:0; padding:0; background:url('{{ asset('img/bg/empty_slot.gif') }}') center/100% 100% no-repeat; box-shadow:0 0 0 1px rgba(134,100,62,.35); }
        .player-trade__offer-slot img { display:block; width:60px; height:60px; object-fit:contain; }
        button.player-trade__offer-slot { cursor:pointer; }
        .player-trade__offer-count { position:absolute; left:2px; bottom:2px; padding:0 3px; background:rgba(67,40,20,.75); color:#fff6d4; font-size:10px; font-weight:bold; }
        .player-trade__money-box, .player-trade__summary { width:100%; border-collapse:collapse; margin-top:4px; font-size:11px; }
        .player-trade__money-box td, .player-trade__summary td { border:1px solid #d3b692; padding:3px 5px; text-align:left; }
        .player-trade__money-box .player-trade__money-title { font-weight:normal; }
        .player-trade__money-line { display:flex; align-items:center; justify-content:space-between; gap:5px; }
        .player-trade__money-value { display:inline-flex; align-items:center; gap:3px; }
        .player-trade__money-value img { width:11px; height:11px; vertical-align:middle; }
        .player-trade__money-input { width:85px; max-width:100%; height:18px; box-sizing:border-box; border:1px solid #ae8a61; background:#fff9e8; color:#461c0b; text-align:right; font-size:11px; }
        .player-trade__summary th { width:42%; padding:3px 5px; border:1px solid #d3b692; text-align:left; }
        .player-trade__ready { color:#a32b1b; font-weight:bold; }
        .player-trade__ready.is-ready { color:#24762d; }
        .player-trade__actions { display:flex; justify-content:center; gap:4px; margin-top:8px; }
        .player-trade__waiting, .player-trade__finished { max-width:650px; margin:24px auto; text-align:center; }
        .player-trade__waiting p, .player-trade__finished p { margin:8px; line-height:1.5; }
        .player-trade__empty { color:#8b765f; font-style:italic; }
        .player-trade__inventory-list > .player-trade__empty { grid-column:1/-1; padding:10px; }
        .player-trade__quantity-modal { width:420px; max-width:calc(100vw - 20px); max-height:calc(100dvh - 20px); margin:auto; padding:0; border:0; background:transparent; color:#461c0b; font:11px Tahoma,Arial,sans-serif; }
        .player-trade__quantity-modal::backdrop { background:rgba(0,0,0,.45); }
        .player-trade__quantity-modal .popup_global_close_btn { border:0; padding:0; background-color:transparent; }
        .player-trade__quantity-content { padding:14px 12px 10px; text-align:center; }
        .player-trade__quantity-item { display:flex; gap:12px; align-items:center; margin-bottom:14px; text-align:left; }
        .player-trade__quantity-item img { width:60px; height:60px; object-fit:contain; }
        .player-trade__quantity-controls { display:flex; align-items:center; justify-content:center; gap:7px; margin:9px 0; }
        .player-trade__quantity-controls input { width:65px; padding:4px; border:1px solid #ae8a61; background:#fff9e8; color:#461c0b; text-align:center; font-size:12px; }
    </style>
</head>
<body>
@php
    $initiatorName = $trade->initiator->player?->name ?? $trade->initiator->name;
    $targetName = $trade->target->player?->name ?? $trade->target->name;
    $isInitiator = $mySide === 'initiator';
    $otherSide = $isInitiator ? 'target' : 'initiator';
    $ownOffer = $trade->{$mySide.'_offer'} ?? [];
@endphp
<main class="player-trade{{ $canTrade ? '' : ' is-blocked' }}" id="player-trade"
      data-state-url="{{ route('player-trade.state', $trade->uuid) }}"
      data-offer-url="{{ route('player-trade.offer', $trade->uuid) }}"
      data-confirm-url="{{ route('player-trade.confirm', $trade->uuid) }}"
      data-cancel-url="{{ route('player-trade.cancel', $trade->uuid) }}"
      data-tooltip-url="{{ route('player-trade.tooltip', ['uuid' => $trade->uuid, 'item' => '__ITEM__']) }}"
      data-my-side="{{ $mySide }}"
      data-trade-uuid="{{ $trade->uuid }}"
      data-expires-at="{{ $expiresAt }}"
      data-can-trade="{{ $canTrade ? '1' : '0' }}">
    <nav class="player-trade__nav" aria-label="{{ __('Торговля') }}">
        <span class="player-trade__tab is-active">{{ __('Торговля') }}</span>
        @if(in_array($trade->status, ['pending', 'active'], true))
            <button class="player-trade__tab" type="button" data-trade-cancel>{{ __('Прервать торговлю') }}</button>
        @endif
    </nav>
    <div class="player-trade__notice" id="trade-notice" aria-live="polite"></div>
    @if(in_array($trade->status, ['pending', 'active'], true))
        <div class="player-trade__proximity-notice" id="trade-proximity-notice" role="alert" @if($canTrade) hidden @endif>{{ $tradeBlockMessage }}</div>
    @endif

    @if($trade->status === 'pending')
        <section class="player-trade__waiting">
            @component('player-trade::partials.panel', ['title' => __('Ожидаем ответа игрока')])
                <p>{{ __('Предложение отправлено игроку :name. Окно обновится автоматически после принятия.', ['name' => $targetName]) }}</p>
                <div class="player-trade__actions"><b class="butt2 pointer"><b><button type="button" data-trade-cancel>{{ __('Отменить предложение') }}</button></b></b></div>
            @endcomponent
        </section>
    @elseif($trade->status === 'active')
        <div class="player-trade__layout">
            <section class="player-trade__inventory" aria-label="{{ __('Рюкзак') }}">
                @component('player-trade::partials.panel', ['title' => __('Рюкзак')])
                    <div class="player-trade__inventory-head">{{ __('Предметов:') }} <span id="trade-inventory-count">{{ $inventory->count() }}</span></div>
                    <div class="player-trade__inventory-list" id="trade-inventory">
                        @forelse($inventory as $row)
                            @php($remainingCount = max(0, $row->count - ($ownOffer['items'][$row->id] ?? 0)))
                            <div class="player-trade__inventory-item" role="button" tabindex="0" data-inventory-item="{{ $row->id }}" data-id="{{ $row->item_id }}" data-name="{{ $row->item->itemInfo->name }}" data-image="{{ asset($row->item->itemInfo->image) }}" data-count="{{ $row->count }}" aria-label="{{ __('Добавить в предложение') }}: {{ $row->item->itemInfo->name }}" aria-disabled="{{ $canTrade ? 'false' : 'true' }}" @if($remainingCount === 0) hidden @endif>
                                <input type="checkbox" hidden data-backpack-id="{{ $row->id }}" @checked(isset($ownOffer['items'][$row->id])) @disabled(!$canTrade)>
                                <img src="{{ asset($row->item->itemInfo->image) }}" alt="{{ $row->item->itemInfo->name }}">
                                <span class="player-trade__item-count" data-inventory-remaining @if($remainingCount <= 1) hidden @endif>{{ $remainingCount }}</span>
                                <input type="hidden" value="{{ $ownOffer['items'][$row->id] ?? 1 }}" data-item-count="{{ $row->id }}">
                            </div>
                        @empty
                            <div class="player-trade__empty">{{ __('В рюкзаке нет предметов, которые можно передать.') }}</div>
                        @endforelse
                    </div>
                @endcomponent
            </section>
            @foreach([$mySide, $otherSide] as $side)
                @php($isOwn = $side === $mySide)
                <section class="player-trade__participant" aria-label="{{ $side === 'initiator' ? $initiatorName : $targetName }}">
                    @component('player-trade::partials.panel', ['title' => $side === 'initiator' ? $initiatorName : $targetName])
                        <div class="player-trade__offer" id="trade-offer-{{ $side }}">
                            @for($i = 0; $i < 9; $i++)<div class="player-trade__offer-slot"></div>@endfor
                        </div>
                        <table class="player-trade__money-box" border="0" cellspacing="0" cellpadding="0">
                            <tr><td colspan="2" class="player-trade__money-title">{{ __('Деньги:') }}</td></tr>
                            <tr><td colspan="2">
                                <div class="player-trade__money-line">
                                    @if($isOwn)
                                        <b class="butt2 pointer{{ $canTrade ? '' : ' disabled' }}"><b><button type="button" id="trade-confirm" data-confirmed="{{ $trade->{$mySide.'_confirmed'} ? '1' : '0' }}" @disabled(!$canTrade)>{{ $trade->{$mySide.'_confirmed'} ? __('Не готов') : __('Готов') }}</button></b></b>
                                        <span class="player-trade__money-value"><input class="player-trade__money-input" id="trade-money-input" type="number" min="0" max="{{ (int) auth()->user()->money }}" value="{{ $ownOffer['money'] ?? 0 }}" aria-label="{{ __('Монеты к передаче:') }}" @disabled(!$canTrade)><img src="{{ asset('img/icon/m_game.gif') }}" alt="{{ __('Монеты') }}"></span>
                                    @else
                                        <span class="player-trade__ready" id="trade-ready-{{ $side }}">{{ __('Не готов') }}</span>
                                        <span class="player-trade__money-value"><img src="{{ asset('img/icon/m_game.gif') }}" alt="{{ __('Монеты') }}"><span id="trade-money-{{ $side }}">0</span></span>
                                    @endif
                                </div>
                            </td></tr>
                        </table>
                        <table class="player-trade__summary" border="0" cellspacing="0" cellpadding="0">
                            <tr><th>{{ __('Сумма:') }}</th><td><span class="player-trade__money-value"><img src="{{ asset('img/icon/m_game.gif') }}" alt="{{ __('Монеты') }}"><span id="trade-total-{{ $side }}">{{ $isOwn ? ($ownOffer['money'] ?? 0) : 0 }}</span></span></td></tr>
                            @if($isOwn)
                                <tr><th>{{ __('В кошельке:') }}</th><td><span class="player-trade__money-value"><img src="{{ asset('img/icon/m_game.gif') }}" alt="{{ __('Монеты') }}">{{ number_format((int) auth()->user()->money, 0, '', ' ') }}</span></td></tr>
                                <tr><td colspan="2"><span class="player-trade__ready" id="trade-ready-{{ $side }}">{{ __('Не готов') }}</span></td></tr>
                            @endif
                        </table>
                        @if($isOwn)
                            <div class="player-trade__actions">
                                <b class="butt2 pointer"><b><button type="button" id="trade-refresh">{{ __('Обновить') }}</button></b></b>
                                <b class="butt2 pointer"><b><button type="button" data-trade-cancel>{{ __('Выйти') }}</button></b></b>
                            </div>
                        @endif
                    @endcomponent
                </section>
            @endforeach
        </div>
    @else
        <section class="player-trade__finished">
            @component('player-trade::partials.panel', ['title' => $trade->status === 'completed' ? __('Сделка завершена') : __('Сделка отменена')])
                <p>{{ $trade->status === 'completed' ? __('Предметы и монеты переданы участникам.') : __('Передачи предметов и монет не произошло.') }}</p>
            @endcomponent
        </section>
    @endif
</main>
@if($trade->status === 'active')
    @include('player-trade::partials.quantity-modal')
@endif
@if($trade->status === 'active' || $trade->status === 'pending')
<script>
(() => {
    const root = document.getElementById('player-trade');
    const csrf = document.querySelector('meta[name="csrf-token"]').content;
    const notice = document.getElementById('trade-notice');
    const t = (key) => typeof window.gameTranslate === 'function' ? window.gameTranslate(key) : key;
    let offerTimer = null;
    let busy = false;
    let offerDirty = false;
    let tradingBlocked = root.dataset.canTrade !== '1';
    let tooltipTarget = null;
    let quantityItem = null;
    const tooltipRequests = new Map();
    const quantityModal = document.getElementById('trade-quantity-modal');
    const quantityInput = document.getElementById('trade-quantity-input');
    let expiryTimer = null;
    let currentExpiry = null;

    function setNotice(message, error) {
        notice.textContent = message || '';
        notice.classList.toggle('is-error', Boolean(error));
    }

    async function request(url, method, payload) {
        const response = await fetch(url, {
            method,
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
            body: payload ? JSON.stringify(payload) : undefined,
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || t('Не удалось выполнить действие.'));
        return data;
    }

    function clearTooltip() {
        tooltipTarget = null;
        hideItemTooltip();
    }

    function attachTooltip(element) {
        element.addEventListener('mouseenter', async (event) => {
            if (itemTooltipTopWindow().gameSettings?.show_item_tooltips === false) return;
            tooltipTarget = element;
            const id = element.dataset.id;
            if (!window.itemTooltip[id]) {
                if (!tooltipRequests.has(id)) {
                    tooltipRequests.set(id, request(root.dataset.tooltipUrl.replace('__ITEM__', id), 'GET'));
                }
                try {
                    const data = await tooltipRequests.get(id);
                    window.itemTooltip[id] = data.tooltip;
                } catch (error) {
                    tooltipRequests.delete(id);
                    return;
                }
            }
            if (tooltipTarget === element && element.isConnected && typeof itemTooltipTopWindow().gebi === 'function') {
                showItemInfo(element, event, 2);
            }
        });
        element.addEventListener('mouseleave', () => {
            if (tooltipTarget === element) clearTooltip();
        });
    }

    function closeQuantityModal() {
        if (quantityModal?.open) quantityModal.close();
        quantityItem = null;
    }

    function setQuantity(value) {
        const maximum = Number(quantityInput.max);
        quantityInput.value = Math.min(maximum, Math.max(1, Math.trunc(Number(value) || 1)));
        const minus = document.getElementById('trade-quantity-minus');
        const plus = document.getElementById('trade-quantity-plus');
        minus.disabled = Number(quantityInput.value) <= 1;
        plus.disabled = Number(quantityInput.value) >= maximum;
        minus.closest('b.butt2').classList.toggle('disabled', minus.disabled);
        plus.closest('b.butt2').classList.toggle('disabled', plus.disabled);
    }

    function inventoryRemaining(element) {
        const checkbox = element.querySelector('[data-backpack-id]');
        const offered = checkbox.checked ? Number(element.querySelector('[data-item-count]').value) : 0;
        return Math.max(0, Number(element.dataset.count) - offered);
    }

    function updateInventoryRemainders() {
        let visibleCount = 0;
        document.querySelectorAll('[data-inventory-item]').forEach((element) => {
            const remaining = inventoryRemaining(element);
            element.hidden = remaining === 0;
            const counter = element.querySelector('[data-inventory-remaining]');
            counter.textContent = remaining;
            counter.hidden = remaining <= 1;
            if (remaining > 0) visibleCount++;
            if (element.hidden && tooltipTarget === element) clearTooltip();
        });
        const total = document.getElementById('trade-inventory-count');
        if (total) total.textContent = visibleCount;
    }

    function selectInventoryItem(element) {
        if (tradingBlocked) return;
        const checkbox = element.querySelector('[data-backpack-id]');
        const count = element.querySelector('[data-item-count]');
        const remaining = inventoryRemaining(element);
        if (remaining < 1) return;
        clearTooltip();
        if (remaining === 1) {
            count.value = (checkbox.checked ? Number(count.value) : 0) + 1;
            checkbox.checked = true;
            checkbox.dispatchEvent(new Event('change', { bubbles:true }));
            return;
        }

        quantityItem = element;
        document.getElementById('trade-quantity-name').textContent = element.dataset.name;
        document.getElementById('trade-quantity-image').src = element.dataset.image;
        document.getElementById('trade-quantity-max').textContent = remaining;
        quantityInput.max = remaining;
        setQuantity(1);
        quantityModal.showModal();
        quantityInput.focus();
        quantityInput.select();
    }

    function renderSide(side, data) {
        const items = document.getElementById('trade-offer-' + side);
        const money = document.getElementById('trade-money-' + side);
        const total = document.getElementById('trade-total-' + side);
        const ready = document.getElementById('trade-ready-' + side);
        if (!items || !ready) return;
        const signature = JSON.stringify(data.items);
        if (items.dataset.signature !== signature) {
            if (tooltipTarget && items.contains(tooltipTarget)) clearTooltip();
            items.replaceChildren();
            data.items.forEach((item) => {
                const own = side === root.dataset.mySide;
                const box = document.createElement(own ? 'button' : 'div');
                box.className = 'player-trade__offer-slot';
                box.dataset.id = item.item_id;
                box.setAttribute('aria-label', item.name + ' ×' + item.count);
                attachTooltip(box);
                if (own) {
                    box.type = 'button';
                    box.disabled = tradingBlocked;
                    box.addEventListener('click', () => {
                        const checkbox = document.querySelector('[data-backpack-id="' + item.backpack_id + '"]');
                        if (!checkbox) return;
                        checkbox.checked = false;
                        checkbox.dispatchEvent(new Event('change', { bubbles:true }));
                    });
                }
                const image = document.createElement('img');
                image.src = item.image;
                image.alt = item.name;
                const count = document.createElement('span');
                count.className = 'player-trade__offer-count';
                count.textContent = item.count;
                box.append(image, count);
                items.appendChild(box);
            });
            for (let index = data.items.length; index < 9; index++) {
                const empty = document.createElement('div');
                empty.className = 'player-trade__offer-slot';
                items.appendChild(empty);
            }
            items.dataset.signature = signature;
        }
        items.querySelectorAll('button').forEach((button) => { button.disabled = tradingBlocked; });
        if (money) money.textContent = Number(data.money).toLocaleString();
        if (total) total.textContent = Number(data.money).toLocaleString();
        ready.textContent = data.confirmed ? t('Готов к обмену') : t('Не готов');
        ready.classList.toggle('is-ready', data.confirmed);
        if (side === root.dataset.mySide) {
            const confirm = document.getElementById('trade-confirm');
            confirm.dataset.confirmed = data.confirmed ? '1' : '0';
            confirm.textContent = data.confirmed ? t('Не готов') : t('Готов');
            confirm.disabled = tradingBlocked;
            confirm.closest('b.butt2').classList.toggle('disabled', tradingBlocked);
        }
    }

    function applyState(data) {
        scheduleExpiry(data.expires_at);
        tradingBlocked = !data.can_trade;
        root.classList.toggle('is-blocked', tradingBlocked);
        const banner = document.getElementById('trade-proximity-notice');
        banner.textContent = data.block_message || '';
        banner.hidden = !tradingBlocked;
        document.querySelectorAll('#trade-inventory input, #trade-money-input').forEach((input) => {
            input.disabled = tradingBlocked;
        });
        document.querySelectorAll('[data-inventory-item]').forEach((item) => {
            item.setAttribute('aria-disabled', String(tradingBlocked));
            item.tabIndex = tradingBlocked ? -1 : 0;
        });
        if (tradingBlocked) {
            clearTimeout(offerTimer);
            closeQuantityModal();
            setNotice('');
        }
        renderSide('initiator', data.initiator);
        renderSide('target', data.target);
        if (!busy && !offerDirty) {
            const own = data[root.dataset.mySide];
            const quantities = new Map(own.items.map((item) => [Number(item.backpack_id), Number(item.count)]));
            document.querySelectorAll('[data-inventory-item]').forEach((item) => {
                const count = quantities.get(Number(item.dataset.inventoryItem)) || 0;
                item.querySelector('[data-backpack-id]').checked = count > 0;
                item.querySelector('[data-item-count]').value = count || 1;
            });
            const moneyInput = document.getElementById('trade-money-input');
            if (moneyInput) moneyInput.value = own.money;
            updateInventoryRemainders();
        }
    }

    function scheduleExpiry(expiresAt) {
        if (expiresAt === currentExpiry && expiryTimer !== null) return;
        clearTimeout(expiryTimer);
        currentExpiry = expiresAt;
        if (!expiresAt) return;
        expiryTimer = setTimeout(() => {
            expiryTimer = null;
            realtime.scheduleSync(0);
        }, Math.max(1000, Date.parse(expiresAt) - Date.now() + 250));
    }

    function consumeState(data) {
        if (data.status === 'completed' || data.status === 'cancelled') {
            window.location.reload();
            return;
        }
        if (data.status === 'active' && document.querySelector('.player-trade__waiting')) {
            window.location.reload();
            return;
        }
        applyState(data);
    }

    async function refresh() {
        if (busy) return;
        try {
            const data = await request(root.dataset.stateUrl, 'GET');
            if (busy) return;
            consumeState(data);
        } catch (error) { setNotice(error.message, true); }
    }

    function currentOffer() {
        const items = Array.from(document.querySelectorAll('[data-backpack-id]:checked')).map((checkbox) => ({
                backpack_id: Number(checkbox.dataset.backpackId),
                count: Number(document.querySelector('[data-item-count="' + checkbox.dataset.backpackId + '"]').value),
            }));
        return { items, money: Number(document.getElementById('trade-money-input').value || 0) };
    }

    async function saveOffer() {
        if (!document.getElementById('trade-inventory')) return;
        if (tradingBlocked) throw new Error(document.getElementById('trade-proximity-notice').textContent);
        const offer = currentOffer();
        const data = await request(root.dataset.offerUrl, 'PUT', offer);
        offerDirty = JSON.stringify(offer) !== JSON.stringify(currentOffer());
        consumeState(data);
        return data;
    }

    function queueOfferSave() {
        if (!document.getElementById('trade-inventory') || tradingBlocked) return;
        clearTimeout(offerTimer);
        offerTimer = setTimeout(async () => {
            if (busy) return;
            busy = true;
            try {
                await saveOffer();
                setNotice(t('Предложение обновлено. Подтверждение сброшено у обоих игроков.'));
                if (offerDirty) queueOfferSave();
            } catch (error) { setNotice(error.message, true); await refresh(); }
            finally { busy = false; realtime.scheduleSync(); }
        }, 350);
    }

    document.querySelectorAll('[data-inventory-item]').forEach((element) => {
        attachTooltip(element);
        element.addEventListener('click', () => selectInventoryItem(element));
        element.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                selectInventoryItem(element);
            }
        });
    });
    document.getElementById('trade-quantity-minus')?.addEventListener('click', () => setQuantity(Number(quantityInput.value) - 1));
    document.getElementById('trade-quantity-plus')?.addEventListener('click', () => setQuantity(Number(quantityInput.value) + 1));
    document.getElementById('trade-quantity-all')?.addEventListener('click', () => setQuantity(quantityInput.max));
    quantityInput?.addEventListener('change', () => setQuantity(quantityInput.value));
    document.querySelectorAll('[data-quantity-cancel]').forEach((button) => button.addEventListener('click', closeQuantityModal));
    quantityModal?.addEventListener('close', () => { quantityItem = null; });
    quantityModal?.addEventListener('click', (event) => {
        if (event.target === quantityModal) closeQuantityModal();
    });
    document.getElementById('trade-quantity-form')?.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!quantityItem || tradingBlocked) return;
        setQuantity(quantityInput.value);
        const checkbox = quantityItem.querySelector('[data-backpack-id]');
        const count = quantityItem.querySelector('[data-item-count]');
        const remaining = inventoryRemaining(quantityItem);
        if (remaining < 1) { closeQuantityModal(); return; }
        count.value = (checkbox.checked ? Number(count.value) : 0) + Math.min(remaining, Number(quantityInput.value));
        checkbox.checked = true;
        closeQuantityModal();
        checkbox.dispatchEvent(new Event('change', { bubbles:true }));
    });
    document.querySelectorAll('#trade-inventory [data-backpack-id], #trade-money-input').forEach((input) => {
        const markOfferDirty = () => { offerDirty = true; updateInventoryRemainders(); queueOfferSave(); };
        input.addEventListener('change', markOfferDirty);
        input.addEventListener('input', markOfferDirty);
    });
    document.getElementById('trade-confirm')?.addEventListener('click', async () => {
        if (busy || tradingBlocked) return;
        const confirmed = document.getElementById('trade-confirm').dataset.confirmed !== '1';
        busy = true;
        clearTimeout(offerTimer);
        try {
            if (offerDirty) await saveOffer();
            if (offerDirty) { queueOfferSave(); return; }
            const data = await request(root.dataset.confirmUrl, 'POST', { confirmed });
            consumeState(data);
        }
        catch (error) { setNotice(error.message, true); await refresh(); }
        finally { busy = false; realtime.scheduleSync(); }
    });
    document.getElementById('trade-refresh')?.addEventListener('click', async (event) => {
        const button = event.currentTarget;
        if (button.disabled) return;
        button.disabled = true;
        button.closest('b.butt2').classList.add('disabled');
        clearTimeout(offerTimer);
        try {
            while (busy) await new Promise((resolve) => setTimeout(resolve, 50));
            busy = true;
            clearTimeout(offerTimer);
            while (offerDirty && !tradingBlocked) await saveOffer();
            window.location.reload();
        } catch (error) { setNotice(error.message, true); }
        finally {
            busy = false;
            button.disabled = false;
            button.closest('b.butt2').classList.remove('disabled');
        }
    });
    document.querySelectorAll('[data-trade-cancel]').forEach((button) => button.addEventListener('click', async () => {
        try { await request(root.dataset.cancelUrl, 'POST'); window.location.reload(); }
        catch (error) { setNotice(error.message, true); }
    }));

    updateInventoryRemainders();
    const realtime = new PlayerTradeRealtime({ uuid: root.dataset.tradeUuid, sync: refresh });
    realtime.start();
    scheduleExpiry(root.dataset.expiresAt);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) realtime.scheduleSync(0);
    });
    window.addEventListener('pagehide', () => {
        realtime.stop();
        clearTimeout(offerTimer);
        clearTimeout(expiryTimer);
        expiryTimer = null;
    });
    window.addEventListener('pageshow', (event) => {
        if (event.persisted) {
            realtime.start();
            scheduleExpiry(currentExpiry);
        }
    });
})();
</script>
@endif
</body>
</html>
