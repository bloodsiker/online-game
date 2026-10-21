@php
    $fallbackImage = asset('main/images/effects-unavailable.png');
@endphp
<table width="490" border="0" cellspacing="0" cellpadding="0" class="mrg-top">
    <tbody>
    <tr height="22">
        <td width="20" align="right" valign="bottom"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-left.gif') }}" width="20" height="22" alt></td>
        <td class="tbl-shp_sml-top" valign="top" align="center">
            <table border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">
                <td><img src="{{ asset('img/bg/info/tbl-usi_label-left.gif') }}" width="27" height="22" alt></td>
                <td class="tbl-usi_label-center">{{ $title }}</td>
                <td><img src="{{ asset('img/bg/info/tbl-usi_label-right.gif') }}" width="27" height="22" alt></td>
            </tr></tbody></table>
        </td>
        <td width="20" align="left" valign="bottom"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-top-right.gif') }}" width="20" height="22" alt></td>
    </tr>
    <tr>
        <td class="tbl-usi_left">&nbsp;</td>
        <td class="tbl-usi_bg" valign="top" style="padding:6px 4px;">
            <div class="monster-abilities-grid">
                @foreach($abilities as $ability)
                    @php
                        $isEffect = $kind === 'effect';
                        $isPassive = $kind === 'passive';
                        $name = match (true) {
                            $isEffect => $ability->name,
                            $isPassive => $ability->type->label(),
                            default => $ability->getLabel(),
                        };
                        $image = match (true) {
                            $isPassive => $ability->type->iconUrl(),
                            $kind === 'skill' => $ability->image ?: $ability->mechanic_type->iconUrl(),
                            default => $ability->image ?: $fallbackImage,
                        };
                        $description = match (true) {
                            $isEffect => strip_tags((string) $ability->description),
                            $isPassive => $ability->type->monsterDescription($ability->value),
                            default => strip_tags($ability->getInstance()->getDescription()),
                        };
                        $meta = match (true) {
                            $isEffect => array_values(array_filter([
                                ['label' => 'Механика', 'value' => $ability->resolvedActiveType()?->label() ?? 'Дебаф'],
                                ['label' => 'Шанс', 'value' => $ability->pivot->chance.'%'],
                                ['label' => 'Длительность', 'value' => $ability->pivot->duration_seconds.' сек.'],
                                $ability->pivot->power_percent !== null ? ['label' => 'Сила', 'value' => $ability->pivot->power_percent.'%'] : null,
                            ])),
                            $isPassive => [
                                ['label' => 'Тип', 'value' => 'Встроенная пассивка'],
                                ['label' => 'Значение', 'value' => $ability->value.'%'],
                            ],
                            default => array_values(array_filter([
                                ['label' => 'Тип', 'value' => $ability->getLabel()],
                                [
                                    'label' => 'Условие',
                                    'value' => match (true) {
                                        $ability->mechanic_type === \App\Modules\Battle\Domain\Enums\BossMechanicType::DEATH_EXPLOSION => 'При смерти босса',
                                        $ability->trigger_hp_percent !== null => 'HP ≤ '.$ability->trigger_hp_percent.'%',
                                        $ability->trigger_turn !== null => 'Ход ≥ '.$ability->trigger_turn,
                                        default => 'С начала боя',
                                    },
                                ],
                                [
                                    'label' => 'Повтор',
                                    'value' => isset($ability->config['cooldown_turns'])
                                        ? 'каждые '.$ability->config['cooldown_turns'].' х.'
                                        : 'один раз за бой',
                                ],
                            ])),
                        };
                    @endphp
                    <a href="#" class="monster-ability-icon"
                       data-tooltip-type="{{ $isEffect ? 'Эффект' : ($isPassive ? 'Пассивка' : 'Умение') }}"
                       data-tooltip-name="{{ $name }}"
                       data-tooltip-image="{{ $image }}"
                       data-tooltip-description="{{ $description }}"
                       data-tooltip-meta="{{ e(json_encode($meta, JSON_UNESCAPED_UNICODE)) }}"
                       onmouseover="showMonsterAbilityInfo(this,event,2)"
                       onmouseout="showMonsterAbilityInfo(this,event,0)"
                       onclick="return false;">
                        <img src="{{ $image }}" width="60" height="60" alt="{{ $name }}">
                    </a>
                @endforeach
            </div>
        </td>
        <td class="tbl-usi_right">&nbsp;</td>
    </tr>
    <tr height="18">
        <td width="20" align="right" valign="top"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-left.gif') }}" width="20" height="18" alt></td>
        <td class="tbl-shp_sml-bottom" valign="top" align="center">&nbsp;</td>
        <td width="20" align="left" valign="top"><img src="{{ asset('img/bg/info/tbl-shp_sml-corner-bottom-right.gif') }}" width="20" height="18" alt></td>
    </tr>
    </tbody>
</table>
