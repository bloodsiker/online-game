<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Стрельба из лука: колчаны и простые стрелы (магазин снаряжения),
 * стрелы с бонусом урона — только крафтом Ремесленника (рецепты в лавке рецептов).
 * Идемпотентен: предметы ищутся по name_legacy, картинки (image) не перезаписываются.
 */
final class ArcheryItemsSeeder extends Seeder
{
    private const EQUIPMENT_SHOP_ID = 2;

    private const RECIPE_SHOP_ID = 28;

    private const CRAFTSMAN_SKILL_ID = 20;

    private const BOW_SKILL_ID = 17;

    private const WEAPON_CATEGORY_ID = 1;

    private const DEFAULT_IMAGE = ShareItem::DEFAULT_IMAGE;

    /** Колчаны: уровень, вместимость, цена. Простой — стартовый, остальные под луки 20+ уровня. */
    private const QUIVERS = [
        ['ru' => 'Простой колчан', 'uk' => 'Простий сагайдак', 'lvl' => 1, 'capacity' => 50, 'price' => 300],
        ['ru' => 'Кожаный колчан', 'uk' => 'Шкіряний сагайдак', 'lvl' => 20, 'capacity' => 75, 'price' => 3000],
        ['ru' => 'Укреплённый колчан', 'uk' => 'Укріплений сагайдак', 'lvl' => 35, 'capacity' => 100, 'price' => 25000],
        ['ru' => 'Охотничий колчан', 'uk' => 'Мисливський сагайдак', 'lvl' => 55, 'capacity' => 150, 'price' => 150000],
        ['ru' => 'Колчан следопыта', 'uk' => 'Сагайдак слідопита', 'lvl' => 70, 'capacity' => 200, 'price' => 600000],
    ];

    /**
     * Ремесленные стрелы: тир навыка Ремесленника (1/50/100/150/200), доска-ингредиент,
     * плоский бонус к урону выстрела, требуемый уровень (как у колчанов: 1/20/35/55/70 —
     * проверяется при закладке в колчан). Рецепт: 3 доски → 20 стрел.
     */
    private const CRAFTED_ARROWS = [
        ['ru' => 'Берёзовая стрела', 'lvl' => 1, 'uk' => 'Березова стріла', 'skill' => 1, 'exp' => 8, 'recipe_price' => 300, 'board' => 'Берёзовая доска', 'min' => 2, 'max' => 4, 'price' => 2],
        ['ru' => 'Тисовая стрела', 'lvl' => 20, 'uk' => 'Тисова стріла', 'skill' => 50, 'exp' => 20, 'recipe_price' => 800, 'board' => 'Тисовая доска', 'min' => 5, 'max' => 8, 'price' => 5],
        ['ru' => 'Чёрная стрела', 'lvl' => 35, 'uk' => 'Чорна стріла', 'skill' => 100, 'exp' => 40, 'recipe_price' => 2000, 'board' => 'Чёрная доска', 'min' => 9, 'max' => 14, 'price' => 10],
        ['ru' => 'Стрела огненного клёна', 'lvl' => 55, 'uk' => 'Стріла вогняного клена', 'skill' => 150, 'exp' => 60, 'recipe_price' => 5000, 'board' => 'Доска огненного клёна', 'min' => 14, 'max' => 21, 'price' => 20],
        ['ru' => 'Стрела древа вечности', 'lvl' => 70, 'uk' => 'Стріла древа вічності', 'skill' => 200, 'exp' => 100, 'recipe_price' => 12000, 'board' => 'Доска древа вечности', 'min' => 20, 'max' => 30, 'price' => 40],
    ];

    private const BOARDS_PER_CRAFT = 3;

    private const ARROWS_PER_CRAFT = 20;

    public function run(): void
    {
        DB::transaction(function (): void {
            $ammoCategory = $this->ensureCategory(self::EQUIPMENT_SHOP_ID, 'Колчаны и стрелы', 'Сагайдаки та стріли');
            $recipeCategory = $this->ensureCategory(self::RECIPE_SHOP_ID, 'Рецепты стрел', 'Рецепти стріл');
            $sort = 100;

            foreach (self::QUIVERS as $quiver) {
                $id = $this->upsertItem(
                    ru: $quiver['ru'],
                    uk: $quiver['uk'],
                    type: 'quiver',
                    attributes: ['slot' => 'quiver', 'quiver_capacity' => $quiver['capacity'], 'is_stackable' => 0, 'price' => $quiver['price']],
                    description: ['ru' => sprintf('Колчан на %d стрел. Пополняется из инвентаря: пункт «Пополнить колчан» у стрел.', $quiver['capacity']),
                        'uk' => sprintf('Сагайдак на %d стріл. Поповнюється з інвентарю: пункт «Поповнити сагайдак» у стріл.', $quiver['capacity'])],
                );
                $this->setLevelRequirement($id, $quiver['lvl']);
                $this->shopItem(self::EQUIPMENT_SHOP_ID, $ammoCategory, $id, $quiver['price'], $sort--);
            }

            // Стартовый лук — существующий «Лук следопыта» (3 качества). Ему был ошибочно
            // назначен навык «Владение мечом»: чиним навык, ставим требования 1 уровня,
            // обычный продаём в магазине. Временный дубль «Простой лук» удаляем.
            $this->removeItem('Простой лук');
            foreach (['Лук следопыта', 'Усиленный лук следопыта', 'Закалённый лук следопыта'] as $bowName) {
                $bow = (int) DB::table('share_items')->where('name_legacy', $bowName)->value('id');
                if ($bow === 0) {
                    continue;
                }
                DB::table('share_items')->where('id', $bow)->update(['skill_id' => self::BOW_SKILL_ID, 'is_two_hand' => 1, 'updated_at' => now()]);
                $this->setLevelRequirement($bow, 1);
                DB::table('share_item_requirements')->insert([
                    'share_item_id' => $bow, 'type' => 'stat', 'stat_key' => 'agility', 'min_value' => 2,
                    'created_at' => now(), 'updated_at' => now(),
                ]);
                if ($bowName === 'Лук следопыта') {
                    $this->shopItem(self::EQUIPMENT_SHOP_ID, self::WEAPON_CATEGORY_ID, $bow, 100, 0);
                }
            }
            $this->sortShopCategoryByLevel(self::EQUIPMENT_SHOP_ID, self::WEAPON_CATEGORY_ID);

            $basicArrow = $this->upsertItem(
                ru: 'Простая стрела',
                uk: 'Проста стріла',
                type: 'arrow',
                attributes: ['slot' => null, 'is_stackable' => 1, 'price' => 1],
                description: ['ru' => 'Обычная стрела без бонуса к урону. Один выстрел — одна стрела.', 'uk' => 'Звичайна стріла без бонусу до шкоди. Один постріл — одна стріла.'],
            );
            $this->setStats($basicArrow, []);
            $this->shopItem(self::EQUIPMENT_SHOP_ID, $ammoCategory, $basicArrow, 1, $sort--);

            foreach (self::CRAFTED_ARROWS as $i => $arrow) {
                $arrowId = $this->upsertItem(
                    ru: $arrow['ru'],
                    uk: $arrow['uk'],
                    type: 'arrow',
                    attributes: ['slot' => null, 'is_stackable' => 1, 'price' => $arrow['price']],
                    description: ['ru' => 'Стрела ремесленной работы: добавляет урон к выстрелу из лука.', 'uk' => 'Стріла ремісничої роботи: додає шкоду до пострілу з лука.'],
                );
                $this->setStats($arrowId, ['attack_min' => $arrow['min'], 'attack_max' => $arrow['max']]);
                $this->setLevelRequirement($arrowId, $arrow['lvl']);

                $recipeItemId = $this->upsertItem(
                    ru: sprintf('Рецепт «%s»', $arrow['ru']),
                    uk: sprintf('Рецепт «%s»', $arrow['uk']),
                    type: 'recipe',
                    attributes: ['slot' => null, 'is_stackable' => 1, 'price' => $arrow['recipe_price'], 'skill_id' => self::CRAFTSMAN_SKILL_ID, 'skill_lvl' => $arrow['skill'], 'skill_exp' => $arrow['exp']],
                    description: ['ru' => sprintf('Рецепт Ремесленника: %d стрел из %d досок.', self::ARROWS_PER_CRAFT, self::BOARDS_PER_CRAFT),
                        'uk' => sprintf('Рецепт Ремісника: %d стріл із %d дошок.', self::ARROWS_PER_CRAFT, self::BOARDS_PER_CRAFT)],
                );
                $this->upsertRecipe($recipeItemId, $arrowId, $arrow['board'], $i);
                $this->shopItem(self::RECIPE_SHOP_ID, $recipeCategory, $recipeItemId, $arrow['recipe_price'], 100 - $i);
            }
        });
    }

    /** Удаляет предмет, если на него нет экземпляров у игроков. */
    private function removeItem(string $nameLegacy): void
    {
        $id = DB::table('share_items')->where('name_legacy', $nameLegacy)->value('id');
        if ($id === null || DB::table('items')->where('share_item_id', $id)->exists()) {
            return;
        }

        DB::table('shop_items')->where('share_item_id', $id)->delete();
        DB::table('share_item_stats')->where('share_item_id', $id)->delete();
        DB::table('share_item_requirements')->where('share_item_id', $id)->delete();
        DB::table('share_items')->where('id', $id)->delete();
    }

    private function ensureCategory(int $structureId, string $ru, string $uk): int
    {
        $id = DB::table('share_structure_categories')->where('name_legacy', $ru)->value('id')
            ?? DB::table('share_structure_categories')->insertGetId([
                'name_legacy' => $ru,
                'name' => json_encode(['ru' => $ru, 'uk' => $uk], JSON_UNESCAPED_UNICODE),
                'is_active' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

        DB::table('shop_categories')->updateOrInsert(
            ['structure_id' => $structureId, 'share_structure_category_id' => $id],
            ['created_at' => now(), 'updated_at' => now()],
        );

        return (int) $id;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array{ru: string, uk: string}  $description
     */
    private function upsertItem(string $ru, string $uk, string $type, array $attributes, array $description): int
    {
        $row = $attributes + [
            'type' => $type,
            'name' => json_encode(['ru' => $ru, 'uk' => $uk], JSON_UNESCAPED_UNICODE),
            'description' => json_encode($description, JSON_UNESCAPED_UNICODE),
            'description_legacy' => $description['ru'],
            'rarity' => 'common',
            'is_active' => 1,
            'is_sell' => 1,
            'is_give' => 1,
            'updated_at' => now(),
        ];

        $id = DB::table('share_items')->where('name_legacy', $ru)->value('id');
        if ($id === null) {
            return (int) DB::table('share_items')->insertGetId($row + [
                'name_legacy' => $ru,
                'image' => self::DEFAULT_IMAGE,
                'created_at' => now(),
            ]);
        }

        DB::table('share_items')->where('id', $id)->update($row);

        return (int) $id;
    }

    private function setLevelRequirement(int $shareItemId, int $level): void
    {
        DB::table('share_item_requirements')->where('share_item_id', $shareItemId)->delete();
        DB::table('share_item_requirements')->insert([
            'share_item_id' => $shareItemId, 'type' => 'level', 'min_value' => $level,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** @param array<string, int> $stats */
    private function setStats(int $shareItemId, array $stats): void
    {
        DB::table('share_item_stats')->where('share_item_id', $shareItemId)->delete();
        foreach ($stats as $type => $value) {
            DB::table('share_item_stats')->insert([
                'share_item_id' => $shareItemId, 'stat_type' => $type, 'value' => $value, 'value_type' => 'flat',
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    private function upsertRecipe(int $recipeItemId, int $arrowId, string $boardName, int $sort): void
    {
        $boardId = DB::table('share_items')->where('name_legacy', $boardName)->value('id');
        if ($boardId === null) {
            throw new \RuntimeException(sprintf('Не найдена доска «%s» для рецепта стрел.', $boardName));
        }

        DB::table('share_recipes')->updateOrInsert(
            ['share_item_id' => $recipeItemId],
            [
                'kraft_item_id' => $arrowId,
                'result_count' => self::ARROWS_PER_CRAFT,
                'is_random_result' => 0,
                'percent' => 100,
                'unlock_type' => 'learnable',
                'sort_order' => 200 + $sort,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
        $recipeId = (int) DB::table('share_recipes')->where('share_item_id', $recipeItemId)->value('id');

        DB::table('share_recipe_has_items')->where('share_recipe_id', $recipeId)->delete();
        DB::table('share_recipe_has_items')->insert([
            'share_recipe_id' => $recipeId, 'share_item_id' => $boardId, 'count' => self::BOARDS_PER_CRAFT,
            'sort_order' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** Раздел магазина по возрастанию требуемого уровня (магазин выводит по убыванию sort_order). */
    private function sortShopCategoryByLevel(int $structureId, int $categoryId): void
    {
        $rows = DB::table('shop_items as s')
            ->leftJoin('share_item_requirements as r', fn ($join) => $join->on('r.share_item_id', '=', 's.share_item_id')->where('r.type', 'level'))
            ->where('s.structure_id', $structureId)
            ->where('s.share_structure_category_id', $categoryId)
            ->groupBy('s.id', 's.price')
            ->selectRaw('s.id, s.price, COALESCE(MAX(r.min_value), 0) as lvl')
            ->get()
            ->sortBy([['lvl', 'asc'], ['price', 'asc'], ['id', 'asc']])
            ->values();

        foreach ($rows as $index => $row) {
            DB::table('shop_items')->where('id', $row->id)->update(['sort_order' => $rows->count() - $index]);
        }
    }

    private function shopItem(int $structureId, int $categoryId, int $shareItemId, int $price, int $sort): void
    {
        DB::table('shop_items')->updateOrInsert(
            ['structure_id' => $structureId, 'share_item_id' => $shareItemId],
            [
                'share_structure_category_id' => $categoryId,
                'price' => $price,
                'diamond' => 0,
                'sort_order' => $sort,
                'purchase_limit_quantity' => 0,
                'purchase_limit_period_seconds' => 0,
                'min_reputation_points' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        );
    }
}
