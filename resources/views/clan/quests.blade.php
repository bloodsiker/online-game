<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Клановые квесты</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        * { font-family: Tahoma; font-size: 12px; }
        html { height: 100%; }
        body { height: 100%; margin: 0; color: #000; }
        a, a:link, a:visited, a:active { text-decoration: none; color: #461C0B; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .w100 { width: 100%; }

        .section-title {
            background-image: url(/img/bg/info/tbl-usi_label-center.gif);
            background-repeat: repeat-x;
            color: #FCF5B7;
            font-weight: bold;
            font-size: 11px;
            padding: 3px 10px 5px;
            margin-bottom: 6px;
            display: block;
        }

        /* Active quest card */
        .active-quest {
            border: 1px solid #c8990a;
            background: #fffbe8;
            border-radius: 3px;
            padding: 8px 10px;
            margin-bottom: 10px;
        }
        .active-quest .quest-title {
            font-size: 12px;
            font-weight: bold;
            color: #461c0b;
            margin-bottom: 4px;
        }
        .active-quest .quest-acceptor {
            color: #7a5a00;
            margin-bottom: 6px;
        }
        .progress-row { margin-bottom: 5px; }
        .progress-label { color: #333; margin-bottom: 2px; }
        .progress-bar-wrap {
            background: #e0c090;
            border: 1px solid #b08040;
            height: 10px;
            border-radius: 3px;
            overflow: hidden;
        }
        .progress-bar-fill {
            background: linear-gradient(to bottom, #e8b030, #c8900a);
            height: 100%;
            min-width: 2px;
        }
        .progress-nums { color: #666; text-align: right; margin-top: 1px; }

        /* Available quest cards */
        .quest-card {
            border: 1px solid #DB9F73;
            border-radius: 3px;
            background-image: url(/img/bg/info/bg_l.gif);
            padding: 6px 8px;
            margin-bottom: 5px;
        }
        .quest-card .q-title { font-weight: bold; color: #461c0b; font-size: 12px; }
        .quest-card .q-desc  { color: #555; margin: 2px 0 4px; line-height: 1.4; }
        .quest-card .q-obj   { color: #8B0000; margin-bottom: 1px; }
        .quest-card .q-reward { color: #2a7a2a; font-weight: bold; }
        .quest-card .q-cooldown { color: #666; font-style: italic; }
        .badge-clan {
            display: inline-block;
            background: #c8990a;
            color: #fff;
            font-size: 9px;
            font-weight: bold;
            padding: 1px 4px;
            border-radius: 2px;
            margin-right: 4px;
            vertical-align: middle;
        }
        .badge-active {
            display: inline-block;
            background: #2a7a2a;
            color: #fff;
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 4px;
            vertical-align: middle;
        }
        .badge-cooldown {
            display: inline-block;
            background: #888;
            color: #fff;
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 2px;
            margin-left: 4px;
            vertical-align: middle;
        }
        .separator { border: none; border-top: 1px solid #DB9F73; margin: 8px 0; }

        /* History table */
        table.history-tbl {
            border-top: 1px solid #DB9F73;
            border-left: 1px solid #DB9F73;
            border-collapse: separate;
            width: 100%;
        }
        .history-tbl td, .history-tbl th {
            border-right: 1px solid #DB9F73;
            border-bottom: 1px solid #DB9F73;
            padding: 3px 6px;
            color: #461c0b;
        }
        .history-tbl th { color: #955c4a; white-space: nowrap; background-image: url(/img/bg/info/bg_l.gif); }
        .history-tbl tr.bg_l td { background-image: url(/img/bg/info/bg_l.gif); }

        .cancel-btn {
            cursor: pointer;
            color: #8B0000;
            border: 1px solid #c49485;
            background-image: url(/img/bg/info/bg_l.gif);
            padding: 2px 6px;
            border-radius: 2px;
            font-size: 10px;
        }
        .cancel-btn:hover { background-color: #f5ddd0; }
    </style>
</head>
<body class="regblk">

@include('clan.partials.tabs', ['activeTab' => 'clan.quests'])

<table class="coll" width="100%" height="100%" border="0">
    <tbody>
    <tr>
        <td valign="top" width="100%">
            <table width="100%" border="0" cellspacing="0" cellpadding="0" height="100%">
                <tbody>
                <tr height="22">
                    <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
                    <td class="tbl-shp-sml tt" valign="top" align="center">
                        <table border="0" cellspacing="0" cellpadding="0"><tbody>
                        <tr height="22">
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22"></td>
                            <td align="center" class="tbl-usi_label-center">Клановые квесты — {{ $clan->name }}</td>
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                        </tr>
                        </tbody></table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 8px 10px;">

                        {{-- ===== ACTIVE QUESTS ===== --}}
                        <span class="section-title">Активные квесты</span>

                        @forelse($activeQuests as $activeQuest)
                            <div class="active-quest">
                                <div class="quest-title">
                                    {{ $activeQuest->quest->title }}
                                </div>
                                <div class="quest-acceptor">
                                    Выполняет: <b>{{ $activeQuest->user->name }}</b>
                                    <span style="color:#aaa;">— принят {{ $activeQuest->created_at->locale('ru')->diffForHumans() }}</span>
                                </div>

                                @php $stageObjectives = $activeQuest->currentStageObjectives(); @endphp

                                @if($stageObjectives->isNotEmpty())
                                    <div style="margin-bottom:4px; color:#777; font-style:italic;">Прогресс по целям:</div>
                                    @foreach($stageObjectives as $obj)
                                        @php
                                            $done = $obj->questObjective->type === 'deliver'
                                                ? $obj->questObjective->required_amount
                                                : $obj->amount;
                                            $need = $obj->questObjective->required_amount;
                                            $pct  = $need > 0 ? min(100, round($done / $need * 100)) : 0;
                                        @endphp
                                        <div class="progress-row">
                                            <div class="progress-label">
                                                @if($done >= $need)
                                                    <span style="color:#2a7a2a;">✓</span>
                                                @else
                                                    <span style="color:#8B0000;">•</span>
                                                @endif
                                                {{ $obj->questObjective->description }}
                                            </div>
                                            <div class="progress-bar-wrap">
                                                <div class="progress-bar-fill" style="width:{{ $pct }}%;"></div>
                                            </div>
                                            <div class="progress-nums">{{ $done }} / {{ $need }} ({{ $pct }}%)</div>
                                        </div>
                                    @endforeach
                                @endif

                                @if($activeQuest->quest->rewards->isNotEmpty())
                                    <div style="margin-top:6px; color:#777;">
                                        Награда:
                                        @foreach($activeQuest->quest->rewards as $reward)
                                            <span class="q-reward">
                                                @php
                                                    echo match($reward->type) {
                                                        \App\Enums\QuestRewardType::EXP         => '+' . $reward->amount . ' exp',
                                                        \App\Enums\QuestRewardType::MONEY        => '+' . $reward->amount . ' монет',
                                                        \App\Enums\QuestRewardType::CLAN_POINTS  => '+' . $reward->amount . ' кл. очков',
                                                        \App\Enums\QuestRewardType::ITEM         => ($reward->amount > 1 ? $reward->amount . 'x ' : '') . ($reward->itemInfo?->name ?? 'предмет'),
                                                        default => '',
                                                    };
                                                @endphp
                                            </span>
                                            @if(!$loop->last) &nbsp;|&nbsp; @endif
                                        @endforeach
                                    </div>
                                @endif

                                @if($isLeader)
                                    <div style="margin-top:8px;">
                                        <form id="clan-cancel-{{ $activeQuest->id }}" method="POST" action="{{ route('quest.clan.cancel', $activeQuest->id) }}" style="display:inline;">
                                            @csrf
                                            <b class="butt2 pointer">
                                                <b><input value="Отменить квест" type="button" onclick="return top.systemConfirm('Вы действительно хотите отменить клановый квест?','Действие',false,function(){document.getElementById('clan-cancel-{{ $activeQuest->id }}').submit();})"></b>
                                            </b>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="brd2-all" style="padding:8px 10px; color:#888; font-style:italic; margin-bottom:10px;">
                                Нет активных клановых квестов. Любой член клана может взять квест у NPC.
                            </div>
                        @endforelse

                        <hr class="separator">

                        {{-- ===== AVAILABLE QUESTS ===== --}}
                        <span class="section-title">Доступные квесты</span>

                        @php
                            // Find last completed record per quest for cooldown display
                            use App\Models\Quest\QuestClanProgress;
                            $lastCompleted = QuestClanProgress::where('clan_id', $clan->id)
                                ->where('status', \App\Enums\QuestPlayerStatus::COMPLETED)
                                ->get()
                                ->keyBy('quest_id');
                        @endphp

                        @forelse($availableQuests as $quest)
                            @php
                                $isCurrentlyActive = $activeQuests->contains('quest_id', $quest->id);
                                $lastRecord        = $lastCompleted->get($quest->id);
                                $onCooldown        = $lastRecord && $lastRecord->reset_at && now()->lt($lastRecord->reset_at);
                                $cooldownStr       = $onCooldown
                                    ? $lastRecord->reset_at->locale('ru')->diffForHumans(now(), true, false, 2)
                                    : null;
                                $resetLabel = match(true) {
                                    $quest->reset_period === 86400   => 'раз в сутки',
                                    $quest->reset_period === 172800  => 'раз в 2 дня',
                                    $quest->reset_period === 259200  => 'раз в 3 дня',
                                    $quest->reset_period === 604800  => 'раз в неделю',
                                    default                          => 'повторяемый',
                                };
                            @endphp
                            <div class="quest-card">
                                <div class="q-title">
                                    <span class="badge-clan">КЛАН</span>
                                    {{ $quest->title }}
                                    @if($isCurrentlyActive)
                                        <span class="badge-active">в процессе</span>
                                    @elseif($onCooldown)
                                        <span class="badge-cooldown">доступен через {{ $cooldownStr }}</span>
                                    @endif
                                    <span style="color:#aaa; font-weight:normal; font-size:10px; margin-left:6px;">{{ $resetLabel }}</span>
                                </div>

                                <div class="q-desc">{!! $quest->description !!}</div>

                                {{-- Objectives --}}
                                @foreach($quest->objectives as $obj)
                                    <div class="q-obj">
                                        • {{ $obj->description }}
                                    </div>
                                @endforeach

                                {{-- Rewards --}}
                                @if($quest->rewards->isNotEmpty())
                                    <div style="margin-top:4px;">
                                        <span style="color:#777;">Награда: </span>
                                        @foreach($quest->rewards as $reward)
                                            <span class="q-reward">
                                                @php
                                                    echo match($reward->type) {
                                                        \App\Enums\QuestRewardType::EXP         => '+' . $reward->amount . ' опыта',
                                                        \App\Enums\QuestRewardType::MONEY        => '+' . $reward->amount . ' монет',
                                                        \App\Enums\QuestRewardType::CLAN_POINTS  => '+' . $reward->amount . ' клановых очков',
                                                        \App\Enums\QuestRewardType::ITEM         => ($reward->amount > 1 ? $reward->amount . 'x ' : '') . ($reward->itemInfo?->name ?? 'предмет'),
                                                        default => '',
                                                    };
                                                @endphp
                                            </span>
                                            @if(!$loop->last) &nbsp;|&nbsp; @endif
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div style="color:#888; font-style:italic;">Нет доступных клановых квестов.</div>
                        @endforelse

                        <hr class="separator">

                        {{-- ===== HISTORY ===== --}}
                        <span class="section-title">История выполнения</span>

                        @if($history->isNotEmpty())
                            <table class="history-tbl">
                                <thead>
                                <tr>
                                    <th>Квест</th>
                                    <th>Выполнил</th>
                                    <th>Завершён</th>
                                    <th>Следующий запуск</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($history as $record)
                                    <tr class="bg_l">
                                        <td>{{ $record->quest->title }}</td>
                                        <td>{{ $record->user->name }}</td>
                                        <td nowrap>{{ $record->completed_at?->format('d.m.Y H:i') }}</td>
                                        <td nowrap>
                                            @if($record->reset_at)
                                                @if(now()->lt($record->reset_at))
                                                    <span style="color:#c8990a;">через {{ $record->reset_at->locale('ru')->diffForHumans(now(), true, false, 2) }}</span>
                                                @else
                                                    <span style="color:#2a7a2a;">доступен</span>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @else
                            <div style="color:#888; font-style:italic;">История пуста.</div>
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

<script>
    @if(session()->has('message'))
        window.parent.showErrorIframe('{{ session('message') }}');
    @endif
</script>

</body>
</html>
