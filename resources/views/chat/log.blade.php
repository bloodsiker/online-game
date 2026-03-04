<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            background-color: #FFE4AA;
            color: #000;
            font-family: Tahoma;
            font-size: 11px;
        }
        div.log {
            width: 100%;
            height: 100%;
            vertical-align: top;
            background: url({{ asset('img/bg/chat/fightlog_bg.gif') }});
        }
        table.log {
            width: 100%;
            height: 1%;
            vertical-align: top;
            background: url({{ asset('img/bg/chat/fightlog_bg.gif') }});
        }
        table.log tr.fightlog_light, table.log tr.fightlog_dark {
            height: 15px;
        }
        table.log td {
            padding: 0;
            font-family: Tahoma, Arial;
            font-size: 10px;
            padding-top: 1px;
            vertical-align: top;
            padding-left: 5px;
        }
        table.log tr.fightlog_dark td {
            color: #735f54;
        }
        table.log tr.fightlog_light td {
            color: #fac69f;
        }
        table.log td.separator {
            width: 2px;
            padding: 0;
            background: url({{ asset('img/bg/chat/fightlog_bg.gif') }}) left repeat-y;
        }
        table.log td.result {
            width: 90px;
        }
        table.log tr.separator {
            height: 2px;
        }
        table.log tr.fightlog_light td.result, table.log tr.fightlog_dark td.result, table.log td.result {
            color: #fd8b35;
            background: url({{ asset('img/bg/chat/fightlog_dbg.gif') }});
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0" id="body_content" onload="loaded=true;">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td width="100%" style="" valign="top" class="log">
            <div style="width: 100%; height: 100%;" class="log">
                <table class="log" cellspacing="0" id="content">

                    <tbody>
                    <tr class="fightlog_dark">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span title="Шальной пес [3]">Шальной пе... [3]</span><img
                                src="images/sw1.gif" width="14" height="10"><span title="Tap0K [3]">Tap0K [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk1">уворот</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_light">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span
                                title="Tap0K [3]">Tap0K [3]</span><img src="images/swd2.gif" width="14"
                                                                       height="10"><span title="Шальной пес [3]">Шальной пе... [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk3">-307 удар</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_dark">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span title="Шальной пес [3]">Шальной пе... [3]</span><img
                                src="images/sw3.gif" width="14" height="10"><span title="Tap0K [3]">Tap0K [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk1">уворот</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_light">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span
                                title="Tap0K [3]">Tap0K [3]</span><img src="images/swd3.gif" width="14"
                                                                       height="10"><span title="Шальной пес [3]">Шальной пе... [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk3">-307 удар</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_dark">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span title="Шальной пес [3]">Шальной пе... [3]</span><img
                                src="images/sw2.gif" width="14" height="10"><span title="Tap0K [3]">Tap0K [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk1">уворот</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_light">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span
                                title="Tap0K [3]">Tap0K [3]</span><img src="images/swd2.gif" width="14"
                                                                       height="10"><span title="Шальной пес [3]">Шальной пе... [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk3">-313 удар</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_dark">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span title="Шальной пес [3]">Шальной пе... [3]</span><img
                                src="images/sw2.gif" width="14" height="10"><span title="Tap0K [3]">Tap0K [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk1">уворот</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_light">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span
                                title="Tap0K [3]">Tap0K [3]</span><img src="images/swd3.gif" width="14"
                                                                       height="10"><span title="Шальной пес [3]">Шальной пе... [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk3">-294 удар</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    <tr class="fightlog_dark">
                        <td nowrap="" class="action" style="background: rgb(2, 2, 2);"><span title="Шальной пес [3]">Шальной пе... [3]</span><img
                                src="images/sw1.gif" width="14" height="10"><span title="Tap0K [3]">Tap0K [3]</span>
                        </td>
                        <td class="separator"></td>
                        <td nowrap="" class="result flk1">уворот</td>
                    </tr>
                    <tr class="separator">
                        <td class="s1"></td>
                        <td class="s2"></td>
                        <td class="s3"></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>


</body>
</html>
