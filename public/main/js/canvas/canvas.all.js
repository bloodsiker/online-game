(function($) {
    "use strict";

    function safeAdd(x, y) {
        var lsw = (x & 65535) + (y & 65535);
        var msw = (x >> 16) + (y >> 16) + (lsw >> 16);
        return msw << 16 | lsw & 65535
    }

    function bitRotateLeft(num, cnt) {
        return num << cnt | num >>> 32 - cnt
    }

    function md5cmn(q, a, b, x, s, t) {
        return safeAdd(bitRotateLeft(safeAdd(safeAdd(a, q), safeAdd(x, t)), s), b)
    }

    function md5ff(a, b, c, d, x, s, t) {
        return md5cmn(b & c | ~b & d, a, b, x, s, t)
    }

    function md5gg(a, b, c, d, x, s, t) {
        return md5cmn(b & d | c & ~d, a, b, x, s, t)
    }

    function md5hh(a, b, c, d, x, s, t) {
        return md5cmn(b ^ c ^ d, a, b, x, s, t)
    }

    function md5ii(a, b, c, d, x, s, t) {
        return md5cmn(c ^ (b | ~d), a, b, x, s, t)
    }

    function binlMD5(x, len) {
        x[len >> 5] |= 128 << len % 32;
        x[(len + 64 >>> 9 << 4) + 14] = len;
        var i;
        var olda;
        var oldb;
        var oldc;
        var oldd;
        var a = 1732584193;
        var b = -271733879;
        var c = -1732584194;
        var d = 271733878;
        for (i = 0; i < x.length; i += 16) {
            olda = a;
            oldb = b;
            oldc = c;
            oldd = d;
            a = md5ff(a, b, c, d, x[i], 7, -680876936);
            d = md5ff(d, a, b, c, x[i + 1], 12, -389564586);
            c = md5ff(c, d, a, b, x[i + 2], 17, 606105819);
            b = md5ff(b, c, d, a, x[i + 3], 22, -1044525330);
            a = md5ff(a, b, c, d, x[i + 4], 7, -176418897);
            d = md5ff(d, a, b, c, x[i + 5], 12, 1200080426);
            c = md5ff(c, d, a, b, x[i + 6], 17, -1473231341);
            b = md5ff(b, c, d, a, x[i + 7], 22, -45705983);
            a = md5ff(a, b, c, d, x[i + 8], 7, 1770035416);
            d = md5ff(d, a, b, c, x[i + 9], 12, -1958414417);
            c = md5ff(c, d, a, b, x[i + 10], 17, -42063);
            b = md5ff(b, c, d, a, x[i + 11], 22, -1990404162);
            a = md5ff(a, b, c, d, x[i + 12], 7, 1804603682);
            d = md5ff(d, a, b, c, x[i + 13], 12, -40341101);
            c = md5ff(c, d, a, b, x[i + 14], 17, -1502002290);
            b = md5ff(b, c, d, a, x[i + 15], 22, 1236535329);
            a = md5gg(a, b, c, d, x[i + 1], 5, -165796510);
            d = md5gg(d, a, b, c, x[i + 6], 9, -1069501632);
            c = md5gg(c, d, a, b, x[i + 11], 14, 643717713);
            b = md5gg(b, c, d, a, x[i], 20, -373897302);
            a = md5gg(a, b, c, d, x[i + 5], 5, -701558691);
            d = md5gg(d, a, b, c, x[i + 10], 9, 38016083);
            c = md5gg(c, d, a, b, x[i + 15], 14, -660478335);
            b = md5gg(b, c, d, a, x[i + 4], 20, -405537848);
            a = md5gg(a, b, c, d, x[i + 9], 5, 568446438);
            d = md5gg(d, a, b, c, x[i + 14], 9, -1019803690);
            c = md5gg(c, d, a, b, x[i + 3], 14, -187363961);
            b = md5gg(b, c, d, a, x[i + 8], 20, 1163531501);
            a = md5gg(a, b, c, d, x[i + 13], 5, -1444681467);
            d = md5gg(d, a, b, c, x[i + 2], 9, -51403784);
            c = md5gg(c, d, a, b, x[i + 7], 14, 1735328473);
            b = md5gg(b, c, d, a, x[i + 12], 20, -1926607734);
            a = md5hh(a, b, c, d, x[i + 5], 4, -378558);
            d = md5hh(d, a, b, c, x[i + 8], 11, -2022574463);
            c = md5hh(c, d, a, b, x[i + 11], 16, 1839030562);
            b = md5hh(b, c, d, a, x[i + 14], 23, -35309556);
            a = md5hh(a, b, c, d, x[i + 1], 4, -1530992060);
            d = md5hh(d, a, b, c, x[i + 4], 11, 1272893353);
            c = md5hh(c, d, a, b, x[i + 7], 16, -155497632);
            b = md5hh(b, c, d, a, x[i + 10], 23, -1094730640);
            a = md5hh(a, b, c, d, x[i + 13], 4, 681279174);
            d = md5hh(d, a, b, c, x[i], 11, -358537222);
            c = md5hh(c, d, a, b, x[i + 3], 16, -722521979);
            b = md5hh(b, c, d, a, x[i + 6], 23, 76029189);
            a = md5hh(a, b, c, d, x[i + 9], 4, -640364487);
            d = md5hh(d, a, b, c, x[i + 12], 11, -421815835);
            c = md5hh(c, d, a, b, x[i + 15], 16, 530742520);
            b = md5hh(b, c, d, a, x[i + 2], 23, -995338651);
            a = md5ii(a, b, c, d, x[i], 6, -198630844);
            d = md5ii(d, a, b, c, x[i + 7], 10, 1126891415);
            c = md5ii(c, d, a, b, x[i + 14], 15, -1416354905);
            b = md5ii(b, c, d, a, x[i + 5], 21, -57434055);
            a = md5ii(a, b, c, d, x[i + 12], 6, 1700485571);
            d = md5ii(d, a, b, c, x[i + 3], 10, -1894986606);
            c = md5ii(c, d, a, b, x[i + 10], 15, -1051523);
            b = md5ii(b, c, d, a, x[i + 1], 21, -2054922799);
            a = md5ii(a, b, c, d, x[i + 8], 6, 1873313359);
            d = md5ii(d, a, b, c, x[i + 15], 10, -30611744);
            c = md5ii(c, d, a, b, x[i + 6], 15, -1560198380);
            b = md5ii(b, c, d, a, x[i + 13], 21, 1309151649);
            a = md5ii(a, b, c, d, x[i + 4], 6, -145523070);
            d = md5ii(d, a, b, c, x[i + 11], 10, -1120210379);
            c = md5ii(c, d, a, b, x[i + 2], 15, 718787259);
            b = md5ii(b, c, d, a, x[i + 9], 21, -343485551);
            a = safeAdd(a, olda);
            b = safeAdd(b, oldb);
            c = safeAdd(c, oldc);
            d = safeAdd(d, oldd)
        }
        return [a, b, c, d]
    }

    function binl2rstr(input) {
        var i;
        var output = "";
        var length32 = input.length * 32;
        for (i = 0; i < length32; i += 8) {
            output += String.fromCharCode(input[i >> 5] >>> i % 32 & 255)
        }
        return output
    }

    function rstr2binl(input) {
        var i;
        var output = [];
        output[(input.length >> 2) - 1] = undefined;
        for (i = 0; i < output.length; i += 1) {
            output[i] = 0
        }
        var length8 = input.length * 8;
        for (i = 0; i < length8; i += 8) {
            output[i >> 5] |= (input.charCodeAt(i / 8) & 255) << i % 32
        }
        return output
    }

    function rstrMD5(s) {
        return binl2rstr(binlMD5(rstr2binl(s), s.length * 8))
    }

    function rstrHMACMD5(key, data) {
        var i;
        var bkey = rstr2binl(key);
        var ipad = [];
        var opad = [];
        var hash;
        ipad[15] = opad[15] = undefined;
        if (bkey.length > 16) {
            bkey = binlMD5(bkey, key.length * 8)
        }
        for (i = 0; i < 16; i += 1) {
            ipad[i] = bkey[i] ^ 909522486;
            opad[i] = bkey[i] ^ 1549556828
        }
        hash = binlMD5(ipad.concat(rstr2binl(data)), 512 + data.length * 8);
        return binl2rstr(binlMD5(opad.concat(hash), 512 + 128))
    }

    function rstr2hex(input) {
        var hexTab = "0123456789abcdef";
        var output = "";
        var x;
        var i;
        for (i = 0; i < input.length; i += 1) {
            x = input.charCodeAt(i);
            output += hexTab.charAt(x >>> 4 & 15) + hexTab.charAt(x & 15)
        }
        return output
    }

    function str2rstrUTF8(input) {
        return unescape(encodeURIComponent(input))
    }

    function rawMD5(s) {
        return rstrMD5(str2rstrUTF8(s))
    }

    function hexMD5(s) {
        return rstr2hex(rawMD5(s))
    }

    function rawHMACMD5(k, d) {
        return rstrHMACMD5(str2rstrUTF8(k), str2rstrUTF8(d))
    }

    function hexHMACMD5(k, d) {
        return rstr2hex(rawHMACMD5(k, d))
    }

    function md5(string, key, raw) {
        if (!key) {
            if (!raw) {
                return hexMD5(string)
            }
            return rawMD5(string)
        }
        if (!raw) {
            return hexHMACMD5(key, string)
        }
        return rawHMACMD5(key, string)
    }
    if (typeof define === "function" && define.amd) {
        define(function() {
            return md5
        })
    } else if (typeof module === "object" && module.exports) {
        module.exports = md5
    } else {
        $.md5 = md5
    }
})(this);
(function() {
    "use strict";
    var HowlerGlobal = function() {
        this.init()
    };
    HowlerGlobal.prototype = {
        init: function() {
            var self = this || Howler;
            self._counter = 1e3;
            self._codecs = {};
            self._howls = [];
            self._muted = false;
            self._volume = 1;
            self._canPlayEvent = "canplaythrough";
            self._navigator = typeof window !== "undefined" && window.navigator ? window.navigator : null;
            self.masterGain = null;
            self.noAudio = false;
            self.usingWebAudio = true;
            self.autoSuspend = true;
            self.ctx = null;
            self.mobileAutoEnable = true;
            self._setup();
            return self
        },
        volume: function(vol) {
            var self = this || Howler;
            vol = parseFloat(vol);
            if (!self.ctx) {
                setupAudioContext()
            }
            if (typeof vol !== "undefined" && vol >= 0 && vol <= 1) {
                self._volume = vol;
                if (self._muted) {
                    return self
                }
                if (self.usingWebAudio) {
                    self.masterGain.gain.setValueAtTime(vol, Howler.ctx.currentTime)
                }
                for (var i = 0; i < self._howls.length; i++) {
                    if (!self._howls[i]._webAudio) {
                        var ids = self._howls[i]._getSoundIds();
                        for (var j = 0; j < ids.length; j++) {
                            var sound = self._howls[i]._soundById(ids[j]);
                            if (sound && sound._node) {
                                sound._node.volume = sound._volume * vol
                            }
                        }
                    }
                }
                return self
            }
            return self._volume
        },
        mute: function(muted) {
            var self = this || Howler;
            if (!self.ctx) {
                setupAudioContext()
            }
            self._muted = muted;
            if (self.usingWebAudio) {
                self.masterGain.gain.setValueAtTime(muted ? 0 : self._volume, Howler.ctx.currentTime)
            }
            for (var i = 0; i < self._howls.length; i++) {
                if (!self._howls[i]._webAudio) {
                    var ids = self._howls[i]._getSoundIds();
                    for (var j = 0; j < ids.length; j++) {
                        var sound = self._howls[i]._soundById(ids[j]);
                        if (sound && sound._node) {
                            sound._node.muted = muted ? true : sound._muted
                        }
                    }
                }
            }
            return self
        },
        unload: function() {
            var self = this || Howler;
            for (var i = self._howls.length - 1; i >= 0; i--) {
                self._howls[i].unload()
            }
            if (self.usingWebAudio && self.ctx && typeof self.ctx.close !== "undefined") {
                self.ctx.close();
                self.ctx = null;
                setupAudioContext()
            }
            return self
        },
        codecs: function(ext) {
            return (this || Howler)._codecs[ext.replace(/^x-/, "")]
        },
        _setup: function() {
            var self = this || Howler;
            self.state = self.ctx ? self.ctx.state || "running" : "running";
            self._autoSuspend();
            if (!self.usingWebAudio) {
                if (typeof Audio !== "undefined") {
                    try {
                        var test = new Audio;
                        if (typeof test.oncanplaythrough === "undefined") {
                            self._canPlayEvent = "canplay"
                        }
                    } catch (e) {
                        self.noAudio = true
                    }
                } else {
                    self.noAudio = true
                }
            }
            try {
                var test = new Audio;
                if (test.muted) {
                    self.noAudio = true
                }
            } catch (e) {}
            if (!self.noAudio) {
                self._setupCodecs()
            }
            return self
        },
        _setupCodecs: function() {
            var self = this || Howler;
            var audioTest = null;
            try {
                audioTest = typeof Audio !== "undefined" ? new Audio : null
            } catch (err) {
                return self
            }
            if (!audioTest || typeof audioTest.canPlayType !== "function") {
                return self
            }
            var mpegTest = audioTest.canPlayType("audio/mpeg;").replace(/^no$/, "");
            var checkOpera = self._navigator && self._navigator.userAgent.match(/OPR\/([0-6].)/g);
            var isOldOpera = checkOpera && parseInt(checkOpera[0].split("/")[1], 10) < 33;
            self._codecs = {
                mp3: !!(!isOldOpera && (mpegTest || audioTest.canPlayType("audio/mp3;").replace(/^no$/, ""))),
                mpeg: !!mpegTest,
                opus: !!audioTest.canPlayType('audio/ogg; codecs="opus"').replace(/^no$/, ""),
                ogg: !!audioTest.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/, ""),
                oga: !!audioTest.canPlayType('audio/ogg; codecs="vorbis"').replace(/^no$/, ""),
                wav: !!audioTest.canPlayType('audio/wav; codecs="1"').replace(/^no$/, ""),
                aac: !!audioTest.canPlayType("audio/aac;").replace(/^no$/, ""),
                caf: !!audioTest.canPlayType("audio/x-caf;").replace(/^no$/, ""),
                m4a: !!(audioTest.canPlayType("audio/x-m4a;") || audioTest.canPlayType("audio/m4a;") || audioTest.canPlayType("audio/aac;")).replace(/^no$/, ""),
                mp4: !!(audioTest.canPlayType("audio/x-mp4;") || audioTest.canPlayType("audio/mp4;") || audioTest.canPlayType("audio/aac;")).replace(/^no$/, ""),
                weba: !!audioTest.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/, ""),
                webm: !!audioTest.canPlayType('audio/webm; codecs="vorbis"').replace(/^no$/, ""),
                dolby: !!audioTest.canPlayType('audio/mp4; codecs="ec-3"').replace(/^no$/, ""),
                flac: !!(audioTest.canPlayType("audio/x-flac;") || audioTest.canPlayType("audio/flac;")).replace(/^no$/, "")
            };
            return self
        },
        _enableMobileAudio: function() {
            var self = this || Howler;
            var isMobile = /iPhone|iPad|iPod|Android|BlackBerry|BB10|Silk|Mobi/i.test(self._navigator && self._navigator.userAgent);
            var isTouch = !!("ontouchend" in window || self._navigator && self._navigator.maxTouchPoints > 0 || self._navigator && self._navigator.msMaxTouchPoints > 0);
            if (self._mobileEnabled || !self.ctx || !isMobile && !isTouch) {
                return
            }
            self._mobileEnabled = false;
            if (!self._mobileUnloaded && self.ctx.sampleRate !== 44100) {
                self._mobileUnloaded = true;
                self.unload()
            }
            self._scratchBuffer = self.ctx.createBuffer(1, 1, 22050);
            var unlock = function() {
                Howler._autoResume();
                var source = self.ctx.createBufferSource();
                source.buffer = self._scratchBuffer;
                source.connect(self.ctx.destination);
                if (typeof source.start === "undefined") {
                    source.noteOn(0)
                } else {
                    source.start(0)
                }
                if (typeof self.ctx.resume === "function") {
                    self.ctx.resume()
                }
                source.onended = function() {
                    source.disconnect(0);
                    self._mobileEnabled = true;
                    self.mobileAutoEnable = false;
                    document.removeEventListener("touchstart", unlock, true);
                    document.removeEventListener("touchend", unlock, true)
                }
            };
            document.addEventListener("touchstart", unlock, true);
            document.addEventListener("touchend", unlock, true);
            return self
        },
        _autoSuspend: function() {
            var self = this;
            if (!self.autoSuspend || !self.ctx || typeof self.ctx.suspend === "undefined" || !Howler.usingWebAudio) {
                return
            }
            for (var i = 0; i < self._howls.length; i++) {
                if (self._howls[i]._webAudio) {
                    for (var j = 0; j < self._howls[i]._sounds.length; j++) {
                        if (!self._howls[i]._sounds[j]._paused) {
                            return self
                        }
                    }
                }
            }
            if (self._suspendTimer) {
                clearTimeout(self._suspendTimer)
            }
            self._suspendTimer = setTimeout(function() {
                if (!self.autoSuspend) {
                    return
                }
                self._suspendTimer = null;
                self.state = "suspending";
                self.ctx.suspend().then(function() {
                    self.state = "suspended";
                    if (self._resumeAfterSuspend) {
                        delete self._resumeAfterSuspend;
                        self._autoResume()
                    }
                })
            }, 3e4);
            return self
        },
        _autoResume: function() {
            var self = this;
            if (!self.ctx || typeof self.ctx.resume === "undefined" || !Howler.usingWebAudio) {
                return
            }
            if (self.state === "running" && self._suspendTimer) {
                clearTimeout(self._suspendTimer);
                self._suspendTimer = null
            } else if (self.state === "suspended") {
                self.ctx.resume().then(function() {
                    self.state = "running";
                    for (var i = 0; i < self._howls.length; i++) {
                        self._howls[i]._emit("resume")
                    }
                });
                if (self._suspendTimer) {
                    clearTimeout(self._suspendTimer);
                    self._suspendTimer = null
                }
            } else if (self.state === "suspending") {
                self._resumeAfterSuspend = true
            }
            return self
        }
    };
    var Howler = new HowlerGlobal;
    var Howl = function(o) {
        var self = this;
        if (!o.src || o.src.length === 0) {
            console.error("An array of source files must be passed with any new Howl.");
            return
        }
        self.init(o)
    };
    Howl.prototype = {
        init: function(o) {
            var self = this;
            if (!Howler.ctx) {
                setupAudioContext()
            }
            self._autoplay = o.autoplay || false;
            self._format = typeof o.format !== "string" ? o.format : [o.format];
            self._html5 = o.html5 || false;
            self._muted = o.mute || false;
            self._loop = o.loop || false;
            self._pool = o.pool || 5;
            self._preload = typeof o.preload === "boolean" ? o.preload : true;
            self._rate = o.rate || 1;
            self._sprite = o.sprite || {};
            self._src = typeof o.src !== "string" ? o.src : [o.src];
            self._volume = o.volume !== undefined ? o.volume : 1;
            self._xhrWithCredentials = o.xhrWithCredentials || false;
            self._duration = 0;
            self._state = "unloaded";
            self._sounds = [];
            self._endTimers = {};
            self._queue = [];
            self._playLock = false;
            self._onend = o.onend ? [{
                fn: o.onend
            }] : [];
            self._onfade = o.onfade ? [{
                fn: o.onfade
            }] : [];
            self._onload = o.onload ? [{
                fn: o.onload
            }] : [];
            self._onloaderror = o.onloaderror ? [{
                fn: o.onloaderror
            }] : [];
            self._onplayerror = o.onplayerror ? [{
                fn: o.onplayerror
            }] : [];
            self._onpause = o.onpause ? [{
                fn: o.onpause
            }] : [];
            self._onplay = o.onplay ? [{
                fn: o.onplay
            }] : [];
            self._onstop = o.onstop ? [{
                fn: o.onstop
            }] : [];
            self._onmute = o.onmute ? [{
                fn: o.onmute
            }] : [];
            self._onvolume = o.onvolume ? [{
                fn: o.onvolume
            }] : [];
            self._onrate = o.onrate ? [{
                fn: o.onrate
            }] : [];
            self._onseek = o.onseek ? [{
                fn: o.onseek
            }] : [];
            self._onresume = [];
            self._webAudio = Howler.usingWebAudio && !self._html5;
            if (typeof Howler.ctx !== "undefined" && Howler.ctx && Howler.mobileAutoEnable) {
                Howler._enableMobileAudio()
            }
            Howler._howls.push(self);
            if (self._autoplay) {
                self._queue.push({
                    event: "play",
                    action: function() {
                        self.play()
                    }
                })
            }
            if (self._preload) {
                self.load()
            }
            return self
        },
        load: function() {
            var self = this;
            var url = null;
            if (Howler.noAudio) {
                self._emit("loaderror", null, "No audio support.");
                return
            }
            if (typeof self._src === "string") {
                self._src = [self._src]
            }
            for (var i = 0; i < self._src.length; i++) {
                var ext, str;
                if (self._format && self._format[i]) {
                    ext = self._format[i]
                } else {
                    str = self._src[i];
                    if (typeof str !== "string") {
                        self._emit("loaderror", null, "Non-string found in selected audio sources - ignoring.");
                        continue
                    }
                    ext = /^data:audio\/([^;,]+);/i.exec(str);
                    if (!ext) {
                        ext = /\.([^.]+)$/.exec(str.split("?", 1)[0])
                    }
                    if (ext) {
                        ext = ext[1].toLowerCase()
                    }
                }
                if (!ext) {
                    console.warn('No file extension was found. Consider using the "format" property or specify an extension.')
                }
                if (ext && Howler.codecs(ext)) {
                    url = self._src[i];
                    break
                }
            }
            if (!url) {
                self._emit("loaderror", null, "No codec support for selected audio sources.");
                return
            }
            self._src = url;
            self._state = "loading";
            if (window.location.protocol === "https:" && url.slice(0, 5) === "http:") {
                self._html5 = true;
                self._webAudio = false
            }
            new Sound(self);
            if (self._webAudio) {
                loadBuffer(self)
            }
            return self
        },
        play: function(sprite, internal) {
            var self = this;
            var id = null;
            if (typeof sprite === "number") {
                id = sprite;
                sprite = null
            } else if (typeof sprite === "string" && self._state === "loaded" && !self._sprite[sprite]) {
                return null
            } else if (typeof sprite === "undefined") {
                sprite = "__default";
                var num = 0;
                for (var i = 0; i < self._sounds.length; i++) {
                    if (self._sounds[i]._paused && !self._sounds[i]._ended) {
                        num++;
                        id = self._sounds[i]._id
                    }
                }
                if (num === 1) {
                    sprite = null
                } else {
                    id = null
                }
            }
            var sound = id ? self._soundById(id) : self._inactiveSound();
            if (!sound) {
                return null
            }
            if (id && !sprite) {
                sprite = sound._sprite || "__default"
            }
            if (self._state !== "loaded") {
                sound._sprite = sprite;
                sound._ended = false;
                var soundId = sound._id;
                self._queue.push({
                    event: "play",
                    action: function() {
                        self.play(soundId)
                    }
                });
                return soundId
            }
            if (id && !sound._paused) {
                if (!internal) {
                    setTimeout(function() {
                        self._emit("play", sound._id)
                    }, 0)
                }
                return sound._id
            }
            if (self._webAudio) {
                Howler._autoResume()
            }
            var seek = Math.max(0, sound._seek > 0 ? sound._seek : self._sprite[sprite][0] / 1e3);
            var duration = Math.max(0, (self._sprite[sprite][0] + self._sprite[sprite][1]) / 1e3 - seek);
            var timeout = duration * 1e3 / Math.abs(sound._rate);
            sound._paused = false;
            sound._ended = false;
            sound._sprite = sprite;
            sound._seek = seek;
            sound._start = self._sprite[sprite][0] / 1e3;
            sound._stop = (self._sprite[sprite][0] + self._sprite[sprite][1]) / 1e3;
            sound._loop = !!(sound._loop || self._sprite[sprite][2]);
            var node = sound._node;
            if (self._webAudio) {
                var playWebAudio = function() {
                    self._refreshBuffer(sound);
                    var vol = sound._muted || self._muted ? 0 : sound._volume;
                    node.gain.setValueAtTime(vol, Howler.ctx.currentTime);
                    sound._playStart = Howler.ctx.currentTime;
                    if (typeof node.bufferSource.start === "undefined") {
                        sound._loop ? node.bufferSource.noteGrainOn(0, seek, 86400) : node.bufferSource.noteGrainOn(0, seek, duration)
                    } else {
                        sound._loop ? node.bufferSource.start(0, seek, 86400) : node.bufferSource.start(0, seek, duration)
                    }
                    if (timeout !== Infinity) {
                        self._endTimers[sound._id] = setTimeout(self._ended.bind(self, sound), timeout)
                    }
                    if (!internal) {
                        setTimeout(function() {
                            self._emit("play", sound._id)
                        }, 0)
                    }
                };
                if (Howler.state === "running") {
                    playWebAudio()
                } else {
                    self.once("resume", playWebAudio);
                    self._clearTimer(sound._id)
                }
            } else {
                var playHtml5 = function() {
                    node.currentTime = seek;
                    node.muted = sound._muted || self._muted || Howler._muted || node.muted;
                    node.volume = sound._volume * Howler.volume();
                    node.playbackRate = sound._rate;
                    try {
                        var play = node.play();
                        if (typeof Promise !== "undefined" && play instanceof Promise) {
                            self._playLock = true;
                            play.then(function() {
                                self._playLock = false;
                                self._loadQueue()
                            })
                        }
                        if (node.paused) {
                            self._emit("playerror", sound._id, "Playback was unable to start. This is most commonly an issue " + "on mobile devices where playback was not within a user interaction.");
                            return
                        }
                        if (timeout !== Infinity) {
                            self._endTimers[sound._id] = setTimeout(self._ended.bind(self, sound), timeout)
                        }
                        if (!internal) {
                            self._emit("play", sound._id)
                        }
                    } catch (err) {
                        self._emit("playerror", sound._id, err)
                    }
                };
                var loadedNoReadyState = window && window.ejecta || !node.readyState && Howler._navigator.isCocoonJS;
                if (node.readyState === 4 || loadedNoReadyState) {
                    playHtml5()
                } else {
                    var listener = function() {
                        playHtml5();
                        node.removeEventListener(Howler._canPlayEvent, listener, false)
                    };
                    node.addEventListener(Howler._canPlayEvent, listener, false);
                    self._clearTimer(sound._id)
                }
            }
            return sound._id
        },
        pause: function(id) {
            var self = this;
            if (self._state !== "loaded" || self._playLock) {
                self._queue.push({
                    event: "pause",
                    action: function() {
                        self.pause(id)
                    }
                });
                return self
            }
            var ids = self._getSoundIds(id);
            for (var i = 0; i < ids.length; i++) {
                self._clearTimer(ids[i]);
                var sound = self._soundById(ids[i]);
                if (sound && !sound._paused) {
                    sound._seek = self.seek(ids[i]);
                    sound._rateSeek = 0;
                    sound._paused = true;
                    self._stopFade(ids[i]);
                    if (sound._node) {
                        if (self._webAudio) {
                            if (!sound._node.bufferSource) {
                                continue
                            }
                            if (typeof sound._node.bufferSource.stop === "undefined") {
                                sound._node.bufferSource.noteOff(0)
                            } else {
                                sound._node.bufferSource.stop(0)
                            }
                            self._cleanBuffer(sound._node)
                        } else if (!isNaN(sound._node.duration) || sound._node.duration === Infinity) {
                            sound._node.pause()
                        }
                    }
                }
                if (!arguments[1]) {
                    self._emit("pause", sound ? sound._id : null)
                }
            }
            return self
        },
        stop: function(id, internal) {
            var self = this;
            if (self._state !== "loaded") {
                self._queue.push({
                    event: "stop",
                    action: function() {
                        self.stop(id)
                    }
                });
                return self
            }
            var ids = self._getSoundIds(id);
            for (var i = 0; i < ids.length; i++) {
                self._clearTimer(ids[i]);
                var sound = self._soundById(ids[i]);
                if (sound) {
                    sound._seek = sound._start || 0;
                    sound._rateSeek = 0;
                    sound._paused = true;
                    sound._ended = true;
                    self._stopFade(ids[i]);
                    if (sound._node) {
                        if (self._webAudio) {
                            if (sound._node.bufferSource) {
                                if (typeof sound._node.bufferSource.stop === "undefined") {
                                    sound._node.bufferSource.noteOff(0)
                                } else {
                                    sound._node.bufferSource.stop(0)
                                }
                                self._cleanBuffer(sound._node)
                            }
                        } else if (!isNaN(sound._node.duration) || sound._node.duration === Infinity) {
                            sound._node.currentTime = sound._start || 0;
                            sound._node.pause()
                        }
                    }
                    if (!internal) {
                        self._emit("stop", sound._id)
                    }
                }
            }
            return self
        },
        mute: function(muted, id) {
            var self = this;
            if (self._state !== "loaded") {
                self._queue.push({
                    event: "mute",
                    action: function() {
                        self.mute(muted, id)
                    }
                });
                return self
            }
            if (typeof id === "undefined") {
                if (typeof muted === "boolean") {
                    self._muted = muted
                } else {
                    return self._muted
                }
            }
            var ids = self._getSoundIds(id);
            for (var i = 0; i < ids.length; i++) {
                var sound = self._soundById(ids[i]);
                if (sound) {
                    sound._muted = muted;
                    if (sound._interval) {
                        self._stopFade(sound._id)
                    }
                    if (self._webAudio && sound._node) {
                        sound._node.gain.setValueAtTime(muted ? 0 : sound._volume, Howler.ctx.currentTime)
                    } else if (sound._node) {
                        sound._node.muted = Howler._muted ? true : muted
                    }
                    self._emit("mute", sound._id)
                }
            }
            return self
        },
        volume: function() {
            var self = this;
            var args = arguments;
            var vol, id;
            if (args.length === 0) {
                return self._volume
            } else if (args.length === 1 || args.length === 2 && typeof args[1] === "undefined") {
                var ids = self._getSoundIds();
                var index = ids.indexOf(args[0]);
                if (index >= 0) {
                    id = parseInt(args[0], 10)
                } else {
                    vol = parseFloat(args[0])
                }
            } else if (args.length >= 2) {
                vol = parseFloat(args[0]);
                id = parseInt(args[1], 10)
            }
            var sound;
            if (typeof vol !== "undefined" && vol >= 0 && vol <= 1) {
                if (self._state !== "loaded") {
                    self._queue.push({
                        event: "volume",
                        action: function() {
                            self.volume.apply(self, args)
                        }
                    });
                    return self
                }
                if (typeof id === "undefined") {
                    self._volume = vol
                }
                id = self._getSoundIds(id);
                for (var i = 0; i < id.length; i++) {
                    sound = self._soundById(id[i]);
                    if (sound) {
                        sound._volume = vol;
                        if (!args[2]) {
                            self._stopFade(id[i])
                        }
                        if (self._webAudio && sound._node && !sound._muted) {
                            sound._node.gain.setValueAtTime(vol, Howler.ctx.currentTime)
                        } else if (sound._node && !sound._muted) {
                            sound._node.volume = vol * Howler.volume()
                        }
                        self._emit("volume", sound._id)
                    }
                }
            } else {
                sound = id ? self._soundById(id) : self._sounds[0];
                return sound ? sound._volume : 0
            }
            return self
        },
        fade: function(from, to, len, id) {
            var self = this;
            if (self._state !== "loaded") {
                self._queue.push({
                    event: "fade",
                    action: function() {
                        self.fade(from, to, len, id)
                    }
                });
                return self
            }
            self.volume(from, id);
            var ids = self._getSoundIds(id);
            for (var i = 0; i < ids.length; i++) {
                var sound = self._soundById(ids[i]);
                if (sound) {
                    if (!id) {
                        self._stopFade(ids[i])
                    }
                    if (self._webAudio && !sound._muted) {
                        var currentTime = Howler.ctx.currentTime;
                        var end = currentTime + len / 1e3;
                        sound._volume = from;
                        sound._node.gain.setValueAtTime(from, currentTime);
                        sound._node.gain.linearRampToValueAtTime(to, end)
                    }
                    self._startFadeInterval(sound, from, to, len, ids[i], typeof id === "undefined")
                }
            }
            return self
        },
        _startFadeInterval: function(sound, from, to, len, id, isGroup) {
            var self = this;
            var vol = from;
            var diff = to - from;
            var steps = Math.abs(diff / .01);
            var stepLen = Math.max(4, steps > 0 ? len / steps : len);
            var lastTick = Date.now();
            sound._fadeTo = to;
            sound._interval = setInterval(function() {
                var tick = (Date.now() - lastTick) / len;
                lastTick = Date.now();
                vol += diff * tick;
                vol = Math.max(0, vol);
                vol = Math.min(1, vol);
                vol = Math.round(vol * 100) / 100;
                if (self._webAudio) {
                    sound._volume = vol
                } else {
                    self.volume(vol, sound._id, true)
                }
                if (isGroup) {
                    self._volume = vol
                }
                if (to < from && vol <= to || to > from && vol >= to) {
                    clearInterval(sound._interval);
                    sound._interval = null;
                    sound._fadeTo = null;
                    self.volume(to, sound._id);
                    self._emit("fade", sound._id)
                }
            }, stepLen)
        },
        _stopFade: function(id) {
            var self = this;
            var sound = self._soundById(id);
            if (sound && sound._interval) {
                if (self._webAudio) {
                    sound._node.gain.cancelScheduledValues(Howler.ctx.currentTime)
                }
                clearInterval(sound._interval);
                sound._interval = null;
                self.volume(sound._fadeTo, id);
                sound._fadeTo = null;
                self._emit("fade", id)
            }
            return self
        },
        loop: function() {
            var self = this;
            var args = arguments;
            var loop, id, sound;
            if (args.length === 0) {
                return self._loop
            } else if (args.length === 1) {
                if (typeof args[0] === "boolean") {
                    loop = args[0];
                    self._loop = loop
                } else {
                    sound = self._soundById(parseInt(args[0], 10));
                    return sound ? sound._loop : false
                }
            } else if (args.length === 2) {
                loop = args[0];
                id = parseInt(args[1], 10)
            }
            var ids = self._getSoundIds(id);
            for (var i = 0; i < ids.length; i++) {
                sound = self._soundById(ids[i]);
                if (sound) {
                    sound._loop = loop;
                    if (self._webAudio && sound._node && sound._node.bufferSource) {
                        sound._node.bufferSource.loop = loop;
                        if (loop) {
                            sound._node.bufferSource.loopStart = sound._start || 0;
                            sound._node.bufferSource.loopEnd = sound._stop
                        }
                    }
                }
            }
            return self
        },
        rate: function() {
            var self = this;
            var args = arguments;
            var rate, id;
            if (args.length === 0) {
                id = self._sounds[0]._id
            } else if (args.length === 1) {
                var ids = self._getSoundIds();
                var index = ids.indexOf(args[0]);
                if (index >= 0) {
                    id = parseInt(args[0], 10)
                } else {
                    rate = parseFloat(args[0])
                }
            } else if (args.length === 2) {
                rate = parseFloat(args[0]);
                id = parseInt(args[1], 10)
            }
            var sound;
            if (typeof rate === "number") {
                if (self._state !== "loaded") {
                    self._queue.push({
                        event: "rate",
                        action: function() {
                            self.rate.apply(self, args)
                        }
                    });
                    return self
                }
                if (typeof id === "undefined") {
                    self._rate = rate
                }
                id = self._getSoundIds(id);
                for (var i = 0; i < id.length; i++) {
                    sound = self._soundById(id[i]);
                    if (sound) {
                        sound._rateSeek = self.seek(id[i]);
                        sound._playStart = self._webAudio ? Howler.ctx.currentTime : sound._playStart;
                        sound._rate = rate;
                        if (self._webAudio && sound._node && sound._node.bufferSource) {
                            sound._node.bufferSource.playbackRate.setValueAtTime(rate, Howler.ctx.currentTime)
                        } else if (sound._node) {
                            sound._node.playbackRate = rate
                        }
                        var seek = self.seek(id[i]);
                        var duration = (self._sprite[sound._sprite][0] + self._sprite[sound._sprite][1]) / 1e3 - seek;
                        var timeout = duration * 1e3 / Math.abs(sound._rate);
                        if (self._endTimers[id[i]] || !sound._paused) {
                            self._clearTimer(id[i]);
                            self._endTimers[id[i]] = setTimeout(self._ended.bind(self, sound), timeout)
                        }
                        self._emit("rate", sound._id)
                    }
                }
            } else {
                sound = self._soundById(id);
                return sound ? sound._rate : self._rate
            }
            return self
        },
        seek: function() {
            var self = this;
            var args = arguments;
            var seek, id;
            if (args.length === 0) {
                id = self._sounds[0]._id
            } else if (args.length === 1) {
                var ids = self._getSoundIds();
                var index = ids.indexOf(args[0]);
                if (index >= 0) {
                    id = parseInt(args[0], 10)
                } else if (self._sounds.length) {
                    id = self._sounds[0]._id;
                    seek = parseFloat(args[0])
                }
            } else if (args.length === 2) {
                seek = parseFloat(args[0]);
                id = parseInt(args[1], 10)
            }
            if (typeof id === "undefined") {
                return self
            }
            if (self._state !== "loaded") {
                self._queue.push({
                    event: "seek",
                    action: function() {
                        self.seek.apply(self, args)
                    }
                });
                return self
            }
            var sound = self._soundById(id);
            if (sound) {
                if (typeof seek === "number" && seek >= 0) {
                    var playing = self.playing(id);
                    if (playing) {
                        self.pause(id, true)
                    }
                    sound._seek = seek;
                    sound._ended = false;
                    self._clearTimer(id);
                    if (playing) {
                        self.play(id, true)
                    }
                    if (!self._webAudio && sound._node) {
                        sound._node.currentTime = seek
                    }
                    self._emit("seek", id)
                } else {
                    if (self._webAudio) {
                        var realTime = self.playing(id) ? Howler.ctx.currentTime - sound._playStart : 0;
                        var rateSeek = sound._rateSeek ? sound._rateSeek - sound._seek : 0;
                        return sound._seek + (rateSeek + realTime * Math.abs(sound._rate))
                    } else {
                        return sound._node.currentTime
                    }
                }
            }
            return self
        },
        playing: function(id) {
            var self = this;
            if (typeof id === "number") {
                var sound = self._soundById(id);
                return sound ? !sound._paused : false
            }
            for (var i = 0; i < self._sounds.length; i++) {
                if (!self._sounds[i]._paused) {
                    return true
                }
            }
            return false
        },
        duration: function(id) {
            var self = this;
            var duration = self._duration;
            var sound = self._soundById(id);
            if (sound) {
                duration = self._sprite[sound._sprite][1] / 1e3
            }
            return duration
        },
        state: function() {
            return this._state
        },
        unload: function() {
            var self = this;
            var sounds = self._sounds;
            for (var i = 0; i < sounds.length; i++) {
                if (!sounds[i]._paused) {
                    self.stop(sounds[i]._id)
                }
                if (!self._webAudio) {
                    var checkIE = /MSIE |Trident\//.test(Howler._navigator && Howler._navigator.userAgent);
                    if (!checkIE) {
                        sounds[i]._node.src = "data:audio/wav;base64,UklGRigAAABXQVZFZm10IBIAAAABAAEARKwAAIhYAQACABAAAABkYXRhAgAAAAEA"
                    }
                    sounds[i]._node.removeEventListener("error", sounds[i]._errorFn, false);
                    sounds[i]._node.removeEventListener(Howler._canPlayEvent, sounds[i]._loadFn, false)
                }
                delete sounds[i]._node;
                self._clearTimer(sounds[i]._id);
                var index = Howler._howls.indexOf(self);
                if (index >= 0) {
                    Howler._howls.splice(index, 1)
                }
            }
            var remCache = true;
            for (i = 0; i < Howler._howls.length; i++) {
                if (Howler._howls[i]._src === self._src) {
                    remCache = false;
                    break
                }
            }
            if (cache && remCache) {
                delete cache[self._src]
            }
            Howler.noAudio = false;
            self._state = "unloaded";
            self._sounds = [];
            self = null;
            return null
        },
        on: function(event, fn, id, once) {
            var self = this;
            var events = self["_on" + event];
            if (typeof fn === "function") {
                events.push(once ? {
                    id: id,
                    fn: fn,
                    once: once
                } : {
                    id: id,
                    fn: fn
                })
            }
            return self
        },
        off: function(event, fn, id) {
            var self = this;
            var events = self["_on" + event];
            var i = 0;
            if (typeof fn === "number") {
                id = fn;
                fn = null
            }
            if (fn || id) {
                for (i = 0; i < events.length; i++) {
                    var isId = id === events[i].id;
                    if (fn === events[i].fn && isId || !fn && isId) {
                        events.splice(i, 1);
                        break
                    }
                }
            } else if (event) {
                self["_on" + event] = []
            } else {
                var keys = Object.keys(self);
                for (i = 0; i < keys.length; i++) {
                    if (keys[i].indexOf("_on") === 0 && Array.isArray(self[keys[i]])) {
                        self[keys[i]] = []
                    }
                }
            }
            return self
        },
        once: function(event, fn, id) {
            var self = this;
            self.on(event, fn, id, 1);
            return self
        },
        _emit: function(event, id, msg) {
            var self = this;
            var events = self["_on" + event];
            for (var i = events.length - 1; i >= 0; i--) {
                if (!events[i].id || events[i].id === id || event === "load") {
                    setTimeout(function(fn) {
                        fn.call(this, id, msg)
                    }.bind(self, events[i].fn), 0);
                    if (events[i].once) {
                        self.off(event, events[i].fn, events[i].id)
                    }
                }
            }
            return self
        },
        _loadQueue: function() {
            var self = this;
            if (self._queue.length > 0) {
                var task = self._queue[0];
                self.once(task.event, function() {
                    self._queue.shift();
                    self._loadQueue()
                });
                task.action()
            }
            return self
        },
        _ended: function(sound) {
            var self = this;
            var sprite = sound._sprite;
            if (!self._webAudio && sound._node && !sound._node.paused && !sound._node.ended && sound._node.currentTime < sound._stop) {
                setTimeout(self._ended.bind(self, sound), 100);
                return self
            }
            var loop = !!(sound._loop || self._sprite[sprite][2]);
            self._emit("end", sound._id);
            if (!self._webAudio && loop) {
                self.stop(sound._id, true).play(sound._id)
            }
            if (self._webAudio && loop) {
                self._emit("play", sound._id);
                sound._seek = sound._start || 0;
                sound._rateSeek = 0;
                sound._playStart = Howler.ctx.currentTime;
                var timeout = (sound._stop - sound._start) * 1e3 / Math.abs(sound._rate);
                self._endTimers[sound._id] = setTimeout(self._ended.bind(self, sound), timeout)
            }
            if (self._webAudio && !loop) {
                sound._paused = true;
                sound._ended = true;
                sound._seek = sound._start || 0;
                sound._rateSeek = 0;
                self._clearTimer(sound._id);
                self._cleanBuffer(sound._node);
                Howler._autoSuspend()
            }
            if (!self._webAudio && !loop) {
                self.stop(sound._id)
            }
            return self
        },
        _clearTimer: function(id) {
            var self = this;
            if (self._endTimers[id]) {
                clearTimeout(self._endTimers[id]);
                delete self._endTimers[id]
            }
            return self
        },
        _soundById: function(id) {
            var self = this;
            for (var i = 0; i < self._sounds.length; i++) {
                if (id === self._sounds[i]._id) {
                    return self._sounds[i]
                }
            }
            return null
        },
        _inactiveSound: function() {
            var self = this;
            self._drain();
            for (var i = 0; i < self._sounds.length; i++) {
                if (self._sounds[i]._ended) {
                    return self._sounds[i].reset()
                }
            }
            return new Sound(self)
        },
        _drain: function() {
            var self = this;
            var limit = self._pool;
            var cnt = 0;
            var i = 0;
            if (self._sounds.length < limit) {
                return
            }
            for (i = 0; i < self._sounds.length; i++) {
                if (self._sounds[i]._ended) {
                    cnt++
                }
            }
            for (i = self._sounds.length - 1; i >= 0; i--) {
                if (cnt <= limit) {
                    return
                }
                if (self._sounds[i]._ended) {
                    if (self._webAudio && self._sounds[i]._node) {
                        self._sounds[i]._node.disconnect(0)
                    }
                    self._sounds.splice(i, 1);
                    cnt--
                }
            }
        },
        _getSoundIds: function(id) {
            var self = this;
            if (typeof id === "undefined") {
                var ids = [];
                for (var i = 0; i < self._sounds.length; i++) {
                    ids.push(self._sounds[i]._id)
                }
                return ids
            } else {
                return [id]
            }
        },
        _refreshBuffer: function(sound) {
            var self = this;
            sound._node.bufferSource = Howler.ctx.createBufferSource();
            sound._node.bufferSource.buffer = cache[self._src];
            if (sound._panner) {
                sound._node.bufferSource.connect(sound._panner)
            } else {
                sound._node.bufferSource.connect(sound._node)
            }
            sound._node.bufferSource.loop = sound._loop;
            if (sound._loop) {
                sound._node.bufferSource.loopStart = sound._start || 0;
                sound._node.bufferSource.loopEnd = sound._stop
            }
            sound._node.bufferSource.playbackRate.setValueAtTime(sound._rate, Howler.ctx.currentTime);
            return self
        },
        _cleanBuffer: function(node) {
            var self = this;
            if (Howler._scratchBuffer) {
                node.bufferSource.onended = null;
                node.bufferSource.disconnect(0);
                try {
                    node.bufferSource.buffer = Howler._scratchBuffer
                } catch (e) {}
            }
            node.bufferSource = null;
            return self
        }
    };
    var Sound = function(howl) {
        this._parent = howl;
        this.init()
    };
    Sound.prototype = {
        init: function() {
            var self = this;
            var parent = self._parent;
            self._muted = parent._muted;
            self._loop = parent._loop;
            self._volume = parent._volume;
            self._rate = parent._rate;
            self._seek = 0;
            self._paused = true;
            self._ended = true;
            self._sprite = "__default";
            self._id = ++Howler._counter;
            parent._sounds.push(self);
            self.create();
            return self
        },
        create: function() {
            var self = this;
            var parent = self._parent;
            var volume = Howler._muted || self._muted || self._parent._muted ? 0 : self._volume;
            if (parent._webAudio) {
                self._node = typeof Howler.ctx.createGain === "undefined" ? Howler.ctx.createGainNode() : Howler.ctx.createGain();
                self._node.gain.setValueAtTime(volume, Howler.ctx.currentTime);
                self._node.paused = true;
                self._node.connect(Howler.masterGain)
            } else {
                self._node = new Audio;
                self._errorFn = self._errorListener.bind(self);
                self._node.addEventListener("error", self._errorFn, false);
                self._loadFn = self._loadListener.bind(self);
                self._node.addEventListener(Howler._canPlayEvent, self._loadFn, false);
                self._node.src = parent._src;
                self._node.preload = "auto";
                self._node.volume = volume * Howler.volume();
                self._node.load()
            }
            return self
        },
        reset: function() {
            var self = this;
            var parent = self._parent;
            self._muted = parent._muted;
            self._loop = parent._loop;
            self._volume = parent._volume;
            self._rate = parent._rate;
            self._seek = 0;
            self._rateSeek = 0;
            self._paused = true;
            self._ended = true;
            self._sprite = "__default";
            self._id = ++Howler._counter;
            return self
        },
        _errorListener: function() {
            var self = this;
            self._parent._emit("loaderror", self._id, self._node.error ? self._node.error.code : 0);
            self._node.removeEventListener("error", self._errorFn, false)
        },
        _loadListener: function() {
            var self = this;
            var parent = self._parent;
            parent._duration = Math.ceil(self._node.duration * 10) / 10;
            if (Object.keys(parent._sprite).length === 0) {
                parent._sprite = {
                    __default: [0, parent._duration * 1e3]
                }
            }
            if (parent._state !== "loaded") {
                parent._state = "loaded";
                parent._emit("load");
                parent._loadQueue()
            }
            self._node.removeEventListener(Howler._canPlayEvent, self._loadFn, false)
        }
    };
    var cache = {};
    var loadBuffer = function(self) {
        var url = self._src;
        if (cache[url]) {
            self._duration = cache[url].duration;
            loadSound(self);
            return
        }
        if (/^data:[^;]+;base64,/.test(url)) {
            var data = atob(url.split(",")[1]);
            var dataView = new Uint8Array(data.length);
            for (var i = 0; i < data.length; ++i) {
                dataView[i] = data.charCodeAt(i)
            }
            decodeAudioData(dataView.buffer, self)
        } else {
            var xhr = new XMLHttpRequest;
            xhr.open("GET", url, true);
            xhr.withCredentials = self._xhrWithCredentials;
            xhr.responseType = "arraybuffer";
            xhr.onload = function() {
                var code = (xhr.status + "")[0];
                if (code !== "0" && code !== "2" && code !== "3") {
                    self._emit("loaderror", null, "Failed loading audio file with status: " + xhr.status + ".");
                    return
                }
                decodeAudioData(xhr.response, self)
            };
            xhr.onerror = function() {
                if (self._webAudio) {
                    self._html5 = true;
                    self._webAudio = false;
                    self._sounds = [];
                    delete cache[url];
                    self.load()
                }
            };
            safeXhrSend(xhr)
        }
    };
    var safeXhrSend = function(xhr) {
        try {
            xhr.send()
        } catch (e) {
            xhr.onerror()
        }
    };
    var decodeAudioData = function(arraybuffer, self) {
        Howler.ctx.decodeAudioData(arraybuffer, function(buffer) {
            if (buffer && self._sounds.length > 0) {
                cache[self._src] = buffer;
                loadSound(self, buffer)
            }
        }, function() {
            self._emit("loaderror", null, "Decoding audio data failed.")
        })
    };
    var loadSound = function(self, buffer) {
        if (buffer && !self._duration) {
            self._duration = buffer.duration
        }
        if (Object.keys(self._sprite).length === 0) {
            self._sprite = {
                __default: [0, self._duration * 1e3]
            }
        }
        if (self._state !== "loaded") {
            self._state = "loaded";
            self._emit("load");
            self._loadQueue()
        }
    };
    var setupAudioContext = function() {
        try {
            if (typeof AudioContext !== "undefined") {
                Howler.ctx = new AudioContext
            } else if (typeof webkitAudioContext !== "undefined") {
                Howler.ctx = new webkitAudioContext
            } else {
                Howler.usingWebAudio = false
            }
        } catch (e) {
            Howler.usingWebAudio = false
        }
        var iOS = /iP(hone|od|ad)/.test(Howler._navigator && Howler._navigator.platform);
        var appVersion = Howler._navigator && Howler._navigator.appVersion.match(/OS (\d+)_(\d+)_?(\d+)?/);
        var version = appVersion ? parseInt(appVersion[1], 10) : null;
        if (iOS && version && version < 9) {
            var safari = /safari/.test(Howler._navigator && Howler._navigator.userAgent.toLowerCase());
            if (Howler._navigator && Howler._navigator.standalone && !safari || Howler._navigator && !Howler._navigator.standalone && !safari) {
                Howler.usingWebAudio = false
            }
        }
        if (Howler.usingWebAudio) {
            Howler.masterGain = typeof Howler.ctx.createGain === "undefined" ? Howler.ctx.createGainNode() : Howler.ctx.createGain();
            Howler.masterGain.gain.setValueAtTime(Howler._muted ? 0 : 1, Howler.ctx.currentTime);
            Howler.masterGain.connect(Howler.ctx.destination)
        }
        Howler._setup()
    };
    if (typeof define === "function" && define.amd) {
        define([], function() {
            return {
                Howler: Howler,
                Howl: Howl
            }
        })
    }
    if (typeof exports !== "undefined") {
        exports.Howler = Howler;
        exports.Howl = Howl
    }
    if (typeof window !== "undefined") {
        window.HowlerGlobal = HowlerGlobal;
        window.Howler = Howler;
        window.Howl = Howl;
        window.Sound = Sound
    } else if (typeof global !== "undefined") {
        global.HowlerGlobal = HowlerGlobal;
        global.Howler = Howler;
        global.Howl = Howl;
        global.Sound = Sound
    }
})();
(function() {
    "use strict";
    HowlerGlobal.prototype._pos = [0, 0, 0];
    HowlerGlobal.prototype._orientation = [0, 0, -1, 0, 1, 0];
    HowlerGlobal.prototype.stereo = function(pan) {
        var self = this;
        if (!self.ctx || !self.ctx.listener) {
            return self
        }
        for (var i = self._howls.length - 1; i >= 0; i--) {
            self._howls[i].stereo(pan)
        }
        return self
    };
    HowlerGlobal.prototype.pos = function(x, y, z) {
        var self = this;
        if (!self.ctx || !self.ctx.listener) {
            return self
        }
        y = typeof y !== "number" ? self._pos[1] : y;
        z = typeof z !== "number" ? self._pos[2] : z;
        if (typeof x === "number") {
            self._pos = [x, y, z];
            self.ctx.listener.setPosition(self._pos[0], self._pos[1], self._pos[2])
        } else {
            return self._pos
        }
        return self
    };
    HowlerGlobal.prototype.orientation = function(x, y, z, xUp, yUp, zUp) {
        var self = this;
        if (!self.ctx || !self.ctx.listener) {
            return self
        }
        var or = self._orientation;
        y = typeof y !== "number" ? or[1] : y;
        z = typeof z !== "number" ? or[2] : z;
        xUp = typeof xUp !== "number" ? or[3] : xUp;
        yUp = typeof yUp !== "number" ? or[4] : yUp;
        zUp = typeof zUp !== "number" ? or[5] : zUp;
        if (typeof x === "number") {
            self._orientation = [x, y, z, xUp, yUp, zUp];
            self.ctx.listener.setOrientation(x, y, z, xUp, yUp, zUp)
        } else {
            return or
        }
        return self
    };
    Howl.prototype.init = function(_super) {
        return function(o) {
            var self = this;
            self._orientation = o.orientation || [1, 0, 0];
            self._stereo = o.stereo || null;
            self._pos = o.pos || null;
            self._pannerAttr = {
                coneInnerAngle: typeof o.coneInnerAngle !== "undefined" ? o.coneInnerAngle : 360,
                coneOuterAngle: typeof o.coneOuterAngle !== "undefined" ? o.coneOuterAngle : 360,
                coneOuterGain: typeof o.coneOuterGain !== "undefined" ? o.coneOuterGain : 0,
                distanceModel: typeof o.distanceModel !== "undefined" ? o.distanceModel : "inverse",
                maxDistance: typeof o.maxDistance !== "undefined" ? o.maxDistance : 1e4,
                panningModel: typeof o.panningModel !== "undefined" ? o.panningModel : "HRTF",
                refDistance: typeof o.refDistance !== "undefined" ? o.refDistance : 1,
                rolloffFactor: typeof o.rolloffFactor !== "undefined" ? o.rolloffFactor : 1
            };
            self._onstereo = o.onstereo ? [{
                fn: o.onstereo
            }] : [];
            self._onpos = o.onpos ? [{
                fn: o.onpos
            }] : [];
            self._onorientation = o.onorientation ? [{
                fn: o.onorientation
            }] : [];
            return _super.call(this, o)
        }
    }(Howl.prototype.init);
    Howl.prototype.stereo = function(pan, id) {
        var self = this;
        if (!self._webAudio) {
            return self
        }
        if (self._state !== "loaded") {
            self._queue.push({
                event: "stereo",
                action: function() {
                    self.stereo(pan, id)
                }
            });
            return self
        }
        var pannerType = typeof Howler.ctx.createStereoPanner === "undefined" ? "spatial" : "stereo";
        if (typeof id === "undefined") {
            if (typeof pan === "number") {
                self._stereo = pan;
                self._pos = [pan, 0, 0]
            } else {
                return self._stereo
            }
        }
        var ids = self._getSoundIds(id);
        for (var i = 0; i < ids.length; i++) {
            var sound = self._soundById(ids[i]);
            if (sound) {
                if (typeof pan === "number") {
                    sound._stereo = pan;
                    sound._pos = [pan, 0, 0];
                    if (sound._node) {
                        sound._pannerAttr.panningModel = "equalpower";
                        if (!sound._panner || !sound._panner.pan) {
                            setupPanner(sound, pannerType)
                        }
                        if (pannerType === "spatial") {
                            sound._panner.setPosition(pan, 0, 0)
                        } else {
                            sound._panner.pan.setValueAtTime(pan, Howler.ctx.currentTime)
                        }
                    }
                    self._emit("stereo", sound._id)
                } else {
                    return sound._stereo
                }
            }
        }
        return self
    };
    Howl.prototype.pos = function(x, y, z, id) {
        var self = this;
        if (!self._webAudio) {
            return self
        }
        if (self._state !== "loaded") {
            self._queue.push({
                event: "pos",
                action: function() {
                    self.pos(x, y, z, id)
                }
            });
            return self
        }
        y = typeof y !== "number" ? 0 : y;
        z = typeof z !== "number" ? -.5 : z;
        if (typeof id === "undefined") {
            if (typeof x === "number") {
                self._pos = [x, y, z]
            } else {
                return self._pos
            }
        }
        var ids = self._getSoundIds(id);
        for (var i = 0; i < ids.length; i++) {
            var sound = self._soundById(ids[i]);
            if (sound) {
                if (typeof x === "number") {
                    sound._pos = [x, y, z];
                    if (sound._node) {
                        if (!sound._panner || sound._panner.pan) {
                            setupPanner(sound, "spatial")
                        }
                        sound._panner.setPosition(x, y, z)
                    }
                    self._emit("pos", sound._id)
                } else {
                    return sound._pos
                }
            }
        }
        return self
    };
    Howl.prototype.orientation = function(x, y, z, id) {
        var self = this;
        if (!self._webAudio) {
            return self
        }
        if (self._state !== "loaded") {
            self._queue.push({
                event: "orientation",
                action: function() {
                    self.orientation(x, y, z, id)
                }
            });
            return self
        }
        y = typeof y !== "number" ? self._orientation[1] : y;
        z = typeof z !== "number" ? self._orientation[2] : z;
        if (typeof id === "undefined") {
            if (typeof x === "number") {
                self._orientation = [x, y, z]
            } else {
                return self._orientation
            }
        }
        var ids = self._getSoundIds(id);
        for (var i = 0; i < ids.length; i++) {
            var sound = self._soundById(ids[i]);
            if (sound) {
                if (typeof x === "number") {
                    sound._orientation = [x, y, z];
                    if (sound._node) {
                        if (!sound._panner) {
                            if (!sound._pos) {
                                sound._pos = self._pos || [0, 0, -.5]
                            }
                            setupPanner(sound, "spatial")
                        }
                        sound._panner.setOrientation(x, y, z)
                    }
                    self._emit("orientation", sound._id)
                } else {
                    return sound._orientation
                }
            }
        }
        return self
    };
    Howl.prototype.pannerAttr = function() {
        var self = this;
        var args = arguments;
        var o, id, sound;
        if (!self._webAudio) {
            return self
        }
        if (args.length === 0) {
            return self._pannerAttr
        } else if (args.length === 1) {
            if (typeof args[0] === "object") {
                o = args[0];
                if (typeof id === "undefined") {
                    if (!o.pannerAttr) {
                        o.pannerAttr = {
                            coneInnerAngle: o.coneInnerAngle,
                            coneOuterAngle: o.coneOuterAngle,
                            coneOuterGain: o.coneOuterGain,
                            distanceModel: o.distanceModel,
                            maxDistance: o.maxDistance,
                            refDistance: o.refDistance,
                            rolloffFactor: o.rolloffFactor,
                            panningModel: o.panningModel
                        }
                    }
                    self._pannerAttr = {
                        coneInnerAngle: typeof o.pannerAttr.coneInnerAngle !== "undefined" ? o.pannerAttr.coneInnerAngle : self._coneInnerAngle,
                        coneOuterAngle: typeof o.pannerAttr.coneOuterAngle !== "undefined" ? o.pannerAttr.coneOuterAngle : self._coneOuterAngle,
                        coneOuterGain: typeof o.pannerAttr.coneOuterGain !== "undefined" ? o.pannerAttr.coneOuterGain : self._coneOuterGain,
                        distanceModel: typeof o.pannerAttr.distanceModel !== "undefined" ? o.pannerAttr.distanceModel : self._distanceModel,
                        maxDistance: typeof o.pannerAttr.maxDistance !== "undefined" ? o.pannerAttr.maxDistance : self._maxDistance,
                        refDistance: typeof o.pannerAttr.refDistance !== "undefined" ? o.pannerAttr.refDistance : self._refDistance,
                        rolloffFactor: typeof o.pannerAttr.rolloffFactor !== "undefined" ? o.pannerAttr.rolloffFactor : self._rolloffFactor,
                        panningModel: typeof o.pannerAttr.panningModel !== "undefined" ? o.pannerAttr.panningModel : self._panningModel
                    }
                }
            } else {
                sound = self._soundById(parseInt(args[0], 10));
                return sound ? sound._pannerAttr : self._pannerAttr
            }
        } else if (args.length === 2) {
            o = args[0];
            id = parseInt(args[1], 10)
        }
        var ids = self._getSoundIds(id);
        for (var i = 0; i < ids.length; i++) {
            sound = self._soundById(ids[i]);
            if (sound) {
                var pa = sound._pannerAttr;
                pa = {
                    coneInnerAngle: typeof o.coneInnerAngle !== "undefined" ? o.coneInnerAngle : pa.coneInnerAngle,
                    coneOuterAngle: typeof o.coneOuterAngle !== "undefined" ? o.coneOuterAngle : pa.coneOuterAngle,
                    coneOuterGain: typeof o.coneOuterGain !== "undefined" ? o.coneOuterGain : pa.coneOuterGain,
                    distanceModel: typeof o.distanceModel !== "undefined" ? o.distanceModel : pa.distanceModel,
                    maxDistance: typeof o.maxDistance !== "undefined" ? o.maxDistance : pa.maxDistance,
                    refDistance: typeof o.refDistance !== "undefined" ? o.refDistance : pa.refDistance,
                    rolloffFactor: typeof o.rolloffFactor !== "undefined" ? o.rolloffFactor : pa.rolloffFactor,
                    panningModel: typeof o.panningModel !== "undefined" ? o.panningModel : pa.panningModel
                };
                var panner = sound._panner;
                if (panner) {
                    panner.coneInnerAngle = pa.coneInnerAngle;
                    panner.coneOuterAngle = pa.coneOuterAngle;
                    panner.coneOuterGain = pa.coneOuterGain;
                    panner.distanceModel = pa.distanceModel;
                    panner.maxDistance = pa.maxDistance;
                    panner.refDistance = pa.refDistance;
                    panner.rolloffFactor = pa.rolloffFactor;
                    panner.panningModel = pa.panningModel
                } else {
                    if (!sound._pos) {
                        sound._pos = self._pos || [0, 0, -.5]
                    }
                    setupPanner(sound, "spatial")
                }
            }
        }
        return self
    };
    Sound.prototype.init = function(_super) {
        return function() {
            var self = this;
            var parent = self._parent;
            self._orientation = parent._orientation;
            self._stereo = parent._stereo;
            self._pos = parent._pos;
            self._pannerAttr = parent._pannerAttr;
            _super.call(this);
            if (self._stereo) {
                parent.stereo(self._stereo)
            } else if (self._pos) {
                parent.pos(self._pos[0], self._pos[1], self._pos[2], self._id)
            }
        }
    }(Sound.prototype.init);
    Sound.prototype.reset = function(_super) {
        return function() {
            var self = this;
            var parent = self._parent;
            self._orientation = parent._orientation;
            self._pos = parent._pos;
            self._pannerAttr = parent._pannerAttr;
            return _super.call(this)
        }
    }(Sound.prototype.reset);
    var setupPanner = function(sound, type) {
        type = type || "spatial";
        if (type === "spatial") {
            sound._panner = Howler.ctx.createPanner();
            sound._panner.coneInnerAngle = sound._pannerAttr.coneInnerAngle;
            sound._panner.coneOuterAngle = sound._pannerAttr.coneOuterAngle;
            sound._panner.coneOuterGain = sound._pannerAttr.coneOuterGain;
            sound._panner.distanceModel = sound._pannerAttr.distanceModel;
            sound._panner.maxDistance = sound._pannerAttr.maxDistance;
            sound._panner.refDistance = sound._pannerAttr.refDistance;
            sound._panner.rolloffFactor = sound._pannerAttr.rolloffFactor;
            sound._panner.panningModel = sound._pannerAttr.panningModel;
            sound._panner.setPosition(sound._pos[0], sound._pos[1], sound._pos[2]);
            sound._panner.setOrientation(sound._orientation[0], sound._orientation[1], sound._orientation[2])
        } else {
            sound._panner = Howler.ctx.createStereoPanner();
            sound._panner.pan.setValueAtTime(sound._stereo, Howler.ctx.currentTime)
        }
        sound._panner.connect(sound._node);
        if (!sound._paused) {
            sound._parent.pause(sound._id, true).play(sound._id, true)
        }
    }
})();
var _Group = function() {
    this._tweens = {};
    this._tweensAddedDuringUpdate = {}
};
_Group.prototype = {
    getAll: function() {
        return Object.keys(this._tweens).map(function(tweenId) {
            return this._tweens[tweenId]
        }.bind(this))
    },
    removeAll: function() {
        this._tweens = {}
    },
    add: function(tween) {
        this._tweens[tween.getId()] = tween;
        this._tweensAddedDuringUpdate[tween.getId()] = tween
    },
    remove: function(tween) {
        delete this._tweens[tween.getId()];
        delete this._tweensAddedDuringUpdate[tween.getId()]
    },
    update: function(time, preserve) {
        var tweenIds = Object.keys(this._tweens);
        if (tweenIds.length === 0) {
            return false
        }
        time = time !== undefined ? time : TWEEN.now();
        while (tweenIds.length > 0) {
            this._tweensAddedDuringUpdate = {};
            for (var i = 0; i < tweenIds.length; i++) {
                var tween = this._tweens[tweenIds[i]];
                if (tween && tween.update(time) === false) {
                    tween._isPlaying = false;
                    if (!preserve) {
                        delete this._tweens[tweenIds[i]]
                    }
                }
            }
            tweenIds = Object.keys(this._tweensAddedDuringUpdate)
        }
        return true
    }
};
var TWEEN = new _Group;
TWEEN.Group = _Group;
TWEEN._nextId = 0;
TWEEN.nextId = function() {
    return TWEEN._nextId++
};
if (typeof window === "undefined" && typeof process !== "undefined") {
    TWEEN.now = function() {
        var time = process.hrtime();
        return time[0] * 1e3 + time[1] / 1e6
    }
} else if (typeof window !== "undefined" && window.performance !== undefined && window.performance.now !== undefined) {
    TWEEN.now = window.performance.now.bind(window.performance)
} else if (Date.now !== undefined) {
    TWEEN.now = Date.now
} else {
    TWEEN.now = function() {
        return (new Date).getTime()
    }
}
TWEEN.Tween = function(object, group) {
    this._object = object;
    this._valuesStart = {};
    this._valuesEnd = {};
    this._valuesStartRepeat = {};
    this._duration = 1e3;
    this._repeat = 0;
    this._repeatDelayTime = undefined;
    this._yoyo = false;
    this._isPlaying = false;
    this._reversed = false;
    this._delayTime = 0;
    this._startTime = null;
    this._easingFunction = TWEEN.Easing.Linear.None;
    this._interpolationFunction = TWEEN.Interpolation.Linear;
    this._chainedTweens = [];
    this._onStartCallback = null;
    this._onStartCallbackFired = false;
    this._onUpdateCallback = null;
    this._onCompleteCallback = null;
    this._onStopCallback = null;
    this._group = group || TWEEN;
    this._id = TWEEN.nextId()
};
TWEEN.Tween.prototype = {
    getId: function getId() {
        return this._id
    },
    isPlaying: function isPlaying() {
        return this._isPlaying
    },
    to: function to(properties, duration) {
        this._valuesEnd = properties;
        if (duration !== undefined) {
            this._duration = duration
        }
        return this
    },
    start: function start(time) {
        this._group.add(this);
        this._isPlaying = true;
        this._onStartCallbackFired = false;
        this._startTime = time !== undefined ? typeof time === "string" ? TWEEN.now() + parseFloat(time) : time : TWEEN.now();
        this._startTime += this._delayTime;
        for (var property in this._valuesEnd) {
            if (this._valuesEnd[property] instanceof Array) {
                if (this._valuesEnd[property].length === 0) {
                    continue
                }
                this._valuesEnd[property] = [this._object[property]].concat(this._valuesEnd[property])
            }
            if (this._object[property] === undefined) {
                continue
            }
            this._valuesStart[property] = this._object[property];
            if (this._valuesStart[property] instanceof Array === false) {
                this._valuesStart[property] *= 1
            }
            this._valuesStartRepeat[property] = this._valuesStart[property] || 0
        }
        return this
    },
    stop: function stop() {
        if (!this._isPlaying) {
            return this
        }
        this._group.remove(this);
        this._isPlaying = false;
        if (this._onStopCallback !== null) {
            this._onStopCallback(this._object)
        }
        this.stopChainedTweens();
        return this
    },
    end: function end() {
        this.update(this._startTime + this._duration);
        return this
    },
    stopChainedTweens: function stopChainedTweens() {
        for (var i = 0, numChainedTweens = this._chainedTweens.length; i < numChainedTweens; i++) {
            this._chainedTweens[i].stop()
        }
    },
    delay: function delay(amount) {
        this._delayTime = amount;
        return this
    },
    repeat: function repeat(times) {
        this._repeat = times;
        return this
    },
    repeatDelay: function repeatDelay(amount) {
        this._repeatDelayTime = amount;
        return this
    },
    yoyo: function yoyo(yoyo) {
        this._yoyo = yoyo;
        return this
    },
    easing: function easing(easing) {
        this._easingFunction = easing;
        return this
    },
    interpolation: function interpolation(interpolation) {
        this._interpolationFunction = interpolation;
        return this
    },
    chain: function chain() {
        this._chainedTweens = arguments;
        return this
    },
    onStart: function onStart(callback) {
        this._onStartCallback = callback;
        return this
    },
    onUpdate: function onUpdate(callback) {
        this._onUpdateCallback = callback;
        return this
    },
    onComplete: function onComplete(callback) {
        this._onCompleteCallback = callback;
        return this
    },
    onStop: function onStop(callback) {
        this._onStopCallback = callback;
        return this
    },
    update: function update(time) {
        var property;
        var elapsed;
        var value;
        if (time < this._startTime) {
            return true
        }
        if (this._onStartCallbackFired === false) {
            if (this._onStartCallback !== null) {
                this._onStartCallback(this._object)
            }
            this._onStartCallbackFired = true
        }
        elapsed = (time - this._startTime) / this._duration;
        elapsed = elapsed > 1 ? 1 : elapsed;
        value = this._easingFunction(elapsed);
        for (property in this._valuesEnd) {
            if (this._valuesStart[property] === undefined) {
                continue
            }
            var start = this._valuesStart[property] || 0;
            var end = this._valuesEnd[property];
            if (end instanceof Array) {
                this._object[property] = this._interpolationFunction(end, value)
            } else {
                if (typeof end === "string") {
                    if (end.charAt(0) === "+" || end.charAt(0) === "-") {
                        end = start + parseFloat(end)
                    } else {
                        end = parseFloat(end)
                    }
                }
                if (typeof end === "number") {
                    this._object[property] = start + (end - start) * value
                }
            }
        }
        if (this._onUpdateCallback !== null) {
            this._onUpdateCallback(this._object)
        }
        if (elapsed === 1) {
            if (this._repeat > 0) {
                if (isFinite(this._repeat)) {
                    this._repeat--
                }
                for (property in this._valuesStartRepeat) {
                    if (typeof this._valuesEnd[property] === "string") {
                        this._valuesStartRepeat[property] = this._valuesStartRepeat[property] + parseFloat(this._valuesEnd[property])
                    }
                    if (this._yoyo) {
                        var tmp = this._valuesStartRepeat[property];
                        this._valuesStartRepeat[property] = this._valuesEnd[property];
                        this._valuesEnd[property] = tmp
                    }
                    this._valuesStart[property] = this._valuesStartRepeat[property]
                }
                if (this._yoyo) {
                    this._reversed = !this._reversed
                }
                if (this._repeatDelayTime !== undefined) {
                    this._startTime = time + this._repeatDelayTime
                } else {
                    this._startTime = time + this._delayTime
                }
                return true
            } else {
                if (this._onCompleteCallback !== null) {
                    this._onCompleteCallback(this._object)
                }
                for (var i = 0, numChainedTweens = this._chainedTweens.length; i < numChainedTweens; i++) {
                    this._chainedTweens[i].start(this._startTime + this._duration)
                }
                return false
            }
        }
        return true
    }
};
TWEEN.Easing = {
    Linear: {
        None: function(k) {
            return k
        }
    },
    Quadratic: {
        In: function(k) {
            return k * k
        },
        Out: function(k) {
            return k * (2 - k)
        },
        InOut: function(k) {
            if ((k *= 2) < 1) {
                return .5 * k * k
            }
            return -.5 * (--k * (k - 2) - 1)
        }
    },
    Cubic: {
        In: function(k) {
            return k * k * k
        },
        Out: function(k) {
            return --k * k * k + 1
        },
        InOut: function(k) {
            if ((k *= 2) < 1) {
                return .5 * k * k * k
            }
            return .5 * ((k -= 2) * k * k + 2)
        }
    },
    Quartic: {
        In: function(k) {
            return k * k * k * k
        },
        Out: function(k) {
            return 1 - --k * k * k * k
        },
        InOut: function(k) {
            if ((k *= 2) < 1) {
                return .5 * k * k * k * k
            }
            return -.5 * ((k -= 2) * k * k * k - 2)
        }
    },
    Quintic: {
        In: function(k) {
            return k * k * k * k * k
        },
        Out: function(k) {
            return --k * k * k * k * k + 1
        },
        InOut: function(k) {
            if ((k *= 2) < 1) {
                return .5 * k * k * k * k * k
            }
            return .5 * ((k -= 2) * k * k * k * k + 2)
        }
    },
    Sinusoidal: {
        In: function(k) {
            return 1 - Math.cos(k * Math.PI / 2)
        },
        Out: function(k) {
            return Math.sin(k * Math.PI / 2)
        },
        InOut: function(k) {
            return .5 * (1 - Math.cos(Math.PI * k))
        }
    },
    Exponential: {
        In: function(k) {
            return k === 0 ? 0 : Math.pow(1024, k - 1)
        },
        Out: function(k) {
            return k === 1 ? 1 : 1 - Math.pow(2, -10 * k)
        },
        InOut: function(k) {
            if (k === 0) {
                return 0
            }
            if (k === 1) {
                return 1
            }
            if ((k *= 2) < 1) {
                return .5 * Math.pow(1024, k - 1)
            }
            return .5 * (-Math.pow(2, -10 * (k - 1)) + 2)
        }
    },
    Circular: {
        In: function(k) {
            return 1 - Math.sqrt(1 - k * k)
        },
        Out: function(k) {
            return Math.sqrt(1 - --k * k)
        },
        InOut: function(k) {
            if ((k *= 2) < 1) {
                return -.5 * (Math.sqrt(1 - k * k) - 1)
            }
            return .5 * (Math.sqrt(1 - (k -= 2) * k) + 1)
        }
    },
    Elastic: {
        In: function(k) {
            if (k === 0) {
                return 0
            }
            if (k === 1) {
                return 1
            }
            return -Math.pow(2, 10 * (k - 1)) * Math.sin((k - 1.1) * 5 * Math.PI)
        },
        Out: function(k) {
            if (k === 0) {
                return 0
            }
            if (k === 1) {
                return 1
            }
            return Math.pow(2, -10 * k) * Math.sin((k - .1) * 5 * Math.PI) + 1
        },
        InOut: function(k) {
            if (k === 0) {
                return 0
            }
            if (k === 1) {
                return 1
            }
            k *= 2;
            if (k < 1) {
                return -.5 * Math.pow(2, 10 * (k - 1)) * Math.sin((k - 1.1) * 5 * Math.PI)
            }
            return .5 * Math.pow(2, -10 * (k - 1)) * Math.sin((k - 1.1) * 5 * Math.PI) + 1
        }
    },
    Back: {
        In: function(k) {
            var s = 1.70158;
            return k * k * ((s + 1) * k - s)
        },
        Out: function(k) {
            var s = 1.70158;
            return --k * k * ((s + 1) * k + s) + 1
        },
        InOut: function(k) {
            var s = 1.70158 * 1.525;
            if ((k *= 2) < 1) {
                return .5 * (k * k * ((s + 1) * k - s))
            }
            return .5 * ((k -= 2) * k * ((s + 1) * k + s) + 2)
        }
    },
    Bounce: {
        In: function(k) {
            return 1 - TWEEN.Easing.Bounce.Out(1 - k)
        },
        Out: function(k) {
            if (k < 1 / 2.75) {
                return 7.5625 * k * k
            } else if (k < 2 / 2.75) {
                return 7.5625 * (k -= 1.5 / 2.75) * k + .75
            } else if (k < 2.5 / 2.75) {
                return 7.5625 * (k -= 2.25 / 2.75) * k + .9375
            } else {
                return 7.5625 * (k -= 2.625 / 2.75) * k + .984375
            }
        },
        InOut: function(k) {
            if (k < .5) {
                return TWEEN.Easing.Bounce.In(k * 2) * .5
            }
            return TWEEN.Easing.Bounce.Out(k * 2 - 1) * .5 + .5
        }
    }
};
TWEEN.Interpolation = {
    Linear: function(v, k) {
        var m = v.length - 1;
        var f = m * k;
        var i = Math.floor(f);
        var fn = TWEEN.Interpolation.Utils.Linear;
        if (k < 0) {
            return fn(v[0], v[1], f)
        }
        if (k > 1) {
            return fn(v[m], v[m - 1], m - f)
        }
        return fn(v[i], v[i + 1 > m ? m : i + 1], f - i)
    },
    Bezier: function(v, k) {
        var b = 0;
        var n = v.length - 1;
        var pw = Math.pow;
        var bn = TWEEN.Interpolation.Utils.Bernstein;
        for (var i = 0; i <= n; i++) {
            b += pw(1 - k, n - i) * pw(k, i) * v[i] * bn(n, i)
        }
        return b
    },
    CatmullRom: function(v, k) {
        var m = v.length - 1;
        var f = m * k;
        var i = Math.floor(f);
        var fn = TWEEN.Interpolation.Utils.CatmullRom;
        if (v[0] === v[m]) {
            if (k < 0) {
                i = Math.floor(f = m * (1 + k))
            }
            return fn(v[(i - 1 + m) % m], v[i], v[(i + 1) % m], v[(i + 2) % m], f - i)
        } else {
            if (k < 0) {
                return v[0] - (fn(v[0], v[0], v[1], v[1], -f) - v[0])
            }
            if (k > 1) {
                return v[m] - (fn(v[m], v[m], v[m - 1], v[m - 1], f - m) - v[m])
            }
            return fn(v[i ? i - 1 : 0], v[i], v[m < i + 1 ? m : i + 1], v[m < i + 2 ? m : i + 2], f - i)
        }
    },
    Utils: {
        Linear: function(p0, p1, t) {
            return (p1 - p0) * t + p0
        },
        Bernstein: function(n, i) {
            var fc = TWEEN.Interpolation.Utils.Factorial;
            return fc(n) / fc(i) / fc(n - i)
        },
        Factorial: function() {
            var a = [1];
            return function(n) {
                var s = 1;
                if (a[n]) {
                    return a[n]
                }
                for (var i = n; i > 1; i--) {
                    s *= i
                }
                a[n] = s;
                return s
            }
        }(),
        CatmullRom: function(p0, p1, p2, p3, t) {
            var v0 = (p2 - p0) * .5;
            var v1 = (p3 - p1) * .5;
            var t2 = t * t;
            var t3 = t * t2;
            return (2 * p1 - 2 * p2 + v0 + v1) * t3 + (-3 * p1 + 3 * p2 - 2 * v0 - v1) * t2 + v0 * t + p1
        }
    }
};
(function(root) {
    if (typeof define === "function" && define.amd) {
        define([], function() {
            return TWEEN
        })
    } else if (typeof module !== "undefined" && typeof exports === "object") {
        module.exports = TWEEN
    } else if (root !== undefined) {
        root.TWEEN = TWEEN
    }
})(this);
! function(e) {
    if ("object" == typeof exports && "undefined" != typeof module) module.exports = e();
    else if ("function" == typeof define && define.amd) define([], e);
    else {
        ("undefined" != typeof window ? window : "undefined" != typeof global ? global : "undefined" != typeof self ? self : this).pako = e()
    }
}(function() {
    return function e(t, i, n) {
        function a(o, s) {
            if (!i[o]) {
                if (!t[o]) {
                    var f = "function" == typeof require && require;
                    if (!s && f) return f(o, !0);
                    if (r) return r(o, !0);
                    var l = new Error("Cannot find module '" + o + "'");
                    throw l.code = "MODULE_NOT_FOUND", l
                }
                var d = i[o] = {
                    exports: {}
                };
                t[o][0].call(d.exports, function(e) {
                    var i = t[o][1][e];
                    return a(i || e)
                }, d, d.exports, e, t, i, n)
            }
            return i[o].exports
        }
        for (var r = "function" == typeof require && require, o = 0; o < n.length; o++) a(n[o]);
        return a
    }({
        1: [function(e, t, i) {
            "use strict";

            function n(e, t) {
                return Object.prototype.hasOwnProperty.call(e, t)
            }
            var a = "undefined" != typeof Uint8Array && "undefined" != typeof Uint16Array && "undefined" != typeof Int32Array;
            i.assign = function(e) {
                for (var t = Array.prototype.slice.call(arguments, 1); t.length;) {
                    var i = t.shift();
                    if (i) {
                        if ("object" != typeof i) throw new TypeError(i + "must be non-object");
                        for (var a in i) n(i, a) && (e[a] = i[a])
                    }
                }
                return e
            }, i.shrinkBuf = function(e, t) {
                return e.length === t ? e : e.subarray ? e.subarray(0, t) : (e.length = t, e)
            };
            var r = {
                    arraySet: function(e, t, i, n, a) {
                        if (t.subarray && e.subarray) e.set(t.subarray(i, i + n), a);
                        else
                            for (var r = 0; r < n; r++) e[a + r] = t[i + r]
                    },
                    flattenChunks: function(e) {
                        var t, i, n, a, r, o;
                        for (n = 0, t = 0, i = e.length; t < i; t++) n += e[t].length;
                        for (o = new Uint8Array(n), a = 0, t = 0, i = e.length; t < i; t++) r = e[t], o.set(r, a), a += r.length;
                        return o
                    }
                },
                o = {
                    arraySet: function(e, t, i, n, a) {
                        for (var r = 0; r < n; r++) e[a + r] = t[i + r]
                    },
                    flattenChunks: function(e) {
                        return [].concat.apply([], e)
                    }
                };
            i.setTyped = function(e) {
                e ? (i.Buf8 = Uint8Array, i.Buf16 = Uint16Array, i.Buf32 = Int32Array, i.assign(i, r)) : (i.Buf8 = Array, i.Buf16 = Array, i.Buf32 = Array, i.assign(i, o))
            }, i.setTyped(a)
        }, {}],
        2: [function(e, t, i) {
            "use strict";

            function n(e, t) {
                if (t < 65537 && (e.subarray && o || !e.subarray && r)) return String.fromCharCode.apply(null, a.shrinkBuf(e, t));
                for (var i = "", n = 0; n < t; n++) i += String.fromCharCode(e[n]);
                return i
            }
            var a = e("./common"),
                r = !0,
                o = !0;
            try {
                String.fromCharCode.apply(null, [0])
            } catch (e) {
                r = !1
            }
            try {
                String.fromCharCode.apply(null, new Uint8Array(1))
            } catch (e) {
                o = !1
            }
            for (var s = new a.Buf8(256), f = 0; f < 256; f++) s[f] = f >= 252 ? 6 : f >= 248 ? 5 : f >= 240 ? 4 : f >= 224 ? 3 : f >= 192 ? 2 : 1;
            s[254] = s[254] = 1, i.string2buf = function(e) {
                var t, i, n, r, o, s = e.length,
                    f = 0;
                for (r = 0; r < s; r++) 55296 == (64512 & (i = e.charCodeAt(r))) && r + 1 < s && 56320 == (64512 & (n = e.charCodeAt(r + 1))) && (i = 65536 + (i - 55296 << 10) + (n - 56320), r++), f += i < 128 ? 1 : i < 2048 ? 2 : i < 65536 ? 3 : 4;
                for (t = new a.Buf8(f), o = 0, r = 0; o < f; r++) 55296 == (64512 & (i = e.charCodeAt(r))) && r + 1 < s && 56320 == (64512 & (n = e.charCodeAt(r + 1))) && (i = 65536 + (i - 55296 << 10) + (n - 56320), r++), i < 128 ? t[o++] = i : i < 2048 ? (t[o++] = 192 | i >>> 6, t[o++] = 128 | 63 & i) : i < 65536 ? (t[o++] = 224 | i >>> 12, t[o++] = 128 | i >>> 6 & 63, t[o++] = 128 | 63 & i) : (t[o++] = 240 | i >>> 18, t[o++] = 128 | i >>> 12 & 63, t[o++] = 128 | i >>> 6 & 63, t[o++] = 128 | 63 & i);
                return t
            }, i.buf2binstring = function(e) {
                return n(e, e.length)
            }, i.binstring2buf = function(e) {
                for (var t = new a.Buf8(e.length), i = 0, n = t.length; i < n; i++) t[i] = e.charCodeAt(i);
                return t
            }, i.buf2string = function(e, t) {
                var i, a, r, o, f = t || e.length,
                    l = new Array(2 * f);
                for (a = 0, i = 0; i < f;)
                    if ((r = e[i++]) < 128) l[a++] = r;
                    else if ((o = s[r]) > 4) l[a++] = 65533, i += o - 1;
                    else {
                        for (r &= 2 === o ? 31 : 3 === o ? 15 : 7; o > 1 && i < f;) r = r << 6 | 63 & e[i++], o--;
                        o > 1 ? l[a++] = 65533 : r < 65536 ? l[a++] = r : (r -= 65536, l[a++] = 55296 | r >> 10 & 1023, l[a++] = 56320 | 1023 & r)
                    }
                return n(l, a)
            }, i.utf8border = function(e, t) {
                var i;
                for ((t = t || e.length) > e.length && (t = e.length), i = t - 1; i >= 0 && 128 == (192 & e[i]);) i--;
                return i < 0 ? t : 0 === i ? t : i + s[e[i]] > t ? i : t
            }
        }, {
            "./common": 1
        }],
        3: [function(e, t, i) {
            "use strict";
            t.exports = function(e, t, i, n) {
                for (var a = 65535 & e | 0, r = e >>> 16 & 65535 | 0, o = 0; 0 !== i;) {
                    i -= o = i > 2e3 ? 2e3 : i;
                    do {
                        r = r + (a = a + t[n++] | 0) | 0
                    } while (--o);
                    a %= 65521, r %= 65521
                }
                return a | r << 16 | 0
            }
        }, {}],
        4: [function(e, t, i) {
            "use strict";
            t.exports = {
                Z_NO_FLUSH: 0,
                Z_PARTIAL_FLUSH: 1,
                Z_SYNC_FLUSH: 2,
                Z_FULL_FLUSH: 3,
                Z_FINISH: 4,
                Z_BLOCK: 5,
                Z_TREES: 6,
                Z_OK: 0,
                Z_STREAM_END: 1,
                Z_NEED_DICT: 2,
                Z_ERRNO: -1,
                Z_STREAM_ERROR: -2,
                Z_DATA_ERROR: -3,
                Z_BUF_ERROR: -5,
                Z_NO_COMPRESSION: 0,
                Z_BEST_SPEED: 1,
                Z_BEST_COMPRESSION: 9,
                Z_DEFAULT_COMPRESSION: -1,
                Z_FILTERED: 1,
                Z_HUFFMAN_ONLY: 2,
                Z_RLE: 3,
                Z_FIXED: 4,
                Z_DEFAULT_STRATEGY: 0,
                Z_BINARY: 0,
                Z_TEXT: 1,
                Z_UNKNOWN: 2,
                Z_DEFLATED: 8
            }
        }, {}],
        5: [function(e, t, i) {
            "use strict";
            var n = function() {
                for (var e, t = [], i = 0; i < 256; i++) {
                    e = i;
                    for (var n = 0; n < 8; n++) e = 1 & e ? 3988292384 ^ e >>> 1 : e >>> 1;
                    t[i] = e
                }
                return t
            }();
            t.exports = function(e, t, i, a) {
                var r = n,
                    o = a + i;
                e ^= -1;
                for (var s = a; s < o; s++) e = e >>> 8 ^ r[255 & (e ^ t[s])];
                return -1 ^ e
            }
        }, {}],
        6: [function(e, t, i) {
            "use strict";
            t.exports = function() {
                this.text = 0, this.time = 0, this.xflags = 0, this.os = 0, this.extra = null, this.extra_len = 0, this.name = "", this.comment = "", this.hcrc = 0, this.done = !1
            }
        }, {}],
        7: [function(e, t, i) {
            "use strict";
            t.exports = function(e, t) {
                var i, n, a, r, o, s, f, l, d, u, c, h, b, w, m, k, _, g, v, p, x, y, S, E, B;
                i = e.state, n = e.next_in, E = e.input, a = n + (e.avail_in - 5), r = e.next_out, B = e.output, o = r - (t - e.avail_out), s = r + (e.avail_out - 257), f = i.dmax, l = i.wsize, d = i.whave, u = i.wnext, c = i.window, h = i.hold, b = i.bits, w = i.lencode, m = i.distcode, k = (1 << i.lenbits) - 1, _ = (1 << i.distbits) - 1;
                e: do {
                    b < 15 && (h += E[n++] << b, b += 8, h += E[n++] << b, b += 8), g = w[h & k];
                    t: for (;;) {
                        if (v = g >>> 24, h >>>= v, b -= v, 0 === (v = g >>> 16 & 255)) B[r++] = 65535 & g;
                        else {
                            if (!(16 & v)) {
                                if (0 == (64 & v)) {
                                    g = w[(65535 & g) + (h & (1 << v) - 1)];
                                    continue t
                                }
                                if (32 & v) {
                                    i.mode = 12;
                                    break e
                                }
                                e.msg = "invalid literal/length code", i.mode = 30;
                                break e
                            }
                            p = 65535 & g, (v &= 15) && (b < v && (h += E[n++] << b, b += 8), p += h & (1 << v) - 1, h >>>= v, b -= v), b < 15 && (h += E[n++] << b, b += 8, h += E[n++] << b, b += 8), g = m[h & _];
                            i: for (;;) {
                                if (v = g >>> 24, h >>>= v, b -= v, !(16 & (v = g >>> 16 & 255))) {
                                    if (0 == (64 & v)) {
                                        g = m[(65535 & g) + (h & (1 << v) - 1)];
                                        continue i
                                    }
                                    e.msg = "invalid distance code", i.mode = 30;
                                    break e
                                }
                                if (x = 65535 & g, v &= 15, b < v && (h += E[n++] << b, (b += 8) < v && (h += E[n++] << b, b += 8)), (x += h & (1 << v) - 1) > f) {
                                    e.msg = "invalid distance too far back", i.mode = 30;
                                    break e
                                }
                                if (h >>>= v, b -= v, v = r - o, x > v) {
                                    if ((v = x - v) > d && i.sane) {
                                        e.msg = "invalid distance too far back", i.mode = 30;
                                        break e
                                    }
                                    if (y = 0, S = c, 0 === u) {
                                        if (y += l - v, v < p) {
                                            p -= v;
                                            do {
                                                B[r++] = c[y++]
                                            } while (--v);
                                            y = r - x, S = B
                                        }
                                    } else if (u < v) {
                                        if (y += l + u - v, (v -= u) < p) {
                                            p -= v;
                                            do {
                                                B[r++] = c[y++]
                                            } while (--v);
                                            if (y = 0, u < p) {
                                                p -= v = u;
                                                do {
                                                    B[r++] = c[y++]
                                                } while (--v);
                                                y = r - x, S = B
                                            }
                                        }
                                    } else if (y += u - v, v < p) {
                                        p -= v;
                                        do {
                                            B[r++] = c[y++]
                                        } while (--v);
                                        y = r - x, S = B
                                    }
                                    for (; p > 2;) B[r++] = S[y++], B[r++] = S[y++], B[r++] = S[y++], p -= 3;
                                    p && (B[r++] = S[y++], p > 1 && (B[r++] = S[y++]))
                                } else {
                                    y = r - x;
                                    do {
                                        B[r++] = B[y++], B[r++] = B[y++], B[r++] = B[y++], p -= 3
                                    } while (p > 2);
                                    p && (B[r++] = B[y++], p > 1 && (B[r++] = B[y++]))
                                }
                                break
                            }
                        }
                        break
                    }
                } while (n < a && r < s);
                n -= p = b >> 3, h &= (1 << (b -= p << 3)) - 1, e.next_in = n, e.next_out = r, e.avail_in = n < a ? a - n + 5 : 5 - (n - a), e.avail_out = r < s ? s - r + 257 : 257 - (r - s), i.hold = h, i.bits = b
            }
        }, {}],
        8: [function(e, t, i) {
            "use strict";

            function n(e) {
                return (e >>> 24 & 255) + (e >>> 8 & 65280) + ((65280 & e) << 8) + ((255 & e) << 24)
            }

            function a() {
                this.mode = 0, this.last = !1, this.wrap = 0, this.havedict = !1, this.flags = 0, this.dmax = 0, this.check = 0, this.total = 0, this.head = null, this.wbits = 0, this.wsize = 0, this.whave = 0, this.wnext = 0, this.window = null, this.hold = 0, this.bits = 0, this.length = 0, this.offset = 0, this.extra = 0, this.lencode = null, this.distcode = null, this.lenbits = 0, this.distbits = 0, this.ncode = 0, this.nlen = 0, this.ndist = 0, this.have = 0, this.next = null, this.lens = new h.Buf16(320), this.work = new h.Buf16(288), this.lendyn = null, this.distdyn = null, this.sane = 0, this.back = 0, this.was = 0
            }

            function r(e) {
                var t;
                return e && e.state ? (t = e.state, e.total_in = e.total_out = t.total = 0, e.msg = "", t.wrap && (e.adler = 1 & t.wrap), t.mode = O, t.last = 0, t.havedict = 0, t.dmax = 32768, t.head = null, t.hold = 0, t.bits = 0, t.lencode = t.lendyn = new h.Buf32(de), t.distcode = t.distdyn = new h.Buf32(ue), t.sane = 1, t.back = -1, S) : Z
            }

            function o(e) {
                var t;
                return e && e.state ? (t = e.state, t.wsize = 0, t.whave = 0, t.wnext = 0, r(e)) : Z
            }

            function s(e, t) {
                var i, n;
                return e && e.state ? (n = e.state, t < 0 ? (i = 0, t = -t) : (i = 1 + (t >> 4), t < 48 && (t &= 15)), t && (t < 8 || t > 15) ? Z : (null !== n.window && n.wbits !== t && (n.window = null), n.wrap = i, n.wbits = t, o(e))) : Z
            }

            function f(e, t) {
                var i, n;
                return e ? (n = new a, e.state = n, n.window = null, (i = s(e, t)) !== S && (e.state = null), i) : Z
            }

            function l(e) {
                if (he) {
                    var t;
                    for (u = new h.Buf32(512), c = new h.Buf32(32), t = 0; t < 144;) e.lens[t++] = 8;
                    for (; t < 256;) e.lens[t++] = 9;
                    for (; t < 280;) e.lens[t++] = 7;
                    for (; t < 288;) e.lens[t++] = 8;
                    for (k(g, e.lens, 0, 288, u, 0, e.work, {
                        bits: 9
                    }), t = 0; t < 32;) e.lens[t++] = 5;
                    k(v, e.lens, 0, 32, c, 0, e.work, {
                        bits: 5
                    }), he = !1
                }
                e.lencode = u, e.lenbits = 9, e.distcode = c, e.distbits = 5
            }

            function d(e, t, i, n) {
                var a, r = e.state;
                return null === r.window && (r.wsize = 1 << r.wbits, r.wnext = 0, r.whave = 0, r.window = new h.Buf8(r.wsize)), n >= r.wsize ? (h.arraySet(r.window, t, i - r.wsize, r.wsize, 0), r.wnext = 0, r.whave = r.wsize) : ((a = r.wsize - r.wnext) > n && (a = n), h.arraySet(r.window, t, i - n, a, r.wnext), (n -= a) ? (h.arraySet(r.window, t, i - n, n, 0), r.wnext = n, r.whave = r.wsize) : (r.wnext += a, r.wnext === r.wsize && (r.wnext = 0), r.whave < r.wsize && (r.whave += a))), 0
            }
            var u, c, h = e("../utils/common"),
                b = e("./adler32"),
                w = e("./crc32"),
                m = e("./inffast"),
                k = e("./inftrees"),
                _ = 0,
                g = 1,
                v = 2,
                p = 4,
                x = 5,
                y = 6,
                S = 0,
                E = 1,
                B = 2,
                Z = -2,
                A = -3,
                z = -4,
                R = -5,
                N = 8,
                O = 1,
                C = 2,
                I = 3,
                T = 4,
                U = 5,
                D = 6,
                F = 7,
                L = 8,
                H = 9,
                j = 10,
                M = 11,
                K = 12,
                P = 13,
                Y = 14,
                q = 15,
                G = 16,
                X = 17,
                W = 18,
                J = 19,
                Q = 20,
                V = 21,
                $ = 22,
                ee = 23,
                te = 24,
                ie = 25,
                ne = 26,
                ae = 27,
                re = 28,
                oe = 29,
                se = 30,
                fe = 31,
                le = 32,
                de = 852,
                ue = 592,
                ce = 15,
                he = !0;
            i.inflateReset = o, i.inflateReset2 = s, i.inflateResetKeep = r, i.inflateInit = function(e) {
                return f(e, ce)
            }, i.inflateInit2 = f, i.inflate = function(e, t) {
                var i, a, r, o, s, f, u, c, de, ue, ce, he, be, we, me, ke, _e, ge, ve, pe, xe, ye, Se, Ee, Be = 0,
                    Ze = new h.Buf8(4),
                    Ae = [16, 17, 18, 0, 8, 7, 9, 6, 10, 5, 11, 4, 12, 3, 13, 2, 14, 1, 15];
                if (!e || !e.state || !e.output || !e.input && 0 !== e.avail_in) return Z;
                (i = e.state).mode === K && (i.mode = P), s = e.next_out, r = e.output, u = e.avail_out, o = e.next_in, a = e.input, f = e.avail_in, c = i.hold, de = i.bits, ue = f, ce = u, ye = S;
                e: for (;;) switch (i.mode) {
                    case O:
                        if (0 === i.wrap) {
                            i.mode = P;
                            break
                        }
                        for (; de < 16;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        if (2 & i.wrap && 35615 === c) {
                            i.check = 0, Ze[0] = 255 & c, Ze[1] = c >>> 8 & 255, i.check = w(i.check, Ze, 2, 0), c = 0, de = 0, i.mode = C;
                            break
                        }
                        if (i.flags = 0, i.head && (i.head.done = !1), !(1 & i.wrap) || (((255 & c) << 8) + (c >> 8)) % 31) {
                            e.msg = "incorrect header check", i.mode = se;
                            break
                        }
                        if ((15 & c) !== N) {
                            e.msg = "unknown compression method", i.mode = se;
                            break
                        }
                        if (c >>>= 4, de -= 4, xe = 8 + (15 & c), 0 === i.wbits) i.wbits = xe;
                        else if (xe > i.wbits) {
                            e.msg = "invalid window size", i.mode = se;
                            break
                        }
                        i.dmax = 1 << xe, e.adler = i.check = 1, i.mode = 512 & c ? j : K, c = 0, de = 0;
                        break;
                    case C:
                        for (; de < 16;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        if (i.flags = c, (255 & i.flags) !== N) {
                            e.msg = "unknown compression method", i.mode = se;
                            break
                        }
                        if (57344 & i.flags) {
                            e.msg = "unknown header flags set", i.mode = se;
                            break
                        }
                        i.head && (i.head.text = c >> 8 & 1), 512 & i.flags && (Ze[0] = 255 & c, Ze[1] = c >>> 8 & 255, i.check = w(i.check, Ze, 2, 0)), c = 0, de = 0, i.mode = I;
                    case I:
                        for (; de < 32;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        i.head && (i.head.time = c), 512 & i.flags && (Ze[0] = 255 & c, Ze[1] = c >>> 8 & 255, Ze[2] = c >>> 16 & 255, Ze[3] = c >>> 24 & 255, i.check = w(i.check, Ze, 4, 0)), c = 0, de = 0, i.mode = T;
                    case T:
                        for (; de < 16;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        i.head && (i.head.xflags = 255 & c, i.head.os = c >> 8), 512 & i.flags && (Ze[0] = 255 & c, Ze[1] = c >>> 8 & 255, i.check = w(i.check, Ze, 2, 0)), c = 0, de = 0, i.mode = U;
                    case U:
                        if (1024 & i.flags) {
                            for (; de < 16;) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            i.length = c, i.head && (i.head.extra_len = c), 512 & i.flags && (Ze[0] = 255 & c, Ze[1] = c >>> 8 & 255, i.check = w(i.check, Ze, 2, 0)), c = 0, de = 0
                        } else i.head && (i.head.extra = null);
                        i.mode = D;
                    case D:
                        if (1024 & i.flags && ((he = i.length) > f && (he = f), he && (i.head && (xe = i.head.extra_len - i.length, i.head.extra || (i.head.extra = new Array(i.head.extra_len)), h.arraySet(i.head.extra, a, o, he, xe)), 512 & i.flags && (i.check = w(i.check, a, he, o)), f -= he, o += he, i.length -= he), i.length)) break e;
                        i.length = 0, i.mode = F;
                    case F:
                        if (2048 & i.flags) {
                            if (0 === f) break e;
                            he = 0;
                            do {
                                xe = a[o + he++], i.head && xe && i.length < 65536 && (i.head.name += String.fromCharCode(xe))
                            } while (xe && he < f);
                            if (512 & i.flags && (i.check = w(i.check, a, he, o)), f -= he, o += he, xe) break e
                        } else i.head && (i.head.name = null);
                        i.length = 0, i.mode = L;
                    case L:
                        if (4096 & i.flags) {
                            if (0 === f) break e;
                            he = 0;
                            do {
                                xe = a[o + he++], i.head && xe && i.length < 65536 && (i.head.comment += String.fromCharCode(xe))
                            } while (xe && he < f);
                            if (512 & i.flags && (i.check = w(i.check, a, he, o)), f -= he, o += he, xe) break e
                        } else i.head && (i.head.comment = null);
                        i.mode = H;
                    case H:
                        if (512 & i.flags) {
                            for (; de < 16;) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            if (c !== (65535 & i.check)) {
                                e.msg = "header crc mismatch", i.mode = se;
                                break
                            }
                            c = 0, de = 0
                        }
                        i.head && (i.head.hcrc = i.flags >> 9 & 1, i.head.done = !0), e.adler = i.check = 0, i.mode = K;
                        break;
                    case j:
                        for (; de < 32;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        e.adler = i.check = n(c), c = 0, de = 0, i.mode = M;
                    case M:
                        if (0 === i.havedict) return e.next_out = s, e.avail_out = u, e.next_in = o, e.avail_in = f, i.hold = c, i.bits = de, B;
                        e.adler = i.check = 1, i.mode = K;
                    case K:
                        if (t === x || t === y) break e;
                    case P:
                        if (i.last) {
                            c >>>= 7 & de, de -= 7 & de, i.mode = ae;
                            break
                        }
                        for (; de < 3;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        switch (i.last = 1 & c, c >>>= 1, de -= 1, 3 & c) {
                            case 0:
                                i.mode = Y;
                                break;
                            case 1:
                                if (l(i), i.mode = Q, t === y) {
                                    c >>>= 2, de -= 2;
                                    break e
                                }
                                break;
                            case 2:
                                i.mode = X;
                                break;
                            case 3:
                                e.msg = "invalid block type", i.mode = se
                        }
                        c >>>= 2, de -= 2;
                        break;
                    case Y:
                        for (c >>>= 7 & de, de -= 7 & de; de < 32;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        if ((65535 & c) != (c >>> 16 ^ 65535)) {
                            e.msg = "invalid stored block lengths", i.mode = se;
                            break
                        }
                        if (i.length = 65535 & c, c = 0, de = 0, i.mode = q, t === y) break e;
                    case q:
                        i.mode = G;
                    case G:
                        if (he = i.length) {
                            if (he > f && (he = f), he > u && (he = u), 0 === he) break e;
                            h.arraySet(r, a, o, he, s), f -= he, o += he, u -= he, s += he, i.length -= he;
                            break
                        }
                        i.mode = K;
                        break;
                    case X:
                        for (; de < 14;) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        if (i.nlen = 257 + (31 & c), c >>>= 5, de -= 5, i.ndist = 1 + (31 & c), c >>>= 5, de -= 5, i.ncode = 4 + (15 & c), c >>>= 4, de -= 4, i.nlen > 286 || i.ndist > 30) {
                            e.msg = "too many length or distance symbols", i.mode = se;
                            break
                        }
                        i.have = 0, i.mode = W;
                    case W:
                        for (; i.have < i.ncode;) {
                            for (; de < 3;) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            i.lens[Ae[i.have++]] = 7 & c, c >>>= 3, de -= 3
                        }
                        for (; i.have < 19;) i.lens[Ae[i.have++]] = 0;
                        if (i.lencode = i.lendyn, i.lenbits = 7, Se = {
                            bits: i.lenbits
                        }, ye = k(_, i.lens, 0, 19, i.lencode, 0, i.work, Se), i.lenbits = Se.bits, ye) {
                            e.msg = "invalid code lengths set", i.mode = se;
                            break
                        }
                        i.have = 0, i.mode = J;
                    case J:
                        for (; i.have < i.nlen + i.ndist;) {
                            for (; Be = i.lencode[c & (1 << i.lenbits) - 1], me = Be >>> 24, ke = Be >>> 16 & 255, _e = 65535 & Be, !(me <= de);) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            if (_e < 16) c >>>= me, de -= me, i.lens[i.have++] = _e;
                            else {
                                if (16 === _e) {
                                    for (Ee = me + 2; de < Ee;) {
                                        if (0 === f) break e;
                                        f--, c += a[o++] << de, de += 8
                                    }
                                    if (c >>>= me, de -= me, 0 === i.have) {
                                        e.msg = "invalid bit length repeat", i.mode = se;
                                        break
                                    }
                                    xe = i.lens[i.have - 1], he = 3 + (3 & c), c >>>= 2, de -= 2
                                } else if (17 === _e) {
                                    for (Ee = me + 3; de < Ee;) {
                                        if (0 === f) break e;
                                        f--, c += a[o++] << de, de += 8
                                    }
                                    de -= me, xe = 0, he = 3 + (7 & (c >>>= me)), c >>>= 3, de -= 3
                                } else {
                                    for (Ee = me + 7; de < Ee;) {
                                        if (0 === f) break e;
                                        f--, c += a[o++] << de, de += 8
                                    }
                                    de -= me, xe = 0, he = 11 + (127 & (c >>>= me)), c >>>= 7, de -= 7
                                }
                                if (i.have + he > i.nlen + i.ndist) {
                                    e.msg = "invalid bit length repeat", i.mode = se;
                                    break
                                }
                                for (; he--;) i.lens[i.have++] = xe
                            }
                        }
                        if (i.mode === se) break;
                        if (0 === i.lens[256]) {
                            e.msg = "invalid code -- missing end-of-block", i.mode = se;
                            break
                        }
                        if (i.lenbits = 9, Se = {
                            bits: i.lenbits
                        }, ye = k(g, i.lens, 0, i.nlen, i.lencode, 0, i.work, Se), i.lenbits = Se.bits, ye) {
                            e.msg = "invalid literal/lengths set", i.mode = se;
                            break
                        }
                        if (i.distbits = 6, i.distcode = i.distdyn, Se = {
                            bits: i.distbits
                        }, ye = k(v, i.lens, i.nlen, i.ndist, i.distcode, 0, i.work, Se), i.distbits = Se.bits, ye) {
                            e.msg = "invalid distances set", i.mode = se;
                            break
                        }
                        if (i.mode = Q, t === y) break e;
                    case Q:
                        i.mode = V;
                    case V:
                        if (f >= 6 && u >= 258) {
                            e.next_out = s, e.avail_out = u, e.next_in = o, e.avail_in = f, i.hold = c, i.bits = de, m(e, ce), s = e.next_out, r = e.output, u = e.avail_out, o = e.next_in, a = e.input, f = e.avail_in, c = i.hold, de = i.bits, i.mode === K && (i.back = -1);
                            break
                        }
                        for (i.back = 0; Be = i.lencode[c & (1 << i.lenbits) - 1], me = Be >>> 24, ke = Be >>> 16 & 255, _e = 65535 & Be, !(me <= de);) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        if (ke && 0 == (240 & ke)) {
                            for (ge = me, ve = ke, pe = _e; Be = i.lencode[pe + ((c & (1 << ge + ve) - 1) >> ge)], me = Be >>> 24, ke = Be >>> 16 & 255, _e = 65535 & Be, !(ge + me <= de);) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            c >>>= ge, de -= ge, i.back += ge
                        }
                        if (c >>>= me, de -= me, i.back += me, i.length = _e, 0 === ke) {
                            i.mode = ne;
                            break
                        }
                        if (32 & ke) {
                            i.back = -1, i.mode = K;
                            break
                        }
                        if (64 & ke) {
                            e.msg = "invalid literal/length code", i.mode = se;
                            break
                        }
                        i.extra = 15 & ke, i.mode = $;
                    case $:
                        if (i.extra) {
                            for (Ee = i.extra; de < Ee;) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            i.length += c & (1 << i.extra) - 1, c >>>= i.extra, de -= i.extra, i.back += i.extra
                        }
                        i.was = i.length, i.mode = ee;
                    case ee:
                        for (; Be = i.distcode[c & (1 << i.distbits) - 1], me = Be >>> 24, ke = Be >>> 16 & 255, _e = 65535 & Be, !(me <= de);) {
                            if (0 === f) break e;
                            f--, c += a[o++] << de, de += 8
                        }
                        if (0 == (240 & ke)) {
                            for (ge = me, ve = ke, pe = _e; Be = i.distcode[pe + ((c & (1 << ge + ve) - 1) >> ge)], me = Be >>> 24, ke = Be >>> 16 & 255, _e = 65535 & Be, !(ge + me <= de);) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            c >>>= ge, de -= ge, i.back += ge
                        }
                        if (c >>>= me, de -= me, i.back += me, 64 & ke) {
                            e.msg = "invalid distance code", i.mode = se;
                            break
                        }
                        i.offset = _e, i.extra = 15 & ke, i.mode = te;
                    case te:
                        if (i.extra) {
                            for (Ee = i.extra; de < Ee;) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            i.offset += c & (1 << i.extra) - 1, c >>>= i.extra, de -= i.extra, i.back += i.extra
                        }
                        if (i.offset > i.dmax) {
                            e.msg = "invalid distance too far back", i.mode = se;
                            break
                        }
                        i.mode = ie;
                    case ie:
                        if (0 === u) break e;
                        if (he = ce - u, i.offset > he) {
                            if ((he = i.offset - he) > i.whave && i.sane) {
                                e.msg = "invalid distance too far back", i.mode = se;
                                break
                            }
                            he > i.wnext ? (he -= i.wnext, be = i.wsize - he) : be = i.wnext - he, he > i.length && (he = i.length), we = i.window
                        } else we = r, be = s - i.offset, he = i.length;
                        he > u && (he = u), u -= he, i.length -= he;
                        do {
                            r[s++] = we[be++]
                        } while (--he);
                        0 === i.length && (i.mode = V);
                        break;
                    case ne:
                        if (0 === u) break e;
                        r[s++] = i.length, u--, i.mode = V;
                        break;
                    case ae:
                        if (i.wrap) {
                            for (; de < 32;) {
                                if (0 === f) break e;
                                f--, c |= a[o++] << de, de += 8
                            }
                            if (ce -= u, e.total_out += ce, i.total += ce, ce && (e.adler = i.check = i.flags ? w(i.check, r, ce, s - ce) : b(i.check, r, ce, s - ce)), ce = u, (i.flags ? c : n(c)) !== i.check) {
                                e.msg = "incorrect data check", i.mode = se;
                                break
                            }
                            c = 0, de = 0
                        }
                        i.mode = re;
                    case re:
                        if (i.wrap && i.flags) {
                            for (; de < 32;) {
                                if (0 === f) break e;
                                f--, c += a[o++] << de, de += 8
                            }
                            if (c !== (4294967295 & i.total)) {
                                e.msg = "incorrect length check", i.mode = se;
                                break
                            }
                            c = 0, de = 0
                        }
                        i.mode = oe;
                    case oe:
                        ye = E;
                        break e;
                    case se:
                        ye = A;
                        break e;
                    case fe:
                        return z;
                    case le:
                    default:
                        return Z
                }
                return e.next_out = s, e.avail_out = u, e.next_in = o, e.avail_in = f, i.hold = c, i.bits = de, (i.wsize || ce !== e.avail_out && i.mode < se && (i.mode < ae || t !== p)) && d(e, e.output, e.next_out, ce - e.avail_out) ? (i.mode = fe, z) : (ue -= e.avail_in, ce -= e.avail_out, e.total_in += ue, e.total_out += ce, i.total += ce, i.wrap && ce && (e.adler = i.check = i.flags ? w(i.check, r, ce, e.next_out - ce) : b(i.check, r, ce, e.next_out - ce)), e.data_type = i.bits + (i.last ? 64 : 0) + (i.mode === K ? 128 : 0) + (i.mode === Q || i.mode === q ? 256 : 0), (0 === ue && 0 === ce || t === p) && ye === S && (ye = R), ye)
            }, i.inflateEnd = function(e) {
                if (!e || !e.state) return Z;
                var t = e.state;
                return t.window && (t.window = null), e.state = null, S
            }, i.inflateGetHeader = function(e, t) {
                var i;
                return e && e.state ? 0 == (2 & (i = e.state).wrap) ? Z : (i.head = t, t.done = !1, S) : Z
            }, i.inflateSetDictionary = function(e, t) {
                var i, n, a = t.length;
                return e && e.state ? 0 !== (i = e.state).wrap && i.mode !== M ? Z : i.mode === M && (n = 1, (n = b(n, t, a, 0)) !== i.check) ? A : d(e, t, a, a) ? (i.mode = fe, z) : (i.havedict = 1, S) : Z
            }, i.inflateInfo = "pako inflate (from Nodeca project)"
        }, {
            "../utils/common": 1,
            "./adler32": 3,
            "./crc32": 5,
            "./inffast": 7,
            "./inftrees": 9
        }],
        9: [function(e, t, i) {
            "use strict";
            var n = e("../utils/common"),
                a = [3, 4, 5, 6, 7, 8, 9, 10, 11, 13, 15, 17, 19, 23, 27, 31, 35, 43, 51, 59, 67, 83, 99, 115, 131, 163, 195, 227, 258, 0, 0],
                r = [16, 16, 16, 16, 16, 16, 16, 16, 17, 17, 17, 17, 18, 18, 18, 18, 19, 19, 19, 19, 20, 20, 20, 20, 21, 21, 21, 21, 16, 72, 78],
                o = [1, 2, 3, 4, 5, 7, 9, 13, 17, 25, 33, 49, 65, 97, 129, 193, 257, 385, 513, 769, 1025, 1537, 2049, 3073, 4097, 6145, 8193, 12289, 16385, 24577, 0, 0],
                s = [16, 16, 16, 16, 17, 17, 18, 18, 19, 19, 20, 20, 21, 21, 22, 22, 23, 23, 24, 24, 25, 25, 26, 26, 27, 27, 28, 28, 29, 29, 64, 64];
            t.exports = function(e, t, i, f, l, d, u, c) {
                var h, b, w, m, k, _, g, v, p, x = c.bits,
                    y = 0,
                    S = 0,
                    E = 0,
                    B = 0,
                    Z = 0,
                    A = 0,
                    z = 0,
                    R = 0,
                    N = 0,
                    O = 0,
                    C = null,
                    I = 0,
                    T = new n.Buf16(16),
                    U = new n.Buf16(16),
                    D = null,
                    F = 0;
                for (y = 0; y <= 15; y++) T[y] = 0;
                for (S = 0; S < f; S++) T[t[i + S]]++;
                for (Z = x, B = 15; B >= 1 && 0 === T[B]; B--);
                if (Z > B && (Z = B), 0 === B) return l[d++] = 20971520, l[d++] = 20971520, c.bits = 1, 0;
                for (E = 1; E < B && 0 === T[E]; E++);
                for (Z < E && (Z = E), R = 1, y = 1; y <= 15; y++)
                    if (R <<= 1, (R -= T[y]) < 0) return -1;
                if (R > 0 && (0 === e || 1 !== B)) return -1;
                for (U[1] = 0, y = 1; y < 15; y++) U[y + 1] = U[y] + T[y];
                for (S = 0; S < f; S++) 0 !== t[i + S] && (u[U[t[i + S]]++] = S);
                if (0 === e ? (C = D = u, _ = 19) : 1 === e ? (C = a, I -= 257, D = r, F -= 257, _ = 256) : (C = o, D = s, _ = -1), O = 0, S = 0, y = E, k = d, A = Z, z = 0, w = -1, N = 1 << Z, m = N - 1, 1 === e && N > 852 || 2 === e && N > 592) return 1;
                for (;;) {
                    g = y - z, u[S] < _ ? (v = 0, p = u[S]) : u[S] > _ ? (v = D[F + u[S]], p = C[I + u[S]]) : (v = 96, p = 0), h = 1 << y - z, E = b = 1 << A;
                    do {
                        l[k + (O >> z) + (b -= h)] = g << 24 | v << 16 | p | 0
                    } while (0 !== b);
                    for (h = 1 << y - 1; O & h;) h >>= 1;
                    if (0 !== h ? (O &= h - 1, O += h) : O = 0, S++, 0 == --T[y]) {
                        if (y === B) break;
                        y = t[i + u[S]]
                    }
                    if (y > Z && (O & m) !== w) {
                        for (0 === z && (z = Z), k += E, R = 1 << (A = y - z); A + z < B && !((R -= T[A + z]) <= 0);) A++, R <<= 1;
                        if (N += 1 << A, 1 === e && N > 852 || 2 === e && N > 592) return 1;
                        l[w = O & m] = Z << 24 | A << 16 | k - d | 0
                    }
                }
                return 0 !== O && (l[k + O] = y - z << 24 | 64 << 16 | 0), c.bits = Z, 0
            }
        }, {
            "../utils/common": 1
        }],
        10: [function(e, t, i) {
            "use strict";
            t.exports = {
                2: "need dictionary",
                1: "stream end",
                0: "",
                "-1": "file error",
                "-2": "stream error",
                "-3": "data error",
                "-4": "insufficient memory",
                "-5": "buffer error",
                "-6": "incompatible version"
            }
        }, {}],
        11: [function(e, t, i) {
            "use strict";
            t.exports = function() {
                this.input = null, this.next_in = 0, this.avail_in = 0, this.total_in = 0, this.output = null, this.next_out = 0, this.avail_out = 0, this.total_out = 0, this.msg = "", this.state = null, this.data_type = 2, this.adler = 0
            }
        }, {}],
        "/lib/inflate.js": [function(e, t, i) {
            "use strict";

            function n(e) {
                if (!(this instanceof n)) return new n(e);
                this.options = o.assign({
                    chunkSize: 16384,
                    windowBits: 0,
                    to: ""
                }, e || {});
                var t = this.options;
                t.raw && t.windowBits >= 0 && t.windowBits < 16 && (t.windowBits = -t.windowBits, 0 === t.windowBits && (t.windowBits = -15)), !(t.windowBits >= 0 && t.windowBits < 16) || e && e.windowBits || (t.windowBits += 32), t.windowBits > 15 && t.windowBits < 48 && 0 == (15 & t.windowBits) && (t.windowBits |= 15), this.err = 0, this.msg = "", this.ended = !1, this.chunks = [], this.strm = new d, this.strm.avail_out = 0;
                var i = r.inflateInit2(this.strm, t.windowBits);
                if (i !== f.Z_OK) throw new Error(l[i]);
                this.header = new u, r.inflateGetHeader(this.strm, this.header)
            }

            function a(e, t) {
                var i = new n(t);
                if (i.push(e, !0), i.err) throw i.msg || l[i.err];
                return i.result
            }
            var r = e("./zlib/inflate"),
                o = e("./utils/common"),
                s = e("./utils/strings"),
                f = e("./zlib/constants"),
                l = e("./zlib/messages"),
                d = e("./zlib/zstream"),
                u = e("./zlib/gzheader"),
                c = Object.prototype.toString;
            n.prototype.push = function(e, t) {
                var i, n, a, l, d, u, h = this.strm,
                    b = this.options.chunkSize,
                    w = this.options.dictionary,
                    m = !1;
                if (this.ended) return !1;
                n = t === ~~t ? t : !0 === t ? f.Z_FINISH : f.Z_NO_FLUSH, "string" == typeof e ? h.input = s.binstring2buf(e) : "[object ArrayBuffer]" === c.call(e) ? h.input = new Uint8Array(e) : h.input = e, h.next_in = 0, h.avail_in = h.input.length;
                do {
                    if (0 === h.avail_out && (h.output = new o.Buf8(b), h.next_out = 0, h.avail_out = b), (i = r.inflate(h, f.Z_NO_FLUSH)) === f.Z_NEED_DICT && w && (u = "string" == typeof w ? s.string2buf(w) : "[object ArrayBuffer]" === c.call(w) ? new Uint8Array(w) : w, i = r.inflateSetDictionary(this.strm, u)), i === f.Z_BUF_ERROR && !0 === m && (i = f.Z_OK, m = !1), i !== f.Z_STREAM_END && i !== f.Z_OK) return this.onEnd(i), this.ended = !0, !1;
                    h.next_out && (0 !== h.avail_out && i !== f.Z_STREAM_END && (0 !== h.avail_in || n !== f.Z_FINISH && n !== f.Z_SYNC_FLUSH) || ("string" === this.options.to ? (a = s.utf8border(h.output, h.next_out), l = h.next_out - a, d = s.buf2string(h.output, a), h.next_out = l, h.avail_out = b - l, l && o.arraySet(h.output, h.output, a, l, 0), this.onData(d)) : this.onData(o.shrinkBuf(h.output, h.next_out)))), 0 === h.avail_in && 0 === h.avail_out && (m = !0)
                } while ((h.avail_in > 0 || 0 === h.avail_out) && i !== f.Z_STREAM_END);
                return i === f.Z_STREAM_END && (n = f.Z_FINISH), n === f.Z_FINISH ? (i = r.inflateEnd(this.strm), this.onEnd(i), this.ended = !0, i === f.Z_OK) : n !== f.Z_SYNC_FLUSH || (this.onEnd(f.Z_OK), h.avail_out = 0, !0)
            }, n.prototype.onData = function(e) {
                this.chunks.push(e)
            }, n.prototype.onEnd = function(e) {
                e === f.Z_OK && ("string" === this.options.to ? this.result = this.chunks.join("") : this.result = o.flattenChunks(this.chunks)), this.chunks = [], this.err = e, this.msg = this.strm.msg
            }, i.Inflate = n, i.inflate = a, i.inflateRaw = function(e, t) {
                return t = t || {}, t.raw = !0, a(e, t)
            }, i.ungzip = a
        }, {
            "./utils/common": 1,
            "./utils/strings": 2,
            "./zlib/constants": 4,
            "./zlib/gzheader": 6,
            "./zlib/inflate": 8,
            "./zlib/messages": 10,
            "./zlib/zstream": 11
        }]
    }, {}, [])("/lib/inflate.js")
});
! function e(r, t, n) {
    function o(i, a) {
        if (!t[i]) {
            if (!r[i]) {
                var u = "function" == typeof require && require;
                if (!a && u) return u(i, !0);
                if (s) return s(i, !0);
                throw new Error("Cannot find module '" + i + "'")
            }
            var f = t[i] = {
                exports: {}
            };
            r[i][0].call(f.exports, function(e) {
                var t = r[i][1][e];
                return o(t ? t : e)
            }, f, f.exports, e, r, t, n)
        }
        return t[i].exports
    }
    for (var s = "function" == typeof require && require, i = 0; i < n.length; i++) o(n[i]);
    return o
}({
    1: [function(e) {
        (function() {
            var r = e("./lib/amf/amf.js"),
                t = e("./lib/amf/spec.js"),
                n = e("./lib/type/bytearray.js");
            window.AMF = r, window.Spec = t, window.ByteArray = n
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/fake_aabf2119.js", "/")
    }, {
        "./lib/amf/amf.js": 2,
        "./lib/amf/spec.js": 6,
        "./lib/type/bytearray.js": 14,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    2: [function(e, r) {
        (function() {
            function t(e, r, t, n) {
                n = "undefined" == typeof n ? f : n;
                var o = new d,
                    s = new m(o, n);
                return s.serialize(e, r, t)
            }

            function n(e, r) {
                var t = new c(e),
                    n = new p(t);
                return n.deserialize(r)
            }

            function o(e) {
                return n(e)
            }

            function s(e, r) {
                return t(e, !0, void 0, r)
            }

            function i(e, r) {
                b[e] = r
            }

            function a(e) {
                return e in b ? b[e] : null
            }
            var u = 1,
                f = 0,
                l = "_classMapping";
            r.exports = {
                serialize: t,
                deserialize: n,
                parse: o,
                stringify: s,
                registerClassAlias: i,
                getClassByAlias: a,
                CLASS_MAPPING: u,
                DEFAULT_OPTIONS: f,
                CLASS_MAPPING_FIELD: l
            };
            var d = e("../io/output.js"),
                c = e("../io/input.js"),
                m = e("../amf/serializer.js"),
                p = e("../amf/deserializer.js"),
                b = {}
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/amf/amf.js", "/lib/amf")
    }, {
        "../amf/deserializer.js": 4,
        "../amf/serializer.js": 5,
        "../io/input.js": 11,
        "../io/output.js": 12,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    3: [function(e, r) {
        (function() {
            var t = e("./spec.js"),
                n = e("../type/bytearray.js"),
                o = e("../util/reference-store.js"),
                s = e("../exception/not-supported.js"),
                i = function(e) {
                    this.stream = e, this.referenceStore = new o
                };
            i.prototype = {
                getDataType: function(e) {
                    switch (!0) {
                        case "undefined" == typeof e:
                            return t.AMF3_UNDEFINED;
                        case null === e:
                            return t.AMF3_NULL;
                        case e === !0 || e === !1:
                            return e ? t.AMF3_TRUE : t.AMF3_FALSE;
                        case "number" == typeof e && e % 1 === 0:
                            return e < t.MIN_INT || e > t.MAX_INT ? t.AMF3_DOUBLE : t.AMF3_INT;
                        case "number" == typeof e && e % 1 !== 0:
                            return t.AMF3_DOUBLE;
                        case "string" == typeof e:
                            return t.AMF3_STRING;
                        case e instanceof Date:
                            return t.AMF3_DATE;
                        case e instanceof n:
                            return t.AMF3_BYTE_ARRAY;
                        case e instanceof Array:
                            return t.AMF3_ARRAY;
                        case "object" == typeof e:
                            return t.AMF3_OBJECT;
                        case "function" == typeof e:
                            throw new s("Cannot serialize a function");
                        default:
                            return null
                    }
                }
            }, r.exports = i
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/amf/base.js", "/lib/amf")
    }, {
        "../exception/not-supported.js": 9,
        "../type/bytearray.js": 14,
        "../util/reference-store.js": 18,
        "./spec.js": 6,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    4: [function(e, r) {
        (function() {
            var t = e("./amf.js"),
                n = e("./base.js"),
                o = e("./spec.js"),
                s = e("../util/reference-store.js"),
                i = e("../type/bytearray.js"),
                a = e("utf8"),
                u = e("../exception/deserialization.js"),
                f = function(e) {
                    n.call(this, e)
                };
            f.prototype = new n, f.prototype.constructor = f, f.prototype.deserialize = function(e) {
                //console.log(e);
                var r = this.stream.readByte(e);
                switch (parseInt(r)) {
                    case o.AMF3_UNDEFINED:
                        return void 0;
                    case o.AMF3_NULL:
                        return null;
                    case o.AMF3_FALSE:
                        return !1;
                    case o.AMF3_TRUE:
                        return !0;
                    case o.AMF3_INT:
                        return this.deserializeInt();
                    case o.AMF3_DOUBLE:
                        return this.deserializeDouble();
                    case o.AMF3_STRING:
                        return this.deserializeString();
                    case o.AMF3_DATE:
                        return this.deserializeDate();
                    case o.AMF3_ARRAY:
                        return this.deserializeArray();
                    case o.AMF3_OBJECT:
                        return this.deserializeObject();
                    case o.AMF3_BYTE_ARRAY:
                        return this.deserializeByteArray();
                    default:
                        throw new u("Cannot deserialize type: " + r)
                }
            }, f.prototype.deserializeInt = function() {
                for (var e = 0, r = 0, t = this.stream.readByte(); 0 !== (128 & t) && 3 > r;) e <<= 7, e |= 127 & t, t = this.stream.readByte(), r++;
                return 3 > r ? (e <<= 7, e |= t) : (e <<= 8, e |= t, 0 !== (268435456 & e) && (e |= 3758096384)), e
            }, f.prototype.deserializeDouble = function() {
                return this.stream.readDouble()
            }, f.prototype.deserializeString = function() {
                var e = this.deserializeInt();
                if (0 === (e & o.REFERENCE_BIT)) return e >>= o.REFERENCE_BIT, this.referenceStore.getByReference(e, s.TYPE_STRING);
                var r = e >> o.REFERENCE_BIT,
                    t = a.decode(this.stream.readRawBytes(r));
                return this.referenceStore.addReference(t, s.TYPE_STRING), t
            }, f.prototype.deserializeDate = function() {
                var e = this.deserializeInt();
                if (0 === (e & o.REFERENCE_BIT)) return e >>= o.REFERENCE_BIT, this.referenceStore.getByReference(e, s.TYPE_OBJECT);
                var r = this.stream.readDouble(),
                    t = new Date(r);
                return this.referenceStore.addReference(t, s.TYPE_OBJECT), t
            }, f.prototype.deserializeArray = function() {
                var e = this.deserializeInt();
                if (0 === (e & o.REFERENCE_BIT)) return e >>= o.REFERENCE_BIT, this.referenceStore.getByReference(e, s.TYPE_OBJECT);
                var r = e >> o.REFERENCE_BIT,
                    t = [];
                this.referenceStore.addReference(t, s.TYPE_OBJECT);
                for (var n = this.deserializeString(); n.length > 0;) t[n] = this.deserialize(), n = this.deserializeString();
                for (var i = 0; r > i; i++) t.push(this.deserialize());
                return t
            }, f.prototype.deserializeObject = function() {
                var e = this.deserializeInt();
                if (0 === (e & o.REFERENCE_BIT)) return e >>= o.REFERENCE_BIT, this.referenceStore.getByReference(e, s.TYPE_OBJECT);
                var r = this.deserializeString(),
                    n = {};
                this.referenceStore.addReference(n, s.TYPE_OBJECT);
                for (var i = {}, a = this.deserializeString(); a.length;) i[a] = this.deserialize(), a = this.deserializeString();
                if (r && r.length > 0) {
                    var f = t.getClassByAlias(r);
                    if (!f) throw new u("Class " + r + " cannot be found. Consider registering a class alias.");
                    n = new f, "importData" in n && "function" == typeof n.importData ? n.importData(i) : l(n, i)
                } else l(n, i);
                return n
            };
            var l = function(e, r) {
                try {
                    for (var t in r) {
                        var n = r[t];
                        e[t] = n
                    }
                } catch (o) {
                    throw new u("Property '" + t + "' cannot be set on instance '" + typeof e + "'")
                }
            };
            f.prototype.deserializeByteArray = function() {
                var e = this.deserializeInt();
                if (0 === (e & o.REFERENCE_BIT)) return e >>= o.REFERENCE_BIT, this.referenceStore.getByReference(e, s.TYPE_OBJECT);
                var r = e >> o.REFERENCE_BIT,
                    t = this.stream.readRawBytes(r);
                return new i(t)
            }, r.exports = f
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/amf/deserializer.js", "/lib/amf")
    }, {
        "../exception/deserialization.js": 8,
        "../type/bytearray.js": 14,
        "../util/reference-store.js": 18,
        "./amf.js": 2,
        "./base.js": 3,
        "./spec.js": 6,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19,
        utf8: 23
    }],
    5: [function(e, r) {
        (function() {
            var t = e("./base.js"),
                n = e("./spec.js"),
                o = e("../util/reference-store.js"),
                s = e("../util/object.js"),
                i = (e("../type/bytearray.js"), e("utf8")),
                a = e("../exception/serialization.js"),
                u = function(e, r) {
                    this.options = r, t.call(this, e)
                };
            u.prototype = new t, u.prototype.constructor = u, u.prototype.serialize = function(e, r, t) {
                "undefined" == typeof r && (r = !0);
                var o = t ? t : this.getDataType(e);
                switch (r && this.stream.writeByte(o), o) {
                    case n.AMF3_UNDEFINED:
                    case n.AMF3_NULL:
                    case n.AMF3_FALSE:
                    case n.AMF3_TRUE:
                        break;
                    case n.AMF3_INT:
                        this.serializeInt(e);
                        break;
                    case n.AMF3_DOUBLE:
                        this.serializeDouble(e);
                        break;
                    case n.AMF3_STRING:
                        this.serializeString(e);
                        break;
                    case n.AMF3_DATE:
                        this.serializeDate(e);
                        break;
                    case n.AMF3_ARRAY:
                        this.serializeArray(e);
                        break;
                    case n.AMF3_OBJECT:
                        this.serializeObject(e);
                        break;
                    case n.AMF3_BYTE_ARRAY:
                        this.serializeByteArray(e);
                        break;
                    default:
                        throw new a("Unrecognized AMF type [" + o + "]")
                }
                return this.stream.getRaw()
            }, u.prototype.serializeInt = function(e) {
                if (e < n.MIN_INT || e > n.MAX_INT) throw new a("Integer out of range: " + e);
                e &= 536870911, e < n.MIN_2_BYTE_INT ? this.stream.writeByte(e) : e < n.MIN_3_BYTE_INT ? (this.stream.writeByte(e >> 7 & 127 | 128), this.stream.writeByte(127 & e)) : e < n.MIN_4_BYTE_INT ? (this.stream.writeByte(e >> 14 & 127 | 128), this.stream.writeByte(e >> 7 & 127 | 128), this.stream.writeByte(127 & e)) : (this.stream.writeByte(e >> 22 & 127 | 128), this.stream.writeByte(e >> 15 & 127 | 128), this.stream.writeByte(e >> 8 & 127 | 128), this.stream.writeByte(255 & e))
            }, u.prototype.serializeDouble = function(e) {
                this.stream.writeDouble(e)
            }, u.prototype.serializeString = function(e, r) {
                if (r = "undefined" == typeof r ? !0 : r) {
                    var t = this.referenceStore.getReference(e, o.TYPE_STRING);
                    if (t !== !1) return void this.serializeInt(t << 1)
                }
                var n = i.encode(e);
                this.serializeInt(n.length << 1 | 1), this.stream.writeRaw(n)
            }, u.prototype.serializeDate = function(e) {
                var r = this.referenceStore.getReference(e, o.TYPE_OBJECT);
                return r !== !1 ? void this.serializeInt(r << 1) : void this.serialize(e.getTime(), !0, n.AMF3_DOUBLE)
            }, u.prototype.serializeArray = function(e) {
                var r = this.referenceStore.getReference(e, o.TYPE_OBJECT);
                if (r !== !1) return void this.serializeInt(r << 1);
                var t = null,
                    s = n.isDenseArray(e);
                if (s) {
                    this.serializeInt(e.length << 1 | n.REFERENCE_BIT), this.serializeString("");
                    for (var i in e) t = e[i], this.serialize(t)
                } else {
                    this.serializeInt(1);
                    for (var a in e) t = e[a], this.serializeString(a, !1), this.serialize(t);
                    this.serializeString("")
                }
            }, u.prototype.serializeObject = function(e) {
                var r = this.referenceStore.getReference(e, o.TYPE_OBJECT);
                if (r !== !1) return void this.serializeInt(r << 1);
                var t = e;
                s.isSerializable(e) && (e = e.exportData());
                var n = s.getObjectKeys(e);
                if (this.serializeInt(11), this.serializeString(s.getClassName(t, this.options), !1), n.length > 0)
                    for (var i in n) {
                        var a = n[i],
                            u = e[a];
                        this.serializeString(a, !1), this.serialize(u)
                    }
                this.serializeString("")
            }, u.prototype.serializeByteArray = function(e) {
                if (!("getData" in e)) throw new a("Invalid ByteArray data provided");
                var r = this.referenceStore.getReference(e, o.TYPE_OBJECT);
                return r !== !1 ? void this.serializeInt(r << 1) : (this.serializeInt(e.getData().length << 1 | n.REFERENCE_BIT), void this.stream.writeRaw(e.getData()))
            }, r.exports = u
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/amf/serializer.js", "/lib/amf")
    }, {
        "../exception/serialization.js": 10,
        "../type/bytearray.js": 14,
        "../util/object.js": 17,
        "../util/reference-store.js": 18,
        "./base.js": 3,
        "./spec.js": 6,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19,
        utf8: 23
    }],
    6: [function(e, r) {
        (function() {
            r.exports = {
                AMF3_UNDEFINED: 0,
                AMF3_NULL: 1,
                AMF3_FALSE: 2,
                AMF3_TRUE: 3,
                AMF3_INT: 4,
                AMF3_DOUBLE: 5,
                AMF3_STRING: 6,
                AMF3_XML_DOC: 7,
                AMF3_DATE: 8,
                AMF3_ARRAY: 9,
                AMF3_OBJECT: 10,
                AMF3_XML: 11,
                AMF3_BYTE_ARRAY: 12,
                AMF3_VECTOR_INT: 13,
                AMF3_VECTOR_UINT: 14,
                AMF3_VECTOR_DOUBLE: 15,
                AMF3_VECTOR_OBJECT: 16,
                AMF3_DICTIONARY: 17,
                OBJECT_DYNAMIC: 0,
                REFERENCE_BIT: 1,
                MIN_2_BYTE_INT: 128,
                MIN_3_BYTE_INT: 16384,
                MIN_4_BYTE_INT: 2097152,
                MAX_INT: 268435455,
                MIN_INT: -268435456,
                isLittleEndian: function() {
                    return !0
                },
                isDenseArray: function(e) {
                    if (!e) return !0;
                    var r = 0;
                    for (var t in e) {
                        if (t != r) return !1;
                        r++
                    }
                    return !0
                }
            }
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/amf/spec.js", "/lib/amf")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    7: [function(e, r) {
        (function() {
            var e = function(e, r) {
                this.message = e, this.name = r
            };
            e.prototype = new Error, e.prototype.constructor = e, r.exports = e
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/exception/base.js", "/lib/exception")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    8: [function(e, r) {
        (function() {
            var t = e("./base.js"),
                n = function(e) {
                    t.call(this, e, "DeserializationException")
                };
            n.prototype = new t, n.prototype.constructor = n, r.exports = n
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/exception/deserialization.js", "/lib/exception")
    }, {
        "./base.js": 7,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    9: [function(e, r) {
        (function() {
            var t = e("./base.js"),
                n = function(e) {
                    t.call(this, e, "NotSupportedException")
                };
            n.prototype = new t, n.prototype.constructor = n, r.exports = n
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/exception/not-supported.js", "/lib/exception")
    }, {
        "./base.js": 7,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    10: [function(e, r) {
        (function() {
            var t = e("./base.js"),
                n = function(e) {
                    t.call(this, e, "SerializationException")
                };
            n.prototype = new t, n.prototype.constructor = n, r.exports = n
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/exception/serialization.js", "/lib/exception")
    }, {
        "./base.js": 7,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    11: [function(e, r) {
        (function() {
            var t = e("./stream.js"),
                n = (e("../amf/spec.js"), e("../util/ieee754.js")),
                o = function(e) {
                    this.pointer = 0, t.call(this, e)
                };
            o.prototype = new t, o.prototype.constructor = o, o.prototype.readByte = function() {
                return this.readBytes(1)
            }, o.prototype.readRawByte = function() {
                return this.readBytes(1, !0)
            }, o.prototype.readRawBytes = function(e) {
                return "undefined" == typeof e && (e = 1), this.readBytes(e, !0)
            }, o.prototype.readBytes = function(e, r) {
                if ("undefined" == typeof e && (e = 1), "undefined" == typeof r && (r = !1), value = this.getRaw().substr(this.pointer, e), this.pointer += value.length, r) return value;
                for (var t = "", n = 0; n < value.length; n++) t += value.charCodeAt(n);
                return t
            }, o.prototype.readDouble = function() {
                var e = this.readRawBytes(8);
                return n.unpack(s(e))
            };
            var s = function(e) {
                var r = [],
                    t = e.split("");
                for (var n in t) r.push(t[n].toString().charCodeAt(0));
                return r
            };
            r.exports = o
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/io/input.js", "/lib/io")
    }, {
        "../amf/spec.js": 6,
        "../util/ieee754.js": 15,
        "./stream.js": 13,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    12: [function(e, r) {
        (function() {
            var t = e("./stream.js"),
                n = (e("../amf/spec.js"), e("../util/ieee754.js")),
                o = function(e) {
                    t.call(this, e)
                };
            o.prototype = new t, o.prototype.constructor = o, o.prototype.writeByte = function(e) {
                this.raw += String.fromCharCode(e)
            }, o.prototype.writeDouble = function(e) {
                this.raw += s(n.pack(e, 11, 52))
            }, o.prototype.writeRaw = function(e) {
                this.raw += e
            };
            var s = function(e) {
                var r = "";
                for (var t in e) {
                    var n = e[t];
                    r += String.fromCharCode(parseInt(n))
                }
                return r
            };
            r.exports = o
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/io/output.js", "/lib/io")
    }, {
        "../amf/spec.js": 6,
        "../util/ieee754.js": 15,
        "./stream.js": 13,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    13: [function(e, r) {
        (function() {
            var e = function(e) {
                e && "undefined" != typeof e || (e = ""), this.raw = e.toString()
            };
            e.prototype = {
                getRaw: function() {
                    return this.raw
                },
                toString: function() {
                    return this.getRaw()
                }
            }, r.exports = e
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/io/stream.js", "/lib/io")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    14: [function(e, r) {
        (function() {
            var e = function(e) {
                this.data = e
            };
            e.prototype = {
                getData: function() {
                    return this.data
                },
                setData: function(e) {
                    this.data = e
                },
                toString: function() {
                    return this.getData()
                }
            }, r.exports = e
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/type/bytearray.js", "/lib/type")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    15: [function(e, r) {
        (function() {
            function e(e) {
                var r, t, n, o, s, i, a, u, f = 11,
                    l = 52,
                    d = (1 << f - 1) - 1;
                for (isNaN(e) ? (t = (1 << d) - 1, n = Math.pow(2, l - 1), r = 0) : 1 / 0 === e || e === -1 / 0 ? (t = (1 << d) - 1, n = 0, r = 0 > e ? 1 : 0) : 0 === e ? (t = 0, n = 0, r = 1 / e === -1 / 0 ? 1 : 0) : (r = 0 > e, e = Math.abs(e), e >= Math.pow(2, 1 - d) ? (o = Math.min(Math.floor(Math.log(e) / Math.LN2), d), t = o + d, n = Math.round(e * Math.pow(2, l - o) - Math.pow(2, l))) : (t = 0, n = Math.round(e / Math.pow(2, 1 - d - l)))), i = [], s = l; s; s -= 1) i.push(n % 2 ? 1 : 0), n = Math.floor(n / 2);
                for (s = f; s; s -= 1) i.push(t % 2 ? 1 : 0), t = Math.floor(t / 2);
                for (i.push(r ? 1 : 0), i.reverse(), a = i.join(""), u = []; a.length;) u.push(parseInt(a.substring(0, 8), 2)), a = a.substring(8);
                return u
            }

            function t(e) {
                var r, t, n, o, s, i, a, u, f = 11,
                    l = 52,
                    d = [];
                for (r = e.length; r; r -= 1)
                    for (n = e[r - 1], t = 8; t; t -= 1) d.push(n % 2 ? 1 : 0), n >>= 1;
                return d.reverse(), o = d.join(""), s = (1 << f - 1) - 1, i = parseInt(o.substring(0, 1), 2) ? -1 : 1, a = parseInt(o.substring(1, 1 + f), 2), u = parseInt(o.substring(1 + f), 2), a === (1 << f) - 1 ? 0 !== u ? 0 / 0 : 1 / 0 * i : a > 0 ? i * Math.pow(2, a - s) * (1 + u / Math.pow(2, l)) : 0 !== u ? i * Math.pow(2, -(s - 1)) * (u / Math.pow(2, l)) : 0 > i ? -0 : 0
            }
            r.exports = {
                pack: e,
                unpack: t
            }
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/util/ieee754.js", "/lib/util")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    16: [function(e, r) {
        (function() {
            r.exports = function(e, r, t) {
                if (void 0 === e || null === e) throw new TypeError('"array" is null or not defined');
                var n = e.length >>> 0;
                for (t = +t || 0, 1 / 0 === Math.abs(t) && (t = 0), 0 > t && (t += n, 0 > t && (t = 0)); n > t; t++)
                    if (e[t] === r) return t;
                return -1
            }
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/util/indexof.js", "/lib/util")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    17: [function(e, r) {
        (function() {
            var t = e("./../amf/amf.js"),
                n = function(e) {
                    return e ? "exportData" in e && "importData" in e : !1
                },
                o = function(e, r) {
                    return "object" == typeof e && t.CLASS_MAPPING_FIELD in e ? r & t.CLASS_MAPPING ? e._classMapping : "" : ""
                },
                s = function(e) {
                    if (!e) return [];
                    var r = [];
                    for (var n in e) n != t.CLASS_MAPPING_FIELD && "function" != typeof e[n] && r.push(n);
                    return r
                };
            r.exports = {
                isSerializable: n,
                getClassName: o,
                getObjectKeys: s
            }
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/util/object.js", "/lib/util")
    }, {
        "./../amf/amf.js": 2,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    18: [function(e, r) {
        (function() {
            var t = e("./indexof.js"),
                n = function() {
                    this.store = {}, this.store[n.TYPE_STRING] = [], this.store[n.TYPE_OBJECT] = []
                },
                o = function(e, r) {
                    var n = t(this.store[r], e);
                    return n >= 0 ? n : this.validate(e) ? (this.addReference(e, r), !1) : !1
                },
                s = function(e, r) {
                    if (!this.store.hasOwnProperty(r)) return !1;
                    var t = this.store[r].length;
                    return e >= t ? !1 : t ? this.store[r][e] : !1
                },
                i = function(e) {
                    return null === e || "string" == typeof e && !e.length ? !1 : !0
                },
                a = function(e, r) {
                    return this.validate(e) ? (this.store[r].push(e), e) : !1
                };
            n.prototype = {
                getReference: o,
                addReference: a,
                getByReference: s,
                validate: i
            }, n.TYPE_STRING = "string", n.TYPE_OBJECT = "object", r.exports = n
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/lib/util/reference-store.js", "/lib/util")
    }, {
        "./indexof.js": 16,
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    19: [function(e, r, t) {
        (function(r, n, o) {
            function o(e, r, t) {
                if (!(this instanceof o)) return new o(e, r, t);
                var n = typeof e;
                if ("base64" === r && "string" === n)
                    for (e = L(e); e.length % 4 !== 0;) e += "=";
                var s;
                if ("number" === n) s = F(e);
                else if ("string" === n) s = o.byteLength(e, r);
                else {
                    if ("object" !== n) throw new Error("First argument needs to be a number, array or string.");
                    s = F(e.length)
                }
                var i;
                o._useTypedArrays ? i = M(new Uint8Array(s)) : (i = this, i.length = s, i._isBuffer = !0);
                var a;
                if (o._useTypedArrays && "function" == typeof Uint8Array && e instanceof Uint8Array) i._set(e);
                else if (R(e))
                    for (a = 0; s > a; a++) i[a] = o.isBuffer(e) ? e.readUInt8(a) : e[a];
                else if ("string" === n) i.write(e, 0, r);
                else if ("number" === n && !o._useTypedArrays && !t)
                    for (a = 0; s > a; a++) i[a] = 0;
                return i
            }

            function s(e, r, t, n) {
                t = Number(t) || 0;
                var s = e.length - t;
                n ? (n = Number(n), n > s && (n = s)) : n = s;
                var i = r.length;
                G(i % 2 === 0, "Invalid hex string"), n > i / 2 && (n = i / 2);
                for (var a = 0; n > a; a++) {
                    var u = parseInt(r.substr(2 * a, 2), 16);
                    G(!isNaN(u), "Invalid hex string"), e[t + a] = u
                }
                return o._charsWritten = 2 * a, a
            }

            function i(e, r, t, n) {
                var s = o._charsWritten = O(z(r), e, t, n);
                return s
            }

            function a(e, r, t, n) {
                var s = o._charsWritten = O(U(r), e, t, n);
                return s
            }

            function u(e, r, t, n) {
                return a(e, r, t, n)
            }

            function f(e, r, t, n) {
                var s = o._charsWritten = O(W(r), e, t, n);
                return s
            }

            function l(e, r, t, n) {
                var s = o._charsWritten = O(x(r), e, t, n);
                return s
            }

            function d(e, r, t) {
                return X.fromByteArray(0 === r && t === e.length ? e : e.slice(r, t))
            }

            function c(e, r, t) {
                var n = "",
                    o = "";
                t = Math.min(e.length, t);
                for (var s = r; t > s; s++) e[s] <= 127 ? (n += Y(o) + String.fromCharCode(e[s]), o = "") : o += "%" + e[s].toString(16);
                return n + Y(o)
            }

            function m(e, r, t) {
                var n = "";
                t = Math.min(e.length, t);
                for (var o = r; t > o; o++) n += String.fromCharCode(e[o]);
                return n
            }

            function p(e, r, t) {
                return m(e, r, t)
            }

            function b(e, r, t) {
                var n = e.length;
                (!r || 0 > r) && (r = 0), (!t || 0 > t || t > n) && (t = n);
                for (var o = "", s = r; t > s; s++) o += C(e[s]);
                return o
            }

            function h(e, r, t) {
                for (var n = e.slice(r, t), o = "", s = 0; s < n.length; s += 2) o += String.fromCharCode(n[s] + 256 * n[s + 1]);
                return o
            }

            function y(e, r, t, n) {
                n || (G("boolean" == typeof t, "missing or invalid endian"), G(void 0 !== r && null !== r, "missing offset"), G(r + 1 < e.length, "Trying to read beyond buffer length"));
                var o = e.length;
                if (!(r >= o)) {
                    var s;
                    return t ? (s = e[r], o > r + 1 && (s |= e[r + 1] << 8)) : (s = e[r] << 8, o > r + 1 && (s |= e[r + 1])), s
                }
            }

            function g(e, r, t, n) {
                n || (G("boolean" == typeof t, "missing or invalid endian"), G(void 0 !== r && null !== r, "missing offset"), G(r + 3 < e.length, "Trying to read beyond buffer length"));
                var o = e.length;
                if (!(r >= o)) {
                    var s;
                    return t ? (o > r + 2 && (s = e[r + 2] << 16), o > r + 1 && (s |= e[r + 1] << 8), s |= e[r], o > r + 3 && (s += e[r + 3] << 24 >>> 0)) : (o > r + 1 && (s = e[r + 1] << 16), o > r + 2 && (s |= e[r + 2] << 8), o > r + 3 && (s |= e[r + 3]), s += e[r] << 24 >>> 0), s
                }
            }

            function w(e, r, t, n) {
                n || (G("boolean" == typeof t, "missing or invalid endian"), G(void 0 !== r && null !== r, "missing offset"), G(r + 1 < e.length, "Trying to read beyond buffer length"));
                var o = e.length;
                if (!(r >= o)) {
                    var s = y(e, r, t, !0),
                        i = 32768 & s;
                    return i ? -1 * (65535 - s + 1) : s
                }
            }

            function _(e, r, t, n) {
                n || (G("boolean" == typeof t, "missing or invalid endian"), G(void 0 !== r && null !== r, "missing offset"), G(r + 3 < e.length, "Trying to read beyond buffer length"));
                var o = e.length;
                if (!(r >= o)) {
                    var s = g(e, r, t, !0),
                        i = 2147483648 & s;
                    return i ? -1 * (4294967295 - s + 1) : s
                }
            }

            function v(e, r, t, n) {
                return n || (G("boolean" == typeof t, "missing or invalid endian"), G(r + 3 < e.length, "Trying to read beyond buffer length")), q.read(e, r, t, 23, 4)
            }

            function j(e, r, t, n) {
                return n || (G("boolean" == typeof t, "missing or invalid endian"), G(r + 7 < e.length, "Trying to read beyond buffer length")), q.read(e, r, t, 52, 8)
            }

            function E(e, r, t, n, o) {
                o || (G(void 0 !== r && null !== r, "missing value"), G("boolean" == typeof n, "missing or invalid endian"), G(void 0 !== t && null !== t, "missing offset"), G(t + 1 < e.length, "trying to write beyond buffer length"), k(r, 65535));
                var s = e.length;
                if (!(t >= s))
                    for (var i = 0, a = Math.min(s - t, 2); a > i; i++) e[t + i] = (r & 255 << 8 * (n ? i : 1 - i)) >>> 8 * (n ? i : 1 - i)
            }

            function I(e, r, t, n, o) {
                o || (G(void 0 !== r && null !== r, "missing value"), G("boolean" == typeof n, "missing or invalid endian"), G(void 0 !== t && null !== t, "missing offset"), G(t + 3 < e.length, "trying to write beyond buffer length"), k(r, 4294967295));
                var s = e.length;
                if (!(t >= s))
                    for (var i = 0, a = Math.min(s - t, 4); a > i; i++) e[t + i] = r >>> 8 * (n ? i : 3 - i) & 255
            }

            function A(e, r, t, n, o) {
                o || (G(void 0 !== r && null !== r, "missing value"), G("boolean" == typeof n, "missing or invalid endian"), G(void 0 !== t && null !== t, "missing offset"), G(t + 1 < e.length, "Trying to write beyond buffer length"), P(r, 32767, -32768));
                var s = e.length;
                t >= s || (r >= 0 ? E(e, r, t, n, o) : E(e, 65535 + r + 1, t, n, o))
            }

            function B(e, r, t, n, o) {
                o || (G(void 0 !== r && null !== r, "missing value"), G("boolean" == typeof n, "missing or invalid endian"), G(void 0 !== t && null !== t, "missing offset"), G(t + 3 < e.length, "Trying to write beyond buffer length"), P(r, 2147483647, -2147483648));
                var s = e.length;
                t >= s || (r >= 0 ? I(e, r, t, n, o) : I(e, 4294967295 + r + 1, t, n, o))
            }

            function S(e, r, t, n, o) {
                o || (G(void 0 !== r && null !== r, "missing value"), G("boolean" == typeof n, "missing or invalid endian"), G(void 0 !== t && null !== t, "missing offset"), G(t + 3 < e.length, "Trying to write beyond buffer length"), J(r, 34028234663852886e22, -34028234663852886e22));
                var s = e.length;
                t >= s || q.write(e, r, t, n, 23, 4)
            }

            function T(e, r, t, n, o) {
                o || (G(void 0 !== r && null !== r, "missing value"), G("boolean" == typeof n, "missing or invalid endian"), G(void 0 !== t && null !== t, "missing offset"), G(t + 7 < e.length, "Trying to write beyond buffer length"), J(r, 17976931348623157e292, -17976931348623157e292));
                var s = e.length;
                t >= s || q.write(e, r, t, n, 52, 8)
            }

            function L(e) {
                return e.trim ? e.trim() : e.replace(/^\s+|\s+$/g, "")
            }

            function M(e) {
                return e._isBuffer = !0, e._get = e.get, e._set = e.set, e.get = V.get, e.set = V.set, e.write = V.write, e.toString = V.toString, e.toLocaleString = V.toString, e.toJSON = V.toJSON, e.copy = V.copy, e.slice = V.slice, e.readUInt8 = V.readUInt8, e.readUInt16LE = V.readUInt16LE, e.readUInt16BE = V.readUInt16BE, e.readUInt32LE = V.readUInt32LE, e.readUInt32BE = V.readUInt32BE, e.readInt8 = V.readInt8, e.readInt16LE = V.readInt16LE, e.readInt16BE = V.readInt16BE, e.readInt32LE = V.readInt32LE, e.readInt32BE = V.readInt32BE, e.readFloatLE = V.readFloatLE, e.readFloatBE = V.readFloatBE, e.readDoubleLE = V.readDoubleLE, e.readDoubleBE = V.readDoubleBE, e.writeUInt8 = V.writeUInt8, e.writeUInt16LE = V.writeUInt16LE, e.writeUInt16BE = V.writeUInt16BE, e.writeUInt32LE = V.writeUInt32LE, e.writeUInt32BE = V.writeUInt32BE, e.writeInt8 = V.writeInt8, e.writeInt16LE = V.writeInt16LE, e.writeInt16BE = V.writeInt16BE, e.writeInt32LE = V.writeInt32LE, e.writeInt32BE = V.writeInt32BE, e.writeFloatLE = V.writeFloatLE, e.writeFloatBE = V.writeFloatBE, e.writeDoubleLE = V.writeDoubleLE, e.writeDoubleBE = V.writeDoubleBE, e.fill = V.fill, e.inspect = V.inspect, e.toArrayBuffer = V.toArrayBuffer, e
            }

            function D(e, r, t) {
                return "number" != typeof e ? t : (e = ~~e, e >= r ? r : e >= 0 ? e : (e += r, e >= 0 ? e : 0))
            }

            function F(e) {
                return e = ~~Math.ceil(+e), 0 > e ? 0 : e
            }

            function N(e) {
                return (Array.isArray || function(e) {
                    return "[object Array]" === Object.prototype.toString.call(e)
                })(e)
            }

            function R(e) {
                return N(e) || o.isBuffer(e) || e && "object" == typeof e && "number" == typeof e.length
            }

            function C(e) {
                return 16 > e ? "0" + e.toString(16) : e.toString(16)
            }

            function z(e) {
                for (var r = [], t = 0; t < e.length; t++) {
                    var n = e.charCodeAt(t);
                    if (127 >= n) r.push(e.charCodeAt(t));
                    else {
                        var o = t;
                        n >= 55296 && 57343 >= n && t++;
                        for (var s = encodeURIComponent(e.slice(o, t + 1)).substr(1).split("%"), i = 0; i < s.length; i++) r.push(parseInt(s[i], 16))
                    }
                }
                return r
            }

            function U(e) {
                for (var r = [], t = 0; t < e.length; t++) r.push(255 & e.charCodeAt(t));
                return r
            }

            function x(e) {
                for (var r, t, n, o = [], s = 0; s < e.length; s++) r = e.charCodeAt(s), t = r >> 8, n = r % 256, o.push(n), o.push(t);
                return o
            }

            function W(e) {
                return X.toByteArray(e)
            }

            function O(e, r, t, n) {
                for (var o = 0; n > o && !(o + t >= r.length || o >= e.length); o++) r[o + t] = e[o];
                return o
            }

            function Y(e) {
                try {
                    return decodeURIComponent(e)
                } catch (r) {
                    return String.fromCharCode(65533)
                }
            }

            function k(e, r) {
                G("number" == typeof e, "cannot write a non-number as a number"), G(e >= 0, "specified a negative value for writing an unsigned value"), G(r >= e, "value is larger than maximum value for type"), G(Math.floor(e) === e, "value has a fractional component")
            }

            function P(e, r, t) {
                G("number" == typeof e, "cannot write a non-number as a number"), G(r >= e, "value larger than maximum allowed value"), G(e >= t, "value smaller than minimum allowed value"), G(Math.floor(e) === e, "value has a fractional component")
            }

            function J(e, r, t) {
                G("number" == typeof e, "cannot write a non-number as a number"), G(r >= e, "value larger than maximum allowed value"), G(e >= t, "value smaller than minimum allowed value")
            }

            function G(e, r) {
                if (!e) throw new Error(r || "Failed assertion")
            }
            var X = e("base64-js"),
                q = e("ieee754");
            t.Buffer = o, t.SlowBuffer = o, t.INSPECT_MAX_BYTES = 50, o.poolSize = 8192, o._useTypedArrays = function() {
                if ("undefined" == typeof Uint8Array || "undefined" == typeof ArrayBuffer) return !1;
                try {
                    var e = new Uint8Array(0);
                    return e.foo = function() {
                        return 42
                    }, 42 === e.foo() && "function" == typeof e.subarray
                } catch (r) {
                    return !1
                }
            }(), o.isEncoding = function(e) {
                switch (String(e).toLowerCase()) {
                    case "hex":
                    case "utf8":
                    case "utf-8":
                    case "ascii":
                    case "binary":
                    case "base64":
                    case "raw":
                    case "ucs2":
                    case "ucs-2":
                    case "utf16le":
                    case "utf-16le":
                        return !0;
                    default:
                        return !1
                }
            }, o.isBuffer = function(e) {
                return !(null === e || void 0 === e || !e._isBuffer)
            }, o.byteLength = function(e, r) {
                var t;
                switch (e += "", r || "utf8") {
                    case "hex":
                        t = e.length / 2;
                        break;
                    case "utf8":
                    case "utf-8":
                        t = z(e).length;
                        break;
                    case "ascii":
                    case "binary":
                    case "raw":
                        t = e.length;
                        break;
                    case "base64":
                        t = W(e).length;
                        break;
                    case "ucs2":
                    case "ucs-2":
                    case "utf16le":
                    case "utf-16le":
                        t = 2 * e.length;
                        break;
                    default:
                        throw new Error("Unknown encoding")
                }
                return t
            }, o.concat = function(e, r) {
                if (G(N(e), "Usage: Buffer.concat(list, [totalLength])\nlist should be an Array."), 0 === e.length) return new o(0);
                if (1 === e.length) return e[0];
                var t;
                if ("number" != typeof r)
                    for (r = 0, t = 0; t < e.length; t++) r += e[t].length;
                var n = new o(r),
                    s = 0;
                for (t = 0; t < e.length; t++) {
                    var i = e[t];
                    i.copy(n, s), s += i.length
                }
                return n
            }, o.prototype.write = function(e, r, t, n) {
                if (isFinite(r)) isFinite(t) || (n = t, t = void 0);
                else {
                    var o = n;
                    n = r, r = t, t = o
                }
                r = Number(r) || 0;
                var d = this.length - r;
                t ? (t = Number(t), t > d && (t = d)) : t = d, n = String(n || "utf8").toLowerCase();
                var c;
                switch (n) {
                    case "hex":
                        c = s(this, e, r, t);
                        break;
                    case "utf8":
                    case "utf-8":
                        c = i(this, e, r, t);
                        break;
                    case "ascii":
                        c = a(this, e, r, t);
                        break;
                    case "binary":
                        c = u(this, e, r, t);
                        break;
                    case "base64":
                        c = f(this, e, r, t);
                        break;
                    case "ucs2":
                    case "ucs-2":
                    case "utf16le":
                    case "utf-16le":
                        c = l(this, e, r, t);
                        break;
                    default:
                        throw new Error("Unknown encoding")
                }
                return c
            }, o.prototype.toString = function(e, r, t) {
                var n = this;
                if (e = String(e || "utf8").toLowerCase(), r = Number(r) || 0, t = void 0 !== t ? Number(t) : t = n.length, t === r) return "";
                var o;
                switch (e) {
                    case "hex":
                        o = b(n, r, t);
                        break;
                    case "utf8":
                    case "utf-8":
                        o = c(n, r, t);
                        break;
                    case "ascii":
                        o = m(n, r, t);
                        break;
                    case "binary":
                        o = p(n, r, t);
                        break;
                    case "base64":
                        o = d(n, r, t);
                        break;
                    case "ucs2":
                    case "ucs-2":
                    case "utf16le":
                    case "utf-16le":
                        o = h(n, r, t);
                        break;
                    default:
                        throw new Error("Unknown encoding")
                }
                return o
            }, o.prototype.toJSON = function() {
                return {
                    type: "Buffer",
                    data: Array.prototype.slice.call(this._arr || this, 0)
                }
            }, o.prototype.copy = function(e, r, t, n) {
                var o = this;
                if (t || (t = 0), n || 0 === n || (n = this.length), r || (r = 0), n !== t && 0 !== e.length && 0 !== o.length) {
                    G(n >= t, "sourceEnd < sourceStart"), G(r >= 0 && r < e.length, "targetStart out of bounds"), G(t >= 0 && t < o.length, "sourceStart out of bounds"), G(n >= 0 && n <= o.length, "sourceEnd out of bounds"), n > this.length && (n = this.length), e.length - r < n - t && (n = e.length - r + t);
                    for (var s = 0; n - t > s; s++) e[s + r] = this[s + t]
                }
            }, o.prototype.slice = function(e, r) {
                var t = this.length;
                if (e = D(e, t, 0), r = D(r, t, t), o._useTypedArrays) return M(this.subarray(e, r));
                for (var n = r - e, s = new o(n, void 0, !0), i = 0; n > i; i++) s[i] = this[i + e];
                return s
            }, o.prototype.get = function(e) {
                return console.log(".get() is deprecated. Access using array indexes instead."), this.readUInt8(e)
            }, o.prototype.set = function(e, r) {
                return console.log(".set() is deprecated. Access using array indexes instead."), this.writeUInt8(e, r)
            }, o.prototype.readUInt8 = function(e, r) {
                return r || (G(void 0 !== e && null !== e, "missing offset"), G(e < this.length, "Trying to read beyond buffer length")), e >= this.length ? void 0 : this[e]
            }, o.prototype.readUInt16LE = function(e, r) {
                return y(this, e, !0, r)
            }, o.prototype.readUInt16BE = function(e, r) {
                return y(this, e, !1, r)
            }, o.prototype.readUInt32LE = function(e, r) {
                return g(this, e, !0, r)
            }, o.prototype.readUInt32BE = function(e, r) {
                return g(this, e, !1, r)
            }, o.prototype.readInt8 = function(e, r) {
                if (r || (G(void 0 !== e && null !== e, "missing offset"), G(e < this.length, "Trying to read beyond buffer length")), !(e >= this.length)) {
                    var t = 128 & this[e];
                    return t ? -1 * (255 - this[e] + 1) : this[e]
                }
            }, o.prototype.readInt16LE = function(e, r) {
                return w(this, e, !0, r)
            }, o.prototype.readInt16BE = function(e, r) {
                return w(this, e, !1, r)
            }, o.prototype.readInt32LE = function(e, r) {
                return _(this, e, !0, r)
            }, o.prototype.readInt32BE = function(e, r) {
                return _(this, e, !1, r)
            }, o.prototype.readFloatLE = function(e, r) {
                return v(this, e, !0, r)
            }, o.prototype.readFloatBE = function(e, r) {
                return v(this, e, !1, r)
            }, o.prototype.readDoubleLE = function(e, r) {
                return j(this, e, !0, r)
            }, o.prototype.readDoubleBE = function(e, r) {
                return j(this, e, !1, r)
            }, o.prototype.writeUInt8 = function(e, r, t) {
                t || (G(void 0 !== e && null !== e, "missing value"), G(void 0 !== r && null !== r, "missing offset"), G(r < this.length, "trying to write beyond buffer length"), k(e, 255)), r >= this.length || (this[r] = e)
            }, o.prototype.writeUInt16LE = function(e, r, t) {
                E(this, e, r, !0, t)
            }, o.prototype.writeUInt16BE = function(e, r, t) {
                E(this, e, r, !1, t)
            }, o.prototype.writeUInt32LE = function(e, r, t) {
                I(this, e, r, !0, t)
            }, o.prototype.writeUInt32BE = function(e, r, t) {
                I(this, e, r, !1, t)
            }, o.prototype.writeInt8 = function(e, r, t) {
                t || (G(void 0 !== e && null !== e, "missing value"), G(void 0 !== r && null !== r, "missing offset"), G(r < this.length, "Trying to write beyond buffer length"), P(e, 127, -128)), r >= this.length || (e >= 0 ? this.writeUInt8(e, r, t) : this.writeUInt8(255 + e + 1, r, t))
            }, o.prototype.writeInt16LE = function(e, r, t) {
                A(this, e, r, !0, t)
            }, o.prototype.writeInt16BE = function(e, r, t) {
                A(this, e, r, !1, t)
            }, o.prototype.writeInt32LE = function(e, r, t) {
                B(this, e, r, !0, t)
            }, o.prototype.writeInt32BE = function(e, r, t) {
                B(this, e, r, !1, t)
            }, o.prototype.writeFloatLE = function(e, r, t) {
                S(this, e, r, !0, t)
            }, o.prototype.writeFloatBE = function(e, r, t) {
                S(this, e, r, !1, t)
            }, o.prototype.writeDoubleLE = function(e, r, t) {
                T(this, e, r, !0, t)
            }, o.prototype.writeDoubleBE = function(e, r, t) {
                T(this, e, r, !1, t)
            }, o.prototype.fill = function(e, r, t) {
                if (e || (e = 0), r || (r = 0), t || (t = this.length), "string" == typeof e && (e = e.charCodeAt(0)), G("number" == typeof e && !isNaN(e), "value is not a number"), G(t >= r, "end < start"), t !== r && 0 !== this.length) {
                    G(r >= 0 && r < this.length, "start out of bounds"), G(t >= 0 && t <= this.length, "end out of bounds");
                    for (var n = r; t > n; n++) this[n] = e
                }
            }, o.prototype.inspect = function() {
                for (var e = [], r = this.length, n = 0; r > n; n++)
                    if (e[n] = C(this[n]), n === t.INSPECT_MAX_BYTES) {
                        e[n + 1] = "...";
                        break
                    } return "<Buffer " + e.join(" ") + ">"
            }, o.prototype.toArrayBuffer = function() {
                if ("function" == typeof Uint8Array) {
                    if (o._useTypedArrays) return new o(this).buffer;
                    for (var e = new Uint8Array(this.length), r = 0, t = e.length; t > r; r += 1) e[r] = this[r];
                    return e.buffer
                }
                throw new Error("Buffer.toArrayBuffer not supported in this browser")
            };
            var V = o.prototype
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/browserify/node_modules/buffer/index.js", "/node_modules/browserify/node_modules/buffer")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        "base64-js": 20,
        buffer: 19,
        ieee754: 21
    }],
    20: [function(e, r) {
        (function() {
            var e = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
            ! function() {
                "use strict";

                function t(e) {
                    var r = e.charCodeAt(0);
                    return r === i ? 62 : r === a ? 63 : u > r ? -1 : u + 10 > r ? r - u + 26 + 26 : l + 26 > r ? r - l : f + 26 > r ? r - f + 26 : void 0
                }

                function n(e) {
                    function r(e) {
                        f[d++] = e
                    }
                    var n, o, i, a, u, f;
                    if (e.length % 4 > 0) throw new Error("Invalid string. Length must be a multiple of 4");
                    var l = e.length;
                    u = "=" === e.charAt(l - 2) ? 2 : "=" === e.charAt(l - 1) ? 1 : 0, f = new s(3 * e.length / 4 - u), i = u > 0 ? e.length - 4 : e.length;
                    var d = 0;
                    for (n = 0, o = 0; i > n; n += 4, o += 3) a = t(e.charAt(n)) << 18 | t(e.charAt(n + 1)) << 12 | t(e.charAt(n + 2)) << 6 | t(e.charAt(n + 3)), r((16711680 & a) >> 16), r((65280 & a) >> 8), r(255 & a);
                    return 2 === u ? (a = t(e.charAt(n)) << 2 | t(e.charAt(n + 1)) >> 4, r(255 & a)) : 1 === u && (a = t(e.charAt(n)) << 10 | t(e.charAt(n + 1)) << 4 | t(e.charAt(n + 2)) >> 2, r(a >> 8 & 255), r(255 & a)), f
                }

                function o(r) {
                    function t(r) {
                        return e.charAt(r)
                    }

                    function n(e) {
                        return t(e >> 18 & 63) + t(e >> 12 & 63) + t(e >> 6 & 63) + t(63 & e)
                    }
                    var o, s, i, a = r.length % 3,
                        u = "";
                    for (o = 0, i = r.length - a; i > o; o += 3) s = (r[o] << 16) + (r[o + 1] << 8) + r[o + 2], u += n(s);
                    switch (a) {
                        case 1:
                            s = r[r.length - 1], u += t(s >> 2), u += t(s << 4 & 63), u += "==";
                            break;
                        case 2:
                            s = (r[r.length - 2] << 8) + r[r.length - 1], u += t(s >> 10), u += t(s >> 4 & 63), u += t(s << 2 & 63), u += "="
                    }
                    return u
                }
                var s = "undefined" != typeof Uint8Array ? Uint8Array : Array,
                    i = ("0".charCodeAt(0), "+".charCodeAt(0)),
                    a = "/".charCodeAt(0),
                    u = "0".charCodeAt(0),
                    f = "a".charCodeAt(0),
                    l = "A".charCodeAt(0);
                r.exports.toByteArray = n, r.exports.fromByteArray = o
            }()
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/browserify/node_modules/buffer/node_modules/base64-js/lib/b64.js", "/node_modules/browserify/node_modules/buffer/node_modules/base64-js/lib")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    21: [function(e, r, t) {
        (function() {
            t.read = function(e, r, t, n, o) {
                var s, i, a = 8 * o - n - 1,
                    u = (1 << a) - 1,
                    f = u >> 1,
                    l = -7,
                    d = t ? o - 1 : 0,
                    c = t ? -1 : 1,
                    m = e[r + d];
                for (d += c, s = m & (1 << -l) - 1, m >>= -l, l += a; l > 0; s = 256 * s + e[r + d], d += c, l -= 8);
                for (i = s & (1 << -l) - 1, s >>= -l, l += n; l > 0; i = 256 * i + e[r + d], d += c, l -= 8);
                if (0 === s) s = 1 - f;
                else {
                    if (s === u) return i ? 0 / 0 : 1 / 0 * (m ? -1 : 1);
                    i += Math.pow(2, n), s -= f
                }
                return (m ? -1 : 1) * i * Math.pow(2, s - n)
            }, t.write = function(e, r, t, n, o, s) {
                var i, a, u, f = 8 * s - o - 1,
                    l = (1 << f) - 1,
                    d = l >> 1,
                    c = 23 === o ? Math.pow(2, -24) - Math.pow(2, -77) : 0,
                    m = n ? 0 : s - 1,
                    p = n ? 1 : -1,
                    b = 0 > r || 0 === r && 0 > 1 / r ? 1 : 0;
                for (r = Math.abs(r), isNaN(r) || 1 / 0 === r ? (a = isNaN(r) ? 1 : 0, i = l) : (i = Math.floor(Math.log(r) / Math.LN2), r * (u = Math.pow(2, -i)) < 1 && (i--, u *= 2), r += i + d >= 1 ? c / u : c * Math.pow(2, 1 - d), r * u >= 2 && (i++, u /= 2), i + d >= l ? (a = 0, i = l) : i + d >= 1 ? (a = (r * u - 1) * Math.pow(2, o), i += d) : (a = r * Math.pow(2, d - 1) * Math.pow(2, o), i = 0)); o >= 8; e[t + m] = 255 & a, m += p, a /= 256, o -= 8);
                for (i = i << o | a, f += o; f > 0; e[t + m] = 255 & i, m += p, i /= 256, f -= 8);
                e[t + m - p] |= 128 * b
            }
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/browserify/node_modules/buffer/node_modules/ieee754/index.js", "/node_modules/browserify/node_modules/buffer/node_modules/ieee754")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    22: [function(e, r) {
        (function(e) {
            var e = r.exports = {};
            e.nextTick = function() {
                var e = "undefined" != typeof window && window.setImmediate,
                    r = "undefined" != typeof window && window.postMessage && window.addEventListener;
                if (e) return function(e) {
                    return window.setImmediate(e)
                };
                if (r) {
                    var t = [];
                    return window.addEventListener("message", function(e) {
                        var r = e.source;
                        if ((r === window || null === r) && "process-tick" === e.data && (e.stopPropagation(), t.length > 0)) {
                            var n = t.shift();
                            n()
                        }
                    }, !0),
                        function(e) {
                            t.push(e), window.postMessage("process-tick", "*")
                        }
                }
                return function(e) {
                    setTimeout(e, 0)
                }
            }(), e.title = "browser", e.browser = !0, e.env = {}, e.argv = [], e.binding = function() {
                throw new Error("process.binding is not supported")
            }, e.cwd = function() {
                return "/"
            }, e.chdir = function() {
                throw new Error("process.chdir is not supported")
            }
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js", "/node_modules/browserify/node_modules/insert-module-globals/node_modules/process")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }],
    23: [function(e, r, t) {
        (function(e, n) {
            ! function(e) {
                function o(e) {
                    for (var r, t, n = [], o = 0, s = e.length; s > o;) r = e.charCodeAt(o++), r >= 55296 && 56319 >= r && s > o ? (t = e.charCodeAt(o++), 56320 == (64512 & t) ? n.push(((1023 & r) << 10) + (1023 & t) + 65536) : (n.push(r), o--)) : n.push(r);
                    return n
                }

                function s(e) {
                    for (var r, t = e.length, n = -1, o = ""; ++n < t;) r = e[n], r > 65535 && (r -= 65536, o += g(r >>> 10 & 1023 | 55296), r = 56320 | 1023 & r), o += g(r);
                    return o
                }

                function i(e, r) {
                    return g(e >> r & 63 | 128)
                }

                function a(e) {
                    if (0 == (4294967168 & e)) return g(e);
                    var r = "";
                    return 0 == (4294965248 & e) ? r = g(e >> 6 & 31 | 192) : 0 == (4294901760 & e) ? (r = g(e >> 12 & 15 | 224), r += i(e, 6)) : 0 == (4292870144 & e) && (r = g(e >> 18 & 7 | 240), r += i(e, 12), r += i(e, 6)), r += g(63 & e | 128)
                }

                function u(e) {
                    for (var r, t = o(e), n = t.length, s = -1, i = ""; ++s < n;) r = t[s], i += a(r);
                    return i
                }

                function f() {
                    if (y >= h) throw Error("Invalid byte index");
                    var e = 255 & b[y];
                    if (y++, 128 == (192 & e)) return 63 & e;
                    throw Error("Invalid continuation byte")
                }

                function l() {
                    var e, r, t, n, o;
                    if (y > h) throw Error("Invalid byte index");
                    if (y == h) return !1;
                    if (e = 255 & b[y], y++, 0 == (128 & e)) return e;
                    if (192 == (224 & e)) {
                        var r = f();
                        if (o = (31 & e) << 6 | r, o >= 128) return o;
                        throw Error("Invalid continuation byte")
                    }
                    if (224 == (240 & e)) {
                        if (r = f(), t = f(), o = (15 & e) << 12 | r << 6 | t, o >= 2048) return o;
                        throw Error("Invalid continuation byte")
                    }
                    if (240 == (248 & e) && (r = f(), t = f(), n = f(), o = (15 & e) << 18 | r << 12 | t << 6 | n, o >= 65536 && 1114111 >= o)) return o;
                    throw Error("Invalid UTF-8 detected")
                }

                function d(e) {
                    b = o(e), h = b.length, y = 0;
                    for (var r, t = [];
                         (r = l()) !== !1;) t.push(r);
                    return s(t)
                }
                var c = "object" == typeof t && t,
                    m = "object" == typeof r && r && r.exports == c && r,
                    p = "object" == typeof n && n;
                (p.global === p || p.window === p) && (e = p);
                var b, h, y, g = String.fromCharCode,
                    w = {
                        version: "2.0.0",
                        encode: u,
                        decode: d
                    };
                if ("function" == typeof define && "object" == typeof define.amd && define.amd) define(function() {
                    return w
                });
                else if (c && !c.nodeType)
                    if (m) m.exports = w;
                    else {
                        var _ = {},
                            v = _.hasOwnProperty;
                        for (var j in w) v.call(w, j) && (c[j] = w[j])
                    }
                else e.utf8 = w
            }(this)
        }).call(this, e("/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js"), "undefined" != typeof self ? self : "undefined" != typeof window ? window : {}, e("buffer").Buffer, arguments[3], arguments[4], arguments[5], arguments[6], "/node_modules/utf8/utf8.js", "/node_modules/utf8")
    }, {
        "/Library/WebServer/Documents/projects/amf/js/node_modules/browserify/node_modules/insert-module-globals/node_modules/process/browser.js": 22,
        buffer: 19
    }]
}, {}, [1]);
var canvas = {
    animation: {},
    utils: {},
    ui: {},
    px: {
        Loader: PIXI.loader,
        Container: PIXI.Container,
        Sprite: PIXI.Sprite,
        Ticker: PIXI.ticker.Ticker,
        Application: PIXI.Application,
        ColorMatrixFilter: PIXI.filters.ColorMatrixFilter,
        TextureEmpty: PIXI.Texture.EMPTY,
        SlicedSprite: PIXI.mesh.NineSlicePlane,
        Graphics: PIXI.Graphics,
        Text: PIXI.Text,
        BitmapText: PIXI.extras.BitmapText,
        Point: PIXI.Point,
        Rectangle: PIXI.Rectangle,
        Circle: PIXI.Circle,
        Ellipse: PIXI.Ellipse,
        BlendMode: PIXI.BLEND_MODES,
        ScaleMode: PIXI.SCALE_MODES,
        GlowFilter: PIXI.filters.GlowFilter,
        TiltShiftFilter: PIXI.filters.TiltShiftFilter,
        DropShadowFilter: PIXI.filters.DropShadowFilter,
        AlphaFilter: PIXI.filters.AlphaFilter,
        TilingSprite: PIXI.extras.TilingSprite,
        utils: PIXI.utils,
        Texture: PIXI.Texture,
        ResponseType: PIXI.loaders.Resource.XHR_RESPONSE_TYPE,
        LoadType: PIXI.loaders.Resource.LOAD_TYPE,
        Tween: TWEEN,
        inflate: pako.inflate,
        Howl: Howl,
        AMF: AMF
    },
    data: {
        battle: {},
        location: {},
        manor: {
            throne: {}
        },
        leftMenu: {},
        cube: {},
        clanWar: {}
    },
    app: {
        view: {
            window: {}
        },
        battle: {
            view: {
                elements: {}
            },
            engine: {}
        },
        mem: {
            view: {}
        },
        location: {
            view: {
                elements: {},
                popups: {},
                fronts: {}
            }
        },
        world: {
            view: {},
            engine: {},
            manor: {
                popup: {}
            }
        },
        manor: {
            view: {
                controls: {
                    elements: {},
                    camp: {},
                    throne: {}
                }
            }
        },
        hunt: {
            view: {
                elements: {}
            },
            engine: {}
        },
        inst: {
            view: {
                combo: {}
            }
        },
        user: {
            view: {}
        },
        leftMenu: {
            view: {}
        },
        avatar: {
            view: {}
        },
        topMenu: {
            view: {}
        },
        rightMenu: {
            view: {
                menu: {}
            }
        },
        compass: {
            view: {}
        },
        magic: {
            view: {
                slots: {}
            }
        },
        birthday: {
            view: {}
        },
        cube: {
            view: {}
        },
        casino: {
            view: {}
        },
        diceGame: {
            view: {}
        },
        treasure: {
            view: {}
        },
        wheel: {
            view: {}
        },
        mirror: {
            view: {}
        },
        clanWar: {
            view: {}
        },
        clanCitadel: {
            view: {
                sanctuary: {}
            }
        },
        barber: {},
        firstBattle: {
            view: {}
        },
        puzzle: {
            view: {}
        }
    },
    modules: {},
    isSupported: function() {
        var result = /Chrome\/(\d+)/.exec(navigator.userAgent);
        if (result && result[1] && parseInt(result[1]) < 30) {
            return false
        }
        return true
    },
    isMobile: function() {
        return /iPhone|iPad|iPod|Android|BlackBerry|BB10|Silk|Mobi/i.test(navigator && navigator.userAgent)
    },
    isWeakHardware: function () {
        if(canvas.isMobile()) { return true; }
        try{ if(top.gpuTier == 1) { return true; } } catch (e) {}
        try{ if(navigator.hardwareConcurrency > 0 && navigator.hardwareConcurrency <= 3) { return true; } }catch(e){}
        return false;
    },
};
canvas.px.Container.prototype.startDrag = function(lockPoint, bounds, startPoint, dragScale) {
    this._dragScale = dragScale || 1;
    this._dragLockPoint = lockPoint || new canvas.px.Point;
    this._dragBounds = bounds;
    this._dragStartPosition = this.parent.toGlobal(new canvas.px.Point(this.x, this.y));
    this._dragStartViewPosition = new canvas.px.Point(this.x, this.y);
    canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_MOVE, null, this._dragMouseMoveHandler, this);
    if (startPoint) {
        this._dragMouseMoveHandler({
            params: {
                x: startPoint.x,
                y: startPoint.y,
                mouseData: {
                    offsetX: startPoint.x,
                    offsetY: startPoint.y
                }
            }
        })
    }
};
canvas.px.Container.prototype.stopDrag = function() {
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_MOVE, null, this._dragMouseMoveHandler, this)
};
canvas.px.Container.prototype._dragMouseMoveHandler = function(event) {
    var point = new canvas.px.Point(this._dragStartViewPosition.x + (event.params.mouseData.offsetX - this._dragStartPosition.x - this._dragLockPoint.x), this._dragStartViewPosition.y + (event.params.mouseData.offsetY - this._dragStartPosition.y - this._dragLockPoint.y));
    if (this._dragBounds) {
        point.x = point.x < this._dragBounds.x ? this._dragBounds.x : point.x > this._dragBounds.x + this._dragBounds.width ? this._dragBounds.x + this._dragBounds.width : point.x;
        point.y = point.y < this._dragBounds.y ? this._dragBounds.y : point.y > this._dragBounds.y + this._dragBounds.height ? this._dragBounds.y + this._dragBounds.height : point.y
    }
    this.position.set(point.x * this._dragScale, point.y * this._dragScale)
};
if (canvas.px.Container.prototype.numChildren == undefined) {
    Object.defineProperty(canvas.px.Container.prototype, "numChildren", {
        get: function() {
            return this.children ? this.children.length : 0
        }
    })
}
canvas.px.Container.prototype.contains = function(child) {
    return child && child.parent == this
};
canvas.px.Container.prototype.removeIfExist = function(child) {
    if (this.contains(child)) return this.removeChild(child);
    return null
};
canvas.px.Rectangle.prototype.intersects = function(rect) {
    if (rect.x < this.x + this.width && this.x < rect.x + rect.width && rect.y < this.y + this.height) return this.y < rect.y + rect.height;
    else return false
};
canvas.px.Rectangle.prototype.intersectsPoint = function(point) {
    return point.x <= this.x + this.width && point.x >= this.x && point.y <= this.y + this.height && point.y >= this.y
};
canvas.px.SlicedSprite.prototype.setSize = function(w, h) {
    if (w > 0) this.width = w;
    if (h > 0) this.height = h
};
canvas.px.Container.prototype.getObjectsUnderPoint = function(point) {
    var result = [],
        len = this.children.length,
        i, child;
    for (i = 0; i < len; i++) {
        child = this.children[i];
        if (child.getBounds().intersectsPoint(point)) {
            result.push(child);
            result = result.concat(child.getObjectsUnderPoint(new canvas.px.Point(point.x, point.y)))
        }
    }
    return result
};
if (canvas.isMobile()) {
    canvas.px.Container.prototype.pointertap = function(event) {
        if (this.click) {
            this.click.call(this, event)
        }
    };
    canvas.px.Container.prototype.pointerdown = function(event) {
        if (this.mousedown) {
            this.mousedown.call(this, event)
        }
    };
    canvas.px.Container.prototype.pointerup = function(event) {
        if (this.mouseup) {
            this.mouseup.call(this, event)
        }
    };
    canvas.px.Container.prototype.pointermove = function(event) {
        if (this.mousemove) {
            this.mousemove.call(this, event)
        }
    };
    canvas.px.Container.prototype.pointerout = function(event) {
        if (this.mouseout) {
            this.mouseout.call(this, event)
        }
    };
    canvas.px.Container.prototype.pointerover = function(event) {
        if (this.mouseover) {
            this.mouseover.call(this, event)
        }
    }
}
canvas.Config = {
    init: function() {
        if (!this.domain && document.domain) {
            this.domain = "http" + (document.URL.substr(0, 5) == "https" ? "s" : "") + "://" + document.domain;
            if (this.domain.indexOf("localhost") >= 0 || this.domain.indexOf("127.0.0.1") >= 0) {
                this.isLocal = true;
                this.domain += ":8080"
            }
            this.wwwPath = this.domain + "/";
            this.dataPath = this.domain + (this.isLocal ? "/canvas/content/" : "/images/data/");
            this.imgPath = this.dataPath + (this.isLocal ? "" : "canvas/");
            this.soundsPath = this.imgPath + "sounds/";
            this.botsPath = this.imgPath + "bots/";
            this.petsPath = this.imgPath + "pets/";
            this.petsUiPath = this.imgPath + "pets_ui/";
            this.huntMapsPath = this.imgPath + "hunt_maps/";
            this.huntBotsPath = this.imgPath + "hunt_bots/";
            this.huntResPath = this.imgPath + "hunt_res/";
            this.spellsPath = this.imgPath + "spells/mci/";
            this.spellsAtlasPath = this.imgPath + "spells/";
            this.effectsAtlasPath = this.imgPath + "effects/";
            this.effectsPath = this.imgPath + "effects/mci/";
            this.flagsPath = this.imgPath + "flags/";
            this.skPath = this.imgPath + "sk/";
            this.packsPath = this.imgPath + "packs/";
            this.packsAnimsPath = this.imgPath + "packs_anims/";
            this.fontsPath = this.imgPath + "fonts/";
            this.areasPath = this.imgPath + "areas/";
            this.worldPath = this.imgPath + "world/";
            this.instPath = this.imgPath + "inst/";
            this.skyPath = this.imgPath + "sky/";
            this.userBackgroundsPath = this.imgPath + "user_backs/";
            this.userViewsPath = this.imgPath + "user_views/";
            this.mountsPath = this.imgPath + "mounts/";
            this.clanBuildingsPath = this.imgPath + "clan_buildings/";
            this.manorBuildingsPath = this.imgPath + "estate_buildings/";
            this.throneRoomPath = this.imgPath + "throne_room/";
            this.localePath = this.imgPath + "locale/";
            this.ui = this.imgPath + "ui/";
            this.effects = this.imgPath + "effects/effects.json";
            this.amfPath = this.dataPath + "locale/ru/amf/";
            this.artifactsPath = this.dataPath + "artifacts/";
            this.clansPath = this.dataPath + "clans/";
            this.uxPath = "images/data/canvas/ux.cfg?ux=1517916589";
            this.entryPoint = "entry_point.php";
            this.isMobile = canvas.isMobile();
            this.initLang("ru")
        }
    },
    initLang: function(lang) {
        this.lng = lang;
        this.amfPath = this.dataPath + "locale/" + this.lng + "/amf/";
        this.langPath = this.localePath + this.lng + "/";
        this.locale = this.langPath + "locale.json"
    }
};
canvas.Const = {
    FONT_ARIAL_9_BOLD_STROKE: "arial_9_bold_stroke",
    FONT_ARIAL_10_BOLD_STROKE: "arial_10_bold_stroke",
    FONT_ARIAL_11: "arial_11",
    FONT_ARIAL_11_BOLD: "arial_11_bold",
    FONT_ARIAL_11_BOLD_STROKE: "arial_11_bold_stroke",
    FONT_IFLASH: "iFlash502",
    FONT_CUPRUM_12_BOLD: "cuprum_12_bold",
    FONT_CUPRUM_16_BOLD: "cuprum_16_bold",
    FONT_CUPRUM_22_BOLD: "cuprum_22_bold",
    FONT_CUPRUM_22_BOLD_STROKE: "cuprum_22_bold_stroke",
    FONT_CUPRUM_34_BOLD_STROKE: "cuprum_34_bold_stroke",
    FONT_CUPRUM_24_BOLD_STROKE_BROWN: "cuprum_24_bold_stroke_brown",
    FONT_CUPRUM_40_BOLD: "cuprum_40_bold",
    FONT_AMERICAN_TEXT_40_SHADOW_GRADIENT: "american_text_40_shadow_gradient",
    FONT_TAHOMA_9: "tahoma_9",
    FONT_TAHOMA_9_STROKE: "tahoma_9_stroke",
    FONT_TAHOMA_9_BOLD: "tahoma_9_bold",
    FONT_TAHOMA_9_BOLD_STROKE: "tahoma_9_bold_stroke",
    FONT_TAHOMA_10: "tahoma_10",
    FONT_TAHOMA_10_STROKE: "tahoma_10_stroke",
    FONT_TAHOMA_10_BOLD: "tahoma_10_bold",
    FONT_TAHOMA_10_BOLD_STROKE: "tahoma_10_bold_stroke",
    FONT_TAHOMA_10_BOLD_SHARP: "tahoma_10_bold_sharp",
    FONT_TAHOMA_11: "tahoma_11",
    FONT_TAHOMA_11_BOLD: "tahoma_11_bold",
    FONT_TAHOMA_11_BOLD_STROKE: "tahoma_11_bold_stroke",
    FONT_TAHOMA_11_BOLD_STROKE_BEVEL_SHARP: "tahoma_11_bold_stroke_bevel_sharp",
    FONT_TAHOMA_12: "tahoma_12",
    FONT_TAHOMA_12_BOLD: "tahoma_12_bold",
    FONT_TAHOMA_12_BOLD_STROKE: "tahoma_12_bold_stroke",
    FONT_TAHOMA_12_BOLD_STROKE_RED_WHITE: "tahoma_12_bold_stroke_red_white",
    FONT_TAHOMA_13: "tahoma_13",
    FONT_TAHOMA_13_BOLD: "tahoma_13_bold",
    FONT_TAHOMA_13_BOLD_STROKE: "tahoma_13_bold_stroke",
    FONT_TAHOMA_14: "tahoma_14",
    FONT_TAHOMA_14_STROKE: "tahoma_14_stroke",
    FONT_TAHOMA_14_BOLD: "tahoma_14_bold",
    FONT_TAHOMA_14_BOLD_STROKE: "tahoma_14_bold_stroke",
    FONT_TAHOMA_14_NUMBERS_BOLD_STROKE_BEVEL: "tahoma_14_numbers_bold_stroke_bevel",
    FONT_TAHOMA_15: "tahoma_15",
    FONT_TAHOMA_15_STROKE: "tahoma_15_stroke",
    FONT_TAHOMA_15_BOLD: "tahoma_15_bold",
    FONT_TAHOMA_15_BOLD_STROKE: "tahoma_15_bold_stroke",
    FONT_TAHOMA_16: "tahoma_16",
    FONT_TAHOMA_16_STROKE: "tahoma_16_stroke",
    FONT_TAHOMA_16_BOLD: "tahoma_16_bold",
    FONT_TAHOMA_16_BOLD_STROKE: "tahoma_16_bold_stroke",
    FONT_TAHOMA_16_BOLD_GLOW_NUMBERS: "tahoma_16_bold_glow_numbers",
    FONT_TAHOMA_18: "tahoma_18",
    FONT_TAHOMA_18_BOLD_STROKE: "tahoma_18_bold_stroke",
    FONT_TAHOMA_20_BOLD_STROKE: "tahoma_20_bold_stroke",
    FONT_TAHOMA_S_15_BOLD: "tahoma_s_15_bold",
    FONT_RADA_18: "rada_18",
    FONT_MYRIAD_PRO_15_BOLD_STROKE: "myriad_pro_15_bold_stroke",
    LANG_RU: "ru",
    LANG_EN: "en",
    SK_SLOT_NAMES: ["HEAD", "HELM", "BODY", "TRUS", "HAND11", "HAND12", "HAND13", "HAND21", "HAND22", "HAND23", "FOOT11", "FOOT12", "FOOT13", "FOOT21", "FOOT22", "FOOT23", "SWORD", "SHIELD", "SWORD2", "BANNER", "BOW"],
    SK_WPN_SLOT_NAMES: ["SWORD", "SHIELD", "SWORD2", "BANNER", "BOW"],
    UI_SLOT_NAMES: ["HEAD", "HELM", "BODY", "TRUS", "HAND11", "HAND12", "HAND13", "HAND21", "HAND22", "HAND23", "FOOT11", "FOOT12", "FOOT13", "FOOT21", "FOOT22", "FOOT23", "SWORD", "SHIELD", "SWORD2"],
    AVATAR_SLOT_NAMES: ["HEAD", "HELM", "BODY", "HAND11", "HAND21"],
    UI_EXCLUDE_ANIM_SLOTS: ["TRUS", "FOOT21", "FOOT22", "FOOT23", "HAND21", "HAND22", "HAND23", "HAND13"],
    UI_TROPHY_SLOT_NAMES: ["HEAD"],
    UI_SLOT_POS: {
        HAND13: [8, 7],
        SWORD: [1],
        SWORD2: [1],
        HAND12: [8],
        HAND11: [8],
        FOOT11: [1, 8],
        FOOT12: [1, 8],
        FOOT13: [1, 8],
        BODY: [8],
        TRUS: [8],
        HEAD: [8],
        HELM: [8],
        FOOT21: [2, 4],
        FOOT22: [2, 3],
        FOOT23: [2, 3],
        HAND22: [2, 3],
        HAND23: [2, 3],
        HAND21: [2, 3],
        SHIELD: [1]
    },
    SK_COLOR_TRANSFORMS: {
        c1: {
            rr: -30,
            gg: -30,
            bb: -30
        },
        c2: {
            rr: -80,
            gg: -80,
            bb: -80
        },
        c3: {
            rr: 0,
            gg: -30,
            bb: -30
        },
        c4: {
            rr: 20,
            gg: 20,
            bb: 0
        },
        c5: {
            rr: 40,
            gg: 40,
            bb: 0
        },
        c6: {
            rr: -60,
            gg: -40,
            bb: -215
        },
        c7: {
            rr: -60,
            gg: 10,
            bb: 45
        },
        c8: {
            rr: -75,
            gg: 20,
            bb: 10
        },
        c9: {
            rr: 0,
            gg: 10,
            bb: 0,
            gm: 1.2,
            bm: .8
        },
        c10: {
            rr: 0,
            gg: -30,
            bb: 0,
            rm: .8
        },
        c11: {
            rr: -20,
            gg: -20,
            bb: -20
        },
        c12: {
            rr: -90,
            gg: -90,
            bb: -90
        },
        c13: {
            rr: -50,
            gg: -75,
            bb: -100
        },
        c14: {
            rr: 75,
            gg: -25,
            bb: -150
        },
        c15: {
            rr: 128,
            gg: 110,
            bb: 0
        },
        c16: {
            rr: 100,
            gg: -50,
            bb: -50
        },
        c17: {
            rr: -30,
            gg: -30,
            bb: -30
        },
        c18: {
            rr: -100,
            gg: -100,
            bb: -100
        },
        c19: {
            rr: 50,
            gg: 0,
            bb: -150
        },
        c20: {
            rr: 0,
            gg: -180,
            bb: -180
        },
    },
    SK_DIE_COLORS_HUMAN: [
        [.875, .875, .875, 0, 26, 32],
        [.75, .75, .75, 0, 51, 64],
        [.625, .625, .625, 0, 77, 96],
        [.5, .5, .5, 0, 102, 128],
        [.375, .375, .375, 0, 128, 159],
        [.25, .25, .25, 0, 153, 191],
        [.125, .125, .125, 0, 179, 223]
    ],
    SK_DIE_COLORS_MAGMAR: [
        [.875, .875, .875, 0, 26, 32],
        [.75, .75, .75, 0, 51, 64],
        [.625, .625, .625, 0, 77, 96],
        [.5, .5, .5, 0, 102, 128],
        [.375, .375, .375, 0, 128, 159],
        [.25, .25, .25, 0, 153, 191],
        [.125, .125, .125, 0, 179, 223]
    ],
    KEYS: {
        A: 65,
        B: 66,
        C: 67,
        D: 68,
        E: 69,
        F: 70,
        G: 71,
        H: 72,
        I: 73,
        J: 74,
        K: 75,
        L: 76,
        M: 77,
        N: 78,
        O: 79,
        P: 80,
        Q: 81,
        R: 82,
        S: 83,
        T: 84,
        U: 85,
        V: 86,
        W: 87,
        X: 88,
        Y: 89,
        Z: 90,
        LEFT_ARROW: 37,
        UP_ARROW: 38,
        RIGHT_ARROW: 39,
        DOWN_ARROW: 40,
        KEY_0: 48,
        KEY_1: 49,
        KEY_2: 50,
        KEY_3: 51,
        KEY_4: 52,
        KEY_5: 53,
        KEY_6: 54,
        KEY_7: 55,
        KEY_8: 56,
        KEY_9: 57,
        NUM_0: 96,
        NUM_1: 97,
        NUM_2: 98,
        NUM_3: 99,
        NUM_4: 100,
        NUM_5: 101,
        NUM_6: 102,
        NUM_7: 103,
        NUM_8: 104,
        NUM_9: 105,
        MINUS: 189,
        EQUAL: 187,
        RIGHT_SQUARE_BRACKET: 221,
        LEFT_SQUARE_BRACKET: 219,
        SPACEBAR: 32,
        ESC: 27,
        TAB: 9,
        POINT: 190
    },
    CODE_TO_KEYS: {
        KeyA: "A",
        KeyB: "B",
        KeyC: "C",
        KeyD: "D",
        KeyE: "E",
        KeyF: "F",
        KeyG: "G",
        KeyH: "H",
        KeyI: "I",
        KeyJ: "J",
        KeyK: "K",
        KeyL: "L",
        KeyM: "M",
        KeyN: "N",
        KeyO: "O",
        KeyP: "P",
        KeyQ: "Q",
        KeyR: "R",
        KeyS: "S",
        KeyT: "T",
        KeyU: "U",
        KeyV: "V",
        KeyW: "W",
        KeyX: "X",
        KeyY: "Y",
        KeyZ: "Z",
        ArrowLeft: "LEFT_ARROW",
        ArrowUp: "UP_ARROW",
        ArrowRight: "RIGHT_ARROW",
        ArrowDown: "DOWN_ARROW",
        Digit0: "KEY_0",
        Digit1: "KEY_1",
        Digit2: "KEY_2",
        Digit3: "KEY_3",
        Digit4: "KEY_4",
        Digit5: "KEY_5",
        Digit6: "KEY_6",
        Digit7: "KEY_7",
        Digit8: "KEY_8",
        Digit9: "KEY_9",
        Numpad0: "NUM_0",
        Numpad1: "NUM_1",
        Numpad2: "NUM_2",
        Numpad3: "NUM_3",
        Numpad4: "NUM_4",
        Numpad5: "NUM_5",
        Numpad6: "NUM_6",
        Numpad7: "NUM_7",
        Numpad8: "NUM_8",
        Numpad9: "NUM_9",
        Minus: "MINUS",
        Equal: "EQUAL",
        BracketRight: "RIGHT_SQUARE_BRACKET",
        BracketLeft: "LEFT_SQUARE_BRACKET",
        Space: "SPACEBAR",
        Escape: "ESC",
        Tab: "TAB",
        Period: "POINT"
    },
    KEYBOARD_BUTTONS: {
        113: "й",
        119: "ц",
        101: "у",
        114: "к",
        116: "е",
        121: "н",
        117: "г",
        105: "ш",
        111: "щ",
        112: "з",
        91: "х",
        93: "ъ",
        97: "ф",
        115: "ы",
        100: "в",
        102: "а",
        103: "п",
        104: "р",
        106: "о",
        107: "л",
        108: "д",
        59: "ж",
        39: "э",
        122: "я",
        120: "ч",
        99: "с",
        118: "м",
        98: "и",
        110: "т",
        109: "ь",
        44: "б",
        46: "ю",
        96: "ё"
    },
    GENDER: {
        MALE: "M",
        FEMALE: "F"
    },
    KIND: {
        HUM: 1,
        MAG: 2
    },
    ENTRY_POINT: {
        OBJECT: {
            USER: "user"
        },
        ACTION: {
            BOW_ORDER_GET: "bow_order_get"
        }
    }
};
canvas.EventManager = {
    events: {},
    addEventListener: function(eventName, target, handler, context) {
        if (this.hasEventListener(eventName, target, handler, context)) return;
        if (!this.events[eventName]) this.events[eventName] = new Array;
        this.events[eventName].push({
            target: target,
            handler: handler,
            context: context
        })
    },
    removeEventListener: function(eventName, target, handler, context) {
        if (this.events[eventName] && this.events[eventName].length > 0) {
            var a = this.events[eventName];
            for (var i = a.length - 1; i >= 0; i--) {
                if (a[i].target == target && a[i].handler == handler && (typeof context == "undefined" || a[i].context == context)) {
                    a.splice(i, 1)
                }
            }
        }
    },
    hasEventListener: function(eventName, target, handler, context) {
        if (this.events[eventName] && this.events[eventName].length > 0) {
            var a = this.events[eventName];
            for (var i = a.length - 1; i >= 0; i--) {
                if ((!target || a[i].target == target) && (!handler || a[i].handler == handler) && (!context || a[i].context == context)) {
                    return true
                }
            }
        }
        return false
    },
    removeAllListeners: function(eventName, target, handler, context) {
        var a = this.events[eventName];
        if (a && a.length > 0) {
            for (var i = a.length - 1; i >= 0; i--) {
                if ((!target || a[i].target == target) && (!handler || a[i].handler == handler) && (!context || a[i].context == context)) {
                    a.splice(i, 1)
                }
            }
        }
    },
    dispatchEvent: function(eventName, target, params) {
        var list = this.events[eventName];
        if (!list || list.length === 0) return;
        var events = list.slice(0);
        var e = {
            target: target,
            params: params,
            name: eventName
        };
        for (var i = 0; i < events.length; i++) {
            var l = events[i];
            if (l.handler && (l.target == null || l.target == target)) {
                l.handler.apply(l.context, [e])
            }
        }
    },
    destroy: function() {
        for (var key in this.events) {
            delete this.events[key]
        }
    }
};
canvas.Functions = {
    colorMatrixCache: {},
    setNumberLen: function(s, len) {
        if (len == undefined) len = 2;
        s = s.toString();
        while (s.length < len) s = "0" + s;
        return s
    },
    getColorMatrixFilter: function() {
        var hash = canvas.Functions.getColorMatrixHash.apply(this, arguments);
        if (canvas.Functions.colorMatrixCache[hash]) return canvas.Functions.colorMatrixCache[hash];
        var colorMatrixFilter = new canvas.px.ColorMatrixFilter;
        colorMatrixFilter.matrix = [arguments[0], 0, 0, 0, arguments[4] / 255, 0, arguments[1], 0, 0, arguments[5] / 255, 0, 0, arguments[2], 0, arguments[6] / 255, 0, 0, 0, arguments[3], 0];
        canvas.Functions.colorMatrixCache[hash] = colorMatrixFilter;
        return colorMatrixFilter
    },
    getColorMatrixHash: function() {
        return arguments[0].toString().concat("_", arguments[1], "_", arguments[2], "_", arguments[3], "_", arguments[4], "_", arguments[5], "_", arguments[6])
    },
    formatDate: function(time, defaultText, type, fullString, showSeconds, showFirstZero) {
        if (defaultText == undefined) defaultText = "00";
        if (showSeconds == undefined) showSeconds = true;
        if (showFirstZero == undefined) showFirstZero = true;
        if (time == 0) return "";
        var seconds = Math.round(time * .001);
        var minutes = showSeconds ? Math.floor(seconds / 60) : Math.ceil(seconds / 60);
        var days;
        var hours;
        var str = defaultText;
        if (seconds > 0) {
            str = "";
            days = Math.floor(minutes / 1440);
            if (days > 0) {
                str += showFirstZero ? this.setNumberLen(days, 2) : days;
                switch (type) {
                    case 1:
                        str += canvas.Translator.getText(2e3) + " ";
                        break;
                    case 2:
                        str += canvas.Translator.getText(2e3) + " ";
                        break;
                    case 3:
                        str += " " + canvas.Translator.getText(2e3) + ". ";
                        break;
                    default:
                        str += ":"
                }
                hours = Math.floor((minutes - days * 1440) / 60);
                str += showFirstZero ? this.setNumberLen(hours, 2) : hours;
                switch (type) {
                    case 1:
                        str += canvas.Translator.getText(2001);
                        break;
                    case 2:
                        str += canvas.Translator.getText(2001);
                        break;
                    case 3:
                        str += " " + canvas.Translator.getText(2001) + ".";
                        break;
                    default:
                        str += ""
                }
            } else {
                hours = Math.floor(minutes / 60);
                if (hours > 0) {
                    str += showFirstZero ? this.setNumberLen(hours, 2) : hours;
                    switch (type) {
                        case 1:
                            str += canvas.Translator.getText(2001) + " ";
                            break;
                        case 2:
                            str += canvas.Translator.getText(2001) + " ";
                            break;
                        case 3:
                            str += " " + canvas.Translator.getText(2001) + ". ";
                            break;
                        default:
                            str += ":"
                    }
                    minutes = minutes - hours * 60;
                    str += showFirstZero ? this.setNumberLen(minutes, 2) : minutes;
                    switch (type) {
                        case 1:
                            str += canvas.Translator.getText(2002);
                            break;
                        case 2:
                            str += canvas.Translator.getText(2016);
                            break;
                        case 3:
                            str += " " + canvas.Translator.getText(2016) + ".";
                            break;
                        default:
                            str += ""
                    }
                } else {
                    if (minutes > 0) {
                        str += showFirstZero ? this.setNumberLen(minutes, 2) : minutes;
                        switch (type) {
                            case 1:
                                str += canvas.Translator.getText(2002) + " ";
                                break;
                            case 2:
                                str += canvas.Translator.getText(2016) + " ";
                                break;
                            case 3:
                                str += " " + canvas.Translator.getText(2016) + ". ";
                                break;
                            default:
                                str += ":"
                        }
                    } else {
                        if (fullString) str += "00:"
                    }
                    if (showSeconds) {
                        seconds = seconds - minutes * 60;
                        str += showFirstZero ? this.setNumberLen(seconds, 2) : seconds;
                        switch (type) {
                            case 1:
                                str += canvas.Translator.getText(2003);
                                break;
                            case 2:
                                str += canvas.Translator.getText(2017);
                                break;
                            case 3:
                                str += " " + canvas.Translator.getText(2017) + ".";
                                break;
                            default:
                                str += ""
                        }
                    } else {
                        str = str.substr(0, str.length - 1)
                    }
                }
            }
        }
        return str
    },
    degToRad: function(deg) {
        return deg * (Math.PI / 180)
    },
    radToDeg: function(rad) {
        return rad * (180 / Math.PI)
    },
    navigateToURL: function(url, target) {
        window.open(url, target)
    },
    clearChildren: function(container) {
        while (container.children.length > 0) {
            var child = container.removeChildAt(0)
        }
    },
    destroyChildren: function(container) {
        while (container.children.length > 0) {
            var child = container.removeChildAt(0);
            child.destroy({
                children: true
            })
        }
    },
    decodeUrlParams: function(str) {
        var a = str.split("&");
        var len = a.length;
        var result = {};
        var b;
        for (var i = 0; i < len; i++) {
            b = a[i].split("=");
            result[b[0]] = decodeURIComponent(b[1])
        }
        return result
    },
    testFlag: function(flags, flag) {
        return (flags & flag) == flag
    },
    testStatus: function(obj) {
        if (obj && obj.hasOwnProperty("status") && obj.status != 100 && obj.error != undefined) return false;
        return true
    },
    greyScaleCache: {},
    getGreyScale: function(value) {
        if (value == undefined) value = .5;
        if (canvas.Functions.greyScaleCache[value]) return canvas.Functions.greyScaleCache[value];
        var f = new canvas.px.ColorMatrixFilter;
        f.greyscale(value);
        canvas.Functions.greyScaleCache[value] = f;
        return f
    },
    brightnessCache: {},
    getBrightness: function(value) {
        if (value == undefined) value = 1.2;
        if (canvas.Functions.brightnessCache[value]) return canvas.Functions.brightnessCache[value];
        var f = new canvas.px.ColorMatrixFilter;
        f.brightness(value);
        canvas.Functions.brightnessCache[value] = f;
        return f
    },
    saturationCache: {},
    getSaturation: function(value) {
        if (value == undefined) value = .2;
        if (canvas.Functions.saturationCache[value]) return canvas.Functions.saturationCache[value];
        var f = new canvas.px.ColorMatrixFilter;
        f.saturate(value);
        canvas.Functions.saturationCache[value] = f;
        return f
    },
    blurCache: {},
    getBlur: function(value) {
        if (value == undefined) value = 1;
        if (canvas.Functions.blurCache[value]) return canvas.Functions.blurCache[value];
        var f = new canvas.px.TiltShiftFilter(value, 0);
        canvas.Functions.blurCache[value] = f;
        return f
    },
    dropShadowCache: {},
    getDropShadow: function(rotation, distance, blur, color, alpha) {
        if (rotation == undefined) rotation = 45;
        if (distance == undefined) distance = 5;
        if (blur == undefined) blur = 2;
        if (color == undefined) color = 0;
        if (alpha == undefined) alpha = .5;
        var hash = rotation.toString().concat("_" + distance + "_" + blur + "_" + color + "_" + alpha);
        if (canvas.Functions.dropShadowCache[hash]) return canvas.Functions.dropShadowCache[hash];
        var f = new canvas.px.DropShadowFilter({
            rotation: rotation,
            distance: distance,
            blur: blur,
            color: color,
            alpha: alpha
        });
        canvas.Functions.dropShadowCache[hash] = f;
        return f
    },
    blackAndWhiteCache: null,
    getBlackAndWhite: function() {
        if (canvas.Functions.blackAndWhiteCache) return canvas.Functions.blackAndWhiteCache;
        var f = new canvas.px.ColorMatrixFilter;
        f.blackAndWhite();
        canvas.Functions.blackAndWhiteCache = f;
        return f
    },
    glowCache: {},
    getGlow: function(distance, outerStrength, innerStrength, color, quality) {
        if (distance == undefined) distance = 10;
        if (outerStrength == undefined) outerStrength = 4;
        if (innerStrength == undefined) innerStrength = 0;
        if (color == undefined) color = 16777215;
        if (quality == undefined) quality = .1;
        var hash = distance.toString().concat("_" + outerStrength + "_" + innerStrength + "_" + color + "_" + quality);
        if (canvas.Functions.glowCache[hash]) return canvas.Functions.glowCache[hash];
        var f = new canvas.px.GlowFilter(distance, outerStrength, innerStrength, color, quality);
        canvas.Functions.glowCache[hash] = f;
        return f
    },
    alphaCache: {},
    getAlpha: function(value) {
        if (value == undefined) value = 1;
        if (canvas.Functions.alphaCache[value]) return canvas.Functions.alphaCache[value];
        var f = new canvas.px.AlphaFilter;
        f.alpha = value;
        canvas.Functions.alphaCache[value] = f;
        return f
    },
    findParent: function(target, source) {
        while (source) {
            if (source == target) return true;
            source = source.parent
        }
        return false
    },
    findParentByName: function(name, source) {
        var len = name.length;
        while (source) {
            if (source.name && source.name.substr(0, len) == name) return source;
            source = source.parent
        }
        return null
    },
    cloneSimpleObject: function(object) {
        var result = {};
        for (var key in object) {
            result[key] = object[key]
        }
        return result
    },
    parseUx: function() {
        if (canvas.ResourceLoader.ux) return;
        canvas.ResourceLoader.ux = {};
        var i = 0;
        var j;
        var data;
        var a;
        var b;
        var c;
        var folders;
        var len;
        var res;
        while (data = canvas.ResourceLoader.get("ux" + i++)) {
            folders = [];
            a = data.data.split("\n");
            b = a[0].split(",");
            len = b.length;
            for (j = 0; j < len; j++) {
                res = b[j].match(/{(\d+)}:(.+)/i);
                folders[parseInt(res[1])] = canvas.Config.wwwPath + res[2]
            }
            len = a.length;
            for (j = 1; j < len; j++) {
                if (a[j]) {
                    res = a[j].match(/{(\d+)}(.+)\?ux=(.+)/i);
                    canvas.ResourceLoader.ux[folders[parseInt(res[1])] + res[2]] = res[3]
                }
            }
        }
        canvas.ResourceLoader.uxReady = true;
        canvas.EventManager.dispatchEvent(canvas.Event.UX_READY)
    },
    extractSwfName: function(path) {
        if (!path) return "";
        var a = path.split(".")[0].split("/");
        return a[a.length - 1]
    },
    random: function(size) {
        if (!size) return 0;
        var result = Math.floor(Math.random() * size);
        return result >= size ? size - 1 : result
    },
    randomArray: function(array) {
        var len = array.length,
            i, j, temp;
        for (i = len - 1; i > 0; i--) {
            j = Math.floor(Math.random() * (i + 1));
            temp = array[i];
            array[i] = array[j];
            array[j] = temp
        }
    },
    pointInRect: function(point, rect) {
        return point.x > rect.x && point.x < rect.x + rect.width && point.y > rect.y && point.y < rect.y + rect.height
    },
    now: function() {
        return Math.round(Date.now() * .001)
    },
    getCyrillicInput: function(str) {
        var result = str || "";
        return result.replace(/[a-z\[\]\;\'\,\.\`]/gi, function(char, key) {
            var res = canvas.Const.KEYBOARD_BUTTONS[char.toLowerCase().charCodeAt(0)];
            return res || char
        })
    },
    getMoney: function(floatValue) {
        return Math.floor(floatValue * 100)
    },
    getMoneyGold: function(floatValue) {
        return Math.floor(floatValue * .01)
    },
    getGems: function(floatValue) {
        return Math.round(floatValue * 100) / 100
    },
    getSessTarget: function() {
        var result = {
            target: "",
            autoPenalty: -1
        };
        try {
            var target = getCookie("sess_target")
        } catch (e) {}
        if (target && target != "null") {
            var a = target.split("_");
            result.target = a[0];
            result.autoPenalty = a.length > 1 ? parseInt(a[1]) : -1;
            result.manor = a.length > 2 ? parseInt(a[2]) == 1 : false
        }
        return result
    },
    deleteSessTarget: function() {
        deleteCookie("sess_target");
        deleteCookie("sess_navigate_memory");
        deleteCookie("sess_navigate_array")
    },
    getAttribute: function(node, name) {
        return node.attributes[name] ? node.attributes[name].value : undefined
    },
    getChildNodeByName: function(node, name) {
        var len = node.children.length;
        var result = null;
        for (var i = 0; i < len; i++) {
            if (node.children[i].nodeName == name) {
                if (!result) result = new Array;
                result.push(node.children[i])
            }
        }
        return result
    },
    getChildValueByName: function(node, name) {
        var len = node.children.length;
        for (var i = 0; i < len; i++) {
            if (node.children[i].nodeName == name) {
                return node.children[i].innerHTML
            }
        }
        return ""
    },
    getMoneyForText: function(value) {
        var result = {};
        result.icon = "money_silver";
        result.value = value;
        if (value >= 100) {
            result.value = value * .01;
            result.icon = "money_gold"
        } else if (value < 1) {
            result.value = value * 100;
            result.icon = "money_bronze"
        }
        return result
    },
    getNumberName: function(num, name1, name2, name3) {
        var i = num % 100;
        var result;
        if (i >= 5 && i <= 20) {
            result = num + " " + name3
        } else {
            i = i % 10;
            if (i == 1) {
                result = num + " " + name1
            } else if (i >= 2 && i <= 4) {
                result = num + " " + name2
            } else {
                result = num + " " + name3
            }
        }
        return result
    },
    hasChildNode: function(node, name) {
        return node && node.getElementsByTagName(name).length > 0
    },
    destroy: function() {
        var a = [this.colorMatrixCache, this.greyScaleCache, this.brightnessCache, this.saturationCache, this.blurCache, this.dropShadowCache, this.glowCache, this.alphaCache];
        var i, obj, str, len = a.length;
        for (i = 0; i < len; i++) {
            obj = a[i];
            for (str in obj) {
                delete obj[str]
            }
        }
    },
    entryPointGetUrl: function (t, e) {
        return canvas.Config.entryPoint + "?object=" + t + "&action=" + e + "&json_mode_on=1"
    },
    entryPointConcatObjectAction: function (t, e) {
        return t + "|" + e
    }
};
canvas.ResourceLoader = {
    EVENT_COMPLETE: "ResourceLoader.COMPLETE",
    EVENT_PROGRESS: "ResourceLoader.PROGRESS",
    stack: [],
    aliases: {},
    inProgress: false,
    inited: false,
    ux: null,
    uxReady: false,
    init: function() {
        if (!this.inited) {
            canvas.px.Loader.on("progress", this.progress);
            canvas.px.Loader.on("error", function() {});
            canvas.px.Loader.on("add", {});
            canvas.px.Loader.on("complete", this.complete);
            this.inited = true
        }
    },
    add: function(files) {
        var i, len;
        if (this.inProgress) {
            this.stack = this.stack.concat(files)
        } else {
            var tmpObj = {};
            for (i = files.length - 1; i >= 0; i--) {
                if (typeof files[i] == "object") {
                    files[i][1] = this.applyUx(files[i][1]);
                    if (files[i][0]) {
                        this.aliases[files[i][0]] = files[i][1]
                    }
                    if (files[i][2] != undefined) {
                        files[i][2].url = files[i][1];
                        files[i] = files[i][2]
                    } else {
                        files[i] = files[i][1]
                    }
                } else {
                    files[i] = this.applyUx(files[i])
                }
                if (this.getResource(files[i]) || tmpObj[files[i]]) {
                    files.splice(i, 1)
                } else {
                    tmpObj[files[i]] = true
                }
            }
            if (files.length > 0) {
                this.inProgress = true;
                try {
                    canvas.px.Loader.add(files).load()
                } catch (e) {
                    console.log("ResourceLoader.load: " + e)
                }
            } else {
                this.complete()
            }
        }
    },
    remove: function(url) {
        url = this.applyUx(url);
        this.deleteResource(this.aliases[url] || url)
    },
    complete: function() {
        var thisObj = canvas.ResourceLoader;
        thisObj.inProgress = false;
        if (thisObj.stack.length > 0) {
            var a = thisObj.stack;
            thisObj.stack = [];
            thisObj.add(a)
        } else {
            canvas.EventManager.dispatchEvent(thisObj.EVENT_COMPLETE, thisObj)
        }
    },
    progress: function() {
        var thisObj = canvas.ResourceLoader;
        canvas.EventManager.dispatchEvent(thisObj.EVENT_PROGRESS, thisObj, {
            progress: canvas.px.Loader.progress > 100 ? canvas.px.Loader.progress - 100 : canvas.px.Loader.progress
        })
    },
    get: function(url) {
        url = this.applyUx(url);
        var res = this.getResource(this.aliases[url] || url);
        var result = null;
        if (res) {
            switch (res.extension) {
                case "mci":
                    if (!result && res.data) {
                        if (!res.unpacked && typeof res.data == "string" && res.data.charAt(0) != "{") {
                            try {
                                res.data = this.unpackBinary(res.data)
                            } catch (e) {
                                return null
                            }
                        }
                        res.unpacked = true;
                        if (typeof res.data == "string") res.data = JSON.parse(res.data);
                        result = res
                    }
                    break;
                case "xml":
                case "fnt":
                    result = res.data ? res : null;
                    break;
                case "json":
                    result = res.textures ? res : null;
                    break;
                case "amf":
                    if (res.unpacked) {
                        result = res.data
                    } else {
                        result = res.data = this.unpackAmf(res.data);
                        res.unpacked = true
                    }
                    break;
                default:
                    result = res.data ? res : null
            }
        }
        return result
    },
    getTexture: function(url) {
        return this.get(url) ? this.get(url).texture ? this.get(url).texture : canvas.px.TextureEmpty : canvas.px.TextureEmpty
    },
    getImage: function(url, name) {
        var res = this.get(url);
        if (res && !res.textures[name + ".png"]) {
            console.log("Warning! Texture '" + name + "' not found in atlas '" + url + "'.")
        }
        return res ? res.textures[name + ".png"] ? res.textures[name + ".png"] : canvas.px.TextureEmpty : canvas.px.TextureEmpty
    },
    unpackBinary: function(data) {
        var strData = atob(data);
        var i, charData = [],
            len = strData.length;
        for (i = 0; i < len; i++) {
            charData.push(strData.charCodeAt(i))
        }
        var data = canvas.px.inflate(charData);
        return this.arrayBufferToString(data)
    },
    arrayBufferToString: function(buffer) {
        var bufView = new Uint16Array(buffer);
        var length = bufView.length;
        var result = "";
        var addition = Math.pow(2, 16) - 1;
        for (var i = 0; i < length; i += addition) {
            if (i + addition > length) {
                addition = length - i
            }
            result += String.fromCharCode.apply(null, bufView.subarray(i, i + addition))
        }
        return result
    },
    unpackAmf: function(buffer) {
        var arr = new Uint8Array(buffer);
        var str = "";
        var len = arr.length;
        for (var i = 0; i < len; i++) {
            str += String.fromCharCode(arr[i])
        }
        return canvas.px.AMF.parse(str)
    },
    applyUx: function(str) {
        if (this.ux && this.ux[str]) return str + "?ux=" + this.ux[str];
        else return str
        return str;
    },
    testUx: function(str) {
        return !this.ux || this.ux[str] ? true : false
    },
    addSubAlias: function(name, alias) {
        if (this.aliases[name]) {
            this.aliases[alias] = this.aliases[name]
        }
    },
    getResource: function(url) {
        return canvas.px.Loader.resources[url]
    },
    deleteResource: function(url) {
        delete canvas.px.Loader.resources[url]
    },
    destroy: function() {
        delete this.ux
    }
};
canvas.Log = {
    debug: false,
    ALL: "ALL",
    BATTLE: "BATTLE",
    MEM: "MEM",
    LOCATION: "LOCATION",
    WORLD: "WORLD",
    MANOR: "MANOR",
    HUNT: "HUNT",
    INST: "INST",
    USER: "USER",
    LEFT_MENU: "LEFT_MENU",
    AVATAR: "AVATAR",
    TOP_MENU: "TOP_MENU",
    RIGHT_MENU: "RIGHT_MENU",
    COMPASS: "COMPASS",
    MAGIC: "MAGIC",
    PETS: "PETS",
    BIRTHDAY: "BIRTHDAY",
    CUBE: "CUBE",
    CASINO: "CASINO",
    DICE_GAME: "DICE_GAME",
    TREASURE: "TREASURE",
    WHEEL: "WHEEL",
    MIRROR: "MIRROR",
    CLAN_CITADEL: "CLAN_CITADEL",
    CLAN_WAR: "CLAN_WAR",
    BARBER: "BARBER",
    FIRST_BATTLE: "FIRST_BATTLE",
    PUZZLE: "PUZZLE",
    ALL_NAMES: [this.BATTLE, this.MEM, this.LOCATION, this.WORLD, this.MANOR, this.HUNT, this.INST, this.USER, this.LEFT_MENU, this.AVATAR, this.TOP_MENU, this.RIGHT_MENU, this.COMPASS, this.MAGIC, this.PETS, this.BIRTHDAY, this.CUBE, this.CASINO, this.DICE_GAME, this.TREASURE, this.WHEEL, this.MIRROR, this.CLAN_WAR, this.CLAN_CITADEL, this.BARBER, this.FIRST_BATTLE, this.PUZZLE],
    maxLen: 2e3,
    log: {},
    internal: "",
    title: "",
    add: function(name, message, color) {
        if (name == this.ALL) {
            for (var i = 0; i < this.ALL_NAMES.length; i++) {
                this.add(this.ALL_NAMES[i], "GLOBAL LOG >> " + message, color)
            }
            return
        }
        if (!this.log[name]) this.log[name] = [];
        var str = "";
        if (color) {
            str += '<font color="' + color + '">'
        }
        str += Date.now() + ": " + message;
        if (color) {
            str += "</font>"
        }
        this.log[name].push(str);
        if (this.log[name].length > this.maxLen) {
            this.log[name].shift()
        }
        if (this.debug) {
            console.log(Date.now() + " " + name + ": " + message)
        }
    },
    addInternal: function(message, color) {
        if (color) {
            this.internal += '<font color="' + color + '">'
        }
        this.internal += Date.now() + ": " + message;
        if (color) {
            this.internal += "</font>"
        }
        this.internal += "<br/>"
    },
    show: function(name) {
        var str = "<h1>DWAR LOG</h1>" + (this.title ? "<span style='color: blue;'>" + this.title + "</span><br/><br/>" : "");
        for (var name in this.log) {
            var len = this.log[name].length;
            for (var i = 0; i < len; i++) {
                str += this.log[name][i] + "<br/>"
            }
        }
        this.log[name] = [];
        try {
            var w = window.open();
            w.document.open();
            w.document.write(str);
            w.document.close()
        } catch (e) {}
    },
    showInternal: function(target, w, h) {
        this.field = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD, 0, w, h, "left", "top", 16777215, 1);
        target.addChild(this.field);
        this.field.text = this.internal;
        canvas.EventManager.addEventListener(canvas.Event.STAGE_WHEEL, null, this.mouseWheelHandler, this)
    },
    mouseWheelHandler: function(e) {
        if (e.params.wheelDelta > 0) {
            this.field.container.y += 40
        } else {
            this.field.container.y -= 40
        }
    }
};
canvas.InputManager = {
    inited: false,
    lastExternalKey: "",
    lastExternalKeyTime: 0,
    init: function() {
        if (!this.inited) {
            document.addEventListener("keydown", this.onKeyDown.bind(this));
            document.addEventListener("keyup", this.onKeyUp.bind(this));
            this.inited = true
        }
    },
    onKeyDown: function(key) {
        canvas.EventManager.dispatchEvent(canvas.Event.STAGE_KEY_DOWN, null, this.processKey(key))
    },
    onKeyUp: function(key) {
        canvas.EventManager.dispatchEvent(canvas.Event.STAGE_KEY_UP, null, this.processKey(key))
    },
    processKey: function(key) {
        if (key.code) {
            key.globalKeyCode = canvas.Const.KEYS[canvas.Const.CODE_TO_KEYS[key.code]]
        } else {
            key.globalKeyCode = key.keyCode
        }
        return key
    },
    externalKey: function(e) {
        var str = e.altKey + "," + e.code + "," + e.ctrlKey + "," + e.globalKeyCode + "," + e.keyCode + "," + e.shiftKey;
        if (str != this.lastExternalKey || Date.now() > this.lastExternalKeyTime + 100) {
            this.onKeyDown(e);
            this.lastExternalKey = str;
            this.lastExternalKeyTime = Date.now()
        }
    }
};
canvas.Translator = {
    lang: "ru",
    dictionary: {
        text0: "ОЧIКУЙТЕ",
        text1: "ЗАВЕРШЕННЯ",
        text2: "вийти",
        text3: "Невозможно авторизироваться",
        text4: "ПОМИЛКА",
        text5: "соединение с сервером" + "\n" + "прервано",
        text6: "Бiй вже завершено",
        text7: "восстановить",
        text8: "Запрос",
        text9: "согласен",
        text10: "отклоняю",
        text11: "отменить",
        text12: "Отправлен запрос на применение",
        text13: "Ожидание ответа ...",
        text14: "Игрок",
        text15: "запрашивает использование на Вас",
        text16: "УВОРОТ",
        text17: "ПОГЛОЩЕНО",
        text18: "ОТРАЖЕНО",
        text19: "Невозможно применить эту магию",
        text20: "Невозможно использовать на себя",
        text21: "Невозможно использовать на дружественную цель",
        text22: "Невозможно использовать на текущего оппонента",
        text23: "Невозможно использовать на оппонентов",
        text24: "Не хватает маны",
        text25: "На цель уже наложено подобное заклинание",
        text26: "На вас наложено Молчание",
        text27: "Недостаточно стрел",
        text28: "Недостаточно ярости",
        text29: "Воскресiння",
        text30: "нанесена шкода",
        text31: "БЛОК",
        text32: "Закрити",
        text33: "атака в голову",
        text34: "атака в корпус",
        text35: "атака в ноги",
        text36: "оборонительный режим",
        text37: "обновить",
        text38: "позвать на помощь",
        text39: "покинуть бой?",
        text40: "Внимание! Покинув этот бой, вы не сможете в него вернуться.",
        text41: "данный игрок вышел из боя",
        text42: "Следующее использование через ",
        text43: "осталось",
        text44: "осталcя",
        text46: "ИММУНИТЕТ",
        text47: "ПРОМАХ",
        text48: "далее",
        text49: "мана",
        text50: "ходa",
        text51: "ход",
        text52: "ходов",
        text53: ", но не более",
        text54: "не более",
        text55: "д.",
        text56: "ч.",
        text57: "м.",
        text58: "с.",
        text59: "бiй перервали",
        text60: "Q/UP",
        text61: "W/RIGHT",
        text62: "E/DOWN",
        text63: "R/LEFT",
        text64: "Tab",
        text65: "пропустить ход",
        text66: "Викликати тінь в бій?",
        text67: "ПIДТВЕРДЖЕННЯ",
        text68: "прикликати",
        text69: "отмена",
        text70: "На цель не наложено необходимое заклинание",
        text71: "Всього / Вбитих",
        text72: "Неподходящая цель",
        text100: "Охрана",
        text101: "Ячейки в мире Фэо",
        text102: "Рюкзак",
        text103: "Хранилище",
        text104: "Войти",
        text105: "Найм рабочих",
        text106: "Здание",
        text107: "Енергія",
        text108: "по цене",
        text109: "Купить риолит",
        text110: "Разместить",
        text111: "Изменить",
        text112: "Ожидаемое время выкупа заявки",
        text113: "Здание построено до максимального уровня",
        text114: "Улучшение",
        text115: "Использовать",
        text116: "Продукты",
        text117: "нет данных",
        text118: "более 1 дня",
        text121: "Цена",
        text122: "Активные предложения",
        text123: "Продавец",
        text124: "Продать",
        text126: "Продать участок",
        text127: "Область",
        text128: "Локация",
        text129: "Накопленный опыт",
        text130: "Прибавлять",
        text131: "Вычитать",
        text132: "Вы накапливаете опыт когда находитесь в поместье. Количество накапливаемого опыта зависит от уровня здания.",
        text133: "Накапливать",
        text134: "Вы уверены, что хотите перейти в режим накапливания опыта?",
        text135: "Вы уверены, что хотите перейти в режим использования накопленного опыта? Стоимость составит",
        text136: "Накопленная доблесть",
        text137: "Вы накапливаете доблесть, когда находитесь в поместье. Количество накапливаемой доблести зависит от уровня здания.",
        text138: "Вы уверены, что хотите перейти в режим накапливания доблести?",
        text139: "Вы уверены, что хотите перейти в режим использования накопленной доблести? Стоимость составит",
        text140: "часов",
        text141: "Корм",
        text142: "Опит",
        text143: "Тренировать",
        text144: "Требуется",
        text145: "Изготовление займет",
        text146: "Изготовить",
        text147: "Выберите рецепт",
        text148: "Поместье",
        text149: "енергія",
        text150: "Выход",
        text151: "Работать",
        text152: "В локацию",
        text153: "Выбрать",
        text154: "Заказчик",
        text155: "Цена за ед.",
        text156: "Обновить",
        text157: "Биржа энергии",
        text158: "Все заявки",
        text159: "Мои заявки",
        text160: "У вас есть",
        text161: "единиц энергии",
        text162: "Выполн./Объем",
        text163: "Создать заявку",
        text164: "Удалить заявку?",
        text165: "Прогноз: работы будут выполнены через",
        text166: "рекоммендуется повысить цену работ",
        text167: "Купить",
        text168: "Купить участок за",
        text169: "Риолит",
        text170: "У вас денег",
        text171: "Вы потратите",
        text172: "уровень",
        text173: "Поместья",
        text174: "Покупка",
        text175: "Продажа",
        text176: "требует энергии",
        text177: "Нанять рабочих",
        text178: "Построить",
        text179: "Построен на",
        text180: "Благо антитравматизма",
        text181: "Получить",
        text182: "Потратить",
        text183: "Вы получите",
        text184: "Работать на",
        text186: "Количество",
        text187: "Все",
        text188: "Скрыть меню",
        text189: "Показать меню",
        text190: "Ваши вещи со сроком жизни",
        text191: "Список задач",
        text192: "Забрать",
        text193: "Отменить",
        text194: "хватает",
        text195: "не хватает",
        text196: "из",
        text199: "Цена изменилась. Купить участок за",
        text200: "купить",
        text201: "нет предложений",
        text202: "снять с продажи",
        text203: "продать",
        text204: "Снять участок с продажи?",
        text205: "У вас энергии",
        text206: "или",
        text207: "Текущее местоположение",
        text208: "Переход между",
        text209: "Переход в область",
        text210: "Загрузка",
        text211: "Флаг",
        text212: "Ваше текущее местоположение",
        text213: "Выделенная область",
        text214: "Выделенная локация",
        text215: "Список локаций",
        text216: "Ваше местоназначение",
        text217: "Цель",
        text218: "Ваше поместье",
        text219: "Купить у государства",
        text220: "Купить у игроков",
        text221: "Режим карты",
        text222: "Режим недвижимости",
        text224: "Ваш участок",
        text225: "Переезд на новый участок обойдётся вам в",
        text226: "Поговорить",
        text227: "Искать рецепт",
        text228: "Разрушить",
        text229: "Разрушить здание",
        text230: "Выбор здания для постройки",
        text231: "Для постройки требуется",
        text232: "Вернуться",
        text233: "Если вы еще не использовали ресурсы для строительства данного здания, можно изменить  выбор, вернувшись к списку доступных для возведения зданий.",
        text234: "Фарм",
        text235: "Добыть за",
        text236: "Произвести за",
        text237: "Произвести",
        text238: "Не хватает ресурсов для производства",
        text239: "До окончания поиска клада осталось",
        text240: "Котлован",
        text242: "добавить",
        text243: "информация",
        text244: "Все рецепты",
        text245: "Избранные рецепты",
        text246: "Ваши вещи для хранения на складе",
        text247: "Ваши вещи для «заморозки» на складе",
        text248: "Доступно при постройке Хранилища",
        text249: "уровня",
        text250: "В Поместье все здания построены до максимального уровня.",
        text251: "Доступные фронты",
        text252: "В тронный зал",
        text253: "Склад",
        text254: "Тронный зал",
        text255: "Необходимо активировать портал",
        text256: "Доступно при постройке портала",
        text258: "После открытия портала вы сможете выбрать локацию, на которую он будет вести.",
        text259: "Искать локацию",
        text260: "Перенастройка станет доступна через",
        text261: "Активировать",
        text262: "Перенастроить",
        text263: "Сбросить",
        text264: "Запрашивать подтверждение при перемещении в локации",
        text265: "Перехід за",
        text266: "Купить камень",
        text267: "Не ждать",
        text268: "Выберите локацию",
        text269: "Переместиться в локацию",
        text270: "Перехід",
        text271: "Стоимость составит",
        text272: "Сортировка по названию",
        text273: "Сортировка по цвету",
        text274: "Сортировка по типу",
        text275: "Сортировка по времени жизни",
        text276: "Введите название предмета",
        text277: "Поиск по названию",
        text278: "Сортировка предметов",
        text279: "Сбросить фильтр",
        text280: "Сортировка по умолчанию",
        text281: "Введите имя питомца",
        text282: "Сортировка по готовности",
        text283: "Сортировка по имени",
        text284: "Сортировка по уровню",
        text285: "Сортировка",
        text286: "Гарантированные трофеи",
        text287: "Осталось нажатий",
        text288: "Убить сразу 10 монстров",
        text289: "Выберите монстра для охоты",
        text290: "Охота за",
        text291: "Большая охота за",
        text292: "Егерский лагерь",
        text293: "Добыча ресурсов",
        text294: "Поиск артефактов",
        text295: "Экипировка",
        text296: "Тип ресурса",
        text297: "Вид ресурса",
        text298: "Награда",
        text299: "Фрагменты в наличии",
        text300: "Выйти",
        text301: "Гильдийский пул",
        text302: "Серый остров",
        text303: "Зеленый остров",
        text304: "Золотой остров",
        text305: "Красный остров",
        text306: "Синий остров",
        text307: "Бойцы",
        text308: "Выпустить резерв",
        text309: "Готовий",
        text310: "закрити",
        text311: "лог бою",
        text312: "команда",
        text313: "Ok",
        text314: "До начала",
        text315: "Резерв",
        text316: "Воеводы",
        text317: "Левая башня",
        text318: "Правая башня",
        text319: "Ворота",
        text320: "Вы уверены, что хотите покинуть клановую битву до ее завершения?",
        text321: "Расставить",
        text322: "Воевода",
        text323: "Внимание! Не все участники распределены по островам.<br>Вы уверены, что хотите вступить в бой?",
        text324: "Создать случайный талисман",
        text325: "Создать талисман выбранного уровня",
        text326: "Разрушить предметы",
        text327: "Выбор питомца",
        text328: "Трава",
        text329: "Камни",
        text330: "Рыба",
        text331: "Поиск талисманов",
        text332: "Поиск ценностей",
        text333: "Поиск снаряжения",
        text334: "Вы уверены, что хотите разрушить вещь?",
        text335: "Добыча травы",
        text336: "Добыча камней",
        text337: "Добыча рыбы",
        text338: "Выберите питомца",
        text339: "Добыча ресурсов в Егерском лагере доступна со 2 уровня здания.",
        text340: "План",
        text400: "Не определено",
        text402: "Фарм пройшов успiшно",
        text403: "Фарм не вдався",
        text405: "мирный",
        text406: "пассивный",
        text407: "спокойный",
        text408: "раздраженный",
        text409: "агрессивный",
        text410: "частый",
        text411: "обычный",
        text412: "редкий",
        text413: "единичный",
        text414: "раритетный",
        text417: "Виберіть об'єкт дії",
        text418: "Опис об'екта",
        text419: "добыть",
        text420: "собрать",
        text421: "ловить",
        text422: "атаковать",
        text424: "Недостаточно даных",
        text425: "Неверные даные",
        text426: "Невiдома помилка",
        text429: "Вами",
        text430: "Всего",
        text431: "Проверка мастерства",
        text432: "Соберите изображение за отведенное время и нажмите кнопку “готово”",
        text433: "Оставшееся время",
        text434: "сек.",
        text435: "Готово",
        text436: "Ви почали добувати ресурс першим",
        text437: "Ви почали добувати ресурс другим",
        text438: "Перетащите, чтобы найти клад",
        text440: "использовать",
        text441: "Выполняется действие",
        text442: "Действие выполнено успешно",
        text443: "Действие завершилось неудачей",
        text444: "далеко",
        text445: "близко",
        text446: "рядом!",
        text447: "Вiдображати ресурс цього кольору",
        text500: "Премиум-аккаунт",
        text505: "статуя",
        text506: "обелиск",
        text507: "Осталось убийств монстров на сегодня",
        text600: "Дар небес",
        text601: "Испытайте судьбу и заберите награду",
        text602: "Каждый бросок приближает к четырем кубикам!",
        text603: "Кости",
        text604: "Призы",
        text605: "Каждый день вы можете испытать удачу до 5 раз подряд. Первые две попытки бросается 1 кубик, следующие две попытки - 2 кубика, пятая попытка - 3 кубика. Сумма очков, выпавших на кубиках, укажет на ваш приз! Кроме того, каждый бросок кубиков приближает вас к супер-игре, которая позволит вам бросить сразу 4 кубика и даст шанс выиграть самые ценные призы!",
        text606: "Бросить бесплатно",
        text607: "Бросить за",
        text608: "Завершить игру на 1 час",
        text609: "/info/library/index.php?obj=cat&id=157",
        text700: "Нажмите, чтобы получить Дар Небес!",
        text702: "Локация под контролем магмар",
        text703: "Локация под контролем людей",
        text704: "Локация свободна",
        text706: "Бой доступен через",
        text707: "Начать бой",
        text708: "Бой доступен",
        text709: "Подготовка к бою еще",
        text710: "Идет бой",
        text711: "Время для боя еще не пришло",
        text712: "Вступить",
        text713: "Бой уже идет. Вы не можете вмешаться.",
        text714: "Вмешаться в бой",
        text715: "Вы уверены, что хотите вмешаться в бой за локацию",
        text716: "Действие доступно до",
        text718: "Помилка",
        text720: "Вы уверены, что хотите начать бой за локацию",
        text721: "/info/library/index.php?obj=cat&id=161",
        text723: "Время",
        text724: "Владения людей",
        text725: "Владения магмар",
        text726: "Нейтральные владения",
        text727: "Ожидание начала боя",
        text728: "Подготовка к бою",
        text731: "Воскреснути за",
        text732: "Чарiвне дзеркало",
        text733: "Активнiсть",
        text734: "Однорукий бандит",
        text735: "Бриллиантовый бандит",
        text800: "Название",
        text802: "Снять",
        text803: "Снять эффект",
        text805: "Да",
        text806: "Нет",
        text809: "Количество слотов зависит от браслета",
        text810: "Скрыть",
        text811: "Осталось скрытий",
        text812: "Скрыть все",
        text813: "Отменить скрытие",
        text814: "Открыть все",
        text900: "Взломать",
        text901: "Открыть",
        text902: "Оставшиеся ходы",
        text903: "Замок заклинило!",
        text905: "Вы уверены?",
        text1000: "убрать",
        text1100: "/info/forum/",
        text1200: "Бриллиант",
        text1201: "назад",
        text1202: "Цитадель",
        text1203: "Чертеж",
        text1250: "Обратиться",
        text1252: "Нужен уровень",
        text1253: "Алтарь доступен",
        text1254: "Персонаж доступен",
        text1255: "Персонажі",
        text1299: "строить",
        text1300: "ПОКАЗАТИ ВБИТИХ",
        text1301: "СХОВАТИ ВБИТИХ",
        text1302: "Показати життя/ману",
        text1303: "Сховати життя/ману",
        text1304: "1я Команда",
        text1305: "2я Команда",
        text1306: "Введіть нік цілі",
        text1400: "Магия Зеркал",
        text1401: "Вы можете выиграть один из этих призов",
        text1402: "ваш результат",
        text1403: "Играть за",
        text1404: "Выберите любые три зеркала,<br/>сумма очков укажет ваш приз!",
        text1405: "Шкала удачи",
        text1406: "Гарантированные призы",
        text1407: "/info/library/index.php?obj=cat&id=189",
        text1408: "Каждая игра накапливает энергию удачи. При достижении максимума Вы сможете сыграть на более ценные призы!",
        text1409: "Каждая игра приближает вас к очередному гарантированному призу. Счетчик игр сбрасывается по окончании события!",
        text1500: "Режим просмотра",
        text1501: "Выберите объект в зале, который хотите купить. Вы увидите список доступных вариантов.",
        text1502: "Магазин",
        text1506: "поставить",
        text1507: "Купить предмет",
        text1508: "примерить",
        text1509: "Сегодня день рождения у <b>#0#</b> ваших друзей!<br/>Вы можете подарить им подарки.",
        text1510: "Поздравить",
        text1511: "Подарки в рюкзаке",
        text1512: "Купить подарок",
        text1513: "У вас нет ни одного подарка.",
        text1514: "",
        text1515: "Сегодня день рождения у <b>1</b> вашего друга!<br/>Вы можете подарить ему подарок.",
        text1516: "Ваш текущий бонус",
        text1517: "Получить бонус",
        text1518: "Играть еще раз",
        text1519: "Начав следующую игру, вы получите возможность накопить новый бонус вместо текущего.<br/>Хотите сыграть еще раз?",
        text1520: "Бесплатная попытка через",
        text1521: "Игра окончена",
        text1522: "Жми!",
        text1523: "Начать",
        text1524: "Завершить",
        text1525: "Недостаточно средств!",
        text1526: "Чтобы получить бонус, обменяйте бриллианты на золото.",
        text1527: "Максимальный выигрыш на сервере",
        text1528: "Ваш наибольший выигрыш за сегодня",
        text1600: "ДЖЕКПОТ",
        text1601: "Беспл.игр",
        text1700: "Опис",
        text1701: "Виберiть стать персонажа i вступіть у бiй зi своiм першим противником. Щоб перемогти ворога, використовуйте не лише звичайнi удари, а й заклинання.",
        text1702: "Вибiр роду",
        text1703: "ЧОЛОВIК",
        text1704: "ЖIНКА",
        text1705: "вибрати",
        text1706: "магия огня",
        text1707: "магия воздуха",
        text1708: "магия света",
        text1709: "магия земли",
        text1710: "магия тьмы",
        text1711: "перейти в режим магii",
        text1712: "перейти в режим атаки",
        text1713: "Инферно, длительность #0# сек",
        text1714: "используйте книгу заклинаний",
        text1715: "Боi - лише частина великих звершень та пригод, якi чекають на вас у грi Легенда: Спадщина драконiв.",
        text1716: "Ми захоплюємось вашою силою та безстрашнiстю, i iншим вам долю великого воiна. А тепер зареєструйтесь у грi та продовжуйте свiй легендарний шлях у свiтi Фео!",
        text1717: "Щоб перевiрити своi сили в бою, виберiть один з типiв ударiв, навiвши курсор мишi на будь-який з мечiв, розташованих по колу.",
        text1718: "Освоiти режим магii дуже просто: наведiть курсор мишi на меч на синьому полi та використовуйте сили вогню, повiтря чи темряви.",
        text1800: "Выход в",
        text1801: "Вход в",
        text1802: "Игроки",
        text2000: "д",
        text2001: "г",
        text2002: "хв",
        text2003: "сек",
        text2004: "Січня",
        text2005: "Лютого",
        text2006: "Березня",
        text2007: "Квітня",
        text2008: "Травня",
        text2009: "Червня",
        text2010: "Липня",
        text2011: "Серпня",
        text2012: "Вересня",
        text2013: "Жовтня",
        text2014: "Листопада",
        text2015: "Грудня",
        text2016: "м",
        text2017: "с",
        text2018: "Ок",
        text2019: "Отмена",
        text2020: "Подтверждение",
        text2021: "За",
        text2022: "шт",
        text2023: "час",
        text2024: "часа",
        text2025: "ур.",
        text2026: "Больше не спрашивать",
        text2027: "Сбрасывается при выходе из локации",
        text2028: "З",
        text2029: "до",
        text2030: "Час до закiнчення",
        text2031: "Час до початку",
        text2032: "Активна подiя",
        text2033: "Отправить",
        text2034: "Создать",
        text3000: "великi битви",
        text3001: "невидимiсть",
        text3002: "вiдвага",
        text3003: "життя",
        text3004: "пошта",
        text3005: "квести",
        text3006: "тварини",
        text3007: "компас",
        text3008: "професii",
        text3009: "урчi",
        text3010: "Прокладіть найкоротший шлях до будь-якої точки ФЕО",
        text3011: "Локації",
        text3012: "Ресурси",
        text3013: "Монстри",
        text3014: "інстанси",
        text3015: "Шлях займе",
        text3016: "переходів",
        text3017: "Искомый объект можно найти в нескольких локациях:",
        text3018: "Ничего не найдено",
        text3019: "Маршрут не найден",
        text3020: "перехід",
        text3021: "переходів",
        text3022: "Продовжити маршрут",
        text3023: "Объект находится в текущей локации",
        text3024: "Продовжити",
        text3025: "Недостаточно средств.",
        text3026: "Случайный вид",
        text3027: "Смена образа",
        text3028: "Персонализация",
        text3029: "Выбор народа",
        text3030: "Создания мира Фэо,  обладающие разумом, волей и человеческой речью, обитают на материке Огрий, приручили тигров, владеют магией света, воздуха, воды.",
        text3031: "Создания мира Фэо, в их жилах течет раскаленная лава, обладают огромной физической силой, укротили зорбов, поклоняются богине Верциде, владеют магией теней, огня, земли.",
        text3032: "Дійти",
        text3033: "Соединение",
        text3034: "Регистрация открыта",
        text3035: "Регистрация закрыта",
        text3036: "Хорошее",
        text3037: "Население",
        text3038: "Выбрать сервер",
        text3039: "Выбор сервера",
        text3040: "Введіть назву об'єкту",
        text3041: "або виберіть зі списку",
		text3042: "репутацii"
    },
    isInited: false,
    init: function(xml) {
        this.isInited = true;
        for (key in this.dictionary) {
            this.dictionary[key] = this.dictionary[key].replace("И", String.fromCharCode(1048))
        }
        var str;
        var hash = {};
        for (key in this.dictionary) {
            if (hash[this.dictionary[str]])
                canvas.Log.add(canvas.Log.ALL, "Same word in Dictionary! ID: " + str + " VALUE: " + this.dictionary[str] + " REPLACE_ID: " + hash[this.dictionary[str]], 16711680);
            else
                hash[this.dictionary[str]] = str
        }
        if (xml && xml.firstChild && xml.firstChild.childNodes) {
            var len = xml.firstChild.childNodes.length;
            for (var i = 0; i < len; i++) {
                var element = xml.firstChild.childNodes[i];
                if (!element.firstChild)
                    continue;
                this.dictionary[element.nodeName] = element.textContent
            }
        }
    },
    getText: function(id, params) {
        var result = this.dictionary ? this.dictionary["text" + id.toString()] : "Translator not initialized!";
        var r;
        var a;
        var b;
        var str;
        var str2;
        var key;
        if (params) {
            r = /#\d+[^#]*#/gi;
            a = result.match(r);
            for (key in a) {
                str = a[key];
                if (typeof str != "string")
                    continue;
                b = str.substr(1, str.length - 2).split(",");
                str2 = params[parseInt(b[0])];
                if (str2) {
                    result = result.replace(str, str2)
                }
            }
        }
        return result
    },
    getLang: function() {
        return this.lang
    },
    toUpperCaseFirstChar: function(value) {
        if (!value || value == "")
            return "";
        return value.charAt(0).toUpperCase() + value.substr(1).toLowerCase()
    }
};
canvas.Event = {
    STAGE_MOUSE_UP: "STAGE_MOUSE_UP",
    STAGE_MOUSE_MOVE: "STAGE_MOUSE_MOVE",
    STAGE_MOUSE_OUT: "STAGE_MOUSE_OUT",
    STAGE_WHEEL: "STAGE_WHEEL",
    STAGE_KEY_DOWN: "STAGE_KEY_DONW",
    STAGE_KEY_UP: "STAGE_KEY_UP",
    UX_READY: "UX_READY",
    ARTIKULS_LOADED: "ARTIKULS_LOADED"
};
canvas.px.MovieClipEvent = {
    EVENT_UPDATE: "MovieClip.UPDATE",
    EVENT_COMPLETE: "MovieClip.COMPLETE",
    EVENT_READY: "MovieClip.READY"
};
canvas.px.MovieClipProps = {
    colorTransform: "ct",
    alpha: "al",
    masking: "mg",
    mask: "mk",
    matrix: "mx",
    actions: "as",
    children: "ch",
    label: "lb"
};
canvas.px.MovieClip = function(url, basePath, smoothing) {
    this._frameEvent = "";
    Object.defineProperty(this, "frameEvent", {
        get: function() {
            return this._frameEvent
        },
        set: function(value) {
            if (this._frameEvent) {
                canvas.EventManager.removeEventListener(this._frameEvent, null, this._render, this)
            }
            this._frameEvent = value;
            if (this._frameEvent) {
                canvas.EventManager.addEventListener(this._frameEvent, null, this._render, this)
            }
            this.updateFrameEvent()
        }
    });
    this._currentFrame = 1;
    Object.defineProperty(this, "currentFrame", {
        get: function() {
            return this._currentFrame
        },
        set: function(value) {
            if (value > this.totalFrames) {
                if (!this.loop) {
                    this.stop();
                    canvas.EventManager.dispatchEvent(canvas.px.MovieClipEvent.EVENT_COMPLETE, this);
                    if (this.autoDestroy) {
                        this.destroy({
                            children: true
                        })
                    }
                    return
                }
            }
            this._currentFrame = value > this.totalFrames ? 1 : value < 1 ? this.totalFrames : value
        }
    });
    this._locked = false;
    Object.defineProperty(this, "locked", {
        get: function() {
            return this._locked
        },
        set: function(value) {
            if (this._locked == value) return;
            this._locked = value
        }
    });
    this.url = url;
    this.container = new canvas.px.Container;
    this.spriteHash = {};
    this.stopped = false;
    this.labels = {};
    this.currentLabel = "";
    this.basePath = basePath;
    this.autoDestroy = false;
    this.loop = false;
    this.ready = false;
    this.skipMasks = false;
    this.mcChildsCache = {};
    this.smoothing = smoothing == undefined ? true : smoothing;
    canvas.px.Container.call(this);
    this.addChild(this.container);
    if (!this.data && !canvas.ResourceLoader.get(this.url + ".mci")) {
        canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this._init, this);
        canvas.ResourceLoader.add([this.url + ".mci"])
    } else {
        this._init()
    }
    return this
};
canvas.px.MovieClip.prototype = Object.create(canvas.px.Container.prototype);
canvas.px.MovieClip.prototype._init = function() {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this._init, this);
    if (!this.data) {
        if (!canvas.ResourceLoader.get(this.url + ".mci")) {
            return
        }
        this.data = canvas.ResourceLoader.get(this.url + ".mci").data
    }
    this.maxTotalFrames = this.totalFrames = this.data.frames.length;
    if (!this.mcChilds) this.mcChilds = this.data.mc;
    if (this.mcChilds) {
        for (var str in this.mcChilds) {
            var len = this.mcChilds[str].frames.length;
            if (len > this.maxTotalFrames) this.maxTotalFrames = len
        }
    }
    for (var str in this.data.frames) {
        if (this.data.frames[str][canvas.px.MovieClipProps.label]) {
            this.labels[this.data.frames[str][canvas.px.MovieClipProps.label].name] = this.data.frames[str][canvas.px.MovieClipProps.label].frame
        }
    }
    if (!this.framePath) this.framePath = this.data.name ? this.data.name + "/" : "";
    if (!this.atlas) {
        var atlasUrl = "";
        if (this.data.noAtlas) {
            atlasUrl = ""
        } else if (this.data.atlas) {
            atlasUrl = (this.basePath ? this.basePath : canvas.Config.imgPath) + this.data.atlas
        } else {
            atlasUrl = this.url
        }
        this.atlasUrl = atlasUrl;
        if (atlasUrl && !canvas.ResourceLoader.get(atlasUrl + ".json")) {
            canvas.ResourceLoader.add([atlasUrl + ".json"]);
            canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this._ready, this)
        } else {
            this._ready()
        }
    } else {
        this._ready()
    }
};
canvas.px.MovieClip.prototype._ready = function() {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this._ready, this);
    if (!this.atlas && this.atlasUrl) {
        this.atlas = canvas.ResourceLoader.get(this.atlasUrl + ".json").textures
    }
    this.ready = true;
    this.render();
    this.currentFrame = 1;
    canvas.EventManager.dispatchEvent(canvas.px.MovieClipEvent.EVENT_READY, this)
};
canvas.px.MovieClip.prototype._render = function() {
    if (!this.stopped) this.render()
};
canvas.px.MovieClip.prototype.render = function() {
    if (this.locked) return;
    if (canvas.px.MovieClip.debugEnable) {
        if (Date.now() - canvas.px.MovieClip.debugTime >= 1e3) {
            console.log("Renders per second: " + canvas.px.MovieClip.debugRenderCount);
            canvas.px.MovieClip.debugRenderCount = 0;
            canvas.px.MovieClip.debugTime = Date.now()
        }
        canvas.px.MovieClip.debugRenderCount++
    }
    if (!this.ready) return;
    if (this.data.frames[this.currentFrame - 1][canvas.px.MovieClipProps.label]) {
        this.currentLabel = this.data.frames[this.currentFrame - 1][canvas.px.MovieClipProps.label].name
    }
    var actions = this.data.frames[this.currentFrame - 1][canvas.px.MovieClipProps.actions];
    if (actions) {
        for (var key in actions) {
            var action = actions[key];
            if (this[action.name]) {
                switch (action.name) {
                    case "gotoAndPlay":
                        this.gotoAndPlay(parseInt(action.params[0]) + 1);
                        break;
                    default:
                        this[action.name].apply(this, action.params ? action.params : null)
                }
            }
        }
    }
    this.container.removeChildren();
    var key;
    for (key in this.mcChildsCache) {
        this.mcChildsCache[key].wasActive = false
    }
    var cacheIndexes = {};
    var colorTransform;
    var matrix;
    for (key in this.data.frames[this.currentFrame - 1][canvas.px.MovieClipProps.children]) {
        var child = this.data.frames[this.currentFrame - 1][canvas.px.MovieClipProps.children][key];
        if (child[canvas.px.MovieClipProps.masking]) {
            continue
        }
        if (!child.name && !child.mc) continue;
        var sprite;
        if (child.mc) {
            if (this.mcChildsCache[child.mc]) {
                sprite = this.mcChildsCache[child.mc];
                sprite.gotoAndStop(sprite.currentFrame)
            } else {
                sprite = new canvas.px.MovieClipChild(this.mcChilds[child.mc], this.atlas, this.framePath, this.mcChilds, this.smoothing);
                this.mcChildsCache[child.mc] = sprite
            }
            sprite.name = child.mc;
            sprite.wasActive = true
        } else {
            sprite = this.getImage(child.name, cacheIndexes);
            sprite.name = child.name
        }
        this.clearImage(sprite);
        matrix = child[canvas.px.MovieClipProps.matrix];
        if (!matrix.set) {
            child[canvas.px.MovieClipProps.matrix] = matrix = new canvas.px.Matrix(matrix.a, matrix.b, matrix.c, matrix.d, matrix.tx, matrix.ty)
        }
        sprite.transform.localTransform = matrix;
        sprite.alpha = child[canvas.px.MovieClipProps.alpha] == undefined ? 1 : child[canvas.px.MovieClipProps.alpha];
        colorTransform = child[canvas.px.MovieClipProps.colorTransform];
        if (colorTransform) {
            if (typeof colorTransform == "string") {
                sprite.filters = [canvas.Functions.colorMatrixCache[colorTransform]]
            } else {
                child[canvas.px.MovieClipProps.colorTransform] = canvas.Functions.getColorMatrixHash.apply(this, colorTransform);
                sprite.filters = [canvas.Functions.getColorMatrixFilter.apply(this, colorTransform)]
            }
        }
        this.container.addChild(sprite);
        if (!this.skipMasks && child[canvas.px.MovieClipProps.mask]) {
            var mskData = child[canvas.px.MovieClipProps.mask];
            var msk = this.getImage(mskData.name, cacheIndexes);
            this.clearImage(msk);
            matrix = mskData[canvas.px.MovieClipProps.matrix];
            if (!matrix.set) {
                mskData[canvas.px.MovieClipProps.matrix] = matrix = new canvas.px.Matrix(matrix.a, matrix.b, matrix.c, matrix.d, matrix.tx, matrix.ty)
            }
            msk.transform.localTransform = matrix;
            this.container.addChild(msk);
            sprite.mask = msk
        }
    }
    for (key in this.mcChildsCache) {
        var tempMc = this.mcChildsCache[key];
        if (!tempMc.wasActive) tempMc.currentFrame = tempMc.totalFrames
    }
    canvas.EventManager.dispatchEvent(canvas.px.MovieClipEvent.EVENT_UPDATE, this);
    this.currentFrame++
};
canvas.px.MovieClip.prototype.getImage = function(name, cacheIndexes) {
    if (cacheIndexes[name] == undefined) cacheIndexes[name] = 0;
    else cacheIndexes[name]++;
    var sprite;
    var indexedName = name + "__" + cacheIndexes[name];
    var t;
    if (this.spriteHash[indexedName]) {
        sprite = this.spriteHash[indexedName]
    } else {
        t = this.atlas ? this.atlas[this.framePath + name + ".png"] : canvas.px.TextureEmpty;
        if (!this.smoothing && t) t.baseTexture.scaleMode = canvas.px.ScaleMode.NEAREST;
        sprite = new canvas.px.Sprite(t);
        this.spriteHash[indexedName] = sprite
    }
    return sprite
};
canvas.px.MovieClip.prototype.clearImage = function(sprite) {
    sprite.mask = null;
    sprite.filters = null
};
canvas.px.MovieClip.prototype.gotoAndPlay = function(frame) {
    this.currentFrame = typeof frame == "string" ? this.labels[frame] || 1 : frame;
    this.stopped = false
};
canvas.px.MovieClip.prototype.gotoAndStop = function(frame) {
    this.currentFrame = typeof frame == "string" ? this.labels[frame] || 1 : frame;
    this.stopped = true;
    this.render()
};
canvas.px.MovieClip.prototype.stop = function() {
    this.stopped = true
};
canvas.px.MovieClip.prototype.play = function() {
    this.stopped = false
};
canvas.px.MovieClip.prototype.stopChildren = function() {
    if (this.mcChildsCache) {
        for (var str in this.mcChildsCache) {
            this.mcChildsCache[str].frameEvent = this.frameEvent;
            this.mcChildsCache[str].stop()
        }
    }
};
canvas.px.MovieClip.prototype.playChildren = function() {
    if (this.mcChildsCache) {
        for (var str in this.mcChildsCache) {
            this.mcChildsCache[str].frameEvent = this.frameEvent;
            this.mcChildsCache[str].play()
        }
    }
};
canvas.px.MovieClip.prototype.updateFrameEvent = function() {};
canvas.px.MovieClip.prototype.destroy = function() {
    this.stop();
    this.frameEvent = null;
    if (this.parent) this.parent.removeChild(this);
    canvas.px.Container.prototype.destroy.call(this, {
        children: true
    });
    var key;
    for (key in this.spriteHash) {
        this.spriteHash[key].destroy()
    }
    for (key in this.mcChildsCache) {
        this.mcChildsCache[key].destroy()
    }
};
canvas.px.MovieClip.debugEnable = false;
canvas.px.MovieClip.debugTime = Date.now();
canvas.px.MovieClip.debugRenderCount = 0;
canvas.px.MovieClipChild = function(data, atlas, framePath, mcChilds, smoothing) {
    this.data = data;
    this.atlas = atlas;
    this.framePath = framePath;
    this.mcChilds = mcChilds;
    canvas.px.MovieClip.call(this, null, null, smoothing);
    this.loop = true;
    this.gotoAndStop(1)
};
canvas.px.MovieClipChild.prototype = Object.create(canvas.px.MovieClip.prototype);
canvas.px.WindowEvent = {
    EVENT_OPEN: "WindowEvent.EVENT_OPEN",
    EVENT_CLOSE: "WindowEvent.EVENT_CLOSE",
    EVENT_DRAG: "WindowEvent.EVENT_DRAG",
    EVENT_DRAG_FINISH: "WindowEvent.EVENT_DRAG_FINISH"
};
canvas.px.Window = function() {
    canvas.px.Container.call(this);
    this.closeButton = null;
    this.header = null;
    this.added = this.addedHandler.bind(this);
    this.removed = this.removedHandler.bind(this);
    this.click = this.clickHandler.bind(this);
    this.mousedown = this.downHandler.bind(this);
    this.interactive = true;
    this.dragAvailable = true
};
canvas.px.Window.prototype = Object.create(canvas.px.Container.prototype);
canvas.px.Window.prototype.clickHandler = function(mouseData) {
    if (canvas.Functions.findParent(this.closeButton, mouseData.target)) {
        canvas.EventManager.dispatchEvent(canvas.px.WindowEvent.EVENT_CLOSE, this)
    }
};
canvas.px.Window.prototype.downHandler = function(mouseData) {
    if (canvas.Functions.findParent(this.header, mouseData.target)) {
        canvas.EventManager.dispatchEvent(canvas.px.WindowEvent.EVENT_DRAG, this, mouseData)
    }
};
canvas.px.Window.prototype.addedHandler = function(e) {};
canvas.px.Window.prototype.removedHandler = function(e) {};
canvas.px.Window.prototype.resize = function(r) {};
canvas.px.Window.prototype.startDrag = function(rect, point) {
    if (!this.dragAvailable) return;
    this.dragRect = rect;
    this.startDragMousePoint = new canvas.px.Point(point.x - this.x, point.y - this.y);
    canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_MOVE, null, this.dragHandler, this)
};
canvas.px.Window.prototype.dragHandler = function(event) {
    var targetX = event.params.mouseData.offsetX - this.startDragMousePoint.x;
    var targetY = event.params.mouseData.offsetY - this.startDragMousePoint.y;
    targetX = targetX < this.dragRect.x ? this.dragRect.x : targetX > this.dragRect.x + this.dragRect.width ? this.dragRect.x + this.dragRect.width : targetX;
    targetY = targetY < this.dragRect.y ? this.dragRect.y : targetY > this.dragRect.y + this.dragRect.height ? this.dragRect.y + this.dragRect.height : targetY;
    this.position.set(Math.max(0, Math.round(targetX)), Math.max(0, Math.round(targetY)))
};
canvas.px.Window.prototype.stopDrag = function() {
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_MOVE, null, this.dragHandler, this);
    canvas.EventManager.dispatchEvent(canvas.px.WindowEvent.EVENT_DRAG_FINISH, this)
};
canvas.px.Window.prototype.destroy = function() {
    canvas.px.Container.prototype.destroy.apply(this, [{
        children: true
    }])
};
canvas.px.Mask = function(w, h, color, alpha) {
    this._active = true;
    Object.defineProperty(this, "active", {
        get: function() {
            return this._active
        },
        set: function(value) {
            this._active = value;
            this.update()
        }
    });
    canvas.px.Graphics.call(this);
    if (w == undefined) w = 100;
    if (h == undefined) h = 100;
    this.color = color == undefined ? 0 : color;
    this.alpha = alpha == undefined ? 0 : alpha;
    this.setSize(w, h)
};
canvas.px.Mask.prototype = Object.create(canvas.px.Graphics.prototype);
canvas.px.Mask.prototype.setSize = function(w, h) {
    if (w == undefined) w = -1;
    if (h == undefined) h = -1;
    if (w >= 0) this._width = w;
    if (h >= 0) this._height = h;
    this.update()
};
canvas.px.Mask.prototype.update = function() {
    this.clear();
    if (this.active) {
        this.beginFill(this.color, this.alpha);
        this.drawRect(0, 0, this._width, this._height);
        this.endFill()
    }
};
canvas.px.RoundRect = function(color, alpha, w, h, ellipseWidth, ellipseHeight, centered, fill, borderColor, borderAlpha) {
    this._myColor = 0;
    Object.defineProperty(this, "myColor", {
        get: function() {
            return this._myColor
        },
        set: function(value) {
            this._myColor = value;
            this.update()
        }
    });
    this._myAlpha = 1;
    Object.defineProperty(this, "myAlpha", {
        get: function() {
            return this._myAlpha
        },
        set: function(value) {
            this._myAlpha = value;
            this.update()
        }
    });
    this._borderColor = 0;
    Object.defineProperty(this, "borderColor", {
        get: function() {
            return this._borderColor
        },
        set: function(value) {
            this._borderColor = value;
            this.update()
        }
    });
    this._borderAlpha = 1;
    Object.defineProperty(this, "borderAlpha", {
        get: function() {
            return this._borderAlpha
        },
        set: function(value) {
            this._borderAlpha = value;
            this.update()
        }
    });
    this._w = 0;
    Object.defineProperty(this, "w", {
        get: function() {
            return this._w
        },
        set: function(value) {
            if (value == this._w) return;
            this._w = value;
            this.update()
        }
    });
    this._h = 0;
    Object.defineProperty(this, "h", {
        get: function() {
            return this._h
        },
        set: function(value) {
            if (value == this._h) return;
            this._h = value;
            this.update()
        }
    });
    this._ellipseWidth = 0;
    Object.defineProperty(this, "ellipseWidth", {
        get: function() {
            return this._ellipseWidth
        },
        set: function(value) {
            if (value == this._ellipseWidth) return;
            this._ellipseWidth = value;
            this.update()
        }
    });
    canvas.px.Graphics.call(this);
    if (color == undefined) color = 0;
    if (alpha == undefined) alpha = 1;
    if (w == undefined) w = 100;
    if (h == undefined) h = 100;
    if (ellipseWidth == undefined) ellipseWidth = 30;
    if (centered == undefined) centered = false;
    if (fill == undefined) fill = true;
    if (borderColor == undefined) borderColor = 0;
    if (borderAlpha == undefined) borderAlpha = 0;
    this._myColor = color;
    this._myAlpha = alpha;
    this._fill = fill;
    this._borderAlpha = borderAlpha;
    this._borderColor = borderColor;
    this._w = w;
    this._h = h;
    this._ellipseWidth = ellipseWidth;
    this._centered = centered;
    this.update()
};
canvas.px.RoundRect.prototype = Object.create(canvas.px.Graphics.prototype);
canvas.px.RoundRect.prototype.update = function() {
    this.clear();
    this.lineStyle(1, this._borderColor, this._borderAlpha);
    if (this._fill) {
        this.beginFill(this._myColor, this._myAlpha)
    }
    this.drawRoundedRect(this._centered ? -this._w / 2 : 0, this._centered ? -this._h / 2 : 0, this._w, this._h, this._ellipseWidth);
    this.endFill()
};
canvas.px.RoundRect.prototype.setSize = function(ww, hh) {
    this._w = ww;
    this._h = hh;
    this.update()
};
canvas.px.Polygon = function(data, color, alpha) {
    canvas.px.Graphics.call(this);
    if (color == undefined) color = 0;
    if (alpha == undefined) alpha = 0;
    if (!data) return;
    this.beginFill(color, alpha);
    var len = data.length;
    for (var j = 0; j < len; j += 4) {
        this.drawRect(data[j], data[j + 1], 1, data[j + 3] - data[j + 1])
    }
    this.endFill()
};
canvas.px.Polygon.prototype = Object.create(canvas.px.Graphics.prototype);
canvas.px.PolygonData = function(data) {
    var a = [];
    var len = data.length;
    for (var j = 0; j < len; j += 2) {
        a.push(data[j], data[j + 1])
    }
    PIXI.Polygon.call(this, a)
};
canvas.px.PolygonData.prototype = Object.create(PIXI.Polygon.prototype);
canvas.px.Matrix = function(a, b, c, d, tx, ty) {
    return new PIXI.Matrix(a == undefined ? 1 : a, b == undefined ? 0 : b, c == undefined ? 0 : c, d == undefined ? 1 : d, tx, ty)
};
canvas.px.RoundProgress = function(radius, color, alpha) {
    this._progress = 0;
    Object.defineProperty(this, "progress", {
        get: function() {
            return this._progress
        },
        set: function(value) {
            this._progress = value < 0 ? 0 : value > 1 ? 1 : value;
            this.update()
        }
    });
    canvas.px.Container.call(this);
    this.color = color;
    this.alpha = alpha;
    this.radius = radius;
    this.r1 = new canvas.px.Graphics;
    this.r2 = new canvas.px.Graphics;
    this.addChild(this.r1);
    this.addChild(this.r2);
    var msk = new canvas.px.Mask(this.radius, this.radius * 2);
    this.addChild(msk);
    msk.position.set(0, -this.radius);
    this.r1.mask = msk;
    msk = new canvas.px.Mask(this.radius, this.radius * 2);
    this.addChild(msk);
    msk.position.set(-this.radius, -this.radius);
    this.r2.mask = msk;
    this.r1.clear();
    this.r1.beginFill(this.color, this.alpha);
    this.r1.arc(0, 0, this.radius, canvas.Functions.degToRad(0), canvas.Functions.degToRad(180));
    this.r1.endFill();
    this.r2.clear();
    this.r2.beginFill(this.color, this.alpha);
    this.r2.arc(0, 0, this.radius, canvas.Functions.degToRad(0), canvas.Functions.degToRad(180));
    this.r2.endFill()
};
canvas.px.RoundProgress.prototype = Object.create(canvas.px.Container.prototype);
canvas.px.RoundProgress.prototype.update = function() {
    this.r1.rotation = canvas.Functions.degToRad(90 + (this.progress >= .5 ? 180 : Math.round(360 * this.progress)));
    this.r2.rotation = canvas.Functions.degToRad(-90 + (this.progress <= .5 ? 0 : Math.round(360 * (this.progress - .5))))
};
canvas.utils.LocalStorage = function(name) {
    this.name = name;
    if (typeof Storage !== "undefined") {
        this.isSupported = true
    }
};
canvas.utils.LocalStorage.prototype.get = function(key) {
    if (this.isSupported) {
        return localStorage.getItem(this.name + "_" + key)
    } else {
        return undefined
    }
};
canvas.utils.LocalStorage.prototype.set = function(key, value) {
    if (this.isSupported) {
        localStorage.setItem(this.name + "_" + key, value)
    }
};
canvas.utils.URLRequestEvent = {
    EVENT_COMPLETE: "URLRequestEvent.EVENT_COMPLETE",
    EVENT_ERROR: "URLRequestEvent.EVENT_ERROR"
};
canvas.utils.URLRequest = function(url, method, params, responseType) {
    this.stack = [];
    this.request = new XMLHttpRequest;
    this.request.onreadystatechange = this.onLoad.bind(this);
    this.url = url || "";
    this.method = method || "GET";
    this.params = params || {};
    this.responseType = responseType || "text";
    this.request.responseType = this.responseType;
    return this
};
canvas.utils.URLRequest.prototype.load = function(url, par, info) {
    if (this.busy) {
        this.stack.push(arguments);
        return
    }
    this.busy = true;
    this.info = info;
    if (typeof url != "undefined" && url) this.url = url;
    if (typeof par != "undefined" && par) this.params = par;
    this.request.open(this.method, this.url, true);
    if (this.method == "POST") {
        this.request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        var params = this.params;
        if (typeof params == "object") {
            params = "";
            for (var key in this.params) {
                if (params) params += "&";
                params += key + "=" + this.params[key]
            }
        }
        this.request.send(params)
    } else {
        this.request.send()
    }
};
canvas.utils.URLRequest.prototype.onLoad = function() {
    if (this.request.readyState == 4) {
        if (this.request.status == 200) {
            canvas.EventManager.dispatchEvent(canvas.utils.URLRequestEvent.EVENT_COMPLETE, this)
        } else {
            canvas.Log.add(canvas.Log.ALL, "URL not found <b>" + this.url + "</b>", "#FF0000");
            canvas.EventManager.dispatchEvent(canvas.utils.URLRequestEvent.EVENT_ERROR, this)
        }
        this.busy = false;
        if (this.stack.length) {
            this.load.apply(this, this.stack.shift())
        }
    }
};
canvas.utils.URLRequest.prototype.abort = function() {
    if (this.request) {
        this.request.abort()
    }
};
canvas.utils.HintParams = function(view, light, pos) {
    this.view = view;
    this.light = light || false;
    this.pos = pos == undefined ? canvas.utils.HintPos.RIGHT_BOTTOM : pos
};
canvas.utils.HintPos = {
    RIGHT_BOTTOM: "rb",
    RIGHT_TOP: "rt",
    LEFT_BOTTOM: "lb",
    LEFT_TOP: "lt"
};
canvas.utils.HintManager = function() {
    canvas.utils.HintManager.instance = this
};
canvas.utils.HintManager.prototype.init = function(view, w, h) {
    this.view = view;
    this.resize(w, h);
    if (this.interval == undefined) this.interval = setInterval(this.handlerTimer.bind(this), 200)
};
canvas.utils.HintManager.prototype.resize = function(w, h) {
    this.width = w;
    this.height = h
};
canvas.utils.HintManager.prototype.add = function(target, params) {
    target.hintParams = params;
    target.mouseover = this.handlerOver.bind(this);
    target.mouseout = this.handlerOut.bind(this)
};
canvas.utils.HintManager.prototype.remove = function(target) {
    this.hide(target);
    target.mouseover = null;
    target.mouseout = null
};
canvas.utils.HintManager.prototype.show = function(target) {
    if (!target) return;
    if (this.currentTarget != null) {
        this.hide(this.currentTarget)
    }
    var hintParams = target.hintParams;
    if (hintParams && hintParams.view) {
        this.view.addChild(hintParams.view);
        this.currentTarget = target;
        this.view.mousemove = this.handlerMove.bind(this);
        if (hintParams.light) {
            this.currentTarget.filters = [canvas.Functions.getBrightness()]
        }
    }
};
canvas.utils.HintManager.prototype.hideAndRemove = function(target) {
    if (target == undefined) target = this.currentTarget;
    this.remove(target);
    this.hide(target)
};
canvas.utils.HintManager.prototype.hide = function(target) {
    if (target == undefined) target = this.currentTarget;
    if (!target) return;
    if (this.currentTarget != target) return;
    var hintParams = target.hintParams;
    if (hintParams) {
        if (hintParams.light) {
            this.currentTarget.filters = []
        }
        this.view.removeChildren();
        this.currentTarget = null;
        this.view.mousemove = null
    }
};
canvas.utils.HintManager.prototype.handlerOver = function(mouseData) {
    this.show(mouseData.currentTarget);
    this.handlerMove(mouseData)
};
canvas.utils.HintManager.prototype.handlerOut = function(mouseData) {
    this.hide(mouseData.currentTarget)
};
canvas.utils.HintManager.prototype.handlerMove = function(mouseData) {
    if (!this.currentTarget.worldVisible) {
        this.hide(this.currentTarget);
        return
    }
    if (this.currentTarget) {
        var targetX, targetY;
        switch (this.currentTarget.hintParams.pos) {
            case canvas.utils.HintPos.RIGHT_BOTTOM:
                targetX = 15 + mouseData.data.global.x;
                targetY = 20 + mouseData.data.global.y;
                break;
            case canvas.utils.HintPos.RIGHT_TOP:
                targetX = 15 + mouseData.data.global.x;
                targetY = mouseData.data.global.y - this.currentTarget.hintParams.view.height - 2;
                break;
            case canvas.utils.HintPos.LEFT_BOTTOM:
                targetX = mouseData.data.global.x - this.currentTarget.hintParams.view.width - 2;
                targetY = 20 + mouseData.data.global.y;
                break;
            case canvas.utils.HintPos.LEFT_TOP:
                targetX = mouseData.data.global.x - this.currentTarget.hintParams.view.width - 2;
                targetY = mouseData.data.global.y - this.currentTarget.hintParams.view.height - 2;
                break
        }
        var diff = targetX + this.currentTarget.hintParams.view.width - this.width;
        if (diff > 0) {
            targetX -= diff
        }
        diff = targetY + this.currentTarget.hintParams.view.height - this.height;
        if (diff > 0) {
            targetY -= diff
        }
        if (targetX < 0) targetX = 0;
        if (targetY < 0) targetY = 0;
        this.currentTarget.hintParams.view.position.set(Math.round(targetX), Math.round(targetY))
    }
};
canvas.utils.HintManager.prototype.handlerTimer = function() {
    if (this.currentTarget && !this.currentTarget.worldVisible) {
        this.hide(this.currentTarget)
    }
};
canvas.utils.ServerTime = function(serverTime, timezoneOffset) {
    this._serverDiff = 0;
    this._timezoneOffset = 180;
    if (serverTime != undefined) this.init(serverTime, timezoneOffset)
};
canvas.utils.ServerTime.prototype.init = function(serverTime, timezoneOffset) {
    if (timezoneOffset == undefined) timezoneOffset = 180;
    this._serverDiff = Math.round(Date.now() * .001) - serverTime;
    this._timezoneOffset = timezoneOffset
};
canvas.utils.ServerTime.prototype.getTime = function() {
    return Math.round(Date.now() * .001) - this._serverDiff
};
canvas.utils.ServerTime.prototype.getDate = function() {
    var date = new Date;
    date.setTime(date.getTime() - this._serverDiff * 1e3 + (this._timezoneOffset + date.getTimezoneOffset()) * 6e4);
    return date
};
canvas.utils.WindowsManager = function(wall) {
    Object.defineProperty(this, "haveActiveWindows", {
        get: function() {
            return this.activeWindows.length > 0
        }
    });
    canvas.utils.WindowsManager.instance = this;
    this.activeWindows = [];
    this.container = null;
    this.dragingWindow = null;
    this.bottomContainer = null;
    this.w = 0;
    this.h = 0;
    this.dx = 0;
    this.dy = 0;
    this.currentWindow = null;
    if (wall) this.wall = wall;
    canvas.EventManager.addEventListener(canvas.px.WindowEvent.EVENT_CLOSE, null, this.closeHandler, this);
    canvas.EventManager.addEventListener(canvas.px.WindowEvent.EVENT_DRAG, null, this.dragHandler, this)
};
canvas.utils.WindowsManager.prototype.init = function(container, w, h, bottomContainer) {
    this.bottomContainer = bottomContainer;
    this.container = container;
    this.w = w;
    this.h = h;
    this.resizeWindows()
};
canvas.utils.WindowsManager.prototype.openWindow = function(window, applyFilter, blockBack, centered, applyWall) {
    if (applyFilter == undefined) applyFilter = true;
    if (blockBack == undefined) blockBack = true;
    if (centered == undefined) centered = true;
    if (applyWall == undefined) applyWall = false;
    window.__centered = centered;
    if (this.activeWindows.indexOf(window) < 0) {
        this.activeWindows.push(window)
    }
    if (window.parent != this.container) {
        this.container.addChild(window);
        this.resizeWindow(window)
    }
    if (this.bottomContainer != null) {
        if (blockBack) {
            this.bottomContainer.interactive = false;
            this.bottomContainer.interactiveChildren = false
        }
        if (applyFilter) {
            if (this.wall) {
                this.wall.active = true
            } else {
                this.bottomContainer.filters = [canvas.Functions.getGreyScale(0.3)]
            }
        }
        if (applyWall) {
            window.__wall = new canvas.px.Mask(this.bottomContainer.width, this.bottomContainer.height, 0, .7);
            this.bottomContainer.addChild(window.__wall)
        }
        canvas.EventManager.dispatchEvent(canvas.px.WindowEvent.EVENT_OPEN, this, window)
    }
};
canvas.utils.WindowsManager.prototype.resizeWindows = function() {
    var window;
    var len = this.activeWindows.length;
    for (var i = 0; i < len; i++) {
        window = this.activeWindows[i];
        this.resizeWindow(window)
    }
};
canvas.utils.WindowsManager.prototype.resizeWindow = function(window) {
    if (window.__centered) {
        window.x = Math.max(0, Math.round((this.w - window.width) * .5));
        window.y = Math.max(0, Math.round((this.h - window.height) * .5))
    }
};
canvas.utils.WindowsManager.prototype.closeWindow = function(window) {
    var i = this.activeWindows.indexOf(window);
    if (i >= 0) {
        this.activeWindows.splice(i, 1)
    } else {
        return
    }
    if (window.parent == this.container) this.container.removeChild(window);
    if (window.__wall && this.bottomContainer) {
        if (window.__wall.parent == this.bottomContainer) {
            this.bottomContainer.removeChild(window.__wall)
        }
        window.__wall.destroy();
        window.__wall = null
    }
    if (this.bottomContainer != null && this.activeWindows.length == 0) {
        this.bottomContainer.interactive = true;
        this.bottomContainer.interactiveChildren = true;
        this.bottomContainer.filters = [];
        if (this.wall) this.wall.active = false
    }
    canvas.EventManager.dispatchEvent(canvas.px.WindowEvent.EVENT_CLOSE, this, window)
};
canvas.utils.WindowsManager.prototype.windowIsActive = function(window) {
    return this.activeWindows.indexOf(window) >= 0
};
canvas.utils.WindowsManager.prototype.closeAllWindows = function() {
    var window;
    var a = [];
    var len = this.activeWindows.length;
    var i;
    for (i = 0; i < len; i++) {
        window = this.activeWindows[i];
        a.push(window)
    }
    len = a.length;
    for (i = 0; i < len; i++) {
        window = a[i];
        this.closeWindow(window)
    }
};
canvas.utils.WindowsManager.prototype.closeHandler = function(e) {
    if (this.windowIsActive(e.target)) {
        this.currentWindow = e.target;
        this.closeWindow(e.target)
    }
};
canvas.utils.WindowsManager.prototype.dragHandler = function(e) {
    this.dragingWindow = e.target;
    if (this.dragingWindow != null && this.windowIsActive(this.dragingWindow)) {
        this.container.setChildIndex(this.dragingWindow, this.container.numChildren - 1);
        this.dragingWindow.startDrag(new canvas.px.Rectangle(0, 0, this.w - this.dragingWindow.width, this.h - this.dragingWindow.height), new canvas.px.Point(e.params.data.global.x + this.dx, e.params.data.global.y + this.dy));
        canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.upHandler, this)
    }
};
canvas.utils.WindowsManager.prototype.upHandler = function(e) {
    if (this.container != null && this.dragingWindow != null) {
        canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.upHandler, this);
        this.dragingWindow.stopDrag();
        this.dragingWindow = null
    }
};
canvas.utils.ABCAbout = {
    VERSION: 1,
    REQUEST_URL_DWAR: "entry_point.php?object=common&action=action&json_mode_on=1"
};
canvas.utils.ABCEvent = {};
canvas.utils.ABCPointTypes = {
    END: 0,
    MOVE: 1,
    CLICK: 2,
    LEAVE: 3,
    TAB: 4,
    SPACE: 5,
    KEY: 6,
    MOUSE_DOWN: 7,
    MOUSE_UP: 8,
    toString: function(status) {
        switch (status) {
            case this.START:
                return "start";
            case this.END:
                return "end";
            case this.MOVE:
                return "move";
            case this.CLICK:
                return "click";
            case this.LEAVE:
                return "leave";
            case this.TAB:
                return "tab";
            case this.SPACE:
                return "space";
            case this.KEY:
                return "key";
            case this.MOUSE_DOWN:
                return "down";
            case this.MOUSE_UP:
                return "up"
        }
        return "unknow#" + status
    }
};
canvas.utils.ABCMouseEventTypes = {
    DOWN: 1,
    UP: 2,
    CLICK: 3,
    DCLICK: 4,
    toString: function(event) {
        switch (event) {
            case this.DOWN:
                return "down";
            case this.UP:
                return "up";
            case this.CLICK:
                return "click";
            case this.DCLICK:
                return "double-click"
        }
        return "none"
    }
};
canvas.utils.ABCKeyboardEventTypes = {
    DOWN: 1,
    UP: 2
};
canvas.utils.ABCPointData = function(parent, status, x, y, time) {
    this.x = x;
    this.y = y;
    this.frame = 0;
    this.endType = -1;
    this.parent = parent;
    this.status = status;
    this.time = time;
    this.uxIndex = canvas.utils.ABCPointData.uxIndexCount++
};
canvas.utils.ABCPointData.prototype.toObject = function() {
    if (this.object) return this.object;
    var object = {};
    object["s"] = this.status;
    object["x"] = Math.round(this.x * 1e3) / 1e3;
    object["y"] = Math.round(this.y * 1e3) / 1e3;
    object["t"] = this.time;
    if (this.endType != -1) {
        object["e"] = this.endType
    }
    if (this.keyInfo) {
        object["k"] = this.keyInfo.toObject()
    }
    if (this.sendInfo) {
        object["i"] = this.sendInfo
    }
    this.object = object;
    return object
};
canvas.utils.ABCPointData.uxIndexCount = 1;
canvas.utils.ABCMouseData = function(event, time, x, y) {
    this.event = event;
    this.time = time;
    this.x = x;
    this.y = y
};
canvas.utils.ABCMouseData.prototype.toObject = function() {
    if (this.object) return this.object;
    var object = new Object;
    object["e"] = this.event;
    object["t"] = this.time;
    object["x"] = this.x;
    object["y"] = this.y;
    this.object = object;
    return object
};
canvas.utils.ABCKeyboardData = function(event, code, time) {
    this.event = event;
    this.code = code;
    this.time = time
};
canvas.utils.ABCKeyboardData.prototype.toObject = function() {
    if (this.object) return this.object;
    var object = new Object;
    object["e"] = this.event;
    object["c"] = this.code;
    object["t"] = this.time;
    this.object = object;
    return object
};
canvas.utils.ABCKeyInfo = function(keyCode, deltaTime) {
    this.keyCode = keyCode;
    this.deltaTime = deltaTime
};
canvas.utils.ABCKeyInfo.prototype.toObject = function() {
    var object = {};
    object["c"] = this.keyCode;
    object["t"] = this.deltaTime;
    return object
};
canvas.utils.ABCParameters = function(parameters) {
    this.userId = parameters["user_id"];
    if (parameters["abc_cfcs"]) {
        this.countForClearSession = parseInt(parameters["abc_cfcs"])
    } else {
        this.countForClearSession = 20
    }
    this.keyboardLog = parameters["abc_keyboard"] == "1";
    this.mouseLog = parameters["abc_mouse"] == "1";
    this.maxPointsCount = parameters["abc_max_points"] ? parseInt(parameters["abc_max_points"]) : 0
};
canvas.utils.ABController = {
    init: function(app, fromType) {
        this.pointTime = 0;
        Object.defineProperty(this, "diffPointTime", {
            get: function() {
                if (this.pointTime != 0) {
                    var diff = Date.now() - this.pointTime;
                    this.pointTime = Date.now();
                    return diff
                }
                this.pointTime = Date.now();
                return 0
            }
        });
        this.keyboardTime = 0;
        Object.defineProperty(this, "diffKeyboardTime", {
            get: function() {
                if (this.keyboardTime != 0) {
                    var diff = Date.now() - this.keyboardTime;
                    this.keyboardTime = Date.now();
                    return diff
                }
                this.keyboardTime = Date.now();
                return 0
            }
        });
        this.mouseTime = 0;
        Object.defineProperty(this, "diffMouseTime", {
            get: function() {
                if (this.mouseTime != 0) {
                    var diff = Date.now() - this.mouseTime;
                    this.mouseTime = Date.now();
                    return diff
                }
                this.mouseTime = Date.now();
                return 0
            }
        });
        this.stage = app.stage;
        this.fromType = fromType;
        this.fromData = null;
        this.alc = null;
        this.tstjs = -1;
        this.tstcl = -1;
        this.app = app;
        this.isStageMove = false;
        this.stageMoveIsListening = false;
        this.clickTime = Date.now();
        this.leaveTime = Date.now();
        this.keyDowns = {};
        this._parameters = new canvas.utils.ABCParameters(app.par);
        this.reset(true);
        var res = is_touch_device();
        this.tstjs = parseInt(res) ? 1 : 0;
        this.stage.tstcl = this.tstclCallback.bind(this);
        this.stage.mousemove = this.moveHandler.bind(this);
        this.stage.click = this.clickHandler.bind(this);
        this.stage.mousedown = this.downHandler.bind(this);
        this.stage.mouseup = this.upHandler.bind(this);
        document.addEventListener("keydown", this.keyDownHandler.bind(this));
        document.addEventListener("keyup", this.keyUpHandler.bind(this));
        document.addEventListener("mouseout", this.leaveHandler.bind(this))
    },
    tstclCallback: function(tst) {
        this.tstcl = tst ? 1 : 0
    },
    reset: function(isStart) {
        this.focusedObject = null;
        if (isStart || this._points.length > this._parameters.countForClearSession) {
            this.resetIndex = 0;
            this._points = new Array;
            this._keyboardEventsList = new Array;
            this._mouseEventsList = new Array;
            this.pointTime = 0;
            this.keyboardTime = 0;
            this.mouseTime = 0
        } else {
            this.resetIndex = this._points.length
        }
    },
    addPoint: function(point) {
        this._points.push(point);
        if (this._parameters.maxPointsCount > 0 && this._points.length > this._parameters.maxPointsCount) {
            this._points.shift()
        }
    },
    addKey: function(key) {
        this._keyboardEventsList.push(key)
    },
    addMouse: function(mouse) {
        this._mouseEventsList.push(mouse)
    },
    getMousePosition: function() {
        return this.app.app.renderer.plugins.interaction.mouse.global
    },
    moveHandler: function(e) {
        var pos = this.getMousePosition();
        this.addPoint(new canvas.utils.ABCPointData(null, canvas.utils.ABCPointTypes.MOVE, pos.x, pos.y, this.diffPointTime));
        if (this.stageMoveIsListening) {
            this.isStageMove = true
        }
    },
    clickHandler: function(e) {
        if (this.isStageMove) return;
        if (this._parameters.mouseLog) {
            var pos = this.getMousePosition();
            this.addMouse(new canvas.utils.ABCMouseData(canvas.utils.ABCMouseEventTypes.CLICK, this.diffMouseTime, pos.x, pos.y))
        }
        if (Date.now() - this.clickTime < 500) {
            this.doubleClickHandler()
        }
        this.clickTime = Date.now()
    },
    downHandler: function(e) {
        var pos = this.getMousePosition();
        this.addPoint(new canvas.utils.ABCPointData(null, canvas.utils.ABCPointTypes.MOUSE_DOWN, pos.x, pos.y, this.diffPointTime));
        this.stageMoveIsListening = true;
        this.isStageMove = false;
        if (this._parameters.mouseLog) {
            this.addMouse(new canvas.utils.ABCMouseData(canvas.utils.ABCMouseEventTypes.DOWN, this.diffMouseTime, pos.x, pos.y))
        }
    },
    upHandler: function(e) {
        if (this.isStageMove) return;
        var pos = this.getMousePosition();
        this.addPoint(new canvas.utils.ABCPointData(null, canvas.utils.ABCPointTypes.MOUSE_UP, pos.x, pos.y, this.diffPointTime));
        if (this._parameters.mouseLog) {
            this.addMouse(new canvas.utils.ABCMouseData(canvas.utils.ABCMouseEventTypes.UP, this.diffMouseTime, pos.x, pos.y))
        }
    },
    doubleClickHandler: function() {
        if (this._parameters.mouseLog) {
            var pos = this.getMousePosition();
            this.addMouse(new canvas.utils.ABCMouseData(canvas.utils.ABCMouseEventTypes.DCLICK, this.diffMouseTime, pos.x, pos.y))
        }
    },
    leaveHandler: function(e) {
        if (Date.now() - this.leaveTime < 500) return;
        var point = this._points[this._points.length - 1];
        if (point) {
            this.addPoint(new canvas.utils.ABCPointData(null, canvas.utils.ABCPointTypes.LEAVE, point.x, point.y, this.diffPointTime))
        }
        this.leaveTime = Date.now()
    },
    keyDownHandler: function(e) {
        this.keyDowns[e.code] = Date.now();
        if (this._parameters.keyboardLog) {
            this.addKey(new canvas.utils.ABCKeyboardData(canvas.utils.ABCKeyboardEventTypes.DOWN, e.code, this.diffKeyboardTime))
        }
    },
    keyUpHandler: function(e) {
        var downTime = this.keyDowns[e.code];
        var deltaTime = Date.now() - downTime;
        var keyInfo = new canvas.utils.ABCKeyInfo(e.code, deltaTime);
        var point = new canvas.utils.ABCPointData(null, canvas.utils.ABCPointTypes.KEY, 0, 0, this.diffPointTime);
        point.keyInfo = keyInfo;
        this.addPoint(point);
        if (this._parameters.keyboardLog) {
            this.addKey(new canvas.utils.ABCKeyboardData(canvas.utils.ABCKeyboardEventTypes.UP, e.code, this.diffKeyboardTime))
        }
    },
    toString: function(additionalInfo) {
        var pos = this.getMousePosition();
        var endPoint = new canvas.utils.ABCPointData(null, canvas.utils.ABCPointTypes.END, pos.x, pos.y, this.diffPointTime);
        endPoint.endType = additionalInfo && additionalInfo["et"] ? additionalInfo["et"] : -1;
        this._points.push(endPoint);
        var i;
        var sendPoints = [];
        var prev;
        var sum = 0;
        var len;
        var lenMin;
        var lenMax;
        var dx;
        var dy;
        var point;
        for (i = 0; i < this._points.length; i++) {
            point = this._points[i];
            sendPoints.push(point.toObject());
            if (i > this.resetIndex) {
                dx = prev.x - point.x;
                dy = prev.y - point.y;
                len = Math.sqrt(dx * dx + dy * dy);
                lenMin = lenMin != undefined ? Math.min(lenMin, len) : len;
                lenMax = lenMax != undefined ? Math.max(lenMax, len) : len;
                sum += len
            }
            prev = point
        }
        var sendKeyboards = [];
        var keyboard;
        for (i = 0; i < this._keyboardEventsList.length; i++) {
            keyboard = this._keyboardEventsList[i];
            sendKeyboards.push(keyboard.toObject())
        }
        var sendMouse = [];
        var mouse;
        for (i = 0; i < this._mouseEventsList.length; i++) {
            mouse = this._mouseEventsList[i];
            sendMouse.push(mouse.toObject())
        }
        var lengthInfo = {};
        lengthInfo["sum"] = sum;
        lengthInfo["count"] = sendPoints.length - 1 - this.resetIndex;
        lengthInfo["min"] = lenMin;
        lengthInfo["max"] = lenMax;
        var info = {};
        info["from"] = this.fromType;
        info["fromData"] = this.fromData;
        info["length"] = lengthInfo;
        info["width"] = this.app.width;
        info["height"] = this.app.height;
        if (window.get_client_width) info["ex_width"] = get_client_width();
        if (window.get_client_height) info["ex_height"] = get_client_height();
        info["ai"] = additionalInfo;
        info["cap"] = navigator.userAgent;
        info["tst"] = {};
        info["tst"]["js"] = this.tstjs;
        info["tst"]["cl"] = this.tstcl;
        info["st"] = this.resetIndex;
        info["id"] = this._parameters.userId;
        info["fps"] = 30;
        info["v"] = canvas.utils.ABCAbout.VERSION;
        var data = {};
        data["points"] = sendPoints;
        data["kb"] = sendKeyboards;
        data["ms"] = sendMouse;
        data["info"] = info;
        var string = JSON.stringify(data);
        string = btoa(string);
        this.reset(false);
        return string
    },
    sendRequest: function(url, additionalInfo) {
        var par = {};
        par["m"] = this.toString(additionalInfo);
        var req = new canvas.utils.URLRequest(url, "POST", par);
        req.load();
        return req
    }
};
canvas.utils.RGB = function(color) {
    if (color == undefined) color = 0;
    this.color = color;
    this.a = color >>> 24 & 255;
    this.r = color >>> 16 & 255;
    this.g = color >>> 8 & 255;
    this.b = color & 255
};
canvas.utils.RGB.prototype.toHex = function() {
    return this.a << 24 | this.r << 16 | this.g << 8 | this.b
};
canvas.utils.TimeLog = {
    start: Date.now(),
    last: Date.now(),
    log: function(comment) {
        if (comment == undefined) comment = "";
        console.log("TimeLog (" + comment + "): " + (Date.now() - this.last) + "/" + (Date.now() - this.start) + " ms.");
        this.last = Date.now()
    }
};
canvas.utils.ServerRandomizer = function() {
    this.LCG_A = 0;
    this.LCG_C = 0;
    this.LCG_M = 0
};
canvas.utils.ServerRandomizer.prototype.init = function(seed, a, c, m) {
    this.seed = seed;
    this.LCG_A = a;
    this.LCG_C = c;
    this.LCG_M = m
};
canvas.utils.ServerRandomizer.prototype.lcg = function(x, a, c, m) {
    return (a * x + c) % m
};
canvas.utils.ServerRandomizer.prototype.lcgRand = function(min, max) {
    this.seed = this.lcg(this.seed, this.LCG_A, this.LCG_C, this.LCG_M);
    return Math.round(Math.abs(this.seed) / this.LCG_M * (max - min) + min)
};
canvas.utils.ServerRandomizer.prototype.lcgShuffle = function(arr) {
    var result = new Array,
        len;
    while ((len = arr.length) > 0) {
        result.push(arr.splice(this.lcgRand(0, len - 1), 1)[0])
    }
    return result
};
var WSProxy = function(options) {
    this.options = options || {};
    this.options.onConnect = this.options.onConnect || function() {
        console.warn("onConnect event", arguments)
    };
    this.options.onMessage = this.options.onMessage || function() {
        console.warn("onMessage event", arguments)
    };
    if(typeof this.options.onError !== "function") {
        this.options.onError = function() {
            console.warn("onError event", arguments)
        }
    }
    this.onOpenWs = this.onOpenWs.bind(this);
    this.onMessageWs = this.onMessageWs.bind(this);
    this.curPackSize = 0;
    this.curPack = ""
};
WSProxy.prototype.log = function() {
    if (this.options.logEnabled) {
        console.log.apply(console, arguments)
    }
};
WSProxy.prototype._connect = function() {
    var ws = this.ws = new WebSocket("ws" + (document.URL.substr(0, 5) == "https" ? "s" : "") + "://" + this.connectOptions.ws.host);
    ws.onopen = this.onOpenWs;
    ws.onmessage = this.onMessageWs;
    var self = this;
    ws.onclose = function(event) {
        console.warn("WSProxy:onclose", event);
        self.reconnect()
    };
    ws.onerror = function(event) {
        console.error("WSProxy:onerror", event);
        try{self.options.onError();}catch(e) { console.log(e); }
        self.reconnect()
    }
};
WSProxy.prototype._parseMessage = function(message) {
    if (this.storedMessage) {
        message = this.storedMessage + message;
        this.storedMessage = ""
    }
    var a = message.split("\0");
    var i, len = a.length;
    for (i = 0; i < len; i++) {
        if (a[i]) {
            if (a[i + 1] == undefined) {
                this.storedMessage = a[i]
            } else {
                this.options.onMessage(a[i])
            }
        }
    }
};
WSProxy.prototype.onOpenWs = function() {
    this.log("WSProxy:onOpenWs");
    this.ws.send(JSON.stringify({
        event: "connect",
        host: this.connectOptions.fs.host,
        port: this.connectOptions.fs.port
    }))
};
WSProxy.prototype.onMessageWs = function(event) {
    var message;
    try {
        message = JSON.parse(event.data)
    } catch (e) {
        console.error("parse json data", event.data, e);
        message = {}
    }
    switch (message.event) {
        case "connected":
            this.connected = true;
            this._tryCount = 0;
            this.sourcePool = "";
            this.options.onConnect();
            break;
        case "message":
            this._parseMessage(message.data);
            break
    }
};
WSProxy.prototype.connect = function(options) {
    this.log("WSProxy:connect", options);
    this.connectOptions = options;
    if (this.ws) {
        console.error("Connection already exists. Call destroy method");
        return
    }
    this._connect()
};
WSProxy.prototype.reconnect = function() {
    if (!this._tryCount) this._tryCount = 0;
    this._tryCount++;
    this.log("WSProxy:reconnectWs", this._tryCount);
    this.destroy();
    var self = this;
    this._reconnectTimeout = setTimeout(function() {
        self._connect()
    }, Math.pow(2, this._tryCount) * 1e3)
};
WSProxy.prototype.destroy = function() {
    this.log("WSProxy:destroy");
    this.connected = false;
    if (this.ws) {
        this.ws.onopen = null;
        this.ws.onmessage = null;
        this.ws.onclose = null;
        this.ws.onerror = null;
        this.ws.close();
        this.ws = null
    }
    clearTimeout(this._reconnectTimeout)
};
WSProxy.prototype.send = function(data) {
    if (!this.connected) {
        console.error("WSProxy is not connected");
        return
    }
    this.ws.send(JSON.stringify({
        event: "message",
        data: data
    }))
};
canvas.ui.Event = {
    EVENT_CHANGE: "canvas.ui.Event.CHANGE"
};
canvas.ui.Component = function() {
    this._enabled = true;
    this.interactive = true;
    canvas.px.Container.call(this)
};
canvas.ui.Component.prototype = Object.create(canvas.px.Container.prototype);
canvas.ui.Component.prototype.setSize = function(width, height) {
    if (width > 0) this._width = width;
    if (height > 0) this._height = height;
    this.update()
};
canvas.ui.Component.prototype.update = function() {};
canvas.ui.Component.prototype.destroy = function() {
    canvas.px.Container.prototype.destroy.apply(this, [{
        children: true
    }])
};
canvas.ui.ButtonEvent = {
    EVENT_CLICK: "ButtonEvent.CLICK",
    EVENT_SELECT: "ButtonEvent.SELECT",
    EVENT_DOWN: "ButtonEvent.DOWN",
    EVENT_OVER: "ButtonEvent.OVER",
    EVENT_OUT: "ButtonEvent.OUT"
};
canvas.ui.Button = function(baseTexture, overTexture, downTexture, disabledTexture, sliceArray, checkSprite, radioArray) {
    canvas.ui.Component.call(this);
    Object.defineProperty(this, "enabled", {
        get: function() {
            return this._enabled
        },
        set: function(value) {
            this._enabled = value;
            if (this.disabledTexture) {
                this.sprite.texture = value ? this.baseTexture : this.disabledTexture
            } else {
                if (value) {
                    this.filters = null
                } else {
                    this.filters = [canvas.Functions.getBlackAndWhite()]
                }
            }
            this.buttonMode = this.sprite.interactive = value;
            this.update()
        },
        configurable: true
    });
    Object.defineProperty(this, "checked", {
        get: function() {
            return this._checked
        },
        set: function(value) {
            this._checked = value;
            if (this.checkSprite) {
                if (this._checked) {
                    if (this.checkSprite.parent != this) this.addChild(this.checkSprite)
                } else {
                    if (this.checkSprite.parent == this) this.removeChild(this.checkSprite)
                }
            }
        }
    });
    canvas.ui.Component.call(this);
    this.baseTexture = baseTexture;
    this.overTexture = overTexture;
    this.downTexture = downTexture;
    this.disabledTexture = disabledTexture;
    this.checkSprite = checkSprite;
    this.radioArray = radioArray;
    if (sliceArray) {
        this.sprite = new canvas.px.SlicedSprite(baseTexture, sliceArray[0], sliceArray[1], sliceArray[2], sliceArray[3])
    } else {
        this.sprite = new canvas.px.Sprite(this.baseTexture)
    }
    this.sprite.interactive = true;
    this.buttonMode = true;
    this.addChild(this.sprite);
    this.sprite.mouseover = this.handlerOver.bind(this);
    this.sprite.mouseout = this.handlerOut.bind(this);
    this.sprite.mousedown = this.handlerDown.bind(this);
    this.sprite.click = this.handlerUp.bind(this)
};
canvas.ui.Button.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.Button.prototype.setSize = function(width, height) {
    canvas.ui.Component.prototype.setSize.apply(this, [width, height]);
    if (width > 0) this.sprite.width = width;
    if (height > 0) this.sprite.height = height
};
canvas.ui.Button.prototype.handlerOver = function(mouseData) {
    if (this.overTexture) this.sprite.texture = this.overTexture;
    canvas.EventManager.dispatchEvent(canvas.ui.ButtonEvent.EVENT_OVER, this, mouseData)
};
canvas.ui.Button.prototype.handlerOut = function(mouseData) {
    if (this.baseTexture) this.sprite.texture = this.baseTexture;
    canvas.EventManager.dispatchEvent(canvas.ui.ButtonEvent.EVENT_OUT, this, mouseData)
};
canvas.ui.Button.prototype.handlerDown = function(mouseData) {
    if (this.downTexture) this.sprite.texture = this.downTexture;
    canvas.EventManager.dispatchEvent(canvas.ui.ButtonEvent.EVENT_DOWN, this, mouseData)
};
canvas.ui.Button.prototype.handlerUp = function(mouseData) {
    if (!canvas.Config.isMobile && this.overTexture) {
        this.sprite.texture = this.overTexture
    } else {
        this.sprite.texture = this.baseTexture
    }
    if (this.checkSprite) {
        if (this.radioArray) {
            this.select()
        } else {
            this.checked = !this.checked
        }
    }
    canvas.EventManager.dispatchEvent(canvas.ui.ButtonEvent.EVENT_CLICK, this, mouseData)
};
canvas.ui.Button.prototype.select = function() {
    if (this.radioArray) {
        var len = this.radioArray.length;
        for (var i = 0; i < len; i++) {
            this.radioArray[i].checked = this.radioArray[i] == this
        }
        canvas.EventManager.dispatchEvent(canvas.ui.ButtonEvent.EVENT_SELECT, this)
    }
};
canvas.ui.SimpleButton = function(texture, sliceArray, checkSprite, radioArray) {
    canvas.ui.Button.call(this, texture, null, null, null, sliceArray, checkSprite, radioArray);
    Object.defineProperty(this, "enabled", {
        get: function() {
            return this._enabled
        },
        set: function(value) {
            this._enabled = value;
            this.buttonMode = this.sprite.interactive = value;
            if (value) {
                this.filters = null
            } else {
                this.filters = [canvas.Functions.getBlackAndWhite()]
            }
        },
        configurable: true
    })
};
canvas.ui.SimpleButton.prototype = Object.create(canvas.ui.Button.prototype);
canvas.ui.SimpleButton.prototype.handlerOver = function(mouseData) {
    this.filters = [canvas.Functions.getBrightness(1.2)];
    canvas.ui.Button.prototype.handlerOver.apply(this, mouseData)
};
canvas.ui.SimpleButton.prototype.handlerOut = function(mouseData) {
    this.filters = null;
    canvas.ui.Button.prototype.handlerOut.apply(this, mouseData)
};
canvas.ui.SimpleButton.prototype.handlerDown = function(mouseData) {
    this.filters = [canvas.Functions.getSaturation(-.2)];
    canvas.ui.Button.prototype.handlerDown.apply(this, mouseData)
};
canvas.ui.SimpleButton.prototype.handlerUp = function(mouseData) {
    if (canvas.Config.isMobile) {
        this.filters = null
    } else {
        this.filters = [canvas.Functions.getBrightness(1.2)]
    }
    canvas.ui.Button.prototype.handlerUp.apply(this, mouseData)
};
canvas.ui.Text = function(font, color, width, height, hAlign, vAlign, backColor, backAlpha, masking) {
    this._color = 0;
    Object.defineProperty(this, "color", {
        get: function() {
            return this._color
        },
        set: function(value) {
            this._color = value;
            this.field.tint = this._color
        }
    });
    this._font = "";
    Object.defineProperty(this, "font", {
        get: function() {
            return this._font
        },
        set: function(value) {
            if (this._font === value) return;
            this._font = value;
            this.field.font = this._font
        }
    });
    this._hAlign = "left";
    Object.defineProperty(this, "hAlign", {
        get: function() {
            return this._hAlign
        },
        set: function(value) {
            this._hAlign = value;
            this.field.align = this._hAlign;
            this.update()
        }
    });
    this._vAlign = "top";
    Object.defineProperty(this, "vAlign", {
        get: function() {
            return this._vAlign
        },
        set: function(value) {
            this._vAlign = value;
            this.update()
        }
    });
    this._text = "";
    Object.defineProperty(this, "text", {
        get: function() {
            return this._text
        },
        set: function(value) {
            this._text = value;
            this.field.text = this._text;
            this.update()
        }
    });
    this._border = false;
    Object.defineProperty(this, "border", {
        get: function() {
            return this._border
        },
        set: function(value) {
            this._border = value;
            if (this._border && !this.borderImage) {
                this.borderImage = new canvas.px.Graphics;
                this.addChild(this.borderImage)
            }
            this.update()
        }
    });
    this._backColor = 0;
    Object.defineProperty(this, "backColor", {
        get: function() {
            return this._backColor
        },
        set: function(value) {
            this._backColor = value;
            if (!this.back) {
                this.createBack()
            }
            this.update()
        }
    });
    this._backAlpha = 0;
    Object.defineProperty(this, "backAlpha", {
        get: function() {
            return this._backAlpha
        },
        set: function(value) {
            this._backAlpha = value;
            if (!this.back) {
                this.createBack()
            }
            this.update()
        }
    });
    Object.defineProperty(this, "textWidth", {
        get: function() {
            return Math.round(this.field.textWidth)
        },
        set: function(value) {}
    });
    Object.defineProperty(this, "textHeight", {
        get: function() {
            return Math.round(this.field.textHeight)
        },
        set: function(value) {}
    });
    canvas.ui.Component.call(this);
    this._font = font;
    if (color != undefined) this._color = color;
    if (width != undefined) this._width = width;
    else this._width = 100;
    if (height != undefined) this._height = height;
    else this._height = 100;
    if (vAlign != undefined) this._vAlign = vAlign;
    if (hAlign != undefined) this._hAlign = hAlign;
    if (backColor != undefined) {
        this.createBack();
        this._backColor = backColor
    }
    if (backAlpha) this._backAlpha = backAlpha;
    if (masking != undefined && masking) {
        this.createMask()
    }
    this.field = new canvas.px.BitmapText("", {
        font: font,
        align: this._hAlign
    });
    if (color >= 0) this.field.tint = color;
    this.addChild(this.field);
    this.interactive = false
};
canvas.ui.Text.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.Text.prototype.update = function() {
    this.field.maxWidth = this._width;
    var textHeight = this.field.textHeight;
    if (this._height < textHeight) textHeight = this._height;
    switch (this.hAlign) {
        case "left":
            this.field.x = 0;
            break;
        case "center":
            this.field.x = Math.round((this._width - this.field.textWidth) * .5);
            break;
        case "right":
            this.field.x = Math.round(this._width - this.field.textWidth);
            break
    }
    switch (this.vAlign) {
        case "top":
            this.field.y = 0;
            break;
        case "middle":
            this.field.y = Math.round((this._height - textHeight) * .5);
            break;
        case "bottom":
            this.field.y = Math.round(this._height - textHeight);
            break
    }
    if (this.border) {
        this.borderImage.clear();
        this.borderImage.beginFill(0, 0);
        this.borderImage.lineStyle(2, 16711680, 1);
        this.borderImage.drawRect(0, 0, this._width, this._height);
        this.borderImage.endFill()
    }
    if (this.back) {
        this.back.clear();
        this.back.beginFill(this.backColor, this.backAlpha);
        this.back.drawRect(0, 0, this._width, this._height);
        this.back.endFill()
    }
    if (this.msk) {
        this.msk.clear();
        this.msk.beginFill(0, 1);
        this.msk.drawRect(0, 0, this._width, this._height);
        this.msk.endFill()
    }
};
canvas.ui.Text.prototype.createBack = function() {
    this.back = new canvas.px.Graphics;
    this.addChildAt(this.back, 0)
};
canvas.ui.Text.prototype.createMask = function() {
    this.msk = new canvas.px.Graphics;
    this.addChildAt(this.msk, 0);
    this.mask = this.msk
};
canvas.ui.Text.prototype.clone = function() {
    return new canvas.ui.Text(this._font, this._color, this._width, this._height, this._hAlign, this._vAlign, this._backColor, this._backAlpha)
};
canvas.ui.ProgressEvent = {
    EVENT_PROGRESS: "ProgressEvent.EVENT_PROGRESS"
};
canvas.ui.ProgressType = {
    HORIZONTAL: "h",
    VERTICAL: "v"
};
canvas.ui.Progress = function(texture, sliceArray, type) {
    this._selectable = false;
    Object.defineProperty(this, "selectable", {
        get: function() {
            return this._selectable
        },
        set: function(value) {
            this._selectable = value;
            if (value) {
                if (!this.back) {
                    this.back = new canvas.px.Graphics;
                    this.addChildAt(this.back);
                    this.back.interactive = true;
                    this.back.mousedown = this.handlerBackDown.bind(this)
                }
                this.back.interactive = true
            } else {
                if (this.back) this.back.interactive = false
            }
            this.update()
        }
    });
    this._progress = 1;
    Object.defineProperty(this, "progress", {
        get: function() {
            return this._progress
        },
        set: function(value) {
            this._progress = value > 1 ? 1 : value < 0 ? 0 : value;
            this.update()
        }
    });
    canvas.ui.Component.call(this);
    if (type == undefined) type = canvas.ui.ProgressType.HORIZONTAL;
    this.type = type;
    if (texture) {
        if (sliceArray) {
            this.backImage = this.addChild(new canvas.px.SlicedSprite(texture, sliceArray[0], sliceArray[1], sliceArray[2], sliceArray[3]))
        } else {
            this.backImage = this.addChild(new canvas.px.Sprite(texture))
        }
        this.sprite = new canvas.px.Mask(this.backImage.width, this.backImage.height);
        this.addChild(this.sprite);
        this.backImage.mask = this.sprite;
        this.setSize(this.backImage.width, this.backImage.height)
    } else {
        this.sprite = new canvas.px.Graphics;
        this.sprite.beginFill(0, 1);
        this.sprite.lineStyle(0, 0, 0);
        this.sprite.drawRect(0, 0, 1, 1);
        this.sprite.endFill()
    }
    this.addChild(this.sprite)
};
canvas.ui.Progress.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.Progress.prototype.update = function() {
    if (this.back) {
        this.back.clear();
        this.back.beginFill(0, 0);
        this.back.lineStyle(0, 0, 0);
        this.back.drawRect(0, 0, this._width, this._height);
        this.back.endFill()
    }
    if (this.type == canvas.ui.ProgressType.HORIZONTAL) {
        if (this.backImage) {
            this.backImage.width = this._width;
            this.sprite.setSize(this._width * this.progress, this._height)
        } else {
            this.sprite.width = this._width * this.progress;
            if (this._height) this.sprite.height = this._height
        }
    } else {
        if (this.backImage) {
            this.backImage.height = this._height;
            this.sprite.setSize(this._width, this._height * this.progress)
        } else {
            this.sprite.height = this._height * this.progress;
            if (this._width) this.sprite.width = this._width
        }
    }
};
canvas.ui.Progress.prototype.handlerBackDown = function(mouseData) {
    this.back.mousemove = this.handlerBackMove.bind(this);
    canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.handlerStageMouseUp, this);
    this.handlerBackMove(mouseData)
};
canvas.ui.Progress.prototype.handlerBackMove = function(mouseData) {
    var point = this.back.toLocal(new canvas.px.Point(mouseData.data.global.x, mouseData.data.global.y));
    if (this.type == canvas.ui.ProgressType.HORIZONTAL) {
        this.progress = point.x / this._width
    } else {
        this.progress = point.y / this._height
    }
    canvas.EventManager.dispatchEvent(canvas.ui.ProgressEvent.EVENT_PROGRESS, this)
};
canvas.ui.Progress.prototype.handlerStageMouseUp = function(event) {
    if (this.back) this.back.mousemove = null;
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.handlerStageMouseUp, this)
};
canvas.ui.Progress.prototype.destroy = function() {
    if (this.back) this.back.mousedown = null;
    this.handlerStageMouseUp();
    canvas.ui.Component.prototype.destroy.apply(this, [{
        children: true
    }])
};
canvas.ui.ImageEvent = {
    EVENT_LOADED: "ImageEvent.LOADED"
};
canvas.ui.Image = function(url, w, h, smoothing) {
    canvas.ui.Component.call(this);
    this.smoothing = smoothing == undefined ? true : smoothing;
    this.ready = false;
    if (w && h) {
        this.wall = new canvas.px.Mask(w, h, 0, 0);
        this.addChild(this.wall)
    }
    if (url) this.setImage(url)
};
canvas.ui.Image.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.Image.prototype.setImage = function(url, w, h) {
    if (this.url === url) return;
    this.url = url;
    var texture = canvas.ResourceLoader.getTexture(url);
    if (!this.sprite) {
        this.sprite = new canvas.px.Sprite;
        this.addChild(this.sprite)
    }
    if (texture == canvas.px.TextureEmpty) {
        canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.handlerLoaded, this);
        canvas.ResourceLoader.add([url])
    } else {
        this.setTexture(texture);
        this._ready(w, h)
    }
};
canvas.ui.Image.prototype.setTexture = function(texture) {
    if (!this.smoothing) texture.baseTexture.scaleMode = canvas.px.ScaleMode.NEAREST;
    this.sprite.texture = texture
};
canvas.ui.Image.prototype.clear = function() {
    this.url = "";
    if (this.sprite) {
        this.sprite.texture = canvas.px.TextureEmpty
    }
};
canvas.ui.Image.prototype.handlerLoaded = function() {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.handlerLoaded, this);
    this.setTexture(canvas.ResourceLoader.getTexture(this.url));
    this._ready()
};
canvas.ui.Image.prototype._ready = function(w, h) {
    this.ready = true;
    canvas.EventManager.dispatchEvent(canvas.ui.ImageEvent.EVENT_LOADED, this);
    if (this.wall) {
        this.removeChild(this.wall);
        if (this.wall.graphicsData) this.wall.destroy({
            children: true
        });
        delete this.wall
    }
    if (w && h) {
        this.sprite.width = w;
        this.sprite.height = h;
    }
};
canvas.ui.ScrollEvent = {
    EVENT_SCROLL: "ScrollEvent.EVENT_SCROLL"
};
canvas.ui.ScrollType = {
    HORIZONTAL: "h",
    VERTICAL: "v"
};
canvas.ui.Scroll = function(type, minus, plus, back, drag, wheelTarget, useDrag, useWheel, stopOnStageOut, hidePlusMinus, dragTarget) {
    this._min = 0;
    Object.defineProperty(this, "min", {
        get: function() {
            return this._min
        },
        set: function(value) {
            if (!value) value = 0;
            this._min = value > this.max ? this.max : value;
            this.current = this.current
        }
    });
    this._max = 100;
    Object.defineProperty(this, "max", {
        get: function() {
            return this._max
        },
        set: function(value) {
            if (!value) value = 0;
            this._max = value < this.min ? this.min : value;
            this.current = this.current
        }
    });
    this._step = 1;
    Object.defineProperty(this, "step", {
        get: function() {
            return this._step
        },
        set: function(value) {
            this._step = value
        }
    });
    this._current = 0;
    Object.defineProperty(this, "current", {
        get: function() {
            return this._current
        },
        set: function(value) {
            if (!value) value = 0;
            this._current = value < this.min ? this.min : value > this.max ? this.max : value;
            this.update()
        }
    });
    this._backPadding = 0;
    Object.defineProperty(this, "backPadding", {
        get: function() {
            return this._backPadding
        },
        set: function(value) {
            this._backPadding = value;
            this.update()
        }
    });
    this._padding = 0;
    Object.defineProperty(this, "padding", {
        get: function() {
            return this._padding
        },
        set: function(value) {
            this._padding = value;
            this.update()
        }
    });
    canvas.ui.Component.call(this);
    if (useDrag == undefined) useDrag = false;
    if (useWheel == undefined) useWheel = true;
    if (stopOnStageOut == undefined) stopOnStageOut = true;
    this.type = type;
    this.useDrag = useDrag;
    this.useWheel = useWheel;
    this.minusView = minus;
    this.plusView = plus;
    this.backView = back;
    this.dragView = drag;
    this.wheelTarget = wheelTarget;
    this.dragTarget = dragTarget || this.wheelTarget;
    this.hidePlusMinus = hidePlusMinus ? hidePlusMinus : false;
    if (this.backView) {
        this.addChild(this.backView);
        this.backView.interactive = true
    }
    this.dragDirection = true;
    this.wasDragged = false;
    this.shape = new canvas.px.Graphics;
    this.addChild(this.shape);
    this.shape.interactive = this.backView ? true : false;
    this.shape.click = this.shapeClickHandler.bind(this);
    if (this.dragView) {
        this.addChild(this.dragView);
        this.dragView.interactive = true;
        this.dragView.buttonMode = true;
        this.dragView.mousedown = this.handlerDragDown.bind(this)
    }
    if (this.minusView) {
        this.addChild(this.minusView);
        this.minusView.interactive = true;
        this.minusView.buttonMode = true;
        this.minusView.click = this.minusClickHandler.bind(this)
    }
    if (this.plusView) {
        this.addChild(this.plusView);
        this.plusView.interactive = true;
        this.plusView.buttonMode = true;
        this.plusView.click = this.plusClickHandler.bind(this)
    }
    if (this.wheelTarget) {
        this.wheelTarget.interactive = true;
        this.wheelTarget.mouseover = this.handlerWheelTargetOver.bind(this);
        this.wheelTarget.mouseout = this.handlerWheelTargetOut.bind(this);
        if (useDrag) {
            this.dragTarget.interactive = true;
            this.dragTarget.mousedown = this.handlerDragDown.bind(this)
        }
    }
    if (stopOnStageOut) {
        canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_OUT, null, this.stageOutHandler, this)
    }
};
canvas.ui.Scroll.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.Scroll.prototype.update = function() {
    if (this.type == canvas.ui.ScrollType.VERTICAL) {
        if (this.backView) {
            this.backView.width = this._width;
            this.backView.height = this._height - this._backPadding * 2;
            this.backView.y = this._backPadding
        }
        this.shape.clear();
        this.shape.beginFill(0, 0);
        this.shape.lineStyle(0, 0, 0);
        this.shape.drawRect(0, 0, this._width, this._height - this._padding * 2);
        this.shape.endFill();
        this.shape.y = this._padding;
        if (this.minusView) {
            this.minusView.x = Math.round((this._width - this.minusView.width) * .5)
        }
        if (this.plusView) {
            this.plusView.x = Math.round((this._width - this.plusView.width) * .5);
            this.plusView.y = this._height - this.plusView.height
        }
        if (this.dragView) {
            this.dragView.position.set(Math.round((this._width - this.dragView.width) * .5), this._padding + Math.round((this.shape.height - this.dragView.height) * ((this.current - this.min) / (this.max - this.min))))
        }
    } else {
        if (this.backView) {
            this.backView.height = this._height;
            this.backView.width = this._width - this._backPadding * 2;
            this.backView.x = this._backPadding
        }
        this.shape.clear();
        this.shape.beginFill(0, 0);
        this.shape.lineStyle(0, 0, 0);
        this.shape.drawRect(0, 0, this._width - this._padding * 2, this._height);
        this.shape.endFill();
        this.shape.x = this._padding;
        if (this.minusView) {
            this.minusView.y = Math.round((this._height - this.minusView.height) * .5)
        }
        if (this.plusView) {
            this.plusView.y = Math.round((this._height - this.plusView.height) * .5);
            this.plusView.x = this._width - this.plusView.width
        }
        if (this.dragView) {
            this.dragView.position.set(this._padding + Math.round((this.shape.width - this.dragView.width) * ((this.current - this.min) / (this.max - this.min))), Math.round((this._height - this.dragView.height) * .5))
        }
    }
    if (this.minusView) {
        if (this.hidePlusMinus == 2) {
            this.minusView.enabled = !this.hidePlusMinus || this.current != this.min
        } else {
            this.minusView.visible = !this.hidePlusMinus || this.current != this.min
        }
    }
    if (this.plusView) {
        if (this.hidePlusMinus == 2) {
            this.plusView.enabled = !this.hidePlusMinus || this.current != this.max
        } else {
            this.plusView.visible = !this.hidePlusMinus || this.current != this.max
        }
    }
    if (this.dragView) {
        this.dragView.visible = !this.isDisabled();
        this.dragView.__height = this.dragView.height;
        this.dragView.__width = this.dragView.width
    }
};
canvas.ui.Scroll.prototype.minusClickHandler = function() {
    this.current -= this.step;
    canvas.EventManager.dispatchEvent(canvas.ui.ScrollEvent.EVENT_SCROLL, this)
};
canvas.ui.Scroll.prototype.plusClickHandler = function() {
    this.current += this.step;
    canvas.EventManager.dispatchEvent(canvas.ui.ScrollEvent.EVENT_SCROLL, this)
};
canvas.ui.Scroll.prototype.shapeClickHandler = function(mouseData) {
    var point = this.shape.toLocal(new canvas.px.Point(mouseData.data.global.x, mouseData.data.global.y));
    if (this.type == canvas.ui.ScrollType.VERTICAL) {
        this.current = this.min + Math.round(point.y / this.shape.height * (this.max - this.min))
    } else {
        this.current = this.min + Math.round(point.x / this.shape.width * (this.max - this.min))
    }
    canvas.EventManager.dispatchEvent(canvas.ui.ScrollEvent.EVENT_SCROLL, this)
};
canvas.ui.Scroll.prototype.handlerDragDown = function(mouseData) {
    this.isDraggingContent = mouseData.currentTarget != this.dragView;
    this.dragDirection = mouseData.currentTarget == this.dragView;
    if (this.type == canvas.ui.ScrollType.VERTICAL) {
        this.startDragPosition = mouseData.data.global.y;
        this.startDragViewPosition = this.dragView.y
    } else {
        this.startDragPosition = mouseData.data.global.x;
        this.startDragViewPosition = this.dragView.x
    }
    this.startCurrent = this.current;
    this.shape.mousemove = this.handlerDragMove.bind(this);
    canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.handlerStageMouseUp, this);
    this.wasDragged = false
};
canvas.ui.Scroll.prototype.handlerDragMove = function(mouseData) {
    var newPos;
    var tmp, target;
    if (this.type == canvas.ui.ScrollType.VERTICAL) {
        tmp = this.startDragPosition - mouseData.data.global.y;
        if (this.dragDirection) {
            newPos = this.startDragViewPosition - tmp
        } else {
            newPos = this.startDragViewPosition + tmp
        }
        target = this.isDraggingContent ? this.startCurrent + tmp : Math.round((newPos - this.padding) / (this.shape.height - this.dragView.__height) * (this.max - this.min))
    } else {
        tmp = this.startDragPosition - mouseData.data.global.x;
        if (this.dragDirection) {
            newPos = this.startDragViewPosition - tmp
        } else {
            newPos = this.startDragViewPosition + tmp
        }
        target = this.isDraggingContent ? this.startCurrent + tmp : Math.round((newPos - this.padding) / (this.shape.width - this.dragView.__width) * (this.max - this.min))
    }
    if (!this.wasDragged && Math.abs(newPos - this.startDragViewPosition) > 2) this.wasDragged = true;
    if (this.current != target) {
        this.current = target;
        canvas.EventManager.dispatchEvent(canvas.ui.ScrollEvent.EVENT_SCROLL, this)
    }
};
canvas.ui.Scroll.prototype.handlerStageMouseUp = function(event) {
    this.isDraggingContent = false;
    this.shape.mousemove = null;
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.handlerStageMouseUp, this);
    this.wasDragged = false
};
canvas.ui.Scroll.prototype.handlerWheelTargetOver = function() {
    if (this.useWheel) {
        canvas.EventManager.addEventListener(canvas.Event.STAGE_WHEEL, null, this.mouseWheelHandler, this)
    }
};
canvas.ui.Scroll.prototype.handlerWheelTargetOut = function() {
    if (this.useWheel) {
        canvas.EventManager.removeEventListener(canvas.Event.STAGE_WHEEL, null, this.mouseWheelHandler, this)
    }
};
canvas.ui.Scroll.prototype.mouseWheelHandler = function(e) {
    if (!this.wheelTarget.parent) return;
    if (e.params.wheelDelta > 0) {
        this.minusClickHandler()
    } else {
        this.plusClickHandler()
    }
};
canvas.ui.Scroll.prototype.stageOutHandler = function(e) {
    this.handlerStageMouseUp()
};
canvas.ui.Scroll.prototype.isDisabled = function() {
    return this.min == this.max
};
canvas.ui.Scroll.prototype.destroy = function() {
    if (this.minusView) {
        this.minusView.click = null
    }
    if (this.plusView) {
        this.plusView.click = null
    }
    if (this.dragView) {
        this.dragView.mousedown = null
    }
    if (this.wheelTarget) {
        this.wheelTarget.mouseover = null;
        this.wheelTarget.mouseout = null;
        canvas.EventManager.removeEventListener(canvas.Event.STAGE_WHEEL, null, this.mouseWheelHandler, this)
    }
    if (this.dragTarget) {
        this.dragTarget.mousedown = null
    }
    this.shape.click = null;
    this.shape.mousemove = null;
    this.handlerStageMouseUp();
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_OUT, null, this.stageOutHandler, this);
    canvas.ui.Component.prototype.destroy.apply(this, [{
        children: true
    }])
};
canvas.ui.HtmlTextEvent = {
    EVENT_LINK: "HtmlTextEvent.EVENT_LINK"
};
canvas.ui.HtmlText = function(font, boldFont, color, width, height, hAlign, vAlign, backColor, backAlpha, masking) {
    this._hAlign = "left";
    Object.defineProperty(this, "hAlign", {
        get: function() {
            return this._hAlign
        },
        set: function(value) {
            this._hAlign = value;
            this.update()
        }
    });
    this._vAlign = "top";
    Object.defineProperty(this, "vAlign", {
        get: function() {
            return this._vAlign
        },
        set: function(value) {
            this._vAlign = value;
            this.update()
        }
    });
    this._lineSpace = 2;
    Object.defineProperty(this, "lineSpace", {
        get: function() {
            return this._lineSpace
        },
        set: function(value) {
            this._lineSpace = value;
            this.update()
        }
    });
    this._text = "";
    Object.defineProperty(this, "text", {
        get: function() {
            return this._text
        },
        set: function(value) {
            this._text = value.replace(/<br>/gi, "<br/>");
            this.make()
        }
    });
    Object.defineProperty(this, "color", {
        get: function() {
            return this._color
        },
        set: function(value) {
            this._color = value;
            this.make()
        }
    });
    this._border = false;
    Object.defineProperty(this, "border", {
        get: function() {
            return this._border
        },
        set: function(value) {
            this._border = value;
            if (this._border && !this.borderImage) {
                this.borderImage = new canvas.px.Graphics;
                this.addChild(this.borderImage)
            }
            this.update()
        }
    });
    this._backColor = 0;
    Object.defineProperty(this, "backColor", {
        get: function() {
            return this._backColor
        },
        set: function(value) {
            this._backColor = value;
            if (!this.back) {
                this.createBack()
            }
            this.update()
        }
    });
    this._backAlpha = 0;
    Object.defineProperty(this, "backAlpha", {
        get: function() {
            return this._backAlpha
        },
        set: function(value) {
            this._backAlpha = value;
            if (!this.back) {
                this.createBack()
            }
            this.update()
        }
    });
    Object.defineProperty(this, "textWidth", {
        get: function() {
            return Math.round(this.container.width)
        },
        set: function(value) {}
    });
    Object.defineProperty(this, "textHeight", {
        get: function() {
            return Math.round(this.container.height)
        },
        set: function(value) {}
    });
    canvas.ui.Component.call(this);
    if (font == undefined) font = canvas.Const.FONT_TAHOMA_12;
    if (boldFont == undefined) boldFont = font + "_BOLD";
    if (color == undefined) color = 16777215;
    this.font = font;
    this.boldFont = boldFont;
    this._color = color;
    if (width != undefined) this._width = width;
    else this._width = 100;
    if (height != undefined) this._height = height;
    else this._height = 100;
    if (vAlign != undefined) this._vAlign = vAlign;
    if (hAlign != undefined) this._hAlign = hAlign;
    if (backColor != undefined) {
        this.createBack();
        this._backColor = backColor
    }
    if (backAlpha) this._backAlpha = backAlpha;
    if (masking != undefined && masking) {
        this.createMask()
    }
    this.lineHeight = canvas.px.BitmapText.fonts[this.font].lineHeight;
    this.container = new canvas.px.Container;
    this.addChild(this.container);
    this.click = this.clickHandler.bind(this)
};
canvas.ui.HtmlText.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.HtmlText.prototype.make = function() {
    canvas.Functions.destroyChildren(this.container);
    this.style = {};
    this.style.font = this.font;
    this.style.color = this.color;
    var doc = (new DOMParser).parseFromString("<root>" + this.text + "</root>", "text/xml");
    this.parseList(doc.children[0].childNodes, this.style);
    this.update()
};
canvas.ui.HtmlText.prototype.parseList = function(list, style) {
    var len = list.length;
    var len2;
    var node;
    var value;
    var sprite;
    var data;
    var charData;
    var charCode;
    var newStyle;
    var g;
    var prevCharCode;
    for (var i = 0; i < len; i++) {
        node = list[i];
        switch (node.nodeName) {
            case "font":
                newStyle = canvas.Functions.cloneSimpleObject(style);
                if (node.attributes.color) {
                    newStyle.color = parseInt(node.attributes.color.value.substr(1), 16)
                }
                if (node.attributes.face) {
                    newStyle.font = node.attributes.face.value
                }
                this.parseList(node.childNodes, newStyle);
                break;
            case "b":
                newStyle = canvas.Functions.cloneSimpleObject(style);
                newStyle.font = this.boldFont;
                this.parseList(node.childNodes, newStyle);
                break;
            case "img":
                if (node.attributes.src) {
                    if (node.attributes.atlas) {
                        sprite = new canvas.px.Sprite(canvas.ResourceLoader.getImage(node.attributes.atlas.value, node.attributes.src.value))
                    } else {
                        sprite = new canvas.ui.Image(node.attributes.src.value, node.attributes.width ? parseInt(node.attributes.width.value) : 20, node.attributes.height ? parseInt(node.attributes.height.value) : 20)
                    }
                    sprite.image = true;
                    sprite.xOffset = node.attributes.xOffset ? parseInt(node.attributes.xOffset.value) : 0;
                    sprite.yOffset = node.attributes.yOffset ? parseInt(node.attributes.yOffset.value) : 0;
                    sprite.xyScale = node.attributes.scale ? parseFloat(node.attributes.scale.value) : 1;
                    if (style.href) {
                        sprite.href = style.href;
                        sprite.target = style.target;
                        sprite.interactive = true;
                        sprite.buttonMode = true
                    } else {
                        sprite.interactive = false
                    }
                    this.container.addChild(sprite)
                }
                break;
            case "a":
                newStyle = canvas.Functions.cloneSimpleObject(style);
                newStyle.normal = node.attributes.normal && parseInt(node.attributes.normal.value);
                if (node.attributes.href) {
                    newStyle.href = node.attributes.href.value
                } else {
                    newStyle.href = "#"
                }
                if (node.attributes.target) {
                    newStyle.target = node.attributes.target.value
                }
                this.parseList(node.childNodes, newStyle);
                break;
            case "br":
                sprite = new canvas.px.Sprite(canvas.px.TextureEmpty);
                sprite.br = true;
                sprite.charCode = 32;
                this.container.addChild(sprite);
                break
        }
        if (node.nodeValue) {
            value = node.nodeValue;
            len2 = value.length;
            data = canvas.px.BitmapText.fonts[style.font];
            for (var j = 0; j < len2; j++) {
                charCode = value.charCodeAt(j);
                charData = data.chars[charCode];
                if (!charData) continue;
                sprite = new canvas.px.Sprite(charData.texture);
                sprite.xOffset = charData.xOffset;
                sprite.yOffset = charData.yOffset;
                sprite.xAdvance = charData.xAdvance;
                sprite.tint = style.color;
                sprite.charCode = charCode;
                prevCharCode = charCode;
                if (style.href) {
                    sprite.href = style.href;
                    sprite.target = style.target;
                    if (!style.normal) {
                        g = new canvas.px.Graphics;
                        g.lineStyle(1, style.color, 1);
                        g.beginFill();
                        g.moveTo(0, data.lineHeight - sprite.yOffset);
                        g.lineTo(this.getWidth(sprite) + 1, data.lineHeight - sprite.yOffset);
                        g.endFill();
                        sprite.addChildAt(g, 0)
                    }
                    if (style.href != "#") {
                        sprite.interactive = true;
                        sprite.buttonMode = true
                    }
                }
                this.container.addChild(sprite)
            }
        }
    }
    sprite = this.container.addChild(new canvas.px.Sprite(canvas.px.TextureEmpty));
    sprite.charCode = 32
};
canvas.ui.HtmlText.prototype.update = function() {
    var len = this.container.children.length;
    var len2;
    var curW = 0;
    var curH = 0;
    var tmpX = 0;
    var tmpY = 0;
    var sprite;
    var lines = [];
    var line = [];
    var word = [];
    var wordLen = 0;
    var fontMaxH = 0;
    var i;
    var j;
    var dx;
    var dy;
    lines.push(line);
    for (i = 0; i < len; i++) {
        sprite = this.container.children[i];
        sprite.visible = true;
        if (sprite.charCode == 32) {
            if (tmpX >= this._width) {
                lines[lines.length - 1] = line;
                line = [];
                lines.push(line);
                tmpX = wordLen
            }
            line = line.concat(word);
            word = [];
            wordLen = 0
        }
        if (sprite.br) {
            line = line.concat(word);
            word = [];
            wordLen = 0;
            lines[lines.length - 1] = line;
            line = [];
            lines.push(line);
            tmpX = wordLen
        }
        word.push(sprite);
        tmpX += this.getWidth(sprite);
        wordLen += this.getWidth(sprite)
    }
    lines[lines.length - 1] = line.concat(word);
    len = lines.length;
    tmpX = 0;
    tmpY = 0;
    for (i = 0; i < len; i++) {
        line = lines[i];
        len2 = line.length;
        if (line.length == 1 && line[0].br) {
            tmpY += this.lineHeight
        }
        for (j = 0; j < len2; j++) {
            sprite = line[j];
            if (j == 0 && sprite.charCode == 32) {
                sprite.visible = false;
                continue
            }
            sprite.position.set(tmpX + sprite.xOffset, tmpY + sprite.yOffset);
            if (sprite.xyScale) sprite.scale.set(sprite.xyScale, sprite.xyScale);
            tmpX += this.getWidth(sprite);
            curH = Math.max(curH, sprite.height);
            if (!sprite.image) fontMaxH = Math.max(fontMaxH, sprite.height)
        }
        dy = Math.round((curH - fontMaxH) * .5);
        switch (this.hAlign) {
            case "center":
                dx = Math.round((this._width - tmpX) * .5);
                break;
            case "right":
                dx = Math.round(this._width - tmpX);
                break;
            default:
                dx = 0
        }
        for (j = 0; j < len2; j++) {
            sprite = line[j];
            sprite.x += dx;
            if (sprite.image) {
                sprite.y += Math.round((curH - sprite.height) * .5)
            } else {
                sprite.y += dy
            }
        }
        tmpX = 0;
        tmpY += curH + this.lineSpace;
        curH = 0;
        fontMaxH = 0
    }
    switch (this.vAlign) {
        case "top":
            this.container.y = 0;
            break;
        case "middle":
            this.container.y = Math.round((this._height - this.container.height) * .5);
            break;
        case "bottom":
            this.container.y = Math.round(this._height - this.container.height);
            break
    }
    if (this.border) {
        this.borderImage.clear();
        this.borderImage.beginFill(0, 0);
        this.borderImage.lineStyle(2, 16711680, 1);
        this.borderImage.drawRect(-2, -2, this._width + 4, this._height + 4);
        this.borderImage.endFill()
    }
    if (this.back) {
        this.back.clear();
        this.back.beginFill(this.backColor, this.backAlpha);
        this.back.drawRect(0, 0, this._width, this._height);
        this.back.endFill()
    }
    if (this.msk) {
        this.msk.clear();
        this.msk.beginFill(0, 1);
        this.msk.drawRect(0, 0, this._width, this._height);
        this.msk.endFill()
    }
};
canvas.ui.HtmlText.prototype.createBack = function() {
    this.back = new canvas.px.Graphics;
    this.addChildAt(this.back, 0)
};
canvas.ui.HtmlText.prototype.getWidth = function(sprite) {
    return Math.round(sprite.xAdvance ? sprite.xAdvance : sprite.width)
};
canvas.ui.HtmlText.prototype.clickHandler = function(mouseData) {
    if (mouseData.target.href) {
        if (mouseData.target.href.substr(0, 6) == "event:") {
            canvas.EventManager.dispatchEvent(canvas.ui.HtmlTextEvent.EVENT_LINK, this, mouseData.target.href.substr(6))
        } else {
            canvas.Functions.navigateToURL(mouseData.target.href, mouseData.target.target ? mouseData.target.target : "_blank")
        }
    }
};
canvas.ui.HtmlText.prototype.createMask = function() {
    this.msk = new canvas.px.Graphics;
    this.addChildAt(this.msk, 0);
    this.mask = this.msk
};
canvas.ui.PagerEvent = {
    EVENT_CHANGE: "PagerEvent.CHANGE"
};
canvas.ui.Pager = function(homeView, endView, minusView, plusView, fieldTemplate, showCount) {
    Object.defineProperty(this, "startIndex", {
        get: function() {
            return this.currentPage * this.onPage
        }
    });
    Object.defineProperty(this, "endIndex", {
        get: function() {
            return this.currentPage * this.onPage + this.onPage
        }
    });
    this._paddingNumbersLeft = 5;
    Object.defineProperty(this, "paddingNumbersLeft", {
        get: function() {
            return this._paddingNumbersLeft
        },
        set: function(value) {
            this._paddingNumbersLeft = value;
            this.update()
        }
    });
    this._paddingNumbersRight = 5;
    Object.defineProperty(this, "paddingNumbersRight", {
        get: function() {
            return this._paddingNumbersRight
        },
        set: function(value) {
            this._paddingNumbersRight = value;
            this.update()
        }
    });
    this._padding = 2;
    Object.defineProperty(this, "padding", {
        get: function() {
            return this._padding
        },
        set: function(value) {
            this._padding = value;
            this.update()
        }
    });
    this._showCount = 5;
    Object.defineProperty(this, "showCount", {
        get: function() {
            return this._showCount
        },
        set: function(value) {
            this._showCount = value;
            var len = this.numbersContainer.numChildren;
            var num = Math.max(this._showCount, len - 1);
            var sprite;
            for (var i = 0; i < num; i++) {
                if (i < this._showCount && i >= len) {
                    sprite = this.fieldTemplate.clone();
                    sprite.name = "field" + i;
                    sprite.interactive = true;
                    sprite.buttonMode = true;
                    sprite.text = "";
                    this.numbersContainer.addChild(sprite)
                }
                if (i >= this._showCount && i < len) {
                    sprite = this.numbersContainer.removeChildAt(i);
                    sprite.destroy()
                }
            }
            this.update()
        }
    });
    this._currentPage = 0;
    Object.defineProperty(this, "currentPage", {
        get: function() {
            return this._currentPage
        },
        set: function(value) {
            this._currentPage = value < 0 ? 0 : value >= this.numPages ? this.numPages - 1 : value;
            this.update()
        }
    });
    canvas.ui.Component.call(this);
    this.fieldTemplate = fieldTemplate;
    this.container = new canvas.px.Container;
    this.addChild(this.container);
    this.buttonsContainer = new canvas.px.Container;
    this.container.addChild(this.buttonsContainer);
    this.buttonHome = new canvas.ui.SimpleButton(homeView);
    this.buttonEnd = new canvas.ui.SimpleButton(endView);
    this.buttonMinus = new canvas.ui.SimpleButton(minusView);
    this.buttonPlus = new canvas.ui.SimpleButton(plusView);
    this.buttonsContainer.addChild(this.buttonHome);
    this.buttonsContainer.addChild(this.buttonEnd);
    this.buttonsContainer.addChild(this.buttonMinus);
    this.buttonsContainer.addChild(this.buttonPlus);
    this.buttonsContainer.visible = false;
    this.numbersContainer = new canvas.px.Container;
    this.container.addChild(this.numbersContainer);
    this.showCount = showCount == undefined ? 5 : showCount;
    this.container.addChild(this.numbersContainer);
    this.click = this._clickHandler.bind(this)
};
canvas.ui.Pager.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.Pager.prototype.init = function(count, onPage) {
    this.numPages = Math.ceil(count / onPage);
    this.currentPage = 0;
    this.onPage = onPage;
    this.update()
};
canvas.ui.Pager.prototype.update = function() {
    if (this._currentPage == 0) {
        this.buttonHome.interactive = false;
        this.buttonHome.alpha = .5;
        this.buttonMinus.interactive = false;
        this.buttonMinus.alpha = .5
    } else {
        this.buttonHome.interactive = true;
        this.buttonHome.alpha = 1;
        this.buttonMinus.interactive = true;
        this.buttonMinus.alpha = 1
    }
    if (this._currentPage == this.numPages - 1) {
        this.buttonEnd.interactive = false;
        this.buttonEnd.alpha = .5;
        this.buttonPlus.interactive = false;
        this.buttonPlus.alpha = .5
    } else {
        this.buttonEnd.interactive = true;
        this.buttonEnd.alpha = 1;
        this.buttonPlus.interactive = true;
        this.buttonPlus.alpha = 1
    }
    var startPage;
    var endPage;
    var i;
    var tmpX = 0;
    var t;
    var sprite;
    if (this.numPages > this._showCount) {
        this.buttonsContainer.visible = true;
        startPage = this._currentPage - Math.round((this._showCount - 1) * .5)
    } else {
        startPage = 0;
        endPage = this.numPages - 1;
        this.buttonsContainer.visible = false
    }
    if (startPage < 0) startPage = 0;
    endPage = startPage + this._showCount;
    for (i = 0; i < this._showCount; i++) {
        sprite = this.numbersContainer.getChildAt(i);
        sprite.visible = false;
        if (startPage + i >= this.numPages) {
            continue
        }
        sprite.visible = true;
        sprite.text = (startPage + i + 1).toString();
        sprite.x = tmpX;
        tmpX += sprite.width + this._padding;
        if (startPage + i == this._currentPage) {
            sprite.color = 7026733;
            sprite.interactive = false
        } else {
            sprite.color = 12713984;
            sprite.interactive = true
        }
    }
    this.buttonMinus.x = this.buttonHome.width + this._padding;
    this.numbersContainer.x = Math.round(this.buttonMinus.x + this.buttonMinus.width + this.paddingNumbersLeft);
    this.buttonPlus.x = this.numbersContainer.x + this.numbersContainer.width + this.paddingNumbersRight;
    this.buttonEnd.x = this.buttonPlus.x + this.buttonPlus.width + this._padding;
    this.numbersContainer.y = Math.round((Math.max(this.buttonsContainer.height, this.numbersContainer.height) - this.numbersContainer.height) * .5);
    this.container.x = -Math.round(this.buttonsContainer.width * .5)
};
canvas.ui.Pager.prototype._clickHandler = function(event) {
    if (this.buttonHome.interactive && canvas.Functions.findParent(this.buttonHome, event.target)) {
        this.currentPage = 0;
        canvas.EventManager.dispatchEvent(canvas.ui.PagerEvent.EVENT_CHANGE, this, this.currentPage)
    } else if (this.buttonMinus.interactive && canvas.Functions.findParent(this.buttonMinus, event.target)) {
        this.currentPage--;
        canvas.EventManager.dispatchEvent(canvas.ui.PagerEvent.EVENT_CHANGE, this, this.currentPage)
    } else if (this.buttonEnd.interactive && canvas.Functions.findParent(this.buttonEnd, event.target)) {
        this.currentPage = this.numPages - 1;
        canvas.EventManager.dispatchEvent(canvas.ui.PagerEvent.EVENT_CHANGE, this, this.currentPage)
    } else if (this.buttonPlus.interactive && canvas.Functions.findParent(this.buttonPlus, event.target)) {
        this.currentPage++;
        canvas.EventManager.dispatchEvent(canvas.ui.PagerEvent.EVENT_CHANGE, this, this.currentPage)
    } else if (event.target.name && event.target.name.substr(0, 5) == "field") {
        var startPage = this._currentPage - Math.round((this._showCount - 1) * .5);
        if (startPage < 0) startPage = 0;
        this.currentPage = parseInt(event.target.name.substr(5)) + startPage;
        canvas.EventManager.dispatchEvent(canvas.ui.PagerEvent.EVENT_CHANGE, this, this.currentPage)
    }
};
canvas.ui.Pager.prototype.destroy = function() {
    this.click = null;
    canvas.ui.Component.prototype.destroy.call(this)
};
canvas.ui.InputEvent = {
    EVENT_CHANGE: "InputEvent.CHANGE"
};
canvas.ui.Input = function(par, align, w, dx, dy) {
    this._color = 5191459;
    Object.defineProperty(this, "color", {
        get: function() {
            return this._color
        },
        set: function(value) {
            this._color = value;
            this.input.style.color = value
        }
    });
    this._parent = null;
    Object.defineProperty(this, "parent", {
        get: function() {
            return this._parent
        },
        set: function(value) {
            this._parent = value;
            if (this._parent) {
                this.interval = setInterval(this.handlerEnterFrame.bind(this), 10);
                this.input.style.display = "block";
                this.handlerEnterFrame();
                this.lastPoint.x = undefined;
                this.lastPoint.y = undefined
            } else {
                if (this.interval) clearInterval(this.interval);
                this.input.style.display = "none"
            }
        }
    });
    Object.defineProperty(this, "text", {
        get: function() {
            return this.input.value
        },
        set: function(value) {
            this.input.value = value
        }
    });
    Object.defineProperty(this, "align", {
        get: function() {
            return this.input.style["text-align"]
        },
        set: function(value) {
            this.input.style["text-align"] = value
        }
    });
    this._width = 0;
    Object.defineProperty(this, "width", {
        get: function() {
            return this._width
        },
        set: function(value) {
            this._width = value;
            this.input.style.width = this._width * this.scale
        }
    });
    Object.defineProperty(this, "bold", {
        set: function(value) {
            this.input.style["font-weight"] = value ? "bold" : "normal"
        }
    });
    this._scale = 1;
    Object.defineProperty(this, "scale", {
        get: function() {
            return this._scale
        },
        set: function(value) {
            this._scale = value;
            this.lastPoint.x = undefined;
            this.lastPoint.y = undefined;
            if (this.input) {
                this.width = this.width;
                this.input.style.fontSize = Math.floor(12 * this.scale)
            }
        }
    });
    if (align == undefined) align = "left";
    if (w == undefined) w = 100;
    this.x = 0;
    this.y = 0;
    this.dx = dx || 0;
    this.dy = dy || 0;
    this.input = document.createElement("input");
    this.input.type = "text";
    this.input.style = "border: none;background-color: transparent; height:14px; position:absolute;font-size: 12px;font-family: Tahoma;color:#ffffff;text-align: " + align + ";outline: none;overflow: hidden;";
    this.width = w;
    this.input.value = "";
    this.input.oninput = this.changeHandler.bind(this);
    par.appendChild(this.input);
    this.lastPoint = new canvas.px.Point(-1e3, -1e3)
};
canvas.ui.Input.prototype.changeHandler = function() {
    canvas.EventManager.dispatchEvent(canvas.ui.InputEvent.EVENT_CHANGE, this, this.input.value)
};
canvas.ui.Input.prototype.handlerEnterFrame = function(force) {
    if (!this.parent) return;
    var p = this.parent.toGlobal(new canvas.px.Point(0, 0));
    if (this.lastPoint.x != p.x || this.lastPoint.y != p.y || force) {
        this.lastPoint.x = p.x;
        this.lastPoint.y = p.y;
        this.input.style.left = p.x + this.x * this.scale + this.dx;
        if (this.stageHeight) this.input.style.bottom = this.stageHeight - p.y - this.y * this.scale + this.dy
    }
};
canvas.ui.ListEvent = {
    EVENT_SELECT: "ListEvent.SELECT"
};
canvas.ui.ListType = {
    VERTICAL: "ListType.VERTICAL",
    HORIZONTAL: "ListType.HORIZONTAL"
};
canvas.ui.List = function(type, cellsCount) {
    this._scrollMaxAdd = 0;
    Object.defineProperty(this, "scrollMaxAdd", {
        get: function() {
            return this._scrollMaxAdd
        },
        set: function(value) {
            this._scrollMaxAdd = value;
            this.update()
        }
    });
    this._paddingV = 5;
    Object.defineProperty(this, "paddingV", {
        get: function() {
            return this._paddingV
        },
        set: function(value) {
            this._paddingV = value;
            this.update()
        }
    });
    this._paddingH = 5;
    Object.defineProperty(this, "paddingH", {
        get: function() {
            return this._paddingH
        },
        set: function(value) {
            this._paddingH = value;
            this.update()
        }
    });
    this._scrollPadding = 0;
    Object.defineProperty(this, "scrollPadding", {
        get: function() {
            return this._scrollPadding
        },
        set: function(value) {
            this._scrollPadding = value;
            this.update()
        }
    });
    this._cellsCount = 2;
    Object.defineProperty(this, "cellsCount", {
        get: function() {
            return this._cellsCount
        },
        set: function(value) {
            this._cellsCount = value;
            this.update()
        }
    });
    this._scroll = null;
    Object.defineProperty(this, "scroll", {
        get: function() {
            return this._scroll
        },
        set: function(value) {
            if (this._scroll) {
                canvas.EventManager.removeEventListener(canvas.ui.ScrollEvent.EVENT_SCROLL, this._scroll, this.scrollHandler, this)
            }
            this._scroll = value;
            if (this._scroll) {
                canvas.EventManager.addEventListener(canvas.ui.ScrollEvent.EVENT_SCROLL, this._scroll, this.scrollHandler, this)
            }
        }
    });
    canvas.ui.Component.call(this);
    this._cellsCount = cellsCount ? cellsCount : 2;
    this.type = type ? type : canvas.ui.ListType.VERTICAL;
    this.multiSelect = false;
    this.virtual = true;
    this.reverseLayers = false;
    this.autoDestroy = true;
    this.items = [];
    this.mainContainer = new canvas.px.Container;
    this.wall = new canvas.px.Mask;
    this.mainContainer.addChild(this.wall);
    this.container = new canvas.px.Container;
    this.mainContainer.addChild(this.container);
    this.msk = new canvas.px.Mask;
    this.mainContainer.addChild(this.msk);
    this.mainContainer.mask = this.msk;
    this.addChild(this.mainContainer)
};
canvas.ui.List.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.List.prototype.add = function(item, forceUpdate) {
    if (forceUpdate == undefined) forceUpdate = true;
    this.items.push(item);
    if (forceUpdate) this.update()
};
canvas.ui.List.prototype.remove = function(item, forceUpdate) {
    if (forceUpdate == undefined) forceUpdate = true;
    var index = this.items.indexOf(item);
    if (index >= 0) this.items.splice(index, 1);
    if (forceUpdate) this.update();
    if (autoDestroy) item.destroy()
};
canvas.ui.List.prototype.addAt = function(index, item, forceUpdate) {
    if (forceUpdate == undefined) forceUpdate = true;
    this.items[index] = item;
    if (forceUpdate) this.update()
};
canvas.ui.List.prototype.removeAt = function(index, forceUpdate) {
    if (forceUpdate == undefined) forceUpdate = true;
    var item = this.items[index];
    this.items.splice(index, 1);
    if (forceUpdate) this.update();
    if (autoDestroy) item.destroy()
};
canvas.ui.List.prototype.clear = function(forceUpdate) {
    if (forceUpdate == undefined) forceUpdate = true;
    if (this.autoDestroy) {
        var len = this.items.length;
        for (var i = 0; i < len; i++) {
            this.items[i].destroy()
        }
    }
    this.items = [];
    if (forceUpdate) this.update()
};
canvas.ui.List.prototype.update = function() {
    this.wall.setSize(this._width, this._height);
    this.msk.setSize(this._width, this._height);
    var len = this.items.length;
    this.container.removeChildren();
    var renderer;
    var j = 0;
    var k = 0;
    var tmp = 0;
    var max = 0;
    for (var i = 0; i < len; i++) {
        if (j == this.cellsCount) {
            j = 0;
            tmp += max;
            max = 0;
            k++
        }
        renderer = this.items[i];
        if (this.reverseLayers) {
            this.container.addChildAt(renderer, 0)
        } else {
            this.container.addChild(renderer)
        }
        if (this.type == canvas.ui.ListType.VERTICAL) {
            renderer.position.set(j * (renderer._width + this.paddingH), tmp);
            max = Math.max(max, renderer._height + this.paddingV)
        } else {
            renderer.position.set(tmp, j * (renderer._height + this.paddingV));
            max = Math.max(max, renderer._width + this.paddingH)
        }
        j++
    }
    if (this._scroll) {
        if (this.type == canvas.ui.ListType.VERTICAL) {
            if (this.container.height > this._height) {
                this.addChild(this.scroll);
                this.scroll.setSize(0, this._height);
                this.scroll.position.set(this._width - this.scroll._width + this.scrollPadding, 0);
                this.scroll.max = this.container.height - this._height + this.scrollMaxAdd;
                this.scroll.dragView.setSize(0, Math.max(16, Math.round((this._height - 32) * (this._height / this.container.height))));
                this.scroll.current = this.scroll.current
            } else {
                this.scroll.max = 0;
                if (this.contains(this.scroll)) this.removeChild(this.scroll)
            }
        } else {
            if (this.container.width > this._width) {
                this.addChild(this.scroll);
                this.scroll.setSize(this._width, 0);
                this.scroll.position.set(0, this._height - this.scroll._height + this.scrollPadding);
                this.scroll.max = this.container.width - this._width + this.scrollMaxAdd;
                this.scroll.dragView.setSize(Math.max(16, Math.round((this._width - 32) * (this._width / this.container.width))), 0);
                this.scroll.current = this.scroll.current
            } else {
                this.scroll.max = 0;
                if (this.contains(this.scroll)) this.removeChild(this.scroll)
            }
        }
        this.scrollHandler()
    }
};
canvas.ui.List.prototype.scrollHandler = function(event) {
    if (this.type == canvas.ui.ListType.VERTICAL) {
        this.container.y = -this.scroll.current
    } else {
        this.container.x = -this.scroll.current
    }
    var len = this.items.length;
    var item;
    for (var i = 0; i < len; i++) {
        item = this.items[i];
        if (this.type == canvas.ui.ListType.VERTICAL) {
            if (item.y >= this.scroll.current - item._height && item.y <= this.scroll.current + this._height) {
                if (this.reverseLayers) {
                    this.container.addChildAt(item, 0)
                } else {
                    this.container.addChild(item)
                }
            } else {
                if (this.container.contains(item)) this.container.removeChild(item)
            }
        } else {
            if (item.x >= this.scroll.current - item._width && item.x <= this.scroll.current + this._width) {
                if (this.reverseLayers) {
                    this.container.addChildAt(item, 0)
                } else {
                    this.container.addChild(item)
                }
            } else {
                if (this.container.contains(item)) this.container.removeChild(item)
            }
        }
    }
};
canvas.ui.List.prototype.select = function(target) {
    if (this.scroll && this.scroll.wasDragged) return;
    if (this.multiSelect) {
        target.selected = !target.selected
    } else {
        var len = this.items.length;
        for (var i = 0; i < len; i++) {
            this.items[i].selected = this.items[i] === target
        }
    }
    canvas.EventManager.dispatchEvent(canvas.ui.ListEvent.EVENT_SELECT, this, target)
};
canvas.ui.List.prototype.getSelectedItems = function() {
    var result = [];
    var len = this.items.length;
    for (var i = 0; i < len; i++) {
        if (this.items[i].selected) {
            result.push(this.items[i])
        }
    }
    return result
};
canvas.ui.List.prototype.destroy = function() {
    this.scroll = null;
    this.container.click = null;
    this.autoDestroy = true;
    this.clear();
    canvas.ui.Component.prototype.destroy.apply(this)
};
canvas.ui.ListRenderer = function(data, w, h) {
    this.oldData = null;
    this._data = null;
    Object.defineProperty(this, "data", {
        get: function() {
            return this._data
        },
        set: function(value) {
            this.oldData = this._data;
            this._data = value;
            this.update()
        }
    });
    this._selected = false;
    Object.defineProperty(this, "selected", {
        get: function() {
            return this._selected
        },
        set: function(value) {
            this._selected = value;
            this.updateSelection()
        }
    });
    canvas.ui.Component.call(this);
    this.click = this.clickHandler.bind(this);
    this.mouseover = this.overHandler.bind(this);
    this.mouseout = this.outHandler.bind(this);
    if (w != undefined) this._width = w;
    if (h != undefined) this._height = h;
    if (data != undefined) this.data = data
};
canvas.ui.ListRenderer.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.ListRenderer.prototype.updateSelection = function() {};
canvas.ui.ListRenderer.prototype.clickHandler = function() {
    if (this.parent && this.parent.parent.parent && this.parent.parent.parent.select) {
        this.parent.parent.parent.select(this)
    }
};
canvas.ui.ListRenderer.prototype.overHandler = function() {};
canvas.ui.ListRenderer.prototype.outHandler = function() {};
canvas.ui.ListRenderer.prototype.destroy = function() {
    this.click = null;
    this.mouseover = null;
    this.mouseout = null;
    canvas.ui.Component.prototype.destroy.apply(this)
};
canvas.ui.ComboBoxEvent = {
    EVENT_SELECT: "ComboBoxEvent.SELECT",
    EVENT_OPEN: "ComboBoxEvent.OPEN",
    EVENT_CLOSE: "ComboBoxEvent.CLOSE"
};
canvas.ui.ComboBoxType = {
    UP: "UP",
    DOWN: "DOWN"
};
canvas.ui.ComboBox = function(headerBack, headerRenderer, listBack, listRenderer, type) {
    Object.defineProperty(this, "enabled", {
        get: function() {
            return this._enabled
        },
        set: function(value) {
            this._enabled = value;
            if (value) {
                this.filters = null
            } else {
                this.filters = [canvas.Functions.getBlackAndWhite()]
            }
            this.interactive = this.interactiveChildren = value;
            this.close()
        },
        configurable: true
    });
    this._selectedIndex = 0;
    Object.defineProperty(this, "selectedIndex", {
        get: function() {
            return this._selectedIndex
        },
        set: function(value) {
            this._selectedIndex = value;
            this.selectedItem = this.list.items[this._selectedIndex].data;
            this.update()
        }
    });
    canvas.ui.Component.call(this);
    if (type == undefined) type = canvas.ui.ComboBoxType.DOWN;
    this.type = type;
    this.headerBack = headerBack;
    this.listBack = listBack;
    this.listRenderer = listRenderer;
    this.headerRenderer = headerRenderer;
    this.addChild(this.headerBack);
    this.addChild(this.headerRenderer);
    this.list = new canvas.ui.List(canvas.ui.ListType.VERTICAL, 1);
    this.listBack.addChild(this.list);
    this.headerBack.click = this.headerClickHandler.bind(this);
    canvas.EventManager.addEventListener(canvas.ui.ListEvent.EVENT_SELECT, this.list, this.listSelectHandler, this);
    this.interactive = true;
    this.mouseover = this.overHandler.bind(this);
    this.mouseout = this.outHandler.bind(this)
};
canvas.ui.ComboBox.prototype = Object.create(canvas.ui.Component.prototype);
canvas.ui.ComboBox.prototype.init = function(items) {
    this.list.clear();
    var len = items.length;
    if (len > 0) {
        for (var i = 0; i < len; i++) {
            this.list.add(new this.listRenderer(items[i]), false)
        }
    }
    this.setSize(this._width, this._height);
    this.selectedIndex = 0
};
canvas.ui.ComboBox.prototype.update = function() {
    this.headerRenderer.data = this.selectedItem
};
canvas.ui.ComboBox.prototype.setSize = function(width, height) {
    canvas.ui.Component.prototype.setSize.apply(this, [width, height]);
    if (width > 0) {
        this.headerBack.setSize(width, 0);
        this.headerRenderer.setSize(width, 0)
    }
    if (height > 0) {}
    this.list.setSize(width, height - this.headerBack.height);
    var len = this.list.items.length;
    for (var i = 0; i < len; i++) {
        this.list.items[i].setSize(this._width, 0)
    }
    this.listBack.setSize(width, Math.min(this.list.container.height + this.list.paddingV + 4, height - this.headerBack.height));
    if (this.type == canvas.ui.ComboBoxType.UP) {
        this.listBack.y = -this.listBack._height
    } else {
        this.listBack.y = this.headerBack.height
    }
};
canvas.ui.ComboBox.prototype.headerClickHandler = function(e) {
    if (this.listBack.parent) {
        this.close()
    } else {
        this.open()
    }
};
canvas.ui.ComboBox.prototype.open = function() {
    this.addChild(this.listBack);
    this.setSize(this._width, this._height);
    canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.stageMouseUpHandler, this);
    canvas.EventManager.dispatchEvent(canvas.ui.ComboBoxEvent.EVENT_OPEN, this)
};
canvas.ui.ComboBox.prototype.isOpened = function() {
    return this.listBack.parent != null
};
canvas.ui.ComboBox.prototype.stageMouseUpHandler = function(e) {
    if (this.isOver) return;
    this.close()
};
canvas.ui.ComboBox.prototype.close = function(e) {
    if (this.listBack.parent) {
        this.removeChild(this.listBack)
    }
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.stageMouseUpHandler, this);
    canvas.EventManager.dispatchEvent(canvas.ui.ComboBoxEvent.EVENT_CLOSE, this)
};
canvas.ui.ComboBox.prototype.listSelectHandler = function(e) {
    if (e.params) {
        this.selectedIndex = this.list.items.indexOf(e.params);
        canvas.EventManager.dispatchEvent(canvas.ui.ComboBoxEvent.EVENT_SELECT, this, this.selectedItem);
        this.close()
    }
};
canvas.ui.ComboBox.prototype.outHandler = function(e) {
    this.isOver = false
};
canvas.ui.ComboBox.prototype.overHandler = function(e) {
    this.isOver = true
};
canvas.ui.ComboBox.prototype.setSelectedItem = function(key, value) {
    var i, len = this.list.items.length;
    for (i = 0; i < len; i++) {
        if (value != undefined) {
            if (this.list.items[i].data[key] === value) {
                this.selectedIndex = i;
                return
            }
        } else {
            if (this.list.items[i].data === key) {
                this.selectedIndex = i;
                return
            }
        }
    }
};
canvas.ui.ComboBox.prototype.destroy = function() {
    this.headerBack.click = null;
    this.mouseover = null;
    this.mouseout = null;
    canvas.EventManager.removeEventListener(canvas.Event.STAGE_MOUSE_UP, null, this.stageMouseUpHandler, this);
    canvas.EventManager.removeEventListener(canvas.ui.ListEvent.EVENT_SELECT, this.list, this.listSelectHandler, this);
    canvas.ui.Component.prototype.destroy.apply(this, [{
        children: true
    }])
};
canvas.data.ItemPrototypeData = {
    EVENT_COMPLETE: "ItemPrototypeData.EVENT_COMPLETE",
    QUALITY_GREY: 0,
    QUALITY_GREEN: 1,
    QUALITY_BLUE: 2,
    QUALITY_VIOLET: 3,
    QUALITY_RED: 4,
    QUALITY_TURQUOISE: 5,
    prototypes: {},
    unParsedData: {},
    loadPrototype: function(id) {
        var str = (Math.floor(parseInt(id) * .01) * 100).toString();
        while (str.length < 5) str = "0" + str;
        if (this.unParsedData[str]) return;
        var url = canvas.Config.amfPath + "artifact_artikul_" + str + ".amf";
        this.unParsedData[url] = true;
        canvas.ResourceLoader.add([url]);
        canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.completeHandler, this)
    },
    completeHandler: function() {
        canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.completeHandler, this);
        for (var str in this.unParsedData) {
            this.parse(canvas.ResourceLoader.get(str))
        }
        this.unParsedData = new Object;
        canvas.EventManager.dispatchEvent(this.EVENT_COMPLETE)
    },
    parse: function(data) {
        var obj, prototypeData;
        for (var i in data) {
            obj = data[i];
            prototypeData = new Object;
            prototypeData.id = obj.id;
            prototypeData.title = obj.title;
            prototypeData.description = obj.description;
            prototypeData.quality = obj.quality;
            prototypeData.picture = obj.picture;
            this.prototypes[prototypeData.id] = prototypeData
        }
    },
    getItemPrototype: function(id) {
        var result = this.prototypes[id];
        if (!result) {
            this.loadPrototype(id)
        }
        return result
    },
    getColorByQuality: function(quality) {
        switch (parseInt(quality)) {
            case this.QUALITY_GREY:
                return 6710886;
            case this.QUALITY_GREEN:
                return 3381504;
            case this.QUALITY_BLUE:
                return 3342591;
            case this.QUALITY_VIOLET:
                return 10027161;
            case this.QUALITY_RED:
                return 16711680;
            case this.QUALITY_TURQUOISE:
                return 93809;
            default:
                return 0
        }
    }
};
canvas.app.CanvasApp = function(par, parent, showPreloader, widthOffset, heightOffset, hideCanvas, topWindow, antialias, preserveDB) {
    if (showPreloader == undefined) showPreloader = true;
    if (hideCanvas == undefined) hideCanvas = false;
    if (preserveDB == undefined) preserveDB = false;
    this.topWindow = topWindow == undefined ? window : topWindow;
    this._fpsInterval;
    this._fps = 0;
    Object.defineProperty(this, "fps", {
        get: function() {
            return this._fps
        },
        set: function(value) {
            this._fps = value;
            clearInterval(this._fpsInterval);
            if (this._fps > 0) {
                this._fpsInterval = setInterval(this.handlerEnterFrame.bind(this), 1e3 / this._fps)
            }
        }
    });
    this.par = typeof par == "string" ? canvas.Functions.decodeUrlParams(par.replace(/\+/g, "%20")) : par;
    this.par.width = parseInt(this.par.width) || this.defaultWidth;
    this.par.height = parseInt(this.par.height) || this.defaultHeight;
    this.par.parent = parent ? parent : {
        style: {},
        setAttribute: function() {},
        addEventListener: function() {}
    };
    this.par.parent.style.userSelect = "none";
    canvas.px.utils.skipHello();
    if (!hideCanvas) {
        this.app = new canvas.px.Application(this.par.width || 100, this.par.height || 100, {
            transparent: true,
            antialias: antialias == undefined ? false : antialias,
            //powerPreference: 'high-performance',
            cullable: true,
            useContextAlpha: true,
            preserveDrawingBuffer: preserveDB,
        });
        this.par.parent.appendChild(this.app.view);
        if (topWindow != undefined) {
            this.app.renderer.plugins.interaction.removeEvents();
            this.app.renderer.plugins.interaction.topWindow = topWindow;
            this.app.renderer.plugins.interaction.interactionDOMElement = this.app.view;
            this.app.renderer.plugins.interaction.addEvents()
        }
    }
    this.par.parent.setAttribute("tabindex", 0);
    this.par.parent.style.outline = "none";
    this.width = this.par.width;
    this.height = this.par.height;
    this.widthOffset = widthOffset || 0;
    this.heightOffset = heightOffset || 0;
    this.stage = this.app ? this.app.stage : new canvas.px.Container;
    this.root = new canvas.px.Container;
    this.stage.addChild(this.root);
    var isMobile = canvas.isMobile();
    this.topWindow.document.addEventListener(isMobile ? "pointermove" : "mousemove", this.handlerStageMouseMove);
    this.topWindow.addEventListener(isMobile ? "pointerup" : "mouseup", this.handlerStageMouseUp);
    this.topWindow.document.addEventListener("mousewheel", this.handlerWheelHandler);
    this.destroy = this.destroy.bind(this);
    window.addEventListener("unload", this.destroy);
    this.stage.interactive = true;
    canvas.ResourceLoader.init();
    canvas.Config.init();
    if (showPreloader) {
        this.preloader = new canvas.px.MovieClip(canvas.Config.effectsPath + "preloader");
        this.preloader.gotoAndStop(1);
        if (this.preloader.ready) {
            this.preloaderReady()
        } else {
            canvas.EventManager.addEventListener(canvas.px.MovieClipEvent.EVENT_READY, this.preloader, this.preloaderReady, this)
        }
    } else {
        this.preInit()
    }
    this.handlerMouseDownBinded = this.handlerMouseDown.bind(this);
    this.handlerMouseOutBinded = this.handlerMouseOut.bind(this);
    this.handlerDragStartBinded = this.handlerDragStart.bind(this);
    this.resizeBinded = this.resize.bind(this);
    this.par.parent.addEventListener(isMobile ? "pointerdown" : "mousedown", this.handlerMouseDownBinded);
    this.par.parent.addEventListener(isMobile ? "pointerout" : "mouseout", this.handlerMouseOutBinded);
    this.par.parent.addEventListener("dragstart", this.handlerDragStartBinded);
    this.topWindow.addEventListener("resize", this.resizeBinded);
    this.render = this.render.bind(this);
    this.resize()
};
canvas.app.CanvasApp.prototype.preloaderReady = function() {
    canvas.EventManager.removeEventListener(canvas.px.MovieClipEvent.EVENT_READY, this.preloader, this.preloaderReady, this);
    canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_PROGRESS, null, this.handlerProgress, this);
    this.stage.addChild(this.preloader);
    this.preInit()
};
canvas.app.CanvasApp.prototype.preInit = function() {
    if (this.par.ux_conf) {
        var a = this.par.ux_conf.split(",");
        var resources = [];
        for (var i = 0; i < a.length; i++) resources.push(["ux" + i, canvas.Config.wwwPath + a[i]]);
        canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.preInitReady, this);
        canvas.ResourceLoader.add(resources)
    } else {
        this.init()
    }
};
canvas.app.CanvasApp.prototype.preInitReady = function() {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.preInitReady, this);
    if (canvas.ResourceLoader.uxReady) {
        this.uxReady()
    } else if (canvas.ResourceLoader.ux) {
        canvas.EventManager.addEventListener(canvas.Event.UX_READY, null, this.uxReady, this)
    } else {
        canvas.Functions.parseUx();
        this.uxReady()
    }
};
canvas.app.CanvasApp.prototype.uxReady = function() {
    canvas.EventManager.removeEventListener(canvas.Event.UX_READY, null, this.uxReady, this);
    this.init()
};
canvas.app.CanvasApp.prototype.init = function(resources) {
    if (resources == undefined) resources = [];
    if (!canvas.Translator.isInited && this.par.locale_file) {
        canvas.Translator.lang = this.par.locale_file.match(/locale\/([a-z]{2})\//i)[1];
        resources.push(["translate", canvas.Config.wwwPath + this.par.locale_file])
    }
    canvas.Config.initLang(canvas.Translator.lang);
    for (var i = 0; i < resources.length; i++) {
        for (var j = 0; j < resources[i].length; j++) {
            resources[i][j] = resources[i][j].replace("{canvas.Config.langPath}", canvas.Config.langPath)
        }
    }
    canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.ready, this);
    canvas.ResourceLoader.add(resources)
};
canvas.app.CanvasApp.prototype.ready = function(resources) {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.ready, this);
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_PROGRESS, null, this.handlerProgress, this);
    var xml = canvas.ResourceLoader.get("translate");
    if (!canvas.Translator.isInited) {
        canvas.Translator.init(xml && xml.data ? xml.data : null)
    }
    if (this.preloader) {
        this.preloader.destroy();
        delete this.preloader
    }
    if (this.app) this.app.ticker.stop()
};
canvas.app.CanvasApp.prototype.handlerProgress = function(e) {
    this.preloader.gotoAndStop(Math.round(e.params.progress))
};
canvas.app.CanvasApp.prototype.handlerStageMouseMove = function(mouseData) {
    canvas.EventManager.dispatchEvent(canvas.Event.STAGE_MOUSE_MOVE, null, {
        x: mouseData.clientX,
        y: mouseData.clientY,
        mouseData: mouseData
    })
};
canvas.app.CanvasApp.prototype.handlerStageMouseUp = function(mouseData) {
    canvas.EventManager.dispatchEvent(canvas.Event.STAGE_MOUSE_UP, null, {
        x: mouseData.clientX,
        y: mouseData.clientY,
        mouseData: mouseData
    })
};
canvas.app.CanvasApp.prototype.handlerWheelHandler = function(mouseData) {
    canvas.EventManager.dispatchEvent(canvas.Event.STAGE_WHEEL, null, {
        wheelDelta: mouseData.wheelDelta
    })
};
canvas.app.CanvasApp.prototype.handlerEnterFrame = function() {
    if (this.app && !this.animationRequested) {
        this.animationRequested = true;
        requestAnimationFrame(this.render)
    }
};
canvas.app.CanvasApp.prototype.render = function() {
    if (this.app) this.app.render();
    this.animationRequested = false
};
canvas.app.CanvasApp.prototype.handlerMouseDown = function(e) {};
canvas.app.CanvasApp.prototype.handlerMouseOut = function(e) {
    canvas.EventManager.dispatchEvent(canvas.Event.STAGE_MOUSE_OUT)
};
canvas.app.CanvasApp.prototype.handlerDragStart = function(e) {
    e.preventDefault();
    e.stopPropagation()
};
canvas.app.CanvasApp.prototype.focus = function() {
    this.par.parent.focus()
};
canvas.app.CanvasApp.prototype.externalKey = function(e) {
    canvas.InputManager.externalKey(e)
};
canvas.app.CanvasApp.prototype.resize = function() {
    if (this.app) this.app.renderer.resize(0, 0);
    this.width = (this.par.width ? this.par.width : this.par.parent.clientWidth) + this.widthOffset;
    this.height = (this.par.height ? this.par.height : this.par.parent.clientHeight) + this.heightOffset;
    if (this.preloader) {
        this.preloader.position.set(Math.round(this.width * .5), Math.round(this.height * .5))
    }
    if (this.app) this.app.renderer.resize(this.width, this.height)
};
canvas.app.CanvasApp.prototype.destroy = function(destroyResources, destroyEvents) {
    if (destroyResources == undefined) destroyResources = true;
    if (destroyEvents == undefined) destroyEvents = true;
    this.fps = 0;
    this.topWindow.document.removeEventListener("mousemove", this.handlerStageMouseMove);
    this.topWindow.removeEventListener("mouseup", this.handlerStageMouseUp);
    this.topWindow.document.removeEventListener("mousewheel", this.handlerWheelHandler);
    if (this.par.parent && this.par.parent.removeEventListener) {
        this.par.parent.removeEventListener("mousedown", this.handlerMouseDownBinded);
        this.par.parent.removeEventListener("mouseout", this.handlerMouseOutBinded);
        this.par.parent.removeEventListener("dragstart", this.handlerDragStartBinded)
    }
    this.topWindow.removeEventListener("resize", this.resizeBinded);
    window.removeEventListener("unload", this.destroy);
    if (destroyResources) this.destroyResources();
    if (this.app) {
        if (this.par.parent) this.par.parent.removeChild(this.app.view);
        var app = this.app;
        this.app = null;
        app.destroy(true, true)
    }
    delete this.par.parent;
    delete this.par;
    if (destroyEvents) canvas.EventManager.destroy();
    canvas.Functions.destroy();
    canvas.px.Container.prototype.destroy.apply(this.stage, [true])
};
canvas.app.CanvasApp.prototype.destroyResources = function() {
    canvas.px.Loader.destroy();
    canvas.ResourceLoader.destroy();
    canvas.px.utils.destroyTextureCache()
};
canvas.app.CanvasTopMenu = function(par, parent) {
    canvas.app.CanvasApp.call(this, par, parent, false, 0, 0);
    this.app.view.id = "top_mnu"
};
canvas.app.CanvasTopMenu.prototype = Object.create(canvas.app.CanvasApp.prototype);
canvas.app.CanvasTopMenu.prototype.init = function() {
    this.model = new canvas.app.topMenu.Model(this.par);
    canvas.app.topMenu.model = this.model;
    var fonts = [canvas.Const.FONT_TAHOMA_11_BOLD_STROKE];
    var resources = [
        ["ui", canvas.Config.ui + "main.json"]
    ];
    for (var i = 0; i < fonts.length; i++) resources.push(canvas.Config.fontsPath + fonts[i] + ".fnt");
    if (this.par.configXml) resources.push(["configMenu", this.par.configXml]);
    canvas.app.CanvasApp.prototype.init.call(this, resources)
};
canvas.app.CanvasTopMenu.prototype.ready = function() {
    canvas.app.CanvasApp.prototype.ready.call(this);
    this.model.parseConfig(canvas.ResourceLoader.get("configMenu"));
    this.main = new canvas.app.topMenu.Main(this.model);
    this.model.main = this.main;
    this.root.addChild(this.main);
    this.fps = 24;
    if(canvas.isWeakHardware()) {
        this.fps = 16;
    }
    this.resize()
};
canvas.app.CanvasTopMenu.prototype.handlerEnterFrame = function() {
    canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ENTER_FRAME);
    canvas.app.CanvasApp.prototype.handlerEnterFrame.call(this)
};
canvas.app.CanvasTopMenu.prototype.resize = function() {
    canvas.app.CanvasApp.prototype.resize.call(this)
};
canvas.app.CanvasTopMenu.prototype.swfObject = function(data) {
    if (this.main) {
        this.main.swfObject(data)
    }
};
canvas.app.CanvasTopMenu.prototype.blinkButton = function(id, status) {
    if (this.main) {
        this.main.blinkButton(parseInt(id), status)
    }
};
canvas.app.CanvasTopMenu.prototype.executeMenuId = function(id) {
    this.main.executeMenuId(id)
};
canvas.app.topMenu.log = function(message, color) {
    canvas.Log.add(canvas.Log.TOP_MENU, message, color)
};
canvas.app.view.MappingHint = function(title, color, isBold, isHtml) {
    canvas.px.Container.call(this);
    if (title == undefined) title = "No title";
    if (isBold == undefined) isBold = true;
    if (color == undefined) color = 6770493;
    if (isHtml == undefined) isHtml = false;
    this.color = color;
    if (isHtml) {
        this.tf = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_11, canvas.Const.FONT_TAHOMA_11_BOLD, color, 1e3, 16, "left", "middle");
        this.tf.position.set(16, 8)
    } else {
        this.tf = new canvas.ui.Text(isBold ? canvas.Const.FONT_TAHOMA_11_BOLD : canvas.Const.FONT_TAHOMA_11, color, 1e3, 16, "left");
        this.tf.position.set(16, 11)
    }
    this.bg = new canvas.px.SlicedSprite(canvas.ResourceLoader.getImage("ui", "alt"), 33, 0, 33, 0);
    this.addChild(this.bg);
    this.addChild(this.tf);
    this.update(title);
    this.interactiveChildren = false
};
canvas.app.view.MappingHint.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.view.MappingHint.prototype.update = function(title, color) {
    if (color == undefined) color = this.color;
    this.tf.text = title;
    this.tf.color = color;
    this.bg.width = Math.max(77, this.tf.textWidth + 35);
    this.tf.x = Math.round((this.bg.width - this.tf.textWidth) * .5)
};
canvas.app.view.PxHint = function(title) {
    this._text = "";
    Object.defineProperty(this, "text", {
        get: function() {
            return this._text
        },
        set: function(value) {
            this._text = value;
            this.visible = this._text != "";
            this.title.text = this._text;
            this.back.clear();
            this.back.beginFill(3237463432);
            this.back.lineStyle(1, 4289953873, 1);
            this.back.drawRect(0, 0, this.title.width + 2, this.title.height > 15 ? this.title.height + 2 : 12);
            this.back.endFill()
        }
    });
    canvas.px.Container.call(this);
    if (title == undefined) title = "";
    var iFlash = canvas.Translator.getLang() == canvas.Const.LANG_RU;
    this.title = new canvas.ui.Text(iFlash ? canvas.Const.FONT_IFLASH : canvas.Const.FONT_TAHOMA_9_STROKE);
    this.title.setSize(300, 20);
    this.title.position.set(2, iFlash ? 2 : 0);
    this.back = new canvas.px.Graphics;
    this.addChild(this.back);
    this.addChild(this.title);
    this.text = title
};
canvas.app.view.PxHint.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.view.MainButton = function(textColor, base, over, down, disabled, html) {
    var base = canvas.ResourceLoader.getImage("ui", base == undefined ? "main_button" : base);
    var over = canvas.ResourceLoader.getImage("ui", over == undefined ? "main_button_over" : over);
    var down = canvas.ResourceLoader.getImage("ui", down == undefined ? "main_button_down" : down);
    var disabled = canvas.ResourceLoader.getImage("ui", disabled == undefined ? "main_button_disabled" : disabled);
    canvas.ui.Button.call(this, base, over, down, disabled, [33, 0, 33, 0]);
    if (html != undefined && html) {
        this.field = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_11_BOLD, canvas.Const.FONT_TAHOMA_11_BOLD, textColor == undefined ? 16375713 : textColor, 100, 20, "center")
    } else {
        this.field = new canvas.ui.Text(canvas.Const.FONT_TAHOMA_11_BOLD, textColor == undefined ? 16375713 : textColor, 100, 20, "center")
    }
    this.field.interactive = false;
    this.field.position.set(25, 10);
    this.addChild(this.field);
    this.setSize(150, 35)
};
canvas.app.view.MainButton.prototype = Object.create(canvas.ui.Button.prototype);
canvas.app.view.MainButton.prototype.setSize = function(w, h) {
    canvas.ui.Button.prototype.setSize.apply(this, [w, h]);
    this.field.setSize(w - 50);
    this.sprite.hitArea = new canvas.px.Rectangle(25, 6, w - 50, this._height - 12)
};
canvas.app.view.MainButton.prototype.setTitle = function(text) {
    this.field.text = text
};
canvas.app.view.MainButtonWhite = function(textColor) {
    canvas.app.view.MainButton.call(this, textColor == undefined ? 8278832 : textColor, "main_button_white", "main_button_white_over", "main_button_white", "main_button_white_disabled")
};
canvas.app.view.MainButtonWhite.prototype = Object.create(canvas.app.view.MainButton.prototype);
canvas.app.view.BigHint = function(title, text) {
    canvas.px.Container.call(this);
    if (title == undefined) title = "";
    if (text == undefined) text = "";
    this.back = new canvas.px.SlicedSprite(canvas.ResourceLoader.getImage("ui", "alt2"), 14, 24, 14, 4);
    this.addChild(this.back);
    this.back.width = 289;
    this.back.height = 80;
    this.headerField = new canvas.ui.Text(canvas.Const.FONT_TAHOMA_11_BOLD, 4010799, this.back.width, 20, "center");
    this.headerField.position.set(0, 5);
    this.addChild(this.headerField);
    this.headerField.text = title;
    this.infoField = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12, 4010799, this.back.width - 45, 1e3, "left");
    this.infoField.position.set(22, 32);
    this.addChild(this.infoField);
    this.update(text)
};
canvas.app.view.BigHint.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.view.BigHint.prototype.update = function(text, title) {
    this.infoField.text = text;
    this.back.height = this.infoField.textHeight + 45;
    if (title != undefined) {
        this.headerField.text = title
    }
};
canvas.app.view.BigHint2 = function(w, text) {
    canvas.px.Container.call(this);
    if (text == undefined) text = "";
    this.back = this.addChild(new canvas.px.SlicedSprite(canvas.ResourceLoader.getImage("ui", "alt3"), 12, 12, 12, 12));
    this.back.width = w || 200;
    this.back.height = 80;
    this.infoField = this.addChild(new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_11, canvas.Const.FONT_TAHOMA_11_BOLD, 6770493, this.back.width - 45, 1e3, "left"));
    this.infoField.position.set(22, 8);
    this.update(text)
};
canvas.app.view.BigHint2.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.view.BigHint2.prototype.update = function(text) {
    this.infoField.text = text ? text : "";
    this.back.height = this.infoField.textHeight + 20
};
canvas.app.view.OldButton = function(textColor) {
    if (textColor == undefined) textColor = 12124160;
    canvas.ui.SimpleButton.call(this, canvas.ResourceLoader.getImage("ui", "old_button"), [42, 0, 42, 0]);
    this.field = new canvas.ui.Text(canvas.Const.FONT_TAHOMA_11_BOLD, textColor, 100, 20, "center");
    this.field.position.set(25, 6);
    this.addChild(this.field);
    this.setSize(171, 25)
};
canvas.app.view.OldButton.prototype = Object.create(canvas.ui.SimpleButton.prototype);
canvas.app.view.OldButton.prototype.setSize = function(w, h) {
    canvas.ui.Button.prototype.setSize.apply(this, [w, h]);
    this.field.setSize(w - 50, 20);
    this.sprite.hitArea = new canvas.px.Rectangle(25, 5, w - 50, h ? h - 10 : 15)
};
canvas.app.view.OldButton.prototype.setTitle = function(text) {
    this.field.text = text
};
canvas.app.view.Money = function(color, font, multiColors, w, h, hAlign, vAlign, iconSide, backColor, backAlpha, masking) {
    this.multiColors = multiColors == undefined ? false : multiColors;
    this.iconSide = iconSide == undefined ? true : iconSide;
    this.displayGold = true;
    this.displaySilver = true;
    this.displayBronze = true;
    this.showIfZero = false;
    this._money = 0;
    Object.defineProperty(this, "money", {
        get: function() {
            return this._money
        },
        set: function(value) {
            this._money = value;
            this.update()
        }
    });
    Object.defineProperty(this, "gold", {
        get: function() {
            return Math.floor(this._money * 1e-4)
        },
        set: function(value) {
            this.money = value * 1e4
        }
    });
    this._gems = 0;
    Object.defineProperty(this, "gems", {
        get: function() {
            return this._gems
        },
        set: function(value) {
            this._gems = canvas.Functions.getGems(value);
            this.update()
        }
    });
    this._rubins = 0;
    Object.defineProperty(this, "rubins", {
        get: function() {
            return this._rubins
        },
        set: function(value) {
            this._rubins = canvas.Functions.getGems(value);
            this.update()
        }
    });
    this._color = 0;
    Object.defineProperty(this, "color", {
        get: function() {
            return this._color
        },
        set: function(value) {
            this._color = value;
            this.field.color = this._color;
            this.update()
        }
    });
    this._image = "";
    Object.defineProperty(this, "image", {
        get: function() {
            return this._image
        },
        set: function(value) {
            this._image = value;
            this.update()
        }
    });
    this._imageCount = 0;
    Object.defineProperty(this, "imageCount", {
        get: function() {
            return this._imageCount
        },
        set: function(value) {
            this._imageCount = value;
            this.update()
        }
    });
    canvas.px.Container.call(this);
    if (color == undefined) color = 7026733;
    this.field = new canvas.ui.HtmlText(font == undefined ? canvas.Const.FONT_TAHOMA_11 : font, font == undefined ? canvas.Const.FONT_TAHOMA_11_BOLD : font, color, w == undefined ? 300 : w, h == undefined ? 20 : h, hAlign == undefined ? "left" : hAlign, vAlign == undefined ? "top" : vAlign, backColor, backAlpha, masking);
    this.addChild(this.field)
};
canvas.app.view.Money.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.view.Money.prototype.update = function() {
    var str = "";
    var num;
    var value = this.money;
    var bronze = Math.floor(value % 100);
    value = Math.floor(value * .01);
    var silver = Math.floor(value % 100);
    value = Math.floor(value * .01);
    var gold = Math.floor(value);
    if (gold != 0 && this.displayGold) {
        if (this.iconSide) {
            str += (str == "" ? "" : " ") + "<img src='money_gold' yOffset='2' atlas='ui'/> " + (this.multiColors ? "<font color='#fed200'>" + gold + "</font>" : gold)
        } else {
            str += (str == "" ? "" : " ") + (this.multiColors ? "<font color='#fed200'>" + gold + "</font>" : gold) + " <img src='money_gold' yOffset='2' atlas='ui'/>"
        }
    }
    if ((silver != 0 || this.showIfZero && Math.abs(gold) > 0) && this.displaySilver) {
        if (this.iconSide) {
            str += (str == "" ? "" : " ") + "<img src='money_silver' yOffset='2' atlas='ui'/> " + (this.multiColors ? "<font color='#dadada'>" + silver + "</font>" : silver)
        } else {
            str += (str == "" ? "" : " ") + (this.multiColors ? "<font color='#dadada'>" + silver + "</font>" : silver) + " <img src='money_silver' yOffset='2' atlas='ui'/>"
        }
    }
    if ((bronze != 0 || this.showIfZero && Math.abs(this.money) >= 100) && this.displayBronze) {
        if (this.iconSide) {
            str += (str == "" ? "" : " ") + "<img src='money_bronze' yOffset='2' atlas='ui'/> " + (this.multiColors ? "<font color='#f29f72'>" + bronze + "</font>" : bronze)
        } else {
            str += (str == "" ? "" : " ") + (this.multiColors ? "<font color='#f29f72'>" + bronze + "</font>" : bronze) + " <img src='money_bronze' yOffset='2' atlas='ui'/>"
        }
    }
    if (this.gems != 0) {
        value = this.showIfZero ? this.gems.toFixed(2) : this.gems;
        if (this.iconSide) {
            str += (str == "" ? "" : " ") + "<img src='money_crystal' yOffset='2' atlas='ui'/> " + (this.multiColors ? "<font color='#72bdf2'>" + value + "</font>" : value)
        } else {
            str += (str == "" ? "" : " ") + (this.multiColors ? "<font color='#72bdf2'>" + value + "</font>" : value) + " <img src='money_crystal' yOffset='2' atlas='ui'/>"
        }
    }
    if (this.rubins != 0) {
        value = this.showIfZero ? this.rubins.toFixed(2) : this.rubins;
        if (this.iconSide) {
            str += (str == "" ? "" : " ") + "<img src='money_rubin' yOffset='2' atlas='ui'/> " + (this.multiColors ? "<font color='#ef5b5b'>" + value + "</font>" : value)
        } else {
            str += (str == "" ? "" : " ") + (this.multiColors ? "<font color='#ef5b5b'>" + value + "</font>" : value) + " <img src='money_rubin' yOffset='2' atlas='ui'/>"
        }
    }
    if (this.image) {
        value = this.imageCount;
        if (this.iconSide) {
            str += (str == "" ? "" : " ") + "<img src='" + this.image + "' width='60' height='60' yOffset='1' scale='0.33'/> " + (this.multiColors ? "<font color='#ef5b5b'>" + value + "</font>" : value)
        } else {
            str += (str == "" ? "" : " ") + (this.multiColors ? "<font color='#ef5b5b'>" + value + "</font>" : value) + " <img src='" + this.image + "' width='60' height='60' yOffset='1' scale='0.33'/>"
        }
    }
    this.field.text = str
};
canvas.app.view.Money.prototype.animateToValue = function(money, gems, rubins, steps) {
    if (steps == undefined) steps = 10;
    if (this.timer) clearInterval(this.timer);
    this.targetMoney = money == undefined ? this.money : money;
    this.targetGems = gems == undefined ? this.gems : canvas.Functions.getGems(gems);
    this.targetRubins = rubins == undefined ? this.rubins : canvas.Functions.getGems(rubins);
    this.stepMoney = Math.max(Math.ceil(Math.abs(this.money - this.targetMoney) / steps), .01);
    this.stepGems = Math.max(Math.abs(this.gems - this.targetGems) / steps, .01);
    this.stepRubins = Math.max(Math.abs(this.rubins - this.targetRubins) / steps, .01);
    this.timer = setInterval(this.timerHandler.bind(this), 50)
};
canvas.app.view.Money.prototype.timerHandler = function() {
    var target;
    if (this.targetMoney > this.money) {
        target = this.money + this.stepMoney;
        if (target >= this.targetMoney) {
            this.showIfZero = false;
            this.money = target = this.targetMoney
        } else {
            this.showIfZero = true;
            this.money = target
        }
        this._money = target
    } else if (this.targetMoney < this.money) {
        target = this.money - this.stepMoney;
        if (target <= this.targetMoney) {
            this.showIfZero = false;
            this.money = target = this.targetMoney
        } else {
            this.showIfZero = true;
            this.money = target
        }
        this._money = target
    } else if (this.targetGems > this.gems) {
        target = this.gems + this.stepGems;
        if (target >= this.targetGems) {
            this.showIfZero = false;
            this.gems = this.targetGems
        } else {
            this.showIfZero = true;
            this.gems = target
        }
    } else if (this.targetGems < this.gems) {
        target = this.gems - this.stepGems;
        if (target <= this.targetGems) {
            this.showIfZero = false;
            this.gems = this.targetGems
        } else {
            this.showIfZero = true;
            this.gems = target
        }
    } else if (this.targetRubins > this.rubins) {
        target = this.rubins + this.stepRubins;
        if (target >= this.targetRubins) {
            this.showIfZero = false;
            this.rubins = this.targetRubins
        } else {
            this.showIfZero = true;
            this.rubins = target
        }
    } else if (this.targetRubins < this.rubins) {
        target = this.rubins - this.stepRubins;
        if (target <= this.targetRubins) {
            this.showIfZero = false;
            this.rubins = this.targetRubins
        } else {
            this.showIfZero = true;
            this.rubins = target
        }
    } else {
        clearInterval(this.timer)
    }
};
canvas.app.view.Money.prototype.makeOneCoin = function(moneyValue) {
    if (moneyValue < 100) {
        return moneyValue
    } else if (moneyValue < 1e4) {
        return Math.floor(moneyValue * .01) * 100
    } else {
        return Math.floor(moneyValue * 1e-4) * 1e4
    }
};
canvas.app.view.Money.prototype.reset = function() {
    this.money = this.gems = this.rubins = 0;
    this.image = ""
};
canvas.app.view.SmallButton = function(textColor, html) {
    var base = canvas.ResourceLoader.getImage("ui", "small_button");
    var over = canvas.ResourceLoader.getImage("ui", "small_button_over");
    var down = canvas.ResourceLoader.getImage("ui", "small_button_down");
    var disabled = canvas.ResourceLoader.getImage("ui", "small_button_disabled");
    canvas.ui.Button.call(this, base, over, down, disabled, [8, 0, 8, 0]);
    if (html != undefined && html) {
        this.field = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_10_BOLD, canvas.Const.FONT_TAHOMA_10_BOLD, textColor == undefined ? 2954752 : textColor, 100, 20, "center")
    } else {
        this.field = new canvas.ui.Text(canvas.Const.FONT_TAHOMA_10_BOLD, textColor == undefined ? 2954752 : textColor, 100, 20, "center")
    }
    this.field.interactive = false;
    this.field.position.set(8, 1);
    this.addChild(this.field);
    this.setSize(60, 15)
};
canvas.app.view.SmallButton.prototype = Object.create(canvas.ui.Button.prototype);
canvas.app.view.SmallButton.prototype.setSize = function(w, h) {
    canvas.ui.Button.prototype.setSize.apply(this, [w, h]);
    this.field.setSize(w - 16)
};
canvas.app.view.SmallButton.prototype.setTitle = function(text) {
    this.field.text = text
};
canvas.app.view.MacroHtmlText = function() {
    this.M_ARTIFACT = "ARTIFACT";
    canvas.ui.HtmlText.apply(this, arguments)
};
canvas.app.view.MacroHtmlText.prototype = Object.create(canvas.ui.HtmlText.prototype);
canvas.app.view.MacroHtmlText.prototype.make = function() {
    this.applyMacro();
    canvas.ui.HtmlText.prototype.make.call(this)
};
canvas.app.view.MacroHtmlText.prototype.applyMacro = function() {
    var text = this._text;
    var r = new RegExp(/#[A-Z_]+\[[^#\]]+\]#/gi);
    var b;
    var str;
    var macros;
    var params;
    var loading = false;
    var itemPrototype;
    var result = "";
    var obj = r.exec(text);
    while (obj != null) {
        b = obj[0].split("[");
        macros = b[0].substr(1);
        params = b[1].substr(0, b[1].length - 2);
        switch (macros) {
            case this.M_ARTIFACT:
                b = params.split(",");
                itemPrototype = canvas.data.ItemPrototypeData.getItemPrototype(b[0]);
                if (!itemPrototype) loading = true;
                if (!loading) {
                    str = "<b><font color='#" + canvas.data.ItemPrototypeData.getColorByQuality(itemPrototype.quality).toString(16) + "'><a href='artifact_info.php?artikul_id=" + itemPrototype.id + "' target='_blank'>" + (b[1] ? b[1] : itemPrototype.title) + "</a></font>" + (b[2] ? " <font color='#ba0000'>" + b[2] + " " + canvas.Translator.getText(2022) + "</font>" : "") + "</b>";
                    r.lastIndex = obj.index + str.length;
                    text = text.substr(0, obj.index) + str + text.substr(obj.index + obj[0].length)
                }
                break
        }
        obj = r.exec(text)
    }
    if (loading) {
        canvas.EventManager.addEventListener(canvas.data.ItemPrototypeData.EVENT_COMPLETE, null, this.completeProtoHandler, this)
    } else {
        this._text = text
    }
};
canvas.app.view.MacroHtmlText.prototype.completeProtoHandler = function() {
    canvas.EventManager.removeEventListener(canvas.data.ItemPrototypeData.EVENT_COMPLETE, null, this.completeProtoHandler, this);
    this.make()
};
canvas.app.view.window.ConfirmWindowOld = function() {
    canvas.px.Window.call(this);
    this.back = this.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "confirm_back")));
    this.header = new canvas.px.SlicedSprite(canvas.ResourceLoader.getImage("ui", "header"), 31, 0, 31, 0);
    this.header.y = -10;
    this.addChild(this.header);
    this.header.interactive = true;
    this.headerField = new canvas.ui.Text(canvas.Const.FONT_TAHOMA_11_BOLD, 16577975, this.back.width, 20, "center", "top", 0, 0);
    this.headerField.y = -7;
    this.addChild(this.headerField);
    this.infoField = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD, 5324857, this.back.width - 40, 85, "center", "middle");
    this.addChild(this.infoField);
    this.infoField.position.set(18, 20);
    this.yesButton = new canvas.app.view.MainButton;
    this.yesButton.setSize(190, 0);
    this.addChild(this.yesButton);
    this.yesButton.setTitle(canvas.Translator.getText(805));
    this.yesButton.position.set(Math.round((this.back.width - this.yesButton.width) * .5), this.back.height - 95);
    this.noButton = new canvas.app.view.MainButton;
    this.noButton.setSize(190, 0);
    this.addChild(this.noButton);
    this.noButton.setTitle(canvas.Translator.getText(806));
    this.noButton.position.set(this.yesButton.x, this.back.height - 60);
    this.closeButton = this.noButton
};
canvas.app.view.window.ConfirmWindowOld.prototype = Object.create(canvas.px.Window.prototype);
canvas.app.view.window.ConfirmWindowOld.prototype.update = function(headerText, text, yesTitle, noTitle, actionFunc, actionFuncParams, context) {
    this.headerField.text = headerText;
    this.header.width = this.headerField.textWidth + 80;
    this.header.x = Math.round((this.back.width - this.header.width) * .5);
    if (yesTitle != undefined) this.yesButton.setTitle(yesTitle);
    if (noTitle != undefined) this.noButton.setTitle(noTitle);
    this.infoField.text = text;
    this.yesButton.visible = yesTitle != "";
    this.actionFunc = actionFunc;
    this.actionFuncParams = actionFuncParams;
    this.context = context
};
canvas.app.view.window.ConfirmWindowOld.prototype.clickHandler = function(mouseData) {
    canvas.px.Window.prototype.clickHandler.call(this, mouseData);
    if (canvas.Functions.findParent(this.yesButton, mouseData.target)) {
        if (this.actionFunc != null) this.actionFunc.apply(this.context, this.actionFuncParams);
        canvas.EventManager.dispatchEvent(canvas.px.WindowEvent.EVENT_CLOSE, this)
    }
};
canvas.app.topMenu.Const = {
    BRILL_VERSION: 1,
    ID_LOCATION: 2
};
canvas.app.topMenu.Event = {
    ENTER_FRAME: "TopMenu.ENTER_FRAME",
    ITEM_DOWN: "TopMenu.ITEM_DOWN",
    ITEM_OVER: "TopMenu.ITEM_OVER",
    ITEM_OUT: "TopMenu.ITEM_OUT",
    ITEM_SELECT: "TopMenu.ITEM_SELECT",
    ITEM_DESELECT: "TopMenu.ITEM_DESELECT",
    ITEM_CLICK: "TopMenu.ITEM_CLICK"
};
canvas.app.topMenu.Main = function(model) {
    canvas.px.Container.call(this);
    this.model = model;
    this.view = new canvas.app.topMenu.View;
    this.view.interactive = true;
    this.view.mouseup = this.upHandler.bind(this);
    this.addChild(this.view);
    this.dragItem = null;
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_DOWN, null, this.itemDownHandler, this);
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_OVER, null, this.itemOverHandler, this);
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_SELECT, null, this.itemSelectHandler, this);
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_DESELECT, null, this.itemDeselectHandler, this);
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_CLICK, null, this.itemClickHandler, this);
    canvas.EventManager.addEventListener(canvas.Event.STAGE_MOUSE_OUT, null, this.outHandler, this)
};
canvas.app.topMenu.Main.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.topMenu.Main.prototype.swfObject = function(data) {
    var str;
    var config;
    if (data) {
        for (str in data) {
            switch (str) {
                case "common|top_menu":
                    this.model.dragging = parseInt(data[str].dragDropItems) != 0;
                    break;
                case "bgFilledState":
                    var i = parseInt(data[str]);
                    switch (i) {
                        case 1:
                        case 2:
                            this.view.changeBg((i + 1).toString());
                            break;
                        default:
                            this.view.changeBg("")
                    }
                    break
            }
        }
    }
};
canvas.app.topMenu.Main.prototype.blinkButton = function(id, status) {
    var conf = this.model;
    if (status) {
        if (conf.blinkIds.indexOf(id) < 0) conf.blinkIds.push(id)
    } else {
        if (conf.blinkIds.indexOf(id) >= 0) conf.blinkIds.splice(conf.blinkIds.indexOf(id), 1)
    }
    this.view.testBlink()
};
canvas.app.topMenu.Main.prototype.itemDownHandler = function(event) {
    this.dragItem = event.params
};
canvas.app.topMenu.Main.prototype.itemOverHandler = function(event) {
    if (this.model.dragging && this.dragItem && this.dragItem != event.params) {
        this.dragItem.wasChanged = true;
        this.view.replace(this.dragItem, event.params);
        var len = this.view.items.length;
        var str = "";
        for (var i = 0; i < len; i++) {
            str += (str ? "," : "") + this.view.items[i].data.id
        }
        this.model.localStorage.set("order", str);
        this.view.testSubMenuSides()
    }
};
canvas.app.topMenu.Main.prototype.upHandler = function() {
    this.dragItem = null
};
canvas.app.topMenu.Main.prototype.outHandler = function() {
    this.dragItem = null
};
canvas.app.topMenu.Main.prototype.itemSelectHandler = function(event) {
    this.view.selectItem(event.params)
};
canvas.app.topMenu.Main.prototype.itemDeselectHandler = function(event) {
    this.view.deselectItem(event.params)
};
canvas.app.topMenu.Main.prototype.itemClickHandler = function(event) {
    var item = event.params;
    if (this.model.blinkIds.indexOf(item.data.id) >= 0) {
        this.model.blinkIds.splice(this.model.blinkIds.indexOf(item.data.id), 1);
        var request = new canvas.utils.URLRequest("/user_conf.php?mode=button_press&type=top&id=" + item.data.id);
        request.load()
    }
    if (canvas.app.topMenu.model.jsPopup) {
        this.processMenu(item.data)
    } else {
        switch (item.data.id) {
            case 8:
                this.model.localStorage.set("brillVersion", canvas.app.topMenu.Const.BRILL_VERSION);
                this.processMenu(item.data);
                break;
            case 12:
                canvas.Functions.navigateToURL(canvas.Translator.getText(1100), "_blank");
                break;
            case 11:
                setTimeout(help_menu.bind(this, item.x - 25, item.y + 80), 100);
                break;
            /*
        case 17:
            canvas.Functions.navigateToURL("user_referrer.php", "main");
            break;*/
            case 21:
                canvas.Functions.navigateToURL("info/info/", "_blank");
                break;
            case 22:
                canvas.Functions.navigateToURL("info/library/", "_blank");
                break;
            case 23:
                canvas.Functions.navigateToURL("info/news/", "_blank");
                break;
            default:
                this.processMenu(item.data)
        }
    }
};
canvas.app.topMenu.Main.prototype.processMenu = function(data) {
    try {
        if (data.command) processMenu(data.command)
    } catch (e) {}
};
canvas.app.topMenu.Main.prototype.executeMenuId = function(id) {
    this.itemClickHandler({
        params: {
            data: this.model.itemsById[id]
        }
    })
};
canvas.app.topMenu.Model = function(par) {
    this.labels = par.labels.split("|");
    this.localStorage = new canvas.utils.LocalStorage(canvas.Log.TOP_MENU);
    this.dragging = parseInt(par.dragDropItems) != 0;
    this.jsPopup = par.js_popup != undefined && par.js_popup == "1";
    var brillVersion = parseInt(this.localStorage.get("brillVersion"));
    this.showBrillMessage = (!brillVersion || brillVersion != canvas.app.topMenu.Const.BRILL_VERSION) && par.br_msg;
    this.blinkIds = [];
    if (par.blink != undefined) {
        var a = par.blink.split("|");
        if (this.showBrillMessage) a.push(8);
        var len = a.length;
        for (var i = 0; i < len; i++) {
            if (a[i] != "" && this.blinkIds.indexOf(parseInt(a[i])) < 0) {
                this.blinkIds.push(parseInt(a[i]))
            }
        }
    }
    this.items = [];
    this.itemsById = {}
};
canvas.app.topMenu.Model.prototype.parseConfig = function(xml) {
    var doc;
    if (xml) {
        doc = xml.data;
        var list = doc.children[0].children;
        var len = list.length;
        var len2;
        var item;
        var item2;
        var index;
        var order = this.localStorage.get("order");
        var a = order ? order.split(",") : null;
        for (var i = 0; i < len; i++) {
            item = this.parseItem(list[i]);
            if (a) {
                item.index = a.indexOf(item.id.toString())
            }
            this.items.push(item);
            this.itemsById[item.id] = item;
            len2 = list[i].children.length;
            if (len2 > 0) {
                item.items = [];
                for (var j = 0; j < len2; j++) {
                    item2 = this.parseItem(list[i].children[j]);
                    item.items.push(item2)
                }
            }
        }
        if (a) {
            this.items.sort(function(a, b) {
                return a.index - b.index
            })
        } else {
            this.items.sort(function(a, b) {
                return a.id - b.id
            })
        }
    } else {}
};
canvas.app.topMenu.Model.prototype.parseItem = function(obj) {
    return {
        id: parseInt(obj.attributes.id.value),
        label: obj.attributes.label.value,
        command: obj.attributes.command ? obj.attributes.command.value : "",
        pict: obj.attributes.pict.value.split(".")[0]
    }
};
canvas.app.topMenu.View = function() {
    canvas.px.Container.call(this);
    this.container = this.addChild(new canvas.px.Container);
    this.container.position.set(0, 6);
    var conf = canvas.app.topMenu.model;
    var len = conf.items.length;
    this.items = [];
    var item;
    for (var i = 0; i < len; i++) {
        item = this.container.addChild(new canvas.app.topMenu.view.ItemView(conf.items[i]));
        item.position.set(i * 55, 0);
        item.init();
        this.items.push(item)
    }
    this.testSubMenuSides();
    setTimeout(this.testBlink.bind(this), 100)
};
canvas.app.topMenu.View.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.topMenu.View.prototype.update = function() {
    var len = this.items.length;
    var item;
    var pos;
    for (var i = 0; i < len; i++) {
        item = this.items[i];
        pos = i * 55;
        if (item.x != pos) {
            item.go(pos)
        }
    }
};
canvas.app.topMenu.View.prototype.testBlink = function() {
    var len = this.items.length;
    for (var i = 0; i < len; i++) {
        this.items[i].testBlink()
    }
};
canvas.app.topMenu.View.prototype.replace = function(item1, item2) {
    var index1 = this.items.indexOf(item1);
    var index2 = this.items.indexOf(item2);
    this.items[index1] = item2;
    this.items[index2] = item1;
    this.container.addChild(item1);
    item2.interactive = item2.container.interactive = false;
    item2.outHandler();
    this.update()
};
canvas.app.topMenu.View.prototype.selectItem = function(item) {
    var len = this.items.length;
    var tmpItem;
    for (var i = 0; i < len; i++) {
        tmpItem = this.items[i];
        if (item != tmpItem) {
            tmpItem.mode = 2
        }
    }
    if (item) {
        this.container.addChild(item)
    }
};
canvas.app.topMenu.View.prototype.deselectItem = function(item) {
    var len = this.items.length;
    var tmpItem;
    for (var i = 0; i < len; i++) {
        tmpItem = this.items[i];
        if (item != tmpItem) {
            tmpItem.mode = 0
        }
    }
};
canvas.app.topMenu.View.prototype.testSubMenuSides = function() {
    var len = this.items.length;
    var item;
    for (var i = 0; i < len; i++) {
        item = this.items[i];
        if (item.items) {
            item.menuSide = i <= len * .5
        }
    }
};
canvas.app.topMenu.View.prototype.changeBg = function(index) {
    var len = this.items.length;
    var item;
    for (var i = 0; i < len; i++) {
        item = this.items[i];
        if (item.data.pict == "battleField") {
            item.image.texture = canvas.ResourceLoader.getImage("ui", "top/battleField" + index)
        }
    }
};
canvas.app.topMenu.view.ItemView = function(data, isSmall) {
    this.data = data;
    this._mode = 0;
    Object.defineProperty(this, "mode", {
        get: function() {
            return this._mode
        },
        set: function(value) {
            if (this._mode != value) {
                this._mode = value;
                switch (value) {
                    case 0:
                        this.targetArrowRotation = 0;
                        this.targetArrowX = 36;
                        this.targetY = 0;
                        this.targetDarknees = 0;
                        this.targetItemsY = -80;
                        break;
                    case 1:
                        this.targetArrowRotation = canvas.Functions.degToRad(180);
                        this.targetArrowX = 10;
                        this.targetY = 0;
                        this.targetDarknees = 0;
                        this.targetItemsY = 10;
                        break;
                    case 2:
                        this.targetDarknees = 5;
                        this.targetY = -20;
                        this.targetItemsY = -80;
                        break
                }
                canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this)
            }
        }
    });
    this._darknees = 0;
    Object.defineProperty(this, "darknees", {
        get: function() {
            return this._darknees
        },
        set: function(value) {
            if (this._darknees != value) {
                this._darknees = value;
                if (value > 0) {
                    this.filters = [canvas.Functions.getBrightness(1 - .1 * value)]
                } else {
                    this.filters = []
                }
            }
        }
    });
    this._menuSide = true;
    Object.defineProperty(this, "menuSide", {
        get: function() {
            return this._menuSide
        },
        set: function(value) {
            if (this._menuSide != value) {
                this._menuSide = value;
                if (this.items) {
                    var len = this.items.length;
                    for (var i = 0; i < len; i++) {
                        this.items[i].x = this.items[i].targetX = value ? i * 50 : -i * 50 - 94
                    }
                }
            }
        }
    });
    canvas.px.Container.call(this);
    if (isSmall == undefined) isSmall = false;
    this.isSmall = isSmall;
    this.container = this.addChild(new canvas.px.Container);
    this.container.pivot = new canvas.px.Point(30, 30);
    this.container.interactive = true;
    var texture;
    if (this.isSmall) {
        this.container.position.set(30, 30);
        this.back = this.container.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/item_back_small")));
        texture = canvas.ResourceLoader.getImage("ui", "top/" + data.pict);
        this.image = this.container.addChild(new canvas.px.Sprite(texture));
        this.front = this.container.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/item_front_small")));
        this.back.position.set(8, 8);
        this.image.position.set(8, 0);
        this.hitArea = new canvas.px.Circle(34, 30, 26);
        this.light = new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/light_small"));
        this.light.position.set(7, 6)
    } else {
        this.container.position.set(30, 30);
        this.back = this.container.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/item_back")));
        texture = canvas.ResourceLoader.getImage("ui", "top/" + data.pict);
        this.image = this.container.addChild(new canvas.px.Sprite(texture));
        this.front = this.container.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/item_front")));
        this.field = this.addChild(new canvas.ui.Text(canvas.Const.FONT_TAHOMA_11_BOLD_STROKE, 16770731, 70, 17, "center"));
        this.field.position.set(0, 60);
        this.back.position.set(6, 0);
        this.image.position.set(6, 0);
        this.hitArea = new canvas.px.Circle(34, 30, 30);
        this.field.alpha = 0;
        this.field.text = canvas.app.topMenu.model.labels[data.label];
        this.light = new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/light_big"));
        this.light.position.set(5, 0)
    }
    this.interactive = true;
    this.buttonMode = true;
    this.lightDirection = 0;
    this.mouseover = this.over2Handler.bind(this);
    this.container.mouseover = this.overHandler.bind(this);
    this.mouseout = this.outHandler.bind(this);
    this.mousedown = this.downHandler.bind(this);
    this.mouseup = this.upHandler.bind(this);
    this.click = this.clickHandler.bind(this);
    this.wasChanged = false;
    if (this.data.items) {
        this.arrowBack = this.container.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/arrow_back")));
        this.arrow = this.arrowBack.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "top/arrow")));
        this.arrow.pivot = new canvas.px.Point(14, 14);
        this.arrow.position.set(14, 14);
        this.arrowBack.position.set(36, 36);
        this.itemsContainer = new canvas.px.Container;
        this.itemsContainer.position.set(50, -80);
        var len = this.data.items.length;
        this.items = [];
        var item;
        for (var i = 0; i < len; i++) {
            item = this.itemsContainer.addChild(new canvas.app.topMenu.view.ItemView(this.data.items[i], true));
            item.position.set(i * 50, 0);
            item.init();
            this.items.push(item)
        }
        canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_OVER, null, this.itemOverHandler, this);
        canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ITEM_OUT, null, this.itemOutHandler, this)
    }
};
canvas.app.topMenu.view.ItemView.prototype = Object.create(canvas.px.Container.prototype);
canvas.app.topMenu.view.ItemView.prototype.init = function() {
    this.targetAlpha = 0;
    this.targetScale = 1;
    this.targetArrowRotation = 0;
    this.targetArrowX = 36;
    this.targetX = this.x;
    this.targetY = 0;
    this.targetDarknees = 0;
    this.targetItemsY = -80
};
canvas.app.topMenu.view.ItemView.prototype.go = function(value) {
    this.targetX = value;
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this)
};
canvas.app.topMenu.view.ItemView.prototype.overHandler = function() {
    this.targetAlpha = 1;
    this.targetScale = 1.15;
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this);
    this.over2Handler()
};
canvas.app.topMenu.view.ItemView.prototype.over2Handler = function() {
    if (!this.overedItem && !this.isSmall && this.items) {
        this.field.text = canvas.app.topMenu.model.labels[this.data.label]
    }
    canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_OVER, null, this)
};
canvas.app.topMenu.view.ItemView.prototype.downHandler = function() {
    this.targetAlpha = 1;
    this.targetScale = this.mode == 1 ? 1 : .9;
    this.wasChanged = false;
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this);
    if (this.mode == 0 && !this.isSmall) {
        canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_DOWN, null, this)
    }
};
canvas.app.topMenu.view.ItemView.prototype.upHandler = function() {
    this.targetAlpha = 1;
    this.targetScale = 1;
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this)
};
canvas.app.topMenu.view.ItemView.prototype.outHandler = function() {
    this.targetAlpha = 0;
    this.targetScale = 1;
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this);
    canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_OUT, null, this)
};
canvas.app.topMenu.view.ItemView.prototype.frameHandler = function() {
    if (this.field && this.targetAlpha != this.field.alpha || this.targetScale != this.container.scale.x || this.targetX != this.x || this.targetY != this.y || this.arrowBack && (this.targetArrowX != this.arrowBack.x || this.targetArrowRotation != this.arrow.rotation) || this.targetDarknees != this.darknees || this.itemsContainer && this.targetItemsY != this.itemsContainer.y) {
        if (this.field) {
            if (this.targetAlpha > this.field.alpha) {
                this.field.alpha = Math.min(this.field.alpha + .3, this.targetAlpha)
            } else if (this.targetAlpha < this.field.alpha) {
                this.field.alpha = Math.max(this.field.alpha - .1, this.targetAlpha)
            }
        }
        if (this.targetScale > this.container.scale.x) {
            this.container.scale.x = Math.min(this.container.scale.x + .05, this.targetScale);
            this.container.scale.y = this.container.scale.x
        } else if (this.targetScale < this.container.scale.x) {
            this.container.scale.x = Math.max(this.container.scale.x - .1, this.targetScale);
            this.container.scale.y = this.container.scale.x
        }
        var diff;
        if (this.targetX > this.x) {
            diff = Math.round(Math.abs(this.targetX - this.x) * .4);
            this.x = Math.min(this.x + Math.max(20, diff), this.targetX)
        } else if (this.targetX < this.x) {
            diff = Math.round(Math.abs(this.targetX - this.x) * .4);
            this.x = Math.max(this.x - Math.max(20, diff), this.targetX)
        }
        if (this.targetY > this.y) {
            this.y = Math.min(this.y + 5, this.targetY)
        } else if (this.targetY < this.y) {
            this.y = Math.max(this.y - 5, this.targetY)
        }
        if (this.arrowBack) {
            if (this.targetArrowX > this.arrowBack.x) {
                this.arrowBack.x = Math.min(this.arrowBack.x + 8, this.targetArrowX)
            } else if (this.targetArrowX < this.arrowBack.x) {
                this.arrowBack.x = Math.max(this.arrowBack.x - 8, this.targetArrowX)
            }
            if (this.targetArrowRotation > this.arrow.rotation) {
                this.arrow.rotation = Math.min(this.arrow.rotation + .8, this.targetArrowRotation)
            } else if (this.targetArrowRotation < this.arrow.rotation) {
                this.arrow.rotation = Math.max(this.arrow.rotation - .8, this.targetArrowRotation)
            }
        }
        if (this.targetDarknees > this.darknees) {
            this.darknees = Math.min(this.darknees + 2, this.targetDarknees)
        } else if (this.targetDarknees < this.darknees) {
            this.darknees = Math.max(this.darknees - 2, this.targetDarknees)
        }
        if (this.itemsContainer) {
            if (this.targetItemsY > this.itemsContainer.y) {
                if (!this.contains(this.itemsContainer)) this.addChild(this.itemsContainer);
                this.itemsContainer.y = Math.min(this.itemsContainer.y + 20, this.targetItemsY)
            } else if (this.targetItemsY < this.itemsContainer.y) {
                if (!this.contains(this.itemsContainer)) this.addChild(this.itemsContainer);
                this.itemsContainer.y = Math.max(this.itemsContainer.y - 20, this.targetItemsY)
            } else {
                if (this.targetItemsY < 0) {
                    this.removeIfExist(this.itemsContainer)
                }
            }
        }
    } else {
        canvas.EventManager.removeEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this);
        this.interactive = this.container.interactive = true
    }
};
canvas.app.topMenu.view.ItemView.prototype.clickHandler = function() {
    if (!this.wasChanged) {
        if (this.data.items) {
            if (this.mode == 1) {
                this.mode = 0;
                canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_DESELECT, null, this)
            } else {
                this.mode = 1;
                canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_SELECT, null, this)
            }
        } else {
            canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_DESELECT);
            canvas.EventManager.dispatchEvent(canvas.app.topMenu.Event.ITEM_CLICK, null, this);
            this.stopBlink()
        }
    }
};
canvas.app.topMenu.view.ItemView.prototype.itemOverHandler = function(event) {
    if (this.items.indexOf(event.params) >= 0) {
        this.overedItem = event.params;
        this.field.text = canvas.app.topMenu.model.labels[event.params.data.label];
        this.targetAlpha = 1;
        this.targetScale = 1;
        canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.frameHandler, this)
    }
};
canvas.app.topMenu.view.ItemView.prototype.itemOutHandler = function(event) {
    if (this.items.indexOf(event.params) >= 0) {
        if (this.overedItem == event.params) {
            this.overedItem = null
        }
    }
};
canvas.app.topMenu.view.ItemView.prototype.startBlink = function() {
    this.lightDirection = true;
    this.light.alpha = 0;
    this.container.addChild(this.light);
    canvas.EventManager.addEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.blinkFrameHandler, this)
};
canvas.app.topMenu.view.ItemView.prototype.stopBlink = function() {
    this.container.removeIfExist(this.light);
    canvas.EventManager.removeEventListener(canvas.app.topMenu.Event.ENTER_FRAME, null, this.blinkFrameHandler, this)
};
canvas.app.topMenu.view.ItemView.prototype.blinkFrameHandler = function() {
    if (this.lightDirection) {
        this.light.alpha = Math.min(this.light.alpha + .04, 1);
        if (this.light.alpha == 1) this.lightDirection = false
    } else {
        this.light.alpha = Math.max(this.light.alpha - .04, 0);
        if (this.light.alpha == 0) this.lightDirection = true
    }
};
canvas.app.topMenu.view.ItemView.prototype.testBlink = function() {
    var model = canvas.app.topMenu.model;
    var result = false;
    if (model.blinkIds.indexOf(this.data.id) >= 0) {
        this.startBlink();
        result = true
    }
    if (this.items) {
        var len = this.items.length;
        var res = false;
        for (var i = 0; i < len; i++) {
            if (this.items[i].testBlink()) {
                res = true
            }
        }
        if (res && this.mode != 1) {
            this.clickHandler()
        }
    }
    return result
};