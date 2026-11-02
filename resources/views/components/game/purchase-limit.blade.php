@props(['availability' => null])
@if($availability)
    <div class="shop-purchase-limit" style="margin-top:6px;font-size:11px;line-height:1.4;color:#75503a;white-space:normal;">
        {{ $availability['label'] }}
        @if($availability['remaining'] === 0 && $availability['available_at'])
            <br>{{ __('commerce.purchase_available', ['date' => $availability['available_at']->format('d.m.Y H:i')]) }}
        @endif
    </div>
@endif
