<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\MagicSkill\Application\Services\MagicCastGuard;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class MagicAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private MagicHitCalculator $magicHitCalc,
        private MagicCastGuard $castGuard,
        private FightHitInterface $player,     // StatSheet с полными рассчитанными статами
        private Player $playerModel, // Player model для чтения/записи mp_now
        private FightHitInterface $monster,
        private MagicSkill $magicSkill,
    ) {}

    public function getHits(): array
    {
        if (! $this->magicSkill instanceof MagicSkill) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage('Заклинание не изучено или отключено'),
            ];
        }

        if (! $this->magicSkill->isAttackSkill()) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage('Это не атакующее заклинание'),
            ];
        }

        $castAttempt = $this->castGuard->tryConsume($this->playerModel, $this->magicSkill);

        if (! $castAttempt->ok) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage($castAttempt->reason),
            ];
        }

        // Магия не уворачивается и не критует (см. спеку, правило 1) — сразу считаем финальный урон.
        $hit = $this->magicHitCalc->hit(
            $this->player,
            $this->monster,
            $this->magicSkill->min_damage,
            $this->magicSkill->max_damage,
            $this->magicSkill->power_coefficient,
        );

        foreach ($this->magicSkill->skillEffects as $effectData) {
            if (random_int(1, 100) <= $effectData->pivot->chance) {
                $hit->addAppliedEffect($effectData, tickValue: $hit->getDamage());
            }
        }

        return [
            $hit
                ->setMagicSkill($this->magicSkill)
                ->setWeaponName(sprintf('заклинанием «%s»', $this->magicSkill->name))
                ->setWeapon(null)
                ->setSkill($this->magicSkill->skill),
        ];
    }
}
