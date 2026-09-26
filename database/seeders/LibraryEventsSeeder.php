<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryEventsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->updateOrCreate(
                ['slug' => 'sobytiya'],
                [
                    'parent_id' => null,
                    'name' => 'События',
                    'description' => 'Дневные и недельные активности, временные события мира, награды и влияние на территориях.',
                    'sort_order' => 90,
                    'is_active' => true,
                ],
            );

            $this->article(
                category: $category,
                title: 'Активности',
                slug: 'aktivnosti',
                excerpt: 'Дневные и недельные задания: как накапливается прогресс, обновляются циклы и выдаются награды.',
                content: $this->activitiesContent(),
                sortOrder: 0,
            );

            $this->article(
                category: $category,
                title: 'События',
                slug: 'mirovye-sobytiya',
                excerpt: 'Ограниченные по времени события на картах: общая цель, личный лимит, влияние и временные предметы.',
                content: $this->worldEventsContent(),
                sortOrder: 10,
            );
        });
    }

    private function article(
        LibraryCategory $category,
        string $title,
        string $slug,
        string $excerpt,
        string $content,
        int $sortOrder,
    ): void {
        LibraryArticle::withTrashed()->updateOrCreate(
            ['slug' => $slug],
            [
                'category_id' => $category->id,
                'author_id' => null,
                'title' => $title,
                'excerpt' => $excerpt,
                'content' => $content,
                'status' => LibraryArticle::STATUS_PUBLISHED,
                'published_at' => now(),
                'sort_order' => $sortOrder,
                'deleted_at' => null,
            ],
        );
    }

    private function activitiesContent(): string
    {
        return <<<'HTML'
<p><b>Активности</b> — это регулярно обновляемые задания, которые выполняются одновременно с обычными приключениями. Побеждайте указанных монстров, заполняйте шкалу прогресса и получайте награду за завершение текущего цикла.</p>

<div class="library-info-block library-info-block--tip"><strong>Где посмотреть:</strong> откройте раздел «События» и перейдите во вкладку «Активности». Задания разделены на дневные и недельные.</div>

<h2>Виды активностей</h2>
HTML
            .$this->frame('Периоды обновления', <<<'HTML'
<table>
<thead><tr><th>Вид</th><th>Как работает</th><th>Когда начинается новый цикл</th></tr></thead>
<tbody>
<tr><td><b>Дневная</b></td><td>Короткая цель, которую можно выполнить в течение текущего дня</td><td>С началом нового игрового дня</td></tr>
<tr><td><b>Недельная</b></td><td>Более объёмная цель с увеличенным временем на выполнение</td><td>С началом новой игровой недели</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<p>Прогресс каждого цикла хранится отдельно. Незавершённый результат прошлого дня или недели не переносится в новый период.</p>

<h2>Как выполнить активность</h2>
HTML
            .$this->frame('Порядок выполнения', <<<'HTML'
<ol>
<li>Откройте вкладку с дневными или недельными активностями.</li>
<li>Посмотрите описание задания, требуемое количество и награду.</li>
<li>Побеждайте указанного в задании монстра.</li>
<li>Каждая подходящая победа увеличивает личный прогресс на единицу.</li>
<li>Когда шкала заполнится, награда автоматически поступит в рюкзак или на баланс.</li>
</ol>
HTML)
            .<<<'HTML'

<div class="library-info-block library-info-block--important"><strong>Награду не нужно забирать вручную:</strong> после выполнения цели игра сразу выдаёт её и отправляет персональное системное сообщение в чат.</div>

<h2>Награды</h2>
<p>У каждой активности есть основная предметная награда. Дополнительно задание может принести монеты, диаманты или ещё один предмет. Точный состав и количество всегда показаны на карточке активности.</p>

<ul>
<li>предметы добавляются в рюкзак;</li>
<li>монеты и диаманты зачисляются на баланс персонажа;</li>
<li>одну активность можно завершить только один раз за её текущий цикл;</li>
<li>после обновления периода прогресс снова начинается с нуля.</li>
</ul>

<div class="library-info-block library-info-block--warning"><strong>Следите за периодом:</strong> если не успеть заполнить шкалу до обновления дневного или недельного цикла, накопленный прогресс будет потерян.</div>
HTML;
    }

    private function worldEventsContent(): string
    {
        return <<<'HTML'
<p><b>Мировые события</b> — это ограниченные по времени происшествия, которые разворачиваются на определённой карте. Во время события игроки сообща собирают особые предметы или уничтожают появившихся монстров, но каждый участник имеет собственный предел вклада.</p>

<div class="library-info-block library-info-block--tip"><strong>Где посмотреть:</strong> во вкладках «Текущие события», «Будущие события» и «Мои события» отображаются состояние события, карта, цель, таймеры и ваш личный прогресс.</div>

<h2>Цели событий</h2>
HTML
            .$this->frame('Возможные задачи', <<<'HTML'
<table>
<thead><tr><th>Тип события</th><th>Задача участника</th><th>Как засчитывается результат</th></tr></thead>
<tbody>
<tr><td><b>Сбор предметов</b></td><td>Найти на локациях карты особые ресурсы и подобрать их</td><td>Предмет должен быть успешно собран персонажем</td></tr>
<tr><td><b>Уничтожение монстров</b></td><td>Найти специальных существ события и победить их</td><td>Победа засчитывается игроку, который нанёс последний удар</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<p>Если для события не указаны конкретные локации, цели появляются на случайно выбранных локациях всей карты. Их количество ограничено, а после сбора или победы новые цели постепенно появляются снова, пока событие активно.</p>

<h2>Общий и личный прогресс</h2>
HTML
            .$this->frame('Две шкалы события', <<<'HTML'
<table>
<tbody>
<tr><th>Общий прогресс</th><td>Показывает вклад всех игроков и оставшееся количество целей во всём событии</td></tr>
<tr><th>Ваш прогресс</th><td>Показывает личный вклад персонажа и его индивидуальный лимит</td></tr>
<tr><th>До завершения</th><td>Таймер оставшегося времени активного события</td></tr>
<tr><th>До начала</th><td>Таймер появления будущего события</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<div class="library-info-block library-info-block--important"><strong>Личный лимит:</strong> после его достижения персонаж больше не может увеличивать свой прогресс в этом запуске события, даже если общая цель ещё не выполнена.</div>

<h2>Влияние на территории</h2>
<p>За каждую засчитанную цель персонаж получает указанное количество <b>влияния</b> выбранной территории. Событие может проходить на дочерней карте, например в Канализации, а влияние при этом начисляться её родительской территории. Влияние сохраняется между запусками и открывает территориальные уровни, медали, награды, особых NPC, квесты, проходы и товары магазина влияния.</p>

<p>Количество уже накопленного влияния можно посмотреть во вкладке «Влияние». Там для каждой территории отображаются текущий уровень и прогресс до следующего порога.</p>

<h2>Особые предметы события</h2>
<p>Предметы, найденные в событиях на сбор, попадают в рюкзак персонажа. Они имеют ограниченный срок жизни и автоматически исчезнут после его окончания независимо от того, где находятся.</p>

<div class="library-info-block library-info-block--warning"><strong>Не откладывайте использование:</strong> срок жизни предмета продолжает идти после завершения события. Просроченные предметы не сохраняются для следующего запуска.</div>

<h2>Повторное проведение</h2>
<p>После завершения событие перемещается из текущих в будущие. Если для него настроен повтор, на карточке появляется обратный отсчёт до следующего запуска. Новый запуск получает отдельные общую шкалу и личный прогресс для каждого игрока.</p>
HTML;
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }
}
