<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Models\Experience;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlayerLevelUpService
{
    public function maxLevel(): int
    {
        return (int) Experience::query()->max('lvl');
    }

    public function raiseToLevel(Player $player, int $targetLevel, ?Closure $afterEachLevel = null): Player
    {
        $this->validateTargetLevel($player, $targetLevel);

        while ($this->raiseOneLevel($player->id, $targetLevel)) {
            // Каждый уровень обрабатывается отдельно, чтобы PlayerLeveledUp
            // начислил расовые характеристики и свободные очки по очереди.
            $afterEachLevel?->__invoke();
        }

        return Player::query()->findOrFail($player->id);
    }

    private function raiseOneLevel(int $playerId, int $targetLevel): bool
    {
        return DB::transaction(function () use ($playerId, $targetLevel): bool {
            $player = Player::query()
                ->with('race')
                ->lockForUpdate()
                ->findOrFail($playerId);

            if ($player->lvl >= $targetLevel) {
                return false;
            }

            $newLevel = $player->lvl + 1;
            $levelConfig = Experience::query()->where('lvl', $newLevel)->first();

            if ($levelConfig === null) {
                throw ValidationException::withMessages([
                    'target_level' => "Для уровня {$newLevel} отсутствует настройка опыта.",
                ]);
            }

            $player->lvl = $newLevel;
            $player->exp = (int) $levelConfig->exp;
            $player->save();

            event(new PlayerLeveledUp($player));

            return true;
        });
    }

    private function validateTargetLevel(Player $player, int $targetLevel): void
    {
        if ($targetLevel <= $player->lvl) {
            throw ValidationException::withMessages([
                'target_level' => 'Целевой уровень должен быть выше текущего.',
            ]);
        }

        $maxLevel = $this->maxLevel();
        if ($targetLevel > $maxLevel) {
            throw ValidationException::withMessages([
                'target_level' => "Максимальный доступный уровень: {$maxLevel}.",
            ]);
        }
    }
}
