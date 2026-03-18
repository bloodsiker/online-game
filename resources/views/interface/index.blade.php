<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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


</body>
</html>
