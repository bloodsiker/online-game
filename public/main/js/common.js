// $Id: common.js,v 1.156 2010-02-04 08:34:34 s.ignatenkov Exp $

var undefined;
var iam_sorting_now;
//document.write('<script type="text\/javascript" src="\/js\/base64.js"><\/' + 'script>');

Number.prototype.toFixed = Number.prototype.toFixed || function(fractionDigits){
	return Math.floor( this * Math.pow(10, fractionDigits) + .5) / Math.pow(10, fractionDigits)
};

function is_touch_device() {
	return (('ontouchstart' in window)
		|| (navigator.MaxTouchPoints > 0)
		|| (navigator.msMaxTouchPoints > 0));
}
function str_trim(str) {
	return str.replace(/^\s*/, "").replace(/\s*$/, "");
}
function array_filter(arr, fun) {
	var len = arr.length;
	if (typeof fun != "function")
		throw new TypeError();
	var res = new Array();
	var thisp = arguments[1];
	for (var i = 0; i < len; i++) {
		if (i in arr) {
			var val = arr[i];
			if (fun.call(thisp, val, i, arr))
				res.push(val);
		}
	}
	return res;
}

function getScrollMaxY(win) {
    var innerh;
    if (win.innerHeight){
        innerh = win.innerHeight;
    } else {
        innerh = win.document.body.clientHeight;
    }

    if (win.innerHeight && win.scrollMaxY){
        // Firefox
        yWithScroll = win.innerHeight + win.scrollMaxY;
    } else if (win.document.body.scrollHeight > win.document.body.offsetHeight){
        // all but Explorer Mac
        yWithScroll = win.document.body.scrollHeight;
    } else {
        // works in Explorer 6 Strict, Mozilla (not FF) and Safari
        yWithScroll = win.document.body.offsetHeight;
    }
    return yWithScroll - innerh;
}

function array_unique (arr) {
	var res = [];
	var len = arr.length;
	for (var i = 0; i < len; i++) {
		for (var j = i + 1; j < len; j++) {
			if (arr[i] === arr[j]) {
				j = ++i;
			}
		}
		res.push(arr[i]);
	}
	return res;
}
function gebi(id){
	return document.getElementById(id)
}
function loadArtifactArtikulsData(artikul_ids, complete_func) {
	if (typeof(artikul_ids) != "object") {
		return;
	}

	var unloaded_artikul_ids = [],
		alt;
	for (var i = artikul_ids.length - 1; i >= 0; i--) {
		alt = get_art_alt('AA_' + artikul_ids[i]);
		if (!alt) {
			unloaded_artikul_ids.push(artikul_ids[i]);
		}
	}
	//var DATA_OK = 100;
	entry_point_request('info', 'alt_artikuls', {artikuls: unloaded_artikul_ids}, function(data) {
			if (data.status != 100 || !data['artikuls']) {
				if (complete_func != undefined) complete_func.call();
				return;
			}

			for (var i = data['artikuls'].length - 1; i >= 0; i--) {
				set_art_alt('AA_' + data['artikuls'][i]['id'], data['artikuls'][i]);
			}
			if (complete_func != undefined) complete_func.call();
		}
	);
}
function get_art_alt(id, win) {
	if (win) {
		if (win.art_alt && win.art_alt[id]) return win.art_alt[id];
		for (var i = 0; i < win.frames.length; ++i) {
			var res = get_art_alt(id, win.frames[i]);
			if (res !== false) return res;
		}
		return false;
	}
	if (art_alt && art_alt[id]) return art_alt[id];
	if (top.items_alt && top.items_alt[id]) return top.items_alt[id];
	return get_art_alt(id, top.frames['main_frame']);
}

function set_art_alt(id, data, win) {
    if (win) {
        if (win.art_alt) {
            win.art_alt[id] = data;
            return;
        }
        for (var i = 0; i < win.frames.length; ++i) {
            if (win.frames[i].art_alt) {
                win.frames[i].art_alt[id] = data;
                return;
            }
        }
        return;
    }
    if (art_alt) {
        art_alt[id] = data;
        return;
    }
    if (_top().items_alt) {
        _top().items_alt[id] = data;
        return;
    }
    _top().frames['main_frame'].art_alt[id] = data;
}

function loadPuzzle(params) {
    var useCanvas = (params.useCanvas == undefined) ? false : params.useCanvas >= 1;
    var swf_params = [];
    swf_params.push("puzzle=" + params["puzzle"]);
    swf_params.push("matrix=" + params["matrix"]);
    swf_params.push("steps=" + params["steps"]);
    swf_params.push("can_breakopen=" + params["can_breakopen"]);
    swf_params.push("can_purchase=" + params["can_purchase"]);
    swf_params.push("GrPack=/images/swf/treasure_puzzle_graph.swf");
    swf_params.push("locale_file=" + _top().locale_file);
    swf_params.push("width=612");
    swf_params.push("height=500");
    swf_params = swf_params.join('&');
    var html;
    if (useCanvas) {
        html = document.createElement("div");
        if (document.treasurePuzzle) {
            document.treasurePuzzle.destroy();
        }
        document.treasurePuzzle = new canvas.app.CanvasTreasure(swf_params, html);
    } else {
        html = AC_FL_RunContent(
            'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,60,0',
            'width', '612',
            'height', '500',
            'src', 'images/swf/treasure_puzzle.swf',
            'quality', 'high',
            'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
            'align', 'middle',
            'play', 'true',
            'loop', 'true',
            'scale', 'showall',
            'wmode', 'transparent',
            'devicefont', 'false',
            'id', 'WohPlayer',
            'bgcolor', '#ffffff',
            'name', 'WohPlayer',
            'menu', 'true',
            'cancelwrite', 'true',
            'allowScriptAccess', 'sameDomain',
            'allowFullScreen', 'true',
            'movie', 'images/swf/treasure_puzzle.swf',
            'salign', '',
            'flashVars', swf_params
        );
    }
    var options = {width: 612, height: 500};
    confirmCenterDiv(html, options);
}

function openPuzzle(action, steps) {
    steps = steps || "";

    switch (action) {
        case "purchase":
        case "solve":
        case "breakopen":
            entry_point_request('puzzlechest', action, {steps: steps}, function (data) {
                if (data && data["error"]) {
                    showMsg2("error.php?error=" + encodeURIComponent(data["error"]) + "&errcrc=" + data["error_crc"], "Hata");
                } else {
                    confirmCenterDivClose();
                }
            });
            break;
    }
}

function jsquote(str){
	return str.replace(/'/g,'&#39;').replace(/>/g,'&gt;').replace(/</g,'&lt;').replace(/&/g,'&amp;') //'
}

function copyBoard(obj, txt){
	if (document.body.createTextRange) { // IE
		var d=document.createElement('INPUT');
		d.type='hidden';
		d.value=txt;
		document.body.appendChild(d).createTextRange().execCommand("Copy");
		document.body.removeChild(d);
		return;
	} else try { // FireFox
		netscape.security.PrivilegeManager.enablePrivilege('UniversalXPConnect');
		var gClipboardHelper = Components.classes["@mozilla.org/widget/clipboardhelper;1"].getService(Components.interfaces.nsIClipboardHelper);
		gClipboardHelper.copyString(txt);
	} catch (e) { // Google Chrome
		
	}
}

function getCoords(obj){
	var o=typeof(obj) == 'string' ? gebi(obj) : obj
	var ret={'l':o.offsetLeft,'t':o.offsetTop,'w':o.offsetWidth,'h':o.offsetHeight}
	while(o=o.offsetParent){
		ret.l+=o.offsetLeft
		ret.t+=o.offsetTop
	}
	return ret
}
var waitFuncId=0
function waitObj(id,evFunc){
	if(document.getElementById){
		if(typeof evFunc=='function'){
			window['waitFunc'+waitFuncId]=evFunc
			evFunc='waitFunc'+waitFuncId
			waitFuncId++
		}
		var obj=(id=='body')?document.body:document.getElementById(id)
		if(obj) window[evFunc]()
		else setTimeout("waitObj('"+id+"','"+evFunc+"')",100)
	}else{
		onload=evFunc
	}
}

function preloadImages() {
	var d = document;
	if(!d._prImg) d._prImg = new Array();
	var i, j = d._prImg.length, a = preloadImages.arguments;
	for (i=0; i<a.length; i++) {
		d._prImg[j] = new Image;
		d._prImg[j++].src = a[i];
	}
}

function checkbox_set(pfx, val) {
  var chk=document.getElementsByTagName('INPUT');
  for(var i=0;i<chk.length;i++){
    if(chk[i].name.indexOf(pfx)==0 || chk[i].getAttribute('grp')==pfx){
      chk[i].checked = (val == undefined ? !chk[i].checked: val);
    }
  }
}

// ==============================================================================
window.fxamodule = true;

function showError(error) {
	return showMsg2("error.php?error="+error);
}

function luckyMsg(text, url) {
	var error_div = top.window.gebi('error_div');
	
	error_div.errorCloseCallback = function() {
		top.frames["main_frame"].frames["main"].location.href = url;
		error_div.errorCloseCallback = null;
	}
	
	showMsg2("error.php?error="+encodeURIComponent(text), "Сообщение");

}

function showMsg2(url, title, w, h) {
	try {
		w=w||480;
		h=h||300;
		var win = top.window;
		var doc = top.document;
		var width = doc.body.clientWidth;
		var height = doc.body.clientHeight;
		var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
		var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);
		var obj = top.gebi('error');
		var div = top.gebi('error_div');
		if (!obj || !div) return false;
		obj.src=url;
		
		div.style.width = div_width;
		div.style.height = div_height;
		
		obj.width = w;
		obj.height = h;
		obj.style.left = ((width-w)/2);
		obj.style.top = ((height-h)/2);
		div.style.display = 'block';
		obj.style.display = 'block';
		win.scrollTo(0,0);
//		obj = top.gebi('artifact_alt');
//		if (obj) obj.innerHTML='';
	} catch(e) {}
	return true;
}

function showMsg(url, title, w, h) {
    w=w||520
    h=h||320
	var win = top.window;
	if (win.showModelessDialog) {
		var sFeatures = 'dialogWidth:' + w + 'px; dialogHeight:' + h + 'px; center:yes; help:no; status:no; unadorned:yes; scroll:no;';
		return win.showModelessDialog("msg.php", {win: win, src: url, title: title}, sFeatures);
	} else {
		return win.open(url, "", 'width=' + w + ',height=' + h + ',location=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no');
	}
}

function changeDivDisplay(div_id, display) {
	if (!div_id || !display) return false;
	div = gebi(div_id);
	if (!div) return false;
	div.style.display = display;
}

function showUserInfo(nick, server_url) {
	var url = "/user_info.php?nick="+nick;
	if (server_url) url = server_url+url;
	window.open(url, "", "width=930,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showArtifactInfo(artifact_id,artikul_id,set_id,evnt,user_store) {
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
	if (artifact_id) url += "?artifact_id="+artifact_id;
	else if (artikul_id) url += "?artikul_id="+artikul_id;
	else if (set_id) url += "?set_id="+set_id;
	else return;
	if(user_store){
		url += '&user_store=1';
	}
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showPetInfo(pet_id,artikul_id) {
	var url = "/pet_info.php";
	if (pet_id) url += "?pet_id="+pet_id;
	else if (artikul_id) url += "?artikul_id="+artikul_id;
	else return;
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showSmsForm(nick) {
	var url = "/area_post.php?&mode=sms&hide=1&nick=" + nick;
	window.open(url, "", "width=920,height=500,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function time_online_get(){

}

function dialogEventCheck(event,param,close) {
	if (!param) param = '0';
	if (!top.dialogOn && event != 'FAQ' && event != null) return false;
	if (event) {
		var id = top.dialogEvent[event+'_'+param];
		if (id && id.length && id.length > 0) {
			for(var i=0;i<id.length;i++) {
				if (top.dialogShow[id[i]]) continue;
				var k = false;
				for(var j=0;j<top.dialogNeed.length;j++) {
					if (top.dialogNeed[j] == id[i]) {
						k = true;
						break;
					}
				}
				if (top.showNow == id[i]) k=true;
				if (!k) top.dialogNeed.push(id[i]); 
			}
		}
	}
	try {
		var div = top.frames['main_frame'].gebi('dialog_div');
		//var frame = top.frames['main_frame'].gebi('dialog_frame');
		if (div.style.display == 'none' || close) {
			var id = top.dialogNeed.shift();
			if (id) {
				updateSwf({'dialog': 'id='+id});
				//swfTransfer('id','dialog',id);
				//frame.src='tests.php?id='+id;
				div.style.display = '';
				if (id > 1) top.dialogShow[id] = id;
				top.showNow = id;
			} else {
				div.style.display = 'none';
				//frame.src='';
				top.showNow = 0;
			}
		}
	} catch(e) {}
}

function sortTable(table, colIndex, sortType, reverse) {
    var tbody = table.tBodies[0],
        tr = Array.prototype.slice.call(tbody.rows, 0),
        i;

    reverse = parseInt(reverse) || 1;

    switch (sortType) {
        case 'string':
            tr = tr.sort(function(a, b) {
                return reverse * a.cells[colIndex].getAttribute('data-sort').localeCompare(b.cells[colIndex].getAttribute('data-sort'));
            });

            break;

        default: /* sort as number */
            tr = tr.sort(function(a, b) {
                return reverse * (parseFloat(a.cells[colIndex].getAttribute('data-sort')) - parseFloat(b.cells[colIndex].getAttribute('data-sort')));
            });
    }

    for (i = 0; i < tr.length; i++) {
        tbody.appendChild(tr[i]);
    }
}

function showFightInfo(fight_id, server_id) {
	var url = "/fight_info.php?fight_id="+fight_id;
	if (server_id) url += "&server_id="+server_id;
	window.open(url, "", "width=990,height=700,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes");
}
function showInstInfo() {
	var url = "/instance_stat.php";
	window.open(url, "", "width=990,height=700,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes");
}
function showInstanceInfo(instance_id, server_id) {
	var url = "/instance_stat.php?instance_id="+instance_id+'&outside=1';
	if (server_id) url += "&server_id="+server_id;
	window.open(url, "", "width=990,height=700,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes");
}
function showClanBattleInfo(clan_battle_id, server_id) {
	var url = "/clan_battle_info.php?clan_battle_id="+clan_battle_id+'&server_id='+server_id;
	window.open(url, "", "width=990,height=700,location=yes,menubar=yes,resizable=yes,scrollbars=yes,status=yes,toolbar=yes");
}
function showBotInfo(bot_id, artikul_id, fight_id) {
	var url = "/bot_info.php";

	if (bot_id) {
		if (fight_id) {
			url += "?fight_user_id="+bot_id+"&fight_id="+fight_id;
		} else {
			url += "?bot_id="+bot_id;
		}
	}	
	else if (artikul_id) url += "?artikul_id="+artikul_id;
	try{
        if(top.frames["main_frame"].frames["main"].__fight_php__){
            url += "&f=1";
        }
	}catch(e){}

	window.open(url, "", "width=915,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function confirmCenterDiv(html, options) {
    var name = _top().$.Popup;
    if (_top().popup != null && _top().popup != undefined) {
        _top().popup.close();
    }
    _top().popup = new name(options);
    if (options.withoutQuotes) {
        html = html.replace(/&quot;/g, '"').replace(/&backslash;/g, '\\');
    }
    _top().popup.open(html, options.type || 'html');
}

function showPopupDialog(url, title, w, h){
    try {
        w=w||480;
        h=h||300;
        var win = top.window;
        var doc = top.document;
        var width = doc.body.clientWidth;
        var height = doc.body.clientHeight;
        var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
        var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);
        var obj = top.gebi('popup_dialog_iframe');
        var div = top.gebi('popup_dialog_div');
        if (!obj || !div) return false;


        var win = top.window;
        var popup = win.open(url, 'popup_dialog_iframe', 'width=' + w + ',height=' + h + ',location=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no', false);

        //obj.src=url;

        div.style.width = div_width;
        div.style.height = div_height;

        obj.width = w;
        obj.height = h;
        obj.style.left = ((width-w)/2);
        obj.style.top = ((height-h)/2);
        div.style.display = 'block';
        obj.style.display = 'block';
        win.scrollTo(0,0);
//		obj = top.gebi('artifact_alt');
//		if (obj) obj.innerHTML='';
    } catch(e) {}
    return true;
}

function showExBackpack(url, title, w, h, l_cor, t_cor) {
    if(l_cor === undefined) l_cor = -495;
    if(t_cor === undefined) t_cor = 0;
    try {
        w=w||480;
        h=h||300;
        var win = top.window;
        var doc = top.document;
        var width = doc.body.clientWidth;
        var height = doc.body.clientHeight;
        var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
        var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);
        var obj = top.gebi('battle_backpack_iframe');
        var div = top.gebi('battle_backpack_div');
        var pos = _top().frames['main_frame'].$('#items_right_td').offset();
        if (!obj || !div) return false;

        obj.src=url;
        var win = top.window;
        var popup = win.open(url, 'battle_backpack_iframe', 'width=' + w + ',height=' + h + ',location=no,menubar=no,resizable=no,scrollbars=no,status=no,toolbar=no', false);


        div.style.width = div_width;
        div.style.height = div_height;

        obj.width = w;
        obj.height = h;
        if(pos) {
            $(obj).css('left', pos.left + l_cor);
            $(obj).css('top', pos.top + t_cor);
        }else{
            obj.style.left = ((width-w)/2);
            obj.style.top = ((height-h)/2);
        }
        div.style.display = 'block';
        obj.style.display = 'block';
        win.scrollTo(0,0);
//		obj = top.gebi('artifact_alt');
//		if (obj) obj.innerHTML='';
    } catch(e) {}
    return true;
}

function closeExBackpack() {
    try {
        var obj = top.gebi('battle_backpack_iframe');
        var div = top.gebi('battle_backpack_div');
        obj.src = "about:blank";
        div.style.display = 'none';
        obj.style.display = 'none';
    } catch(e) {}
    try{
        top.frames['main_frame'].right_menu_vars[2].state = 0;
        top.frames['main_frame'].right_menu_vars[6].state = 0;
        top.frames['main_frame'].right_menu_init();
    }catch (e){}
}

function closePopupDialog(){
    try {
        var obj = top.gebi('popup_dialog_iframe');
        var div = top.gebi('popup_dialog_div');
        obj.src = "";
        div.style.display = 'none';
        obj.style.display = 'none';
    } catch(e) {}
}

function closeHeavensGift(){
    confirmCenterDivClose();
    entry_point_request('HeavensGift', 'close');
}

function showAltInHeavensGift(artikul_id, p1, p2) {
    artifactAltSimple(artikul_id, p1, p2);
}
function openHeavensGift(useCanvas) {
    if (useCanvas == undefined) useCanvas = false;
    var html;
    var par = 'dice_game_controller_url=entry_point.php&locale_file='+_top().locale_file+'&width=460&height=520';
    if (useCanvas) {
        html = document.createElement("div");
        new canvas.app.CanvasDiceGame(par,html);
    } else {
        html = document.createElement("div");
        new canvas.app.CanvasDiceGame(par,html);
    }
    var options = {width:460,height:520};
    confirmCenterDiv(html, options);
}

var advanced_controllers = {
	'clan|building_action': ['building_type_id', 'building_action'],
	'estate|building': ['type_id', 'building_action']
};


var DATA_OK = 100;
function entry_point_request(object, action, params, callback, error_callback) {
    params = params || {};
    params = $.extend({
        json_mode_on: 1,
        object: object,
        action: action
    }, params);

    var send_data = {
        url: '/entry_point.php?object='+object+'&action='+action+'&json_mode_on=1',
        dataType: 'json', cache: false, type: "POST"
    };
    if (params.ajaxParam) {
        send_data = $.extend(send_data, params.ajaxParam);
        delete params.ajaxParam;
    }

    send_data.data = params;

    return $.ajax(send_data)
        .done(function(data) {
            var key = object + '|' + action;
            if (advanced_controllers[key]) {
                var key_vals = advanced_controllers[key];
                for (var i in key_vals) key += '|'+params[key_vals[i]];
            }
            var action_data = data[key] || data;
            if (callback instanceof Function) {
                callback.call(this, action_data, data);
            }
		
            if (_top().chat && _top().chat.chatReceiveObject) {
                _top().chat.chatReceiveObject(data);
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            if (error_callback instanceof Function) {
                error_callback.call(this, textStatus);
            }
        });
}



var frame_content_hider = null;


function showPunishmentInfo(nick) {
	var url = "/punishment_info.php?nick="+nick;
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showInjuryInfo(nick) {
	var url = "/injury_info.php?nick="+nick;
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showEffectInfo(nick) {
	var url = "/effect_info.php?nick="+nick;
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showClanInfo(clan_id) {
    /*if (evnt && evnt.shiftKey && clan_id) {
        chat_add_macros('clan_'+clan_id);
        return;
    }*/
	var url = "/clan_info.php?clan_id="+clan_id;
	window.open(url, "", "width=730,height=650,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showFriendsInfo() {
	var url = "/friend_list.php";
	window.open(url, "contacts", "width=900,height=650,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function showAchievementInfo(achievement_id) {
	var url = "/achievement_info.php?id="+achievement_id;
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function userPrvTag() {
	var chatFrame = getChatFrame();
	try {
		for (i=0; i<arguments.length; i++) chatFrame.chatPrvTag(arguments[i]);
                   }
                   catch (e) {}
                   }
                   function userToTag() {
                   var chatFrame=getChatFrame();
                   try {
                   for (i=0; i<arguments.length; i++) chatFrame.chatToTag(arguments[i]);
	}
	catch (e) {}
}


function userIgnore(name,status) {
	var chatFrame = getChatFrame();
	try {
		chatFrame.chatSyncIgnore(name,status);
	}
	catch (e) {}
}

function userAttack(nick, url_error) {
	var rnd = Math.floor(Math.random()*1000000000);
	var url_success = 'fight.php?'+rnd;
	var urlATTACK = 'action_run.php?code=ATTACK&url_success='+url_success+'&url_error='+escape(url_error||'area.php')+'&in[nick]='+(nick ? nick : '');
	tProcessMenu('b07', {force: true, lock: true, url: urlATTACK});
//	try {
//		if (!top.frames["main_frame"].frames["main"].__fight_php__) top.frames["main_frame"].frames["main"].location.href = urlATTACK;
//	}
//	catch (e) {}
}
function confirm_friend(url) {
	try {
		top.frames['main_frame'].frames['main_hidden'].location.href = url;
	}
	catch (e) {}
}

function confirm_bg(area_id) {
	try {
		top.frames['main_frame'].frames['main'].location.href = 'area_bgs.php?area_id=' + area_id + '&action=confirm';
	}
	catch (e) {}
}

function confirm_slaughter(area_id) {
	try {
		top.frames['main_frame'].frames['main'].location.href = 'area_bgs.php?area_id=' + area_id + '&action=confirm_slaughter';
	}
	catch (e) {}
}

function show_slaughter_stat(instance_id, finish, baseurl) {
	if (baseurl+'' == 'undefined') baseurl = '';
	var url = baseurl + 'instance_stat.php?outside=1&instance_id=' + instance_id + '&finish=' + finish;
	window.open(url, "", "width=730,height=550,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
//	try {
//		top.frames['main_frame'].frames['main'].location.href = 'instance_stat.php?instance_id=' + instance_id + '&finish=' + finish;
//	}
//	catch (e) {}
}

function getChatFrame() {
	var win = window
	try {win = dialogArguments || window} catch(e) {}
	////while (win.opener) win = win.opener;
	if (win.closed) return;

	return win.top.frames['chat'];
}

function fightHelpRequest() {
	var chatFrame = getChatFrame();
	try {
		chatFrame.chatSendMessage('/FIGHTHELP');
	} catch(e) {}
}

function parse_str(str, array){	// Parses the string into variables
    //
    // +   original by: Cagri Ekin
    // +   improved by: Michael White (http://crestidg.com)

    var glue1 = '=';
    var glue2 = '&';

    var array2 = str.split(glue2);
    var array3 = [];
    for(var x=0; x<array2.length; x++){
        if (!array2[x]) continue;
        var tmp = array2[x].split(glue1);
        array3[unescape(tmp[0])] = unescape(tmp[1] || '').replace(/[+]/g, ' ');
    }

    if(array){
        array = array3;
    } else{
        return array3;
    }
}
function html_button(title, param) {
	var html = '';

	param = param || {};
	
	var add = param.add || '';
	var addClassName = param.className || '';
	html += '<b class="butt1 pointer ' + addClassName+ '"><b>';
	html +=	'	<button class="butt1" ' + add + '>' + title + '</button>';
	html +=	'</b></b>';
	
	return html;
}

if (window.$) {
	$(document).on('click', 'span.artifact-slot', function (evnt) {
		var win;
		
		if (_top().dwar) {
			var win = _top();
		} else {
			win = window.opener || _top().dialogArguments.win;
		}

		var constants = win._top().dwar || {};
		var actionBlock = $(this).find('.artifact-slot__action').eq(0),
			action;
		
		for (var k in constants.backpack.actions) {
			action = constants.backpack.actions[k];
			
			if (actionBlock.hasClass(action)) {
				var art = actionBlock.parents('.artifact-slot:first').get(0);
				
				if (action === 'info') {
					var artifact_id = art.getAttribute('artifact_id');
					var gift_id = art.getAttribute('gift');
					var artikul_id = art.getAttribute('artikul_id');
					var set_id = art.getAttribute('set_id');

					if (gift_id !== null) {
						gift_id = artifact_id;
						artifact_id = false;
					}
					
					showArtifactInfo(artifact_id, artikul_id, set_id, evnt, gift_id);
				} else {
					if (typeof artifactAct != 'undefined') {
						artifactAct(art, action);
					}
				}
				break;
			}
		}
		// есть артефакты, для которых нет действия
	}).on('mouseenter', 'span.artifact-slot', function (e) {
		$(this).addClass('hover');
		artifactAlt(this, e, 2);
		// показать альт
	}).on('mouseleave', 'span.artifact-slot', function (e) {
		$(this).removeClass('hover');
		artifactAlt(this, e, 0);
		// скрыть альт
	}).on('mouseenter', 'span.artifact-slot__ico', function () {
		var win;

		if (_top().dwar) {
			var win = _top();
		} else {
			win = window.opener || _top().dialogArguments.win;
		}

		var constants = win._top().dwar || {};
		// изменить действие
		var classes = this.className.split(' '), actionBlock, action;
		for (var i in classes) {
			action = classes[i];

			if (inArray(action, constants.backpack.actions)) {
				actionBlock = $(this).parents('.artifact-slot:first').find('.artifact-slot__action');
				actionBlock.get(0).cache = actionBlock.eq(0).clone();
				actionBlock.removeClass(constants.backpack.actions.join(' ')).addClass(action);
				break;
			}
		}
	}).on('mouseleave', 'span.artifact-slot__ico', function () {
		var actionBlock = $(this).parents('.artifact-slot:first').find('.artifact-slot__action');
		var actionElem = actionBlock.get(0);
		actionElem.className = actionElem.cache.get(0).className;
	});
	
	jQuery.fn.extend({
		clanMngGradeTabInit: function () {
			var activeClass = 'active';
			var defaultClass = 'default';
			var e = $(this); 
			var n = 0;
			var page_param = parse_str(location.search.split('?')[1]);
			$('.tab a').each(function(i, v) {
				$(v).click(function(e) {
					$(this).clanMngGradeDismarkTab();
					e.preventDefault();
					if (!$(v).hasClass(activeClass) && !$(v).hasClass('lock')) {
						var oldActiveTabName = $('a.' + activeClass).removeClass(activeClass).addClass(defaultClass).get(0).hash.split('=')[1];
						var oldActive = $('.' + oldActiveTabName);
						if (oldActive.length > 0) {
							var tab_e = $('#' + oldActiveTabName + '-link');
							oldActive.hide().each(function (j, h) {
								if ($(h).hasClass('not-saved')) {
									tab_e.clanMngGradeMarkTab();
									return false;
								}
							});
						}
						var newClass = '.'+$(this).removeClass(defaultClass).addClass(activeClass).get(0).hash.split('=')[1];
						$(newClass).show();
						$('#colspan-watch').attr('colspan', $(newClass).eq(0).parent().children(newClass).length);
					}
				});
				var submode = v.hash.split('=')[1];
				if ($(v).parent().get(0).id === (page_param.submode + '-link')) $(v).addClass(activeClass).removeClass(defaultClass);
				var t = $('.'+ submode);
				if (!page_param.submode && i === 0 || page_param.submode === submode) {
					n = t.show().eq(0).parent().children('.'+submode).length;
				}
			});
			$('#colspan-watch').attr('colspan', n);
			if (!page_param.submode) $('.tab:first a').removeClass(defaultClass).addClass(activeClass);
			e.parents('form:first').submit(function(event) {
				$(this).attr('action', $(this).attr('action') + '&' +$('.tab a.active').get(0).hash.split('#')[1]);
				var changed = e.find('.not-saved');
				if (changed.length > 0) {
					event.preventDefault();
					var pst = changed.length > 1 ? 's' : ''; var onlyGradeName = true;
					var change_grade = {}; var j = 1;
					changed.each(function (i, c) {
						c = $(c).find('input').get(0);
						if (c.type === 'checkbox') onlyGradeName = false;
						var grade_e = $(c).parents('tr:first').find('td:first input').get(0);
						var rowIndex = $(c).parents('tr:first').get(0).rowIndex;
						if (change_grade[rowIndex]) return true;
						var grade_title = grade_e.value;
						if (grade_e.firstVal !== grade_e.value) grade_title += ' ( ' + grade_e.firstVal + ' ) ';
						var lmsg = "<br/>" + (j++) + '. ' + grade_title;
						change_grade[rowIndex] = lmsg;
					});
					var msg = _top().dwar.lang.clan.management[(onlyGradeName ? 'confirmChangeGradeName' : 'confirmChangeGradePerm') + pst] + "<br/>";
					for (var i in change_grade) msg += change_grade[i];
					return _top().systemConfirm(null, msg, this);
				};
			});
		},
		inputSetHint: function() {
			var e = this[0]; var c = 'hint';
			var hint = e.getAttribute('rel');
			if (e.value === '') this.addClass(c).val(hint);
			this.focusin(function() {
				if (e.value === hint) $(this).removeClass(c).val('');
			}).focusout(function() {
				if (e.value === '') $(this).addClass(c).val(hint);
			});
		},
		clanManagementGradeWatch: function () {
			this.each(function(i, v) {
				$(v).find('input').each(function(j, e) {
					e.firstVal = (e.type === 'checkbox' ? e.checked : e.value);
					$(e).change(function() {
						$(this).clanMngGradeMarkCell();
						var td = $(e).parents('td:first');
						if (td.hasClass('not-saved')) return;
						var group = td[0].className;
						if ($(v).find('.' + group + '.not-saved').length === 0) {
							var tab = $('#' + group + '-link');
							if (tab.length) tab.clanMngGradeDismarkTab();
						}
					});
				});
			});
		},
		clanMngGradeMarkCell: function () {
			var f = this.get(0); var isCheckBox = (f.type === 'checkbox');
			var fv = f.firstVal; var td = this.parents('td:first');
			if (isCheckBox && fv === f.checked || !isCheckBox && fv === f.value) {
				td.removeClass('not-saved');
			} else {
				td.addClass('not-saved');
			}
		},
		clanMngGradeMarkTab: function () { if (this.find('.warn').length === 0) this.children().prepend($('<span/>').addClass('warn').attr('title', _top().dwar.lang.clan.management.notSaveWarn).html('<img src="images/warning-ico.png">')); },
		clanMngGradeDismarkTab: function() { this.find('.warn').remove(); },
		swapWith: function(to) {
			return this.each(function() {
				var copy_to = $(to).clone(true);
				var copy_from = $(this).clone(true);
				$(to).replaceWith(copy_from);
				$(this).replaceWith(copy_to);
			});
		}
	});
}

//fight_id where `target_nick` need help
function fightHelp(fight_id, target_nick, confirm_msg) {
    top.systemConfirm(confirm_msg + ' ' + target_nick + '?', 'Дія', false, function() {

        try {
            var rnd = Math.floor(Math.random()*1000000000);
            var err_url = top.frames["main_frame"].frames["main"].location;
            var url = 'action_run.php?code=FIGHT_HELP'+
                '&in[fight]=' + escape(fight_id) +
                '&in[target_nick]=' + target_nick +
                '&url_success=' + escape('fight.php?'+rnd) +
                '&url_error=' + escape(err_url) +
                '&' + rnd;
            top.frames["main_frame"].frames["main"].location.href = url;
        }
        catch (e) {
        }
    })
}

function botAttack(bot_id, url_error) {
	var rnd = Math.floor(Math.random()*1000000000);
	var url_success = 'fight.php?'+rnd;
	var urlATTACK = 'action_run.php?code=ATTACK_BOT&url_success='+url_success+'&url_error='+escape(url_error||'area.php')+'&bot_id='+bot_id;
	if (!bot_id) return;
	try {
		if (!top.frames["main_frame"].frames["main"].__fight_php__) top.frames["main_frame"].frames["main"].location.href = urlATTACK;
	}
	catch (e) {}
}

function huntAttack(bot_id) {
	botAttack(bot_id,'hunt.php');
}

function _background(obj, name) {
	if (obj.tagName == 'IMAGE') {
		obj.src = name;
	} else {
		obj.style.backgroundImage = 'url('+name+')'
	}
}

function getIframeShift() {
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

function artifactAlt(obj, evnt, show) {
    // Сортировка в рюкзаке
    if (typeof (iam_sorting_now) !== 'undefined' && iam_sorting_now) {
        show = 0;
    }

    var art_id = obj.getAttribute('div_id');
    if (!art_id) art_id = 'AA_' + obj.getAttribute('artifact_id');
    var artifact_alt = _top().gebi('artifact_alt');
    if (!artifact_alt) return;

    var tpl = obj.getAttribute('tpl');
    tpl = tpl ? tpl : 'renderArtifactAlt';

    var collection = obj.getAttribute('collection') || false;
    var act1 = obj.getAttribute('act1');
    var act2 = obj.getAttribute('act2');
    var act3 = obj.getAttribute('act3');
    var act4 = obj.getAttribute('act4') || 0;
    if (act2 === null) act2 = 0;
    if (act3 == 0 || act3 === null) act3 = '' // костыль что бы не переименовывать картинки в локализациях
    if (act4 == 0) act4 = '';
    if (act1 == null) act1 = 0;
    if (show == 2) {
        document.onmousemove = function (e) {
            artifactAlt(obj, e || event, 1);
        }

        if (!artifact_alt.getAttribute('art_id') || obj.getAttribute('div_id') != artifact_alt.getAttribute('art_id')) {
            if (typeof (art_alt) == "undefined") {
                console_log('art_alt is undefined', 'artifactAlt_1');//js_error_log
            } else if (!art_alt[art_id]) {
                console_log('art_alt[art_id] is empty', 'artifactAlt_2');//js_error_log
            } else if (art_alt[art_id] && art_alt[art_id] != undefined) {
                artifact_alt.innerHTML = window[tpl](art_id);
            }
            artifact_alt.setAttribute('art_id', obj.getAttribute('div_id'));
        }

        artifact_alt.style.display = 'block';
        if (_top().locale_path && (act1 || act2 || act3 || act4 || !collection)) {
            _background(obj, (_top().locale_path + "images/itemact-" + act1) + act2 + (act3 + (act4 + ".gif")));
        }

        _top().obj = obj;
        if (_top().show_alt) {
            _top().show_alt();
        }
    }
    if (!show) {
        if (act1 || act2 || act3 || act4) {
            _background(obj, 'images/d.gif');
        }
        artifact_alt.style.display = 'none';
        document.onmousemove = function () {
        }
        return;
    }

    var coor = getIframeShift();
    var ex = evnt.clientX + coor.left;
    var ey = evnt.clientY + coor.top;

    if (_top().noIframeAlt) {
        ex = evnt.clientX + _top().document.body.scrollLeft;
        ey = evnt.clientY + _top().document.body.scrollTop;
    }
    if (_top().recruit) {
        ey = evnt.clientY + _top().document.body.scrollTop;
    }

    if (act1 || act2 || act3 || act4 || collection) {
        obj.style.cursor = 'pointer'
        obj.onclick = (act1 != 0 ? function (e) {
            try {
                artifactAct(obj, act1, e || event)
            } catch (e) {
            }
        } : function (e) {
            showArtifactInfo(obj.getAttribute('aid'), obj.getAttribute('art_id'), null, e || event, obj.getAttribute('gift_id'))
        });
        if (!collection)
            _background(obj, (_top().locale_path + "images/itemact-" + act1) + act2 + (act3 + (act4 + ".gif")));
        var coord = getCoords(obj)
        var cont = gebi("item_list")
        var scroll_x = window.scrollX || window.document.body.scrollLeft;
        var scroll_y = window.scrollY || window.document.body.scrollTop;
        var rel_x = (ex + scroll_x - coord.l - coor.left);
       // var rel_y = (ey + scroll_y - coord.t - coor.top);
		//var rel_y = (ey + cont.scrollTop - coord.t - coor.top)
        if (rel_x >= 40) {
			var rel_y = (ey + scroll_y - coord.t - coor.top);
            if (rel_y < 20) {
				var rel_y = (ey + cont.scrollTop - coord.t - coor.top)
                if (obj.getAttribute('gift_id')) { // для подарков
                    obj.onclick = function (e) {
                        showArtifactInfo(false, false, null, e || event, obj.getAttribute('gift_id'))
                    };
                } else if (obj.getAttribute('store')) { // в магазине при клике на info необходимо выводить товар по артикулу
                    obj.onclick = function (e) {
                        showArtifactInfo(false, obj.getAttribute('art_id'), null, e || event)
                    };
                } else {
                    obj.onclick = function (e) {
                        showArtifactInfo(obj.getAttribute('aid'), null, null, e || event)
                    }
                }
                if (!collection)
                    _background(obj, _top().locale_path + 'images/itemact_info' + act2 + (act3 + (act4 + '.gif')));
                try {
                    obj.style.cursor = 'hand'
                } catch (e) {
                }
                try {
                    obj.style.cursor = 'pointer'
                } catch (e) {
                }
            }
            if (act2 != 0 && rel_y >= 40) {
                obj.onclick = function (e) {
                    try {
                        artifactAct(obj, act2, e || event)
                    } catch (e) {
                    }
                }
                if (!collection)
                    _background(obj, _top().locale_path + 'images/itemact_drop' + act2 + (act3 + (act4 + '.gif')));
                try {
                    obj.style.cursor = 'hand'
                } catch (e) {
                }
                try {
                    obj.style.cursor = 'pointer'
                } catch (e) {
                }
            }
        }
        if (act3 > 0 && rel_x < 20) {
			var rel_y = (ey + cont.scrollTop - coord.t - coor.top)
            if (rel_y < 20) {
                obj.onclick = function (e) {
                    try {
                        artifactAct(obj, act3, e || event)
                    } catch (e) {
                    }
                };
                if (!collection)
                    _background(obj, _top().locale_path + 'images/itemact_use' + act2 + (act3 + (act4 + '.gif')));
                try {
                    obj.style.cursor = 'hand'
                } catch (e) {
                }
                try {
                    obj.style.cursor = 'pointer'
                } catch (e) {
                }
            }
        }
        if (act4 > 0 && rel_x > 20 && rel_x <= 40) {
			var rel_y = (ey + cont.scrollTop - coord.t - coor.top)
            if (rel_y > 40) {
                obj.onclick = function (e) {
                    try {
                        artifactAct(obj, act4, e || event)
                    } catch (e) {
                    }
                };
                if (!collection)
                    _background(obj, _top().locale_path + 'images/itemact_sup' + act2 + (act3 + (act4 + '.gif')));
                try {
                    obj.style.cursor = 'hand'
                } catch (e) {
                }
                try {
                    obj.style.cursor = 'pointer'
                } catch (e) {
                }
            }
        }
    }

    var top = _top(),
        iframeShift = getIframeShift(),
        bodyRect = top.document.body.getBoundingClientRect(),

        quirksIE = (top.document.documentMode && top.document.documentMode === 5) ? true : false;

    var x, y, sx, sy, prnt;

    if (evnt) {
        evnt = fixEvent(evnt);
    }

    prnt = evnt.target;
    sx = 0;
    sy = 0;

    if (navigator.userAgent.toLowerCase().indexOf('firefox') === -1) {
        while (prnt.nodeName !== 'BODY') {
            prnt = prnt.parentNode;

            if (prnt.nodeName === 'DIV' && prnt.getAttribute('id') === 'overflowFix') {
                sx = prnt.scrollLeft;
                sy = prnt.scrollTop;

                break;
            }
        }
    }

    if (quirksIE) {
        x = evnt.screenX - top.screenLeft;
        y = evnt.screenY - top.screenTop;

        if (x + artifact_alt.offsetWidth > bodyRect.right) {
            x -= artifact_alt.offsetWidth + 20;
        }

        if (x < 0) {
            x = (evnt.screenX - top.screenLeft) - artifact_alt.offsetWidth / 2;
        }

        if (y + artifact_alt.offsetHeight > bodyRect.bottom) {
            y -= artifact_alt.offsetHeight + 20;
        }

        if (y < 0) {
            y = evnt.screenY - top.screenTop + 10;
        }

        artifact_alt.style.position = 'absolute';
        artifact_alt.style.left = x + sx + top.document.body.scrollLeft + 10 + 'px';
        artifact_alt.style.top = y + sy + top.document.body.scrollTop + 10 + 'px';
    } else {
        x = evnt.clientX + iframeShift.left;
        y = evnt.clientY + iframeShift.top;

        if (x + artifact_alt.offsetWidth > bodyRect.right) {
            x -= artifact_alt.offsetWidth + 20;
        }

        if (x < 0) {
            x = (evnt.clientX + iframeShift.left) - artifact_alt.offsetWidth / 2;
        }

        if (y + artifact_alt.offsetHeight > bodyRect.bottom) {
            y -= artifact_alt.offsetHeight + 20;
        }

        if (y < 0) {
            y = evnt.clientY + iframeShift.top + 10;
        }

        artifact_alt.style.position = 'fixed';
        artifact_alt.style.left = x + sx + 10 + 'px';
        artifact_alt.style.top = y + sy + 10 + 'px';
    }

    return false;
}

function userAlt(obj, evnt, show) {
	var soc_id = obj.getAttribute('soc_id');
	var soc_user_id = obj.getAttribute('soc_user_id');
	var user_alt = top.gebi('artifact_alt');
	if (!user_alt) return;

	if (show == 1) {
		document.onmousemove=function(e) {userAlt(obj, e||event, 1);}

		if (!user_alt.getAttribute('soc_id') || !user_alt.getAttribute('soc_user_id') || obj.getAttribute('soc_id') != user_alt.getAttribute('soc_id') || obj.getAttribute('soc_user_id') != user_alt.getAttribute('soc_user_id') ) {
			if (soc_user_alts[soc_id] && soc_user_alts[soc_id] != undefined || soc_user_alts[soc_user_id] && soc_user_alts[soc_user_id] != undefined) {
				user_alt.innerHTML = renderUserAlt(soc_id, soc_user_id);
			}
			user_alt.setAttribute('soc_id',obj.getAttribute('soc_id'));
			user_alt.setAttribute('soc_user_id',obj.getAttribute('soc_user_id'));
		}

		user_alt.style.display = 'block';

		top.obj = obj;
		if (top.show_alt) {
			top.show_alt();
		}
	}
	if (!show) {
		user_alt.style.display = 'none';
		document.onmousemove=function(){}
		return;
	}

	var coor = getIframeShift();
	var ex = evnt.clientX+coor.l;
	var ey = evnt.clientY+coor.t;

	if (top.noIframeAlt) {
		ex = evnt.clientX + top.document.body.scrollLeft;
		ey = evnt.clientY + top.document.body.scrollTop;
	}

	var x = ex + user_alt.offsetWidth > top.document.body.clientWidth - 20 ? ex - user_alt.offsetWidth - 10 : ex + 10;
	var y = ey + user_alt.offsetHeight - top.document.body.scrollTop > top.document.body.clientHeight - 20 ? ey - user_alt.offsetHeight - 10 : ey + 10;

	if (x < 0 ) {
		x = ex - user_alt.offsetWidth/2;
	}
	if (x < 7 ) {
		x = 7;
	}
	if (x > top.document.body.clientWidth - user_alt.offsetWidth - 20) {
		x= top.document.body.clientWidth - user_alt.offsetWidth - 20;
	}

	user_alt.style.left = x;
	user_alt.style.top = y;

	return;
}

function renderUserAlt(soc_id, soc_user_id) {
	var a = soc_user_alts[soc_id][soc_user_id];
	var content = '';

	content += '<table width="200" border="0" cellspacing="0" cellpadding="0" style="background-color:#FBD4A4;">';
	content += '<tr><td width="14" class="aa-tl"><img src="images/d.gif" width="14" height="24"><br></td>';
	content += '<td class="aa-t" align="center" style="vertical-align:middle"><b>' + a.name + '</b></td>';
	content += '<td width="14" class="aa-tr"><img src="images/d.gif" width="14" height="24"><br></td></tr>';
	content += '<tr><td class="aa-l" style="padding:0;"></td><td style="padding:0;">';
	content += '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td align="center">';
	content += '<img src="' + a.avatar + '" alt="">';
	content += '</td></tr></table>';
	content += '</td><td class="aa-r" style="padding:0px"></td></tr>';
	content += '<tr><td class="aa-bl"></td><td class="aa-b"><img src="images/d.gif" width="1" height="5"></td><td class="aa-br"></td></tr>';
	content += '</table>';

	return content;
}

function renderArtifactAlt(id) {
    var a = get_art_alt(id);
    if (!a) {
        //console_log('art_alt[id] is empty', 'renderArtifactAlt', id, window);//js_error_log
        return '';
    }
    // var a = art_alt[id];
    var bg = true;
    var i = 0;
    var content = '';

    content += '<table width="300" border="0" cellspacing="0" cellpadding="0" style="background-color:#FBD4A4;">';
    content += '<tr><td width="14" class="aa-tl"><img src="images/d.gif" width="14" height="24"><br></td>';
    content += '<td class="aa-t" align="center" style="vertical-align:middle"><b style="color:' + a.color + '">' + a.title + '</b></td>';
    content += '<td width="14" class="aa-tr"><img src="images/d.gif" width="14" height="24"><br></td></tr>';
    content += '<tr><td class="aa-l" style="padding:0;"></td><td style="padding:0;">';
    content += '<table width="275" style=" margin: 3px" border="0" cellspacing="0" cellpadding="0"><tr>';
    content += '<td align="center" valign="top" width="60">';
    content += '<table width="60" height="60" cellpadding="0" cellspacing="0" border="0" style="margin: 2px" background="' + a.image + '"><tr><td valign="bottom">';
    if (a.count && a.count != undefined) {
        content += '<div class="artifact-slot-qnt">' + a.count + '</div>';
    } else if (a.enchant_icon && a.enchant_icon != undefined) {
        content += a.enchant_icon.replace(/&quot;/g, '"');
    } else {
        content += '&nbsp;';
    }
    content += '</td></tr></table>';
    content += '</td><td>';
    content += '<div><img src="images/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle">&nbsp;' + a.kind + '</div>';
    if (a.dur && a.dur != undefined) {
        content += '<div><img src="images/tbl-shp_item-iznos.gif" width="11" height="10" align="absmiddle"> <span class="red">' + a.dur + '</span>/' + a.dur_max + '</div>';
    }
    if (a.price && a.price != undefined) {
        content += '<div class="b red">' + a.price.replace(/&quot;/g, '"') + '</div>';
    }
    if (a.com && a.com != undefined) {
        content += '<div class="b red">' + a.com.title + ' ' + a.com.value + '</div>';
    }
    if (a.owner && a.owner != undefined) {
        content += '<div><b class="b red">' + a.owner.title + '</b>' + a.owner.value + '</div>';
    }
    content += '</td><td>';
    if (a.lev && a.lev != undefined) {
        content += '<div><img src="images/tbl-shp_level-icon.gif" width="11" height="10" align="absmiddle"> ' + a.lev.title + ' <b class="red">' + a.lev.value + '</b></div>';
    }
    if (a.trend && a.trend != undefined) {
        content += '<div><img src="images/tbl-shp_item-trend.gif" width="11" height="10" align="absmiddle">&nbsp;' + a.trend.replace(/&quot;/g, '"') + '</div>';
    }
    if (a.cls && a.cls != undefined) {
        content += '<div><img src="images/class.gif" width="11" height="10" align="absmiddle"> ';
        for (i in a.cls) {
            content += a.cls[i].replace(/&quot;/g, '"');
        }
        content += '</div>'
    }
    content += '</td></tr></table>';
    content += '<table width="100%" cellpadding="0" cellspacing="0" border="0">';
    if (a.exp && a.exp != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.exp.title + '</td><td class="grnn b" align="right">' + a.exp.value.replace(/&quot;/g, '"') + '</td></tr>';
        bg = !bg;
    }
    if (a.skills && a.skills != undefined) {
        for (i in a.skills) {
            content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.skills[i].title + '</td><td class="red" align="right">' + a.skills[i].value.replace(/&quot;/g, '"') + '</td></tr>';
            bg = !bg;
        }
    }
    if (a.skills_e && a.skills_e != undefined) {
        for (i in a.skills_e) {
            content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.skills_e[i].title + '</td><td class="red" align="right">' + a.skills_e[i].value.replace(/&quot;/g, '"') + '</td></tr>';
            bg = !bg;
        }
    }
    if (a.enchant && a.enchant != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.enchant.title + '</td><td class="red" align="right">' + a.enchant.value + '</td></tr>';
        bg = !bg;
    }
    if (a.oprava && a.oprava != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.oprava.title + '</td><td class="red" align="right">' + a.oprava.value + '</td></tr>';
        bg = !bg;
    }
	if (a.symbols && typeof (a.symbols) == "object") {
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
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.enchant_mod.title + '</td><td class="red" align="right">' + a.enchant_mod.value.replace(/&quot;/g, '"') + '</td></tr>';
        bg = !bg;
    }
    if (a.set && a.set != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td>' + a.set.title + '</td><td class="red" align="right">' + a.set.value.replace(/&quot;/g, '"') + '</td></tr>';
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
    if (a.nofrozen && a.nofrozen != undefined) {
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2" class="violet b">' + a.nofrozen + '</td></tr>';
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
        content += '<tr class="skill_list ' + (bg ? 'list_dark' : '') + '"><td colspan="2">' + a.desc.replace(/&quot;/g, '"') + '</td></tr>';
        bg = !bg;
    }

    content += '</table>';
    content += '</td><td class="aa-r" style="padding:0px"></td></tr>';
    content += '<tr><td class="aa-bl"></td><td class="aa-b"><img src="images/d.gif" width="1" height="5"></td><td class="aa-br"></td></tr>';
    content += '</table>';

    return content;
}

function updateBag() {
	var win = window
	try {win = dialogArguments ?  dialogArguments.win || dialogArguments : window} catch(e) {}
	////while (win.opener) win = win.opener;
	if (win.closed) return false;

	try{
		var win_main = win.top.frames['main_frame'].frames['main']
		if(win_main.is_userphp) {
				win_main.location.href = win_main.urlMODE + '&update_swf=1'
			return true;
		}
	}
	catch (e) {}
	return false;
}
function updateSwf(params) {
	var win = window
	try {win = dialogArguments ?  dialogArguments.win || dialogArguments : window} catch(e) {}
	////while (win.opener) win = win.opener;
	if (win.closed) return;

	var url = 'main_iframe.php?mode=update_swf';
	if (!params) return;
	try {
		for (i in params) {
	  		url += '&tar[]='+i;
	  		if (params[i]) url += '&add['+i+']='+escape(params[i]);
		}
		win.top.frames['main_frame'].frames['main_hidden'].location.href = url;
	}
	catch (e) {}
}

function updateHP() {
	updateSwf({'lvl': '' ,'items': ''});
}
function fightRedirect(fight_id, cd) {
	if (!cd || isNaN(cd)) cd = false;
	else {
		setTimeout(function(){
			fightRedirect(fight_id);
		}, cd);
		return;
	}
	var rnd = Math.floor(Math.random()*1000000000);
	var url = 'fight.php?'+rnd;
	if (top.__lastFightId && (top.__lastFightId >= fight_id)) return;
	top.__lastFightId = fight_id;
//	top.frames["main_frame"].frames["main"].location.href = url;
	tProcessMenu('b06', {url: url, lock: true, force: true});
}

function fightFinished() {
	try {
		tUnlockFrame();
		tUnsetFrame('main');
		top.frames["main_frame"].frames["main"].__fight_php__ = false;
	} catch (e) {}
}

function updatePartyLoot() {
	try {
		top.frames['main_frame'].frames['main_hidden'].location.href = 'main_iframe.php?mode=update_party';
	} catch (e) {};
}

function fightUpdateLog(ctime, nick1, level1, nick2, level2, code, i1, i2, i3, s1) {
	try {
		top.frames['main_frame'].frames['main'].fightUpdateLog(ctime, nick1, level1, nick2, level2, code, i1, i2, i3, s1);
	} catch (e) {};
}

function resurrect(paidResurrect) {
	addurl = '';
	if (paidResurrect) {
		addurl = '&in[paidResurrect]=1'
	}
	//top.frames["main_frame"].frames["main"].location.href = 'action_run.php?code=RESURRECT&url_success=area.php&url_error=area.php'+addurl;
	tProcessMenu('b06', {url: 'action_run.php?code=RESURRECT&url_success=area.php&url_error=area.php'+addurl});
}

function inArray(needle, haystack) {
    var length = haystack.length;
    for (var i = 0; i < length; i++) {
        if (haystack[i] == needle) return true;
    }
    return false;
}


function _html_money_gold_str(o, amount) {
    var str = '<a href="' + o.url + '" target="_blank">';
    str += '<span title="' + o.alt + '"><img src="/images/' + o.picture + '" border="0" width="11" height="11" align="absmiddle" /></span></a>';
    str += '&nbsp;' + amount + '&nbsp;';
    return str;
}

function html_money_str(count, price, price_gold, price_work, price_silver, hide_empty_silver) {
    var multi = price < 0 ? -1 : 1;
    multi *= price_gold < 0 ? -1 : 1;
    multi *= price_work < 0 ? -1 : 1;
    multi *= price_silver < 0 ? -1 : 1;
    var money_info = _top().money_type_info;
    var str = '';

    var amount = moneyRound(Math.abs(price) * count);
    var amount_gold = moneyRound(Math.abs(price_gold) * count);
    var amount_work = moneyRound(Math.abs(price_work) * count);
    var amount_silver = moneyRound(Math.abs(price_silver) * count);

    var gold = Math.floor(amount / 100);
    var silver = Math.floor(amount - (gold * 100));

    //работаем с int чтобы избежать проблем типа 101.1-101 = 0.09999999999999432
    amount = parseInt(amount * 100, 10);
    var bronze = Math.floor(amount - gold * 10000 - silver * 100);

    if (gold) str += _html_money_str(money_info.gold, gold);
    if (!hide_empty_silver && gold && bronze || silver) str += _html_money_str(money_info.silver, silver);
    if (bronze) str += _html_money_str(money_info.bronze, bronze);

    if (amount_gold) str += _html_money_gold_str(money_info.brilliante, parseFloat(amount_gold) ? (Math.round(amount_gold) == amount_gold ? Math.round(amount_gold) : amount_gold) : 0.00);
    if (amount_work) str += _html_money_str(money_info.work, amount_work, true);
    if (amount_silver) str += _html_money_gold_str(money_info.ruby, parseFloat(amount_silver) ? amount_silver : 0.00);

    if (str == '') {
        str += _html_money_str(money_info.bronze, 0);
    }

    return (multi == -1 ? ' - ' : '') + str;
}

function _html_money_str(o, amount, nospace) {
    var str = '<span title="' + o.alt + '"><img src="/images/' + o.picture + '" border=0 width=11 height=11 align=absmiddle>&nbsp;' + amount + (nospace ? '' : '&nbsp;') + '</span>';
    return str;
}

function moneyRound(num) {
    return parseFloat(num) ? num.toFixed(2) : num;
}

function artifact_calc_sell_price(e) {
    e = $(e);

    var price = parseFloat(e.attr('sell_price'));

    return {money: price};
}

function artifact_calc_repair_price(e) {
    e = $(e);

    return {money: parseFloat(e.attr('repair_price'))}
}

function artifact_get_color(artifact_id) {
    var artifact_alt = get_art_alt('AA_' + artifact_id);
    return artifact_alt.color;
}


function quality_color(quality){
    var quality_info = {
        0:'#666666',
        1:'#339900',
        2:'#3300ff',
        3:'#990099',
        4:'#ff0000',
        5:'#016e71',
        6:'#ff4500',
        7:'#b55e00',
    };
    return quality_info[quality];
}

//implement JSON.stringify serialization.
var JSON = JSON || {};

JSON.stringify = JSON.stringify || function (obj) {
    var t = typeof (obj);
    if (t != "object" || obj === null) {
        // simple data type
        if (t == "string") obj = '"' + obj + '"';
        return String(obj);
    } else {
        // recurse array or object
        var n, v, json = [], arr = (obj && obj.constructor == Array);
        for (n in obj) {
            v = obj[n];
            t = typeof (v);
            if (t == "string") v = '"' + v + '"';
            else if (t == "object" && v !== null) v = JSON.stringify(v);
            json.push((arr ? "" : '"' + n + '":') + String(v));
        }
        return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
    }
};

// =======================================================================================

function js_money_input_assemble(id_prefix) {
	var m1 = gebi(id_prefix+'1').value;
	var m2 = gebi(id_prefix+'2').value;
	var m3 = gebi(id_prefix+'3').value;

	if (m1.match(/[^0-9.]/)) m1 = m1.replace(/[^0-9].*$/, '');
	if (m2.match(/[^0-9.]/)) m2 = m2.replace(/[^0-9].*$/, '');
	if (m3.match(/[^0-9.]/)) m3 = m3.replace(/[^0-9].*$/, '');
	v = m1/100.0 + m2*1.0 + m3*100.0;
	res = (isNaN(v) || v <= 0) ? 0 : (1.0 * (1.0*v).toFixed(2)).toFixed(2);
	return res*1.0;
}

function js_money_input_fill(id_prefix, amount) {
	var m1 = gebi(id_prefix+'1');
	var m2 = gebi(id_prefix+'2');
	var m3 = gebi(id_prefix+'3');

    var str = ' ';
	var t=[];
	amount = amount * 100;
	for (i = 0; i < 2; i++) {
		t[i] = (amount % 100);
		amount = (amount - t[i]) / 100;
	}
	t[2] = amount;
	m1.value = t[0].toFixed(0);
	m2.value = t[1].toFixed(0);
	m3.value = t[2].toFixed(0);
}

// ========= swf data transfer functions ===============================================================

function getSWF(name) {
    var win = window;
    var mainFrame = win._top().frames.main_frame;
    try {
        win = dialogArguments || window
    } catch (e) {
    }
    //while (win.opener) win = win.opener;
    if (win.closed) return;
    switch (name) {
        case 'top_mnu':
        case 'lvl':
        case 'items':
        case 'dialog':
        case 'items_right':
            win = win._top().frames.main_frame;
            break;
        case 'game':
        case 'mem':
        case 'area':
        case 'instance':
        case 'wheel_fortune':
        case 'estate':
            win = win._top().frames.main_frame.frames.main;
            break;
        case 'inventory':
		    win = win._top().frames.main_frame.frames.main;
            break;
        case 'magic':
        case 'cube':
			//win = win._top().frames.main_frame.frames.backpack;
			win = mainFrame ? mainFrame.frames.main : null;
            break;
        case 'world':
            win = win._top().opened_windows['world_map'];
            name = 'game';
            break;
    }
    if (navigator.appName.indexOf("Microsoft") != -1) {
        return win[name];
    } else {
        return win.document[name];
    }
}

/*function getSWF(name) {
    var win = window;
    var mainFrame = win._top().frames.main_frame;
    try {
        win = dialogArguments || window
    } catch (e) {
    }
    //while (win.opener) win = win.opener;
    if (win.closed) return;

    win = window;
    switch (name) {
        case 'top_mnu':
        case 'lvl':
        case 'items':
        case 'dialog':
        case 'items_right':
            win = mainFrame;
            break;
        case 'game':
        case 'mem':
        case 'area':
        case 'instance':
        case 'wheel_fortune':
        case 'estate':
            win = mainFrame ? mainFrame.frames.main : null;
            break;
        case 'inventory':
        case 'magic':
        case 'cube':
            win = mainFrame ? mainFrame.frames.main : null;
            break;
        case 'world':
            win = win._top().opened_windows['world_map'];
            name = 'game';
            break;
    }
    if (navigator.appName.indexOf("Microsoft") != -1) {
        return win ? win[name] : null;
    } else {
        return win ? win.document[name] : null;
    }
}*/

 
function swfTransfer(name, tar, data) {
    try {
        if (tar == "inventory") {
            var vars = data.split("@", 2);
            var obj = { 'user|view': JSON.parse(vars[1]) };
            swfObject('inventory', obj);
        } else if (tar == "area") {// bura değişecek
            var vars = data.split("@", 2);
            var obj = { 'common|area_conf': JSON.parse(vars[1]) };
            swfObject('area', obj);
        } else if (tar == "lvl") {// bura değişecek
			if(name == 'game'){				 
			 getSWF(tar).swfData(name, data);
			}else{
				var vars = data.split("@", 2);
				var obj = { 'user|view': JSON.parse(vars[1]) };
				swfObject('lvl', obj);
			}
          
        }else if (tar == "event") {
            var vars = data.split("@", 2);
            var obj = { 'common|event_conf': JSON.parse(vars[1]) };
            swfObject('area', obj);
        } else if (tar == "fight") {// bura değişecek
            var vars = data.split("@", 2);
            var obj = { 'fight|count': JSON.parse(vars[1]) };
            swfObject('area', obj);
        } else if (tar == "items_update") {
            var vars = data.split("@", 2);
            var obj = { 'user|effects': JSON.parse(vars[1]) };
            swfObject('items', obj);
		}else if (tar == "items_right") {
            var vars = data.split("@", 2);
            var obj = { 'user|mount': JSON.parse(vars[1]) };
            var user_view = JSON.parse(vars[1]);
			if (typeof(user_view['is_mount']) != "undefined" && typeof(user_view['mount_id']) != "undefined") {
				swfObject('items_right', {
					'user|mount': {
						'status': 100,
						'is_mount': user_view['is_mount'],
						'mount_id': user_view['mount_id']
					}
				});
			}else{
				swfObject('items_right', obj);
			}
        } else {
            getSWF(tar).swfData(name, data);
        }
        return true;
    } catch (e) { }
    return false;
}

function swfObject(tar, object) {
    var swf;
    try {
        swf = getSWF(tar);
        swf.swfObject(object);
        return true;
    } catch (e) {
        if (swf && swf.swfObject) {
            console_log("swfObject ERROR: tar=" + tar + ", error=" + e);
        }
    }
    return false;
}

function moveMedals(shift) {
	if (((shift < 0) && (position > 0)) || ((shift > 0) && (medals[position + MedalsOnPage]))) {
		position += shift;
		showMedals();
	}
}
function showMedals() {
	for(i=0;i<MedalsOnPage;i++) {
		document.getElementById('medal_' + i).innerHTML = medals[i + position] ? medals[i + position] : '&nbsp;';
	}
	if (position > 0) {
		document.getElementById('medal_l').src = "/images/medal_l_act.gif";
		document.getElementById('medal_l').style.cursor = "pointer";
	} else {
		document.getElementById('medal_l').src = "/images/medal_l.gif";
		document.getElementById('medal_l').style.cursor = "default";
	}
	if (medals[position + MedalsOnPage]) {
		document.getElementById('medal_r').src = "/images/medal_r_act.gif";
		document.getElementById('medal_r').style.cursor = "pointer";
	} else {
		document.getElementById('medal_r').src = "/images/medal_r.gif";
		document.getElementById('medal_r').style.cursor = "default";
	}
	return 1;
}

function ShowDiv(obj, evnt, show, param) {
	var div = gebi(obj.getAttribute('div_id'));
	if (!div) return;
	if (show == 2) {
		document.onmousemove=function(e) {artifactAlt(obj, e||event, 1)}
		div.style.display = 'block';
	}
	if (!show) {
		div.style.display = 'none';
		document.onmousemove=function(){}
		return;
	}
	var l = t = 0;

	try{
        l = (param.l ? param.l : 0);
        t = (param.t ? param.t : 0);
	}catch(e){}

	var ex = evnt.clientX + document.body.scrollLeft + l;
	var ey = evnt.clientY + document.body.scrollTop + t;

	var x = evnt.clientX + div.offsetWidth > document.body.clientWidth - 7 ? ex - div.offsetWidth - 10 : ex + 10;
	var y = evnt.clientY + div.offsetHeight > document.body.clientHeight - 7 ? ey - div.offsetHeight - 10 : ey + 10;

	if (x < 0 ) {
		x = ex - div.offsetWidth/2
	}
	if (x < 7 ) {
		x = 7
	}
	if (x > document.body.clientWidth - div.offsetWidth - 7) {
		x= document.body.clientWidth - div.offsetWidth - 7
	}

	div.style.left = x;
	div.style.top = y;
}

function refreshEvent (id) {document.location.href = 'user_event.php?mode=events&event_id='+id;}
function enterGreatFights () {document.location.href = 'area_fights.php?mode=great';}

function common_is_email_valid(email,all) {
	if (!email && !all) {
		return true;
	}
	var re = '';
	if (all) {
		re = /^([A-z0-9_\-]+\.)*[A-z0-9_\-]+@([A-z0-9][A-z0-9\-]*[A-z0-9]\.)+[A-z]{2,4}$/i;
	} else {
		re = /^([A-z0-9_\-]+\.)*[A-z0-9_\-]+(@)?([A-z0-9][A-z0-9\-]*[A-z0-9]\.)*(\.)?[A-z]{0,4}$/i;
	}
	if (!re.test(email)) {
		return false;
	}
	return true;
}

function petAlt(obj, evnt, show) {
	var div = gebi(obj.getAttribute('div_id'));
	if (!div) return;
	var act1 = obj.getAttribute('act1');
	var act2 = obj.getAttribute('act2');
	if (show == 2) {
		document.onmousemove=function(e) {petAlt(obj, e||event, 1)}
		div.style.display = 'block';
		if (act1 || act2) {
			_background(obj, (top.locale_path + "images/itemact-"+ act1) + (act2 +".gif"));
		}
	}
	if (!show) {
		if (act1 || act2) {
			_background(obj, 'images/d.gif');
		}
		div.style.display = 'none';
		document.onmousemove=function(){}
		return;
	}

	var ex = evnt.clientX + document.body.scrollLeft;
	var ey = evnt.clientY + document.body.scrollTop;

	if (act1 || act2) {
		obj.style.cursor = 'default'
		obj.onclick = (act1 != 0 ? function(){try{petAct(obj, act1)}catch(e){}} : function(){showPetInfo(obj.getAttribute('aid'), obj.getAttribute('art_id'))});
		_background(obj, (top.locale_path + "images/itemact-"+ act1) + (act2 +".gif"));
		var coord = getCoords(obj)
		var cont = gebi("item_list")
		var rel_x = (ex + cont.scrollLeft - coord.l)
		if (rel_x >= 40) {
			var rel_y = (ey + cont.scrollTop - coord.t)
			if (rel_y < 20) {
				obj.onclick = function(){showPetInfo(obj.getAttribute('aid'))}
				_background(obj, top.locale_path + 'images/itemact_info' + act2 +'.gif');
				try{obj.style.cursor = 'hand'} catch(e){}
				try{obj.style.cursor = 'pointer'} catch(e){}
			}
			if (act2 != 0 && rel_y >= 40) {
				obj.onclick = function(){try{petAct(obj, act2)}catch(e){}}
				_background(obj, top.locale_path + 'images/itemact_drop' + act2 +'.gif');
				try{obj.style.cursor = 'hand'} catch(e){}
				try{obj.style.cursor = 'pointer'} catch(e){}
			}
		}
	}
	var x = evnt.clientX + div.offsetWidth > document.body.clientWidth - 7 ? ex - div.offsetWidth - 10 : ex + 10;
	var y = evnt.clientY + div.offsetHeight > document.body.clientHeight - 7 ? ey - div.offsetHeight - 10 : ey + 10;

	if (x < 0 ) {
		x = ex - div.offsetWidth/2
	}
	if (x < 7 ) {
		x = 7
	}
	if (x > document.body.clientWidth - div.offsetWidth - 7) {
		x= document.body.clientWidth - div.offsetWidth - 7
	}

	div.style.left = x;
	div.style.top = y;
}

function fb_feed(lock_id,feed_id, data) {
	try{
		top.frames['main_frame'].wall(lock_id,feed_id, data)
	} catch(e) {}
}

var mountId;
function updateMount(mount_id) {
	_top().frames['main_frame'].mountID = mount_id;
}

function switchSkillPanel(current, list) {
	for (i = 0; i <= list.length; ++i) {
		var item = gebi(list[i]);
		var link = gebi(list[i] + '_lnk');
		var left = gebi(list[i] + '_left');
		var right = gebi(list[i] + '_right');
		var bg = gebi(list[i] + '_bg');
		if (item) item.style.display = 'none';
		if (link) link.className = 'tbl-shp_menu-link_inact';
		if (left) left.src = 'images/tbl-shp_menu-left-inact.gif';
		if (right) right.src = 'images/tbl-shp_menu-right-inact.gif';
		if (bg) bg.className = 'tbl-shp_menu-center-inact';
	}
	for (i = 0; i <= current.length; ++i) {
		var item = gebi(current[i]);
		var link = gebi(current[i] + '_lnk');
		var left = gebi(current[i] + '_left');
		var right = gebi(current[i] + '_right');
		var bg = gebi(current[i] + '_bg');
		if (item) item.style.display = '';
		if (link) link.className = 'tbl-shp_menu-link_act';
		if (left) left.src = 'images/tbl-shp_menu-left-act.gif';
		if (right) right.src = 'images/tbl-shp_menu-right-act.gif';
		if (bg) bg.className = 'tbl-shp_menu-center-act';
	}
}

function getKeyCode(e) {
	return (window.event) ? event.keyCode : e.keyCode;
}

function toggle_visibility(id) {
	var obj = gebi(id);
	if (obj) {
    	obj.style.display = obj.style.display=='' ? 'none' : '';
      	return obj.style.display=='none';
    }
	return false;
}

function explode(str, delimeter) {
	return str ? str.split(delimeter ? delimeter : '') : [];
}

function implode(array, delimeter) {
	var str = '';
	if(array) {
		var array_length = array.length ? array.length-1 : 0;
		for(var id in array)
			str = str + array[id] + (array_length-- ? delimeter : '');
	}
	return str;
}

// Применительно к объектам Array
// IE сцуко не поддерживает Array::indexOf
function indexOf(arr, value) {
	for(var id in arr)
		if(arr[id] == value)
			return id;
	return -1;
}

function getXmlHttp(){
	try {
		return new ActiveXObject("Msxml2.XMLHTTP");
	} catch (e) {
		try {
			return new ActiveXObject("Microsoft.XMLHTTP");
		} catch (ee) {
		}
	}
	if (typeof XMLHttpRequest!='undefined') {
		return new XMLHttpRequest();
	}
}

function getUrl(url, cb) {
	var xmlhttp = getXmlHttp();
	xmlhttp.open("GET", url);
	if (cb) {
		xmlhttp.onreadystatechange = function() {
			if (xmlhttp.readyState == 4) {
				cb(
				xmlhttp.status,
				xmlhttp.getAllResponseHeaders(),
				xmlhttp.responseText
				);
			}
		}
	}
	xmlhttp.send(null);
}

function doPost(actionUrl, params) {
	var newF = document.createElement("form");
	newF.action = actionUrl;
	newF.method = 'POST';
	var parms = params.split('&');
	for (var i=0; i<parms.length; i++) {
	  var pos = parms[i].indexOf('=');
	  if (pos > 0) {
		   var key = parms[i].substring(0,pos);
		   var val = parms[i].substring(pos+1);
		   var newH = document.createElement("input");
		   newH.name = key;
		   newH.type = 'hidden';
		   newH.value = val;
		   newF.appendChild(newH);
	  }
	}
	document.getElementsByTagName('body')[0].appendChild(newF);
	newF.submit();
}

//document.write('<script src="\/js\/console_log.js"><\/' + 'script>');

function updateAltEffects(effects) {
	top.frames['main_frame'].temp_effects = effects;
}


function moveToClanBattleLobby() {
	try {
		top.frames['main_frame'].frames['main'].location.href = 'clan_battle_conf.php?clan_battle_request_confirm=1';
	}
	catch (e) {}
}

function tutorialHook(step, end){
	top.frames['main_frame'].tutorialShow(step);
	if (end) {
		top.frames['main_frame'].tutorialEnd();
	}
}

function getClientWidth()
{
  return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
}

function getClientHeight()
{
  return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
}

function chat_add_artifact_macros(id, end_space) {
	return chat_add_macros('artifact_'+id, end_space);
}

function chat_add_macros(name, end_space) {
	if (end_space === undefined)
		end_space = true;
	var text = '[['+name+']]';
	if (end_space)
		text += ' ';
	var win = window;
	if (!win.top.frames['chat']) return false;
	win.top.frames['chat'].chatAddToMsg(text);
	return true;
}

function change_select_color(element) {
	var option = element.options[element.selectedIndex];

	if (option.style.color != "") {
		element.style.color = option.style.color;
	} else {
		element.style.color = "";
	}
}

function check_select_color() {
	var change_select = gebi('change_select_id');

	if (change_select) {
		change_select_color(change_select);
	}
}

function user_show_prof_bag(show) {
        var pr_bag_tab = top.frames['main_frame'].frames['main'].gebi("tab_pr_bag");
        if (show) pr_bag_tab.style.display = 'block';
        else pr_bag_tab.style.display = 'none';
}

var client_exchange_store;

function isInClient() {
	return document.cookie.indexOf("isInClient") > -1;
}

function clientExchangePut(text) {
	if (!isInClient()) return false
	if(!client_exchange_store) {
		client_exchange_store = new Array();
	}
	return client_exchange_store.push(text);
}

function clientExchangeGet() {
	if (!isInClient()) return false;
	if(client_exchange_store && client_exchange_store.length) {
		return client_exchange_store.shift();
	}
	return null;
}

function vardump (object, maxdepth) {
	maxdepth = maxdepth || 50;
	switch (typeof(object)) {
		case 'boolean':
			return object ? 'true' : 'false';
			break;

		case 'number':
			return object.toString();
			break;

		case 'string':
			return '"' + object + '"';
			break;

		case 'object':
			if (maxdepth <= 0) {
				return "...";
			}

			maxdepth--;

			var ret = [];
			var isArray = object instanceof Array;
			for (var key in object) {
				isArray ? ret.push(vardump(object[key])) : ret.push('"'+key+'"' + ': ' + vardump(object[key], maxdepth));
			}

			maxdepth++;

			return (isArray ? '[' : '{') + ret.join(', ') + (isArray ? ']' : '}');
			break;

		case 'undefined':
			return 'undefined';
			break;

		case 'function':
			return 'function';
			break;
	}

	return '?';
};

function clientReceive(data, swf_name) {
    _top().frames['chat'].clientCallBack($.parseJSON(data));
}

function isInInstance() {
    return _top().frames['chat'].checkInInstance();
}


function systemConfirm(ms,obj,func){
    if (ms){
        var confirm_ms = gebi('confirm_ms');
        confirm_ms.innerHTML = '<b>'+ms+'</b>';
    }

    var div = gebi('systemConfirm_div');
	div.style.top = document.body.scrollTop + (document.body.clientHeight - div.offsetHeight)/2
	div.style.left = document.body.scrollLeft + (document.body.clientWidth - div.offsetWidth)/2 - 150
	div.style.display = 'block';
    div.style.top = document.body.scrollTop + (document.body.clientHeight - div.offsetHeight*2)/2;
    div.style.left = document.body.scrollLeft + ((document.body.clientWidth - div.offsetWidth)/2);

    var close_div = top.gebi('systemConfirm_close_div');
    close_div.style.width = document.body.clientWidth;
    close_div.style.height = document.body.clientHeight;
    close_div.style.display = 'block';

	var  btnOk = gebi("btnOk");
	btnOk.onclick = function () {
        if(!func) {
            if (obj.href) {
                location.href = obj.href;
            }
            else if (obj.submit) {
                obj.submit();
            }
        }
        else {
            func();
        }
        div.style.display = 'none';
        close_div.style.display = 'none';
        return true;
    };

    $('.btn_sys_confirm_close').click(function(){
        div.style.display = 'none';
        close_div.style.display = 'none';
    });

    $('.popup_global_close_btn').click(function(){
        div.style.display = 'none';
        close_div.style.display = 'none';
    });

    gebi('systemConfirm_close_div').onclick = function () {
        div.style.display = 'none';
        close_div.style.display = 'none';
        return true;
    };
	
	var  btnCancel = gebi("btnCancel");
	btnCancel.onclick = function () {
		div.style.display = 'none';
	}

    return false;
}

function systemConfirm_zatochka(ms,title,obj,func){
    if (title){
        var systemConfirm_title = gebi('systemConfirm_title');
        systemConfirm_title.innerHTML = title;
    }
	if (ms){
		var confirm_ms = gebi('confirm_ms');
		confirm_ms.innerHTML = '<b>'+ms+'</b>';
	}

	var div = gebi('systemConfirm_div');
	div.style.top = document.body.scrollTop + (document.body.clientHeight - div.offsetHeight)/2
	div.style.left = document.body.scrollLeft + (document.body.clientWidth - div.offsetWidth)/2 - 150
	div.style.display = 'block';

	var  btnOk = gebi("btnOk");
	btnOk.onclick = function () {
        if(!func) {
            if (obj.href) {
                location.href = obj.href;
            }
            else if (obj.submit) {
                obj.submit();
            }
        }
        else {
            func();
        }
        div.style.display = 'none';
        close_div.style.display = 'none';
        return true;
    };
	
	$('.btn_sys_confirm_close').click(function(){
        div.style.display = 'none';
        close_div.style.display = 'none';
    });

    $('.popup_global_close_btn').click(function(){
        div.style.display = 'none';
        close_div.style.display = 'none';
    });
	
	gebi('systemConfirm_close_div').onclick = function () {
        div.style.display = 'none';
        close_div.style.display = 'none';
        return true;
    };

	var  btnCancel = gebi("btnCancel");
	btnCancel.onclick = function () {
		div.style.display = 'none';
	}

return false;
}

function fixEvent(e, _this) {
    e = e || window.event;

    var docElem = document.documentElement,
        body = document.body;

    if (!e.currentTarget) e.currentTarget = _this;
    if (!e.target) e.target = e.srcElement;

    if (!e.relatedTarget) {
        if (e.type === 'mouseover') e.relatedTarget = e.fromElement;
        if (e.type === 'mouseout') e.relatedTarget = e.toElement;
    }

    if (!e.pageX && e.clientX) {
        e.pageX = e.clientX + (docElem.scrollLeft || body && body.scrollLeft || 0);
        e.pageX -= docElem.clientLeft || 0;

        e.pageY = e.clientY + (docElem.scrollTop || body && body.scrollTop || 0);
        e.pageY -= docElem.clientTop || 0;
    }

    if (!e.which && e.button) {
        e.which = e.button & 1 ? 1 : (e.button & 2 ? 3 : (e.button & 4 ? 2 : 0));
    }

    return e;
}

function hasClass(el, c) {
	return el.className.match(new RegExp('(^|\\s+)'+c+'($|\\s+)'));
}

function addClass(el, c) {
	if (!hasClass(el, c)) {
		el.className += ' '+c;
	}
}

function removeClass(el, c) {
	if (hasClass(el, c)) {
		el.className = el.className.replace(new RegExp('(^|\\s+)'+c+'($|\s+)', 'gi'), ' ').replace(/\s+/g, ' ').replace(/(^|$)/g, '');
	}
}
function backpack_diff(data) {
    var art_pfx = 'AA_';
    var frame_def = 3;
    var is_diff = false;

    //global alts update (left and right items)
    if (data.changed) {
        $.each(data.changed, function (index, item) {
            if (top.frames['main_frame'].art_alt[art_pfx + item.id]) {
                top.frames['main_frame'].art_alt[art_pfx + item.id] = item;
            }
        });
    }

    var $backpack = top.$('#main_frame').contents().find('#backpack');

    if (!$backpack || $backpack.attr('data-loaded') != 1) {
        return;
    }

    var backpack_groups = top.backpack_groups;
    var backpack_group_sub = top.backpack_group_sub;

    var $sections = {};
    $.each($backpack.contents().find('iframe.backpack-section'), function () {
        if ($(this).attr('data-loaded')) {
            $sections[$(this).attr('data-group').toString()] = $(this);
        }
    });

    // info
    if ((typeof data.info == 'object') && (typeof data.info.amount != 'undefined') && (typeof data.info.amount_max != 'undefined')) {
        $.each($sections, function (index, item) {
            item.contents().find('#artifact_amount').text(data.info.amount);
            item.contents().find('#artifact_amount_max').text(data.info.amount_max);
        });
    }

    // money
    if ((typeof data.money == 'object')) {
        $.each($sections, function (index, item) {
            for (var i in data.money) item.contents().find('#money-type-' + i).html(data.money[i].replace(/&quot;/g, '"'));
        });
    }

    // info pr bag
    if ((typeof data.info_pr_bag == 'object') && (typeof data.info_pr_bag.amount != 'undefined') && (typeof data.info_pr_bag.amount_max != 'undefined')) {
        $.each($sections, function () {
            $(this).contents().find('#pr_bag_section').toggle(!!(data.info_pr_bag.amount_max > 0));
            $(this).contents().find('#pr_artifact_amount').html(data.info_pr_bag.amount);
            $(this).contents().find('#pr_artifact_amount_max').html(data.info_pr_bag.amount_max);
        });
    }

    // deleted
    if (data.deleted && data.deleted.length > 0) {
        $.each(data.deleted, function (index, id) {
            is_diff = true;
            $.each($sections, function () {
                var $c = $(this).contents();
                var $el = $c.find('#' + art_pfx + id);
                if ($el.length > 0) {
                    $el.remove();
                    $c.find('.bag_section').each(function () {
                        if ($(this).attr('id') == 'bag_section' || $(this).attr('id') == 'pr_bag_section') {
                            $(this).find('.backpack_list').find('.empty').toggle(!($(this).find('.item').length > 0));
                        } else {
                            $(this).toggle($(this).find('.item').length > 0);
                        }
                    });
                    return false;
                }
            });
        });
    }

    // changed
    if (data.changed) {
        if ($.browser.msie) {
            if (typeof $backpack[0].contentWindow.__reload_if_ie__ != 'undefined') {
                $backpack[0].contentWindow.__reload_if_ie__ = true;
            }
        }
        var add = {};
        var sub_def = false
        $.each(data.changed, function (index, item) {
            is_diff = true;
            $.each(backpack_group_sub, function (key, value) {
                $.each(value.types, function (_, value) {
                    if (value == item.type_id) {
                        sub_def = key;
                    }
                });
            });
            var iframe_num = sub_def ? sub_def : (backpack_groups[item.kind_id] ? backpack_groups[item.kind_id] : frame_def);
            var $iframe = $sections[iframe_num];
            if (!$iframe) {
                return true;
            }
            if (item.enchant_icon) {
                item.enchant_icon = item.enchant_icon.replace(/&quot;/g, '"');
            }
            $iframe[0].contentWindow.art_alt[art_pfx + item.id] = item;
            var $el = $iframe.contents().find('#' + art_pfx + item.id);
            var $el_new = $('<li></li>');
            var sub = null, $ul1;
            if (backpack_group_sub[iframe_num]) {
                if (backpack_group_sub[iframe_num].kinds && typeof backpack_group_sub[iframe_num].kinds[item.kind_id] != 'undefined') {
                    sub = backpack_group_sub[iframe_num].kinds[item.kind_id];
                } else if (backpack_group_sub[iframe_num].types && typeof backpack_group_sub[iframe_num].types[item.type_id] != 'undefined') {
                    sub = backpack_group_sub[iframe_num].types[item.type_id];
                } else if (backpack_group_sub[iframe_num].quality && typeof backpack_group_sub[iframe_num].quality[item.quality] != 'undefined') {
                    sub = backpack_group_sub[iframe_num].quality[item.quality];
                } else if (backpack_group_sub[iframe_num].profession && typeof (item.profession) != 'undefined' && parseInt(item.profession, 10) != 0) {
                    sub = backpack_group_sub[iframe_num].profession;
                }
                $ul1 = $iframe.contents().find('#item_list_sub_' + sub);
                if ($ul1.length == 0) {
                    sub = null;
                }
            }

            if (sub === null) {
                $ul1 = $iframe.contents().find('#' + (parseInt(item.storage_type) == 0 ? 'item_list_' : 'pr_item_list'));
                var $ul2 = $iframe.contents().find('#' + (parseInt(item.storage_type) == 0 ? 'pr_item_list' : 'item_list_'));
            }
            if ($el.length > 0) { // change
                $el_new
                    .attr('id', art_pfx + item.id)
                    .attr('class', 'item')
                    .attr('aid', 'art_' + item.id)
                    .attr('ord', item.ord)
                    .attr('data-id', item.id)
                    .attr('data-title', item.title)
                    .attr('data-kind', item.kind_id)
                    .attr('data-ttl', item.time_expire)
                    .attr('data-quality', item.quality)
                    .html(html_artifact_slot(item));
                $el.replaceWith($el_new);
                add[iframe_num]++;
            } else { // add
                if (!add[iframe_num]) {
                    add[iframe_num] = 0;
                }
                add[iframe_num]++;
                $el_new
                    .attr('id', art_pfx + item.id)
                    .attr('class', 'item')
                    .attr('aid', 'art_' + item.id)
                    .attr('ord', item.ord)
                    .attr('data-id', item.id)
                    .attr('data-title', item.title)
                    .attr('data-kind', item.kind_id)
                    .attr('data-ttl', item.time_expire)
                    .attr('data-quality', item.quality)
                    .html(html_artifact_slot(item));

                var $children = $ul1.children('.item');
                var $point = false;
                item.ord = parseInt(item.ord);
                $.each($children, function () {
                    if (parseInt($(this).attr('ord')) > item.ord) {
                        $point = $(this);
                        return false;
                    }
                });
                if ($point) {
                    $el_new.insertBefore($point);
                } else {
                    $el_new.appendTo($ul1);
                }
            }

            $ul1.find('.empty').toggle(!($ul1.find('.item').length));
            if (typeof sub == 'undefined') {
                $ul2.find('.empty').toggle(!($ul2.find('.item').length));
            } else {
                $iframe.contents().find('#bag_section_sub_' + sub).toggle($ul1.find('.item').length > 0)
            }
        });
        for (var i in add) {
            if ($sections[i][0].contentWindow.itemsFilterSync && (typeof $sections[i][0].contentWindow.itemsFilterSync == 'function')) {
                $sections[i][0].contentWindow.itemsFilterSync();
            }
        }
    }

    if (data.cnt_changed) {
        $.each(data.cnt_changed, function (index, item) {
            is_diff = true;
            var iframe_num = (backpack_groups[item.kind_id] ? backpack_groups[item.kind_id] : frame_def);
            var $iframe = $sections[iframe_num];
            if (!$iframe) return true;
            $iframe[0].contentWindow.art_alt[art_pfx + item.id].cnt = item.cnt;
            var $el = $iframe.contents().find('#' + art_pfx + item.id);
            if ($el.length <= 0) return true;
            $el.contents().find('.artifact-slot-qnt').html(item.cnt);
        });
    }

    // __reset_on_diff__
    if (is_diff) {
        var $main = _top().$('#main_frame').contents().find('#main');
        if ($main[0].contentWindow.__reset_on_diff__) {
            tUnsetFrame('main');
            $main[0].contentWindow.__reset_on_diff__ = false;
        }
    }
}

										   
		 
					 
					 
							 
							   
										 
										   
																																 
																																	 
													 
												  
																		 
									   
							 
																																											  
									
									  
					  
					   
				  
											   
									   
				
											   
											   
		 
									
									
						   
				 
	 
				
 


function html_artifact_slot(data) {
	var pfx = 'AA_';
	var html = '';

	data._act1 = data._act1 ? data._act1 : 0;
	data._act2 = data._act2 ? data._act2 : 0;
	data._act3 = data._act3 ? data._act3 : 0;

	html += '<table width="60" height="60" cellpadding="0" cellspacing="0" border="0" style="float: left; margin: 1px" background="'+data.image+'">';
	html += '<tbody><tr>';
	html += '<td act1="'+data._act1+'" act2="'+data._act2+'" act3="'+data._act3+'" puton_confirm="'+data._puton+'" aid="'+data.id+'" cnt="'+(data.cnt && data.cnt > 1 ? data.cnt : 0)+'" div_id="'+pfx+data.id+'" onmouseover="artifactAlt(this,event,2)" onmouseout="artifactAlt(this,event,0)" valign="bottom" style="cursor: pointer; background-image: url(/images/d.gif); ">';
	if (data.cnt && data.cnt > 1) {
		html += '<div class="bpdig">'+data.cnt+'</div>';
	} else {
		html += '&nbsp;';
	}
	html += '</td>';
	html += '</tr></tbody>';
	html += '</table>';

	return html;
}

function tProcessMenu(par, opt) {
	_top().frames['main_frame'].processMenu(par, opt);
}

function tSetFrameData(frame, value) {
	if (!frame)
		return;
	try {
		top.frames['main_frame'].gebi(frame).setAttribute('data-loaded', value);
	} catch (e) {}
}

function tUnsetFrame(frame, full) {
    if (!frame)
        return;
    try {
        if (full) {
            top.frames['main_frame'].frames[frame].location.href = 'blank.html';
        }
        var obj = top.frames['main_frame'].gebi(frame);
        obj.setAttribute('data-loaded', 0);
        obj.setAttribute('data-par', '');
    } catch (e) {}
}

function tLockFrame() {
    top.iframe_locked = true;
}

function tUnlockFrame() {
    top.iframe_locked = false;
}

function return_link(url) {
    try {
        if (top.frames['main_frame'].frames['main'].__location__ || typeof url == 'undefined') {
            top.tProcessMenu('b06');
        } else {
            top.frames['main_frame'].frames[top.iframe].location.href = url;
        }
    } catch(e) {}
}
document.addEventListener("keyup",itemsRight);
document.addEventListener("keydown",keyDownHandler);

if (_top().js_debug) {
	window.onerror = window_onerror;
}

function itemsRight(e) {
    var input = false;
    switch (e.target.tagName) {
        case 'INPUT':
        case 'TEXTAREA':
            input = true;
            break;
    }
    if (!input) {
        if (e.shiftKey || e.ctrlKey || e.altKey) {
            var swf = getSWF('items_right');
            if (swf) swf.externalKey({shiftKey : e.shiftKey, ctrlKey : e.ctrlKey, altKey : e.altKey, keyCode : e.keyCode, code : e.code});
        }
    }
}

function keyDownHandler(e) {
    var input = false;
    switch (e.target.tagName) {
        case 'INPUT':
        case 'TEXTAREA':
            input = true;
            break;
    }
    if (!input) {
        var swf = getSWF('game');
        if (swf) swf.externalKey(e);
    }
}

function confirmCenterDivClose() {
    if (_top().popup != null && _top().popup != undefined) {
        _top().popup.close();
    }

    if (typeof (showNextPopupWindow) == "function") {
        showNextPopupWindow();
    }
}

function canvasIsSupported() {
    var result = /Chrome\/(\d+)/.exec(navigator.userAgent);
    if (result && result[1] && parseInt(result[1]) < 30) {
        return false;
    }
    return true;
}

function jailExit() {
    if (window.jailExitStarted) return;
    window.jailExitStarted = true;
    entry_point_request('common', 'jailExit', {}, function(response){
        if (response['status'] != DATA_OK && response['error']) {
            showError(response['error'],response['error_crc']);
            window.jailExitStarted = false;
        } else {
            location.reload();
        }
    });
}

function openPremiumStore(category) {
    category = category || '';

    var opt = {};
    switch (category) {
        case "gifts":
            opt.premium_add_url = "category_id=19";
            break;
    }

    tProcessMenu('premium_store', opt);
}
function openPremium() {
    tProcessMenu("b36",  {url: '/area_banks.php?mode=premium'});
}

function openLocator() {
    window.open('/friend_list.php?mode=located', "", "width=920,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}

function confirm_front(area_id) {
    entry_point_request('front', 'fight_join', {area_id: area_id}, function(response) { if (response['status'] != 100) showError(response['error'],response['error_crc']); })
}

function confirm_front(area_id) {
    entry_point_request('front', 'fight_join', {area_id: area_id}, function (response) {
        if (response['status'] != 100) showError(response['error'], response['error_crc']);
    })
}

function front_conf(front_id) {
    var params = {};
    if (front_id) params = {'front_id': front_id};
    entry_point_request('front', 'conf', params, function (response) {
        if (response['status'] == DATA_OK) swfObject('area', response);
    });
}

function front_fight_start() {
    entry_point_request('front', 'fight_start', {}, function (response) {
        if (response['status'] != DATA_OK && response['error']) {
            showError(response['error'], response['error_crc']);
        } else {
            swfObject('area', response);
        }
    });
}

function front_locations() {
    entry_point_request('front', 'locations', {}, function (response) {
        if (response['status'] == DATA_OK) {
            swfObject('area', response);
            swfObject('world', response);
        }
    });
}

window.last_top = false;

function countSymbols(e, field, infoBlock, infoLabel) {
    e = e || window.event;
    var key = e.keyCode || e.which;
    var modifier = 0;

    if (document.selection || navigator.userAgent.indexOf("Opera") > -1) { // IE | Opera
        modifier = 1;

        if (key == 8) { // Backspace
            modifier = -1;
        }
    }

    if (key > 36 && key < 41) { //
        return;
    }

    if (e.ctrlKey || e.altKey || e.metaKey) { //
        return;
    }

    field = (typeof field === 'string') ? document.getElementById(field) : field;
    infoBlock = (typeof infoBlock === 'string') ? document.getElementById(infoBlock) : infoBlock;
    infoLabel = infoLabel.split('|');

    if (infoBlock.style.display == 'none') {
        infoBlock.style.display = 'block';
    }

    if (field.getAttribute('maxlength')) {
        //
        var caretPos = getCaretPosition(field);

		/*
		 *
		 *
		 */
        setTimeout(function() {
            field.max = parseInt(field.getAttribute('maxlength'));
            field.value = field.value.substr(0, field.max);

            if (document.selection || navigator.userAgent.indexOf("Opera") > -1) { // IE | Opera
                setCaretPosition(field, caretPos + modifier);
            }

            infoBlock.innerHTML = infoLabel[0] + ' ' + (field.max - field.value.length) + ' ' + infoLabel[1] + ' ' + field.max;
        }, 0);
    }
}

function getCaretPosition(field) {
    var caretPos = 0;

    if (document.selection) { // IE
        var currentRange = document.selection.createRange();
        var workRange = currentRange.duplicate();

        field.select();

        var allRange = document.selection.createRange();
        var len = 0;

        while (workRange.compareEndPoints('StartToStart', allRange) > 0) {
            workRange.moveStart('character', -1);

            len++;
        }

        currentRange.select();

        caretPos = len;
    } else if (field.selectionStart || field.selectionStart == '0') { // W3C
        caretPos = field.selectionStart;
    }

    return caretPos;
}

function gui_styled(input, textarea) {
    input = input || false;
    textarea = textarea || false;

    if (input) {
        var w = '';

        $('.' + input).each(function() {
            if ($(this).attr('width')) {
                w = $(this).attr('width');
            } else {
                w = '';
            }

            $(this).wrap('<div class="ff__input-wrap" style="width: ' + w + '"><div class="ff__input-wrap-inner"><div class="ff__input-wrap-input"></div></div></div>');
        })
    }

    if (textarea) {
        var w = '';

        $('.' + textarea).each(function() {
            if ($(this).attr('width')) {
                w = $(this).attr('width');
            } else {
                w = '';
            }

            $(this).wrap('<div class="textarea-styled" style="width: ' + w + '"></div>');
        })
    }

    $('.textarea-styled').append('<div class="textarea-styled__right-top"></div><div class="textarea-styled__right-bottom"></div><div class="textarea-styled__left-bottom"></div><div class="textarea-styled__left-top"></div><div class="textarea-styled__top"></div><div class="textarea-styled__right"></div><div class="textarea-styled__bottom"></div><div class="textarea-styled__left"></div>');

    $('.ff__input-wrap')
        .on('mouseenter', function() {
            $(this).addClass('hover');
        })
        .on('mouseleave', function() {
            $(this).removeClass('hover');
        })
        .on('click', function() {
            $(this).children('input').focus();
        });

    $('.ff__input-wrap input')
        .on('focus', function() {
            $(this).parents('.ff__input-wrap').addClass('focus');
        })
        .on('blur', function() {
            $(this).parents('.ff__input-wrap').removeClass('focus');
        });

    $('.textarea-styled')
        .on('mouseenter', function() {
            $(this).addClass('hover');
        })
        .on('mouseleave', function() {
            $(this).removeClass('hover');
        });

    $('.textarea-styled textarea')
        .on('focus', function() {
            $(this).parents('.textarea-styled').addClass('focus');
        })
        .on('blur', function() {
            $(this).parents('.textarea-styled').removeClass('focus');
        });
}

window.last_top = false;
function _top() {
    if (window.last_top) return window.last_top;
    var p = window;
    while (true) {
        try {
            if (p.location.href.match(/main\.php/) || p.parent === p) { break; }
        } catch (e) {
            window.last_top = p;
            return p;
        }
        p = p.parent.window;
    }
    window.last_top = p;
    return p;
}
try {
    window.top = top = _top();
} catch (e) { }



window.close_ = window.close;
window.close = function (e, id) {
    var win = _top().window;
    if (win.js_popup && id) {
        win.destroyPopup(e, id);
    } else {
        win.close_();
    }
};

if (_top().js_popup) {
    window.open_ = window.open;
    window.open = function (url, name, params) {
        if (params) {
            var w = params.match(/width=(\d+),?/)[1];
            var h = params.match(/height=(\d+),?/)[1];
        }
        _top().createPopup({
            //			title: name,
            iframe: {
                src: url,
                height: h || 400,
                width: w
            }
        });
    };
}

function error_close() {
    try {
        var win = _top().window;
        var obj = _top().gebi('error');
        var div = _top().gebi('error_div');
        if (!obj || !div) return false;

        obj.style.display = 'none';
        div.style.display = 'none';
        obj.src='';
        obj.width = 1;
        obj.height = 1;
        obj.style.left = 0;
        obj.style.top = 0;

        if (div.errorCloseCallback) {
            div.errorCloseCallback();
        }
    } catch(e) { return false; }

    return true;
}


function karsilastir(nick) {
    try {
        top.frames['main_frame'].frames['main'].location.href = 'user.php?mode=achievement_compare&eslestir[button]&filter[compare_nick]=' + nick;
    }
    catch (e) { }
}
function showMsg3(url, title, w, h) {
    w = w || 520;
    h = h || 320;

    var div = gebi('popup_styled'),
        iframe = gebi('popup_styled_iframe'),
        hider = gebi('frame_content_hider'),
        title_div = gebi('popup_styled_title');

    iframe.src = url;
    iframe.style.height = h;
    iframe.style.width = w;

    title_div.innerHTML = title;

    div.style.top = document.body.scrollTop + (document.body.clientHeight - h) / 2;
    div.style.left = document.body.scrollLeft + (document.body.clientWidth - w) / 2;

    hider.style.display = 'block';

    iframe.onload = function () {
        div.style.display = 'block';
    }
}
function closeMsg() {
    gebi('frame_content_hider').style.display = 'none';
    gebi('popup_styled').style.display = 'none';
}

function show_skor(url, title, w, h) {
    try {
        w=w||480;
        h=h||300;
        var win = top.window;
        var doc = top.document;
        var width = doc.body.clientWidth;
        var height = doc.body.clientHeight;
        var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
        var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);
        var obj = top.gebi('festival');
        var div = top.gebi('festival_div');
        if (!obj || !div) return false;
        obj.src=url;

        div.style.width = div_width;
        div.style.height = div_height;

        obj.width = w;
        obj.height = h;
        div.style.display = 'block';
        obj.style.display = 'block';
        win.scrollTo(0,0);
//		obj = top.gebi('artifact_alt');
//		if (obj) obj.innerHTML='';
    } catch(e) {}
    return true;
}

function festival_close() {
    try {
        var win = top.window;
        var obj = top.gebi('festival');
        var div = top.gebi('festival_div');
        if (!obj || !div) return false;
        obj.style.display = 'none';
        div.style.display = 'none';
        obj.src='';
        obj.width = 1;
        obj.height = 1;
        obj.style.left = 0;
        obj.style.top = 0;

        if (div.errorCloseCallback) {
            div.errorCloseCallback();
        }

    } catch(e) {}

    return true;
}


function showMsg3(url, title, w, h) {
    closePopupDialog();
    try {
        w=w||480;
        h=h||300;
        var win = top.window;
        var doc = top.document;
        var width = doc.body.clientWidth;
        var height = doc.body.clientHeight;
        var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
        var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);
        var obj = top.gebi('error');
        var div = top.gebi('error_div');
        if (!obj || !div) return false;
        obj.src=url;
        div.style.width = div_width;
        div.style.height = div_height;
        obj.width = w;
        obj.height = h;
        obj.style.left = ((width-w)/2);
        obj.style.top = ((height-h)/2);
        div.style.display = 'block';
        obj.style.display = 'block';
        win.scrollTo(0,0);
    } catch(e) {}
    return true;
}
function popupDialog(html, title, width, height, modal) {
    var options = {};
    if (width) options.width = width;
    if (height) options.height = height;
    if (modal) options.modal = modal;
    var cont = $('#popup_global .popup_global_container', _top().document);
    options.closeContent = '';
    if (_top().popupDialogObj) _top().popupDialogObj.close();
    var name = _top().$.Popup;
    _top().popupDialogObj = new name(options);
    var popup = _top().popupDialogObj;
    html = $(html);
    cont.find('.popup_global_content').html(html.html());
    cont.find('.popup_global_title').html(title);
    var el = cont.get(0);
    if (!el || !el.outerHTML) return;
    popup.open(el.outerHTML, 'html');
    _top().$('input').chStyler();
}

function popupDialogClose() {
    if (_top().popupDialogObj) {
        _top().popupDialogObj.close();
    }

    if (typeof(showNextPopupWindow) == "function") {
        showNextPopupWindow();
    }
}

function entry_point_request_bag(object, action, params, funcx, error_callback) {
    params = params || {};
    params = $.extend({
        json_mode_on: 1,
        object: object,
        action: action
    }, params);

    var send_data = {
        url: '/entry_point.php?object='+object+'&action='+action+'&json_mode_on=1',
        dataType: 'json', cache: false, type: "POST"
    };
    if (params.ajaxParam) {
        send_data = $.extend(send_data, params.ajaxParam);
        delete params.ajaxParam;
    }

    send_data.data = params;

    return $.ajax(send_data)
        .done(function(data) {
            if(funcx){
                location.href = funcx;
            }
        })
        .fail(function(jqXHR, textStatus, errorThrown) {
            if (error_callback instanceof Function) {
                error_callback.call(this, textStatus);
            }
        });
}

function popup2(url,title,w,h){
    w=w||480;
    h=h||300;
    var win = _top().window;
    var doc = _top().document;
    var width = doc.body.clientWidth;
    var height = doc.body.clientHeight;
    var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
    var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);

    var content = "<iframe style=\"z-index: 100000;\" src="+url+" id=\"provocation_frame\" width=\"100%\" height=\"100%\" frameborder=\"0\" name=\"provocation_frame\" scrolling=\"yes\"></iframe>";
    var popup_global = _top().$('#popup_global_s');
    var popup_global_title = _top().$('#popup_global_title_s');
    var popup_global_content = _top().$('#popup_global_content_s');
    popup_global_title.html(title);
    popup_global_content.html('');
    popup_global_content.html(content);
    popup_global.show();
    popup_global.css('top', ((height-h)/2));
    popup_global.css('left', ((width-w)/2));
    popup_global.css('width',w);
    popup_global.css('height',h);
    popup_global.css('position', 'absolute');
    win.scrollTo(0,0);
}

function delete_user_drop(id){
    $.ajax('/pub/api.php', {
        dataType : 'json',
        data : {
            'mode' : 'delete_user_drop',
            'ref' : id,
        },
        complete : function(data, statusCode){
            if(data.responseText){
                var out = JSON.parse(data.responseText);
                if(out['status'] == 0){
					$('#drop_info_' + id).remove();
                }
            }
        },
        async: false,
    });
}

function showShadowInfo(nick, fight_user_id, bot_id, fight_id, server_id) {
    var url = "/companion_info.php";
    if (typeof nick != 'undefined' && nick) {
        url += "?nick=" + nick;
    } else if (typeof fight_user_id != 'undefined' && fight_user_id) {
        url += "?fight_user_id=" + fight_user_id;
        if ((typeof fight_id != 'undefined' && fight_id) && (typeof server_id != 'undefined' && server_id)) {
            url += "&fight_id=" + fight_id + "&server_id=" + server_id;
        }
    } else if (typeof bot_id != 'undefined' && bot_id) {
        url += "?bot_id=" + bot_id;
    }
    window.open(url, "", "width=915,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no");
}


function copyToClipboard(str) {
	const el = document.createElement('textarea');
	el.value = str;
	el.setAttribute('readonly', '');
	el.style.position = 'absolute';
	el.style.left = '-9999px';
	document.body.appendChild(el);
	el.select();
	document.execCommand('copy');
	document.body.removeChild(el);
}

function resurrect(modeId) {
    var addurl = '';
    if (modeId) {
        addurl = '&in[mode_id]=' + modeId;
    }
    tProcessMenu('b06', {url: 'action_run.php?code=RESURRECT&url_success=area.php&url_error=area.php'+addurl});
}

function big_windows_show(url) {
    try {
        w = (top.document.body.clientWidth - 40);
        h = top.frames['main_frame'].document.body.clientHeight;
        var win = top.window;
        var doc = top.document;
        var width = doc.body.clientWidth;
        var height = doc.body.clientHeight;
        var div_width = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollWidth : doc.documentElement.scrollWidth,width);
        var div_height = Math.max(doc.compatMode != 'CSS1Compat' ? doc.body.scrollHeight : doc.documentElement.scrollHeight,height);
        var obj = top.gebi('error');
        var div = top.gebi('error_div');
        if (!obj || !div) return false;
        obj.src=url;

        obj.width = w;
        obj.height = h;
        obj.style.left = ((width-w)/2);
        obj.style.top = 0;
        div.style.display = 'none';
        obj.style.display = 'block';
        win.scrollTo(0,0);
    } catch(e) {}
    return true;
}

function addSavedAction(data) {
    _top().dwar.saved_actions[data['id']] = data;
}

function removeSavedAction(action_id) {
    delete _top().dwar.saved_actions[action_id];
}

function toggleSavedAction(actionId) {
    entry_point_request('favorite', 'switch_action', {action_id: actionId}, function (response) {
        if (response.status == 100) {
            gebi('action_save_check').checked = (response.added == 1);
            gebi('action_err').innerHTML = '';
        } else {
            if (response.error) gebi('action_err').innerHTML = response.error + '<br/>';
            gebi('action_save_check').checked = !gebi('action_save_check').checked;
        }
    });
}

function stopAction() {
    $.ajax({
        url: '/entry_point.php',
        data: {
            json_mode_on: true,
            object: 'common',
            action: 'stopaction'
        },
        dataType: 'JSON',
        success: function (objects) {
            _top().chat.chatReceiveObject(objects);
        }
    });
}

function topDwar(data) {
    if (_top().dwar) {
        for (var i in data) _top().dwar[i] = data[i];
    } else if (_top().dialogArguments && _top().dialogArguments.win && _top().dialogArguments.win.dwar) {
        for (var i in data) _top().dialogArguments.win.dwar[i] = data[i];
    }
}

function html_period_str_lite(time){
    ostd = time % 86400;
    days = (time - ostd) / 86400;
    osth = ostd % 3600;
    hours = (ostd - osth) / 3600;
    ostm = osth % 60;
    minutes = (osth - ostm) / 60;
    seconds = ostm % 60;
    seconds = ( seconds < 10 ? '0'+seconds : seconds );
    return ( days ? days + ' д ' : '') + ( hours ? hours + '&nbsp;г&nbsp;' : '') + ( minutes ? minutes + '&nbsp;хв&nbsp;' : '') + ( seconds ? seconds + '&nbsp;сек ' : '');
}

function html_period_str(v, with_seconds, with_minutes, glue, extended) {
    if(!with_seconds || with_seconds == undefined) with_seconds = false;
    if(!with_minutes || with_minutes == undefined) with_minutes = true;
    if(!glue || glue == undefined) glue = ' ';
    if(!extended || extended == undefined) extended = true;
    v = Math.max(v,0);
    ss = parseInt(v) % 60;
    mm = parseInt(v/60) % 60;
    hh = parseInt(v/3600) % 24;
    dd = parseInt(v/86400);
    t = Array();
    if(extended) {
        if (dd) t.push(dd.toString().concat(' д'));
        if (hh || (dd && mm)) t.push(hh.toString().concat(' г '));
        if (with_minutes && mm) t.push(mm.toString().concat(' хв '));
        if ((with_seconds && ss) || (v < 60)) t.push(ss.toString().concat(' сек '));
    }else{
        t.push(sprintf("%02d", hh));
        if (with_minutes) t.push(sprintf("%02d", mm));
        if (with_seconds) t.push(sprintf("%02d", ss));
    }
    return t.join(glue); //Implode With Glue
}

/**
* Найти ближайшего родителя с заданным ID или className
*
* @param {object} - DOM-нода или её ID, начиная с которой будем подниматься вверх по дереву
* @param {string} - id или class у родителя, который ищем
*
* @return {boolean|object} - объект в случае успешного поиска и false в случае провала
*/
function findClosest(elem, parent) {
	elem = typeof elem === 'string' ? document.getElementById(elem) : elem;
	var obj = elem.parentNode;

	if (!parent || typeof parent !== 'string') {
		return false;
	}

	if (parent.indexOf('#') !== -1) {
		parent = parent.split('#')[1];

		while (obj && obj.id !== parent) {
			obj = obj.parentNode;
		}
	} else if (parent.indexOf('.') !== -1) {
		parent = parent.split('.')[1];

		while (obj && obj.className.indexOf(parent) === -1) {
			obj = obj.parentNode;
		}
	} else {
		return false;
	}

	return obj;
}

/**
* Функция обратного отсчета оставшегося времени
*
* @param {object|string} - DOM-нода или её ID
* @param {number} - время до истечения срока
* @param {array} - массив с подписями к часам, минутам, секундам и т.п.
*                  ['д', 'ч', 'мин', 'сек']
* @param {function} - коллбэк
*
* @return {void}
*/
function countdownTimer(elem, dtime, textArray, callback) {
	elem = typeof elem === 'string' ? document.getElementById(elem) : elem;
	elem.timer = setInterval(function() {
		var ctime = (new Date).getTime(),
			time = dtime - (ctime / 1000);

		elem.innerHTML = htmlPeriodStr(time, textArray);

		if (time <= 0) {
			clearInterval(elem.timer);

			if (callback) {
				callback.call(elem);
			}
		}
	}, 1000);
}

function guideShow(gid, link) {
    if(gid === undefined) gid = 0;
    _xfr = _top();
    let lnk = 'user_guide.php';
    if(lnk !== undefined && typeof link == 'string') lnk = link;
    _xfr.need_frames['user_guide'].src = lnk + '?guide_id='+gid;
    _xfr.sFrMe('user_guide');
}

function time_current() {
    return Math.round(((new Date()).getTime()) / 1000);
}

function html_page_list(action, page, page_count, param={}) {
    size = param['size'] !== undefined ? param['size']: 10;
    format = param['format'] !== undefined ? param['format']: '&nbsp;%d&nbsp;';

    if (page_count < 2) return '';
    html = param['prefix'] ? param['prefix']: '<nobr>'+('Сторінки: ')+'</nobr>';
    page_start = Math.floor(page/size)*size;
    page_end = Math.min(page_start+size,page_count);
    if (page_start > 0) {
        html += '<a href="#" onclick="'+action+'(0); return false;">'+sprintf(format,1)+'</a>...';
        html += '<a href="#" onclick="'+action+'('+(page_start-1)+'); return false;">&nbsp;&laquo;&nbsp;</a>';
    }
    for (i=page_start; i<page_end; i++) {
        t = sprintf(format,i+1);
        if (page === i) t = '<b>'+t+'</b>';
        html += '<a href="#" onclick="'+action+'('+i+'); return false;">'+t+'</a>';
    }
    if (page_end < page_count) {
        html += '<a href="#" onclick="'+action+'('+(page_end)+'); return false;">&nbsp;&raquo;&nbsp;</a>';
        html += '...<a href="#" onclick="'+action+'('+(page_count-1)+'); return false;">'+sprintf(format,page_count)+'</a>';
    }
    return html;
}

function html_page_list2(action, page, page_count, param={}){
    size = param['size'] !== undefined ? param['size']: 10;
    if(param['page_name'] === undefined) param['page_name'] = 'page';

    if (page_count < 2) return '';
    html = '<table border=0 cellpadding=0 cellspacing=0 class="pg w100" '+(param['add_table'] !== undefined ? param['add_table'] : '')+'><tr>';
    html += '<td class="b" width=10><nobr>'+('Сторінки:')+'&nbsp;</nobr></td>';
    page_start = Math.floor(page/size)*size;
    page_end = Math.min(page_start+size,page_count);

    if (page_start > 0) {
        html += '<td class="pg-inact"><a href="#" onclick="'+action+'(0); return false;" class="pg-inact_lnk">1</a></td><td width=17>...</td>';
        html += '<td class="pg-inact"><a href="#" onclick="'+action+'('+(page_start-1)+'); return false;" class="pg-inact_lnk">&laquo;</a></td>';
    }
    for (i=page_start; i<page_end; i++) {
        cl_pfx = (page === i ? 'pg-act': 'pg-inact');
        html += '<td class="'+cl_pfx+'"><a href="#" onclick="'+action+'('+(i)+'); return false;" class="'+cl_pfx+'_lnk">'+(i+1)+'</a></td>';
    }
    if (page_end < page_count) {
        html += '<td class="pg-inact"><a href="#" onclick="'+action+'('+(page_end)+'); return false;" class="pg-inact_lnk">&raquo;</a></td>';
        html += '<td width=17>...</td><td class="pg-inact"><a href="#" onclick="'+action+'('+(page_count-1)+'); return false;" class="pg-inact_lnk">'+page_count+'</a></td>';
    }
    html += '<td style="text-align: left;">'+(param['add'] !== undefined ? param['add'] : '')+'</td>';
    t = [];
    html += '<td width="1%" style="text-align:right" nowrap>';
    if (page > 0) {
        html += '<a href="#" onclick="'+action+'('+(page-1)+'); return false;"><img src="/images/p-left-red.gif" border=0 width=29 height=17 title="'+('Предыдущая')+'"></a>';
    } else {
        html += '<img src="/images/p-left-gray.gif" border=0 width=29 height=17 title="'+('Предыдущая')+'">';
    }
    html += '<img src="/images/pg-act.gif" border=0 width=17 height=17>';
    if (page < page_count-1) {
        html += '<a href="#" onclick="'+action+'('+(page+1)+'); return false;"><img src="/images/p-right-red.gif" border=0 width=29 height=17 title="'+('Следующая')+'"></a>';
    } else {
        html += '<img src="/images/p-right-gray.gif" border=0 width=29 height=17 title="'+('Следующая')+'">';
    }
    html += '</td>';
    html += '</tr></table>';
    return  html;
}

function chat_add_message(msg) {
    w_frm_chat = window.top.frames['chat'];
    w_frm_chat.chatTextHtml += msg+'<msg_end>';
    w_frm_chat.chatUpdateText();
    w_frm_chat.chatTextHtml = '';
}


if (!Object.keys) {
    Object.keys = (function () {
        'use strict';
        var hasOwnProperty = Object.prototype.hasOwnProperty,
            hasDontEnumBug = !({toString: null}).propertyIsEnumerable('toString'),
            dontEnums = [
                'toString',
                'toLocaleString',
                'valueOf',
                'hasOwnProperty',
                'isPrototypeOf',
                'propertyIsEnumerable',
                'constructor'
            ],
            dontEnumsLength = dontEnums.length;

        return function (obj) {
            if (typeof obj !== 'object' && (typeof obj !== 'function' || obj === null)) {
                throw new TypeError('Object.keys called on non-object');
            }

            var result = [], prop, i;

            for (prop in obj) {
                if (hasOwnProperty.call(obj, prop)) {
                    result.push(prop);
                }
            }

            if (hasDontEnumBug) {
                for (i = 0; i < dontEnumsLength; i++) {
                    if (hasOwnProperty.call(obj, dontEnums[i])) {
                        result.push(dontEnums[i]);
                    }
                }
            }
            return result;
        };
    }());
}

function setTransformImage(select) {
    var selectedOption = select.options[select.selectedIndex],

        slotBefore = $('span[data-slot="transformBefore"]')[0],
        slotAfter = $('span[data-slot="transformAfter"]')[0],

        innerSlotBefore = slotBefore.firstChild,
        innerSlotAfter = slotAfter.firstChild,

        slotEnchantFrame = selectedOption.getAttribute('data-enchant3'),
        slotEnchant = selectedOption.getAttribute('data-enchant-class'),

        itemBefore,
        itemAfter;

    innerSlotBefore.innerHTML = '';
    innerSlotBefore.setAttribute('div_id', '');
    innerSlotBefore.style.backgroundImage = 'none';
    innerSlotBefore.onmouseover = function(e) {};
    innerSlotBefore.onmouseout = function(e) {};

    innerSlotAfter.setAttribute('div_id', '');
    innerSlotAfter.style.backgroundImage = 'none';
    innerSlotAfter.onmouseover = function(e) {};
    innerSlotAfter.onmouseout = function(e) {};

    if (art_alt) {
        itemBefore = art_alt['AA_' + selectedOption.value];
        itemAfter = art_alt['AA_' + selectedOption.getAttribute('data-transform-artikul')];

        if (itemBefore) {
            innerSlotBefore.setAttribute('div_id', itemBefore.artifact_alt_id);
            innerSlotBefore.style.backgroundImage = 'url(' + itemBefore.image + ')';
            innerSlotBefore.onmouseover = function(e) {
                artifactAlt(this, e, 2);
            };
            innerSlotBefore.onmouseout = function(e) {
                artifactAlt(this, e, 0);
            };

            if (slotEnchantFrame) {
                innerSlotBefore.innerHTML += '<span class="artifact-slot__enchant enchant-oprava"></span>';
            }

            if (slotEnchant) {
                innerSlotBefore.innerHTML += '<span class="artifact-slot__enchant ' + slotEnchant + '"></span>';
            }
        }

        if (itemAfter) {
            innerSlotAfter.setAttribute('div_id', itemAfter.artifact_alt_id);
            innerSlotAfter.style.backgroundImage = 'url(' + itemAfter.image + ')';
            innerSlotAfter.onmouseover = function(e) {
                artifactAlt(this, e, 2);
            };
            innerSlotAfter.onmouseout = function(e) {
                artifactAlt(this, e, 0);
            };
        }
    }
}

function clan_talents_alt(talent_id, num, m_event, evnt){
    var alt_div = _top().$('#clan_talents_alt');
    var cur_id = alt_div.attr("data-id");
    var cur_num = alt_div.attr("data-num");

    if(evnt == 2){
        document.onmousemove=function(e){ clan_talents_alt(0, 0, e||event, 1); }
        alt_div.show();
    }else if(evnt == 0){
        document.onmousemove=function(){};
        alt_div.hide(); return false;
    }

    if(evnt == 1){
        var coor = getIframeShift();
        var ex = m_event.clientX+coor.left;
        var ey = m_event.clientY+coor.top;
        var x = 0;
        var y = 0;

        alt_div.offsetWidth = alt_div.width();
        alt_div.offsetHeight = alt_div.height();

        if(top.noIframeCoords !== undefined && top.noIframeCoords) {
            ex = m_event.clientX;
            ey = m_event.clientY;

            x = ex + 10 + alt_div.offsetWidth > top.document.body.clientWidth ? (ex) - (ex + 10 + alt_div.offsetWidth - top.document.body.clientWidth) : ex + 10;
            y = ey + alt_div.offsetHeight - top.document.body.scrollTop > top.document.body.clientHeight - 20 ? ey - alt_div.offsetHeight - 10 : ey + 10;
            alt_div.css('left', x);
            alt_div.css('top', y);
        }else{
            ex = m_event.clientX + top.document.body.scrollLeft;
            ey = m_event.clientY + top.document.body.scrollTop;
            x = ex + alt_div.offsetWidth > top.document.body.clientWidth - 20 ? ex - alt_div.offsetWidth - 10 : ex + 10;
            y = ey + alt_div.offsetHeight - top.document.body.scrollTop > top.document.body.clientHeight - 20 ? ey - alt_div.offsetHeight - 10 : ey + 10;
            if (x < 0 ) {
                x = ex - alt_div.offsetWidth/2;
            }
            if (x < 7 ) {
                x = 7;
            }
            if (x > top.document.body.clientWidth - alt_div.offsetWidth - 20) {
                x= top.document.body.clientWidth - alt_div.offsetWidth - 20;
            }
            alt_div.css('left', x + 100);
            alt_div.css('top', y + 175);
        }
        return false;
    }

    if(cur_id == talent_id && cur_num == num) return false;

    alt_div.attr("data-id", talent_id);
    alt_div.attr("data-num", num);

    var content = '';

    content += '<table width="300" border="0" cellspacing="0" cellpadding="0" class="aa-table">';
    content += '<tr><td width="14" class="aa-tl"><img src="images/d.gif" width="14" height="24"><br></td>';
    content += '<td class="aa-t aa-table-t" align="center" style="vertical-align:middle"><b style="color:' + quality_color((talents_hash[talent_id][num]['level'] - 1)) + '">' + talents_hash[talent_id][num]['title'] + '</b></td>';
    content += '<td width="14" class="aa-tr"><img src="images/d.gif" width="14" height="24"><br></td></tr>';
    content += '<tr class="bg_alt2"><td class="aa-l" style="padding:0;"></td><td  class="bg_alt3" style="padding:0;">';
    content += '<table width="275" style=" margin: 3px" border="0" cellspacing="0" cellpadding="0" class="aa-table-t"><tr>';
    content += '<td align="center" valign="top" width="60">';

    try{
        content += talents_hash[talent_id][num]['description'];
    }catch(e){ content += ' --- '; }

    content += '</td></tr></table></td><td class="aa-r" style="padding:0px"></td></tr>';
    content += '<tr><td class="aa-bl"></td><td class="aa-b"><img src="images/d.gif" width="1" height="5"></td><td class="aa-br"></td></tr>';
    content += '</table>';

    alt_div.html(content);
}


var get_event_number = function(number, plus) {
	plus = plus === undefined ? false : true;
	number = Math.floor(number);
	if(!(number % 2 === 0)) {
		number += (plus ? 1 : -1);
	}
	return number;
}