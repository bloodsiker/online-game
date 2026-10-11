@php
    $item = $items->get($entry->share_item_id);
    $buying = $action === 'purchase';
    $goodId = $buying ? $entry->id : $entry->trade_good_id;
    $price = (int) ($buying ? $entry->base_price : $entry->sale_price);
    $available = (int) ($buying ? $entry->stock->current_quantity : $counts[$item->id]);
    $maxCount = $buying ? min($available, max(0, $capacity - $cargoCount), $price > 0 ? intdiv($money, $price) : $available) : $available;
    $canTrade = $licensed && $maxCount > 0 && (!$buying || $level >= $entry->required_skill_lvl);
    $formId = 'trade-'.$action.'-'.$goodId;
@endphp
<tr><td>
    <table class="coll w100 p10h p6v brd2-all trade-row"><tbody><tr class="bg_l">
        <td width="60">
            <div class="relic-frame">
                <a href="{{ url('/item/info/s/'.$item->id) }}" data-id="{{ $item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" onclick="window.open(this.href,'','width=730,height=700,resizable=yes,scrollbars=yes');return false;">
                    <img class="relic-img" src="{{ $item->image }}" alt="{{ $item->name }}">
                </a>
            </div>
        </td>
        <td>
            <div class="trade-item-title">
                <a class="relic-name" href="{{ url('/item/info/s/'.$item->id) }}" style="color:{{ $item->rarity?->color() ?? '#666666' }}" data-id="{{ $item->id }}" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" onclick="window.open(this.href,'','width=730,height=700,resizable=yes,scrollbars=yes');return false;">{{ $item->name }}</a>
            </div>
            <div class="trade-item-detail">{{ $buying ? 'Требуемый навык: '.$entry->required_skill_lvl : 'Торговый груз' }}</div>
            <span class="relic-badge {{ $canTrade ? 'active' : 'inactive' }}">
                {{ !$licensed ? 'нужна лицензия' : ($buying && $level < $entry->required_skill_lvl ? 'недостаточно навыка' : ($canTrade ? ($buying ? 'доступен для покупки' : 'принимается сейчас') : 'покупка недоступна')) }}
            </span>
        </td>
        <td align="center" width="140">
            <div>За 1 шт.: <b>{{ number_format($price, 0, '', ' ') }}</b> монет</div>
            @unless($buying)<div>Опыт: <b>+{{ $entry->exp_per_unit }}</b> за шт.</div>@endunless
            <div>{{ $buying ? 'В наличии:' : 'В рюкзаке:' }} <b class="redd">{{ $available }}</b> шт.</div>
        </td>
        <td align="center" width="110">
            <div class="cart-amount-sell-price {{ $canTrade ? '' : 'disabled' }}">
                <span class="cart-amount-input-cont"><span class="b-input"><span class="b-input__inner">
                    <span class="arrow left left-disabled" data-trade-step="-1" title="Уменьшить количество"></span>
                    <span class="arrow right {{ $maxCount <= 1 ? 'right-disabled' : '' }}" data-trade-step="1" title="Увеличить количество"></span>
                    <input type="text" inputmode="numeric" name="quantity" value="1" form="{{ $formId }}" class="cart_amount_sell_input trade-count" data-max="{{ max(1, $maxCount) }}" data-price="{{ $price }}" autocomplete="off" aria-label="Количество" @disabled(!$canTrade)>
                </span></span></span>
            </div>
            <div>= <b class="redd trade-total">{{ number_format($price, 0, '', ' ') }}</b> монет</div>
        </td>
        <td align="right" width="80">
            <form id="{{ $formId }}" method="post" action="{{ route('trade.act', [$post->id, $action]) }}">
                @csrf
                <input type="hidden" name="request_key" value="{{ (string) Illuminate\Support\Str::uuid() }}">
                <input type="hidden" name="trade_good_id" value="{{ $goodId }}">
                <span class="butt1 pointer trade-action {{ $canTrade ? '' : 'disabled' }}"><span><input type="submit" value="{{ $buying ? 'Купить' : 'Продать' }}" @disabled(!$canTrade)></span></span>
            </form>
        </td>
    </tr></tbody></table>
</td></tr>
