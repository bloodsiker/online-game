(() => {
    const root = document.getElementById('telegram-backpack-app');
    if (!root) return;
    let labels = JSON.parse(root.dataset.translations || '{}');
    const byId = name => document.getElementById('telegram-backpack-' + name);
    const grid = byId('items'), categories = byId('categories'), message = byId('message');
    const refresh = byId('refresh');
    const empty = byId('empty'), bot = byId('bot'), close = byId('close');
    const detail = byId('detail'), navigation = byId('navigation');
    const panels = { backpack: byId('inventory'), character: byId('profile'), auction: byId('auction') };
    const lots = byId('lots'), lotsEmpty = byId('lots-empty'), lotsPagination = byId('lots-pagination');
    const lotsPrevious = byId('lots-previous'), lotsNext = byId('lots-next');
    let view = 'backpack', auctionPage = 1, auctionLastPage = 1;
    const webApp = window.Telegram?.WebApp;
    const initData = webApp?.initData || '';
    const params = new URLSearchParams(window.location.search);
    const validCategories = ['main', 'misc', 'key', 'quest', 'artifact', 'gift'];
    let category = validCategories.includes(params.get('category')) ? params.get('category') : 'main';
    let busy = false, sequence = 0, activeRequest, initialScroll = true;

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
            image.width = image.height = 60;
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

    function renderProfile(character) {
        byId('profile-name').textContent = character.nickname;
        byId('profile-level').textContent = character.level;
        byId('profile-money').textContent = formatNumber(character.money);
        byId('profile-diamonds').textContent = formatNumber(character.diamonds);
        byId('profile-content').hidden = false;
    }

    function renderLots(data) {
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
            picture.addEventListener('click', () => itemDetails({ ...lot, type_label: labels.mini_app_lot }));
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
        lotsEmpty.hidden = data.data.length > 0;
        lotsPagination.hidden = data.meta.total === 0;
        byId('lots-page').textContent = labels.mini_app_market_page.replace(/:(page|last|total)/g,
            (_, key) => ({ page: auctionPage, last: auctionLastPage, total: data.meta.total })[key]);
    }

    function translate(values, locale) {
        labels = { ...labels, ...values };
        if (['ru', 'uk'].includes(locale)) document.documentElement.lang = locale;
        document.title = byId('title').textContent = view === 'character' ? labels.character
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
        empty.hidden = lotsEmpty.hidden = lotsPagination.hidden = true;
        byId('profile-content').hidden = byId('character').hidden = true;
        for (const name of ['profile-name', 'profile-level', 'profile-money', 'profile-diamonds']) byId(name).textContent = '';
    }

    function switchView(nextView) {
        if (!initData || nextView === view || !Object.hasOwn(panels, nextView)) return;
        view = nextView;
        clearContents();
        for (const [key, panel] of Object.entries(panels)) panel.hidden = key !== view;
        categories.hidden = view !== 'backpack';
        for (const button of navigation.querySelectorAll('[data-view]')) {
            if (button.dataset.view === view) button.setAttribute('aria-current', 'page');
            else button.removeAttribute('aria-current');
        }
        translate({}, document.documentElement.lang);
        window.scrollTo({ top: 0, behavior: 'instant' });
        load();
    }

    async function load(nextPage = auctionPage) {
        if (!initData) return;
        const requestView = view, current = ++sequence;
        activeRequest?.abort();
        const controller = new AbortController();
        activeRequest = controller;
        const timeout = setTimeout(() => controller.abort(), 15000);
        busy = true;
        controls();
        if (detail.open) detail.close();
        message.hidden = true;
        bot.hidden = true;
        try {
            const endpoint = requestView === 'character' ? root.dataset.characterEndpoint
                : requestView === 'auction' ? root.dataset.auctionEndpoint : root.dataset.endpoint;
            const url = new URL(endpoint, window.location.href);
            if (url.origin !== window.location.origin) throw new Error('Invalid API origin');
            if (requestView === 'auction') url.searchParams.set('page', String(nextPage));
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
            byId('character').textContent = data.character.nickname + ' [' + data.character.level + ']';
            byId('character').hidden = false;
            if (requestView === 'character') renderProfile(data.character);
            else if (requestView === 'auction') renderLots(data);
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
    for (const button of navigation.querySelectorAll('[data-view]')) {
        button.addEventListener('click', () => switchView(button.dataset.view));
    }
    lotsPrevious.addEventListener('click', () => load(auctionPage - 1));
    lotsNext.addEventListener('click', () => load(auctionPage + 1));
    close.addEventListener('click', () => webApp?.close());
    byId('detail-close').addEventListener('click', () => detail.close());
    detail.addEventListener('click', event => { if (event.target === detail) detail.close(); });
    controls();
    if (!initData) {
        showMessage(labels.mini_app_open_from_bot);
        bot.hidden = false;
        for (const button of navigation.querySelectorAll('button')) button.disabled = true;
        return;
    }
    close.hidden = false;
    webApp.ready();
    webApp.expand();
    load();
})();
