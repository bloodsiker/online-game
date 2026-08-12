<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $npc->name }}</title>
    <link rel="stylesheet" type="text/css" href="{{ asset('css/main.css') }}">
    <style>
        body {
            height: 100%;
            margin: 0;
            color: #000;
            font-family: Tahoma;
            font-size: 12px;
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
        .brd2-bt {
            border-bottom: 1px solid #DB9F73;
        }
        .brd2-top {
            border-top: 1px solid #DB9F73;
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

    </style>
</head>
<body>

<table border="0" cellspacing="0" cellpadding="0" width="100%" style="position: relative; top: 0px;">
    <tbody>
    @php
        $group = 'main';
        $btnLeft1 = 'img/bg/btn/btn-left1.gif';
        $btnCenter1 = 'img/bg/btn/btn-cent1.gif';
        $btnRight1 = 'img/bg/btn/btn-right1.gif';

        $btnLeft2 = 'img/bg/btn/btn-left2.gif';
        $btnCenter2 = 'img/bg/btn/btn-cent2.gif';
        $btnRight2 = 'img/bg/btn/btn-right2.gif';
    @endphp
    <tr height="21">
        <td width="19"><img id="left_1" src="{{ asset($btnLeft2) }}" width="19" height="21"><br></td>
        <td width="60" id="tab_1" align="center" style="background: url({{ asset($btnCenter2) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_1" href="" title="Взятые" class="btn_2">Квесты</a>
        </td>
        <td width="19"><img id="right_1" src="{{ asset($btnRight2) }}" width="19" height="21"><br></td>

        <td></td>

        <td width="19"><img id="left_4" src="{{ asset($btnLeft1) }}" width="19" height="21"><br></td>
        <td width="140" id="tab_4" align="center" style="background: url({{ asset($btnCenter1) }}) center top repeat-x; padding: 0px 2px 6px;">
            <a id="center_4" href="{{ route('location') }}" title="Вернуться" class="btn_1">Вернуться в локацию</a></td>
        <td width="19"><img id="right_4" src="{{ asset($btnRight1) }}" width="19" height="21"><br></td>
    </tr>
    </tbody>
</table>

<table cellspacing="0" cellpadding="5" width="100%" height="100%">
    <tbody>
    <tr>
        <td height="10"></td>
    </tr>
    <tr>
        <td>
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
                                            <td align="center" class="tbl-usi_label-center">Квесты</td>
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
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                                    <table class="coll w100 p6h p2v brd2-all">
                                        <tbody>
                                        @if($npc->structures->count())
                                            @foreach($npc->structures as $structure)
                                                @if($structure->isShop())
                                                    <tr class="bg_l" title=""
                                                        onclick="location.href='{{ route('shop', ['id' => $structure->id]) }}'" onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                        <td class="brd2-top brd2-bt" width="1%">
                                                            <img src="{{ asset('img/icon/qst_store.gif') }}" alt="icon" width="46" height="28">
                                                        </td>
                                                        <td class="brd2-top brd2-bt">{{ $structure->name }}</td>
                                                        <td class="brd2-top brd2-bt" align="right">
                                                            <b class="butt2 pointer">
                                                                <b>
                                                                    <input value="Далее" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('shop', ['id' => $structure->id]) }}';" style="width:60px">
                                                                </b>
                                                            </b>
                                                        </td>
                                                    </tr>
                                                @endif

                                                @if($structure->isExchange())
                                                    <tr class="bg_l" title=""
                                                        onclick="location.href='{{ route('exchange', ['id' => $structure->id]) }}'" onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                        <td class="brd2-top brd2-bt" width="1%">
                                                            <img src="{{ asset('img/icon/qst_store.gif') }}" width="46" height="28">
                                                        </td>
                                                        <td class="brd2-top brd2-bt">{{ $structure->name }}</td>
                                                        <td class="brd2-top brd2-bt" align="right">
                                                            <b class="butt2 pointer">
                                                                <b>
                                                                    <input value="Далее" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('exchange', ['id' => $structure->id]) }}';" style="width:60px">
                                                                </b>
                                                            </b>
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        @endif

                                        @if($dialogueStartNode)
                                            <tr class="bg_l"
                                                onclick="location.href='{{ route('npc.dialogue', ['id' => $npc->id]) }}'"
                                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                <td class="brd2-top brd2-bt" width="1%">
                                                    <img src="{{ asset('img/icon/qst_dlg_m.gif') }}" width="46" height="28">
                                                </td>
                                                <td class="brd2-top brd2-bt">{{ $dialogueStartNode->title }}</td>
                                                <td class="brd2-top brd2-bt" align="right">
                                                    <b class="butt2 pointer"><b>
                                                        <input value="Поговорить" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('npc.dialogue', ['id' => $npc->id]) }}';" style="width:90px">
                                                    </b></b>
                                                </td>
                                            </tr>
                                        @endif

                                        @if($isClanRegistrar ?? false)
                                            <tr class="bg_l"
                                                onclick="location.href='{{ route('clan') }}'"
                                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                <td class="brd2-top brd2-bt" width="1%">
                                                    <img src="{{ asset('img/icon/qst_dlg_m.gif') }}" width="46" height="28">
                                                </td>
                                                <td class="brd2-top brd2-bt">Регистрация клана</td>
                                                <td class="brd2-top brd2-bt" align="right">
                                                    <b class="butt2 pointer"><b>
                                                        <input value="Говорить" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('clan') }}';" style="width:90px">
                                                    </b></b>
                                                </td>
                                            </tr>
                                        @endif

                                        @foreach($reputationShops ?? [] as $reputationShop)
                                            <tr class="bg_l"
                                                onclick="location.href='{{ route('reputation.shop', ['id' => $reputationShop->id]) }}'"
                                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                <td class="brd2-top brd2-bt" width="1%">
                                                    <img src="{{ asset('img/icon/qst_store.gif') }}" width="46" height="28">
                                                </td>
                                                <td class="brd2-top brd2-bt">Магазин репутации «{{ $reputationShop->name }}»</td>
                                                <td class="brd2-top brd2-bt" align="right">
                                                    <b class="butt2 pointer"><b>
                                                        <input value="Далее" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('reputation.shop', ['id' => $reputationShop->id]) }}';" style="width:60px">
                                                    </b></b>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @foreach($quests as $quest)
                                            <tr class="bg_l"
                                                onclick="location.href='{{ route('quest', ['id' => $quest->id, 'npc' => $npc->id]) }}'"
                                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'">
                                                <td class="brd2-top brd2-bt" width="1%">
                                                    <img src="{{ asset($quest->type->isMain() ? 'img/icon/qst_main_start.gif' : 'img/icon/qst_start.gif') }}" width="46" height="28">
                                                </td>
                                                <td class="brd2-top brd2-bt">@if($quest->isClan())[Клан] @endif @if($quest->is_feat ?? false)<b style="color:#7a4e00;">⚔ Подвиг:</b> @endif{{ $quest->title }}</td>
                                                <td class="brd2-top brd2-bt" align="right" onclick="event.stopPropagation()">
                                                    <b class="butt2 pointer"><b>
                                                        <input value="Взять" type="button" onclick="if(document._submit)return false;document._submit=true;location.href='{{ route('quest', ['id' => $quest->id, 'npc' => $npc->id]) }}';" style="width:60px">
                                                    </b></b>
                                                    @if($quest->type->isReputation() && isset($quest->reputation_id))
                                                        <form method="POST" action="{{ route('reputation.decline', $quest->reputation_id) }}" style="display:inline">
                                                            @csrf
                                                            <input type="hidden" name="npc" value="{{ $npc->id }}">
                                                            <b class="butt2 pointer"><b>
                                                                <input value="Отказ" type="submit" style="width:60px">
                                                            </b></b>
                                                        </form>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                        @foreach($questsOnCooldown as $cooldown)
                                            <tr class="bg_l">
                                                <td class="brd2-top brd2-bt" width="1%">
                                                    <img src="{{ asset('img/icon/qst_start.gif') }}" width="46" height="28" style="opacity:0.45;">
                                                </td>
                                                <td class="brd2-top brd2-bt" style="color:#888;">
                                                    {{ $cooldown->label ?? ($cooldown->quest->isClan() ? '[Клан] ' : '') . $cooldown->quest->title }}
                                                    <br><small style="color:#999;">Повторный проход через: {{ $cooldown->diff }}</small>
                                                </td>
                                                <td class="brd2-top brd2-bt" align="right">
                                                    <span style="color:#aaa; font-size:10px;">Недоступен</span>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @foreach($questsInProgress as $questInProgress)
                                            <tr class="bg_l"
                                                onclick="location.href='{{ route('quest', ['id' => $questInProgress->id, 'npc' => $npc->id]) }}'"
                                                onmouseover="this.className='bg_l2'" onmouseout="this.className='bg_l'" style="cursor:pointer;">
                                                <td class="brd2-top brd2-bt" width="1%">
                                                    <img src="{{ asset($questInProgress->canComplete ? 'img/icon/qst_start_m.gif' : 'img/icon/qst_start_ro.gif') }}" width="46" height="28">
                                                </td>
                                                <td class="brd2-top brd2-bt">
                                                    @if($questInProgress->isClan())[Клан] @endif{{ $questInProgress->title }}
                                                    @php
                                                        $stage      = $questInProgress->currentStage;
                                                        $objectives = $stage
                                                            ? $questInProgress->objectives->filter(fn($o) => $o->stage_id === $stage->id)
                                                            : $questInProgress->objectives;
                                                    @endphp
                                                    @if($stage)
                                                        <br><small style="color:#461C0B; font-weight:bold;">Этап {{ $stage->order }}{{ $stage->title ? ': ' . $stage->title : '' }}</small>
                                                    @endif
                                                    @foreach($objectives as $obj)
                                                        @php
                                                            $po   = $questInProgress->questPlayer?->objectives->firstWhere('quest_objective_id', $obj->id);
                                                            $done = $obj->type === 'deliver' ? $obj->required_amount : ($po?->amount ?? 0);
                                                        @endphp
                                                        <br><small style="color:#555;">{{ $obj->description }} — {{ $done }}/{{ $obj->required_amount }}</small>
                                                    @endforeach
                                                </td>
                                                <td class="brd2-top brd2-bt" align="right">
                                                    @if($questInProgress->canComplete)
                                                        <b class="butt2 pointer"><b>
                                                            <input value="Сдать" type="button"
                                                                   onclick="event.stopPropagation();location.href='{{ route('quest.complete', ['id' => $questInProgress->id, 'npc' => $npc->id]) }}';"
                                                                   style="width:60px">
                                                        </b></b>
                                                    @else
                                                        <span style="color:#888; font-size:10px;">В процессе</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach

                                        @php
                                            $hasAnything = $npc->structures->count()
                                                || $dialogueStartNode
                                                || ($isClanRegistrar ?? false)
                                                || collect($reputationShops ?? [])->count()
                                                || $quests->count()
                                                || $questsOnCooldown->count()
                                                || $questsInProgress->count();
                                        @endphp
                                        @unless($hasAnything)
                                            <tr class="bg_l">
                                                <td class="brd2-top brd2-bt" colspan="3" align="center" style="padding: 10px; color:#7a1c00; font-weight: bold;">
                                                    Квестов нет
                                                </td>
                                            </tr>
                                        @endunless
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

                    <td width="16"><img src="{{ asset('img/icon/d.gif') }}" width="16" height="1"></td>

                    <td valign="top" width="202" height="100%">
                        <table width="240" border="0" cellspacing="0" cellpadding="0">
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
                                            <td align="center" class="tbl-usi_label-center">{{ $npc->name }}</td>
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
                                <td class="tbl-usi_bg" valign="top" style="padding: 4px 0 4px 0">
                                    <img src="{{ $npc->image }}" alt="{{ $npc->name }}" width="190"
                                         height="171"><br>
                                    <div class="p2v">
                                        {!! $npc->description !!}
                                    </div>
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

<script>
    @if($messageType === 'success' && $message)
        try {
            let experience = parseFloat('{{ $player->getPercentExp() }}');
            let lvl = parseInt('{{ $player->lvl }}');
            let hp = { current: parseInt('{{ $player->hp_now }}'), max: parseInt('{{ $player->hp_max }}') };
            let mp = { current: parseInt('{{ $player->mp_now }}'), max: parseInt('{{ $player->mp_max }}') };
            let money = parseInt('{{ $player->user->money }}');
            let diamond = parseInt('{{ $player->user->diamond }}');
            parent.sendToFrame('character-frame', { hp, mp, experience, lvl, money, diamond });
        } catch (e) {}
    @endif
</script>

</body>
</html>
