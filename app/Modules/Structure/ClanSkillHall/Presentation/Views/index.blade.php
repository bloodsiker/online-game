<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $hall->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .tbl-usi_bg { background: url(/img/bg/tbl-usi_bg.gif) repeat; }
        .tbl-shp-sml { background: url(/img/bg/tbl-shp-sml.png) no-repeat; font-size: 0; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sides { background: url(/img/bg/tbl-shp-sides.png) no-repeat; font-size: 0; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-usi_label-center { background: url(/img/bg/info/tbl-usi_label-center.gif) repeat-x; height: 19px; color: #FCF5B7; font-weight: bold; font-size: 11px; padding: 0 10px 3px; }
        .brd2-all { border: 1px solid #DB9F73; }
        .brd2-top { border-top: 1px solid #DB9F73; }
        .brd2-bt { border-bottom: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .skill-grid { margin: -6px; font-size: 0; text-align: center; }
        .skill-card { display: inline-block; vertical-align: top; width: 250px; min-height: 310px; margin: 6px; padding: 8px; box-sizing: border-box; font-size: 11px; text-align: center; background: url(/img/bg/bgg.gif) repeat; border-radius: 5px; box-shadow: 0 0 3px rgba(0,0,0,.9); }
        .skill-title { min-height: 30px; color: #7a3010; font-size: 13px; font-weight: bold; }
        .skill-level { display: block; margin-top: 2px; color: #8D2616; font-weight: bold; }
        .skill-icon { display: inline-block; width: 60px; height: 60px; margin: 7px auto 5px; padding: 5px 6px 6px; background: url(/main/images/user-reward-frame.png) no-repeat; }
        .skill-icon img { width: 60px; height: 60px; object-fit: cover; }
        .skill-desc { min-height: 46px; margin: 5px 0 8px; color: #49382d; line-height: 15px; }
        .skill-requirements { min-height: 86px; padding: 5px; box-sizing: border-box; text-align: left; background: url(/img/bg/tbl-usi_bg.gif) repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .req-title { color: #553e20; font-weight: bold; text-align: center; }
        .requirement-items { display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; margin-top: 4px; }
        .requirement-item { width: 42px; text-align: center; line-height: 12px; }
        .requirement-item img { width: 36px; height: 36px; object-fit: cover; border: 1px solid #9a713e; vertical-align: middle; }
        .requirement-count { display: block; margin-top: 1px; font-size: 10px; white-space: nowrap; }
        .req-ok { color: #247327; font-weight: bold; }
        .req-fail { color: #a02020; font-weight: bold; }
        .skill-effect { min-height: 20px; margin: 3px 0; color: #1a4d8a; font-weight: bold; }
        .skill-kind { display: table; margin: 2px auto 4px; padding: 1px 5px; border-radius: 3px; font-weight: bold; }
        .skill-kind.passive { color: #1a4d8a; background: #dceafb; border: 1px solid #92afd0; }
        .skill-kind.active { color: #8D2616; background: #f7e0d2; border: 1px solid #d5a17e; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid; font-weight: bold; }
        .message.success { color: #247327; border-color: #247327; }
        .message.error { color: #a02020; border-color: #a02020; }
    </style>
</head>
<body>
<table border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr height="21">
        <td width="19"><img src="{{ asset('img/bg/btn/btn-left2.gif') }}" width="19" height="21" alt=""></td>
        <td align="center" nowrap style="background:url({{ asset('img/bg/btn/btn-cent2.gif') }}) center top repeat-x;padding:0 2px 6px;">
            <a href="{{ route('clan_skill_hall', ['id' => $hall->id]) }}" class="btn_2">Скилы</a>
        </td>
        <td width="19"><img src="{{ asset('img/bg/btn/btn-right2.gif') }}" width="19" height="21" alt=""></td>
        <td width="100%"></td>
        <td width="19"><img src="{{ asset('img/bg/btn/btn-left1.gif') }}" width="19" height="21" alt=""></td>
        <td align="center" nowrap style="background:url({{ asset('img/bg/btn/btn-cent1.gif') }}) center top repeat-x;padding:0 2px 6px;">
            <a href="{{ route('location') }}" class="btn_1">Вернуться в локацию</a>
        </td>
        <td width="19"><img src="{{ asset('img/bg/btn/btn-right1.gif') }}" width="19" height="21" alt=""></td>
    </tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top:10px;">
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="center">
            <table border="0" cellspacing="0" cellpadding="0"><tr height="22">
                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                <td align="center" class="tbl-usi_label-center">{{ $hall->name }}</td>
                <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
            </tr></table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h" width="100%" style="margin-bottom:10px;"><tr>
                <td align="left"><b>Клан:</b> {{ $clan->name }} &nbsp; <b>Уровень:</b> {{ $clan->lvl }}</td>
                <td align="right"><b>Бонусные очки:</b> <span class="req-ok">{{ number_format($clan->points, 0, '.', ' ') }}</span></td>
            </tr></table>

            @if(session('success'))<div class="message success">{{ session('success') }}</div>@endif
            @if(session('message'))<div class="message error">{{ session('message') }}</div>@endif

            <div class="skill-grid">
                @forelse($definitions as $definition)
                    @php
                        $currentLevel = $learnedMap[$definition->id] ?? 0;
                        $nextLevel = $currentLevel + 1;
                        $isMaxed = $currentLevel >= $definition->max_level;
                        $next = $isMaxed ? null : $definition->levels->firstWhere('level', $nextLevel);
                        $current = $currentLevel > 0 ? $definition->levels->firstWhere('level', $currentLevel) : null;
                        $displayMagicSkill = $current?->magicSkill ?? $next?->magicSkill;
                        $requirements = $next?->itemRequirements ?? collect();
                        if ($requirements->isEmpty() && $next?->share_item_id) {
                            $requirements = collect([(object) ['share_item_id' => $next->share_item_id, 'count' => $next->share_item_count ?? 1, 'shareItem' => $next->stoneItem]]);
                        }
                        $hasClanLevel = $next !== null && $clan->lvl >= $next->required_clan_level;
                        $hasPoints = $next !== null && $clan->points >= $next->required_bonus_points;
                        $hasItems = $requirements->every(fn ($requirement) => $backpackShareItemCounts->get($requirement->share_item_id, 0) >= $requirement->count);
                        $canLearnThis = $next !== null && $hasClanLevel && $hasPoints && $hasItems;
                    @endphp
                    <div class="skill-card">
                        <div class="skill-title">{{ $definition->name }}<span class="skill-level">Уровень {{ $currentLevel }} / {{ $definition->max_level }}</span></div>
                        <div class="skill-icon">
                            <img src="{{ $definition->icon ?: asset('img/icon/qst_default_start_new_m.gif') }}" alt="{{ $definition->name }}">
                        </div>
                        @if($displayMagicSkill)
                            <span class="skill-kind {{ $displayMagicSkill->is_passive ? 'passive' : 'active' }}">
                                {{ $displayMagicSkill->is_passive ? 'Пассивный навык' : 'Активный навык' }}
                            </span>
                        @endif
                        <div class="skill-desc">{{ $definition->description }}</div>
                        <div class="skill-effect">
                            @foreach($next?->magicSkill?->effects ?? [] as $effect)
                                +{{ $effect['value'] }}{{ !empty($effect['is_percent']) ? '%' : '' }} {{ \App\Modules\Clan\Domain\Enums\ClanSkillEffectType::tryFrom($effect['type'])?->label() ?? $effect['type'] }}@if(!$loop->last)<br>@endif
                            @endforeach
                            @if($isMaxed)Максимальный уровень@endif
                        </div>
                        @if(!$isMaxed && $next)
                            <div class="skill-requirements">
                                <div class="req-title">Требования для уровня {{ $nextLevel }}</div>
                                <div>Уровень: <span class="{{ $hasClanLevel ? 'req-ok' : 'req-fail' }}">{{ $clan->lvl }} / {{ $next->required_clan_level }}</span></div>
                                <div>Очки: <span class="{{ $hasPoints ? 'req-ok' : 'req-fail' }}">{{ number_format($clan->points, 0, '.', ' ') }} / {{ number_format($next->required_bonus_points, 0, '.', ' ') }}</span></div>
                                @if($requirements->isNotEmpty())
                                    <div class="requirement-items">
                                        @foreach($requirements as $requirement)
                                            @php
                                                $count = $backpackShareItemCounts->get($requirement->share_item_id, 0);
                                                $item = $requirement->shareItem;
                                            @endphp
                                            <div class="requirement-item" title="{{ $item?->name ?? 'Предмет' }}"
                                                 data-id="{{ $requirement->share_item_id }}"
                                                 onmouseover="showItemInfo(this,event,2)"
                                                 onmouseout="showItemInfo(this,event,0)">
                                                <img src="{{ $item?->image ?? asset('img/bg/empty_slot.gif') }}" alt="{{ $item?->name ?? 'Предмет' }}">
                                                <span class="requirement-count {{ $count >= $requirement->count ? 'req-ok' : 'req-fail' }}">{{ $count }} / {{ $requirement->count }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            @if($canLearn)
                                <form method="post" action="{{ route('clan_skill_hall.learn', ['id' => $hall->id, 'skillId' => $definition->id]) }}" style="margin-top:7px;">
                                    @csrf
                                    <b class="butt2 pointer {{ !$canLearnThis ? 'disabled' : '' }}"><b><input type="submit" value="{{ $currentLevel ? 'Улучшить' : 'Изучить' }}" {{ !$canLearnThis ? 'disabled' : '' }}></b></b>
                                </form>
                            @endif
                        @endif
                    </div>
                @empty
                    <div style="padding:25px; font-size:12px; color:#49382d;"><b>Клановые навыки пока не добавлены.</b></div>
                @endforelse
            </div>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18"><td class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb">&nbsp;</td><td class="tbl-shp-sml rb"><b></b></td></tr>
</table>
<script>
    function equalizeSkillRequirements() {
        const requirements = Array.from(document.querySelectorAll('.skill-requirements'));
        if (requirements.length === 0) return;

        requirements.forEach((element) => { element.style.height = ''; });
        const maxHeight = Math.max(...requirements.map((element) => element.offsetHeight));
        requirements.forEach((element) => { element.style.height = maxHeight + 'px'; });
    }

    window.addEventListener('load', equalizeSkillRequirements);
    window.addEventListener('resize', equalizeSkillRequirements);
</script>
{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</body>
</html>
