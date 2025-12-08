//lvl
//put fonts canvas.Const.FONT_TAHOMA_11_BOLD,
canvas.app.CanvasAvatar.prototype.init = function() {
    this.model = new canvas.app.avatar.Model(this.par);
    canvas.app.avatar.model = this.model;
    this.model.width = this.par.width;
    this.model.height = this.par.height;
    var fonts = [canvas.Const.FONT_IFLASH, canvas.Const.FONT_TAHOMA_11_BOLD, canvas.Const.FONT_TAHOMA_10_BOLD_SHARP, canvas.Const.FONT_TAHOMA_11_BOLD_STROKE_BEVEL_SHARP, canvas.Const.FONT_TAHOMA_10_STROKE];
    var resources = [
        ["ui", canvas.Config.ui + "main.json"]
    ];
    for (var i = 0; i < fonts.length; i++) resources.push(canvas.Config.fontsPath + fonts[i] + ".fnt");
    canvas.app.CanvasApp.prototype.init.call(this, resources)
};
//location
//redeclare navigateUrl
canvas.Functions.navigateToURLExtend = function(lnk, callback) {
    //console.log('okan');
    if(callback === undefined) callback = function() {};
    var Conf = canvas.app.location.model;
    _top().frames['main_frame'].frames['main_hidden'].location.href = lnk;
    Conf.inWaitingAnswer = false;
    /*
    $.ajax(lnk, {
        complete : function(data, statusCode){
            callback();
            Conf.inWaitingAnswer = false;
        },
        async: true,
    });
     */
};
canvas.app.location.View.prototype.navigateToUrl = function() {
    var curTime = Date.now();
    var Conf = canvas.app.location.model;
    if (curTime - Conf.reqTime > 1e4 || !Conf.inWaitingAnswer) {
        var target = Conf.waitRefresh ? Conf.MAIN_HIDDEN : "_self";
        if (!Conf.waitRefresh) {
           // console.log('not okan');
            canvas.Functions.navigateToURL(Conf.transitionLnk, target)
        } else {
            canvas.Functions.navigateToURLExtend(Conf.transitionLnk, function () {
                //_top().frames['main_frame'].updateSwf({'area':'', 'event':'', 'area_fight':''});
            });
        }
        this.main.hintManager.hide();
        Conf.reqTime = Date.now();
        Conf.inWaitingAnswer = true;
    }
};
//fight
//http to https!
canvas.app.battle.engine.Connection.prototype.initProxy = function() {
    if(canvas.app.battle.model.proxyHost.includes('http')) {
        this.proxyAddr = canvas.app.battle.model.proxyHost + "/";
    }else{
        this.proxyAddr = "https://" + canvas.app.battle.model.proxyHost + "/";
    }

    this.proxyLoader = new canvas.utils.URLRequest;
    canvas.EventManager.addEventListener(canvas.utils.URLRequestEvent.EVENT_COMPLETE, this.proxyLoader, this.proxyCompleteHandler, this);
    canvas.EventManager.addEventListener(canvas.utils.URLRequestEvent.EVENT_ERROR, this.proxyLoader, this.proxyErrorHandler, this);
    this.proxyReady = true;
    this.proxyQueue = [];
    this.startProxy()
};
//common
//only 13 px
canvas.app.firstBattle.Model.prototype.parse = function(par) {
    this.persParams = {};
    this.persParams[canvas.Const.GENDER.MALE] = {
        nick: this.ok(par.fight_user_male_nick) ? par.fight_user_male_nick : "Warrior",
        sk: this.ok(par.fight_user_male_skeleton) ? par.fight_user_male_skeleton : 1,
        parts: this.ok(par.fight_user_male_parts) ? par.fight_user_male_parts : "590330;;;,0,0;;;,0;;;,0;;;,404;;;,590325;;;,275;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,0;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,0;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,0;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,275;;;,271;;;,590325;;;,0;;;,271;;;,0;;;,0;;;,1186;;;,0;;;,0;;;,1368;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;",
        level: 5,
        curHp: 200,
        maxHp: 200,
        curMp: 100,
        maxMp: 100
    };
    this.persParams[canvas.Const.GENDER.FEMALE] = {
        nick: this.ok(par.fight_user_female_nick) ? par.fight_user_female_nick : "Warrior",
        sk: this.ok(par.fight_user_female_skeleton) ? par.fight_user_female_skeleton : 1,
        parts: this.ok(par.fight_user_female_parts) ? par.fight_user_female_parts : "262159;;;,0,0;;;,0;;;,0;;;,407;;;,262145;;;,274;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,0;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,0;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,0;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,274;;;,270;;;,262145;;;,0;;;,270;;;,0;;;,0;;;,1187;;;,0;;;,0;;;,1369;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;,0;;;",
        level: 5,
        curHp: 200,
        maxHp: 200,
        curMp: 100,
        maxMp: 100
    };
    this.enemyParams = {
        nick: this.ok(par.fight_enemy_nick) ? par.fight_enemy_nick : "Enemy",
        sk: this.ok(par.fight_enemy_skeleton) ? par.fight_enemy_skeleton : 1160,
        parts: this.ok(par.fight_enemy_parts) ? par.fight_enemy_parts : "",
        level: 6,
        curHp: 250,
        maxHp: 250,
        curMp: 150,
        maxMp: 150
    };
    this.link = this.ok(par.link) ? par.link : "https://" + location.host + "/"
};
//clock
canvas.modules.ChatClock = function(parent, ts, tm) {
    parent.style.backgroundImage = "url('images/tbl-main_chat-clock-bg.gif')";
    var img = document.createElement("img");
    parent.appendChild(img);
    img.setAttribute('id', 'main_chan_clock_btn');
    img.src = "images/tbl-main_chat-clock-btn2.png";
    img.title = "Настройка флудилки";
    img.style.cursor = "pointer";
    img.style.position = "relative";
    img.style.top = 12;
    img.onclick = this.clickHandler.bind(this);
    var span = document.createElement("span");
    parent.appendChild(span);
    span.style.position = "relative";
    span.style.color = "#f9dfa1";
    span.style.fontSize = 13;
    span.style.fontWeight = "bold";
    span.style.top = 13;
    span.style.left = 2;
    this.span = span;
    var d1 = new Date;
    if (tm == undefined) {
        tm = d1.getHours() + ":" + d1.getMinutes() + ":" + d1.getSeconds()
    }
    var ar = tm.split(":");
    var d2 = new Date;
    d2.setHours(parseInt(ar[0]));
    d2.setMinutes(parseInt(ar[1]));
    d2.setSeconds(parseInt(ar[2]));
    this.delta = d2.getTime() - d1.getTime();
    setInterval(this.timerHandler.bind(this), 1e4);
    this.timerHandler()
};
canvas.modules.ChatClock.prototype.clickHandler = function() {
    chat_change_time_zone()
};
canvas.modules.ChatClock.prototype.timerHandler = function() {
    var d = new Date(Date.now() + this.delta);
    this.span.innerText = canvas.Functions.setNumberLen(d.getHours()) + ":" + canvas.Functions.setNumberLen(d.getMinutes())
};
canvas.modules.ChatClock.prototype.getTime = function() {
    return Date.now() + this.delta
};
canvas.modules.ChatClock.prototype.time_shift = function(minutes) {
    this.delta += parseInt(minutes) * 6e4
};
//magic
canvas.app.CanvasMagic.prototype.init = function() {
    this.model = new canvas.app.magic.Model(this.par);
    this.model.width = this.par.width;
    this.model.height = this.par.height;
    this.model.type = this.par.type;
    canvas.app.magic.model = this.model;
    switch (this.model.type) {
        case canvas.app.magic.Const.TYPE_AURA:
            canvas.app.magic.modelAura = this.model;
            break;
        case canvas.app.magic.Const.TYPE_SHADOW:
            canvas.app.magic.modelShadow = this.model;
            break;
        default:
            canvas.app.magic.modelMagic = this.model
    }
    var fonts = [canvas.Const.FONT_IFLASH, canvas.Const.FONT_TAHOMA_9_STROKE];
    var resources = [
        ["ui", canvas.Config.ui + "magic.json"]
    ];
    for (var i = 0; i < fonts.length; i++) resources.push(canvas.Config.fontsPath + fonts[i] + ".fnt");
    canvas.app.CanvasApp.prototype.init.call(this, resources)
};
//path
canvas.app.location.Finder.prototype.find = function(start_loc_id, target_loc_id) {
    canvas.app.location.log("Finder: start = " + start_loc_id + ", target = " + target_loc_id, 10551296);
    var Conf = canvas.app.location.model;
    this.p1 = start_loc_id;
    this.p2 = target_loc_id;
    if (String(this.p1) == String(this.p2)) {
        return [0]
    }
    this.p2_tmp = this.p2;
    this.p1_tmp = this.p2_tmp;
    this.arr_tmp = [];
    this.arr_trash = [];
    this.cache_cont = {};
    this.arr_loc = [];
    this.arr_tmp.push(this.p1);
    this.arr_trash = this.arr_trash.concat(this.arr_tmp);
    var k = 0;
    var len = Conf.NUM_LOCATIONS;
    if (len <= 0) return [0];
    link: while (k < len) {
        k++;
        var arr2 = [];
        for (var i = this.arr_tmp.length; i--;) {
            var arr1 = [];
            arr1 = this.getNearLoc(this.arr_tmp[i]);
            this.clearArr(arr1, this.arr_trash);
            this.cache_cont[this.arr_tmp[i]] = arr1;
            if (this.seach(arr1, this.p2)) {
                break link
            }
            this.arr_trash = this.arr_trash.concat(arr1);
            arr2 = arr2.concat(arr1)
        }
        this.arr_tmp = [];
        this.arr_tmp = this.arr_tmp.concat(arr2);
        if (this.arr_tmp.length == 0) {
            return [-1]
        }
    }
    this.arr_loc.push(this.p1_tmp);
    var repeat_cnt_over = 30;
    while (this.p1_tmp != this.p1) {
        console.log(this.p1_tmp + " = " + this.p1);
        this.p2_tmp = this.getNextCacheLoc(this.p2_tmp);
        this.p1_tmp = this.p2_tmp;
        this.arr_loc.push(this.p1_tmp);
        repeat_cnt_over--;
        if(repeat_cnt_over <= 0) break;
    }
    return this.arr_loc
};



/*

canvas.app.battle.engine.MCmd.prototype.persActEffects = function() {
    var parser = canvas.app.battle.model.serverParser;
    var conf = canvas.app.battle.model;
    var baseLnk = this.baseLnk;
    var iPersId = parser.params[2].val;
    var cmdIndex = parser.params.pop().val;
    if (iPersId == conf.persId) {
        conf.persLastEffectsUpdateIndex = cmdIndex
    } else if (iPersId == conf.oppId) {
        conf.oppLastEffectsUpdateIndex = cmdIndex
    }
    var curArtId;
    var curPic;
    var curCnt;
    var curTitle;
    var curAnimData;
    var eetimeMax;
    var turnsLeft;
    var g;
    var increaser = 7;
    if (conf.persBafsFlag) {
        conf.persBafsFlag = false;
        var sp1 = "|";
        var sp2 = ";";
        var bafs_delta_time = conf.serverTimestamp - conf.clientTimestamp;
        var pers_bafs = iPersId.toString() + sp1 + bafs_delta_time.toString() + sp1;
        for (g = 3; g < parser.params.length; g += increaser) {
            curArtId = parser.params[g].val;
            curPic = canvas.Config.artifactsPath + parser.params[g + 3].val;
            curCnt = parser.params[g + 1].val;
            curTitle = parser.params[g + 2].val;
            eetimeMax = parser.params[g + 5].val;
            turnsLeft = parser.params[g + 6].val;
            canvas.app.battle.log(" // curArtId  " + curArtId, 10066329);
            canvas.app.battle.log(" // curPic    " + curPic, 10066329);
            canvas.app.battle.log(" // curCnt    " + curCnt, 10066329);
            canvas.app.battle.log(" // curTitle  " + curTitle, 10066329);
            canvas.app.battle.log(" // eetimeMax " + eetimeMax, 10066329);
            pers_bafs += curArtId.toString() + sp2 + curPic + sp2 + curCnt.toString() + sp2 + curTitle + sp2 + eetimeMax.toString() + sp2 + turnsLeft.toString() + sp1
        }
        baseLnk.sendData("mem", "pers_bafs@" + pers_bafs.substr(0))
    }
    var curList = [];
    var curListHash = {};
    var auraUpdate = false;
    for (g = 3; g < parser.params.length; g += increaser) {
        curArtId = parser.params[g].val;
        curPic = canvas.Config.artifactsPath + parser.params[g + 3].val;
        curCnt = parser.params[g + 1].val;
        curTitle = parser.params[g + 2].val;
        curAnimData = parser.params[g + 4].val;
        eetimeMax = parser.params[g + 5].val;
        turnsLeft = parser.params[g + 6].val;
        if (!auraUpdate) auraUpdate = conf.testCurrentAura(curArtId, iPersId);
        if (curListHash[curPic] != undefined) {
            curList[curListHash[curPic]].cnt += curCnt
        } else {
            var new_eff = {};
            new_eff.id = curArtId;
            new_eff.pic = curPic;
            new_eff.title = curTitle;
            new_eff.cnt = curCnt;
            new_eff.lnk = null;
            new_eff.animData = curAnimData;
            new_eff.eetimeMax = eetimeMax;
            new_eff.turnsLeft = turnsLeft;
            curListHash[curPic] = curList.push(new_eff) - 1
        }
    }
    console.log(curListHash);
    console.log(curList);
    if (auraUpdate) {
        baseLnk.updateAuras(iPersId)
    }
    var cur_eff_list;
    if (iPersId == conf.persId) {
        cur_eff_list = baseLnk.view.effectsP1
    } else if (iPersId == conf.oppId) {
        cur_eff_list = baseLnk.view.effectsP2
    } else {
        canvas.app.battle.log("       WARN: pers_id=" + iPersId.toString() + " not active", 16711680)
    }
    if (cur_eff_list) {
        cur_eff_list.initEffects(curList);
        var curPers = baseLnk.players[iPersId];
        if (curPers) {
            curPers.clearAdditionalEffects();
            var ss;
            var additional_effect;
            for (var k = 0; k < curList.length; k++) {
                ss = curList[k].animData.split("/");
                if (ss.length > 2) {
                    if (ss[2] != "null") {
                        additional_effect = ss[2];
                        curPers.showAdditionalEffects(conf.parser.parseAdditionalEffectsData(canvas.app.battle.Const.AEFF_ABSORB, additional_effect))
                    }
                }
            }
        } else {
            canvas.app.battle.log("       WARN: pers iPersId=" + iPersId + " is null", 10027008)
        }
    }
};

canvas.app.battle.engine.MCmd.prototype.useEffect = function() {
    var parser = canvas.app.battle.model.serverParser;
    var conf = canvas.app.battle.model;
    var baseLnk = this.baseLnk;
    canvas.app.battle.log("mCmd useEffect: paramsCount= " + parser.params.length);
    var effId = parser.params[2].val;
    var targetId = parser.params[3].val;
    var usageStatus = parser.params[4].val;
    console.log(parser.params);
    canvas.app.battle.log("        effId=" + String(effId) + " targetId=" + String(targetId) + " usageStatus=" + String(usageStatus), 10066329);
    var g;
    var slot_id = null;
    for (g in conf.spells) {
        if (conf.spells[g]["effId"] == effId) {
            slot_id = g;
            break
        }
    }
    if (!slot_id) {
        if (!conf.spellsBow[effId]) {
            canvas.app.battle.log("        send sig to items_left.swf", 10066329);
            baseLnk.sendData("items", "update_cnt@" + String(effId) + ",-1")
        } else {
            canvas.app.battle.log("        apply bow spells cooldown eff_id=" + effId, 10066329);
            baseLnk.view.bowPanel.confirmUseEffect(effId)
        }
    } else {
        canvas.app.battle.log("        apply spells cooldown slot_id=" + slot_id, 10066329);
        baseLnk.view.centerView.useSlotConfirmed(slot_id)
    }
    conf.useFlag(effId);
    baseLnk.updateAuras(conf.persId)
};

 */

//BotView
if(canvas.app.botView === undefined) canvas.app.botView = {};
canvas.app.botView.Const = {
    WIDTH: 321,
    HEIGHT: 225,
};
canvas.app.botView.Event = {
    ENTER_FRAME: "BotView.ENTER_FRAME",
};
canvas.app.botView.Model = function(par) {
    this.width = canvas.app.botView.Const.WIDTH;
    this.height = canvas.app.botView.Const.HEIGHT;
    this.parse(par)
};
canvas.app.botView.Model.prototype.parse = function(par) {
    this.sk = this.ok(par.sk) ? par.sk : 0;
    this.scale = {
        'x': this.ok(par.scale_x) ? par.scale_x : 0.65,
        'y': this.ok(par.scale_y) ? par.scale_y : 0.65
    };
    this.position = {
        'x': this.ok(par.pos_x) ? par.pos_x : (canvas.app.botView.Const.WIDTH / 2) - 50,
        'y': this.ok(par.pos_y) ? par.pos_y : 0,
    };
};
canvas.app.botView.Model.prototype.ok = function(value) {
    return value != undefined
};
canvas.app.CanvasBotView = function(par, parent) {
    canvas.app.CanvasApp.call(this, par, parent, true, 0, 0)
};
canvas.app.CanvasBotView.prototype = Object.create(canvas.app.CanvasApp.prototype);
canvas.app.CanvasBotView.prototype.init = function() {
    this.model = new canvas.app.botView.Model(this.par);
    canvas.app.botView.model = this.model;
    this.model.width = this.par.width;
    this.model.height = this.par.height;
    var fonts = [canvas.Const.FONT_IFLASH, canvas.Const.FONT_TAHOMA_9_STROKE, canvas.Const.FONT_TAHOMA_9, canvas.Const.FONT_TAHOMA_9_BOLD, canvas.Const.FONT_TAHOMA_11, canvas.Const.FONT_TAHOMA_11_BOLD, canvas.Const.FONT_TAHOMA_12_BOLD, canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD_STROKE_RED_WHITE, canvas.Const.FONT_TAHOMA_20_BOLD_STROKE];
    var resources = [
        ["locale", "{canvas.Config.langPath}locale.json"],
    ];
    for (var i = 0; i < fonts.length; i++) resources.push(canvas.Config.fontsPath + fonts[i] + ".fnt");
    canvas.app.CanvasApp.prototype.init.call(this, resources)
};
canvas.app.CanvasBotView.prototype.ready = function() {
    canvas.app.CanvasApp.prototype.ready.call(this);
    if(this.model.sk !== undefined && parseInt(this.model.sk) > 0) {
        this.addEnemy(this.model.sk);
    }
    this.fps = 20;
};
canvas.app.CanvasBotView.prototype.addEnemy = function (sk) {
    if(this.enemy !== undefined) this.root.removeIfExist(this.enemy);
    this.enemy = new canvas.animation.Bot(canvas.Config.botsPath + "img" + sk + "/img" + sk);
    this.root.addChild(this.enemy);
    this.enemy.scale.x = this.model.scale.x;
    this.enemy.scale.y = this.model.scale.y;
    this.enemy.position.set(this.model.position.x, this.model.position.y);
    this.enemy.frameEvent = canvas.app.botView.Event.ENTER_FRAME;
}
canvas.app.CanvasBotView.prototype.handlerEnterFrame = function() {
    canvas.EventManager.dispatchEvent(canvas.app.botView.Event.ENTER_FRAME);
    canvas.app.CanvasApp.prototype.handlerEnterFrame.call(this)
};
canvas.app.CanvasBotView.prototype.resize = function() {
    canvas.app.CanvasApp.prototype.resize.call(this)
};

if(canvas.app.mountView === undefined) canvas.app.mountView = {};
canvas.app.mountView.Const = {
    WIDTH: 300,
    HEIGHT: 180,
};
canvas.app.mountView.Event = {
    ENTER_FRAME: "MountView.ENTER_FRAME",
};
canvas.app.mountView.Model = function(par) {
    this.width = canvas.app.mountView.Const.WIDTH;
    this.height = canvas.app.mountView.Const.HEIGHT;
    this.parse(par)
};
canvas.app.mountView.Model.prototype.parse = function(par) {
    this.sk = this.ok(par.sk) ? par.sk : 0;
};
canvas.app.mountView.Model.prototype.ok = function(value) {
    return value != undefined
};
canvas.app.CanvasMountView = function(par, parent) {
    canvas.app.CanvasApp.call(this, par, parent, true, 0, 0);
    this.mountTop = new canvas.px.Container;
    this.mountBottom = new canvas.px.Container;
};
canvas.app.CanvasMountView.prototype = Object.create(canvas.app.CanvasApp.prototype);
canvas.app.CanvasMountView.prototype.init = function() {
    this.model = new canvas.app.mountView.Model(this.par);
    canvas.app.mountView.model = this.model;
    this.model.width = this.par.width;
    this.model.height = this.par.height;
    var fonts = [canvas.Const.FONT_IFLASH, canvas.Const.FONT_TAHOMA_9_STROKE, canvas.Const.FONT_TAHOMA_9, canvas.Const.FONT_TAHOMA_9_BOLD, canvas.Const.FONT_TAHOMA_11, canvas.Const.FONT_TAHOMA_11_BOLD, canvas.Const.FONT_TAHOMA_12_BOLD, canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD_STROKE_RED_WHITE, canvas.Const.FONT_TAHOMA_20_BOLD_STROKE];
    var resources = [
        ["locale", "{canvas.Config.langPath}locale.json"],
    ];
    for (var i = 0; i < fonts.length; i++) resources.push(canvas.Config.fontsPath + fonts[i] + ".fnt");
    canvas.app.CanvasApp.prototype.init.call(this, resources)
};
canvas.app.CanvasMountView.prototype.ready = function() {
    canvas.app.CanvasApp.prototype.ready.call(this);
    if(this.model.sk !== undefined && parseInt(this.model.sk) > 0) {
        this.addMount(this.model.sk);
    }
    this.fps = 20;
};
canvas.app.CanvasMountView.prototype.addMount = function (sk) {
    if(this.mountBottom !== undefined) this.root.removeIfExist(this.mountBottom);
    if(this.mountTop !== undefined) this.root.removeIfExist(this.mountTop);

    if (sk != undefined && sk) {
        sk = parseInt(sk);
        if (sk > 0) {
            var strBot = "mount_" + canvas.Functions.setNumberLen(sk, 2) + "_bot";
            var strTop = "mount_" + canvas.Functions.setNumberLen(sk, 2) + "_top";
            this.mountBottom = new canvas.px.MovieClip(canvas.Config.mountsPath + strBot + "/" + strBot, null, true);
            this.mountTop = new canvas.px.MovieClip(canvas.Config.mountsPath + strTop + "/" + strTop, null, true);
            this.mountTop.loop = true;
            this.mountBottom.loop = true
        }
    }
    if (sk > 0) {
        this.mountTop.position.set(0, 0);
        this.mountBottom.position.set(0, 0);
        this.root.position.set(20, -80);
        this.root.scale.x = 0.75;
        this.root.scale.y = 0.75;
        this.root.addChildAt(this.mountBottom, 0);
        this.root.addChild(this.mountTop);
    }

    this.mountTopCurrentFrame = 0;
    this.mountBottomCurrentFrame = 0;

    this.msk = new canvas.px.Graphics;
    this.msk.beginFill(16777215, 1);
    this.msk.drawCircle(175, 230, 110);
    this.msk.endFill();
    this.root.addChild(this.msk);
    this.root.mask = this.msk;

    if (this.mountTop.mcChildsCache["mc1"]) {
        this.mountTop.stop();
        this.mountTop.mcChildsCache["mc1"].loop = true;
        this.mountTop.mcChildsCache["mc1"].frameEvent = canvas.app.mountView.Event.ENTER_FRAME;
        this.mountTop.mcChildsCache["mc1"].play();
    }else{
        this.mountTop.frameEvent = canvas.app.mountView.Event.ENTER_FRAME;
    }
    if (this.mountBottom.mcChildsCache["mc1"]) {
        this.mountBottom.stop();
        this.mountBottom.mcChildsCache["mc1"].loop = true;
        this.mountBottom.mcChildsCache["mc1"].frameEvent = canvas.app.mountView.Event.ENTER_FRAME;
        this.mountBottom.mcChildsCache["mc1"].play();
    }else{
        this.mountBottom.frameEvent = canvas.app.mountView.Event.ENTER_FRAME;
    }
}
canvas.app.CanvasMountView.prototype.handlerEnterFrame = function() {
    canvas.EventManager.dispatchEvent(canvas.app.mountView.Event.ENTER_FRAME);
    canvas.app.CanvasApp.prototype.handlerEnterFrame.call(this);
};
canvas.app.CanvasMountView.prototype.render = function() {
    canvas.app.CanvasApp.prototype.render.call(this);
    if (this.mountTop.mcChildsCache["mc1"]) {
        this.mountTop.mcChildsCache["mc1"].gotoAndStop(this.mountTopCurrentFrame);
        this.mountTopCurrentFrame++;
        if(this.mountTopCurrentFrame >= this.mountTop.mcChildsCache["mc1"].totalFrames) this.mountTopCurrentFrame = 0;
    }
    if (this.mountBottom.mcChildsCache["mc1"]) {
        this.mountBottom.mcChildsCache["mc1"].gotoAndStop(this.mountBottomCurrentFrame);
        this.mountBottomCurrentFrame++;
        if(this.mountBottomCurrentFrame >= this.mountBottom.mcChildsCache["mc1"].totalFrames) this.mountBottomCurrentFrame = 0;
    }
};

canvas.app.CanvasMountView.prototype.resize = function() {
    canvas.app.CanvasApp.prototype.resize.call(this)
};


//PersView
if(canvas.app.persView === undefined) canvas.app.persView = {};
canvas.app.persView.Const = {
    WIDTH: 225,
    HEIGHT: 360,
};
canvas.app.persView.Event = {
    ENTER_FRAME: "PersView.ENTER_FRAME",
};
canvas.app.persView.Model = function(par) {
    this.width = canvas.app.persView.Const.WIDTH;
    this.height = canvas.app.persView.Const.HEIGHT;
    this.parse(par)
};
canvas.app.persView.Model.prototype.parse = function(data) {
    if (this.ok(data.sk)) this.mySk = data.sk;
    if (this.ok(data.gender)) this.gender = data.gender == "1" ? "M" : "F";
    if (this.ok(data.kind)) this.race = data.kind == "1" ? "hum" : "mag";
    if (this.ok(data.inv_time)) this.invTime = parseInt(data.inv_time) * 1e3;
    if (this.ok(data.parts)) this.parts = data.parts;
    if (this.ok(data.dead)) this.ghost = data.dead == "1";
};
canvas.app.persView.Model.prototype.ok = function(value) {
    return value != undefined
};
canvas.app.CanvasPersView = function(par, parent) {
    canvas.app.CanvasApp.call(this, par, parent, true, 0, 0)
};
canvas.app.CanvasPersView.prototype = Object.create(canvas.app.CanvasApp.prototype);
canvas.app.CanvasPersView.prototype.init = function() {
    this.model = new canvas.app.persView.Model(this.par);
    canvas.app.persView.model = this.model;
    this.model.width = this.par.width;
    this.model.height = this.par.height;

    this.model.scale = (this.par.scale !== undefined ? this.par.scale : 0.7);

    if(this.preloader) this.preloader.alpha = 1;

    var fonts = [canvas.Const.FONT_IFLASH, canvas.Const.FONT_TAHOMA_9_STROKE, canvas.Const.FONT_TAHOMA_9, canvas.Const.FONT_TAHOMA_9_BOLD, canvas.Const.FONT_TAHOMA_11, canvas.Const.FONT_TAHOMA_11_BOLD, canvas.Const.FONT_TAHOMA_12_BOLD, canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD_STROKE_RED_WHITE, canvas.Const.FONT_TAHOMA_20_BOLD_STROKE];
    var resources = [
        ["locale", "{canvas.Config.langPath}locale.json"],
    ];
    for (var i = 0; i < fonts.length; i++) resources.push(canvas.Config.fontsPath + fonts[i] + ".fnt");
    canvas.app.CanvasApp.prototype.init.call(this, resources)
};
canvas.app.CanvasPersView.prototype.update = function(par) {
    this.par = typeof par == "string" ? canvas.Functions.decodeUrlParams(par.replace(/\+/g, "%20")) : par;
    this.model = new canvas.app.persView.Model(this.par);
    canvas.app.persView.model = this.model;

    var conf = this.model;

    this.skContainer.removeIfExist(this.player);

    this.player = new canvas.animation.SkeletonInfo(conf.getSkPath(), new canvas.px.Point(0, 0), this.skContainer, undefined);
    this.player.frameEvent = canvas.app.persView.Event.ENTER_FRAME;
    this.player.loop = true;
    this.player.scale.x = conf.scale;
    this.player.scale.y = conf.scale;
    this.skContainer.addChild(this.player);
    //this.player.frameEvent = canvas.app.persView.Event.ENTER_FRAME;
    this.player.position.set(20, 20);
    //this.player.mask = msk;

    this.player.skeletonData = new canvas.data.battle.SkeletonData(conf.parts, conf.gender);
};
canvas.app.persView.Model.prototype.getSkPath = function() {
    return canvas.Config.skPath + this.gender + "/sk" + canvas.Functions.setNumberLen(String(this.mySk), 2) + "_UI"
};
canvas.app.CanvasPersView.prototype.ready = function() {
    canvas.app.CanvasApp.prototype.ready.call(this);
    var conf = this.model;

    this.playerContainer = this.root.addChild(new canvas.px.Container());
    var msk = new canvas.px.Graphics();
    msk.beginFill(0, 1);
    msk.drawCircle(44, 44, 34);
    msk.endFill();
    //this.playerContainer.addChild(msk);
    this.skContainer = new canvas.px.Container();

    this.player = new canvas.animation.SkeletonInfo(conf.getSkPath(), new canvas.px.Point(0, 0), this.skContainer, undefined);
    this.player.frameEvent = canvas.app.persView.Event.ENTER_FRAME;
    this.player.loop = true;
    this.player.scale.x = conf.scale;
    this.player.scale.y = conf.scale;
    this.skContainer.addChild(this.player);
    //this.player.frameEvent = canvas.app.persView.Event.ENTER_FRAME;
    this.player.position.set(20, 20);
    //this.player.mask = msk;

    this.player.skeletonData = new canvas.data.battle.SkeletonData(conf.parts, conf.gender);

    this.playerContainer.addChild(this.skContainer);

    this.fps = 15;
};
canvas.app.CanvasPersView.prototype.addEnemy = function (sk) {
    if(this.enemy !== undefined) this.root.removeIfExist(this.enemy);
    this.enemy = new canvas.animation.Bot(canvas.Config.botsPath + "img" + sk + "/img" + sk);
    this.root.addChild(this.enemy);
    this.enemy.scale.x = 0.65;
    this.enemy.scale.y = 0.65;
    this.enemy.position.set((canvas.app.persView.Const.WIDTH / 2) - 50, 0);
    this.enemy.frameEvent = canvas.app.persView.Event.ENTER_FRAME;
}
canvas.app.CanvasPersView.prototype.handlerEnterFrame = function() {
    canvas.EventManager.dispatchEvent(canvas.app.persView.Event.ENTER_FRAME);
    canvas.app.CanvasApp.prototype.handlerEnterFrame.call(this)
};
canvas.app.CanvasPersView.prototype.resize = function() {
    canvas.app.CanvasApp.prototype.resize.call(this)
};


function chatReceiveObject(objects) {
    for (var i in objects) {
        var fnc, fnc_name = 'controller_' + i.replace('|', '_');
        try {
            fnc = eval(fnc_name);
            if (typeof fnc == 'function') {
                var object = {};
                object[i] = objects[i];
                fnc.call(this, object);
            }
        } catch (e) {}
    }
}

function controller_cube_puton(obj) {
    swfObject('cube', obj);
}

function controller_cube_putoff(obj) {
    swfObject('cube', obj);
}

function controller_cube_craft(obj) {
    swfObject('cube', obj);
}

function controller_cube_use_recipe(obj) {
    swfObject('cube', obj);
}

canvas.app.avatar.Model.prototype.parse = function(data) {
    var money;
    var honorTable;
    var myHonor;
    var myHonorMin;
    var myHonorMax;
    var time = Date.now();
    if (this.ok(data.server_time) && this.ok(data.timezone)) this.serverTime.init(parseInt(data.server_time), parseInt(data.timezone) * .6);
    if (this.ok(data.nick)) this.login = data.nick;
    if (this.ok(data.exp)) this.exp = parseInt(data.exp);
    if (this.ok(data.expMax)) this.expMax = parseInt(data.expMax);
    if (this.ok(data.showMaxLevel)) this.showMaxLevel = parseInt(data.showMaxLevel);
    if (this.ok(data.showMaxRank)) this.showMaxRank = parseInt(data.showMaxRank);
    if (this.ok(data.rankHonorTable) && this.ok(data.honor) && this.ok(data.rankHonor)) {
        honorTable = data.rankHonorTable.split("|");
        myHonor = parseInt(data.honor);
        myHonorMin = parseInt(data.rankHonor);
        myHonorMax = 0;
        for (var i = 0; i < honorTable.length; i++) {
            if (honorTable[i] == myHonorMin) {
                if (honorTable[i + 1] != undefined) myHonorMax = honorTable[i + 1];
                else myHonorMax = honorTable[i]
            }
        }
        this.valor = myHonor - myHonorMin;
        this.valorMax = myHonorMax - myHonorMin
    }
    if (this.ok(data.work)) this.energy = parseInt(data.work);
    if (this.ok(data.workMax)) this.energyMax = parseInt(data.workMax);
    if (this.ok(data.hp) && this.ok(data.hpMax) && this.ok(data.hpT)) {
        this.hp = this.hpCur = parseInt(data.hp);
        this.hpMax = parseInt(data.hpMax);
        this.hpTime = parseInt(data.hpT);
        this.hpStartTime = time
    }
    if (this.ok(data.mp) && this.ok(data.mpMax) && this.ok(data.mpT)) {
        this.mp = this.mpCur = parseInt(data.mp);
        this.mpMax = parseInt(data.mpMax);
        this.mpTime = parseInt(data.mpT);
        this.mpStartTime = time
    }
    if (this.ok(data.estateExp) && this.ok(data.exp_limit) && this.ok(data.exp_speed)) {
        this.manorExp = this.manorExpCur = parseInt(data.estateExp);
        this.manorExpMax = parseInt(data.exp_limit);
        this.manorExpSpeed = parseFloat(data.exp_speed);
        this.manorExpStartTime = time
    }
    if (this.ok(data.estateHonor) && this.ok(data.honor_limit) && this.ok(data.honor_speed)) {
        this.manorValor = this.manorValorCur = parseInt(data.estateHonor);
        this.manorValorMax = parseInt(data.honor_limit);
        this.manorValorSpeed = parseFloat(data.honor_speed);
        this.manorValorStartTime = time
    }
    if (this.ok(data.lvl)) this.level = parseInt(data.lvl);
    if (this.ok(data.moneyGame)) {
        money = data.moneyGame;
        this.bronze = money.split(".")[1] == undefined ? 0 : parseInt(money.split(".")[1].substr(0, 2));
        money = (Math.floor(parseFloat(money)) * .01).toFixed(2);
        this.silver = money.split(".")[1] == undefined ? 0 : parseInt(money.split(".")[1].substr(0, 2));
        this.gold = parseInt(money.split(".")[0])
    }
    if (this.ok(data.premium_level)) this.premiumLevel = parseInt(data.premium_level);
    if (this.ok(data.hide_premium) && parseInt(data.hide_premium) == 1) this.premiumLevel = 0;
    if (this.ok(data.moneyGold)) this.crystal = parseFloat(data.moneyGold);
    if (this.ok(data.moneySilver)) this.rubin = parseFloat(data.moneySilver);
    if (this.ok(data.effect)) this.effects = parseInt(data.effect) < 0 ? 0 : parseInt(data.effect);
    if (this.ok(data.gender)) this.gender = data.gender == "1" ? "M" : "F";
    if (this.ok(data.kind)) this.race = data.kind == "1" ? "hum" : "mag";
    if (this.ok(data.inv_time)) this.invTime = parseInt(data.inv_time) * 1e3;
    if (this.ok(data.parts)) this.parts = data.parts;
    if (this.ok(data.dead)) this.ghost = data.dead == "1";
    if (this.ok(data.lang)) this.useTahoma = data.lang != "ru";
    if (this.ok(data.estateFlags)) this.manorFlags = parseInt(data.estateFlags);
    if (this.ok(data.avail_bots_kills_amount)) this.availBotsKills = parseInt(data.avail_bots_kills_amount);
    if (this.ok(data.display_currency)) this.currencyFlags = parseInt(data.display_currency);
    if (this.energyMax == 0) {
        if (this.mpMax == 0) this.currentView = 2;
        else this.currentView = 1
    } else {
        if (this.mpMax == 0) this.currentView = 3;
        else this.currentView = 0
    }
    if (this.localStorage == null) {
        this.localStorage = new canvas.utils.LocalStorage(canvas.Log.AVATAR);
        this.setEnergyMode(this.localStorage.get("energyMode") == undefined ? 0 : parseInt(this.localStorage.get("energyMode")), false)
    } else {
        this.setEnergyMode(this.energyMode)
    }
    if (!this.updateFlags[canvas.app.avatar.Const.UPDATE_FLAG_PERS]) this.updateFlags[canvas.app.avatar.Const.UPDATE_FLAG_PERS] = this.parts + this.gender != this.updateHash[canvas.app.avatar.Const.UPDATE_FLAG_PERS];

    var t = data;

    this.petId = this.ok(t.pet_id) ? parseInt(t.pet_id) : 0;
    this.lastPetId = this.ok(t.last_pet_id) ? parseInt(t.last_pet_id) : 0;
    this.ok(t.redbutton_global_time) && (this.redButtonGlobalTime = parseInt(t.redbutton_global_time));
    this.ok(t.redbutton_user_time) && (this.redButtonUserTime = parseInt(t.redbutton_user_time));
    this.ok(t.redbutton_limit) && (this.redButtonLimit = parseInt(t.redbutton_limit));
    this.ok(t.redbutton_used) && (this.redButtonUsed = parseInt(t.redbutton_used));
    this.ok(t.redbutton_hint) && (this.redButtonHint = t.redbutton_hint);
    this.ok(t.redbutton_picture) && (this.redButtonPicture = t.redbutton_picture);
    this.ok(t.redbutton_confirm) && (this.redButtonConfirm = 0 < parseInt(t.redbutton_confirm));

    this.updateHash[canvas.app.avatar.Const.UPDATE_FLAG_PERS] = this.parts + this.gender;

    if (this.ok(data.premium_ddate)) this.premium_ddate = data.premium_ddate;
};

canvas.app.avatar.View.prototype.updatePremium = function() {
    var conf = canvas.app.avatar.model;
    var date1 = conf.serverTime.getDate();
    var date2 = new Date(conf.premium_ddate);
    var premiumSeconds = date2.getTime() - date1.getTime();
    var str = canvas.Translator.getText(500);
    if (conf.premiumLevel > 0) {
        str += " " + conf.premiumLevel + " " + canvas.Translator.getText(2025) + " : " + canvas.Functions.formatDate(premiumSeconds, "00", 3, false, true, false)
    }
    this.premiumButton.level = conf.premiumLevel;
    if(isNaN(this.premiumButton.level)) this.premiumButton.level = 0;
    this.premiumHint.update(str)
};



canvas.app.sc = {view: {elements: {}},engine: {}};
canvas.app.sc.Const = {
    CAPTCHA_WIDTH: 3,
    CAPTCHA_HEIGHT: 2,
    CAPTCHA_CELL_SIZE: 128,
};
//SimpleCaptcha
canvas.app.sc.view.SimpleCaptcha = function(baseLnk) {
    this.baseLnk = baseLnk;

    Object.defineProperty(this, "_width", {
        get: function() {
            return this.width
        }
    });
    canvas.px.Window.call(this);

    this.doneButton = new canvas.app.view.MainButton;
    this.addChild(this.doneButton);
    this.doneButton.setSize(150);
    this.doneButton.setTitle("ТЕСТ!");
    this.doneButton.x = 35;
    this.descriptionField = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD, 7092268, 166, 224, "left");
    this.descriptionField.interactive = false;
    this.descriptionField.position.set(31, 80);
    this.addChild(this.descriptionField);
    this.captcha = new canvas.app.hunt.view.elements.Captcha;
    this.addChild(this.captcha);
    this.captcha.position.set(212, 42);

    if(canvas.isMobile()) {
        this.descriptionField2 = new canvas.ui.HtmlText(canvas.Const.FONT_TAHOMA_12, canvas.Const.FONT_TAHOMA_12_BOLD, 7092268, 166, 224, "left");
        this.descriptionField2.interactive = false;
        this.descriptionField2.position.set(31, 225);
        this.descriptionField2.text = 'Для сбора паззла на телефоне нажмите на элемент паззла и куда хотите его переставить.';
        this.addChild(this.descriptionField2);
    }

    this.aLoader = new canvas.utils.URLRequest;
    canvas.EventManager.addEventListener(canvas.utils.URLRequestEvent.EVENT_COMPLETE, this.aLoader, this.completeHandler, this);
    canvas.EventManager.addEventListener(canvas.utils.URLRequestEvent.EVENT_ERROR, this.aLoader, this.errorHandler, this);
    canvas.EventManager.addEventListener(canvas.ui.ButtonEvent.EVENT_CLICK, this.doneButton, this.doneClickHandler, this)
};
canvas.app.sc.view.SimpleCaptcha.prototype = Object.create(canvas.px.Window.prototype);
canvas.app.sc.view.SimpleCaptcha.prototype.doneClickHandler = function(event) {
    var url = "/main_iframe.php?mode=sc&action=minigame_check&sequence=" + this.captcha.getResultString();
    this.aLoader.load(url);
};
canvas.app.sc.view.SimpleCaptcha.prototype.show = function(event) {
    this._isActive = true;
    if (this.timer) clearInterval(this.timer);
    this.timer = setInterval(this.timerHandler.bind(this), 1e3);
    var url = "/main_iframe.php?mode=sc&action=minigame_get";
    canvas.EventManager.addEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.handlerImageLoaded, this);
    var texture = canvas.ResourceLoader.getTexture("captcha");
    if (texture) {
        texture.destroy(true);
        canvas.ResourceLoader.remove("captcha")
    }
    canvas.ResourceLoader.add([
        ["captcha", url, {
            xhrType: canvas.px.ResponseType.BLOB,
            loadType: canvas.px.LoadType.IMAGE
        }]
    ]);
    this.timerHandler();
    //canvas.app.manor.view.WindowManorBase.prototype.show.call(this, event)
};
canvas.app.sc.view.SimpleCaptcha.prototype.hide = function(event) {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.handlerImageLoaded, this);
    this._isActive = false;
    if (this.timer) clearInterval(this.timer);
    this.captcha.clear();
    //canvas.app.manor.view.WindowManorBase.prototype.hide.call(this, event)
};
canvas.app.sc.view.SimpleCaptcha.prototype.handlerImageLoaded = function() {
    canvas.EventManager.removeEventListener(canvas.ResourceLoader.EVENT_COMPLETE, null, this.handlerImageLoaded, this);
    this.initCaptcha()
};
canvas.app.sc.view.SimpleCaptcha.prototype.initCaptcha = function() {
    var sprite;
    var a = new Array;
    var b;
    var texture;
    if (this.images) {
        for (var k = 0; k < this.images.length; k++) {
            this.images[k].texture.destroy(true)
        }
    }
    this.images = new Array;
    for (var i = 0; i < canvas.app.sc.Const.CAPTCHA_HEIGHT; i++) {
        for (var j = 0; j < canvas.app.sc.Const.CAPTCHA_WIDTH; j++) {
            texture = new canvas.px.Texture(canvas.ResourceLoader.getTexture("captcha"), new canvas.px.Rectangle(j * canvas.app.sc.Const.CAPTCHA_CELL_SIZE, i * canvas.app.sc.Const.CAPTCHA_CELL_SIZE, canvas.app.sc.Const.CAPTCHA_CELL_SIZE, canvas.app.sc.Const.CAPTCHA_CELL_SIZE));
            sprite = new canvas.px.Sprite(texture);
            this.images.push(sprite);
            sprite.addChild(new canvas.px.Sprite(canvas.ResourceLoader.getImage("ui", "captcha_cell")))
        }
    }
    this.captcha.init(this.images, canvas.app.sc.Const.CAPTCHA_WIDTH, canvas.app.sc.Const.CAPTCHA_HEIGHT, canvas.app.sc.Const.CAPTCHA_CELL_SIZE, canvas.app.sc.Const.CAPTCHA_CELL_SIZE)
};
canvas.app.sc.view.SimpleCaptcha.prototype.updateTime = function() {
    var secondsLeft = 0;
    let confcaptchaFinishTime = 100;
    if (confcaptchaFinishTime > 0) {
        secondsLeft = Math.round((confcaptchaFinishTime - Date.now()) * .001)
    }
    if (secondsLeft < 1) {
        //canvas.utils.WindowsManager.instance.closeWindow(this);
        return
    }
    this.descriptionField.text = canvas.Translator.getText(432) + "<br/><br/>" + canvas.Translator.getText(433) + ": <b>" + secondsLeft + "</b> " + canvas.Translator.getText(434);
    this.doneButton.y = this.descriptionField.y + this.descriptionField.textHeight + 20
};
canvas.app.sc.view.SimpleCaptcha.prototype.timerHandler = function() {
    this.updateTime()
};
canvas.app.sc.view.SimpleCaptcha.prototype.completeHandler = function() {
    /*var doc = (new DOMParser).parseFromString(this.aLoader.request.responseText, "text/xml");
    if (doc.firstChild.attributes.msg) {
        this.baseLnk.popup.show_message(doc.firstChild.attributes.msg.value, 16711680);
    }
     */
    //canvas.utils.WindowsManager.instance.closeWindow(this);
};
canvas.app.sc.view.SimpleCaptcha.prototype.errorHandler = function() {
    //canvas.utils.WindowsManager.instance.closeWindow(this);
};
canvas.isWeakHardware = function () {
    if(canvas.isMobile()) { return true; }
    try{ if(top.gpuTier == 1) { return true; } } catch (e) {}
    try{ if(navigator.hardwareConcurrency > 0 && navigator.hardwareConcurrency <= 3) { return true; } }catch(e){}
    return false;
}

canvas.app.manor.view.controls.camp.MineTalismanRenderer.prototype.updateButton = function() {
    var Conf = canvas.app.manor.model;
    var dCampMission;
    var petView;
    var reward;
    var i, len = this.pets.length;
    if (this.selectedPets.length && this.selectedPets[0].mission) {
        for (i = 0; i < len; i++) {
            this.pets[i].interactiveChildren = false
        }
        this.button.enabled = this.selectedPets[0].missionIsComplete();
        this.button.setTitle(canvas.Translator.getText(192));
    } else {
        for (i = 0; i < len; i++) {
            this.pets[i].interactiveChildren = !this.pets[i].locked
        }
        this.button.enabled = false;
        this.button.setTitle(canvas.Translator.getText(2033));
        if (this.mission && this.selectedPets.length) {
            reward = this.mission.getRewardBySkill(this.petsCommonSkill);
            if (reward && Conf.campData.artifacts[this.mission.price1Id].count >= this.tmpPrice) {
                if (Math.floor(1.2 * this.petsCommonSkill / reward.bonusDifficulty) > 0) this.button.enabled = true
            }
        }

        window.ff = this;
        if(this.texterror === undefined) {
            this.texterror = new canvas.ui.Text(canvas.Const.FONT_TAHOMA_11, 7092753, 200, 18, "center");
        }
        this.texterror.position.set(625, 90);
        this.texterror.text = "Не хватает очков умений";

        this.container.removeIfExist(this.texterror);

        if(this.selectedPets.length) {
            if (!this.button.enabled) {
                this.container.addChild(this.texterror);
            } else {
                this.button.setTitle(canvas.Translator.getText(2033));
            }
        }else{
            this.button.setTitle(canvas.Translator.getText(2033));
        }
    }
};