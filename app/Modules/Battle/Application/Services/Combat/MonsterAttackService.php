<?php

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\Boss\BossPhaseService;
use App\Modules\Battle\Domain\Contracts\RandomizerInterface;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Monster\Domain\Enums\MonsterAttackType;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Enums\RunePassiveType;
use App\Services\PlayerSkillService;

readonly class MonsterAttackService
{
    public function __construct(
        private HitCalculator $hitCalc,
        private MagicHitCalculator $magicHitCalc,
        private BossPhaseService $bossPhaseService,
        private BattleEffectService $effectService,
        private MonsterOnHitEffectService $onHitEffectService,
        private PlayerStatService $statService,
        private PlayerRunePassiveService $runePassiveService,
        private RandomizerInterface $random,
        private PlayerSkillService $playerSkillService,
    ) {}

    public function execute(Player $player, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        // Защита считается по итоговым статам (шмот/баффы), а не по сырой модели
        $sheet = $this->statService->resolve($player);
        $monster = $locationMonster->monster;
        $isMagic = $monster->usesMagicAttack();
        $hit = $this->resolveHit($monster, $sheet, $monster->min_dmg, $monster->max_dmg, $isMagic);

        if (! $isMagic && $hit->isDodge()) {
            $result->log(sprintf('<p>%s атакует неудачно... Вы <b class="color-green">увернулись</b></p>', $monster->name));

            return;
        }

        if (! $isMagic) {
            $this->applyDefensiveRunePassives($player, $hit, $locationMonster, $result);
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $msg = $isMagic
            ? sprintf('<p>%s использует магическую атаку! <br>Повреждения: <b class="color-magic">%s</b></p>', $monster->name, $hit->getDamage())
            : ($hit->isCritical()
            ? sprintf('<p>%s прыгнул на вас. <b class="color-red">нанесен критический удар!</b> <br>Повреждения: <b>%s</b></p>', $locationMonster->monster->name, $hit->getDamage())
            : sprintf('<p>%s прыгнул на вас. <br>Повреждения: <b>%s</b></p>', $locationMonster->monster->name, $hit->getDamage()));

        $result->log($msg);

        if (! $isMagic) {
            $this->applyShieldBlockReflect($player, $hit, $locationMonster, $result);
        }

        $this->onHitEffectService->apply($player, $monster, $hit, $result, $sheet->getHpMax());
    }

    /**
     * Защитные пассивки рун при получении удара: «Щит» (шанс полностью
     * заблокировать атаку — тогда «Отражение» уже нечего отражать) и
     * «Отражение» (% полученного урона снимается с HP моба). Отдельно от
     * applyShieldBlockReflect() — там речь о статах предмета-щита
     * (block_chance/flat/percent), это про руны.
     */
    private function applyDefensiveRunePassives(Player $player, FightHitDTO $hit, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        $passives = $this->runePassiveService->resolve($player);

        if ($passives === []) {
            return;
        }

        $shield = $this->runePassiveService->totalValue($passives, RunePassiveType::SHIELD);
        if ($shield > 0 && $this->random->chance($shield)) {
            $hit->setDamage(0);

            $result->log('<p><b class="color-reflect">🛡 Руна полностью заблокировала атаку!</b></p>');

            return;
        }

        $reflect = $this->runePassiveService->totalValue($passives, RunePassiveType::REFLECT);
        if ($reflect > 0 && $hit->getDamage() > 0) {
            $reflectedDamage = max(1, (int) round($hit->getDamage() * $reflect / 100));
            $locationMonster->hp_now = max(0, $locationMonster->hp_now - $reflectedDamage);

            $result->log(sprintf(
                '<p><b class="color-reflect">🔁 Руна отражает %d%% урона назад! %s получает %d урона!</b></p>',
                $reflect,
                $locationMonster->monster->name,
                $reflectedDamage
            ));
        }
    }

    /**
     * Блок щита: если сработал (HitCalculator::applyShieldBlock), отражённый
     * урон снимается с HP моба — FightOrchestrator сам проверит hp_now<=0
     * и обработает смерть моба тем же путём, что и обычный удар игрока.
     * Заодно единственная точка, где блок засчитывается в навык щита.
     */
    private function applyShieldBlockReflect(Player $player, FightHitDTO $hit, MonsterOnLocation $locationMonster, AttackResultDTO $result): void
    {
        // До проверки отражения: блок мог сработать, но погасить 0 урона
        // (см. FightHitDTO::$blocked) — навык такой блок всё равно засчитывает
        $this->gainShieldSkill($player, $hit);

        $reflected = $hit->getReflectedDamage();

        if ($reflected <= 0) {
            return;
        }

        $locationMonster->hp_now = max(0, $locationMonster->hp_now - $reflected);

        $result->log(sprintf(
            '<p><b class="color-reflect">🛡 Щит заблокировал удар и отразил %d урона обратно!</b></p>',
            $reflected
        ));
    }

    /**
     * Навык щита («Владение щитом») качается ЗА УСПЕШНЫЙ БЛОК, а не за каждый
     * удар — в отличие от оружейных навыков, которые начисляются в
     * AttackService на каждом попадании игрока. Поэтому у щитов
     * share_items.skill_exp заметно выше, чем у оружия: блок срабатывает лишь
     * в части раундов (block_chance), и без компенсации навык качался бы в
     * несколько раз медленнее оружейного.
     *
     * Навык берётся с самого предмета-щита (skill_id/skill_exp), а не по
     * захардкоженному id — так же, как оружие передаёт свой навык через
     * FightHitDTO::getWeapon().
     */
    private function gainShieldSkill(Player $player, FightHitDTO $hit): void
    {
        if (! $hit->isBlocked()) {
            return;
        }

        $shield = $this->equippedShield($player);

        if (! $shield) {
            return;
        }

        $this->playerSkillService->gainExperienceSkill($player, $shield->skill, $shield);
    }

    /** Щит может быть в любой руке — ищем предмет типа SHIELD среди обеих */
    private function equippedShield(Player $player): ?ShareItem
    {
        $equip = $player->playerEquip;

        if (! $equip) {
            return null;
        }

        foreach ([$equip->handLeft, $equip->handRight] as $item) {
            $info = $item?->itemInfo;

            if ($info && $info->type === ShareItemType::SHIELD) {
                return $info;
            }
        }

        return null;
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

        $sheet = $this->statService->resolve($player);

        if ($useSpecialSkill) {
            $this->executeBossSpecialSkill(
                $player,
                $sheet,
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
                $sheet,
                $locationMonster,
                $minDmg,
                $maxDmg,
                $totalModifier,
                $result
            );
        }
    }

    private function executeBossNormalAttack(
        Player $player,
        StatSheet $sheet,
        MonsterOnLocation $locationMonster,
        int $minDmg,
        int $maxDmg,
        int $totalModifier,
        AttackResultDTO $result
    ): void {
        $monster = $locationMonster->monster;

        $isMagic = $monster->usesMagicAttack();
        $hit = $this->resolveHit($monster, $sheet, $minDmg, $maxDmg, $isMagic);

        if (! $isMagic && $hit->isDodge()) {
            $result->log(sprintf(
                '<p><b class="color-boss">%s</b> атакует неудачно... Вы <b class="color-green">увернулись</b></p>',
                $monster->name
            ));

            return;
        }

        if (! $isMagic) {
            $this->applyDefensiveRunePassives($player, $hit, $locationMonster, $result);
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $modifierText = $totalModifier > 0
            ? sprintf(' <span class="color-enrage">(урон +%d%%)</span>', $totalModifier)
            : '';

        $msg = $isMagic
            ? sprintf(
                '<p><b class="color-boss">%s</b> использует магическую атаку%s! <br>Повреждения: <b class="color-magic">%s</b></p>',
                $monster->name,
                $modifierText,
                $hit->getDamage(),
            )
            : ($hit->isCritical()
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
            ));

        $result->log($msg);

        if (! $isMagic) {
            $this->applyShieldBlockReflect($player, $hit, $locationMonster, $result);
        }

        $this->onHitEffectService->apply($player, $monster, $hit, $result, $sheet->getHpMax());
    }

    private function executeBossSpecialSkill(
        Player $player,
        StatSheet $sheet,
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
                $sheet,
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

        $attackType = MonsterAttackType::tryFrom((string) ($skill->parameters['damage_type'] ?? $monster->attack_type?->value))
            ?? MonsterAttackType::PHYSICAL;
        $isMagic = $attackType->isMagic();
        $powerCoefficient = (float) ($skill->parameters['magic_power_coefficient'] ?? $monster->magic_power_coefficient);
        $hit = $this->resolveHit($monster, $sheet, $skillMinDmg, $skillMaxDmg, $isMagic, $powerCoefficient);

        if (! $isMagic && $hit->isDodge()) {
            $result->log(sprintf(
                '<p><b class="color-boss">%s</b> использует <b>%s</b>, но вы <b class="color-green">увернулись</b>!</p>',
                $monster->name,
                $skill->skill_name ?? $randomSkill
            ));

            return;
        }

        if (! $isMagic) {
            $this->applyDefensiveRunePassives($player, $hit, $locationMonster, $result);
        }

        $player->hp_now = max(0, $player->hp_now - $hit->getDamage());

        $skillEmoji = $this->getSkillEmoji($randomSkill);

        $msg = $isMagic
            ? sprintf(
                '<p><b class="color-boss">%s</b> использует <b class="color-skill">%s %s</b>! <b class="color-magic">Магический урон: %s</b> (x%.1f)</p>',
                $monster->name,
                $skillEmoji,
                $skill->skill_name ?? $randomSkill,
                $hit->getDamage(),
                $damageMultiplier,
            )
            : ($hit->isCritical()
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
            ));

        $result->log($msg);

        if (! $isMagic) {
            $this->applyShieldBlockReflect($player, $hit, $locationMonster, $result);
        }

        $this->onHitEffectService->apply($player, $monster, $hit, $result, $sheet->getHpMax());

        if (isset($skill->parameters['effects'])) {
            $this->applySkillEffects($player, $battle, $skill->parameters['effects'], $monster->name, $result);
        }
    }

    /**
     * Магическая атака моба использует ту же формулу, что и заклинания игрока:
     * базовый диапазон + magic_attack × coefficient, затем magic_resistance
     * цели. Для неё не применяются уворот, крит и блок щитом.
     */
    private function resolveHit(
        Monster $monster,
        StatSheet $target,
        float|int $minDamage,
        float|int $maxDamage,
        bool $isMagic,
        ?float $powerCoefficient = null,
    ): FightHitDTO {
        if ($isMagic) {
            return $this->magicHitCalc->hit(
                $monster,
                $target,
                (int) round($minDamage),
                (int) round($maxDamage),
                $powerCoefficient ?? (float) $monster->magic_power_coefficient,
            );
        }

        return $this->hitCalc->hit($monster, $target, (int) round($minDamage), (int) round($maxDamage));
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
            $durationOrTicks = (int) ($effect['duration'] ?? $effect['stacks'] ?? 2);
            $value = (float) ($effect['value'] ?? 0);

            if ($type === null || random_int(1, 100) > $chance) {
                continue;
            }

            $this->effectService->applyCustomEffectToPlayer(
                $type,
                $value,
                $durationOrTicks,
                $player,
                $battle,
                $result
            );
        }
    }
}
