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
                        $name = $isEffect ? $ability->name : $ability->getLabel();
                        $image = $ability->image ?: $fallbackImage;
                        $description = $isEffect
                            ? strip_tags((string) $ability->description)
                            : strip_tags((string) ($ability->config['description'] ?? ''));
                        $meta = $isEffect
                            ? array_values(array_filter([
                                ['label' => 'Механика', 'value' => $ability->resolvedActiveType()?->label() ?? 'Дебаф'],
                                ['label' => 'Шанс', 'value' => $ability->pivot->chance.'%'],
                                ['label' => 'Длительность', 'value' => $ability->pivot->duration_seconds.' сек.'],
                                $ability->pivot->power_percent !== null ? ['label' => 'Сила', 'value' => $ability->pivot->power_percent.'%'] : null,
                            ]))
                            : array_values(array_filter([
                                ['label' => 'Тип', 'value' => $ability->getLabel()],
                                $ability->trigger_hp_percent !== null ? ['label' => 'Условие', 'value' => 'HP ≤ '.$ability->trigger_hp_percent.'%'] : null,
                                $ability->trigger_turn !== null ? ['label' => 'Условие', 'value' => 'Ход '.$ability->trigger_turn] : null,
                            ]));
                    @endphp
                    <a href="#" class="monster-ability-icon"
                       data-tooltip-type="{{ $isEffect ? 'Эффект' : 'Умение' }}"
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
