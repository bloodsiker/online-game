<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Location;

use Database\Seeders\AshFoothillsLocationsSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use LogicException;
use Tests\TestCase;

class AshFoothillsLocationsSeederTest extends TestCase
{
    public function test_topology_is_connected_reciprocal_and_respects_internal_walls(): void
    {
        $moves = (new AshFoothillsLocationsSeeder)->topology();
        $this->assertCount(70, $moves);
        $opposite = ['north' => 'south', 'south' => 'north', 'east' => 'west', 'west' => 'east'];
        $edges = 0;
        foreach ($moves as $id => $directions) {
            foreach ($directions as $direction => $target) {
                if ($target !== null && $target !== 395) {
                    $this->assertSame($id, $moves[$target][$opposite[$direction]]);
                    $edges++;
                }
            }
        }
        $this->assertSame(138, $edges);
        $visited = [];
        $queue = [1630];
        while ($queue !== []) {
            $id = array_pop($queue);
            if ($id === 395 || isset($visited[$id])) {
                continue;
            }
            $visited[$id] = true;
            foreach (array_filter($moves[$id]) as $target) {
                $queue[] = $target;
            }
        }
        $this->assertCount(70, $visited);
        $this->assertNull($moves[1694]['east']);
        $this->assertNull($moves[1691]['east']);
        $this->assertNull($moves[1695]['south']);
        $this->assertNull($moves[1675]['south']);
        $this->assertNull($moves[1670]['east']);
        $this->assertNull($moves[1653]['south']);
        $this->assertNull($moves[1658]['east']);
        $this->assertNull($moves[1661]['east']);
        $this->assertSame(395, $moves[1630]['east']);
        $this->assertSame(1631, $moves[1630]['west']);
    }

    public function test_seeding_preserves_existing_content_and_other_maps_and_is_repeatable(): void
    {
        $this->createIsolatedDatabase();
        $before = DB::table('locations')->where('id', 395)->first();
        $seeder = new AshFoothillsLocationsSeeder;
        $seeder->run();
        $seeder->run();

        $this->assertSame(70, DB::table('locations')->where('map_id', 16)->count());
        $this->assertEquals($before, DB::table('locations')->where('id', 395)->first());
        $this->assertSame('Исходное название', DB::table('locations')->where('id', 1631)->value('name'));
        $this->assertSame('Исходное описание', DB::table('locations')->where('id', 1631)->value('description'));
        $this->assertSame('original.webp', DB::table('locations')->where('id', 1631)->value('image'));
        $this->assertSame(1631, DB::table('maps')->where('id', 17)->value('resp_location_id'));
        $this->assertSame(1632, DB::table('locations')->where('id', 1631)->value('west'));
    }

    public function test_conflicting_location_id_aborts_without_creating_locations(): void
    {
        $this->createIsolatedDatabase();
        DB::table('locations')->insert(['id' => 1699, 'map_id' => 17, 'name' => 'Другой регион']);
        try {
            (new AshFoothillsLocationsSeeder)->run();
            $this->fail('Expected a conflict exception.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('другой карты', $exception->getMessage());
        }
        $this->assertSame(4, DB::table('locations')->count());
        $this->assertNull(DB::table('locations')->where('id', 1631)->value('west'));
    }

    public function test_conflicting_existing_exit_aborts_without_overwriting_it(): void
    {
        $this->createIsolatedDatabase();
        DB::table('locations')->where('id', 1631)->update(['west' => 395]);
        try {
            (new AshFoothillsLocationsSeeder)->run();
            $this->fail('Expected a conflict exception.');
        } catch (LogicException $exception) {
            $this->assertStringContainsString('занят переход west', $exception->getMessage());
        }
        $this->assertSame(3, DB::table('locations')->count());
        $this->assertSame(395, DB::table('locations')->where('id', 1631)->value('west'));
    }

    private function createIsolatedDatabase(): void
    {
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        Schema::create('maps', function (Blueprint $table): void {
            $table->id();
            $table->string('folder');
            $table->unsignedBigInteger('resp_location_id');
        });
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('map_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            foreach (['north', 'south', 'east', 'west', 'up', 'down'] as $direction) {
                $table->unsignedBigInteger($direction)->nullable();
            }
            foreach (['count_monster', 'percent_respawn_monster', 'time_not_attack', 'is_locked'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
        });
        DB::table('maps')->insert([
            ['id' => 16, 'folder' => 'subcity/ash_foothills', 'resp_location_id' => 1630],
            ['id' => 17, 'folder' => 'subcity/ash_depths', 'resp_location_id' => 1631],
        ]);
        DB::table('locations')->insert([
            ['id' => 395, 'map_id' => 4, 'name' => 'Среди могил', 'east' => 394, 'west' => null, 'south' => 1630, 'description' => null, 'image' => null],
            ['id' => 1630, 'map_id' => 16, 'name' => 'Вход', 'east' => 395, 'west' => 1631, 'south' => null, 'description' => null, 'image' => null],
            ['id' => 1631, 'map_id' => 16, 'name' => 'Исходное название', 'east' => 1630, 'west' => null, 'south' => null, 'description' => 'Исходное описание', 'image' => 'original.webp'],
        ]);
    }
}
