<?php

declare(strict_types=1);

namespace App\Modules\Player\Infrastructure\Persistence;

use App\Models\Experience;
use App\Modules\Player\Application\DTOs\InitialExperienceDTO;
use App\Modules\Player\Domain\Repositories\PlayerRepositoryInterface;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class EloquentPlayerRepository implements PlayerRepositoryInterface
{
    public function save(Player $player): void
    {
        $player->save();
    }

    public function register(User $user, Player $player): Player
    {
        return DB::transaction(function () use ($user, $player): Player {
            $player->save();

            (new PlayerEquipment)->forceFill([
                'player_id' => $player->id,
            ])->save();

            $user->forceFill([
                'player_id' => $player->id,
            ])->save();

            return $player->fresh(['race', 'playerEquip']);
        });
    }

    public function getInitialExperienceForLevel(int $level): InitialExperienceDTO
    {
        $experience = Experience::query()
            ->where('lvl', $level)
            ->firstOrFail();

        return new InitialExperienceDTO(
            expUp: (int) $experience->exp + (int) $experience->exp_diff,
            expDiff: (int) $experience->exp_diff,
        );
    }
}
