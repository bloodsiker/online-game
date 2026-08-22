<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class MagicAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private FightHitInterface $player,     // StatSheet с полными рассчитанными статами
        private Player $playerModel, // Player model для чтения/записи mp_now
        private Monster $monster,
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

        if ($this->playerModel->mp_now < $this->magicSkill->mana_cost) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage(sprintf('Недостаточно маны, требуется %s', $this->magicSkill->mana_cost)),
            ];
        }

        $this->playerModel->mp_now -= $this->magicSkill->mana_cost;

        // Базовый урон от скилла (уворот бросается один раз — внутри hit() ниже)
        $baseDamage = random_int($this->magicSkill->min_damage, $this->magicSkill->max_damage);

        // TODO(Task 4): заменить на MagicHitCalculator — без уворота/крита/брони, см. спеку.
        $hit = $this->hitCalc->hit($this->player, $this->monster, $baseDamage, $baseDamage);

        if ($hit->isDodge()) {
            return [
                $hit
                    ->setMagicSkill($this->magicSkill)
                    ->setWeaponName($this->magicSkill->name)
                    ->setWeapon(null),
            ];
        }

        foreach ($this->magicSkill->skillEffects as $effectData) {
            if (random_int(1, 100) <= $effectData->pivot->chance) {
                $hit->addAppliedEffect($effectData);
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
