<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\ItemAssembly\Infrastructure\Persistence\Models\ItemAssembly;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureMapConfig;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class LibraryTreasureHunterSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            if (LibraryArticle::withTrashed()->where('slug', 'kladoiskatel')->exists()) {
                $this->command?->info('Статья о кладоискателе уже существует; правки сохранены.');

                return;
            }
            $category = LibraryCategory::where('slug', 'reputatsii')->firstOrFail();
            $configs = TreasureMapConfig::with(['item', 'reward', 'reputation.tiers'])->orderBy('required_reputation')->get();
            if ($configs->isEmpty()) {
                throw new \RuntimeException('Сначала создайте репутацию, карты и шкатулки кладоискателя.');
            }
            $assemblies = ItemAssembly::where('type', 'treasure_map')->with(['components', 'rewards'])->get();
            $article = new LibraryArticle([
                'category_id' => $category->id, 'slug' => 'kladoiskatel',
                'title' => ['ru' => 'Кладоискатель: карты и поиск сокровищ', 'uk' => 'Шукач скарбів: карти та пошук скарбів'],
                'excerpt' => [
                    'ru' => 'Соберите четыре разных фрагмента, найдите место по карте и получите шкатулку сокровищ. Все тиры карт, пороги и бонусы репутации.',
                    'uk' => 'Зберіть чотири різні фрагменти, знайдіть місце за картою та отримайте скриньку скарбів. Усі рівні карт, пороги й бонуси репутації.',
                ],
                'content' => ['ru' => $this->content('ru', $configs, $assemblies), 'uk' => $this->content('uk', $configs, $assemblies)],
                'status' => LibraryArticle::STATUS_PUBLISHED, 'published_at' => now(),
                'sort_order' => (int) $category->articles()->max('sort_order') + 10,
            ]);
            foreach (['title', 'excerpt', 'content'] as $field) {
                if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                    $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
                }
            }
            $article->save();
            $this->command?->info('Опубликована статья о кладоискателе: #'.$article->id);
        });
    }

    public function refreshCatalogTables(): array
    {
        return DB::transaction(function (): array {
            $article = LibraryArticle::where('slug', 'kladoiskatel')->lockForUpdate()->firstOrFail();
            $configs = TreasureMapConfig::with(['item', 'reward', 'reputation.tiers'])->orderBy('required_reputation')->get();
            $assemblies = ItemAssembly::where('type', 'treasure_map')->with(['components', 'rewards'])->get();
            $before = $article->getTranslations('content');
            $after = [];
            foreach ($before as $locale => $content) {
                $headers = [
                    $locale === 'uk' ? 'Фрагменти I–IV: по 1 шт.' : 'Фрагменты I–IV: по 1 шт.' => $this->assemblyTable($locale, $configs, $assemblies),
                    'Карта' => $this->parameterTable($locale, $configs),
                    $locale === 'uk' ? 'Щабель' : 'Ступень' => $this->reputationTierTable($locale, $configs->first()->reputation),
                ];
                foreach ($headers as $heading => $table) {
                    $content = preg_replace_callback(
                        '~<table\b[^>]*>\s*<thead>\s*<tr\b[^>]*>\s*<th\b[^>]*>\s*'.preg_quote($heading, '~').'\s*</th>.*?</table>~su',
                        static fn () => $table,
                        $content, -1, $matches,
                    );
                    if ($matches !== 1) {
                        throw new \RuntimeException('Не найдена таблица «'.$heading.'» в статье: '.$locale);
                    }
                }
                $after[$locale] = $content;
            }
            if ($before === $after) {
                return ['article_id' => $article->id, 'updated' => false, 'maps' => $configs->count()];
            }
            $backup = 'backups/treasure-hunt/library-catalog-before-'.now()->format('Ymd-His-u').'.json';
            Storage::disk('local')->put($backup, json_encode([
                'article_id' => $article->id, 'content' => $before,
            ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            $article->setTranslations('content', $after);
            if (Schema::hasColumn('library_articles', 'content_legacy')) {
                $article->setAttribute('content_legacy', $article->getTranslation('content', 'ru'));
            }
            $article->save();

            return ['article_id' => $article->id, 'updated' => true, 'maps' => $configs->count(), 'backup' => $backup];
        });
    }

    private function content(string $locale, Collection $configs, Collection $assemblies): string
    {
        $uk = $locale === 'uk';
        $reputation = $configs->first()->reputation;
        $intro = $uk
            ? '<p><b>Шукач скарбів</b> — репутація для тих, хто готовий вирушати в дорогу заради захованих знахідок. Збирайте карти, звіряйте їх із місцевістю та шукайте скарби. Кожна успішна знахідка приносить закриту скриньку й очки репутації.</p>'
            : '<p><b>Кладоискатель</b> — репутация для тех, кто готов отправиться в путь ради спрятанных находок. Собирайте карты, сверяйте их с местностью и ищите сокровища. Каждая успешная находка приносит закрытую шкатулку и очки репутации.</p>';
        $intro .= '<p>[[reputation:'.$reputation->id.';display:inline]]</p>';
        $assembly = $uk
            ? '<p>Для однієї карти потрібні <b>чотири різні фрагменти I, II, III та IV одного рівня</b>, по одному кожного виду. Натисніть на будь-який із цих фрагментів у рюкзаку та виберіть <b>«Зібрати карту»</b>. Чотири частини витратяться, а готова карта з’явиться в рюкзаку.</p><p>Чотири однакові фрагменти або суміш різних рівнів не підійдуть. Зайві фрагменти залишаються в рюкзаку. Фрагменти складаються у стоси, їх можна передавати та продавати на аукціоні. <b>Готова карта індивідуальна й не передається.</b></p>'
            : '<p>Для одной карты нужны <b>четыре разных фрагмента I, II, III и IV одного тира</b>, по одному каждого вида. Нажмите на любой из этих фрагментов в рюкзаке и выберите <b>«Собрать карту»</b>. Четыре части израсходуются, а готовая карта появится в рюкзаке.</p><p>Четыре одинаковых фрагмента или смесь разных тиров не подойдут. Лишние фрагменты остаются в рюкзаке. Фрагменты складываются в стопки, их можно передавать и продавать на аукционе. <b>Готовая карта индивидуальна и не передаётся.</b></p>';
        $assembly .= $this->assemblyTable($locale, $configs, $assemblies);
        $destination = $uk
            ? '<p>Під час збирання для кожної карти <b>окремо й випадково визначаються територія та локація скарбу на ній</b>. Назву території вказано в підказці та інформації про карту. Номер локації до успішної перевірки прихований.</p><p>Дві карти одного рівня можуть вести до різних територій і локацій. Повторні перевірки, вихід із гри та перезавантаження сторінки не змінюють обране місце.</p>'
            : '<p>При сборке для каждой карты <b>отдельно и случайно определяются территория и локация клада на ней</b>. Название территории указано в тултипе и информации о карте. Номер локации до успешной проверки скрыт.</p><p>Две карты одного тира могут вести на разные территории и локации. Повторные проверки, выход из игры и перезагрузка страницы не меняют выбранное место.</p>';
        $search = $uk
            ? '<ol><li>Перевірте поріг репутації для своєї карти та вирушайте на зазначену територію.</li><li>На локаціях відкривайте меню карти в рюкзаку й вибирайте <b>«Звірити з місцевістю»</b>. Повідомлення про невідповідність означає, що потрібно перевірити інше місце.</li><li>Коли локація збігатиметься, карта активується. У підказці та інформації про карту з’явиться <b>номер локації</b>.</li><li>Відкрийте опис цієї локації та натисніть <b>«Шукати скарб»</b>. Дочекайтеся завершення шкали пошуку. Спробу можна скасувати.</li></ol><p>Одночасно може бути активована лише одна карта. Пошук недоступний під час бою, після смерті персонажа, під час видобутку ресурсу або зламу скрині. Перехід на іншу локацію перериває поточну спробу, але зберігає активацію карти.</p>'
            : '<ol><li>Проверьте порог репутации для своей карты и отправляйтесь на указанную территорию.</li><li>На локациях открывайте меню карты в рюкзаке и выбирайте <b>«Сверить с местностью»</b>. Сообщение о несовпадении означает, что нужно проверить другое место.</li><li>Когда локация совпадёт, карта активируется. В тултипе и информации о карте появится <b>номер локации</b>.</li><li>Откройте описание этой локации и нажмите <b>«Искать клад»</b>. Дождитесь завершения шкалы поиска. Попытку можно отменить.</li></ol><p>Одновременно может быть активирована только одна карта. Поиск недоступен во время боя, после смерти персонажа, во время добычи ресурса или взлома сундука. Переход на другую локацию прерывает текущую попытку, но сохраняет активацию карты.</p>';
        $result = $uk
            ? '<p><b>Невдала спроба:</b> карту не витрачено, скринька й репутація не видаються. Можна знову натиснути «Повторити пошук».</p><p><b>Успішна спроба:</b> карта витрачається, у рюкзак надходить закрита скринька відповідного рівня та нараховується репутація. Про знахідку повідомить особисте системне повідомлення в чаті.</p><p><b>Карта витрачається під час виявлення скарбу, а не відкриття скриньки.</b> Скриньку потім потрібно відкрити з рюкзака через злам: враховуйте вимоги замка, відмичок і пастки. Репутацію за знахідку ви отримуєте ще до відкриття скриньки.</p><p>Кожна спроба пошуку незалежна. Невдачі не підвищують шанс, гарантованого виявлення після певної кількості спроб немає.</p>'
            : '<p><b>Неудачная попытка:</b> карта не расходуется, шкатулка и репутация не выдаются. Можно снова нажать «Повторить поиск».</p><p><b>Успешная попытка:</b> карта расходуется, в рюкзак поступает закрытая шкатулка соответствующего тира и начисляется репутация. О находке сообщит личное системное сообщение в чате.</p><p><b>Карта расходуется при обнаружении клада, а не при открытии шкатулки.</b> Шкатулку затем нужно открыть из рюкзака через взлом: учитывайте требования замка, отмычек и ловушки. Репутацию за находку вы получаете ещё до открытия шкатулки.</p><p>Каждая попытка поиска независима. Неудачи не повышают шанс, гарантированного обнаружения после определённого числа попыток нет.</p>';
        $parameters = $uk
            ? '<p>Поріг потрібен для перевірки карти й початку пошуку. Шанс і час у таблиці — <b>базові, без бонусів репутації</b>. Репутація нараховується лише за успішну знахідку.</p>'
            : '<p>Порог требуется для проверки карты и начала поиска. Шанс и время в таблице — <b>базовые, без бонусов репутации</b>. Репутация начисляется только за успешную находку.</p>';
        $parameters .= $this->parameterTable($locale, $configs);

        return $this->frame($uk ? 'Репутація шукача скарбів' : 'Репутация кладоискателя', $intro)
            .$this->frame($uk ? 'Фрагменти та збирання карт' : 'Фрагменты и сборка карт', $assembly)
            .$this->frame($uk ? 'Куди веде готова карта' : 'Куда ведёт готовая карта', $destination)
            .$this->frame($uk ? 'Як розпочати пошук' : 'Как начать поиск', $search)
            .$this->frame($uk ? 'Результат пошуку та скринька' : 'Результат поиска и шкатулка', $result)
            .$this->frame($uk ? 'Рівні карт та умови пошуку' : 'Тиры карт и условия поиска', $parameters)
            .$this->frame($uk ? 'Щаблі й бонуси репутації' : 'Ступени и бонусы репутации', $this->bonusTable($locale, $reputation));
    }

    private function parameterTable(string $locale, Collection $configs): string
    {
        $uk = $locale === 'uk';
        $headers = $uk ? ['Карта', 'Поріг репутації', 'Шанс', 'Час пошуку', 'Репутація за знахідку'] : ['Карта', 'Порог репутации', 'Шанс', 'Время поиска', 'Репутация за находку'];
        $rows = '';
        foreach ($configs as $config) {
            $rows .= '<tr><td>'.e($config->item->getTranslation('name', $locale)).'</td><td align="center">'.$config->required_reputation.'</td><td align="center">'.$config->success_chance_percent.'%</td><td align="center">'.$config->search_duration_seconds.' '.($uk ? 'с' : 'сек.').'</td><td align="center">+'.$config->reputation_reward.'</td></tr>';
        }

        return $this->table($headers, $rows);
    }

    private function assemblyTable(string $locale, Collection $configs, Collection $assemblies): string
    {
        $rows = '';
        foreach ($configs as $config) {
            $assembly = $assemblies->first(fn ($candidate) => $candidate->rewards->contains('id', $config->share_item_id));
            if ($assembly === null || $assembly->components->count() !== 4) {
                throw new \RuntimeException('Не настроены четыре фрагмента для карты #'.$config->share_item_id);
            }
            $fragments = $assembly->components->map(fn ($part) => '[[item:'.$part->id.']]')->implode(' ');
            $rows .= '<tr><td colspan="3"><b style="color:'.e($config->item->rarity->color()).'">'.e($config->item->getTranslation('name', $locale)).'</b></td></tr>'
                .'<tr><td align="center"><div class="structures" style="white-space:nowrap">'.$fragments.'</div></td><td align="center"><div class="structures">[[item:'.$config->share_item_id.']]</div></td><td align="center"><div class="structures">[[item:'.$config->reward_share_item_id.']]</div></td></tr>';
        }

        return $this->table($locale === 'uk' ? ['Фрагменти I–IV: по 1 шт.', 'Готова карта', 'Знахідка'] : ['Фрагменты I–IV: по 1 шт.', 'Готовая карта', 'Находка'], $rows);
    }

    private function reputationTierTable(string $locale, Reputation $reputation): string
    {
        $uk = $locale === 'uk';
        $bonuses = DB::table('treasure_reputation_bonuses')->whereIn('reputation_tier_id', $reputation->tiers->modelKeys())->get()->keyBy('reputation_tier_id');
        $rows = '';
        foreach ($reputation->tiers->sortBy('min_points') as $tier) {
            $bonus = $bonuses->get($tier->id);
            $name = $tier->getTranslation('medal_name', $locale) ?: ($uk ? 'Без звання' : 'Без звания');
            $range = $tier->max_points === null ? $tier->min_points.'+' : $tier->min_points.'–'.$tier->max_points;
            $rows .= '<tr><td>'.e($name).'</td><td align="center">'.$range.'</td><td align="center">+'.(float) ($bonus?->search_chance_bonus_percent ?? 0).' п.п.</td><td align="center">'.(float) ($bonus?->search_speed_bonus_percent ?? 0).'%</td></tr>';
        }

        return $this->table($uk ? ['Щабель', 'Репутація', 'До шансу', 'Прискорення пошуку'] : ['Ступень', 'Репутация', 'К шансу', 'Ускорение поиска'], $rows);
    }

    private function bonusTable(string $locale, Reputation $reputation): string
    {
        $uk = $locale === 'uk';
        $text = $uk
            ? '<p>Зростання репутації відкриває карти з вищими вимогами, покращує шанс знахідки й прискорює пошук. Застосовується <b>лише найвищий досягнутий щабель</b>: бонуси різних щаблів не складаються.</p>'
            : '<p>Рост репутации открывает карты с более высокими требованиями, улучшает шанс находки и ускоряет поиск. Применяется <b>только наивысшая достигнутая ступень</b>: бонусы разных ступеней не складываются.</p>';
        $text .= $this->reputationTierTable($locale, $reputation);

        return $text.($uk
            ? '<p>Бонус шансу додається у відсоткових пунктах: наприклад, 50% + 5 п.п. = 55%. Підсумковий шанс не перевищує 100%. Угорі показані поточні умови; параметри можуть змінюватися з балансуванням гри.</p>'
            : '<p>Бонус шанса прибавляется в процентных пунктах: например, 50% + 5 п.п. = 55%. Итоговый шанс не превышает 100%. Выше приведены текущие условия; параметры могут меняться при балансировке игры.</p>');
    }

    private function table(array $headers, string $rows): string
    {
        return '<table class="coll brd2-all p10h p4v" width="100%" cellspacing="0" cellpadding="0"><thead><tr class="bg_l">'
            .implode('', array_map(fn ($heading) => '<th align="center">'.e($heading).'</th>', $headers))
            .'</tr></thead><tbody>'.$rows.'</tbody></table>';
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
