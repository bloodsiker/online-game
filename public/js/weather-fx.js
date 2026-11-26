/* Визуальные эффекты погоды: дождь, снег, туман. Слой поверх страницы, клики не перехватывает. */
(function () {
    'use strict';

    // Время следующей вспышки хранится между страницами: при частых переходах по локациям
    // таймер не сбрасывается, и молния всё равно сверкает примерно раз в 10–25 секунд.
    var FLASH_KEY = 'weatherFxNextFlash';
    function scheduleFlash() {
        var at = Date.now() + 10000 + Math.random() * 15000;
        try { sessionStorage.setItem(FLASH_KEY, String(at)); } catch (e) {}
        return at;
    }
    function loadNextFlash() {
        try {
            var at = parseInt(sessionStorage.getItem(FLASH_KEY), 10);
            if (at > 0 && at < Date.now() + 26000) return at;
        } catch (e) {}
        return scheduleFlash();
    }

    function makeLayer(tag) {
        var el = document.createElement(tag);
        el.setAttribute('aria-hidden', 'true');
        el.style.cssText = 'position:fixed;left:0;top:0;width:100%;height:100%;pointer-events:none;z-index:900;';
        document.body.appendChild(el);
        return el;
    }

    // Спрайты снежинок: шесть лучей с боковыми веточками; тёмно-синяя подложка делает их видимыми на светлом фоне
    function makeFlakes() {
        var out = [];
        [[0.55, 0.8, 0.28, 0.2], [0.45, 0.75, 0.34, 0.24], [0.6, 0.85, 0.22, 0.16]].forEach(function (cfg) {
            var size = 64, c = document.createElement('canvas');
            c.width = c.height = size;
            var g = c.getContext('2d'), R = size / 2 - 3;
            g.translate(size / 2, size / 2);
            g.lineCap = 'round';
            [['rgba(60,90,150,0.45)', 4.5], ['rgba(255,255,255,0.95)', 2.2]].forEach(function (pass) {
                g.strokeStyle = pass[0];
                g.lineWidth = pass[1];
                g.beginPath();
                for (var k = 0; k < 6; k++) {
                    g.save();
                    g.rotate(k * Math.PI / 3);
                    g.moveTo(0, 0); g.lineTo(0, -R);
                    [cfg[0], cfg[1]].forEach(function (at, bi) {
                        var by = -R * at, bl = R * (bi === 0 ? cfg[2] + 0.12 : cfg[3]);
                        g.moveTo(0, by); g.lineTo(-bl * 0.8, by - bl * 0.7);
                        g.moveTo(0, by); g.lineTo(bl * 0.8, by - bl * 0.7);
                    });
                    g.restore();
                }
                g.stroke();
            });
            out.push(c);
        });
        return out;
    }

    // Заснеженные углы: статичные сугробы (внизу крупные, сверху небольшие), всегда в одном состоянии.
    function snowCorners() {
        var canvas = makeLayer('canvas');
        var g = canvas.getContext('2d');
        var seed = 7;
        function rnd() { seed = (seed * 16807) % 2147483647; return (seed - 1) / 2147483646; }

        function drift(cx, cy, dirX, dirY, size, count) {
            // Пуфы выстраиваются от угла к центру экрана; чем дальше от угла, тем меньше и ниже
            var puffs = [];
            for (var i = 0; i < count; i++) {
                var t = Math.pow(rnd(), 0.8);
                var along = t * size * 1.5;
                var depth = (1 - t) * size * 0.75 * (0.6 + rnd() * 0.5);
                puffs.push({
                    x: cx + dirX * along + dirX * (-rnd() * size * 0.1),
                    y: cy + dirY * depth * 0.55 + dirY * (-rnd() * size * 0.05),
                    r: size * (0.1 + rnd() * 0.16) * (1.15 - t * 0.6)
                });
            }
            puffs.sort(function (a, b) { return Math.abs(b.x - cx) - Math.abs(a.x - cx); });
            function paint(dx, dy, rgb, alpha) {
                puffs.forEach(function (pf) {
                    var grad = g.createRadialGradient(pf.x + dx, pf.y + dy, pf.r * 0.2, pf.x + dx, pf.y + dy, pf.r);
                    grad.addColorStop(0, 'rgba(' + rgb + ',' + alpha + ')');
                    grad.addColorStop(0.75, 'rgba(' + rgb + ',' + (alpha * 0.85).toFixed(3) + ')');
                    grad.addColorStop(1, 'rgba(' + rgb + ',0)');
                    g.fillStyle = grad;
                    g.beginPath();
                    g.arc(pf.x + dx, pf.y + dy, pf.r, 0, 6.2832);
                    g.fill();
                });
            }
            paint(dirX * size * 0.02, dirY * size * 0.045, '95,120,170', 0.13);   // тень на стороне, обращённой к центру
            paint(0, 0, '246,249,255', 0.38);
            paint(-dirX * size * 0.015, -dirY * size * 0.03, '255,255,255', 0.2);     // светлая вершина у угла
        }

        function draw() {
            var w = canvas.width = window.innerWidth, h = canvas.height = window.innerHeight;
            seed = 7;
            var base = Math.min(w, h);
            drift(0, h, 1, -1, base * 0.26, 22);
            drift(w, h, -1, -1, base * 0.26, 22);
            drift(0, 0, 1, 1, base * 0.13, 10);
            drift(w, 0, -1, 1, base * 0.13, 10);
        }
        window.addEventListener('resize', draw);
        draw();
    }

    // Спрайт стеклянной капли: прозрачная середина, контур, кромка и блики. Рисуется один раз.
    var DROP_REF_W = 3, DROP_REF_L = 17, DROP_SCALE = 4;
    var dropSprite = (function () {
        var ww = DROP_REF_W, hh = DROP_REF_L / 2, sc = DROP_SCALE;
        var c = document.createElement('canvas');
        c.width = Math.ceil((ww * 2 + 4) * sc); c.height = Math.ceil((DROP_REF_L + 4) * sc);
        var g = c.getContext('2d');
        g.scale(sc, sc);
        g.translate(ww + 2, hh + 2);
        g.beginPath();
        g.moveTo(0, -hh);
        g.bezierCurveTo(ww * 0.5, -hh * 0.3, ww, hh * 0.2, ww, hh * 0.5);
        g.arc(0, hh * 0.5, ww, 0, Math.PI, false);
        g.bezierCurveTo(-ww, hh * 0.2, -ww * 0.5, -hh * 0.3, 0, -hh);
        g.closePath();
        g.fillStyle = 'rgba(120,160,220,0.07)';
        g.fill();
        g.strokeStyle = 'rgba(50,85,150,0.35)';
        g.lineWidth = 1;
        g.stroke();
        g.beginPath();
        g.arc(0, hh * 0.5, ww * 0.75, 0.2, 1.9, false);
        g.strokeStyle = 'rgba(40,70,130,0.3)';
        g.stroke();
        g.beginPath();
        g.ellipse(-ww * 0.4, hh * 0.35, ww * 0.18, hh * 0.2, -0.2, 0, 6.2832);
        g.fillStyle = 'rgba(255,255,255,0.75)';
        g.fill();
        g.beginPath();
        g.arc(ww * 0.4, hh * 0.75, ww * 0.12, 0, 6.2832);
        g.fillStyle = 'rgba(255,255,255,0.45)';
        g.fill();
        return c;
    })();

    function particles(type) {
        var canvas = makeLayer('canvas');
        var ctx = canvas.getContext('2d');
        var w = 0, h = 0, dpr = Math.min(window.devicePixelRatio || 1, 2);
        var list = [], raf = 0, flash = 0, flashes = 0, bolt = null, nextFlash = loadNextFlash();
        var rain = type === 'storm';
        var flakes = rain ? [] : makeFlakes();

        function resize() {
            w = window.innerWidth; h = window.innerHeight;
            canvas.width = w * dpr; canvas.height = h * dpr;
            ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
        }
        function spawn(randomY) {
            return rain
                ? { x: Math.random() * (w + 100), y: randomY ? Math.random() * h : -20, v: 1.6 + Math.random() * 1.2, l: 9 + Math.random() * 6, w: 1.8 + Math.random() * 1.1 }
                : { x: Math.random() * w, y: randomY ? Math.random() * h : -20, v: 0.25 + Math.random() * 0.5, size: 12 + Math.random() * 14, img: Math.floor(Math.random() * flakes.length), p: Math.random() * 6.28, s: 0.2 + Math.random() * 0.4, a: Math.random() * 6.28, spin: (Math.random() - 0.5) * 0.02 };
        }
        function init() {
            resize();
            var n = Math.max(40, Math.min(rain ? 120 : 100, Math.round(w * h / (rain ? 8000 : 10000))));
            list = [];
            for (var i = 0; i < n; i++) list.push(spawn(true));
        }
        // Молния: ствол толстый сверху и истончается к концу; ветки отходят толщиной в долю ствола
        // в этой точке и тоньше с каждым звеном. Точки хранят свою толщину: [x, y, width].
        function makeBolt() {
            var lines = [];
            function branch(x, y, dirX, steps, wd, depth) {
                var pts = [[x, y, wd]];
                for (var k = 0; k < steps; k++) {
                    y += 10 + Math.random() * 20;
                    x += dirX * (5 + Math.random() * 22) + (Math.random() - 0.5) * 14;
                    var cur = wd * (1 - (k + 1) / (steps + 1)) + 0.4;
                    pts.push([x, y, cur]);
                    if (depth < 1 && k > 0 && Math.random() < 0.22) {
                        branch(x, y, Math.random() < 0.5 ? -1 : 1, 2 + Math.floor(Math.random() * 3), cur * 0.6, depth + 1);
                    }
                }
                lines.push({ pts: pts });
            }
            var x = w * (0.15 + Math.random() * 0.7), y = 0, endY = h * (0.6 + Math.random() * 0.35);
            var main = [[x, y, 7]];
            while (y < endY) {
                y += 12 + Math.random() * 24;
                x += (Math.random() - 0.5) * (Math.random() < 0.15 ? 120 : 46);   // изредка резкий излом
                var prog = Math.min(1, y / endY);
                var wd = 7 * Math.pow(1 - prog, 0.9) + 1.3;
                main.push([x, y, wd]);
                if (Math.random() < 0.28 && prog < 0.9) {
                    branch(x, y, Math.random() < 0.5 ? -1 : 1, 4 + Math.floor(Math.random() * 5), wd * 0.55, 0);
                }
            }
            lines.unshift({ pts: main });
            return { lines: lines, endY: y, reveal: 0 };
        }
        function drawBolt(alpha) {
            if (!bolt) return;
            // молния «вырастает» сверху вниз за ~9 кадров: рисуем только то, что выше линии роста
            bolt.reveal = Math.min(bolt.endY + 40, bolt.reveal + bolt.endY / 9);
            var ry = bolt.reveal;
            ctx.save();
            ctx.lineJoin = 'round';
            ctx.lineCap = 'round';
            // слои от широкого бледного свечения к тонкому белому ядру — без видимой границы цветов;
            // размытие shadowBlur не используется (оно тяжёлое), свечение даёт сумма широких прозрачных слоёв
            [[7, '120,130,240', 0.05], [5, '125,140,245', 0.08], [3.6, '150,165,250', 0.12],
             [2.5, '190,200,255', 0.22], [1.6, '230,236,255', 0.45], [0.9, '255,255,255', 1]].forEach(function (pass) {
                ctx.strokeStyle = 'rgba(' + pass[1] + ',' + (pass[2] * alpha).toFixed(3) + ')';
                bolt.lines.forEach(function (line) {
                    for (var i = 1; i < line.pts.length; i++) {
                        var p0 = line.pts[i - 1], p1 = line.pts[i];
                        if (p0[1] > ry) break;
                        var x1 = p1[0], y1 = p1[1];
                        if (y1 > ry) { var k = (ry - p0[1]) / (y1 - p0[1]); x1 = p0[0] + (x1 - p0[0]) * k; y1 = ry; }
                        ctx.lineWidth = Math.max(0.6, p1[2] * pass[0]);
                        ctx.beginPath();
                        ctx.moveTo(p0[0], p0[1]);
                        ctx.lineTo(x1, y1);
                        ctx.stroke();
                    }
                });
            });
            ctx.restore();
        }
        function frame(t) {
            if (document.hidden) { raf = requestAnimationFrame(frame); return; }
            ctx.clearRect(0, 0, w, h);
            if (rain) {
                if (Date.now() > nextFlash) { flash = 1; flashes = 1; bolt = makeBolt(); nextFlash = scheduleFlash(); }
                if (flash > 0) {
                    ctx.fillStyle = 'rgba(245,248,255,' + (0.6 * flash).toFixed(3) + ')';
                    ctx.fillRect(0, 0, w, h);
                    drawBolt(flash);
                    flash -= 0.04;
                    // второе быстрое мигание, как у настоящей молнии
                    if (flashes > 0 && flash < 0.6) { flash = 0.85 + Math.random() * 0.15; flashes -= 1; }
                }
                // Стеклянные капли: рисуются из готового спрайта (форма и блики нарисованы один раз)
                for (var i = 0; i < list.length; i++) {
                    var d = list[i];
                    ctx.save();
                    ctx.translate(d.x, d.y);
                    ctx.rotate(0.12);
                    var dk = d.l / DROP_REF_L / DROP_SCALE;
                    ctx.drawImage(dropSprite, -dropSprite.width * dk / 2, -dropSprite.height * dk / 2, dropSprite.width * dk, dropSprite.height * dk);
                    ctx.restore();
                    d.y += d.v; d.x -= d.v * 0.12;
                    if (d.y > h + 20) list[i] = spawn(false);
                }
            } else {
                for (var j = 0; j < list.length; j++) {
                    var f = list[j];
                    f.p += 0.012; f.a += f.spin;
                    f.y += f.v; f.x += Math.sin(f.p) * f.s + 0.25;
                    ctx.save();
                    ctx.translate(f.x, f.y);
                    ctx.rotate(f.a);
                    ctx.drawImage(flakes[f.img], -f.size / 2, -f.size / 2, f.size, f.size);
                    ctx.restore();
                    if (f.y > h + 20 || f.x > w + 20) list[j] = spawn(false);
                }
            }
            raf = requestAnimationFrame(frame);
        }
        window.addEventListener('resize', init);
        init();
        raf = requestAnimationFrame(frame);
    }

    // Туман: клубящиеся облака из множества мягких «пуфов». Спрайт облака рисуется один раз
    // (тёмная подложка + светлые пуфы сверху — видна объёмная структура), дальше слои плывут с разной скоростью.
    function cloudSprite(width, height) {
        var c = document.createElement('canvas');
        c.width = width; c.height = height;
        var g = c.getContext('2d');
        var puffs = [];
        var count = Math.round(width / 16);
        for (var i = 0; i < count; i++) {
            var u = Math.random();
            var r = height * (0.09 + Math.random() * 0.15);
            var sx = 1.4 + Math.random() * 1.6;
            var span = height * 0.5 * Math.sin(u * Math.PI);
            puffs.push({
                x: r * sx + u * (width - 2 * r * sx),
                y: Math.max(r * 1.1, Math.min(height - r * 1.1, height * 0.5 + (Math.random() - 0.5) * span)),
                r: r, sx: sx
            });
        }
        function paint(dy, rgb, alpha) {
            puffs.forEach(function (pf) {
                var grad = g.createRadialGradient(0, 0, pf.r * 0.1, 0, 0, pf.r);
                grad.addColorStop(0, 'rgba(' + rgb + ',' + alpha + ')');
                grad.addColorStop(0.55, 'rgba(' + rgb + ',' + (alpha * 0.5).toFixed(3) + ')');
                grad.addColorStop(1, 'rgba(' + rgb + ',0)');
                g.save();
                g.translate(pf.x, pf.y + dy);
                g.scale(pf.sx, 1);
                g.fillStyle = grad;
                g.beginPath();
                g.arc(0, 0, pf.r, 0, 6.2832);
                g.fill();
                g.restore();
            });
        }
        paint(height * 0.05, '115,130,150', 0.26);
        paint(0, '248,250,253', 0.4);
        paint(-height * 0.04, '255,255,255', 0.22);
        return c;
    }

    function fog() {
        var canvas = makeLayer('canvas');
        var ctx = canvas.getContext('2d');
        var w = 0, h = 0, layers = [], last = 0;

        function build() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            layers = [];
            var n = 7;
            for (var i = 0; i < n; i++) {
                var sw = Math.round(w * (0.9 + Math.random() * 0.7));
                var sh = Math.round(h * (0.28 + Math.random() * 0.2));
                layers.push({
                    img: cloudSprite(sw, sh),
                    x: Math.random() * (w + sw) - sw,
                    y: h * (i / n) + (Math.random() - 0.3) * h * 0.1,
                    v: (2 + Math.random() * 4) * (i % 2 ? 1 : -1),
                    a: 0.29 + Math.random() * 0.17
                });
            }
        }
        function frame(t) {
            var dt = Math.min(0.1, (t - last) / 1000 || 0);
            last = t;
            if (!document.hidden) {
                ctx.clearRect(0, 0, w, h);
                layers.forEach(function (l) {
                    var sw = l.img.width;
                    l.x += l.v * dt;
                    if (l.x > w) l.x = -sw;
                    if (l.x < -sw) l.x = w;
                    ctx.globalAlpha = l.a;
                    ctx.drawImage(l.img, l.x, l.y);
                });
                ctx.globalAlpha = 1;
            }
            requestAnimationFrame(frame);
        }
        window.addEventListener('resize', build);
        build();
        requestAnimationFrame(frame);
    }

    // Ветер: светлые штрихи и летящие листья. Попутный ветер спокойный, ураган — быстрый и густой.
    function leafSprites() {
        return ['#6f9a45', '#a8822e', '#b4602e', '#7d8f3a'].map(function (color) {
            var c = document.createElement('canvas');
            c.width = 40; c.height = 24;
            var g = c.getContext('2d');
            g.translate(20, 12);
            g.beginPath();
            g.moveTo(-16, 0);
            g.quadraticCurveTo(-4, -11, 16, 0);
            g.quadraticCurveTo(-4, 11, -16, 0);
            g.closePath();
            g.fillStyle = color;
            g.globalAlpha = 0.85;
            g.fill();
            g.globalAlpha = 1;
            g.strokeStyle = 'rgba(60,45,20,0.55)';
            g.lineWidth = 1;
            g.stroke();
            g.beginPath();
            g.moveTo(-16, 0); g.lineTo(13, 0);
            g.stroke();
            return c;
        });
    }

    // Попутный ветер: плавные волнистые потоки воздуха в три слоя глубины (дальние бледные и медленные,
    // ближние толще и ярче), редкие завитки и листья, летящие по волнам потока.
    function breeze(hurricane) {
        var canvas = makeLayer('canvas');
        var ctx = canvas.getContext('2d');
        var w = 0, h = 0, ribbons = [], leaves = [], vortices = [], sprites = leafSprites();
        var K = hurricane ? 2.1 : 1;   // множитель скорости
        var LAYERS = [
            { thick: 1.6, alpha: 0.2, speed: 0.9, amp: 14, len: 320 },   // дальний
            { thick: 2.8, alpha: 0.3, speed: 1.6, amp: 22, len: 420 },    // средний
            { thick: 4.4, alpha: 0.42, speed: 2.4, amp: 30, len: 520 }   // ближний
        ];

        function newRibbon(layer, randomX) {
            var L = LAYERS[layer];
            var len = L.len * (0.7 + Math.random() * 0.7);
            return {
                layer: layer,
                head: randomX ? Math.random() * (w + len) : -20,
                y0: h * (0.05 + Math.random() * 0.9),
                len: len,
                amp: L.amp * (hurricane ? 1.4 : 1) * (0.6 + Math.random() * 0.8),
                wl: 160 + Math.random() * 220,
                ph: Math.random() * 6.28,
                dph: 0.006 + Math.random() * 0.01,
                v: L.speed * K * (0.8 + Math.random() * 0.5),
                drift: (Math.random() - 0.3) * 0.08
            };
        }
        function newLeaf(randomX) {
            return {
                x: randomX ? Math.random() * w : -30,
                base: h * (0.1 + Math.random() * 0.8),
                amp: 14 + Math.random() * 26,
                wl: 180 + Math.random() * 160,
                ph: Math.random() * 6.28,
                dph: 0.01 + Math.random() * 0.01,
                v: (1.4 + Math.random() * 1.2) * K,
                size: 12 + Math.random() * 10,
                img: Math.floor(Math.random() * sprites.length),
                tumble: Math.random() * 6.28,
                tspin: (0.03 + Math.random() * 0.04) * (hurricane ? 2 : 1)
            };
        }
        // Смерч: воронка из колец, расширяющаяся кверху, с перспективой (ближняя сторона крупнее и ярче).
        function newTornado(far) {
            var H = h * (far ? 0.38 + Math.random() * 0.1 : 0.62 + Math.random() * 0.14);
            var Rt = H * (0.25 + Math.random() * 0.07);
            var parts = [], n = far ? 55 : 95;
            for (var i = 0; i < n; i++) {
                parts.push({
                    t: Math.random(), a: Math.random() * 6.28,
                    rise: 0.0006 + Math.random() * 0.0014,
                    size: far ? 7 : 11 + Math.random() * 8,
                    img: Math.floor(Math.random() * sprites.length),
                    tilt: Math.random() * 6.28,
                    off: 0.3 + Math.random() * 0.85, jit: Math.random() * 6.28,
                    kind: i % 4 === 0 ? 0 : (i % 4 === 1 ? 1 : 2),   // 0 — лист, 1 — тёмный обломок, 2 — светлая пыль
                    dot: 1 + Math.random() * 1.6
                });
            }
            return {
                far: far,
                x: -Rt, gy: h * (far ? 0.78 : 0.94), H: H, Rt: Rt, Rb: Math.max(9, Rt * 0.2),
                vx: (far ? 0.35 : 0.6) + Math.random() * 0.4,
                sway: Rt * (0.5 + Math.random() * 0.4), ph: Math.random() * 6.28,
                life: 0, max: 1100 + Math.random() * 600, parts: parts
            };
        }
        function seedTornado(far) {
            var t = newTornado(far);
            t.x = w * (0.15 + Math.random() * 0.7);
            t.life = t.max * (0.2 + Math.random() * 0.3);    // на старте страницы смерч уже в разгаре
            return t;
        }
        function init() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            var area = w * h;
            var nr = Math.max(9, Math.round(area / (hurricane ? 70000 : 42000)));
            ribbons = []; leaves = [];
            for (var i = 0; i < nr; i++) ribbons.push(newRibbon(i % 3, true));
            ribbons.sort(function (p, q) { return p.layer - q.layer; });
            var nl = Math.max(4, Math.round(area / (hurricane ? 45000 : 110000)));
            vortices = [];
            if (hurricane) { vortices.push(seedTornado(true)); vortices.push(seedTornado(false)); }
            for (var k = 0; k < nl; k++) leaves.push(newLeaf(true));
        }

        function ribbonY(rb, x) {
            // амплитуда плавно нарастает к голове потока, как у настоящей струи
            return rb.y0 + Math.sin(x / rb.wl * 6.28 + rb.ph) * rb.amp + Math.sin(x / (rb.wl * 0.43) + rb.ph * 1.7) * rb.amp * 0.25;
        }

        function frame() {
            if (!document.hidden) {
                ctx.clearRect(0, 0, w, h);
                ctx.lineCap = 'butt';
                ribbons.forEach(function (rb, idx) {
                    var L = LAYERS[rb.layer], step = 10, n = Math.ceil(rb.len / step), px = 0, py = 0;
                    rb.ph += rb.dph;
                    rb.head += rb.v;
                    rb.y0 += rb.drift;
                    (hurricane ? [[0, 0, 1, 'rgba(255,255,255,']] : [[0, 1.0, 0.18, 'rgba(90,105,130,'], [0, 0, 1, 'rgba(255,255,255,']]).forEach(function (pass, pi) {
                        px = 0; py = 0;
                        for (var k = 0; k <= n; k++) {
                            var tt = k / n;
                            var x = rb.head - tt * rb.len;
                            var y = ribbonY(rb, x) + (pi === 0 ? 2.5 : 0);
                            if (k > 0) {
                                var taper = Math.pow(Math.sin(tt * Math.PI), 0.8);
                                ctx.lineWidth = L.thick * (pi === 0 ? 1.5 : 1) * taper + 0.2;
                                ctx.strokeStyle = pass[3] + (L.alpha * pass[2] * taper).toFixed(3) + ')';
                                ctx.beginPath();
                                ctx.moveTo(px, py);
                                ctx.lineTo(x, y);
                                ctx.stroke();
                            }
                            px = x; py = y;
                        }
                    });
                    if (rb.head - rb.len > w + 20) ribbons[idx] = newRibbon(rb.layer, false);
                });

                vortices.forEach(function (tn, ti) {
                    tn.x += tn.vx; tn.ph += 0.018; tn.life += 1;
                    var env = Math.sin(Math.min(1, tn.life / tn.max) * Math.PI);
                    var vis = Math.min(1, env * 3) * (tn.far ? 0.75 : 1);
                    var F = 0.3;                                            // сплюснутость колец (перспектива)
                    function cxAt(t) { return tn.x + Math.sin(tn.ph + t * 2.6) * tn.sway * t + Math.sin(t * 7 + tn.ph * 1.7) * tn.Rt * 0.1 * t; }
                    function rAt(t) { return (tn.Rb + (tn.Rt - tn.Rb) * Math.pow(t, 1.5)) * (1 + 0.1 * Math.sin(t * 9 + tn.ph * 2.3) + 0.07 * Math.sin(t * 17 - tn.ph * 3.1)); }
                    function fadeAt(t) { return Math.min(1, Math.max(0, (1 - t) / 0.32)) * Math.min(1, t / 0.05 + 0.15); }   // верх растворяется, без ровного обреза
                    function yAt(t) { return tn.gy - t * tn.H; }

                    // облако пыли у основания
                    for (var d = 0; d < 4; d++) {
                        var dx = cxAt(0) + (d - 1.5) * tn.Rt * 0.35, dr = tn.Rt * (0.45 + 0.12 * Math.sin(tn.ph * 2 + d));
                        var dg = ctx.createRadialGradient(dx, tn.gy, 0, dx, tn.gy, dr);
                        dg.addColorStop(0, 'rgba(185,155,115,' + (0.34 * vis).toFixed(3) + ')');
                        dg.addColorStop(1, 'rgba(185,155,115,0)');
                        ctx.fillStyle = dg;
                        ctx.save();
                        ctx.translate(dx, tn.gy);
                        ctx.scale(1.6, 0.5);
                        ctx.translate(-dx, -tn.gy);
                        ctx.beginPath();
                        ctx.arc(dx, tn.gy, dr, 0, 6.2832);
                        ctx.fill();
                        ctx.restore();
                    }

                    function ring(t, from, to, alpha, dash) {
                        ctx.beginPath();
                        ctx.ellipse(cxAt(t), yAt(t), rAt(t), rAt(t) * F, Math.sin(t * 5 + tn.ph) * 0.09, from, to);
                        ctx.strokeStyle = 'rgba(245,238,225,' + (alpha * vis * fadeAt(t)).toFixed(3) + ')';
                        ctx.lineWidth = 1.2 + t * 2.2;
                        if (dash) { ctx.setLineDash([rAt(t) * 0.55, rAt(t) * 0.4]); ctx.lineDashOffset = -tn.ph * rAt(t) * 1.6 * (1.3 - t * 0.5) * (0.7 + 0.6 * Math.abs(Math.sin(t * 13))); }
                        ctx.stroke();
                        if (dash) ctx.setLineDash([]);
                    }
                    function particle(pt, front) {
                        var rad = rAt(pt.t) * pt.off * (1 + 0.1 * Math.sin(pt.a * 3 + pt.jit));
                        var x = cxAt(pt.t) + Math.cos(pt.a) * rad, y = yAt(pt.t) + Math.sin(pt.a) * rad * F;
                        var depth = 0.72 + 0.28 * Math.sin(pt.a);
                        var al = (front ? 0.92 : 0.45) * vis * fadeAt(pt.t);
                        if (pt.kind === 0) {
                            var sz = pt.size * depth * (tn.far ? 0.8 : 1);
                            ctx.save();
                            ctx.globalAlpha = al;
                            ctx.translate(x, y);
                            ctx.rotate(pt.a + 1.57 + Math.sin(pt.tilt) * 0.6);
                            ctx.scale(1, 0.55 + 0.45 * Math.cos(pt.tilt * 1.3));
                            ctx.drawImage(sprites[pt.img], -sz / 2, -sz * 0.3, sz, sz * 0.6);
                            ctx.restore();
                        } else {
                            ctx.fillStyle = pt.kind === 1 ? 'rgba(95,75,55,' + (al * 0.85).toFixed(3) + ')' : 'rgba(240,228,205,' + (al * 0.9).toFixed(3) + ')';
                            ctx.beginPath();
                            ctx.arc(x, y, pt.dot * depth, 0, 6.2832);
                            ctx.fill();
                        }
                    }

                    // клубящаяся шапка наверху: воронка уходит в облако, а не обрывается
                    for (var cp = 0; cp < 6; cp++) {
                        var capx = cxAt(0.95) + (cp - 2.5) * tn.Rt * 0.5 + Math.sin(tn.ph * 1.3 + cp * 1.7) * tn.Rt * 0.18;
                        var capy = yAt(0.97) - Math.abs(Math.sin(cp * 2.1 + tn.ph * 0.9)) * tn.Rt * 0.25;
                        var capr = tn.Rt * (0.5 + 0.15 * Math.sin(cp * 1.9 + tn.ph));
                        var cg = ctx.createRadialGradient(capx, capy, 0, capx, capy, capr);
                        cg.addColorStop(0, 'rgba(165,152,140,' + (0.2 * vis).toFixed(3) + ')');
                        cg.addColorStop(1, 'rgba(165,152,140,0)');
                        ctx.fillStyle = cg;
                        ctx.save();
                        ctx.translate(capx, capy);
                        ctx.scale(1.5, 0.7);
                        ctx.translate(-capx, -capy);
                        ctx.beginPath();
                        ctx.arc(capx, capy, capr, 0, 6.2832);
                        ctx.fill();
                        ctx.restore();
                    }

                    // 1) задний план: дальняя половина колец и частицы за воронкой
                    for (var li = 1; li <= 12; li++) ring(li / 12, Math.PI, 6.2832, 0.1, false);
                    tn.parts.forEach(function (pt) {
                        pt.jit += 0.03;
                        pt.a += (0.045 + (1 - pt.t) * 0.07) * (tn.far ? 0.8 : 1) * (1 + 0.55 * Math.sin(pt.jit * 1.3));
                        pt.tilt += 0.07;
                        pt.t += pt.rise * (1 + 1.2 * Math.sin(pt.jit)) + Math.sin(pt.jit * 2.1) * 0.0016;
                        pt.off += Math.sin(pt.jit * 0.7) * 0.004;
                        if (pt.off < 0.25) pt.off = 0.25; else if (pt.off > 1.15) pt.off = 1.15;
                        if (pt.t > 1.02) { pt.t = 0.02; } else if (pt.t < 0) { pt.t = 0.9; }
                        if (Math.sin(pt.a) < 0) particle(pt, false);
                    });

                    // 2) тело воронки: тёмные края и светлая середина — эффект цилиндра
                    var SL = 18;
                    [[1.35, 0.4], [1, 1]].forEach(function (bp) {       // мягкий ореол вокруг воронки + само тело
                        var kw = bp[0], ka = bp[1];
                        for (var si = 0; si < SL; si++) {
                            var ta = si / SL, tb = (si + 1) / SL + 0.004, tm = (ta + tb) / 2;
                            var cm = cxAt(tm), rm = rAt(tm) * kw;
                            var lg = ctx.createLinearGradient(cm - rm, 0, cm + rm, 0);
                            lg.addColorStop(0, 'rgba(95,82,70,' + (0.0 * vis).toFixed(3) + ')');
                            var mf = fadeAt(tm);
                            lg.addColorStop(0.18, 'rgba(95,82,70,' + (0.2 * ka * vis * mf).toFixed(3) + ')');
                            lg.addColorStop(0.5, 'rgba(225,215,200,' + (0.1 * ka * vis * mf).toFixed(3) + ')');
                            lg.addColorStop(0.82, 'rgba(95,82,70,' + (0.2 * ka * vis * mf).toFixed(3) + ')');
                            lg.addColorStop(1, 'rgba(95,82,70,' + (0.0 * vis).toFixed(3) + ')');
                            ctx.fillStyle = lg;
                            ctx.beginPath();
                            ctx.moveTo(cxAt(ta) - rAt(ta) * kw, yAt(ta));
                            ctx.lineTo(cxAt(tb) - rAt(tb) * kw, yAt(tb));
                            ctx.lineTo(cxAt(tb) + rAt(tb) * kw, yAt(tb));
                            ctx.lineTo(cxAt(ta) + rAt(ta) * kw, yAt(ta));
                            ctx.closePath();
                            ctx.fill();
                        }
                    });

                    // 3) передний план: ближняя половина колец с бегущими штрихами и частицы перед воронкой
                    for (var lj = 1; lj <= 12; lj++) ring(lj / 12, 0, Math.PI, 0.3, true);
                    tn.parts.forEach(function (pt) { if (Math.sin(pt.a) >= 0) particle(pt, true); });

                    if (tn.life >= tn.max || tn.x - tn.Rt * 1.5 > w) vortices[ti] = newTornado(tn.far);
                });

                leaves.forEach(function (lf, i) {
                    lf.ph += lf.dph; lf.x += lf.v; lf.tumble += lf.tspin;
                    var y = lf.base + Math.sin(lf.x / lf.wl * 6.28 + lf.ph) * lf.amp;
                    var slope = Math.cos(lf.x / lf.wl * 6.28 + lf.ph) * lf.amp / lf.wl * 6.28;
                    ctx.save();
                    ctx.translate(lf.x, y);
                    ctx.rotate(Math.atan(slope) + Math.sin(lf.tumble) * 0.6);
                    ctx.scale(1, 0.6 + 0.4 * Math.cos(lf.tumble));
                    ctx.drawImage(sprites[lf.img], -lf.size / 2, -lf.size * 0.3, lf.size, lf.size * 0.6);
                    ctx.restore();
                    if (lf.x > w + 40) leaves[i] = newLeaf(false);
                });
            }
            requestAnimationFrame(frame);
        }
        window.addEventListener('resize', init);
        init();
        requestAnimationFrame(frame);
    }

    // Радуга: полупрозрачная дуга в верхней части экрана, плавно проявляется.
    function rainbow() {
        var canvas = makeLayer('canvas');
        canvas.style.opacity = '0';
        canvas.style.transition = 'opacity 4s ease';
        canvas.style.filter = 'blur(1.5px)';
        var fade = 'linear-gradient(to bottom, #000 12%, transparent 100%)';
        canvas.style.webkitMaskImage = fade;
        canvas.style.maskImage = fade;
        var g = canvas.getContext('2d');
        function draw() {
            var w = canvas.width = window.innerWidth, h = canvas.height = window.innerHeight;
            var cx = w * 0.5, cy = h * 1.02, outer = Math.min(w * 0.46, h * 1.0), band = Math.max(8, outer * 0.035);
            var colors = ['255,70,70', '255,150,50', '255,225,70', '90,200,90', '70,170,235', '90,100,225', '165,95,205'];
            colors.forEach(function (rgb, i) {
                g.beginPath();
                g.arc(cx, cy, outer - i * band, Math.PI, Math.PI * 2);
                g.strokeStyle = 'rgba(' + rgb + ',0.22)';
                g.lineWidth = band + 1;
                g.stroke();
            });
        }
        window.addEventListener('resize', draw);
        draw();
        requestAnimationFrame(function () { requestAnimationFrame(function () { canvas.style.opacity = '1'; }); });
    }

    // Зной: дальний план — свечение солнца и дымка у земли; средний — неровные волны марева и столбы
    // горячего воздуха, медленно поднимающиеся вверх; ближний — редкие тёплые искры-пылинки.
    function heat() {
        var canvas = makeLayer('canvas');
        var ctx = canvas.getContext('2d');
        var w = 0, h = 0, lines = [], columns = [], motes = [];

        function newLine(randomY) {
            // гуще у земли: y распределён с перекосом к низу
            var y = randomY ? h * (0.3 + 0.7 * Math.pow(Math.random(), 0.7)) : h * (0.95 + Math.random() * 0.1);
            return {
                x0: Math.random() * w * 0.9 - w * 0.1,
                len: 140 + Math.random() * 340,
                y: y,
                amp: 3 + Math.random() * 7,
                wl: 60 + Math.random() * 120,
                ph: Math.random() * 6.28,
                sp: 0.008 + Math.random() * 0.014,
                a: 0.25 + Math.random() * 0.3,
                lw: 1.8 + Math.random() * 2.8,
                rise: 0.03 + Math.random() * 0.07
            };
        }
        function newColumn(randomY) {
            var ch = h * (0.25 + Math.random() * 0.3);
            return {
                x: Math.random() * w,
                y: randomY ? h * (0.35 + Math.random() * 0.65) : h * (0.95 + Math.random() * 0.1),
                sx: 30 + Math.random() * 70,
                sy: ch,
                v: 0.12 + Math.random() * 0.22,
                sway: Math.random() * 6.28,
                life: 0
            };
        }
        function newMote(randomY) {
            return {
                x: Math.random() * w,
                y: randomY ? Math.random() * h : h + 10,
                v: 0.25 + Math.random() * 0.55,
                r: 0.9 + Math.random() * 1.6,
                ph: Math.random() * 6.28,
                sp: 0.02 + Math.random() * 0.03
            };
        }
        function init() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            var nl = Math.max(9, Math.round(h / 34));
            var nc = Math.max(5, Math.round(w / 110));
            var nm = Math.max(10, Math.round(w * h / 22000));
            lines = []; columns = []; motes = [];
            for (var i = 0; i < nl; i++) lines.push(newLine(true));
            for (var j = 0; j < nc; j++) columns.push(newColumn(true));
            for (var k = 0; k < nm; k++) motes.push(newMote(true));
        }

        function frame() {
            if (!document.hidden) {
                ctx.clearRect(0, 0, w, h);

                // дальний план
                var sun = ctx.createRadialGradient(w * 0.88, h * 0.04, 0, w * 0.88, h * 0.04, Math.max(w, h) * 0.6);
                sun.addColorStop(0, 'rgba(255,215,110,0.3)');
                sun.addColorStop(1, 'rgba(255,215,110,0)');
                ctx.fillStyle = sun;
                ctx.fillRect(0, 0, w, h);
                var haze = ctx.createLinearGradient(0, h, 0, h * 0.5);
                haze.addColorStop(0, 'rgba(255,150,50,0.22)');
                haze.addColorStop(1, 'rgba(255,150,50,0)');
                ctx.fillStyle = haze;
                ctx.fillRect(0, h * 0.5, w, h * 0.5);

                // столбы горячего воздуха: вытянутые мягкие пятна, поднимаются, покачиваются и тают у верха
                columns.forEach(function (c, i) {
                    c.y -= c.v; c.sway += 0.01; c.life += 1;
                    var rel = (h - c.y) / h;                       // 0 внизу … 1 вверху
                    var fade = Math.sin(Math.min(1, Math.max(0, rel * 1.25)) * Math.PI);
                    var cx = c.x + Math.sin(c.sway) * 14;
                    ctx.save();
                    ctx.translate(cx, c.y);
                    ctx.scale(c.sx, c.sy);
                    var g = ctx.createRadialGradient(0, 0, 0, 0, 0, 1);
                    g.addColorStop(0, 'rgba(255,244,215,' + (0.42 * fade).toFixed(3) + ')');
                    g.addColorStop(0.6, 'rgba(255,230,185,' + (0.22 * fade).toFixed(3) + ')');
                    g.addColorStop(1, 'rgba(255,220,170,0)');
                    ctx.fillStyle = g;
                    ctx.beginPath();
                    ctx.arc(0, 0, 1, 0, 6.2832);
                    ctx.fill();
                    ctx.restore();
                    if (c.y + c.sy < -20) columns[i] = newColumn(false);
                });

                // неровные волны марева: разной длины, толщины и амплитуды, края растворяются
                lines.forEach(function (l, i) {
                    l.ph += l.sp;
                    l.y -= l.rise;
                    ctx.lineWidth = l.lw;
                    ctx.lineCap = 'butt';
                    var step = 12, n = Math.ceil(l.len / step), prevX = 0, prevY = 0;
                    for (var k = 0; k <= n; k++) {
                        var x = l.x0 + k * step;
                        var y = l.y + Math.sin(x / l.wl * 6.28 + l.ph) * l.amp;
                        if (k > 0) {
                            var edge = Math.sin(Math.min(1, (k - 0.5) / n) * Math.PI);
                            ctx.strokeStyle = 'rgba(255,255,255,' + (l.a * edge).toFixed(3) + ')';
                            ctx.beginPath();
                            ctx.moveTo(prevX, prevY);
                            ctx.lineTo(x, y);
                            ctx.stroke();
                        }
                        prevX = x; prevY = y;
                    }
                    if (l.y < h * 0.22) lines[i] = newLine(false);
                });

                // ближний план: тёплые искры, плывущие вверх и мерцающие
                motes.forEach(function (m, i) {
                    m.ph += m.sp;
                    m.y -= m.v;
                    m.x += Math.sin(m.ph) * 0.35;
                    var tw = 0.35 + 0.45 * (0.5 + 0.5 * Math.sin(m.ph * 3));
                    ctx.fillStyle = 'rgba(255,238,190,' + tw.toFixed(3) + ')';
                    ctx.beginPath();
                    ctx.arc(m.x, m.y, m.r, 0, 6.2832);
                    ctx.fill();
                    if (m.y < -10) motes[i] = newMote(false);
                });
            }
            requestAnimationFrame(frame);
        }
        window.addEventListener('resize', init);
        init();
        requestAnimationFrame(frame);
    }

    window.WeatherFx = {
        start: function (type) {
            if (document.getElementById('weather-fx-on')) return;
            var mark = document.createElement('meta');
            mark.id = 'weather-fx-on';
            document.head.appendChild(mark);
            if (type === 'storm' || type === 'blizzard') particles(type);
            if (type === 'blizzard') snowCorners();
            else if (type === 'fog') fog();
            else if (type === 'tailwind') breeze(false);
            else if (type === 'hurricane') breeze(true);
            else if (type === 'rainbow') rainbow();
            else if (type === 'heat') heat();
        }
    };
})();
