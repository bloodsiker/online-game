<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Новый эффект предмета teleport_location: переносит игрока на локацию value
 * (механизм телепортации Городской стражи). Предмет многоразовый.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats',
        'rename_name', 'change_race', 'change_avatar', 'grant_diamonds',
        'cure_poison', 'cure_bleed', 'cure_burn', 'cure_paralysis', 'produce_item', 'grant_reputation'];

    private const NEW_TYPES = [...self::OLD_TYPES, 'teleport_location'];

    public function up(): void
    {
        $this->setEnum(self::NEW_TYPES);
    }

    public function down(): void
    {
        DB::table('share_item_effects')->where('effect_type', 'teleport_location')->delete();
        $this->setEnum(self::OLD_TYPES);
    }

    /** @param  list<string>  $types */
    private function setEnum(array $types): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        $list = "'".implode("','", $types)."'";
        DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list}) NULL");
    }
};
