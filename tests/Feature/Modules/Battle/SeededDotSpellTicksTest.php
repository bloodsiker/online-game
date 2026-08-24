<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Domain\Enums\ActiveEffectType;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Database\Seeders\MagicBookStarterSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Final review, CRITICAL 1: у DoT-заклинания из стартового набора книг эффект
 * был засеян под slug 'burn_spell', которого нет в ActiveEffectType — строка
 * MonsterActiveEffect создавалась с type = NULL и никогда не тикала и не
 * истекала (мёртвый контент). Тест гоняет ИМЕННО засеянный сидером эффект
 * через боевой цикл, а не собранный руками в тесте.
 */
class SeededDotSpellTicksTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        // Полный набор миграций на sqlite не поднимается (давняя проблема репозитория —
        // 2026_06_17_000001 добавляет уже существующую колонку dungeons.death_behavior),
        // поэтому схема под сидер собирается вручную — как в соседних тестах модуля.
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('attack');
            $table->string('target_type')->default('enemy');
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('mana_cost')->default(0);
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->integer('base_healing')->default(0);
            $table->integer('cooldown')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->boolean('is_passive')->default(false);
            $table->json('effects')->nullable();
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('effect');
            $table->string('slug');
            $table->string('type')->default('debuff');
            $table->text('description')->nullable();
            $table->integer('chance')->default(0);
            $table->integer('duration')->default(0);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stacks')->default(1);
            $table->integer('tick_interval')->default(1);
            $table->integer('value_per_tick')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_dispellable')->default(true);
            $table->timestamps();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(100);
            $table->timestamps();
        });
        Schema::create('magic_skill_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->string('type');
            $table->string('stat_key')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('min_value')->default(0);
            $table->timestamps();
        });
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('resource');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('is_two_hand')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->timestamps();
        });
        Schema::create('magic_skill_books', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now')->default(100);
            $table->integer('hp_max')->default(100);
            $table->boolean('is_drop_money')->default(false);
            $table->timestamps();
        });
        Schema::create('monster_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_monster_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->integer('rounds')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        $spellSkillId = DB::table('skills')->insertGetId(['name' => 'Колдовство']);

        // Три атакующих заклинания из AttackSkillSeeder — сидер книг оборачивает
        // их в книги (bookifyExistingAttackSpells) и без них ругается варнингом.
        foreach (['fire_spark', 'flame_barrage', 'incinerating_vortex'] as $slug) {
            MagicSkill::create([
                'name' => $slug, 'slug' => $slug, 'type' => 'attack', 'target_type' => 'enemy',
                'skill_id' => $spellSkillId, 'level' => 1,
            ]);
        }

        $this->artisan('db:seed', ['--class' => MagicBookStarterSeeder::class])->run();
    }

    public function test_seeded_dot_spell_resolves_to_a_live_active_effect_type(): void
    {
        $skill = MagicSkill::where('slug', 'smoldering_wound')->firstOrFail();
        $effect = $skill->skillEffects()->firstOrFail();

        $this->assertSame('burn', $effect->slug, 'сидер обязан переиспользовать существующую строку effects.slug=burn');
        $this->assertSame(
            ActiveEffectType::BURN,
            ActiveEffectType::tryFrom($effect->slug),
            'слаг засеянного эффекта обязан резолвиться в ActiveEffectType, иначе DoT никогда не тикнет',
        );
        $this->assertSame(1, DB::table('effects')->where('slug', 'burn')->count(), 'дубликат строки burn создаваться не должен');
    }

    public function test_seeded_dot_spell_actually_ticks_and_expires_in_battle(): void
    {
        $skill = MagicSkill::where('slug', 'smoldering_wound')->firstOrFail();
        $effect = $skill->skillEffects()->firstOrFail();

        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id, 'hp_now' => 500, 'hp_max' => 500]);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        // Каст: MagicAttackStrategy передаёт сюда посчитанный урон как tickValueOverride.
        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 7);

        $row = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->first();
        $this->assertNotNull($row);
        $this->assertSame('burn', $row->type, 'type = NULL означал бы, что строку никто никогда не прочитает');

        $hpBefore = $locMonster->hp_now;

        // Сразу после наложения тика нет: время, а не число ходов, управляет DoT.
        $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO);
        $this->assertSame($hpBefore, $locMonster->hp_now);

        DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)
            ->update(['last_tick_at' => now()->subSeconds(2)]);
        $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO);
        $this->assertSame($hpBefore - 7, $locMonster->hp_now);

        // При следующем обращении к мобу забираются пропущенные тики до expires_at.
        DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)
            ->update(['last_tick_at' => now()->subSeconds(4), 'expires_at' => now()]);
        $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO);
        $this->assertSame($hpBefore - 21, $locMonster->hp_now);
        $this->assertSame(
            0,
            DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->count(),
            'после истечения времени строка эффекта обязана быть удалена',
        );
    }
}
