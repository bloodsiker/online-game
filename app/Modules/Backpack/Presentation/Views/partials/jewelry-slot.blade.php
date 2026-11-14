{{-- Ячейка ювелирного слота (кольцо, серьга, ожерелье, колчан) на странице экипировки; $emptyImage — плейсхолдер пустой ячейки. --}}
<td class="item-hero" align="center"
    @if($item) data-id="{{ $item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
    @if($item)
        <img src="{{ $item->itemInfo->image }}" class="hero-itm" style="background: linear-gradient(0deg, rgb(206, 187, 170), rgb(233, 225, 217)); border-color: rgb(206, 187, 170);">
        <a href="{{ route('items.put_off', ['id' => $item->id]) }}" class="item-put-off" onclick="hideEquippedItemTooltip()">{{ __('снять') }}</a>
    @else
        <img src="{{ isset($emptyImage) ? asset($emptyImage).'?v='.@filemtime(public_path($emptyImage)) : asset('img/bg/empty_slot.gif') }}" class="hero-itm">
    @endif
</td>
