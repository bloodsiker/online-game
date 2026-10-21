<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Характеристики клана') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 12px; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .clan-stats { width: 100%; border: 1px solid #DB9F73; border-collapse: collapse; }
        .clan-stats th { width: 42%; padding: 6px 10px; text-align: left; border: 1px solid #DB9F73; }
        .clan-stats td { padding: 6px 10px; border: 1px solid #DB9F73; }
        .rep-progress-bar { position: relative; width: 100%; height: 31px; overflow: hidden; }
        .rep-progress-bar__bg { height: 27px; margin: 2px 5px 0; overflow: hidden; border-radius: 5px; background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -54px repeat-x; }
        .rep-progress-bar__fill { height: 27px; background: url({{ asset('img/progressbar/progress-bar-1-bg.png') }}) 0 -27px repeat-x; }
        .rep-progress-bar__border { position: absolute; top: 0; left: 0; width: 100%; height: 31px; }
        .rep-progress-bar__border-left,
        .rep-progress-bar__border-right,
        .rep-progress-bar__border-center { height: 31px; background: url({{ asset('img/progressbar/progress-bar-1-border.png') }}) no-repeat; }
        .rep-progress-bar__border-left,
        .rep-progress-bar__border-right { position: absolute; top: 0; width: 20px; }
        .rep-progress-bar__border-left { left: 0; }
        .rep-progress-bar__border-right { right: 0; background-position: 0 -31px; }
        .rep-progress-bar__border-center { margin: 0 20px; background-position: 0 -62px; background-repeat: repeat-x; }
        .rep-progress-bar__text { position: absolute; top: 0; left: 0; width: 100%; color: #fff; font-size: 11px; font-weight: bold; line-height: 31px; text-align: center; text-shadow: -1px 0 2px #444, 0 1px 2px #444, 1px 0 2px #444, 0 -1px 2px #444; }
        .muted { color: #765C4A; }
        .level-value { color: #8D2616; font-size: 15px; font-weight: bold; }
        .tax-paid { color: #397323; font-weight: bold; }
        .tax-overdue { color: #8D2616; font-weight: bold; }
    </style>
</head>
<body class="regblk">

@include('clan::partials.tabs', ['activeTab' => 'clan.characteristics'])

<table class="coll" width="100%" height="100%" border="0" style="margin-top:20px;">
    <tbody><tr><td valign="top">
        <table width="100%" border="0" cellspacing="0" cellpadding="0">
            <tbody>
            <tr height="22">
                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"></td>
                <td class="tbl-shp-sml tt" valign="top" align="center">
                    <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
                        <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                        <td align="center" class="tbl-usi_label-center">{{ __('Характеристики клана') }}</td>
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
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('Клан') }}</th><td><b>{{ $clan->name }}</b></td></tr>
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('Дата создания клана') }}</th><td>{{ $clan->created_at?->format('d.m.Y H:i') ?? '—' }}</td></tr>
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('Уровень клана') }}</th><td><span class="level-value">{{ $clan->lvl }}</span></td></tr>
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('Общий опыт клана') }}</th><td><b>{{ number_format((float) $clan->experience, 2, '.', ' ') }}</b></td></tr>
                        <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}">
                            <th>{{ __('Налог оплачен до') }}</th>
                            <td>
                                @if($clan->hasPaidTax())
                                    <span class="tax-paid">{{ $clan->tax_paid_until->format('d.m.Y H:i') }}</span>
                                @elseif($clan->tax_paid_until)
                                    <span class="tax-overdue">{{ __('Просрочен с') }} {{ $clan->tax_paid_until->format('d.m.Y H:i') }}</span>
                                @else
                                    <span class="tax-overdue">{{ __('Не оплачен') }}</span>
                                @endif
                            </td>
                        </tr>
                        @if($nextLevel)
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('Следующий уровень') }}</th><td>{{ $nextLevel->level }} <span class="muted">({{ __('требуется :amount опыта', ['amount' => number_format((float) $nextLevel->experience_required, 2, '.', ' ')]) }})</span></td></tr>
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('До следующего уровня') }}</th><td><b>{{ number_format($experienceToNextLevel, 2, '.', ' ') }}</b></td></tr>
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}">
                                <th>{{ __('Прогресс уровня') }}</th>
                                <td>
                                    @php
                                        $levelExperience = max(0, (float) $clan->experience - $currentLevelExperience);
                                        $levelExperienceRequired = max(1, (float) $nextLevel->experience_required - $currentLevelExperience);
                                        $progress = min(100, round($progressPercent));
                                    @endphp
                                    <div class="rep-progress-bar" title="{{ $progress }}%">
                                        <div class="rep-progress-bar__bg">
                                            <div class="rep-progress-bar__fill" style="width: {{ $progress }}%;"></div>
                                        </div>
                                        <div class="rep-progress-bar__border">
                                            <div class="rep-progress-bar__border-left"></div>
                                            <div class="rep-progress-bar__border-right"></div>
                                            <div class="rep-progress-bar__border-center"></div>
                                        </div>
                                        <div class="rep-progress-bar__text">{{ number_format($levelExperience, 2, '.', ' ') }}/{{ number_format($levelExperienceRequired, 2, '.', ' ') }}</div>
                                    </div>
                                </td>
                            </tr>
                        @else
                            <tr class="{{ $rowIndex++ % 2 === 0 ? 'bg_l' : '' }}"><th>{{ __('Прогресс уровня') }}</th><td><b class="grnn">{{ __('Достигнут максимальный уровень') }}</b></td></tr>
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
