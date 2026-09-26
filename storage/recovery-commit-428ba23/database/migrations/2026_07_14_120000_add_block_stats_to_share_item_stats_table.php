<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_TYPES = ['attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'agility', 'intuition',
        'wisdom', 'intelligence', 'dodge', 'critical', 'magic_attack', 'hp_max'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'block_chance', 'block_flat', 'block_percent'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::NEW_TYPES)."'";
        DB::statement("ALTER TABLE share_item_stats MODIFY stat_type ENUM({$list})");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_item_stats MODIFY stat_type ENUM({$list})");
    }
};
