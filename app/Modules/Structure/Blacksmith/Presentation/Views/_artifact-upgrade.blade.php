@include('blacksmith::_craft-hint', [
    'hint' => __('Каждая ступень усиливает собственные характеристики артефакта на 5%. Успех — 100%.'),
])

@if(isset($errors) && $errors->any())
    <div class="message" role="alert">{{ $errors->first() }}</div>
@endif

@forelse($artifactUpgrades as $upgrade)
    @php
        $artifact = $upgrade['item'];
        $source = $artifact->itemInfo;
        $level = (int) $artifact->upgrade_lvl;
        $nextLevel = min($maxArtifactUpgradeLevel, $level + 1);
    @endphp
    <table class="artifact-assembly artifact-upgrade" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td width="8" height="10" class="gp-frame-tl"></td>
            <td height="10" class="gp-frame-t"></td>
            <td width="8" height="10" class="gp-frame-tr"></td>
        </tr>
        <tr>
            <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
            <td class="gp-frame-bg" align="center">
                <div class="artifact-upgrade__title" style="color:{{ $source->rarity->color() }}">{{ $source->name }}</div>
                <div class="artifact-upgrade__equation">
                    <div class="artifact-upgrade__operand">
                        <a class="upgrade-icon" href="{{ route('items.info', ['id' => $artifact->id]) }}"
                           data-id="{{ $artifact->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                           onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                            <img src="{{ $source->image }}" alt="{{ $source->name }} +{{ $level }}">
                        </a>
                        <span class="artifact-assembly__count">+{{ $level }}</span>
                    </div>
                    <span class="artifact-upgrade__symbol" aria-hidden="true">+</span>
                    <div class="artifact-upgrade__operand" title="{{ $source->name }} +0">
                        <a class="upgrade-icon" href="{{ route('items.info.share', ['id' => $source->id]) }}"
                           data-id="artifact_template_{{ $source->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                           onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                            <img src="{{ $source->image }}" alt="{{ $source->name }} +0">
                        </a>
                        <span class="artifact-assembly__count {{ $upgrade['donors'] > 0 ? 'req-ok' : 'req-fail' }}">{{ $upgrade['donors'] }} / 1 <span class="artifact-upgrade__note">(+0)</span></span>
                    </div>
                    @foreach($upgrade['materials'] as $material)
                        <span class="artifact-upgrade__symbol" aria-hidden="true">+</span>
                        <div class="artifact-upgrade__operand" title="{{ $material['item']->name }}">
                            <a class="upgrade-icon" href="{{ route('items.info.share', ['id' => $material['item']->id]) }}"
                               data-id="artifact_template_{{ $material['item']->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                               onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                                <img src="{{ $material['item']->image }}" alt="{{ $material['item']->name }}">
                            </a>
                            <span class="artifact-assembly__count {{ $material['available'] >= $material['needed'] ? 'req-ok' : 'req-fail' }}">{{ $material['available'] }} / {{ $material['needed'] }}</span>
                        </div>
                    @endforeach
                    <span class="artifact-upgrade__symbol" aria-hidden="true">=</span>
                    <div class="artifact-upgrade__operand">
                        <span class="upgrade-icon artifact-upgrade__result-icon" data-id="artifact_preview_{{ $artifact->id }}"
                              onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)">
                            <img src="{{ $source->image }}" alt="{{ $source->name }} +{{ $nextLevel }}">
                        </span>
                        <span class="artifact-assembly__count req-ok">+{{ $nextLevel }}</span>
                    </div>
                    <div class="artifact-upgrade__action">
                        @if($level < $maxArtifactUpgradeLevel)
                            <form method="post" action="{{ route('blacksmith.artifact_upgrade', $blacksmith->id) }}" onsubmit="this.querySelector('input[type=submit]').disabled = true;">
                                @csrf
                                <input type="hidden" name="item_id" value="{{ $artifact->id }}">
                                <input type="hidden" name="expected_level" value="{{ $level }}">
                                <span class="butt1 pointer {{ $upgrade['canUpgrade'] ? '' : 'disabled' }}"><span>
                                    <input type="submit" class="grnn" value="{{ __('Заточить') }} +{{ $nextLevel }}" @disabled(! $upgrade['canUpgrade'])>
                                </span></span>
                            </form>
                            <div class="artifact-upgrade__price {{ $user->money >= $upgrade['config']->gold_cost ? 'req-ok' : 'req-fail' }}">
                                <img src="{{ asset('img/icon/m_game.gif') }}" width="11" height="11" alt="{{ __('Монеты') }}">
                                {{ format_money($upgrade['config']->gold_cost) }}
                            </div>
                        @else
                            <b>{{ __('Достигнут максимальный уровень заточки.') }}</b>
                        @endif
                    </div>
                </div>
                <fieldset class="artifact-assembly__pool">
                    <legend>{{ __('Характеристики') }}</legend>
                    <div class="artifact-upgrade__stats">
                        @foreach($source->stats as $stat)
                            <div class="artifact-upgrade__stat">
                                <span>{{ $stat->stat_type->label() }}:</span>
                                <span class="artifact-upgrade__stat-values">
                                    <b>{{ round($stat->value + \App\Modules\Structure\Blacksmith\Domain\Services\ArtifactUpgradeRules::bonus((float) $stat->value, $level), 4) }}{{ $stat->isPercent() ? '%' : '' }}</b>
                                    <span class="artifact-upgrade__stat-arrow">→</span>
                                    <b class="grnn">{{ round($stat->value + \App\Modules\Structure\Blacksmith\Domain\Services\ArtifactUpgradeRules::bonus((float) $stat->value, $nextLevel), 4) }}{{ $stat->isPercent() ? '%' : '' }}</b>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            </td>
            <td width="8" class="gp-frame-r"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
        </tr>
        <tr>
            <td width="8" height="10" class="gp-frame-bl"></td>
            <td height="10" class="gp-frame-b"></td>
            <td width="8" height="10" class="gp-frame-br"></td>
        </tr>
        </tbody>
    </table>
@empty
    <div class="empty-list">{{ __('В рюкзаке нет артефактов с доступной заточкой.') }}</div>
@endforelse
