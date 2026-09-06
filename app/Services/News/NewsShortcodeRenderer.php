<?php

declare(strict_types=1);

namespace App\Services\News;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipStatsBuilder;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection;

final class NewsShortcodeRenderer
{
    /** @var array<int, ShareItem> */
    private array $items = [];

    /** @var array<string, array<string, mixed>> */
    private array $tooltipItems = [];

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

        $this->loadItems(array_map('intval', $matches[1]));

        return preg_replace_callback($this->pattern(), function (array $match): string {
            $itemId = (int) $match[1];
            $count = isset($match[2]) && $match[2] !== '' ? max(1, (int) $match[2]) : 1;
            $item = $this->items[$itemId] ?? null;

            if (! $item) {
                return $match[0];
            }

            $tooltipId = 'news_'.$item->id.'_'.$count;
            $this->registerTooltip($item, $count, $tooltipId);

            return $this->renderItem($item, $count, $tooltipId);
        }, $html) ?? $html;
    }

    public function tooltipScript(): string
    {
        if ($this->tooltipItems === []) {
            return '';
        }

        $script = '<script>';

        foreach ($this->tooltipItems as $id => $item) {
            $script .= 'art_alt["AA_'.$id.'"] = '.json_encode($item, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG).';';
        }

        return $script.'</script>';
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

    private function registerTooltip(ShareItem $item, int $count, string $tooltipId): void
    {
        if (isset($this->tooltipItems[$tooltipId])) {
            return;
        }

        $levelRequirement = $item->requirements
            ->first(fn ($requirement): bool => $requirement->type === ShareItemRequirementType::LEVEL);

        $this->tooltipItems[$tooltipId] = [
            'id' => (string) $item->id,
            'title' => $item->name,
            'color' => $item->rarity->color(),
            'image' => asset($item->image),
            'count' => $count > 1 ? $count : null,
            'kind' => $item->getTypeName(),
            'price' => $item->price
                ? sprintf('<span title=""><img src="%s" border=0 width=11 height=11 align=absmiddle></span> %s', asset('img/icon/m_game.gif'), $item->price)
                : '',
            'lev' => $levelRequirement
                ? ['title' => ' Уровень ', 'value' => (string) $levelRequirement->min_value]
                : null,
            'skills' => ItemTooltipStatsBuilder::build($item),
            'skills_e' => $this->requirementsForTooltip($item),
            'desc' => $item->description ?? '',
            'nogive' => ! $item->is_sell ? 'Предмет нельзя передать!' : '',
            'noweight' => ! $item->is_weight ? 'Предмет не занимает места в рюкзаке' : '',
            'nosell' => ! $item->is_sell ? 'Предмет нельзя сдать в скупку' : '',
        ];
    }

    /**
     * @return array<int, array{title: string, value: string}>
     */
    private function requirementsForTooltip(ShareItem $item): array
    {
        $requirements = [];

        foreach ($item->requirements as $requirement) {
            if ($requirement->type === ShareItemRequirementType::LEVEL) {
                continue;
            }

            $requirements[] = [
                'title' => 'Требуется: '.$requirement->label(),
                'value' => (string) $requirement->min_value,
            ];
        }

        return $requirements;
    }

    private function renderItem(ShareItem $item, int $count, string $tooltipId): string
    {
        $countHtml = $count > 1
            ? '<div class="artifact-slot-qnt" style="float:right;margin:0 2px 2px 0;color:#fff;font:bold 11px Tahoma;text-shadow:1px 1px 1px #000;">'.$count.'</div>'
            : '&nbsp;';

        return sprintf(
            '<a href="%s" class="news-shortcode-item" onmouseover="artifactAltSimple(%s, 2, event);" onmouseout="artifactAltSimple(%s, 0, event);" style="display:inline-block;vertical-align:middle;text-decoration:none;"><table width="60" height="60" cellpadding="0" cellspacing="0" border="0" style="display:inline-table;margin:1px;background:url(%s);background-size:60px 60px;background-repeat:no-repeat;background-position:center;"><tr><td valign="bottom">%s</td></tr></table></a>',
            e(route('items.info.share', ['id' => $item->id])),
            e(json_encode($tooltipId, JSON_UNESCAPED_UNICODE)),
            e(json_encode($tooltipId, JSON_UNESCAPED_UNICODE)),
            e(asset($item->image)),
            $countHtml
        );
    }
}
