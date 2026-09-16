<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LockpickRecipesSeeder extends Seeder
{
    private const PROFESSION = 'Ремесленник';

    private const RECIPE_IMAGE = '/img/resource/scroll_weapon.gif';

    /**
     * @var list<array{
     *     result: string,
     *     level: int,
     *     experience: int,
     *     rarity: string,
     *     price: int,
     *     ingredients: array<string, int>
     * }>
     */
    private const RECIPES = [
        [
            'result' => 'Грубая отмычка',
            'level' => 1,
            'experience' => 10,
            'rarity' => 'common',
            'price' => 100,
            'ingredients' => [
                'Железный слиток' => 1,
                'Древесная смола' => 1,
            ],
        ],
        [
            'result' => 'Усиленная отмычка',
            'level' => 50,
            'experience' => 25,
            'rarity' => 'uncommon',
            'price' => 500,
            'ingredients' => [
                'Железный слиток' => 2,
                'Древесная смола' => 2,
                'Уголь' => 1,
            ],
        ],
        [
            'result' => 'Стальная отмычка',
            'level' => 100,
            'experience' => 50,
            'rarity' => 'rare',
            'price' => 2_000,
            'ingredients' => [
                'Железный слиток' => 3,
                'Древесная смола' => 2,
                'Пыль рубин' => 1,
            ],
        ],
        [
            'result' => 'Точная отмычка',
            'level' => 150,
            'experience' => 100,
            'rarity' => 'epic',
            'price' => 6_000,
            'ingredients' => [
                'Железный слиток' => 4,
                'Древесная смола' => 3,
                'Пыль алмаз' => 1,
            ],
        ],
        [
            'result' => 'Мастерская отмычка',
            'level' => 200,
            'experience' => 150,
            'rarity' => 'legendary',
            'price' => 15_000,
            'ingredients' => [
                'Железный слиток' => 5,
                'Янтарная смола' => 1,
                'Пыль звёздный камень' => 1,
            ],
        ],
        [
            'result' => 'Рунная отмычка',
            'level' => 250,
            'experience' => 250,
            'rarity' => 'heroic',
            'price' => 30_000,
            'ingredients' => [
                'Железный слиток' => 6,
                'Осколок метеорита' => 1,
                'Красный Камень Печати' => 1,
            ],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $skillId = DB::table('skills')
                ->where('name', self::PROFESSION)
                ->where('type', 'peaceful')
                ->value('id');

            if ($skillId === null) {
                throw new RuntimeException('Мирная профессия «'.self::PROFESSION.'» не найдена.');
            }

            foreach (self::RECIPES as $definition) {
                $resultId = $this->itemId($definition['result']);
                $recipeItemId = $this->upsertRecipeItem($definition, (int) $skillId);
                $recipeId = $this->upsertRecipe($recipeItemId, $resultId);
                $this->syncIngredients($recipeId, $definition['ingredients']);
            }
        });

        $this->command?->info('Созданы рецепты отмычек для профессии «'.self::PROFESSION.'».');
    }

    /** @param array{result: string, level: int, experience: int, rarity: string, price: int, ingredients: array<string, int>} $definition */
    private function upsertRecipeItem(array $definition, int $skillId): int
    {
        $name = sprintf('Рецепт «%s»', $definition['result']);
        $item = DB::table('share_items')->where('name', $name)->first();
        $now = now();
        $attributes = [
            'type' => 'recipe',
            'description' => sprintf(
                'Позволяет изучить изготовление предмета «%s». Требуется Ремесленник %d уровня.',
                $definition['result'],
                $definition['level'],
            ),
            'rarity' => $definition['rarity'],
            'skill_id' => $skillId,
            'skill_lvl' => $definition['level'],
            'skill_exp' => $definition['experience'],
            'is_active' => true,
            'is_sell' => true,
            'is_give' => true,
            'is_droppable' => true,
            'is_stackable' => true,
            'is_weight' => true,
            'price' => $definition['price'],
            'updated_at' => $now,
        ];

        if ($item === null) {
            return (int) DB::table('share_items')->insertGetId([
                'name' => $name,
                'image' => self::RECIPE_IMAGE,
                ...$attributes,
                'created_at' => $now,
            ]);
        }

        if ($item->image === null || $item->image === '') {
            $attributes['image'] = self::RECIPE_IMAGE;
        }

        DB::table('share_items')->where('id', $item->id)->update($attributes);

        return (int) $item->id;
    }

    private function upsertRecipe(int $recipeItemId, int $resultId): int
    {
        $recipeId = DB::table('share_recipes')->where('share_item_id', $recipeItemId)->value('id');
        $attributes = [
            'kraft_item_id' => $resultId,
            'percent' => 100,
            'unlock_type' => 'learnable',
            'updated_at' => now(),
        ];

        if ($recipeId === null) {
            return (int) DB::table('share_recipes')->insertGetId([
                'share_item_id' => $recipeItemId,
                ...$attributes,
                'created_at' => now(),
            ]);
        }

        DB::table('share_recipes')->where('id', $recipeId)->update($attributes);

        return (int) $recipeId;
    }

    /** @param array<string, int> $ingredients */
    private function syncIngredients(int $recipeId, array $ingredients): void
    {
        DB::table('share_recipe_has_items')->where('share_recipe_id', $recipeId)->delete();

        $rows = [];
        foreach ($ingredients as $name => $count) {
            $rows[] = [
                'share_recipe_id' => $recipeId,
                'share_item_id' => $this->itemId($name),
                'count' => $count,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('share_recipe_has_items')->insert($rows);
    }

    private function itemId(string $name): int
    {
        $id = DB::table('share_items')->where('name', $name)->value('id');
        if ($id === null) {
            throw new RuntimeException("Не найден предмет «{$name}» для рецепта отмычки.");
        }

        return (int) $id;
    }
}
