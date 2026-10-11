(() => {
    const options = document.querySelectorAll('[data-setting-hint]');
    if (!options.length) return;

    const tooltip = document.createElement('div');
    tooltip.id = 'game-settings-tooltip';
    tooltip.className = 'game-settings-tooltip';
    tooltip.setAttribute('role', 'tooltip');
    tooltip.hidden = true;
    document.body.appendChild(tooltip);

    let active = null;
    let describedControl = null;
    let previousDescription = null;

    function hide() {
        tooltip.hidden = true;
        active = null;
        if (describedControl) {
            if (previousDescription === null) describedControl.removeAttribute('aria-describedby');
            else describedControl.setAttribute('aria-describedby', previousDescription);
        }
        describedControl = null;
    }

    function position(x, y) {
        const margin = 10;
        const width = tooltip.offsetWidth;
        const height = tooltip.offsetHeight;
        const left = x + width + margin > window.innerWidth ? x - width - margin : x + margin;
        const top = y + height + margin > window.innerHeight ? y - height - margin : y + margin;
        tooltip.style.left = Math.max(margin, Math.min(left, window.innerWidth - width - margin)) + 'px';
        tooltip.style.top = Math.max(margin, Math.min(top, window.innerHeight - height - margin)) + 'px';
    }

    function show(option, event) {
        hide();
        active = option;
        tooltip.textContent = option.dataset.settingHint;
        tooltip.hidden = false;
        describedControl = option.querySelector('input:not([type="hidden"]), button, select');
        if (describedControl) {
            previousDescription = describedControl.getAttribute('aria-describedby');
            describedControl.setAttribute('aria-describedby', [previousDescription, tooltip.id].filter(Boolean).join(' '));
        }
        if (event.type === 'mouseenter') position(event.clientX, event.clientY);
        else {
            const rect = option.getBoundingClientRect();
            position(rect.left, rect.bottom);
        }
    }

    options.forEach(option => {
        option.addEventListener('mouseenter', event => show(option, event));
        option.addEventListener('mousemove', event => {
            if (active === option) position(event.clientX, event.clientY);
        });
        option.addEventListener('mouseleave', hide);
        option.addEventListener('focusin', event => show(option, event));
        option.addEventListener('focusout', event => {
            if (!option.contains(event.relatedTarget)) hide();
        });
        option.addEventListener('click', hide);
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape') hide();
    });
    document.addEventListener('scroll', hide, true);
    window.addEventListener('resize', hide);
    window.addEventListener('pagehide', hide);
    document.querySelector('.game-settings')?.addEventListener('submit', hide);
})();
