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

<table width="1440" cellspacing="1" cellpadding="0" id="m0" class="maptable">
    <tbody>

    <tr style=" @if(request()->has('hide')) display: none;  @endif">
        <th colspan="30" class="t0" align="left" style="padding:5px;font-size:14px;">
            <span style="position:absolute;">Высота/Глубина: <b id="mapz0">текущая</b></span>&nbsp;
        </th>
    </tr>
    <tr>
        <td colspan="30" width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u608">
                    <div id="l608" class="s2box bt bl">
                        <s id="z608">0</s>608
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u607">
                    <div id="l607" class="s2box bt">
                        <s id="z607">0</s>607
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u606">
                    <div id="l606" class="s2box bt bb">
                        <s id="z606">0</s>606
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u605">
                    <div id="l605" class="s2box bt bb">
                        <s id="z605">0</s>605
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u604">
                    <div id="l604" class="s2box bt bb">
                        <s id="z604">0</s>604
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u603">
                    <div id="l603" class="s2box bt">
                        <s id="z603">0</s>603
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u600">
                    <div id="l600" class="s2box bt">
                        <s id="z600">0</s>600
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u599">
                    <div id="l599" class="s2box bt">
                        <s id="z599">0</s>599
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u598">
                    <div id="l598" class="s2box bt br">
                        <s id="z598">0</s>598
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u578">
                    <div id="l578" class="s2box bt bl">
                        <s id="z578">0</s>578
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u577">
                    <div id="l577" class="s2box bt bb">
                        <s id="z577">0</s>577
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u576">
                    <div id="l576" class="s2box bt">
                        <s id="z576">0</s>576
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u574">
                    <div id="l574" class="s2box bt br">
                        <s id="z574">0</s>574
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u570">
                    <div id="l570" class="s2box bt bl">
                        <s id="z570">0</s>570
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u569">
                    <div id="l569" class="s2box bt br">
                        <s id="z569">0</s>569
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u561">
                    <div id="l561" class="s2box bt bl">
                        <s id="z561">0</s>561
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u560">
                    <div id="l560" class="s2box bt bb">
                        <s id="z560">0</s>560
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u559">
                    <div id="l559" class="s2box bt br">
                        <s id="z559">0</s>559
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u517">
                    <div id="l517" class="s2box bt bl">
                        <s id="z517">0</s>517
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u516">
                    <div id="l516" class="s2box bt bb">
                        <s id="z516">0</s>516
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u515">
                    <div id="l515" class="s2box bt br">
                        <s id="z515">0</s>515
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u511">
                    <div id="l511" class="s2box br bl bt">
                        <s id="z511">0</s>
                        <a class="alvl" href="{{ route('on_map', array_merge(['s' => 'Gh865Vpo'], request()->except(['s']))) }}#245">
                            <img src="{{ asset('img/icon/up.gif') }}" hspace="0" vspace="2" border="0" width="26" height="7">
                        </a>
                        <br>
                        511
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u610">
                    <div id="l610" class="s2box bl">
                        <s id="z610">0</s>610
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u609">
                    <div id="l609" class="s2box bb br">
                        <s id="z609">0</s>609
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u602">
                    <div id="l602" class="s2box bl bb">
                        <s id="z602">0</s>602
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u601">
                    <div id="l601" class="s2box bb">
                        <s id="z601">0</s>601
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u596">
                    <div id="l596" class="s2box">
                        <s id="z596">0</s>596
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u597">
                    <div id="l597" class="s2box bb br">
                        <s id="z597">0</s>597
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u579">
                    <div id="l579" class="s2box br bl">
                        <s id="z579">0</s>579
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u575">
                    <div id="l575" class="s2box bb bl">
                        <s id="z575">0</s>575
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u573">
                    <div id="l573" class="s2box bb">
                        <s id="z573">0</s>573
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u572">
                    <div id="l572" class="s2box bb bt">
                        <s id="z572">0</s>572
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u571">
                    <div id="l571" class="s2box bb">
                        <s id="z571">0</s>571
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u568">
                    <div id="l568" class="s2box br">
                        <s id="z568">0</s>568
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u562">
                    <div id="l562" class="s2box br bl">
                        <s id="z562">0</s>562
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u558">
                    <div id="l558" class="s2box bl br">
                        <s id="z558">0</s>558
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u518">
                    <div id="l518" class="s2box br bl">
                        <s id="z518">0</s>518
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u514">
                    <div id="l514" class="s2box bl">
                        <s id="z514">0</s>514
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u513">
                    <div id="l513" class="s2box bb bt">
                        <s id="z513">0</s>513
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u512">
                    <div id="l512" class="s2box bb br">
                        <s id="z512">0</s>512
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u611">
                    <div id="l611" class="s2box bl br">
                        <s id="z611">0</s>611
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u595">
                    <div id="l595" class="s2box bl br">
                        <s id="z595">0</s>595
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u580">
                    <div id="l580" class="s2box br bl">
                        <s id="z580">0</s>580
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u567">
                    <div id="l567" class="s2box bl">
                        <s id="z567">0</s>567
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u566">
                    <div id="l566" class="s2box bt bb">
                        <s id="z566">0</s>566
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u565">
                    <div id="l565" class="s2box bt bb">
                        <s id="z565">0</s>565
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u563">
                    <div id="l563" class="s2box br">
                        <s id="z563">0</s>563
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u557">
                    <div id="l557" class="s2box bl br">
                        <s id="z557">0</s>557
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u519">
                    <div id="l519" class="s2box br bl">
                        <s id="z519">0</s>519
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u520">
                    <div id="l520" class="s2box bl br">
                        <s id="z520">0</s>520
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u612">
                    <div id="l612" class="s2box bl br">
                        <s id="z612">0</s>612
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u594">
                    <div id="l594" class="s2box bl br">
                        <s id="z594">0</s>594
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u581">
                    <div id="l581" class="s2box br bl">
                        <s id="z581">0</s>581
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u586">
                    <div id="l586" class="s2box bt bl">
                        <s id="z586">0</s>586
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u587">
                    <div id="l587" class="s2box bt br">
                        <s id="z587">0</s>587
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u682">
                    <div id="l682" class="s2box bl br">
                        <s id="z682">0</s>682
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u564">
                    <div id="l564" class="s2box bl bb">
                        <s id="z564">0</s>564
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u555">
                    <div id="l555" class="s2box bt">
                        <s id="z555">0</s>555
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u556">
                    <div id="l556" class="s2box bb br">
                        <s id="z556">0</s>556
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u523">
                    <div id="l523" class="s2box bl">
                        <s id="z523">0</s>523
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u522">
                    <div id="l522" class="s2box bt bb">
                        <s id="z522">0</s>522
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u521">
                    <div id="l521" class="s2box bb br">
                        <s id="z521">0</s>521
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u613">
                    <div id="l613" class="s2box bl br">
                        <s id="z613">0</s>613
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u630">
                    <div id="l630" class="s2box bl bt">
                        <s id="z630">0</s>630
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u593">
                    <div id="l593" class="s2box bb">
                        <s id="z593">0</s>593
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u592">
                    <div id="l592" class="s2box bb bt">
                        <s id="z592">0</s>592
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u591">
                    <div id="l591" class="s2box bb bt">
                        <s id="z591">0</s>591
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u582">
                    <div id="l582" class="s2box bb">
                        <s id="z582">0</s>582
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u583">
                    <div id="l583" class="s2box bb bt">
                        <s id="z583">0</s>583
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u584">
                    <div id="l584" class="s2box bb bt">
                        <s id="z584">0</s>584
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u585">
                    <div id="l585" class="s2box">
                        <s id="z585">0</s>585
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u588">
                    <div id="l588" class="s2box br">
                        <s id="z588">0</s>588
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u681">
                    <div id="l681" class="s2box bl br">
                        <s id="z681">0</s>681
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u554">
                    <div id="l554" class="s2box bl br">
                        <s id="z554">0</s>554
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u524">
                    <div id="l524" class="s2box bl br">
                        <s id="z524">0</s>524
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
    </tr>

    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u614">
                    <div id="l614" class="s2box bl br">
                        <s id="z614">0</s>614
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u629">
                    <div id="l629" class="s2box bl br">
                        <s id="z629">0</s>629
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u590">
                    <div id="l590" class="s2box bl bb">
                        <s id="z590">0</s>590
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u589">
                    <div id="l589" class="s2box br bb">
                        <s id="z589">0</s>589
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u680">
                    <div id="l680" class="s2box bl br">
                        <s id="z680">0</s>680
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u553">
                    <div id="l553" class="s2box bl bb">
                        <s id="z553">0</s>553
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u552">
                    <div id="l552" class="s2box br bt">
                        <s id="z552">0</s>552
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u525">
                    <div id="l525" class="s2box bl bb">
                        <s id="z525">0</s>525
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u526">
                    <div id="l526" class="s2box bt bb">
                        <s id="z526">0</s>526
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u527">
                    <div id="l527" class="s2box bt bb">
                        <s id="z527">0</s>527
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u528">
                    <div id="l528" class="s2box bt br">
                        <s id="z528">0</s>528
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
            <div class="a511" style="">
                <div id="u615">
                    <div id="l615" class="s2box bl">
                        <s id="z615">0</s>615
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u622">
                    <div id="l622" class="s2box bt br">
                        <s id="z622">0</s>622
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u626">
                    <div id="l626" class="s2box bl bt">
                        <s id="z626">0</s>626
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u627">
                    <div id="l627" class="s2box bt bb">
                        <s id="z627">0</s>627
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u628">
                    <div id="l628" class="s2box bb">
                        <s id="z628">0</s>628
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u631">
                    <div id="l631" class="s2box bt br">
                        <s id="z631">0</s>631
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u679">
                    <div id="l679" class="s2box bl bb">
                        <s id="z679">0</s>679
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u678">
                    <div id="l678" class="s2box bt bb">
                        <s id="z678">0</s>678
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u677">
                    <div id="l677" class="s2box bt br">
                        <s id="z677">0</s>677
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u551">
                    <div id="l551" class="s2box br bl">
                        <s id="z551">0</s>551
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u529">
                    <div id="l529" class="s2box bl br">
                        <s id="z529">0</s>529
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
            <div class="a511" style="">
                <div id="u616">
                    <div id="l616" class="s2box bl">
                        <s id="z616">0</s>616
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u621">
                    <div id="l621" class="s2box">
                        <s id="z621">0</s>621
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u623">
                    <div id="l623" class="s2box bt bb">
                        <s id="z623">0</s>623
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u624">
                    <div id="l624" class="s2box bt bb">
                        <s id="z624">0</s>624
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u625">
                    <div id="l625" class="s2box br">
                        <s id="z625">0</s>625
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u632">
                    <div id="l632" class="s2box bl">
                        <s id="z632">0</s>632
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u633">
                    <div id="l633" class="s2box bt bb">
                        <s id="z633">0</s>633
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u634">
                    <div id="l634" class="s2box bt br">
                        <s id="z634">0</s>634
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u639">
                    <div id="l639" class="s2box bt bl">
                        <s id="z639">0</s>639
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u640">
                    <div id="l640" class="s2box bt br">
                        <s id="z640">0</s>640
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u676">
                    <div id="l676" class="s2box bl br">
                        <s id="z676">0</s>676
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u550">
                    <div id="l550" class="s2box br bl">
                        <s id="z550">0</s>550
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u536">
                    <div id="l536" class="s2box bt bl">
                        <s id="z536">0</s>536
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u535">
                    <div id="l535" class="s2box bt">
                        <s id="z535">0</s>535
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u530">
                    <div id="l530" class="s2box">
                        <s id="z530">0</s>530
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u531">
                    <div id="l531" class="s2box bt br">
                        <s id="z531">0</s>531
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u617">
                    <div id="l617" class="s2box bl">
                        <s id="z617">0</s>617
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u620">
                    <div id="l620" class="s2box br">
                        <s id="z620">0</s>620
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u649">
                    <div id="l649" class="s2box bl bb">
                        <s id="z649">0</s>649
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u648">
                    <div id="l648" class="s2box bt bb">
                        <s id="z648">0</s>648
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u647">
                    <div id="l647" class="s2box bt">
                        <s id="z647">0</s>647
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u646">
                    <div id="l646" class="s2box br bb">
                        <s id="z646">0</s>646
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u635">
                    <div id="l635" class="s2box bl bb">
                        <s id="z635">0</s>635
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u636">
                    <div id="l636" class="s2box bt bb">
                        <s id="z636">0</s>636
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u637">
                    <div id="l637" class="s2box bt bb">
                        <s id="z637">0</s>637
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u638">
                    <div id="l638" class="s2box">
                        <s id="z638">0</s>638
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u641">
                    <div id="l641" class="s2box br">
                        <s id="z641">0</s>641
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u671">
                    <div id="l671" class="s2box bl bt">
                        <s id="z671">0</s>671
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u672">
                    <div id="l672" class="s2box bt">
                        <s id="z672">0</s>672
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u673">
                    <div id="l673" class="s2box bt">
                        <s id="z673">0</s>673
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u674">
                    <div id="l674" class="s2box br">
                        <s id="z674">0</s>674
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u548">
                    <div id="l548" class="s2box bt bl">
                        <s id="z548">0</s>548
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u549">
                    <div id="l549" class="s2box br">
                        <s id="z549">0</s>549
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u538">
                    <div id="l538" class="s2box bt bl">
                        <s id="z538">0</s>538
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u537">
                    <div id="l537" class="s2box bb">
                        <s id="z537">0</s>537
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u534">
                    <div id="l534" class="s2box bb">
                        <s id="z534">0</s>534
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u533">
                    <div id="l533" class="s2box bb">
                        <s id="z533">0</s>533
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u532">
                    <div id="l532" class="s2box bb br">
                        <s id="z532">0</s>532
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u618">
                    <div id="l618" class="s2box bl bb">
                        <s id="z618">0</s>618
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u619">
                    <div id="l619" class="s2box br bb">
                        <s id="z619">0</s>619
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u650">
                    <div id="l650" class="s2box br bl">
                        <s id="z650">0</s>650
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u645">
                    <div id="l645" class="s2box bl">
                        <s id="z645">0</s>645
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u642">
                    <div id="l642" class="s2box br">
                        <s id="z642">0</s>642
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u670">
                    <div id="l670" class="s2box bl bb">
                        <s id="z670">0</s>670
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u669">
                    <div id="l669" class="s2box bb">
                        <s id="z669">0</s>669
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u668">
                    <div id="l668" class="s2box">
                        <s id="z668">0</s>668
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u675">
                    <div id="l675" class="s2box br bb">
                        <s id="z675">0</s>675
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u547">
                    <div id="l547" class="s2box bl">
                        <s id="z547">0</s>547
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u546">
                    <div id="l546" class="s2box br">
                        <s id="z546">0</s>546
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u539">
                    <div id="l539" class="s2box br bl">
                        <s id="z539">0</s>539
                    </div>
                </div>
            </div>
        </td>
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
            <div class="a511" style="">
                <div id="u651">
                    <div id="l651" class="s2box bb bl">
                        <s id="z651">0</s>651
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u652">
                    <div id="l652" class="s2box bt">
                        <s id="z652">0</s>652
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u653">
                    <div id="l653" class="s2box bb bt">
                        <s id="z653">0</s>653
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u654">
                    <div id="l654" class="s2box bb bt">
                        <s id="z654">0</s>654
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u655">
                    <div id="l655" class="s2box br bt">
                        <s id="z655">0</s>655
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u644">
                    <div id="l644" class="s2box bl bb">
                        <s id="z644">0</s>644
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u643">
                    <div id="l643" class="s2box br bb">
                        <s id="z643">0</s>643
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u666">
                    <div id="l666" class="s2box bt bl">
                        <s id="z666">0</s>666
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u667">
                    <div id="l667" class="s2box bb br">
                        <s id="z667">0</s>667
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u545">
                    <div id="l545" class="s2box bl">
                        <s id="z545">0</s>545
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u542">
                    <div id="l542" class="s2box">
                        <s id="z542">0</s>542
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u541">
                    <div id="l541" class="s2box bb bt">
                        <s id="z541">0</s>541
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u540">
                    <div id="l540" class="s2box br">
                        <s id="z540">0</s>540
                    </div>
                </div>
            </div>
        </td>
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
            <div class="a511" style="">
                <div id="u683">
                    <div id="l683" class="s2box bl br">
                        <s id="z683">0</s>683
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u656">
                    <div id="l656" class="s2box br bl">
                        <s id="z656">0</s>656
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u665">
                    <div id="l665" class="s2box br bl">
                        <s id="z665">0</s>665
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u544">
                    <div id="l544" class="s2box bl bb">
                        <s id="z544">0</s>544
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u543">
                    <div id="l543" class="s2box bb br">
                        <s id="z543">0</s>543
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u818">
                    <div id="l818" class="s2box bb bl">
                        <s id="z818">0</s>818
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u817">
                    <div id="l817" class="s2box bb bt">
                        <s id="z817">0</s>817
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u816">
                    <div id="l816" class="s2box bb bt">
                        <s id="z816">0</s>816
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u814">
                    <div id="l814" class="s2box bt">
                        <s id="z814">0</s>814
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u813">
                    <div id="l813" class="s2box br bt">
                        <s id="z813">0</s>813
                    </div>
                </div>
            </div>
        </td>
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
            <div class="a511" style="">
                <div id="u690">
                    <div id="l690" class="s2box bl bt">
                        <s id="z690">0</s>690
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u689">
                    <div id="l689" class="s2box bt">
                        <s id="z689">0</s>689
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u684">
                    <div id="l684" class="s2box">
                        <s id="z684">0</s>684
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u685">
                    <div id="l685" class="s2box bt br">
                        <s id="z685">0</s>685
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u657">
                    <div id="l657" class="s2box bb bl">
                        <s id="z657">0</s>657
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u658">
                    <div id="l658" class="s2box bt bb">
                        <s id="z658">0</s>658
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u659">
                    <div id="l659" class="s2box bt bb">
                        <s id="z659">0</s>659
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u660">
                    <div id="l660" class="s2box bt">
                        <s id="z660">0</s>660
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u661">
                    <div id="l661" class="s2box bt">
                        <s id="z661">0</s>661
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u662">
                    <div id="l662" class="s2box bt bb">
                        <s id="z662">0</s>662
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u663">
                    <div id="l663" class="s2box">
                        <s id="z663">0</s>663
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u664">
                    <div id="l664" class="s2box bt br">
                        <s id="z664">0</s>664
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
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u815">
                    <div id="l815" class="s2box bl bb">
                        <s id="z815">0</s>815
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u812">
                    <div id="l812" class="s2box br">
                        <s id="z812">0</s>812
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u696">
                    <div id="l696" class="s2box bl bt">
                        <s id="z696">0</s>696
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u695">
                    <div id="l695" class="s2box bt">
                        <s id="z695">0</s>695
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u694">
                    <div id="l694" class="s2box bt bb">
                        <s id="z694">0</s>694
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u693">
                    <div id="l693" class="s2box bt bb">
                        <s id="z693">0</s>693
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u692">
                    <div id="l692" class="s2box bt bb">
                        <s id="z692">0</s>692
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u691">
                    <div id="l691" class="s2box bb">
                        <s id="z691">0</s>691
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u688">
                    <div id="l688" class="s2box bb">
                        <s id="z688">0</s>688
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u687">
                    <div id="l687" class="s2box bb">
                        <s id="z687">0</s>687
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u686">
                    <div id="l686" class="s2box bb br">
                        <s id="z686">0</s>686
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u749">
                    <div id="l749" class="s2box bl">
                        <s id="z749">0</s>749
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u750">
                    <div id="l750" class="s2box bb br">
                        <s id="z750">0</s>750
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u751">
                    <div id="l751" class="s2box bb bl">
                        <s id="z751">0</s>751
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u752">
                    <div id="l752" class="s2box br">
                        <s id="z752">0</s>752
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
            <div class="a511" style="">
                <div id="u811">
                    <div id="l811" class="s2box br bl">
                        <s id="z811">0</s>811
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u697">
                    <div id="l697" class="s2box bl">
                        <s id="z697">0</s>6967
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u698">
                    <div id="l698" class="s2box bb br">
                        <s id="z698">0</s>698
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
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u748">
                    <div id="l748" class="s2box bl br">
                        <s id="z748">0</s>748
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u753">
                    <div id="l753" class="s2box br bl">
                        <s id="z753">0</s>753
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u764">
                    <div id="l764" class="s2box bt bl">
                        <s id="z764">0</s>764
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u765">
                    <div id="l765" class="s2box bt bb">
                        <s id="z765">0</s>765
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u766">
                    <div id="l766" class="s2box bt br">
                        <s id="z766">0</s>766
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u795">
                    <div id="l795" class="s2box bt bl">
                        <s id="z795">0</s>795
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u796">
                    <div id="l796" class="s2box bt br">
                        <s id="z796">0</s>796
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u800">
                    <div id="l800" class="s2box bt bl">
                        <s id="z800">0</s>800
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u810">
                    <div id="l810" class="s2box br">
                        <s id="z810">0</s>810
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u699">
                    <div id="l699" class="s2box bl br">
                        <s id="z699">0</s>699
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
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u747">
                    <div id="l747" class="s2box bl">
                        <s id="z747">0</s>747
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u756">
                    <div id="l756" class="s2box br bt">
                        <s id="z756">0</s>756
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u755">
                    <div id="l755" class="s2box bl bt">
                        <s id="z755">0</s>755
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u754">
                    <div id="l754" class="s2box br">
                        <s id="z754">0</s>754
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u763">
                    <div id="l763" class="s2box br bl">
                        <s id="z763">0</s>763
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u767">
                    <div id="l767" class="s2box bl br">
                        <s id="z767">0</s>767
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u794">
                    <div id="l794" class="s2box bl">
                        <s id="z794">0</s>794
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u797">
                    <div id="l797" class="s2box">
                        <s id="z797">0</s>797
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u798">
                    <div id="l798" class="s2box bb bt">
                        <s id="z798">0</s>798
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u799">
                    <div id="l799" class="s2box bb">
                        <s id="z799">0</s>799
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u809">
                    <div id="l809" class="s2box br">
                        <s id="z809">0</s>809
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u700">
                    <div id="l700" class="s2box bl">
                        <s id="z700">0</s>700
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u708">
                    <div id="l708" class="s2box bt">
                        <s id="z708">0</s>708
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u709">
                    <div id="l709" class="s2box bt bb">
                        <s id="z709">0</s>709
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u710">
                    <div id="l710" class="s2box bt br">
                        <s id="z710">0</s>710
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u714">
                    <div id="l714" class="s2box bl bt">
                        <s id="z714">0</s>714
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u715">
                    <div id="l715" class="s2box br bt">
                        <s id="z715">0</s>715
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u718">
                    <div id="l718" class="s2box bl bt">
                        <s id="z718">0</s>718
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u719">
                    <div id="l719" class="s2box br bt">
                        <s id="z719">0</s>719
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u743">
                    <div id="l743" class="s2box bl bt">
                        <s id="z743">0</s>743
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u744">
                    <div id="l744" class="s2box bb bt">
                        <s id="z744">0</s>744
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u745">
                    <div id="l745" class="s2box bb bt">
                        <s id="z745">0</s>745
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u746">
                    <div id="l746" class="s2box bb">
                        <s id="z746">0</s>746
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u757">
                    <div id="l757" class="s2box bb">
                        <s id="z757">0</s>757
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u758">
                    <div id="l758" class="s2box bb bt">
                        <s id="z758">0</s>758
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u759">
                    <div id="l759" class="s2box">
                        <s id="z759">0</s>759
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u760">
                    <div id="l760" class="s2box bb">
                        <s id="z760">0</s>760
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u761">
                    <div id="l761" class="s2box bb bt">
                        <s id="z761">0</s>761
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u762">
                    <div id="l762" class="s2box bb">
                        <s id="z762">0</s>762
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u769">
                    <div id="l769" class="s2box bt">
                        <s id="z769">0</s>769
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u768">
                    <div id="l768" class="s2box bb br">
                        <s id="z768">0</s>768
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u793">
                    <div id="l793" class="s2box bl">
                        <s id="z793">0</s>793
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u792">
                    <div id="l792" class="s2box br">
                        <s id="z792">0</s>792
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u808">
                    <div id="l808" class="s2box br bl">
                        <s id="z808">0</s>808
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u701">
                    <div id="l701" class="s2box bl">
                        <s id="z701">0</s>701
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u707">
                    <div id="l707" class="s2box br bb">
                        <s id="z707">0</s>707
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u711">
                    <div id="l711" class="s2box bb bl">
                        <s id="z711">0</s>711
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u712">
                    <div id="l712" class="s2box bb bt">
                        <s id="z712">0</s>712
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u713">
                    <div id="l713" class="s2box br bb">
                        <s id="z713">0</s>713
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u716">
                    <div id="l716" class="s2box bl bb">
                        <s id="z716">0</s>716
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u717">
                    <div id="l717" class="s2box br bb">
                        <s id="z717">0</s>717
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u720">
                    <div id="l720" class="s2box bl bb">
                        <s id="z720">0</s>720
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u721">
                    <div id="l721" class="s2box bt bb">
                        <s id="z721">0</s>721
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u722">
                    <div id="l722" class="s2box br">
                        <s id="z722">0</s>722
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u780">
                    <div id="l780" class="s2box bl br">
                        <s id="z780">0</s>780
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u770">
                    <div id="l770" class="s2box bl br">
                        <s id="z770">0</s>770
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u790">
                    <div id="l790" class="s2box bl">
                        <s id="z790">0</s>790
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u791">
                    <div id="l791" class="s2box br bb">
                        <s id="z791">0</s>791
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u807">
                    <div id="l807" class="s2box br bl">
                        <s id="z807">0</s>807
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u702">
                    <div id="l702" class="s2box bl br">
                        <s id="z702">0</s>702
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
            <div class="a511" style="">
                <div id="u723">
                    <div id="l723" class="s2box br bl">
                        <s id="z723">0</s>723
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u779">
                    <div id="l779" class="s2box bl br">
                        <s id="z779">0</s>779
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u771">
                    <div id="l771" class="s2box bl br">
                        <s id="z771">0</s>771
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u789">
                    <div id="l789" class="s2box bl br">
                        <s id="z789">0</s>789
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u806">
                    <div id="l806" class="s2box br bl">
                        <s id="z806">0</s>806
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u703">
                    <div id="l703" class="s2box bl">
                        <s id="z703">0</s>703
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u706">
                    <div id="l706" class="s2box br bt">
                        <s id="z706">0</s>706
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u740">
                    <div id="l740" class="s2box bl bt">
                        <s id="z740">0</s>740
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u739">
                    <div id="l739" class="s2box bt">
                        <s id="z739">0</s>739
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u738">
                    <div id="l738" class="s2box bt bb">
                        <s id="z738">0</s>738
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u737">
                    <div id="l737" class="s2box bt br">
                        <s id="z737">0</s>737
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u733">
                    <div id="l733" class="s2box bt bl">
                        <s id="z733">0</s>733
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u724">
                    <div id="l724" class="s2box">
                        <s id="z724">0</s>724
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u725">
                    <div id="l725" class="s2box bt">
                        <s id="z725">0</s>725
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u726">
                    <div id="l726" class="s2box bt">
                        <s id="z726">0</s>726
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u727">
                    <div id="l727" class="s2box bt br">
                        <s id="z727">0</s>727
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u781">
                    <div id="l781" class="s2box bl bt">
                        <s id="z781">0</s>781
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u778">
                    <div id="l778" class="s2box">
                        <s id="z778">0</s>778
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u777">
                    <div id="l777" class="s2box bt">
                        <s id="z777">0</s>777
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u774">
                    <div id="l774" class="s2box bt">
                        <s id="z774">0</s>774
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u773">
                    <div id="l773" class="s2box bt bb">
                        <s id="z773">0</s>773
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u772">
                    <div id="l772" class="s2box bb">
                        <s id="z772">0</s>772
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u784">
                    <div id="l784" class="s2box bt br">
                        <s id="z784">0</s>784
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u788">
                    <div id="l788" class="s2box bl br">
                        <s id="z788">0</s>788
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u805">
                    <div id="l805" class="s2box br bl">
                        <s id="z805">0</s>805
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>
    <tr>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u704">
                    <div id="l704" class="s2box bl bb">
                        <s id="z704">0</s>704
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u705">
                    <div id="l705" class="s2box br bb">
                        <s id="z705">0</s>705
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u741">
                    <div id="l741" class="s2box bl bb">
                        <s id="z741">0</s>741
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u742">
                    <div id="l742" class="s2box bb br">
                        <s id="z742">0</s>742
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u736">
                    <div id="l736" class="s2box bb bl">
                        <s id="z736">0</s>736
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u735">
                    <div id="l735" class="s2box bb bt">
                        <s id="z735">0</s>735
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u734">
                    <div id="l734" class="s2box bb bt">
                        <s id="z734">0</s>734
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u732">
                    <div id="l732" class="s2box bb">
                        <s id="z732">0</s>732
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u731">
                    <div id="l731" class="s2box bb">
                        <s id="z731">0</s>731
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u730">
                    <div id="l730" class="s2box bb">
                        <s id="z730">0</s>730
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u729">
                    <div id="l729" class="s2box bb">
                        <s id="z729">0</s>729
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u728">
                    <div id="l728" class="s2box bb br">
                        <s id="z728">0</s>728
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u782">
                    <div id="l782" class="s2box bl bb">
                        <s id="z782">0</s>782
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u783">
                    <div id="l783" class="s2box bb">
                        <s id="z783">0</s>783
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u776">
                    <div id="l776" class="s2box bb">
                        <s id="z776">0</s>776
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u775">
                    <div id="l775" class="s2box bb br">
                        <s id="z775">0</s>775
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
        <td width="48" height="48"></td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u785">
                    <div id="l785" class="s2box bb bl">
                        <s id="z785">0</s>785
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u786">
                    <div id="l786" class="s2box bb bt">
                        <s id="z786">0</s>786
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u787">
                    <div id="l787" class="s2box bb">
                        <s id="z787">0</s>787
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u801">
                    <div id="l801" class="s2box bb bt">
                        <s id="z801">0</s>801
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u802">
                    <div id="l802" class="s2box bb bt">
                        <s id="z802">0</s>802
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u803">
                    <div id="l803" class="s2box bb bt">
                        <s id="z803">0</s>803
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48">
            <div class="a511" style="">
                <div id="u804">
                    <div id="l804" class="s2box br bb">
                        <s id="z804">0</s>804
                    </div>
                </div>
            </div>
        </td>
        <td width="48" height="48"></td>
    </tr>

    <tr>
        <td colspan="30" rowspan="23" width="48" height="48"></td>
    </tr>


    </tbody>
</table>
<script>
    var zbmax = 0;
    var zbmin = 0;
</script>
<style>
    .a1 {
        background-color: rgba(216, 216, 216, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
    .a2 {
        background-color: rgba(210, 240, 185, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
    .a3 {
        background-color: rgba(233, 233, 233, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
    .a4 {
        background-color: rgba(206, 209, 253, 0.6);
        margin: 0px;
        padding: 0px;
        border-spacing: 0px;
    }
    .a511 {
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
            document.getElementById('mapz' + (i - zbmin)).innerHTML = (n == 0 ? 'текущая' : (n > 0 ? '+' : '') + n);
        }
    }

    function refreshMap(lid) {
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
            for (var i in e) if (e[i].style != undefined) e[i].style.backgroundColor = s ? '#D1C1B4' : '';
        }
    }

    function area_click(aid) {
        for (var j in ma) if (j != aid && ma[j]) {
            area_show(j, false);
            ma[j] = false;
        }
        ma[aid] = ma[aid] == undefined || ma[aid] == false;
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
