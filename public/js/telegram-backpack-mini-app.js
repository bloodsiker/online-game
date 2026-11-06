(() => {
    const root = document.getElementById('telegram-backpack-app');
    if (!root) return;
    let labels = JSON.parse(root.dataset.translations || '{}');
    const byId = name => document.getElementById('telegram-backpack-' + name);
    const grid = byId('items'), categories = byId('categories'), message = byId('message');
    const previous = byId('previous'), next = byId('next'), refresh = byId('refresh');
    const pageLabel = byId('page'), empty = byId('empty'), bot = byId('bot'), close = byId('close');
    const detail = byId('detail');
    const webApp = window.Telegram?.WebApp;
    const initData = webApp?.initData || '';
    const params = new URLSearchParams(window.location.search);
    const validCategories = ['main', 'misc', 'key', 'quest', 'artifact', 'gift'];
    let category = validCategories.includes(params.get('category')) ? params.get('category') : 'main';
    let page = Math.max(1, Math.min(100000, Math.trunc(Number(params.get('page'))) || 1));
    let requested = { category, page }, lastPage = 1, busy = false, sequence = 0, activeRequest;

    function showMessage(text, error = false) {
        message.textContent = text;
        message.classList.toggle('is-error', error);
        message.hidden = false;
    }

    function controls() {
        previous.disabled = busy || page <= 1;
        next.disabled = busy || page >= lastPage;
        refresh.disabled = busy || !initData;
        root.classList.toggle('is-loading', busy);
        grid.setAttribute('aria-busy', String(busy));
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

    function renderItems(items) {
        grid.replaceChildren();
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
            grid.append(button);
        }
        empty.hidden = items.length > 0;
    }

    function renderCategories(groups) {
        categories.replaceChildren();
        for (const group of groups) {
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
            button.addEventListener('click', () => load(group.key, 1));
            categories.append(button);
        }
    }

    function translate(values, locale) {
        labels = { ...labels, ...values };
        if (['ru', 'uk'].includes(locale)) document.documentElement.lang = locale;
        document.title = byId('title').textContent = labels.backpack;
        refresh.setAttribute('aria-label', labels.mini_app_refresh);
        refresh.title = labels.mini_app_refresh;
        close.setAttribute('aria-label', labels.close);
        close.title = labels.close;
        byId('detail-close').textContent = labels.close;
        previous.textContent = '← ' + labels.previous;
        next.textContent = labels.next + ' →';
        empty.textContent = labels.backpack_empty;
    }

    async function load(nextCategory, nextPage) {
        if (!initData) return;
        requested = { category: nextCategory, page: nextPage };
        const current = ++sequence;
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
            const url = new URL(root.dataset.endpoint, window.location.href);
            if (url.origin !== window.location.origin) throw new Error('Invalid API origin');
            url.searchParams.set('category', nextCategory);
            url.searchParams.set('page', String(nextPage));
            url.searchParams.set('per_page', '8');
            const response = await fetch(url, {
                credentials: 'omit', cache: 'no-store', signal: controller.signal,
                headers: { Accept: 'application/json', 'X-Telegram-Init-Data': initData },
            });
            const data = await response.json().catch(() => ({}));
            if (current !== sequence) return;
            if (!response.ok || data.ok !== true) {
                grid.replaceChildren(); categories.replaceChildren(); empty.hidden = true;
                pageLabel.textContent = '';
                previous.disabled = next.disabled = true;
                const text = response.status === 401 ? labels.mini_app_session_expired
                    : response.status === 403 ? (data.code === 'telegram_not_linked' ? labels.not_linked : labels.account_unavailable)
                    : response.status === 429 ? labels.too_many_attempts : labels.mini_app_load_failed;
                showMessage(text, true);
                bot.hidden = ![401, 403].includes(response.status);
                lastPage = page = 1;
                return;
            }
            translate(data.labels, data.locale);
            category = data.category;
            page = data.meta.current_page;
            lastPage = data.meta.last_page;
            byId('character').textContent = data.character.nickname + ' [' + data.character.level + ']';
            byId('character').hidden = false;
            renderCategories(data.categories);
            renderItems(data.data);
            pageLabel.textContent = labels.mini_app_page.replace(/:(page|last|total)/g, (_, key) => ({ page, last: lastPage, total: data.meta.total })[key]);
            message.hidden = true;
        } catch (error) {
            if (current === sequence) showMessage(labels.connection_failed, true);
        } finally {
            clearTimeout(timeout);
            if (current === sequence) { busy = false; controls(); }
        }
    }

    previous.addEventListener('click', () => load(category, page - 1));
    next.addEventListener('click', () => load(category, page + 1));
    refresh.addEventListener('click', () => load(requested.category, requested.page));
    close.addEventListener('click', () => webApp?.close());
    byId('detail-close').addEventListener('click', () => detail.close());
    detail.addEventListener('click', event => { if (event.target === detail) detail.close(); });
    controls();
    if (!initData) {
        showMessage(labels.mini_app_open_from_bot);
        bot.hidden = false;
        return;
    }
    close.hidden = false;
    const applyTheme = () => {
        if (['light', 'dark'].includes(webApp.colorScheme)) document.documentElement.dataset.theme = webApp.colorScheme;
    };
    applyTheme();
    webApp.onEvent?.('themeChanged', applyTheme);
    webApp.ready();
    webApp.expand();
    load(category, page);
})();
