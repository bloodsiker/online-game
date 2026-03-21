<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Онлайн Игра</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
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

<table cellpadding="0" cellspacing="0" width="100%" height="69%" border="0">
    <tbody>
    <tr class="tbl-main_top-bg" style="height: 73px">
        <td colspan="3">
            <iframe id="menu-frame" width="100%" height="60px" frameborder="0" src="{{ route('menu') }}"></iframe>
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

                            function handleHotbarKey(e) {
                                const key = parseInt(e.key);
                                if (key >= 1 && key <= 9) {
                                    useAbility(key);
                                }
                            }

                            document.addEventListener('keydown', handleHotbarKey);

                            // Пробрасываем хоткеи из game-frame в хотбар
                            gameFrame.addEventListener('load', () => {
                                try {
                                    gameFrame.contentDocument.addEventListener('keydown', handleHotbarKey);
                                } catch (e) {}
                            });

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

                            function applyMapState() {
                                const mapWrapper = document.getElementById('map-wrapper');
                                const mapArrow   = document.getElementById('map-toggle-arrow');
                                const mapBtn     = document.querySelector('.map-toggle-btn');
                                if (!mapWrapper || !mapArrow || !mapBtn) return;
                                if (mapVisible) {
                                    mapWrapper.classList.remove('hidden');
                                    mapArrow.classList.add('open');
                                    mapBtn.style.left = '-3px';
                                } else {
                                    mapWrapper.classList.add('hidden');
                                    mapArrow.classList.remove('open');
                                    mapBtn.style.left = '-14px';
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
            <iframe id="chat-frame" height="100%" width="100%" frameborder="0" src="{{ route('chat') }}"></iframe>
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
<iframe width="1" height="1" frameborder="0" id="error" name="error" src="" scrolling="no" style="display: none; position: absolute; left: 0px; top: 0px; z-index: 1001;" allowtransparency="true"></iframe>

<script language="javaScript" src="{{ asset('js/common.js') }}"></script>

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
    document.addEventListener('keydown', function(event) {
        if (event.ctrlKey || event.metaKey || event.altKey) {
            return;
        }

        switch (event.key.toLowerCase()) {
            case 'arrowup':
                document.getElementById('move-north').click();
                break;
            case 'arrowdown':
                document.getElementById('move-south').click();
                break;
            case 'arrowleft':
                document.getElementById('move-west').click();
                break;
            case 'arrowright':
                document.getElementById('move-east').click();
                break;
            case 'f':
                document.getElementById('take-item').click();
                break;
            case 'i':
                toLocation('{{ route('backpack') }}');
                break;
            case 'c':
                toLocation('{{ route('character') }}');
                break;
            case ' ':
                toLocation('{{ route('location') }}');
                break;
            default:
                return;
        }
        event.preventDefault();
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
    <div class="popup_global_container" style="width:360px;">
        <div class="popup-top-left">
            <div class="popup-top-right">
                <div class="popup-top-center">
                    <div class="popup_global_title">Распределение очков</div>
                </div>
            </div>
            <div class="popup_global_close_btn" onclick="closePtsModal()"></div>
        </div>
        <div class="popup-left-center">
            <div class="popup-right-center">
                <div class="popup_global_content" style="padding:10px 18px 4px;">
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

    function openPtsModal(data) {
        _ptsData = data;

        document.getElementById('pts-modal-free').textContent = data.free;
        document.getElementById('pts-msg').style.display = 'none';

        const labels = { str: 'Сила', int: 'Интуиция', agil: 'Ловкость', intel: 'Интеллект', mud: 'Мудрость' };
        const rows   = document.getElementById('pts-rows');
        const btnStyle = 'width:24px;height:24px;border:1px solid #a07040;background:url(/img/bg/table-header.jpg) repeat-x top left #c8924a;color:#461c0b;font-weight:bold;font-size:15px;cursor:pointer;border-radius:3px;padding:0;line-height:1;text-shadow:0 1px 0 rgba(255,255,255,.4);box-shadow:inset 0 1px 0 rgba(255,255,255,.25);';
        const inpStyle = 'width:34px;text-align:center;border:1px solid #CEBBAA;border-radius:3px;padding:3px 0;font-family:Tahoma;font-size:12px;color:#461c0b;font-weight:bold;background:#fffaf3;';

        rows.innerHTML = Object.keys(labels).map((k) => `
            <div style="display:flex;align-items:center;gap:8px;padding:6px 2px;border-bottom:1px solid #e8d4c0;">
                <span style="width:82px;flex-shrink:0;font-weight:bold;color:#2a1a0e;">${labels[k]}</span>
                <span style="width:42px;flex-shrink:0;font-weight:bold;color:#8b2000;font-size:12px;" id="ptsr-${k}">${data.full[k]}</span>
                <div style="display:flex;align-items:center;gap:5px;">
                    <button type="button" onclick="ptsDec('${k}')" style="${btnStyle}">−</button>
                    <input type="text" id="ptsi-${k}" value="0" style="${inpStyle}" readonly>
                    <button type="button" onclick="ptsInc('${k}')" style="${btnStyle}">+</button>
                </div>
            </div>`).join('');

        document.getElementById('pts-overlay').style.display = 'block';
        document.getElementById('pts-modal').style.display   = 'block';
    }

    function closePtsModal() {
        document.getElementById('pts-overlay').style.display = 'none';
        document.getElementById('pts-modal').style.display   = 'none';
    }

    function _ptsAdded() {
        return ['str','int','agil','intel','mud'].reduce((s, k) => s + (parseInt(document.getElementById('ptsi-' + k)?.value) || 0), 0);
    }

    function ptsInc(key) {
        if (_ptsData.free - _ptsAdded() <= 0) return;
        const inp = document.getElementById('ptsi-' + key);
        inp.value = parseInt(inp.value) + 1;
        document.getElementById('ptsr-' + key).textContent = _ptsData.full[key] + parseInt(inp.value);
        document.getElementById('pts-modal-free').textContent = _ptsData.free - _ptsAdded();
    }

    function ptsDec(key) {
        const inp = document.getElementById('ptsi-' + key);
        if (parseInt(inp.value) <= 0) return;
        inp.value = parseInt(inp.value) - 1;
        document.getElementById('ptsr-' + key).textContent = _ptsData.full[key] + parseInt(inp.value);
        document.getElementById('pts-modal-free').textContent = _ptsData.free - _ptsAdded();
    }

    function ptsSave() {
        const body = {
            str:   parseInt(document.getElementById('ptsi-str').value)   || 0,
            int:   parseInt(document.getElementById('ptsi-int').value)   || 0,
            agil:  parseInt(document.getElementById('ptsi-agil').value)  || 0,
            intel: parseInt(document.getElementById('ptsi-intel').value) || 0,
            mud:   parseInt(document.getElementById('ptsi-mud').value)   || 0,
            ostr:   _ptsData.bases.str,
            oint:   _ptsData.bases.int,
            oagil:  _ptsData.bases.agil,
            ointel: _ptsData.bases.intel,
            omud:   _ptsData.bases.mud,
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
