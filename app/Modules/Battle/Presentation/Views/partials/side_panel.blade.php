<table border="0" cellspacing="0" cellpadding="0" class="fight-window" width="100%">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="center">
            <table border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                    <td align="center" class="tbl-usi-hdr mbg">Участники боя</td>
                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding: 4px 6px 6px;">
            <div class="bp-wrap">

                {{-- Противники --}}
                @php
                    $activeMonsterId = $randomAttackedMonster?->locationMonster?->id;
                    $monsterDetails = $battle->detailsWithMonsters->sortByDesc(
                        fn ($details) => $details->locationMonster?->id === $activeMonsterId
                    );
                @endphp
                <div class="bp-sec bp-sec-enemy">Противники ({{ $monsterDetails->count() }})</div>
                @foreach($monsterDetails as $details)
                    @if($details->status->isLife() && $details->locationMonster)
                        @php
                            $isTarget = $activeMonsterId === $details->locationMonster->id;
                            $monster  = $details->locationMonster->monster;
                            $hpPct    = $details->locationMonster->hp_max > 0
                                ? round(($details->locationMonster->hp_now / $details->locationMonster->hp_max) * 100)
                                : 0;
                            $hpClass  = $hpPct > 60 ? 'hp-high' : ($hpPct > 30 ? 'hp-mid' : 'hp-low');
                        @endphp
                        <div class="bp-unit{{ $isTarget ? ' bp-unit-target' : '' }}">
                            <div class="bp-unit-name">
                                @if($isTarget)<span class="bp-target-arrow">&#9658;</span>@endif
                                <a href="{{ route('info.monster', ['id' => $details->locationMonster->id]) }}" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;" class="{{ $isTarget ? 'color-red' : '' }}">{{ $monster->name }}</a><span class="bp-unit-lvl">{{ $monster->lvl }}</span>
                            </div>
                            <div class="bp-hp-row">
                                <div class="bp-hp-bar"><div class="bp-hp-fill {{ $hpClass }}" style="width:{{ $hpPct }}%"></div></div>
                                <span class="bp-hp-text">{{ $details->locationMonster->hp_now }}/{{ $details->locationMonster->hp_max }}</span>
                            </div>
                            @if($isTarget && $monster->image)
                                <a href="{{ route('info.monster', ['id' => $details->locationMonster->id]) }}" class="bp-target-image" onclick="window.open(this.href,'','width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');return false;">
                                    <img src="{{ $monster->image }}" alt="{{ $monster->name }}">
                                </a>
                            @endif
                        </div>
                    @endif
                @endforeach

                {{-- Союзники --}}
                <div class="bp-sec bp-sec-ally">Союзники ({{ $battle->detailsWithUsers->count() }})</div>
                @foreach($battle->detailsWithUsers as $details)
                    @if($details->user)
                        @php
                            $isCurrentPlayer = $details->user->id === auth()->id();
                            $allyPlayer = $isCurrentPlayer ? $player : $details->user->player;
                            $allyHpMax = $isCurrentPlayer ? $playerDecorator->getHpMax() : $allyPlayer->hp_max;
                            $allyMpMax = $isCurrentPlayer ? $playerDecorator->getMpMax() : $allyPlayer->mp_max;
                            $allyHpPct = $allyHpMax > 0
                                ? min(100, max(0, round(($allyPlayer->hp_now / $allyHpMax) * 100)))
                                : 0;
                            $allyMpPct = $allyMpMax > 0
                                ? min(100, max(0, round(($allyPlayer->mp_now / $allyMpMax) * 100)))
                                : 0;
                        @endphp
                        <div class="bp-unit" @if($isCurrentPlayer) id="bp-unit-me" @endif>
                            <div class="bp-unit-name">
                                <b><a href="{{ route('info.user', ['id' => $details->user->id]) }}" target="_blank">{{ $details->user->name }}</a></b><span class="bp-unit-lvl">[{{ $allyPlayer->lvl }}]</span><span class="bp-unit-time">{{ $details->updated_at->format('H:i:s') }}</span>
                            </div>
                            <div class="bp-hp-row">
                                <div class="bp-hp-bar"><div class="bp-hp-fill hp-ally" style="width:{{ $allyHpPct }}%"></div></div>
                                <span class="bp-hp-text">{{ $allyPlayer->hp_now }}/{{ $allyHpMax }}</span>
                            </div>
                            <div class="bp-hp-row">
                                <div class="bp-hp-bar"><div class="bp-mp-fill" style="width:{{ $allyMpPct }}%"></div></div>
                                <span class="bp-hp-text">{{ $allyPlayer->mp_now }}/{{ $allyMpMax }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="bp-footer">
                    <a href="#" onclick="window.open('{{ route('fight.log', ['id' => $battle->id]) }}', '', 'width=730,height=550,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;">История сражения &raquo;</a>
                </div>

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
