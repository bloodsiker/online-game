(function (root) {
    'use strict';

    // Одна очередь на корзину: ответы разных строк не перезаписывают итог вразнобой.
    function createQueue(options) {
        const dirty = new Map();
        let timer = null;
        let pending = null;

        function flush() {
            root.clearTimeout(timer);
            timer = null;
            if (pending) return pending;

            pending = (async function () {
                while (dirty.size) {
                    const [id, quantity] = dirty.entries().next().value;
                    const response = await options.send(id, quantity);
                    if (dirty.get(id) === quantity) dirty.delete(id);
                    if (options.onSaved) options.onSaved(id, response);
                }
            })().finally(function () { pending = null; });

            return pending;
        }

        return {
            set: function (id, quantity) {
                dirty.set(id, quantity);
                root.clearTimeout(timer);
                timer = root.setTimeout(function () {
                    flush().catch(options.onError || function () {});
                }, 300);
            },
            flush: flush
        };
    }

    root.createShopCartQuantityQueue = createQueue;
    if (!root.document) return;

    root.document.querySelectorAll('[data-cart-quantity-form]').forEach(function (form) {
        const values = new Map();
        let submitting = false;
        const message = form.dataset.saveError;

        function showError(error) {
            const text = error.message || message;
            try {
                if (root.top && root.top.showErrorIframe) {
                    root.top.showErrorIframe(text);
                    return;
                }
            } catch (_) {}
            root.alert(text);
        }

        function setText(selector, value, container) {
            const element = (container || form).querySelector(selector);
            if (element && value !== undefined) element.textContent = value;
        }

        const queue = createQueue({
            send: async function (id, quantity) {
                const response = await root.fetch(form.dataset.updateUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': form.querySelector('[name="_token"]').value
                    },
                    body: JSON.stringify({shop_item_id: Number(id), quantity: quantity})
                });
                const data = await response.json().catch(function () { return {}; });
                if (!response.ok) throw new Error(data.message || message);
                return data;
            },
            onSaved: function (id, data) {
                const row = form.querySelector('[data-cart-row="' + id + '"]');
                if (row) {
                    setText('[data-cart-line-price]', data.line_price, row);
                    setText('[data-cart-line-diamond]', data.line_diamond, row);
                }
                setText('[data-cart-total-price]', data.total_price);
                setText('[data-cart-total-diamond]', data.total_diamond);
            },
            onError: showError
        });

        form.querySelectorAll('[data-cart-quantity]').forEach(function (input) {
            values.set(input.dataset.cartQuantity, Number(input.value));
        });

        form.addEventListener('shop-cart-quantity-change', function (event) {
            const input = event.target;
            if (!input.hasAttribute('data-cart-quantity') || submitting) return;
            const id = input.dataset.cartQuantity;
            const quantity = Number(input.value);
            if (values.get(id) === quantity) return;
            values.set(id, quantity);
            queue.set(id, quantity);
        });

        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (submitting) return;
            submitting = true;
            const controls = form.querySelectorAll('[data-cart-quantity], [type="submit"]');
            controls.forEach(function (control) { control.disabled = true; });
            try {
                // Даже при мгновенном нажатии оплаты сервер получит последнее количество.
                await queue.flush();
                root.HTMLFormElement.prototype.submit.call(form);
            } catch (error) {
                submitting = false;
                controls.forEach(function (control) { control.disabled = false; });
                showError(error);
            }
        });
    });
})(typeof window === 'undefined' ? globalThis : window);
