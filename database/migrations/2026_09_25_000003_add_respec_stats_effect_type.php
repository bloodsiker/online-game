<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ItemEffectType::RESPEC_STATS — сброс только вложенных через AllocateStats
 * очков характеристик, без расового прироста (см. RespecStatsStrategy).
 * Зеркалит паттерн 2026_09_07_000002_add_restore_lost_exp_effect_type.php.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp'];

    private const NEW_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }

    public function down(): void
    {
        DB::table('share_item_effects')->where('effect_type', 'respec_stats')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }
};
