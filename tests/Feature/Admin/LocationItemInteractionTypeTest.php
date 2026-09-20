<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\LocationController;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

final class LocationItemInteractionTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        $this->createTables();
        DB::table('share_items')->insert([
            ['id' => 10, 'name' => 'Сундук', 'type' => 'chest'],
            ['id' => 11, 'name' => 'Ресурс', 'type' => 'resource'],
        ]);
    }

    public function test_admin_can_choose_how_chest_is_used_on_location(): void
    {
        $this->addItem(10, 'pickup');
        $this->addItem(10, 'open_here');

        $this->assertDatabaseHas('item_on_locations', [
            'item_id' => 1,
            'interaction_type' => 'pickup',
        ]);
        $this->assertDatabaseHas('item_on_locations', [
            'item_id' => 2,
            'interaction_type' => 'open_here',
        ]);
    }

    public function test_regular_location_item_always_uses_pickup_interaction(): void
    {
        $this->addItem(11, 'open_here');

        $this->assertDatabaseHas('item_on_locations', [
            'item_id' => 1,
            'interaction_type' => 'pickup',
        ]);
    }

    private function addItem(int $shareItemId, string $interactionType): void
    {
        $request = Request::create('/admin/location/6/item', 'POST', [
            'share_item_id' => $shareItemId,
            'count' => 1,
            'interaction_type' => $interactionType,
        ]);
        $location = new Location;
        $location->id = 6;

        app(LocationController::class)->addItem($request, $location);
    }

    private function createTables(): void
    {
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->integer('upgrade_lvl')->default(0);
            $table->integer('upgrade_pity')->default(0);
            $table->integer('upgrade_fail_streak')->default(0);
            $table->integer('socket_count')->default(0);
            $table->integer('rune_slot_count')->default(0);
            $table->integer('additional_attack')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_open')->default(false);
            $table->timestamps();
        });
        Schema::create('item_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('location_id');
            $table->integer('count')->default(1);
            $table->string('interaction_type')->default('pickup');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }
}
