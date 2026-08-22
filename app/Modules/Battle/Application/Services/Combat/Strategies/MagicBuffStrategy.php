<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class MagicBuffStrategy implements AttackStrategyInterface
{
    public function __construct(
        private Player $playerModel,
        private MagicSkill $magicSkill,
    ) {}

    public function getHits(): array
    {
        if ($this->playerModel->mp_now < $this->magicSkill->mana_cost) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage(sprintf('Недостаточно маны, требуется %s', $this->magicSkill->mana_cost)),
            ];
        }

        $this->playerModel->mp_now -= $this->magicSkill->mana_cost;

        $hit = (new FightHitDTO)
            ->setDamage(0)
            ->setWeapon(null)
            ->setWeaponName(sprintf('заклинанием «%s»', $this->magicSkill->name))
            ->setMagicSkill($this->magicSkill);

        // Healing
        if ($this->magicSkill->base_healing > 0) {
            $heal = $this->magicSkill->base_healing;
            $this->playerModel->hp_now = min(
                $this->playerModel->hp_max,
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
