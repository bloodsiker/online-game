let showItemInfo = (obj, evnt, show) => {
    // Сортировка в рюкзаке
    if (typeof(iam_sorting_now) !== 'undefined' && iam_sorting_now) {
        show = 0;
    }
    var itemId = obj.dataset.id;
    if (!itemId) itemId = 'AA_' + obj.getAttribute('artifact_id');
    var itemInfo = getTopWindow().gebi('artifact_alt');

    if (!itemInfo) return;

    if (show === 2) {
        document.onmousemove = function(e) {showItemInfo(obj, e||event, 1);}

        if (itemTooltip !== undefined && itemTooltip[itemId] !== undefined) {
            if (!itemInfo.dataset.id || obj.dataset.id != itemInfo.dataset.id) {
                if (itemTooltip[itemId] && itemTooltip[itemId] !== undefined) {
                    itemInfo.innerHTML = renderItemInfo(itemId);
                }
                itemInfo.setAttribute('data-id', obj.dataset.id);
            }
            itemInfo.style.display = 'block';
        } else {
            itemInfo.style.display = 'none';
        }
    }
    if (!show) {
        itemInfo.style.display = 'none';
        document.onmousemove = function(){}
        return;
    }

    var coor = getIframeShift();
    var ex = evnt.clientX+coor.left;
    var ey = evnt.clientY+coor.top;

    if (_top().noIframeAlt) {
        ex = evnt.clientX + _top().document.body.scrollLeft;
        ey = evnt.clientY + _top().document.body.scrollTop;
    }

    var x = ex + itemInfo.offsetWidth > _top().document.body.clientWidth - 20 ? ex - itemInfo.offsetWidth - 10 : ex + 10;
    var y = ey + itemInfo.offsetHeight - _top().document.body.scrollTop > _top().document.body.clientHeight - 20 ? ey - itemInfo.offsetHeight - 10 : ey + 10;

    if (x < 0 ) {
        x = ex - itemInfo.offsetWidth / 2;
    }
    if (x < 7 ) {
        x = 7;
    }
    if (x > _top().document.body.clientWidth - itemInfo.offsetWidth - 20) {
        x = _top().document.body.clientWidth - itemInfo.offsetWidth - 20;
    }

    itemInfo.style.left = x + 'px';
    itemInfo.style.top  = y + 'px';

}

let renderItemInfo = (id) => {
    var a = itemTooltip[id];
    if(!a) {
        try { a = get_itemTooltip(id); } catch (e) {}
    }
    if (!a) return '';
    var bg = true;
    var i = 0;
    var content = '';

    content += '<table width="300" border="0" cellspacing="0" cellpadding="0" style="background-color:#FBD4A4;" class="aa-table">';
    content += '<tr><td width="14" class="aa-tl"><img src="/img/icon/d.gif" width="14" height="24"><br></td>';
    content += '<td class="aa-t aa-table-t" align="center" style="vertical-align:middle"><b style="color:' + a.color + '">' + a.title + '</b></td>';
    content += '<td width="14" class="aa-tr"><img src="/img/icon/d.gif" width="14" height="24"><br></td></tr>';
    content += '<tr><td class="aa-l" style="padding:0;"></td><td style="padding:0;">';
    content += '<table width="275" style=" margin: 3px" border="0" cellspacing="0" cellpadding="0" class="aa-table-t"><tr>';
    content += '<td align="center" valign="top" width="50">';
    content += '<table width="50" height="50" cellpadding="0" cellspacing="0" border="0" style="margin:2px; background-image: url(' + a.image + '); background-size: cover; background-position: center;"><tr><td valign="bottom">';
    if (a.count && a.count !== undefined) {
        content += '<div class="bpdig">' + a.count + '</div>';
    } else if ((a.enchant_icon && a.enchant_icon !== undefined) || (a.oprava && a.oprava !== undefined)) {
        content += '<span class="enchants">';
        if(a.enchant_icon){
            content += a.enchant_icon;
        }
        if(a.oprava){
            content += '<img src="/images/enchants/oprava.png" alt="" class="enchant2_png">';
        }
        content += '</span>';
    } else {
        content += '&nbsp;';
    }

    content += '</td></tr></table>';
    content += '</td><td>';
    content += '<div><img src="/img/icon/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle">&nbsp;' + a.kind + '</div>';
    if (a.dur && a.dur != undefined && !(a.nobreaks && a.nobreaks != undefined)) {
        content += '<div><img src="/img/bg/item_info/tbl-shp_item-iznos.gif" width="11" height="10" align="absmiddle"> <span class="red">' + a.dur + '</span>/' + a.dur_max + '</div>';
    }
    if (a.nobreaks && a.nobreaks != undefined) {
        content += '<div><img src="/img/bg/item_info/tbl-shp_item-iznos.gif" width="11" height="10" align="absmiddle"> <span class="red">' + a.nobreaks + '</span></div>';
    }
    if (a.diamond && a.diamond != undefined && a.diamond.length > 0) {
        content += '<div class="b red">' + a.diamond + '</div>';
    }
    if (a.price && a.price != undefined && a.price.length > 0) {
        content += '<div class="b red">' + a.price + '</div>';
    }
    if (a.com && a.com != undefined) {
        content += '<div class="b red">' + a.com.title + ' ' + a.com.value + '</div>';
    }
    if (a.owner && a.owner != undefined) {
        content += '<div><b class="b red">' + a.owner.title + '</b>' + a.owner.value + '</div>';
    }
    content += '</td><td>';
    // if (a.lev && a.lev != undefined) {
    //     content += '<div><img src="/img/icon/tbl-shp_level-icon.gif" width="11" height="10" align="absmiddle"> ' + a.lev.title + ' <b class="red">' + a.lev.value + '</b></div>';
    // }
    if (a.trend && a.trend != undefined) {
        content += '<div><img src="/img/bg/item_info/tbl-shp_item-trend.gif" width="11" height="10" align="absmiddle">&nbsp;' + a.trend + '</div>';
    }
    if (a.cls && a.cls != undefined) {
        content += '<div><img src="/img/bg/item_info/class.gif" width="11" height="10" align="absmiddle"> ';
        for (i in a.cls) {
            content += a.cls[i];
        }
        content += '</div>'
    }
    content += '</td></tr></table>';
    content += '<table class="aa-table-t" width="100%" cellpadding="0" cellspacing="0" border="0">';
    if (a.exp && a.exp != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.exp.title + '</td><td class="grnn b" align="right">' + a.exp.value + '</td></tr>';
        bg = !bg;
    }
    if (a.skills && a.skills != undefined) {
        for (i in a.skills) {
            content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.skills[i].title + '</td><td class="red" align="right">' + a.skills[i].value + '</td></tr>';
            bg = !bg;
        }
    }
    if (a.skills_e && a.skills_e != undefined) {
        for (i in a.skills_e) {
            content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.skills_e[i].title + '</td><td class="red" align="right">' + a.skills_e[i].value + '</td></tr>';
            bg = !bg;
        }
    }
    if (a.enchant && a.enchant != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.enchant.title + '</td><td class="red" align="right">' + a.enchant.value + '</td></tr>';
        bg = !bg;
    }
    if (a.symbols && typeof(a.symbols) == "object") {
        var symbol, isDisplaySymbolLabel = false;

        for (i in a.symbols) {
            if (!a.symbols.hasOwnProperty(i)) {
                continue;
            }

            symbol = a.symbols[i];
            content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + (isDisplaySymbolLabel ? '&nbsp;' : symbol.title) + '</td><td class="red" align="right">' + symbol.value.replace(/&quot;/g, '"') + '</td></tr>';
            bg = !bg;
            isDisplaySymbolLabel = true;
        }
    }
    if (a.enchant_mod && a.enchant_mod != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.enchant_mod.title + '</td><td class="red" align="right">' + a.enchant_mod.value + '</td></tr>';
        bg = !bg;
    }
    if (a.oprava && a.oprava != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.oprava.title + '</td><td class="red" align="right">' + a.oprava.value + '</td></tr>';
        bg = !bg;
    }

    if (a.set && a.set != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.set.title + '</td><td class="red" align="right">' + a.set.value + '</td></tr>';
        bg = !bg;
    }
    if (a.change && a.change != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="grnn b">' + a.change + '</td></tr>';
        bg = !bg;
    }
    if (a.nogive && a.nogive != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="redd b">' + a.nogive + '</td></tr>';
        bg = !bg;
    }
    if (a.clan_thing && a.clan_thing != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="violet b">' + a.clan_thing + '</td></tr>';
        bg = !bg;
    }
    if (a.boe && a.boe != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="redd b">' + a.boe + '</td></tr>';
        bg = !bg;
    }
    if (a.noweight && a.noweight != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="grnn b">' + a.noweight + '</td></tr>';
        bg = !bg;
    }
    if (a.nosell && a.nosell != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="dark b">' + a.nosell + '</td></tr>';
        bg = !bg;
    }
    if (a.nofreeze && a.nofreeze != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2"><b style="color: #0969a2;">' + a.nofreeze + '</td></tr>';
        bg = !bg;
    }
    if (a.updtimer && a.updtimer != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="dark b grnn">' + a.updtimer + '</td></tr>';
        bg = !bg;
    }
    if (a.cansmol && a.cansmol != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="b" style="color: #008080">' + a.cansmol + '</td></tr>';
        bg = !bg;
    }
    if (a.smith && a.smith != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="b" style="color: #008080">' + a.smith + '</td></tr>';
        bg = !bg;
    }
    if (a.note && a.note != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2">' + a.note + '</td></tr>';
        bg = !bg;
    }
    if (a.engrave && a.engrave != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2">' + a.engrave + '</td></tr>';
        bg = !bg;
    }
    if (a.rank_min && a.rank_min != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2">' + a.rank_min.replace(/&quot;/g, '"') + '</td></tr>';
        bg = !bg;
    }
    if (a.desc && a.desc != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2">' + a.desc + '</td></tr>';
        bg = !bg;
    }
    content += '</table>';
    content += '</td><td class="aa-r" style="padding:0px"></td></tr>';
    content += '<tr><td class="aa-bl"></td><td class="aa-b"></td><td class="aa-br"></td></tr>';
    content += '</table>';

    return content;
}

function showArtifactInfo(artifact_id, artikul_id, set_id, evnt, user_store) {
    if (typeof(iam_sorting_now) !== 'undefined' && iam_sorting_now)
        return false;
    if (evnt && evnt.shiftKey && artifact_id) {
        chat_add_artifact_macros(artifact_id);
        return;
    }
    if (evnt && evnt.shiftKey && artikul_id) {
        chat_add_macros('artikul_'+artikul_id);
        return;
    }
    var url = "/artifact_info.php";
    if (artifact_id) url += "?artifact_id=" + artifact_id;
    else if (artikul_id) url += "?artikul_id=" + artikul_id;
    else if (set_id) url += "?set_id=" + set_id;
    else return;
    if (user_store) {
        url += '&user_store=1';
    }
    window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

let _background = (obj, name) => {
    if (obj.tagName == 'IMAGE') {
        obj.src = name;
    } else {
        obj.style.backgroundImage = 'url('+name+')'
    }
}

let _top = () => {
    return window.top;

    if (window.last_top) return window.last_top;
    var p = window;
    while (true) {
        try {
            if (/game/.test(p.location.href) || p.parent === p) { break; }
        } catch (e) {
            window.last_top = p;
            return p;
        }
        p = p.parent.window;
    }
    window.last_top = p;
    return p;
}

let getTopWindow = () => {
    let w = window;

    while (w.parent && w.parent !== w) {
        try {
            w.parent.document; // проверка доступа
            w = w.parent;
        } catch (e) {
            break;
        }
    }

    return w;
}

let gebi = (id) => {
    return document.getElementById(id)
}

let getIframeShift = () => {
    var currentWindow = window,
        currentFrame = null,

        docElem = null,
        body = null,

        top = 0,
        left = 0,

        scrollTop = 0,
        scrollLeft = 0;

    while (currentFrame = currentWindow.frameElement) {
        currentWindow = currentWindow.parent;

        top += Math.round(currentFrame.getBoundingClientRect().top);
        left += Math.round(currentFrame.getBoundingClientRect().left);
    }

    return {
        top: top,
        left: left
    };
}
