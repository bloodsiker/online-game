<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\Events\PlayerLeveledUp;
use App\Models\Battle\BattleDetail;
use App\Models\Monster\Monster;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Player\Player;
use app\Services\PlayerSkillService;
use App\Services\QuestProgressService;
use App\Services\DropService;

readonly class AttackService
{
    public function __construct(
        private AttackStrategyResolver $resolver,
        private QuestProgressService   $questService,
        private PlayerSkillService     $playerSkillService,
        private DropService            $dropService,
    ) {}

    public function execute(Player $player, MonsterOnLocation $locMonster, int $action): AttackResultDTO
    {
        $result = new AttackResultDTO();
        $strategy = $this->resolver->resolve($player, $locMonster->monster, $action);

        foreach ($strategy->getHits() as $hit) {
            if ($hit->isCantCast()) {
                $result->log(sprintf('<p><b class="color-info">%s</b></p>', $hit->getMessage()));
                continue;
            }

            if ($hit->isDodge()) {
                $result->log('<p>Вы атакуете неудачно... Враг <b class="color-green">увернулся</b></p>');
                continue;
            }

            $exp = $this->calculateExperience($player, $locMonster->monster, min($locMonster->hp_now, $hit->getDamage()));

            $locMonster->hp_now = max(0, $locMonster->hp_now - $hit->getDamage());
            $player->exp += $exp;

            $this->playerSkillService->gainExperienceSkill($player, $hit->getSkill(), $hit->getWeapon());

            $result->log($hit->isCritical()
                ? sprintf('<p>Вы ударили врага %s... <b class="color-red">нанесен критический урон!</b> <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>', $hit->getWeaponName(), $hit->getDamage(), $exp)
                : sprintf('<p>Вы ударили врага %s! <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>', $hit->getWeaponName(), $hit->getDamage(), $exp)
            );
        }

        return $result;
    }

    public function handleMonsterDeath(Player $player, MonsterOnLocation $locationMonster, BattleDetail $attackedMonster, AttackResultDTO $result)
    {
        $locationMonster->active = 0;
        $locationMonster->save();

        $attackedMonster->status = 0;
        $attackedMonster->save();

        $this->dropService->dropMoney($player->user, $locationMonster, $result);
        $this->questService->progressKillAndCollect($player, $locationMonster, $result);
    }

    public function checkLevelUp(Player $player, AttackResultDTO $result)
    {
        if ($player->exp >= $player->exp_up) {
            $player->lvl++;
            $player->save();

            event(new PlayerLeveledUp($player));

            $result->log(sprintf("<p><b style='background:#cff44f;'>Вы получили новый уровень %s.</b></p>", $player->lvl));
        }
    }

    private function calculateExperience(Player $player, Monster $monster, int $damage)
    {
        $takeExp = ($damage * $monster->exp) / $monster->hp;
        $levelDifference = $player->lvl - $monster->lvl;
        $experienceMultiplier = max(0.01, 1 - 0.05 * $levelDifference); // Experience reduced by 5% per level

        // Experience is calculated as base experience multiplied by damage and adjusted by level.
        return round(max(1, $takeExp * $experienceMultiplier));
    }
}
