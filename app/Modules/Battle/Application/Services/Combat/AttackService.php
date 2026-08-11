<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\DTO\AttackResultDTO;
use App\DTO\FightHitDTO;
use App\Events\PlayerLeveledUp;
use App\Modules\Battle\Application\Services\Combat\Boss\BossShieldService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Domain\Enums\ActiveEffectType;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Event\Domain\Services\EventActivityProgressService;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Structure\Blacksmith\Domain\Enums\RunePassiveType;
use App\Services\DropService;
use App\Services\PlayerSkillService;

readonly class AttackService
{
    public function __construct(
        private AttackStrategyResolver $resolver,
        private QuestProgressService $questService,
        private EventActivityProgressService $eventActivityProgressService,
        private PlayerSkillService $playerSkillService,
        private DropService $dropService,
        private BossShieldService $shieldService,
        private BattleEffectService $effectService,
        private PlayerRunePassiveService $runePassiveService,
        private PlayerStatService $statService,
        private RandomizerInterface $random,
    ) {}

    public function execute(Player $player, MonsterOnLocation $locMonster, int $action, Battle $battle, float $xpMultiplier = 1.0): AttackResultDTO
    {
        $result = new AttackResultDTO;

        // Restore HP the monster would have regenerated since the last attack
        $locMonster->regenerate();

        $strategy = $this->resolver->resolve($player, $locMonster->monster, $action);

        $isBoss = $locMonster->monster->isBoss();
        $runePassives = $this->runePassiveService->resolve($player);
        $effectiveHpMax = $this->statService->resolve($player)->getHpMax();

        /** @var FightHitDTO $hit */
        foreach ($strategy->getHits() as $hit) {
            if ($hit->isCantCast()) {
                $result->log(sprintf('<p><b class="color-info">%s</b></p>', $hit->getMessage()));

                continue;
            }

            if ($hit->isDodge()) {
                $result->log(sprintf(
                    '<p>Вы атакуете неудачно... %s <b class="color-green">увернулся</b></p>',
                    $locMonster->monster->name
                ));

                continue;
            }

            // Бафф-хит (damage=0): применяем эффекты на себя и выходим
            if ($hit->getDamage() === 0) {
                if ($hit->getMessage()) {
                    $result->log(sprintf('<p class="color-buff">%s</p>', $hit->getMessage()));
                }
                foreach ($hit->getSelfAppliedEffects() as $effect) {
                    $this->effectService->applyEffectToPlayer($effect, $player, $battle, $result);
                    $result->log(sprintf(
                        '<p>Заклинание %s наложило на вас: <b class="color-purple">%s</b></p>',
                        $hit->getMagicSkill()->name,
                        $effect->name
                    ));
                }

                continue;
            }

            $damage = $this->applyRageBonus($player, $hit->getDamage(), $runePassives, $effectiveHpMax);

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
                    $exp = $this->calculateExperience($player, $locMonster->monster, 1, $locMonster->hp_max, $xpMultiplier);
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

            $exp = $this->calculateExperience($player, $locMonster->monster, min($locMonster->hp_now, $damage), $locMonster->hp_max, $xpMultiplier);

            $locMonster->hp_now = max(0, $locMonster->hp_now - $damage);
            $player->exp += $exp;

            $this->playerSkillService->gainExperienceSkill($player, $hit->getSkill(), $hit->getWeapon());

            $result->log($hit->isCritical()
                ? sprintf(
                    '<p>Вы ударили %s оружием %s... <b class="color-red">нанесен критический урон!</b> <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>',
                    $locMonster->monster->name,
                    $hit->getWeaponName(),
                    $damage,
                    $exp
                )
                : sprintf(
                    '<p>Вы ударили %s оружием %s! <br>Повреждения: <b>%s</b> (ваш опыт +%s) </p>',
                    $locMonster->monster->name,
                    $hit->getWeaponName(),
                    $damage,
                    $exp
                )
            );

            if (! $hit->getAppliedEffects()->isEmpty()) {
                foreach ($hit->getAppliedEffects() as $effect) {
                    $this->effectService->applyEffectToMonster($effect, $locMonster, $battle, $result);

                    $result->log(sprintf(
                        '<p>%s получил эффект от вашего заклинания %s: <b class="color-purple">%s</b></p>',
                        $locMonster->monster->name,
                        $hit->getMagicSkill()->name,
                        $effect->name
                    ));
                }
            }

            $this->applyOffensiveRunePassives($player, $locMonster, $battle, $damage, $hit->getHandSide(), $runePassives, $xpMultiplier, $effectiveHpMax, $result);
        }

        return $result;
    }

    /**
     * Триггеры пассивок рун при успешной атаке игрока: Вампиризм (лечение %
     * от урона), Оглушение (шанс оглушить моба на 1 ход), Двойной удар (шанс
     * повторить урон этого хита) и Цепная атака (шанс задеть второго живого
     * моба в этом же бою за 50% урона). Реагируют строго на реально
     * нанесённый урон, поэтому применяются уже после вычитания HP у моба.
     *
     * $handSide — рука, нанёсшая именно этот хит (см. FightHitDTO::getHandSide()):
     * пассивка руны из оружия в другой руке при дуал-вилде не участвует —
     * только руны этого оружия и руны вне рук (броня и т.п.).
     *
     * @param  list<array{type: RunePassiveType, value: int, runeName: string, itemName: string, handSide: ?string}>  $runePassives
     */
    private function applyOffensiveRunePassives(
        Player $player,
        MonsterOnLocation $locMonster,
        Battle $battle,
        int $damage,
        ?string $handSide,
        array $runePassives,
        float $xpMultiplier,
        int $effectiveHpMax,
        AttackResultDTO $result,
    ): void {
        if ($runePassives === []) {
            return;
        }

        $vampirism = $this->runePassiveService->totalValue($runePassives, RunePassiveType::VAMPIRISM, $handSide);
        if ($vampirism > 0 && $this->random->chance($vampirism)) {
            $heal = max(1, (int) round($damage * 0.10));
            $before = $player->hp_now;
            $player->hp_now = min($effectiveHpMax, $player->hp_now + $heal);
            $actualHeal = $player->hp_now - $before;

            if ($actualHeal > 0) {
                $result->log(sprintf(
                    '<p><b class="color-buff">🩸 Вампиризм руны восстанавливает вам %d HP!</b></p>',
                    $actualHeal
                ));
            }
        }

        $stun = $this->runePassiveService->totalValue($runePassives, RunePassiveType::STUN, $handSide);
        if ($stun > 0 && $locMonster->hp_now > 0 && $this->random->chance($stun)) {
            $this->effectService->applyCustomEffectToMonster(
                ActiveEffectType::STUN,
                0,
                1,
                $locMonster,
                $battle,
                $result,
            );
        }

        $doubleStrike = $this->runePassiveService->totalValue($runePassives, RunePassiveType::DOUBLE_STRIKE, $handSide);
        if ($doubleStrike > 0 && $locMonster->hp_now > 0 && $this->random->chance($doubleStrike)) {
            $extraDamage = min($locMonster->hp_now, $damage);
            $extraExp = $this->calculateExperience($player, $locMonster->monster, $extraDamage, $locMonster->hp_max, $xpMultiplier);

            $locMonster->hp_now = max(0, $locMonster->hp_now - $damage);
            $player->exp += $extraExp;

            $result->log(sprintf(
                '<p><b class="color-red">⚔ Двойной удар руны! Вы наносите ещё %d урона %s (опыт +%d)</b></p>',
                $damage,
                $locMonster->monster->name,
                $extraExp
            ));
        }

        $chainAttack = $this->runePassiveService->totalValue($runePassives, RunePassiveType::CHAIN_ATTACK, $handSide);
        if ($chainAttack > 0 && $this->random->chance($chainAttack)) {
            $this->applyChainAttack($player, $locMonster, $battle, $damage, $xpMultiplier, $result);
        }
    }

    /**
     * Цепная атака: находит другого живого моба в этом же бою (не текущую
     * цель) и наносит ему 50% урона этого хита.
     */
    private function applyChainAttack(
        Player $player,
        MonsterOnLocation $locMonster,
        Battle $battle,
        int $damage,
        float $xpMultiplier,
        AttackResultDTO $result,
    ): void {
        $secondTarget = BattleDetail::whereNotNull('location_monster_id')
            ->where('battle_id', $battle->id)
            ->where('location_monster_id', '!=', $locMonster->id)
            ->where('status', BattleDetailStatus::LIFE)
            ->with('locationMonster.monster')
            ->first();

        $secondMonster = $secondTarget?->locationMonster;

        if (! $secondMonster || $secondMonster->hp_now <= 0) {
            return;
        }

        $chainDamage = max(1, (int) round($damage * 0.5));
        $chainDamage = min($secondMonster->hp_now, $chainDamage);
        $exp = $this->calculateExperience($player, $secondMonster->monster, $chainDamage, $secondMonster->hp_max, $xpMultiplier);

        $secondMonster->hp_now = max(0, $secondMonster->hp_now - $chainDamage);
        $secondMonster->save();
        $player->exp += $exp;

        $result->log(sprintf(
            '<p><b class="color-purple">🔗 Цепная атака руны поражает %s! <br>Повреждения: %d (ваш опыт +%d)</b></p>',
            $secondMonster->monster->name,
            $chainDamage,
            $exp
        ));
    }

    /**
     * Ярость: пока HP игрока ниже 30% от эффективного максимума (с учётом
     * экипировки), урон этого хита увеличивается на суммарный % пассивки.
     */
    private function applyRageBonus(Player $player, int $damage, array $runePassives, int $effectiveHpMax): int
    {
        $rage = $this->runePassiveService->totalValue($runePassives, RunePassiveType::RAGE);

        if ($rage <= 0) {
            return $damage;
        }

        if ($effectiveHpMax <= 0 || $player->hp_now / $effectiveHpMax >= 0.30) {
            return $damage;
        }

        return (int) round($damage * (1 + $rage / 100));
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

        if (! $damageToHeal) {
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
        $healAmount = (int) (($damage * $conversionPercent) / 100);

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
            $convertedDamage = (int) (($damage * $conversionPercent) / 100);
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

        if (! $immunity) {
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

        $isImmune = match ($immunityType) {
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

        if (! $reflect) {
            return;
        }

        if ($reflect['expires_at_turn'] < $battle->rounds) {
            unset($metadata['reflect_damage']);
            $battle->boss_metadata = $metadata;
            $battle->save();

            return;
        }

        $reflectedDamage = (int) (($damage * $reflect['percent']) / 100);
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

        if ($locationMonster->monster->isBoss()) {
            $locationMonster->monster->scheduleRespawn();
        }

        MonsterActiveEffect::where('location_monster_id', $locationMonster->id)->delete();

        $this->dropService->dropMoney($player->user, $locationMonster, $result);
        $this->questService->progressKillAndCollect($player, $locationMonster, $result);
        $this->eventActivityProgressService->progressKill($player, $locationMonster);
    }

    public function checkLevelUp(Player $player, AttackResultDTO $result)
    {
        if ($player->exp >= $player->exp_up) {
            $player->lvl++;
            $player->save();

            event(new PlayerLeveledUp($player));

            $result->logSide(sprintf('<p class="msg-levelup">&#9650; Вы получили новый уровень <b>%s</b>!</p>', $player->lvl));
        }
    }

    private function calculateExperience(Player $player, Monster $monster, int $damage, int $monsterMaxHp, float $xpMultiplier = 1.0): int
    {
        $takeExp = ($damage * $monster->exp) / $monsterMaxHp;

        $levelDifference = $player->lvl - $monster->lvl;
        $levelMultiplier = min(2.0, max(0.01, 1 - 0.05 * $levelDifference));

        return (int) round(max(1, $takeExp * $levelMultiplier * $xpMultiplier));
    }
}
