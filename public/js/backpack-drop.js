(function () {
    'use strict';

    var pending = false;

    window.dropBackpackItem = async function (itemId, url, quantity, csrfToken) {
        if (pending) return;
        pending = true;
        var button = document.querySelector('#ask_confirm_ok_container input');
        if (button) button.disabled = true;

        try {
            var response = await fetch(url, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ c: 1, qty: quantity }),
            });
            var data = await response.json();
            if (!response.ok || !data.ok || !data.backpack_update) {
                throw new Error(data.message || gameTranslate('Не удалось выбросить предмет.'));
            }

            var update = data.backpack_update;
            document.querySelectorAll('td[data-id="' + itemId + '"]').forEach(function (cell) {
                if (update.removed) {
                    var item = cell.closest('li.item');
                    if (item) item.remove();
                    return;
                }
                cell.setAttribute('data-count', update.count);
                var badge = cell.querySelector('.bpdig');
                if (update.count > 1) {
                    if (!badge) {
                        badge = document.createElement('div');
                        badge.className = 'bpdig';
                        cell.appendChild(badge);
                    }
                    badge.textContent = update.count;
                } else if (badge) {
                    badge.remove();
                }
            });
            var capacity = document.getElementById('artifact_amount');
            if (capacity) capacity.textContent = update.capacity_used;
            closeDropPopup();
        } catch (error) {
            var message = error.message || gameTranslate('Не удалось выбросить предмет.');
            try { window.top.showErrorIframe(message); } catch (_) {}
        } finally {
            pending = false;
            if (button) button.disabled = false;
        }
    };
})();
