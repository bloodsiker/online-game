<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Models\Player\Player;
use App\Modules\Player\Application\DTOs\CharacterDTO;
use App\Modules\Player\Application\DTOs\PlayerSkillDTO;
use App\Modules\Player\Domain\Services\PlayerStatService;

class GetCharacter
{
    public function __construct(
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(Player $player): CharacterDTO
    {
        $stats = $this->statService->resolve($player);

        $skills = $player->skills
            ->map(fn ($s) => new PlayerSkillDTO(
                name: $s->skill->name,
                level: $s->lvl,
                exp: $s->exp,
                expUp: $s->exp_up,
            ))
            ->all();

        return new CharacterDTO(
            playerName: $player->user->name,
            level: $player->lvl,
            raceName: $player->race->name,
            hpNow: $player->hp_now,
            mpNow: $player->mp_now,
            money: $player->user->money,
            exp: $player->exp,
            expUp: $player->exp_up,
            expPercent: $player->getPercentExp(),
            victory: $player->victory,
            death: $player->death,
            freeStats: $player->free_stats,
            baseStrength: (int) $player->getStrength(),
            baseIntuition: (int) $player->getInt(),
            baseAgility: (int) $player->getAgility(),
            baseIntelligence: (int) $player->getIntelligence(),
            baseWisdom: (int) $player->getMud(),
            stats: $stats,
            skills: $skills,
        );
    }
}
