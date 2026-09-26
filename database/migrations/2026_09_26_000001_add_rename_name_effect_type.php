<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ItemEffectType::RENAME_NAME — единственный эффект, требующий доп. ввода от
 * игрока (новое имя) до применения, поэтому не идёт через ItemEffectStrategy
 * (её apply() не имеет доступа ни к User, ни к Request) — обрабатывается
 * отдельным блоком в ItemController::performUseItem(), как таргет дебаффа.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats'];

    private const NEW_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats', 'rename_name'];

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }

        $shareItemId = DB::table('share_items')->where('name', 'Сертификат «Новое имя»')->value('id');

        if ($shareItemId !== null) {
            DB::table('share_items')->where('id', $shareItemId)->update(['is_use' => 1]);

            $exists = DB::table('share_item_effects')
                ->where('share_item_id', $shareItemId)
                ->where('effect_type', 'rename_name')
                ->exists();

            if (! $exists) {
                DB::table('share_item_effects')->insert([
                    'share_item_id' => $shareItemId,
                    'effect_type' => 'rename_name',
                    // value/value_type — техническая заглушка, новое имя приходит из запроса
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
        DB::table('share_item_effects')->where('effect_type', 'rename_name')->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }
};
