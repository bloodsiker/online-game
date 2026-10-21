<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ItemEffectType::CHANGE_AVATAR — как RENAME_NAME/CHANGE_RACE, требует доп.
 * ввода от игрока (id новой строки race_avatars) до применения, обрабатывается
 * отдельным блоком в ItemController::useItem(), минуя ItemEffectStrategy.
 *
 * В отличие от add_change_race_effect_type.php (где сертификат уже был
 * создан вручную через админку), здесь сам share_items создаётся этой
 * миграцией — по прямому указанию пользователя.
 */
return new class extends Migration
{
    private const OLD_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats', 'rename_name', 'change_race'];

    private const NEW_TYPES = ['heal_hp', 'heal_mp', 'buff_attack', 'buff_armor', 'damage_hp',
        'attack_min', 'attack_max', 'armor', 'bag_slot', 'belt_slot', 'restore_lost_exp', 'respec_stats', 'rename_name', 'change_race', 'change_avatar'];

    private const ITEM_NAME_RU = 'Сертификат «Смена аватара»';

    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::NEW_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }

        $shareItemId = DB::table('share_items')->where('name->ru', self::ITEM_NAME_RU)->value('id');

        if ($shareItemId === null) {
            $shareItemId = DB::table('share_items')->insertGetId([
                'type' => 'scroll',
                'name' => json_encode(['ru' => 'Сертификат «Смена аватара»', 'uk' => 'Сертифікат «Зміна аватара»'], JSON_UNESCAPED_UNICODE),
                'description' => json_encode([
                    'ru' => 'Позволяет один раз сменить аватар персонажа на другой образ той же расы и пола.',
                    'uk' => 'Дозволяє один раз змінити аватар персонажа на інший образ тієї ж раси і статі.',
                ], JSON_UNESCAPED_UNICODE),
                'is_weight' => 0,
                'is_use' => 1,
                'is_sell' => 1,
                'is_auction_sellable' => 0,
                'is_give' => 1,
                'is_clan_warehouse_allowed' => 1,
                'is_slot_usable' => 0,
                'is_droppable' => 1,
                'is_stackable' => 0,
                'count_use' => 0,
                'price' => 0,
                'rarity' => 'common',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $exists = DB::table('share_item_effects')
            ->where('share_item_id', $shareItemId)
            ->where('effect_type', 'change_avatar')
            ->exists();

        if (! $exists) {
            DB::table('share_item_effects')->insert([
                'share_item_id' => $shareItemId,
                // value/value_type — техническая заглушка, новый аватар приходит из запроса
                'effect_type' => 'change_avatar',
                'value' => 0,
                'value_type' => 'flat',
                'duration_seconds' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        DB::table('share_item_effects')->where('effect_type', 'change_avatar')->delete();
        DB::table('share_items')->where('name->ru', self::ITEM_NAME_RU)->delete();

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            $list = "'".implode("','", self::OLD_TYPES)."'";
            DB::statement("ALTER TABLE share_item_effects MODIFY effect_type ENUM({$list})");
        }
    }
};
