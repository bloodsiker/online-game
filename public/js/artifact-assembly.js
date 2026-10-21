(function () {
    'use strict';

    var busy = false;
    var messages = window.artifactAssemblyMessages || {
        refreshFailed: 'Не удалось обновить количество частей. Обновите страницу.',
        assemblyFailed: 'Не удалось собрать артефакт.',
        resultChanged: 'Артефакт получен. Обновите страницу, чтобы увидеть результат.',
    };

    function landingSteps(length, current, target) {
        var turns = Math.max(8, length * 3);
        return turns + ((target - ((current + turns) % length) + length) % length);
    }

    function animateLanding(length, current, target, highlight) {
        var steps = landingSteps(length, current, target);
        return new Promise(function (resolve) {
            var step = 0;
            function next() {
                current = (current + 1) % length;
                highlight(current);
                step++;
                if (step === steps) {
                    resolve(current);
                    return;
                }
                var progress = step / steps;
                setTimeout(next, 65 + Math.round(430 * progress * progress));
            }
            setTimeout(next, 65);
        });
    }

    function showMessage(assembly, message) {
        var status = assembly.querySelector('.artifact-assembly__status');
        if (!status) {
            status = document.createElement('div');
            status.className = 'artifact-assembly__status';
            status.setAttribute('role', 'status');
            status.style.marginTop = '12px';
            assembly.querySelector('.gp-frame-bg').appendChild(status);
        }
        status.textContent = message;
    }

    async function refreshParts(assembly) {
        var response = await fetch(window.location.href, { credentials: 'same-origin', headers: { 'Accept': 'text/html' } });
        if (!response.ok) throw new Error(messages.refreshFailed);
        var page = new DOMParser().parseFromString(await response.text(), 'text/html');
        var fresh = page.querySelector('[data-assembly-id="' + assembly.dataset.assemblyId + '"]');
        if (!fresh) throw new Error(messages.refreshFailed);
        assembly.querySelector('.artifact-assembly__ingredients').replaceWith(fresh.querySelector('.artifact-assembly__ingredients'));
        assembly.querySelector('.artifact-assemble-button').closest('.butt1').replaceWith(fresh.querySelector('.artifact-assemble-button').closest('.butt1'));
    }

    async function assemble(button) {
        if (busy || button.disabled || !button.dataset.href) return;
        var assembly = button.closest('[data-assembly-id]');
        var items = Array.from(assembly.querySelectorAll('[data-result-id]'));
        if (!items.length) return;
        busy = true;
        button.disabled = true;
        assembly.classList.add('is-assembling');
        assembly.setAttribute('aria-busy', 'true');
        items.forEach(function (item) { item.classList.remove('is-highlighted', 'is-winner'); });
        showMessage(assembly, '');
        var current = -1;
        var timer;
        var spinning = true;
        function highlight(index) {
            items.forEach(function (item, itemIndex) { item.classList.toggle('is-highlighted', itemIndex === index); });
        }
        function spin() {
            if (!spinning) return;
            current = (current + 1) % items.length;
            highlight(current);
            timer = setTimeout(spin, 60);
        }
        spin();

        try {
            var response = await fetch(button.dataset.href, {
                method: 'POST', credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
            });
            var result = await response.json();
            spinning = false;
            clearTimeout(timer);
            if (!response.ok || !result.ok) throw new Error(result.message || messages.assemblyFailed);

            if (result.success) {
                var target = items.findIndex(function (item) { return Number(item.dataset.resultId) === Number(result.crafted_share_item_id); });
                if (target < 0) throw new Error(messages.resultChanged);
                current = await animateLanding(items.length, current, target, highlight);
                items[current].classList.add('is-winner');
                var preview = assembly.querySelector('.artifact-assembly__result-preview');
                var icon = preview.querySelector('.upgrade-icon');
                icon.classList.remove('random-result-icon');
                icon.textContent = '';
                icon.appendChild(items[current].querySelector('img').cloneNode(true));
                icon.dataset.id = result.crafted_share_item_id;
                icon.onmouseover = function (event) { showItemInfo(icon, event, 2); };
                icon.onmouseout = function (event) { showItemInfo(icon, event, 0); };
                var name = preview.querySelector('.artifact-assembly__result-name');
                var poolName = items[current].querySelector('small');
                if (name) {
                    name.textContent = poolName ? poolName.textContent : icon.querySelector('img').alt;
                    if (poolName) name.style.color = poolName.style.color;
                }
                icon.removeAttribute('title');
            } else {
                items.forEach(function (item) { item.classList.remove('is-highlighted'); });
            }
            showMessage(assembly, result.message);
        } catch (error) {
            spinning = false;
            clearTimeout(timer);
            items.forEach(function (item) { item.classList.remove('is-highlighted'); });
            showMessage(assembly, error.message || messages.assemblyFailed);
        } finally {
            try {
                await refreshParts(assembly);
            } catch (error) {
                showMessage(assembly, error.message);
            }
            assembly.classList.remove('is-assembling');
            assembly.setAttribute('aria-busy', 'false');
            busy = false;
        }
    }

    document.addEventListener('click', function (event) {
        var button = event.target.closest('.artifact-assemble-button');
        if (!button) return;
        event.preventDefault();
        assemble(button);
    });

    window.ArtifactAssembly = { landingSteps: landingSteps, animateLanding: animateLanding };
})();
