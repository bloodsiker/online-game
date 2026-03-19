<?php

namespace App\Services\Combat;

use App\DTO\AttackResultDTO;
use App\DTO\FightDTO;
use App\DTO\FightHitDTO;
use App\Events\PlayerLeveledUp;
use App\Models\Battle\Battle;
use App\Models\Battle\BattleDetail;
use App\Models\Monster\Monster;
use App\Models\Monster\MonsterOnLocation;
use App\Models\Player\Player;
use App\Services\Combat\Boss\BossShieldService;
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
        private BossShieldService      $shieldService,
    ) {}

    public function execute(Player $player, MonsterOnLocation $locMonster, int $action, Battle $battle): AttackResultDTO
    {
        $result = new AttackResultDTO();

        // Restore HP the monster would have regenerated since the last attack
        $locMonster->regenerate();

        $strategy = $this->resolver->resolve($player, $locMonster->monster, $action);

        $isBoss = $locMonster->monster->isBoss();

        /** @var FightHitDTO $hit */
        foreach ($strategy->getHits() as $hit) {
            if ($hit->isCantCast()) {
                $result->log(sprintf('<p><b class="color-info">%s</b></p>', $hit->getMessage()));
                continue;
            }

            if ($hit->isDodge()) {
                $result->log('<p>Вы атакуете неудачно... Враг <b class="color-green">увернулся</b></p>');
                continue;
            }

            $damage = $hit->getDamage();

            if ($isBoss && $battle) {

                // Перевірка імунітету
                $damage = $this->checkImmunity($battle, $damage, $hit, $result);
                if ($damage <= 0) {
                    continue;
                }

                // 🆕 Перевірка конвертації урону в лікування (ПЕРЕД щитом!)
                $damageConverted = $this->checkDamageToHeal($battle, $damage, $locMonster, $result);

                if ($damageConverted) {
                    // Урон було сконвертовано в лікування - пропускаємо далі

                    // Нараховуємо досвід за спробу атаки (опціонально)
                    $exp = $this->calculateExperience($player, $locMonster->monster, 1);
                    $player->exp += $exp;

                    continue;
                }

                // Обробка щита (якщо урон не сконвертовано)
                if ($this->shieldService->hasActiveShield($battle)) {
                    $damage = $this->shieldService->damageShield($battle, $damage, $result);

                    if ($damage <= 0) {
                        continue;
                    }
                }

                // Відбиття урону
                $this->reflectDamage($battle, $damage, $player, $result);
            }

            $exp = $this->calculateExperience($player, $locMonster->monster, min($locMonster->hp_now, $damage));

            $locMonster->hp_now = max(0, $locMonster->hp_now - $damage);
            $player->exp += $exp;

            $this->playerSkillService->gainExperienceSkill($player, $hit->getSkill(), $hit->getWeapon());

            $result->log($hit->isCritical()
                ? sprintf(
                    '<p>Вы ударили врага %s... <b class="color-red">нанесен критический урон!</b> <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>',
                    $hit->getWeaponName(),
                    $damage,
                    $exp
                )
                : sprintf(
                    '<p>Вы ударили врага %s! <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>',
                    $hit->getWeaponName(),
                    $damage,
                    $exp
                )
            );

            if (!$hit->getAppliedEffects()->isEmpty()) {
                foreach ($hit->getAppliedEffects() as $effect) {
//                    if ($effect->isHeal()) {
//                        $locMonster->hp_now = min($locMonster->monster->hp, $locMonster->hp_now + $effect->power);
//                    }
//
//                    if ($effect) {
//
//                    }

                    $result->log(sprintf(
                        '<p>%s получил эффект от вашего заклинания %s: <b class="color-purple">%s</b></p>',
                        $locMonster->monster->name,
                        $hit->getMagicSkill()->name,
                        $effect->name
                    ));
                }
            }
        }

        return $result;
    }

    /**
     * Проверка конвертации урона в лечение
     * Возвращает true, если урон был преобразован
     */
    private function checkDamageToHeal(
        Battle $battle,
        int $damage,
        MonsterOnLocation $locMonster,
        AttackResultDTO $result
    ): bool {
        $metadata = $battle->boss_metadata ?? [];
        $damageToHeal = $metadata['damage_to_heal'] ?? null;

        if (!$damageToHeal) {
            return false;
        }

        // Перевірка чи не закінчилася дія
        if ($damageToHeal['expires_at_turn'] < $battle->rounds) {
            unset($metadata['damage_to_heal']);
            $battle->boss_metadata = $metadata;
            $battle->save();

            $result->log(sprintf(
                '<p class="color-info">💉 Конвертация урона в лечение закончилась!</p>'
            ));

            return false;
        }

        // Розраховуємо кількість лікування
        $conversionPercent = $damageToHeal['conversion_percent'];
        $healAmount = (int)(($damage * $conversionPercent) / 100);

        // Застосовуємо обмеження максимального лікування за хіт
        if ($damageToHeal['max_heal_per_hit']) {
            $healAmount = min($healAmount, $damageToHeal['max_heal_per_hit']);
        }

        // Лікуємо боса
        $oldHp = $locMonster->hp_now;
        $maxHp = $locMonster->monster->hp;
        $locMonster->hp_now = min($maxHp, $locMonster->hp_now + $healAmount);
        $actualHeal = $locMonster->hp_now - $oldHp;

        // Оновлюємо статистику
        $damageToHeal['total_healed'] += $actualHeal;
        $damageToHeal['hits_converted']++;
        $metadata['damage_to_heal'] = $damageToHeal;
        $battle->boss_metadata = $metadata;
        $battle->save();

        // Логування
        if ($conversionPercent === 100) {
            $result->log(sprintf(
                '<p><b class="color-damage-to-heal">💉 Ваш урон (%d) превращено в лечение! Босс восстановил %d HP!</b></p>',
                $damage,
                $actualHeal
            ));
        } else {
            $convertedDamage = (int)(($damage * $conversionPercent) / 100);
            $normalDamage = $damage - $convertedDamage;

            $result->log(sprintf(
                '<p><b class="color-damage-to-heal">💉 %d%% вашего урона (%d з %d) превращено в лечение! Босс восстановил %d HP!</b></p>',
                $conversionPercent,
                $convertedDamage,
                $damage,
                $actualHeal
            ));

            // Якщо конвертація неповна - повертаємо false щоб залишковий урон пройшов
            if ($conversionPercent < 100) {
                return false;
            }
        }

        return true;
    }

    /**
     * Перевірка імунітету боса
     */
    private function checkImmunity(
        Battle $battle,
        int $damage,
        FightHitDTO $hit,
        AttackResultDTO $result
    ): int {
        $metadata = $battle->boss_metadata ?? [];
        $immunity = $metadata['immunity'] ?? null;

        if (!$immunity) {
            return $damage;
        }

        if ($immunity['expires_at_turn'] < $battle->rounds) {
            unset($metadata['immunity']);
            $battle->boss_metadata = $metadata;
            $battle->save();
            return $damage;
        }

        $immunityType = $immunity['type'];
        $attackType = $hit->getWeapon() ? 'physical' : 'magic';

        $isImmune = match($immunityType) {
            'all' => true,
            'physical' => $attackType === 'physical',
            'magic' => $attackType === 'magic',
            default => false,
        };

        if ($isImmune) {
            $immunity['blocked_damage'] += $damage;
            $metadata['immunity'] = $immunity;
            $battle->boss_metadata = $metadata;
            $battle->save();

            $result->log(sprintf(
                '<p><b class="color-immunity">✨ Босс иммунен к этому типу урона! (%d урон заблокирован)</b></p>',
                $damage
            ));

            return 0;
        }

        return $damage;
    }

    /**
     * Відбиття урону назад гравцю
     */
    private function reflectDamage(
        Battle $battle,
        int $damage,
        Player $player,
        AttackResultDTO $result
    ): void {
        $metadata = $battle->boss_metadata ?? [];
        $reflect = $metadata['reflect_damage'] ?? null;

        if (!$reflect) {
            return;
        }

        if ($reflect['expires_at_turn'] < $battle->rounds) {
            unset($metadata['reflect_damage']);
            $battle->boss_metadata = $metadata;
            $battle->save();
            return;
        }

        $reflectedDamage = (int)(($damage * $reflect['percent']) / 100);
        $actualReflected = max(1, $reflectedDamage);

        $player->hp_now = max(0, $player->hp_now - $actualReflected);

        $reflect['total_reflected'] += $actualReflected;
        $metadata['reflect_damage'] = $reflect;
        $battle->boss_metadata = $metadata;
        $battle->save();

        $result->log(sprintf(
            '<p><b class="color-reflect">🔁 %d%% урон отбит назад! Вы получили %d урона!</b></p>',
            $reflect['percent'],
            $actualReflected
        ));
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
