<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Services;

use App\Models\Item\Item;

class MessageRenderer
{
    /**
     * Escape user input and render shortcodes as safe HTML.
     *
     * [[item_ID]]  → styled item name span
     * to[NAME]     → styled mention prefix
     */
    public function render(string $message, bool $trusted = false): string
    {
        // Trusted messages (system/information/quest) contain safe server-generated HTML
        $escaped = $trusted ? $message : e($message);

        // Replace [[item_ID]] shortcodes
        $rendered = preg_replace_callback('/\[\[item_(\d+)\]\]/', function ($matches) {
            $item = Item::with('itemInfo')->find((int) $matches[1]);

            if (! $item || ! $item->itemInfo) {
                return '<span class="chat-item-unknown" title="Предмет не найден">[???]</span>';
            }

            $name = e($item->getName());
            $name .= $item->upgrade_lvl > 0 ? ' +'.$item->upgrade_lvl : '';
            $color = e($item->itemInfo->rarity?->color() ?? '#666666');
            $desc = e($item->itemInfo->description ?? '');

            return '<span class="chat-item" style="color:'.$color.'" title="'.$desc.'">'.$name.'</span>';
        }, $escaped);

        // Highlight to[NAME] prefix
        $rendered = preg_replace(
            '/^to\[([^\]]+)\]\s*-?\s*/u',
            '<span class="chat-to">»$1</span> ',
            $rendered,
        );

        return $rendered;
    }
}
