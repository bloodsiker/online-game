<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\RunePassiveType;
use App\Enums\RuneRarity;
use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Item\ItemRune;
use App\Models\User;

class RuneService
{
    public const MAX_RUNE_SLOTS = 3;

    /**
     * Stat pool with base value ranges.
     * 'type' maps to PlayerStatService::modifiersFromEntry() keys.
     */
    private const STAT_POOL = [
        'attack'       => ['min' => 3,  'max' => 8],   // раскрывается в 4 стата урона
        'armor'        => ['min' => 5,  'max' => 12],
        'hp_max'       => ['min' => 20, 'max' => 50],
        'mp_max'       => ['min' => 15, 'max' => 35],
        'strength'     => ['min' => 2,  'max' => 5],
        'agility'      => ['min' => 2,  'max' => 5],
        'intelligence' => ['min' => 2,  'max' => 5],
        'critical'     => ['min' => 1,  'max' => 4],
        'dodge'        => ['min' => 1,  'max' => 4],
    ];

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Вставить руну в слот предмета. Потребляет руну из рюкзака.
     *
     * Safe mode  — всегда успешно, случайные статы.
     * Risk mode  — шанс провала (руна уничтожается), при успехе +25% к диапазону значений.
     */
    public function imbue(
        User $user,
        Item $item,
        Backpack $runeSlot,
        int $slotIndex,
        bool $riskMode,
    ): array {
        $runeInfo = $runeSlot->item->itemInfo;

        if ($runeInfo->type !== ShareItemType::RUNE) {
            return ['success' => false, 'message' => 'Выбранный предмет не является руной.'];
        }

        if ($slotIndex < 0 || $slotIndex >= $item->rune_slot_count) {
            return ['success' => false, 'message' => 'Неверный номер рунного слота.'];
        }

        $alreadyFilled = ItemRune::where('item_id', $item->id)
            ->where('slot_index', $slotIndex)
            ->exists();

        if ($alreadyFilled) {
            return ['success' => false, 'message' => 'Рунный слот уже занят. Сначала удалите руну.'];
        }

        /** @var RuneRarity $rarity */
        $rarity = $runeInfo->rune_rarity;

        // Режим риска: проверяем провал
        if ($riskMode && $rarity->riskFailChance() > 0) {
            $roll = random_int(1, 100);
            if ($roll <= $rarity->riskFailChance()) {
                $this->consumeFromBackpack($runeSlot);

                return [
                    'success'  => false,
                    'message'  => sprintf('Вплавление провалилось! Руна «%s» уничтожена.', $runeInfo->name),
                    'destroyed' => true,
                ];
            }
        }

        $stats   = $this->rollStats($runeInfo, $riskMode);
        $passive = $this->rollPassive($rarity);

        $this->consumeFromBackpack($runeSlot);

        ItemRune::create([
            'item_id'       => $item->id,
            'slot_index'    => $slotIndex,
            'share_item_id' => $runeInfo->id,
            'stats'         => $stats,
            'passive_skill' => $passive,
            'reroll_count'  => 0,
        ]);

        $passiveNote = $passive
            ? sprintf(' Пассивка: %s', $passive['description'])
            : '';

        return [
            'success' => true,
            'message' => sprintf('Руна «%s» вплавлена в слот %d.%s', $runeInfo->name, $slotIndex + 1, $passiveNote),
        ];
    }

    /**
     * Удалить руну из слота (руна уничтожается, слот освобождается).
     */
    public function removeRune(Item $item, int $slotIndex): array
    {
        $itemRune = ItemRune::where('item_id', $item->id)
            ->where('slot_index', $slotIndex)
            ->first();

        if (! $itemRune instanceof ItemRune) {
            return ['success' => false, 'message' => 'Рунный слот пуст.'];
        }

        $runeName = $itemRune->runeInfo->name;
        $itemRune->delete();

        return ['success' => true, 'message' => sprintf('Руна «%s» удалена. Руна уничтожена.', $runeName)];
    }

    /**
     * Переброс статов руны. Заблокированные статы (locked=true) не изменяются.
     * Стоимость: base_cost * (reroll_count+1)^1.5 + locked_count * base_cost * 0.5
     */
    public function reroll(User $user, Item $item, int $slotIndex, array $lockedIndices = []): array
    {
        $itemRune = ItemRune::where('item_id', $item->id)
            ->where('slot_index', $slotIndex)
            ->first();

        if (! $itemRune instanceof ItemRune) {
            return ['success' => false, 'message' => 'Рунный слот пуст.'];
        }

        /** @var RuneRarity $rarity */
        $rarity   = $itemRune->runeInfo->rune_rarity;
        $baseCost = $rarity->rerollBaseCost();
        $cost     = (int) round($baseCost * pow($itemRune->reroll_count + 1, 1.5));
        $cost    += count($lockedIndices) * (int) round($baseCost * 0.5);

        if ($user->money < $cost) {
            return ['success' => false, 'message' => sprintf('Недостаточно золота. Нужно: %d', $cost)];
        }

        $oldStats  = $itemRune->stats;
        $newStats  = $this->rollStats($itemRune->runeInfo, false);

        // Preserve locked stats
        $merged = [];
        foreach ($newStats as $i => $newStat) {
            if (isset($lockedIndices[$i]) && isset($oldStats[$i])) {
                $merged[] = array_merge($oldStats[$i], ['locked' => true]);
            } else {
                $merged[] = array_merge($newStat, ['locked' => false]);
            }
        }

        // Keep lock flags on any extra old stats beyond new count
        foreach ($oldStats as $i => $oldStat) {
            if ($i >= count($newStats) && in_array($i, $lockedIndices, true) && isset($merged[$i])) {
                $merged[$i] = array_merge($oldStat, ['locked' => true]);
            }
        }

        $user->money -= $cost;
        $user->save();

        $itemRune->stats         = $merged;
        $itemRune->reroll_count += 1;
        $itemRune->save();

        return [
            'success' => true,
            'message' => sprintf('Статы руны перебросаны. Потрачено: %d золота.', $cost),
            'cost'    => $cost,
        ];
    }

    /**
     * Открыть новый рунный слот (потребляет RUNE_KEY из рюкзака).
     */
    public function openSlot(Item $item, Backpack $keySlot): array
    {
        if ($item->rune_slot_count >= self::MAX_RUNE_SLOTS) {
            return ['success' => false, 'message' => 'Достигнуто максимальное количество рунных слотов.'];
        }

        if ($keySlot->item->itemInfo->type !== ShareItemType::RUNE_KEY) {
            return ['success' => false, 'message' => 'Выбранный предмет не является рунным ключом.'];
        }

        $this->consumeFromBackpack($keySlot);

        $item->rune_slot_count += 1;
        $item->save();

        return [
            'success' => true,
            'message' => sprintf('Рунный слот открыт. Теперь у предмета %d слот(а/ов).', $item->rune_slot_count),
        ];
    }

    /**
     * Стоимость следующего переброса для клиента.
     */
    public function nextRerollCost(ItemRune $itemRune, int $lockedCount = 0): int
    {
        $rarity   = $itemRune->runeInfo->rune_rarity;
        $baseCost = $rarity->rerollBaseCost();
        $cost     = (int) round($baseCost * pow($itemRune->reroll_count + 1, 1.5));
        $cost    += $lockedCount * (int) round($baseCost * 0.5);

        return $cost;
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function rollStats(\App\Models\Share\ShareItem $runeInfo, bool $riskMode): array
    {
        /** @var RuneRarity $rarity */
        $rarity = $runeInfo->rune_rarity;
        $mult   = $rarity->multiplier();

        // Risk mode: roll in upper 75-100% of range instead of 0-100%
        $rangeLow = $riskMode ? 0.75 : 0.0;

        // Пул доступных статов (тематика руны или все)
        $pool = $runeInfo->rune_stat_pool ?? array_keys(self::STAT_POOL);

        [$min, $max] = $rarity->statCount();
        $count       = random_int($min, $max);
        $count       = min($count, count($pool));

        // Shuffle pool, pick N without duplicates
        $keys     = $pool;
        shuffle($keys);
        $selected = array_slice($keys, 0, $count);

        $stats = [];
        foreach ($selected as $statType) {
            $def      = self::STAT_POOL[$statType] ?? ['min' => 1, 'max' => 5];
            $rawMin   = (int) round($def['min'] * $mult);
            $rawMax   = (int) round($def['max'] * $mult);
            $effMin   = (int) round($rawMin + ($rawMax - $rawMin) * $rangeLow);
            $value    = random_int(max(1, $effMin), max(1, $rawMax));

            $stats[] = [
                'type'       => $statType,
                'value'      => $value,
                'is_percent' => false,
                'locked'     => false,
            ];
        }

        return $stats;
    }

    private function rollPassive(RuneRarity $rarity): ?array
    {
        if ($rarity->passiveChance() === 0) {
            return null;
        }

        if (random_int(1, 100) > $rarity->passiveChance()) {
            return null;
        }

        $passives = RunePassiveType::cases();
        $passive  = $passives[array_rand($passives)];
        [$min, $max] = $passive->valueRange();
        $value = random_int($min, $max);

        return [
            'type'        => $passive->value,
            'value'       => $value,
            'label'       => $passive->label(),
            'description' => $passive->description($value),
        ];
    }

    private function consumeFromBackpack(Backpack $slot): void
    {
        if ($slot->count > 1) {
            $slot->count -= 1;
            $slot->save();
        } else {
            $slot->item->delete();
            $slot->delete();
        }
    }
}