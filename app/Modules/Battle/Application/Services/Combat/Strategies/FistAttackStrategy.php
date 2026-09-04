<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\Services\Combat\HitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Support\Facades\Cache;

class FistAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private HitCalculator $hitCalc,
        private StatSheet $player,
        private FightHitInterface $monster
    ) {}

    public function getHits(): array
    {
        // Довідковий рядок "кулак": 1 запит на весь час життя кешу замість 1 на удар.
        $skill = Cache::rememberForever(
            'skill:hand:'.Skill::SKILL_HAND_ID,
            static fn () => Skill::find(Skill::SKILL_HAND_ID)
        );
        $hit = $this->hitCalc->hit(
            $this->player,
            $this->monster,
            $this->player->getLeftHandMinDmg(),
            $this->player->getLeftHandMaxDmg()
        );

        return [$hit->setWeaponName('кулаком')->setSkill($skill)];
    }
}
