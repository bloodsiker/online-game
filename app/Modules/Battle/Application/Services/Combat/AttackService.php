<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\Boss\BossShieldService;
use App\Modules\Battle\Application\Services\DropService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Battle\Infrastructure\Persistence\Models\BattleDetail;
use App\Modules\Clan\Domain\Services\ClanExperienceService;
use App\Modules\Dungeon\Application\Services\DungeonStageService;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Event\Domain\Services\EventActivityProgressService;
use App\Modules\Event\Domain\Services\WorldEventKillService;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Events\PlayerLeveledUp;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Domain\Services\PlayerSkillService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Reputation\Domain\Enums\DivineFavorType;
use App\Modules\Reputation\Domain\Services\DivineFavorService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Structure\Blacksmith\Domain\Enums\RunePassiveType;

readonly class AttackService
{
    public function __construct(
        private AttackStrategyResolver $resolver,
        private QuestProgressService $questService,
        private EventActivityProgressService $eventActivityProgressService,
        private WorldEventKillService $worldEventKillService,
        private ExperienceService $experienceService,
        private ClanExperienceService $clanExperienceService,
        private PlayerSkillService $playerSkillService,
        private DropService $dropService,
        private BossShieldService $shieldService,
        private BattleEffectService $effectService,
        private PlayerRunePassiveService $runePassiveService,
        private PlayerStatService $statService,
        private RandomizerInterface $random,
        private DivineFavorService $divineFavorService,
        private DungeonStageService $dungeonStageService,
    ) {}

    public function execute(Player $player, MonsterOnLocation $locMonster, int $action, Battle $battle, float $xpMultiplier = 1.0): AttackResultDTO
    {
        $result = new AttackResultDTO;

        // Restore HP the monster would have regenerated since the last attack
        $locMonster->regenerate();

        $strategy = $this->resolver->resolve($player, $locMonster, $action, $battle);

        $isBoss = $locMonster->monster->isBoss();
        $runePassives = $this->runePassiveService->resolve($player);
        $divineFavors = $this->divineFavorService->resolve($player);
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
                    $this->effectService->applyEffectToPlayer(
                        $effect,
                        $player,
                        $battle,
                        $result,
                        (int) $effect->pivot->duration_seconds,
                    );
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
                    $this->awardMonsterExperience($player, $locMonster->monster, $exp);

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
            $this->awardMonsterExperience($player, $locMonster->monster, $exp);

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
                foreach ($hit->getAppliedEffects() as $applied) {
                    $this->effectService->applyEffectToMonster(
                        $applied['effect'],
                        $locMonster,
                        $battle,
                        $result,
                        (int) $applied['effect']->pivot->duration_seconds,
                        $applied['tickValue'],
                    );

                    $result->log(sprintf(
                        '<p>%s получил эффект от вашего заклинания %s: <b class="color-purple">%s</b></p>',
                        $locMonster->monster->name,
                        $hit->getMagicSkill()->name,
                        $applied['effect']->name
                    ));
                }
            }

            $this->applyOffensiveRunePassives($player, $locMonster, $battle, $damage, $hit->getHandSide(), $runePassives, $xpMultiplier, $effectiveHpMax, $result);
            $this->applyDivineFavors($player, $locMonster, $battle, $divineFavors, $effectiveHpMax, $result);
        }

        return $result;
    }

    /**
     * «Милость богов»: независимо от оружия/рун, по репутации с настроенной
     * божественной милостью — шанс на лечение себя (Богиня жизни), яд на
     * цель (Бог мёртвых) или временный баф атаки на себя (Бог войны). Все три
     * эффекта используют одну и ту же величину % — из текущего тира
     * репутации, либо фиксированную (feat_favor_percent), если подвиг
     * выполнен и милость стала постоянной.
     *
     * @param  list<array{reputation: Reputation, type: DivineFavorType, chance: int, percent: int, durationSeconds: int, permanent: bool}>  $divineFavors
     */
    private function applyDivineFavors(
        Player $player,
        MonsterOnLocation $locMonster,
        Battle $battle,
        array $divineFavors,
        int $effectiveHpMax,
        AttackResultDTO $result,
    ): void {
        foreach ($divineFavors as $favor) {
            if (! $this->random->chance($favor['chance'])) {
                continue;
            }

            match ($favor['type']) {
                DivineFavorType::HEAL => $this->applyDivineHeal($player, $battle, $favor, $effectiveHpMax, $result),
                DivineFavorType::POISON => $this->applyDivinePoison($locMonster, $battle, $favor, $result),
                DivineFavorType::ATTACK_BUFF => $this->applyDivineAttackBuff($player, $battle, $favor, $result),
            };
        }
    }

    /** @param  array{reputation: Reputation, type: DivineFavorType, chance: int, percent: int, durationSeconds: int, permanent: bool}  $favor */
    private function applyDivineHeal(Player $player, Battle $battle, array $favor, int $effectiveHpMax, AttackResultDTO $result): void
    {
        $effect = Effect::where('slug', 'fiora_blessing_'.$favor['percent'])->first();
        if ($effect === null) {
            return;
        }

        $durationSeconds = $favor['durationSeconds'];
        $ticks = max(1, intdiv($durationSeconds, max(1, (int) $effect->tick_interval)));
        $totalHeal = max($ticks, (int) round($effectiveHpMax * $effect->value_per_tick / 100));
        $tickValue = max(1, (int) round($totalHeal / $ticks));

        $result->log(sprintf(
            '<p><b class="color-buff">🙏 Милость «%s» нисходит на вас — тело начинает восстанавливаться!</b></p>',
            $favor['reputation']->name,
        ));

        $this->effectService->applyEffectToPlayer(
            $effect,
            $player,
            $battle,
            $result,
            $durationSeconds,
            $tickValue,
        );
    }

    /** @param  array{reputation: Reputation, type: DivineFavorType, chance: int, percent: int, durationSeconds: int, permanent: bool}  $favor */
    private function applyDivinePoison(MonsterOnLocation $locMonster, Battle $battle, array $favor, AttackResultDTO $result): void
    {
        if ($locMonster->hp_now <= 0) {
            return;
        }

        $effect = Effect::where('slug', 'kairell_wrath_'.$favor['percent'])->first();
        if ($effect === null) {
            return;
        }

        $durationSeconds = $favor['durationSeconds'];
        $ticks = max(1, intdiv($durationSeconds, max(1, (int) $effect->tick_interval)));
        $totalDamage = max($ticks, (int) round($locMonster->hp_max * $effect->value_per_tick / 100));
        $tickValue = max(1, (int) round($totalDamage / $ticks));

        $result->log(sprintf(
            '<p><b class="color-debuff">☠️ Милость «%s» отравляет %s!</b></p>',
            $favor['reputation']->name,
            $locMonster->monster->name,
        ));

        $this->effectService->applyEffectToMonster(
            $effect,
            $locMonster,
            $battle,
            $result,
            $durationSeconds,
            $tickValue,
        );
    }

    /** @param  array{reputation: Reputation, type: DivineFavorType, chance: int, percent: int, durationSeconds: int, permanent: bool}  $favor */
    private function applyDivineAttackBuff(Player $player, Battle $battle, array $favor, AttackResultDTO $result): void
    {
        $effect = Effect::where('slug', 'divine_attack_buff_'.$favor['percent'])->first();
        if ($effect === null) {
            return;
        }

        $this->effectService->applyEffectToPlayer($effect, $player, $battle, $result, $favor['durationSeconds']);

        $result->log(sprintf(
            '<p><b class="color-buff">⚔ Милость «%s» разгорается в вас яростью боя!</b></p>',
            $favor['reputation']->name,
        ));
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
            $this->awardMonsterExperience($player, $locMonster->monster, $extraExp);

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
        $this->awardMonsterExperience($player, $secondMonster->monster, $exp);

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
        // Юзер потрібен свіжим (нагороди пишуть гроші), але без глобального
        // каскаду player+race — ці сутності в бою вже завантажені.
        $player->loadMissing(['user' => static fn ($query) => $query->without('player')]);

        $locationMonster->active = 0;
        $locationMonster->save();

        $attackedMonster->status = 0;
        $attackedMonster->save();

        if ($locationMonster->monster->isBoss()) {
            $locationMonster->monster->scheduleRespawn();
        }

        MonsterActiveEffect::where('location_monster_id', $locationMonster->id)->delete();

        $this->dropService->dropMoney($player->user, $locationMonster, $result);
        foreach ($this->questService->progressKillAndCollect($player, $locationMonster) as $message) {
            $result->logSide($message);
        }
        $this->eventActivityProgressService->progressKill($player, $locationMonster);
        $eventProgress = $this->worldEventKillService->progressKill($player, $locationMonster);
        if ($eventProgress?->allowed) {
            $nextStageMessage = $eventProgress->stageAdvanced
                ? sprintf(' Начался следующий этап: <b>«%s»</b>.', e($eventProgress->nextStageTitle ?? ''))
                : '';
            $result->logSide(sprintf(
                '<p class="message-event"><b>Событие:</b> +%d влияния. Ваш прогресс: %d/%d.%s</p>',
                $eventProgress->influenceAwarded,
                $eventProgress->playerProgress,
                $eventProgress->playerLimit,
                $nextStageMessage,
            ));
        }

        $stageMessage = $this->dungeonStageService->handleMonsterKilled($locationMonster);
        if ($stageMessage !== null) {
            $result->logSide(sprintf('<p><b>%s</b></p>', e($stageMessage)));
        }
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

        $baseExperience = (int) round(max(1, $takeExp * $levelMultiplier * $xpMultiplier));

        return $this->experienceService->calculateGain($player, $baseExperience);
    }

    private function awardMonsterExperience(Player $player, Monster $monster, int $experience): void
    {
        $player->exp += $experience;
        $this->clanExperienceService->awardForMonsterExperience($player, $monster, $experience);
    }
}
