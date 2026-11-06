<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_CLAN_INDEX = 'quest_clan_progress_user_id_quest_id_status_unique';

    public function up(): void
    {
        // MySQL DDL commits implicitly: validate every table before adding any index.
        foreach ($this->definitions() as $definition) {
            $this->assertUniqueRows($definition['table'], collect($definition['columns']));
        }

        foreach ($this->definitions() as $definition) {
            if (! Schema::hasIndex($definition['table'], $definition['name'])) {
                Schema::table($definition['table'], fn (Blueprint $table) => $table->unique($definition['columns'], $definition['name']));
            }
        }

        if (Schema::hasIndex('quest_clan_progress', self::OLD_CLAN_INDEX)) {
            $this->preserveForeignKeyIndexes('quest_clan_progress', self::OLD_CLAN_INDEX);
            Schema::table('quest_clan_progress', fn (Blueprint $table) => $table->dropUnique(self::OLD_CLAN_INDEX));
        }
    }

    public function down(): void
    {
        // Restoring the old restriction may be impossible after legitimate cross-clan progress.
        // Refuse the rollback before touching indexes; never merge or delete progress.
        $this->assertUniqueRows('quest_clan_progress', collect(['user_id', 'quest_id', 'status']));
        if (! Schema::hasIndex('quest_clan_progress', self::OLD_CLAN_INDEX)) {
            Schema::table('quest_clan_progress', fn (Blueprint $table) => $table->unique(['user_id', 'quest_id', 'status'], self::OLD_CLAN_INDEX));
        }
        foreach ($this->definitions()->reverse() as $definition) {
            if (Schema::hasIndex($definition['table'], $definition['name'])) {
                $this->preserveForeignKeyIndexes($definition['table'], $definition['name']);
                Schema::table($definition['table'], fn (Blueprint $table) => $table->dropUnique($definition['name']));
            }
        }
    }

    private function preserveForeignKeyIndexes(string $tableName, string $dropping): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        // InnoDB may silently remove a redundant FK index when adding a covering unique.
        // Restore support before dropping that unique, including during rollback.
        foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
            $covered = collect(Schema::getIndexes($tableName))->contains(
                fn ($index): bool => $index['name'] !== $dropping
                    && array_slice($index['columns'], 0, count($foreignKey['columns'])) === $foreignKey['columns'],
            );
            if (! $covered) {
                Schema::table($tableName, fn (Blueprint $table) => $table->index($foreignKey['columns'], $foreignKey['name']));
            }
        }
    }

    private function definitions(): Collection
    {
        return collect([
            ['table' => 'backpacks', 'columns' => ['user_id', 'item_id'], 'name' => 'backpacks_user_item_unique'],
            ['table' => 'item_on_locations', 'columns' => ['item_id', 'location_id'], 'name' => 'ground_item_location_unique'],
            ['table' => 'shop_carts', 'columns' => ['user_id', 'shop_item_id'], 'name' => 'shop_cart_user_offer_unique'],
            ['table' => 'quest_player_objectives', 'columns' => ['quest_player_id', 'quest_objective_id'], 'name' => 'qpo_progress_objective_unique'],
            ['table' => 'quest_clan_objectives', 'columns' => ['quest_clan_progress_id', 'quest_objective_id'], 'name' => 'qco_progress_objective_unique'],
            ['table' => 'clan_members', 'columns' => ['user_id'], 'name' => 'clan_member_user_unique'],
            ['table' => 'battle_details', 'columns' => ['battle_id', 'user_id'], 'name' => 'battle_detail_user_unique'],
            ['table' => 'battle_details', 'columns' => ['battle_id', 'location_monster_id'], 'name' => 'battle_detail_spawn_unique'],
            ['table' => 'quest_clan_progress', 'columns' => ['clan_id', 'quest_id'], 'name' => 'qcp_clan_quest_unique'],
        ]);
    }

    private function assertUniqueRows(string $table, Collection $columns): void
    {
        $query = DB::table($table)->select($columns->all())->selectRaw('COUNT(*) AS duplicate_count');
        // SQL unique indexes permit multiple NULLs, e.g. monster rows have no user_id.
        foreach ($columns as $column) {
            $query->whereNotNull($column);
        }
        $duplicates = $query->groupBy($columns->all())->havingRaw('COUNT(*) > 1')->limit(10)->get();
        if ($duplicates->isNotEmpty()) {
            throw new RuntimeException("Cannot add unique constraint to {$table}: ".$duplicates->toJson().'. No rows were changed. Resolve duplicates explicitly before retrying.');
        }
    }
};
