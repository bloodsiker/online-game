<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Распределение очков</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { height: 100%; margin: 0; }
        body {
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            color: #2a1a0e;
            background-repeat: repeat;
        }

        .pts-wrap {
            padding: 12px 14px;
            max-width: 480px;
        }

        /* ── заголовок ── */
        .pts-header {
            background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left #DFBBA3;
            border: 1px solid #CEBBAA;
            border-radius: 4px 4px 0 0;
            padding: 6px 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pts-header-title {
            font-size: 12px;
            font-weight: bold;
            color: #461c0b;
        }
        .pts-points-badge {
            background: #FADCC2;
            border: 1px solid #CEBBAA;
            border-radius: 10px;
            padding: 2px 10px;
            font-size: 11px;
            color: #461c0b;
        }
        .pts-points-badge b { font-size: 13px; color: #8b2000; }

        /* ── тело карточки ── */
        .pts-body {
            border: 1px solid #CEBBAA;
            border-top: none;
            border-radius: 0 0 4px 4px;
            overflow: hidden;
        }

        /* ── строка характеристики ── */
        .pts-row {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 10px;
            border-bottom: 1px solid #e8d4c0;
            background: #FFF8EA;
        }
        .pts-row:nth-child(even) { background: #FFFBF5; }
        .pts-row:last-child { border-bottom: none; }

        .pts-row-name {
            width: 90px;
            flex-shrink: 0;
            color: #5a3a2a;
            font-weight: bold;
        }
        .pts-row-current {
            width: 50px;
            flex-shrink: 0;
            font-weight: bold;
            color: #461c0b;
            font-size: 12px;
        }
        .pts-row-controls {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }
        .pts-btn {
            width: 22px;
            height: 22px;
            border: 1px solid #CEBBAA;
            background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left #EDD5C3;
            color: #461c0b;
            font-weight: bold;
            font-size: 13px;
            cursor: pointer;
            border-radius: 3px;
            display: flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
            padding: 0;
            font-family: Tahoma, sans-serif;
        }
        .pts-btn:hover { background-color: #FADCC2; }
        .pts-btn:active { background-color: #CEBBAA; }
        .pts-input {
            width: 32px;
            text-align: center;
            border: 1px solid #CEBBAA;
            background: #fff;
            border-radius: 3px;
            padding: 2px 0;
            font-family: Tahoma, sans-serif;
            font-size: 11px;
            color: #461c0b;
            font-weight: bold;
        }
        .pts-row-result {
            margin-left: auto;
            font-size: 12px;
            font-weight: bold;
            color: #8b2000;
            min-width: 40px;
            text-align: right;
        }

        /* ── подсказка ── */
        .pts-tip {
            margin-top: 10px;
            background: #FEEFB6;
            border: 1px solid #e0d080;
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 11px;
            color: #5a4a00;
            line-height: 1.5;
        }
        .pts-tip b { color: #3a2a00; }

        /* ── ссылки ── */
        .pts-links {
            margin-top: 10px;
            font-size: 11px;
            color: #5a3a2a;
        }
        .pts-links a { color: #8b3a1a; text-decoration: none; }
        .pts-links a:hover { text-decoration: underline; }

        /* ── кнопка сохранить ── */
        .pts-save-wrap { padding: 10px 0 4px; }
    </style>
</head>
<body>
<script>
    if (!window.parent.hero) {
        self.close();
    } else {
        window.top.i0(164924, 9875184);
        if (window.name == "game") window.top.hero.gcl1();

        function sxl(y, n, l) { window.top.hero.sx4tn(y, n, l); }
        function sg(y, n, l)  { window.top.hero.sx1tn(y, n, l); }
        function t1(n)        { window.top.hero.go1(n); }

        window.top.hero.focus();
    }
</script>

<form action="{{ route('character.point_save') }}" name="incpoints" method="post">
    {{ csrf_field() }}
    <input name="ostr"   type="hidden" value="{{ $character->baseStrength }}">
    <input name="oint"   type="hidden" value="{{ $character->baseIntuition }}">
    <input name="oagil"  type="hidden" value="{{ $character->baseAgility }}">
    <input name="ointel" type="hidden" value="{{ $character->baseIntelligence }}">
    <input name="omud"   type="hidden" value="{{ $character->baseWisdom }}">

    <div class="pts-wrap">

        <div class="pts-header">
            <span class="pts-header-title">Распределение очков</span>
            <span class="pts-points-badge">
                Свободно: <b><input type="text" value="{{ $character->freeStats }}" name="points"
                    style="background:transparent;border:0;font-weight:bold;font-size:13px;color:#8b2000;width:30px;padding:0;font-family:Tahoma;" readonly></b>
            </span>
        </div>

        <div class="pts-body">
            @foreach([
                ['Сила',       'strength',     $character->stats->getStrength(),     $character->baseStrength],
                ['Интуиция',   'intuition',    $character->stats->getInt(),          $character->baseIntuition],
                ['Ловкость',   'agility',      $character->stats->getAgility(),      $character->baseAgility],
                ['Интеллект',  'intelligence', $character->stats->getIntelligence(), $character->baseIntelligence],
                ['Мудрость',   'wisdom',       $character->stats->getMud(),          $character->baseWisdom],
            ] as [$label, $key, $full, $base])
                <div class="pts-row">
                    <span class="pts-row-name">{{ $label }}</span>
                    <span class="pts-row-current" title="Полное значение">{{ $full }}</span>
                    <div class="pts-row-controls">
                        <button type="button" class="pts-btn" onclick="decval('{{ $key }}')">−</button>
                        <input type="text" name="{{ $key }}" value="0" size="3"
                               class="pts-input"
                               onchange="checkval7('{{ $key }}');newVal('{{ $key }}');">
                        <button type="button" class="pts-btn" onclick="incval('{{ $key }}')">+</button>
                    </div>
                    <span class="pts-row-result" id="n{{ $key }}">{{ $base }}</span>
                </div>
            @endforeach
        </div>

        <div class="pts-save-wrap">
            <span class="butt1 pointer"><span><input value="Сохранить" type="submit"></span></span>
        </div>

        <div class="pts-tip">
            <b>Подсказка:</b> На первых порах лучше распределять под оружие, так как оно в начале важнее.
            <br><a href="/help/lib/" target="_blank">Справочник: Вещи и оружие (минимальные требования и свойства) »</a>
        </div>

    </div>
</form>

<script>
    allpoints = {{ $character->freeStats }};

    function checkval7(vid) {
        var cur = Math.floor(document.forms.incpoints[vid].value * 1);
        if (cur < 0) cur = 0;
        document.forms.incpoints[vid].value = cur;
        var p = document.forms.incpoints["strength"].value * 1
              + document.forms.incpoints["intuition"].value * 1
              + document.forms.incpoints["agility"].value * 1
              + document.forms.incpoints["intelligence"].value * 1
              + document.forms.incpoints["wisdom"].value * 1;
        if (allpoints - p < 0) {
            document.forms.incpoints[vid].value = cur + allpoints - p;
            document.forms.incpoints.points.value = 0;
        } else {
            document.forms.incpoints.points.value = allpoints - p;
        }
    }

    function newVal(vid) {
        var el   = document.getElementById("n" + vid);
        var form = document.forms['incpoints'];
        if (form && el) {
            el.innerHTML = form["o" + vid].value * 1 + form[vid].value * 1;
        }
    }

    newVal("strength");
    newVal("intuition");
    newVal("agility");
    newVal("intelligence");
    newVal("wisdom");

    function incval(incname) {
        if (document.forms.incpoints.points.value > 0) {
            document.forms.incpoints.points.value = document.forms.incpoints.points.value * 1 - 1;
            document.forms.incpoints[incname].value = document.forms.incpoints[incname].value * 1 + 1;
        }
        newVal(incname);
    }

    function decval(incname) {
        if (document.forms.incpoints[incname].value > 0) {
            document.forms.incpoints.points.value = document.forms.incpoints.points.value * 1 + 1;
            document.forms.incpoints[incname].value = document.forms.incpoints[incname].value * 1 - 1;
        }
        newVal(incname);
    }

    function p72(h) {
        return h < 245 * 0.25
            ? "<font color=#AA0000>" + Math.floor(h) + "</font>"
            : Math.floor(h);
    }

    if (window.parent.hero) {
        window.parent.hero.i7(100, 100, p72(245), "53");
        window.top.hero.spl5reset();
    }

    @if (session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}')
    @endif
</script>
</body>
</html>