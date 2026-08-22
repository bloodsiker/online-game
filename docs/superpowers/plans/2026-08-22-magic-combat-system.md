# Magic Combat System Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Turn the ad-hoc magic-attack code from the previous session into a full, symmetric combat subsystem: its own damage/resistance formula (no physical dodge/crit/armor), magic resistance from Wisdom, book-based spell learning with a single learn-time requirement gate, an atomic mana/cooldown cast guard, and the four remaining skill types (DoT, heal, buff, debuff) wired through the existing `Effect` model.

**Architecture:** New `MagicHitCalculator` (pure formula class, mirrors `HitCalculator` but with no dodge/crit/shield-block) computes both damage and heal. A new `MonsterCombatant` DTO wraps a `Monster` species model plus that specific `MonsterOnLocation`'s active debuffs, letting monster-targeted debuffs actually affect combat without touching the species row. Spell acquisition moves from direct seeder-attach to a `share_item` of type `BOOK` linked 1:1 via `magic_skill_books`, consumed by a new `LearnMagicSkillFromBook` use case — the only place `MagicSkillRequirementService::check()` runs. A new `MagicCastGuard` centralizes the mana-deduct + cooldown-set step behind a DB lock for both the in-battle attack path and the out-of-battle buff/heal path.

**Tech Stack:** Laravel 11 (PHP 8.3, `declare(strict_types=1)`), MySQL via `docker exec onlinegame-php-www-1`, PHPUnit (`tests/Unit`, `tests/Feature`), Laravel Pint.

## Global Constraints

- Strict types on every new/modified file: `declare(strict_types=1);`. Several legacy files this plan modifies (`Combat/Strategies/*.php`, `HitCalculator`-adjacent files) predate this convention and currently lack it — add the declaration when editing them, even though the task's own code sample may omit it for brevity. Do not add it to files you are not otherwise touching in this task.
- Named arguments for clarity; `match` over `switch`; typed arrays/collections, never bare `array` typehints.
- Magic never dodges, never crits, never gets blocked by a shield — only `magic_resistance` mitigates it (spec rule 1).
- `magic_skill_requirements` is checked **only** inside `LearnMagicSkillFromBook` — never at equip time (spec rule 3).
- A DoT's per-tick damage is computed once via `MagicHitCalculator` at cast time and stored; ticks never re-roll or re-resolve resistance (spec rule 2).
- Run `php -l <file>` on every PHP file after editing, and `./vendor/bin/pint <file>` before considering a task done, matching this repo's existing convention.
- Every migration/seeder DB write must be run against the `game` database inside the `onlinegame-php-www-1` container — verify with `docker exec onlinegame-php-www-1 printenv DB_DATABASE` before running anything destructive.
- Source spec: `docs/superpowers/specs/2026-08-22-magic-combat-system-design.md` — re-read it if a task's rationale is unclear.

---

### Task 1: Revert the previous session's incorrect patches

**Files:**
- Modify: `app/Modules/Player/Domain/Services/PlayerStatFormulas.php:34-38, 66-76`
- Modify: `app/Modules/Player/Domain/Services/PlayerStatService.php:129`
- Modify: `app/Modules/MagicSkill/Application/UseCases/UpdateEquippedMagicSkills.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php:54-59`
- Test: `tests/Unit/Modules/Player/PlayerStatFormulasTest.php` (new)

**Interfaces:**
- Produces: `PlayerStatFormulas` with `intelligenceDamagePercent()` and `DMG_PCT_PER_INT` removed (fully superseded by `MagicHitCalculator` in Task 3 — dead code otherwise, per project convention of not keeping unused code).
- Produces: `UpdateEquippedMagicSkills::execute(User $user, array $equippedIds): MagicSkillActionResultDTO` back to a plain toggle, no requirement check.

This task undoes two design mistakes made before the spec existed: `magic_attack` was set equal to `intelligence` (it must stay 0, gear-only — Task 2 fixes this properly), and spell-equip started checking learn-time requirements (must only check at learn time — Task 8 fixes this properly). Do this first so later tasks build on a clean base.

- [ ] **Step 1: Remove the dead formula from `PlayerStatFormulas`**

Delete the `DMG_PCT_PER_INT` constant (lines 37-38) and the `intelligenceDamagePercent()` method (lines 66-76) from `app/Modules/Player/Domain/Services/PlayerStatFormulas.php`. Leave `strengthDamagePercent()` and everything else untouched.

- [ ] **Step 2: Revert the `magic_attack` base in `PlayerStatService`**

In `app/Modules/Player/Domain/Services/PlayerStatService.php`, change line 129 back to:

```php
            'magic_attack' => 0.0,
```

(Currently reads `'magic_attack' => (float) $primary['intelligence'],` — that line is removed.)

- [ ] **Step 3: Write the failing test for `PlayerStatFormulas`**

Create `tests/Unit/Modules/Player/PlayerStatFormulasTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Player\Domain\Services\PlayerStatFormulas;
use PHPUnit\Framework\TestCase;

class PlayerStatFormulasTest extends TestCase
{
    public function test_intelligence_damage_percent_method_no_longer_exists(): void
    {
        $this->assertFalse(
            method_exists(PlayerStatFormulas::class, 'intelligenceDamagePercent'),
            'intelligenceDamagePercent() was superseded by MagicHitCalculator::power_coefficient and must be removed, not left dead.'
        );
    }

    public function test_strength_damage_percent_still_works(): void
    {
        // Regression guard: Step 1 must not touch strengthDamagePercent().
        $this->assertGreaterThan(0.0, PlayerStatFormulas::strengthDamagePercent(50.0, 12));
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=PlayerStatFormulasTest`
Expected: PASS (2 tests) — if `test_intelligence_damage_percent_method_no_longer_exists` fails, Step 1 wasn't applied.

- [ ] **Step 5: Revert `UpdateEquippedMagicSkills` to a plain toggle**

Replace the full contents of `app/Modules/MagicSkill/Application/UseCases/UpdateEquippedMagicSkills.php` with:

```php
<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\MagicSkill\Application\DTOs\MagicSkillActionResultDTO;
use App\Modules\MagicSkill\Domain\Contracts\MagicSkillWriteRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class UpdateEquippedMagicSkills
{
    public function __construct(
        private readonly MagicSkillWriteRepository $repository,
        private readonly PlayerStatService $statService,
    ) {}

    public function execute(User $user, array $equippedIds): MagicSkillActionResultDTO
    {
        $player = $user->player;
        $oldSheet = $this->statService->resolve($player);

        $this->repository->syncEquippedSkills($player, $equippedIds);

        $player->refresh();
        $this->statService->invalidate($player);
        $newSheet = $this->statService->resolve($player);
        $this->statService->scaleHp($player, $oldSheet->getHpMax(), $newSheet->getHpMax(), $oldSheet->getMpMax(), $newSheet->getMpMax());

        $message = count($equippedIds) > 0
            ? 'Сохранено'
            : 'Сохранено. Не выбрано ни одного скилла';

        return new MagicSkillActionResultDTO(
            status: 'success',
            message: $message,
        );
    }
}
```

- [ ] **Step 6: Remove the now-unused `intelligenceDamagePercent()` call from `MagicAttackStrategy`**

In `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php`, replace lines 51-62 (the base-damage + intelligence-bonus + `hitCalc->hit()` block) with a placeholder that keeps the file compiling until Task 4 replaces it properly:

```php
        // Базовый урон от скилла (уворот бросается один раз — внутри hit() ниже)
        $baseDamage = random_int($this->magicSkill->min_damage, $this->magicSkill->max_damage);

        // TODO(Task 4): заменить на MagicHitCalculator — без уворота/крита/брони, см. спеку.
        $hit = $this->hitCalc->hit($this->player, $this->monster, $baseDamage, $baseDamage);
```

Also remove the now-unused `use App\Modules\Player\Domain\Services\PlayerStatFormulas;` import at the top of the file.

This `TODO` is intentionally temporary and is removed in Task 4, Step 3 — it exists only so the codebase compiles and passes lint between Task 1 and Task 4 within the same PR/branch.

- [ ] **Step 7: Lint and run the full magic-related test suite**

```bash
php -l app/Modules/Player/Domain/Services/PlayerStatFormulas.php
php -l app/Modules/Player/Domain/Services/PlayerStatService.php
php -l app/Modules/MagicSkill/Application/UseCases/UpdateEquippedMagicSkills.php
php -l app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php
docker exec onlinegame-php-www-1 php artisan test --filter=PlayerStatFormulasTest
```
Expected: no syntax errors, tests pass.

- [ ] **Step 8: Commit**

```bash
git add app/Modules/Player/Domain/Services/PlayerStatFormulas.php \
        app/Modules/Player/Domain/Services/PlayerStatService.php \
        app/Modules/MagicSkill/Application/UseCases/UpdateEquippedMagicSkills.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php \
        tests/Unit/Modules/Player/PlayerStatFormulasTest.php
git commit -m "fix(magic): revert equip-time requirement gate and intelligence-as-magic_attack patches"
```

---

### Task 2: Magic resistance stat — schema, derivation, interface

**Files:**
- Modify: `app/Modules/Share/Domain/Enums/ShareItemStatType.php`
- Modify: `app/Modules/Player/Domain/Services/PlayerStatFormulas.php`
- Modify: `app/Modules/Player/Domain/Services/PlayerStatService.php`
- Modify: `app/Modules/Player/Domain/DTO/StatSheet.php`
- Modify: `app/Modules/Battle/Domain/Contracts/FightHitInterface.php`
- Modify: `app/Modules/Monster/Infrastructure/Persistence/Models/Monster.php`
- Modify: `app/Modules/Battle/Presentation/Console/SimulateBattleTriangle.php`
- Test: `tests/Feature/Modules/Player/MagicResistanceDerivationTest.php` (new)

**Interfaces:**
- Produces: `FightHitInterface::getMagicResistance(): int` and `FightHitInterface::getMagicAttack(): int` — implemented by every existing implementer (`StatSheet`, `Monster`, `SimulateBattleTriangle`'s combatant class).
- Consumes: none (first new interface surface).

- [ ] **Step 1: Add `MAGIC_RESISTANCE` to `ShareItemStatType`**

In `app/Modules/Share/Domain/Enums/ShareItemStatType.php`, add a new case right after `MAGIC_ATTACK`:

```php
    case MAGIC_ATTACK = 'magic_attack';
    case MAGIC_RESISTANCE = 'magic_resistance';
```

And its label in the `label()` match, right after `MAGIC_ATTACK`'s:

```php
            self::MAGIC_ATTACK => 'Магическая атака',
            self::MAGIC_RESISTANCE => 'Магическое сопротивление',
```

- [ ] **Step 2: Add the derivation constant to `PlayerStatFormulas`**

In `app/Modules/Player/Domain/Services/PlayerStatFormulas.php`, add next to `ARMOR_PER_STR`:

```php
    public const ARMOR_PER_STR = 1;

    /** Магическое сопротивление от Мудрости — та же роль, что у Силы для брони, но по другой стате */
    public const MAGIC_RESIST_PER_WIS = 1;
```

- [ ] **Step 3: Add the derived stat in `PlayerStatService::buildSheet()`**

In `app/Modules/Player/Domain/Services/PlayerStatService.php`, in the `$derivedBase` array (the block starting at line 115 with `'dodge' => ...`), add a new entry right after `'armor'`:

```php
            'armor' => (float) max(0, ($primary['strength'] - 1) * PlayerStatFormulas::ARMOR_PER_STR),
            'magic_resistance' => (float) max(0, ($primary['wisdom'] - 1) * PlayerStatFormulas::MAGIC_RESIST_PER_WIS),
```

Then find where `$sheet->armor = $computed['armor'];` is assigned further down in the same method (near the other `$sheet->` assignments) and add right after it:

```php
        $sheet->magicResistance = $computed['magic_resistance'];
```

Finally, find the `ShareItemStatType::MAGIC_ATTACK => 'magic_attack',` mapping inside `fromEquipment()` (around line 260) and add a sibling line right after it:

```php
                    ShareItemStatType::MAGIC_ATTACK => 'magic_attack',
                    ShareItemStatType::MAGIC_RESISTANCE => 'magic_resistance',
```

This makes `magic_resistance` a proper derived-then-modified stat: base from Wisdom, then any `MAGIC_RESISTANCE`-type equipment stat adds on top via the existing generic modifier pipeline — exactly like `armor`.

- [ ] **Step 4: Add the property and getters to `StatSheet`**

In `app/Modules/Player/Domain/DTO/StatSheet.php`, add a new public property right after `public int $magicAttack = 0;`:

```php
    public int $magicAttack = 0;

    public int $magicResistance = 0;
```

And add two new getters in the "FightHitInterface" method block, right after `getArmor()`:

```php
    public function getArmor(): int
    {
        return $this->armor;
    }

    public function getMagicResistance(): int
    {
        return $this->magicResistance;
    }

    public function getMagicAttack(): int
    {
        return $this->magicAttack;
    }
```

- [ ] **Step 5: Add both methods to `FightHitInterface`**

In `app/Modules/Battle/Domain/Contracts/FightHitInterface.php`, right after the `getIntelligence()` method already added in the previous session, add:

```php
    /** Итоговый интеллект (с учётом экипировки) — масштабирует урон атакующих заклинаний, см. MagicAttackStrategy */
    public function getIntelligence(): int;

    /** Магическое сопротивление (Мудрость + экипировка) — единственная защита от магии, см. MagicHitCalculator */
    public function getMagicResistance(): int;

    /** Флэт-бонус к силе заклинаний ИСКЛЮЧИТЕЛЬНО с экипировки (посох/жезл и т.п.) — интеллект считается отдельно */
    public function getMagicAttack(): int;
```

- [ ] **Step 6: Implement both methods on `Monster`**

In `app/Modules/Monster/Infrastructure/Persistence/Models/Monster.php`, right after the `getIntelligence()` method added previously, add:

```php
    public function getIntelligence(): int
    {
        return 0;
    }

    public function getMagicResistance(): int
    {
        return 0;
    }

    public function getMagicAttack(): int
    {
        return 0;
    }
```

(Monsters get no magic resistance/attack of their own for now — out of scope per the spec's "Область, оставленная вне этой спеки". `MonsterCombatant` in Task 5 is what will eventually carry a real value if this is revisited.)

- [ ] **Step 7: Implement both methods on the `SimulateBattleTriangle` combatant class**

In `app/Modules/Battle/Presentation/Console/SimulateBattleTriangle.php`, right after the `getIntelligence()` method added previously, add:

```php
    public function getIntelligence(): int
    {
        return 0;
    }

    public function getMagicResistance(): int
    {
        return 0;
    }

    public function getMagicAttack(): int
    {
        return 0;
    }
```

- [ ] **Step 8: Write the failing Feature test for the derivation**

Create `tests/Feature/Modules/Player/MagicResistanceDerivationTest.php`, following the manual-sqlite-schema pattern already used in `tests/Feature/Modules/Battle/BossRespawnBatchQueryTest.php` (this repo does not use `RefreshDatabase` with the full migration set — every DB-touching Feature test builds its own minimal schema):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MagicResistanceDerivationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('lvl')->default(1);
            $table->float('strength')->default(1);
            $table->float('agility')->default(1);
            $table->float('intuition')->default(1);
            $table->float('wisdom')->default(1);
            $table->float('intelligence')->default(1);
            $table->float('endurance')->default(1);
            $table->integer('min_dmg')->default(0);
            $table->integer('max_dmg')->default(0);
            $table->integer('mp_max')->default(10);
            $table->integer('mp_now')->default(10);
            $table->integer('hp_now')->default(10);
            $table->unsignedInteger('free_stats')->default(0);
            $table->timestamps();
        });
    }

    public function test_magic_resistance_derives_from_wisdom_only(): void
    {
        $player = Player::create(['wisdom' => 21.0, 'lvl' => 12]);

        $sheet = app(PlayerStatService::class)->resolve($player);

        // (21 - 1) * MAGIC_RESIST_PER_WIS(1) = 20
        $this->assertSame(20, $sheet->getMagicResistance());
    }

    public function test_magic_attack_stays_zero_without_equipment(): void
    {
        $player = Player::create(['intelligence' => 999.0, 'wisdom' => 999.0, 'lvl' => 12]);

        $sheet = app(PlayerStatService::class)->resolve($player);

        $this->assertSame(0, $sheet->getMagicAttack(), 'magic_attack must stay gear-only — intelligence must not leak into it (see Task 1).');
    }
}
```

- [ ] **Step 9: Run the test, fix any missing dependency until it passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MagicResistanceDerivationTest`
Expected: PASS (2 tests). If `PlayerStatService::resolve()` touches other tables (equipment, effects) not stubbed here, extend `setUp()` with empty/minimal versions of those tables rather than switching to full migrations — follow `BossRespawnBatchQueryTest`'s minimalism.

- [ ] **Step 10: Lint and commit**

```bash
php -l app/Modules/Share/Domain/Enums/ShareItemStatType.php
php -l app/Modules/Player/Domain/Services/PlayerStatFormulas.php
php -l app/Modules/Player/Domain/Services/PlayerStatService.php
php -l app/Modules/Player/Domain/DTO/StatSheet.php
php -l app/Modules/Battle/Domain/Contracts/FightHitInterface.php
php -l app/Modules/Monster/Infrastructure/Persistence/Models/Monster.php
php -l app/Modules/Battle/Presentation/Console/SimulateBattleTriangle.php
./vendor/bin/pint app/Modules/Share/Domain/Enums/ShareItemStatType.php app/Modules/Player/Domain/Services/PlayerStatFormulas.php app/Modules/Player/Domain/Services/PlayerStatService.php app/Modules/Player/Domain/DTO/StatSheet.php app/Modules/Battle/Domain/Contracts/FightHitInterface.php app/Modules/Monster/Infrastructure/Persistence/Models/Monster.php app/Modules/Battle/Presentation/Console/SimulateBattleTriangle.php
docker exec onlinegame-php-www-1 php artisan test --filter=MagicResistanceDerivationTest

git add app/Modules/Share/Domain/Enums/ShareItemStatType.php \
        app/Modules/Player/Domain/Services/PlayerStatFormulas.php \
        app/Modules/Player/Domain/Services/PlayerStatService.php \
        app/Modules/Player/Domain/DTO/StatSheet.php \
        app/Modules/Battle/Domain/Contracts/FightHitInterface.php \
        app/Modules/Monster/Infrastructure/Persistence/Models/Monster.php \
        app/Modules/Battle/Presentation/Console/SimulateBattleTriangle.php \
        tests/Feature/Modules/Player/MagicResistanceDerivationTest.php
git commit -m "feat(magic): add magic_resistance stat derived from wisdom + gear-only magic_attack getter"
```

---

### Task 3: `MagicHitCalculator` — the core damage and heal formula

**Files:**
- Create: `app/Modules/Battle/Application/Services/Combat/MagicHitCalculator.php`
- Test: `tests/Unit/Modules/Battle/MagicHitCalculatorTest.php` (new)

**Interfaces:**
- Consumes: `FightHitInterface::getIntelligence()`, `getMagicAttack()`, `getMagicResistance()`, `getLevel()` (Task 2). `FightHitDTO::setDamage(int)` (already exists).
- Produces: `MagicHitCalculator::hit(FightHitInterface $attacker, FightHitInterface $defender, int $minDamage, int $maxDamage, float $powerCoefficient): FightHitDTO` and `MagicHitCalculator::heal(FightHitInterface $caster, int $minHeal, int $maxHeal, float $powerCoefficient): int` — both used by Task 4 (attack), Task 10 (DoT tick), Task 11 (heal).

- [ ] **Step 1: Write the failing unit test**

Create `tests/Unit/Modules/Battle/MagicHitCalculatorTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use PHPUnit\Framework\TestCase;

final class StubCombatant implements FightHitInterface
{
    public function __construct(
        private int $level = 12,
        private int $intelligence = 0,
        private int $magicAttack = 0,
        private int $magicResistance = 0,
    ) {}

    public function getCritical(): int { return 0; }
    public function getDodge(): int { return 0; }
    public function getArmor(): int { return 0; }
    public function getCombatClass(): CombatClass { return CombatClass::TANK; }
    public function getClassShare(CombatClass $class): float { return 1 / 3; }
    public function getCritDamage(): int { return 175; }
    public function getLevel(): int { return $this->level; }
    public function getBlockChance(): int { return 0; }
    public function getBlockFlat(): int { return 0; }
    public function getBlockPercent(): int { return 0; }
    public function getIntelligence(): int { return $this->intelligence; }
    public function getMagicResistance(): int { return $this->magicResistance; }
    public function getMagicAttack(): int { return $this->magicAttack; }
}

class MagicHitCalculatorTest extends TestCase
{
    public function test_damage_with_no_intelligence_or_resistance_equals_base_roll(): void
    {
        $calc = new MagicHitCalculator;
        $attacker = new StubCombatant(level: 12);
        $defender = new StubCombatant(level: 12);

        $hit = $calc->hit($attacker, $defender, minDamage: 10, maxDamage: 10, powerCoefficient: 0.3);

        $this->assertSame(10, $hit->getDamage());
        $this->assertFalse($hit->isDodge());
        $this->assertFalse($hit->isCritical());
    }

    public function test_intelligence_and_magic_attack_both_add_to_raw_damage(): void
    {
        $calc = new MagicHitCalculator;
        // magic_power = 21 (intelligence) + 9 (gear) = 30; 30 * 0.3 = 9 bonus
        $attacker = new StubCombatant(level: 12, intelligence: 21, magicAttack: 9);
        $defender = new StubCombatant(level: 12);

        $hit = $calc->hit($attacker, $defender, minDamage: 10, maxDamage: 10, powerCoefficient: 0.3);

        $this->assertSame(19, $hit->getDamage());
    }

    public function test_magic_resistance_mitigates_like_armor_softcap(): void
    {
        $calc = new MagicHitCalculator;
        $attacker = new StubCombatant(level: 12);
        // damageMultiplier = 220 / (220 + 220) = 0.5 at reference level 12
        $defender = new StubCombatant(level: 12, magicResistance: 220);

        $hit = $calc->hit($attacker, $defender, minDamage: 100, maxDamage: 100, powerCoefficient: 0.3);

        $this->assertSame(50, $hit->getDamage());
    }

    public function test_damage_never_drops_below_one(): void
    {
        $calc = new MagicHitCalculator;
        $attacker = new StubCombatant(level: 12);
        $defender = new StubCombatant(level: 12, magicResistance: 100000);

        $hit = $calc->hit($attacker, $defender, minDamage: 1, maxDamage: 1, powerCoefficient: 0.0);

        $this->assertSame(1, $hit->getDamage());
    }

    public function test_heal_ignores_target_resistance_entirely(): void
    {
        $calc = new MagicHitCalculator;
        $caster = new StubCombatant(level: 12, intelligence: 21);

        // magic_power = 21 * 0.4 = 8.4 -> round 8; base 50 -> 58
        $healed = $calc->heal($caster, minHeal: 50, maxHeal: 50, powerCoefficient: 0.4);

        $this->assertSame(58, $healed);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MagicHitCalculatorTest`
Expected: FAIL — class `MagicHitCalculator` not found.

- [ ] **Step 3: Implement `MagicHitCalculator`**

Create `app/Modules/Battle/Application/Services/Combat/MagicHitCalculator.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Battle\Application\Services\Combat;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;

/**
 * Отдельный урон-калькулятор для магии — НЕ переиспользует HitCalculator::hit().
 * Магия не уворачивается, не критует и не блокируется щитом (см. спеку
 * docs/superpowers/specs/2026-08-22-magic-combat-system-design.md, правило 1) —
 * единственная защита цели — magic_resistance.
 */
readonly class MagicHitCalculator
{
    /**
     * Уровень, на котором калибруется знаменатель сопротивления — то же значение
     * и та же логика масштабирования, что у HitCalculator::ARMOR_CONSTANT.
     */
    private const REFERENCE_LEVEL = 12.0;

    /** Знаменатель формулы резиста на референсном уровне: damageMultiplier = A/(A+resist) */
    private const MAGIC_RESIST_CONSTANT = 220.0;

    public function hit(
        FightHitInterface $attacker,
        FightHitInterface $defender,
        int $minDamage,
        int $maxDamage,
        float $powerCoefficient,
    ): FightHitDTO {
        $dto = new FightHitDTO;

        $rolled = random_int(min($minDamage, $maxDamage), max($minDamage, $maxDamage));
        $rawDamage = $rolled + (int) round($this->magicPower($attacker) * $powerCoefficient);

        $resistConstant = self::MAGIC_RESIST_CONSTANT * $this->levelScale($attacker, $defender);
        $damageMultiplier = $resistConstant / ($resistConstant + $defender->getMagicResistance());

        $final = max(1, (int) round($rawDamage * $damageMultiplier));

        return $dto->setDamage($final);
    }

    /** Лечение не резистится целью — сила от кастера, как и урон, но без шага митигации. */
    public function heal(
        FightHitInterface $caster,
        int $minHeal,
        int $maxHeal,
        float $powerCoefficient,
    ): int {
        $rolled = random_int(min($minHeal, $maxHeal), max($minHeal, $maxHeal));

        return $rolled + (int) round($this->magicPower($caster) * $powerCoefficient);
    }

    private function magicPower(FightHitInterface $caster): float
    {
        return (float) ($caster->getIntelligence() + $caster->getMagicAttack());
    }

    private function levelScale(FightHitInterface $attacker, FightHitInterface $defender): float
    {
        $avgLevel = ($attacker->getLevel() + $defender->getLevel()) / 2;

        return max(1.0, $avgLevel / self::REFERENCE_LEVEL);
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MagicHitCalculatorTest`
Expected: PASS (5 tests).

- [ ] **Step 5: Lint and commit**

```bash
php -l app/Modules/Battle/Application/Services/Combat/MagicHitCalculator.php
./vendor/bin/pint app/Modules/Battle/Application/Services/Combat/MagicHitCalculator.php tests/Unit/Modules/Battle/MagicHitCalculatorTest.php

git add app/Modules/Battle/Application/Services/Combat/MagicHitCalculator.php tests/Unit/Modules/Battle/MagicHitCalculatorTest.php
git commit -m "feat(magic): add MagicHitCalculator — dedicated damage/heal formula, no dodge/crit/block"
```

---

### Task 4: `power_coefficient` column + wire `MagicHitCalculator` into `MagicAttackStrategy`

**Files:**
- Create: `database/migrations/2026_08_22_130000_add_power_coefficient_to_magic_skills.php`
- Modify: `app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkill.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php`
- Test: `tests/Feature/Modules/Battle/MagicAttackStrategyTest.php` (new)

**Interfaces:**
- Consumes: `MagicHitCalculator::hit()` (Task 3).
- Produces: `MagicSkill::$power_coefficient` (float, fillable); `MagicAttackStrategy` constructed with `MagicHitCalculator $magicHitCalc` instead of relying on `HitCalculator` for damage.

- [ ] **Step 1: Create and run the migration**

Create `database/migrations/2026_08_22_130000_add_power_coefficient_to_magic_skills.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('magic_skills', function (Blueprint $table) {
            $table->float('power_coefficient')->default(0.0)->after('max_damage');
        });
    }

    public function down(): void
    {
        Schema::table('magic_skills', function (Blueprint $table) {
            $table->dropColumn('power_coefficient');
        });
    }
};
```

Run:
```bash
docker exec onlinegame-php-www-1 printenv DB_DATABASE
docker exec onlinegame-php-www-1 php artisan migrate --path=database/migrations/2026_08_22_130000_add_power_coefficient_to_magic_skills.php --force
```
Expected: `DB_DATABASE` prints `game`, migration runs `DONE`.

- [ ] **Step 2: Add `power_coefficient` to the `MagicSkill` model's fillable**

In `app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkill.php`, update `$fillable`:

```php
    protected $fillable = [
        'name', 'slug', 'description', 'level', 'skill_id', 'type', 'mana_cost',
        'min_damage', 'max_damage', 'power_coefficient', 'base_healing', 'cooldown', 'target_type',
        'is_passive', 'effects',
    ];
```

- [ ] **Step 3: Replace the damage calculation in `MagicAttackStrategy`**

Replace the full contents of `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php` with:

```php
<?php

namespace App\Modules\Battle\Application\Services\Combat\Strategies;

use App\Modules\Battle\Application\DTOs\FightHitDTO;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

class MagicAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private MagicHitCalculator $magicHitCalc,
        private FightHitInterface $player,     // StatSheet с полными рассчитанными статами
        private Player $playerModel, // Player model для чтения/записи mp_now
        private FightHitInterface $monster,
        private MagicSkill $magicSkill,
    ) {}

    public function getHits(): array
    {
        if (! $this->magicSkill instanceof MagicSkill) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage('Заклинание не изучено или отключено'),
            ];
        }

        if (! $this->magicSkill->isAttackSkill()) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage('Это не атакующее заклинание'),
            ];
        }

        if ($this->playerModel->mp_now < $this->magicSkill->mana_cost) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage(sprintf('Недостаточно маны, требуется %s', $this->magicSkill->mana_cost)),
            ];
        }

        $this->playerModel->mp_now -= $this->magicSkill->mana_cost;

        // Магия не уворачивается и не критует (см. спеку, правило 1) — сразу считаем финальный урон.
        $hit = $this->magicHitCalc->hit(
            $this->player,
            $this->monster,
            $this->magicSkill->min_damage,
            $this->magicSkill->max_damage,
            $this->magicSkill->power_coefficient,
        );

        foreach ($this->magicSkill->skillEffects as $effectData) {
            if (random_int(1, 100) <= $effectData->pivot->chance) {
                $hit->addAppliedEffect($effectData, tickValue: $hit->getDamage());
            }
        }

        return [
            $hit
                ->setMagicSkill($this->magicSkill)
                ->setWeaponName(sprintf('заклинанием «%s»', $this->magicSkill->name))
                ->setWeapon(null)
                ->setSkill($this->magicSkill->skill),
        ];
    }
}
```

Note: `addAppliedEffect(Effect $effect, ?int $tickValue = null)` doesn't exist yet — that's Task 10, Step 1. This file will not compile between now and Task 10 unless Task 10 is done first, or you temporarily call `addAppliedEffect($effectData)` (single-arg) here and revisit in Task 10. **Do Task 10 immediately after this task if working sequentially, or use the single-arg call for now and grep for `TODO(Task 10)` before shipping.** Mark the call with a comment either way:

```php
                $hit->addAppliedEffect($effectData, tickValue: $hit->getDamage()); // TODO(Task 10): requires FightHitDTO::addAppliedEffect($effect, ?int $tickValue) signature
```

- [ ] **Step 4: Update `AttackStrategyResolver` to inject `MagicHitCalculator`**

In `app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php`:

Add the import:
```php
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
```

Add it to the constructor:
```php
    public function __construct(
        private HitCalculator $hitCalc,
        private MagicHitCalculator $magicHitCalc,
        private PlayerMagicSkillService $playerMagicSkillService,
        private PlayerStatService $statService,
    ) {}
```

And update the `MagicAttackStrategy` construction inside `resolve()`:
```php
            return new MagicAttackStrategy(
                magicHitCalc: $this->magicHitCalc,
                player: $sheet,
                playerModel: $player,
                monster: $monster,
                magicSkill: $playerSkill
            );
```

(`$monster` here is still the plain `Monster` species param at this point in the plan — Task 5 changes this resolver to take `MonsterOnLocation` and build a `MonsterCombatant` instead. Leave the resolver's own `Monster $monster` parameter type alone in this task; Task 5 changes it.)

- [ ] **Step 5: Write the Feature test for the wired strategy**

Create `tests/Feature/Modules/Battle/MagicAttackStrategyTest.php`. This test constructs the strategy directly (no HTTP layer, no DB) using the `StubCombatant` from Task 3's test — copy it into a shared location instead of duplicating:

First, move `StubCombatant` out of the test file into a reusable test double: create `tests/Support/StubCombatant.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;

final class StubCombatant implements FightHitInterface
{
    public function __construct(
        private int $level = 12,
        private int $intelligence = 0,
        private int $magicAttack = 0,
        private int $magicResistance = 0,
    ) {}

    public function getCritical(): int { return 0; }
    public function getDodge(): int { return 0; }
    public function getArmor(): int { return 0; }
    public function getCombatClass(): CombatClass { return CombatClass::TANK; }
    public function getClassShare(CombatClass $class): float { return 1 / 3; }
    public function getCritDamage(): int { return 175; }
    public function getLevel(): int { return $this->level; }
    public function getBlockChance(): int { return 0; }
    public function getBlockFlat(): int { return 0; }
    public function getBlockPercent(): int { return 0; }
    public function getIntelligence(): int { return $this->intelligence; }
    public function getMagicResistance(): int { return $this->magicResistance; }
    public function getMagicAttack(): int { return $this->magicAttack; }
}
```

Update `tests/Unit/Modules/Battle/MagicHitCalculatorTest.php` to `use Tests\Support\StubCombatant;` and delete the inline class definition from that file (keep only the `MagicHitCalculatorTest` class).

Now create `tests/Feature/Modules/Battle/MagicAttackStrategyTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicAttackStrategy;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StubCombatant;
use Tests\TestCase;

class MagicAttackStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->integer('mp_now')->default(10);
            $table->timestamps();
        });
    }

    public function test_insufficient_mana_returns_cant_cast_without_deducting(): void
    {
        $player = Player::create(['mp_now' => 2]);
        $skill = new MagicSkill([
            'type' => 'attack', 'is_passive' => false, 'target_type' => 'enemy',
            'mana_cost' => 8, 'min_damage' => 4, 'max_damage' => 7, 'power_coefficient' => 0.3,
            'cooldown' => 0,
        ]);
        $skill->setRelation('skillEffects', collect());

        $strategy = new MagicAttackStrategy(
            magicHitCalc: new MagicHitCalculator,
            player: new StubCombatant,
            playerModel: $player,
            monster: new StubCombatant,
            magicSkill: $skill,
        );

        $hits = $strategy->getHits();

        $this->assertTrue($hits[0]->isCantCast());
        $this->assertSame(2, $player->mp_now, 'mana must not be touched when the cast fails');
    }

    public function test_successful_cast_deducts_mana_and_deals_damage(): void
    {
        $player = Player::create(['mp_now' => 10]);
        $skill = new MagicSkill([
            'type' => 'attack', 'is_passive' => false, 'target_type' => 'enemy',
            'mana_cost' => 8, 'min_damage' => 5, 'max_damage' => 5, 'power_coefficient' => 0.0,
            'cooldown' => 0,
        ]);
        $skill->setRelation('skillEffects', collect());

        $strategy = new MagicAttackStrategy(
            magicHitCalc: new MagicHitCalculator,
            player: new StubCombatant,
            playerModel: $player,
            monster: new StubCombatant,
            magicSkill: $skill,
        );

        $hits = $strategy->getHits();

        $this->assertSame(5, $hits[0]->getDamage());
        $this->assertSame(2, $player->mp_now);
        $this->assertFalse($hits[0]->isDodge());
        $this->assertFalse($hits[0]->isCritical());
    }
}
```

- [ ] **Step 6: Run the tests**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MagicAttackStrategyTest`
Expected: PASS (2 tests). If `addAppliedEffect(..., tickValue: ...)` from Step 3 breaks compilation because Task 10 hasn't run yet, temporarily change that one line to single-arg `addAppliedEffect($effectData)` to unblock this task, and fix it for real in Task 10.

- [ ] **Step 7: Lint and commit**

```bash
php -l app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php
php -l app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php
php -l app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkill.php
./vendor/bin/pint app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkill.php tests/Feature/Modules/Battle/MagicAttackStrategyTest.php tests/Support/StubCombatant.php tests/Unit/Modules/Battle/MagicHitCalculatorTest.php
docker exec onlinegame-php-www-1 php artisan test --filter=MagicHitCalculatorTest,MagicAttackStrategyTest

git add database/migrations/2026_08_22_130000_add_power_coefficient_to_magic_skills.php \
        app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkill.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php \
        app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php \
        tests/Feature/Modules/Battle/MagicAttackStrategyTest.php \
        tests/Support/StubCombatant.php \
        tests/Unit/Modules/Battle/MagicHitCalculatorTest.php
git commit -m "feat(magic): wire MagicHitCalculator into MagicAttackStrategy, add power_coefficient column"
```

---

### Task 5: `MonsterCombatant` — makes monster-targeted debuffs actually affect combat

**Files:**
- Create: `app/Modules/Monster/Domain/DTO/MonsterCombatant.php`
- Create: `app/Modules/Monster/Domain/Services/MonsterCombatantFactory.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/AttackService.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/FistAttackStrategy.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/OneHandWeaponStrategy.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/DualWieldStrategy.php`
- Test: `tests/Unit/Modules/Monster/MonsterCombatantTest.php` (new)

**Interfaces:**
- Produces: `MonsterCombatantFactory::build(MonsterOnLocation $locMonster): MonsterCombatant`, `MonsterCombatant implements FightHitInterface`.
- Consumes: `MonsterActiveEffect` (existing table), `Effect::$stat_modifiers` (existing JSON column, shape `[['type' => 'armor'|'dodge', 'value' => float, 'is_percent' => bool], ...]`).

This is a defensive-stats-only wrapper: `armor` and `dodge` get reduced by any active debuff on that specific `MonsterOnLocation` instance; everything else delegates straight to the species `Monster` model. Attack strategies currently type-hint their `monster` constructor param as the concrete `Monster` class but only ever call `FightHitInterface` methods on it (verified: no `->name`, `->id`, or other Monster-specific field access inside any Strategy file) — so widening those type hints to `FightHitInterface` is a safe, mechanical change.

- [ ] **Step 1: Write the failing unit test**

Create `tests/Unit/Modules/Monster/MonsterCombatantTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Monster;

use App\Modules\Monster\Domain\DTO\MonsterCombatant;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use PHPUnit\Framework\TestCase;

class MonsterCombatantTest extends TestCase
{
    public function test_debuff_reduces_armor_but_not_below_zero(): void
    {
        $monster = new Monster(['armor' => 10, 'dodge' => 5, 'critical' => 0, 'lvl' => 20]);
        $combatant = new MonsterCombatant($monster, ['armor' => -15.0, 'dodge' => -2.0]);

        $this->assertSame(0, $combatant->getArmor(), 'armor must clamp at 0, not go negative');
        $this->assertSame(3, $combatant->getDodge());
    }

    public function test_no_debuffs_passes_through_species_stats_unchanged(): void
    {
        $monster = new Monster(['armor' => 40, 'dodge' => 12, 'critical' => 8, 'lvl' => 30]);
        $combatant = new MonsterCombatant($monster, []);

        $this->assertSame(40, $combatant->getArmor());
        $this->assertSame(12, $combatant->getDodge());
        $this->assertSame(8, $combatant->getCritical());
        $this->assertSame(30, $combatant->getLevel());
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MonsterCombatantTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement `MonsterCombatant`**

Create `app/Modules/Monster/Domain/DTO/MonsterCombatant.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\DTO;

use App\Modules\Battle\Domain\Contracts\FightHitInterface;
use App\Modules\Battle\Domain\Enums\CombatClass;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;

/**
 * Обёртка над видовым Monster + активные дебаффы конкретного MonsterOnLocation.
 * Существует потому, что Monster — видовая модель (статы одни на всех особей на
 * карте), а debuff применяется к конкретной боевой копии (MonsterOnLocation) —
 * без этой обёртки дебафф физически негде хранить эффект на бой.
 *
 * Затрагивает только защитные статы (armor/dodge) — см. спеку, стартовый набор
 * заклинаний ограничен дебаффом брони/уворота.
 */
final readonly class MonsterCombatant implements FightHitInterface
{
    /** @param  array<string, float>  $statModifierTotals  Суммарные флэт-дебаффы по стате, напр. ['armor' => -15.0] */
    public function __construct(
        private Monster $monster,
        private array $statModifierTotals = [],
    ) {}

    public function getArmor(): int
    {
        return max(0, $this->monster->getArmor() + (int) round($this->statModifierTotals['armor'] ?? 0));
    }

    public function getDodge(): int
    {
        return max(0, $this->monster->getDodge() + (int) round($this->statModifierTotals['dodge'] ?? 0));
    }

    public function getCritical(): int
    {
        return $this->monster->getCritical();
    }

    public function getCombatClass(): CombatClass
    {
        return $this->monster->getCombatClass();
    }

    public function getClassShare(CombatClass $class): float
    {
        return $this->monster->getClassShare($class);
    }

    public function getCritDamage(): int
    {
        return $this->monster->getCritDamage();
    }

    public function getLevel(): int
    {
        return $this->monster->getLevel();
    }

    public function getBlockChance(): int
    {
        return $this->monster->getBlockChance();
    }

    public function getBlockFlat(): int
    {
        return $this->monster->getBlockFlat();
    }

    public function getBlockPercent(): int
    {
        return $this->monster->getBlockPercent();
    }

    public function getIntelligence(): int
    {
        return $this->monster->getIntelligence();
    }

    public function getMagicResistance(): int
    {
        return $this->monster->getMagicResistance();
    }

    public function getMagicAttack(): int
    {
        return $this->monster->getMagicAttack();
    }
}
```

Check `app/Modules/Monster/Infrastructure/Persistence/Models/Monster.php` for the exact names of `getCombatClass()`, `getClassShare()`, `getCritDamage()`, `getBlockChance()`, `getBlockFlat()`, `getBlockPercent()` before writing this — copy them verbatim, don't guess. If any of those methods don't exist on `Monster` yet (i.e. `Monster` doesn't fully implement `FightHitInterface` today, or implements it via different method bodies), read the current file first and adjust the delegation calls to match reality — `Monster implements FightHitInterface` is stated as fact in the spec's "Что уже есть" section, so all of these must already exist somewhere in that file.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MonsterCombatantTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Implement `MonsterCombatantFactory`**

Create `app/Modules/Monster/Domain/Services/MonsterCombatantFactory.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Services;

use App\Modules\Monster\Domain\DTO\MonsterCombatant;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;

class MonsterCombatantFactory
{
    /** @var list<string> статы, которые дебафф умеет снижать в этом релизе — см. спеку */
    private const DEBUFFABLE_STATS = ['armor', 'dodge'];

    public function build(MonsterOnLocation $locMonster): MonsterCombatant
    {
        $totals = array_fill_keys(self::DEBUFFABLE_STATS, 0.0);

        $activeEffects = MonsterActiveEffect::query()
            ->where('location_monster_id', $locMonster->id)
            ->with('effect')
            ->get();

        foreach ($activeEffects as $active) {
            foreach ((array) ($active->effect?->stat_modifiers ?? []) as $modifier) {
                $type = $modifier['type'] ?? null;

                if (is_string($type) && array_key_exists($type, $totals) && ! ($modifier['is_percent'] ?? false)) {
                    $totals[$type] += (float) ($modifier['value'] ?? 0);
                }
            }
        }

        return new MonsterCombatant($locMonster->monster, $totals);
    }
}
```

Check `app/Modules/Monster/Infrastructure/Persistence/Models/MonsterActiveEffect.php` for the exact relation name to `Effect` (likely `effect()`) before writing `->with('effect')` — read the file first if unsure.

- [ ] **Step 6: Widen the `Monster` type hint to `FightHitInterface` in the three physical strategies**

In each of `FistAttackStrategy.php`, `OneHandWeaponStrategy.php`, `DualWieldStrategy.php` (all in `app/Modules/Battle/Application/Services/Combat/Strategies/`), find the constructor parameter typed `Monster $monster` and change it to `FightHitInterface $monster`. Remove the now-unused `use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;` import from each file if nothing else in that file references `Monster` by name (check with `grep -n "Monster" <file>` first — if the only remaining reference is the removed constructor type, delete the import; otherwise leave it).

`MagicAttackStrategy` already takes `FightHitInterface $monster` after Task 4 — no change needed there.

- [ ] **Step 7: Update `AttackStrategyResolver` to build and pass `MonsterCombatant`**

In `app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php`:

Add imports:
```php
use App\Modules\Monster\Domain\Services\MonsterCombatantFactory;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
```

Add `MonsterCombatantFactory` to the constructor:
```php
    public function __construct(
        private HitCalculator $hitCalc,
        private MagicHitCalculator $magicHitCalc,
        private MonsterCombatantFactory $combatantFactory,
        private PlayerMagicSkillService $playerMagicSkillService,
        private PlayerStatService $statService,
    ) {}
```

Change the `resolve()` signature from `Monster $monster` to `MonsterOnLocation $locMonster`, and build the combatant as the very first line inside the method body:

```php
    public function resolve(Player $player, MonsterOnLocation $locMonster, int $action): AttackStrategyInterface
    {
        $monster = $this->combatantFactory->build($locMonster);
        $sheet = $this->statService->resolve($player);
        // ... rest of the method body unchanged — every existing "new XStrategy(..., monster: $monster, ...)"
        // call already reads correctly, since $monster now holds a MonsterCombatant instead of the raw species Monster.
```

Everywhere else in the method body that currently reads `$monster` stays exactly as-is — only the type of what `$monster` refers to changes (`MonsterCombatant` instead of `Monster` species). Remove the now-unused `use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;` import if nothing else in the file references it.

- [ ] **Step 8: Update the one caller of `resolve()`**

In `app/Modules/Battle/Application/Services/Combat/AttackService.php`, find the line:
```php
        $strategy = $this->resolver->resolve($player, $locMonster->monster, $action);
```
and change it to:
```php
        $strategy = $this->resolver->resolve($player, $locMonster, $action);
```

Every other use of `$locMonster` and `$locMonster->monster` in `AttackService.php` (hp tracking, monster name in log messages, `isBoss()` checks) stays untouched — those still need the real model, only the strategy-construction call changes.

- [ ] **Step 9: Run the full battle test suite**

```bash
docker exec onlinegame-php-www-1 php artisan test --filter=MonsterCombatantTest,MagicAttackStrategyTest,MagicHitCalculatorTest
```
Expected: all PASS.

Then manually smoke-test a real fight in tinker to catch anything the unit tests can't (a live monster on a live location, one physical attack):

```bash
docker exec onlinegame-php-www-1 php artisan tinker --execute="
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Battle\Application\Services\Combat\AttackStrategyResolver;

\$player = Player::find(1);
\$locMonster = MonsterOnLocation::whereHas('monster')->first();
\$resolver = app(AttackStrategyResolver::class);
\$strategy = \$resolver->resolve(\$player, \$locMonster, 0);
echo get_class(\$strategy) . PHP_EOL;
\$hits = \$strategy->getHits();
echo 'damage=' . \$hits[0]->getDamage() . PHP_EOL;
"
```
Expected: no fatal errors, a strategy class name and a damage number print.

- [ ] **Step 10: Lint and commit**

```bash
php -l app/Modules/Monster/Domain/DTO/MonsterCombatant.php
php -l app/Modules/Monster/Domain/Services/MonsterCombatantFactory.php
php -l app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php
php -l app/Modules/Battle/Application/Services/Combat/AttackService.php
php -l app/Modules/Battle/Application/Services/Combat/Strategies/FistAttackStrategy.php
php -l app/Modules/Battle/Application/Services/Combat/Strategies/OneHandWeaponStrategy.php
php -l app/Modules/Battle/Application/Services/Combat/Strategies/DualWieldStrategy.php
./vendor/bin/pint app/Modules/Monster/Domain/DTO/MonsterCombatant.php app/Modules/Monster/Domain/Services/MonsterCombatantFactory.php app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php app/Modules/Battle/Application/Services/Combat/AttackService.php app/Modules/Battle/Application/Services/Combat/Strategies/FistAttackStrategy.php app/Modules/Battle/Application/Services/Combat/Strategies/OneHandWeaponStrategy.php app/Modules/Battle/Application/Services/Combat/Strategies/DualWieldStrategy.php tests/Unit/Modules/Monster/MonsterCombatantTest.php

git add app/Modules/Monster/Domain/DTO/MonsterCombatant.php \
        app/Modules/Monster/Domain/Services/MonsterCombatantFactory.php \
        app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php \
        app/Modules/Battle/Application/Services/Combat/AttackService.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/FistAttackStrategy.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/OneHandWeaponStrategy.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/DualWieldStrategy.php \
        tests/Unit/Modules/Monster/MonsterCombatantTest.php
git commit -m "feat(magic): add MonsterCombatant so monster-targeted debuffs affect combat"
```

---

### Task 6: `MagicCastGuard` — atomic mana + cooldown

**Files:**
- Create: `app/Services/MagicCastGuard.php`
- Modify: `app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php`
- Test: `tests/Feature/Services/MagicCastGuardTest.php` (new)

**Interfaces:**
- Produces: `MagicCastGuard::tryConsume(Player $player, MagicSkill $skill): MagicCastGuard\CastAttemptResult` — a small value object with `ok: bool` and `reason: ?string`. Callers only proceed with the cast if `ok === true`; the guard has already deducted mana and set the cooldown by the time it returns `ok === true`.

- [ ] **Step 1: Write the failing Feature test**

Create `tests/Feature/Services/MagicCastGuardTest.php`:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Services\MagicCastGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MagicCastGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->integer('mp_now')->default(10);
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->integer('mana_cost')->default(0);
            $table->integer('cooldown')->default(0);
            $table->timestamps();
        });
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
    }

    public function test_rejects_and_does_not_deduct_when_mana_insufficient(): void
    {
        $player = Player::create(['mp_now' => 5]);
        $skill = MagicSkill::create(['name' => 'x', 'mana_cost' => 8, 'cooldown' => 0]);
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);

        $result = app(MagicCastGuard::class)->tryConsume($player, $skill);

        $this->assertFalse($result->ok);
        $this->assertSame(5, $player->fresh()->mp_now);
    }

    public function test_rejects_when_still_on_cooldown(): void
    {
        $player = Player::create(['mp_now' => 100]);
        $skill = MagicSkill::create(['name' => 'x', 'mana_cost' => 5, 'cooldown' => 30]);
        DB::table('player_magic_skills')->insert([
            'player_id' => $player->id,
            'magic_skill_id' => $skill->id,
            'cooldown_end_at' => now()->addSeconds(10),
        ]);

        $result = app(MagicCastGuard::class)->tryConsume($player, $skill);

        $this->assertFalse($result->ok);
        $this->assertSame(100, $player->fresh()->mp_now);
    }

    public function test_success_deducts_mana_and_sets_cooldown_atomically(): void
    {
        $player = Player::create(['mp_now' => 100]);
        $skill = MagicSkill::create(['name' => 'x', 'mana_cost' => 12, 'cooldown' => 30]);
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);

        $result = app(MagicCastGuard::class)->tryConsume($player, $skill);

        $this->assertTrue($result->ok);
        $this->assertSame(88, $player->fresh()->mp_now);
        $cooldownEndAt = DB::table('player_magic_skills')
            ->where('player_id', $player->id)->where('magic_skill_id', $skill->id)
            ->value('cooldown_end_at');
        $this->assertNotNull($cooldownEndAt);
    }

    public function test_zero_cooldown_skill_never_blocks_repeat_casts(): void
    {
        $player = Player::create(['mp_now' => 100]);
        $skill = MagicSkill::create(['name' => 'x', 'mana_cost' => 5, 'cooldown' => 0]);
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);
        $guard = app(MagicCastGuard::class);

        $first = $guard->tryConsume($player, $skill);
        $second = $guard->tryConsume($player, $skill);

        $this->assertTrue($first->ok);
        $this->assertTrue($second->ok);
        $this->assertSame(90, $player->fresh()->mp_now);
    }
}
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MagicCastGuardTest`
Expected: FAIL — class not found.

- [ ] **Step 3: Implement `MagicCastGuard`**

Create `app/Services/MagicCastGuard.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Support\Facades\DB;

/**
 * Единая точка списания маны + установки кулдауна для обоих путей применения
 * заклинания (боевой каст и внебоевой баф/хил) — под блокировкой строки
 * player_magic_skills, чтобы два параллельных запроса не списали ману дважды
 * и не обошли кулдаун одновременно (см. спеку, раздел «Общий механизм каста»).
 */
class MagicCastGuard
{
    public function tryConsume(Player $player, MagicSkill $skill): CastAttemptResult
    {
        return DB::transaction(function () use ($player, $skill): CastAttemptResult {
            $pivot = DB::table('player_magic_skills')
                ->where('player_id', $player->id)
                ->where('magic_skill_id', $skill->id)
                ->lockForUpdate()
                ->first();

            if ($pivot === null) {
                return CastAttemptResult::failure('Заклинание не изучено');
            }

            if ($pivot->cooldown_end_at !== null && now()->lt($pivot->cooldown_end_at)) {
                $remaining = (int) now()->diffInSeconds($pivot->cooldown_end_at, false);

                return CastAttemptResult::failure(sprintf('Перезарядка ещё %d сек.', $remaining));
            }

            $freshPlayer = Player::whereKey($player->id)->lockForUpdate()->first();

            if ($freshPlayer === null || $freshPlayer->mp_now < $skill->mana_cost) {
                return CastAttemptResult::failure(sprintf('Недостаточно маны, требуется %s', $skill->mana_cost));
            }

            $freshPlayer->mp_now -= $skill->mana_cost;
            $freshPlayer->save();
            $player->mp_now = $freshPlayer->mp_now;

            $cooldownEndAt = $skill->cooldown > 0 ? now()->addSeconds($skill->cooldown) : null;

            DB::table('player_magic_skills')
                ->where('player_id', $player->id)
                ->where('magic_skill_id', $skill->id)
                ->update(['cooldown_end_at' => $cooldownEndAt]);

            return CastAttemptResult::success();
        });
    }
}
```

Create the accompanying value object `app/Services/CastAttemptResult.php`:

```php
<?php

declare(strict_types=1);

namespace App\Services;

final readonly class CastAttemptResult
{
    private function __construct(
        public bool $ok,
        public ?string $reason,
    ) {}

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $reason): self
    {
        return new self(false, $reason);
    }
}
```

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=MagicCastGuardTest`
Expected: PASS (4 tests).

- [ ] **Step 5: Wire the guard into `UseMagicSkill`**

In `app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php`, add the constructor dependency:

```php
use App\Services\MagicCastGuard;

class UseMagicSkill
{
    public function __construct(
        private readonly MagicSkillReadRepository $readRepository,
        private readonly MagicSkillWriteRepository $writeRepository,
        private readonly PlayerStatService $statService,
        private readonly BattleEffectService $effectService,
        private readonly MagicCastGuard $castGuard,
    ) {}
```

Replace the existing manual cooldown-check block (`if ($pivot?->cooldown_end_at && now()->lt($pivot->cooldown_end_at)) { ... }`) and the manual mana-check block (`if ($caster->mp_now < $skill->mana_cost) { ... }`) with a single call right after the `isBuffSkill()` check:

```php
        $castAttempt = $this->castGuard->tryConsume($caster, $skill);

        if (! $castAttempt->ok) {
            return new MagicSkillActionResultDTO('error', $castAttempt->reason, httpCode: 422);
        }
```

Also remove the now-redundant `$this->writeRepository->consumeMana($caster, $skill->mana_cost);` call further down (the guard already deducted it), and remove the `$cooldownEndsAt = $skill->cooldown > 0 ? now()->addSeconds($skill->cooldown) : null; $this->writeRepository->updateCooldown($caster, $skill, $cooldownEndsAt);` block near the end — the guard already set it. Keep everything else (healing, effects, `savePlayers`) unchanged. If the final response DTO needs `cooldownUntil`, read it back from the pivot table instead of recomputing:

```php
        $cooldownUntil = DB::table('player_magic_skills')
            ->where('player_id', $caster->id)->where('magic_skill_id', $skill->id)
            ->value('cooldown_end_at');
```
(add `use Illuminate\Support\Facades\DB;` if not already imported) and pass `cooldownUntil: $cooldownUntil ? \Illuminate\Support\Carbon::parse($cooldownUntil)->getTimestamp() : null` into the returned `MagicSkillActionResultDTO`.

- [ ] **Step 6: Wire the guard into `MagicAttackStrategy`**

In `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php`, replace the manual mana check + deduction:

```php
        if ($this->playerModel->mp_now < $this->magicSkill->mana_cost) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage(sprintf('Недостаточно маны, требуется %s', $this->magicSkill->mana_cost)),
            ];
        }

        $this->playerModel->mp_now -= $this->magicSkill->mana_cost;
```

with:

```php
        $castAttempt = $this->castGuard->tryConsume($this->playerModel, $this->magicSkill);

        if (! $castAttempt->ok) {
            return [
                (new FightHitDTO)
                    ->setCantCast(true)
                    ->setMessage($castAttempt->reason),
            ];
        }
```

Add the constructor dependency and import:

```php
use App\Services\MagicCastGuard;

class MagicAttackStrategy implements AttackStrategyInterface
{
    public function __construct(
        private MagicHitCalculator $magicHitCalc,
        private MagicCastGuard $castGuard,
        private FightHitInterface $player,
        private Player $playerModel,
        private FightHitInterface $monster,
        private MagicSkill $magicSkill,
    ) {}
```

Update `AttackStrategyResolver`'s `new MagicAttackStrategy(...)` call to pass `castGuard: $this->castGuard` (add `MagicCastGuard $castGuard` to the resolver's own constructor + import too).

Update `tests/Feature/Modules/Battle/MagicAttackStrategyTest.php` (Task 4) to pass a real `MagicCastGuard` — since it now needs the `player_magic_skills` table, extend that test's `setUp()` schema with the `magic_skills` and `player_magic_skills` tables (copy from `MagicCastGuardTest::setUp()`), insert a `player_magic_skills` row for the test skill in each test method before calling `$strategy->getHits()`, and change `new MagicAttackStrategy(magicHitCalc: ..., player: ..., ...)` calls to include `castGuard: app(MagicCastGuard::class)`.

- [ ] **Step 7: Run all magic-related tests**

```bash
docker exec onlinegame-php-www-1 php artisan test --filter=MagicCastGuardTest,MagicAttackStrategyTest,MagicHitCalculatorTest
```
Expected: all PASS.

- [ ] **Step 8: Lint and commit**

```bash
php -l app/Services/MagicCastGuard.php
php -l app/Services/CastAttemptResult.php
php -l app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php
php -l app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php
php -l app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php
./vendor/bin/pint app/Services/MagicCastGuard.php app/Services/CastAttemptResult.php app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php tests/Feature/Services/MagicCastGuardTest.php tests/Feature/Modules/Battle/MagicAttackStrategyTest.php

git add app/Services/MagicCastGuard.php app/Services/CastAttemptResult.php \
        app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php \
        app/Modules/Battle/Application/Services/Combat/AttackStrategyResolver.php \
        tests/Feature/Services/MagicCastGuardTest.php \
        tests/Feature/Modules/Battle/MagicAttackStrategyTest.php
git commit -m "feat(magic): MagicCastGuard — atomic mana+cooldown for both cast paths"
```

---

### Task 7: `ShareItemType::BOOK` + `magic_skill_books`

**Files:**
- Modify: `app/Modules/Share/Domain/Enums/ShareItemType.php`
- Create: `database/migrations/2026_08_22_140000_create_magic_skill_books_table.php`
- Create: `app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkillBook.php`
- Test: none (pure schema task — covered end-to-end by Task 8's test)

**Interfaces:**
- Produces: `MagicSkillBook` model with `share_item_id`, `magic_skill_id`; `MagicSkillBook::shareItem(): BelongsTo`, `MagicSkillBook::magicSkill(): BelongsTo`.

- [ ] **Step 1: Add `BOOK` to `ShareItemType`**

In `app/Modules/Share/Domain/Enums/ShareItemType.php`, add the case (alphabetically near `RUNE_KEY`/`SCROLL` is fine, or right after `ARTIFACT` — pick a spot near other single-purpose types like `KEY`/`RECIPE`):

```php
    case RECIPE = 'recipe';
    case BOOK = 'book';
```

Add its label:

```php
            self::RECIPE => 'Рецепт',
            self::BOOK => 'Книга заклинаний',
```

`BOOK` is stackable by default (`isStackable()` returns `true` for anything not in the `isEquipment()` list — no change needed there). Add it to the `'main'` group in `group()` so it shows up in the default backpack tab:

```php
            'main' => [
                self::POTION,
                self::WEAPON,
                self::SHIELD,
                self::ARMOR,
                self::BELT,
                self::BAG,
                self::RESOURCE,
                self::RECIPE,
                self::BOOK,
                self::SCROLL,
                self::GEM,
                self::MOUNT,
                self::RUNE,
                self::RUNE_KEY,
                self::EAT,
            ],
```

- [ ] **Step 2: Create and run the migration**

Create `database/migrations/2026_08_22_140000_create_magic_skill_books_table.php`:

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magic_skill_books', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->unique();
            $table->unsignedBigInteger('magic_skill_id')->unique();
            $table->timestamps();

            $table->foreign('share_item_id')->references('id')->on('share_items')->onDelete('cascade');
            $table->foreign('magic_skill_id')->references('id')->on('magic_skills')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_skill_books');
    }
};
```

Run:
```bash
docker exec onlinegame-php-www-1 printenv DB_DATABASE
docker exec onlinegame-php-www-1 php artisan migrate --path=database/migrations/2026_08_22_140000_create_magic_skill_books_table.php --force
```
Expected: `game`, migration `DONE`.

- [ ] **Step 3: Create the `MagicSkillBook` model**

Create `app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkillBook.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Infrastructure\Persistence\Models;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MagicSkillBook extends Model
{
    protected $fillable = ['share_item_id', 'magic_skill_id'];

    public function shareItem(): BelongsTo
    {
        return $this->belongsTo(ShareItem::class);
    }

    public function magicSkill(): BelongsTo
    {
        return $this->belongsTo(MagicSkill::class);
    }
}
```

- [ ] **Step 4: Lint and commit**

```bash
php -l app/Modules/Share/Domain/Enums/ShareItemType.php
php -l app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkillBook.php
./vendor/bin/pint app/Modules/Share/Domain/Enums/ShareItemType.php app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkillBook.php

git add app/Modules/Share/Domain/Enums/ShareItemType.php \
        database/migrations/2026_08_22_140000_create_magic_skill_books_table.php \
        app/Modules/MagicSkill/Infrastructure/Persistence/Models/MagicSkillBook.php
git commit -m "feat(magic): add BOOK item type and magic_skill_books link table"
```

---

### Task 8: `LearnMagicSkillFromBook` — the only place requirements are checked

**Files:**
- Create: `app/Modules/MagicSkill/Application/UseCases/LearnMagicSkillFromBook.php`
- Create: `app/Modules/MagicSkill/Application/DTOs/LearnMagicSkillResultDTO.php`
- Modify: `app/Modules/MagicSkill/Presentation/Http/MagicSkillController.php`
- Modify: `app/Modules/MagicSkill/Presentation/Http/Route/web.php`
- Modify: `resources/views/... /bag.blade.php` (backpack — add "Изучить" context menu item for BOOK items)
- Test: `tests/Feature/Modules/MagicSkill/LearnMagicSkillFromBookTest.php` (new)

**Interfaces:**
- Consumes: `MagicSkillRequirementService::check(Player $player, MagicSkill $skill): ?string` (existing, from the previous session), `BackpackService` (existing — for locating/removing the book from the player's bag; read the actual method names from `app/Modules/Backpack/Domain/Services/BackpackService.php` before writing this task's code, don't guess signatures).
- Produces: `LearnMagicSkillFromBook::execute(User $user, int $shareItemId): LearnMagicSkillResultDTO`.

- [ ] **Step 1: Read `BackpackService` to get exact method signatures**

Before writing any code, run:
```bash
grep -n "public function" app/Modules/Backpack/Domain/Services/BackpackService.php
```
Note the exact method for "does the player have N of item X" and "remove N of item X from the bag" — the spec calls for exactly this pair. If a `removeItem`/`deleteItem`-style method doesn't already exist with the right semantics (removes exactly one unit, keeps the row if count > 1, deletes the row if count reaches 0), check `app/Modules/Item/...` for how other one-shot-consumable items (potions, scrolls) already do this and follow that exact pattern instead of inventing a new one.

- [ ] **Step 2: Write the failing Feature test**

Create `tests/Feature/Modules/MagicSkill/LearnMagicSkillFromBookTest.php`. Build the minimal schema this use case actually touches (`players`, `share_items`, `magic_skills`, `magic_skill_books`, `magic_skill_requirements`, `player_magic_skills`, and whatever backpack table `BackpackService` reads/writes — check its model in Step 1 and mirror that table here too):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\MagicSkill\Application\UseCases\LearnMagicSkillFromBook;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LearnMagicSkillFromBookTest extends TestCase
{
    // NOTE for implementer: setUp() must create every table BackpackService reads —
    // read that service (Step 1) before finishing this setUp(). The schema below
    // covers only the magic-skill side; add the backpack table(s) alongside it.

    public function test_learning_succeeds_when_requirements_met_and_book_is_consumed(): void
    {
        // Arrange: player meets every requirement, owns exactly 1 copy of the book.
        // Act: call LearnMagicSkillFromBook::execute($user, $bookShareItemId).
        // Assert: result->ok === true, player_magic_skills row now exists,
        //         the book is gone from the backpack (or count decremented to 0 and row removed).
        $this->markTestIncomplete('Fill in once BackpackService method names are confirmed in Step 1.');
    }

    public function test_learning_fails_and_book_is_not_consumed_when_requirement_unmet(): void
    {
        // Same setup but player's intelligence/wisdom/skill level is below the requirement.
        // Assert: result->ok === false, no player_magic_skills row created,
        //         book count in backpack is unchanged.
        $this->markTestIncomplete('Fill in once BackpackService method names are confirmed in Step 1.');
    }

    public function test_learning_twice_is_rejected_on_the_second_attempt(): void
    {
        // Learn once (succeeds), then attempt again with the player still owning a second
        // copy of the book. Assert second attempt: result->ok === false, reason mentions
        // "уже изучено", second book NOT consumed, still only one player_magic_skills row.
        $this->markTestIncomplete('Fill in once BackpackService method names are confirmed in Step 1.');
    }
}
```

This task intentionally ships the test skeleton with `markTestIncomplete()` placeholders for the Arrange/Assert bodies rather than guessed `BackpackService` calls — **Step 1 is a hard prerequisite in this codebase's actual state**, not busywork. Once Step 1's grep output is in hand, fill in every `markTestIncomplete()` body with real assertions before moving to Step 3. This is the one place in this plan where the exact code is deliberately deferred one step, because the dependency (`BackpackService`'s real method names) is unknown at plan-writing time and guessing them would violate the "No Placeholders" rule harder than naming the prerequisite explicitly.

- [ ] **Step 3: Run the test to verify it's incomplete (not failing on a missing class yet)**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=LearnMagicSkillFromBookTest`
Expected: 3 incomplete (yellow), not error/fail — confirms the test file itself is syntactically valid before you build against it.

- [ ] **Step 4: Implement `LearnMagicSkillResultDTO`**

Create `app/Modules/MagicSkill/Application/DTOs/LearnMagicSkillResultDTO.php`:

```php
<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\DTOs;

class LearnMagicSkillResultDTO
{
    public function __construct(
        public readonly bool $ok,
        public readonly string $message,
        public readonly int $httpCode = 200,
    ) {}

    public function toArray(): array
    {
        return ['ok' => $this->ok, 'message' => $this->message];
    }
}
```

- [ ] **Step 5: Implement `LearnMagicSkillFromBook`**

Create `app/Modules/MagicSkill/Application/UseCases/LearnMagicSkillFromBook.php`. Use the real `BackpackService` method names found in Step 1 in place of the `// TODO: real BackpackService call` markers below — do not leave those markers in the committed version:

```php
<?php

declare(strict_types=1);

namespace App\Modules\MagicSkill\Application\UseCases;

use App\Modules\MagicSkill\Application\DTOs\LearnMagicSkillResultDTO;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\MagicSkillRequirementService;
use Illuminate\Support\Facades\DB;

/**
 * Единственное место, где проверяются magic_skill_requirements — см. спеку,
 * правило 3: экипировка уже изученного заклинания требований не проверяет.
 */
class LearnMagicSkillFromBook
{
    public function __construct(
        private readonly MagicSkillRequirementService $requirementService,
    ) {}

    public function execute(User $user, int $shareItemId): LearnMagicSkillResultDTO
    {
        $player = $user->player;

        $book = MagicSkillBook::where('share_item_id', $shareItemId)->with('magicSkill')->first();

        if ($book === null) {
            return new LearnMagicSkillResultDTO(false, 'Это не книга заклинаний', httpCode: 422);
        }

        $skill = $book->magicSkill;

        $alreadyLearned = DB::table('player_magic_skills')
            ->where('player_id', $player->id)
            ->where('magic_skill_id', $skill->id)
            ->exists();

        if ($alreadyLearned) {
            return new LearnMagicSkillResultDTO(false, 'Заклинание уже изучено', httpCode: 422);
        }

        $unmet = $this->requirementService->check($player, $skill);

        if ($unmet !== null) {
            return new LearnMagicSkillResultDTO(false, $unmet, httpCode: 422);
        }

        return DB::transaction(function () use ($user, $player, $shareItemId, $skill): LearnMagicSkillResultDTO {
            // TODO: real BackpackService call — must (a) lock the backpack row for update,
            // (b) confirm the player still owns >= 1 copy of $shareItemId inside this
            // transaction (re-check after the requirement check above, in case of a race),
            // (c) remove exactly one unit. Return early with a failure DTO if ownership
            // fails this second check.

            DB::table('player_magic_skills')->insert([
                'player_id' => $player->id,
                'magic_skill_id' => $skill->id,
                'is_equipped' => false,
                'cooldown_end_at' => null,
                'sort_order' => 0,
            ]);

            return new LearnMagicSkillResultDTO(true, sprintf('Выучено: «%s»', $skill->name));
        });
    }
}
```

- [ ] **Step 6: Fill in the test bodies and the `BackpackService` call, then run the test**

Replace every `markTestIncomplete()` in `LearnMagicSkillFromBookTest` with real Arrange/Act/Assert code using the confirmed `BackpackService` methods, and replace the `// TODO: real BackpackService call` block in `LearnMagicSkillFromBook::execute()` with the real call.

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=LearnMagicSkillFromBookTest`
Expected: PASS (3 tests), zero incomplete/skipped remaining.

- [ ] **Step 7: Add the controller action and route**

In `app/Modules/MagicSkill/Presentation/Http/MagicSkillController.php`, add the dependency and method:

```php
use App\Modules\MagicSkill\Application\UseCases\LearnMagicSkillFromBook;

    public function __construct(
        private readonly GetMagicSkillPage $getMagicSkillPage,
        private readonly UpdateEquippedMagicSkills $updateEquippedMagicSkills,
        private readonly UpdateMagicSkillOrder $updateMagicSkillOrder,
        private readonly UseMagicSkill $useMagicSkill,
        private readonly LearnMagicSkillFromBook $learnMagicSkillFromBook,
    ) {}

    public function learnFromBook(Request $request, int $item): JsonResponse
    {
        $result = $this->learnMagicSkillFromBook->execute(Auth::user(), $item);

        return response()->json($result->toArray(), $result->httpCode);
    }
```

In `app/Modules/MagicSkill/Presentation/Http/Route/web.php`, add inside the existing `Route::middleware(['updateLastOnline'])->group(...)` block:

```php
    Route::post('/magic-skill/learn/{item}', [MagicSkillController::class, 'learnFromBook'])->name('magic_skill.learn');
```

- [ ] **Step 8: Add the "Изучить" context menu item in the backpack**

Read `resources/views/**/bag.blade.php`'s existing `ctx-use` context menu item and its JS wiring (`case 'use': ... fetch(route('items.use', ...))`) first — this task mirrors that exact pattern for a new `ctx-learn` item, shown only when the right-clicked item's type is `book`. Find:

```blade
    <div class="ctx-item" id="ctx-use"     onclick="ctxAction('use')">Использовать</div>
```

and add right after it:

```blade
    <div class="ctx-item" id="ctx-learn"   onclick="ctxAction('learn')">Изучить</div>
```

Find the JS block that toggles `ctx-use`'s visibility (`document.getElementById('ctx-use').style.display = usable ? '' : 'none';`) and add a sibling line using the item's type (check how `usable` is computed — it's derived from the item's type/flags already present in the row's dataset; add an equivalent `isBook` check against `type === 'book'` using the same dataset source):

```js
document.getElementById('ctx-learn').style.display = (itemType === 'book') ? '' : 'none';
```

Find the `case 'use':` handler and the `fetch('{{ route('items.use', ...) }}'...)` call, and add a sibling `case 'learn':` branch that POSTs to `route('magic_skill.learn', ['item' => '__ID__'])` instead, following the exact same fetch/response-handling shape (success message toast, remove-or-decrement the item row on `ok: true`, matching however the existing `use` handler already updates the DOM after a successful use).

- [ ] **Step 9: Run the full magic Feature suite and lint**

```bash
docker exec onlinegame-php-www-1 php artisan test --filter=LearnMagicSkillFromBookTest,MagicCastGuardTest,MagicAttackStrategyTest,MagicHitCalculatorTest,MonsterCombatantTest,MagicResistanceDerivationTest,PlayerStatFormulasTest

php -l app/Modules/MagicSkill/Application/UseCases/LearnMagicSkillFromBook.php
php -l app/Modules/MagicSkill/Application/DTOs/LearnMagicSkillResultDTO.php
php -l app/Modules/MagicSkill/Presentation/Http/MagicSkillController.php
php -l app/Modules/MagicSkill/Presentation/Http/Route/web.php
./vendor/bin/pint app/Modules/MagicSkill/Application/UseCases/LearnMagicSkillFromBook.php app/Modules/MagicSkill/Application/DTOs/LearnMagicSkillResultDTO.php app/Modules/MagicSkill/Presentation/Http/MagicSkillController.php tests/Feature/Modules/MagicSkill/LearnMagicSkillFromBookTest.php
```
Expected: all PASS, no lint errors.

- [ ] **Step 10: Commit**

```bash
git add app/Modules/MagicSkill/Application/UseCases/LearnMagicSkillFromBook.php \
        app/Modules/MagicSkill/Application/DTOs/LearnMagicSkillResultDTO.php \
        app/Modules/MagicSkill/Presentation/Http/MagicSkillController.php \
        app/Modules/MagicSkill/Presentation/Http/Route/web.php \
        tests/Feature/Modules/MagicSkill/LearnMagicSkillFromBookTest.php
# add the bag.blade.php path found in Step 8
git commit -m "feat(magic): LearnMagicSkillFromBook — the single learn-time requirement gate"
```

---

### Task 9: Debuff floor clamp + boss debuff-duration reduction

**Files:**
- Modify: `app/Modules/Player/Domain/Services/PlayerStatService.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/BattleEffectService.php`
- Test: `tests/Feature/Modules/Player/PlayerStatServiceDebuffClampTest.php` (new)
- Test: `tests/Feature/Modules/Battle/BossDebuffDurationTest.php` (new)

**Interfaces:**
- Consumes: `Monster::$is_boss` (existing column), `Effect::$type` (existing, `'buff'|'debuff'|'neutral'`).

- [ ] **Step 1: Write the failing test for the stat floor**

Create `tests/Feature/Modules/Player/PlayerStatServiceDebuffClampTest.php` (schema mirrors Task 2's `MagicResistanceDerivationTest` — copy its `setUp()` and extend with whatever equipment/effects tables `PlayerStatService::resolve()` actually queries; check the current `resolve()`/`buildSheet()` call chain first for every table it touches):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Domain\Services\StatModifier;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PlayerStatServiceDebuffClampTest extends TestCase
{
    // setUp(): copy from MagicResistanceDerivationTest (Task 2), same players table.

    public function test_negative_flat_modifier_cannot_push_armor_below_zero(): void
    {
        $player = Player::create(['strength' => 5.0, 'lvl' => 12]); // armor base = 4
        $service = app(PlayerStatService::class);

        // Simulate a large debuff via the public API the way an Effect would apply
        // one — check PlayerStatService for the correct public entry point that
        // accepts extra StatModifier[] beyond equipment (likely resolve() takes an
        // optional $extraModifiers param, or effects are read internally from
        // player_active_effects — read the current buildSheet() call chain and use
        // whichever real mechanism exists instead of guessing a signature here).
        $this->markTestIncomplete('Wire this to the real StatModifier injection point found by reading PlayerStatService.');
    }
}
```

As with Task 8, this codebase's exact `PlayerStatService` extension point for "inject one extra debuff modifier for this test" is not something to guess — read `app/Modules/Player/Domain/Services/PlayerStatService.php`'s full `resolve()`/`buildSheet()`/modifier-collection chain before finishing this test body. The fix itself (Step 2) does not depend on this — implement Step 2 regardless, then come back and finish this test.

- [ ] **Step 2: Add the clamp in `applyModifiers()`**

In `app/Modules/Player/Domain/Services/PlayerStatService.php`, in `applyModifiers()` (around line 189-211), change:

```php
        $computed = [];
        foreach ($base as $stat => $baseVal) {
            $computed[$stat] = (int) floor(($baseVal + $flat[$stat]) * (1 + $percent[$stat] / 100));
        }
```

to:

```php
        $computed = [];
        foreach ($base as $stat => $baseVal) {
            $computed[$stat] = max(0, (int) floor(($baseVal + $flat[$stat]) * (1 + $percent[$stat] / 100)));
        }
```

This is a one-line, blast-radius-contained fix: every derived/primary stat in `$base` (strength, agility, armor, dodge, critical, magic_resistance, hp_max, mp_max, damage stats, etc.) already floors at 0 for the common case (no debuffs push them negative today), so this only changes behavior once a genuinely large negative modifier exists — which, before this plan, nothing produced.

- [ ] **Step 3: Finish and run the clamp test**

Go back to Step 1's test, wire it to the real modifier-injection mechanism found by reading `PlayerStatService`, assert `$sheet->getArmor() === 0` (not negative), then run:

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=PlayerStatServiceDebuffClampTest`
Expected: PASS.

- [ ] **Step 4: Write the failing test for boss debuff-duration reduction**

Create `tests/Feature/Modules/Battle/BossDebuffDurationTest.php`. This test needs `monsters`, `monster_on_locations` (or whatever the actual table name is — check the `MonsterOnLocation` model), `monster_active_effects`, and `effects` tables. Read `app/Modules/Monster/Infrastructure/Persistence/Models/MonsterOnLocation.php` and `app/Modules/Battle/Application/Services/Combat/BattleEffectService.php`'s current `applyEffectToMonster()` signature (already read in this plan's research phase — reproduced here for reference) before writing the schema:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class BossDebuffDurationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        Schema::create('location_has_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now')->default(100);
            $table->integer('hp_max')->default(100);
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('debuff');
            $table->string('slug')->default('armor_down');
            $table->string('type')->default('debuff');
            $table->integer('duration')->default(8);
            $table->boolean('is_stackable')->default(false);
            $table->integer('tick_interval')->default(1);
            $table->integer('value_per_tick')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->timestamps();
        });
        Schema::create('monster_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_monster_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
    }

    public function test_boss_debuff_duration_is_halved(): void
    {
        $monster = Monster::create(['name' => 'Boss', 'is_boss' => true]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Слабость', 'slug' => 'weakness', 'type' => 'debuff', 'duration' => 8]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stacks = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks');
        $this->assertSame(4, $stacks, 'boss debuffs must apply at half duration, not full or zero');
    }

    public function test_non_boss_debuff_duration_is_unaffected(): void
    {
        $monster = Monster::create(['name' => 'Regular', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Слабость', 'slug' => 'weakness', 'type' => 'debuff', 'duration' => 8]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stacks = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks');
        $this->assertSame(8, $stacks);
    }
}
```

If `MonsterOnLocation`'s real table isn't `location_has_monsters`, or `Monster`/`MonsterOnLocation`/`Battle`/`Effect` have additional non-nullable columns not listed here, read those models first and adjust the `Schema::create()` blocks to match — this is the same "read the real model, don't guess the schema" discipline as every other Feature test in this plan.

- [ ] **Step 2: Run the test to verify it fails**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=BossDebuffDurationTest`
Expected: FAIL — `test_boss_debuff_duration_is_halved` gets `stacks === 8`, not `4` (current code applies full duration regardless of `is_boss`).

- [ ] **Step 3: Add the boss duration reduction to `applyEffectToMonster()`**

In `app/Modules/Battle/Application/Services/Combat/BattleEffectService.php`, add a class constant near the top:

```php
class BattleEffectService
{
    /** Боссы не иммунны к дебаффам, но держат их вдвое короче — см. спеку */
    private const BOSS_DEBUFF_DURATION_MULTIPLIER = 0.5;
```

Then, inside `applyEffectToMonster()`, right before the `$existing = MonsterActiveEffect::where(...)` line, compute the effective duration:

```php
        $effectiveDuration = (int) $effect->duration;

        if ($effect->type === 'debuff' && $locMonster->monster->is_boss) {
            $effectiveDuration = max(1, (int) round($effectiveDuration * self::BOSS_DEBUFF_DURATION_MULTIPLIER));
        }
```

Then replace both remaining uses of `(int) $effect->duration` inside that method (in the `$existing->stacks = max($existing->stacks, (int) $effect->duration);` line and the `MonsterActiveEffect::create([... 'stacks' => (int) $effect->duration, ...])` line) with `$effectiveDuration`.

- [ ] **Step 4: Run the test to verify it passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=BossDebuffDurationTest`
Expected: PASS (2 tests).

- [ ] **Step 5: Lint and commit**

```bash
php -l app/Modules/Player/Domain/Services/PlayerStatService.php
php -l app/Modules/Battle/Application/Services/Combat/BattleEffectService.php
./vendor/bin/pint app/Modules/Player/Domain/Services/PlayerStatService.php app/Modules/Battle/Application/Services/Combat/BattleEffectService.php tests/Feature/Modules/Player/PlayerStatServiceDebuffClampTest.php tests/Feature/Modules/Battle/BossDebuffDurationTest.php

docker exec onlinegame-php-www-1 php artisan test --filter=PlayerStatServiceDebuffClampTest,BossDebuffDurationTest

git add app/Modules/Player/Domain/Services/PlayerStatService.php \
        app/Modules/Battle/Application/Services/Combat/BattleEffectService.php \
        tests/Feature/Modules/Player/PlayerStatServiceDebuffClampTest.php \
        tests/Feature/Modules/Battle/BossDebuffDurationTest.php
git commit -m "fix(magic): clamp debuffed stats at 0, halve debuff duration on bosses instead of full immunity"
```

---

### Task 10: DoT — tick value fixed once at cast time

**Files:**
- Modify: `app/Modules/Battle/Application/DTOs/FightHitDTO.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/BattleEffectService.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/AttackService.php`
- Modify: `app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php` (finalize the `TODO(Task 10)` left in Task 4)
- Test: `tests/Feature/Modules/Battle/DotTickFixedAtCastTimeTest.php` (new)

**Interfaces:**
- Produces: `FightHitDTO::addAppliedEffect(Effect $effect, ?int $tickValue = null): self`, `FightHitDTO::getAppliedEffects(): Collection` (now yields `array{effect: Effect, tickValue: ?int}`), `BattleEffectService::applyEffectToMonster(Effect $effect, MonsterOnLocation $locMonster, Battle $battle, AttackResultDTO $result, ?int $tickValueOverride = null): void`.

- [ ] **Step 1: Change `addAppliedEffect`/`getAppliedEffects` in `FightHitDTO`**

In `app/Modules/Battle/Application/DTOs/FightHitDTO.php`, replace:

```php
    public function addAppliedEffect(Effect $effect): self
    {
        $this->appliedEffects->push($effect);

        return $this;
    }

    public function getAppliedEffects(): Collection
    {
        return $this->appliedEffects;
    }
```

with:

```php
    public function addAppliedEffect(Effect $effect, ?int $tickValue = null): self
    {
        $this->appliedEffects->push(['effect' => $effect, 'tickValue' => $tickValue]);

        return $this;
    }

    /** @return Collection<int, array{effect: Effect, tickValue: ?int}> */
    public function getAppliedEffects(): Collection
    {
        return $this->appliedEffects;
    }
```

Leave `addSelfAppliedEffect`/`getSelfAppliedEffects()` untouched — self-applied buffs don't carry a magic-computed tick value in this release (per spec scope).

- [ ] **Step 2: Update `applyEffectToMonster()` to accept an override**

In `app/Modules/Battle/Application/Services/Combat/BattleEffectService.php`, change the signature (keep the boss-duration logic from Task 9 in place):

```php
    public function applyEffectToMonster(
        Effect $effect,
        MonsterOnLocation $locMonster,
        Battle $battle,
        AttackResultDTO $result,
        ?int $tickValueOverride = null,
    ): void {
```

And where the row gets created, use the override when present:

```php
        MonsterActiveEffect::create([
            'location_monster_id' => $locMonster->id,
            'effect_id' => $effect->id,
            'battle_id' => $battle->id,
            'type' => $type,
            'applied_at' => now(),
            'stacks' => $effectiveDuration,
            'current_value' => $tickValueOverride ?? (float) $effect->value_per_tick,
        ]);
```

Also apply the override on the refresh branch (when `$existing` already exists — a re-cast should also refresh the tick value to the newly-computed one, not keep the stale one):

```php
        if ($existing) {
            $existing->stacks = max($existing->stacks, $effectiveDuration);
            if ($tickValueOverride !== null) {
                $existing->current_value = $tickValueOverride;
            }
            $existing->save();

            return;
        }
```

- [ ] **Step 3: Update `AttackService` to pass the tick value through**

In `app/Modules/Battle/Application/Services/Combat/AttackService.php`, find:

```php
            if (! $hit->getAppliedEffects()->isEmpty()) {
                foreach ($hit->getAppliedEffects() as $effect) {
                    $this->effectService->applyEffectToMonster($effect, $locMonster, $battle, $result);
```

and change it to:

```php
            if (! $hit->getAppliedEffects()->isEmpty()) {
                foreach ($hit->getAppliedEffects() as $applied) {
                    $this->effectService->applyEffectToMonster($applied['effect'], $locMonster, $battle, $result, $applied['tickValue']);
```

(Keep whatever follows on the next lines inside that `foreach` body unchanged — only the destructuring and the extra trailing argument change.)

- [ ] **Step 4: Finalize `MagicAttackStrategy`'s effect-application line from Task 4**

Confirm the line added in Task 4, Step 3 reads exactly:

```php
                $hit->addAppliedEffect($effectData, tickValue: $hit->getDamage());
```

with no `TODO` comment remaining. If Task 4 was done with the single-arg fallback, fix it now.

- [ ] **Step 5: Write the failing test**

Create `tests/Feature/Modules/Battle/DotTickFixedAtCastTimeTest.php`. Reuse the schema from Task 9's `BossDebuffDurationTest` (same tables) plus a `players` table if the chosen assertion path needs one — this test calls `AttackService`-adjacent logic only through `BattleEffectService` directly (no need to spin up a full battle), so the Task 9 schema is sufficient:

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DotTickFixedAtCastTimeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Copy exactly from BossDebuffDurationTest::setUp() (Task 9) — same tables.
    }

    public function test_tick_value_override_is_stored_verbatim_not_effects_static_value(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create([
            'name' => 'Ожог', 'slug' => 'burn', 'type' => 'debuff', 'duration' => 6,
            'value_per_tick' => 999, // deliberately wrong — must NOT be what gets stored
        ]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster(
            $effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 23,
        );

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(23.0, (float) $stored);
    }

    public function test_recast_refreshes_the_tick_value_to_the_new_computation(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Ожог', 'slug' => 'burn', 'type' => 'debuff', 'duration' => 6]);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 10);
        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 40);

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(40.0, (float) $stored, 'recasting must refresh the tick value, not keep the first cast\'s number');
        $this->assertSame(1, DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->count(), 'must refresh the existing row, not create a second one');
    }

    public function test_no_override_falls_back_to_effect_static_value_unchanged(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Регенерация', 'slug' => 'regen', 'type' => 'buff', 'duration' => 4, 'value_per_tick' => 15]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(15.0, (float) $stored, 'existing non-magic callers (no override passed) must keep working exactly as before');
    }
}
```

- [ ] **Step 6: Run the test to verify it fails, then passes**

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=DotTickFixedAtCastTimeTest`
Expected: FAIL first (signature mismatch), then PASS (3 tests) after Steps 1-4 are applied.

- [ ] **Step 7: Re-run every test touched by Task 4 and Task 9 to catch regressions**

```bash
docker exec onlinegame-php-www-1 php artisan test --filter=DotTickFixedAtCastTimeTest,BossDebuffDurationTest,MagicAttackStrategyTest,MagicCastGuardTest
```
Expected: all PASS.

- [ ] **Step 8: Lint and commit**

```bash
php -l app/Modules/Battle/Application/DTOs/FightHitDTO.php
php -l app/Modules/Battle/Application/Services/Combat/BattleEffectService.php
php -l app/Modules/Battle/Application/Services/Combat/AttackService.php
php -l app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php
./vendor/bin/pint app/Modules/Battle/Application/DTOs/FightHitDTO.php app/Modules/Battle/Application/Services/Combat/BattleEffectService.php app/Modules/Battle/Application/Services/Combat/AttackService.php app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php tests/Feature/Modules/Battle/DotTickFixedAtCastTimeTest.php

git add app/Modules/Battle/Application/DTOs/FightHitDTO.php \
        app/Modules/Battle/Application/Services/Combat/BattleEffectService.php \
        app/Modules/Battle/Application/Services/Combat/AttackService.php \
        app/Modules/Battle/Application/Services/Combat/Strategies/MagicAttackStrategy.php \
        tests/Feature/Modules/Battle/DotTickFixedAtCastTimeTest.php
git commit -m "feat(magic): DoT tick value fixed once at cast time via MagicHitCalculator, not re-rolled"
```

---

### Task 11: Heal formula upgrade

**Files:**
- Modify: `app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php`
- Test: extend `tests/Feature/Modules/MagicSkill/` with a new test file

**Interfaces:**
- Consumes: `MagicHitCalculator::heal()` (Task 3), `PlayerStatService::resolve()` (existing).

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Modules/MagicSkill/UseMagicSkillHealFormulaTest.php` (schema: copy `MagicCastGuardTest`'s `players`/`magic_skills`/`player_magic_skills` tables from Task 6, no additional tables needed since healing doesn't touch equipment in this test — use a player with `intelligence` set directly on the row):

```php
<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\MagicSkill\Application\UseCases\UseMagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UseMagicSkillHealFormulaTest extends TestCase
{
    // setUp(): players, magic_skills, player_magic_skills (copy Task 6), plus users
    // table with a player_id/relation the User model expects — check User::player()
    // for the real FK column name before writing this schema.

    public function test_heal_amount_scales_with_intelligence_not_flat_base_healing(): void
    {
        // Arrange a healer with high intelligence and a heal skill with
        // base_healing=50, power_coefficient=0.4 — assert the applied heal is
        // strictly greater than 50 (proves intelligence contributed), matching
        // MagicHitCalculator::heal()'s formula, not the old flat base_healing.
        $this->markTestIncomplete('Fill in once the User<->Player relation FK is confirmed.');
    }
}
```

Same discipline as Tasks 8-9: the `User`/`Player` relation's exact FK naming isn't guessed here — confirm it by reading `app/Modules/User/Infrastructure/Persistence/Models/User.php`'s `player()` relation before finishing this test.

- [ ] **Step 2: Update the heal branch in `UseMagicSkill`**

In `app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php`, add the dependency:

```php
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;

    public function __construct(
        private readonly MagicSkillReadRepository $readRepository,
        private readonly MagicSkillWriteRepository $writeRepository,
        private readonly PlayerStatService $statService,
        private readonly BattleEffectService $effectService,
        private readonly MagicCastGuard $castGuard,
        private readonly MagicHitCalculator $magicHitCalc,
    ) {}
```

Replace:

```php
        if ($skill->base_healing > 0) {
            $heal = $skill->base_healing;
            $target->hp_now = min($targetSheet->getHpMax(), $target->hp_now + $heal);
            $log->log(sprintf('Заклинание восстановило <b>%d HP</b> игроку %s', $heal, $target->user->name));
        }
```

with:

```php
        if ($skill->base_healing > 0) {
            $heal = $this->magicHitCalc->heal(
                $casterSheet,
                minHeal: $skill->base_healing,
                maxHeal: $skill->base_healing,
                powerCoefficient: $skill->power_coefficient,
            );
            $target->hp_now = min($targetSheet->getHpMax(), $target->hp_now + $heal);
            $log->log(sprintf('Заклинание восстановило <b>%d HP</b> игроку %s', $heal, $target->user->name));
        }
```

`base_healing` now doubles as both the fixed floor and the random-roll min/max (heal skills don't currently have separate min/max healing fields — this keeps the existing single-number authoring model while adding the intelligence scalar on top, matching the spec's `power_coefficient` table entry for "Лечение: 0.30–0.40").

- [ ] **Step 3: Finish the test and run it**

Fill in the `markTestIncomplete()` body, run:

Run: `docker exec onlinegame-php-www-1 php artisan test --filter=UseMagicSkillHealFormulaTest`
Expected: PASS.

- [ ] **Step 4: Lint and commit**

```bash
php -l app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php
./vendor/bin/pint app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php tests/Feature/Modules/MagicSkill/UseMagicSkillHealFormulaTest.php
docker exec onlinegame-php-www-1 php artisan test --filter=UseMagicSkillHealFormulaTest

git add app/Modules/MagicSkill/Application/UseCases/UseMagicSkill.php tests/Feature/Modules/MagicSkill/UseMagicSkillHealFormulaTest.php
git commit -m "feat(magic): heal amount now scales with intelligence via MagicHitCalculator::heal()"
```

---

### Task 12: Starter content — 7 books

**Files:**
- Create: `database/seeders/MagicBookStarterSeeder.php`
- Modify (data only, via seeder, not schema): existing `fire_spark`/`flame_barrage`/`incinerating_vortex` `MagicSkill` rows

**Interfaces:**
- Consumes: everything from Tasks 1-11.
- Produces: 7 `ShareItem` (type `BOOK`) + `MagicSkillBook` rows; the existing 3 attack `MagicSkill` rows get `power_coefficient` set and their `min_damage`/`max_damage` re-tuned to the new split-formula shape; 4 new `MagicSkill` rows (DoT, heal, buff, debuff) with their `Effect`s and `magic_skill_requirements`.

This task is intentionally data-only — no new PHP classes. Follow the exact pattern of `database/seeders/BuffSkillSeeder.php` (already in this codebase) for skill+effect creation, and `database/migrations/2026_08_22_120000_add_spellcasting_skill_and_magic_skill_requirements.php`'s `addRequirements()`-style helper (already used in `database/seeders/AttackSkillSeeder.php` from the previous session) for `magic_skill_requirements` rows.

- [ ] **Step 1: Set `power_coefficient` on the 3 existing attack spells and re-tune their min/max**

The previous session's spells baked the full intended damage into `min_damage`/`max_damage` (already ×1.3 of weapon-tier damage, with intelligence bonus applied as a separate multiplicative step on top). Now that `MagicHitCalculator` splits damage into `random(min,max) + round(magicPower × power_coefficient)`, lower `min_damage`/`max_damage` back toward the roll-only portion so total expected damage stays in the same ballpark once a mid-investment caster's intelligence contributes:

| Slug | level | old min-max | new min-max | power_coefficient |
|---|---|---|---|---|
| `fire_spark` | 1 | 4-7 | 4-7 | 0.30 |
| `flame_barrage` | 20 | 21-30 | 21-30 | 0.30 |
| `incinerating_vortex` | 55 | 59-87 | 59-87 | 0.30 |

(Min/max stay the same — only `power_coefficient` is newly set, since at low-to-mid intelligence investment the formula's `magicPower × power_coefficient` term is already a small addition on top, matching the ballpark the previous session already balanced by eye. Real tuning happens in Task 13 via `battle:simulate-pve`, not here — this step only makes the column non-zero so the new formula path is exercised at all.)

- [ ] **Step 2: Write `MagicBookStarterSeeder`**

Create `database/seeders/MagicBookStarterSeeder.php`. This is long but entirely mechanical — mirror `BuffSkillSeeder`'s structure (Effects first, then Skills, then link to books, then attach nothing to a player — books go through the backpack/shop, not direct attach):

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Skill;
use App\Modules\MagicSkill\Domain\Enums\MagicSkillRequirementType;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
use App\Modules\Player\Domain\Enums\PlayerStatKey;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Стартовый набор книг заклинаний — см. спеку, раздел «Стартовый набор — 7 книг».
 * 3 книги оборачивают уже существующие атакующие заклинания (id из AttackSkillSeeder
 * прошлой сессии), 4 — новые (DoT/heal/buff/debuff), по одному архетипу магии на
 * первый релиз.
 */
class MagicBookStarterSeeder extends Seeder
{
    private const SPELL_SKILL_NAME = 'Колдовство';

    public function run(): void
    {
        if (ShareItem::where('name', 'Книга: Огненная искра')->exists()) {
            $this->command->info('MagicBookStarterSeeder: уже существует, пропускаем.');

            return;
        }

        DB::transaction(function () {
            $spellSkillId = (int) Skill::where('name', self::SPELL_SKILL_NAME)->value('id');

            if ($spellSkillId === 0) {
                $this->command->warn('MagicBookStarterSeeder: навык «Колдовство» не найден — прогоните миграцию 2026_08_22_120000 и AttackSkillSeeder сначала.');

                return;
            }

            $this->bookifyExistingAttackSpells();
            $this->createDotSpell($spellSkillId);
            $this->createHealSpell($spellSkillId);
            $this->createBuffSpell($spellSkillId);
            $this->createDebuffSpell($spellSkillId);

            $this->command->info('MagicBookStarterSeeder: 7 книг созданы.');
        });
    }

    private function bookifyExistingAttackSpells(): void
    {
        $spells = [
            'fire_spark' => ['name' => 'Книга: Огненная искра', 'power_coefficient' => 0.30],
            'flame_barrage' => ['name' => 'Книга: Огненный залп', 'power_coefficient' => 0.30],
            'incinerating_vortex' => ['name' => 'Книга: Испепеляющий вихрь', 'power_coefficient' => 0.30],
        ];

        foreach ($spells as $slug => $data) {
            $skill = MagicSkill::where('slug', $slug)->first();

            if ($skill === null) {
                $this->command->warn(sprintf('MagicBookStarterSeeder: заклинание %s не найдено, пропускаю книгу.', $slug));

                continue;
            }

            $skill->update(['power_coefficient' => $data['power_coefficient']]);

            $this->makeBook($data['name'], $skill, priceMultiplier: 200);
        }
    }

    private function createDotSpell(int $spellSkillId): void
    {
        $effect = Effect::create([
            'name' => 'Ожог',
            'slug' => 'burn',
            'type' => 'debuff',
            'description' => 'Периодический урон огнём',
            'duration' => 6,
            'is_stackable' => false,
            'max_stacks' => 1,
            'tick_interval' => 2,
            'value_per_tick' => 0, // перекрывается MagicHitCalculator-расчётом при касте — см. MagicAttackStrategy
            'stat_modifiers' => null,
            'is_dispellable' => true,
        ]);

        $skill = MagicSkill::create([
            'name' => 'Тлеющая рана',
            'slug' => 'smoldering_wound',
            'description' => 'Поджигает цель — урон каждые 2 секунды в течение 6 секунд.',
            'type' => 'attack',
            'target_type' => 'enemy',
            'skill_id' => $spellSkillId,
            'mana_cost' => 15,
            'min_damage' => 2,
            'max_damage' => 4,
            'power_coefficient' => 0.12,
            'base_healing' => 0,
            'cooldown' => 8,
            'level' => 10,
            'is_passive' => false,
        ]);
        $skill->skillEffects()->attach($effect->id, ['chance' => 100]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 10],
            [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 12],
            [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 8],
        ]);

        $this->makeBook('Книга: Тлеющая рана', $skill);
    }

    private function createHealSpell(int $spellSkillId): void
    {
        $skill = MagicSkill::create([
            'name' => 'Малое исцеление плоти',
            'slug' => 'minor_flesh_mending',
            'description' => 'Восстанавливает здоровье, усиливается интеллектом заклинателя.',
            'type' => 'heal',
            'target_type' => 'all',
            'skill_id' => $spellSkillId,
            'mana_cost' => 18,
            'min_damage' => 0,
            'max_damage' => 0,
            'power_coefficient' => 0.35,
            'base_healing' => 40,
            'cooldown' => 20,
            'level' => 5,
            'is_passive' => false,
        ]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 5],
            [MagicSkillRequirementType::STAT, PlayerStatKey::WISDOM, null, 10],
        ]);

        $this->makeBook('Книга: Малое исцеление плоти', $skill);
    }

    private function createBuffSpell(int $spellSkillId): void
    {
        $effect = Effect::create([
            'name' => 'Прилив магии',
            'slug' => 'arcane_surge',
            'type' => 'buff',
            'description' => '+25% к магической атаке на 15 секунд',
            'duration' => 15,
            'is_stackable' => false,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'value_per_tick' => 0,
            'stat_modifiers' => [
                ['type' => 'magic_attack', 'value' => 25, 'is_percent' => true],
            ],
            'is_dispellable' => true,
        ]);

        $skill = MagicSkill::create([
            'name' => 'Прилив магии',
            'slug' => 'arcane_surge_skill',
            'description' => 'Временно усиливает магическую атаку заклинателя на 25%.',
            'type' => 'buff',
            'target_type' => 'self',
            'skill_id' => $spellSkillId,
            'mana_cost' => 22,
            'min_damage' => 0,
            'max_damage' => 0,
            'power_coefficient' => 0.0,
            'base_healing' => 0,
            'cooldown' => 30,
            'level' => 15,
            'is_passive' => false,
        ]);
        $skill->skillEffects()->attach($effect->id, ['chance' => 100]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 15],
            [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 18],
        ]);

        $this->makeBook('Книга: Прилив магии', $skill);
    }

    private function createDebuffSpell(int $spellSkillId): void
    {
        $effect = Effect::create([
            'name' => 'Разъедающая ржавчина',
            'slug' => 'corroding_rust',
            'type' => 'debuff',
            'description' => 'Снижает броню цели на 10 секунд',
            'duration' => 10,
            'is_stackable' => false,
            'max_stacks' => 1,
            'tick_interval' => 1,
            'value_per_tick' => 0,
            'stat_modifiers' => [
                ['type' => 'armor', 'value' => -15, 'is_percent' => false],
            ],
            'is_dispellable' => true,
        ]);

        $skill = MagicSkill::create([
            'name' => 'Разъедающая ржавчина',
            'slug' => 'corroding_rust_skill',
            'description' => 'Разъедает броню цели, снижая защиту на 10 секунд.',
            'type' => 'attack',
            'target_type' => 'enemy',
            'skill_id' => $spellSkillId,
            'mana_cost' => 20,
            'min_damage' => 3,
            'max_damage' => 6,
            'power_coefficient' => 0.15,
            'base_healing' => 0,
            'cooldown' => 12,
            'level' => 18,
            'is_passive' => false,
        ]);
        $skill->skillEffects()->attach($effect->id, ['chance' => 100]);

        $this->addRequirements($skill->id, $spellSkillId, [
            [MagicSkillRequirementType::LEVEL, null, null, 18],
            [MagicSkillRequirementType::STAT, PlayerStatKey::INTELLIGENCE, null, 20],
            [MagicSkillRequirementType::SKILL, null, $spellSkillId, 3],
        ]);

        $this->makeBook('Книга: Разъедающая ржавчина', $skill);
    }

    private function makeBook(string $name, MagicSkill $skill, int $priceMultiplier = 150): void
    {
        $book = ShareItem::create([
            'name' => $name,
            'type' => ShareItemType::BOOK,
            'description' => sprintf('Обучает заклинанию «%s». Расходуется при изучении.', $skill->name),
            'is_sell' => true,
            'is_give' => true,
            'price' => $priceMultiplier * max(1, $skill->level),
        ]);

        MagicSkillBook::create(['share_item_id' => $book->id, 'magic_skill_id' => $skill->id]);
    }

    /** @param  array<int, array{0: MagicSkillRequirementType, 1: ?PlayerStatKey, 2: ?int, 3: int}>  $requirements */
    private function addRequirements(int $magicSkillId, int $spellSkillId, array $requirements): void
    {
        foreach ($requirements as [$type, $statKey, $skillId, $minValue]) {
            DB::table('magic_skill_requirements')->insert([
                'magic_skill_id' => $magicSkillId,
                'type' => $type->value,
                'stat_key' => $statKey?->value,
                'skill_id' => $skillId,
                'min_value' => $minValue,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
```

Check `App\Modules\Share\Infrastructure\Persistence\Models\ShareItem`'s actual fillable fields for `is_sell`/`is_give`/`price` before running this — the previous session's item work already established these exist and default appropriately (`is_sell=false, is_give=false` on quest items), so confirm the exact column names with `docker exec onlinegame-php-www-1 php artisan tinker --execute="dd((new App\Modules\Share\Infrastructure\Persistence\Models\ShareItem)->getFillable());"` if unsure.

- [ ] **Step 3: Run the seeder against the real `game` database**

```bash
docker exec onlinegame-php-www-1 printenv DB_DATABASE
docker exec onlinegame-php-www-1 php artisan tinker --execute="echo config('database.connections.mysql.database');"
docker exec onlinegame-php-www-1 php artisan db:seed --class=Database\\Seeders\\MagicBookStarterSeeder --force
```
Expected: both commands print `game`, seeder logs "7 книг созданы."

- [ ] **Step 4: Verify via tinker**

```bash
docker exec onlinegame-php-www-1 php artisan tinker --execute="
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkillBook;
\$books = MagicSkillBook::with(['shareItem', 'magicSkill.requirements'])->get();
foreach (\$books as \$b) {
    echo \$b->shareItem->name . ' -> ' . \$b->magicSkill->name . ' (' . \$b->magicSkill->requirements->count() . ' req)' . PHP_EOL;
}
"
```
Expected: 7 lines, each with a non-zero requirement count except the buff spell if you chose to give it only a level requirement (still ≥ 1).

- [ ] **Step 5: Lint and commit**

```bash
php -l database/seeders/MagicBookStarterSeeder.php
./vendor/bin/pint database/seeders/MagicBookStarterSeeder.php

git add database/seeders/MagicBookStarterSeeder.php
git commit -m "feat(magic): starter set of 7 spell books (3 existing attack tiers + DoT/heal/buff/debuff)"
```

---

### Task 13: Admin UI — book/skill configuration

**Files:**
- Modify: `app/Http/Controllers/Admin/ItemController.php` (or wherever `ShareItem` create/edit already lives — confirm the exact controller by reading `routes/admin.php` for the `items` resource route)
- Create/Modify: admin view for `MagicSkillBook` linking (a `magic_skill_id` select added to the item edit form when `type === book`) and `power_coefficient` field on the `MagicSkill` admin form (find the existing magic-skill admin CRUD — confirm its controller/routes first)

**Interfaces:**
- Consumes: `MagicSkillBook`, `MagicSkill::$power_coefficient`, `MagicSkillRequirement` (all from Tasks 2-8).

This task's exact file set depends on how the admin panel currently manages `magic_skills` — the previous sessions' work only ever touched `magic_skills` via seeders and tinker, never through `routes/admin.php` or an `Admin\MagicSkillController`. Check first:

```bash
grep -n "magic" routes/admin.php
find app/Http/Controllers/Admin -iname "*Magic*"
```

- [ ] **Step 1: If no admin CRUD exists for `magic_skills` today, scope this task down**

If the grep/find above come back empty, there is no existing admin magic-skill screen to extend — building one from scratch (list/create/edit forms, routes, views) is a full CRUD feature in its own right and is out of this plan's bite-sized-task budget. In that case:
- Skip building new admin screens in this task.
- Instead, add a single read-only tinker/artisan verification (already covered by Task 12, Step 4) as the interim way to confirm book↔skill wiring.
- Note in the commit message that a dedicated admin screen is deferred, and leave a one-line TODO comment in `docs/superpowers/specs/2026-08-22-magic-combat-system-design.md`'s "Область, оставленная вне этой спеки" section pointing at this gap for a future plan.

- [ ] **Step 2: If an admin CRUD for `magic_skills` already exists, extend it**

Add a `power_coefficient` number input to its edit/create form (mirroring how `mana_cost`/`cooldown` are already rendered there), and on the linked `ShareItem` edit form (when `type === 'book'`), add a `magic_skill_id` select populated from `MagicSkill::pluck('name', 'id')`, wired to create/update the corresponding `MagicSkillBook` row on save. Follow the exact Blade/controller patterns already used for the nearest analogous existing admin form (e.g. how `share_item_requirements` are edited on the item admin page, if that exists) — read that file before writing new markup, don't invent new admin UI conventions.

- [ ] **Step 3: Manual verification (no automated test — this is admin-only CRUD wiring)**

Log into `/admin`, navigate to whichever screen Step 2 touched, and confirm: editing `power_coefficient` on a `MagicSkill` persists; linking a `BOOK`-type `ShareItem` to a `MagicSkill` creates/updates a `MagicSkillBook` row (verify with the tinker query from Task 12, Step 4).

- [ ] **Step 4: Lint and commit (only if Step 2 produced code)**

```bash
php -l <every file touched in Step 2>
./vendor/bin/pint <every file touched in Step 2>

git add <every file touched in Step 2>
git commit -m "feat(magic): admin UI for power_coefficient and book<->skill linking"
```

If Step 1's scope-down applied instead, commit only the spec's one-line TODO addition:

```bash
git add docs/superpowers/specs/2026-08-22-magic-combat-system-design.md
git commit -m "docs(magic): note deferred admin CRUD for magic_skills/magic_skill_books"
```

---

### Task 14: Extend `battle:simulate-pve` for magic builds + final checklist pass

**Files:**
- Modify: `app/Modules/Battle/Presentation/Console/SimulatePveEncounter.php`
- Test: manual (this is a simulation/tuning tool, not unit-testable business logic)

**Interfaces:**
- Consumes: `MagicHitCalculator`, `MagicSkill` (the 7 starter spells from Task 12).

- [ ] **Step 1: Read the current `SimulatePveEncounter` command in full**

```bash
cat app/Modules/Battle/Presentation/Console/SimulatePveEncounter.php
```
Note its existing `--dodge=`, build-selection, and output-table structure before adding to it — match its style exactly rather than introducing a second, differently-shaped command.

- [ ] **Step 2: Add a `--build=mage` option (or equivalent) that runs `MagicHitCalculator` instead of `HitCalculator`**

Extend the command's build-selection logic with a "mage" build entry that pulls `intelligence`/`wisdom` from the same build-preset shape the existing tank/dodge/crit builds already use, and computes damage-per-round via `MagicHitCalculator::hit()` using one of the 7 starter spells' `min_damage`/`max_damage`/`power_coefficient` instead of `left_min_dmg`/`right_min_dmg`. Reuse the command's existing output table/loop structure — only the per-round damage source changes for this build.

- [ ] **Step 3: Run the simulator at levels 20, 55, and 100 — the spec's required tuning checkpoints**

```bash
docker exec onlinegame-php-www-1 php artisan battle:simulate-pve --build=mage --level=20
docker exec onlinegame-php-www-1 php artisan battle:simulate-pve --build=mage --level=55
docker exec onlinegame-php-www-1 php artisan battle:simulate-pve --build=mage --level=100
```
Compare the mage build's DPS/survivability against the existing tank/dodge/crit builds' output at the same levels (run those too, without `--build=mage`, if not already part of the command's default output). Per the spec: no build should have an unconditional advantage. If the mage build is wildly over- or under-performing, adjust `power_coefficient` and/or `MAGIC_RESIST_CONSTANT` (Task 3) and re-run — this is the "финальная настройка — по симуляциям" step both the spec and codex's review called for; do not hand-tune these numbers without running the simulator.

- [ ] **Step 4: Run the full automated checklist from the spec's "Тестирование" section**

Re-run every test written across Tasks 1-11 in one pass:

```bash
docker exec onlinegame-php-www-1 php artisan test --filter="PlayerStatFormulasTest|MagicResistanceDerivationTest|MagicHitCalculatorTest|MonsterCombatantTest|MagicAttackStrategyTest|MagicCastGuardTest|LearnMagicSkillFromBookTest|PlayerStatServiceDebuffClampTest|BossDebuffDurationTest|DotTickFixedAtCastTimeTest|UseMagicSkillHealFormulaTest"
```
Expected: all PASS. Cross-check against the spec's checklist item-by-item:
- Книга не тратится при непройденных требованиях → `LearnMagicSkillFromBookTest::test_learning_fails_and_book_is_not_consumed_when_requirement_unmet`
- Повторное изучение невозможно → `LearnMagicSkillFromBookTest::test_learning_twice_is_rejected_on_the_second_attempt`
- Двойной запрос на каст не списывает ману дважды / не обходит кулдаун → `MagicCastGuardTest` (all 4 tests — true concurrent-request testing isn't practical in synchronous PHPUnit; the `lockForUpdate()` + single-transaction design in Task 6 is the actual guarantee, verified by code review, not a race-condition test)
- Экипировка не проверяет требования повторно → covered by Task 1's revert + the absence of any requirement check in `UpdateEquippedMagicSkills` post-Task-1
- DoT обновляет длительность, не стакается, сила тика фиксирована → `DotTickFixedAtCastTimeTest`
- Бафы/дебафы применяются и снимаются, стат пересчитан → `PlayerStatServiceDebuffClampTest`, `BossDebuffDurationTest`
- Опыт «Колдовства» начисляется только при валидном применении → already true via `AttackService::gainExperienceSkill($player, $hit->getSkill(), ...)`, unchanged by this plan — confirm with a quick tinker cast-and-check if not already covered by an existing test
- Магия не уворачивается/не критует/не блокируется → `MagicHitCalculatorTest` (no dodge/crit fields exist on its return path at all — structurally guaranteed, not just tested)
- Мудрость (MP + resist) не даёт вырожденного билда на 20/55/100 → Step 3's simulator run

- [ ] **Step 5: Lint and commit**

```bash
php -l app/Modules/Battle/Presentation/Console/SimulatePveEncounter.php
./vendor/bin/pint app/Modules/Battle/Presentation/Console/SimulatePveEncounter.php

git add app/Modules/Battle/Presentation/Console/SimulatePveEncounter.php
git commit -m "feat(magic): extend battle:simulate-pve with a mage build, tune power_coefficient/MAGIC_RESIST_CONSTANT from results"
```

---

## Self-Review Notes

**Spec coverage:** every numbered rule and section of `docs/superpowers/specs/2026-08-22-magic-combat-system-design.md` maps to a task — правила 1-4 (Task 3/4 formula, Task 1/8 learn-time gate, Task 10 DoT-fixed-tick, Task 14 wisdom checklist), Данные и миграции (Tasks 2, 4, 7), Формула (Task 3), Общий механизм каста (Task 6), Изучение по книгам (Tasks 7-8), DoT/баф/дебаф/лечение (Tasks 9-11), Стартовый набор (Task 12), Админка (Task 13), Тестирование (Task 14). "Область, оставленная вне этой спеки" is intentionally not covered — physical-attack concurrency, elemental resistances, and per-boss debuff tuning stay out per the spec itself.

**Placeholder scan:** two intentional, explicitly-flagged exceptions exist — Task 8 (`LearnMagicSkillFromBookTest`'s `markTestIncomplete()`) and Task 9/11 (same pattern) — because the real dependency (`BackpackService` method names, `User↔Player` FK name, `PlayerStatService`'s modifier-injection entry point) is unknown at plan-writing time and both tasks name the exact grep/read command to resolve it before the placeholder can be removed, per the "No Placeholders" rule's own allowance for naming a concrete prerequisite rather than guessing.

**Type consistency:** `FightHitInterface::getMagicResistance()`/`getMagicAttack()` (Task 2) are used identically in `MagicHitCalculator` (Task 3), `StubCombatant`/`MonsterCombatant` (Tasks 3, 5), and `Monster`/`SimulateBattleTriangle` (Task 2) — same names, same `int` return type throughout. `FightHitDTO::addAppliedEffect()`'s new signature (Task 10) is introduced in Task 4 as a forward reference with an explicit `TODO(Task 10)` marker and step-by-step fix instructions, so a sequential implementer isn't left with a silently broken build between those two tasks.
