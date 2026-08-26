<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;
use App\Modules\Share\Domain\Enums\ShareItemRequirementType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;

class ItemRequirementService
{
    /**
     * Проверяет, выполняет ли игрок все требования предмета.
     * Возвращает null при успехе или строку с описанием первого неудовлетворённого требования.
     */
    public function check(Player $player, ShareItem $item): ?string
    {
        $item->loadMissing('requirements.skill');

        $requiredSkillIds = $item->requirements
            ->where('type', ShareItemRequirementType::SKILL)
            ->pluck('skill_id')
            ->filter()
            ->unique()
            ->values();

        $skillLevels = $requiredSkillIds->isEmpty()
            ? collect()
            : PlayerSkill::where('player_id', $player->id)
                ->whereIn('skill_id', $requiredSkillIds)
                ->pluck('lvl', 'skill_id');

        foreach ($item->requirements as $req) {
            $met = match ($req->type) {
                ShareItemRequirementType::LEVEL => $player->lvl >= $req->min_value,
                ShareItemRequirementType::STAT => $this->checkStat($player, (string) $req->stat_key, $req->min_value),
                ShareItemRequirementType::SKILL => (int) $skillLevels->get($req->skill_id, 0) >= $req->min_value,
            };

            if (! $met) {
                return 'Требование не выполнено: '.$req->label().' ≥ '.$req->min_value;
            }
        }

        return null;
    }

    private function checkStat(Player $player, string $key, int $minValue): bool
    {
        $stat = PlayerStatKey::tryFrom($key);

        $value = match ($stat) {
            PlayerStatKey::STRENGTH => (int) floor($player->strength),
            PlayerStatKey::AGILITY => (int) floor($player->agility),
            PlayerStatKey::INTUITION => (int) floor($player->intuition),
            PlayerStatKey::WISDOM => (int) floor($player->wisdom),
            PlayerStatKey::INTELLIGENCE => (int) floor($player->intelligence),
            PlayerStatKey::ENDURANCE => (int) floor($player->endurance),
            null => 0,
        };

        return $value >= $minValue;
    }
}
