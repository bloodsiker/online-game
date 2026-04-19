<?php

declare(strict_types=1);

namespace App\Services\ItemTooltip;

use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;

/**
 * Строит массив строк характеристик для тултипа предмета.
 * Каждая строка: ['title' => '...', 'value' => '...']
 */
final class ItemTooltipStatsBuilder
{
    /**
     * @return array<int, array{title: string, value: string}>
     */
    public static function build(ShareItem $item): array
    {
        $stats = [];

        // Слот экипировки
        if ($item->slot !== null) {
            $stats[] = ['title' => 'Слот', 'value' => $item->slot->label()];
        }

        // Двуручное оружие
        if ($item->is_two_hand) {
            $stats[] = ['title' => 'Тип', 'value' => 'Двуручное'];
        }

        // Пассивные статы из share_item_stats
        // attack_min и attack_max объединяем в одну строку "X – Y"
        $attackMin = null;
        $attackMax = null;

        foreach ($item->stats as $stat) {
            if ($stat->stat_type === ShareItemStatType::ATTACK_MIN) {
                $attackMin = $stat->value;

                continue;
            }
            if ($stat->stat_type === ShareItemStatType::ATTACK_MAX) {
                $attackMax = $stat->value;

                continue;
            }

            $valueStr = $stat->isPercent() ? $stat->value.'%' : (string) $stat->value;
            $stats[] = ['title' => $stat->stat_type->label(), 'value' => '+'.$valueStr];
        }

        if ($attackMin !== null || $attackMax !== null) {
            $stats[] = [
                'title' => 'Атака',
                'value' => '+'.($attackMin ?? 0).' .. +'.($attackMax ?? 0),
            ];
        }

        // Активные эффекты из share_item_effects (зелья, баффы)
        foreach ($item->effects as $effect) {
            $valueStr = $effect->value_type === ItemEffectValueType::PERCENT
                ? $effect->value.'%'
                : (string) $effect->value;

            if ($effect->duration_seconds) {
                $valueStr .= ' ('.$effect->duration_seconds.' сек.)';
            }

            $stats[] = ['title' => $effect->effect_type->label(), 'value' => '+'.$valueStr];
        }

        return $stats;
    }

    /**
     * @return array<int, array{title: string, value: string}>
     */
    public static function buildRequirements(ShareItem $item): array
    {
        $reqs = [];

        foreach ($item->requirements as $req) {
            $reqs[] = [
                'title' => $req->label(),
                'type' => $req->type->value,
                'stat_key' => $req->stat_key,
                'skill_id' => $req->skill_id,
                'min_value' => $req->min_value,
            ];
        }

        return $reqs;
    }
}
