<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Item\Domain\Enums\ArrowConsumeStatus;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ShareItemStatType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;

/**
 * Выстрел из лука: один удар — одна стрела из колчана (уже списана резолвером).
 * Без стрел ход пропускается; ремесленные стрелы добавляют плоский урон
 * (статы attack_min / attack_max стрелы).
 */
class BowAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private FightHitInterface $player,
        private FightHitInterface $monster,
        private Item $bow,
        private bool $bowInLeftHand,
        private ArrowConsumeStatus $arrowStatus,
        private ?ShareItem $arrow,
        private int $weaponSkillLevel = 0,
    ) {}

    public function getHits(): array
    {
        if ($this->arrowStatus !== ArrowConsumeStatus::CONSUMED) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage($this->arrowStatus === ArrowConsumeStatus::NO_QUIVER
                        ? __('Для стрельбы из лука наденьте колчан со стрелами. Ход пропущен.')
                        : __('Стрелы закончились! Пополните колчан в инвентаре. Ход пропущен.')),
            ];
        }

        [$bonusMin, $bonusMax] = $this->arrowBonus();

        $min = ($this->bowInLeftHand ? $this->player->getLeftHandMinDmg() : $this->player->getRightHandMinDmg()) + $bonusMin;
        $max = ($this->bowInLeftHand ? $this->player->getLeftHandMaxDmg() : $this->player->getRightHandMaxDmg()) + $bonusMax;

        $hit = $this->hitCalc->hit($this->player, $this->monster, $min, max($min, $max), $this->weaponSkillLevel);

        return [
            $hit
                ->setWeaponName($this->bow->itemInfo->name)
                ->setSkill($this->bow->itemInfo->skill)
                ->setWeapon($this->bow->itemInfo)
                ->setAmmo($this->arrow)
                ->setHandSide($this->bowInLeftHand ? 'left' : 'right'),
        ];
    }

    /** @return array{0: int, 1: int} */
    private function arrowBonus(): array
    {
        $min = 0;
        $max = 0;

        foreach ($this->arrow?->stats ?? [] as $stat) {
            if ($stat->stat_type === ShareItemStatType::ATTACK_MIN) {
                $min += (int) $stat->value;
            } elseif ($stat->stat_type === ShareItemStatType::ATTACK_MAX) {
                $max += (int) $stat->value;
            }
        }

        return [$min, $max];
    }
}
