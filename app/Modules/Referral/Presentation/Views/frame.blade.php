<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рефералы</title>
    <style>
        html { height: 100%; width: 100%; }
        body {
            height: 100%;
            margin: 0;
            background-color: #ffe4aa;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
        }
        .info { margin-bottom: 4px; color: #554848; }
        .pnick {
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            color: #674F3D;
        }
        a { color: #000; }
        a:hover { color: #000; }
        .lgb { background-image: url({{ asset('img/bg/lgb.gif') }}); background-repeat: repeat; }
        .lgb-left { background-image: url({{ asset('img/icon/lgb-left.gif') }}); background-repeat: repeat-y; width: 14px; }
        .lgb-right { background-image: url({{ asset('img/icon/lgb-right.gif') }}); background-repeat: repeat-y; width: 15px; }
        .tbl-main_separator-v { background-image: url({{ asset('img/bg/separator_v.gif') }}); background-repeat: repeat-y; width: 3px; }
        .lvl { font-size: 11px; color: #5c4030; }
        .claims { font-size: 10px; color: #7a5c3e; }
        .empty { padding: 6px; color: #7a6050; }
    </style>
</head>
<body>
<table cellpadding="0" cellspacing="0" width="100%" height="100%" border="0">
    <tbody>
    <tr class="lgb" width="100%" height="100%" style="vertical-align: top">
        <td width="1%" class="lgb-left" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
        <td>
            <center><b style="color:green">Приглашённых: {{ count($frame->referrals) }}</b></center>
            <br>

            @forelse($frame->referrals as $referral)
                <div class="info">
                    <a href="{{ route('info.user', ['id' => $referral->userId]) }}" target="_blank"
                       class="pnick" title="Информация о персонаже"><b>{{ $referral->name }}</b></a>
                    <span class="lvl">[{{ $referral->level }} ур.]</span>
                    @if($referral->claimedRewardsCount > 0)
                        <span class="claims">&mdash; наград: {{ $referral->claimedRewardsCount }}</span>
                    @endif
                </div>
            @empty
                <div class="empty">Вы ещё никого не пригласили</div>
            @endforelse
        </td>
        <td width="1%" class="lgb-right" style="background-position-y: -5px;"><img src="{{ asset('img/icon/d.gif') }}" width="15" height="1"><br></td>
    </tr>
    <tr style="position: absolute; bottom: 0; left: -3px">
        <td class="tbl-main_separator-v" width="3">
            <img src="{{ asset('img/bg/separator_v.gif') }}" width="3" height="1">
        </td>
        <td width="100%" height="18" style="background: url({{ asset('img/bg/tbl-main_users-bottom.gif') }}) repeat-x; padding-left: 14px; color: rgb(66, 42, 23);">
            <b>Рефералов:</b> <b style="color:#FF0000;">{{ count($frame->referrals) }}</b>
        </td>
    </tr>
    </tbody>
</table>
</body>
</html>
