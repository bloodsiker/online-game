{{-- Подарок в информации игрока: плитка как у артефактов, подсказка как у предметов (GiftTooltipStrategy, ключ gift_{id}). --}}
<span class="player-gift" data-id="{{ \App\Modules\Gift\Application\ItemTooltip\GiftTooltipStrategy::PREFIX }}{{ $gift->id }}"
      onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"><img src="{{ $gift->shareItem?->image }}" width="60" height="60" alt="{{ $gift->shareItem?->name }}"></span>
