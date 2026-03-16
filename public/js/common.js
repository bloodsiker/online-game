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

function gebi(id) {
    return document.getElementById(id);
}
