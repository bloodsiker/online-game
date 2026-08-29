<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Характеристики клана</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 12px; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .clan-stats { width: 100%; border: 1px solid #DB9F73; border-collapse: collapse; }
        .clan-stats th { width: 42%; padding: 6px 10px; text-align: left; border: 1px solid #DB9F73; }
        .clan-stats td { padding: 6px 10px; border: 1px solid #DB9F73; }
        .progress-bar { position: relative; width: 100%; height: 17px; }
        .progress-bar__bg { position: absolute; right: 3px; left: 3px; top: 0; height: 17px; background: url(/img/bg/progress-bar.png) 0 -51px repeat-x; }
        .progress-bar__red { position: absolute; right: 3px; top: 0; height: 17px; background: url(/img/bg/progress-bar.png) 0 -68px repeat-x; }
        .progress-bar__cover { position: absolute; left: 20px; right: 20px; top: 0; height: 17px; background: url(/img/bg/progress-bar.png) 0 0 repeat-x; }
        .progress-bar__left { position: absolute; left: 0; top: 0; width: 20px; height: 17px; background: url(/img/bg/progress-bar.png) 0 -17px no-repeat; }
        .progress-bar__right { position: absolute; right: 0; top: 0; width: 20px; height: 17px; background: url(/img/bg/progress-bar.png) 0 -34px no-repeat; }
        .progress-bar__marker { position: absolute; top: 0; width: 5px; height: 17px; background: url(/img/bg/progress-bar.png) 0 -85px no-repeat; }
        .progress-bar__txt {
            position: absolute;
            left: 3px;
            right: 3px;
            top: 3px;
            color: #fff;
            font-size: 10px;
            line-height: 11px;
            text-align: center;
            text-shadow: -1px 0 2px #444, 0 1px 2px #444, 1px 0 2px #444, 0 -1px 2px #444,
                         -1px 0 1px #640303, 0 1px 1px #640303, 1px 0 1px #640303, 0 -1px 1px #640303;
        }
        .progress-bar__txt span { color: #fff; }
        .muted { color: #765C4A; }
        .level-value { color: #8D2616; font-size: 15px; font-weight: bold; }
    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.characteristics'])

<table class="coll" width="100%" height="100%" border="0" style="margin-top:20px;">
    <tbody><tr><td valign="top">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"></td>
                <td class="tbl-shp-sml tt" valign="top" align="center">
                    <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
                        <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                        <td align="center" class="tbl-usi_label-center">Характеристики клана</td>
                        <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                    </tr></tbody></table>
                </td>
                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"></td>
            </tr>
            <tr>
                <td class="tbl-shp-sides ls">&nbsp;</td>
                <td class="tbl-usi_bg" valign="top" style="padding:8px 10px;">
                    <table class="clan-stats" cellspacing="0" cellpadding="0">
                        <tbody>
                        @php
                            $rowIndex = 0;
                        @endphp
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>Клан</th><td><b>{{ $clan->name }}</b></td></tr>
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>Уровень клана</th><td><span class="level-value">{{ $clan->lvl }}</span></td></tr>
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>Общий опыт клана</th><td><b>{{ number_format((float) $clan->experience, 2, '.', ' ') }}</b></td></tr>
                        @if($nextLevel)
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>Следующий уровень</th><td>{{ $nextLevel->level }} <span class="muted">(требуется {{ number_format((float) $nextLevel->experience_required, 2, '.', ' ') }} опыта)</span></td></tr>
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>До следующего уровня</th><td><b>{{ number_format($experienceToNextLevel, 2, '.', ' ') }}</b></td></tr>
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}">
                                <th>Прогресс уровня</th>
                                <td>
                                    @php
                                        $levelExperience = max(0, (float) $clan->experience - $currentLevelExperience);
                                        $levelExperienceRequired = max(1, (float) $nextLevel->experience_required - $currentLevelExperience);
                                        $progress = min(100, round($progressPercent));
                                    @endphp
                                    <div class="progress-bar">
                                        <div class="progress-bar__bg"></div>
                                        <div class="progress-bar__red" style="width: {{ 100 - $progress }}%;"></div>
                                        <div class="progress-bar__cover"></div>
                                        <div class="progress-bar__left"></div>
                                        <div class="progress-bar__right"></div>
                                        <div class="progress-bar__marker" style="right: {{ 100 - $progress }}%;"></div>
                                        <div class="progress-bar__txt"><span>{{ number_format($levelExperience, 2, '.', ' ') }}/{{ number_format($levelExperienceRequired, 2, '.', ' ') }}</span></div>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>Прогресс уровня</th><td><b class="grnn">Достигнут максимальный уровень</b></td></tr>
                        @endif
                        </tbody>
                    </table>
                </td>
                <td class="tbl-shp-sides rs">&nbsp;</td>
            </tr>
            <tr height="18">
                <td width="20" align="right" valign="top" class="tbl-shp-sml lb"></td>
                <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
                <td width="20" align="left" valign="top" class="tbl-shp-sml rb"></td>
            </tr>
            </tbody>
        </table>
    </td></tr></tbody>
</table>
</body>
</html>
