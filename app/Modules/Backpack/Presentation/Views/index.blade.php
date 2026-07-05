<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
        }
        table.frames {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
        }
        iframe {
            display: block;
            width: 100%;
            height: 100%;
            border: 0;
        }
    </style>
</head>
<body>

<table class="frames" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td width="290px" valign="top">
            <iframe id="equip-frame" name="equip" src="{{ route('backpack.equip') }}"></iframe>
        </td>
        <td valign="top">
            <iframe id="bag-frame" name="bag" src="{{ route('backpack.bag', array_filter(['group' => request('group', 'main'), 'sid' => request('sid')])) }}"></iframe>
        </td>
    </tr>
    </tbody>
</table>

<script>
    // Кросс-фреймовая синхронизация: после «одеть» рюкзак просит обновить экипировку, после «снять» — наоборот
    function reloadEquip() {
        document.getElementById('equip-frame').contentWindow.location.reload();
    }

    function reloadBag() {
        document.getElementById('bag-frame').contentWindow.location.reload();
    }

    // Сообщения от игрового окна (backpack_update и т.п.) пробрасываем внутрь фрейма рюкзака
    window.addEventListener('message', function (e) {
        try {
            document.getElementById('bag-frame').contentWindow.postMessage(e.data, '*');
        } catch (err) {}
    });
</script>

</body>
</html>