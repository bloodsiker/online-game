<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Персонаж</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        .b {
            background-color: #CEBBAA;
        }
        .color-red {
            color: red;
        }
        .t0 {
            background: url({{ asset('img/bg/table-header2.jpg') }}) repeat-x top left;
            background-color: #EDD5C3;
        }
        .t1 {
            background: url({{ asset('img/bg/table-header.jpg') }}) repeat-x top left;
            background-color: #DFBBA3;
        }
        .l0 {
            background-color: #FFF8EA;
        }
        .l1 {
            background-color: #FFFBF5;
        }

        .tbgr {
            background-color: #FADCC2;
        }

        input.in5but, a.in5but, a.in5but:link, a.in5but:visited {
            cursor: pointer;
            min-height: 19px;
            text-decoration: none;
            display: inline-block;
        }
    </style>
</head>
<body>
<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <script>if (!window.parent.hero) {
                    self.close();
                } else {
                    window.top.i0(164924, 9875184);
                    if (window.name == "game") window.top.hero.gcl1();

                    function sxl(y, n, l) {
                        window.top.hero.sx4tn(y, n, l);
                    }

                    function sg(y, n, l) {
                        window.top.hero.sx1tn(y, n, l);
                    }

                    function t1(n) {
                        window.top.hero.go1(n);
                    }

                    window.top.hero.focus();
                }
            </script>

            <b>Распределение очков</b>
            <p>Вы можете увеличить значения основных характеристик, распределив свободные очки между ними.</p>
            <p></p>
            <form action="{{ route('character.point_save') }}" name="incpoints" method="post">
                В вашем распоряжении (очков):
                <input type="button" value="{{ $player->getFreeStats() }}" name="points" style="background: transparent;border:0;font-weight:bold;">
                <input name="ostr" type="hidden" value="{{ $player->getStrength() }}">
                <input name="oint" type="hidden" value="{{ $player->getInt() }}">
                <input name="oagil" type="hidden" value="{{ $player->getAgility() }}">
                <input name="ointel" type="hidden" value="{{ $player->getIntelligence() }}">
                <input name="omud" type="hidden" value="{{ $player->getMud() }}">
                {{ csrf_field() }}
                <br>
                <br>
                <table cellspacing="1" cellpadding="1" border="0" class="b">
                    <tbody>
                    <tr>
                        <td class="tbgr">
                            <table cellspacing="1" cellpadding="5" border="0" width="100%" class="b">
                                <tbody>
                                <tr class="t1" valign="bottom">
                                    <td><b>Характеристика:</b> <a href="/help/character/#1" target="_blank">(?)</a></td>
                                    <td><b>Значение:</b></td>
                                    <td><b>Увеличить на:</b></td>
                                    <td><b>Результат:</b><br><small>(только <a href="/help/character/" target="_blank">базовое
                                                значение</a>)</small></td>
                                </tr>
                                <tr class="l0">
                                    <td>Cила:</td>
                                    <td><b>{{ $playerDecorator->getStrength() }}</b> <small>(64+3)</small></td>
                                    <td>
                                        <input type="button" value=" - " onclick="decval('str');" class="in5but">
                                        <input type="text" name="str" value="0" size="3" style="text-align:center"
                                            onchange="checkval7('str');newVal('str');hidebut3();" onfocus="showbut3();"
                                            onblur="hidebut3();">
                                        <input type="button" value=" + " onclick="incval('str');" class="in5but">
                                    </td>
                                    <td id="nstr">{{ $player->getStrength() }}</td>
                                </tr>
                                <tr class="l1">
                                    <td>Интуиция:</td>
                                    <td><b>{{ $playerDecorator->getInt() }}</b></td>
                                    <td>
                                        <input type="button" value=" - " onclick="decval('int');" class="in5but">
                                        <input type="text" name="int" value="0" size="3" style="text-align:center"
                                               onchange="checkval7('int');newVal('int');hidebut3();" onfocus="showbut3();"
                                               onblur="hidebut3();">
                                        <input type="button" value=" + " onclick="incval('int');" class="in5but">
                                    </td>
                                    <td id="nint">{{ $player->getInt() }}</td>
                                </tr>
                                <tr class="l0">
                                    <td>Ловкость:</td>
                                    <td><b>{{ $playerDecorator->getAgility() }}</b></td>
                                    <td>
                                        <input type="button" value=" - " onclick="decval('agil');" class="in5but">
                                        <input type="text" name="agil" value="0" size="3" style="text-align:center"
                                            onchange="checkval7('agil');newVal('agil');hidebut3();" onfocus="showbut3();"
                                            onblur="hidebut3();">
                                        <input type="button" value=" + " onclick="incval('agil');" class="in5but">
                                    </td>
                                    <td id="nagil">{{ $player->getAgility() }}</td>
                                </tr>
                                <tr class="l1">
                                    <td>Интеллект:</td>
                                    <td><b>{{ $playerDecorator->getIntelligence() }}</b></td>
                                    <td>
                                        <input type="button" value=" - " onclick="decval('intel');" class="in5but">
                                        <input type="text" name="intel" value="0" size="3" style="text-align:center"
                                            onchange="checkval7('intel');newVal('intel');hidebut3();" onfocus="showbut3();"
                                            onblur="hidebut3();">
                                        <input type="button" value=" + " onclick="incval('intel');" class="in5but">
                                    </td>
                                    <td id="nintel">{{ $player->getIntelligence() }}</td>
                                </tr>
                                <tr class="l0">
                                    <td>Мудрость:</td>
                                    <td><b>{{ $playerDecorator->getMud() }}</b></td>
                                    <td>
                                        <input type="button" value=" - " onclick="decval('mud');" class="in5but">
                                        <input type="text" name="mud" value="0" size="3" style="text-align:center"
                                            onchange="checkval7('mud');newVal('mud');hidebut3();" onfocus="showbut3();"
                                            onblur="hidebut3();">
                                        <input type="button" value=" + " onclick="incval('mud');" class="in5but">
                                    </td>
                                    <td id="nmud">{{ $player->getMud() }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <p>
                    <span class="butt1 pointer"><span><input value="Сохранить" type="submit"></span></span>
{{--                    <input type="button" value="Предварительный просмотр" onclick="previewp();" class="in5but">--}}
{{--                    <input type="submit" value="Сохранить" class="in5but"> <font id="but3"></font>--}}
                    <script>
                        allpoints = {{ $player->getFreeStats() }};

                        function checkval7(vid) {
                            document.forms.incpoints[vid].value = Math.floor(document.forms.incpoints[vid].value);
                            p = document.forms.incpoints["str"].value * 1 + document.forms.incpoints["int"].value * 1 + document.forms.incpoints["agil"].value * 1 + document.forms.incpoints["intel"].value * 1 + document.forms.incpoints["mud"].value * 1;
                            if (allpoints - p < 0) {
                                document.forms.incpoints[vid].value = document.forms.incpoints[vid].value * 1 + allpoints * 1 - p * 1;
                                document.forms.incpoints.points.value = 0;
                            } else {
                                document.forms.incpoints.points.value = allpoints * 1 - p * 1;
                            }
                            ;
                        }

                        function showbut3() {
                            document.all["but3"].innerHTML = "<input type=button value=считать class=in5but>";
                        }

                        function hidebut3() {
                            document.all["but3"].innerHTML = "";
                        }

                        function newVal(vid) {
                            var targetElement = document.getElementById("n" + vid);
                            var form = document.forms['incpoints'];

                            if (form) {
                                var oValue = form["o" + vid].value * 1;
                                var vidValue = form[vid].value * 1;
                                targetElement.innerHTML = oValue + vidValue;
                            }
                        }

                        newVal("str");
                        newVal("int");
                        newVal("agil");
                        newVal("intel");
                        newVal("mud");

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

                        @if (session()->has('message'))
                            window.parent.showErrorIframe('{{ session('message') }}')
                        @endif
                    </script>
                </p>
                <p><font style="background:#FEEFB6;"><b>Подсказка</b> - <font class="r">На первых порах лучше распределять под оружие, так как оно в начале важнее.</font></font></p>
                <p><a href="/help/lib/" target="_blank">Справочник: Вещи и оружие (минимальные требования и свойства)</a> »</p>
                <p>« <a href="{{ route('character') }}">Персонаж</a></p>
                <p>« <a href="{{ route('location') }}">Описание местности</a>
                    <script>
                        function p72(h) {
                            if (h < 245 * 0.25) {
                                return "<font color=#AA0000>" + Math.floor(h) + "</font>";
                            } else {
                                return Math.floor(h);
                            }
                        }

                        if (window.parent.hero) {
                            window.parent.hero.i7(100, 100, p72(245), "53");
                            window.top.hero.spl5reset();
                        }</script>
                </p>
            </form>
        </td>
    </tr>
    </tbody>
</table>

<script>
    {{--document.addEventListener('keydown', function(event) {--}}
    {{--    switch (event.key.toLowerCase()) {--}}
    {{--        case 'i':--}}
    {{--            window.parent.sendDataToGame('{{ route('backpack') }}');--}}
    {{--            break;--}}
    {{--        case 'c':--}}
    {{--            window.parent.sendDataToGame('{{ route('character') }}');--}}
    {{--            break;--}}
    {{--        case ' ':--}}
    {{--            window.parent.sendDataToGame('{{ route('location') }}');--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}
</script>

</body>
</html>
