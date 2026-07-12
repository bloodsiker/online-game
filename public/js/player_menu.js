/**
 * Контекстное меню персонажа (ПКМ по нику) — как на проде.
 * Использование: initPlayerMenu({ myUserId, csrfToken, ignoredIds, friendsAddUrl,
 *   ignoreAddUrl, ignoreRemoveBase, infoUrlBase, sendPrivate, selector })
 */
(function () {
    var cfg = null;
    var ignoredIds = new Set();
    var ctxMenuEl = null;

    var CSS = ''
        + '.ctx-menu{position:absolute;z-index:100;min-width:130px;border:1px solid #e3b360;'
        + 'background:#f8e5a8;border-radius:4px;box-shadow:0 3px 3px 1px rgba(41,13,5,.4);}'
        + '.ctx-menu ul{list-style:none;margin:0;padding:0 0 6px;}'
        + '.ctx-menu li{font-weight:normal;color:#955c4a;}'
        + '.ctx-menu li span{display:block;cursor:pointer;padding:0 18px 3px;font-family:Tahoma;font-size:11px;}'
        + '.ctx-menu li span:hover{color:#a40001;background:#edcf8f;}'
        + '.ctx-menu li.ctx-menu__title{margin-bottom:6px;padding:6px 18px 3px;border-bottom:1px solid #e2b25e;'
        + 'font-weight:bold;font-family:Tahoma;font-size:11px;text-align:center;color:#6a382f;}';

    function showInfo(message, title) {
        try {
            window.top.systemInfo(message, title || 'Информация');
        } catch (e) {
            alert(message);
        }
    }

    function openUserInfo(uid) {
        window.open(cfg.infoUrlBase + uid, '', 'width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
    }

    function copyNick(name) {
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(name);
            return;
        }
        var input = document.createElement('textarea');
        input.value = name;
        document.body.appendChild(input);
        input.select();
        document.execCommand('copy');
        input.remove();
    }

    function addFriend(name) {
        fetch(cfg.friendsAddUrl, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': cfg.csrfToken, 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ name: name }),
        })
            .then(function (r) { return r.json(); })
            .then(function (data) { showInfo(data.message, data.ok ? 'Информация' : 'Ошибка'); })
            .catch(function () { showInfo('Не удалось отправить запрос.', 'Ошибка'); });
    }

    function toggleIgnore(uid, name) {
        var ignored = ignoredIds.has(uid);
        var req = ignored
            ? fetch(cfg.ignoreRemoveBase + uid, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': cfg.csrfToken } })
            : fetch(cfg.ignoreAddUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': cfg.csrfToken, 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: uid }),
            });

        req.then(function (r) {
            if (!r.ok) {
                showInfo('Не удалось изменить игнор-лист.', 'Ошибка');
                return;
            }
            ignored ? ignoredIds.delete(uid) : ignoredIds.add(uid);
            showInfo(ignored ? name + ' удалён из игнор-листа.' : name + ' добавлен в игнор-лист.');
        }).catch(function () {
            showInfo('Не удалось изменить игнор-лист.', 'Ошибка');
        });
    }

    function closeCtxMenu() {
        if (ctxMenuEl) {
            ctxMenuEl.remove();
            ctxMenuEl = null;
        }
    }

    function showCtxMenu(e, uid, name) {
        closeCtxMenu();

        var items = [
            { txt: 'Инфо', click: function () { openUserInfo(uid); } },
            { txt: 'Приват', click: function () { cfg.sendPrivate(name); } },
            { txt: 'Скопировать', click: function () { copyNick(name); } },
        ];
        if (uid !== cfg.myUserId) {
            items.push({ txt: 'Добавить в друзья', click: function () { addFriend(name); } });
            items.push({
                txt: ignoredIds.has(uid) ? 'Не игнорировать' : 'Игнорировать',
                click: function () { toggleIgnore(uid, name); },
            });
        }

        var menu = document.createElement('div');
        menu.className = 'ctx-menu';

        var list = document.createElement('ul');
        var title = document.createElement('li');
        title.className = 'ctx-menu__title';
        title.textContent = name;
        list.appendChild(title);

        items.forEach(function (item) {
            var li = document.createElement('li');
            var span = document.createElement('span');
            span.textContent = item.txt;
            span.onclick = function () { item.click(); closeCtxMenu(); };
            li.appendChild(span);
            list.appendChild(li);
        });

        menu.appendChild(list);
        document.body.appendChild(menu);

        var x = e.pageX, y = e.pageY;
        if (x + menu.offsetWidth > document.documentElement.clientWidth + window.scrollX) {
            x = Math.max(0, x - menu.offsetWidth);
        }
        if (y + menu.offsetHeight > document.documentElement.clientHeight + window.scrollY) {
            y = Math.max(0, y - menu.offsetHeight);
        }
        menu.style.left = x + 'px';
        menu.style.top = y + 'px';
        ctxMenuEl = menu;
    }

    window.initPlayerMenu = function (options) {
        cfg = options;
        ignoredIds = new Set(options.ignoredIds || []);

        var style = document.createElement('style');
        style.textContent = CSS;
        document.head.appendChild(style);

        var selector = options.selector || 'a.pnick[data-uid], a.player-link[data-uid]';

        document.addEventListener('contextmenu', function (e) {
            var link = e.target.closest ? e.target.closest(selector) : null;
            if (!link) {
                closeCtxMenu();
                return;
            }
            e.preventDefault();
            var name = link.dataset.name || link.textContent.trim();
            showCtxMenu(e, parseInt(link.dataset.uid, 10), name);
        });

        document.addEventListener('click', closeCtxMenu);
        window.addEventListener('blur', closeCtxMenu);
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeCtxMenu(); });
    };
})();