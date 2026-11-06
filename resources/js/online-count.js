document.addEventListener('DOMContentLoaded', () => {
    const counters = Array.from(document.querySelectorAll('[data-online-count]'));
    if (!window.Echo || counters.length === 0) return;

    const render = count => counters.forEach(counter => {
        counter.textContent = String(Math.max(0, Number(count) || 0));
    });

    const channel = window.Echo.channel('online-count')
        .listen('.online.count.updated', event => render(event.count));

    // Echo exposes whisper() only on private/presence channels; this channel is public.
    channel.subscribed(() => channel.subscription.trigger('client-online-count-request', {}));
});
