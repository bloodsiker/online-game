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
                <div class="bp-sec bp-sec-enemy">Противники ({{ $battle->detailsWithMonsters->count() }})</div>
                @foreach($battle->detailsWithMonsters as $details)
                    @if($details->status->isLife())
                        @php
                            $isTarget = $randomAttackedMonster->locationMonster->id === $details->locationMonster->id;
                            $hpPct    = $details->locationMonster->hp_max > 0
                                ? round(($details->locationMonster->hp_now / $details->locationMonster->hp_max) * 100)
                                : 0;
                            $hpClass  = $hpPct > 60 ? 'hp-high' : ($hpPct > 30 ? 'hp-mid' : 'hp-low');
                        @endphp
                        <div class="bp-unit{{ $isTarget ? ' bp-unit-target' : '' }}">
                            <div class="bp-unit-name">
                                @if($isTarget)<span class="bp-target-arrow">&#9658;</span>@endif
                                <a href="{{ route('info.monster', ['id' => $details->locationMonster->id]) }}" target="_blank" class="{{ $isTarget ? 'color-red' : '' }}">{{ $details->locationMonster->monster->name }}</a><span class="bp-unit-lvl">{{ $details->locationMonster->monster->lvl }}</span>
                            </div>
                            <div class="bp-hp-row">
                                <div class="bp-hp-bar"><div class="bp-hp-fill {{ $hpClass }}" style="width:{{ $hpPct }}%"></div></div>
                                <span class="bp-hp-text">{{ $details->locationMonster->hp_now }}/{{ $details->locationMonster->hp_max }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach

                {{-- Союзники --}}
                <div class="bp-sec bp-sec-ally">Союзники ({{ $battle->detailsWithUsers->count() }})</div>
                @foreach($battle->detailsWithUsers as $details)
                    @if($details->user)
                        @php
                            $allyHpPct = $details->user->player->hp_max > 0 ? round(($details->user->player->hp_now / $details->user->player->hp_max) * 100) : 0;
                            $allyMpPct = $details->user->player->mp_max > 0 ? round(($details->user->player->mp_now / $details->user->player->mp_max) * 100) : 0;
                        @endphp
                        <div class="bp-unit" @if($details->user->id === auth()->id()) id="bp-unit-me" @endif>
                            <div class="bp-unit-name">
                                <b><a href="{{ route('info.user', ['id' => $details->user->id]) }}" target="_blank">{{ $details->user->name }}</a></b><span class="bp-unit-lvl">[{{ $details->user->player->lvl }}]</span><span class="bp-unit-time">{{ $details->updated_at->format('H:i:s') }}</span>
                            </div>
                            <div class="bp-hp-row">
                                <div class="bp-hp-bar"><div class="bp-hp-fill hp-ally" style="width:{{ $allyHpPct }}%"></div></div>
                                <span class="bp-hp-text">{{ $details->user->player->hp_now }}/{{ $details->user->player->hp_max }}</span>
                            </div>
                            <div class="bp-hp-row">
                                <div class="bp-hp-bar"><div class="bp-mp-fill" style="width:{{ $allyMpPct }}%"></div></div>
                                <span class="bp-hp-text">{{ $details->user->player->mp_now }}/{{ $details->user->player->mp_max }}</span>
                            </div>
                        </div>
                    @endif
                @endforeach

                <div class="bp-footer">
                    <a href="/battle/?bid={{ $battle->id }}" target="_blank">История сражения &raquo;</a>
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