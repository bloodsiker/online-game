<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Dungeon;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Dungeon\Application\Services\DungeonRewardService;
use App\Modules\Dungeon\Application\Services\DungeonStageService;
use App\Modules\Dungeon\Domain\Enums\DungeonRunStatus;
use App\Modules\Dungeon\Domain\Enums\DungeonStageCompletionType;
use App\Modules\Dungeon\Domain\Enums\DungeonStageSpawnType;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonRunParticipant;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class DungeonStageServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createSchema();
    }

    public function test_it_spawns_floor_and_advances_after_last_monster_dies(): void
    {
        $dungeon = Dungeon::query()->create(['name' => 'Башня', 'type' => DungeonType::TOWER]);
        $user = User::query()->create(['name' => 'Игрок', 'location_id' => 1]);
        $monster = Monster::query()->create(['name' => 'Страж', 'hp' => 100, 'aggression' => 0]);
        $firstLocation = Location::query()->forceCreate(['name' => 'Этаж 1', 'dungeon_id' => $dungeon->id]);
        $secondLocation = Location::query()->forceCreate(['name' => 'Этаж 2', 'dungeon_id' => $dungeon->id]);
        $first = $this->stage($dungeon, 1, $firstLocation, $monster, 3);
        $second = $this->stage($dungeon, 2, $secondLocation, $monster, 4);
        $first->update(['next_stage_id' => $second->id]);

        $run = DungeonRun::query()->create([
            'dungeon_id' => $dungeon->id,
            'leader_user_id' => $user->id,
            'status' => DungeonRunStatus::ACTIVE,
        ]);
        $session = DungeonSession::query()->create([
            'dungeon_id' => $dungeon->id,
            'dungeon_run_id' => $run->id,
            'user_id' => $user->id,
        ]);
        DungeonRunParticipant::query()->create([
            'dungeon_run_id' => $run->id,
            'user_id' => $user->id,
            'dungeon_session_id' => $session->id,
        ]);

        $service = new DungeonStageService(new DungeonRewardService(
            Mockery::mock(BackpackService::class),
            Mockery::mock(ExperienceService::class),
        ));
        $service->start($run, $session, collect([$user]));

        $this->assertSame(3, MonsterOnLocation::query()->where('active', true)->count());
        $this->assertSame($firstLocation->id, $user->fresh()->location_id);
        $this->assertSame(180, (int) $run->fresh()->stage_started_at->diffInSeconds($run->fresh()->stage_expires_at));

        $lastMonster = MonsterOnLocation::query()->firstOrFail();
        MonsterOnLocation::query()->update(['active' => false]);
        $lastMonster->active = false;
        $message = $service->handleMonsterKilled($lastMonster);

        $this->assertSame('Этаж 1 очищен. Вы поднялись на этаж 2.', $message);
        $this->assertSame($second->id, $run->fresh()->current_stage_id);
        $this->assertSame(4, MonsterOnLocation::query()->where('active', true)->count());
        $this->assertSame($secondLocation->id, $user->fresh()->location_id);
    }

    private function stage(Dungeon $dungeon, int $number, Location $location, Monster $monster, int $count): DungeonStage
    {
        $stage = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id,
            'number' => $number,
            'name' => "Этаж {$number}",
            'time_limit_seconds' => 180,
            'completion_type' => DungeonStageCompletionType::KILL_ALL,
            'spawn_type' => DungeonStageSpawnType::DISTRIBUTED,
            'total_monsters' => $count,
        ]);
        $stage->locations()->attach($location->id, ['is_entry' => true, 'position' => 0]);
        $stage->monsters()->create(['monster_id' => $monster->id, 'quantity' => $count, 'weight' => 1]);

        return $stage;
    }

    private function createSchema(): void
    {
        Schema::create('dungeons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('prev_location_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->integer('warehouse_count')->default(50);
            $table->integer('bag_count')->default(25);
            $table->integer('slot_count')->default(3);
            $table->timestamps();
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('dungeon_id')->nullable();
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('hp');
            $table->integer('aggression')->default(0);
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        Schema::create('dungeon_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->integer('number');
            $table->string('name');
            $table->integer('time_limit_seconds')->nullable();
            $table->string('completion_type');
            $table->string('spawn_type');
            $table->integer('total_monsters');
            $table->unsignedBigInteger('next_stage_id')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_stage_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('location_id');
            $table->boolean('is_entry');
            $table->integer('position');
            $table->timestamps();
        });
        Schema::create('dungeon_stage_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->integer('quantity');
            $table->integer('weight');
            $table->timestamps();
        });
        Schema::create('dungeon_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('leader_user_id');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('status');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('stage_started_at')->nullable();
            $table->timestamp('stage_expires_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('dungeon_run_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('primary_session_id')->nullable();
            $table->integer('current_wave')->default(1);
            $table->timestamp('entered_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_run_participants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_run_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->string('status')->default('active');
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->boolean('active');
            $table->integer('aggression')->nullable();
            $table->integer('is_drop_money')->default(0);
            $table->timestamps();
        });
    }
}
