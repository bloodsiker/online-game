<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\MagicSkill\Application\UseCases\UseMagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\EloquentMagicSkillRepository;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\Services\PlayerEquipmentLoader;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\MagicCastGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UseMagicSkillHealFormulaTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();
    }

    public function test_heal_amount_scales_with_intelligence_not_flat_base_healing(): void
    {
        // Arrange: create a healer with high intelligence and a heal skill with
        // base_healing=50, power_coefficient=0.4 — assert the applied heal is
        // strictly greater than 50 (proves intelligence contributed).

        // Create race
        DB::table('races')->insert(['id' => 1, 'name' => 'Человек']);

        // Create a location for players
        DB::table('locations')->insert([
            'id' => 1,
            'name' => 'Test Location',
        ]);

        // Create player with high intelligence (200)
        $player = Player::forceCreate([
            'user_id' => 1,
            'race_id' => 1,
            'lvl' => 10,
            'hp_max' => 100,
            'hp_now' => 100,
            'mp_max' => 100,
            'mp_now' => 100,
            'strength' => 10.0,
            'agility' => 10.0,
            'intuition' => 10.0,
            'wisdom' => 10.0,
            'intelligence' => 200.0,
            'endurance' => 10.0,
            'experience_multiplier' => 1.0,
            'free_stats' => 0,
        ]);

        // Create user for the player
        User::forceCreate([
            'id' => 1,
            'player_id' => $player->id,
            'name' => 'Healer',
            'email' => 'healer@example.test',
            'password' => 'password',
            'location_id' => 1,
            'last_online_at' => now(),
        ]);

        // Create heal skill: base_healing=50, power_coefficient=0.4
        $skill = MagicSkill::create([
            'name' => 'Heal',
            'slug' => 'heal',
            'type' => 'heal',
            'target_type' => 'ally',
            'is_passive' => false,
            'mana_cost' => 20,
            'cooldown' => 0,
            'level' => 1,
            'base_healing' => 50,
            'power_coefficient' => 0.4,
        ]);

        // Give player the skill
        DB::table('player_magic_skills')->insert([
            'player_id' => $player->id,
            'magic_skill_id' => $skill->id,
            'is_equipped' => true,
            'cooldown_end_at' => null,
            'sort_order' => 0,
        ]);

        // Act: use the heal spell
        $user = User::findOrFail(1);

        // Create service instances
        $equipmentLoader = new PlayerEquipmentLoader;
        $runePassiveService = new PlayerRunePassiveService($equipmentLoader);
        $repository = new EloquentMagicSkillRepository($runePassiveService);
        $statService = new PlayerStatService($equipmentLoader);
        $magicHitCalc = new MagicHitCalculator;
        $effectService = $this->createMock(BattleEffectService::class);
        $castGuard = app(MagicCastGuard::class);

        $useCase = new UseMagicSkill(
            readRepository: $repository,
            writeRepository: $repository,
            statService: $statService,
            effectService: $effectService,
            castGuard: $castGuard,
            magicHitCalc: $magicHitCalc,
        );

        $result = $useCase->execute($user, $skill->id, $player->id);

        // Assert: healing was applied
        if ($result->status !== 'success') {
            throw new \Exception("Heal spell failed: {$result->message}");
        }
        $this->assertSame('success', $result->status, 'Heal spell should succeed');

        // Get the fresh player to check healed HP
        $healedPlayer = $player->fresh();

        // With intelligence 200 + no equipment (magic_attack=0), the formula is:
        // heal = base_healing + (intelligence + magic_attack) * power_coefficient
        // heal = 50 + (200 + 0) * 0.4 = 50 + 80 = 130
        // The player started with 100/100 HP, so should be at 100 (capped at max)

        // But let's test with a damaged player so we can see the healing
        $damagedPlayer = Player::forceCreate([
            'user_id' => 2,
            'race_id' => 1,
            'lvl' => 10,
            'hp_max' => 500,
            'hp_now' => 50,
            'mp_max' => 100,
            'mp_now' => 100,
            'strength' => 10.0,
            'agility' => 10.0,
            'intuition' => 10.0,
            'wisdom' => 10.0,
            'intelligence' => 200.0,
            'endurance' => 10.0,
            'experience_multiplier' => 1.0,
            'free_stats' => 0,
        ]);

        User::forceCreate([
            'id' => 2,
            'player_id' => $damagedPlayer->id,
            'name' => 'Target',
            'email' => 'target@example.test',
            'password' => 'password',
            'location_id' => 1,
            'last_online_at' => now(),
        ]);

        $healerUser = User::findOrFail(1);
        $targetUser = User::findOrFail(2);

        // Heal the target
        $result = $useCase->execute($healerUser, $skill->id, $damagedPlayer->id);

        $this->assertSame('success', $result->status, 'Heal spell should succeed on target');

        $healed = $damagedPlayer->fresh();

        // Verify that healing occurred (HP changed from 50 to something higher)
        $this->assertGreaterThan(50, $healed->hp_now, 'Target HP should increase from healing');

        // Calculate the actual healing amount
        $actualHeal = $healed->hp_now - 50;

        // Most importantly: the actual heal must be strictly greater than base_healing.
        // This proves intelligence contributed via power_coefficient=0.4.
        // Without the formula, healing would be exactly 50, but with intelligence,
        // it must be 50 + (intelligence + magic_attack) * power_coefficient.
        $this->assertGreaterThan(
            $skill->base_healing,
            $actualHeal,
            'Actual healing ('.($actualHeal).') must be strictly greater than base_healing=50 (proves intelligence contributes via power_coefficient)',
        );
    }

    private function createTables(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('races', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('race_id');
            $table->integer('lvl')->default(1);
            $table->integer('hp_max')->default(100);
            $table->integer('hp_now')->default(100);
            $table->integer('mp_max')->default(100);
            $table->integer('mp_now')->default(100);
            $table->float('strength')->default(1.0);
            $table->float('agility')->default(1.0);
            $table->float('intuition')->default(1.0);
            $table->float('wisdom')->default(1.0);
            $table->float('intelligence')->default(1.0);
            $table->float('endurance')->default(1.0);
            $table->float('experience_multiplier')->default(1.0);
            $table->integer('free_stats')->default(0);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id')->nullable();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('prev_location_id')->nullable();
            $table->timestamp('last_online_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->integer('money')->default(0);
            $table->integer('diamond')->default(0);
            $table->integer('warehouse_count')->default(50);
            $table->integer('bag_count')->default(25);
            $table->integer('slot_count')->default(3);
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->string('slug')->nullable();
            $table->string('type')->default('attack');
            $table->string('target_type')->default('enemy');
            $table->boolean('is_passive')->default(false);
            $table->integer('mana_cost')->default(0);
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->integer('cooldown')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->integer('base_healing')->default(0);
            $table->timestamps();
        });

        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->unique(['player_id', 'magic_skill_id']);
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('type')->nullable();
            $table->timestamps();
        });

        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->boolean('is_equipped')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->integer('duration')->default(0);
            $table->json('stat_modifiers')->nullable();
            $table->timestamps();
        });

        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->unsignedSmallInteger('chance')->default(100);
            $table->timestamps();

            $table->unique(['magic_skill_id', 'effect_id']);
        });

        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->timestamps();
        });

        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->integer('upgrade_lvl')->default(0);
            $table->timestamps();
        });

        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->string('slot')->nullable();
            $table->timestamps();
        });

        Schema::create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->integer('value')->default(0);
            $table->string('value_type')->default('flat');
            $table->timestamps();
        });

        Schema::create('item_gems', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('gem_id');
            $table->timestamps();
        });

        Schema::create('gems', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->json('gem_stats')->nullable();
            $table->timestamps();
        });

        Schema::create('item_runes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('rune_id');
            $table->timestamps();
        });

        Schema::create('runes', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->nullable();
            $table->json('stats')->nullable();
            $table->timestamps();
        });

        Schema::create('player_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('effect_type');
            $table->integer('value')->default(0);
            $table->string('value_type')->default('flat');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}
