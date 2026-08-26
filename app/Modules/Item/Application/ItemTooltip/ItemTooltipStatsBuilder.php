<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\ItemTooltip;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ItemEffectValueType;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
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
    public static function build(ShareItem $item, int $upgradeLvl = 0): array
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

        // Бонус заточки: +5% за уровень, так же как считает PlayerStatService::fromEquipment()
        // (оружие — к урону, слоты брони — к своей броне). Другие слоты заточка не усиливает.
        $upgradeMultiplier = 1 + ($upgradeLvl * 5 / 100);
        $isWeaponSlot = $item->slot === ShareItemSlot::HAND;
        $isArmorSlot = $item->slot !== null && in_array($item->slot, ShareItemSlot::armorSlots(), true);

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

            $value = $stat->value;
            if ($upgradeLvl > 0 && $isArmorSlot && $stat->stat_type === ShareItemStatType::ARMOR && ! $stat->isPercent()) {
                $value = floor($value * $upgradeMultiplier);
            }

            $valueStr = $stat->isPercent() ? $value.'%' : (string) $value;
            $stats[] = ['title' => $stat->stat_type->label(), 'value' => '+'.$valueStr];
        }

        if ($attackMin !== null || $attackMax !== null) {
            if ($upgradeLvl > 0 && $isWeaponSlot) {
                $attackMin = $attackMin !== null ? floor($attackMin * $upgradeMultiplier) : null;
                $attackMax = $attackMax !== null ? floor($attackMax * $upgradeMultiplier) : null;
            }

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

    /**
     * Установленные в сокеты камни (item_gems). Каждый камень — заголовочная
     * строка (header) с его именем (ссылка на карточку камня в справочнике),
     * дальше каждая характеристика отдельной строкой (не одной строкой через запятую).
     * Цвет строк — цвет редкости самого камня (ItemRarity::color()), а не
     * фиксированный цвет секции.
     *
     * @return array<int, array{title: string, value: string, header?: bool, url?: string, color: string}>
     */
    public static function buildGems(Item $item): array
    {
        $rows = [];

        foreach ($item->gems as $gem) {
            $color = $gem->gemInfo->rarity->color();
            $rows[] = [
                'title' => $gem->gemInfo->name,
                'value' => '',
                'header' => true,
                'url' => route('items.info.share', ['id' => $gem->gemInfo->id]),
                'color' => $color,
            ];
            array_push($rows, ...self::formatStatEntries($gem->gemInfo->gem_stats ?? [], $color));
        }

        return $rows;
    }

    /**
     * Вплавленные в слоты руны (item_runes) — прокрученные статы и пассивка,
     * тем же построчным форматом и цветом редкости, что и камни.
     *
     * @return array<int, array{title: string, value: string, header?: bool, url?: string, color: string}>
     */
    public static function buildRunes(Item $item): array
    {
        $rows = [];

        foreach ($item->runes as $rune) {
            $color = $rune->runeInfo->rarity->color();
            $rows[] = [
                'title' => $rune->runeInfo->name,
                'value' => '',
                'header' => true,
                'url' => route('items.info.share', ['id' => $rune->runeInfo->id]),
                'color' => $color,
            ];
            array_push($rows, ...self::formatStatEntries($rune->stats ?? [], $color));

            if ($rune->passive_skill) {
                $rows[] = ['title' => 'Пассивка', 'value' => $rune->passive_skill['description'], 'color' => $color];
            }
        }

        return $rows;
    }

    /**
     * @param  array<int, array<string, mixed>>  $entries
     * @return array<int, array{title: string, value: string, color: string}>
     */
    private static function formatStatEntries(array $entries, string $color): array
    {
        $rows = [];

        foreach ($entries as $entry) {
            $key = $entry['type'] ?? $entry['stat'] ?? null;

            if (! $key) {
                continue;
            }

            $valueStr = ($entry['is_percent'] ?? false) ? ($entry['value'] ?? 0).'%' : (string) ($entry['value'] ?? 0);
            $rows[] = ['title' => self::statLabel($key), 'value' => '+'.$valueStr, 'color' => $color];
        }

        return $rows;
    }

    private static function statLabel(string $key): string
    {
        return ShareItemStatType::tryFrom($key)?->label() ?? match ($key) {
            'attack' => 'Атака',
            'strength' => 'Сила',
            'mp_max' => 'Уровень маны',
            default => ucfirst($key),
        };
    }
}
