<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UpgradeScrollType;
use App\Models\Backpack;
use App\Models\User;

class UpgradeService
{
    private const BASE_COST = 100;

    private const GUARANTEED_SUCCESS_STREAK = 10;

    /** success% at each level (index = target level) */
    private const SUCCESS_CHANCES = [
        0 => 100, 1 => 100, 2 => 100, 3 => 100, 4 => 100, 5 => 100,
        6 => 80,  7 => 75,  8 => 70,  9 => 60,  10 => 50,
        11 => 40,  12 => 30,  13 => 25,  14 => 20,  15 => 15,
    ];

    /** destroy% at each level on failure (index = current level before attempt) */
    private const DESTROY_CHANCES = [
        12 => 10, 13 => 20, 14 => 30,
    ];

    private const MAX_LEVEL = 15;

    public function getSuccessChance(int $currentLevel, int $pity, bool $isLucky): float
    {
        if ($currentLevel >= self::MAX_LEVEL) {
            return 0.0;
        }

        $targetLevel = $currentLevel + 1;
        $base = self::SUCCESS_CHANCES[$targetLevel] ?? 15;
        $total = $base + ($pity * 2) + ($isLucky ? 15 : 0);

        return min(100.0, (float) $total);
    }

    public function getDestroyChance(int $currentLevel): int
    {
        return self::DESTROY_CHANCES[$currentLevel] ?? 0;
    }

    public function getGoldCost(int $currentLevel): int
    {
        return (int) (self::BASE_COST * ($currentLevel + 1) ** 2);
    }

    /**
     * @return array{success: bool, destroyed: bool, message: string, new_level: int}
     */
    public function upgrade(User $user, Backpack $itemSlot, Backpack $baseScrollSlot, ?Backpack $bonusScrollSlot): array
    {
        $item = $itemSlot->item;
        $currentLevel = $item->upgrade_lvl;

        if ($currentLevel >= self::MAX_LEVEL) {
            return ['success' => false, 'destroyed' => false, 'message' => 'Предмет уже достиг максимального уровня заточки.', 'new_level' => $currentLevel];
        }

        $bonusType = $bonusScrollSlot
            ? ($bonusScrollSlot->item->itemInfo->upgrade_scroll_type ?? null)
            : null;

        $isLucky = $bonusType === UpgradeScrollType::LUCKY;
        $isProtection = $bonusType === UpgradeScrollType::PROTECTION;
        $isStabilizer = $bonusType === UpgradeScrollType::STABILIZER;

        $cost = $this->getGoldCost($currentLevel);

        if ($user->money < $cost) {
            return ['success' => false, 'destroyed' => false, 'message' => "Недостаточно монет. Необходимо: {$cost}.", 'new_level' => $currentLevel];
        }

        $user->money -= $cost;
        $user->save();

        // Consume base scroll (always)
        $this->consumeScroll($baseScrollSlot);

        // Consume bonus scroll if provided
        if ($bonusScrollSlot) {
            $this->consumeScroll($bonusScrollSlot);
        }

        // Guaranteed success after streak
        $guaranteed = $item->upgrade_fail_streak >= self::GUARANTEED_SUCCESS_STREAK;
        $successChance = $this->getSuccessChance($currentLevel, $item->upgrade_pity, $isLucky);
        $roll = mt_rand(1, 100);
        $success = $guaranteed || $roll <= $successChance;

        if ($success) {
            $item->upgrade_lvl = $currentLevel + 1;
            $item->upgrade_pity = 0;
            $item->upgrade_fail_streak = 0;
            $item->save();

            return [
                'success' => true,
                'destroyed' => false,
                'message' => sprintf('Заточка успешна! Предмет «%s» улучшен до +%d.', $item->itemInfo->name, $item->upgrade_lvl),
                'new_level' => $item->upgrade_lvl,
            ];
        }

        // Failure path
        $item->upgrade_pity++;
        $item->upgrade_fail_streak++;

        // Check destruction (only lvl 12+)
        $destroyChance = $this->getDestroyChance($currentLevel);
        if (! $isProtection && $destroyChance > 0 && mt_rand(1, 100) <= $destroyChance) {
            $item->save();
            $itemSlot->delete();
            $item->delete();

            return [
                'success' => false,
                'destroyed' => true,
                'message' => sprintf('Заточка провалилась. Предмет «%s +%d» уничтожен!', $item->itemInfo->name, $currentLevel),
                'new_level' => 0,
            ];
        }

        // Downgrade (lvl 6+ loses 1 level unless stabilizer)
        if (! $isStabilizer && $currentLevel >= 6) {
            $item->upgrade_lvl = max(0, $currentLevel - 1);
        }

        $item->save();

        return [
            'success' => false,
            'destroyed' => false,
            'message' => sprintf(
                'Заточка провалилась. Уровень: +%d.%s',
                $item->upgrade_lvl,
                ($isStabilizer && $currentLevel >= 6 ? ' (Стабилизатор предотвратил понижение)' : '')
            ),
            'new_level' => $item->upgrade_lvl,
        ];
    }

    private function consumeScroll(Backpack $scrollSlot): void
    {
        if ($scrollSlot->count > 1) {
            $scrollSlot->count--;
            $scrollSlot->save();
        } else {
            $scrollSlot->item->delete();
            $scrollSlot->delete();
        }
    }
}
