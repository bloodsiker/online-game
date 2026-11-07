import './chat.js';

(() => {
    const root = document.getElementById('telegram-backpack-app');
    if (!root) return;
    let labels = JSON.parse(root.dataset.translations || '{}');
    const byId = name => document.getElementById('telegram-backpack-' + name);
    const grid = byId('items'), categories = byId('categories'), message = byId('message');
    const refresh = byId('refresh');
    const empty = byId('empty'), bot = byId('bot'), close = byId('close');
    const detail = byId('detail'), navigation = byId('navigation');
    const panels = { backpack: byId('inventory'), character: byId('profile'), auction: byId('auction'), chat: byId('chat'), reputations: byId('reputations'), influences: byId('influences'), online: byId('online') };
    const lots = byId('lots'), lotsEmpty = byId('lots-empty'), lotsPagination = byId('lots-pagination');
    const lotsPrevious = byId('lots-previous'), lotsNext = byId('lots-next');
    const auctionForm = byId('auction-filters');
    let auctionScope = 'all', auctionFilters = { name: '', type: '', rarity: '' };
    let view = 'backpack', auctionPage = 1, auctionLastPage = 1, onlinePage = 1, onlineLastPage = 1;
    const profileViews = ['character', 'reputations', 'influences'];
    const profileTabs = byId('profile-tabs'), onlineCount = byId('online-count');
    let onlineTimer, onlineRequest, summaryStopped = false;
    const webApp = window.Telegram?.WebApp;
    const initData = webApp?.initData || '';
    const params = new URLSearchParams(window.location.search);
    const validCategories = ['main', 'misc', 'key', 'quest', 'artifact', 'gift'];
    let category = validCategories.includes(params.get('category')) ? params.get('category') : 'main';
    let busy = false, sequence = 0, activeRequest, initialScroll = true;

    const chat = new window.TelegramMiniAppChat({ root, initData, labels: () => labels, onAuthError: text => { showMessage(text, true); bot.hidden = false; } });

    function showMessage(text, error = false) {
        message.textContent = text;
        message.classList.toggle('is-error', error);
        message.hidden = false;
    }

    function controls() {
        refresh.disabled = busy || !initData;
        root.classList.toggle('is-loading', busy);
        grid.setAttribute('aria-busy', String(busy && view === 'backpack'));
        for (const [key, panel] of Object.entries(panels)) panel.setAttribute('aria-busy', String(busy && view === key));
        lotsPrevious.disabled = busy || auctionPage <= 1;
        lotsNext.disabled = busy || auctionPage >= auctionLastPage;
        for (const button of auctionForm.querySelectorAll('button')) button.disabled = busy || !initData;
        byId('online-previous').disabled = busy || onlinePage <= 1;
        byId('online-next').disabled = busy || onlinePage >= onlineLastPage;
    }

    function imageUrl(value) {
        try {
            const url = new URL(value, window.location.href);
            if (['http:', 'https:'].includes(url.protocol)) return url.href;
        } catch (error) { /* Use the game's empty slot. */ }
        return root.dataset.defaultImage;
    }

    function assignImage(image, value, name) {
        image.src = imageUrl(value);
        image.alt = name;
        image.addEventListener('error', () => { image.src = root.dataset.defaultImage; }, { once: true });
    }

    function itemDetails(item) {
        byId('detail-name').textContent = item.name;
        const content = new DOMParser().parseFromString(String(item.description || '').replace(/<br\s*\/?>|<\/(?:p|div|li)>/gi, '\n'), 'text/html');
        for (const element of content.querySelectorAll('script, style, iframe, object, embed')) element.remove();
        const description = byId('detail-description');
        description.textContent = content.body.textContent.trim();
        description.hidden = !description.textContent;
        assignImage(byId('detail-image'), item.image_url, item.name);
        byId('detail-quantity').textContent = labels.mini_app_quantity + ': ' + item.quantity;
        byId('detail-type').textContent = labels.mini_app_type + ': ' + item.type_label;
        byId('detail-upgrade').textContent = labels.mini_app_upgrade + ': +' + item.upgrade_level;
        byId('detail-upgrade').hidden = !item.upgrade_level;
        detail.showModal();
    }

    function renderItems(items, target) {
        for (const item of items) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'telegram-backpack__item';
            const rarity = ['common', 'uncommon', 'rare', 'epic', 'legendary', 'heroic'].includes(item.rarity) ? item.rarity : 'common';
            button.classList.add('rarity-' + rarity);
            button.title = item.name + ' ×' + item.quantity;
            button.setAttribute('aria-label', button.title);
            const icon = document.createElement('span');
            icon.className = 'telegram-backpack__icon';
            const image = document.createElement('img');
            image.width = image.height = 80;
            image.loading = 'lazy';
            assignImage(image, item.image_url, item.name);
            icon.append(image);
            const quantity = document.createElement('span');
            quantity.className = 'telegram-backpack__quantity';
            quantity.textContent = String(item.quantity);
            icon.append(quantity);
            if (item.upgrade_level > 0) {
                const upgrade = document.createElement('span');
                upgrade.className = 'telegram-backpack__upgrade';
                upgrade.textContent = '+' + item.upgrade_level;
                icon.append(upgrade);
            }
            const name = document.createElement('span');
            name.className = 'telegram-backpack__name';
            name.textContent = item.name;
            button.append(icon, name);
            button.addEventListener('click', () => itemDetails(item));
            target.append(button);
        }
    }

    function selectCategory(key, scroll = true) {
        category = key;
        for (const button of categories.children) {
            button.setAttribute('aria-pressed', String(button.dataset.category === key));
        }
        if (scroll) byId('category-' + key)?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function renderCategories(groups, items) {
        categories.replaceChildren();
        grid.replaceChildren();
        const populated = groups.filter(group => items.some(item => item.category === group.key));
        if (!populated.some(group => group.key === category)) category = populated[0]?.key;
        empty.hidden = items.length > 0;
        for (const group of populated) {
            const section = document.createElement('section');
            section.id = 'telegram-backpack-category-' + group.key;
            section.className = 'telegram-backpack__category';
            const heading = document.createElement('h2');
            heading.id = section.id + '-title';
            heading.textContent = group.label;
            section.setAttribute('aria-labelledby', heading.id);
            const itemsGrid = document.createElement('div');
            itemsGrid.className = 'telegram-backpack__grid';
            renderItems(items.filter(item => item.category === group.key), itemsGrid);
            section.append(heading, itemsGrid);
            grid.append(section);
            const button = document.createElement('button');
            button.type = 'button';
            const label = document.createElement('span');
            label.textContent = group.label;
            const count = document.createElement('span');
            count.className = 'telegram-backpack__category-count';
            count.textContent = String(group.positions);
            button.append(label, count);
            button.setAttribute('aria-label', group.label + ': ' + group.positions);
            button.setAttribute('aria-pressed', String(group.key === category));
            button.dataset.category = group.key;
            button.addEventListener('click', () => selectCategory(group.key));
            categories.append(button);
        }
    }

    function formatNumber(value) {
        return new Intl.NumberFormat(document.documentElement.lang, { maximumFractionDigits: 2 }).format(Number(value));
    }

    function progressBar(percent, label = '') {
        const wrap = document.createElement('div'); wrap.className = 'telegram-profile__progress';
        wrap.setAttribute('role', 'progressbar'); wrap.setAttribute('aria-valuenow', String(Math.min(100, Math.max(0, Number(percent) || 0))));
        wrap.setAttribute('aria-valuemin', '0'); wrap.setAttribute('aria-valuemax', '100');
        wrap.setAttribute('aria-label', label || labels.profile_points);
        const fill = document.createElement('span'); fill.style.width = wrap.getAttribute('aria-valuenow') + '%'; wrap.append(fill);
        return wrap;
    }

    function emptyCard(target, text) {
        const paragraph = document.createElement('p'); paragraph.className = 'telegram-backpack__empty'; paragraph.textContent = text; target.append(paragraph);
    }

    function renderProfile(character) {
        byId('profile-name').textContent = character.nickname;
        byId('profile-level').textContent = character.level;
        byId('profile-race').textContent = character.race || '';
        const avatar = byId('profile-avatar'); avatar.src = imageUrl(character.avatar_url || root.dataset.defaultAvatar); avatar.alt = character.nickname;
        avatar.onerror = () => { avatar.onerror = null; avatar.src = root.dataset.defaultAvatar; };
        byId('profile-money').textContent = formatNumber(character.money);
        byId('profile-diamonds').textContent = formatNumber(character.diamonds);
        const stats = byId('profile-stats'); stats.replaceChildren();
        for (const stat of character.stats || []) {
            const row = document.createElement('div'), name = document.createElement('dt'), value = document.createElement('dd');
            name.textContent = stat.label; value.textContent = typeof stat.value === 'number' ? formatNumber(stat.value) : stat.value;
            row.append(name, value); stats.append(row);
        }
        const resources = byId('profile-resources'); resources.replaceChildren();
        for (const resource of character.resources || []) {
            const row = document.createElement('div'); row.className = 'telegram-profile__resource';
            if (['hp', 'mp', 'exp'].includes(resource.key)) row.classList.add('is-' + resource.key);
            const caption = document.createElement('p'), label = document.createElement('span'), value = document.createElement('b');
            label.textContent = resource.label; value.textContent = formatNumber(resource.current) + ' / ' + formatNumber(resource.max);
            caption.append(label, value);
            row.append(caption, progressBar(resource.progress ?? (resource.max > 0 ? resource.current * 100 / resource.max : 0), resource.label)); resources.append(row);
        }
        const skills = byId('profile-skills'); skills.replaceChildren();
        for (const group of character.skills || []) {
            const card = document.createElement('section'); card.className = 'telegram-profile__card';
            const title = document.createElement('h2'); title.textContent = group.label; card.append(title);
            if (!group.skills.length) emptyCard(card, labels.profile_no_skills);
            for (const skill of group.skills) {
                const row = document.createElement('div'); row.className = 'telegram-profile__resource';
                const caption = document.createElement('p'), name = document.createElement('span'), level = document.createElement('b');
                name.textContent = skill.name; level.textContent = formatNumber(skill.level); caption.append(name, level);
                row.append(caption, progressBar(skill.progress, skill.name)); card.append(row);
            }
            skills.append(card);
        }
        byId('profile-content').hidden = false;
    }

    function renderProgressions(rows, type) {
        const list = byId(type + '-list'); list.replaceChildren();
        if (!rows.length) emptyCard(list, type === 'reputations' ? labels.profile_no_reputations : labels.profile_no_influences);
        for (const item of rows) {
            const card = document.createElement('article'); card.className = 'telegram-profile__card telegram-progression';
            if (item.icon) { const image = document.createElement('img'); image.width = image.height = 52; image.loading = 'lazy'; assignImage(image, item.icon, item.name); card.append(image); }
            const body = document.createElement('div'), title = document.createElement('h2'); title.textContent = item.name; body.append(title);
            if (item.level_name) { const level = document.createElement('p'); level.textContent = item.level_name; body.append(level); }
            const points = document.createElement('b'); points.textContent = formatNumber(item.points); body.append(points, progressBar(item.progress, item.name));
            if (item.next !== null) { const next = document.createElement('p'); next.textContent = labels.profile_next + ': ' + formatNumber(item.next); body.append(next); }
            card.append(body); list.append(card);
        }
    }

    function renderOnline(data) {
        const list = byId('online-list'); list.replaceChildren(); onlineCount.textContent = formatNumber(data.online_count);
        if (!data.data.length) emptyCard(list, labels.profile_no_online);
        for (const player of data.data) {
            const row = document.createElement('article'); row.className = 'telegram-online-player';
            if (player.clan_icon) {
                const clan = document.createElement('img'); clan.width = clan.height = 16; clan.src = imageUrl(player.clan_icon); clan.alt = player.clan_name || ''; row.append(clan);
            }
            const name = document.createElement('a'); const url = new URL(player.profile_url, location.href);
            if (['http:', 'https:'].includes(url.protocol) && url.origin === location.origin) {
                name.href = url.href; name.target = '_blank'; name.rel = 'noopener noreferrer';
                name.addEventListener('click', event => {
                    if (event.button !== 0 || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || typeof webApp?.openLink !== 'function') return;
                    try {
                        webApp.openLink(url.href);
                        event.preventDefault();
                    } catch (error) { /* Fall back to the regular link if the Telegram client cannot open it. */ }
                });
            }
            name.textContent = player.name + ' [' + player.level + ']'; row.append(name);
            if (player.clan_name) { const clanName = document.createElement('small'); clanName.textContent = player.clan_name; row.append(clanName); }
            list.append(row);
        }
        onlinePage = data.meta.current_page; onlineLastPage = data.meta.last_page;
        byId('online-pagination').hidden = data.meta.total === 0;
        byId('online-page').textContent = labels.mini_app_market_page.replace(/:(page|last|total)/g, (_, key) => ({ page: onlinePage, last: onlineLastPage, total: data.meta.total })[key]);
    }

    async function refreshOnlineCount() {
        clearTimeout(onlineTimer);
        if (!initData || summaryStopped) return;
        if (document.hidden) { onlineTimer = setTimeout(refreshOnlineCount, 30000); return; }
        onlineRequest?.abort(); const controller = new AbortController(); onlineRequest = controller;
        const timeout = setTimeout(() => controller.abort(), 10000);
        try {
            const url = new URL(root.dataset.onlineCountEndpoint, location.href);
            if (url.origin !== location.origin) throw new Error('Invalid API origin');
            const response = await fetch(url, { credentials: 'omit', cache: 'no-store', signal: controller.signal,
                headers: { Accept: 'application/json', 'X-Telegram-Init-Data': initData } });
            const data = await response.json();
            if (response.ok && data.ok === true) onlineCount.textContent = formatNumber(data.online_count);
            else if ([401, 403].includes(response.status)) {
                summaryStopped = true; ++sequence; activeRequest?.abort(); busy = false; controls();
                onlineCount.textContent = '—'; clearContents(); chat.stop(); chat.records.clear(); chat.render(false); chat.terminal = true; chat.controls();
                showMessage(response.status === 401 ? labels.mini_app_session_expired : labels.not_linked, true); bot.hidden = false;
            }
        } catch (error) { /* A temporary count failure does not interrupt the current page. */ }
        finally { clearTimeout(timeout); if (!summaryStopped && onlineRequest === controller) onlineTimer = setTimeout(refreshOnlineCount, 30000); }
    }

    function renderLots(data) {
        for (const [key, choices] of Object.entries({ type: data.filters.types, rarity: data.filters.rarities })) {
            const select = auctionForm.elements.namedItem(key), value = select.value;
            select.replaceChildren(new Option(labels[key === 'type' ? 'auction_any_type' : 'auction_any_rarity'], ''));
            for (const choice of choices) select.append(new Option(choice.label, choice.value));
            select.value = value;
        }
        lots.replaceChildren();
        for (const lot of data.data) {
            const card = document.createElement('article');
            card.className = 'telegram-market__lot';
            const picture = document.createElement('button');
            picture.type = 'button';
            picture.className = 'telegram-market__picture';
            picture.setAttribute('aria-label', lot.name);
            const image = document.createElement('img');
            image.width = image.height = 60;
            image.loading = 'lazy';
            assignImage(image, lot.image_url, lot.name);
            picture.append(image);
            picture.addEventListener('click', () => itemDetails({ ...lot, type_label: lot.type_label || labels.mini_app_lot }));
            const description = document.createElement('div');
            description.className = 'telegram-market__description';
            const name = document.createElement('h3');
            const rarity = ['common', 'uncommon', 'rare', 'epic', 'legendary', 'heroic'].includes(lot.rarity) ? lot.rarity : 'common';
            name.className = 'rarity-' + rarity;
            name.textContent = lot.name;
            const quantity = document.createElement('p');
            quantity.textContent = '#' + lot.id + ' · ' + labels.mini_app_quantity + ': ' + formatNumber(lot.quantity);
            const price = document.createElement('p');
            price.append(document.createTextNode(labels.mini_app_total_price + ': '));
            const total = document.createElement('b');
            total.className = 'telegram-market__price';
            total.textContent = formatNumber(lot.total_price) + ' ' + labels.coins;
            price.append(total);
            const unit = document.createElement('p');
            unit.textContent = labels.mini_app_unit_price + ': ' + formatNumber(lot.unit_price);
            description.append(name, quantity, price, unit);
            card.append(picture, description);
            lots.append(card);
        }
        auctionPage = data.meta.current_page;
        auctionLastPage = data.meta.last_page;
        lotsEmpty.textContent = labels[auctionScope === 'mine' ? 'mini_app_no_lots' : 'auction_empty'];
        lotsEmpty.hidden = data.data.length > 0;
        lotsPagination.hidden = data.meta.total === 0;
        byId('lots-page').textContent = labels.mini_app_market_page.replace(/:(page|last|total)/g,
            (_, key) => ({ page: auctionPage, last: auctionLastPage, total: data.meta.total })[key]);
    }

    function translate(values, locale) {
        labels = { ...labels, ...values };
        if (['ru', 'uk'].includes(locale)) document.documentElement.lang = locale;
        document.title = byId('title').textContent = view === 'online' ? labels.profile_online : view === 'reputations' ? labels.profile_reputations : view === 'influences' ? labels.profile_influences : view === 'chat' ? labels.chat_title : view === 'character' ? labels.character
            : view === 'auction' ? labels.mini_app_auction : labels.backpack;
        for (const element of root.querySelectorAll('[data-telegram-label]')) {
            element.textContent = labels[element.dataset.telegramLabel];
        }
        navigation.setAttribute('aria-label', labels.mini_app_navigation);
        refresh.setAttribute('aria-label', labels.mini_app_refresh);
        refresh.title = labels.mini_app_refresh;
        close.setAttribute('aria-label', labels.close);
        close.title = labels.close;
        byId('detail-close').textContent = labels.close;
        empty.textContent = labels.mini_app_empty;
    }

    function clearContents() {
        grid.replaceChildren();
        categories.replaceChildren();
        lots.replaceChildren();
        for (const name of ['profile-stats', 'profile-skills', 'profile-resources', 'reputations-list', 'influences-list', 'online-list']) byId(name).replaceChildren();
        byId('profile-avatar').removeAttribute('src'); byId('profile-race').textContent = ''; byId('online-pagination').hidden = true;
        empty.hidden = lotsEmpty.hidden = lotsPagination.hidden = true;
        byId('profile-content').hidden = byId('character').hidden = true;
        for (const name of ['profile-name', 'profile-level', 'profile-money', 'profile-diamonds']) byId(name).textContent = '';
    }

    function switchView(nextView) {
        if (!initData || nextView === view || !Object.hasOwn(panels, nextView)) return;
        chat.resume = false;
        chat.stop();
        view = nextView;
        clearContents();
        for (const [key, panel] of Object.entries(panels)) panel.hidden = key !== view;
        categories.hidden = view !== 'backpack';
        profileTabs.hidden = !profileViews.includes(view);
        for (const button of profileTabs.querySelectorAll('button')) button.setAttribute('aria-pressed', String(button.dataset.profileView === view));
        byId('online-open').setAttribute('aria-pressed', String(view === 'online'));
        for (const button of navigation.querySelectorAll('[data-view]')) {
            if (button.dataset.view === view || (button.dataset.view === 'character' && profileViews.includes(view))) button.setAttribute('aria-current', 'page');
            else button.removeAttribute('aria-current');
        }
        translate({}, document.documentElement.lang);
        window.scrollTo({ top: 0, behavior: 'instant' });
        load();
    }

    async function load(nextPage = (view === 'online' ? onlinePage : auctionPage)) {
        if (!initData) return;
        const requestView = view, current = ++sequence;
        activeRequest?.abort();
        if (requestView === 'chat') {
            busy = false; controls(); message.hidden = true; bot.hidden = true; chat.start(); return;
        }
        const controller = new AbortController();
        activeRequest = controller;
        const timeout = setTimeout(() => controller.abort(), 15000);
        busy = true;
        controls();
        if (detail.open) detail.close();
        message.hidden = true;
        bot.hidden = true;
        try {
            const endpoint = requestView === 'reputations' ? root.dataset.reputationsEndpoint : requestView === 'influences' ? root.dataset.influencesEndpoint : requestView === 'online' ? root.dataset.onlineEndpoint : requestView === 'character' ? root.dataset.characterEndpoint
                : requestView === 'auction' ? root.dataset.auctionEndpoint : root.dataset.endpoint;
            const url = new URL(endpoint, window.location.href);
            if (url.origin !== window.location.origin) throw new Error('Invalid API origin');
            if (['auction', 'online'].includes(requestView)) url.searchParams.set('page', String(nextPage));
            if (requestView === 'auction') {
                url.searchParams.set('scope', auctionScope);
                if (auctionScope === 'all') {
                    for (const [key, value] of Object.entries(auctionFilters)) if (value) url.searchParams.set(key, value);
                }
            }
            const response = await fetch(url, {
                credentials: 'omit', cache: 'no-store', signal: controller.signal,
                headers: { Accept: 'application/json', 'X-Telegram-Init-Data': initData },
            });
            const data = await response.json().catch(() => ({}));
            if (current !== sequence) return;
            if (!response.ok || data.ok !== true) {
                clearContents();
                const text = response.status === 401 ? labels.mini_app_session_expired
                    : response.status === 403 ? (data.code === 'telegram_not_linked' ? labels.not_linked : labels.account_unavailable)
                    : response.status === 429 ? labels.too_many_attempts : labels.mini_app_load_failed;
                showMessage(text, true);
                bot.hidden = ![401, 403].includes(response.status);
                return;
            }
            translate(data.labels, data.locale);
            if (summaryStopped) { summaryStopped = false; refreshOnlineCount(); }
            byId('character').textContent = data.character.nickname + ' [' + data.character.level + ']';
            byId('character').hidden = false;
            if (requestView === 'character') renderProfile(data.character);
            else if (requestView === 'auction') renderLots(data);
            else if (requestView === 'online') renderOnline(data);
            else if (['reputations', 'influences'].includes(requestView)) renderProgressions(data.data, requestView);
            else {
                renderCategories(data.categories, data.data);
                updateToolbarHeight();
                if (initialScroll) {
                    initialScroll = false;
                    if (params.has('category') && category !== data.categories[0]?.key) selectCategory(category);
                }
            }
            message.hidden = true;
        } catch (error) {
            if (current === sequence) showMessage(labels.connection_failed, true);
        } finally {
            clearTimeout(timeout);
            if (current === sequence) { busy = false; controls(); }
        }
    }

    const toolbar = byId('toolbar');
    const updateToolbarHeight = () => root.style.setProperty('--toolbar-height', (toolbar.offsetHeight + 12) + 'px');
    new ResizeObserver(updateToolbarHeight).observe(toolbar);
    refresh.addEventListener('click', () => load());
    for (const button of root.querySelectorAll('[data-view]')) {
        button.addEventListener('click', () => switchView(button.dataset.view));
    }
    for (const button of profileTabs.querySelectorAll('[data-profile-view]')) button.addEventListener('click', () => switchView(button.dataset.profileView));
    for (const button of root.querySelectorAll('[data-auction-scope]')) button.addEventListener('click', () => {
        if (!initData || button.dataset.auctionScope === auctionScope) return;
        auctionScope = button.dataset.auctionScope; auctionPage = 1;
        for (const tab of root.querySelectorAll('[data-auction-scope]')) tab.setAttribute('aria-pressed', String(tab.dataset.auctionScope === auctionScope));
        auctionForm.hidden = auctionScope === 'mine';
        lots.replaceChildren(); lotsEmpty.hidden = lotsPagination.hidden = true;
        load(1);
    });
    auctionForm.addEventListener('submit', event => {
        event.preventDefault();
        auctionFilters = Object.fromEntries(['name', 'type', 'rarity'].map(key => [key, auctionForm.elements.namedItem(key).value.trim()]));
        auctionPage = 1; load(1);
    });
    auctionForm.addEventListener('reset', () => { auctionFilters = { name: '', type: '', rarity: '' }; auctionPage = 1; load(1); });
    byId('online-previous').addEventListener('click', () => load(onlinePage - 1));
    byId('online-next').addEventListener('click', () => load(onlinePage + 1));
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshOnlineCount(); });
    window.addEventListener('pagehide', () => { clearTimeout(onlineTimer); onlineRequest?.abort(); });
    lotsPrevious.addEventListener('click', () => load(auctionPage - 1));
    lotsNext.addEventListener('click', () => load(auctionPage + 1));
    close.addEventListener('click', () => webApp?.close());
    byId('detail-close').addEventListener('click', () => detail.close());
    detail.addEventListener('click', event => { if (event.target === detail) detail.close(); });
    controls();
    if (!initData) {
        showMessage(labels.mini_app_open_from_bot);
        bot.hidden = false;
        for (const button of root.querySelectorAll('[data-view], [data-profile-view]')) button.disabled = true;
        return;
    }
    close.hidden = false;
    webApp.ready();
    webApp.expand();
    load();
    refreshOnlineCount();
})();
