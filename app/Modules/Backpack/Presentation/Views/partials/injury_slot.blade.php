<span
    class="injury-bandage injury-bandage--{{ $injury->severity->value }} @if($injury->imageUrl()) injury-bandage--with-image @endif"
    data-id="{{ $injury->tooltipId() }}"
    data-injury-expires="{{ $injury->expires_at->timestamp }}"
    onmouseover="showItemInfo(this,event,2)"
    onmouseout="showItemInfo(this,event,0)"
    aria-label="{{ $injury->tooltip() }}"
>
    @if($injury->imageUrl())
        <img src="{{ $injury->imageUrl() }}" class="injury-bandage__image" alt="{{ $injury->displayName() }}">
    @endif
    <span class="injury-bandage__timer">{{ $injury->remainingLabel() }}</span>
</span>
