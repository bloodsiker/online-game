<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Blacksmith\Domain\Results\UpgradeResult;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class UpgradeService
{
    private const BASE_COST = 100;

    private const GUARANTEED_SUCCESS_STREAK = 10;

    /** С этого текущего уровня «жалость» (бонус за провалы и гарантия) больше не работает. */
    private const PITY_MAX_LEVEL = 15;

    /** success% at each level (index = target level) */
    private const SUCCESS_CHANCES = [
        0 => 100, 1 => 100, 2 => 100, 3 => 100, 4 => 100, 5 => 100,
        6 => 80,  7 => 75,  8 => 70,  9 => 60,  10 => 50,
        11 => 40,  12 => 30,  13 => 25,  14 => 20,  15 => 15,
        16 => 12,  17 => 10,  18 => 9,   19 => 7,   20 => 6,
        21 => 5,   22 => 4,   23 => 3,   24 => 3,   25 => 2,
    ];

    /** destroy% at each level on failure (index = current level before attempt) */
    private const DESTROY_CHANCES = [
        12 => 10, 13 => 20, 14 => 30,
        15 => 30, 16 => 32, 17 => 34, 18 => 36, 19 => 38,
        20 => 40, 21 => 42, 22 => 45, 23 => 48, 24 => 50,
    ];

    public const MAX_LEVEL = 25;

    public function getSuccessChance(int $currentLevel, int $pity, bool $isLucky): float
    {
        if ($currentLevel >= self::MAX_LEVEL) {
            return 0.0;
        }

        $targetLevel = $currentLevel + 1;
        $base = self::SUCCESS_CHANCES[$targetLevel] ?? 2;
        $pityBonus = $currentLevel < self::PITY_MAX_LEVEL ? $pity * 2 : 0;
        $total = $base + $pityBonus + ($isLucky ? 15 : 0);

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

    public function upgrade(User $user, Backpack $itemSlot, Backpack $baseScrollSlot, ?Backpack $bonusScrollSlot): UpgradeResult
    {
        $item = $itemSlot->item;
        $currentLevel = $item->upgrade_lvl;

        if ($currentLevel >= self::MAX_LEVEL) {
            return UpgradeResult::failed('Предмет уже достиг максимального уровня заточки.', $currentLevel);
        }

        $bonusType = $bonusScrollSlot
            ? ($bonusScrollSlot->item->itemInfo->upgrade_scroll_type ?? null)
            : null;

        $isLucky = $bonusType === UpgradeScrollType::LUCKY;
        $isProtection = $bonusType === UpgradeScrollType::PROTECTION;
        $isStabilizer = $bonusType === UpgradeScrollType::STABILIZER;

        $cost = $this->getGoldCost($currentLevel);

        if ($user->money < $cost) {
            return UpgradeResult::failed("Недостаточно монет. Необходимо: {$cost}.", $currentLevel);
        }

        $user->money -= $cost;
        $user->save();

        $this->consumeScroll($baseScrollSlot);

        if ($bonusScrollSlot) {
            $this->consumeScroll($bonusScrollSlot);
        }

        $guaranteed = $currentLevel < self::PITY_MAX_LEVEL
            && $item->upgrade_fail_streak >= self::GUARANTEED_SUCCESS_STREAK;
        $successChance = $this->getSuccessChance($currentLevel, $item->upgrade_pity, $isLucky);
        $roll = mt_rand(1, 100);
        $success = $guaranteed || $roll <= $successChance;

        if ($success) {
            $item->upgrade_lvl = $currentLevel + 1;
            $item->upgrade_pity = 0;
            $item->upgrade_fail_streak = 0;
            $item->save();

            return UpgradeResult::succeeded(
                sprintf('Заточка успешна! Предмет «%s» улучшен до +%d.', $item->itemInfo->name, $item->upgrade_lvl),
                $item->upgrade_lvl,
            );
        }

        $item->upgrade_pity++;
        $item->upgrade_fail_streak++;

        $destroyChance = $this->getDestroyChance($currentLevel);
        if (! $isProtection && $destroyChance > 0 && mt_rand(1, 100) <= $destroyChance) {
            $item->save();
            $itemSlot->delete();
            $item->delete();

            return UpgradeResult::failed(
                sprintf('Заточка провалилась. Предмет «%s +%d» уничтожен!', $item->itemInfo->name, $currentLevel),
                0,
                true,
            );
        }

        if (! $isStabilizer && $currentLevel >= 6) {
            $item->upgrade_lvl = max(0, $currentLevel - 1);
        }

        $item->save();

        return UpgradeResult::failed(
            sprintf(
                'Заточка провалилась. Уровень: +%d.%s',
                $item->upgrade_lvl,
                $isStabilizer && $currentLevel >= 6 ? ' (Стабилизатор предотвратил понижение)' : ''
            ),
            $item->upgrade_lvl,
        );
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
