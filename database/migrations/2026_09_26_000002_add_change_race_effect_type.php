<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ItemEffectType::CHANGE_RACE — как и RENAME_NAME, требует доп. ввода от игрока
 * (id новой расы) до применения, обрабатывается отдельным блоком в
 * ItemController::performUseItem(), минуя ItemEffectStrategy.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats', 'rename_name'];

    private const NEW_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats', 'rename_name', 'change_race'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }

        $shareItemId = DB::table('share_items')->where('name', 'Сертификат «Смена расы»')->value('id');

        if ($shareItemId !== null) {
            DB::table('share_items')->where('id', $shareItemId)->update(['is_use' => 1]);

            $exists = DB::table('share_item_effects')
                ->where('share_item_id', $shareItemId)
                ->where('effect_type', 'change_race')
                ->exists();

            if (! $exists) {
                DB::table('share_item_effects')->insert([
                    'share_item_id' => $shareItemId,
                    // value/value_type — техническая заглушка, новая раса приходит из запроса
                    'effect_type' => 'change_race',
                    'value' => 0,
                    'value_type' => 'flat',
                    'duration_seconds' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        DB::table('share_item_effects')->where('effect_type', 'change_race')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }
};
