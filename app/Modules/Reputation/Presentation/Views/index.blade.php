<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->reputation->name }}</title>
    <style>
        html { height: 100%; }
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 14px;
        }
        .w100 {width: 100%}
        a { color: #000000; }
        a:hover { color: #353434; }
        .tbl-shp-sides.ls { background-position: left top; background-repeat: repeat-y; }
        .tbl-shp-sides.rs { background-position: right top; background-repeat: repeat-y; }
        .tbl-shp-sml.rt { background-position: 0 -25px; height: 22px; }
        .tbl-shp-sml.tt { background-position: center -50px; background-repeat: repeat-x; height: 22px; }
        .tbl-shp-sml.lt { background-position: 0 0; height: 22px; }
        .tbl-shp-sml.lb { background-position: 0 -75px; }
        .tbl-shp-sml.bb { background-position: center -125px; background-repeat: repeat-x; height: 18px; }
        .tbl-shp-sml.rb { background-position: 0 -100px; }
        .tbl-shp-sml {
            background: url({{ asset('img/bg/tbl-shp-sml.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-shp-sides {
            background: url({{ asset('img/bg/tbl-shp-sides.png') }}) no-repeat;
            font-size: 0;
        }
        .tbl-usi_bg {
            background-image: url({{ asset('img/bg/tbl-usi_bg.gif') }});
            background-repeat: repeat;
        }
        .btn_1 { color: #461c0b !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .btn_2 { color: #ffe9ba !important; text-decoration: none; font-weight: 700; font-size: 11px; }
        .tbl-usi_label-center {
            background-image: url(/img/bg/info/tbl-usi_label-center.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-weight: bold;
            font-size: 11px;
            color: #FCF5B7;
            padding-left: 10px;
            padding-right: 10px;
            padding-bottom: 6px;
        }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .bg_l2 { background-image: url(/img/bg/info/bg_l2.gif); cursor: pointer; }
        .brd2-bt { border-bottom: 1px solid #DB9F73; }
        .brd2-top { border-top: 1px solid #DB9F73; }
        .rep-progress-bar { position: relative; width: 100%; height: 31px; margin: 6px 0 4px; overflow: hidden; }
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
        .reputation-progress-summary { width: 60%; margin: 0 auto; text-align: center; }
        @media (max-width: 680px) { .reputation-progress-summary { width: 90%; } }
        .reputation-medals { padding: 7px 8px 8px; }
        .reputation-medals__heading {
            margin: 0 0 6px;
            color: #461c0b;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
        }
        .reputation-medals__grid {
            display: flex;
            gap: 9px;
            flex-wrap: wrap;
            justify-content: center;
        }
        .reputation-medal {
            display: flex;
            width: 178px;
            min-height: 196px;
            padding: 5px 7px 8px;
            box-sizing: border-box;
            flex-direction: column;
            align-items: center;
            border: 1px solid #b78350;
            background: rgba(250, 224, 181, .66);
            box-shadow: inset 0 1px rgba(255, 255, 255, .75), 0 1px 1px rgba(87, 45, 20, .16);
            color: #5a3724;
            font-size: 10px;
            line-height: 13px;
            cursor: pointer;
        }
        .reputation-medal.is-earned {
            border-color: #b78a22;
            background: linear-gradient(90deg, rgba(255, 242, 176, .92), rgba(246, 218, 141, .56));
        }
        .reputation-medal.is-feat { border-color: #93659c; }
        .reputation-medal.is-locked { color: #806f65; }
        .reputation-medal__icon {
            width: 120px;
            height: 118px;
            flex: 0 0 118px;
            background: url({{ asset('main/images/user-reward-frame.png') }}) center / 120px 118px no-repeat;
            text-align: center;
        }
        .reputation-medal__icon img {
            width: 100px;
            height: 100px;
            margin-top: 8px;
            object-fit: contain;
        }
        .reputation-medal.is-locked .reputation-medal__icon img {
            opacity: .42;
            filter: grayscale(1);
        }
        .reputation-medal__details { width: 100%; min-width: 0; padding-top: 1px; text-align: center; }
        .reputation-medal__name {
            overflow: hidden;
            color: #6e280d;
            font-weight: bold;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .reputation-medal.is-locked .reputation-medal__name { color: #79685c; }
        .reputation-medal__type { color: #8d643d; font-size: 9px; }
        .reputation-medal__condition { margin-top: 3px; }
        .reputation-medal__state { margin-top: 2px; font-weight: bold; }
        .reputation-medal.is-earned .reputation-medal__state { color: #387629; }
        .reputation-medal.is-locked .reputation-medal__state { color: #9a5a22; }
        .medal-rating { color: #9a2517; font-size: 10px; }
        #artifact_alt .aa-table {
            border-radius: 30px 30px 0 0;
            box-shadow: 3px 3px 3px -1px rgba(0, 0, 0, .2);
            font-size: 11px;
        }
        .aa-tl { width: 14px; height: 24px; background: url(/img/bg/item_info/tbl-pop_corner-top-left.gif) no-repeat; }
        .aa-t { height: 24px; background: url(/img/bg/item_info/tbl-pop_top.gif); }
        .aa-tr { width: 14px; height: 24px; background: url(/img/bg/item_info/tbl-pop_corner-top-right.gif) no-repeat; }
        .aa-l { width: 14px; background: url(/img/bg/item_info/tbl-pop_left.gif) repeat-y; }
        .aa-r { width: 14px; background: url(/img/bg/item_info/tbl-pop_right.gif) repeat-y; }
        .aa-bl { width: 14px; height: 5px; background: url(/img/bg/item_info/tbl-pop_corner-bottom-left.gif) no-repeat; }
        .aa-b { height: 5px; background: url(/img/bg/item_info/tbl-pop_bottom.gif) repeat-x; }
        .aa-br { width: 14px; height: 5px; background: url(/img/bg/item_info/tbl-pop_corner-bottom-right.gif) no-repeat; }
        .list_dark { background-color: #f4bb8a; }
        .skill_list td { padding: 0 7px; }
        .red, .red * { color: #d00000; }
        .msg-success { color: #2a7a2a; font-weight: bold; padding: 4px 6px; }
        .msg-error   { color: #a00000; font-weight: bold; padding: 4px 6px; }
    </style>
</head>
<body>

<div id="artifact_alt" style="width: 300px; display: none; position: fixed; z-index: 10000001; left: 0; top: 0"></div>

<table cellspacing="0" cellpadding="10" width="100%" height="100%">
    <tbody>
    <tr valign="top">
        <td>
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="left">
                        @include('player::partials.tabs', ['group' => 'reputation'])
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">

                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tr height="22">
                                <td align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tr height="22">
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                                            <td align="center" class="tbl-usi_label-center">{{ $page->reputation->name }}</td>
                                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="coll w100 p6h p2v brd2-all">
                            <tbody>

                            @if($page->message)
                                <tr>
                                    <td colspan="3" class="{{ $page->messageType === 'success' ? 'msg-success' : 'msg-error' }}">
                                        {{ $page->message }}
                                    </td>
                                </tr>
                            @endif

                            {{-- Reputation Points & Progress --}}
                            <tr class="bg_l">
                                <td class="brd2-top brd2-bt" colspan="3" style="padding: 6px 10px;">
                                    <div class="reputation-progress-summary">
                                    <b>Очки репутации: {{ $page->pr->points }}</b>
                                    &nbsp;|&nbsp;<b>Репутационный рейтинг: {{ number_format($page->player->reputation_rating, 0, '.', ' ') }}</b>
                                    @if($page->currentTier)
                                        &nbsp;—&nbsp;<span style="color:#461C0B;">{{ $page->currentTier->medal_name ?? 'Без звания' }}</span>
                                        @php
                                            $nextTierItem = $page->reputation->tiers->firstWhere('min_points', '>', $page->pr->points);
                                            $tierMin  = $page->currentTier->min_points;
                                            $tierMax  = $nextTierItem ? $nextTierItem->min_points : ($page->currentTier->max_points ?? $page->pr->points);
                                            $range    = $tierMax - $tierMin;
                                            $pct      = $range > 0 ? min(100, round((($page->pr->points - $tierMin) / $range) * 100)) : 100;
                                        @endphp
                                        <div class="rep-progress-bar" title="{{ $page->pr->points - $tierMin }} из {{ $range > 0 ? $range : '∞' }}">
                                            <div class="rep-progress-bar__bg">
                                                <div class="rep-progress-bar__fill" style="width: {{ $pct }}%;"></div>
                                            </div>
                                            <div class="rep-progress-bar__border">
                                                <div class="rep-progress-bar__border-left"></div>
                                                <div class="rep-progress-bar__border-right"></div>
                                                <div class="rep-progress-bar__border-center"></div>
                                            </div>
                                            <div class="rep-progress-bar__text">{{ $pct }}%</div>
                                        </div>
                                        <small style="color:#555;">
                                            @if($nextTierItem)
                                                до «{{ $nextTierItem->medal_name ?? 'следующего уровня' }}»: {{ $nextTierItem->min_points - $page->pr->points }}
                                            @else
                                                (максимальный уровень)
                                            @endif
                                        </small>
                                    @else
                                        <small style="color:#888;">Нет доступного уровня</small>
                                    @endif
                                    </div>
                                </td>
                            </tr>

                            {{-- Medals --}}
                            @php $tiersWithMedals = $page->reputation->tiers->filter(fn($t) => $t->medal_name || $t->feat_medal_name); @endphp
                            @php $medalTooltipEntries = []; @endphp
                            @if($tiersWithMedals->count())
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" colspan="3" style="padding: 0;">
                                        <div class="reputation-medals">
                                        <div class="reputation-medals__heading">Медали</div>
                                        <div class="reputation-medals__grid">
                                        @foreach($tiersWithMedals as $tier)
                                            @if($tier->medal_name)
                                                @php
                                                    $isEarned = $page->earnedMedals->contains('id', $tier->id);
                                                    $regularRatingReward = $tier->regularMedalRating();
                                                    $needsFeat = $tier->feat_quest_id && ! $tier->feat_medal_name;
                                                    $featRequired = $needsFeat && $page->pr->points >= $tier->min_points;
                                                    $condition = $needsFeat
                                                        ? $tier->min_points.' репутации + подвиг'
                                                        : $tier->min_points.' репутации';
                                                    $state = $isEarned
                                                        ? 'Получена'
                                                        : ($featRequired ? 'Требуется подвиг' : 'Не получена');
                                                    $medalTooltipEntries[] = [
                                                        'image' => $tier->medalIconUrl() ?? asset('img/bg/empty_slot.gif'),
                                                        'name' => $tier->medal_name,
                                                        'reputation' => $page->reputation->name,
                                                        'type' => 'Медаль репутации',
                                                        'rating' => $regularRatingReward,
                                                        'minPoints' => $tier->min_points,
                                                        'earnedAt' => $page->earnedMedalDates[$tier->id.'_regular'] ?? null,
                                                        'description' => $needsFeat ? $tier->feat_description : null,
                                                        'condition' => $condition,
                                                        'state' => $state,
                                                    ];
                                                    $medalIndex = array_key_last($medalTooltipEntries);
                                                @endphp
                                                <div class="reputation-medal {{ $isEarned ? 'is-earned' : 'is-locked' }}" data-medal-index="{{ $medalIndex }}" onmouseover="showReputationMedalInfo(this, event, 2);" onmouseout="showReputationMedalInfo(this, event, 0);">
                                                    <div class="reputation-medal__icon">
                                                        <img src="{{ $tier->medalIconUrl() ?? asset('img/bg/empty_slot.gif') }}" alt="">
                                                    </div>
                                                    <div class="reputation-medal__details">
                                                        <div class="reputation-medal__name">{{ $tier->medal_name }} @if($regularRatingReward > 0)<span class="medal-rating">+{{ $regularRatingReward }}</span>@endif</div>
                                                        <div class="reputation-medal__type">Медаль репутации</div>
                                                        <div class="reputation-medal__condition">{{ $condition }}</div>
                                                        <div class="reputation-medal__state">{{ $state }}</div>
                                                    </div>
                                                </div>
                                            @endif

                                            @if($tier->feat_medal_name)
                                                @php
                                                    $isFeatEarned = $page->earnedFeatMedals->contains('id', $tier->id);
                                                    $featAvailable = $page->pr->points >= $tier->min_points;
                                                    $featCondition = $tier->min_points.' репутации + подвиг';
                                                    $featState = $isFeatEarned
                                                        ? 'Получена'
                                                        : ($featAvailable ? 'Требуется подвиг' : 'Не получена');
                                                    $medalTooltipEntries[] = [
                                                        'image' => $tier->featMedalIconUrl() ?? asset('img/bg/empty_slot.gif'),
                                                        'name' => $tier->feat_medal_name,
                                                        'reputation' => $page->reputation->name,
                                                        'type' => 'Медаль за подвиг',
                                                        'rating' => $tier->featMedalRating(),
                                                        'minPoints' => $tier->min_points,
                                                        'earnedAt' => $page->earnedMedalDates[$tier->id.'_feat'] ?? null,
                                                        'description' => $tier->feat_description,
                                                        'condition' => $featCondition,
                                                        'state' => $featState,
                                                    ];
                                                    $medalIndex = array_key_last($medalTooltipEntries);
                                                @endphp
                                                <div class="reputation-medal is-feat {{ $isFeatEarned ? 'is-earned' : 'is-locked' }}" data-medal-index="{{ $medalIndex }}" onmouseover="showReputationMedalInfo(this, event, 2);" onmouseout="showReputationMedalInfo(this, event, 0);">
                                                    <div class="reputation-medal__icon">
                                                        <img src="{{ $tier->featMedalIconUrl() ?? asset('img/bg/empty_slot.gif') }}" alt="">
                                                    </div>
                                                    <div class="reputation-medal__details">
                                                        <div class="reputation-medal__name">{{ $tier->feat_medal_name }} <span class="medal-rating">+{{ $tier->featMedalRating() }}</span></div>
                                                        <div class="reputation-medal__type">Медаль за подвиг</div>
                                                        <div class="reputation-medal__condition">{{ $featCondition }}</div>
                                                        <div class="reputation-medal__state">{{ $featState }}</div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                        </div>
                                        </div>
                                    </td>
                                </tr>
                            @endif

                            {{-- Active Quest (read-only, no take button) --}}
                            @if($page->activeQuest)
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" width="1%" style="padding: 4px 8px;">
                                        <img src="{{ asset('img/icon/qst_start_ro.gif') }}" width="46" height="28">
                                    </td>
                                    <td class="brd2-top brd2-bt" style="padding: 4px 8px;">
                                        <b>{{ $page->activeQuest->quest->title }}</b>
                                        @foreach($page->activeQuest->quest->objectives as $obj)
                                            @php
                                                $done   = $page->progressMap[$obj->id] ?? 0;
                                                $isDone = $done >= $obj->required_amount;
                                            @endphp
                                            <br><small style="color: {{ $isDone ? '#2a7a2a' : '#555' }};">
                                                {{ $obj->description }} — {{ $done }}/{{ $obj->required_amount }}
                                            </small>
                                        @endforeach
                                    </td>
                                    <td class="brd2-top brd2-bt" align="right" style="padding: 4px 8px;">
                                        <span style="color:#888; font-size:10px;">В процессе</span>
                                    </td>
                                </tr>
                            @elseif($page->cooldownDiff)
                                <tr class="bg_l">
                                    <td class="brd2-top brd2-bt" width="1%" style="padding: 4px 8px;">
                                        <img src="{{ asset('img/icon/qst_start.gif') }}" width="46" height="28" style="opacity:0.45;">
                                    </td>
                                    <td class="brd2-top brd2-bt" style="color:#888; padding: 4px 8px;">
                                        Задание на перезарядке
                                        <br><small style="color:#999;">Доступно через: {{ $page->cooldownDiff }}</small>
                                    </td>
                                    <td class="brd2-top brd2-bt" align="right" style="padding: 4px 8px;">
                                        <span style="color:#aaa; font-size:10px;">Перезарядка</span>
                                    </td>
                                </tr>
                            @endif

                            {{-- Back to list --}}
                            <tr class="bg_l"
                                onclick="location.href='{{ route('reputation.list') }}'"
                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                <td class="brd2-top brd2-bt" width="1%" height="28"></td>
                                <td class="brd2-top brd2-bt">« Все репутации</td>
                                <td class="brd2-top brd2-bt" align="right"></td>
                            </tr>

                            </tbody>
                        </table>

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

<script>
    const reputationMedals = @json($medalTooltipEntries);

    function reputationMedalTooltipEscape(value) {
        const element = document.createElement('div');
        element.textContent = value == null ? '' : String(value);

        return element.innerHTML;
    }

    function renderReputationMedalTooltip(medal) {
        let rows = '';
        rows += '<tr class="skill_list list_dark"><td>Репутация</td><td class="b red" align="right">' + reputationMedalTooltipEscape(medal.reputation) + '</td></tr>';
        rows += '<tr class="skill_list"><td>Условие</td><td class="b red" align="right">' + reputationMedalTooltipEscape(medal.condition) + '</td></tr>';
        rows += '<tr class="skill_list list_dark"><td>Статус</td><td class="b red" align="right">' + reputationMedalTooltipEscape(medal.state) + '</td></tr>';

        if (Number(medal.rating) > 0) {
            rows += '<tr class="skill_list"><td>Рейтинг</td><td class="b red" align="right">+' + Number(medal.rating).toLocaleString('ru-RU') + '</td></tr>';
        }
        if (medal.earnedAt) {
            rows += '<tr class="skill_list list_dark"><td>Дата получения</td><td class="b red" align="right">' + reputationMedalTooltipEscape(medal.earnedAt) + '</td></tr>';
        }
        if (medal.description) {
            rows += '<tr class="skill_list"><td colspan="2" style="padding-top:4px;padding-bottom:4px">' + reputationMedalTooltipEscape(medal.description) + '</td></tr>';
        }

        return '<table width="300" border="0" cellspacing="0" cellpadding="0" style="background-color:#FBD4A4" class="aa-table">'
            + '<tr><td width="14" class="aa-tl"><img src="/img/icon/d.gif" width="14" height="24"><br></td>'
            + '<td class="aa-t aa-table-t" align="center" style="vertical-align:middle"><b class="red">' + reputationMedalTooltipEscape(medal.name) + '</b></td>'
            + '<td width="14" class="aa-tr"><img src="/img/icon/d.gif" width="14" height="24"><br></td></tr>'
            + '<tr><td class="aa-l" style="padding:0"></td><td style="padding:0">'
            + '<table width="275" style="margin:3px" border="0" cellspacing="0" cellpadding="0" class="aa-table-t"><tr>'
            + '<td align="center" valign="middle" width="72" height="71" style="background:url(/main/images/user-reward-frame.png) center/72px 71px no-repeat">'
            + '<img src="' + reputationMedalTooltipEscape(medal.image) + '" alt="" width="60" height="60" border="0"></td>'
            + '<td valign="middle"><div><img src="/img/icon/tbl-shp_item-icon.gif" width="11" height="10" align="absmiddle">&nbsp;'
            + reputationMedalTooltipEscape(medal.type) + '</div></td></tr></table>'
            + '<table class="aa-table-t" width="100%" cellpadding="0" cellspacing="0" border="0">' + rows + '</table>'
            + '</td><td class="aa-r" style="padding:0"></td></tr>'
            + '<tr><td class="aa-bl"></td><td class="aa-b"></td><td class="aa-br"></td></tr></table>';
    }

    function showReputationMedalInfo(element, event, show) {
        const tooltip = document.getElementById('artifact_alt');
        if (!tooltip) return;

        if (!show) {
            tooltip.style.display = 'none';
            document.onmousemove = function () {};
            return;
        }

        const medal = reputationMedals[Number(element.dataset.medalIndex)];
        if (!medal) return;

        if (show === 2) {
            tooltip.innerHTML = renderReputationMedalTooltip(medal);
            tooltip.style.display = 'block';
            document.onmousemove = function (moveEvent) {
                showReputationMedalInfo(element, moveEvent, 1);
            };
        }

        const spacing = 10;
        let x = event.clientX + spacing;
        let y = event.clientY + spacing;
        if (x + tooltip.offsetWidth > window.innerWidth - spacing) {
            x = event.clientX - tooltip.offsetWidth - spacing;
        }
        if (y + tooltip.offsetHeight > window.innerHeight - spacing) {
            y = event.clientY - tooltip.offsetHeight - spacing;
        }

        tooltip.style.left = Math.max(7, x) + 'px';
        tooltip.style.top = Math.max(7, y) + 'px';
    }

    @if($page->messageType === 'success' && $page->message)
        try {
            let experience = parseFloat('{{ $page->player->getPercentExp() }}');
            let lvl = parseInt('{{ $page->player->lvl }}');
            let hp = { current: parseInt('{{ $page->player->hp_now }}'), max: parseInt('{{ $page->player->hp_max }}') };
            let mp = { current: parseInt('{{ $page->player->mp_now }}'), max: parseInt('{{ $page->player->mp_max }}') };
            let money = parseInt('{{ $page->player->user->money }}');
            let diamond = parseInt('{{ $page->player->user->diamond }}');
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
        } catch (e) {}
    @endif
</script>

</body>
</html>
