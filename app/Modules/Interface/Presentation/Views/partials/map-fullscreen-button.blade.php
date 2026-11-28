{{--
    Кнопка «развернуть» боковой карты: окно карты плавно вырастает из своего места до 90% экрана
    (затемнение фона, закрытие по кнопке ×, Esc или клику по фону). Тот же iframe — карта не перезагружается.
--}}
<style>
    .map-fs-btn {
        position: absolute; top: 30px; left: 52px; z-index: 11;
        display: inline-flex; align-items: center; justify-content: center;
        width: 26px; height: 26px; padding: 0;
        border: 1px solid #7a4010; border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #f0cc88, #b07030);
        color: #4a1a00; box-shadow: 0 1px 4px rgba(0, 0, 0, .45); cursor: pointer;
        transition: transform .2s ease;
    }
    .map-fs-btn:hover { background: radial-gradient(circle at 35% 30%, #f8dca0, #c08040); transform: scale(1.12); }
    .map-fs-btn:focus-visible, .map-fs-close:focus-visible { outline: 2px solid #f0cc88; outline-offset: 1px; }
    .map-fs-btn svg { width: 14px; height: 14px; display: block; }
    #map-wrapper.hidden ~ .map-fs-btn { display: none; }

    .map-fs-backdrop {
        position: fixed; inset: 0; z-index: 999; background: rgba(8, 4, 0, .72);
        opacity: 0; pointer-events: none; transition: opacity .45s ease;
    }
    .map-fs-backdrop.show { opacity: 1; pointer-events: auto; }

    #map-wrapper.map-fs {
        position: fixed; z-index: 1000; margin: 0;
        transition: top .45s cubic-bezier(.22, .8, .24, 1), left .45s cubic-bezier(.22, .8, .24, 1),
                    width .45s cubic-bezier(.22, .8, .24, 1), height .45s cubic-bezier(.22, .8, .24, 1);
    }
    #map-wrapper.map-fs iframe { width: 100%; }

    .map-fs-close {
        position: fixed; top: 0; left: 0; z-index: 1001;
        width: 30px; height: 30px; padding: 0; border: 1px solid #7a4010; border-radius: 50%;
        background: radial-gradient(circle at 35% 30%, #f0cc88, #b07030); color: #4a1a00;
        font: bold 20px/26px Arial, sans-serif; cursor: pointer; box-shadow: 0 1px 6px rgba(0, 0, 0, .6);
        opacity: 0; transform: scale(.5); pointer-events: none; transition: opacity .25s ease, transform .25s ease;
    }
    .map-fs-close.show { opacity: 1; transform: none; pointer-events: auto; }
    @media (prefers-reduced-motion: reduce) {
        #map-wrapper.map-fs, .map-fs-backdrop, .map-fs-close { transition: none; }
    }
</style>

<button type="button" class="map-fs-btn" id="map-fs-btn"
        title="{{ __('Развернуть карту') }}" aria-label="{{ __('Развернуть карту') }}" onclick="openMapFullscreen()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"></path>
    </svg>
</button>
<div class="map-fs-backdrop" id="map-fs-backdrop"></div>
<button type="button" class="map-fs-close" id="map-fs-close" title="{{ __('Свернуть карту') }}" aria-label="{{ __('Свернуть карту') }}">&times;</button>

<script>
    (function () {
        const el = (id) => document.getElementById(id);
        const duration = () => (window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 450);
        let opened = false;
        let busy = null;

        let chrome = { w: 40, h: 52 };

        // Размер карты внутри iframe (таблица клеток); без доступа к документу — максимум.
        function contentSize() {
            try {
                const doc = el('map-frame').contentDocument;
                const win = el('map-frame').contentWindow;
                const table = doc.querySelector('table.maptable') || doc.querySelector('table');
                if (!table) return null;
                const r = table.getBoundingClientRect();
                return { w: r.right + win.scrollX + 18, h: r.bottom + win.scrollY + 18 }; // + отступ и полоса прокрутки
            } catch (e) { return null; }
        }

        // Подгоняем окно под карту, но не шире/выше 90% экрана и не меньше исходного блока.
        function target() {
            const maxW = window.innerWidth * 0.9;
            const maxH = window.innerHeight * 0.9;
            const size = contentSize();
            const width = size ? Math.min(maxW, Math.max(340, size.w + chrome.w)) : maxW;
            const height = size ? Math.min(maxH, Math.max(300, size.h + chrome.h)) : maxH;
            return { left: (window.innerWidth - width) / 2, top: (window.innerHeight - height) / 2, width, height };
        }

        function place(wrapper, rect) {
            wrapper.style.left = rect.left + 'px';
            wrapper.style.top = rect.top + 'px';
            wrapper.style.width = rect.width + 'px';
            wrapper.style.height = rect.height + 'px';
        }

        window.openMapFullscreen = function () {
            const wrapper = el('map-wrapper');
            const cell = el('td-map');
            if (opened || !wrapper || !cell) return;
            opened = true;
            clearTimeout(busy);

            const from = wrapper.getBoundingClientRect();
            const inner = el('map-frame').getBoundingClientRect();
            chrome = { w: from.width - inner.width, h: from.height - inner.height };
            cell.style.width = cell.getBoundingClientRect().width + 'px'; // ячейка не схлопывается, пока карта «летает»
            wrapper.classList.add('map-fs');
            place(wrapper, from);
            void wrapper.offsetWidth;
            el('map-fs-backdrop').classList.add('show');
            wrapper.classList.add('map-fs-open');
            place(wrapper, target());
            const t = target();
            const close = el('map-fs-close');
            close.style.top = (t.top + 38) + 'px';
            close.style.left = (t.left + t.width - 56) + 'px';
            busy = setTimeout(() => close.classList.add('show'), duration());
        };

        window.closeMapFullscreen = function () {
            const wrapper = el('map-wrapper');
            const cell = el('td-map');
            if (!opened || !wrapper || !cell) return;
            opened = false;
            clearTimeout(busy);

            el('map-fs-close').classList.remove('show');
            el('map-fs-backdrop').classList.remove('show');
            wrapper.classList.remove('map-fs-open');
            place(wrapper, cell.getBoundingClientRect());
            busy = setTimeout(() => {
                wrapper.classList.remove('map-fs');
                ['left', 'top', 'width', 'height'].forEach((p) => { wrapper.style[p] = ''; });
                cell.style.width = '';
            }, duration() + 30);
        };

        el('map-fs-close').addEventListener('click', closeMapFullscreen);
        el('map-fs-backdrop').addEventListener('click', closeMapFullscreen);
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMapFullscreen(); });
        function refit() {
            if (!opened) return;
            const t = target();
            place(el('map-wrapper'), t);
            el('map-fs-close').style.top = (t.top + 38) + 'px';
            el('map-fs-close').style.left = (t.left + t.width - 56) + 'px';
        }
        window.addEventListener('resize', refit);
        // Esc, нажатый внутри iframe карты, до родителя не долетает — ловим и там.
        el('map-frame')?.addEventListener('load', () => {
            refit(); // переход на соседнюю карту: окно плавно подстраивается под новый размер
            try { el('map-frame').contentWindow.document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeMapFullscreen(); }); } catch (e) { /* другой origin */ }
        });
    })();
</script>
