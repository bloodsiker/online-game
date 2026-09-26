<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Location;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Domain\Enums\BattleDetailStatus;
use App\Modules\Battle\Domain\Enums\BattleStatus;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Location\Domain\Services\PlayerMovementService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Player\Domain\Services\PlayerEquipmentLoader;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class PlayerMovementBattleGuardTest extends TestCase
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
            $table->unsignedBigInteger('dungeon_run_id')->nullable();
            $table->unsignedInteger('rounds')->default(0);
            $table->unsignedTinyInteger('status')->default(BattleStatus::ACTIVE->value);
            $table->json('boss_metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->unsignedTinyInteger('status')->default(BattleDetailStatus::LIFE->value);
            $table->timestamps();
        });
    }

    public function test_player_cannot_move_while_alive_in_active_battle_on_current_location(): void
    {
        $battle = Battle::query()->create([
            'location_id' => 2,
            'status' => BattleStatus::ACTIVE,
        ]);
        DB::table('battle_details')->insert([
            'battle_id' => $battle->id,
            'user_id' => 7,
            'status' => BattleDetailStatus::LIFE->value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $location = (new Location)->forceFill(['id' => 2, 'east' => 3]);
        $user = (new User)->forceFill(['id' => 7, 'location_id' => 2]);
        $user->setRelation('currentLocation', $location);

        $service = new PlayerMovementService(
            Mockery::mock(BackpackService::class),
            new PlayerEquipmentLoader,
            new BattleRepository,
        );

        $result = $service->move($user, 'east');

        $this->assertFalse($result->success);
        $this->assertSame('Нельзя покинуть локацию во время боя.', $result->message);
        $this->assertSame(2, $user->location_id);
    }
}
