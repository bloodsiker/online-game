export function initializeGallery(gallery, dialog) {
    const page = gallery.ownerDocument;
    const thumbnails = Array.from(gallery.querySelectorAll('[data-gallery-index]'));
    const preview = gallery.querySelector('[data-gallery-preview]');
    const previewImage = preview?.querySelector('img');
    const track = gallery.querySelector('[data-gallery-track]');
    if (!previewImage || thumbnails.length === 0 || !dialog) return null;

    const fullImage = dialog.querySelector('[data-lightbox-image]');
    const counter = dialog.querySelector('[data-lightbox-counter]');
    let selected = 0;

    function select(index) {
        selected = (index + thumbnails.length) % thumbnails.length;
        const thumbnail = thumbnails[selected];
        preview.href = thumbnail.href;
        previewImage.src = thumbnail.href;
        previewImage.alt = thumbnail.querySelector('img').alt;
        thumbnails.forEach((item, position) => item.setAttribute('aria-current', position === selected ? 'true' : 'false'));

        if (track) {
            const left = thumbnail.offsetLeft - track.offsetLeft - (track.clientWidth - thumbnail.offsetWidth) / 2;
            track.scrollTo({ left: Math.max(0, left), behavior: 'auto' });
        }
        if (dialog.open) {
            fullImage.src = thumbnail.href;
            fullImage.alt = previewImage.alt;
            counter.textContent = `${selected + 1} / ${thumbnails.length}`;
        }
    }

    function unmodifiedClick(event) {
        return !event.ctrlKey && !event.metaKey && !event.shiftKey && !event.altKey && (event.button === undefined || event.button === 0);
    }

    thumbnails.forEach((thumbnail, index) => thumbnail.addEventListener('click', event => {
        if (!unmodifiedClick(event)) return;
        event.preventDefault();
        select(index);
    }));
    gallery.querySelector('[data-gallery-prev]')?.addEventListener('click', () => select(selected - 1));
    gallery.querySelector('[data-gallery-next]')?.addEventListener('click', () => select(selected + 1));
    gallery.addEventListener('keydown', event => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        select(selected + (event.key === 'ArrowRight' ? 1 : -1));
    });

    preview.addEventListener('click', event => {
        if (!unmodifiedClick(event)) return;
        event.preventDefault();
        dialog.showModal();
        select(selected);
        page.body.classList.add('homepage-lightbox-open');
    });
    dialog.querySelector('[data-lightbox-close]').addEventListener('click', () => dialog.close());
    dialog.querySelector('[data-lightbox-prev]').addEventListener('click', () => select(selected - 1));
    dialog.querySelector('[data-lightbox-next]').addEventListener('click', () => select(selected + 1));
    dialog.addEventListener('keydown', event => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        select(selected + (event.key === 'ArrowRight' ? 1 : -1));
    });
    dialog.addEventListener('click', event => {
        if (event.target !== dialog) return;
        const rect = dialog.getBoundingClientRect();
        if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close();
    });
    dialog.addEventListener('close', () => {
        page.body.classList.remove('homepage-lightbox-open');
        fullImage.removeAttribute('src');
        preview.focus();
    });
    select(0);

    return { select, currentIndex: () => selected };
}

export function elapsedTime(start, now) {
    const seconds = Math.floor(Math.abs(now - start) / 1000);
    return [Math.floor(seconds / 86400), Math.floor(seconds / 3600) % 24, Math.floor(seconds / 60) % 60, seconds % 60];
}

export function initializeHomepage(root) {
    const gallery = root.querySelector('[data-homepage-gallery]');
    if (gallery) initializeGallery(gallery, root.querySelector('#homepage-lightbox'));

    root.addEventListener('click', event => {
        const link = event.target.closest('[data-library-info-popup]');
        if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey || event.button > 0) return;
        event.preventDefault();
        window.open(link.href, '', 'width=730,height=800,resizable=yes,scrollbars=yes');
    });

    const timer = root.querySelector('[data-homepage-timer]');
    if (timer) {
        const start = Date.parse(timer.dataset.startedAt);
        const fields = ['days', 'hours', 'minutes', 'seconds'].map(id => timer.querySelector(`#${id}`));
        if (Number.isFinite(start) && fields.every(Boolean)) {
            const update = () => elapsedTime(start, Date.now()).forEach((value, index) => { fields[index].textContent = String(value); });
            let interval = null;
            const resume = () => {
                if (interval !== null) clearInterval(interval);
                interval = null;
                if (!root.hidden) {
                    update();
                    interval = setInterval(update, 1000);
                }
            };
            root.addEventListener('visibilitychange', resume);
            resume();
        }
    }
}
