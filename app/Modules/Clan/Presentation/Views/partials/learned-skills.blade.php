@php
    use App\Modules\Clan\Domain\Enums\ClanSkillEffectType;

    $fallbackImage = asset('main/images/effects-unavailable.png');
@endphp

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-top: 7px;">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt=""></td>
        <td class="tbl-shp_sml-top" valign="top" align="center">
            <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
                <td><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt=""></td>
                <td class="tbl-usi_label-center">Изученные навыки</td>
                <td><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt=""></td>
            </tr></tbody></table>
        </td>
        <td width="20" align="left" valign="bottom"><img src="{{ asset('main/images/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt=""></td>
    </tr>
    <tr>
        <td class="tbl-usi_left">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding: 6px 4px;">
            <div class="clan-skills-grid">
                @forelse($skills as $learnedSkill)
                    @php
                        $definition = $learnedSkill->definition;
                        $levelData = $definition->levels->firstWhere('level', $learnedSkill->current_level);
                        $magicSkill = $levelData?->magicSkill;
                        $image = $magicSkill?->image ?: resolve_storage_image_url($definition->icon) ?: $fallbackImage;
                        $meta = [
                            ['label' => 'Уровень', 'value' => $learnedSkill->current_level.' / '.$definition->max_level],
                        ];

                        if ($magicSkill) {
                            $meta[] = ['label' => 'Тип', 'value' => $magicSkill->is_passive ? 'Пассивный навык' : 'Активный навык'];

                            foreach ($magicSkill->effects ?? [] as $effect) {
                                $effectName = ClanSkillEffectType::tryFrom($effect['type'] ?? '')?->label() ?? ($effect['type'] ?? 'Бонус');
                                $effectValue = ($effect['value'] ?? 0).(! empty($effect['is_percent']) ? '%' : '');
                                $meta[] = ['label' => $effectName, 'value' => '+'.$effectValue];
                            }
                        }

                        $skillUrl = $magicSkill ? route('magic_skill.info', $magicSkill->id) : '#';
                    @endphp
                    <a href="{{ $skillUrl }}" class="clan-skill-icon"
                       data-tooltip-container="clan_skill_alt"
                       data-tooltip-type="Клановый навык"
                       data-tooltip-name="{{ $definition->name }}"
                       data-tooltip-image="{{ $image }}"
                       data-tooltip-description="{{ strip_tags((string) $definition->description) }}"
                       data-tooltip-meta="{{ e(json_encode($meta, JSON_UNESCAPED_UNICODE)) }}"
                       onmouseover="showSkillEffectInfo(this,event,2)"
                       onmouseout="showSkillEffectInfo(this,event,0)"
                       @if($magicSkill)
                           onclick="window.open(this.href, '', 'width=730,height=700,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no'); return false;"
                       @else
                           onclick="return false;"
                       @endif>
                        <img src="{{ $image }}" width="60" height="60" alt="{{ $definition->name }}">
                    </a>
                @empty
                    <div style="padding: 22px 4px; text-align: center;">Клан пока не изучил ни одного навыка.</div>
                @endforelse
            </div>
        </td>
        <td class="tbl-usi_right">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" align="right" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt=""></td>
        <td class="tbl-shp_sml-bottom" valign="top" align="center">&nbsp;</td>
        <td width="20" align="left" valign="top"><img src="{{ asset('main/images/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt=""></td>
    </tr>
    </tbody>
</table>
