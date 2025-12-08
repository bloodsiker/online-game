<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        body {
            margin: 0;
            /*background-color: #92fbe8;*/
            background-color: #ffe4aa;
            color: #000;
            /*color: #ece5e5;*/
            font-family: Tahoma;
            font-size: 13px;
        }
        .chat-area {
            width: 100%;
            height: 100%;
            position: relative;
            padding: 5px;
        }
        .message {
            margin-bottom: 1px;
        }
        small {
            font-size: 13px;
        }
        a {
            color: navy;
            /*color: #990000;*/
        }
        a.n {
            text-decoration: none;
        }
        a:hover{
            color: navy
        }
        .lgb {
            background-image: url({{ asset('img/bg/lgb.gif') }});
            background-repeat: repeat;
        }
        .lgb-left {
            background-image: url({{ asset('img/icon/lgb-left.gif') }});
            background-repeat: repeat-y;
            width: 14px;
        }
        .lgb-right {
            background-image: url({{ asset('img/icon/lgb-right.gif') }});
            background-repeat: repeat-y;
            width: 15px;
        }
    </style>
</head>
<body>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr>
        <td width="1%" class="lgb-left">
            <img src="{{ asset('img/icon/d.gif') }}" width="14" height="1"><br>
        </td>
        <td width="100%" class="lgb" valign="top">
            <div class="chat-area lgb">
                <div class="message">
                    <a href="" class="n"><small>14:55</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Жемчужный</a>
                    - не задохнитесь в пещере с сундуком)) а то вас тут целая шайка))
                </div>
                <div class="message">
                    <a href="" class="n"><small>14:51</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">КрокоДил</a>
                    - А это где такое счатье обнаружилось ?
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:14</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Demogorgon</a>
                    - поздравляю!( новая карта впереди
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:11</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">zeman</a>
                    - На месте недавнего сражения вы нашли предмет "Призрачная бронза" и убрали его в мешок.
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:10</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Demogorgon</a>
                    - это про другую) на перстень) из силуэта выбивается
                </div>
                <div class="message">
                    <a href="" class="n"><small>14:55</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Жемчужный</a>
                    - не задохнитесь в пещере с сундуком)) а то вас тут целая шайка))
                </div>
                <div class="message">
                    <a href="" class="n"><small>14:51</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">КрокоДил</a>
                    - А это где такое счатье обнаружилось ?
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:14</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Demogorgon</a>
                    - поздравляю!( новая карта впереди
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:11</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">zeman</a>
                    - На месте недавнего сражения вы нашли предмет "Призрачная бронза" и убрали его в мешок.
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:10</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Demogorgon</a>
                    - это про другую) на перстень) из силуэта выбивается
                </div>
                <div class="message">
                    <a href="" class="n"><small>14:55</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Жемчужный</a>
                    - не задохнитесь в пещере с сундуком)) а то вас тут целая шайка))
                </div>
                <div class="message">
                    <a href="" class="n"><small>14:51</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">КрокоДил</a>
                    - А это где такое счатье обнаружилось ?
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:14</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Demogorgon</a>
                    - поздравляю!( новая карта впереди
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:11</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">zeman</a>
                    - На месте недавнего сражения вы нашли предмет "Призрачная бронза" и убрали его в мешок.
                </div>
                <div class="message">
                    <a href="" class="n"><small>13:10</small></a>
                    »
                    <a href="https://skazanie.com/info/?uid=160786" target="_blank">Demogorgon</a>
                    - это про другую) на перстень) из силуэта выбивается
                </div>
            </div>
        </td>
        <td width="1%" id="dragLine" class="lgb-right">
            <img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br>
        </td>
    </tr>
    </tbody>
</table>

</body>
</html>
