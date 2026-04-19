<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Квесты</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html {
            height: 100%;
        }
        body {
            height: 100%;
            margin: 0;
            font-family: Tahoma;
            font-size: 11px;
        }
        a {
            color: #000000;
        }
        a:hover{
            color: #353434
        }

        table[cellpadding="0"][cellpadding="0"][border="0"] {
            border-spacing: 0;
            border-collapse: collapse;
        }

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
            padding-bottom: 3px;
        }
        table.coll {
            border-collapse: collapse;
            border-spacing: 0;
        }
        .brd2-all {
            border: 1px solid #DB9F73;
        }
        .bg_l {
            background-image: url(/img/bg/info/bg_l.gif);
        }
        .bg_l2 {
            background-image: url(/img/bg/info/bg_l2.gif);
            cursor: pointer;
        }

        .tbl-shp_menu-center-inact {
            background-image: url(/img/bg/btn/tbl-shp_menu-center-inact.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            padding-left: 4px;
            padding-right: 4px;
            padding-bottom: 2px;
        }
        .tbl-shp_menu-center-act {
            background-image: url(/img/bg/btn/tbl-shp_menu-center-act.gif);
            background-repeat: repeat-x;
            height: 19px;
            font-family: Tahoma;
            font-size: 11px;
            font-weight: bold;
            text-decoration: none;
            padding-left: 4px;
            padding-right: 4px;
            padding-bottom: 2px;
            color: #FFE9BA;
            font-weight: bold;
            width: auto;
        }
        .tbl-shp_menu-link_act {
            color: #FFE9BA !important;
            text-decoration: none;
        }
        .tbl-shp_menu-link_inact {
            color: #461C0B !important;
            text-decoration: none;
        }
        .tbl-sts_top {
            background-image: url(/img/bg/btn/tbl-sts_top.gif);
            background-repeat: repeat-x;
            background-position: bottom;
            height: 19px;
        }
        .tbl-sts b {
            background: url(/img/bg/tbl-sts.png) no-repeat;
            display: block;
            height: 19px;
            overflow: hidden;
            width: 19px;
        }
        .tbl-sts-lt b {
            background-position: 0 -50px;
        }
        .tbl-sts-rt b {
            background-position: 0 -100px;
        }
        .tbl-sts-ltb b {
            background-position: 0 -69px;
            height: 20px;
        }
        .tbl-sts-rtb b {
            background-position: 0 -119px;
            height: 20px;
        }
        .pagination-wrap {
            padding: 4px 2px;
            text-align: center;
        }
        .pagination-wrap a, .pagination-wrap span {
            display: inline-block;
            padding: 1px 5px;
            margin: 0 1px;
            border: 1px solid #DB9F73;
            font-size: 11px;
            font-family: Tahoma;
            text-decoration: none;
            color: #461C0B;
        }
        .pagination-wrap span.current {
            background-image: url(/img/bg/btn/tbl-shp_menu-center-act.gif);
            background-repeat: repeat-x;
            color: #FFE9BA;
            font-weight: bold;
            border-color: #9A5C2E;
        }
        .pagination-wrap span.disabled {
            color: #aaa;
            border-color: #e0c0a0;
        }



    </style>
</head>
<body class="regcolor">

@php
    $btnLeft1 = 'img/bg/btn/btn-left1.gif';
    $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
    $btnRight1 = 'img/bg/btn/btn-right1.gif';

    $btnLeft2 = 'img/bg/btn/btn-left2.gif';
    $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
    $btnRight2 = 'img/bg/btn/btn-right2.gif';

    $tabs = [
        'started'    => 'Взятые',
        'repeatable' => 'Повторяющиеся',
        'completed'  => 'Завершенные',
        'clan'       => 'Клановые',
    ];
@endphp

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    <tr height="21">
        @foreach($tabs as $tabKey => $tabLabel)
            @php $isActive = $tab === $tabKey; @endphp
            <td width="19"><img src="{{ asset($isActive ? $btnLeft2 : $btnLeft1) }}" width="19" height="21"><br></td>
            <td width="80" align="center" style="background: url({{ asset($isActive ? $btnCenter2 : $btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
                <a href="{{ route('quests', ['tab' => $tabKey]) }}" title="{{ $tabLabel }}" class="{{ $isActive ? 'btn_2' : 'btn_1' }}">{{ $tabLabel }}</a>
            </td>
            <td width="19"><img src="{{ asset($isActive ? $btnRight2 : $btnRight1) }}" width="19" height="21"><br></td>
        @endforeach

        <td></td>

        <td width="19"><img src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="2%" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a href="{{ route('location') }}" title="Вернуться" class="btn_1">Вернуться</a></td>
        <td width="19"><img src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table class="coll w-100" cellspacing="0" cellpadding="5" width="100%" height="100%">
    <tbody>
    <tr height="13">
        <td valign="top"></td>
    </tr>
    <tr>
        <td valign="top">
            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr>
                    <td>
                        <span class="butt1 pointer ">
                            <span><input value="Свернуть/Развернуть" type="button" onclick="quest_folding.toggle_all();"></span>
                        </span>
                    </td>
                </tr>
                </tbody>
            </table>

            <table class="coll w100" height="100%">
                <tbody>
                <tr>
                    <td valign="top" width="100%">
                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                            <tbody>
                            <tr height="22">
                                <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                                <td class="tbl-shp-sml tt" valign="top" align="center">
                                    <table border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr height="22">
                                            <td width="27">
                                                <img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22">
                                            </td>
                                            <td align="center" class="tbl-usi_label-center">{{ $tabs[$tab] }}</td>
                                            <td width="27"
                                            ><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22">
                                            </td>
                                        </tr>
                                        </tbody>
                                    </table>
                                </td>
                                <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                            </tr>
                            <tr>
                                <td class="tbl-shp-sides ls">&nbsp;</td>
                                <td class="tbl-usi_bg" valign="top" align="left" style="padding: 6px 4px 6px 4px">
                                    @php
                                        function renderQuestReward($reward): string {
                                            return match($reward->type) {
                                                \App\Modules\Quest\Domain\Enums\QuestRewardType::EXP             => '+' . $reward->amount . ' опыта',
                                                \App\Modules\Quest\Domain\Enums\QuestRewardType::MONEY           => '+' . $reward->amount . ' монет',
                                                \App\Modules\Quest\Domain\Enums\QuestRewardType::ITEM            => ($reward->amount > 1 ? $reward->amount . 'x ' : '') . ($reward->itemInfo?->name ?? 'предмет'),
                                                \App\Modules\Quest\Domain\Enums\QuestRewardType::LOCATION_ACCESS => 'Открыт доступ: ' . ($reward->location?->name ?? 'локация'),
                                                \App\Modules\Quest\Domain\Enums\QuestRewardType::CLAN_POINTS        => '+' . $reward->amount . ' клановых очков',
                                                \App\Modules\Quest\Domain\Enums\QuestRewardType::REPUTATION_POINTS  => '+' . $reward->amount . ' очков репутации',
                                            };
                                        }
                                    @endphp

                                    @if($tab === 'clan')
                                        {{-- CLAN QUEST LIST --}}
                                        <table class="coll w100 p6h p2v brd2-all">
                                            <tbody>
                                            @forelse($clanQuests as $cp)
                                                <tr class="bg_l" onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                    <td class="brd2-top" width="1">
                                                        <img id="image_open_quest_{{ $cp->id }}" onclick="quest_folding.toggle({{ $cp->id }});"
                                                             src="{{ asset('img/icon/qst_minus.gif') }}" width="9" height="9" border="0">
                                                    </td>
                                                    <td class="brd2-top b" onclick="quest_folding.toggle({{ $cp->id }});">
                                                        {{ $cp->quest->title }}
                                                        <span style="color:#777; font-weight:normal;">— взял: {{ $cp->user->name }}</span>
                                                    </td>
                                                    <td class="brd2-top" align="right">
                                                        @if($cp->status === \App\Modules\Quest\Domain\Enums\QuestPlayerStatus::IN_PROGRESS)
                                                            @if(auth()->id() === (int)$cp->clan->owner_id)
                                                                <form id="clan-cancel-{{ $cp->id }}" method="POST" action="{{ route('quest.clan.cancel', $cp->id) }}" style="display:inline;">
                                                                    @csrf
                                                                    <b class="butt2 pointer">
                                                                        <b><input value="Отменить" type="button" onclick="return top.systemConfirm('Вы действительно хотите отменить клановый квест?','Действие',false,function(){document.getElementById('clan-cancel-{{ $cp->id }}').submit();})"></b>
                                                                    </b>
                                                                </form>
                                                            @else
                                                                <span style="color:#c8990a;">В процессе</span>
                                                            @endif
                                                        @elseif($cp->status === \App\Modules\Quest\Domain\Enums\QuestPlayerStatus::COMPLETED)
                                                            @if($cp->reset_at && now()->lt($cp->reset_at))
                                                                <span style="color:#888;">Доступен через {{ $cp->reset_at->locale('ru')->diffForHumans(now(), true, false, 2) }}</span>
                                                            @else
                                                                <span style="color:#4a7a1e;">Выполнен</span>
                                                            @endif
                                                        @endif
                                                    </td>
                                                </tr>
                                                <tr class="bg_l" id="quest_{{ $cp->id }}">
                                                    <td></td>
                                                    <td colspan="2">
                                                        <div class="ajustify b">{!! $cp->quest->description !!}</div>
                                                        @php
                                                            $stages = $cp->quest->stages;
                                                            $hasStages = $stages->isNotEmpty();
                                                            $isCompleted = $cp->status === \App\Modules\Quest\Domain\Enums\QuestPlayerStatus::COMPLETED;
                                                        @endphp
                                                        @if($hasStages)
                                                            @foreach($stages as $stage)
                                                                @php
                                                                    $isDone    = $isCompleted || ($cp->current_stage_id !== null && $stage->order < $cp->currentStage?->order);
                                                                    $isCurrent = !$isCompleted && $stage->id === $cp->current_stage_id;
                                                                @endphp
                                                                @if(!$isDone && !$isCurrent) @continue @endif
                                                                <div style="margin-top:4px; {{ $isDone ? 'color:#999!important;' : 'color:#461C0B;' }} font-weight:bold;">
                                                                    @if($isDone) ✓ @endif
                                                                    Этап {{ $stage->order }}{{ $stage->title ? ': ' . $stage->title : '' }}
                                                                </div>
                                                                @php $stageObjs = $cp->objectives->filter(fn($o) => $o->questObjective->stage_id === $stage->id); @endphp
                                                                @foreach($stageObjs as $obj)
                                                                    @php $done = $obj->questObjective->type === 'deliver' ? $obj->questObjective->required_amount : $obj->amount; @endphp
                                                                    <div class="b" style="{{ $isDone ? 'color:#999;' : 'color:#8B0000;' }} margin-left:10px;">
                                                                        {{ $obj->questObjective->description }} ({{ $done }}/{{ $obj->questObjective->required_amount }})
                                                                    </div>
                                                                @endforeach
                                                            @endforeach
                                                        @else
                                                            @php $stageObjectives = $cp->currentStageObjectives(); @endphp
                                                            @foreach($stageObjectives as $obj)
                                                                @php $done = $obj->questObjective->type === 'deliver' ? $obj->questObjective->required_amount : $obj->amount; @endphp
                                                                <div class="redd b">
                                                                    {{ $obj->questObjective->description }} ({{ $done }}/{{ $obj->questObjective->required_amount }})
                                                                </div>
                                                            @endforeach
                                                        @endif
                                                        @if($isCompleted && $cp->quest->rewards->isNotEmpty())
                                                            <table class="coll" style="margin-top:3px;">
                                                                <tbody>
                                                                @foreach($cp->quest->rewards as $reward)
                                                                    <tr class="b" style="color:#339900!important;">
                                                                        <td nowrap style="color:#339900!important;">{!! $loop->first ? 'Награда:' : '&nbsp;' !!}</td>
                                                                        <td style="color:#339900!important;">{{ renderQuestReward($reward) }}</td>
                                                                    </tr>
                                                                @endforeach
                                                                </tbody>
                                                            </table>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="3" style="padding:8px; text-align:center; color:#888;">
                                                        Нет клановых квестов
                                                    </td>
                                                </tr>
                                            @endforelse
                                            </tbody>
                                        </table>

                                        @if($clanQuests instanceof \Illuminate\Pagination\LengthAwarePaginator && $clanQuests->hasPages())
                                            <div class="pagination-wrap">
                                                @if($clanQuests->onFirstPage())
                                                    <span class="disabled">&laquo;</span>
                                                @else
                                                    <a href="{{ $clanQuests->previousPageUrl() }}">&laquo;</a>
                                                @endif
                                                @foreach($clanQuests->getUrlRange(1, $clanQuests->lastPage()) as $page => $url)
                                                    @if($page == $clanQuests->currentPage())
                                                        <span class="current">{{ $page }}</span>
                                                    @else
                                                        <a href="{{ $url }}">{{ $page }}</a>
                                                    @endif
                                                @endforeach
                                                @if($clanQuests->hasMorePages())
                                                    <a href="{{ $clanQuests->nextPageUrl() }}">&raquo;</a>
                                                @else
                                                    <span class="disabled">&raquo;</span>
                                                @endif
                                            </div>
                                        @endif
                                    @else
                                    {{-- PERSONAL QUEST LIST --}}
                                    <table class="coll w100 p6h p2v brd2-all">
                                        <tbody>
                                        @forelse($quests as $quest)
                                            <tr class="bg_l" onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                <td class="brd2-top" width="1">
                                                    <img id="image_open_quest_{{ $quest->id }}" onclick="quest_folding.toggle({{ $quest->id }});"
                                                         src="{{ asset('img/icon/qst_minus.gif') }}" width="9" height="9" border="0">
                                                </td>
                                                <td class="brd2-top b" onclick="quest_folding.toggle({{ $quest->id }});">{{ $quest->quest->title }}</td>
                                                <td class="brd2-top" align="right">
                                                    @if($quest->status->isProgress())
                                                        <form id="cancel-quest-{{ $quest->id }}" method="POST" action="{{ route('quest.cancel', $quest->id) }}" style="display:none">@csrf</form>
                                                        <b class="butt2 pointer ">
                                                            <b>
                                                                <input value="Отказаться" type="button" onclick="return top.systemConfirm('Вы действительно хотите отказаться от квеста?','Действие',false,function(){document.getElementById('cancel-quest-{{ $quest->id }}').submit();});">
                                                            </b>
                                                        </b>
                                                    @elseif($quest->status->isCompleted())
                                                        @if($quest->reset_at && now()->lt($quest->reset_at))
                                                            <span style="color:#888;">Доступен через {{ $quest->reset_at->locale('ru')->diffForHumans(now(), true, false, 2) }}</span>
                                                        @else
                                                            <span style="color:#4a7a1e;">Выполнен</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr class="bg_l" id="quest_{{ $quest->id }}">
                                                <td></td>
                                                <td colspan="2">
                                                    <div class="ajustify b">{!! $quest->quest->description !!}</div>
                                                    <img src="images/d.gif" width="1" height="6"><br>
                                                    @php
                                                        $stages = $quest->quest->stages;
                                                        $hasStages = $stages->isNotEmpty();
                                                        $currentStageId = $quest->current_stage_id;
                                                        $isCompleted = $quest->status->isCompleted();
                                                    @endphp
                                                    @if($hasStages)
                                                        @foreach($stages as $stage)
                                                            @php
                                                                $isDone    = $isCompleted || ($currentStageId !== null && $stage->order < $quest->currentStage?->order);
                                                                $isCurrent = !$isCompleted && $stage->id === $currentStageId;
                                                            @endphp
                                                            @if(!$isDone && !$isCurrent) @continue @endif
                                                            <div style="margin-top:4px; {{ $isDone ? 'color:#999!important;' : 'color:#461C0B;' }} font-weight:bold;">
                                                                @if($isDone) ✓ @endif
                                                                Этап {{ $stage->order }}{{ $stage->title ? ': ' . $stage->title : '' }}
                                                            </div>
                                                            @if($stage->description && ($isCurrent || $isDone))
                                                                <div style="margin:1px 0 2px 10px; {{ $isDone ? 'color:#bbb;' : 'color:#555;' }} font-style:italic;">
                                                                    {!! $stage->description !!}
                                                                </div>
                                                            @endif
                                                            @php
                                                                $stageObjs = $quest->objectives->filter(fn($o) => $o->questObjective->stage_id === $stage->id);
                                                            @endphp
                                                            @if($stageObjs->isNotEmpty())
                                                                <div style="margin-left:10px;">
                                                                    @foreach($stageObjs as $obj)
                                                                        @php $objDone = $obj->questObjective->type === 'deliver' ? $obj->questObjective->required_amount : $obj->amount; @endphp
                                                                        <div class="b" style="{{ $isDone ? 'color:#999;' : 'color:#8B0000;' }}">
                                                                            <span>{{ $loop->first ? 'Цель:' : '&nbsp;' }}</span>
                                                                            {{ $obj->questObjective->description }}
                                                                            ({{ $objDone }}/{{ $obj->questObjective->required_amount }})
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        @php $stageObjectives = $quest->currentStageObjectives(); @endphp
                                                        @if($stageObjectives->isNotEmpty())
                                                            <div>
                                                                @foreach($stageObjectives as $obj)
                                                                    @php $objDone = $obj->questObjective->type === 'deliver' ? $obj->questObjective->required_amount : $obj->amount; @endphp
                                                                    <div class="redd b">
                                                                        <span>{{ $loop->first ? 'Текущая цель:' : '&nbsp;' }}</span>
                                                                        {{ $obj->questObjective->description }}
                                                                        ({{ $objDone }}/{{ $obj->questObjective->required_amount }})
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                    @endif
                                                    @if($quest->status->isCompleted() && $quest->quest->rewards->isNotEmpty())
                                                        <table class="coll" style="margin-top: 3px;">
                                                            <tbody>
                                                            @foreach($quest->quest->rewards as $reward)
                                                                <tr class="b" style="color: #339900 !important;">
                                                                    <td nowrap="" style="color: #339900 !important;">{!! $loop->first ? 'Награда:' : '&nbsp;' !!}</td>
                                                                    <td style="color: #339900 !important;">{{ renderQuestReward($reward) }}</td>
                                                                </tr>
                                                            @endforeach
                                                            </tbody>
                                                        </table>
                                                    @endif
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" style="padding: 8px; text-align: center; color: #888;">
                                                    Нет квестов в этой категории
                                                </td>
                                            </tr>
                                        @endforelse

                                        </tbody>
                                    </table>

                                    @if($quests instanceof \Illuminate\Pagination\LengthAwarePaginator && $quests->hasPages())
                                        <div class="pagination-wrap">
                                            @if($quests->onFirstPage())
                                                <span class="disabled">&laquo;</span>
                                            @else
                                                <a href="{{ $quests->previousPageUrl() }}">&laquo;</a>
                                            @endif

                                            @foreach($quests->getUrlRange(1, $quests->lastPage()) as $page => $url)
                                                @if($page == $quests->currentPage())
                                                    <span class="current">{{ $page }}</span>
                                                @else
                                                    <a href="{{ $url }}">{{ $page }}</a>
                                                @endif
                                            @endforeach

                                            @if($quests->hasMorePages())
                                                <a href="{{ $quests->nextPageUrl() }}">&raquo;</a>
                                            @else
                                                <span class="disabled">&raquo;</span>
                                            @endif
                                        </div>
                                    @endif
                                    @endif
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
        </td>
    </tr>
    </tbody>
</table>

<script type="text/javascript" src="{{ asset('js/cookie.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/folding.js') }}" ></script>

<script>
    @if($tab === 'clan')
    var clanQuestIds = [{{ $clanQuests instanceof \Illuminate\Pagination\AbstractPaginator ? $clanQuests->pluck('id')->implode(',') : '' }}];
    var quest_folding = new ListFolding({
        id_btn:             'image_open_quest_',
        id_expanded:        'quest_',
        cookie_save_state:  'clan_quests_',
        init_state:         'expanded',
        id_list:            clanQuestIds
    });
    @else
    var quest_folding = new ListFolding({
        id_btn:             'image_open_quest_',
        id_expanded:        'quest_',
        cookie_save_state:  'quests_',
        init_state:			'expanded',
        id_list:            [{{ $questIds }}]
    });
    @endif
    quest_folding.refresh();
</script>

</body>
</html>
