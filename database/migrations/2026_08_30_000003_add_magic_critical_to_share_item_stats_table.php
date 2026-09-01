<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ShareItemStatType::MAGIC_CRITICAL — шанс магического крита, вводится вместе
 * с магическим оружием (жезл). Та же природа бага, что чинили для
 * magic_resistance (2026_08_23_100000) и endurance/crit_damage/block_*
 * раньше: PHP-энам без миграции на MySQL ENUM-колонку роняет запись в проде.
 *
 * OLD_TYPES = NEW_TYPES из 2026_08_23_100000_add_magic_resistance_...
 */
return new class extends Migration
{
    private const OLD_TYPES = ['attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'agility', 'intuition',
        'wisdom', 'intelligence', 'dodge', 'critical', 'magic_attack', 'hp_max', 'block_chance', 'block_flat',
        'block_percent', 'endurance', 'crit_damage', 'magic_resistance'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'magic_critical'];

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

        DB::table('share_item_stats')->where('stat_type', 'magic_critical')->delete();

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_item_stats MODIFY stat_type ENUM({$list})");
    }
};
