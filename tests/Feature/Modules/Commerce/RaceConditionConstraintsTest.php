<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Commerce;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RaceConditionConstraintsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $database = (string) getenv('CONSTRAINT_TEST_DATABASE');
        if ($database === '') {
            config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
            DB::purge('sqlite');
            $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        } else {
            // These checks precede all schema mutations. Never run on a game database.
            $this->assertMatchesRegularExpression('/^codex_constraint_test_[a-z0-9_]+$/D', $database);
            $this->assertSame('mysql', DB::connection()->getDriverName());
            $this->assertSame($database, DB::connection()->getDatabaseName());
        }
        foreach (['quest_clan_objectives', 'quest_clan_progress', 'backpacks', 'item_on_locations', 'shop_carts', 'quest_player_objectives', 'clan_members', 'battle_details', 'users', 'clans', 'quests', 'quest_stages', 'quest_objectives'] as $table) {
            Schema::dropIfExists($table);
        }
        foreach (['users', 'clans', 'quests', 'quest_stages', 'quest_objectives'] as $table) {
            Schema::create($table, fn (Blueprint $table) => $table->id());
            DB::table($table)->insert([['id' => 1], ['id' => 2]]);
        }
        foreach ([
            'backpacks' => ['user_id', 'item_id'],
            'item_on_locations' => ['item_id', 'location_id'],
            'shop_carts' => ['user_id', 'shop_item_id'],
            'quest_player_objectives' => ['quest_player_id', 'quest_objective_id'],
            'clan_members' => ['user_id', 'clan_id'],
            'battle_details' => ['battle_id', 'user_id', 'location_monster_id'],
        ] as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                $table->id();
                foreach ($columns as $column) {
                    $table->unsignedBigInteger($column)->nullable();
                }
                if (in_array('user_id', $columns, true)) {
                    $table->foreign('user_id')->references('id')->on('users');
                }
                $table->integer('count')->default(1);
            });
        }
        (require database_path('migrations/2026_03_21_000001_create_clan_quest_progress_table.php'))->up();
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_03_000003_add_race_condition_unique_constraints.php');
    }

    public static function duplicateRows(): iterable
    {
        yield 'backpack' => ['backpacks', ['user_id' => 1, 'item_id' => 1]];
        yield 'ground' => ['item_on_locations', ['item_id' => 1, 'location_id' => 1]];
        yield 'cart' => ['shop_carts', ['user_id' => 1, 'shop_item_id' => 1]];
        yield 'personal objective' => ['quest_player_objectives', ['quest_player_id' => 1, 'quest_objective_id' => 1]];
        yield 'clan objective' => ['quest_clan_objectives', ['quest_clan_progress_id' => 1, 'quest_objective_id' => 1]];
        yield 'membership across clans' => ['clan_members', ['user_id' => 1, 'clan_id' => 1]];
        yield 'battle player' => ['battle_details', ['battle_id' => 1, 'user_id' => 1]];
        yield 'battle spawn' => ['battle_details', ['battle_id' => 1, 'location_monster_id' => 1]];
        yield 'clan quest different acceptors' => ['quest_clan_progress', ['clan_id' => 1, 'quest_id' => 1, 'user_id' => 1]];
    }

    #[DataProvider('duplicateRows')]
    public function test_database_rejects_duplicate_ownership_and_progress(string $table, mixed $row): void
    {
        $this->migration()->up();
        if ($table === 'quest_clan_objectives') {
            DB::table('quest_clan_progress')->insert(['id' => 1, 'clan_id' => 1, 'quest_id' => 1, 'user_id' => 1]);
        }
        DB::table($table)->insert($row);
        if ($table === 'clan_members') {
            $row['clan_id'] = 2;
        } elseif ($table === 'quest_clan_progress') {
            $row['user_id'] = 2;
        }
        $this->expectException(QueryException::class);
        DB::table($table)->insert($row);
    }

    public function test_preflight_preserves_duplicate_rows_and_does_not_partially_add_indexes(): void
    {
        DB::table('battle_details')->insert([
            ['battle_id' => 1, 'user_id' => 1], ['battle_id' => 1, 'user_id' => 1],
        ]);
        try {
            $this->migration()->up();
            $this->fail('Migration must refuse duplicates.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('battle_details', $exception->getMessage());
            $this->assertStringContainsString('duplicate_count', $exception->getMessage());
        }
        $this->assertDatabaseCount('battle_details', 2);
        $this->assertFalse(Schema::hasIndex('backpacks', 'backpacks_user_item_unique'));
        $this->assertFalse(Schema::hasIndex('quest_clan_progress', 'qcp_clan_quest_unique'));
    }

    public function test_nullable_participants_and_different_item_instances_remain_valid(): void
    {
        $this->migration()->up();
        DB::table('battle_details')->insert([
            ['battle_id' => 1, 'user_id' => 1, 'location_monster_id' => null],
            ['battle_id' => 1, 'user_id' => 2, 'location_monster_id' => null],
            ['battle_id' => 1, 'user_id' => null, 'location_monster_id' => 1],
            ['battle_id' => 1, 'user_id' => null, 'location_monster_id' => 2],
            ['battle_id' => 2, 'user_id' => 1, 'location_monster_id' => null],
        ]);
        DB::table('backpacks')->insert([
            ['user_id' => 1, 'item_id' => 1, 'count' => 5],
            ['user_id' => 1, 'item_id' => 2, 'count' => 1],
        ]);
        $this->assertDatabaseCount('battle_details', 5);
        $this->assertSame(6, (int) DB::table('backpacks')->sum('count'));
    }

    public function test_migration_can_resume_after_partial_ddl_and_round_trip_without_changing_rows(): void
    {
        Schema::table('backpacks', fn (Blueprint $table) => $table->unique(['user_id', 'item_id'], 'backpacks_user_item_unique'));
        DB::table('backpacks')->insert(['user_id' => 1, 'item_id' => 1, 'count' => 7]);
        $before = DB::table('backpacks')->get()->toJson();
        $this->migration()->up();
        $this->migration()->up();
        $this->assertSame($before, DB::table('backpacks')->get()->toJson());
        $this->migration()->down();
        $this->assertSame($before, DB::table('backpacks')->get()->toJson());
        $this->assertFalse(Schema::hasIndex('backpacks', 'backpacks_user_item_unique'));
        $this->assertTrue(Schema::hasIndex('quest_clan_progress', 'quest_clan_progress_user_id_quest_id_status_unique'));
    }

    public function test_acceptor_can_have_completed_the_same_quest_in_another_clan_and_rollback_is_safe(): void
    {
        $this->migration()->up();
        DB::table('quest_clan_progress')->insert([
            ['clan_id' => 1, 'quest_id' => 1, 'user_id' => 1, 'status' => 'completed'],
            ['clan_id' => 2, 'quest_id' => 1, 'user_id' => 1, 'status' => 'completed'],
        ]);
        try {
            $this->migration()->down();
            $this->fail('Rollback must refuse to restore the incompatible old restriction.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('quest_clan_progress', $exception->getMessage());
        }
        $this->assertDatabaseCount('quest_clan_progress', 2);
        $this->assertTrue(Schema::hasIndex('backpacks', 'backpacks_user_item_unique'));
        $this->assertTrue(Schema::hasIndex('quest_clan_progress', 'qcp_clan_quest_unique'));
    }
}
