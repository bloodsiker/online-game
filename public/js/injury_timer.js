document.addEventListener('DOMContentLoaded', function () {
    const bandages = Array.from(document.querySelectorAll('[data-injury-expires]'));
    if (bandages.length === 0) {
        return;
    }

    let reloading = false;
    const tick = function () {
        const now = Math.floor(Date.now() / 1000);

        bandages.forEach(function (bandage) {
            const remaining = Math.max(0, Number(bandage.dataset.injuryExpires) - now);
            const timer = bandage.querySelector('.injury-bandage__timer');
            if (timer) {
                const minutes = String(Math.floor(remaining / 60)).padStart(2, '0');
                const seconds = String(remaining % 60).padStart(2, '0');
                timer.textContent = minutes + ':' + seconds;
            }

            if (remaining === 0 && !reloading) {
                reloading = true;
                window.location.reload();
            }
        });
    };

    tick();
    window.setInterval(tick, 1000);
});
