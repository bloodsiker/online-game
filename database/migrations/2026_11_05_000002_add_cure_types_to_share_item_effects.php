<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ItemEffectType::CURE_* — противоядия от яда, кровотечения, горения и паралича.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp', 'attack_min', 'attack_max',
        'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats', 'rename_name', 'change_race',
        'change_avatar', 'grant_diamonds'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'cure_poison', 'cure_bleed', 'cure_burn', 'cure_paralysis'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", self::NEW_TYPES)."'";
        DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list}) NULL");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::table('share_item_effects')->whereIn('effect_type', ['cure_poison', 'cure_bleed', 'cure_burn', 'cure_paralysis'])->delete();

        $list = "'".implode("','", self::OLD_TYPES)."'";
        DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list}) NULL");
    }
};
