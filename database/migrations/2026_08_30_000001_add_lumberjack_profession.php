<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Четвёртая мирная профессия — Лесоруб. Тот же паттерн, что у
     * Травника/Рыбака/Геолога (2026_08_29_000003): Skill type=peaceful,
     * инструмент type=tool с gathering_tool_share_item_id, ресурсы (брёвна
     * разных пород) с привязкой к навыку и инструменту.
     */
    public function up(): void
    {
        $skillId = DB::table('skills')->where('name', 'Лесоруб')->value('id');

        if ($skillId === null) {
            $skillId = DB::table('skills')->insertGetId([
                'name' => 'Лесоруб',
                'type' => 'peaceful',
                'description' => 'Рубит деревья, добывая брёвна разных пород.',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if (! DB::table('skill_level_requirements')->where('skill_id', $skillId)->exists()) {
            $requirements = [];
            $previous = 0;
            for ($level = 1; $level <= 100; $level++) {
                $required = 100 * $level * $level;
                $requirements[] = [
                    'skill_id' => $skillId,
                    'lvl' => $level,
                    'exp_required' => $required,
                    'exp_diff' => $required - $previous,
                ];
                $previous = $required;
            }
            DB::table('skill_level_requirements')->insert($requirements);
        }

        $axeId = DB::table('share_items')->where('name', 'Топор')->value('id');

        if ($axeId === null) {
            $axeId = DB::table('share_items')->insertGetId([
                'type' => 'tool',
                'name' => 'Топор',
                'description' => 'Топор гильдейской ковки — рассекает любую древесину и не знает износа, сколько бы стволов ни было повалено.',
                'slot' => 'hand',
                'is_two_hand' => false,
                'is_stackable' => false,
                'is_active' => true,
                'is_sell' => true,
                'is_give' => true,
                'is_droppable' => true,
                'is_weight' => true,
                'rarity' => 'common',
                'price' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $logs = [
            'Дубовое бревно' => 'Тяжёлое, плотное бревно дуба — идёт на самую крепкую мебель и постройки.',
            'Сосновое бревно' => 'Смолистое сосновое бревно с резким лесным запахом — лёгкое в обработке.',
            'Берёзовое бревно' => 'Светлое берёзовое бревно с гладкой корой — универсальный материал для мелких поделок.',
        ];

        $sort = 0;
        foreach ($logs as $name => $description) {
            if (DB::table('share_items')->where('name', $name)->exists()) {
                $sort++;
                continue;
            }

            DB::table('share_items')->insert([
                'type' => 'resource',
                'name' => $name,
                'description' => $description,
                'image' => '/img/resource/gathering/wood.svg',
                'is_two_hand' => false,
                'is_active' => true,
                'is_sell' => true,
                'is_give' => true,
                'is_droppable' => true,
                'is_stackable' => true,
                'is_weight' => true,
                'price' => 0,
                'rarity' => 'common',
                'skill_id' => $skillId,
                'skill_lvl' => 1,
                'skill_exp' => 3,
                'gathering_time_seconds' => 10,
                'gathering_respawn_seconds' => 30,
                'gathering_tool_share_item_id' => $axeId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sort++;
        }

        // Топор — в Гильдию мастеров (structure id=19), рядом с серпом/удочкой/киркой
        $guildStructureId = DB::table('structures')->where('name', 'Гильдия мастеров')->value('id');
        $toolsCategoryId = DB::table('share_structure_categories')->where('name', 'Инструменты')->value('id');

        if ($guildStructureId !== null && $toolsCategoryId !== null
            && ! DB::table('shop_items')->where('structure_id', $guildStructureId)->where('share_item_id', $axeId)->exists()) {
            $maxSort = (int) DB::table('shop_items')->where('structure_id', $guildStructureId)->max('sort_order');

            DB::table('shop_items')->insert([
                'structure_id' => $guildStructureId,
                'share_item_id' => $axeId,
                'share_structure_category_id' => $toolsCategoryId,
                'price' => 300,
                'diamond' => 0,
                'sort_order' => $maxSort + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // NPC-лесоруб на локации Гильдии (id=51), тем же паттерном, что остальные
        $guildLocationId = DB::table('structures')->where('name', 'Гильдия мастеров')->value('location_id');

        if ($guildLocationId !== null && ! DB::table('npcs')->where('name', 'Лесоруб Гром')->exists()) {
            DB::table('npcs')->insert([
                'uuid' => (string) \Illuminate\Support\Str::uuid(),
                'location_id' => $guildLocationId,
                'hide_location' => 0,
                'name' => 'Лесоруб Гром',
                'description' => 'Немногословный великан с топором за плечом — валит деревья быстрее, чем кто-либо в округе. Учит ремеслу всех, кто решил взять в руки топор.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Данные могли использоваться игроками (прогресс навыка, предметы в
        // сумках) — не удаляем, как и соседняя миграция профессий.
    }
};
