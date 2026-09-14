<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection;

final class NewsShortcodeRenderer
{
    /** @var array<int, ShareItem> */
    private array $items = [];

    public function __construct(private readonly ItemTooltipCollector $tooltipCollector) {}

    public function render(?string $html): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $matches = [];
        preg_match_all($this->pattern(), $html, $matches);

        if (empty($matches[1])) {
            return $html;
        }

        $itemIds = array_map('intval', $matches[1]);
        $this->loadItems($itemIds);
        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy(
            array_values(array_intersect_key($this->items, array_flip($itemIds)))
        ));

        return preg_replace_callback($this->pattern(), function (array $match): string {
            $itemId = (int) $match[1];
            $count = isset($match[2]) && $match[2] !== '' ? max(1, (int) $match[2]) : 1;
            $item = $this->items[$itemId] ?? null;

            if (! $item) {
                return $match[0];
            }

            return $this->renderItem($item, $count);
        }, $html) ?? $html;
    }

    public function tooltipScript(): string
    {
        return $this->tooltipCollector->renderScript();
    }

    private function pattern(): string
    {
        return '/\[\[item:(\d+)(?:\s*;\s*count\s*:\s*(\d+))?\]\]/i';
    }

    /**
     * @param  array<int, int>  $ids
     */
    private function loadItems(array $ids): void
    {
        $ids = array_values(array_unique(array_filter($ids)));
        $missingIds = array_values(array_filter($ids, fn (int $id): bool => ! isset($this->items[$id])));

        if ($missingIds === []) {
            return;
        }

        /** @var Collection<int, ShareItem> $items */
        $items = ShareItem::query()
            ->with(['stats', 'effects', 'requirements.skill'])
            ->whereIn('id', $missingIds)
            ->get();

        foreach ($items as $item) {
            $this->items[$item->id] = $item;
        }
    }

    private function renderItem(ShareItem $item, int $count): string
    {
        $countHtml = $count > 1
            ? '<span class="artifact-slot-qnt" style="position:absolute;right:2px;bottom:2px;color:#fff;font:bold 11px Tahoma;text-shadow:1px 1px 1px #000;">'.$count.'</span>'
            : '';

        return sprintf(
            '<a href="%s" class="news-shortcode-item" data-id="%s" onmouseover="showItemInfo(this,event,2)" onmouseout="showItemInfo(this,event,0)" onclick="window.open(this.href, \'\', \'width=730,height=700,location=yes,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no\'); return false;" style="display:inline-block;vertical-align:middle;text-decoration:none;"><span class="news-shortcode-item__image" style="position:relative;display:inline-block;width:72px;height:71px;margin:1px;vertical-align:middle;background-image:url(%s),url(%s);background-size:72px 71px,60px 60px;background-position:0 0,6px 5px;background-repeat:no-repeat;">%s</span></a>',
            e(route('items.info.share', ['id' => $item->id])),
            e((string) $item->id),
            e(asset('main/images/user-reward-frame.png')),
            e(asset($item->image)),
            $countHtml
        );
    }
}
