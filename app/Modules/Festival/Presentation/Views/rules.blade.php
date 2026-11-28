<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    @include('partials.image-tabs-fix')
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('festival.rules_title') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    @include('festival::partials.styles')
</head>
<body>
<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @include('festival::partials.tabs', ['active' => 'rules'])
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <x-game.inset-frame class="quest-frame">
                <div class="active-quest festival-rules">
                    <div class="quest-title">{{ __('festival.rules_title') }}</div>
                    <ol>
                        <li>{{ __('festival.rule_1') }}</li>
                        <li>{{ __('festival.rule_2') }}</li>
                        <li>{{ __('festival.rule_3') }}
                            @foreach($rates as $rate)<b>{{ $rate->item->name }}</b> — {{ $rate->points }}@if(! $loop->last), @else.@endif @endforeach
                        </li>
                        <li>{{ __('festival.rule_4') }}</li>
                        <li>{{ __('festival.rule_5') }}</li>
                        <li>{{ __('festival.rule_6') }}</li>
                        <li>{{ __('festival.rule_7') }}</li>
                    </ol>
                </div>
            </x-game.inset-frame>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td>
        <td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>
</body>
</html>
