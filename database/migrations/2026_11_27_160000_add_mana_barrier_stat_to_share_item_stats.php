<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ShareItemStatType::MANA_BARRIER — «Магический барьер» щитов мага: % входящего физического урона,
 * который поглощает мана (MonsterAttackService::applyManaBarrier). PHP-энам без миграции на MySQL ENUM-колонку
 * роняет запись в проде (см. 2026_09_12_000007).
 */
return new class extends Migration
{
    private const OLD_TYPES = ['attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'agility', 'intuition',
        'wisdom', 'intelligence', 'dodge', 'critical', 'magic_attack', 'hp_max', 'block_chance', 'block_flat',
        'block_percent', 'endurance', 'crit_damage', 'magic_resistance', 'magic_critical', 'magic_crit_damage'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'mana_barrier'];

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

        DB::table('share_item_stats')->where('stat_type', 'mana_barrier')->delete();

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_item_stats MODIFY stat_type ENUM({$list})");
    }
};
