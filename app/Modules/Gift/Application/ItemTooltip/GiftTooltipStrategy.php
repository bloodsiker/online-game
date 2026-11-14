<?php

declare(strict_types=1);

namespace App\Modules\Gift\Application\ItemTooltip;

use App\Modules\Gift\Infrastructure\Persistence\Models\PlayerGift;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipDto;
use App\Modules\Item\Application\ItemTooltip\Strategy\ItemTooltipStrategyInterface;
use App\Modules\Share\Domain\Enums\ItemRarity;

/**
 * Подсказка полученного подарка — та же, что у предметов (public/js/item_tooltip.js), ключ «gift_{id}»,
 * плюс «Подарил», «Дата» и подпись. item_tooltip.js вставляет строки как HTML, поэтому всё,
 * что ввёл игрок (ник, подпись), экранируется здесь. Описание предмета — контент админки, как у остальных подсказок.
 */
final class GiftTooltipStrategy implements ItemTooltipStrategyInterface
{
    public const PREFIX = 'gift_';

    /** @param iterable<PlayerGift> $gifts */
    public function __construct(private readonly iterable $gifts) {}

    public function collect(ItemTooltipCollector $collector): void
    {
        foreach ($this->gifts as $gift) {
            $item = $gift->shareItem;
            if ($item === null) {
                continue;
            }
            $sender = $gift->visibleSender();
            $desc = (string) ($item->description ?? '');
            if ($gift->message !== null && $gift->message !== '') {
                // Подпись дарителя — отдельным цветом и курсивом (стиль inline: подсказка выводится на разных страницах).
                $desc .= ($desc !== '' ? '<br><br>' : '').'<b>'.e(__('gift.tooltip_signature')).'</b> '
                    .'<span class="gift-signature" style="color:#1d4f91; font-style:italic; font-weight:bold;">«'.e($gift->message).'»</span>';
            }

            $collector->add(new ItemTooltipDto(
                id: self::PREFIX.$gift->id,
                title: $item->name,
                color: ($item->rarity ?? ItemRarity::COMMON)->color(),
                image: $item->image,
                kind: $item->getTypeName(),
                price: '',
                diamond: '',
                lev: [],
                skills: [],
                desc: $desc,
                store: false,
                nogive: false,
                noweight: false,
                nosell: false,
                specialInfo: [
                    ['title' => __('gift.tooltip_from'), 'value' => $sender !== null ? e($sender->name) : e(__('gift.tooltip_anonymous'))],
                    ['title' => __('gift.tooltip_date'), 'value' => (string) $gift->created_at?->format('d.m.Y')],
                ],
            ));
        }
    }
}
