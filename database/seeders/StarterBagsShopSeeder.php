<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Категории «Сумки» и «Пояса» + стартовые Сумка путника и Пояс путника
 * в Торговой лавке Тарвина (shop 14) — за монеты, без репутации и Eternum.
 * Надеть: сумку с 12 уровня, пояс с 18.
 * Идемпотентен.
 */
final class StarterBagsShopSeeder extends Seeder
{
    private const SHOP_ID = 14;

    /** [ключ, название, [share_item_name_legacy, price, sort_order, минимальный уровень]] */
    private const CATEGORIES = [
        'bags' => [['ru' => 'Сумки', 'uk' => 'Сумки'], ['Сумка путника', 5000, 0, 12]],
        'belts' => [['ru' => 'Пояса', 'uk' => 'Пояси'], ['Пояс путника', 3000, 0, 18]],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $now = now();

            foreach (self::CATEGORIES as [$name, [$itemName, $price, $sort, $minLevel]]) {
                $categoryId = $this->upsertCategory($name);

                DB::table('shop_categories')->updateOrInsert(
                    ['structure_id' => self::SHOP_ID, 'share_structure_category_id' => $categoryId],
                    ['created_at' => $now, 'updated_at' => $now],
                );

                $shareItemId = DB::table('share_items')->where('name_legacy', $itemName)->value('id')
                    ?? throw new RuntimeException("Нет предмета «{$itemName}» — сначала EternityReputationSeeder");

                DB::table('share_item_requirements')->updateOrInsert(
                    ['share_item_id' => $shareItemId, 'type' => 'level'],
                    ['stat_key' => null, 'skill_id' => null, 'min_value' => $minLevel, 'created_at' => $now, 'updated_at' => $now],
                );

                DB::table('shop_items')->updateOrInsert(
                    ['structure_id' => self::SHOP_ID, 'share_item_id' => $shareItemId],
                    [
                        'share_structure_category_id' => $categoryId,
                        'price' => $price,
                        'diamond' => 0,
                        'sort_order' => $sort,
                        'purchase_limit_quantity' => 0,
                        'purchase_limit_period_seconds' => 0,
                        'min_reputation_points' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }
        });
    }

    /** @param  array{ru: string, uk: string}  $name */
    private function upsertCategory(array $name): int
    {
        $id = DB::table('share_structure_categories')->where('name_legacy', $name['ru'])->value('id');
        $values = [
            'name_legacy' => $name['ru'],
            'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
            'is_active' => 1,
            'updated_at' => now(),
        ];

        if ($id !== null) {
            DB::table('share_structure_categories')->where('id', $id)->update($values);

            return (int) $id;
        }

        return (int) DB::table('share_structure_categories')->insertGetId($values + ['created_at' => now()]);
    }
}
