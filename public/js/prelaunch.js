(() => {
    'use strict';

    const modal = document.getElementById('prelaunch-modal');
    if (!modal) return;
    const form = modal.querySelector('form');
    const email = form.querySelector('[name="email"]');
    const result = modal.querySelector('[role="status"]');
    let opener = null;

    function open(trigger) {
        opener = trigger || document.activeElement;
        if (!modal.open) modal.showModal();
        document.body.classList.add('prelaunch-modal-open');
        const signupLink = modal.querySelector('[data-prelaunch-signup]');
        (signupLink.hidden ? modal.querySelector('[data-prelaunch-close]') : signupLink).focus();
    }

    document.addEventListener('click', event => {
        const trigger = event.target.closest('[data-prelaunch-open]');
        if (!trigger) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        open(trigger);
    }, true);

    document.addEventListener('keydown', event => {
        const trigger = event.target.closest('[data-prelaunch-open][role="button"]');
        if (trigger && (event.key === 'Enter' || event.key === ' ')) {
            event.preventDefault();
            trigger.click();
        }
    });

    document.addEventListener('submit', event => {
        if (!event.target.matches('[data-prelaunch-login]')) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        open(event.submitter);
    }, true);

    modal.querySelector('[data-prelaunch-close]').addEventListener('click', () => modal.close());
    modal.addEventListener('click', event => {
        if (event.target !== modal) return;
        const rect = modal.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) modal.close();
    });
    modal.addEventListener('close', () => {
        document.body.classList.remove('prelaunch-modal-open');
        if (opener && opener.isConnected) opener.focus();
    });
    modal.querySelector('[data-prelaunch-signup]').addEventListener('click', event => {
        event.preventDefault();
        form.hidden = false;
        email.focus();
    });

    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        const button = form.querySelector('[type="submit"]');
        if (button.disabled) return;
        const originalLabel = button.textContent;
        button.disabled = true;
        button.textContent = form.dataset.sending;
        result.hidden = true;
        result.classList.remove('is-error');
        let failureMessage = form.dataset.error;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                body: new FormData(form),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok || data.ok !== true) {
                failureMessage = response.status === 419 ? form.dataset.expired
                    : response.status === 429 ? form.dataset.rateLimited
                    : data.errors?.email?.[0] || (response.status === 422 ? data.message : null) || form.dataset.error;
                throw new Error(failureMessage);
            }
            result.textContent = data.message;
            form.hidden = true;
            modal.querySelector('[data-prelaunch-signup]').hidden = true;
        } catch (error) {
            result.textContent = failureMessage;
            result.classList.add('is-error');
        } finally {
            result.hidden = false;
            button.disabled = false;
            button.textContent = originalLabel;
        }
    });

    if (modal.dataset.openOnLoad === '1') open();
})();
