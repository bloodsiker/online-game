<div class="event-influence-list">
    <p class="event-influence-list__intro">Влияние, заработанное вами за участие в событиях.</p>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr height="22">
            <td width="20" class="tbl-shp-sml lt"></td>
            <td class="tbl-shp-sml tt" align="center"><b>Влияние на картах</b></td>
            <td width="20" class="tbl-shp-sml rt"></td>
        </tr>
        <tr>
            <td class="tbl-shp-sides ls">&nbsp;</td>
            <td class="tbl-usi_bg" style="padding: 8px;">
                <table class="event-influence-table">
                    <thead><tr><th>Карта</th><th>Уровень</th><th width="150">Влияние</th><th width="180">Следующий уровень</th></tr></thead>
                    <tbody>
                    @forelse($mapInfluences as $mapInfluence)
                        <tr>
                            <td>
                                @if($mapInfluence->map?->slug)
                                    <a href="{{ route('map.public', ['slug' => $mapInfluence->map->slug]) }}" target="_blank"><b>{{ $mapInfluence->map->name }}</b></a>
                                @else
                                    <b>{{ $mapInfluence->map?->name ?? 'Карта удалена' }}</b>
                                @endif
                            </td>
                            <td>
                                @if($mapInfluence->currentLevel?->medal?->iconUrl())
                                    <img src="{{ $mapInfluence->currentLevel->medal->iconUrl() }}" width="32" height="32" style="object-fit:contain;vertical-align:middle" alt="">
                                @endif
                                <b>{{ $mapInfluence->currentLevel?->name ?? 'Без уровня' }}</b>
                            </td>
                            <td class="event-influence-table__value">{{ number_format($mapInfluence->influence, 0, '', ' ') }}</td>
                            <td align="center">
                                @if($mapInfluence->nextLevel)
                                    {{ $mapInfluence->nextLevel->name }} — {{ number_format($mapInfluence->nextLevel->required_influence, 0, '', ' ') }}
                                @else
                                    Максимальный уровень
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" align="center" style="padding: 18px; color: #766052;">Вы ещё не получали влияние за события.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </td>
            <td class="tbl-shp-sides rs">&nbsp;</td>
        </tr>
        <tr height="18"><td class="tbl-shp-sml lb"></td><td class="tbl-shp-sml bb"></td><td class="tbl-shp-sml rb"></td></tr>
    </table>
</div>
