<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

final class UpdateLibraryThreeGodsSeeder extends Seeder
{
    private const ARTICLES = [
        32 => 'bog-myortvykh-kairell',
        33 => 'boginya-zhizni-fiora',
        34 => 'bog-voiny-karindor',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $articles = LibraryArticle::whereIn('slug', self::ARTICLES)->lockForUpdate()->get()->keyBy('slug');
            if ($articles->count() !== count(self::ARTICLES)) {
                throw new RuntimeException('Не найдены все три статьи о богах.');
            }
            $backup = 'backups/library/three-gods-before-update-'.now()->format('Ymd-His-u').'.json';
            $before = $articles->map(fn (LibraryArticle $article): array => [
                'id' => $article->id, 'content' => $article->getTranslations('content'), 'excerpt' => $article->getTranslations('excerpt'),
            ])->all();
            if (! Storage::disk('local')->put($backup, json_encode($before, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))) {
                throw new RuntimeException('Не удалось сохранить резервную копию статей.');
            }
            foreach (self::ARTICLES as $id => $slug) {
                $reputation = Reputation::with(['npc', 'tiers.featQuest.objectives', 'gambleOptions.shareItem', 'elixirItem'])->findOrFail($id);
                if ($reputation->gambleOptions->isEmpty()) {
                    throw new RuntimeException('Для репутации #'.$id.' не заполнены подношения.');
                }
                $article = $articles->get($slug);
                foreach (['ru', 'uk'] as $locale) {
                    $article->setTranslation('content', $locale, $this->content($reputation, $locale));
                    $article->setTranslation('excerpt', $locale, $locale === 'uk'
                        ? 'Подношення, шанси успіху, медалі та бойова милість. Актуальні ресурси й вимоги для подвигу божества.'
                        : 'Подношения, шансы успеха, медали и боевая милость. Актуальные ресурсы и требования для подвига божества.');
                }
                foreach (['content', 'excerpt'] as $field) {
                    if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                        $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
                    }
                }
                $article->save();
                $this->command?->info('Обновлена статья: '.$slug.' (#'.$article->id.').');
            }
            $this->command?->info('Резервная копия: '.$backup);
        });
    }

    private function content(Reputation $reputation, string $locale): string
    {
        $uk = $locale === 'uk';
        $intro = '<p>'.$reputation->getTranslation('description', $locale).'</p>'
            .'<p>[[npc:'.$reputation->npc_id.';display:inline]]</p><p>[[reputation:'.$reputation->id.';display:inline]]</p>'
            .'<p>'.($uk ? 'Шукайте божество на локації №' : 'Ищите божество на локации №').$reputation->npc?->location_id.'.</p>';
        $rules = $uk
            ? '<p>У діалозі божества відкрийте обмін ресурсів, виберіть іконку підношення та один із варіантів у модальному вікні. Чим більше ресурсів ви віддаєте за спробу, тим вищий шанс прихильності.</p><p><b>Ресурси витрачаються і при успіху, і при невдачі.</b> Очки репутації нараховуються лише при успіху. Повне підношення має 100% шанс; менші варіанти дозволяють ризикнути за меншу ціну.</p><p>Подношення доступне <b>раз на добу для цієї репутації</b>. Відлік починається після спроби, навіть невдалої. Ліміт спільний для всіх ресурсів одного божества.</p>'
            : '<p>В диалоге божества откройте обмен ресурсов, выберите иконку подношения и один из вариантов в модальном окне. Чем больше ресурсов вы отдаёте за попытку, тем выше шанс благосклонности.</p><p><b>Ресурсы расходуются и при успехе, и при неудаче.</b> Очки репутации начисляются только при успехе. Полное подношение имеет 100% шанс; меньшие варианты позволяют рискнуть за меньшую цену.</p><p>Подношение доступно <b>раз в сутки для этой репутации</b>. Отсчёт начинается после попытки, даже неудачной. Лимит общий для всех ресурсов одного божества.</p>';
        $offerings = $this->tableStart($uk ? ['Ресурс', 'Мале: 20%', 'Середнє: 50%', 'Повне: 100%'] : ['Ресурс', 'Малое: 20%', 'Среднее: 50%', 'Полное: 100%']);
        foreach ($reputation->gambleOptions->groupBy('share_item_id') as $itemId => $options) {
            $offerings .= '<tr><td align="center"><div class="structures">[[item:'.$itemId.']]</div></td>';
            foreach ([20, 50, 100] as $chance) {
                $option = $options->firstWhere('success_chance', $chance);
                $offerings .= '<td align="center">'.($option !== null
                    ? $option->resource_cost.' шт.<br>+'.(int) $option->reward_points.' '.($uk ? 'реп. при успіху' : 'реп. при успехе')
                    : '—').'</td>';
            }
            $offerings .= '</tr>';
        }
        $offerings .= '</tbody></table><p>'.($uk ? 'Наведіть курсор на іконки для перегляду предметів. Вартість кожної спроби вказана в штуках вибраного ресурсу.' : 'Наведите курсор на иконки для просмотра предметов. Цена каждой попытки указана в штуках выбранного ресурса.').'</p>';
        $effect = match ((int) $reputation->id) {
            32 => $uk ? 'отруює ціль: шкода обчислюється від максимального здоров’я монстра й розподіляється на час дії ефекту' : 'отравляет цель: урон рассчитывается от максимального здоровья монстра и распределяется на время действия эффекта',
            33 => $uk ? 'відновлює здоров’я персонажа: зцілення обчислюється від максимального HP й розподіляється на час дії ефекту' : 'восстанавливает здоровье персонажа: лечение рассчитывается от максимального HP и распределяется на время действия эффекта',
            34 => $uk ? 'тимчасово збільшує атаку персонажа на відсоток поточного щабля' : 'временно увеличивает атаку персонажа на процент текущей ступени',
        };
        $favor = '<p><b>'.($uk ? 'Бойова милість' : 'Боевая милость').'</b> '.$effect.'.</p><p>'
            .($uk ? 'Шанс спрацювання під час атаки: ' : 'Шанс срабатывания при атаке: ').'<b>'.$reputation->favor_proc_chance.'%</b>. '
            .($uk ? 'Відсоток сили в таблиці нижче — це величина ефекту, а не шанс його спрацювання.' : 'Процент силы в таблице ниже — величина эффекта, а не шанс его срабатывания.').'</p>';
        if ($reputation->elixir_share_item_id) {
            $favor .= '<div class="structures">[[item:'.$reputation->elixir_share_item_id.']]</div>';
        }
        $favor .= $uk
            ? '<p>На початковому щаблі без медалі милість працює лише з активним еліксиром божества. Еліксир видається після підношення, навіть невдалого, доки милість не стала постійною.</p><p><b>З першою звичайною медаллю милість стає постійною</b> — пити еліксир більше не потрібно. Подальші подношення розвивають репутацію, але зайві еліксири вже не видаються. Постійна милість означає постійну можливість спрацювання; окремий бойовий ефект усе одно має обмежену тривалість.</p>'
            : '<p>На начальной ступени без медали милость работает только с активным эликсиром божества. Эликсир выдаётся после подношения, даже неудачного, пока милость не стала постоянной.</p><p><b>С первой обычной медалью милость становится постоянной</b> — пить эликсир больше не нужно. Дальнейшие подношения развивают репутацию, но лишние эликсиры уже не выдаются. Постоянная милость означает постоянную возможность срабатывания; отдельный боевой эффект по-прежнему имеет ограниченную длительность.</p>';
        $tiers = $this->tableStart($uk ? ['Репутація від', 'Медаль', 'Сила милості', 'Тривалість ефекту'] : ['Репутация от', 'Медаль', 'Сила милости', 'Длительность эффекта']);
        foreach ($reputation->tiers as $tier) {
            $label = $tier->getTranslation('medal_name', $locale) ?: ($uk ? 'Без медалі' : 'Без медали');
            $tiers .= '<tr><td align="center">'.$tier->min_points.'</td><td>'.e($label).'</td><td align="center">'.$tier->favor_percent.'%</td><td align="center">'.$tier->favor_duration_seconds.' с</td></tr>';
        }
        $tiers .= '</tbody></table>';
        $top = $reputation->tiers->sortByDesc('min_points')->first();
        $feat = $top?->featQuest;
        $featText = '';
        if ($feat !== null) {
            $featText = '<p><b>'.e($feat->getTranslation('title', $locale)).'</b> — '.($uk ? 'подвиг, доступний від ' : 'подвиг, доступный от ').$top->min_points.' '.($uk ? 'очок репутації.' : 'очков репутации.').'</p>'
                .'<p>'.e($feat->getTranslation('description', $locale)).'</p>'
                .'<p>'.($uk ? 'Зберіть і принесіть усі потрібні ресурси разом:' : 'Соберите и принесите все нужные ресурсы одновременно:').'</p>'
                .$this->tableStart($uk ? ['Предмет', 'Кількість'] : ['Предмет', 'Количество']);
            foreach ($feat->objectives->where('type', 'collect')->where('target_type', 'item') as $objective) {
                $featText .= '<tr><td align="center"><div class="structures">[[item:'.$objective->target_id.']]</div></td><td align="center">'.$objective->required_amount.'</td></tr>';
            }
            $featText .= '</tbody></table><p>'.($uk ? 'Завершений подвиг дає особливу медаль і закріплює посилену милість: ' : 'Завершённый подвиг даёт особую медаль и закрепляет усиленную милость: ').'<b>'.$top->feat_favor_percent.'%</b>. '
                .($uk ? 'Самого досягнення порогу репутації недостатньо — потрібно виконати подвиг.' : 'Самого достижения порога репутации недостаточно — нужно выполнить подвиг.').'</p>';
        }

        return $this->frame($reputation->getTranslation('name', $locale), $intro)
            .$this->frame($uk ? 'Як робити подношення' : 'Как делать подношения', $rules)
            .$this->frame($uk ? 'Ресурси та шанси успіху' : 'Ресурсы и шансы успеха', $offerings)
            .$this->frame($uk ? 'Милість божества' : 'Милость божества', $favor)
            .$this->frame($uk ? 'Щаблі й медалі' : 'Ступени и медали', $tiers)
            .($feat !== null ? $this->frame('Подвиг божества', $featText) : '');
    }

    /** @param list<string> $headers */
    private function tableStart(array $headers): string
    {
        return '<table class="coll brd2-all p10h p4v" width="100%" cellspacing="0" cellpadding="0"><thead><tr class="bg_l">'
            .implode('', array_map(fn (string $header): string => '<th align="center">'.e($header).'</th>', $headers))
            .'</tr></thead><tbody>';
    }

    private function frame(string $title, string $body): string
    {
        return '<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody>'
            .'<tr height="22"><td width="20" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" align="center">'
            .'<table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22">'
            .'<td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td>'
            .'<td class="tbl-usi_label-center" align="center">'.e($title).'</td>'
            .'<td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table>'
            .'</td><td width="20" class="tbl-shp-sml rt"><b></b></td></tr>'
            .'<tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" style="padding:8px 12px">'.$body.'</td><td class="tbl-shp-sides rs">&nbsp;</td></tr>'
            .'<tr height="18"><td width="20" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb">&nbsp;</td><td width="20" class="tbl-shp-sml rb"><b></b></td></tr>'
            .'</tbody></table>';
    }
}
