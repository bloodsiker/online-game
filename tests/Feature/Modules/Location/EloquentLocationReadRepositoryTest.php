<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Location;

use App\Modules\Location\Infrastructure\Persistence\EloquentLocationReadRepository;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EloquentLocationReadRepositoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('item_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedInteger('count')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_counts_visible_items_without_loading_item_models(): void
    {
        DB::table('item_on_locations')->insert([
            ['id' => 1, 'item_id' => 101, 'location_id' => 10, 'dungeon_session_id' => null, 'expires_at' => null],
            ['id' => 2, 'item_id' => 102, 'location_id' => 10, 'dungeon_session_id' => null, 'expires_at' => now()->addHour()],
            ['id' => 3, 'item_id' => 103, 'location_id' => 10, 'dungeon_session_id' => null, 'expires_at' => now()->subHour()],
            ['id' => 4, 'item_id' => 104, 'location_id' => 11, 'dungeon_session_id' => null, 'expires_at' => null],
            ['id' => 5, 'item_id' => 105, 'location_id' => 10, 'dungeon_session_id' => 77, 'expires_at' => null],
        ]);

        $location = (new Location)->forceFill(['id' => 10, 'dungeon_id' => null]);
        $location->exists = true;

        $user = (new User)->forceFill(['id' => 1, 'location_id' => 10]);
        $user->exists = true;
        $user->setRelation('currentLocation', $location);

        DB::flushQueryLog();
        DB::enableQueryLog();

        $count = app(EloquentLocationReadRepository::class)->countItemsOnLocation($user, 10);
        $queries = DB::getQueryLog();

        $this->assertSame(2, $count);
        $this->assertCount(1, $queries);
        $this->assertStringContainsString('count(*) as aggregate', strtolower($queries[0]['query']));
    }
}
