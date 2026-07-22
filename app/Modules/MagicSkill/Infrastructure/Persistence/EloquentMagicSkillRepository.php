<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Infrastructure\Persistence;

use App\Modules\MagicSkill\Domain\Contracts\MagicSkillReadRepository;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class EloquentMagicSkillRepository implements MagicSkillReadRepository, MagicSkillWriteRepository
{
    public function __construct(
        private readonly PlayerRunePassiveService $runePassiveService,
    ) {}

    public function getPlayerPageData(Player $player): array
    {
        $player->loadMissing([
            'user',
            'magicSkills.skillEffects',
            'playerEquip',
        ]);

        [$passiveSkills, $activeSkills] = $player->magicSkills->partition(
            static fn (MagicSkill $skill) => $skill->is_passive
        );

        $allyTargets = Player::query()
            ->whereHas('user', fn ($query) => $query
                ->where('location_id', $player->user->location_id)
                ->where('id', '!=', $player->user_id)
                ->where('last_online_at', '>=', now()->subMinutes(10))
            )
            ->with('user:id,name')
            ->get(['id', 'user_id']);

        return [
            'passiveSkills' => $passiveSkills->values(),
            'activeSkills' => $activeSkills->values(),
            'allyTargets' => $allyTargets,
            'runePassives' => $this->collectRunePassives($player),
        ];
    }

    /**
     * Пассивки вплавленных рун — показываем игроку рядом с пассивными
     * навыками, чтобы было видно, что реально влияет на бой. Данные те же,
     * что читает боевая логика (PlayerRunePassiveService), просто размечены
     * для отображения (label/description вместо type/value).
     */
    private function collectRunePassives(Player $player): Collection
    {
        return collect($this->runePassiveService->resolve($player))
            ->map(fn (array $p) => [
                'itemName' => $p['itemName'],
                'runeName' => $p['runeName'],
                'label' => $p['type']->label(),
                'description' => $p['type']->description($p['value']),
            ]);
    }

    public function findOwnedSkill(Player $player, int $skillId): ?MagicSkill
    {
        return $player->magicSkills()
            ->where('magic_skill_id', $skillId)
            ->with(['skillEffects', 'players'])
            ->first();
    }

    public function findAllyTarget(Player $player, ?int $targetPlayerId): ?Player
    {
        if (! $targetPlayerId) {
            $player->loadMissing('user');

            return $player;
        }

        return Player::query()
            ->whereKey($targetPlayerId)
            ->whereHas('user', fn ($query) => $query
                ->where('location_id', $player->user->location_id)
                ->where('last_online_at', '>=', now()->subMinutes(10))
            )
            ->with('user')
            ->first();
    }

    public function syncEquippedSkills(Player $player, array $equippedIds): void
    {
        $player->magicSkills()->update(['is_equipped' => false]);

        if ($equippedIds !== []) {
            $player->magicSkills()
                ->whereIn('magic_skill_id', $equippedIds)
                ->update(['is_equipped' => true]);
        }
    }

    public function updateSortOrder(Player $player, array $skillIds): void
    {
        foreach ($skillIds as $index => $skillId) {
            $player->magicSkills()
                ->wherePivot('magic_skill_id', (int) $skillId)
                ->updateExistingPivot((int) $skillId, ['sort_order' => $index]);
        }
    }

    public function consumeMana(Player $player, int $manaCost): void
    {
        $player->mp_now -= $manaCost;
    }

    public function savePlayers(Player $caster, Player $target): void
    {
        $caster->save();

        if ($target->id !== $caster->id) {
            $target->save();
        }
    }

    public function updateCooldown(Player $player, MagicSkill $skill, ?CarbonInterface $cooldownEndsAt): void
    {
        $player->magicSkills()->updateExistingPivot($skill->id, [
            'cooldown_end_at' => $cooldownEndsAt,
        ]);
    }
}
