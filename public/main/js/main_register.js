var COMMON_DATA = {}; // data storage for all tooltips
var TEMPLATE_PATH = '';

function F_USER(s) {
    var area = $('#textInputArea');
    area.val(area.val() + '' + s + ',\r\n');
    area.focusin();
}

function F_SMILE(s, id) {
	if(!id || id == '') id = "textInputArea";
	var area = $('#' + id);
    area.val(area.val() + ' ' + s + ' ');
    area.focusin();
}

var tagInputSelectedText = '';
function F_TAGS(tag, id) {
    if(!id || id == '') id = "textInputArea";
    var ta = $('#' + id);
    ta.focus();
	switch(tag){
		case 'b':
            var sel = tagInputSelectedText;
            if(sel){
                ta.val(ta.val().replace(sel, '<b>' + sel + '</b>'));
            }
			break;
        case 'i':
            var sel = tagInputSelectedText;
            if(sel){
                ta.val(ta.val().replace(sel, '<i>' + sel + '</i>'));
            }
            break;
        case 'u':
            var sel = tagInputSelectedText;
            if(sel){
                ta.val(ta.val().replace(sel, '<u>' + sel + '</u>'));
            }
            break;
	}
}

function F_TAGS_INIT(id) {
    $('#' + id).select(function(){
        tagInputSelectedText = document.getSelection();
    });
}

/**
 * Renders tooltip for [[artN]] macros
 *
 * @author r.berezkin
 *
 * @param {Object} data Data for render function
 *
 * @returns {Void}
 */
function templateArt(data) {
	var html = '',
		skills = '',
		skill = {},
		itemData = '',
		count = 0;

	if (data.kind) {
		itemData += '<span class="b-common-alt__data-item"><img src="' + TEMPLATE_PATH + '/images/icons/alt/kind.gif" /> ' + data.kind + '</span>';
	}

	if (data.lev) {
		itemData += '<span class="b-common-alt__data-item"><img src="' + TEMPLATE_PATH + '/images/icons/alt/level.gif" /> ' + data.lev.title + ' <b class="text-red-2">' + data.lev.value + '</b></span>';
	}

	if (data.dur) {
		itemData += '<span class="b-common-alt__data-item"><img src="' + TEMPLATE_PATH + '/images/icons/alt/dur.gif" /> <span class="text-red-2">' + data.dur + '</span>/' + data.dur_max + '</span>';
	}

	if (data.trend) {
		itemData += '<span class="b-common-alt__data-item"><img src="' + TEMPLATE_PATH + '/images/icons/alt/trend.gif" /> ' + data.trend + '</span>';
	}

	if (data.price) {
		itemData += '<span class="b-common-alt__data-item"><b class="text-red-2">' + data.price + '</b></span>';
	}

	if (data.exp) {
		skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
		skills += '<td class="b-common-alt__skills-title">' + data.exp.title + '</td>';
		skills += '<td class="b-common-alt__skills-value text-green"><b>' + data.exp.value + '</b></td>';
		skills += '</tr>';

		count++;
	}

	if (data.skills && data.skills.length) {
		for (var i = 0, len = data.skills.length; i < len; i++) {
			skill = data.skills[i];

			skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
			skills += '<td class="b-common-alt__skills-title">' + skill.title + '</td>';
			skills += '<td class="b-common-alt__skills-value text-red-2">' + skill.value + '</td>';
			skills += '</tr>';

			count++;
		}
	}

	if (data.skills_e && data.skills_e.length) {
		for (var i = 0, len = data.skills_e.length; i < len; i++) {
			skill = data.skills_e[i];

			skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
			skills += '<td class="b-common-alt__skills-title">' + skill.title + '</td>';
			skills += '<td class="b-common-alt__skills-value">' + skill.value + '</td>';
			skills += '</tr>';

			count++;
		}
	}

	if (data.set) {
		skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
		skills += '<td class="b-common-alt__skills-title">' + data.set.title + '</td>';
		skills += '<td class="b-common-alt__skills-value">' + data.set.value + '</td>';
		skills += '</tr>';

		count++;
	}

	if (data.nogive) {
		skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
		skills += '<td colspan="2" class="b-common-alt__skills-title text-red-2"><b>' + data.nogive + '</b></td>';
		skills += '</tr>';

		count++;
	}

	if (data.nosell) {
		skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
		skills += '<td colspan="2" class="b-common-alt__skills-title text-green"><b>' + data.nosell + '</b></td>';
		skills += '</tr>';

		count++;
	}

	if (data.noweight) {
		skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
		skills += '<td colspan="2" class="b-common-alt__skills-title text-grey"><b>' + data.noweight + '</b></td>';
		skills += '</tr>';

		count++;
	}

	if (data.desc) {
		skills += '<tr class="' + (count % 2 == 0 ? 'even' : 'odd') + '">';
		skills += '<td colspan="2" class="b-common-alt__skills-title">' + data.desc + '</td>';
		skills += '</tr>';

		count++;
	}

	html = [
		'<div class="b-common-alt">',
		'<table cellspacing="0">',
		'<tr>',
		'<td colspan="3" class="b-common-alt__title" style="background: red">',
		'<h2 class="b-common-alt__title-inner" style="color: ' + data.color + ';">' + data.title + '</h2>',
		'</td>',
		'</tr>',
		'<tr>',
		'<td class="side">&nbsp;</td>',
		'<td class="b-common-alt__body">',
		'<div class="b-common-alt__body-inner">',
		'<div class="b-common-alt__info">',
		'<div class="b-common-alt__image">',
		'<img src="' + data.picture + '" alt="" />',
		'</div>',
		'<div class="b-common-alt__data">',
		itemData,
		'<span class="b-common-alt__data-item">&nbsp;</span>',
		'</div>',
		'</div>',
		'<table class="b-common-alt__skills">',
		skills,
		'</table>',
		'</div>',
		'</td>',
		'<td class="side">&nbsp;</td>',
		'</tr>',
		'</table>',
		'</div>'
	].join('');

	return html;
}
/* ============ */

/**
* Opens and centers popup block
*
* @author r.berezkin
*
* @param string popup - popup block in jquery format or DOM-node
* @param string shader (optional) - shader block in jquery format or DOM-node
* @param number interval (optional) - animation interval
*
* @return bool
*/
function openPopup(popup, shader, interval) {
	var $popup = $(popup),
	$shader = !shader ? $('.b-popup-shader') : $(shader),
	interval = (interval === undefined) ? 500 : parseInt(interval),
	$close = $popup.find('span[data-button="close"]');

	if (!$popup[0]) {
		if (console.error) {
			console.error('No popup found');
			return false;
		}
	}

	$popup.css({
		'display': 'block',
		'margin-left': '-' + ($popup.width() / 2) + 'px',
		'margin-top': '-' + ($popup.height() / 2) + 'px'
	});

	$shader.fadeIn(interval);

	$close.on('click', function() {
		closePopup($popup, $shader, interval);
	});

	$shader.on('click', function() {
		closePopup($popup, $shader, interval);
	});

	$(document).on('keyup', function(e) {
		if (e.keyCode == 27) {
			closePopup($popup, $shader, interval);
		}
	});

	return false;
}

/**
* Closes popup block
*
* @author r.berezkin
*
* @param string popup - popup block in jquery format or DOM-node
* @param string shader (optional) - shader block in jquery format or DOM-node
* @param number interval (optional) - animation interval
*
* @return bool
*/
function closePopup(popup, shader, interval) {
	var $popup = $(popup),
	$shader = !shader ? $('.b-popup-shader') : $(shader),
	interval = (interval === undefined) ? 500 : parseInt(interval);

	if (!$popup[0]) {
		if (console.error) {
			console.error('No popup found');
			return false;
		}
	}

	$popup.hide();
	$shader.fadeOut(interval);

	$(document).unbind('keyup');

	return false;
}

/**
* Change popup block
*
* @author a.schneider
*
* @param string popup1 - first popup block in jquery format or DOM-node
* @param string popup2 - second popup block in jquery format or DOM-node
* @param string shader (optional) - shader block in jquery format or DOM-node
*
* @return bool
*/
function changePopup(popup1, popup2, shader) {
	closePopup(popup1, shader, 0);

	return openPopup(popup2, shader, 0);
}

/**
* Fixes -webkit-autofill style issue in Goole Chrome browser
* (https://code.google.com/p/chromium/issues/detail?id=1334)
*
* @return void
*/
function fixYellow() {
	if (navigator.userAgent.toLowerCase().indexOf('chrome') > -1) {
		$.each($("input[type=text], input[type=password]"), function() {
			$(this).clone(true).appendTo($(this).parent());
			$(this).remove();
		});
	}
}

// DOM-ready
$(function() {
	/* Replace PT Sans font */
	Cufon.replace('span[data-font="PTSans"]', {
		color: '-linear-gradient(#ffff99, #fea143, #c54500)',
		textShadow: '#000 1px 1px'
	});

	Cufon.replace('span[data-font="PTSansBlack"]', {
		textShadow: 'rgba(255, 255, 255, 0.5) 1px 1px'
	});

	/* Form elements stylization */
	$('input, select').styler();

	/* Focus emulation */
	$('input[type="text"], input[type="password"]').bind('focus blur', function() {
		$(this).parents('.b-common-form__field').toggleClass('focus');
	});

	/* Tooltips */
	if ($.fn.powerTip) {
		$('*[data-object]').powerTip({
			followMouse: true,
			fadeInTime: 0,
			fadeOutTime: 0,
			closeDelay: 0
		}).data('powertip', function() {
				var $this = $(this),
					data = COMMON_DATA[$this.data('object')],
					callback = 'debugTemplate';

				if (!data) return;

				if (data._template) {
					callback = data._template;
				}

				return window[callback](data);
			});
	}

	window.setTimeout(fixYellow, 100);
});