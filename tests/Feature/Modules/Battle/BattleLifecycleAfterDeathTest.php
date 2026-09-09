<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Battle\BattleFinder;
use App\Modules\Battle\Application\Services\Combat\BattleFinishService;
use App\Modules\Battle\Application\Services\DropService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BattleLifecycleAfterDeathTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedInteger('rounds')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->json('boss_metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });
        Schema::create('battle_rounds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedInteger('round_number');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->text('action')->nullable();
            $table->timestamps();
        });
    }

    public function test_battle_finishes_when_the_last_living_player_dies(): void
    {
        $battle = $this->battle();
        DB::table('battle_details')->insert([
            'battle_id' => $battle->id,
            'user_id' => 7,
            'status' => BattleDetailStatus::DEATH->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $finished = $this->finishService()->finishIfNoLivingPlayers($battle);

        $this->assertTrue($finished);
        $this->assertSame(BattleStatus::FINISH, $battle->fresh()->status);
    }

    public function test_battle_continues_while_another_player_is_alive(): void
    {
        $battle = $this->battle();
        DB::table('battle_details')->insert([
            [
                'battle_id' => $battle->id,
                'user_id' => 7,
                'status' => BattleDetailStatus::DEATH->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'battle_id' => $battle->id,
                'user_id' => 8,
                'status' => BattleDetailStatus::LIFE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->assertFalse($this->finishService()->finishIfNoLivingPlayers($battle));
        $this->assertSame(BattleStatus::ACTIVE, $battle->fresh()->status);
    }

    public function test_returning_player_is_reactivated_in_an_active_battle(): void
    {
        $battle = $this->battle();
        DB::table('battle_details')->insert([
            [
                'battle_id' => $battle->id,
                'user_id' => 7,
                'status' => BattleDetailStatus::DEATH->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'battle_id' => $battle->id,
                'user_id' => 8,
                'status' => BattleDetailStatus::LIFE->value,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
        Auth::setUser((new User)->forceFill(['id' => 7, 'location_id' => 2]));
        $location = (new Location)->forceFill(['id' => 2]);

        $found = (new BattleFinder(new BattleRepository))->findActiveForPlayer($location);

        $this->assertSame($battle->id, $found?->id);
        $this->assertDatabaseHas('battle_details', [
            'battle_id' => $battle->id,
            'user_id' => 7,
            'status' => BattleDetailStatus::LIFE->value,
        ]);
        $this->assertDatabaseHas('battle_rounds', [
            'battle_id' => $battle->id,
            'round_number' => 4,
            'user_id' => 7,
        ]);
    }

    private function battle(): Battle
    {
        return Battle::query()->create([
            'location_id' => 2,
            'status' => BattleStatus::ACTIVE,
            'rounds' => 3,
        ]);
    }

    private function finishService(): BattleFinishService
    {
        return new BattleFinishService(
            new BattleRepository,
            Mockery::mock(DropService::class),
        );
    }
}
