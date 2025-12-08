<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Игра</title>
    <style>
        html {
            height: 100%;
            width: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            background-color: #ffe4aa;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        .info-area {
            width: 100%;
            height: 100%;
            position: relative;
            padding: 2px;
        }
        .info {
            margin-bottom: 2px;
        }
        small {
            font-size: 13px;
        }
        a {
            color: #000;
        }
        a.n {
            text-decoration: none;
        }
        a:hover{
            color: #000
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

        .tbl-main_separator-v {
            background-image: url({{ asset('img/bg/separator_v.gif') }});
            background-repeat: repeat-y;
            width: 3px;
        }
        .user_offline {
            color: #B09A8B;
        }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
    <tbody>
    <tr class="lgb" width="100%" height="100%" style="vertical-align: top">
        <td>
            <center><b style="color:green">На локации: {{ $countOnlineLocation }}</b></center>
            <br>

            @foreach($onlineOnLocation as $user)
                <div class="info">
                    @if($user->last_online_at?->timestamp > $tenMinutesAgo->timestamp)
                        <a href="" class="n"><small>{{ $user->last_online_at->format('H:i') }}</small></a>
                        »
                        <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank">{{ $user->name }}</a>
                        {{ $user->player->lvl }}
                        @if(rand(1, 5) == 2)
                            [<a href="https://skazanie.com/info/?cid=NzE=" target="_blank">Elders</a>]
                        @endif
                    @else
                        <span class="user_offline"><small>{{ $user->last_online_at->format('H:i') }}</small>
                        »
                        <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank" class="user_offline">{{ $user->name }}</a>
                        {{ $user->player->lvl }}
                        @if(rand(1, 5) == 2)
                                [<a href="https://skazanie.com/info/?cid=NzE=" target="_blank" class="user_offline">Elders</a>]
                            @endif
                        </span>
                    @endif
                </div>
            @endforeach

            <br>
            <hr>
            <center><b style="color:green">Онлайн: {{ $onlineInGame->count() }}</b></center>

            @foreach($onlineInGame as $user)
                <div class="info">
                    <a href="" class="n"><small>{{ $user->last_online_at->format('H:i') }}</small></a>
                    »
                    <a href="{{ route('info.user', ['id' => $user->id]) }}" target="_blank">{{ $user->name }}</a>
                    {{ $user->player->lvl }}
                    @if(rand(1, 5) == 2)
                        [<a href="https://skazanie.com/info/?cid=NzE=" target="_blank">Elders</a>]
                    @endif
                </div>
            @endforeach
        </td>
    </tr>
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3">
            <img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1">
        </td>
        <td id="td_user_count" width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);">
            <b>Онлайн:</b> <b style="color:#FF0000;" id="chat_user_count">{{ $onlineInGame->count() }}</b>
        </td>
    </tr>
    </tbody>
</table>
{{--<div class="info-area lgb">--}}
{{--    <div class="info">--}}
{{--        <a href="" class="n"><small>14:55</small></a>--}}
{{--         »--}}
{{--        <a href="https://skazanie.com/info/?uid=160786" target="_blank">BlooDlSikeR</a>--}}
{{--        767--}}
{{--        [<a href="https://skazanie.com/info/?cid=NzE=" target="_blank">Elders</a>]--}}
{{--    </div>--}}
{{--    <div class="info">--}}
{{--        <a href="" class="n"><small>14:51</small></a>--}}
{{--        »--}}
{{--        <a href="https://skazanie.com/info/?uid=160786" target="_blank">КрокоДил</a>--}}
{{--    </div>--}}
{{--</div>--}}

</body>
</html>
