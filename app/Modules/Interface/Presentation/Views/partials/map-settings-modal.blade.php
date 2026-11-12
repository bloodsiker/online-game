{{--
    Настройки текущей карты (шестерёнка #map-settings-btn на боковой карте).
    Боковая карта (maps/partials/live-players) кладёт в window.liveMapInfo {id, name, hidden}; оболочка читает его
    при загрузке фрейма и по сообщению 'mapPlayersInfo'. При переходе на другую карту фрейм перезагружается,
    и окно настраивает уже новую карту. Подземелья liveMapInfo не задают — шестерёнка скрыта.
--}}
<style>
    .map-settings-btn {
        position: absolute; top: 30px; right: 20px; z-index: 11;
        width: 26px; height: 26px; padding: 0;
        border: 1px solid #7a4010; border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #f0cc88, #b07030);
        color: #4a1a00; font: 17px/24px Arial, sans-serif; text-align: center;
        box-shadow: 0 1px 4px rgba(0, 0, 0, .45); cursor: pointer;
        transition: transform .3s ease;
    }
    .map-settings-btn:hover { transform: rotate(60deg); }
    .map-settings-btn:focus-visible { outline: 2px solid #f0cc88; outline-offset: 1px; }
    .map-settings-btn[hidden], #map-wrapper.hidden ~ .map-settings-btn { display: none; }
    .map-settings-option { display: flex; align-items: center; justify-content: space-between; gap: 12px; min-height: 26px; color: #795b48; }
    .map-settings-option label { flex: 1; cursor: pointer; }
    .map-settings-option b { color: #6c382c; }
    .map-settings-checkbox { flex: 0 0 24px; width: 24px; height: 23px; margin: 0; border: 0; background: url({{ asset('main/images/gui/gui-checkbox.png') }}) no-repeat 0 -23px; appearance: none; -webkit-appearance: none; cursor: pointer; }
    .map-settings-checkbox:checked { background-position: 0 0; }
    .map-settings-checkbox:disabled { opacity: .5; cursor: wait; }
    .map-settings-checkbox:focus-visible { outline: 1px solid #9c6c3b; border-radius: 4px; }
    .map-settings-note { margin: 8px 0 0; font-size: 10px; color: #927660; }
    .map-settings-error { margin: 8px 0 0; color: #a4150c; font-size: 11px; }
    .map-settings-error[hidden] { display: none; }
</style>

<div id="map-settings-overlay" class="error_div" style="display:none;z-index:1022;" onclick="closeMapSettings()"></div>
<div id="map-settings-modal" role="dialog" aria-modal="true" aria-labelledby="map-settings-title"
     style="display:none;position:fixed;z-index:1023;left:50%;top:50%;transform:translate(-50%,-50%);">
    <div class="popup_global_container" style="width:380px;max-width:calc(100vw - 24px);">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title" id="map-settings-title">{{ __('ui.map_settings') }}</div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="closeMapSettings()"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content" style="padding:12px 18px 8px;">
                    <div class="map-settings-option">
                        <label for="map-settings-hide-players">{{ __('ui.hide_players_on_map') }} — <b id="map-settings-map-name"></b></label>
                        <input class="map-settings-checkbox" id="map-settings-hide-players" type="checkbox">
                    </div>
                    <p class="map-settings-note">{{ __('ui.hide_players_on_map_note') }}</p>
                    <p class="map-settings-error" id="map-settings-error" role="alert" hidden></p>
                    <div style="margin-top:10px;text-align:center;">
                        <b class="butt1 pointer"><b><input value="{{ __('ui.close') }}" type="button" onclick="closeMapSettings()" style="width:100px;"></b></b>
                    </div>
                </div>
            </div>
        </div>
        <div class="popup-left-bottom">
            <div class="popup-right-bottom">
                <div class="popup-bottom-center"></div>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        const url = @json(route('settings.map-players', [], false));
        const errorText = @json(__('ui.map_settings_error'));
        const origin = window.location.origin;
        let currentMap = null;   // {id, name, hidden} — карта, открытая сейчас в боковом фрейме

        const el = (id) => document.getElementById(id);

        function syncButton() {
            const button = el('map-settings-btn');
            if (button) button.hidden = !currentMap;
            if (!currentMap) closeMapSettings();
        }

        function fillModal() {
            if (!currentMap) return;
            el('map-settings-map-name').textContent = currentMap.name;
            el('map-settings-hide-players').checked = !!currentMap.hidden;
            el('map-settings-error').hidden = true;
        }

        window.openMapSettings = function () {
            if (!currentMap) return;
            fillModal();
            el('map-settings-overlay').style.display = 'block';
            el('map-settings-modal').style.display = 'block';
            el('map-settings-hide-players').focus();
        };

        window.closeMapSettings = function () {
            const modal = el('map-settings-modal');
            if (!modal || modal.style.display === 'none') return;
            modal.style.display = 'none';
            el('map-settings-overlay').style.display = 'none';
            el('map-settings-btn')?.focus();
        };

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') window.closeMapSettings();
        });

        // Карта, открытая сейчас в боковом фрейме. Читаем напрямую (один origin), поэтому порядок load/message не важен.
        function readFrameMap() {
            let map = null;
            try { map = el('map-frame')?.contentWindow?.liveMapInfo || null; } catch (e) { map = null; }
            currentMap = map && Number(map.id) > 0 ? { id: Number(map.id), name: String(map.name || ''), hidden: !!map.hidden } : null;
            syncButton();
            if (currentMap && el('map-settings-modal').style.display !== 'none') fillModal();
        }

        window.addEventListener('message', (event) => {
            if (event.origin !== origin || !event.data || event.data.type !== 'mapPlayersInfo') return;
            if (event.source === el('map-frame')?.contentWindow) readFrameMap();
        });

        document.addEventListener('DOMContentLoaded', () => {
            el('map-frame')?.addEventListener('load', readFrameMap);
            readFrameMap();

            el('map-settings-hide-players')?.addEventListener('change', async (event) => {
                const checkbox = event.target;
                if (!currentMap) return;
                const map = currentMap;
                const hidden = checkbox.checked;
                checkbox.disabled = true;
                el('map-settings-error').hidden = true;
                try {
                    const response = await fetch(url, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ map_id: map.id, hidden }),
                    });
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    const data = await response.json();
                    map.hidden = !!data.hidden;
                    el('map-frame')?.contentWindow?.postMessage({ type: 'mapPlayersVisibility', mapId: map.id, hidden: map.hidden }, origin);
                } catch (e) {
                    checkbox.checked = !hidden;
                    el('map-settings-error').textContent = errorText;
                    el('map-settings-error').hidden = false;
                } finally {
                    checkbox.disabled = false;
                }
            });
        });
    })();
</script>
