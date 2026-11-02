(function (window) {
    'use strict';

    function isEditableTarget(target) {
        return target
            && (target.isContentEditable || (typeof target.closest === 'function'
                && target.closest('form, input, textarea, select, button, [role="textbox"], [role="combobox"], [contenteditable]:not([contenteditable="false"])')));
    }

    function handleShortcut(event, routes, navigate, actionDocument, openMap, useHotbarSlot) {
        if (event.defaultPrevented || event.isComposing || event.ctrlKey || event.metaKey || event.altKey || isEditableTarget(event.target)) {
            return;
        }

        const digit = /^Digit([0-9])$/.exec(event.code || '')?.[1]
            ?? (/^[0-9]$/.test(event.key || '') ? event.key : null);
        if (digit !== null) {
            if (event.shiftKey || event.repeat || !useHotbarSlot) return;
            if (useHotbarSlot(digit === '0' ? 10 : Number(digit)) === false) return;
            event.preventDefault();
            event.stopImmediatePropagation();
            return;
        }

        const shortcutByCode = {
            ArrowUp: 'arrowup',
            ArrowDown: 'arrowdown',
            ArrowLeft: 'arrowleft',
            ArrowRight: 'arrowright',
            KeyF: 'f',
            KeyI: 'i',
            KeyB: 'b',
            KeyC: 'c',
            KeyM: 'm',
            KeyQ: 'q',
            Space: 'space',
        }[event.code];
        const key = typeof event.key === 'string' ? event.key.toLowerCase() : '';
        const shortcutByKey = {
            i: 'i',
            'і': 'i',
            'и': 'i',
            'ш': 'i',
            b: 'b',
            c: 'c',
            'с': 'c',
            m: 'm',
            'ь': 'm',
            f: 'f',
            'ф': 'f',
            'а': 'f',
            q: 'q',
            'й': 'q',
            ' ': 'space',
            spacebar: 'space',
        }[key];
        const shortcut = shortcutByCode || shortcutByKey;

        if (!shortcut) {
            return;
        }

        const actionElementIds = {
            arrowup: 'move-north',
            arrowdown: 'move-south',
            arrowleft: 'move-west',
            arrowright: 'move-east',
            f: 'take-item',
            q: 'weapon-attack',
            space: 'attack',
        };

        if (shortcut === 'm') {
            openMap?.();
        } else if (actionElementIds[shortcut]) {
            const actionElement = actionDocument?.getElementById(actionElementIds[shortcut]);

            if (!actionElement) {
                if (shortcut === 'space') {
                    navigate(routes.location);
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }

                return;
            }

            // Удержание Q повторяет только удары внутри активного боя.
            // На локации и странице добычи повторное событие клавиши игнорируем.
            if (shortcut === 'q' && event.repeat && actionElement.dataset.qRepeat !== 'true') {
                return;
            }

            actionElement.click();
        } else {
            const url = ({ i: routes.backpack, b: routes.magicSkill, c: routes.character })[shortcut];

            if (!url) {
                return;
            }

            navigate(url);
        }

        event.preventDefault();
        event.stopImmediatePropagation();
    }

    function bindDocument(targetDocument, routes, navigate, getActionDocument, openMap, useHotbarSlot) {
        if (!targetDocument || targetDocument.documentElement.dataset.gameShortcutsBound === 'true') {
            return;
        }

        targetDocument.documentElement.dataset.gameShortcutsBound = 'true';
        targetDocument.addEventListener('keydown', function (event) {
            handleShortcut(event, routes, navigate, getActionDocument(), openMap, useHotbarSlot);
        }, true);
    }

    window.GameShortcuts = {
        init: function (options) {
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
                        bindDocument(frame.contentDocument, options.routes, options.navigate, getActionDocument, options.openMap, options.useHotbarSlot);
                        frame.contentDocument.querySelectorAll('iframe').forEach(bindFrame);
                    } catch (error) {
                        // Iframe может быть загружен с другого origin.
                    }
                };

                frame.addEventListener('load', bindFrameDocument);
                bindFrameDocument();
            };

            try {
                bindDocument(document, options.routes, options.navigate, getActionDocument, options.openMap, options.useHotbarSlot);
                document.querySelectorAll('iframe').forEach(bindFrame);
            } catch (error) {
                // Основной игровой iframe должен оставаться доступным в текущем origin.
            }
        },
    };
}(window));
