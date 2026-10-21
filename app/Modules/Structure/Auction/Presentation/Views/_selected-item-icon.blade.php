<div class="auction-selected-item" style="position:relative;width:72px;height:71px;flex-shrink:0"
     @if($iconItem) data-id="{{ $iconItem->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" @endif>
    <img src="{{ asset($iconImage ?: 'img/bg/empty_slot.gif') }}" width="60" height="60" alt="{{ $iconName }}"
         style="position:absolute;left:6px;top:5px;width:60px;height:60px;max-width:none;object-fit:contain">
    <img src="{{ asset('main/images/user-reward-frame.png') }}" width="72" height="71" alt="" aria-hidden="true"
         style="position:absolute;inset:0;width:72px;height:71px;max-width:none;pointer-events:none">
</div>
