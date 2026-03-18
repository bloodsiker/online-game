function systemConfirm(ms, title, obj, func) {
    if (title) {
        gebi('systemConfirm_title').innerHTML = title;
    }
    if (ms) {
        gebi('confirm_ms').innerHTML = '<b>' + ms + '</b>';
    }

    var div = gebi('systemConfirm_div');
    var close_div = gebi('systemConfirm_close_div');

    div.style.display = 'block';

    var top  = document.body.scrollTop  + (document.body.clientHeight - div.offsetHeight) / 2 - 100;
    var left = document.body.scrollLeft + (document.body.clientWidth  - div.offsetWidth)  / 2;
    div.style.top  = top  + 'px';
    div.style.left = left + 'px';


    close_div.style.width  = document.body.clientWidth  + 'px';
    close_div.style.height = document.body.clientHeight + 'px';
    close_div.style.display = 'block';

    gebi('btnOk').onclick = function () {
        div.style.display       = 'none';
        close_div.style.display = 'none';
        if (obj && obj.href) {
            location.href = obj.href;
        } else if (obj && typeof obj.submit === 'function') {
            obj.submit();
        } else if (typeof func === 'function') {
            func();
        }
        return true;
    };

    document.querySelectorAll('.btn_sys_confirm_close').forEach(function (el) {
        el.onclick = function () {
            div.style.display       = 'none';
            close_div.style.display = 'none';
        };
    });

    return false;
}

function systemInfo(message, title) {
    var div = gebi('systemInfo_div');

    if (!div) {
        var overlay = document.createElement('div');
        overlay.id = 'systemInfo_overlay';
        overlay.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;z-index:9998;background:rgba(0,0,0,0.4);';
        overlay.onclick = function () { hideSystemInfo(); };
        document.body.appendChild(overlay);

        div = document.createElement('div');
        div.id = 'systemInfo_div';
        div.style.cssText = 'display:none;position:fixed;z-index:9999;min-width: 435px';
        div.innerHTML =
            '<div class="popup_global_container">' +
                '<div class="popup-top-left">' +
                    '<div class="popup-top-right">' +
                        '<div class="popup-top-center">' +
                            '<div class="popup_global_title" id="systemInfo_title">Сообщение</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="popup_global_close_btn" onclick="hideSystemInfo();"></div>' +
                '</div>' +
                '<div class="popup-left-center">' +
                    '<div class="popup-right-center">' +
                        '<div class="popup_global_content" style="padding:20px;">' +
                            '<div style="text-align:center;">' +
                                '<b id="systemInfo_message"></b><br>' +
                            '</div>' +
                            '<div style="margin-top:25px;text-align:center;">' +
                                '<b class="butt1 pointer"><b><input value="Закрыть" type="button" onclick="hideSystemInfo();" class="redd" style="width:100px;"></b></b>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
                '<div class="popup-left-bottom">' +
                    '<div class="popup-right-bottom">' +
                        '<div class="popup-bottom-center"></div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        document.body.appendChild(div);
    }

    if (title)   gebi('systemInfo_title').innerHTML   = title;
    if (message) gebi('systemInfo_message').innerHTML = message;

    var overlayEl = gebi('systemInfo_overlay');
    if (overlayEl) overlayEl.style.display = 'block';

    div.style.display = 'block';

    var top  = (window.innerHeight - div.offsetHeight) / 2;
    var left = (window.innerWidth  - div.offsetWidth)  / 2;
    div.style.top  = Math.max(0, top)  + 'px';
    div.style.left = Math.max(0, left) + 'px';

    return false;
}

function hideSystemInfo() {
    var div     = gebi('systemInfo_div');
    var overlay = gebi('systemInfo_overlay');
    if (div)     div.style.display     = 'none';
    if (overlay) overlay.style.display = 'none';
}

function gebi(id) {
    return document.getElementById(id);
}
