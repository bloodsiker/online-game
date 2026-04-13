<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PlayerStatKey;
use App\Enums\ShareItemRequirementType;
use App\Models\Player\Player;
use App\Models\Player\PlayerSkill;
use App\Models\Share\ShareItem;

class ItemRequirementService
{
    /**
     * Проверяет, выполняет ли игрок все требования предмета.
     * Возвращает null при успехе или строку с описанием первого неудовлетворённого требования.
     */
    public function check(Player $player, ShareItem $item): ?string
    {
        $item->loadMissing('requirements.skill');

        foreach ($item->requirements as $req) {
            $met = match ($req->type) {
                ShareItemRequirementType::LEVEL => $player->lvl >= $req->min_value,
                ShareItemRequirementType::STAT => $this->checkStat($player, (string) $req->stat_key, $req->min_value),
                ShareItemRequirementType::SKILL => PlayerSkill::where('player_id', $player->id)
                    ->where('skill_id', $req->skill_id)
                    ->where('lvl', '>=', $req->min_value)
                    ->exists(),
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
            PlayerStatKey::STRENGTH    => (int) floor($player->strength),
            PlayerStatKey::AGILITY     => (int) floor($player->agility),
            PlayerStatKey::INTUITION   => (int) floor($player->intuition),
            PlayerStatKey::WISDOM      => (int) floor($player->wisdom),
            PlayerStatKey::INTELLIGENCE => (int) floor($player->intelligence),
            null => 0,
        };

        return $value >= $minValue;
    }
}
