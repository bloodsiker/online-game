@include('blacksmith::_craft-hint', ['hint' => __('Соберите все части, чтобы получить один случайный артефакт из списка.')])
@forelse($assemblies as $assembly)
    <table class="artifact-assembly" data-assembly-id="{{ $assembly['id'] }}" width="100%" border="0" cellspacing="0" cellpadding="0">
        <tbody>
        <tr>
            <td width="8" height="10" class="gp-frame-tl"></td>
            <td height="10" class="gp-frame-t"></td>
            <td width="8" height="10" class="gp-frame-tr"></td>
        </tr>
        <tr>
            <td width="8" class="gp-frame-l"><img src="{{ asset('img/bg/blank.gif') }}" width="8" alt=""></td>
            <td class="gp-frame-bg" align="center">
        <div class="artifact-assembly__row">
            <div class="artifact-assembly__ingredients">
                @foreach($assembly['ingredients'] as $item)
                    <div class="artifact-assembly__ingredient">
                        <a class="upgrade-icon" href="{{ route('items.info.share', ['id' => $item['id']]) }}"
                           data-id="{{ $item['id'] }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                           onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}">
                        </a>
                        <span class="artifact-assembly__count {{ $item['active'] ? 'req-ok' : 'req-fail' }}">{{ $item['availableCount'] }} / {{ $item['requiredCount'] }}</span>
                    </div>
                @endforeach
            </div>
            <span class="artifact-assembly__equals" aria-hidden="true">=</span>
            <div class="artifact-assembly__result">
                <div class="artifact-assembly__result-preview">
                @if($assembly['resultMode'] === 'fixed' && count($assembly['resultPool']) === 1)
                    <span class="upgrade-icon" data-id="{{ $assembly['resultPool'][0]['id'] }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"><img src="{{ $assembly['resultPool'][0]['image'] }}" alt="{{ $assembly['resultPool'][0]['name'] }}"></span>
                @else
                    <span class="upgrade-icon random-result-icon" title="{{ __('Случайный артефакт') }}">?</span>
                @endif
                @if($assembly['resultMode'] === 'fixed')
                    <div class="artifact-assembly__result-name" style="color:{{ $assembly['rarityColor'] }}">{{ $assembly['resultPool'][0]['name'] ?? '' }}</div>
                @else
                    <br>
                @endif
                </div>
                <span class="butt1 pointer {{ $assembly['canCraft'] ? '' : 'disabled' }}"><span>
                    <input type="button" class="grnn artifact-assemble-button" value="{{ __('Собрать') }}"
                           @if($assembly['canCraft']) data-href="{{ route('blacksmith.assemble', ['id' => $blacksmith->id, 'assembly' => $assembly['id']]) }}" @else disabled @endif>
                </span></span>
            </div>
        </div>
        <fieldset class="artifact-assembly__pool">
            <legend>{{ __('Возможные артефакты') }}</legend>
            <div class="artifact-assembly__pool-items">
                @foreach($assembly['resultPool'] as $poolItem)
                    <div class="artifact-assembly__pool-item" data-result-id="{{ $poolItem['id'] }}">
                        <a class="upgrade-icon" href="{{ route('items.info.share', ['id' => $poolItem['id']]) }}"
                           data-id="{{ $poolItem['id'] }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"
                           onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                            <img src="{{ $poolItem['image'] }}" alt="{{ $poolItem['name'] }}">
                        </a>
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
    <div class="empty-list">{{ __('Нет доступных сборок артефактов.') }}</div>
@endforelse
