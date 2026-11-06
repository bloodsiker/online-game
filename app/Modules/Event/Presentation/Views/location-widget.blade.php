<style>
    .local-events { margin:8px 0; color:#5c351d; font:11px Tahoma,sans-serif; }
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
