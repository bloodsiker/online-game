import assert from 'node:assert/strict';
import test from 'node:test';
import { initializeGallery, elapsedTime } from '../../resources/js/homepage-ui.js';

function setup() {
    function element() {
        const classes = new Set();
        return { handlers: new Map(), attributes: {}, offsetLeft: 0, clientWidth: 240, offsetWidth: 80,
            classList: { add: x => classes.add(x), remove: x => classes.delete(x), contains: x => classes.has(x) },
            addEventListener(name, fn) { this.handlers.set(name, fn); },
            setAttribute(name, value) { this.attributes[name] = value; },
            removeAttribute(name) { delete this[name]; },
            focus() { this.focused = true; }, scrollTo(options) { this.scrollLeft = options.left; },
        };
    }
    const body = element();
    const thumbnails = Array.from({ length: 7 }, (_, i) => Object.assign(element(), {
        href: `/screens/${i + 1}.png`, offsetLeft: i * 80,
        querySelector: () => ({ alt: `Screenshot ${i + 1}` }),
    }));
    const previewImage = element();
    const preview = Object.assign(element(), { querySelector: () => previewImage });
    const track = element();
    const fullImage = element();
    const counter = element();
    const prev = element(), next = element(), close = element();
    const dialog = Object.assign(element(), {
        open: false, showModal() { this.open = true; },
        close() { this.open = false; this.handlers.get('close')(); },
        querySelector: selector => ({ '[data-lightbox-image]': fullImage, '[data-lightbox-counter]': counter,
            '[data-lightbox-prev]': prev, '[data-lightbox-next]': next, '[data-lightbox-close]': close })[selector],
    });
    const gallery = Object.assign(element(), {
        ownerDocument: { body }, querySelectorAll: () => thumbnails,
        querySelector: selector => ({ '[data-gallery-preview]': preview, '[data-gallery-track]': track,
            '[data-gallery-prev]': prev, '[data-gallery-next]': next })[selector],
    });
    const controller = initializeGallery(gallery, dialog);
    const event = properties => ({ prevented: false, preventDefault() { this.prevented = true; }, ...properties });
    return { controller, gallery, dialog, thumbnails, preview, previewImage, fullImage, counter, prev, next, close, track, body, event };
}

test('thumbnails select the preview and arrows wrap at both ends without clones', () => {
    const ui = setup();
    const click = ui.event({ button: 0 });
    ui.thumbnails[4].handlers.get('click')(click);
    assert.equal(click.prevented, true);
    assert.equal(ui.previewImage.src, '/screens/5.png');
    assert.equal(ui.preview.href, '/screens/5.png');
    assert.equal(ui.thumbnails[4].attributes['aria-current'], 'true');
    assert.equal(ui.thumbnails[0].attributes['aria-current'], 'false');
    ui.controller.select(0);
    ui.prev.handlers.get('click')();
    assert.equal(ui.controller.currentIndex(), 6);
    ui.next.handlers.get('click')();
    assert.equal(ui.controller.currentIndex(), 0);
});

test('viewer opens selected image, navigates with keyboard and restores focus after closing', () => {
    const ui = setup();
    ui.controller.select(4);
    ui.preview.handlers.get('click')(ui.event({ button: 0 }));
    assert.equal(ui.dialog.open, true);
    assert.equal(ui.fullImage.src, '/screens/5.png');
    assert.equal(ui.counter.textContent, '5 / 7');
    assert.equal(ui.body.classList.contains('homepage-lightbox-open'), true);
    ui.dialog.handlers.get('keydown')(ui.event({ key: 'ArrowRight' }));
    assert.equal(ui.fullImage.src, '/screens/6.png');
    assert.equal(ui.counter.textContent, '6 / 7');
    ui.close.handlers.get('click')();
    assert.equal(ui.dialog.open, false);
    assert.equal(ui.fullImage.src, undefined);
    assert.equal(ui.preview.focused, true);
    assert.equal(ui.body.classList.contains('homepage-lightbox-open'), false);
});

test('Ctrl/Command clicks keep normal image links and do not open or alter the gallery', () => {
    const ui = setup();
    for (const modifiers of [{ ctrlKey: true }, { metaKey: true }, { button: 1 }]) {
        const click = ui.event(modifiers);
        ui.preview.handlers.get('click')(click);
        ui.thumbnails[3].handlers.get('click')(click);
        assert.equal(click.prevented, false);
        assert.equal(ui.dialog.open, false);
        assert.equal(ui.controller.currentIndex(), 0);
    }
});

test('elapsed timer handles passed and upcoming dates without negative remainder artifacts', () => {
    const duration = (86400 + 2 * 3600 + 3 * 60 + 4) * 1000;
    assert.deepEqual(elapsedTime(0, duration), [1, 2, 3, 4]);
    assert.deepEqual(elapsedTime(duration, 0), [1, 2, 3, 4]);
});
