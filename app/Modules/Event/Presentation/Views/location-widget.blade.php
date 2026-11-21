<style>
    .local-events { color:#5c351d; font:11px Tahoma,sans-serif; }
    @foreach(['tl' => 'no-repeat', 'tr' => 'no-repeat', 'bl' => 'no-repeat', 'br' => 'no-repeat', 't' => 'repeat-x', 'b' => 'repeat-x', 'l' => 'repeat-y', 'r' => 'repeat-y'] as $part => $repeat)
    .local-events .common-inset-{{ $part }} { background:url('{{ asset('main/images/common-inset-'.$part.'.png') }}') {{ $repeat }}; }
    @endforeach
    .local-events .common-inset-bg { background:#ffffd6; }
    .local-event { display:flex; align-items:center; gap:8px; padding:2px 8px; }
    .local-event + .local-event { border-top:1px solid #d8c29b; }
    .local-event__body { flex:1; min-width:0; line-height:1.35; }
    .local-event__title { color:#a12519; font-weight:bold; }
    .local-event__timer { text-align:right; min-width:64px; line-height:1.35; color:#80551f; }
    .local-event__timer b { font-size:11px; font-variant-numeric:tabular-nums; }
    .local-event__limit { color:#777044; font-style:italic; }
    .local-event-item { display:inline-block; margin-top:3px; padding:2px 5px; border:1px solid #c6a263; background:#fff2cf; border-radius:3px; color:#80551f; font:10px Tahoma,sans-serif; text-decoration:none; }
    .local-event-item[hidden] { display:none; }
    .loot-card--event { box-shadow:inset 0 0 0 1px #c6a263; }

    /* Лейба событий в левом верхнем углу; карточки — во всплывающей панели (hover / фокус / нажатие) */
    .event-beacon { position:fixed; top:4px; left:4px; z-index:900; }
    .event-beacon__button { display:block; padding:0; border:0; background:none; cursor:pointer; filter:drop-shadow(0 1px 2px rgba(0,0,0,.6)); }
    .event-beacon__button:focus-visible { outline:2px solid #ffd27a; outline-offset:2px; }
    /* Лента: золотая кайма, бордовое поле, «ласточкин хвост» по краям */
    .event-beacon__ribbon { position:relative; display:flex; align-items:center; gap:6px; height:24px; padding:0 18px;
        background:linear-gradient(180deg, #ffe9a6, #d9a441 50%, #8a5a1c);
        clip-path:polygon(0 0, 100% 0, calc(100% - 9px) 50%, 100% 100%, 0 100%, 9px 50%); }
    .event-beacon__ribbon::before { content:''; position:absolute; inset:2px; z-index:0;
        background:linear-gradient(180deg, #b8321f, #6e150d);
        clip-path:polygon(0 0, 100% 0, calc(100% - 8px) 50%, 100% 100%, 0 100%, 8px 50%); }
    .event-beacon__ribbon > * { position:relative; z-index:1; }
    .event-beacon__flag { color:#ffe9a6; font-size:13px; line-height:1; }
    .event-beacon__label { color:#fff3cf; font:bold 11px Tahoma,sans-serif; text-shadow:0 1px 1px #3b1a07; white-space:nowrap; }
    .event-beacon__count { min-width:16px; height:16px; padding:0 4px; box-sizing:border-box; border:1px solid #ffe9a6; border-radius:8px;
        background:#3b1a07; color:#ffe9a6; font:bold 10px/14px Tahoma,sans-serif; text-align:center; }
    .event-beacon__panel { display:none; position:absolute; top:100%; left:0; width:min(440px, calc(100vw - 8px));
        max-height:min(460px, calc(100vh - 40px)); overflow:auto; padding-top:2px; }
    /* Подсказка в стиле подсказки предмета: рамка из tbl-pop_*, фон #FBD4A4, полосы #F4BB8A */
    .event-tip__table { width:100%; background:#FBD4A4; border-radius:30px 30px 0 0; box-shadow:3px 3px 3px -1px rgba(0,0,0,.2); font-size:11px; }
    .event-tip__tl { width:14px; height:24px; background:url(/img/bg/item_info/tbl-pop_corner-top-left.gif) no-repeat; }
    .event-tip__t { height:24px; background:url(/img/bg/item_info/tbl-pop_top.gif); text-align:center; vertical-align:middle; color:#8b1a0f; }
    .event-tip__tr { width:14px; height:24px; background:url(/img/bg/item_info/tbl-pop_corner-top-right.gif) no-repeat; }
    .event-tip__l { width:14px; padding:0; background:url(/img/bg/item_info/tbl-pop_left.gif) repeat-y; }
    .event-tip__r { width:14px; padding:0; background:url(/img/bg/item_info/tbl-pop_right.gif) repeat-y; }
    .event-tip__bl { width:14px; height:5px; background:url(/img/bg/item_info/tbl-pop_corner-bottom-left.gif) no-repeat; }
    .event-tip__b { height:5px; background:url(/img/bg/item_info/tbl-pop_bottom.gif) repeat-x; }
    .event-tip__br { width:14px; height:5px; background:url(/img/bg/item_info/tbl-pop_corner-bottom-right.gif) no-repeat; }
    .event-tip__body { padding:3px 0; }
    .event-tip .local-event { padding:4px 7px; }
    .event-tip .local-event + .local-event { border-top:0; }
    .event-tip__row--dark { background:#F4BB8A; }
    .event-beacon:hover .event-beacon__panel,
    .event-beacon:has(:focus-visible) .event-beacon__panel,
    .event-beacon.is-open .event-beacon__panel { display:block; }
    @media (prefers-reduced-motion: no-preference) {
        .event-beacon__button { animation:event-beacon-glow 2.4s ease-in-out infinite; }
        @keyframes event-beacon-glow {
            0%, 100% { filter:drop-shadow(0 1px 2px rgba(0,0,0,.6)); }
            50% { filter:drop-shadow(0 0 5px rgba(255,200,90,.95)); }
        }
    }
</style>
<div id="local-events-host">@include('event::location-cards')</div>
<script>
(() => {
    const host = document.getElementById('local-events-host');
    let events = @js($locationEvents), busy = false, dirty = false, stopped = false, debounce;
    const controller = new AbortController();
    function markItems() {
        document.querySelectorAll('[data-location-event-item]').forEach(marker => {
            const event = events.find(event => event.run_id === Number(marker.dataset.eventRun) && event.item_ids.includes(Number(marker.dataset.itemId)));
            marker.hidden = !event;
            marker.closest('.loot-card')?.classList.toggle('loot-card--event', !!event);
            if (event) {
                marker.textContent = '⚑ ' + @js(__('local-events.item'));
                marker.title = event.title + (event.limit_reached ? ' — ' + @js(__('local-events.limit')) : '');
                marker.href = event.url;
            }
        });
    }
    function tick() {
        host.querySelectorAll('[data-local-event-end]').forEach(card => {
            const seconds = Math.max(0, Math.ceil((Date.parse(card.dataset.localEventEnd) - Date.now()) / 1000));
            const hours = Math.floor(seconds / 3600);
            card.querySelector('[data-local-event-timer]').textContent = (hours ? hours + ':' : '')
                + String(Math.floor(seconds / 60) % 60).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
        });
        const active = events.filter(event => Date.parse(event.ends_at) > Date.now());
        if (active.length !== events.length) {
            events = active;
            host.querySelectorAll('[data-local-event-end]').forEach(card => { if (Date.parse(card.dataset.localEventEnd) <= Date.now()) card.remove(); });
            if (!events.length) host.replaceChildren();
            const count = host.querySelector('[data-local-events-count]');
            if (count) count.textContent = events.length;
            markItems(); schedule();
        }
    }
    async function refresh() {
        if (stopped) return;
        if (busy) { dirty = true; return; }
        busy = true;
        try {
            const response = await fetch(@js(route('events.location')), {credentials:'same-origin', headers:{Accept:'application/json'}, signal:controller.signal});
            if (!response.ok) return;
            const data = await response.json();
            if (stopped) return;
            events = data.events;
            host.innerHTML = data.html;
            tick(); markItems();
        } catch (error) {
            if (error.name !== 'AbortError') console.warn('Local events refresh failed', error);
        } finally {
            busy = false;
            if (dirty) { dirty = false; schedule(); }
        }
    }
    // Нажатие (телефон) открывает/закрывает панель; Escape и клик мимо — закрывают.
    function setOpen(open) {
        const beacon = host.querySelector('[data-event-beacon]');
        if (!beacon) return;
        beacon.classList.toggle('is-open', open);
        beacon.querySelector('.event-beacon__button').setAttribute('aria-expanded', String(open));
    }
    host.addEventListener('click', e => {
        if (e.target.closest('.event-beacon__button')) setOpen(!host.querySelector('[data-event-beacon]')?.classList.contains('is-open'));
    });
    document.addEventListener('click', e => { if (!host.contains(e.target)) setOpen(false); }, {signal: controller.signal});
    document.addEventListener('keydown', e => {
        if (e.key !== 'Escape' || !host.querySelector('[data-event-beacon]')) return;
        setOpen(false);
        if (host.contains(document.activeElement)) document.activeElement.blur();
    }, {signal: controller.signal});
    function schedule() { clearTimeout(debounce); if (!stopped) debounce = setTimeout(refresh, 150); }
    tick();
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', markItems, {once:true}); else markItems();
    const timer = setInterval(tick, 1000);
    const fallback = setInterval(refresh, 30000);
    window.addEventListener('focus', schedule);
    try { window.top.addEventListener('world-event-state-changed', schedule); } catch (error) {}
    window.addEventListener('pagehide', () => {
        stopped = true; controller.abort(); clearInterval(timer); clearInterval(fallback); clearTimeout(debounce);
        window.removeEventListener('focus', schedule);
        try { window.top.removeEventListener('world-event-state-changed', schedule); } catch (error) {}
    }, {once:true});
})();
</script>
