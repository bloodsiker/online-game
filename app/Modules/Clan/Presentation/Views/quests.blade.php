<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Клановые квесты') }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        html { height: 100%; }
        /* font-size задаём через body (наследование), а НЕ через '*': универсальный
           селектор перебивал бы .tbl-shp-sml { font-size: 0 } из main.css у вложенных
           ячеек рамки — картинки лейбла получали строчный отступ под базовую линию,
           строка вырастала выше 22px, а угловые спрайты (no-repeat, 22px) не тянулись,
           из-за чего появлялся разрыв рамки. */
        body { height: 100%; margin: 0; color: #461c0b; font-family: Tahoma; font-size: 11px; }
        a, a:link, a:visited, a:active { text-decoration: none; color: #461C0B; }
        table.coll { border-collapse: collapse; border-spacing: 0; }
        .brd2-all { border: 1px solid #DB9F73; }
        .bg_l { background-image: url(/img/bg/info/bg_l.gif); }
        .w100 { width: 100%; }

        .section-title {
            display: block;
            box-sizing: border-box;
            width: 100%;
            margin: 0 0 5px;
            padding: 4px 8px;
            border: 1px solid #c49485;
            background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat;
            color: #955c4a;
            font-weight: bold;
            font-size: 11px;
            line-height: 14px;
            text-align: left;
        }

        .active-quest {
            margin-bottom: 7px;
            padding: 6px 8px;
            border: 1px solid #c49485;
            border-left: 3px solid #a36a35;
            background: #fffbd6 url(/img/bg/info/bg_l.gif) repeat;
        }
        .active-quest .quest-title {
            margin: -6px -8px 5px;
            padding: 4px 8px;
            border-bottom: 1px solid #db9f73;
            background: rgba(219, 159, 115, .18);
            font-weight: bold;
            font-size: 11px;
            color: #461c0b;
        }
        .active-quest .quest-acceptor {
            color: #955c4a;
            margin-bottom: 6px;
        }
        .progress-row { margin: 0 0 6px; }
        .progress-label { color: #461c0b; margin-bottom: 2px; }
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

        .quest-card {
            margin-bottom: 6px;
            padding: 6px 8px;
            border: 1px solid #DB9F73;
            background-color: #fff8ec;
        }
        .quest-card:hover {
            border-color: #c49485;
        }
        .quest-card .q-title {
            margin: -6px -8px 5px;
            padding: 4px 8px;
            border-bottom: 1px solid #DB9F73;
            background: rgba(219, 159, 115, .14);
            color: #461c0b;
            font-size: 11px;
            font-weight: bold;
        }
        .quest-card .q-desc,
        .quest-card .q-desc p { color: #631c0b; margin: 2px 0 5px; line-height: 1.35; }
        .quest-card .q-obj { color: #8b0000; margin-bottom: 2px; }
        .q-reward { color: #114d01; font-weight: bold; }
        .quest-card .q-cooldown { color: #666; font-style: italic; }
        .badge-clan,
        .badge-active,
        .badge-cooldown {
            display: inline-block;
            margin-right: 4px;
            padding: 0 4px 1px;
            border: 1px solid #c49485;
            background: #f5dfc7 url(/img/bg/info/bg_l.gif) repeat;
            color: #8b0000;
            font-size: 9px;
            font-weight: bold;
            line-height: 12px;
            vertical-align: middle;
        }
        .badge-active {
            margin-left: 4px;
            border-color: #8eaa78;
            color: #114d01;
        }
        .badge-cooldown {
            margin-left: 4px;
            border-color: #aaa08e;
            color: #666;
        }
        .separator { border: 0; height: 5px; margin: 0; }
        .empty-state {
            margin-bottom: 7px;
            padding: 7px 9px;
            border: 1px solid #DB9F73;
            background: #fff8ec url(/img/bg/info/bg_l.gif) repeat;
            color: #955c4a;
            font-style: italic;
        }

        table.history-tbl {
            border-top: 1px solid #DB9F73;
            border-left: 1px solid #DB9F73;
            border-collapse: separate;
            width: 100%;
        }
        .history-tbl td, .history-tbl th {
            border-right: 1px solid #DB9F73;
            border-bottom: 1px solid #DB9F73;
            height: 17px;
            padding: 3px 6px;
            color: #461c0b;
            vertical-align: middle;
        }
        .history-tbl th { color: #955c4a; white-space: nowrap; background: #f3d6b7 url(/img/bg/info/bg_l.gif) repeat; }
        .history-tbl tr.bg_l td { background-image: url(/img/bg/info/bg_l.gif); }
    </style>
</head>
<body class="regblk">

@include('clan::partials.tabs', ['activeTab' => 'clan.quests'])

<table class="coll" width="100%" height="100%" border="0" style="margin-top:20px;">
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
                            <td align="center" class="tbl-usi_label-center">{{ __('Клановые квесты') }} — {{ $clan->name }}</td>
                            <td width="27"><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22"></td>
                        </tr>
                        </tbody></table>
                    </td>
                    <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
                </tr>
                <tr>
                    <td class="tbl-shp-sides ls">&nbsp;</td>
                    <td class="tbl-usi_bg" valign="top" style="padding: 6px 4px;">

                        {{-- ===== ACTIVE QUESTS ===== --}}
                        <span class="section-title">{{ __('Активные квесты') }}</span>

                        @forelse($activeQuests as $activeQuest)
                            <div class="active-quest">
                                <div class="quest-title">
                                    {{ $activeQuest->quest->title }}
                                </div>
                                <div class="quest-acceptor">
                                    {{ __('Выполняет:') }} <b>{{ $activeQuest->user->name }}</b>
                                    <span style="color:#aaa;">— {{ __('принят') }} {{ $activeQuest->created_at->locale(app()->getLocale())->diffForHumans() }}</span>
                                </div>

                                @php $stageObjectives = $activeQuest->currentStageObjectives(); @endphp

                                @if($stageObjectives->isNotEmpty())
                                    <div style="margin-bottom:4px; color:#777; font-style:italic;">{{ __('Прогресс по целям:') }}</div>
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
                                            <div class="progress-bar">
                                                <div class="progress-bar__bg"></div>
                                                <div class="progress-bar__red" style="width: {{ 100 - $pct }}%;"></div>
                                                <div class="progress-bar__cover"></div>
                                                <div class="progress-bar__left"></div>
                                                <div class="progress-bar__right"></div>
                                                <div class="progress-bar__marker" style="right: {{ 100 - $pct }}%;"></div>
                                                <div class="progress-bar__txt">{{ $done }} / {{ $need }} ({{ $pct }}%)</div>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif

                                @if($activeQuest->quest->rewards->isNotEmpty())
                                    <div style="margin-top:6px; color:#777;">
                                        {{ __('Награда:') }}
                                        @foreach($activeQuest->quest->rewards as $reward)
                                            <span class="q-reward">
                                                @php
                                                    echo match($reward->type) {
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::EXP         => '+' . $reward->amount . ' exp',
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::MONEY        => '+' . $reward->amount . ' ' . __('монет'),
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::CLAN_POINTS  => '+' . $reward->amount . ' ' . __('кл. очков'),
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::ITEM         => ($reward->amount > 1 ? $reward->amount . 'x ' : '') . ($reward->itemInfo?->name ?? __('предмет')),
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
                                                <b><input value="{{ __('Отменить квест') }}" type="button" onclick="return top.systemConfirm({!! \Illuminate\Support\Js::from(__('Вы действительно хотите отменить клановый квест?')) !!},{!! \Illuminate\Support\Js::from(__('Действие')) !!},false,function(){document.getElementById('clan-cancel-{{ $activeQuest->id }}').submit();})"></b>
                                            </b>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @empty
                            <div class="empty-state">
                            {{ __('Нет активных клановых квестов. Любой член клана может взять квест у NPC.') }}
                            </div>
                        @endforelse

                        <hr class="separator">

                        {{-- ===== AVAILABLE QUESTS ===== --}}
                        <span class="section-title">{{ __('Доступные квесты') }}</span>

                        @php
                            // Find last completed record per quest for cooldown display
                            use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
                            $lastCompleted = QuestClanProgress::where('clan_id', $clan->id)
                                ->where('status', \App\Modules\Quest\Domain\Enums\QuestPlayerStatus::COMPLETED)
                                ->get()
                                ->keyBy('quest_id');
                        @endphp

                        @forelse($availableQuests as $quest)
                            @php
                                $isCurrentlyActive = $activeQuests->contains('quest_id', $quest->id);
                                $lastRecord        = $lastCompleted->get($quest->id);
                                $onCooldown        = $lastRecord && $lastRecord->reset_at && now()->lt($lastRecord->reset_at);
                                $cooldownStr       = $onCooldown
                                    ? $lastRecord->reset_at->locale(app()->getLocale())->diffForHumans(now(), true, false, 2)
                                    : null;
                                $resetLabel = match(true) {
                                    $quest->reset_period === 86400   => __('раз в сутки'),
                                    $quest->reset_period === 172800  => __('раз в 2 дня'),
                                    $quest->reset_period === 259200  => __('раз в 3 дня'),
                                    $quest->reset_period === 604800  => __('раз в неделю'),
                                    default                          => __('повторяемый'),
                                };
                            @endphp
                            <div class="quest-card">
                                <div class="q-title">
                                    <span class="badge-clan">{{ __('КЛАН') }}</span>
                                    {{ $quest->title }}
                                    @if($isCurrentlyActive)
                                        <span class="badge-active">{{ __('в процессе') }}</span>
                                    @elseif($onCooldown)
                                        <span class="badge-cooldown">{{ __('доступен через') }} {{ $cooldownStr }}</span>
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
                                        <span style="color:#777;">{{ __('Награда:') }} </span>
                                        @foreach($quest->rewards as $reward)
                                            <span class="q-reward">
                                                @php
                                                    echo match($reward->type) {
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::EXP         => '+' . $reward->amount . ' опыта',
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::MONEY        => '+' . $reward->amount . ' ' . __('монет'),
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::CLAN_POINTS  => '+' . $reward->amount . ' ' . __('клановых очков'),
                                                        \App\Modules\Quest\Domain\Enums\QuestRewardType::ITEM         => ($reward->amount > 1 ? $reward->amount . 'x ' : '') . ($reward->itemInfo?->name ?? __('предмет')),
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
                            <div class="empty-state">{{ __('Нет доступных клановых квестов.') }}</div>
                        @endforelse

                        <hr class="separator">

                        {{-- ===== HISTORY ===== --}}
                        <span class="section-title">{{ __('История выполнения') }}</span>

                        @if($history->isNotEmpty())
                            <table class="history-tbl">
                                <thead>
                                <tr>
                                    <th>{{ __('Квест') }}</th>
                                    <th>{{ __('Выполнил') }}</th>
                                    <th>{{ __('Завершён') }}</th>
                                    <th>{{ __('Следующий запуск') }}</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($history as $record)
                                    <tr>
                                        <td>{{ $record->quest->title }}</td>
                                        <td>{{ $record->user->name }}</td>
                                        <td nowrap>{{ $record->completed_at?->format('d.m.Y H:i') }}</td>
                                        <td nowrap>
                                            @if($record->reset_at)
                                                @if(now()->lt($record->reset_at))
                                                    <span style="color:#c8990a;">{{ __('через') }} {{ $record->reset_at->locale(app()->getLocale())->diffForHumans(now(), true, false, 2) }}</span>
                                                @else
                                                    <span style="color:#2a7a2a;">{{ __('доступен') }}</span>
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
                            <div class="empty-state">{{ __('История пуста.') }}</div>
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
