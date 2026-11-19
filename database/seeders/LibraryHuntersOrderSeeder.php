<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

final class LibraryHuntersOrderSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            if (LibraryArticle::withTrashed()->where('slug', 'orden-okhotnikov')->exists()) {
                $this->command?->info('Статья Ордена Охотников уже существует; правки сохранены.');

                return;
            }
            $category = LibraryCategory::where('slug', 'reputatsii')->firstOrFail();
            $reputation = Reputation::with(['npc', 'tiers'])->findOrFail(2);
            $shop = Structure::where('reputation_id', 2)->where('type', Structure::TYPE_SHOP)
                ->with('shopItems.item')->firstOrFail();
            $article = new LibraryArticle([
                'category_id' => $category->id,
                'slug' => 'orden-okhotnikov',
                'title' => ['ru' => 'Орден Охотников', 'uk' => 'Орден Мисливців'],
                'excerpt' => [
                    'ru' => 'Контракты егеря Вульфгара, ступени репутации, охотничьи эликсиры и подвиг «Тень Прародителя».',
                    'uk' => 'Контракти єгеря Вульфгара, щаблі репутації, мисливські еліксири та подвиг «Тінь Прародителя».',
                ],
                'content' => ['ru' => $this->content('ru', $reputation, $shop), 'uk' => $this->content('uk', $reputation, $shop)],
                'status' => LibraryArticle::STATUS_PUBLISHED,
                'published_at' => now(),
                'sort_order' => (int) $category->articles()->max('sort_order') + 10,
            ]);
            foreach (['title', 'excerpt', 'content'] as $field) {
                if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                    $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
                }
            }
            $article->save();
            $this->command?->info('Опубликована статья «Орден Охотников»: #'.$article->id);
        });
    }

    public function restyleExistingArticle(): void
    {
        DB::transaction(function (): void {
            $article = LibraryArticle::where('slug', 'orden-okhotnikov')->lockForUpdate()->firstOrFail();
            $before = $article->getTranslations('content');
            $after = array_map(fn (string $html): string => $this->frameSections($html), $before);
            if ($before === $after) {
                $this->command?->info('Оформление уже применено.');

                return;
            }
            $backup = 'backups/library/hunters-order-before-restyle-'.now()->format('Ymd-His-u').'.json';
            if (! Storage::disk('local')->put($backup, json_encode(['article_id' => $article->id, 'content' => $before], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))) {
                throw new \RuntimeException('Не удалось сохранить копию статьи.');
            }
            $article->setTranslations('content', $after);
            if (Schema::hasColumn('library_articles', 'content_legacy')) {
                $article->setAttribute('content_legacy', $after['ru']);
            }
            $article->save();
        });
    }

    private function frameSections(string $html): string
    {
        if (str_contains($html, 'class="library-game-frame"')) {
            return $html;
        }
        $parts = preg_split('~<h2>(.*?)</h2>~su', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = array_shift($parts);
        for ($index = 0; $index < count($parts); $index += 2) {
            $result .= $this->frame($parts[$index], $parts[$index + 1]);
        }

        return $result;
    }

    private function content(string $locale, Reputation $reputation, Structure $shop): string
    {
        $uk = $locale === 'uk';
        $text = $uk
            ? '<h2>Шлях мисливця</h2><p>Орден Мисливців об’єднує тих, хто захищає дороги й поселення від небезпечних створінь. Виконуйте контракти, здобувайте трофеї та доведіть, що вам під силу полювання на найсильнішого хижака.</p><p>Зверніться до єгеря Вульфгара на локації <b>«Пост Єгеря», №305</b>. У його діалозі доступні завдання Ордену та магазин.</p>'
            : '<h2>Путь охотника</h2><p>Орден Охотников объединяет тех, кто защищает дороги и поселения от опасных существ. Выполняйте контракты, добывайте трофеи и докажите, что вам по силам охота на сильнейшего хищника.</p><p>Обратитесь к егерю Вульфгару на локации <b>«Пост Егеря», №305</b>. В его диалоге доступны задания Ордена и магазин.</p>';
        $text .= '<p>[[npc:'.$reputation->npc_id.';display:inline]]</p><p>[[reputation:2;display:inline]]</p>';
        $text .= $uk
            ? '<h2>Як здобувати репутацію</h2><ol><li>Візьміть контракт у Вульфгара: завдання випадково обирається зі списку вашого поточного щабля.</li><li>Виконайте цілі контракту: перемагайте вказаних монстрів або здобувайте потрібні трофеї. Звичайне полювання без відповідного завдання саме собою не замінює контракт.</li><li>Поверніться до єгеря й здайте завдання, щоб отримати репутацію та інші нагороди.</li></ol><p><b>Одночасно можна виконувати один контракт Ордену. Наступний доступний через 2 дні після завершення попереднього.</b> Зі зростанням репутації змінюються цілі та винагороди.</p>'
            : '<h2>Как получать репутацию</h2><ol><li>Возьмите контракт у Вульфгара: задание случайно выбирается из списка вашей текущей ступени.</li><li>Выполните цели контракта: побеждайте указанных монстров или добывайте нужные трофеи. Обычная охота без соответствующего задания сама по себе не заменяет контракт.</li><li>Вернитесь к егерю и сдайте задание, чтобы получить репутацию и другие награды.</li></ol><p><b>Одновременно можно выполнять один контракт Ордена. Следующий доступен через 2 дня после завершения предыдущего.</b> С ростом репутации меняются цели и награды.</p>';
        $text .= '<h2>'.($uk ? 'Щаблі репутації' : 'Ступени репутации').'</h2><table class="coll brd2-all p10h p4v" width="100%" cellspacing="0" cellpadding="0"><thead><tr class="bg_l"><th>'.($uk ? 'Щабель' : 'Ступень').'</th><th>'.($uk ? 'Репутація від' : 'Репутация от').'</th></tr></thead><tbody>';
        foreach ($reputation->tiers->sortBy('min_points') as $tier) {
            $name = $tier->getTranslation('medal_name', $locale) ?: ($uk ? 'Новачок' : 'Новичок');
            $text .= '<tr><td>'.e($name).'</td><td align="center">'.$tier->min_points.'</td></tr>';
        }
        $text .= '</tbody></table>';
        $text .= $uk
            ? '<h2>Магазин Ордену</h2><p>Починаючи з 500 очок, доступні мисливські еліксири. Вищі щаблі відкривають сильніші варіанти. Вартість, поріг репутації та обмеження вказані на картці товару.</p><ul><li><b>Еліксир Тихої стежки</b> — зменшує ймовірність того, що монстри нападуть першими. На босів не діє.</li><li><b>Мисливський еліксир</b> — збільшує досвід за перемоги над монстрами.</li><li><b>Еліксир удачі мисливця</b> — підвищує шанс випадіння здобичі з монстрів; не поширюється на скрині та спеціальні трофеї, що потребують певного ефекту.</li></ul><p>Ці еліксири діють <b>1 годину</b>. Кожен товар можна придбати <b>1 раз на добу</b>: відлік починається з покупки.</p>'
            : '<h2>Магазин Ордена</h2><p>Начиная с 500 очков доступны охотничьи эликсиры. Более высокие ступени открывают усиленные варианты. Цена, порог репутации и ограничения указаны на карточке товара.</p><ul><li><b>Эликсир Тихой тропы</b> — снижает вероятность того, что монстры нападут первыми. На боссов не действует.</li><li><b>Охотничий эликсир</b> — увеличивает опыт за победы над монстрами.</li><li><b>Эликсир удачи охотника</b> — повышает шанс выпадения добычи с монстров; не распространяется на сундуки и специальные трофеи, требующие определённого эффекта.</li></ul><p>Эти эликсиры действуют <b>1 час</b>. Каждый товар можно купить <b>1 раз в сутки</b>: отсчёт начинается с покупки.</p>';
        foreach ($shop->shopItems->where('share_structure_category_id', 4)->filter(static function ($offer): bool {
            $name = $offer->item?->getTranslation('name', 'ru') ?? '';

            return str_starts_with($name, 'Эликсир Тихой тропы')
                || str_starts_with($name, 'Охотничий эликсир')
                || str_starts_with($name, 'Эликсир удачи охотника');
        })->groupBy('min_reputation_points')->sortKeys() as $points => $offers) {
            $text .= '<h3>'.($uk ? 'Від ' : 'От ').$points.($uk ? ' очок репутації' : ' очков репутации').'</h3><div class="structures">';
            foreach ($offers->sortBy('sort_order') as $offer) {
                $text .= '[[item:'.$offer->share_item_id.']] ';
            }
            $text .= '</div>';
        }
        $text .= $uk
            ? '<p>Наведіть курсор на іконку, щоб переглянути опис еліксиру.</p><h2>Подвиг Легенди: «Тінь Прародителя»</h2><p>На щаблі <b>Легенда, від 5000 очок</b>, Вульфгар запропонує особливе випробування. Здобудьте кігті гірських грифонів, допоможіть приготувати Еліксир Чуття Прародителя, знайдіть приховані сліди та вистежте боса. Еліксир допоможе побачити те, що приховано від звичайного мисливця.</p><p>Деякі етапи потребують очікування: якщо єгер ще не готовий продовжити, завітайте пізніше. Точний час залишається таємницею.</p><p>Завершений подвиг дає особливу відзнаку й посилює бонуси еліксирів щабля «Легенда». Самого досягнення 5000 очок для посилення недостатньо — потрібно завершити подвиг.</p>'
            : '<p>Наведите курсор на иконку, чтобы посмотреть описание эликсира.</p><h2>Подвиг Легенды: «Тень Прародителя»</h2><p>На ступени <b>Легенда, от 5000 очков</b>, Вульфгар предложит особое испытание. Добудьте когти горных грифонов, помогите приготовить Эликсир Чутья Прародителя, найдите скрытые следы и выследите босса. Эликсир поможет увидеть то, что скрыто от обычного охотника.</p><p>Некоторые этапы требуют ожидания: если егерь ещё не готов продолжить, зайдите позже. Точное время остаётся тайной.</p><p>Завершённый подвиг даёт особую награду и усиливает бонусы эликсиров ступени «Легенда». Самого достижения 5000 очков для усиления недостаточно — нужно завершить подвиг.</p>';

        return $this->frameSections($text);
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
