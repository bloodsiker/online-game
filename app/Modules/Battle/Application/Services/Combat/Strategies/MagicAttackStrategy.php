<?php

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Domain\Services\PlayerStatFormulas;
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

        // Бонус от интеллекта — тот же принцип, что у силы для оружия (см. PlayerStatFormulas::strengthDamagePercent)
        $intBonusPct = PlayerStatFormulas::intelligenceDamagePercent(
            (float) $this->player->getIntelligence(),
            $this->player->getLevel(),
        );
        $totalDamage = (int) round($baseDamage * (1 + $intBonusPct / 100));

        // Рассчитываем хит с итоговым уроном
        $hit = $this->hitCalc->hit($this->player, $this->monster, $totalDamage, $totalDamage);

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
