(() => {
    const opener = document.getElementById('change-password-open');
    const modal = document.getElementById('password-modal');
    const overlay = document.getElementById('password-overlay');
    const form = document.getElementById('change-password-form');
    const message = document.getElementById('password-message');
    const save = document.getElementById('password-save');
    let busy = false;
    let previousFocus;
    if (!opener || !modal || !form) return;
    const confirmation = form.elements.password_confirmation;
    const confirmationError = document.getElementById('password-confirmation-error');

    function validateConfirmation(force = false) {
        const mismatch = (force || confirmation.value.length > 0)
            && form.elements.password.value !== confirmation.value;
        confirmationError.hidden = !mismatch;
        confirmation.setCustomValidity(mismatch ? 'Пароли не совпадают.' : '');
        confirmation.setAttribute('aria-invalid', String(mismatch));
        save.disabled = busy || mismatch;
        return !mismatch;
    }

    [form.elements.password, confirmation].forEach(input => {
        input.addEventListener('input', () => validateConfirmation());
        input.addEventListener('change', () => validateConfirmation());
    });

    function close() {
        if (busy) return;
        modal.hidden = overlay.hidden = true;
        form.reset();
        validateConfirmation();
        message.hidden = true;
        previousFocus?.focus();
    }

    function showMessage(text, success = false) {
        message.textContent = text;
        message.classList.toggle('is-success', success);
        message.hidden = false;
    }

    opener.addEventListener('click', () => {
        previousFocus = document.activeElement;
        form.reset();
        validateConfirmation();
        message.hidden = true;
        modal.hidden = overlay.hidden = false;
        form.elements.current_password.focus();
    });
    document.getElementById('password-cancel').addEventListener('click', close);
    document.getElementById('password-modal-close').addEventListener('click', close);
    overlay.addEventListener('click', close);
    document.addEventListener('keydown', event => {
        if (modal.hidden) return;
        if (event.key === 'Escape') close();
        if (event.key === 'Tab') {
            const controls = Array.from(modal.querySelectorAll('input,button')).filter(el => !el.disabled && el.type !== 'hidden');
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault(); last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault(); first.focus();
            }
        }
    });
    form.addEventListener('submit', async event => {
        event.preventDefault();
        if (busy) return;
        if (!validateConfirmation(true)) {
            confirmation.focus();
            return;
        }
        busy = true;
        save.disabled = true;
        save.value = 'Сохраняем…';
        message.hidden = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST', credentials: 'same-origin',
                headers: { Accept: 'application/json' }, body: new FormData(form),
            });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const errors = Object.values(data.errors || {}).flat();
                const fallback = response.status === 419 ? 'Сессия устарела. Обновите страницу и повторите попытку.'
                    : response.status === 429 ? 'Слишком много попыток. Попробуйте через минуту.'
                    : response.status === 401 ? 'Войдите в игру заново.' : 'Не удалось изменить пароль. Попробуйте позже.';
                showMessage(errors.join(' ') || fallback);
                return;
            }
            form.reset();
            showMessage(data.message || 'Пароль успешно изменён.', true);
        } catch (error) {
            showMessage('Не удалось связаться с сервером. Попробуйте ещё раз.');
        } finally {
            busy = false;
            validateConfirmation();
            save.value = 'Сохранить';
        }
    });
})();
