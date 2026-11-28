@php
    $itemLink = static fn (int $id): string => route('items.info.share', ['id' => $id]);
    $tip = static fn ($item): string => 'data-id="'.$item->id.'" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)"';
@endphp
@foreach($rates as $rate)
    @php $have = (int) ($owned[(int) $rate->share_item_id] ?? 0); @endphp
    <table class="coll w100 p10h p6v brd2-all festival-row">
        <tbody>
        <tr class="bg_l">
            <td>
                <table class="festival-table">
                    <tr>
                        <td>
                            <a class="upgrade-icon" href="{{ $itemLink((int) $rate->share_item_id) }}" {!! $tip($rate->item) !!}
                               onclick="window.open(this.href, '', 'width=730,height=700,resizable=yes,scrollbars=yes'); return false;">
                                <img src="{{ $rate->item->image }}" alt="{{ $rate->item->name }}">
                            </a>
                        </td>
                        <td><b>{{ $rate->item->name }}</b><br>{{ $rate->points }} {{ __($perLabel) }}</td>
                        <td class="num">{{ __('festival.in_backpack') }}: <b>{{ $have }}</b></td>
                        <td class="num">
                            <form method="post" data-ajax action="{{ route($route, ['npc' => $npcId]) }}" style="margin:0;">
                                @csrf
                                <input type="hidden" name="share_item_id" value="{{ $rate->share_item_id }}">
                                <div class="cart-amount-sell-price festival-counter">
                                    <span class="cart-amount-input-cont">
                                        <span class="b-input">
                                            <span class="b-input__inner">
                                                <span class="arrow left left-disabled" onclick="festivalCounter(this);" title="{{ __('festival.decrease') }}"></span>
                                                <span class="arrow right" onclick="festivalCounter(this);" title="{{ __('festival.increase') }}"></span>
                                                <input type="text" data-min="{{ $have > 0 ? 1 : 0 }}" data-max="{{ $have }}" name="quantity" value="{{ $have }}" class="cart_amount_sell_input count_sell" autocomplete="off" @readonly($have < 1)>
                                            </span>
                                        </span>
                                    </span>
                                </div>
                                <span class="butt1 pointer {{ $have < 1 ? 'disabled' : '' }}"><span><input type="submit" class="grnn" value="{{ __($button) }}" @disabled($have < 1)></span></span>
                            </form>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        </tbody>
    </table>
    <div style="height:5px;"></div>
@endforeach
