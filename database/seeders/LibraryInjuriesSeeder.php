<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LibraryArticle;
use App\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryInjuriesSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->updateOrCreate(
                ['slug' => 'travmy'],
                [
                    'parent_id' => null,
                    'name' => 'Травмы',
                    'description' => 'Получение травм после смерти, заблокированные ячейки экипировки, штрафы и время восстановления.',
                    'sort_order' => 80,
                    'is_active' => true,
                ],
            );

            LibraryArticle::withTrashed()->updateOrCreate(
                ['slug' => 'sistema-travm'],
                [
                    'category_id' => $category->id,
                    'author_id' => null,
                    'title' => 'Система травм',
                    'excerpt' => 'Как персонаж получает травму, какие ячейки экипировки она блокирует и какие характеристики снижает.',
                    'content' => $this->content(),
                    'status' => LibraryArticle::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'sort_order' => 0,
                    'deleted_at' => null,
                ],
            );
        });
    }

    private function content(): string
    {
        return <<<'HTML'
<p><b>Травма</b> — временное последствие смерти персонажа. После поражения существует 30% вероятности получить повреждение случайной части тела. Травма занимает соответствующую ячейку экипировки, снимает надетую вещь и временно снижает связанные характеристики.</p>

<div class="library-info-block library-info-block--important"><strong>Предмет не уничтожается:</strong> снятая из-за травмы вещь возвращается в рюкзак. После выздоровления её нужно надеть заново.</div>

<h2>Что происходит после получения травмы</h2>
HTML
            .$this->frame('Последствия травмы', <<<'HTML'
<ol>
<li>Надетый на повреждённую часть тела предмет автоматически снимается.</li>
<li>На манекене появляется бинт с иконкой травмы и таймером.</li>
<li>До окончания таймера в заблокированную ячейку нельзя надеть другой предмет.</li>
<li>Персонаж получает указанные для травмы штрафы характеристик.</li>
<li>После завершения времени бинт и травма исчезают автоматически.</li>
</ol>
HTML)
            .<<<'HTML'

<p>При травме руки двуручное оружие снимается полностью, потому что для него необходимы обе свободные руки. Сообщение о полученной травме и времени её действия приходит игроку в личный информационный чат.</p>

<h2>Уровни тяжести</h2>
HTML
            .$this->frame('Тяжесть и длительность', <<<'HTML'
<table>
<thead><tr><th>Уровень</th><th>Длительность</th><th>Штраф</th><th>Вес выпадения</th></tr></thead>
<tbody>
<tr><td><b>Лёгкая</b></td><td>15 минут</td><td>−5%</td><td>65</td></tr>
<tr><td><b>Средняя</b></td><td>30 минут</td><td>−10%</td><td>25</td></tr>
<tr><td><b>Тяжёлая</b></td><td>60 минут</td><td>−15%</td><td>10</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<p>Вес выпадения определяет относительную вероятность уровня среди доступных травм: лёгкие повреждения встречаются чаще, тяжёлые — реже.</p>

<div class="library-info-block library-info-block--warning"><strong>Повторная травма:</strong> новое повреждение той же части тела не может заменить более тяжёлую активную травму более слабой или сократить оставшееся время. Срок может быть продлён, а уровень — повышен.</div>

<h2>Какие характеристики страдают</h2>
HTML
            .$this->frame('Части тела и штрафы', <<<'HTML'
<table>
<thead><tr><th>Часть тела</th><th>Заблокированная экипировка</th><th>Характеристики</th></tr></thead>
<tbody>
<tr><td>Голова</td><td>Шлем</td><td>Интуиция, Мудрость, Интеллект</td></tr>
<tr><td>Плечи</td><td>Наплечники</td><td>Сила, Выносливость</td></tr>
<tr><td>Предплечья</td><td>Наручи</td><td>Сила</td></tr>
<tr><td>Левая рука</td><td>Левая рука</td><td>Сила</td></tr>
<tr><td>Правая рука</td><td>Правая рука</td><td>Сила</td></tr>
<tr><td>Туловище</td><td>Доспех</td><td>Выносливость</td></tr>
<tr><td>Грудь</td><td>Кольчуга</td><td>Выносливость</td></tr>
<tr><td>Ноги</td><td>Поножи</td><td>Ловкость, Выносливость</td></tr>
<tr><td>Ступни</td><td>Обувь</td><td>Ловкость</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<h2>Все травмы и их иконки</h2>
<p>В таблице показаны все активные травмы, их длительность и фактические штрафы. Данные и изображения автоматически обновляются из настроек игры.</p>

[[injury_catalog]]

<div class="library-info-block library-info-block--tip"><strong>Выздоровление:</strong> сейчас травма проходит по таймеру. Когда время истечёт, штраф и блокировка ячейки исчезнут; обновите страницу персонажа, если бинт ещё отображается.</div>
HTML;
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }
}
