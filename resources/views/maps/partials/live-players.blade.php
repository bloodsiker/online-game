{{--
    Игроки онлайн на карте в реальном времени.

    Данные — снимок presence-канала `online` из оболочки игры (index.blade.php → sendOnlinePresenceSnapshot):
    у каждого игрока есть location_id, при переходе игрок заново входит в канал, и оболочка рассылает новый снимок.
    Клетки карты уже размечены: #l{id} — клетка локации, #z{id} — её счётчик.

    В клетке: кольцо 32px (несколько игроков — число внутри), ники по наведению. Цвет — по отношению к вам:
    соклан — зелёный, друг — синий, враг — чёрный, остальные — оранжевый.
    При переходе это кольцо плавно едет по карте в новую клетку (без ника). Свой персонаж не учитывается.
--}}
<style>
    /*
     * Другие игроки в клетке — кольцо 32px, цвет по отношению к вам:
     * соклан — зелёный, друг — синий, враг — чёрный, остальные — оранжевый.
     * В клетке с разными игроками цвет по важности: враг → соклан → друг → остальные.
     */
    .s2box s.live-on,
    .live-walker {
        --ring: #e08a2e;
        --fill: rgba(224, 138, 46, 0.25);
        --ink: #7a4510;
    }

    .live-clan { --ring: #2e9e4f !important; --fill: rgba(46, 158, 79, 0.25) !important; --ink: #145c2a !important; }
    .live-friend { --ring: #2f6fd1 !important; --fill: rgba(47, 111, 209, 0.22) !important; --ink: #143c7a !important; }
    .live-enemy { --ring: #1b1b1b !important; --fill: rgba(27, 27, 27, 0.28) !important; --ink: #000 !important; }

    .s2box s.live-on {
        position: absolute;
        top: 50%;
        left: 50%;
        z-index: 3;
        display: flex;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        /* 1–3 цифры — круг 32px; больше — растягивается в капсулу (до 99999 влезает в клетку 48px). */
        min-width: 32px;
        max-width: 46px;
        height: 32px;
        padding: 0 5px;
        transform: translate(-50%, -50%);
        border-radius: 16px;
        background-color: var(--fill);
        box-shadow: inset 0 0 0 3px var(--ring);
        color: var(--ink);
        font: bold 9px/1 Tahoma, Arial, sans-serif;
        letter-spacing: -0.2px;
        white-space: nowrap;
        text-decoration: none;
        cursor: help;
    }

    /* Один игрок — только кольцо, без цифры. */
    .s2box s.live-on.live-one {
        font-size: 0;
    }

    /* Игрок в пути: то же кольцо, что в клетке, плавно едет в новую клетку. */
    .live-walker {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 50;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background-color: var(--fill);
        box-shadow: inset 0 0 0 3px var(--ring);
        pointer-events: none;
        transition: transform 0.8s ease-in-out;
    }
</style>

<script>
    (function () {
        const viewerId = {{ (int) (auth()->id() ?? 0) }};
        @php $mapRelations = app(\App\Modules\Friend\Application\UseCases\GetMapRelations::class)->execute(auth()->user())->toArray(); @endphp
        const relations = @json($mapRelations);
        const friends = new Set(relations.friends);
        const enemies = new Set(relations.enemies);
        const viewerClanId = relations.clan_id;
        const GROUPS = ['enemy', 'clan', 'friend', 'other'];   // по убыванию важности
        const LABELS = { enemy: 'враг', clan: 'соклан', friend: 'друг', other: '' };
        if (viewerId <= 0 || window.top === window) return;

        const origin = window.location.origin;
        const LEAVE_GRACE_MS = 2500;   // переход = выход + вход в presence: даём игроку «вернуться» с новой локацией
        const WALK_MS = 800;

        /** @type {Map<number, {id: number, name: string, lvl: number, loc: number}>} */
        const players = new Map();
        const leaving = new Map();
        const counters = new Set();
        const inTransit = new Set();   // пока кольцо едет, игрок не учитывается ни в старой, ни в новой клетке

        const cell = (loc) => document.getElementById('l' + loc);

        function group(p) {
            if (enemies.has(p.id)) return 'enemy';
            if (viewerClanId && p.clan === viewerClanId) return 'clan';
            if (friends.has(p.id)) return 'friend';
            return 'other';
        }

        function setGroupClass(el, name) {
            el.classList.remove('live-enemy', 'live-clan', 'live-friend');
            if (name !== 'other') el.classList.add('live-' + name);
        }

        function center(loc) {
            const el = cell(loc);
            if (!el) return null;
            const r = el.getBoundingClientRect();
            return { x: r.left + window.scrollX + r.width / 2, y: r.top + window.scrollY + r.height / 2 };
        }

        function render() {
            const byLocation = new Map();
            players.forEach((p) => {
                if (p.id === viewerId || inTransit.has(p.id) || !cell(p.loc)) return;
                if (!byLocation.has(p.loc)) byLocation.set(p.loc, []);
                byLocation.get(p.loc).push(p);
            });

            counters.forEach((loc) => {
                if (byLocation.has(loc)) return;
                const z = document.getElementById('z' + loc);
                if (z) { z.classList.remove('live-on', 'live-one'); setGroupClass(z, 'other'); z.textContent = '0'; z.removeAttribute('title'); }
                counters.delete(loc);
            });

            byLocation.forEach((list, loc) => {
                const z = document.getElementById('z' + loc);
                if (!z) return;
                list.sort((a, b) => a.name.localeCompare(b.name, 'ru'));
                z.textContent = String(Math.min(list.length, 99999));
                z.title = list.map((p) => p.name + ' [' + p.lvl + ']' + (LABELS[group(p)] ? ' — ' + LABELS[group(p)] : '')).join('\n');
                setGroupClass(z, GROUPS.find((g) => list.some((p) => group(p) === g)));
                z.classList.add('live-on');
                z.classList.toggle('live-one', list.length === 1);
                counters.add(loc);
            });
        }

        function walk(player, from, to) {
            const a = center(from);
            const b = center(to);
            if (!a || !b || player.id === viewerId) return;

            const el = document.createElement('div');
            el.className = 'live-walker';
            setGroupClass(el, group(player));
            el.style.transform = 'translate(' + (a.x - 16) + 'px, ' + (a.y - 16) + 'px)';
            document.body.appendChild(el);
            inTransit.add(player.id);

            requestAnimationFrame(() => requestAnimationFrame(() => {
                el.style.transform = 'translate(' + (b.x - 16) + 'px, ' + (b.y - 16) + 'px)';
            }));
            // Доехало — кольцо «встаёт» в клетку: игрок снова учитывается, уже в новой локации.
            setTimeout(() => {
                inTransit.delete(player.id);
                el.remove();
                render();
            }, WALK_MS);
        }

        function applySnapshot(users) {
            const seen = new Set();

            users.forEach((user) => {
                const id = Number(user.id);
                const loc = Number(user.location_id || 0);
                if (id <= 0 || loc <= 0) return;
                seen.add(id);

                const previous = players.get(id);
                if (leaving.has(id)) { clearTimeout(leaving.get(id)); leaving.delete(id); }
                if (previous && previous.loc !== loc) walk(previous, previous.loc, loc);

                players.set(id, { id, name: String(user.name || ''), lvl: Number(user.lvl || 0), loc, clan: user.clan_id ? Number(user.clan_id) : null });
            });

            // Пропавших не убираем сразу: при переходе игрок выходит и тут же входит в канал с новой локацией.
            players.forEach((p, id) => {
                if (seen.has(id) || leaving.has(id)) return;
                leaving.set(id, setTimeout(() => {
                    leaving.delete(id);
                    players.delete(id);
                    render();
                }, LEAVE_GRACE_MS));
            });

            render();
        }

        window.addEventListener('message', (event) => {
            if (event.origin !== origin || !event.data || event.data.type !== 'onlinePresenceSnapshot') return;
            applySnapshot(Array.isArray(event.data.users) ? event.data.users : []);
        });

        // Первый снимок — по запросу; дальше оболочка присылает его сама при каждом изменении.
        try { window.top.postMessage({ type: 'requestOnlinePresence' }, origin); } catch (e) { /* другой origin */ }
    })();
</script>
