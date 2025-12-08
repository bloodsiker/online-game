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
            padding: 0;
            color: #000000;
            font-family: Tahoma;
            font-size: 14px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }
        li {
            list-style-type: none;
            padding-left: 20px;
            background-image: url('{{ asset('img/icon/users-arrow.gif') }}');
            background-repeat: no-repeat;
            background-position: left center;
            background-size: 14px 12px;
        }
        .mb-5 {
            margin-bottom: 5px;
        }
        .color-red {
            color: red;
        }
        .color-green {
            color: green;
        }
        a.r:link, a.r:visited {
            color: red;
        }
        .main-table {
            width: 100%;
            height: 100%;
        }


        .tbl-sts b {
            background: url({{ asset('img/bg/tbl-sts.png') }}) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-lb b {
            background-position: 0 -170px;
        }
        .tbl-sts-rb b {
            background-position: 0 -219px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-lbt b {
            background-position: 0 -150px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }
        .tbl-sts-rbt b {
            background-position: 0 -200px;
            height: 20px;
        }
    </style>
</head>
<body>

<table class="main-table" cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <p><u><b>Раунд N {{ $battle->rounds++ }}</b></u> - <a href="{{ route('info.monster', ['id' => $attackedMonster->locationMonster->id]) }}" target="_blank">{{ $attackedMonster->locationMonster->monster->name }}</a> {{ $attackedMonster->locationMonster->monster->lvl }} ({{ $attackedMonster->locationMonster->hp_now }}/{{ $attackedMonster->locationMonster->hp_max }})</p>

            {!! $round->action !!}

            <p><font color="red">Внимание!</font>  Есть всего 30 минут, пока действует Возрождение! Сферы <font color="#009900">многоразовые</font></p>
            <p>Теперь вы находитесь здесь: <b>{{ $player->user->currentLocation->name }}</b></p>

            <p><a href="{{ route('location') }}">Описание местности</a> »</p>
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
    {{--            var finishFightButton = document.getElementById('finish-fight');--}}
    {{--            if (finishFightButton) {--}}
    {{--                finishFightButton.click();--}}
    {{--            } else {--}}
    {{--                window.parent.sendDataToGame('{{ route('location') }}');--}}
    {{--            }--}}
    {{--            break;--}}
    {{--        default:--}}
    {{--            return;--}}
    {{--    }--}}
    {{--    event.preventDefault();--}}
    {{--});--}}
</script>

<script>
    function deathDebuff() {
        const curses = [
            { id: 'death' + Date.now(), name: 'Возрождеие', duration: 1800 },
        ];
        const curse = curses[Math.floor(Math.random() * curses.length)];

        parent.sendToFrame('character-frame', { curse });
    }

    deathDebuff();
</script>


<script>
    let hp = {
        current: parseInt('{{ $player->hp_now }}'),
        max: parseInt('{{ $player->hp_max }}')
    };
    let mp = {
        current: parseInt('{{ $player->mp_now }}'),
        max: parseInt('{{ $player->mp_max }}')
    };
    let experience = parseFloat('{{ $player->getPercentExp() }}');
    let lvl = parseInt('{{ $player->lvl }}');

    function playerAction() {
        parent.sendToFrame('character-frame', { hp, mp, experience, lvl });

        const currentLocationId = {{ $player->user->location_id }};
        parent.sendToFrame('map-frame', { currentLocationId });
    }
    playerAction();
</script>

</body>
</html>
