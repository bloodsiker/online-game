<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\Enums\ActiveEffectType;
use App\Models\Battle\Battle;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Services\Combat\Boss\BossPhaseService;

readonly class MonsterAttackService
{
    public function __construct(
        private HitCalculator $hitCalc,
        private BossPhaseService $bossPhaseService,
        private BattleEffectService $effectService,
    ) {}

    public function execute(FightHitInterface $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        $hit = $this->hitCalc->monsterHit($locationMonster->monster, $player, $locationMonster->monster->min_dmg, $locationMonster->monster->max_dmg);

        if ($hit->isDodge()) {
            $result->log(sprintf('<p>%s атакует неудачно... Вы <b class="color-green">увернулись</b></p>', $locationMonster->monster->name));

            return;
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $msg = $hit->isCritical()
            ? sprintf('<p>%s прыгнул на вас. <b class="color-red">нанесен критический удар!</b> <br>Повреждения: <b>%s</b></p>', $locationMonster->monster->name, $hit->getDamage())
            : sprintf('<p>%s прыгнул на вас. <br>Повреждения: <b>%s</b></p>', $locationMonster->monster->name, $hit->getDamage());

        $result->log($msg);
    }

    /**
     * Атака боса з урахуванням фаз, модифікаторів і спеціальних скілів
     */
    public function executeBossAttack(
        Player $player,
        MonsterOnLocation $locationMonster,
        Battle $battle,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;

        // Отримуємо модифіковані характеристики з boss_metadata
        $modifiedAttack = $this->bossPhaseService->getModifiedStat($battle, 'attack');
        $modifiedMinDmg = $this->bossPhaseService->getModifiedStat($battle, 'min_dmg');
        $modifiedMaxDmg = $this->bossPhaseService->getModifiedStat($battle, 'max_dmg');

        // Якщо модифікацій немає - використовуємо базові значення
        $minDmg = $modifiedMinDmg ?? $monster->min_dmg;
        $maxDmg = $modifiedMaxDmg ?? $monster->max_dmg;

        // Отримуємо додаткові модифікатори з механік (наприклад, Enrage)
        $metadata = $battle->boss_metadata ?? [];
        $mechanicModifier = $metadata['attack_modifier'] ?? 0;

        if ($mechanicModifier > 0) {
            $minDmg += (int) (($minDmg * $mechanicModifier) / 100);
            $maxDmg += (int) (($maxDmg * $mechanicModifier) / 100);
        }

        // Отримуємо активні скіли з boss_metadata
        $availableSkills = $this->bossPhaseService->getPhaseSkills($battle);

        // З певною ймовірністю використовуємо спеціальний скіл
        $useSpecialSkill = ! empty($availableSkills) && random_int(1, 100) <= 35;

        // Розраховуємо загальний модифікатор для виводу
        $phaseModifiers = $this->bossPhaseService->getPhaseModifiers($battle);
        $totalModifier = ($phaseModifiers['attack'] ?? 0) + $mechanicModifier;

        if ($useSpecialSkill) {
            $this->executeBossSpecialSkill(
                $player,
                $locationMonster,
                $battle,
                $availableSkills,
                $minDmg,
                $maxDmg,
                $totalModifier,
                $result
            );
        } else {
            $this->executeBossNormalAttack(
                $player,
                $locationMonster,
                $minDmg,
                $maxDmg,
                $totalModifier,
                $result
            );
        }
    }

    private function executeBossNormalAttack(
        FightHitInterface $player,
        MonsterOnLocation $locationMonster,
        int $minDmg,
        int $maxDmg,
        int $totalModifier,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;

        $hit = $this->hitCalc->monsterHit($monster, $player, $minDmg, $maxDmg);

        if ($hit->isDodge()) {
            $result->log(sprintf(
                '<p><b class="color-boss">%s</b> атакует неудачно... Вы <b class="color-green">увернулись</b></p>',
                $monster->name
            ));

            return;
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $modifierText = $totalModifier > 0
            ? sprintf(' <span class="color-enrage">(урон +%d%%)</span>', $totalModifier)
            : '';

        $msg = $hit->isCritical()
            ? sprintf(
                '<p><b class="color-boss">%s</b> набрасывается на вас%s. <b class="color-red">⚡ Критический удар!</b> <br>Повреждения: <b class="color-damage">%s</b></p>',
                $monster->name,
                $modifierText,
                $hit->getDamage()
            )
            : sprintf(
                '<p><b class="color-boss">%s</b> атакует вас%s! <br>Повреждения: <b>%s</b></p>',
                $monster->name,
                $modifierText,
                $hit->getDamage()
            );

        $result->log($msg);
    }

    private function executeBossSpecialSkill(
        Player $player,
        MonsterOnLocation $locationMonster,
        Battle $battle,
        array $availableSkills,
        int $minDmg,
        int $maxDmg,
        int $totalModifier,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;
        $randomSkill = $availableSkills[array_rand($availableSkills)];

        $skill = $monster->skills()
            ->where('skill_code', $randomSkill)
            ->first();

        if (! $skill) {
            $this->executeBossNormalAttack(
                $player,
                $locationMonster,
                $minDmg,
                $maxDmg,
                $totalModifier,
                $result
            );

            return;
        }

        $damageMultiplier = $skill->parameters['damage_multiplier'] ?? 1.0;
        $skillMinDmg = (int) ($minDmg * $damageMultiplier);
        $skillMaxDmg = (int) ($maxDmg * $damageMultiplier);

        $hit = $this->hitCalc->monsterHit($monster, $player, $skillMinDmg, $skillMaxDmg);

        if ($hit->isDodge()) {
            $result->log(sprintf(
                '<p><b class="color-boss">%s</b> использует <b>%s</b>, но вы <b class="color-green">увернулись</b>!</p>',
                $monster->name,
                $skill->skill_name ?? $randomSkill
            ));

            return;
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $skillEmoji = $this->getSkillEmoji($randomSkill);

        $msg = $hit->isCritical()
            ? sprintf(
                '<p><b class="color-boss">%s</b> использует <b class="color-skill">%s %s</b>! <b class="color-red">⚡ Критический удар!</b> <br>Повреждения: <b class="color-damage">%s</b> (x%.1f)</p>',
                $monster->name,
                $skillEmoji,
                $skill->skill_name ?? $randomSkill,
                $hit->getDamage(),
                $damageMultiplier
            )
            : sprintf(
                '<p><b class="color-boss">%s</b> использует <b class="color-skill">%s %s</b>! <br>Повреждения: <b>%s</b> (x%.1f)</p>',
                $monster->name,
                $skillEmoji,
                $skill->skill_name ?? $randomSkill,
                $hit->getDamage(),
                $damageMultiplier
            );

        $result->log($msg);

        if (isset($skill->parameters['effects'])) {
            $this->applySkillEffects($player, $battle, $skill->parameters['effects'], $monster->name, $result);
        }
    }

    private function getSkillEmoji(string $skillCode): string
    {
        return match ($skillCode) {
            'fire_breath' => '🔥',
            'ice_breath' => '❄️',
            'lightning_strike' => '⚡',
            'tail_swipe' => '💨',
            'wing_storm' => '🌪️',
            'earth_slam' => '💥',
            'poison_spit' => '☠️',
            'shadow_strike' => '🌑',
            'holy_smite' => '✨',
            'claw_attack' => '🗡️',
            'bite' => '🦷',
            'roar' => '📢',
            default => '⚔️'
        };
    }

    private function applySkillEffects(
        Player $player,
        Battle $battle,
        array $effects,
        string $monsterName,
        AttackResultDTO $result
    ): void {
        foreach ($effects as $effect) {
            $type = ActiveEffectType::tryFrom($effect['type'] ?? '');
            $chance = $effect['chance'] ?? 100;
            $stacks = (int) ($effect['duration'] ?? $effect['stacks'] ?? 2);
            $value = (float) ($effect['value'] ?? 0);

            if ($type === null || random_int(1, 100) > $chance) {
                continue;
            }

            $this->effectService->applyCustomEffectToPlayer(
                $type,
                $value,
                $stacks,
                $player,
                $battle,
                $result
            );
        }
    }
}
