<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Monster;

use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MonsterHpRegenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->json('name')->nullable();
            $table->integer('lvl')->default(1);
            $table->integer('hp')->default(100);
            $table->integer('armor')->default(0);
            $table->integer('dodge')->default(0);
            $table->integer('critical')->default(0);
            $table->double('min_dmg')->default(0);
            $table->double('max_dmg')->default(0);
            $table->smallInteger('aggression')->default(0);
            $table->boolean('is_boss')->default(false);
            $table->boolean('does_not_regenerate_hp')->default(false);
            $table->timestamps();
        });

        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->timestamp('last_regen_at')->nullable();
            $table->unsignedBigInteger('clan_boss_stage_state_id')->nullable();
            $table->boolean('is_drop_money')->default(false);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_monster_marked_as_non_regenerating_keeps_its_current_hp(): void
    {
        Carbon::setTestNow('2026-10-12 12:00:00');
        $monster = $this->createMonster(doesNotRegenerateHp: true);
        $instance = $this->createInstance($monster);

        $instance->regenerate();

        $this->assertSame(50, $instance->fresh()->hp_now);
    }

    public function test_regular_monster_regeneration_remains_unchanged(): void
    {
        Carbon::setTestNow('2026-10-12 12:00:00');
        $monster = $this->createMonster(doesNotRegenerateHp: false);
        $instance = $this->createInstance($monster);

        $instance->regenerate();

        $this->assertSame(100, $instance->fresh()->hp_now);
    }

    private function createMonster(bool $doesNotRegenerateHp): Monster
    {
        return Monster::query()->create([
            'name' => 'Test Monster',
            'lvl' => 1,
            'hp' => 100,
            'does_not_regenerate_hp' => $doesNotRegenerateHp,
        ]);
    }

    private function createInstance(Monster $monster): MonsterOnLocation
    {
        $instance = MonsterOnLocation::query()->create([
            'monster_id' => $monster->id,
            'hp_now' => 50,
            'hp_max' => 100,
        ]);
        $instance->last_regen_at = now()->subSeconds(600);
        $instance->save();

        return $instance;
    }
}
