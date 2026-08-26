<?php

declare(strict_types=1);

namespace App\Modules\Player\Application\UseCases;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Application\DTOs\CharacterDTO;
use App\Modules\Player\Application\DTOs\PlayerSkillDTO;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemType;

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
                expDiff: $s->exp_diff,
                type: (string) $s->skill->type,
            ))
            ->all();

        return new CharacterDTO(
            playerName: $player->user->name,
            level: $player->lvl,
            raceName: $player->race->name,
            hpNow: $player->hp_now,
            mpNow: $player->mp_now,
            money: $player->user->money,
            diamond: (int) $player->user->diamond,
            bankBalance: (int) $player->user->bank_balance,
            bankAccount: $player->user->bank_account !== null ? (string) $player->user->bank_account : null,
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
            baseEndurance: (int) $player->getEndurance(),
            stats: $stats,
            skills: $skills,
            weaponDamage: $this->weaponDamageRange($player, $stats),
        );
    }

    /**
     * Урон оружия за раунд — та же логика выбора руки(рук), что и в
     * Battle AttackStrategyResolver: одно оружие (включая дворучное) бьёт
     * только своей рукой, два оружия дают сумму ударов, без оружия — кулаки.
     *
     * @return array{min: int, max: int}
     */
    private function weaponDamageRange(Player $player, StatSheet $stats): array
    {
        $equip = $player->playerEquip;
        $left = $equip?->handLeft;
        $right = $equip?->handRight;

        $isWeapon = fn (?Item $item): bool => $item !== null
            && $item->itemInfo?->type === ShareItemType::WEAPON;

        $leftWeapon = $isWeapon($left);
        $rightWeapon = $isWeapon($right);

        return match (true) {
            // Два оружия — удар каждой рукой
            $leftWeapon && $rightWeapon => [
                'min' => $stats->getLeftHandMinDmg() + $stats->getRightHandMinDmg(),
                'max' => $stats->getLeftHandMaxDmg() + $stats->getRightHandMaxDmg(),
            ],
            // Оружие только в правой
            ! $leftWeapon && $rightWeapon => [
                'min' => $stats->getRightHandMinDmg(),
                'max' => $stats->getRightHandMaxDmg(),
            ],
            // Оружие в левой (включая дворучное) или щит справа — бьёт левая
            default => [
                'min' => $stats->getLeftHandMinDmg(),
                'max' => $stats->getLeftHandMaxDmg(),
            ],
        };
    }
}
