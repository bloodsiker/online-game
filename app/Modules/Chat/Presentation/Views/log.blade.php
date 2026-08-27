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
        table.log .battle-entry a {
            color: inherit;
            text-decoration: none;
        }
        table.log .battle-entry a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body leftmargin="0" rightmargin="0" id="body_content">

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td width="100%" style="" valign="top" class="log">
            <div style="width: 100%; height: 100%; overflow-y: auto;" class="log" id="log-scroll">
                <table class="log" cellspacing="0" id="content">
                    <tbody id="log-body"></tbody>
                </table>
            </div>
        </td>
    </tr>
    </tbody>
</table>

<script>
    var maxBattleLogEntries = 200;
    var rowIndex = 0;

    function limitBattleLogEntries(entries) {
        return entries.slice(-maxBattleLogEntries);
    }

    function trimBattleLogDom() {
        var tbody = document.getElementById('log-body');
        if (!tbody) return;

        while (tbody.children.length > maxBattleLogEntries * 2) {
            tbody.removeChild(tbody.firstElementChild);
        }
    }

    function renderEntry(entry) {
        var tbody = document.getElementById('log-body');
        if (!tbody) return;

        var cls = (rowIndex % 2 === 0) ? 'fightlog_dark' : 'fightlog_light';
        rowIndex++;

        var pad = function(n) { return String(n).padStart(2, '0'); };
        var d = new Date(entry.ts);
        var datetime = pad(d.getDate()) + '.' + pad(d.getMonth() + 1) + '.' + d.getFullYear()
            + ' ' + pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds());

        var tr = document.createElement('tr');
        tr.className = cls;
        tr.innerHTML = '<td nowrap="" class="action battle-entry">'
            + datetime + ' &mdash; '
            + '<a href="' + entry.battleUrl + '" target="_blank">Сражение #' + entry.battleId + '</a>'
            + '</td>'
            + '<td class="separator"></td>'
            + '<td nowrap="" class="result battle-entry">' + (entry.monsterName || '') + '</td>';
        tbody.appendChild(tr);

        var sep = document.createElement('tr');
        sep.className = 'separator';
        sep.innerHTML = '<td class="s1"></td><td class="s2"></td><td class="s3"></td>';
        tbody.appendChild(sep);

        var scroll = document.getElementById('log-scroll');
        if (scroll) scroll.scrollTop = scroll.scrollHeight;
    }

    window.addBattleEntry = function(data) {
        var entries = JSON.parse(sessionStorage.getItem('battleLog') || '[]');
        var exists = entries.some(function(e) { return e.battleId === data.battleId; });
        if (!exists) {
            var entry = {
                battleId: data.battleId,
                battleUrl: data.battleUrl,
                monsterName: data.monsterName,
                ts: Date.now(),
            };
            entries.push(entry);
            entries = limitBattleLogEntries(entries);
            sessionStorage.setItem('battleLog', JSON.stringify(entries));
            renderEntry(entry);
            trimBattleLogDom();
        }
    };

    window.addEventListener('message', function(e) {
        if (e.data && e.data.type === 'battle_event') {
            window.addBattleEntry(e.data);
        }
    });

    document.addEventListener('DOMContentLoaded', function() {
        var entries = JSON.parse(sessionStorage.getItem('battleLog') || '[]');
        entries = limitBattleLogEntries(entries);
        sessionStorage.setItem('battleLog', JSON.stringify(entries));
        entries.forEach(function(entry) { renderEntry(entry); });
    });
</script>

</body>
</html>
