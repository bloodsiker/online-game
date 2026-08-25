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

<table width="843" cellspacing="1" cellpadding="0" id="m0" class="maptable">
    <tbody>
    <tr style=" @if(request()->has('hide')) display: none; @endif">
        <th colspan="13" class="t0" align="left" style="padding:5px;font-size:14px;"></th>
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
            <div class="a819" style="">
                <div id="u819">
                    <div id="l819" class="s2box br bl bt">
                        <s id="z819">0</s>
                        <a class="alvl" href="{{ route('on_map', array_merge(['s' => '1p0OH76'], request()->except(['s']))) }}#6">
                            <img src="{{ asset('img/icon/up.gif') }}" hspace="0" vspace="2" border="0" width="26" height="7">
                        </a>
                        <br>
                        819
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
            <div class="a819" style="">
                <div id="u820">
                    <div id="l820" class="s2box bt bl">
                        <s id="z820">0</s>820
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u821">
                    <div id="l821" class="s2box">
                        <s id="z821">0</s><a name="821"></a>821
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u822">
                    <div id="l822" class="s2box bt br">
                        <s id="z822">0</s><a name="822"></a>822
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
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u828">
                    <div id="l828" class="s2box bt bl">
                        <s id="z828">0</s>828
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u827">
                    <div id="l827" class="s2box bt bb">
                        <s id="z827">0</s>827
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u826">
                    <div id="l826" class="s2box bt bb">
                        <s id="z826">0</s>826
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u825">
                    <div id="l825" class="s2box bt bb">
                        <s id="z825">0</s>825
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u824">
                    <div id="l824" class="s2box bb">
                        <s id="z824">0</s>824
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u823">
                    <div id="l823" class="s2box bb">
                        <s id="z823">0</s><a name="823"></a>823
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u838">
                    <div id="l838" class="s2box bb">
                        <s id="z838">0</s><a name="838"></a>838
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u839">
                    <div id="l839" class="s2box bt bb">
                        <s id="z839">0</s>839
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u840">
                    <div id="l840" class="s2box bt bb">
                        <s id="z840">0</s>840
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u841">
                    <div id="l841" class="s2box bt bb">
                        <s id="z841">0</s>841
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u842">
                    <div id="l842" class="s2box br bt">
                        <s id="z842">0</s>842
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>

    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u829">
                    <div id="l829" class="s2box br bl">
                        <s id="z829">0</s>829
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
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u843">
                    <div id="l843" class="s2box br bl">
                        <s id="z843">0</s>843
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>

    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u830">
                    <div id="l830" class="s2box br bl">
                        <s id="z830">0</s>830
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u864">
                    <div id="l864" class="s2box bt bl">
                        <s id="z864">0</s>864
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u862">
                    <div id="l862" class="s2box bt">
                        <s id="z862">0</s>862
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u860">
                    <div id="l860" class="s2box bt">
                        <s id="z860">0</s>860
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u859">
                    <div id="l859" class="s2box bt">
                        <s id="z859">0</s>859
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u857">
                    <div id="l857" class="s2box bt br">
                        <s id="z857">0</s>857
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u844">
                    <div id="l844" class="s2box br bl">
                        <s id="z844">0</s>844
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u831">
                    <div id="l831" class="s2box br bl">
                        <s id="z831">0</s>831
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u865">
                    <div id="l865" class="s2box bl">
                        <s id="z865">0</s>865
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u863">
                    <div id="l863" class="s2box bb br">
                        <s id="z863">0</s>863
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u861">
                    <div id="l861" class="s2box bl br">
                        <s id="z861">0</s>861
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u858">
                    <div id="l858" class="s2box bl bb">
                        <s id="z858">0</s>858
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u856">
                    <div id="l856" class="s2box br">
                        <s id="z856">0</s>856
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u845">
                    <div id="l845" class="s2box br bl">
                        <s id="z845">0</s>845
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u833">
                    <div id="l833" class="s2box bt bb bl">
                        <s id="z833">0</s>833
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u832">
                    <div id="l832" class="s2box">
                        <s id="z832">0</s>832
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u836">
                    <div id="l836" class="s2box bt bb">
                        <s id="z836">0</s>836
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u837">
                    <div id="l837" class="s2box bt bb">
                        <s id="z837">0</s>837
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u866">
                    <div id="l866" class="s2box">
                        <s id="z866">0</s>866
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u869">
                    <div id="l869" class="s2box bb bt">
                        <s id="z869">0</s>869
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u870">
                    <div id="l870" class="s2box">
                        <s id="z870">0</s>870
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u855">
                    <div id="l855" class="s2box bt bb">
                        <s id="z855">0</s>855
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u852">
                    <div id="l852" class="s2box">
                        <s id="z852">0</s>852
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u851">
                    <div id="l851" class="s2box bt bb">
                        <s id="z851">0</s>851
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u850">
                    <div id="l850" class="s2box bt bb">
                        <s id="z850">0</s>850
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u846">
                    <div id="l846" class="s2box">
                        <s id="z846">0</s>846
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u847">
                    <div id="l847" class="s2box bt bb br">
                        <s id="z847">0</s>847
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
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u834">
                    <div id="l834" class="s2box br bl">
                        <s id="z834">0</s>834
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u867">
                    <div id="l867" class="s2box bl bb">
                        <s id="z867">0</s>867
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u868">
                    <div id="l868" class="s2box bb br bt">
                        <s id="z868">0</s>868
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u871">
                    <div id="l871" class="s2box bl br">
                        <s id="z871">0</s>871
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u854">
                    <div id="l854" class="s2box bl bb bt">
                        <s id="z854">0</s>854
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u853">
                    <div id="l853" class="s2box br bb">
                        <s id="z853">0</s>853
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u848">
                    <div id="l848" class="s2box br bl">
                        <s id="z848">0</s>848
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>

    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u835">
                    <div id="l835" class="s2box br bl bb">
                        <s id="z835">0</s>835
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u872">
                    <div id="l872" class="s2box br bl bb">
                        <s id="z872">0</s>872
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u849">
                    <div id="l849" class="s2box br bl bb">
                        <s id="z849">0</s>849
                    </div>
                </div>
            </div>
        </td>
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
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u877">
                    <div id="l877" class="s2box bt bl bb">
                        <s id="z877">0</s>877
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u876">
                    <div id="l876" class="s2box br bt">
                        <s id="z876">0</s>876
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u873">
                    <div id="l873" class="s2box br bl bt">
                        <s id="z873">0</s>873
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u879">
                    <div id="l879" class="s2box bl bt">
                        <s id="z879">0</s>879
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u880">
                    <div id="l880" class="s2box br bt bb">
                        <s id="z880">0</s>880
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
            <div class="a819" style="">
                <div id="u875">
                    <div id="l875" class="s2box bl bb">
                        <s id="z875">0</s>875
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u874">
                    <div id="l874" class="s2box">
                        <s id="z874">0</s>874
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u878">
                    <div id="l878" class="s2box br bb">
                        <s id="z878">0</s>878
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
            <div class="a819" style="">
                <div id="u881">
                    <div id="l881" class="s2box bl br">
                        <s id="z881">0</s>881
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
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a819" style="">
                <div id="u882">
                    <div id="l882" class="s2box bl br bb">
                        <s id="z882">0</s>882
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
        <th colspan="13" class="t0" align="left" style="padding:5px;font-size:14px;"></th>
    </tr>
    </tbody>
</table>
<script>
    var zbmax = 0;
    var zbmin = 0;
</script>
<style>
    .a819 {
        /*background-color: rgba(255, 247, 205, 0.6);*/
        background-color: rgba(169, 245, 195, 0.6);
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
        currLocId = {{ $user?->location_id ?? 0 }};
    }

    var lc = [], ma = [], prevlid = 0, prevlocation = 0, zcurrent;
    var currentLocationElement = document.getElementById('z' + currLocId);
    if (currentLocationElement !== null) {
        zcurrent = currentLocationElement.innerHTML * 1;
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
            @if(request()->routeIs('on_map'))
            document.location.href = '{{ route('on_map', ['hide' => 1]) }}';
            @endif
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
