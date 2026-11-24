{{--
    Кнопка «глаз» в левом углу боковой карты: открывает публичную страницу текущей карты в новом окне
    (с якорем на локацию игрока). Боковая карта (maps/partials/live-players) кладёт в window.liveMapSlug
    slug карты; оболочка читает его при загрузке фрейма и по сообщению 'mapPlayersInfo'.
--}}
<style>
    .map-public-btn {
        position: absolute; top: 30px; left: 20px; z-index: 11;
        display: inline-flex; align-items: center; justify-content: center;
        width: 26px; height: 26px; padding: 0;
        border: 1px solid #7a4010; border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #f0cc88, #b07030);
        color: #4a1a00;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .45); cursor: pointer;
    }
    .map-public-btn:hover { background: radial-gradient(circle at 35% 30%, #f8dca0, #c08040); }
    .map-public-btn:focus-visible { outline: 2px solid #f0cc88; outline-offset: 1px; }
    .map-public-btn svg { width: 16px; height: 16px; display: block; }
    .map-public-btn[hidden], #map-wrapper.hidden ~ .map-public-btn { display: none; }
</style>

<button type="button" class="map-public-btn" id="map-public-btn" hidden
        title="{{ __('Открыть карту в новом окне') }}" aria-label="{{ __('Открыть карту в новом окне') }}" onclick="openPublicMap()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M1 12s4-7.5 11-7.5S23 12 23 12s-4 7.5-11 7.5S1 12 1 12z"></path>
        <circle cx="12" cy="12" r="3.2"></circle>
    </svg>
</button>

<script>
    (function () {
        const urlTemplate = @json(route('map.public', ['slug' => '__SLUG__']));
        const origin = window.location.origin;
        let slug = null;
        let locationId = {{ (int) (auth()->user()?->location_id ?? 0) }};

        const frame = () => document.getElementById('map-frame');

        function readSlug() {
            let value = null;
            try { value = frame()?.contentWindow?.liveMapSlug || null; } catch (e) { value = null; }
            slug = typeof value === 'string' && value !== '' ? value : null;
            const button = document.getElementById('map-public-btn');
            if (button) button.hidden = slug === null;
        }

        window.openPublicMap = function () {
            if (slug === null) return;
            const url = urlTemplate.replace('__SLUG__', encodeURIComponent(slug)) + (locationId > 0 ? '#' + locationId : '');
            window.open(url, '_blank', 'noopener');
        };

        window.addEventListener('message', (event) => {
            if (event.origin !== origin || !event.data) return;
            if (event.data.type === 'mapPlayersInfo' && event.source === frame()?.contentWindow) readSlug();
            if (event.data.type === 'playerLocationChanged' && Number(event.data.locationId) > 0) locationId = Number(event.data.locationId);
        });

        document.addEventListener('DOMContentLoaded', () => {
            frame()?.addEventListener('load', readSlug);
            readSlug();
        });
    })();
</script>
