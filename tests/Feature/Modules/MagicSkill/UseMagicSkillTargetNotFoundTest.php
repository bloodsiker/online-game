<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\MagicSkill\Application\UseCases\UseMagicSkill;
use App\Modules\MagicSkill\Infrastructure\Persistence\EloquentMagicSkillRepository;
use App\Modules\Player\Domain\Services\PlayerRunePassiveService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\MagicCastGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Регрессия на баг из ревью Задачи 6: findAllyTarget() может вернуть null
 * (цель покинула локацию/оффлайн >10 мин), и до фикса tryConsume() уже
 * успевал списать ману и выставить кулдаун прежде, чем метод возвращал
 * 404 «Цель не найдена» — без отката. Порядок должен быть: сначала
 * резолвим цель, и только потом консьюмим ману/кулдаун.
 */
class UseMagicSkillTargetNotFoundTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();

        DB::table('races')->insert(['id' => 1, 'name' => 'Человек']);
        DB::table('players')->insert([
            'id' => 1,
            'user_id' => 1,
            'race_id' => 1,
            'mp_now' => 50,
            'experience_multiplier' => 1.0,
        ]);
        DB::table('users')->insert([
            'id' => 1,
            'player_id' => 1,
            'name' => 'Игрок',
            'email' => 'player@example.test',
            'password' => 'password',
            'location_id' => 10,
            'last_online_at' => now(),
        ]);
        DB::table('magic_skills')->insert([
            'id' => 1,
            'name' => 'Лечение',
            'type' => 'heal',
            'is_passive' => false,
            'mana_cost' => 10,
            'cooldown' => 30,
            'base_healing' => 20,
            'level' => 1,
        ]);
        DB::table('player_magic_skills')->insert([
            'player_id' => 1,
            'magic_skill_id' => 1,
            'cooldown_end_at' => null,
            'is_equipped' => true,
            'sort_order' => 0,
        ]);
    }

    public function test_target_not_found_does_not_consume_mana_or_set_cooldown(): void
    {
        $repository = new EloquentMagicSkillRepository(
            $this->createMock(PlayerRunePassiveService::class),
        );

        $useCase = new UseMagicSkill(
            readRepository: $repository,
            writeRepository: $repository,
            statService: $this->createMock(PlayerStatService::class),
            effectService: $this->createMock(BattleEffectService::class),
            castGuard: new MagicCastGuard,
            magicHitCalc: new MagicHitCalculator,
        );

        $user = User::findOrFail(1);

        // Несуществующий id игрока — findAllyTarget() гарантированно вернёт null.
        $result = $useCase->execute($user, skillId: 1, targetPlayerId: 999999);

        $this->assertSame('error', $result->status);
        $this->assertSame(404, $result->httpCode);
        $this->assertSame('Цель не найдена', $result->message);

        $this->assertSame(50, DB::table('players')->where('id', 1)->value('mp_now'),
            'mana must not be touched when the target cannot be resolved');

        $this->assertNull(
            DB::table('player_magic_skills')
                ->where('player_id', 1)->where('magic_skill_id', 1)
                ->value('cooldown_end_at'),
            'cooldown must not be set when the target cannot be resolved'
        );
    }

    private function createTables(): void
    {
        Schema::create('races', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('race_id');
            $table->integer('mp_now')->default(10);
            $table->float('experience_multiplier')->default(1.0);
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
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
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
        });

        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->timestamps();
        });

        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(0);
            $table->timestamps();
        });
    }
}
