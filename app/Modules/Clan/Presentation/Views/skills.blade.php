<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Навыки клана</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        /* font-size задаём через body (наследование), а НЕ через '*': универсальный
           селектор перебивал бы .tbl-shp-sml { font-size: 0 } из main.css у вложенных
           ячеек рамки — картинки лейбла получали строчный отступ под базовую линию,
           строка вырастала выше 22px, а угловые спрайты (no-repeat, 22px) не тянулись,
           из-за чего появлялся разрыв рамки. */
        body { height: 100%; margin: 0; color: #000; font-family: Tahoma; font-size: 12px; }
        a, a:link, a:visited, a:active { text-decoration: none; }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p4v, .p4v td { padding-top: 4px; padding-bottom: 4px; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd, .brd td { border: 1px solid #C49485; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2, .brd2 td { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .skill-card {
            border: 1px solid #DB9F73;
            border-radius: 4px;
            margin-bottom: 6px;
            padding: 6px 8px;
            background-image: url(/img/bg/info/bg_l.gif);
        }
        .skill-name { font-weight: bold; font-size: 12px; color: #461c0b; }
        .skill-level { color: #8D2616; font-weight: bold; }
        .skill-desc { color: #555; margin: 2px 0 4px; }
        .req-ok { color: #2a7a2a; font-weight: bold; }
        .effect-tag { color: #1a4d8a; font-weight: bold; }
        .skill-kind { display: inline-block; margin-left: 4px; padding: 1px 4px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .skill-kind.passive { color: #1a4d8a; background: #dceafb; border: 1px solid #92afd0; }
        .skill-kind.active { color: #8D2616; background: #f7e0d2; border: 1px solid #d5a17e; }
        .w100 { width: 100%; }
        .skill-spell-icon { width:36px; height:36px; object-fit:contain; cursor:pointer; border:1px solid #db9f73; background:#fbd4a4; }
        #skill_effect_alt .aa-table { border-radius: 30px 30px 0 0; box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, .2); font-size: 11px; }
        #skill_effect_alt .aa-tl { background: url(/img/bg/item_info/tbl-pop_corner-top-left.gif) no-repeat; width: 14px; height: 24px; }
        #skill_effect_alt .aa-t { background: url(/img/bg/item_info/tbl-pop_top.gif); height: 24px; }
        #skill_effect_alt .aa-tr { background: url(/img/bg/item_info/tbl-pop_corner-top-right.gif) no-repeat; width: 14px; height: 24px; }
        #skill_effect_alt .aa-l { background: url(/img/bg/item_info/tbl-pop_left.gif) repeat-y; width: 14px; }
        #skill_effect_alt .aa-r { background: url(/img/bg/item_info/tbl-pop_right.gif) repeat-y; width: 14px; }
        #skill_effect_alt .aa-bl { background: url(/img/bg/item_info/tbl-pop_corner-bottom-left.gif) no-repeat; width: 14px; height: 5px; }
        #skill_effect_alt .aa-b { background: url(/img/bg/item_info/tbl-pop_bottom.gif) repeat-x; height: 5px; }
        #skill_effect_alt .aa-br { background: url(/img/bg/item_info/tbl-pop_corner-bottom-right.gif) no-repeat; width: 14px; height: 5px; }
        #skill_effect_alt .skill_list td { padding: 0 7px; }
        #skill_effect_alt .list_dark { background-color: #F4BB8A; }
    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.skills'])

<table class="coll" width="100%" height="100%" border="0" style="margin-top:20px;">
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                <td align="center" class="tbl-usi_label-center">Навыки клана</td>
                                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px;">

                        {{-- Clan info bar --}}
                        <table class="coll w100 p10h brd2-all" border="0" style="margin-bottom: 10px;">
                            <tbody>
                            <tr class="bg_l">
                                <td align="left" nowrap="" style="padding: 4px 10px;">
                                    <b>Клан:</b> {{ $clan->name }}
                                    &nbsp;&nbsp;
                                    <b>Уровень:</b> {{ $clan->lvl }}
                                </td>
                                <td align="right" nowrap="" style="padding: 4px 10px;">
                                    <b>Бонусные очки:</b>
                                    <b class="req-ok">{{ number_format($clan->points, 0, '.', ' ') }}</b>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        {{-- Skills list --}}
                        @forelse($definitions as $def)
                            @php
                                $currentLevel = $learnedMap[$def->id] ?? 0;
                                $currentLevelData = $currentLevel > 0 ? $def->levels->firstWhere('level', $currentLevel) : null;
                                $displayLevelData = $currentLevelData ?? $def->levels->firstWhere('level', 1);
                                $displayMagicSkill = $displayLevelData?->magicSkill;
                                $skillIcon = $displayMagicSkill?->image ?: $def->icon ?: asset('img/icon/qst_default_start_new_m.gif');
                                $skillIconAlt = $displayMagicSkill?->name ?? $def->name;
                                $skillTooltipMeta = $displayMagicSkill ? array_merge([
                                    ['label' => 'Тип', 'value' => $displayMagicSkill->is_passive ? 'Пассивный навык' : 'Активный навык'],
                                    ['label' => 'Уровень', 'value' => $displayMagicSkill->level],
                                ], array_map(static fn (array $effect): array => [
                                    'label' => 'Бонус',
                                    'value' => sprintf('+%s%s %s', $effect['value'] ?? 0, !empty($effect['is_percent']) ? '%' : '', \App\Modules\Clan\Domain\Enums\ClanSkillEffectType::tryFrom($effect['type'] ?? '')?->label() ?? ($effect['type'] ?? '')),
                                ], $displayMagicSkill->effects ?? [])) : [];
                            @endphp

                            <div class="skill-card">
                                <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                    <tbody>
                                    <tr>
                                        {{-- Icon --}}
                                        <td width="40" valign="top" style="padding-right: 8px;">
                                            <img src="{{ $skillIcon }}" class="skill-spell-icon" alt="{{ $skillIconAlt }}"
                                                 @if($displayMagicSkill)
                                                     data-tooltip-container="skill_effect_alt"
                                                     data-tooltip-type="Заклинание"
                                                     data-tooltip-name="{{ $displayMagicSkill->name }}"
                                                     data-tooltip-image="{{ $skillIcon }}"
                                                     data-tooltip-description="{{ strip_tags((string) $displayMagicSkill->description) }}"
                                                     data-tooltip-meta="{!! e(json_encode($skillTooltipMeta, JSON_UNESCAPED_UNICODE)) !!}"
                                                     onmouseover="showSkillEffectInfo(this,event,2)"
                                                     onmouseout="showSkillEffectInfo(this,event,0)"
                                                     onclick="window.open('{{ route('magic_skill.info', $displayMagicSkill->id) }}','','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;"
                                                 @endif>
                                        </td>

                                        {{-- Info --}}
                                        <td valign="top">
                                            <span class="skill-name">{{ $def->name }}</span>
                                            &nbsp;
                                            <span class="skill-level">
                                                Ур. {{ $currentLevel }} / {{ $def->max_level }}
                                            </span>
                                            @if($displayMagicSkill)
                                                <span class="skill-kind {{ $displayMagicSkill->is_passive ? 'passive' : 'active' }}">
                                                    {{ $displayMagicSkill->is_passive ? 'Пассивный' : 'Активный' }}
                                                </span>
                                            @endif

                                            @if($displayMagicSkill)
                                                @foreach($displayMagicSkill->effects ?? [] as $eff)
                                                    &nbsp;—&nbsp;
                                                    <span class="effect-tag">
                                                        +{{ $eff['value'] }}{{ !empty($eff['is_percent']) ? '%' : '' }}
                                                        {{ \App\Modules\Clan\Domain\Enums\ClanSkillEffectType::tryFrom($eff['type'])?->label() ?? $eff['type'] }}
                                                    </span>
                                                @endforeach
                                            @endif

                                            <div class="skill-desc">{{ $def->description }}</div>

                                            @if(!$currentLevel)
                                                <span style="color:#777;">Не изучен</span>
                                            @endif
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        @empty
                            <div align="center" style="padding: 20px; color: #49382d;">
                                <b>Навыки клана пока не добавлены.</b>
                            </div>
                        @endforelse

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
        </td>
    </tr>
    </tbody>
</table>

<div id="skill_effect_alt" style="width:300px;display:none;position:fixed;z-index:10000002;left:0;top:0"></div>
<script src="{{ asset('js/monster_ability_tooltip.js') }}?v={{ filemtime(public_path('js/monster_ability_tooltip.js')) }}"></script>

</body>
</html>
