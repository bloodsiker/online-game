<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\Models\Battle\Battle;
use App\Models\Monster\MonsterOnLocation;
use App\Services\Combat\Boss\BossPhaseService;

readonly class MonsterAttackService
{
    public function __construct(
        private HitCalculator $hitCalc,
        private BossPhaseService $bossPhaseService,
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
        FightHitInterface $player,
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
            $minDmg += (int)(($minDmg * $mechanicModifier) / 100);
            $maxDmg += (int)(($maxDmg * $mechanicModifier) / 100);
        }

        // Отримуємо активні скіли з boss_metadata
        $availableSkills = $this->bossPhaseService->getPhaseSkills($battle);

        // З певною ймовірністю використовуємо спеціальний скіл
        $useSpecialSkill = !empty($availableSkills) && random_int(1, 100) <= 35;

        // Розраховуємо загальний модифікатор для виводу
        $phaseModifiers = $this->bossPhaseService->getPhaseModifiers($battle);
        $totalModifier = ($phaseModifiers['attack'] ?? 0) + $mechanicModifier;

        if ($useSpecialSkill) {
            $this->executeBossSpecialSkill(
                $player,
                $locationMonster,
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
        FightHitInterface $player,
        MonsterOnLocation $locationMonster,
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

        if (!$skill) {
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
        $skillMinDmg = (int)($minDmg * $damageMultiplier);
        $skillMaxDmg = (int)($maxDmg * $damageMultiplier);

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
            $this->applySkillEffects($player, $skill->parameters['effects'], $monster->name, $result);
        }
    }

    private function getSkillEmoji(string $skillCode): string
    {
        return match($skillCode) {
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
        FightHitInterface $player,
        array $effects,
        string $monsterName,
        AttackResultDTO $result
    ): void {
        foreach ($effects as $effect) {
            $effectType = $effect['type'] ?? '';
            $chance = $effect['chance'] ?? 100;

            if (random_int(1, 100) > $chance) {
                continue;
            }

            switch ($effectType) {
                case 'stun':
                    $result->log(sprintf(
                        '<p class="color-debuff">💫 Вы оглушены атакой <b>%s</b>!</p>',
                        $monsterName
                    ));
                    break;

                case 'poison':
                    $result->log(sprintf(
                        '<p class="color-debuff">☠️ Вы отравлены атакой <b>%s</b>!</p>',
                        $monsterName
                    ));
                    break;

                case 'bleed':
                    $result->log(sprintf(
                        '<p class="color-debuff">🩸 Вы истекаете кровью после атаки <b>%s</b>!</p>',
                        $monsterName
                    ));
                    break;

                case 'burn':
                    $result->log(sprintf(
                        '<p class="color-debuff">🔥 Вы горите после атаки <b>%s</b>!</p>',
                        $monsterName
                    ));
                    break;
            }
        }
    }
}
