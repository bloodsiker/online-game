(function (window) {
    'use strict';

    function isEditableTarget(target) {
        return target
            && typeof target.matches === 'function'
            && (target.matches('input, textarea, select, [contenteditable="true"]') || target.isContentEditable);
    }

    function handleShortcut(event, routes, navigate, actionDocument) {
        if (event.ctrlKey || event.metaKey || event.altKey || isEditableTarget(event.target)) {
            return;
        }

        const shortcutByCode = {
            ArrowUp: 'arrowup',
            ArrowDown: 'arrowdown',
            ArrowLeft: 'arrowleft',
            ArrowRight: 'arrowright',
            KeyF: 'f',
            KeyI: 'i',
            KeyC: 'c',
            Space: 'space',
        }[event.code];
        const key = typeof event.key === 'string' ? event.key.toLowerCase() : '';
        const shortcutByKey = {
            i: 'i',
            'і': 'i',
            'и': 'i',
            'ш': 'i',
            c: 'c',
            'с': 'c',
            f: 'f',
            'ф': 'f',
            'а': 'f',
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
            space: 'attack',
        };

        if (actionElementIds[shortcut]) {
            const actionElement = actionDocument?.getElementById(actionElementIds[shortcut]);

            if (!actionElement) {
                if (shortcut === 'space') {
                    navigate(routes.location);
                    event.preventDefault();
                    event.stopImmediatePropagation();
                }

                return;
            }

            actionElement.click();
        } else {
            const url = ({ i: routes.backpack, c: routes.character })[shortcut];

            if (!url) {
                return;
            }

            navigate(url);
        }

        event.preventDefault();
        event.stopImmediatePropagation();
    }

    function bindDocument(targetDocument, routes, navigate, getActionDocument) {
        if (!targetDocument || targetDocument.documentElement.dataset.gameShortcutsBound === 'true') {
            return;
        }

        targetDocument.documentElement.dataset.gameShortcutsBound = 'true';
        targetDocument.addEventListener('keydown', function (event) {
            handleShortcut(event, routes, navigate, getActionDocument());
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
                        bindDocument(frame.contentDocument, options.routes, options.navigate, getActionDocument);
                    } catch (error) {
                        // Iframe может быть загружен с другого origin.
                    }
                };

                frame.addEventListener('load', bindFrameDocument);
                bindFrameDocument();
            };

            try {
                bindDocument(document, options.routes, options.navigate, getActionDocument);
                document.querySelectorAll('iframe').forEach(bindFrame);
            } catch (error) {
                // Основной игровой iframe должен оставаться доступным в текущем origin.
            }
        },
    };
}(window));
