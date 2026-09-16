<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class LibraryCraftsmanLockpicksSeeder extends Seeder
{
    private const ARTICLE_SLUG = 'remeslennik';

    private const RESULT_NAMES = [
        'Грубая отмычка',
        'Усиленная отмычка',
        'Стальная отмычка',
        'Точная отмычка',
        'Мастерская отмычка',
        'Рунная отмычка',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $article = DB::table('library_articles')->where('slug', self::ARTICLE_SLUG)->first();
            if ($article === null) {
                throw new RuntimeException('Статья «Ремесленник» в библиотеке не найдена.');
            }

            $recipes = $this->recipes();
            $content = (string) $article->content;
            $content = $this->removeExistingSection($content);
            $content = $this->refreshIntroduction($content);
            $section = $this->lockpickSection($recipes);

            $anchor = '<h2>Шанс создания</h2>';
            $content = str_contains($content, $anchor)
                ? str_replace($anchor, $section."\n\n".$anchor, $content)
                : rtrim($content)."\n\n".$section;

            DB::table('library_articles')->where('id', $article->id)->update([
                'excerpt' => 'Переработка древесины, изготовление зазубренных оправ и отмычек: рецепты, материалы и шанс крафта.',
                'content' => $content,
                'updated_at' => now(),
            ]);
        });

        $this->command?->info('Статья «Ремесленник» дополнена разделом об отмычках.');
    }

    private function recipes()
    {
        $recipes = DB::table('share_recipes as recipes')
            ->join('share_items as recipe_items', 'recipe_items.id', '=', 'recipes.share_item_id')
            ->join('share_items as results', 'results.id', '=', 'recipes.kraft_item_id')
            ->whereIn('results.name', self::RESULT_NAMES)
            ->select([
                'recipes.id',
                'results.id as result_id',
                'results.name as result_name',
                'recipe_items.skill_lvl',
                'recipe_items.skill_exp',
            ])
            ->orderBy('recipe_items.skill_lvl')
            ->get();

        if ($recipes->count() !== count(self::RESULT_NAMES)) {
            throw new RuntimeException('Перед обновлением статьи нужно создать все рецепты отмычек.');
        }

        return $recipes->map(function ($recipe) {
            $recipe->ingredients = DB::table('share_recipe_has_items as recipe_items')
                ->join('share_items as ingredients', 'ingredients.id', '=', 'recipe_items.share_item_id')
                ->where('recipe_items.share_recipe_id', $recipe->id)
                ->orderBy('recipe_items.id')
                ->get(['ingredients.name', 'recipe_items.count']);

            return $recipe;
        });
    }

    private function refreshIntroduction(string $content): string
    {
        $content = str_replace(
            '<p><b>Ремесленник</b> — мирная профессия для переработки древесины и изготовления заготовок. Ремесленник превращает брёвна в доски, а затем создаёт зазубренные оправы разных редкостей.</p>',
            '<p><b>Ремесленник</b> — мирная профессия для переработки древесины и изготовления полезных заготовок. Он превращает брёвна в доски, создаёт зазубренные оправы и изготавливает отмычки для Взломщиков.</p>',
            $content,
        );

        return str_replace(
            '<li>Подготовьте брёвна или материалы для создания оправы.</li>',
            '<li>Подготовьте брёвна или материалы для создания оправы либо отмычки.</li>',
            $content,
        );
    }

    private function removeExistingSection(string $content): string
    {
        return preg_replace(
            '/\s*<!-- lockpick-recipes:start -->.*?<!-- lockpick-recipes:end -->\s*/s',
            "\n\n",
            $content,
        ) ?? $content;
    }

    private function lockpickSection($recipes): string
    {
        $shortcodes = $recipes
            ->map(fn ($recipe): string => sprintf('[[item:%d]]', $recipe->result_id))
            ->implode(' ');
        $rows = $recipes->map(function ($recipe): string {
            $ingredients = $recipe->ingredients
                ->map(fn ($ingredient): string => sprintf('%s ×%d', $this->escape($ingredient->name), $ingredient->count))
                ->implode(', ');

            return sprintf(
                '<tr><td>%d</td><td>%s</td><td>%s</td><td>+%d</td></tr>',
                $recipe->skill_lvl,
                $this->escape($recipe->result_name),
                $ingredients,
                $recipe->skill_exp,
            );
        })->implode("\n");

        return <<<HTML
<!-- lockpick-recipes:start -->
<h2>Изготовление отмычек</h2>
<p>Ремесленник может создавать отмычки всех шести тиров. Каждый крафт даёт <b>1 отмычку</b>, а следующие тиры открываются на уровнях <b>1, 50, 100, 150, 200 и 250</b>.</p>

<div class="library-info-block library-info-block--important"><strong>Рецепты изучаются отдельно:</strong> даже при достаточном уровне профессии нужно сначала изучить соответствующую книгу рецепта.</div>

<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">Отмычки Ремесленника</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$shortcodes}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>

<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" align="center" valign="top" style="padding:4px 0 14px"><div class="structures" style="margin:5px"><table><thead><tr><th>Навык</th><th>Отмычка</th><th>Материалы</th><th>Опыт</th></tr></thead><tbody>
{$rows}
</tbody></table></div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>

<div class="library-info-block library-info-block--tip"><strong>Запас для взлома:</strong> для трёх сундуков одного тира разумно подготовить около <b>5 подходящих отмычек</b>. Отмычка тратится при неудаче, если не сработал шанс её сохранения.</div>
<!-- lockpick-recipes:end -->
HTML;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
