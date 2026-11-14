<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Набор бижутерии по тирам экипировки (6 ступеней × кольца/серьги/ожерелья),
 * продаётся в магазине снаряжения (structure 2, разделы «Кольца», «Серьги», «Ожерелья»). Только
 * характеристики, без эффектов и дропа с боссов. Сидер идемпотентен — повторный запуск обновляет предметы
 * по name_legacy, не плодя дублей. Иконки временные (DEFAULT_IMAGE) — заменить в админке.
 */
final class JewelryItemsSeeder extends Seeder
{
    private const SHOP_STRUCTURE_ID = 2;

    /** Разделы магазина по типу предмета: [ru, uk]. */
    private const SHOP_CATEGORIES = [
        'ring' => ['Кольца', 'Персні'],
        'earring' => ['Серьги', 'Сережки'],
        'necklace' => ['Ожерелья', 'Намиста'],
    ];

    /**
     * Ступени привязаны к тирам экипировки: 1–20, 21–50, 51+ — по две ступени на тир
     * (в начале тира и ближе к его концу), чтобы бижутерию меняли дважды за тир.
     * Баланс: требование к характеристике ×1,14 от прежнего (≈ 2,3 × уровень в 1-м тире и ≈ 4,8–5,1 × уровень
     * дальше — как у брони после пересчёта на 5,7 очка за уровень, проходит любая раса); бонус бижутерии — примерно половина
     * бонуса «common»-брони того же уровня (украшений 9 слотов).
     * bonus — основные характеристики, dodge — уворот/крит, armor — броня, mres — магсопротивление,
     * sec — второй параметр (характеристики/уворот/магсопр.), sec_armor — второй параметр-броня (×2 к sec:
     * 1 выносливость ≈ 2 брони по живучести), hp — второй параметр макс. HP. Броня ожерелья защиты (armor)
     * со ступени «Бронзовое» тоже удвоена по той же причине.
     */
    private const TIERS = [
        ['lvl' => 1, 'price' => 500, 'sec' => 0, 'sec_armor' => 0, 'hp' => 0, 'bonus' => 1, 'dodge' => 1, 'armor' => 1, 'mres' => 1, 'req' => 2, 'rarity' => 'common',
            'ru' => ['Простое', 'Простая', 'Простое'], 'uk' => ['Простий', 'Проста', 'Просте']],
        ['lvl' => 13, 'price' => 3000, 'sec' => 1, 'sec_armor' => 2, 'hp' => 25, 'bonus' => 2, 'dodge' => 1, 'armor' => 6, 'mres' => 2, 'req' => 31, 'rarity' => 'common',
            'ru' => ['Бронзовое', 'Бронзовая', 'Бронзовое'], 'uk' => ['Бронзовий', 'Бронзова', 'Бронзове']],
        ['lvl' => 22, 'price' => 30000, 'sec' => 2, 'sec_armor' => 4, 'hp' => 50, 'bonus' => 3, 'dodge' => 2, 'armor' => 8, 'mres' => 2, 'req' => 105, 'rarity' => 'common',
            'ru' => ['Серебряное', 'Серебряная', 'Серебряное'], 'uk' => ['Срібний', 'Срібна', 'Срібне']],
        ['lvl' => 37, 'price' => 144000, 'sec' => 3, 'sec_armor' => 6, 'hp' => 75, 'bonus' => 5, 'dodge' => 3, 'armor' => 12, 'mres' => 3, 'req' => 177, 'rarity' => 'common',
            'ru' => ['Золотое', 'Золотая', 'Золотое'], 'uk' => ['Золотий', 'Золота', 'Золоте']],
        ['lvl' => 51, 'price' => 480000, 'sec' => 4, 'sec_armor' => 8, 'hp' => 100, 'bonus' => 7, 'dodge' => 4, 'armor' => 16, 'mres' => 4, 'req' => 262, 'rarity' => 'common',
            'ru' => ['Мифриловое', 'Мифриловая', 'Мифриловое'], 'uk' => ['Мітриловий', 'Мітрилова', 'Мітрилове']],
        ['lvl' => 70, 'price' => 1440000, 'sec' => 6, 'sec_armor' => 12, 'hp' => 150, 'bonus' => 10, 'dodge' => 6, 'armor' => 22, 'mres' => 6, 'req' => 359, 'rarity' => 'common',
            'ru' => ['Адамантовое', 'Адамантовая', 'Адамантовое'], 'uk' => ['Адамантовий', 'Адамантова', 'Адамантове']],
    ];

    /**
     * Виды: [тип, индекс рода в материале, название ru, название uk, статистика, ключ статы (бонус), требуемая характеристика].
     * 'val' — колонка ступени, из которой берётся величина бонуса; 'stat2'/'val2' — второй параметр.
     */
    private const KINDS = [
        ['type' => 'ring', 'g' => 0, 'ru' => 'кольцо ловкости', 'uk' => 'перстень спритності', 'stat' => 'agility', 'req' => 'agility', 'val' => 'bonus', 'stat2' => 'dodge', 'val2' => 'sec'],
        ['type' => 'ring', 'g' => 0, 'ru' => 'кольцо интуиции', 'uk' => 'перстень інтуїції', 'stat' => 'intuition', 'req' => 'intuition', 'val' => 'bonus', 'stat2' => 'critical', 'val2' => 'sec'],
        ['type' => 'ring', 'g' => 0, 'ru' => 'кольцо мудрости', 'uk' => 'перстень мудрості', 'stat' => 'wisdom', 'req' => 'wisdom', 'val' => 'bonus', 'stat2' => 'magic_resistance', 'val2' => 'sec'],
        ['type' => 'ring', 'g' => 0, 'ru' => 'кольцо разума', 'uk' => 'перстень розуму', 'stat' => 'intelligence', 'req' => 'intelligence', 'val' => 'bonus', 'stat2' => 'magic_attack', 'val2' => 'sec'],
        ['type' => 'ring', 'g' => 0, 'ru' => 'кольцо выносливости', 'uk' => 'перстень витривалості', 'stat' => 'endurance', 'req' => 'strength', 'val' => 'bonus', 'stat2' => 'armor', 'val2' => 'sec_armor'],
        ['type' => 'earring', 'g' => 1, 'ru' => 'серьга уворота', 'uk' => 'сережка ухилу', 'stat' => 'dodge', 'req' => 'agility', 'val' => 'dodge', 'stat2' => 'wisdom', 'val2' => 'sec'],
        ['type' => 'earring', 'g' => 1, 'ru' => 'серьга точности', 'uk' => 'сережка точності', 'stat' => 'critical', 'req' => 'intuition', 'val' => 'dodge', 'stat2' => 'intelligence', 'val2' => 'sec'],
        ['type' => 'necklace', 'g' => 2, 'ru' => 'ожерелье защиты', 'uk' => 'намисто захисту', 'stat' => 'armor', 'req' => 'strength', 'val' => 'armor', 'stat2' => 'hp_max', 'val2' => 'hp'],
        ['type' => 'necklace', 'g' => 2, 'ru' => 'ожерелье стойкости', 'uk' => 'намисто стійкості', 'stat' => 'magic_resistance', 'req' => 'wisdom', 'val' => 'mres', 'stat2' => 'intelligence', 'val2' => 'sec'],
    ];

    /** @return list<array<string, mixed>> */
    private function items(): array
    {
        $items = [];

        foreach (self::TIERS as $tier) {
            foreach (self::KINDS as $kind) {
                $g = $kind['g'];
                $items[] = [
                    'type' => $kind['type'],
                    'ru' => $tier['ru'][$g].' '.$kind['ru'],
                    'uk' => $tier['uk'][$g].' '.$kind['uk'],
                    'lvl' => $tier['lvl'],
                    'price' => $tier['price'],
                    'stats' => $this->stats($kind, $tier),
                    'req_stat' => [$kind['req'], $tier['req']],
                    'rarity' => $tier['rarity'],
                ];
            }
        }

        return $items;
    }

    /**
     * Основная характеристика + второй параметр (со ступени «Бронзовое»; у «Простого» его нет).
     *
     * @param  array<string, mixed>  $kind
     * @param  array<string, mixed>  $tier
     * @return array<string, int>
     */
    private function stats(array $kind, array $tier): array
    {
        $stats = [$kind['stat'] => $tier[$kind['val']]];
        if ($tier[$kind['val2']] > 0) {
            $stats[$kind['stat2']] = $tier[$kind['val2']];
        }

        return $stats;
    }

    public function run(): void
    {
        DB::transaction(function (): void {
            $this->removeLegacyItems();
            $categoryIds = $this->ensureShopCategories();

            $items = $this->items();
            $total = count($items);

            foreach ($items as $i => $def) {
                $shareItemId = $this->upsertItem($def);

                if (isset($def['price'])) {
                    DB::table('shop_items')->updateOrInsert(
                        ['structure_id' => self::SHOP_STRUCTURE_ID, 'share_item_id' => $shareItemId],
                        [
                            'share_structure_category_id' => $categoryIds[$def['type']],
                            'price' => $def['price'],
                            'diamond' => 0,
                            // Магазин выводит по убыванию sort_order: меньший уровень — выше, самый высокий — в конце.
                            'sort_order' => $total - $i,
                            'purchase_limit_quantity' => 0,
                            'purchase_limit_period_seconds' => 0,
                            'min_reputation_points' => 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ],
                    );
                }
            }
        });
    }

    private function removeLegacyItems(): void
    {
        $current = array_column($this->items(), 'ru');
        $ids = DB::table('share_items')
            ->whereIn('type', ['ring', 'earring', 'necklace'])
            ->whereNotIn('name_legacy', $current)
            ->pluck('id');
        if ($ids->isEmpty()) {
            return;
        }

        DB::table('monster_has_items')->whereIn('share_item_id', $ids)->delete();
        DB::table('shop_items')->whereIn('share_item_id', $ids)->delete();
        DB::table('share_item_stats')->whereIn('share_item_id', $ids)->delete();
        DB::table('share_item_requirements')->whereIn('share_item_id', $ids)->delete();
        DB::table('share_items')->whereIn('id', $ids)->delete();
    }

    /**
     * Три раздела магазина: кольца, серьги, ожерелья. Прежняя общая разделы «Кольца», «Серьги», «Ожерелья» удаляется.
     *
     * @return array<string, int> тип предмета => id категории
     */
    private function ensureShopCategories(): array
    {
        $legacyId = DB::table('share_structure_categories')->where('name_legacy', 'Украшения')->value('id');
        if ($legacyId !== null) {
            DB::table('shop_categories')->where('share_structure_category_id', $legacyId)->delete();
            DB::table('share_structure_categories')->where('id', $legacyId)->delete();
        }

        $ids = [];
        foreach (self::SHOP_CATEGORIES as $type => [$ru, $uk]) {
            $categoryId = DB::table('share_structure_categories')->where('name_legacy', $ru)->value('id');

            if ($categoryId === null) {
                $categoryId = DB::table('share_structure_categories')->insertGetId([
                    'name_legacy' => $ru,
                    'name' => json_encode(['ru' => $ru, 'uk' => $uk], JSON_UNESCAPED_UNICODE),
                    'is_active' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('shop_categories')->updateOrInsert(
                ['structure_id' => self::SHOP_STRUCTURE_ID, 'share_structure_category_id' => $categoryId],
                ['created_at' => now(), 'updated_at' => now()],
            );

            $ids[$type] = (int) $categoryId;
        }

        return $ids;
    }

    /** @param array<string, mixed> $def */
    private function upsertItem(array $def): int
    {
        $type = $def['type'];
        $description = ['ru' => 'Простое украшение, усиливающее владельца.', 'uk' => 'Проста прикраса, що посилює власника.'];

        $row = [
            'type' => $type,
            'slot' => $type,
            'name' => json_encode(['ru' => $def['ru'], 'uk' => $def['uk']], JSON_UNESCAPED_UNICODE),
            'description' => json_encode($description, JSON_UNESCAPED_UNICODE),
            'description_legacy' => $description['ru'],
            'rarity' => $def['rarity'],
            'price' => $def['price'] ?? 0,
            'is_active' => 1,
            'is_sell' => 1,
            'is_give' => 1,
            'is_stackable' => 0,
            'innate_passive_type' => null,
            'innate_passive_value' => null,
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('name_legacy', $def['ru'])->value('id');
        if ($id === null) {
            $id = DB::table('share_items')->insertGetId($row + ['name_legacy' => $def['ru'], 'created_at' => now()]);
        } else {
            DB::table('share_items')->where('id', $id)->update($row);
        }

        DB::table('share_item_stats')->where('share_item_id', $id)->delete();
        foreach ($def['stats'] as $stat => $value) {
            DB::table('share_item_stats')->insert([
                'share_item_id' => $id, 'stat_type' => $stat, 'value' => $value, 'value_type' => 'flat',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        DB::table('share_item_requirements')->where('share_item_id', $id)->delete();
        DB::table('share_item_requirements')->insert([
            'share_item_id' => $id, 'type' => 'level', 'min_value' => $def['lvl'],
            'created_at' => now(), 'updated_at' => now(),
        ]);
        DB::table('share_item_requirements')->insert([
            'share_item_id' => $id, 'type' => 'stat', 'stat_key' => $def['req_stat'][0], 'min_value' => $def['req_stat'][1],
            'created_at' => now(), 'updated_at' => now(),
        ]);

        return (int) $id;
    }
}
