<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryLockpickingSeeder extends Seeder
{
    private const LOCKPICK_NAMES = [
        'Грубая отмычка',
        'Усиленная отмычка',
        'Стальная отмычка',
        'Точная отмычка',
        'Мастерская отмычка',
        'Рунная отмычка',
    ];

    private const CHEST_NAMES = [
        'Простой запертый сундук', 'Медный ларец', 'Сундук странника',
        'Укреплённый сундук', 'Железный сейф', 'Запечатанный сундук',
        'Стальной сундук', 'Сундук дозорного', 'Закалённый ларец',
        'Сундук тайной стражи', 'Рунический сейф', 'Проклятый ковчег',
        'Сундук древних', 'Сокровищница магистра', 'Запретный ковчег',
        'Рунный ковчег', 'Сундук владыки', 'Хранилище Бездны',
    ];

    private const CASKETS = [
        'Потрёпанная шкатулка' => '1–14',
        'Медная шкатулка' => '15–29',
        'Стальная шкатулка' => '30–44',
        'Руническая шкатулка' => '45–59',
        'Древняя шкатулка' => '60–69',
        'Шкатулка Бездны' => '70+',
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->where('slug', 'professii')->firstOrFail();
            $lockpicks = ShareItem::query()
                ->whereIn('name', self::LOCKPICK_NAMES)
                ->with('lockpickConfig')
                ->get()
                ->keyBy('name');
            $chests = ShareItem::query()
                ->whereIn('name', self::CHEST_NAMES)
                ->with('lockConfig.trapEffect')
                ->get()
                ->keyBy('name');
            $caskets = ShareItem::query()
                ->whereIn('name', array_keys(self::CASKETS))
                ->with('lockConfig.trapEffect')
                ->get()
                ->keyBy('name');

            LibraryArticle::withTrashed()->updateOrCreate(
                ['slug' => 'vzломshchik'],
                [
                    'category_id' => $category->id,
                    'author_id' => null,
                    'title' => 'Взломщик',
                    'excerpt' => 'Вскрытие замков на сундуках и шкатулках: шанс успеха, тиры отмычек, ловушки и развитие профессии.',
                    'content' => $this->content($lockpicks, $chests, $caskets),
                    'status' => LibraryArticle::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'sort_order' => 70,
                    'deleted_at' => null,
                ],
            );
        });
    }

    private function content($lockpicks, $chests, $caskets): string
    {
        return <<<'HTML'
<p><b>Взломщик</b> — мирная профессия для открытия запертых сундуков и шкатулок. Навык влияет на вероятность успешного взлома и его длительность, а подходящая отмычка помогает работать быстрее и безопаснее.</p>

<div class="library-info-block library-info-block--tip"><strong>Главное:</strong> выберите сундук на локации или шкатулку в рюкзаке, нажмите «Открыть», выберите доступную отмычку и дождитесь заполнения шкалы.</div>

<h2>Как происходит взлом</h2>
HTML
            .$this->frame('Порядок действий', <<<'HTML'
<ol>
<li>Найдите запертый сундук на локации или в рюкзаке.</li>
<li>Откройте окно взлома и выберите отмычку кликом по её изображению.</li>
<li>Наведите курсор на отмычку, чтобы увидеть её тир, требования и бонусы.</li>
<li>Нажмите <b>«Начать взлом»</b> и дождитесь завершения шкалы.</li>
<li>При успехе найденные предметы сразу попадут в рюкзак.</li>
</ol>
HTML)
            .<<<'HTML'

<p>Взлом можно отменить до заполнения шкалы — отмычка при этом не расходуется. Действие недоступно во время боя, после смерти и одновременно с добычей ресурсов.</p>

<div class="library-info-block library-info-block--important"><strong>Сундук не резервируется:</strong> несколько игроков могут одновременно начать взлом одного сундука. Содержимое получит тот, кто первым успешно завершит попытку.</div>

<h2>Шанс успеха и время</h2>
HTML
            .$this->frame('Как рассчитывается сложность', <<<'HTML'
<ul>
<li>При равенстве навыка и сложности замка базовый шанс составляет <b>80%</b>.</li>
<li>Навык выше сложности повышает шанс и постепенно ускоряет взлом.</li>
<li>Замок выше навыка всё равно можно открыть, но вероятность быстро снижается.</li>
<li>Итоговый шанс ограничен диапазоном от <b>5%</b> до <b>95%</b>.</li>
<li>Ловушка может дополнительно снизить вероятность успешного открытия.</li>
</ul>
HTML)
            .<<<'HTML'

<p>Замки и отмычки разделены на шесть тиров. Если тир отмычки ниже тира замка, за каждый уровень разницы шанс уменьшается на <b>10 процентных пунктов</b>, а время увеличивается на <b>15%</b>. Отмычка подходящего или более высокого тира штрафа не получает.</p>

<h2>Отмычки</h2>
<p>Каждый следующий тир требует большего уровня Взломщика. Недоступная отмычка отображается затемнённой и не может быть выбрана. Бонус скорости уменьшает время, сохранение даёт шанс не сломать отмычку при провале, а обход ловушки — избежать её последствий.</p>
HTML
            .$this->lockpickFrame($lockpicks)
            .<<<'HTML'

<div class="library-info-block library-info-block--tip"><strong>Рекомендуемый запас:</strong> для последовательного открытия трёх сундуков одного тира держите около <b>5 подходящих отмычек</b>. При хорошем везении часть останется, но гарантированного количества нет — отмычка расходуется только при неудаче.</div>

<p>Отмычкой более высокого тира можно открывать слабые замки без штрафа, но это обычно невыгодно. Отмычка ниже тира замка получает штраф за каждый недостающий тир.</p>

<h2>Сундуки и сложность замков</h2>
<p>В каждом тире есть обычный сундук без ловушки, защищённый сундук со «Слабостью» и опасный сундук с «Разрывом брони». Чем опаснее замок, тем ценнее его гарантированная и дополнительная добыча.</p>
HTML
            .$this->chestFrame($chests)
            .<<<'HTML'

<h2>Шкатулки с монстров</h2>
<p>Шкатулки — переносная разновидность запертого сундука. Они выпадают с монстров и сразу попадают в рюкзак, поэтому их не нужно открывать на месте и за них нельзя конкурировать с другими игроками.</p>
HTML
            .$this->frame('Как получить и открыть шкатулку', <<<'HTML'
<ol>
<li>Победите монстра подходящего уровня и получите шкатулку в рюкзак.</li>
<li>Откройте рюкзак и нажмите <b>«Открыть»</b> на найденной шкатулке.</li>
<li>Выберите отмычку и завершите взлом по обычным правилам профессии.</li>
<li>При успехе шкатулка исчезнет, а найденные предметы попадут в рюкзак. Монеты из «Горстки монет» сразу зачислятся на баланс.</li>
</ol>
HTML)
            .<<<'HTML'

<div class="library-info-block library-info-block--tip"><strong>Шанс выпадения:</strong> <b>2%</b> с обычного монстра и <b>5%</b> с босса. Награда выпадает только с подходящих по уровню противников: монстр не должен быть ниже игрока более чем на 10 уровней.</div>
HTML
            .$this->casketFrame($caskets)
            .<<<'HTML'

<p>Чем выше уровень монстра, тем сложнее замок и опаснее ловушка на шкатулке, но тем ценнее её содержимое. Внутри можно найти основной ресурс тира, зелье, отмычку и монеты.</p>

<div class="library-info-block library-info-block--warning"><strong>Важно:</strong> неудачная попытка не уничтожает шкатулку. Её можно попробовать открыть повторно, однако отмычка может сломаться, а ловушка — нанести урон и наложить отрицательный эффект.</div>

<h2>Неудача и ловушки</h2>
HTML
            .$this->frame('Последствия провала', <<<'HTML'
<ul>
<li>Замок остаётся закрытым, после чего можно начать новую попытку.</li>
<li>Отмычка обычно ломается, но редкие отмычки имеют шанс сохраниться.</li>
<li>Если на сундуке установлена ловушка, она может нанести мгновенный урон и наложить отрицательный эффект.</li>
<li>Шанс обхода ловушки разыгрывается отдельно: при успехе урон и отрицательный эффект не применяются.</li>
</ul>
HTML)
            .<<<'HTML'

<div class="library-info-block library-info-block--warning"><strong>Совет:</strong> слабой отмычкой можно попытаться открыть замок высокого тира, но такая экономия заметно снижает шанс и увеличивает время. Для опасных сундуков с ловушкой выгоднее использовать лучшую доступную отмычку.</div>

<h2>Развитие профессии</h2>
<p>Опыт Взломщика начисляется только за успешно открытый запертый сундук. Чем сложнее замок, тем больше опыта он может принести. Максимальный уровень профессии — <b>300</b>.</p>
HTML;
    }

    private function lockpickFrame($lockpicks): string
    {
        $rows = '';
        $ranges = ['1–49', '50–99', '100–149', '150–199', '200–249', '250–300'];
        foreach (self::LOCKPICK_NAMES as $index => $name) {
            $item = $lockpicks->get($name);
            if ($item === null || $item->lockpickConfig === null) {
                continue;
            }

            $config = $item->lockpickConfig;
            $requiredSkill = $config->tier === 1 ? 1 : ($config->tier - 1) * 50;
            $rows .= sprintf(
                '<tr><td>[[item:%d]]</td><td>%s</td><td>%d</td><td>Скорость +%d%%<br>Сохранение %d%%<br>Обход ловушки %d%%</td><td><b>5 шт.</b></td></tr>',
                $item->id,
                $ranges[$index],
                $requiredSkill,
                $config->speed_bonus_percent,
                $config->failure_preserve_chance_percent,
                $config->trap_avoid_chance_percent,
            );
        }

        return $this->frame('Тиры отмычек', <<<HTML
<table>
<thead><tr><th>Отмычка</th><th>Замки</th><th>Навык</th><th>Бонусы</th><th>Запас</th></tr></thead>
<tbody>{$rows}</tbody>
</table>
HTML);
    }

    private function casketFrame($caskets): string
    {
        $rows = '';
        foreach (self::CASKETS as $name => $monsterLevels) {
            $casket = $caskets->get($name);
            if ($casket === null || $casket->lockConfig === null) {
                continue;
            }

            $config = $casket->lockConfig;
            $trap = $config->trap_effect_id === null && $config->trap_damage_percent === 0
                ? 'Нет'
                : sprintf(
                    '%s<br>Урон %d%% HP',
                    $config->trapEffect?->name ?? 'Ловушка',
                    $config->trap_damage_percent,
                );
            $rows .= sprintf(
                '<tr><td>[[item:%d]]</td><td>%s</td><td>%d</td><td>%d сек.</td><td>%d</td><td>%s</td></tr>',
                $casket->id,
                $monsterLevels,
                $config->lock_required_skill,
                $config->lock_duration_seconds,
                $config->experience_reward,
                $trap,
            );
        }

        return $this->frame('Линейка шкатулок', <<<HTML
<table>
<thead><tr><th>Шкатулка</th><th>Уровень монстров</th><th>Сложность</th><th>Время</th><th>Опыт</th><th>Ловушка</th></tr></thead>
<tbody>{$rows}</tbody>
</table>
HTML);
    }

    private function chestFrame($chests): string
    {
        $rows = '';
        foreach (self::CHEST_NAMES as $name) {
            $chest = $chests->get($name);
            if ($chest === null || $chest->lockConfig === null) {
                continue;
            }

            $config = $chest->lockConfig;
            $trap = $config->trap_effect_id === null && $config->trap_damage_percent === 0
                ? 'Нет'
                : sprintf(
                    '%s<br>Урон %d%% HP<br>%d сек.',
                    $config->trapEffect?->name ?? 'Ловушка',
                    $config->trap_damage_percent,
                    $config->trap_effect_duration_seconds,
                );
            $rows .= sprintf(
                '<tr><td>[[item:%d]]</td><td>%d</td><td>%d сек.</td><td>%d</td><td>−%d%%</td><td>%s</td></tr>',
                $chest->id,
                $config->lock_required_skill,
                $config->lock_duration_seconds,
                $config->experience_reward,
                $config->trap_chance_penalty_percent,
                $trap,
            );
        }

        return $this->frame('Линейка сундуков', <<<HTML
<table>
<thead><tr><th>Сундук</th><th>Сложность</th><th>Время</th><th>Опыт</th><th>Штраф шанса</th><th>Ловушка</th></tr></thead>
<tbody>{$rows}</tbody>
</table>
HTML);
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }
}
