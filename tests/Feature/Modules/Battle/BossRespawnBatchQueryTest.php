<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Battle\BossRespawnService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Repositories\MonsterOnLocationRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class BossRespawnBatchQueryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->boolean('is_boss')->default(false);
            $table->timestamp('respawn_at')->nullable();
            $table->timestamps();
        });
        Schema::create('location_has_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('monster_id');
            $table->unsignedInteger('aggression')->nullable();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });

        DB::table('locations')->insert(['id' => 10]);
        for ($id = 1; $id <= 3; $id++) {
            DB::table('monsters')->insert([
                'id' => $id,
                'name' => 'Boss '.$id,
                'is_boss' => true,
                'respawn_at' => now()->subMinute(),
            ]);
            DB::table('location_has_monsters')->insert([
                'location_id' => 10,
                'monster_id' => $id,
            ]);
        }
    }

    public function test_alive_bosses_are_checked_with_one_query_for_the_whole_location(): void
    {
        $repository = Mockery::mock(MonsterOnLocationRepository::class);
        $repository->shouldReceive('createMonsterOnLocation')
            ->times(3)
            ->andReturn(new MonsterOnLocation);

        $location = Location::findOrFail(10);

        DB::flushQueryLog();
        DB::enableQueryLog();

        (new BossRespawnService($repository))->respawnIfDue($location, null);

        $aliveBossSelects = collect(DB::getQueryLog())
            ->pluck('query')
            ->filter(static fn (string $query): bool => str_starts_with(strtolower($query), 'select')
                && str_contains(strtolower($query), 'monster_on_locations'));

        $this->assertCount(1, $aliveBossSelects);
        $this->assertSame(0, DB::table('monsters')->whereNotNull('respawn_at')->count());
    }
}
