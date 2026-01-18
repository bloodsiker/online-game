<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Онлайн Игра</title>
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

        .error_div {
            position: absolute;
            height: 100%;
            width: 100%;
            filter: "progid:DXImageTransform.Microsoft.Alpha(opacity=80)";
            moz-opacity: .8;
            opacity: .8;
            background-image: url({{ asset('img/bg/error_bg.gif') }});
            left: 0;
            top: 0;
        }

        .td-map {
            width: 300px;
            transition: width 0.3s ease, opacity 0.3s ease;
            overflow: hidden;
            opacity: 1;
        }

        .td-map.hidden {
            width: 0;
            opacity: 0;
        }
    </style>
</head>
<body class="bg">


<style>
    .hotbar-container {
        position: absolute;
        bottom: 0;
        left: 50%;
        transform: translateX(-50%);
        /*background: linear-gradient(180deg, rgba(20, 20, 30, 0.95) 0%, rgba(15, 15, 25, 0.98) 100%);*/
        background: linear-gradient(180deg, rgb(255 234 183) 0%, rgb(230 195 151) 100%);
        padding: 5px;
        border-radius: 10px;
        /*box-shadow:*/
        /*    0 8px 32px rgba(0, 0, 0, 0.6),*/
        /*    inset 0 1px 0 rgba(255, 255, 255, 0.1),*/
        /*    0 0 40px rgba(100, 150, 255, 0.3);*/
        box-shadow: 0 0px 5px rgb(246 161 9 / 60%), inset 0 1px 0 rgb(230 201 91 / 10%), 0 0 24px rgb(233 230 113 / 30%);
        border: 2px solid rgb(237 147 4 / 30%);
        backdrop-filter: blur(10px);
    }

    .hotbar {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .slot {
        width: 40px;
        height: 40px;
        /*background: linear-gradient(145deg, rgba(40, 40, 55, 0.9), rgba(25, 25, 35, 0.9));*/
        background: linear-gradient(180deg, rgb(255 234 183) 0%, rgb(230 195 151) 100%);
        border: 2px solid rgba(241, 170, 112, 0.6);
        border-radius: 10px;
        cursor: pointer;
        position: relative;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow:
            inset 0 2px 4px rgba(231, 184, 134, 0.5),
            0 2px 8px rgba(235, 196, 150, 0.3);
    }

    .slot:hover {
        transform: translateY(-2px);
        border-color: rgba(242, 176, 112, 0.8);
        box-shadow:
            inset 0 2px 4px rgba(231, 184, 134, 0.5),
            0 2px 8px rgba(235, 196, 150, 0.3);
        background: linear-gradient(145deg, rgba(242, 191, 151, 0.9), rgba(250, 236, 193, 0.9));
    }

    .slot:active {
        transform: translateY(-2px) scale(0.95);
    }

    .slot.active {
        border-color: #ffd700;
        box-shadow:
            inset 0 2px 4px rgba(0, 0, 0, 0.5),
            0 0 20px rgba(255, 215, 0, 0.6),
            0 4px 16px rgba(255, 215, 0, 0.4);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% {
            box-shadow:
                inset 0 2px 4px rgba(0, 0, 0, 0.5),
                0 0 20px rgba(255, 215, 0, 0.6);
        }
        50% {
            box-shadow:
                inset 0 2px 4px rgba(0, 0, 0, 0.5),
                0 0 30px rgba(255, 215, 0, 0.8);
        }
    }

    .slot .icon {
        font-size: 28px;
        filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.5));
    }

    .slot .keybind {
        position: absolute;
        bottom: 2px;
        right: 4px;
        font-size: 10px;
        font-weight: bold;
        color: rgba(255, 255, 255, 0.6);
        background: rgba(0, 0, 0, 0.4);
        padding: 2px 4px;
        border-radius: 3px;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.8);
    }

    .slot .cooldown {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        border-radius: 8px;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        font-weight: bold;
        color: white;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.8);
    }

    .slot.on-cooldown .cooldown {
        display: flex;
    }

    .slot.on-cooldown {
        opacity: 0.5;
        cursor: not-allowed;
    }

    .slot.on-cooldown:hover {
        transform: none;
    }

    .slot.empty {
        opacity: 0.4;
    }

    .slot.empty .icon {
        font-size: 20px;
        opacity: 0.3;
    }

    .tooltip {
        position: absolute;
        bottom: 50px;
        left: 50%;
        transform: translateX(-50%);
        background: rgba(15, 15, 25, 0.95);
        color: white;
        padding: 8px 12px;
        border-radius: 8px;
        font-size: 12px;
        white-space: nowrap;
        pointer-events: none;
        opacity: 0;
        transition: opacity 0.2s;
        border: 1px solid rgba(100, 150, 255, 0.5);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
    }

    .slot:hover .tooltip {
        opacity: 1;
    }

    .tooltip .name {
        font-weight: bold;
        color: #ffd700;
        margin-bottom: 4px;
    }

    .tooltip .desc {
        color: rgba(255, 255, 255, 0.8);
    }
</style>


<table cellpadding="0" cellspacing="0" width="100%" height="70%" border="0">
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
                    <style>
                        .hotbar-drag-handle {
                            position: absolute;
                            left: -15px;
                            top: 50%;
                            transform: translateY(-50%);
                            width: 12px;
                            height: 40px;
                            background: rgba(229, 181, 131, 1);
                            border-radius: 6px 0 0 6px;
                            cursor: move;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: rgba(255, 255, 255, 0.5);
                            font-size: 15px;
                            transition: all 0.2s;
                        }

                        .hotbar-drag-handle:hover {
                            background: rgba(233, 169, 108, 0.9);
                            color: rgba(255, 255, 255, 0.8);
                            width: 15px;
                        }

                        /* Кнопка сворачивания */
                        .hotbar-toggle {
                            position: absolute;
                            right: -15px;
                            top: 50%;
                            transform: translateY(-50%);
                            width: 12px;
                            height: 40px;
                            background: rgba(229, 181, 131, 1);
                            border-radius: 0 6px 6px 0;
                            cursor: pointer;
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            color: rgba(255, 255, 255, 0.6);
                            font-size: 12px;
                            transition: all 0.2s;
                            user-select: none;
                        }

                        .hotbar-toggle:hover {
                            background: rgba(233, 169, 108, 0.9);
                            color: rgba(255, 255, 255, 0.8);
                            width: 15px;
                        }

                        /* Свернутое состояние */
                        .hotbar-container.collapsed .hotbar {
                            display: none;
                        }

                        .hotbar-container.collapsed {
                            width: auto;
                            padding: 8px 12px;
                        }

                        .hotbar-container.collapsed .hotbar-toggle {
                            position: relative;
                            right: 0;
                            transform: none;
                            width: 25px;
                            height: 25px;
                            border-radius: 6px;
                            font-size: 12px;
                        }

                        .hotbar-container.collapsed .hotbar-drag-handle {
                            left: -12px;
                        }

                        .hotbar-collapsed-label {
                            display: none;
                            color: rgba(255, 255, 255, 0.7);
                            font-size: 12px;
                            white-space: nowrap;
                            margin-right: 8px;
                        }

                        .hotbar-container.collapsed .hotbar-collapsed-label {
                            display: block;
                        }

                        .hotbar-container.dragging {
                            opacity: 0.8;
                            cursor: move !important;
                        }

                        .hotbar-container.dragging * {
                            cursor: move !important;
                        }

                        /* КРИТИЧНО: Блокируем iframe при перетаскивании */
                        #game-frame.pointer-events-none {
                            pointer-events: none;
                        }

                        /* Overlay для перехвата событий мыши */
                        .drag-overlay {
                            position: fixed;
                            top: 0;
                            left: 0;
                            right: 0;
                            bottom: 0;
                            z-index: 9998;
                            cursor: move;
                            display: none;
                        }

                        .drag-overlay.active {
                            display: block;
                        }

                        .hotbar-container {
                            z-index: 999;
                            /*transition: all 0.3s ease;*/
                        }
                    </style>
                    <td id="body_content" class="bgg" align="left" valign="top" width="100%" style="position: relative" height="100%">

                        <iframe width="100%" name="game" height="100%" frameborder="0" id="game-frame" src="{{ route('location') }}"></iframe>

                        <div class="drag-overlay" id="drag-overlay"></div>
                        <div class="hotbar-container" id="hotbar">
                            <div class="hotbar-drag-handle">⋮⋮</div>

                            <span class="hotbar-collapsed-label"></span>

                            <div class="hotbar">
                                <div class="slot" data-slot="1" data-cooldown="0">
                                    <span class="icon">⚔️</span>
                                    <span class="keybind">1</span>
                                    <div class="cooldown"></div>
                                    <div class="tooltip">
                                        <div class="name">Удар мечом</div>
                                        <div class="desc">Базовая атака. Урон: 50</div>
                                    </div>
                                </div>

                                <div class="slot" data-slot="2" data-cooldown="0">
                                    <span class="icon">🔥</span>
                                    <span class="keybind">2</span>
                                    <div class="cooldown"></div>
                                    <div class="tooltip">
                                        <div class="name">Огненный шар</div>
                                        <div class="desc">Магическая атака. Урон: 80, КД: 5с</div>
                                    </div>
                                </div>

                                <div class="slot" data-slot="3" data-cooldown="0">
                                    <span class="icon">💚</span>
                                    <span class="keybind">3</span>
                                    <div class="cooldown"></div>
                                    <div class="tooltip">
                                        <div class="name">Исцеление</div>
                                        <div class="desc">Восстанавливает 100 HP, КД: 10с</div>
                                    </div>
                                </div>

                                <div class="slot" data-slot="4" data-cooldown="0">
                                    <span class="icon">🛡️</span>
                                    <span class="keybind">4</span>
                                    <div class="cooldown"></div>
                                    <div class="tooltip">
                                        <div class="name">Щит</div>
                                        <div class="desc">Блокирует урон на 5 секунд</div>
                                    </div>
                                </div>

                                <div class="slot" data-slot="5" data-cooldown="0">
                                    <span class="icon">⚡</span>
                                    <span class="keybind">5</span>
                                    <div class="cooldown"></div>
                                    <div class="tooltip">
                                        <div class="name">Молния</div>
                                        <div class="desc">Мгновенный урон: 120, КД: 8с</div>
                                    </div>
                                </div>

                                <div class="slot" data-slot="6" data-cooldown="0">
                                    <span class="icon">🧪</span>
                                    <span class="keybind">6</span>
                                    <div class="cooldown"></div>
                                    <div class="tooltip">
                                        <div class="name">Зелье маны</div>
                                        <div class="desc">Восстанавливает 50 маны</div>
                                    </div>
                                </div>

                                <div class="slot empty" data-slot="7">
                                    <span class="icon"></span>
                                    <span class="keybind">7</span>
                                    <div class="tooltip">
                                        <div class="name">Пустой слот</div>
                                    </div>
                                </div>

                                <div class="slot empty" data-slot="8">
                                    <span class="icon"></span>
                                    <span class="keybind">8</span>
                                    <div class="tooltip">
                                        <div class="name">Пустой слот</div>
                                    </div>
                                </div>
                            </div>

                            <div class="hotbar-toggle" id="hotbar-toggle">
                                <span class="toggle-icon">◀</span>
                            </div>
                        </div>

                        <script>
                            const slots = document.querySelectorAll('.slot');
                            const hotbar = document.getElementById('hotbar');
                            const dragHandle = document.querySelector('.hotbar-drag-handle');
                            const bodyContent = document.getElementById('body_content');
                            const gameFrame = document.getElementById('game-frame');
                            const dragOverlay = document.getElementById('drag-overlay');
                            const toggleButton = document.getElementById('hotbar-toggle');
                            const toggleIcon = toggleButton.querySelector('.toggle-icon');

                            const abilities = {
                                1: { name: 'Удар мечом', cooldown: 0 },
                                2: { name: 'Огненный шар', cooldown: 5 },
                                3: { name: 'Исцеление', cooldown: 10 },
                                4: { name: 'Щит', cooldown: 6 },
                                5: { name: 'Молния', cooldown: 8 },
                                6: { name: 'Зелье маны', cooldown: 3 }
                            };

                            function useAbility(slotNumber) {
                                const slot = document.querySelector(`[data-slot="${slotNumber}"]`);

                                if (!slot || slot.classList.contains('empty')) {
                                    return;
                                }

                                if (slot.classList.contains('on-cooldown')) {
                                    return;
                                }

                                const ability = abilities[slotNumber];
                                if (!ability) return;

                                slot.classList.add('active');

                                setTimeout(() => {
                                    slot.classList.remove('active');
                                }, 300);

                                if (ability.cooldown > 0) {
                                    slot.classList.add('on-cooldown');
                                    let timeLeft = ability.cooldown;
                                    const cooldownEl = slot.querySelector('.cooldown');
                                    cooldownEl.textContent = timeLeft;

                                    const interval = setInterval(() => {
                                        timeLeft--;
                                        cooldownEl.textContent = timeLeft;

                                        if (timeLeft <= 0) {
                                            clearInterval(interval);
                                            slot.classList.remove('on-cooldown');
                                        }
                                    }, 1000);
                                }
                            }

                            slots.forEach(slot => {
                                slot.addEventListener('click', () => {
                                    const slotNumber = slot.getAttribute('data-slot');
                                    useAbility(slotNumber);
                                });
                            });

                            document.addEventListener('keydown', (e) => {
                                const key = e.key;
                                if (key >= '1' && key <= '8') {
                                    useAbility(key);
                                }
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

        <td class="td-map" id="td-map">
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
        </td>
    </tr>
    <tr>
        <td colspan="3" height="9">
            <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tbody>
                <tr>
                    <td height="9" width="66" class="tbl-main_left-bottom-bg"></td>
                    <td height="9" class="tbl-main_center-bottom"><img src="{{ asset('img/icon/d.gif') }}" alt="" height="9"></td>
                    <td height="9" width="64" class="tbl-main_right-bottom-bg"></td>
                </tr>
                <tr>
                    <td colspan="3" height="3"></td>
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
        <td width="100%"><iframe id="chat-frame" height="100%" width="100%" frameborder="0" src="{{ route('chat') }}"></iframe></td>
        <td width="335px"><iframe id="who-frame" height="100%" width="335px" frameborder="0" src="{{ route('who') }}"></iframe></td>
    </tr>
    <tr style="height: 40px">
        <td colspan="2"><iframe height="45px" width="100%" frameborder="0" id="bottom-frame" src="{{ route('chat-action') }}"></iframe></td>
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

<div id="error_div" class="error_div" style="display: none; z-index: 1000; width: 100%; height: 100vh;"></div>
<iframe width="1" height="1" frameborder="0" id="error" name="error" src="" scrolling="no" style="display: none; position: absolute; left: 0px; top: 0px; z-index: 1001;" allowtransparency="true"></iframe>

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


</body>
</html>
