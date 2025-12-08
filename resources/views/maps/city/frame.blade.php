<style type="text/css">
    <!--
    body {
        scrollbar-face-color: #FAE8D3;
        scrollbar-highlight-color: white;
        scrollbar-shadow-color: #D1A77F;
        scrollbar-3dlight-color: #FBDCCF;
        scrollbar-arrow-color: #AA968A;
        scrollbar-track-color: #EDD9C8;
        scrollbar-darkshadow-color: #AA968A;
    }

    html {
        scrollbar-color: #CBB8A7 #E6D6C5;
        scrollbar-width: thin;
    }


    body::-webkit-scrollbar {
        width: 7px;
        height: 7px;
    }

    body::-webkit-scrollbar-thumb {
        background: #CBB8A7;
        border-radius: 5px;
    }

    body::-webkit-scrollbar-thumb:hover {
        background: #AA968A;
    }

    body::-webkit-scrollbar-track, body::-webkit-scrollbar-corner {
        background: #E6D6C5;
        border-radius: 5px;
    }
    a:link, a:visited {
        color: black;
    }

    a:hover {
        color: #FF8000;
    }

    A.wt:link, A.wt:visited {
        color: white;
    }

    A.wt:hover {
        color: #aaaaaa;
        background-color: #666666;
    }

    A.b:link, A.b:visited {
        color: blue;
    }

    a.b:hover {
        color: #FF8000;
    }

    A.r:hover {
        color: #FF8000;
    }

    a:active {
        color: yellow;
        background-color: black;
    }
    .br {
        border-right: 1px solid #AD998C;
    }
    .bt {
        border-top: 1px solid #AD998C;
    }
    .bl {
        border-left: 1px solid #AD998C;
    }
    .bb {
        border-bottom: 1px solid #AD998C;
    }
    .s2box {
        border-spacing: 0px;
        font-size: 9px;
        text-align: center;
        width: 48px;
        height: 48px;
        position: relative;
        display: table-cell;
        margin: 0px;
        padding: 0px;
        vertical-align: middle;
        box-sizing: border-box;
    }

    .s2box s {
        display: none;
    }

    .ulocation {
        animation: ula 1s linear infinite;
        border-radius: 50%;
        box-shadow: inset 0 0 0 4px #B59387;
        background-color: #B59387;
        background-color: rgba(181, 147, 135, 0.3);
        display: inline-block;
    }

    .maplegend {
    }

    .s2box cite {
        margin: 14px 0px 0px -3px;
        font-style: normal;
        position: absolute;
        z-index: 10;
        display: none;
    }

    .s2box:hover cite {
        white-space: nowrap;
        font-weight: normal;
        font-size: 10px;
        color: #706258;
        display: inline;
        background-color: #FFF8EA;
        border: 1px outset #CEBBAA;
        padding: 10px;
    }

    .s2box em {
        position: relative;
        font-style: normal;
        font-weight: normal;
        opacity: 0.80;
        filter: alpha(opacity=80);
        display: inline-block;
        margin: 0px;
        min-width: 20px;
        border: 1px outset #AD998C;
        padding: 3px;
        color: black;
    }

    .s2box em::after {
        content: "?";
        color: #9C8D84;
        top: -4px;
        font-size: smaller;
        position: absolute;
    }

    .listloc {
        border: 1px solid black;
        width: 32px;
        vertical-align: middle;
        display: inline-block;
        text-align: center;
        margin: 0px;
        font-size: 10px;
        cursor: pointer;
        cursor: hand;
    }

    .alvl {
        z-index: 2;
    }

    .anorth {
        position: absolute;
        left: 19px;
        top: -15px;
        font-size: 18px;
        text-decoration: none;
        z-index: 1;
    }

    .asouth {
        position: absolute;
        left: 19px;
        bottom: -12px;
        font-size: 18px;
        text-decoration: none;
        z-index: 1;
    }

    .awest {
        position: absolute;
        left: -12px;
        top: 10px;
        font-size: 18px;
        text-decoration: none;
        z-index: 1;
    }

    .aeast {
        position: absolute;
        right: -12px;
        top: 10px;
        font-size: 18px;
        text-decoration: none;
        z-index: 1;
    }

    .anorth:hover, .awest:hover, .asouth:hover, .aeast:hover {
        text-decoration: none;
    }

    .an {
        animation: borderAnimation 1s linear infinite;
    }

    .maptable th {
        padding: 5px;
        font-size: 14px;
    }

    .maptable a {
        text-decoration: none;
    }

    .maptable {
        box-sizing: padding-box;
        empty-cells: hide;
        border-collapse: separate;
        background-color: rgba(250, 233, 218, 0.5);
        padding: 0px;
        margin: 0px;
        border: 1px solid #CEBBAA;
    }
</style>

<table width="833" cellspacing="1" cellpadding="0" id="m0" class="maptable">
    <tbody>
    <tr style=" @if(request()->has('hide')) display: none; @endif">
        <th colspan="17" class="t0" align="left" style="padding:5px;font-size:14px;"></th>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u100">
                    <div id="l100" class="s2box bl br">
                        <s id="z100">0</s>
                        <em>100</em>
                        <cite>Городские ворота</cite>
                        <a href="?m=MjAwMA#2000" class="anorth">↑</a>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u99">
                    <div id="l99" class="s2box bt bl">
                        <s id="z99">0</s>99
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u98">
                    <div id="l98" class="s2box">
                        <s id="z3513">0</s><a name="98"></a>98
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u75">
                    <div id="l75" class="s2box bt br">
                        <s id="z75">0</s><a name="75"></a>75
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u68">
                    <div id="l68" class="s2box bt bl">
                        <s id="z68">0</s><a name="68"></a>68
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u69">
                    <div id="l69" class="s2box bt bb">
                        <s id="z69">0</s><a name="69"></a>69
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u70">
                    <div id="l70" class="s2box bt bb">
                        <s id="z70">0</s><a name="70"></a>70
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u71">
                    <div id="l71" class="s2box bb bt">
                        <s id="z71">0</s><a name="71"></a>71
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u72">
                    <div id="l72" class="s2box bt bb">
                        <s id="z72">0</s><a name="72"></a>72
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u73">
                    <div id="l73" class="s2box bb">
                        <s id="z73">0</s><a name="73"></a>73
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u97">
                    <div id="l97" class="s2box">
                        <s id="z97">0</s><a name="97"></a>97
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u74">
                    <div id="l74" class="s2box bb">
                        <s id="z74">0</s><a name="74"></a>74
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u76">
                    <div id="l76" class="s2box bb bt">
                        <s id="z76">0</s><a name="76"></a>76
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u77">
                    <div id="l77" class="s2box bb bt">
                        <s id="z77">0</s><a name="77"></a>77
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u78">
                    <div id="l78" class="s2box bb bt">
                        <s id="z78">0</s><a name="78"></a>78
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u79">
                    <div id="l79" class="s2box bb bt">
                        <s id="z79">0</s><a name="79"></a>79
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u80">
                    <div id="l80" class="s2box br bt">
                        <s id="z80">0</s><a name="80"></a>80
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u67">
                    <div id="l67" class="s2box bl br">
                        <s id="z67">0</s><a name="67"></a>67
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u38">
                    <div id="l38" class="s2box bl bt">
                        <s id="z38">0</s><a name="38"></a>38
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u37">
                    <div id="l37" class="s2box br bt">
                        <s id="z37">0</s><a name="37"></a>37
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u96">
                    <div id="l96" class="s2box bl br">
                        <s id="z96">0</s><a name="96"></a>96
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u41">
                    <div id="l41" class="s2box bl bt">
                        <s id="z41">0</s><a name="41"></a>41
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u42">
                    <div id="l42" class="s2box br bt">
                        <s id="z42">0</s><a name="42"></a>42
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u81">
                    <div id="l81" class="an s2box br bl">
                        <s id="z81">0</s><a name="81"></a>81
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u66">
                    <div id="l66" class="s2box br bl">
                        <s id="z66">0</s><a name="66"></a>66
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u39">
                    <div id="l39" class="s2box br bl bb">
                        <s id="z39">0</s><a name="39"></a>
                        <em>39</em>
                        <cite>Коммисионный магазин</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u36">
                    <div id="l36" class="s2box br bl">
                        <s id="z36">0</s><a name="36"></a>36
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u95">
                    <div id="l95" class="s2box bl br">
                        <s id="z95">0</s><a name="95"></a>95
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u40">
                    <div id="l40" class="s2box bl br">
                        <s id="z40">0</s><a name="40"></a>40
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u43">
                    <div id="l43" class="s2box br bl bb">
                        <s id="z43">0</s><a name="43"></a>43
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u82">
                    <div id="l82" class="s2box br bl">
                        <s id="z82">0</s><a name="82"></a>82
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u64">
                    <div id="l64" class="s2box bt bl">
                        <s id="z64">0</s><a name="64"></a>64
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u65">
                    <div id="l65" class="s2box br">
                        <s id="z65">0</s><a name="65"></a>65
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u27">
                    <div id="l27" class="s2box bl bt bb">
                        <s id="z27">0</s><a name="27"></a>
                        <em>27</em>
                        <cite>Кузня</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u26">
                    <div id="l26" class="s2box bt br">
                        <s id="z26">0</s><a name="26"></a>26
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u8">
                    <div id="l8" class="s2box bl">
                        <s id="z8">0</s><a name="8"></a>8
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u7">
                    <div id="l7" class="s2box bb">
                        <s id="z7">0</s><a name="7"></a>7
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u13">
                    <div id="l13" class="s2box br">
                        <s id="z13">0</s><a name="13"></a>13
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u15">
                    <div id="l15" class="s2box bt bl">
                        <s id="z15">0</s><a name="15"></a>15
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u16">
                    <div id="l16" class="s2box bt br bb">
                        <s id="z16">0</s><a name="16"></a>
                        <em>16</em>
                        <cite>Магазин оружия</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u83">
                    <div id="l83" class="s2box bl">
                        <s id="z83">0</s><a name="83"></a>83
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u84">
                    <div id="l84" class="s2box br bt">
                        <s id="z84">0</s><a name="84"></a>84
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u35">
                    <div id="l35" class="s2box bb bt">
                        <s id="z35">0</s><a name="35"></a>
                        <em>35</em>
                        <cite>Городские ворота</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u34">
                    <div id="l34" class="s2box">
                        <s id="z34">0</s><a name="34"></a>34
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u33">
                    <div id="l33" class="s2box">
                        <s id="z33">0</s><a name="33"></a>33
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u32">
                    <div id="l32" class="s2box bb bt">
                        <s id="z32">0</s><a name="32"></a>32
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u31">
                    <div id="l31" class="s2box bb bt">
                        <s id="z31">0</s><a name="31"></a>31
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u30">
                    <div id="l30" class="s2box bb bt">
                        <s id="z30">0</s><a name="27"></a>30
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u25">
                    <div id="l25" class="s2box">
                        <s id="z25">0</s><a name="25"></a>25
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u9">
                    <div id="l9" class="s2box br">
                        <s id="z9">0</s><a name="9"></a>9
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u6">
                    <div id="l6" class="s2box bl br bt">
                        <s id="z6">0</s><a name="6"></a>
                        <em>6</em>
                        <cite>Целительный фонтан</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u12">
                    <div id="l12" class="s2box bl">
                        <s id="z12">0</s><a name="12"></a>12
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u14">
                    <div id="l14" class="s2box">
                        <s id="z14">0</s><a name="15"></a>14
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u19">
                    <div id="l19" class="s2box bb bt">
                        <s id="z19">0</s><a name="19"></a>19
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u20">
                    <div id="l20" class="s2box bb bt">
                        <s id="z20">0</s><a name="20"></a>20
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u21">
                    <div id="l21" class="s2box bb bt">
                        <s id="z21">0</s><a name="21"></a>21
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u22">
                    <div id="l22" class="s2box">
                        <s id="z22">0</s><a name="22"></a>22
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u23">
                    <div id="l23" class="s2box">
                        <s id="z23">0</s><a name="23"></a>23
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u24">
                    <div id="l24" class="s2box bt bb">
                        <s id="z24">0</s><a name="24"></a>
                        <em>24</em>
                        <cite>Городские ворота</cite>
                    </div>
                </div>
            </div>
        </td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u63">
                    <div id="l63" class="s2box bb bl">
                        <s id="z63">0</s><a name="63"></a>63
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u62">
                    <div id="l62" class="s2box br">
                        <s id="z62">0</s><a name="62"></a>62
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u29">
                    <div id="l29" class="s2box bl bt bb">
                        <s id="z29">0</s><a name="29"></a>29
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u28">
                    <div id="l28" class="s2box bb br">
                        <s id="z28">0</s><a name="28"></a>28
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u10">
                    <div id="l10" class="s2box bl">
                        <s id="z10">0</s><a name="10"></a>10
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u5">
                    <div id="l5" class="s2box">
                        <s id="z5">0</s><a name="5"></a>5
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u11">
                    <div id="l11" class="s2box br">
                        <s id="z11">0</s><a name="11"></a>11
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u17">
                    <div id="l17" class="s2box bb bl">
                        <s id="z17">0</s><a name="17"></a>17
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u18">
                    <div id="l18" class="s2box br bt bb">
                        <s id="z18">0</s><a name="18"></a>
                        <em>18</em>
                        <cite>Магазин брони</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u86">
                    <div id="l86" class="s2box bl">
                        <s id="z86">0</s><a name="86"></a>86
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u85">
                    <div id="l85" class="s2box br bb">
                        <s id="z85">0</s><a name="85"></a>85
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u61">
                    <div id="l61" class="s2box br bl">
                        <s id="z61">0</s><a name="61"></a>61
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u47">
                    <div id="l47" class="s2box br bl bt">
                        <s id="z47">0</s><a name="47"></a>
                        <em>47</em>
                        <cite>Хранилище</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u44">
                    <div id="l44" class="s2box br bl">
                        <s id="z44">0</s><a name="44"></a>44
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u4">
                    <div id="l4" class="s2box bl br">
                        <s id="z4">0</s><a name="4"></a>4
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u48">
                    <div id="l48" class="s2box bl br">
                        <s id="z48">0</s><a name="48"></a>48
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u51">
                    <div id="l51" class="s2box br bl bt">
                        <s id="z51">0</s><a name="51"></a>
                        <em>51</em>
                        <cite>Торговая лавка</cite>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u87">
                    <div id="l87" class="s2box br bl">
                        <s id="z87">0</s><a name="87"></a>87
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u60">
                    <div id="l60" class="s2box bl br">
                        <s id="z60">0</s><a name="60"></a>60
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u46">
                    <div id="l46" class="s2box bl bb">
                        <s id="z46">0</s><a name="46"></a>46
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u45">
                    <div id="l45" class="s2box br bb">
                        <s id="z45">0</s><a name="45"></a>45
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u3">
                    <div id="l3" class="s2box bl br">
                        <s id="z3">0</s><a name="3"></a>3
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u49">
                    <div id="l49" class="s2box bl bb">
                        <s id="z49">0</s><a name="49"></a>49
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u50">
                    <div id="l50" class="s2box br bb">
                        <s id="z50">0</s><a name="50"></a>50
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u88">
                    <div id="l88" class="an s2box br bl">
                        <s id="z88">0</s><a name="88"></a>88
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u59">
                    <div id="l59" class="s2box bb bl">
                        <s id="z59">0</s><a name="59"></a>59
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u58">
                    <div id="l58" class="s2box bt bb">
                        <s id="z58">0</s><a name="58"></a>58
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u57">
                    <div id="l57" class="s2box bt bb">
                        <s id="z57">0</s><a name="57"></a>57
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u56">
                    <div id="l56" class="s2box bb bt">
                        <s id="z56">0</s><a name="56"></a>56
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u55">
                    <div id="l55" class="s2box bt bb">
                        <s id="z55">0</s><a name="55"></a>55
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u54">
                    <div id="l54" class="s2box bt">
                        <s id="z54">0</s><a name="54"></a>54
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u2">
                    <div id="l2" class="s2box">
                        <s id="z2">0</s><a name="2"></a>2
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u94">
                    <div id="l94" class="s2box bt">
                        <s id="z94">0</s><a name="94"></a>94
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u93">
                    <div id="l93" class="s2box bb bt">
                        <s id="z93">0</s><a name="93"></a>93
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u92">
                    <div id="l92" class="s2box bb bt">
                        <s id="z92">0</s><a name="92"></a>92
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u91">
                    <div id="l91" class="s2box bb bt">
                        <s id="z91">0</s><a name="91"></a>91
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u90">
                    <div id="l90" class="s2box bb bt">
                        <s id="z90">0</s><a name="90"></a>90
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u89">
                    <div id="l89" class="s2box br bb">
                        <s id="z89">0</s><a name="89"></a>89
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u53">
                    <div id="l53" class="s2box bb bl">
                        <s id="z53">0</s>53
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u1">
                    <div id="l1" class="s2box">
                        <s id="z1">0</s><a name="1"></a>1
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u52">
                    <div id="l52" class="s2box bb br">
                        <s id="z52">0</s><a name="52"></a>52
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>

    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a1" style="">
                <div id="u101">
                    <div id="l101" class="s2box bl br">
                        <s id="z101">0</s>
                        <em>101</em>
                        <cite>Городские ворота</cite>
                        <a href="{{ route('on_map', array_merge(['s' => 'Gh865Vpo'], request()->except(['s']))) }}#102" class="asouth">↓</a>
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <th colspan="17" class="t0" align="left" style="padding:5px;font-size:14px;"></th>
    </tr>
    </tbody>
</table>
<script>
    var zbmax = 0;
    var zbmin = 0;
</script>
<style>
    .a1 {
        background-color: rgba(255, 247, 205, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
    .a3027 {
        background-color: rgba(210, 240, 185, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
    .a3028 {
        background-color: rgba(233, 233, 233, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
</style>

<script>
    @php
        $user = auth()->user()
    @endphp

    var locInURL = document.location.href.split('#', 2);
    if (locInURL[1] !== undefined) {
        currLocId = locInURL[1];
    } else {
        currLocId = {{ $user?->location_id }};
    }

    var lc = [], ma = [], prevlid = 0, prevlocation = 0, zcurrent;
    if (document.getElementById('z' + currLocId).innerHTML != undefined) {
        zcurrent = document.getElementById('z' + currLocId).innerHTML * 1;
        for (i = zbmin; i <= zbmax; i++) {
            n = i - zcurrent;
            document.getElementById('m' + (i - zbmin)).style.display = (n == 0 ? '' : 'none');
            // document.getElementById('mapz' + (i - zbmin)).innerHTML = (n == 0 ? 'текущая' : (n > 0 ? '+' : '') + n);
        }
    }

    function refreshMap(lid) {
        console.log(lid);
        try {
            ulocation(lid == undefined ? localStorage.getItem('lid') : lid);
        } catch (e) {
        }
    }

    @if($user)
        refreshMap(currLocId)
    @endif

    function gameFocus() {
        window.top.hero.focus();
    }

    function area_show(aid, s) {
        if (s === false || s === true || ma[aid] == undefined || ma[aid] == false) {
            var e = document.getElementsByClassName('a' + aid);
//for(var i in e)if(e[i].style!=undefined)e[i].style.outline=s?'2px solid #403634':'';
            for (var i in e) if (e[i].style !== undefined) e[i].style.backgroundColor = s ? '#D1C1B4' : '';
        }
    }

    function area_click(aid) {
        for (var j in ma) if (j != aid && ma[j]) {
            area_show(j, false);
            ma[j] = false;
        }
        ma[aid] = ma[aid] === undefined || ma[aid] == false;
        area_show(aid, ma[aid]);
    }

    function mapshow() {
        for (i = zbmin; i <= zbmax; i++) {
            n = i - zcurrent;
            document.getElementById('m' + (i - zbmin)).style.display = (n == 0 ? '' : 'none');
        }
    }

    function ms5(nn, lid, lnk) {
        if (lnk != undefined) if (lnk != '') {
            document.location.href = 'map.php?m=' + lnk + '#' + lid;
            return;
        }
        if (lid != undefined) if (lid > 0) mark_lid(lid);//{zcurrent=map_get_lvl(locid[1]);}
        if (nn > 0) if (zcurrent + nn <= zbmax) {
            zcurrent += nn;
            mapshow();
        }
        if (nn < 0) if (zcurrent + nn >= zbmin) {
            zcurrent += nn;
            mapshow();
        }
    }

    function mark_l(lid, s) {
        if ((lc[lid] == undefined || lc[lid] == false) && s < 2) document.getElementById('l' + lid).style.outline = s == 1 ? '4px dotted blue' : '';
        if (s == 2) {
            lc[lid] = lc[lid] == undefined || lc[lid] == false;
            var zl = map_get_lvl(lid);
            if (zcurrent != zl) {
                zcurrent = zl;
                mapshow();
                lc[lid] = true;
            }
            if (lc[lid]) {
                for (var i in lc) if (i != lid && lc[i]) {
                    document.getElementById('l' + i).style.outline = '';
                    lc[i] = false;
                }
            }
            document.getElementById('l' + lid).style.outline = lc[lid] ? '4px dotted blue' : '';
//document.location.href="#"+lid;
            document.getElementById('l' + lid).scrollIntoView({
                block: "center",
                inline: "center"
            });
        }
    }

    function map_get_lvl(lid) {
        var e, t;
        for (var l = zbmin; l <= zbmax; l++) {
            try {
                var t = document.getElementById('m' + (l - zbmin));
                if (t) {
                    e = t.getElementsByClassName('s2box');
//e=t.querySelectorAll('.s2box');
                    for (var i in e) if (e[i].id == 'l' + lid) return l;
                }
            } catch (e) {
            }
        }
        return zbmin;
    }

    function mark_lid(lid) {
        if (prevlid > 0) {
            document.getElementById('l' + prevlid).className = 's2box';
            document.getElementById('l' + prevlid).style.outline = '';
        }
        document.getElementById('l' + lid).className = 'an s2box';
        document.getElementById('l' + lid).style.outline = '4px dotted OrangeRed';
        prevlid = lid;
    }

    function ulocation(lid) {
        if (lid <= 0) return;
        if (prevlocation > 0) document.getElementById('u' + prevlocation).className = '';
        if (document.getElementById('u' + lid) == undefined) {
            document.location.href = '{{ route('on_map', ['hide' => 1]) }}';
        } else {
            zcurrent = map_get_lvl(lid);
            mapshow();
            document.getElementById('u' + lid).className = 'ulocation';
            // document.getElementById('lid').innerText = lid;
            prevlocation = lid;
        }
        // document.getElementById('u' + lid).scrollIntoView({
        //     block: "center",
        //     inline: "center"
        // });
        elementCenter(lid);
    }

    function elementCenter(lid) {
        document.getElementById('u' + lid).scrollIntoView({
            block: "center",
            inline: "center"
        });
    }

    function storeLid(lid) {
        try {
            localStorage.setItem('lid', lid);
        } catch (e) {
        }
    }

    function mark_lid2(lid) {
        document.getElementById('l' + lid).className = 'an s2box';
        document.getElementById('l' + lid).style.outline = '4px dotted OrangeRed';
        document.getElementById('l' + lid).scrollIntoView({
            block: "center",
            inline: "center"
        });
        prevlid = lid;
    }

    locid = document.location.href.split('#', 2);
    if (locid[1] != undefined) {
        locIDs = locid[1].split('-');
        var ll = 0;
        for (var i in locIDs) {
            ll = locIDs[i];
            mark_lid2(ll);
        }
        zcurrent = map_get_lvl(ll);
        mapshow();
    }

    window.addEventListener('message', function(event) {
        const { currentLocationId } = event.data;

        if (currentLocationId !== undefined) {
            storeLid(currentLocationId);
            ulocation(currentLocationId);
        }
    });
</script>
