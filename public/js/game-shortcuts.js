(function (window) {
    'use strict';

    function isEditableTarget(target) {
        return target
            && (target.isContentEditable || (typeof target.closest === 'function'
                && target.closest('form, input, textarea, select, button, [role="textbox"], [role="combobox"], [contenteditable]:not([contenteditable="false"])')));
    }

    // Клавиши по умолчанию; игрок переопределяет их в настройках (window.gameSettings.hotkeys).
    const DEFAULT_HOTKEYS = {
        move_north: 'ArrowUp', move_south: 'ArrowDown', move_west: 'ArrowLeft', move_east: 'ArrowRight',
        move_up: 'PageUp', move_down: 'PageDown',
        take_item: 'KeyF', weapon_attack: 'KeyQ', attack: 'Space',
        backpack: 'KeyI', magic_skill: 'KeyB', character: 'KeyC', map: 'KeyM',
        hotbar_1: 'Digit1', hotbar_2: 'Digit2', hotbar_3: 'Digit3', hotbar_4: 'Digit4', hotbar_5: 'Digit5',
        hotbar_6: 'Digit6', hotbar_7: 'Digit7', hotbar_8: 'Digit8', hotbar_9: 'Digit9', hotbar_10: 'Digit0',
    };

    const ACTION_ELEMENT_IDS = {
        move_north: 'move-north',
        move_south: 'move-south',
        move_west: 'move-west',
        move_east: 'move-east',
        move_up: 'move-up',
        move_down: 'move-down',
        take_item: 'take-item',
        weapon_attack: 'weapon-attack',
        attack: 'attack',
    };

    // Запасной вариант для событий без event.code: символ -> код физической клавиши (латиница, кириллица).
    const CODE_BY_KEY = {
        i: 'KeyI', 'і': 'KeyI', 'и': 'KeyI', 'ш': 'KeyI',
        b: 'KeyB', c: 'KeyC', 'с': 'KeyC',
        m: 'KeyM', 'ь': 'KeyM',
        f: 'KeyF', 'ф': 'KeyF', 'а': 'KeyF',
        q: 'KeyQ', 'й': 'KeyQ',
        ' ': 'Space', spacebar: 'Space',
    };

    let actionByCode = {};

    function buildBindings(hotkeys, magicHotkeys) {
        const bindings = {};
        Object.keys(DEFAULT_HOTKEYS).forEach(function (action) {
            const code = hotkeys && typeof hotkeys[action] === 'string' && hotkeys[action] ? hotkeys[action] : DEFAULT_HOTKEYS[action];
            bindings[code] = action;
        });
        // Клавиши заклинаний: «magic:<id>». Действия игры приоритетнее при совпадении кода.
        Object.keys(magicHotkeys || {}).forEach(function (skillId) {
            const code = magicHotkeys[skillId];
            if (typeof code === 'string' && code && !bindings[code]) {
                bindings[code] = 'magic:' + skillId;
            }
        });

        return bindings;
    }

    actionByCode = buildBindings(null, null);

    function handleShortcut(event, routes, navigate, actionDocument, openMap, useHotbarSlot) {
        if (event.defaultPrevented || event.isComposing || event.ctrlKey || event.metaKey || event.altKey || isEditableTarget(event.target)) {
            return;
        }

        let code = event.code || '';
        if (!code) {
            const key = typeof event.key === 'string' ? event.key.toLowerCase() : '';
            code = /^[0-9]$/.test(key) ? 'Digit' + key : (CODE_BY_KEY[key] || '');
        }
        const action = actionByCode[code];
        if (!action) {
            return;
        }

        const magicSkillId = /^magic:(\d+)$/.exec(action)?.[1];
        if (magicSkillId !== undefined) {
            // Заклинание срабатывает только в бою, где есть его кнопка; повтор удержанием не нужен.
            const spellButton = actionDocument?.querySelector('[data-magic-skill-id="' + magicSkillId + '"]');
            if (!spellButton || event.repeat) return;
            spellButton.click();
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        const hotbarSlot = /^hotbar_(\d+)$/.exec(action)?.[1];
        if (hotbarSlot !== undefined) {
            if (event.shiftKey || event.repeat || !useHotbarSlot) return;
            if (useHotbarSlot(Number(hotbarSlot)) === false) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        if (action === 'map') {
            openMap?.();
        } else if (ACTION_ELEMENT_IDS[action]) {
            const actionElement = actionDocument?.getElementById(ACTION_ELEMENT_IDS[action]);

            if (!actionElement) {
                if (action === 'attack') {
                    navigate(routes.location);
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }

                return;
            }

            // Удержание клавиши атаки оружием повторяет только удары внутри активного боя.
            // На локации и странице добычи повторное событие клавиши игнорируем.
            if (action === 'weapon_attack' && event.repeat && actionElement.dataset.qRepeat !== 'true') {
                return;
            }

            actionElement.click();
        } else {
            const url = ({ backpack: routes.backpack, magic_skill: routes.magicSkill, character: routes.character })[action];

            if (!url) {
                return;
            }

            navigate(url);
        }

        event.preventDefault();
        event.stopImmediatePropagation();
    }

    function bindDocument(targetDocument, routes, navigate, getActionDocument, openMap, useHotbarSlot, handleModalKey) {
        if (!targetDocument || targetDocument.documentElement.dataset.gameShortcutsBound === 'true') {
            return;
        }

        targetDocument.documentElement.dataset.gameShortcutsBound = 'true';
        // Пробел, обработанный модалкой, не должен на keyup «нажать» кнопку под фокусом
        // (например «Заточить» в кузне) — гасим и его.
        let swallowSpaceKeyup = false;
        targetDocument.addEventListener('keydown', function (event) {
            if (handleModalKey && !event.isComposing && handleModalKey(event) === true) {
                event.preventDefault();
                event.stopImmediatePropagation();
                if (event.key === ' ') swallowSpaceKeyup = true;
                return;
            }
            handleShortcut(event, routes, navigate, getActionDocument(), openMap, useHotbarSlot);
        }, true);
        targetDocument.addEventListener('keyup', function (event) {
            if (event.key === ' ' && swallowSpaceKeyup) {
                swallowSpaceKeyup = false;
                event.preventDefault();
                event.stopImmediatePropagation();
            }
        }, true);
    }

    window.GameShortcuts = {
        init: function (options) {
            actionByCode = buildBindings(options.hotkeys, options.magicHotkeys);
            const gameFrame = document.getElementById(options.frameId);

            if (!gameFrame) {
                return;
            }

            const getActionDocument = function () {
                return gameFrame.contentDocument;
            };
            const bindFrame = function (frame) {
                const bindFrameDocument = function () {
                    try {
                        bindDocument(frame.contentDocument, options.routes, options.navigate, getActionDocument, options.openMap, options.useHotbarSlot, options.handleModalKey);
                        frame.contentDocument.querySelectorAll('iframe').forEach(bindFrame);
                    } catch (error) {
                        // Iframe может быть загружен с другого origin.
                    }
                };

                frame.addEventListener('load', bindFrameDocument);
                bindFrameDocument();
            };

            try {
                bindDocument(document, options.routes, options.navigate, getActionDocument, options.openMap, options.useHotbarSlot, options.handleModalKey);
                document.querySelectorAll('iframe').forEach(bindFrame);
            } catch (error) {
                // Основной игровой iframe должен оставаться доступным в текущем origin.
            }
        },
    };
}(window));
