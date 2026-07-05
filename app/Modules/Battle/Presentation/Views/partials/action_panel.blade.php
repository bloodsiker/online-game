<table border="0" cellspacing="0" cellpadding="0" class="fight-window">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td>
        <td class="tbl-shp-sml tt" valign="top" align="center">
            <table border="0" cellspacing="0" cellpadding="0">
                <tbody>
                <tr height="22">
                    <td width="27" class="tbl-usi-hdr lc"><b></b></td>
                    <td align="center" class="tbl-usi-hdr mbg">Ваш ход</td>
                    <td width="27" class="tbl-usi-hdr rc"><b></b></td>
                </tr>
                </tbody>
            </table>
        </td>
        <td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td>
    </tr>
    <tr>
        <td class="tbl-shp-sides ls">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top">
            <div class="fight-body">

                <div class="fight-act">
                    <b class="butt1 pointer"><b><button type="button" class="butt1" onclick="actionAttack('{{ $battle->id }}', '{{ $randomAttackedMonster->locationMonster->id }}', 0); return false;">&#9876; Атаковать оружием</button></b></b>
                </div>

                @if($player->hasEquippedMagicSkill())
                    <div class="fight-spell-label">Сотворить заклинание:</div>
                    @foreach($player->activeMagicSkills as $magicSkill)
                        <div class="fight-act">
                            <b class="butt1 pointer"><b><button type="button" class="butt1" id="s{{ $loop->index }}" onclick="actionAttack('{{ $battle->id }}', '{{ $randomAttackedMonster->locationMonster->id }}', '{{ $magicSkill->id }}'); return false;">{{ $magicSkill->name }} <span class="fight-mana">{{ $magicSkill->mana_cost }}</span></button></b></b>
                        </div>
                    @endforeach
                @endif

                <div class="fight-act fight-act-flee">
                    <b class="butt1 pointer"><b><button type="button" class="butt1" onclick="location.href='{{ route('fight.run-away', ['id' => $battle->id]) }}'">&larr; Убежать</button></b></b>
                </div>

                @php
                    $hpPct = $playerDecorator->getHpMax() > 0 ? round(($player->hp_now / $playerDecorator->getHpMax()) * 100) : 0;
                    $mpPct = $playerDecorator->getMpMax() > 0 ? round(($player->mp_now / $playerDecorator->getMpMax()) * 100) : 0;
                @endphp
                <div class="act-stats">
                    <div class="act-stat-row">
                        <span class="act-stat-label">Здоровье:</span>
                        <div class="act-stat-bar"><div class="act-stat-hp" style="width:{{ $hpPct }}%"></div></div>
                        <span class="act-stat-val">{{ $player->hp_now }}/{{ $playerDecorator->getHpMax() }}</span>
                    </div>
                    <div class="act-stat-row">
                        <span class="act-stat-label">Энергия:</span>
                        <div class="act-stat-bar"><div class="act-stat-mp" style="width:{{ $mpPct }}%"></div></div>
                        <span class="act-stat-val">{{ $player->mp_now }}/{{ $playerDecorator->getMpMax() }}</span>
                    </div>
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