<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ItemEffectType::RESTORE_LOST_EXP — возврат опыта, потерянного при смерти,
 * пока активно «Возрождение» (см. RestoreLostExpStrategy). Зеркалит паттерн
 * 2026_08_22_150000_add_book_type_to_share_items.php.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot'];

    private const NEW_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }

    public function down(): void
    {
        DB::table('share_item_effects')->where('effect_type', 'restore_lost_exp')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }
};
