<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Monster;

use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Domain\Services\MonsterCombatantFactory;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterActiveEffect;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonsterCombatantFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->integer('armor')->default(0);
            $table->integer('dodge')->default(0);
            $table->integer('critical')->default(0);
            $table->integer('lvl')->default(1);
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });

        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->boolean('is_drop_money')->default(false);
            $table->timestamps();
        });

        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->integer('chance')->default(0);
            $table->integer('duration')->default(0);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stacks')->default(1);
            $table->integer('tick_interval')->default(1);
            $table->json('stat_modifiers')->nullable();
            $table->timestamps();
        });

        Schema::create('monster_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_monster_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->timestamps();
        });
    }

    private function createLocMonster(int $armor = 50, int $dodge = 10, int $critical = 5): MonsterOnLocation
    {
        $monster = Monster::create([
            'armor' => $armor,
            'dodge' => $dodge,
            'critical' => $critical,
            'lvl' => 10,
        ]);

        return MonsterOnLocation::create(['monster_id' => $monster->id]);
    }

    /** @param  array<int|string, mixed>  $statModifiers  Raw payload stored as Effect::$stat_modifiers */
    private function attachActiveEffect(MonsterOnLocation $locMonster, array $statModifiers): void
    {
        $effect = Effect::create(['stat_modifiers' => $statModifiers]);

        MonsterActiveEffect::create([
            'location_monster_id' => $locMonster->id,
            'effect_id' => $effect->id,
        ]);
    }

    public function test_flat_armor_debuff_reduces_armor(): void
    {
        $locMonster = $this->createLocMonster(armor: 50, dodge: 10);
        $this->attachActiveEffect($locMonster, [
            ['type' => 'armor', 'value' => -15.0, 'is_percent' => false],
        ]);

        $combatant = (new MonsterCombatantFactory)->build($locMonster, null);

        $this->assertSame(35, $combatant->getArmor());
        $this->assertSame(10, $combatant->getDodge(), 'unrelated stat must stay untouched');
    }

    public function test_percent_modifier_is_not_summed(): void
    {
        $locMonster = $this->createLocMonster(armor: 50, dodge: 10);
        $this->attachActiveEffect($locMonster, [
            ['type' => 'armor', 'value' => -15.0, 'is_percent' => false],
        ]);
        $this->attachActiveEffect($locMonster, [
            ['type' => 'armor', 'value' => -1000.0, 'is_percent' => true],
        ]);

        $combatant = (new MonsterCombatantFactory)->build($locMonster, null);

        $this->assertSame(35, $combatant->getArmor(), 'percent modifier must be ignored — only the flat -15 applies');
    }

    public function test_non_debuffable_stat_is_ignored(): void
    {
        $locMonster = $this->createLocMonster(armor: 50, dodge: 10, critical: 5);
        $this->attachActiveEffect($locMonster, [
            ['type' => 'critical', 'value' => -100.0, 'is_percent' => false],
        ]);

        $combatant = (new MonsterCombatantFactory)->build($locMonster, null);

        $this->assertSame(50, $combatant->getArmor());
        $this->assertSame(10, $combatant->getDodge());
        $this->assertSame(5, $combatant->getCritical(), 'critical is not in DEBUFFABLE_STATS, must pass through unchanged');
    }

    public function test_scalar_stat_modifier_entries_are_skipped_without_error(): void
    {
        $locMonster = $this->createLocMonster(armor: 50, dodge: 10);

        // Legacy/malformed shape seen in the wild (see GenerateSeed.php:456: `['attack' => 5]`)
        // — a flat key => value map instead of the expected list of
        // {type, value, is_percent} objects. Must not throw when a modifier
        // entry isn't an array.
        $this->attachActiveEffect($locMonster, ['attack' => 5]);

        $combatant = (new MonsterCombatantFactory)->build($locMonster, null);

        $this->assertSame(50, $combatant->getArmor());
        $this->assertSame(10, $combatant->getDodge());
    }
}
