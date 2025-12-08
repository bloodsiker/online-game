$(function() {
	brokenBrowser = false;
	// IE8+ support
	(function() {
		var method;
		var noop = function () {};
		var methods = [
			'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error1',
			'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
			'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
			'timeStamp', 'trace', 'warn'
		];
		var length = methods.length;
		var console = (window.console = window.console || {});

		while (length--) {
			method = methods[length];

			// Only stub undefined methods.
			if (!console[method]) {
				console[method] = noop;
			}
		}
	}());
	
	if (navigator.appName == 'Microsoft Internet Explorer') {
		var ua = navigator.userAgent;
		var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
		if (re.exec(ua) != null) {
			var rv = parseFloat(RegExp.$1);
			if (rv < 9) {
				brokenBrowser = true;
			}
		}
	}
	
	if (!Function.prototype.bind) {
		Function.prototype.bind = function (oThis) {
			if (typeof this !== "function") {
				// closest thing possible to the ECMAScript 5 internal IsCallable function
				throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable");
			}
			
			var aArgs = Array.prototype.slice.call(arguments, 1);
			var	fToBind = this;
			var fNOP = function () {};
			var fBound = function () {
				return fToBind.apply(this instanceof fNOP && oThis ? this : oThis,
									aArgs.concat(Array.prototype.slice.call(arguments)));
			};
			
			fNOP.prototype = this.prototype;
			fBound.prototype = new fNOP();

			return fBound;
		};
	}
	$.support.cors = true;
	// end IE8+ support)
	
	Math.getRandomInt = function(min, max) {
		return Math.floor(Math.random() * (max - min + 1)) + min;
	};
	
	window.gebi = function(id) { // TODO вроде jquery есть?!
		return document.getElementById(id);
	};
	
	String.prototype.format = function () {
		var arg = arguments,
			i = 0;
		return this.replace(/%((%)|s)/g, function (n) {
			if (typeof arg[i] !== 'undefined') {
				return arg[i++];
			} else {
				return n;
			}
		});
	};

	String.prototype.ucfirst = function () {
		return '%s%s'.format(this.substr(0, 1).toUpperCase(), this.substr(1));
	};
	
	String.prototype.trim = function() {
		return this.replace(/^\s+/, '').replace(/\s+$/, '')
	};
	
	String.prototype.substitute = function(data) {
		return this.replace(/%(.+?)%/g, function (n, m) { return data[m.toLowerCase()] || n })
	};
	
	window.guid_s4 = function() {
		return Math.floor((1 + Math.random()) * 0x10000).toString(16).substring(1);
	};

	window.guid = function() {
		return guid_s4() + guid_s4() + '-' + guid_s4() + '-' + guid_s4() + '-' + guid_s4() + '-' + guid_s4() + guid_s4() + guid_s4();
	};
	
	_.templateSettings = {
		evaluate : /<\?([\s\S]+?)\?>/g,
		interpolate : /<\?=([\s\S]+?)\?>/g,
		escape : /<\?-([\s\S]+?)\?>/g
	};
	
	window.itemInfo = window.itemInfo || function(itemTplId, nick, id) {
		var url = "/entity/item/" + itemTplId + ".html";
		window.open(url);
	};

	window.fightInfo = window.fightInfo || function(id) {
		var url = "fight.php?id=" + id;
		window.open(url);
	};

	window.eventInfo = window.eventInfo || function(id) {
		var url = "event.php?id=" + id;
		window.open(url);
	};

	window.userInfo = window.userInfo || function(nick) {
		var url = "user.php?nick=" + nick;
		window.open(url);
	};

	window.clanInfo = window.clanInfo || function(title) {
		var url = "clan.php?title=" + title;
		window.open(url);
	};
	
	window.socialShare = function(socnet, url, title) {
		switch (socnet) {
			case 'fb':
				url = 'http://www.facebook.com/sharer.php?u='+encodeURIComponent(url)+'&r='+Math.random()+'&t='+encodeURIComponent(title)
				break
			case 'vk':
				url = 'http://vk.com/share.php?url='+encodeURIComponent(url)+'&r='+Math.random()
				break
			case 'tw':
				url = 'http://twitter.com/share?url='+encodeURIComponent(url)+'&r='+Math.random()+'&text='+encodeURIComponent(title)
				break
		}
		if (url) {
			window.open(url, 'share', 'scrollbars=0,resizable=1,toolbar=0,location=0,menubar=0,status=0,directories=0,width=640,height=480')
		}
	};

	window.toggleFullScreen = function() {
		if (!document.fullscreenElement &&    // alternative standard method
			!document.mozFullScreenElement && !document.webkitFullscreenElement) {  // current working methods
			if (document.documentElement.requestFullscreen) {
				document.documentElement.requestFullscreen();
			} else if (document.documentElement.mozRequestFullScreen) {
				document.documentElement.mozRequestFullScreen();
			} else if (document.documentElement.webkitRequestFullscreen) {
				document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
			}
		} else {
			if (document.cancelFullScreen) {
				document.cancelFullScreen();
			} else if (document.mozCancelFullScreen) {
				document.mozCancelFullScreen();
			} else if (document.webkitCancelFullScreen) {
				document.webkitCancelFullScreen();
			}
		}
	};

	window.translate = function(text) {
		return Drako.translate(text);
	};

	window.objectGetSet = {
		set: function() {
			var t = this;
			var value = arguments[0];
			var v;
			var k;
			var i = 1;
			for (; i < arguments.length; i++) {
				k = arguments[i];
				if (i < (arguments.length - 1)) {
					v = t[k];
					if (!v) {
						v = {};
						t[k] = v;
					}
					t = v
				} else {
					t[k] = value;
				}
			}
		},
		
		get: function() {
			var i = 0;
			var t = this;
			var k;
			var v;
			for (; i < arguments.length; i++) {
				k = arguments[i];
				v = t[k];
				if (!v) {
					return;
				}
				t = v;
			}
			return v;
		}
	};

	window.formatHttpResponse = function(data) {
		return '%s %s'.format(data.status, data.statusText);
	};
	
	/// куки
	window.getCookie = function(name) {
		var cookies = document.cookie.split(/; /)
		var cookie;
		for (var i = 0; i < cookies.length; i++) {
			cookie = cookies[i].split(/=/);
			if (name.trim() == cookie[0].trim()) {
				return unescape(cookie[1])
			}
		}
		return null
	};

	window.setCookie = function(name, value) {
		var str = name + "=" + encodeURIComponent(value)
		for (var i = 2; i < arguments.length; i++) {
			var arg = arguments[i]
			switch (i) {
				case 2:
					if (typeof(arg) != 'undefined') {
						if (typeof(arg) != 'object') {
							var s = 1000, m = 60 * s, h = 60 * m, d = 24 * h, mon = 30 * d, y = 12 * mon
							arg = new Date(new Date().getTime() + eval('0' + arg.toString().toLowerCase().replace(/([0-9]+)([a-z]+)/g, '$1*$2')))
						}
						str += "; expires=" + arg.toGMTString()
					}
					break
				case 3:
					str += "; path=" + arg
					break
				case 4:
					str += "; domain=" + arg
					break
				case 5:
					str += "; secure"
					break
			}
		}
		document.cookie = str
	};

	window.deleteCookie = function(name) {
		var hostname = location.hostname;
		setCookie(name, '', -1, '/', hostname);
		setCookie(name, '', -1, '/', '.' + hostname);
	};
	
	/// главный объект
	var Drako = window.Drako = {};
	_.extend(Drako, Backbone.Events, {
		init: function(params) {
			this.shards = {};
			this.content = {};
			this.htmlCache = {};
			this.styles = {};
			this.projectTitle = 'Драконы Вечности';
			this.lang = params.lang;
			this.socialStandalone = params.socialStandalone;
			this.initDomain = params.initDomain;
			this.staticVersion = !brokenBrowser && params.staticVersion; // у IE проблемы с кешами
			
			var shardCode = params.shardCode;
			var fullUrl = '//%s/shared/json/%s/shards.js'.format(this.initDomain, this.getLang());
			var shardsAjax = $.ajax({
				url: fullUrl,
				cache: false,
				dataType: 'json'
			})
			.done((function(data) {
				_.each(data, (function(data) {
					this.shards[data.code] = new Shard(data);
				}).bind(this));
				this.shard = this.shards[shardCode];
			}).bind(this))
			.fail((function(data) {
				console.warn('Drako: failed loading shard data - %s'.format(formatHttpResponse(data)));
			}).bind(this))
			.then(function() {
				return $.when(Drako.Content('enum'), Drako.initModule('social/main'));
			});
			
			// проверяем необходимость переводов
			if (this.getLang() != 'ru') {
				var transAjax = shardsAjax.then(function() {
					return $.when(Drako.Script('ext/md5'), Drako.Content('translation'));
				});
				return transAjax;
			} else {
				return shardsAjax;
			}
		},
		
		initModule: function(name, params) {
			var loader = Drako.Script(name).then((function() {
				var names = name.split('/');
				var mod = Drako;
				_.each(names, function(path) {
					var modName = path.ucfirst();
					if (modName != 'Main') {
						mod = mod[modName];
					}
				});
				return mod.init.call(mod, params);
			}).bind(this));
			return $.when(loader);
		},
		
		statusCallback: function(params) {
			if (Drako.Auth.App) {
				Drako.Auth.App.messageBox.show(params);
			} else {
				window.document['game'].showStatusMessage(params);
			}
		},
		
		getShard: function(shardCode) {
			if (!shardCode) {
				return this.shard;
			} else {
				return this.shards[shardCode];
			}
		},
		
		getShards: function() {
			return this.shards;
		},
		
		getContentDomain: function() {
			return this.initDomain;
		},
		
		getScriptDomain: function() {
			return this.initDomain;
		},
		
		getHtmlDomain: function() {
			return this.initDomain;
		},
		
		getProjectTitle: function() {
			return '%s %s'.format(translate(this.projectTitle), this.getShard().nick);
		},
		
		getLang: function() {
			return this.lang ? this.lang : 'ru';
		},
		
		translate: function(text) {
			if (this.getLang() == 'ru') {
				return text;
			} else {
				var translation = this.content.translation;
				var hash = CryptoJS.MD5(text).toString();
				return translation[hash] || text;
			}
		},
		
		hideLoader: function() {
			gebi('loader').style.display = 'none';
			gebi('flash').style.left = '0px';
			this.trigger('hideLoader');
		},
		
		redirectTo: function(page, urlParams) {
			var shard = Drako.getShard();
			var url = '//%s/%s'.format(shard.getInfoGameDomain(), page);
			if (urlParams) {
				url += urlParams;
			} else {
				url += location.search;
			}
			location.href = url;
		}
	});
	
	/// шарды
	var Shard = function(params) {
		this.code = params.code;
		this.nick = params.nick;
		this.title = params.title;
		this.description = params.description;
		this.gameDomain = params.game_domain;
		this.fbGameDomain = params.fb_game_domain;
		this.vkGameDomain = params.vk_game_domain;
		this.kgGameDomain = params.kg_game_domain;
		this.xpGameDomain = params.xp_game_domain;
		this.infoDomain = params.info_domain;
		this.fbInfoDomain = params.fb_info_domain;
		this.vkInfoDomain = params.vk_info_domain;
		this.kgInfoDomain = params.kg_info_domain;
		this.xpInfoDomain = params.xp_info_domain;
		this.langs = params.langs;
		this.hidden = params.hidden;
	};
	_.extend(Shard.prototype, {
		getLangsArray: function() {
			var ret = [];
			var langs = Drako.content['enum'].langs;
			var langKeys = _.keys(langs);
			for (var i = 0; i < langKeys.length; i++) {
				var langKey = langKeys[i];
				var langBit = langs[langKey];
				if (this.langs & langBit) {
					ret.push(langKey);
				}
			}
			return ret;
		},
		
		getGameDomain: function() {
			if (Drako.socialStandalone) {
				var socialApp = Drako.Social.apps[Drako.socialStandalone.code];
				return socialApp.getGameDomain(this);
			} else {
				return this.gameDomain;
			}
		},
		
		getInfoDomain: function() {
			if (Drako.socialStandalone) {
				var socialApp = Drako.Social.apps[Drako.socialStandalone.code];
				return socialApp.getInfoDomain(this);
			} else {
				return this.infoDomain;
			}
		},
		
		getInfoGameDomain: function() {
			var infoDomain = this.getInfoDomain();
			if (infoDomain) {
				return infoDomain + '/game';
			} else {
				return this.getGameDomain();
			}
		}
	});
	
	/// загрузчик контента
	Drako.Content = function(name) {
		if (Drako.content[name]) {
			return $.Deferred().resolve([Drako.content[name], 'success']).promise();
		} else {
			// console.log('content cache miss!');
			var fullUrl = '//%s/shared/json/%s/%s.js'.format(Drako.getContentDomain(), Drako.getLang(), name);
			return $.ajax({
				url: fullUrl,
				cache: false,
				dataType: 'json'
			})
			.done((function(data) {
				Drako.content[name] = data;
			}).bind(this))
			.fail((function(data) {
				console.warn('Drako.Content(\'%s\'): failed - %s'.format(name, formatHttpResponse(data)));
			}).bind(this));
		}
	};
	/// загрузчик шаблонов
	Drako.Html = function(name) {
		if (Drako.htmlCache[name]) {
			return $.Deferred().resolve([Drako.htmlCache[name], 'success']).promise();
		} else {
			var fullUrl = '//%s/shared/js/%s.html'.format(Drako.getHtmlDomain(), name);
			return $.ajax({
				url: fullUrl,
				cache: false,
				dataType: 'text'
			})
			.done((function(data) {
				Drako.htmlCache[name] = data;
			}).bind(this))
			.fail((function(data) {
				console.warn('Drako.Html(\'%s\'): failed - %s'.format(name, formatHttpResponse(data)));
			}).bind(this));
		}
	};
	/// загрузчик скриптов
	Drako.Script = function(name) {
		var fullUrl = '//%s/shared/js/%s.js'.format(Drako.getScriptDomain(), name);
		return $.ajax({
			url: fullUrl,
			cache: false,
			dataType: 'script'
		})
		.fail((function(data) {
			console.warn('Drako.Script(\'%s\'): failed - %s'.format(name, formatHttpResponse(data)));
		}).bind(this));
	};
	/// загрузчик стилей
	Drako.Style = function(url) {
		if (Drako.styles[url]) {
			return;
		}
		var ux = Drako.staticVersion || parseInt(new Date().getTime() / 1000);
		var fullUrl = "%s%sux=%s".format(url, (url.indexOf('&') >= 0) ? '&' : '?', ux);
		$('<link rel="stylesheet" type="text/css" href="%s" />'.format(fullUrl)).appendTo("head");
		var state = $.Deferred();
		var checkInterval = 100;
		var maxWaitTime = 5000;
		var delayForParsing = 300;
		var loading = { state: state, retry: 0 };
		loading.intervalId = setInterval(function() {
			var loadingStyle = Drako.styles[url];
			loadingStyle.retry++;
			for (var i=0; i < document.styleSheets.length; i++) {
				var styleSheet = document.styleSheets[i];
				var href = styleSheet.href;
				if (href && (href.indexOf(url) >= 0)) {
					clearTimeout(loadingStyle.intervalId);
					setTimeout(function() {
						loadingStyle.state.resolve();
					}, delayForParsing);
					Drako.styles[url] = true;
					return;
				}
			}
			if (loadingStyle.retry * checkInterval >= maxWaitTime) {
				clearTimeout(loadingStyle.intervalId);
				loadingStyle.state.reject();
				delete Drako.styles[url];
			}
		}, checkInterval);
		Drako.styles[url] = loading;
		return state.promise();
	};
	/// серверные нотификации
	Drako.Notify = function(data) {
		var fullUrl = '//%s/notify.php'.format(Drako.getScriptDomain());
		return $.ajax({
			url: fullUrl,
			cache: false,
			data: data
		})
		.fail((function(response) {
			console.warn('Drako.Notify(): failed - %s'.format(formatHttpResponse(response)), data);
		}).bind(this));
	};
});
