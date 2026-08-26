<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Modules\MagicSkill\Application\Services\MagicCastGuard;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
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
            // Player::$attributes defaults experience_multiplier to 1.0 on every insert.
            $table->float('experience_multiplier')->default(1.0);
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->integer('mana_cost')->default(0);
            $table->integer('cooldown')->default(0);
            // MagicSkill::$attributes defaults level/base_healing/is_passive on every insert.
            $table->unsignedInteger('level')->default(1);
            $table->integer('base_healing')->default(0);
            $table->boolean('is_passive')->default(false);
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
        $player = Player::forceCreate(['mp_now' => 5]);
        $skill = MagicSkill::create(['name' => 'x', 'mana_cost' => 8, 'cooldown' => 0]);
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);

        $result = app(MagicCastGuard::class)->tryConsume($player, $skill);

        $this->assertFalse($result->ok);
        $this->assertSame(5, $player->fresh()->mp_now);
    }

    public function test_rejects_when_still_on_cooldown(): void
    {
        $player = Player::forceCreate(['mp_now' => 100]);
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
        $player = Player::forceCreate(['mp_now' => 100]);
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
        $player = Player::forceCreate(['mp_now' => 100]);
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
