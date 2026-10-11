(() => {
    let closeCurrent = null;

    document.querySelectorAll('.profile-select-wrap select').forEach((select, number) => {
        const wrap = select.parentElement;
        const width = select.getBoundingClientRect().width;
        const originalId = select.id;
        const id = originalId || `profile-select-${number}`;
        const trigger = document.createElement('button');
        const list = document.createElement('div');
        let highlighted = select.selectedIndex;

        trigger.type = 'button';
        trigger.className = 'profile-select profile-select-trigger';
        trigger.style.width = `${width}px`;
        trigger.id = id;
        trigger.setAttribute('role', 'combobox');
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');
        trigger.setAttribute('aria-controls', `${id}-options`);
        if (select.hasAttribute('aria-label')) {
            trigger.setAttribute('aria-label', select.getAttribute('aria-label'));
        }

        list.className = 'profile-select-list';
        list.id = `${id}-options`;
        list.hidden = true;
        list.setAttribute('role', 'listbox');
        list.setAttribute('aria-labelledby', id);

        // Оставляем настоящий select для значения формы и событий change.
        select.id = `${id}-native`;
        select.hidden = true;
        select.tabIndex = -1;
        wrap.append(trigger, list);

        const options = Array.from(select.options, (option, index) => {
            const item = document.createElement('div');
            item.className = 'profile-select-option';
            item.id = `${id}-option-${index}`;
            item.textContent = option.textContent;
            item.setAttribute('role', 'option');
            item.addEventListener('mousemove', () => highlight(index, false));
            item.addEventListener('mousedown', event => event.preventDefault());
            item.addEventListener('click', () => choose(index));
            list.append(item);
            return item;
        });

        function sync() {
            trigger.textContent = select.options[select.selectedIndex]?.textContent || '--';
            options.forEach((item, index) => item.setAttribute('aria-selected', String(index === select.selectedIndex)));
        }

        function highlight(index, scroll = true) {
            highlighted = Math.max(0, Math.min(index, options.length - 1));
            options.forEach((item, i) => item.classList.toggle('is-highlighted', i === highlighted));
            trigger.setAttribute('aria-activedescendant', options[highlighted].id);
            if (scroll) options[highlighted].scrollIntoView({ block: 'nearest' });
        }

        function close() {
            list.hidden = true;
            wrap.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.removeAttribute('aria-activedescendant');
            if (closeCurrent === close) closeCurrent = null;
        }

        function open() {
            if (closeCurrent) closeCurrent();
            closeCurrent = close;
            list.hidden = false;
            wrap.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            highlight(select.selectedIndex);
        }

        function choose(index) {
            const changed = select.selectedIndex !== index;
            select.selectedIndex = index;
            sync();
            close();
            trigger.focus();
            if (changed) select.dispatchEvent(new Event('change', { bubbles: true }));
        }

        trigger.addEventListener('click', () => list.hidden ? open() : close());
        trigger.addEventListener('keydown', event => {
            if (event.key === 'Escape' || event.key === 'Tab') {
                close();
                if (event.key === 'Escape') event.preventDefault();
                return;
            }
            if (['ArrowDown', 'ArrowUp', 'Home', 'End'].includes(event.key)) {
                event.preventDefault();
                if (list.hidden) open();
                const index = event.key === 'Home' ? 0 : event.key === 'End' ? options.length - 1
                    : highlighted + (event.key === 'ArrowDown' ? 1 : -1);
                highlight(index);
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                if (list.hidden) open();
                else choose(highlighted);
            } else if (event.key.length === 1 && !event.ctrlKey && !event.metaKey && !event.altKey) {
                const index = Array.from(select.options).findIndex(option => option.textContent.toLocaleLowerCase().startsWith(event.key.toLocaleLowerCase()));
                if (index >= 0) {
                    event.preventDefault();
                    if (list.hidden) open();
                    highlight(index);
                }
            }
        });
        wrap.addEventListener('focusout', event => {
            if (!wrap.contains(event.relatedTarget)) close();
        });
        select.addEventListener('change', sync);
        sync();
    });

    document.addEventListener('click', event => {
        if (!event.target.closest('.profile-select-wrap') && closeCurrent) closeCurrent();
    });
})();
