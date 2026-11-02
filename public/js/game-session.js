(function () {
    'use strict';

    const config = document.currentScript?.dataset || {};
    if (window.gameSessionGuard) {
        if (config.checkSession === '1') window.gameSessionGuard.check();
        return;
    }
    const homeUrl = config.homeUrl || '/';
    const statusUrl = config.sessionStatusUrl || '/session/status';
    const nativeFetch = window.fetch.bind(window);
    let pendingCheck = null;
    let redirecting = false;

    function redirectHome() {
        if (redirecting) return;
        try {
            const home = new URL(homeUrl, window.location.href);
            if (window.top.location.origin === home.origin && window.top.location.pathname === home.pathname) return;
        } catch (error) {}
        redirecting = true;
        try { window.top.location.replace(homeUrl); }
        catch (error) { window.location.replace(homeUrl); }
    }

    function sameOrigin(url) {
        if (typeof url !== 'string') return false;
        try { return new URL(url, window.location.href).origin === window.location.origin; }
        catch (error) { return false; }
    }

    function checkSession() {
        if (redirecting) return Promise.resolve();
        if (pendingCheck) return pendingCheck;
        pendingCheck = nativeFetch(statusUrl, {
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        }).then(async function (response) {
            if (response.status === 401) {
                redirectHome();
                return;
            }
            if (response.ok && (await response.json()).authenticated === false) redirectHome();
        }).catch(function () {
            // A network failure does not establish that the session has expired.
        }).finally(function () { pendingCheck = null; });
        return pendingCheck;
    }

    function inspect(status, expired) {
        if (status === 401 || expired) redirectHome();
        else if ([405, 419, 500].includes(status)) checkSession();
    }

    const guard = { check: checkSession };
    function install(target) {
        try {
            if (target.gameSessionGuard) return;
            // Accessing the document also verifies that this is a same-origin frame.
            const targetDocument = target.document;
            target.gameSessionGuard = guard;
            const fetchOriginal = target.fetch.bind(target);
            target.fetch = async function (input, options) {
                const response = await fetchOriginal(input, options);
                const url = typeof input === 'string' ? input : input?.url ?? input?.href;
                if (sameOrigin(url)) inspect(response.status, response.headers?.get('X-Game-Session-Expired') === '1');
                return response;
            };

            const prototype = target.XMLHttpRequest?.prototype;
            if (prototype) {
                const open = prototype.open;
                const send = prototype.send;
                prototype.open = function (method, url) {
                    this.gameSessionRequestUrl = String(url);
                    return open.apply(this, arguments);
                };
                prototype.send = function () {
                    this.addEventListener('loadend', function () {
                        if (sameOrigin(this.gameSessionRequestUrl)) {
                            inspect(this.status, this.getResponseHeader('X-Game-Session-Expired') === '1');
                        }
                    }, { once: true });
                    return send.apply(this, arguments);
                };
            }
            targetDocument.addEventListener('load', function (event) {
                if (event.target?.tagName === 'IFRAME') install(event.target.contentWindow);
            }, true);
            targetDocument.querySelectorAll('iframe').forEach(function (frame) { install(frame.contentWindow); });
        } catch (error) {
            // Cross-origin frames are not controlled by the game.
        }
    }

    install(window);
    if (config.checkSession === '1') checkSession();
}());
