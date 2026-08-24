<?php

declare(strict_types=1);

namespace App\Services;

use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerSkill;

class MagicSkillRequirementService
{
    /**
     * Проверяет, выполняет ли игрок все требования заклинания (статы/навык).
     * Возвращает null при успехе или строку с описанием первого неудовлетворённого требования.
     */
    public function check(Player $player, MagicSkill $skill): ?string
    {
        $skill->loadMissing('requirements.skill');

        $requiredSkillIds = $skill->requirements
            ->where('type', MagicSkillRequirementType::SKILL)
            ->pluck('skill_id')
            ->filter()
            ->unique()
            ->values();

        $skillLevels = $requiredSkillIds->isEmpty()
            ? collect()
            : PlayerSkill::where('player_id', $player->id)
                ->whereIn('skill_id', $requiredSkillIds)
                ->pluck('lvl', 'skill_id');

        foreach ($skill->requirements as $req) {
            $met = match ($req->type) {
                // Старые записи будут переведены миграцией в требование навыка.
                // До её запуска они не должны ни блокировать изучение, ни вызывать 500.
                MagicSkillRequirementType::LEVEL => true,
                MagicSkillRequirementType::STAT => $this->checkStat($player, (string) $req->stat_key, $req->min_value),
                MagicSkillRequirementType::SKILL => (int) $skillLevels->get($req->skill_id, 0) >= $req->min_value,
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
