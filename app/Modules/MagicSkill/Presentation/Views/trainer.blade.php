<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $structure->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html, body { min-height: 100%; margin: 0; font-family: Tahoma, Arial, sans-serif; font-size: 11px; color: #000; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .tbl-usi_bg { background: url('/img/bg/tbl-usi_bg.gif') repeat; }
        .brd2-all { border: 1px solid #db9f73; }
        .bg_l { background-image: url('/img/bg/info/bg_l.gif'); }
        .p10h, .p10h td { padding-left: 10px; padding-right: 10px; }
        .p2v, .p2v td { padding-top: 2px; padding-bottom: 2px; }
        .pointer, .pointer input { cursor: pointer; }

        .craft-summary { margin-bottom: 10px; }
        .craft-hint { margin: 0 0 10px; color: #49382d; text-align: center; }
        .upgrade-icon { display: inline-block; width: 60px; height: 60px; padding: 5px 6px 6px; background: url('/main/images/user-reward-frame.png') no-repeat; cursor: pointer; }
        .upgrade-icon img { width: 60px; height: 60px; object-fit: contain; }
        .req-ok { color: #247327 !important; font-weight: bold; }
        .req-fail { color: #a02020 !important; font-weight: bold; }
        .butt1.pointer.disabled { cursor: default; opacity: .5; }
        .butt1.pointer.disabled input { cursor: default; }
        .message { display: inline-block; margin: 0 0 8px; padding: 4px 8px; border: 1px solid #8d2616; color: #8d2616; font-weight: bold; }
        .message.ok { border-color: #247327; color: #247327; }
        .empty-list { padding: 25px; color: #49382d; font-size: 12px; text-align: center; }
        .craft-container { width: 100%; table-layout: fixed; border-spacing: 0; }
        .craft-container .common-inner-block-tl { background: url('/main/images/common-inner-block-tl.png') no-repeat; }
        .craft-container .common-inner-block-tr { background: url('/main/images/common-inner-block-tr.png') no-repeat; }
        .craft-container .common-inner-block-bl { background: url('/main/images/common-inner-block-bl.png') no-repeat; }
        .craft-container .common-inner-block-br { background: url('/main/images/common-inner-block-br.png') no-repeat; }
        .craft-container .common-inner-block-t { background: url('/main/images/common-inner-block-t.png') repeat-x; }
        .craft-container .common-inner-block-b { background: url('/main/images/common-inner-block-b.png') repeat-x; }
        .craft-container .common-inner-block-l { background: url('/main/images/common-inner-block-l.png') repeat-y; }
        .craft-container .common-inner-block-r { background: url('/main/images/common-inner-block-r.png') repeat-y; }
        .craft-container .common-inner-block-bg { background: url('/img/bg/bg_l.gif') repeat; padding: 3px; vertical-align: top; }

        .grimoire { width: 100%; table-layout: fixed; border-spacing: 0; margin-bottom: 12px; }
        .grimoire .gp-frame-tl, .grimoire .gp-frame-tr, .grimoire .gp-frame-bl, .grimoire .gp-frame-br { background: url('/img/bg/common-corners.png') no-repeat; font-size: 0; line-height: 0; }
        .grimoire .gp-frame-t, .grimoire .gp-frame-b { background: url('/img/bg/common-tb.png') repeat-x; font-size: 0; line-height: 0; }
        .grimoire .gp-frame-l, .grimoire .gp-frame-r { background: url('/img/bg/common-lr.png') repeat-y; font-size: 0; line-height: 0; }
        .grimoire .gp-frame-tl, .grimoire .gp-frame-t, .grimoire .gp-frame-l { background-position: 0 0; }
        .grimoire .gp-frame-tr, .grimoire .gp-frame-r { background-position: 100% 0; }
        .grimoire .gp-frame-bl, .grimoire .gp-frame-b { background-position: 0 100%; }
        .grimoire .gp-frame-br { background-position: 100% 100%; }
        .grimoire .gp-frame-bg { background: url('/img/bg/common-bg.png') repeat; padding: 10px; }

        .grimoire__title { margin: 0 0 14px; font-size: 13px; font-weight: bold; text-align: center; }
        .grimoire__row { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: center; gap: 14px; }
        .grimoire__operand { flex: 0 0 92px; width: 92px; min-height: 92px; text-align: center; }
        .grimoire__count { display: block; margin-top: 4px; font-weight: bold; white-space: nowrap; }
        .grimoire__symbol { flex: 0 0 auto; align-self: flex-start; display: flex; align-items: center; height: 71px; color: #8d2616; font-size: 25px; font-weight: bold; }
        .grimoire__skill-icon { text-decoration: none; }
        .grimoire__skill-glyph { display: flex; align-items: center; justify-content: center; width: 60px; height: 60px; border-radius: 4px; font-size: 30px; line-height: 1; }
        .grimoire__skill { flex: 1 1 260px; min-width: 240px; max-width: 420px; text-align: left; }
        .grimoire__skill-name { font-size: 12px; font-weight: bold; }
        .grimoire__skill-name a { text-decoration: none; }
        .grimoire__badges { display: flex; flex-wrap: wrap; gap: 5px; margin: 5px 0 6px; }
        .skill-type-badge { padding: 2px 6px; border: 1px solid rgba(0,0,0,.2); border-radius: 8px; font-size: 10px; font-weight: bold; }
        .badge-buff { background: #2a6e1a; color: #d4f5c8; }
        .badge-heal { background: #1a5e6e; color: #c8f0f5; }
        .badge-attack { background: #6e1a1a; color: #f5c8c8; }
        .badge-debuff { background: #4a1a6e; color: #e0c8f5; }
        .badge-utility { background: #5a4a1a; color: #f5ebc8; }
        .badge-mana { background: #1f3f7a; color: #cfe0ff; }
        .grimoire__lines { color: #553e20; line-height: 16px; }
        .grimoire__lines b { color: #49382d; }
        .grimoire__desc { margin-top: 4px; color: #6b513a; font-style: italic; }
        .grimoire__reqs { flex: 0 0 190px; width: 190px; padding: 5px; box-sizing: border-box; text-align: left; background: url('/img/bg/tbl-usi_bg.gif') repeat; border: 2px solid #e3b360; border-radius: 5px; line-height: 16px; }
        .grimoire__reqs-title { color: #553e20; font-weight: bold; text-align: center; }
        .grimoire__req { display: flex; justify-content: space-between; gap: 6px; }
        .grimoire__action { flex: 0 0 auto; min-width: 120px; min-height: 92px; display: flex; align-items: center; justify-content: center; text-align: center; }
        .grimoire__learned { display: inline-flex; align-items: center; gap: 5px; padding: 7px 14px; color: #1e5e1e; font-weight: bold; background: #e3f3d9; border: 1px solid #7fa865; border-radius: 14px; box-shadow: inset 0 0 0 1px rgba(255,255,255,.5); }
        .grimoire__learned::before { content: '✓'; display: inline-flex; align-items: center; justify-content: center; width: 15px; height: 15px; color: #fff; font-size: 10px; font-weight: bold; background: #4c8f2f; border-radius: 50%; }
    </style>
</head>
<body>

<table width="100%" height="100%" border="0" cellspacing="0" cellpadding="0">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="left">
            @php
                $btnLeft1   = 'img/bg/btn/btn-left1.gif';
                $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
                $btnRight1  = 'img/bg/btn/btn-right1.gif';
                $btnLeft2   = 'img/bg/btn/btn-left2.gif';
                $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
                $btnRight2  = 'img/bg/btn/btn-right2.gif';
            @endphp
            <table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
                <tbody>
                <tr height="21">
                    <td width="19"><img src="{{ asset($btnLeft2) }}" width="19" height="21"></td>
                    <td width="130" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 4px;">
                        <a href="{{ route('mage_trainer', ['id' => $structure->id]) }}" class="btn_2">{{ __('Наставник магии') }}</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight2) }}" width="19" height="21"></td>

                    <td></td>

                    <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"></td>
                    <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 4px;">
                        <a href="{{ route('location') }}" class="btn_1">{{ __('Выход') }}</a>
                    </td>
                    <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:8px 10px; text-align:center;">
            <table class="coll brd2-all bg_l p10h p2v craft-summary" width="100%">
                <tbody><tr>
                    <td align="left"><b>{{ $structure->name }}</b></td>
                    <td align="right" style="color:#955c4a;">
                        <b>{{ __('Колдовство:') }}</b>
                        <b class="redd">{{ $user->player->skills->firstWhere('skill_id', \App\Modules\Skill\Infrastructure\Persistence\Models\Skill::SKILL_MAGIC_ID)?->lvl ?? 0 }}</b>
                        &nbsp;&nbsp;<b>{{ __('Интеллект:') }}</b>
                        <b class="redd">{{ (int) floor($user->player->intelligence) }}</b>
                        &nbsp;&nbsp;<b>{{ __('Мудрость:') }}</b>
                        <b class="redd">{{ (int) floor($user->player->wisdom) }}</b>
                    </td>
                </tr></tbody>
            </table>

            <div id="trainer-message"></div>

            <table class="craft-container" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td width="10" height="10" class="common-inner-block-tl"></td>
                    <td height="10" class="common-inner-block-t"></td>
                    <td width="10" height="10" class="common-inner-block-tr"></td>
                </tr>
                <tr>
                    <td class="common-inner-block-l"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                    <td class="common-inner-block-bg">
                        <div class="craft-hint">{{ __('Прочитайте гримуар, чтобы навсегда выучить его заклинание. Книга расходуется, более высокий ранг заменяет предыдущий.') }}</div>

                        @forelse($books as $book)
                            @php
                                $skill = $book['skill'];
                                $shareItem = $book['shareItem'];
                                $badgeClass = match (true) {
                                    (bool) $skill->is_passive => 'badge-utility',
                                    $skill->type === 'attack' => 'badge-attack',
                                    $skill->type === 'debuff' => 'badge-debuff',
                                    $skill->type === 'heal' => 'badge-heal',
                                    default => 'badge-buff',
                                };
                                $badgeLabel = match (true) {
                                    (bool) $skill->is_passive => __('Пассивка'),
                                    $skill->type === 'attack' => __('Атака'),
                                    $skill->type === 'debuff' => __('Дебафф'),
                                    $skill->type === 'heal' => __('Лечение'),
                                    default => __('Бафф'),
                                };
                            @endphp
                            <table class="grimoire" width="100%" border="0" cellspacing="0" cellpadding="0">
                                <tbody>
                                <tr>
                                    <td width="8" height="10" class="gp-frame-tl"></td>
                                    <td height="10" class="gp-frame-t"></td>
                                    <td width="8" height="10" class="gp-frame-tr"></td>
                                </tr>
                                <tr>
                                    <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                    <td class="gp-frame-bg" align="center">
                                        <div class="grimoire__title" style="color:{{ $shareItem->rarity->color() }}">{{ $shareItem->name }}</div>
                                        <div class="grimoire__row">
                                            <div class="grimoire__operand">
                                                <a class="upgrade-icon" href="{{ route('items.info.share', ['id' => $shareItem->id]) }}"
                                                   data-id="{{ $shareItem->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                   onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                                                    <img src="{{ $shareItem->image }}" alt="{{ $shareItem->name }}">
                                                </a>
                                                <span class="grimoire__count">×{{ $book['count'] }}</span>
                                            </div>

                                            <span class="grimoire__symbol" aria-hidden="true">→</span>

                                            <div class="grimoire__operand">
                                                <a class="upgrade-icon grimoire__skill-icon" href="{{ route('magic_skill.info', $skill->id) }}"
                                                   title="{{ $skill->name }}"
                                                   data-id="magic_skill_{{ $skill->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                   onclick="window.open(this.href, '', 'width=730,height=550,resizable=yes,scrollbars=yes'); return false;">
                                                    @if($skill->image)
                                                        <img src="{{ $skill->image }}" alt="{{ $skill->name }}">
                                                    @else
                                                        <span class="grimoire__skill-glyph {{ $badgeClass }}" aria-hidden="true">✦</span>
                                                    @endif
                                                </a>
                                                <span class="grimoire__count"><span class="skill-type-badge {{ $badgeClass }}">{{ $badgeLabel }}</span></span>
                                            </div>

                                            <div class="grimoire__skill">
                                                <div class="grimoire__skill-name">
                                                    <a href="{{ route('magic_skill.info', $skill->id) }}"
                                                       data-id="magic_skill_{{ $skill->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                                                       onclick="window.open(this.href, '', 'width=730,height=550,resizable=yes,scrollbars=yes'); return false;">{{ $skill->name }}</a>
                                                </div>
                                                @if($skill->mana_cost > 0)
                                                    <div class="grimoire__badges">
                                                        <span class="skill-type-badge badge-mana">{{ $skill->mana_cost }} MP</span>
                                                    </div>
                                                @endif
                                                <div class="grimoire__lines">
                                                    @if($skill->max_damage > 0)
                                                        <div>{{ __('Урон:') }} <b>{{ $skill->min_damage }} – {{ $skill->max_damage }}</b></div>
                                                    @endif
                                                    @if($skill->base_healing > 0)
                                                        <div>{{ __('Лечение:') }} <b>{{ $skill->base_healing }}</b></div>
                                                    @endif
                                                    @if($skill->cooldown > 0)
                                                        <div>{{ __('Перезарядка:') }} <b>{{ $skill->cooldown }} {{ __('сек.') }}</b></div>
                                                    @endif
                                                    @foreach($skill->skillEffects as $effect)
                                                        <div>{{ __('Эффект:') }} <b>{{ $effect->name }}</b>
                                                            <span>({{ $effect->pivot->chance }}%, {{ $effect->pivot->duration_seconds }} {{ __('сек.') }})</span>
                                                            @include('magic-skill::partials.effect-bonuses', ['effect' => $effect])
                                                        </div>
                                                    @endforeach
                                                    @foreach($skill->effects ?? [] as $entry)
                                                        @continue(! is_array($entry) || empty($entry['type']))
                                                        @php
                                                            $statLabel = \App\Modules\Share\Domain\Enums\PassiveBonusStatType::tryFrom($entry['type'])?->label() ?? $entry['type'];
                                                            $sign = ((float) ($entry['value'] ?? 0)) >= 0 ? '+' : '';
                                                        @endphp
                                                        <div>{{ $statLabel }}: <b>{{ $sign }}{{ $entry['value'] ?? 0 }}{{ !empty($entry['is_percent']) ? '%' : '' }}</b></div>
                                                    @endforeach
                                                    @if($skill->description)
                                                        <div class="grimoire__desc">{{ $skill->description }}</div>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="grimoire__reqs">
                                                <div class="grimoire__reqs-title">{{ __('Требования') }}</div>
                                                @forelse($book['requirements'] as $req)
                                                    <div class="grimoire__req">
                                                        <span>{{ $req['label'] }}</span>
                                                        <span class="{{ $req['met'] ? 'req-ok' : 'req-fail' }}">{{ $req['current'] }} / {{ $req['required'] }}</span>
                                                    </div>
                                                @empty
                                                    <div class="req-ok" style="text-align:center;">{{ __('Нет требований') }}</div>
                                                @endforelse
                                            </div>

                                            <div class="grimoire__action">
                                                @if($book['alreadyLearned'])
                                                    <span class="grimoire__learned">{{ __('Уже изучено') }}</span>
                                                @else
                                                    <span class="butt1 pointer {{ $book['canLearn'] ? '' : 'disabled' }}"><span>
                                                        <input type="button" class="grnn learn-book" value="{{ __('Выучить') }}"
                                                               data-share-item-id="{{ $shareItem->id }}" @disabled(! $book['canLearn'])>
                                                    </span></span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
                                </tr>
                                <tr>
                                    <td width="8" height="10" class="gp-frame-bl"></td>
                                    <td height="10" class="gp-frame-b"></td>
                                    <td width="8" height="10" class="gp-frame-br"></td>
                                </tr>
                                </tbody>
                            </table>
                        @empty
                            <div class="empty-list"><b>{{ __('В рюкзаке нет гримуаров. Купите их в Башне магов или найдите в сундуках.') }}</b></div>
                        @endforelse
                    </td>
                    <td class="common-inner-block-r"><img src="{{ asset('main/images/blank.gif') }}" width="10" alt=""></td>
                </tr>
                <tr>
                    <td width="10" height="10" class="common-inner-block-bl"></td>
                    <td height="10" class="common-inner-block-b"></td>
                    <td width="10" height="10" class="common-inner-block-br"></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td class="tbl-shp-sides rs">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" class="tbl-shp-sml lb"><b></b></td>
        <td class="tbl-shp-sml bb">&nbsp;</td>
        <td width="20" class="tbl-shp-sml rb"><b></b></td>
    </tr>
    </tbody>
</table>

<script>
    document.querySelectorAll('.learn-book').forEach(function (button) {
        button.addEventListener('click', function () {
            var btn = this;
            btn.disabled = true;

            fetch('{{ route('magic_skill.learn', ['item' => '__ID__']) }}'.replace('__ID__', btn.dataset.shareItemId), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                },
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                var box = document.getElementById('trainer-message');
                box.innerHTML = '<div class="message' + (data.ok ? ' ok' : '') + '">' + (data.message || '') + '</div>';
                if (data.ok) {
                    window.location.reload();
                } else {
                    btn.disabled = false;
                }
            })
            .catch(function () { btn.disabled = false; });
        });
    });
</script>

{!! $playerStatsScript !!}
{!! $itemTooltipScript !!}
<script src="{{ asset('js/item_tooltip.js') }}?v={{ filemtime(public_path('js/item_tooltip.js')) }}"></script>
</body>
</html>
