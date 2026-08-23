<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Services\MagicCastGuard;

class MagicBuffStrategy implements AttackStrategyInterface
{
    public function __construct(
        private MagicCastGuard $castGuard,
        private MagicHitCalculator $magicHitCalc,
        private StatSheet $casterSheet, // рассчитанные статы кастера: интеллект для хила + эффективный hp_max
        private Player $playerModel,    // Player model для чтения/записи hp_now/mp_now
        private MagicSkill $magicSkill,
    ) {}

    public function getHits(): array
    {
        // Раньше здесь была самодельная проверка маны с чисто in-memory `-=` и
        // вообще без кулдауна: боевые баффы/хилы кастовались каждый раунд
        // бесплатно по кулдауну, а ману могли списать дважды параллельные
        // запросы. Теперь оба боевых пути каста идут через один и тот же
        // MagicCastGuard (транзакция + lockForUpdate), как MagicAttackStrategy.
        $castAttempt = $this->castGuard->tryConsume($this->playerModel, $this->magicSkill);

        if (! $castAttempt->ok) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage($castAttempt->reason),
            ];
        }

        $hit = (new FightHitDTO)
            ->setDamage(0)
            ->setWeapon(null)
            ->setWeaponName(sprintf('заклинанием «%s»', $this->magicSkill->name))
            ->setMagicSkill($this->magicSkill);

        // Healing — та же формула, что и у внебоевого каста (UseMagicSkill):
        // лечение масштабируется интеллектом через MagicHitCalculator::heal(),
        // иначе одно и то же заклинание лечило по-разному в бою и вне боя.
        if ($this->magicSkill->base_healing > 0) {
            $heal = $this->magicHitCalc->heal(
                $this->casterSheet,
                minHeal: $this->magicSkill->base_healing,
                maxHeal: $this->magicSkill->base_healing,
                powerCoefficient: $this->magicSkill->power_coefficient,
            );
            $this->playerModel->hp_now = min(
                $this->casterSheet->getHpMax(),
                $this->playerModel->hp_now + $heal
            );
            $hit->setMessage(sprintf(
                'Заклинание «%s» восстановило <b class="color-green">%d HP</b>',
                $this->magicSkill->name,
                $heal
            ));
        }

        // Effects on self (chance from pivot)
        foreach ($this->magicSkill->skillEffects as $effect) {
            if (random_int(1, 100) <= $effect->pivot->chance) {
                $hit->addSelfAppliedEffect($effect);
            }
        }

        return [$hit];
    }
}
