<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Онлайн Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    @vite('resources/js/app.js')
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100vh;
            margin: 0;
            background-color: #ffe4aa;
            overflow-y: hidden;
            font-family: Tahoma;
            font-size: 14px;
        }

        .tbl-main_top-bg {
            background-image: url({{ asset('img/bg/tbl-main_top-bg.gif') }});
            background-repeat: repeat-x;
            height: 73px;
        }
        .bg {
            background-color: #000;
            background-image: url({{ asset('img/bg/bg.gif') }});
            background-attachment: fixed;
            background-position: 0 5px;
        }
        .tbl-sts_top {
            background-image: url({{ asset('img/bg/tbl-sts_top.gif') }});
            background-repeat: repeat-x;
            background-position: bottom;
            height: 19px;
        }
        .tbl-sts-bb {
            background: url({{ asset('img/bg/tbl-sts.png') }}) left top repeat-x;
        }
        .tbl-sts b {
            background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-lb b {
            background-position: 0 -170px;
        }
        .tbl-sts-rb b {
            background-position: 0 -219px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-lbt b {
            background-position: 0 -150px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }
        .tbl-sts-rbt b {
            background-position: 0 -200px;
            height: 20px;
        }
        .tbl-sts_left {
            background-image: url({{ asset('img/bg/tbl-sts_left.gif') }});
            background-repeat: repeat-y;
            width: 19px;
            background-position: right;
        }
        .tbl-sts_right {
            background-image: url({{ asset('img/bg/tbl-sts_right.gif') }});
            background-repeat: repeat-y;
            width: 19px;
        }
        .bgg {
            background-image: url({{ asset('img/bg/bgg.gif') }});
        }


        /** red line **/
        .tbl-main_left-bottom-bg {
            background: url({{ asset('img/bg/tbl-main_left-bottom-bg.gif') }}) no-repeat bottom;
            height: 9px;
        }
        .tbl-main_center-bottom {
            background: url({{ asset('img/bg/tbl-main_center-bottom.gif') }}) repeat-x bottom;
            height: 9px;
        }
        .tbl-main_right-bottom-bg {
            background: url({{ asset('img/bg/tbl-main_right-bottom-bg.gif') }}) no-repeat bottom;
            height: 9px;
        }

        .td-map {
            padding: 0 !important;
            overflow: hidden;
        }

        .map-wrapper {
            width: 340px;
            overflow: hidden;
            transition: width 0.3s ease, opacity 0.3s ease;
            opacity: 1;
            height: 100%;
        }

        .map-wrapper.hidden {
            width: 0;
            opacity: 0;
        }

        .map-toggle-btn {
            position: absolute;
            top: 50%;
            left: -3px;
            transform: translateY(-50%);
            width: 14px;
            height: 70px;
            background: linear-gradient(to bottom, #e0b870, #b07030, #e0b870);
            border: 1px solid #7a4010;
            border-right: none;
            border-radius: 8px 0 0 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            user-select: none;
            box-shadow: -2px 0 6px rgba(0,0,0,0.35);
            transition: background 0.15s, left 0.3s ease;
            z-index: 10;
        }
        .map-toggle-btn:hover {
            background: linear-gradient(to bottom, #f0cc88, #c88040, #f0cc88);
        }
        .map-toggle-btn:active {
            background: linear-gradient(to bottom, #a06020, #d4a060, #a06020);
        }
        .map-toggle-arrow {
            display: inline-block;
            width: 0;
            height: 0;
            border-top: 5px solid transparent;
            border-bottom: 5px solid transparent;
            border-right: 6px solid #4a1a00;
            transition: transform 0.3s ease;
        }
        .map-toggle-arrow.open {
            transform: rotate(180deg);
        }
    </style>
</head>
<body class="bg">

<div class="tbl-main_top-right-bg" style="position: absolute;width: 60px;height: 90px;top: 0;left: 0;">
    <img src="{{ asset('img/bg/main_corner_left.png') }}" width="65" height="66" border="0">
</div>
<div class="tbl-main_top-right-bg" style="position: absolute;width: 60px;height: 90px;top: 0;right: 5px;">
    <img src="{{ asset('img/bg/main_corner_right.png') }}" width="65" height="66" border="0">
</div>

<table cellpadding="0" cellspacing="0" width="100%" height="69%" border="0">
    <tbody>
    <tr class="tbl-main_top-bg" style="height: 90px">
        <td colspan="3">
            <iframe id="menu-frame" width="100%" height="78px" frameborder="0" src="{{ route('menu') }}"></iframe>
        </td>
    </tr>
    <tr>
{{--        <td>--}}
{{--            <iframe id="interface-frame" width="100%" height="100%" frameborder="0" src="{{ route('interface') }}"></iframe><--}}
{{--        </td>--}}

        <td width="220px">
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr height="33">
                    <td width="19" valign="bottom" align="right" class="tbl-sts tbl-sts-lt"><b></b></td>
                    <td class="tbl-sts_top" align="left" valign="top"></td>
                    <td width="19" valign="bottom" class="tbl-sts tbl-sts-rt"><b></b></td>
                </tr>
                <tr height="100%">
                    <td class="tbl-sts_left" valign="top">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" align="right" class="tbl-sts tbl-sts-ltb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" align="right" class="tbl-sts tbl-sts-lbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td id="hero_content" class="bgg" align="left" valign="top" width="100%">

                        <iframe width="220px" name="hero" height="100%" frameborder="0" id="character-frame" src="{{ route('hero') }}"></iframe>

                    </td>
                    <td valign="top" class="tbl-sts_right">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" class="tbl-sts tbl-sts-rtb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" class="tbl-sts tbl-sts-rbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr height="19">
                    <td align="right" class="tbl-sts tbl-sts-lb"><b></b></td>
                    <td class="tbl-sts tbl-sts-bb">&nbsp;</td>
                    <td class="tbl-sts tbl-sts-rb"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="100%" height="100%">
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr height="33">
                    <td width="19" valign="bottom" align="right" class="tbl-sts tbl-sts-lt"><b></b></td>
                    <td class="tbl-sts_top" align="left" valign="top"></td>
                    <td width="19" valign="bottom" class="tbl-sts tbl-sts-rt"><b></b></td>
                </tr>
                <tr height="100%">
                    <td class="tbl-sts_left" valign="top">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" align="right" class="tbl-sts tbl-sts-ltb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" align="right" class="tbl-sts tbl-sts-lbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>

                    <td id="body_content" class="bgg" align="left" valign="top" width="100%" style="position: relative" height="100%">

                        <iframe width="100%" name="game" height="100%" frameborder="0" id="game-frame" src="{{ route('location') }}"></iframe>

                        <div class="drag-overlay" id="drag-overlay"></div>
                        <div class="hotbar-container" id="hotbar">
                            <div class="hotbar-drag-handle">⋮⋮</div>

                            <span class="hotbar-collapsed-label"></span>

                            <div class="hotbar" id="hotbar-slots">
                                {{-- Слоты рендерятся динамически через JS после загрузки /hotbar --}}
                            </div>

                            <div class="hotbar-toggle" id="hotbar-toggle">
                                <span class="toggle-icon">◀</span>
                            </div>
                        </div>

                        <script>
                            const hotbar = document.getElementById('hotbar');
                            const hotbarSlots = document.getElementById('hotbar-slots');
                            const dragHandle = document.querySelector('.hotbar-drag-handle');
                            const bodyContent = document.getElementById('body_content');
                            const gameFrame = document.getElementById('game-frame');
                            const dragOverlay = document.getElementById('drag-overlay');
                            const toggleButton = document.getElementById('hotbar-toggle');
                            const toggleIcon = toggleButton.querySelector('.toggle-icon');

                            // Данные слотов, загруженные с сервера
                            let hotbarData = {};

                            function buildSlotHtml(slot) {
                                const empty = slot.empty;
                                const cls = empty ? 'slot empty' : 'slot';
                                const icon = slot.image
                                    ? `<img src="${slot.image}" class="slot-img" alt="" style="width:40px;height:40px;object-fit:cover;display:block;border-radius:5px;">`
                                    : `<span class="icon"></span>`;
                                const name = slot.name ?? 'Пустой слот';
                                const cooldown = slot.cooldown ?? 0;
                                const countLine = (!empty && slot.entity_type === 'item' && slot.count != null)
                                    ? `<div class="tooltip-count">Количество: <span class="count-val">${slot.count}</span></div>`
                                    : '';
                                return `
                                    <div class="${cls}" data-slot="${slot.slot}" data-cooldown="${cooldown}" data-entity-type="${slot.entity_type ?? ''}" data-entity-id="${slot.entity_id ?? ''}">
                                        ${icon}
                                        <span class="keybind">${slot.slot}</span>
                                        <div class="cooldown"></div>
                                        <div class="tooltip">
                                            <div class="name">${name}</div>
                                            ${countLine}
                                        </div>
                                    </div>`;
                            }

                            function renderHotbar(data) {
                                hotbarData = {};
                                hotbarSlots.innerHTML = data.slots.map(buildSlotHtml).join('');
                                data.slots.forEach(s => {
                                    if (!s.empty) hotbarData[s.slot] = s;
                                });
                                bindSlotEvents();
                            }

                            function bindSlotEvents() {
                                hotbarSlots.querySelectorAll('.slot').forEach(slot => {
                                    slot.addEventListener('click', () => {
                                        useAbility(parseInt(slot.getAttribute('data-slot')));
                                    });
                                });
                            }

                            const hotbarCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                            function useAbility(slotNumber) {
                                const slot = hotbarSlots.querySelector(`[data-slot="${slotNumber}"]`);
                                if (!slot || slot.classList.contains('empty')) return;
                                if (slot.classList.contains('on-cooldown')) return;

                                const entityType  = slot.dataset.entityType;
                                const cooldownSec = parseFloat(slot.dataset.cooldown) || 0;

                                slot.classList.add('active');
                                setTimeout(() => slot.classList.remove('active'), 300);

                                if (entityType === 'item') {
                                    fetch('{{ route('hotbar.use') }}', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': hotbarCsrf,
                                        },
                                        body: JSON.stringify({ slot: slotNumber }),
                                    })
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.slot_cleared) {
                                            refreshHotbar();
                                        } else if (data.status === 'success') {
                                            const countEl = slot.querySelector('.count-val');
                                            if (countEl) countEl.textContent = data.count;
                                        }
                                        if (data.status === 'success') {
                                            const hpMp = {
                                                hp: { current: data.hp_now, max: data.hp_max },
                                                mp: { current: data.mp_now, max: data.mp_max },
                                                backpack_update: {
                                                    item_id: parseInt(slot.dataset.entityId),
                                                    count:   data.count,
                                                    removed: !!data.slot_cleared,
                                                },
                                            };
                                            sendToFrame('character-frame', hpMp);
                                            gameFrame.contentWindow?.postMessage(hpMp, '*');
                                        }
                                    });
                                }

                                if (cooldownSec > 0) {
                                    const cooldownEl = slot.querySelector('.cooldown');
                                    slot.classList.add('on-cooldown');
                                    let timeLeft = cooldownSec;
                                    cooldownEl.textContent = timeLeft;

                                    const interval = setInterval(() => {
                                        timeLeft--;
                                        if (timeLeft > 0) {
                                            cooldownEl.textContent = timeLeft;
                                        } else {
                                            clearInterval(interval);
                                            slot.classList.remove('on-cooldown');
                                            cooldownEl.textContent = '';
                                        }
                                    }, 1000);
                                }
                            }

                            // Загрузка хотбара с сервера (также доступна дочерним фреймам)
                            function refreshHotbar() {
                                fetch('{{ route('hotbar.index') }}')
                                    .then(r => r.json())
                                    .then(renderHotbar);
                            }

                            refreshHotbar();

                            {{-- Хоткеи 1-9 отключены: срабатывали при наборе текста --}}

                            // Сворачивание/разворачивание панели
                            let isCollapsed = localStorage.getItem('hotbar-collapsed') === 'true';

                            if (isCollapsed) {
                                hotbar.classList.add('collapsed');
                                toggleIcon.textContent = '▶';
                            }

                            toggleButton.addEventListener('click', (e) => {
                                e.stopPropagation();
                                isCollapsed = !isCollapsed;

                                if (isCollapsed) {
                                    hotbar.classList.add('collapsed');
                                    toggleIcon.textContent = '▶';
                                } else {
                                    hotbar.classList.remove('collapsed');
                                    toggleIcon.textContent = '◀';
                                }

                                localStorage.setItem('hotbar-collapsed', isCollapsed);
                            });

                            // Drag and Drop для hotbar
                            let isDragging = false;
                            let offsetX = 0;
                            let offsetY = 0;

                            // Загрузка сохраненной позиции
                            const savedPos = localStorage.getItem('hotbar-position');
                            if (savedPos) {
                                const pos = JSON.parse(savedPos);
                                hotbar.style.left = pos.left;
                                hotbar.style.top = pos.top;
                                hotbar.style.bottom = 'auto';
                                hotbar.style.transform = 'none';
                            }

                            dragHandle.addEventListener('mousedown', startDrag);

                            function startDrag(e) {
                                isDragging = true;
                                hotbar.classList.add('dragging');

                                gameFrame.classList.add('pointer-events-none');
                                dragOverlay.classList.add('active');

                                const rect = hotbar.getBoundingClientRect();
                                const parentRect = bodyContent.getBoundingClientRect();

                                offsetX = e.clientX - rect.left;
                                offsetY = e.clientY - rect.top;

                                hotbar.style.left = (rect.left - parentRect.left) + 'px';
                                hotbar.style.top = (rect.top - parentRect.top) + 'px';
                                hotbar.style.bottom = 'auto';
                                hotbar.style.transform = 'none';

                                e.preventDefault();
                                e.stopPropagation();
                            }

                            document.addEventListener('mousemove', drag);
                            document.addEventListener('mouseup', stopDrag);

                            function drag(e) {
                                if (!isDragging) return;

                                e.preventDefault();

                                const parentRect = bodyContent.getBoundingClientRect();
                                const hotbarRect = hotbar.getBoundingClientRect();

                                let newX = e.clientX - parentRect.left - offsetX;
                                let newY = e.clientY - parentRect.top - offsetY;

                                newX = Math.max(0, Math.min(newX, parentRect.width - hotbarRect.width));
                                newY = Math.max(0, Math.min(newY, parentRect.height - hotbarRect.height));

                                hotbar.style.left = newX + 'px';
                                hotbar.style.top = newY + 'px';
                            }

                            function stopDrag(e) {
                                if (isDragging) {
                                    isDragging = false;
                                    hotbar.classList.remove('dragging');

                                    gameFrame.classList.remove('pointer-events-none');
                                    dragOverlay.classList.remove('active');

                                    localStorage.setItem('hotbar-position', JSON.stringify({
                                        left: hotbar.style.left,
                                        top: hotbar.style.top
                                    }));

                                    e.preventDefault();
                                }
                            }

                            document.addEventListener('selectstart', (e) => {
                                if (isDragging) e.preventDefault();
                            });
                        </script>

                        <script>
                            let mapVisible = localStorage.getItem('map-visible') !== 'false';
                            let mapHiddenForGathering = false;

                            function applyMapState() {
                                const mapWrapper = document.getElementById('map-wrapper');
                                const mapArrow   = document.getElementById('map-toggle-arrow');
                                const mapBtn     = document.querySelector('.map-toggle-btn');
                                if (!mapWrapper || !mapArrow || !mapBtn) return;
                                if (mapVisible && !mapHiddenForGathering) {
                                    mapWrapper.classList.remove('hidden');
                                    mapArrow.classList.add('open');
                                    mapBtn.style.left = '-3px';
                                } else {
                                    mapWrapper.classList.add('hidden');
                                    mapArrow.classList.remove('open');
                                    mapBtn.style.left = '-16px';
                                }
                            }

                            function toggleMap(show) {
                                if (show === undefined) {
                                    mapVisible = !mapVisible;
                                } else {
                                    mapVisible = !!show;
                                }
                                localStorage.setItem('map-visible', mapVisible);
                                applyMapState();
                            }

                            function setMapHiddenForGathering(hidden) {
                                mapHiddenForGathering = !!hidden;
                                applyMapState();
                            }

                            document.addEventListener('DOMContentLoaded', applyMapState);
                        </script>

                    </td>
                    <td valign="top" class="tbl-sts_right">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" class="tbl-sts tbl-sts-rtb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" class="tbl-sts tbl-sts-rbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr height="19">
                    <td align="right" class="tbl-sts tbl-sts-lb"><b></b></td>
                    <td class="tbl-sts tbl-sts-bb">&nbsp;</td>
                    <td class="tbl-sts tbl-sts-rb"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>

        <td class="td-map" id="td-map" style="position: relative; overflow: visible;">
            <div class="map-toggle-btn" onclick="toggleMap()" title="Карта">
                <span class="map-toggle-arrow open" id="map-toggle-arrow"></span>
            </div>
            <div id="map-wrapper" class="map-wrapper">
            <table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0" align="center">
                <tbody>
                <tr height="33">
                    <td width="19" valign="bottom" align="right" class="tbl-sts tbl-sts-lt"><b></b></td>
                    <td class="tbl-sts_top" align="left" valign="top"></td>
                    <td width="19" valign="bottom" class="tbl-sts tbl-sts-rt"><b></b></td>
                </tr>
                <tr height="100%">
                    <td class="tbl-sts_left" valign="top">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" align="right" class="tbl-sts tbl-sts-ltb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" align="right" class="tbl-sts tbl-sts-lbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td id="map_content" class="bgg" align="left" valign="top" width="100%">

                        <iframe width="300px" name="map" height="100%" frameborder="0" id="map-frame" src="{{ route('on_map', ['hide' => 1]) }}"></iframe>

                    </td>
                    <td valign="top" class="tbl-sts_right">
                        <table width="19" height="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr>
                                <td valign="top" class="tbl-sts tbl-sts-rtb"><b></b></td>
                            </tr>
                            <tr>
                                <td>&nbsp;</td>
                            </tr>
                            <tr>
                                <td valign="bottom" class="tbl-sts tbl-sts-rbt"><b></b></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr height="19">
                    <td align="right" class="tbl-sts tbl-sts-lb"><b></b></td>
                    <td class="tbl-sts tbl-sts-bb">&nbsp;</td>
                    <td class="tbl-sts tbl-sts-rb"><b></b></td>
                </tr>
                </tbody>
            </table>
            </div>
        </td>
    </tr>
    <tr>
        <td colspan="4" height="9">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                <tr>
                    <td height="9" width="66" class="tbl-main_left-bottom-bg"></td>
                    <td height="9" class="tbl-main_center-bottom"><img src="{{ asset('img/icon/d.gif') }}" alt="" height="9"></td>
                    <td height="9" width="64" class="tbl-main_right-bottom-bg"></td>
                </tr>
                <tr>
                    <td colspan="4" height="3"></td>
                </tr>
                </tbody>
            </table>
        </td>
    </tr>
    </tbody>
</table>
<table cellpadding="0" cellspacing="0" width="100%" height="30%" border="0">
    <tbody>
    <tr>
        <td>
            <iframe id="chat-frame" height="100%" width="100%" frameborder="0" scrolling="no" src="{{ route('chat') }}"></iframe>
        </td>
    </tr>
    </tbody>
</table>

<style>
    #artifact_alt .aa-table {
        border-radius: 30px 30px 0 0;
        box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, 0.2);
        font-size: 11px;
    }
    .aa-tl {
        background: url(/img/bg/item_info/tbl-pop_corner-top-left.gif) no-repeat;
        width: 14px;
        height: 24px;
    }
    .aa-t {
        background: url(/img/bg/item_info/tbl-pop_top.gif);
        height: 24px;
    }
    .aa-tr {
        background: url(/img/bg/item_info/tbl-pop_corner-top-right.gif) no-repeat;
        width: 14px;
        height: 24px;
    }
    .aa-l {
        background: url(/img/bg/item_info/tbl-pop_left.gif) repeat-y;
        width: 14px;
    }
    .aa-r {
        background: url(/img/bg/item_info/tbl-pop_right.gif) repeat-y;
        width: 14px;
    }
    .aa-bl {
        background: url(/img/bg/item_info/tbl-pop_corner-bottom-left.gif) no-repeat;
        width: 14px;
        height: 5px;
    }
    .aa-b {
        background: url(/img/bg/item_info/tbl-pop_bottom.gif) repeat-x;
        height: 5px;
    }
    .aa-br {
        background: url(/img/bg/item_info/tbl-pop_corner-bottom-right.gif) no-repeat;
        width: 14px;
        height: 5px;
    }
    .list_dark {
        background-color: #F4BB8A;
    }
    .skill_list td {
        padding: 0 7px;
    }
    .redd, .redd * {
        color: #BA0000 !important;
    }
    .red, .red * {
        color: #d00000;
    }
    img {
        vertical-align: middle;
    }
    .b {
        font-weight: bold;
    }
</style>

<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0;top: 0"></div>

{{--<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 487px;top: 190px" art_id="AA_513733">--}}
{{--    <table width="300" border="0" cellspacing="0" cellpadding="0" style="background-color:#FBD4A4;" class="aa-table">--}}
{{--        <tbody>--}}
{{--        <tr>--}}
{{--            <td width="14" class="aa-tl"><img src="/img/icon/d.gif" width="14" height="24"><br></td>--}}
{{--            <td class="aa-t aa-table-t" align="center" style="vertical-align:middle"><b style="color:#990099">Драгоценный--}}
{{--                    аппарат обнуления</b></td>--}}
{{--            <td width="14" class="aa-tr"><img src="/img/icon/d.gif" width="14" height="24"><br></td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td class="aa-l" style="padding:0;"></td>--}}
{{--            <td style="padding:0;">--}}
{{--                <table width="275" style=" margin: 3px" border="0" cellspacing="0" cellpadding="0" class="aa-table-t">--}}
{{--                    <tbody>--}}
{{--                    <tr>--}}
{{--                        <td align="center" valign="top" width="60">--}}
{{--                            <table width="60" height="60" cellpadding="0" cellspacing="0" border="0" style="margin: 2px"--}}
{{--                                   background="https://feo-dwar.com/images/data/artifacts/125846_79.jpg">--}}
{{--                                <tbody>--}}
{{--                                <tr>--}}
{{--                                    <td valign="bottom">&nbsp;</td>--}}
{{--                                </tr>--}}
{{--                                </tbody>--}}
{{--                            </table>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div><img src="/images/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle">&nbsp;Амулеты--}}
{{--                            </div>--}}
{{--                            <div class="b red"><span title="Бриллиант"><img src="/images/m_dmd.gif" border="0"--}}
{{--                                                                            width="11" height="11"--}}
{{--                                                                            align="absmiddle"></span>&nbsp;25.00--}}
{{--                            </div>--}}
{{--                        </td>--}}
{{--                        <td>--}}
{{--                            <div><img src="images/tbl-shp_level-icon.gif" width="11" height="10" align="absmiddle">--}}
{{--                                Уровень <b class="red">1</b></div>--}}
{{--                        </td>--}}
{{--                    </tr>--}}
{{--                    </tbody>--}}
{{--                </table>--}}
{{--                <table class="aa-table-t" width="100%" cellpadding="0" cellspacing="0" border="0">--}}
{{--                    <tbody>--}}
{{--                    <tr class="skill_list list_dark">--}}
{{--                        <td colspan="2" class="redd b">Предмет нельзя передать!</td>--}}
{{--                    </tr>--}}
{{--                    <tr class="skill_list ">--}}
{{--                        <td colspan="2" class="dark b">Предмет нельзя сдать в скупку</td>--}}
{{--                    </tr>--}}
{{--                    <tr class="skill_list list_dark">--}}
{{--                        <td colspan="2"><b style="color: #0969a2;">Предмет нельзя «заморозить»</b></td>--}}
{{--                    </tr>--}}
{{--                    <tr class="skill_list ">--}}
{{--                        <td colspan="2">Загадочное устройство, собранное изобретателем. Если покрутить ручку, маленький--}}
{{--                            диск начинает вращаться вокруг большого. Судя по всему, это должно как-то воздействовать на--}}
{{--                            скрытые в теле магические силы.--}}
{{--                            <br><br>--}}
{{--                            При активации аппарата вы полностью обнулите <strong>дневной лимит убийства--}}
{{--                                монстров</strong>.--}}
{{--                            <br><br>--}}
{{--                            <i>Энергии устройства хватит на <strong>1 использование</strong>.</i></td>--}}
{{--                    </tr>--}}
{{--                    </tbody>--}}
{{--                </table>--}}
{{--            </td>--}}
{{--            <td class="aa-r" style="padding:0px"></td>--}}
{{--        </tr>--}}
{{--        <tr>--}}
{{--            <td class="aa-bl"></td>--}}
{{--            <td class="aa-b"><img src="/img/icon/d.gif" width="1" height="5"></td>--}}
{{--            <td class="aa-br"></td>--}}
{{--        </tr>--}}
{{--        </tbody>--}}
{{--    </table>--}}
{{--</div>--}}


{{--<style>--}}
{{--    #systemConfirm_div {--}}
{{--        z-index: 1100;--}}
{{--        position: fixed !important;--}}
{{--        border-color: #660000;--}}
{{--        position: absolute;--}}
{{--    }--}}
{{--    .popup_global_container {--}}
{{--        box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, 0.2);--}}
{{--        font-size: 11px;--}}
{{--    }--}}
{{--    .popup-top-left {--}}
{{--        position: relative;--}}
{{--        background: url({{ asset('img/bg/modal/popup-top-left.png') }}) left top no-repeat;--}}
{{--    }--}}
{{--    .popup-top-right {--}}
{{--        background: url({{ asset('img/bg/modal/popup-top-right.png') }}) right top no-repeat;--}}
{{--    }--}}
{{--    .popup-top-center {--}}
{{--        margin: 0 36px 0 17px;--}}
{{--        background: url({{ asset('img/bg/modal/popup-top-center.png') }}) left top repeat-x;--}}
{{--    }--}}
{{--    .popup_global_title {--}}
{{--        height: 24px;--}}
{{--        padding-top: 10px;--}}
{{--        color: #f5f4bf;--}}
{{--        font-weight: 700;--}}
{{--        text-align: center;--}}
{{--    }--}}
{{--    .popup_global_close_btn {--}}
{{--        position: absolute;--}}
{{--        right: -2px;--}}
{{--        top: -2px;--}}
{{--        width: 33px;--}}
{{--        height: 35px;--}}
{{--        background: url({{ asset('img/bg/modal/popup-close.png') }}) right top no-repeat;--}}
{{--        cursor: pointer;--}}
{{--    }--}}
{{--    .popup-left-center {--}}
{{--        position: relative;--}}
{{--        background: url({{ asset('img/bg/modal/popup-left-center.png') }}) left top repeat-y;--}}
{{--    }--}}
{{--    .popup-right-center {--}}
{{--        background: url({{ asset('img/bg/modal/popup-right-center.png') }}) right top repeat-y;--}}
{{--    }--}}
{{--    .popup_global_content {--}}
{{--        overflow: hidden;--}}
{{--        margin: 0 18px;--}}
{{--        background: url({{ asset('img/bg/modal/popup-main-bg.png') }}) center center;--}}
{{--    }--}}
{{--    .popup-left-bottom {--}}
{{--        background: url({{ asset('img/bg/modal/popup-left-bottom.png') }}) left bottom no-repeat;--}}
{{--    }--}}
{{--    .popup-right-bottom {--}}
{{--        background: url({{ asset('img/bg/modal/popup-right-bottom.png') }}) right bottom no-repeat;--}}
{{--    }--}}
{{--    .popup-bottom-center {--}}
{{--        height: 17px;--}}
{{--        margin: 0 18px;--}}
{{--        background: url({{ asset('img/bg/modal/popup-bottom-center.png') }}) center bottom repeat-x;--}}
{{--    }--}}
{{--    b.butt1, b.butt1.disabled {--}}
{{--        height: 38px;--}}
{{--        font-size: 26px;--}}
{{--        cursor: pointer;--}}
{{--        background: url({{ asset('img/bg/btn/tbl-btn2_c-left.png') }}) left 4px no-repeat;--}}
{{--        display: inline-block;--}}
{{--    }--}}
{{--    b.butt1 b, b.butt1.disabled b {--}}
{{--        height: 38px;--}}
{{--        font-size: 26px;--}}
{{--        cursor: pointer;--}}
{{--        background: url({{ asset('img/bg/btn/tbl-btn2_c-right.png') }}) right 4px no-repeat;--}}
{{--        display: inline-block;--}}
{{--    }--}}
{{--    b.butt1 input, b.butt1 button.butt1, b.butt1.disabled input, b.butt1.disabled button {--}}
{{--        height: 38px;--}}
{{--        border: 0;--}}
{{--        color: #f8dea4 !important;--}}
{{--        cursor: pointer;--}}
{{--        font-family: Tahoma;--}}
{{--        font-size: 11px !important;--}}
{{--        font-weight: 700;--}}
{{--        text-decoration: none;--}}
{{--        margin: 0 33px;--}}
{{--        background: transparent url({{ asset('img/bg/btn/tbl-btn2_center.png') }}) center top repeat-x;--}}
{{--        padding-bottom: 3px;--}}
{{--        outline: none;--}}
{{--    }--}}
{{--    #confirm_ms {--}}
{{--        color: #ba0000;--}}
{{--    }--}}
{{--</style>--}}


<div id="systemConfirm_close_div" class="error_div btn_sys_confirm_close" style="display:none; z-index: 1000;"></div>
<div class="popup_global_container" id="systemConfirm_div" style="z-index: 10010; position: absolute; display: none; width: 435px; top:0px; left: 0px;">
    <div class="popup-top-left">
        <div class="popup-top-right">
            <div class="popup-top-center">
                <div class="popup_global_title" id="systemConfirm_title"></div>
            </div>
        </div>
        <div class="popup_global_close_btn btn_sys_confirm_close"></div>
    </div>

    <div class="popup-left-center">
        <div class="popup-right-center">
            <div class="popup_global_content" style="padding: 20px;">
                <div id="confirm_ms" style="text-align: center;">

                </div>
                <div style="text-align: center;">
                    <b class="butt1 pointer " ><b><input value="OK" type="submit" onClick="if(document._submit)return false;document._submit=true;" style="width:50px" ID="btnOk"></b></b> <b class="butt1 btn_sys_confirm_close pointer " ><b><input value="ОТМЕНА" type="button" style="width:60px"></b></b>
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


<div id="error_div" class="error_div" style="display: none; z-index: 1000; width: 100%; height: 100vh;"></div>
<img src="{{ asset('data/img/close.png') }}" width="27" height="27" alt="Выход" title="Выход из игры"
     style="position: absolute; top: 0; right: 8px; z-index: 1005; cursor: pointer;" onclick="showLogoutConfirm()">

<div id="fullscreen_button" title="Полноэкранный режим"
     style="position: absolute; top: 50px; right: 3px; z-index: 1005; cursor: pointer;" onclick="toggleFullscreen()">
    <img id="fsc_img" src="{{ asset('data/img/fscreen.png') }}" width="20" height="20" alt="">
</div>

{{-- Панель смайлов (как smiles.php на проде): живёт в главном окне, чтобы ложиться поверх фреймов --}}
@php
    $chatSmiles = \App\Modules\Chat\Domain\Services\MessageRenderer::SMILES;
    $smilePages = array_chunk($chatSmiles, 40, true);
@endphp
<style>
    #smiles_panel {
        display: none;
        position: fixed;
        right: 8px;
        bottom: 50px;
        width: 356px;
        z-index: 1000;
        padding: 4px;
        border: 1px solid #6f4a24;
        background: url({{ asset('img/bg/bgg.gif') }});
        box-shadow: 2px 2px 8px rgba(0, 0, 0, .45);
    }
    .smiles-page { display: none; height: 220px; overflow: hidden; }
    .smiles-page.active { display: block; }
    .smiles-grid { width: 100%; border-collapse: collapse; border-spacing: 0; }
    .smiles-grid td {
        width: 40px;
        height: 40px;
        padding: 1px;
        border: 1px solid #C49485;
        text-align: center;
        vertical-align: middle;
        cursor: pointer;
    }
    .smiles-grid td:hover { background-color: #fff; }
    .smiles-grid td img { max-height: 40px; max-width: 40px; vertical-align: middle; border: 0; }
    .smiles-pager { margin-top: 4px; }
    .smiles-pager table { width: 100%; border-collapse: collapse; }
    .smiles-pager td { color: #8D2616; height: 17px; text-align: center; vertical-align: middle; padding: 0 1px; font-size: 11px; }
    .smiles-pager .lbl { font-weight: bold; white-space: nowrap; text-align: left; width: 10px; }
    .pg-num {
        width: 17px;
        height: 17px;
        cursor: pointer;
        background: url({{ asset('data/img/pg-inact.gif') }}) no-repeat center center;
    }
    .pg-num a { color: #8D2616; font-size: 9px; font-weight: bold; text-decoration: none; }
    .pg-num.active { background-image: url({{ asset('data/img/pg-act.gif') }}); }
    .pg-num.active a { color: #FFF3D2; }
    .pg-arrow { cursor: pointer; vertical-align: middle; }
</style>
<div id="smiles_panel">
    @foreach($smilePages as $p => $pageSmiles)
        <div class="smiles-page{{ $p === 0 ? ' active' : '' }}" data-page="{{ $p }}">
            <table class="smiles-grid">
                @foreach(array_chunk($pageSmiles, 8, true) as $row)
                    <tr>
                        @foreach($row as $code => $img)
                            <td onclick="pickSmile('{{ $code }}')" title="{{ $code }}"><img src="{{ asset('data/smiles/' . $img) }}" alt="{{ $code }}"></td>
                        @endforeach
                        @for($i = count($row); $i < 8; $i++)
                            <td></td>
                        @endfor
                    </tr>
                @endforeach
            </table>
        </div>
    @endforeach

    <div class="smiles-pager">
        <table>
            <tbody>
            <tr>
                <td class="lbl"><nobr>Страницы:&nbsp;</nobr></td>
                @foreach($smilePages as $p => $pageSmiles)
                    <td class="pg-num{{ $p === 0 ? ' active' : '' }}" data-page="{{ $p }}" onclick="smilesPage({{ $p }})"><a href="#" onclick="return false;">{{ $p + 1 }}</a></td>
                @endforeach
                <td style="text-align: left;"></td>
                <td width="1%" nowrap style="text-align: right;">
                    <img id="smiles-prev" class="pg-arrow" src="{{ asset('data/img/p-left-gray.gif') }}" width="29" height="17" title="Предыдущая" onclick="smilesPageShift(-1)">
                    <img src="{{ asset('data/img/pg-act.gif') }}" width="17" height="17" style="vertical-align: middle;">
                    <img id="smiles-next" class="pg-arrow" src="{{ asset('data/img/p-right-red.gif') }}" width="29" height="17" title="Следующая" onclick="smilesPageShift(1)">
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    var smilesCurrentPage = 0;
    var smilesPagesTotal = {{ count($smilePages) }};

    function toggleSmiles() {
        var panel = document.getElementById('smiles_panel');
        panel.style.display = panel.style.display === 'block' ? 'none' : 'block';
    }

    function hideSmiles() {
        document.getElementById('smiles_panel').style.display = 'none';
        // отжать кнопку смайлов в нижней панели чата
        try {
            var chatFrame = document.getElementById('chat-frame');
            var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
            bottomFrame.contentWindow.chatResetSmileBtn();
        } catch (e) {}
    }

    function pickSmile(code) {
        try {
            var chatFrame = document.getElementById('chat-frame');
            var bottomFrame = chatFrame.contentDocument.getElementById('bottom-frame');
            bottomFrame.contentWindow.postMessage({ type: 'insertItem', code: code + ' ' }, '*');
        } catch (e) {}
    }

    function smilesPage(n) {
        if (n < 0 || n >= smilesPagesTotal) return;
        smilesCurrentPage = n;
        document.querySelectorAll('.smiles-page').forEach(function (p) {
            p.classList.toggle('active', parseInt(p.dataset.page) === n);
        });
        document.querySelectorAll('.pg-num').forEach(function (p) {
            p.classList.toggle('active', parseInt(p.dataset.page) === n);
        });
        document.getElementById('smiles-prev').src = n > 0
            ? '{{ asset('data/img/p-left-red.gif') }}'
            : '{{ asset('data/img/p-left-gray.gif') }}';
        document.getElementById('smiles-next').src = n < smilesPagesTotal - 1
            ? '{{ asset('data/img/p-right-red.gif') }}'
            : '{{ asset('data/img/p-right-gray.gif') }}';
    }

    function smilesPageShift(d) {
        smilesPage(smilesCurrentPage + d);
    }

    document.addEventListener('click', function (e) {
        var panel = document.getElementById('smiles_panel');
        if (panel.style.display === 'block' && !panel.contains(e.target)) {
            hideSmiles();
        }
    });
</script>

<div id="logout_confirm_overlay" style="display: none; position: fixed; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.45); z-index: 1100;" onclick="hideLogoutConfirm()"></div>
<div id="logout_confirm" class="popup_global_container" style="display: none; position: fixed; left: 50%; top: 30%; width: 380px; margin-left: -190px; z-index: 1101;">
    <div class="popup-top-left">
        <div class="popup-top-right">
            <div class="popup-top-center">
                <div class="popup_global_title">Выход из игры</div>
            </div>
        </div>
        <div class="popup_global_close_btn" onclick="hideLogoutConfirm();"></div>
    </div>
    <div class="popup-left-center">
        <div class="popup-right-center">
            <div class="popup_global_content" style="padding: 20px;">
                <div style="text-align: center;">
                    <b>Вы уверены, что хотите выйти из игры?</b>
                </div>
                <div style="margin-top: 25px; text-align: center;">
                    <b class="butt1 pointer"><b><input value="Выход" type="button" onclick="confirmLogout();" style="width: 100px;"></b></b>
                    <b class="butt1 pointer"><b><input value="Отмена" type="button" onclick="hideLogoutConfirm();" style="width: 100px;"></b></b>
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

<div id="game-message-overlay" class="error_div" style="display:none;z-index:1002;"></div>
<div id="game-message-modal" style="display:none;position:fixed;z-index:1003;left:50%;top:50%;transform:translate(-50%,-50%);">
    <div class="popup_global_container" style="width:400px;">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title" id="game-message-title"></div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="closeGameMessageModal()"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content" style="padding:14px 18px 10px;">
                    <div id="game-message-content" style="text-align:center;font-size:12px;line-height:1.45;color:#2a1a0e;"></div>
                    <div id="game-message-actions" style="margin:14px 0 4px;text-align:center;"></div>
                </div>
            </div>
        </div>
        <div class="popup-left-bottom"><div class="popup-right-bottom"><div class="popup-bottom-center"></div></div></div>
    </div>
</div>

<script>
    function showLogoutConfirm() {
        document.getElementById('logout_confirm_overlay').style.display = 'block';
        document.getElementById('logout_confirm').style.display = 'block';
    }

    function hideLogoutConfirm() {
        document.getElementById('logout_confirm_overlay').style.display = 'none';
        document.getElementById('logout_confirm').style.display = 'none';
    }

    function confirmLogout() {
        window.location.href = '{{ route('logout') }}';
    }

    function toggleFullscreen() {
        if (document.fullscreenElement) {
            document.exitFullscreen();
        } else {
            document.documentElement.requestFullscreen();
        }
    }

    // Иконка меняется и при выходе по Esc
    document.addEventListener('fullscreenchange', function () {
        document.getElementById('fsc_img').src = document.fullscreenElement
            ? '{{ asset('data/img/fscreen2.png') }}'
            : '{{ asset('data/img/fscreen.png') }}';
    });
</script>

<iframe width="1" height="1" frameborder="0" id="error" name="error" src="" scrolling="no" style="display: none; position: absolute; left: 0px; top: 0px; z-index: 1001;" allowtransparency="true"></iframe>

<script language="javaScript" src="{{ asset('js/common.js') }}"></script>
<script src="{{ asset('js/game-shortcuts.js') }}"></script>

<script>
    function showErrorIframe(message) {
        var iframe = document.getElementById('error');
        var overlay = document.getElementById('error_div');
        var urlError = '{{ route('error') }}';

        iframe.src = urlError + '?message=' + message;

        iframe.style.width = '480px';
        iframe.style.height = '300px';

        iframe.style.left = (window.innerWidth / 2 - 200) + 'px';
        iframe.style.top = (window.innerHeight / 2 - 100) + 'px';

        iframe.style.display = 'block';
        overlay.style.display = 'block';
    }

    function hideErrorIframe() {
        var iframe = document.getElementById('error');
        var overlay = document.getElementById('error_div');

        iframe.src = '';

        iframe.style.display = 'none';
        overlay.style.display = 'none';
    }

    function gebi(id){
        return document.getElementById(id)
    }
</script>
<script>
    GameShortcuts.init({
        frameId: 'game-frame',
        routes: {
            backpack: '{{ route('backpack') }}',
            character: '{{ route('character') }}',
            location: '{{ route('location') }}',
        },
        navigate: toLocation,
    });

    // Обработка сообщений от игрового iframe
    window.addEventListener('message', function(event) {
        // Проверка полученных данных
        // if (event.data.health !== undefined || event.data.mp !== undefined || event.data.experience !== undefined || event.data.lvl !== undefined) {
        //     // Пересылка данных в character-frame
        //     document.getElementById('character-frame').contentWindow.postMessage(event.data, '*');
        // }

        if (event.data.url) {
            toLocation(event.data.url);
        }

    });

    function sendDataToGame(url) {
        window.postMessage({ url: url }, '*');
    }

    function toLocation(url) {
        document.getElementById('game-frame').contentWindow.location.href = url;
    }

    function updateIframeWho() {
        const iframeWho = document.getElementById('who-frame');
        iframeWho.src = iframeWho.src;
    }

    function refreshChatChannels() {
        const chatFrame = document.getElementById('chat-frame');
        if (chatFrame) chatFrame.src = chatFrame.src;
    }
</script>

<script>
    function goTo(direction) {
        const directions = ['up', 'down', 'north', 'west', 'east', 'south'];

        if (!directions.includes(direction)) {
            console.error('Invalid direction:', direction);
            return;
        }

        const routes = {
            up: "{{ route('move-to', ['direction' => 'up']) }}",
            down: "{{ route('move-to', ['direction' => 'down']) }}",
            north: "{{ route('move-to', ['direction' => 'north']) }}",
            west: "{{ route('move-to', ['direction' => 'west']) }}",
            east: "{{ route('move-to', ['direction' => 'east']) }}",
            south: "{{ route('move-to', ['direction' => 'south']) }}",
        };

        toLocation(routes[direction]);
    }

    // Начальное состояние отдано вместе со страницей; последующие обновления приходят по WebSocket.
    const userId = @js((int) (auth()->id() ?? 0));
    const userChannelName = 'App.Models.User.' + userId;
    let hasUnreadMail = @js((bool) ($hasUnreadMail ?? false));
    let mailboxChannel = null;

    function applyUnreadMailState(hasUnread) {
        hasUnreadMail = Boolean(hasUnread);
        const menuFrame = document.getElementById('menu-frame');
        menuFrame?.contentWindow?.blinkButton?.('post', hasUnreadMail);
    }

    function subscribeToMailboxUnread() {
        if (! window.Echo || userId <= 0 || mailboxChannel) return;

        mailboxChannel = window.Echo.private(userChannelName)
            .listen('.post.unread.updated', event => applyUnreadMailState(event.has_unread));
    }

    // Регенерация и периодические эффекты обрабатываются серверным scheduler.
    // WebSocket доставляет готовое состояние во все игровые фреймы.
    const playerId = @js((int) (auth()->user()?->player_id ?? 0));
    const playerStateChannelName = 'player.' + playerId;
    const playerStateFallbackInterval = 30000;
    const playerPresenceInterval = 120000;
    let playerHeartbeatInFlight = false;
    let playerStateFallbackTimer = null;
    let playerPresenceTimer = null;
    let playerStateChannel = null;
    let onlinePresenceChannel = null;
    let onlinePresenceReady = false;
    let onlinePresenceLocationId = @js((int) (auth()->user()?->location_id ?? 0));
    const onlinePresenceUsers = new Map();

    function applyPlayerState(state) {
        if (!state) return;

        const message = {
            type: 'playerState',
            hp: state.hp,
            mp: state.mp,
            effects: state.effects,
            effectDamage: state.effect_damage,
            serverTime: state.server_time,
        };

        sendToFrame('character-frame', message, window.location.origin);
        const gameFrame = document.getElementById('game-frame');
        gameFrame?.contentWindow?.postMessage(message, window.location.origin);

        if (state.dead && state.death_url && gameFrame?.contentWindow) {
            if (state.death_message) {
                showErrorIframe(state.death_message);
            }

            gameFrame.contentWindow.location.replace(state.death_url);
        }
    }

    async function syncPlayerState() {
        if (playerHeartbeatInFlight) return;

        playerHeartbeatInFlight = true;

        try {
            const response = await fetch('{{ route('player.heartbeat') }}', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) return;

            applyPlayerState(await response.json());
        } catch (error) {
            // WebSocket или следующий fallback-запрос восстановит состояние.
        } finally {
            playerHeartbeatInFlight = false;
        }
    }

    function startPlayerStateFallback() {
        if (playerStateFallbackTimer !== null) return;
        playerStateFallbackTimer = window.setInterval(syncPlayerState, playerStateFallbackInterval);
    }

    function stopPlayerStateFallback() {
        if (playerStateFallbackTimer === null) return;
        window.clearInterval(playerStateFallbackTimer);
        playerStateFallbackTimer = null;
    }

    function sendPlayerPresence() {
        if (!playerStateChannel) return;
        playerStateChannel.whisper('player-presence', {sent_at: Date.now()});
        syncPublicOnlineCount();
    }

    function startPlayerPresence() {
        if (playerPresenceTimer !== null) return;
        sendPlayerPresence();
        playerPresenceTimer = window.setInterval(sendPlayerPresence, playerPresenceInterval);
    }

    function stopPlayerPresence() {
        if (playerPresenceTimer === null) return;
        window.clearInterval(playerPresenceTimer);
        playerPresenceTimer = null;
    }

    function subscribeToPlayerState() {
        if (!window.Echo || playerId <= 0) {
            startPlayerStateFallback();
            return;
        }

        playerStateChannel = window.Echo.private(playerStateChannelName)
            .listen('.player.state.updated', function (event) {
                applyPlayerState(event.state);
            })
            .subscribed(function () {
                startPlayerPresence();
            });

        const connection = window.Echo.connector?.pusher?.connection;
        if (!connection) {
            startPlayerStateFallback();
            return;
        }

        connection.bind('connected', function () {
            stopPlayerStateFallback();
            syncPlayerState();
        });
        connection.bind('disconnected', function () {
            stopPlayerPresence();
            startPlayerStateFallback();
        });
        connection.bind('unavailable', function () {
            stopPlayerPresence();
            startPlayerStateFallback();
        });
        connection.bind('failed', function () {
            stopPlayerPresence();
            startPlayerStateFallback();
        });

        if (connection.state === 'connected') {
            stopPlayerStateFallback();
        } else {
            startPlayerStateFallback();
        }
    }

    function normalizeOnlineUser(user) {
        if (!user || Number(user.id) <= 0) return null;

        return {
            id: Number(user.id),
            name: String(user.name || ''),
            lvl: Number(user.lvl || 0),
            location_id: Number(user.location_id || 0),
            time: String(user.time || ''),
            is_online: true,
            clan_id: user.clan_id ? Number(user.clan_id) : null,
            clan_name: user.clan_name || null,
            clan_icon: user.clan_icon || null,
            info_url: user.info_url || ('{{ url('/info/u') }}/' + Number(user.id)),
        };
    }

    function sendOnlinePresenceSnapshot(targetWindow = null) {
        if (!onlinePresenceReady) return;

        const users = Array.from(onlinePresenceUsers.values())
            .sort((left, right) => left.name.localeCompare(right.name, 'ru'));
        const message = {
            type: 'onlinePresenceSnapshot',
            count: users.length,
            viewerLocationId: onlinePresenceLocationId,
            users,
        };

        if (targetWindow) {
            targetWindow.postMessage(message, window.location.origin);
            return;
        }

        try {
            const chatFrame = document.getElementById('chat-frame');
            const whoFrame = chatFrame?.contentDocument?.getElementById('who-frame');
            whoFrame?.contentWindow?.postMessage(message, window.location.origin);
        } catch (error) {
            // Фрейм списка ещё не загрузился; он запросит снимок после загрузки.
        }
    }

    function syncPublicOnlineCount() {
        if (!onlinePresenceReady || !onlinePresenceChannel || onlinePresenceUsers.size === 0) return;

        const leaderId = Math.min(...onlinePresenceUsers.keys());
        if (Number(userId) === leaderId) {
            onlinePresenceChannel.whisper('online-count-sync', {});
        }
    }

    function subscribeToOnlinePresence() {
        if (!window.Echo || userId <= 0 || onlinePresenceChannel) return;

        onlinePresenceChannel = window.Echo.join('online')
            .here(function (users) {
                onlinePresenceUsers.clear();
                users.forEach(function (user) {
                    const normalized = normalizeOnlineUser(user);
                    if (normalized) onlinePresenceUsers.set(normalized.id, normalized);
                });
                onlinePresenceReady = true;
                sendOnlinePresenceSnapshot();
                syncPublicOnlineCount();
            })
            .joining(function (user) {
                const normalized = normalizeOnlineUser(user);
                if (normalized) onlinePresenceUsers.set(normalized.id, normalized);
                sendOnlinePresenceSnapshot();
                syncPublicOnlineCount();
            })
            .leaving(function (user) {
                onlinePresenceUsers.delete(Number(user.id));
                sendOnlinePresenceSnapshot();
                syncPublicOnlineCount();
            });
    }

    function refreshOnlinePresenceLocation(locationId) {
        const nextLocationId = Number(locationId || 0);
        if (nextLocationId <= 0 || nextLocationId === onlinePresenceLocationId) return;

        onlinePresenceLocationId = nextLocationId;
        if (window.Echo && onlinePresenceChannel) {
            window.Echo.leave('online');
            onlinePresenceChannel = null;
            onlinePresenceReady = false;
            onlinePresenceUsers.clear();
        }
        subscribeToOnlinePresence();
    }

    window.addEventListener('message', function (event) {
        if (event.origin !== window.location.origin || !event.data) return;

        if (event.data.type === 'requestOnlinePresence') {
            sendOnlinePresenceSnapshot(event.source);
        } else if (event.data.type === 'playerLocationChanged') {
            refreshOnlinePresenceLocation(event.data.locationId);
        }
    });

    document.addEventListener('DOMContentLoaded', function () {
        const menuFrame = document.getElementById('menu-frame');
        menuFrame?.addEventListener('load', () => applyUnreadMailState(hasUnreadMail));
        applyUnreadMailState(hasUnreadMail);
        subscribeToMailboxUnread();
        syncPlayerState();
        subscribeToPlayerState();
        subscribeToOnlinePresence();
    });
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) syncPlayerState();
    });
    window.addEventListener('pagehide', function () {
        stopPlayerPresence();
        if (window.Echo && playerId > 0) window.Echo.leave(playerStateChannelName);
        if (window.Echo && onlinePresenceChannel) window.Echo.leave('online');
    });

    function attackMonster(id, monsterId, action) {
        let routeTemplate = "{{ route('fight.attack', ['id' => ':id', 'monsterId' => ':monsterId', 'action' => ':action']) }}";
        document.getElementById('game-frame').contentWindow.location.href = routeTemplate
            .replace(':id', id)
            .replace(':monsterId', monsterId)
            .replace(':action', action);
    }

    // Глобальні змінні в головному вікні (parent)
    if (typeof parent.isCooldown === 'undefined') {
        parent.isCooldown = false;
    }
    if (typeof parent.cooldownDuration === 'undefined') {
        parent.cooldownDuration = 1000;
    }

    parent.pendingAction = null;

    function queueAction(fn) {
        if (!parent.isCooldown) {
            fn();
        } else {
            parent.pendingAction = fn;
        }
    }

    function startCooldown() {
        const heroFrame = document.getElementById('character-frame');
        if (!heroFrame) return console.error('Iframe #character-frame не знайдено');

        const heroWindow = heroFrame.contentWindow;
        if (!heroWindow || !heroWindow.document) return console.error('Не вдалося отримати hero iframe');

        const heroDocument = heroWindow.document;
        const cooldownBar = heroDocument.getElementById('cooldownBar');

        if (!cooldownBar) return console.error('Не знайдено елемент cooldownBar');

        parent.isCooldown = true;
        parent.window.dispatchEvent(new CustomEvent('cooldown:start'));

        cooldownBar.classList.remove('filling');
        cooldownBar.style.transition = 'none';
        cooldownBar.style.width = '0%';

        setTimeout(() => {
            cooldownBar.style.transition = `width ${parent.cooldownDuration}ms linear`;
            cooldownBar.classList.add('filling');
            cooldownBar.style.width = '100%';

            setTimeout(() => {
                parent.isCooldown = false;
                parent.window.dispatchEvent(new CustomEvent('cooldown:end'));

                if (parent.pendingAction) {
                    const action = parent.pendingAction;
                    parent.pendingAction = null;
                    action();
                }
            }, parent.cooldownDuration);
        }, 50);

        return parent.isCooldown;
    }


    function sendToFrame(frameIdOrName, data, origin = '*') {
        try {
            const topWin = window.top;
            if (!topWin || !topWin.document) {
                console.error('❌ Нет доступа к top окну');
                return false;
            }

            // 1️⃣ Поиск iframe в top
            let frame =
                topWin.document.getElementById(frameIdOrName) ||
                topWin.frames[frameIdOrName];

            if (frame && frame.contentWindow) {
                frame.contentWindow.postMessage(data, origin);
                return true;
            }

            // 2️⃣ Поиск iframe внутри interface-frame
            const interfaceFrame = topWin.document.getElementById('interface-frame');
            if (interfaceFrame && interfaceFrame.contentWindow) {
                const interfaceDoc =
                    interfaceFrame.contentDocument ||
                    interfaceFrame.contentWindow.document;

                console.log(interfaceDoc);

                // по id
                frame = interfaceDoc.getElementById(frameIdOrName);

                if (frame && frame.contentWindow) {
                    frame.contentWindow.postMessage(data, origin);
                    return true;
                }

                // по name
                frame = interfaceFrame.contentWindow.frames[frameIdOrName];
                if (frame) {
                    frame.postMessage(data, origin);
                    return true;
                }
            }

            console.error(`❌ iframe "${frameIdOrName}" не найден`);
            return false;

        } catch (err) {
            console.error('⚠️ Ошибка при sendToFrame:', err);
            return false;
        }
    }

    const currentLocationId = {{ auth()->user()->location_id }};
    sendToFrame('map-frame', { currentLocationId });
</script>

{{-- Модальное окно распределения очков (открывается из character-frame) --}}
<div id="pts-overlay" class="error_div" style="display:none;z-index:1002;"></div>
<div id="pts-modal" style="display:none;position:fixed;z-index:1003;left:50%;top:50%;transform:translate(-50%,-50%);">
    <div class="popup_global_container" style="width:400px;">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title" id="pts-modal-title">Распределение очков</div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="closePtsModal()"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content" id="pts-modal-content" style="padding:10px 18px 4px;">
                    <div id="pts-modal-points-content">
                    <div style="text-align:center;margin-bottom:8px;font-size:11px;color:#2a1a0e;">
                        Свободно очков: <b id="pts-modal-free" style="color:#8b2000;"></b>
                    </div>
                    <div id="pts-rows"></div>
                    <div id="pts-msg" style="display:none;margin-top:8px;padding:5px 8px;background:#FEEFB6;border:1px solid #e0d080;border-radius:4px;font-size:11px;color:#5a4a00;"></div>
                    <div style="display:flex;gap:8px;margin:12px 0 6px;justify-content:center;">
                        <b class="butt1 pointer" onclick="ptsSave()"><b><input value="Сохранить" type="button"></b></b>
                        <b class="butt1 pointer" onclick="closePtsModal()"><b><input value="Отмена" type="button"></b></b>
                    </div>
                    </div>
                    <div id="pts-modal-map-monsters-content" style="display:none;max-height:min(480px,calc(100vh - 150px));overflow:auto;"></div>
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
    let _ptsData = {};
    let _ptsModalMode = 'points';

    function openPtsModal(data) {
        _ptsData = data;
        _ptsModalMode = 'points';
        document.getElementById('pts-modal-title').textContent = 'Распределение очков';
        document.getElementById('pts-modal-points-content').style.display = '';
        document.getElementById('pts-modal-map-monsters-content').style.display = 'none';

        document.getElementById('pts-modal-free').textContent = data.free;
        document.getElementById('pts-msg').style.display = 'none';

        const labels = { strength: 'Сила', intuition: 'Интуиция', agility: 'Ловкость', intelligence: 'Интеллект', wisdom: 'Мудрость', endurance: 'Выносливость' };
        const rows   = document.getElementById('pts-rows');
        const btnStyle = 'width:24px;height:24px;border:1px solid #a07040;background:url(/img/bg/table-header.jpg) repeat-x top left #c8924a;color:#461c0b;font-weight:bold;font-size:15px;cursor:pointer;border-radius:3px;padding:0;line-height:1;text-shadow:0 1px 0 rgba(255,255,255,.4);box-shadow:inset 0 1px 0 rgba(255,255,255,.25);';
        const inpStyle = 'width:34px;text-align:center;border:1px solid #CEBBAA;border-radius:3px;padding:3px 0;font-family:Tahoma;font-size:12px;color:#461c0b;font-weight:bold;background:#fffaf3;';

        rows.innerHTML = Object.keys(labels).map((k) => {
            const base  = data.bases[k];
            const bonus = data.full[k] - base;
            const bonusHtml = bonus > 0
                ? `<span style="color:#2a7a2a;font-size:11px">(+${bonus})</span>`
                : (bonus < 0 ? `<span style="color:#8b2020;font-size:11px">(${bonus})</span>` : '');
            return `
            <div style="display:flex;align-items:center;gap:8px;padding:6px 2px;border-bottom:1px solid #e8d4c0;">
                <span style="width:82px;flex-shrink:0;font-weight:bold;color:#2a1a0e;">${labels[k]}</span>
                <span style="width:56px;flex-shrink:0;font-size:12px;">
                    <b style="color:#8b2000;" id="ptsr-${k}">${base}</b> ${bonusHtml}
                </span>
                <div style="display:flex;align-items:center;gap:5px;">
                    <button type="button" onclick="ptsDec('${k}')" style="${btnStyle}">−</button>
                    <input type="text" id="ptsi-${k}" value="0" style="${inpStyle}" readonly>
                    <button type="button" onclick="ptsInc('${k}')" style="${btnStyle}">+</button>
                </div>
                <span style="margin-left:4px;font-size:12px;color:#555;">= <b id="ptsf-${k}" style="color:#2a1a0e;">${data.full[k]}</b></span>
            </div>`;
        }).join('');

        document.getElementById('pts-overlay').style.display = 'block';
        document.getElementById('pts-modal').style.display   = 'block';
    }

    function closePtsModal() {
        if (_ptsModalMode === 'map-monsters' || _ptsModalMode === 'map-resources') {
            closeMapCatalogModal();
            return;
        }

        document.getElementById('pts-overlay').style.display = 'none';
        document.getElementById('pts-modal').style.display   = 'none';
    }

    function openMapMonstersModal(data) {
        const title = document.getElementById('pts-modal-title');
        const pointsContent = document.getElementById('pts-modal-points-content');
        const monstersContent = document.getElementById('pts-modal-map-monsters-content');

        _ptsModalMode = 'map-monsters';
        title.textContent = 'Монстры: ' + data.map;
        pointsContent.style.display = 'none';
        monstersContent.style.display = '';
        monstersContent.replaceChildren();

        if (data.error) {
            monstersContent.textContent = data.error;
        } else if (!Array.isArray(data.monsters)) {
            monstersContent.textContent = 'Загрузка…';
        } else if (data.monsters.length === 0) {
            monstersContent.textContent = 'На этой карте монстры не указаны.';
        } else {
            const list = document.createElement('ul');
            list.style.cssText = 'margin:0;padding:7px;list-style:none;';

            data.monsters.forEach(function (monster) {
                const item = document.createElement('li');
                item.style.cssText = 'display:flex;align-items:center;gap:7px;min-height:36px;padding:4px;border-bottom:1px solid rgba(166,115,69,.35);';

                if (monster.image) {
                    const image = document.createElement('img');
                    image.src = monster.image;
                    image.alt = '';
                    image.style.cssText = 'width:30px;height:30px;object-fit:contain;';
                    item.appendChild(image);
                }

                const name = document.createElement('span');
                name.textContent = monster.name;
                name.style.cssText = 'color:#48250f;font-weight:bold;';
                item.appendChild(name);

                const levels = document.createElement('span');
                levels.style.cssText = 'margin-left:4px;color:#89552e;';
                levels.append('(ур. ');
                monster.levels.forEach(function (levelData, index) {
                    if (index > 0) levels.append(', ');

                    const level = document.createElement(levelData.info_url ? 'a' : 'span');
                    level.textContent = levelData.value;
                    if (levelData.info_url) {
                        level.href = levelData.info_url;
                        level.title = 'Открыть информацию о монстре';
                        level.style.cssText = 'color:inherit;';
                        level.addEventListener('click', function (event) {
                            event.preventDefault();
                            window.open(this.href, '', 'width=730,height=650');
                        });
                    }
                    levels.appendChild(level);
                });
                levels.append(')');
                item.appendChild(levels);

                if (monster.is_boss) {
                    const boss = document.createElement('span');
                    boss.textContent = 'БОСС';
                    boss.style.cssText = 'margin-left:5px;color:#8b2000;font-size:10px;font-weight:bold;';
                    item.appendChild(boss);
                }
                list.appendChild(item);
            });
            monstersContent.appendChild(list);
        }

        document.getElementById('pts-overlay').style.display = 'block';
        document.getElementById('pts-modal').style.display = 'block';
    }

    function closeMapMonstersModal() {
        closeMapCatalogModal();
    }

    function closeMapCatalogModal() {
        _ptsModalMode = 'points';
        document.getElementById('pts-overlay').style.display = 'none';
        document.getElementById('pts-modal').style.display = 'none';
        document.getElementById('pts-modal-title').textContent = 'Распределение очков';
        document.getElementById('pts-modal-points-content').style.display = '';
        document.getElementById('pts-modal-map-monsters-content').style.display = 'none';
    }

    function openMapResourcesModal(data) {
        const title = document.getElementById('pts-modal-title');
        const pointsContent = document.getElementById('pts-modal-points-content');
        const resourcesContent = document.getElementById('pts-modal-map-monsters-content');

        _ptsModalMode = 'map-resources';
        title.textContent = 'Ресурсы: ' + data.map;
        pointsContent.style.display = 'none';
        resourcesContent.style.display = '';
        resourcesContent.replaceChildren();

        if (data.error) {
            resourcesContent.textContent = data.error;
        } else if (!Array.isArray(data.resources)) {
            resourcesContent.textContent = 'Загрузка…';
        } else if (data.resources.length === 0) {
            resourcesContent.textContent = 'На этой карте добываемые ресурсы не настроены.';
        } else {
            const list = document.createElement('ul');
            list.style.cssText = 'margin:0;padding:7px;list-style:none;';

            data.resources.forEach(function (resource) {
                const item = document.createElement(resource.info_url ? 'a' : 'li');
                item.style.cssText = 'display:flex;align-items:center;gap:7px;min-height:36px;padding:4px;border-bottom:1px solid rgba(166,115,69,.35);color:inherit;text-decoration:none;';

                if (resource.info_url) {
                    item.href = resource.info_url;
                    item.title = 'Открыть информацию о ресурсе';
                    item.style.cursor = 'pointer';
                    item.addEventListener('click', function (event) {
                        event.preventDefault();
                        window.open(this.href, '', 'width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
                    });
                }

                if (resource.image) {
                    const image = document.createElement('img');
                    image.src = resource.image;
                    image.alt = '';
                    image.style.cssText = 'width:30px;height:30px;object-fit:contain;';
                    item.appendChild(image);
                }

                const details = document.createElement('span');
                details.style.cssText = 'display:flex;align-items:baseline;gap:6px;flex-wrap:wrap;';

                const name = document.createElement('span');
                name.textContent = resource.name;
                name.style.cssText = 'color:#48250f;font-weight:bold;';
                details.appendChild(name);

                const skill = document.createElement('span');
                skill.textContent = resource.skill_name + ' ' + resource.required_level + ' ур.';
                skill.style.cssText = 'color:#89552e;font-size:10px;';
                details.appendChild(skill);

                item.appendChild(details);
                list.appendChild(item);
            });

            resourcesContent.appendChild(list);
        }

        document.getElementById('pts-overlay').style.display = 'block';
        document.getElementById('pts-modal').style.display = 'block';
    }

    function openGameMessageModal(data) {
        document.getElementById('game-message-title').textContent = data.title;
        document.getElementById('game-message-content').textContent = data.message;
        setGameMessageModalActions(data.actions || [{ label: 'Закрыть', onClick: closeGameMessageModal }]);
        document.getElementById('game-message-overlay').style.display = 'block';
        document.getElementById('game-message-modal').style.display = 'block';
    }

    function openGameConfirmModal(data) {
        openGameMessageModal({
            title: data.title,
            message: data.message,
            actions: [
                { label: data.cancelLabel || 'Отмена', onClick: closeGameMessageModal },
                {
                    label: data.confirmLabel || 'Ок',
                    onClick: function () {
                        closeGameMessageModal();
                        data.onConfirm();
                    },
                },
            ],
        });
    }

    function setGameMessageModalActions(actions) {
        var container = document.getElementById('game-message-actions');
        container.innerHTML = '';

        actions.forEach(function (action, index) {
            var wrapper = document.createElement('b');
            wrapper.className = 'butt1 pointer';
            wrapper.style.margin = index ? '0 0 0 8px' : '0';
            var inner = document.createElement('b');
            var button = document.createElement('input');
            button.type = 'button';
            button.value = action.label;
            button.addEventListener('click', action.onClick);
            inner.appendChild(button);
            wrapper.appendChild(inner);
            container.appendChild(wrapper);
        });
    }

    function closeGameMessageModal() {
        document.getElementById('game-message-overlay').style.display = 'none';
        document.getElementById('game-message-modal').style.display = 'none';
    }

    @if(session('party_success') || session('party_error'))
        openGameMessageModal({
            title: @json(session('party_error') ? 'Ошибка группы' : 'Группа'),
            message: @json(session('party_success') ?? session('party_error')),
        });
    @endif

    function _ptsAdded() {
        return ['strength','intuition','agility','intelligence','wisdom','endurance'].reduce((s, k) => s + (parseInt(document.getElementById('ptsi-' + k)?.value) || 0), 0);
    }

    function ptsInc(key) {
        if (_ptsData.free - _ptsAdded() <= 0) return;
        const inp   = document.getElementById('ptsi-' + key);
        inp.value   = parseInt(inp.value) + 1;
        const added = parseInt(inp.value);
        document.getElementById('ptsr-' + key).textContent = _ptsData.bases[key] + added;
        document.getElementById('ptsf-' + key).textContent = _ptsData.full[key]  + added;
        document.getElementById('pts-modal-free').textContent = _ptsData.free - _ptsAdded();
    }

    function ptsDec(key) {
        const inp = document.getElementById('ptsi-' + key);
        if (parseInt(inp.value) <= 0) return;
        inp.value   = parseInt(inp.value) - 1;
        const added = parseInt(inp.value);
        document.getElementById('ptsr-' + key).textContent = _ptsData.bases[key] + added;
        document.getElementById('ptsf-' + key).textContent = _ptsData.full[key]  + added;
        document.getElementById('pts-modal-free').textContent = _ptsData.free - _ptsAdded();
    }

    function ptsSave() {
        const body = {
            strength:     parseInt(document.getElementById('ptsi-strength').value)     || 0,
            intuition:    parseInt(document.getElementById('ptsi-intuition').value)    || 0,
            agility:      parseInt(document.getElementById('ptsi-agility').value)      || 0,
            intelligence: parseInt(document.getElementById('ptsi-intelligence').value) || 0,
            wisdom:       parseInt(document.getElementById('ptsi-wisdom').value)       || 0,
            endurance:    parseInt(document.getElementById('ptsi-endurance').value)    || 0,
            ostrength:     _ptsData.bases.strength,
            ointuition:    _ptsData.bases.intuition,
            oagility:      _ptsData.bases.agility,
            ointelligence: _ptsData.bases.intelligence,
            owisdom:       _ptsData.bases.wisdom,
            oendurance:    _ptsData.bases.endurance,
        };

        fetch(_ptsData.saveUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': _ptsData.csrf, 'Accept': 'application/json' },
            body: JSON.stringify(body),
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === 'error') {
                const msg = document.getElementById('pts-msg');
                msg.textContent = data.message;
                msg.style.display = 'block';
                return;
            }
            closePtsModal();
            showErrorIframe(data.message);
            document.getElementById('game-frame').contentWindow.location.reload();
            document.getElementById('character-frame').contentWindow.location.reload();
        });
    }

    document.getElementById('pts-overlay').addEventListener('click', closePtsModal);
</script>

</body>
</html>
