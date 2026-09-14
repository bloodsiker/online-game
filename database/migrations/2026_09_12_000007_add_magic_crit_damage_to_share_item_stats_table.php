<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ShareItemStatType::MAGIC_CRIT_DAMAGE — сила магического крита с экипировки
 * (см. FightHitInterface::getMagicCritDamage()), вводится вместе со вторым
 * сетом мага «Всполох». Та же природа бага, что чинили для magic_critical
 * (2026_08_30_000003) и раньше: PHP-энам без миграции на MySQL ENUM-колонку
 * роняет запись в проде.
 *
 * OLD_TYPES = NEW_TYPES из 2026_08_30_000003_add_magic_critical_...
 */
return new class extends Migration
{
    private const OLD_TYPES = ['attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'agility', 'intuition',
        'wisdom', 'intelligence', 'dodge', 'critical', 'magic_attack', 'hp_max', 'block_chance', 'block_flat',
        'block_percent', 'endurance', 'crit_damage', 'magic_resistance', 'magic_critical'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'magic_crit_damage'];

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

        DB::table('share_item_stats')->where('stat_type', 'magic_crit_damage')->delete();

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_item_stats MODIFY stat_type ENUM({$list})");
    }
};
