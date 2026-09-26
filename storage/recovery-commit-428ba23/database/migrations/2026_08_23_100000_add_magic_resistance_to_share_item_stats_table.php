<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ShareItemStatType::MAGIC_RESISTANCE появился в PHP-энаме вместе с магической
 * боёвкой (Task 2, магсопр = единственная защита от заклинаний, см.
 * MagicHitCalculator), но ни одна миграция не добавила 'magic_resistance' в
 * MySQL ENUM-колонку share_item_stats.stat_type — из-за этого стат физически
 * нельзя выдать предметом в проде (SQLSTATE[01000]: Data truncated for column
 * 'stat_type'). Та же природа бага, что чинили для endurance/crit_damage
 * (2026_07_16_120000) и для block_* (2026_07_14_120000).
 *
 * OLD_TYPES — точный текущий список колонки, сверен с дампом прода
 * (game.sql, share_item_stats).
 */
return new class extends Migration
{
    private const OLD_TYPES = ['attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'agility', 'intuition',
        'wisdom', 'intelligence', 'dodge', 'critical', 'magic_attack', 'hp_max', 'block_chance', 'block_flat',
        'block_percent', 'endurance', 'crit_damage'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'magic_resistance'];

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

        // Строки с откатываемым значением надо убрать до сужения ENUM, иначе
        // MySQL молча превратит их в '' (или упадёт в strict mode).
        DB::table('share_item_stats')->where('stat_type', 'magic_resistance')->delete();

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_item_stats MODIFY stat_type ENUM({$list})");
    }
};
