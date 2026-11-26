<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LibrarySecretKnowledgeSeeder extends Seeder
{
    private const CATEGORY_SLUG = 'tainye-znaniya';

    private const ARTICLE_SLUG = 'tainye-znaniya-posobiya-o-bossah';

    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->where('slug', self::CATEGORY_SLUG)->first() ?? new LibraryCategory(['slug' => self::CATEGORY_SLUG]);
            $category->fill([
                'parent_id' => null,
                'name' => ['ru' => 'Тайные знания', 'uk' => 'Таємні знання'],
                'description' => [
                    'ru' => 'Ворохи записей, пособия о боссах и части боссов: как устроена механика Мудрого Финко.',
                    'uk' => 'Жмені записів, посібники про босів і частини босів: як працює механіка Мудрого Фінко.',
                ],
                'content_type' => 'articles',
                'sort_order' => 58,
                'is_active' => true,
            ]);
            if (Schema::hasColumn('library_categories', 'name_legacy')) {
                $category->setAttribute('name_legacy', 'Тайные знания');
            }
            $category->save();

            $article = LibraryArticle::withTrashed()->where('slug', self::ARTICLE_SLUG)->first() ?? new LibraryArticle(['slug' => self::ARTICLE_SLUG]);
            $article->fill([
                'category_id' => $category->id,
                'title' => ['ru' => 'Тайные знания и пособия о боссах', 'uk' => 'Таємні знання та посібники про босів'],
                'excerpt' => [
                    'ru' => 'Как получить ворох тайных знаний, отдать его Финко, изучить пособие о боссе и начать получать части боссов.',
                    'uk' => 'Як отримати жменю таємних знань, віддати її Фінку, вивчити посібник про боса та почати отримувати частини босів.',
                ],
                'content' => ['ru' => $this->content('ru'), 'uk' => $this->content('uk')],
                'status' => LibraryArticle::STATUS_PUBLISHED,
                'published_at' => $article->published_at ?? now(),
                'sort_order' => 0,
                'deleted_at' => null,
            ]);
            foreach (['title', 'excerpt', 'content'] as $field) {
                if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                    $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
                }
            }
            $article->save();

            $this->command?->info('Тайные знания: раздел #'.$category->id.', статья #'.$article->id);
        });
    }

    private function content(string $locale): string
    {
        return $locale === 'uk' ? $this->uk() : $this->ru();
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }

    /** @return array<int, array{boss: array{ru: string, uk: string}, need: int, price: string, manual: int, part: int}> */
    private function rows(): array
    {
        return [
            ['boss' => ['ru' => 'Крысиный вожак (6 ур.)', 'uk' => 'Щурячий ватажок (6 рів.)'], 'need' => 3, 'price' => '15 000', 'manual' => 2370, 'part' => 2371],
            ['boss' => ['ru' => 'Чрево Кургана (32 ур.)', 'uk' => 'Черево Кургану (32 рів.)'], 'need' => 7, 'price' => '35 000', 'manual' => 2372, 'part' => 2373],
            ['boss' => ['ru' => 'Костяной Скорпион (36 ур.)', 'uk' => 'Кістяний Скорпіон (36 рів.)'], 'need' => 12, 'price' => '60 000', 'manual' => 2374, 'part' => 2375],
            ['boss' => ['ru' => 'Азазель (45 ур.)', 'uk' => 'Азазель (45 рів.)'], 'need' => 18, 'price' => '90 000', 'manual' => 2376, 'part' => 2377],
            ['boss' => ['ru' => 'Кровавый Хранитель Склепа (55 ур.)', 'uk' => 'Кривавий Хранитель Склепу (55 рів.)'], 'need' => 25, 'price' => '125 000', 'manual' => 2378, 'part' => 2379],
        ];
    }

    private function table(string $locale): string
    {
        $uk = $locale === 'uk';
        $head = $uk
            ? ['Бос', 'Потрібно знань', 'Посібник', 'Ціна', 'Частина боса']
            : ['Босс', 'Нужно знаний', 'Пособие', 'Цена', 'Часть босса'];
        $out = '<table><thead><tr><th>'.implode('</th><th>', $head).'</th></tr></thead><tbody>';
        foreach ($this->rows() as $row) {
            $out .= '<tr><td><b>'.$row['boss'][$uk ? 'uk' : 'ru'].'</b></td><td>'.$row['need'].'</td><td>[[item:'.$row['manual'].']]</td><td>'.$row['price'].'</td><td>[[item:'.$row['part'].']]</td></tr>';
        }

        return $out.'</tbody></table>';
    }

    private function ru(): string
    {
        return <<<'HTML'
<p><b>Тайные знания</b> — механика, которая открывает путь к особым трофеям с боссов. Её хранитель — [[npc:2;display:inline]] в Святилище Древнего Алтаря. Вы собираете ворохи записей, отдаёте их Финко, накапливаете знания и учите пособия о боссах. Выученное пособие приносит часть босса с каждого его убийства.</p>

<div class="library-info-block library-info-block--tip"><strong>Коротко:</strong> ворох → Финко → тайные знания → пособие о боссе → часть босса после каждой победы.</div>

HTML
        .$this->frame('Ворох тайных знаний', <<<'HTML'
<p>[[item:2368]]</p>
<p><b>Ворох тайных знаний</b> — редкая находка. Он выпадает с обычных монстров от 10 уровня; чем выше уровень монстра, тем шанс больше. Ворох можно купить у Финко за 7 800 монет.</p>
HTML)
        .<<<'HTML'

<h2>Изучение вороха у Финко</h2>
HTML
        .$this->frame('Как изучать ворох', <<<'HTML'
<p>Придите к Финко и откройте раздел <b>«Тайные знания»</b>. Выберите <b>«Передать ворох Финко»</b>: Финко начинает изучать записи. За один раз изучается <b>один ворох</b>.</p>
<ul>
<li>Изучение длится <b>1 минуту</b>. В это время на экране появляются старинные рукописи на разных языках.</li>
<li>Попытку можно прервать — ворох в этом случае не расходуется.</li>
<li>Иногда в записях находится тайное знание. При успехе ваши тайные знания растут на 1, а об удаче сообщит личное сообщение в чате.</li>
<li>При неудаче ворох пропадает: ничего ценного в записях не нашлось.</li>
</ul>
<p>Уровень тайных знаний и число ворохов в рюкзаке видны наверху страницы Финко. Знания не сгорают и принадлежат только вам.</p>
HTML)
        .<<<'HTML'

<div class="library-info-block library-info-block--important"><strong>Важно:</strong> знания не тратятся при изучении пособий — накопленный уровень открывает всё новые пособия.</div>

<h2>Пособия о боссах</h2>
<p>Пособия продаются в магазине Финко от дешёвых к дорогим. Купленное пособие нужно <b>изучить</b>: для этого должно хватать тайных знаний. Пособие изучается один раз: при изучении оно расходуется.</p>

HTML
        .$this->frame('Пособия о боссах', $this->table('ru'))
        .<<<'HTML'


<h2>Части боссов</h2>
HTML
        .$this->frame('Что получает игрок', <<<'HTML'
<p>После изучения пособия <b>с каждого убитого вами босса</b> вы получаете его часть — гарантированно, шанс выпадения 100%. Часть приходит прямо в рюкзак, а в чат придёт личное сообщение.</p>
<ul>
<li><b>Один игрок.</b> Если босса убил один игрок и нанёс больше всех урона, часть получает он, если пособие изучено.</li>
<li><b>Группа.</b> Если по урону лидирует группа, часть получают те её участники, кто изучил пособие и сам бил этого босса в этом бою.</li>
<li>За один бой с одного босса часть выдаётся не более одного раза на игрока.</li>
</ul>
HTML)
        .<<<'HTML'

<div class="library-info-block library-info-block--warning"><strong>Внимание:</strong> участник группы, который ушёл в другую локацию или не бил босса, части не получает.</div>

<div class="library-info-block library-info-block--tip"><strong>Совет:</strong> начинайте с пособия о Крысином вожаке — ему нужно всего 3 знания. Копите ворохи: изучать их можно в любое время, срока годности у них нет.</div>
HTML;
    }

    private function uk(): string
    {
        return <<<'HTML'
<p><b>Таємні знання</b> — механіка, що відкриває шлях до особливих трофеїв із босів. Її хранитель — [[npc:2;display:inline]] у Святилищі Прадавнього Вівтаря. Ви збираєте жмені записів, віддаєте їх Фінку, накопичуєте знання та вчите посібники про босів. Вивчений посібник приносить частину боса з кожного його вбивства.</p>

<div class="library-info-block library-info-block--tip"><strong>Коротко:</strong> жменя → Фінко → таємні знання → посібник про боса → частина боса після кожної перемоги.</div>

HTML
        .$this->frame('Жменя таємних знань', <<<'HTML'
<p>[[item:2368]]</p>
<p><b>Жменя таємних знань</b> — рідкісна знахідка. Вона випадає зі звичайних монстрів від 10 рівня; чим вищий рівень монстра, тим більший шанс. Жменю можна купити в Фінка за 7 800 монет.</p>
HTML)
        .<<<'HTML'

<h2>Вивчення жмені в Фінка</h2>
HTML
        .$this->frame('Як вивчати жменю', <<<'HTML'
<p>Прийдіть до Фінка та відкрийте розділ <b>«Таємні знання»</b>. Оберіть <b>«Передати жменю Фінку»</b>: Фінко починає вивчати записи. За один раз вивчається <b>одна жменя</b>.</p>
<ul>
<li>Вивчення триває <b>1 хвилину</b>. Весь цей час на екрані з’являються старовинні рукописи різними мовами.</li>
<li>Спробу можна перервати — жменя в цьому разі не витрачається.</li>
<li>Іноді в записах знаходиться таємне знання. У разі успіху ваші таємні знання зростають на 1, а про вдачу повідомить особисте повідомлення в чаті.</li>
<li>У разі невдачі жменя зникає: нічого цінного в записах не знайшлося.</li>
</ul>
<p>Рівень таємних знань і кількість жмень у наплічнику видно вгорі сторінки Фінка. Знання не згорають і належать лише вам.</p>
HTML)
        .<<<'HTML'

<div class="library-info-block library-info-block--important"><strong>Важливо:</strong> знання не витрачаються при вивченні посібників — накопичений рівень відкриває дедалі нові посібники.</div>

<h2>Посібники про босів</h2>
<p>Посібники продаються в магазині Фінка від дешевих до дорогих. Куплений посібник потрібно <b>вивчити</b>: для цього має вистачати таємних знань. Посібник вивчається один раз: під час вивчення він витрачається.</p>

HTML
        .$this->frame('Посібники про босів', $this->table('uk'))
        .<<<'HTML'


<h2>Частини босів</h2>
HTML
        .$this->frame('Що отримує гравець', <<<'HTML'
<p>Після вивчення посібника <b>з кожного вбитого вами боса</b> ви отримуєте його частину — гарантовано, шанс випадіння 100%. Частина надходить прямо в наплічник, а в чат прийде особисте повідомлення.</p>
<ul>
<li><b>Один гравець.</b> Якщо боса вбив один гравець і завдав найбільше шкоди, частину отримує він, якщо посібник вивчено.</li>
<li><b>Група.</b> Якщо за шкодою лідирує група, частину отримують ті її учасники, хто вивчив посібник і сам бив цього боса в цьому бою.</li>
<li>За один бій з одного боса частина видається не більше одного разу на гравця.</li>
</ul>
HTML)
        .<<<'HTML'

<div class="library-info-block library-info-block--warning"><strong>Увага:</strong> учасник групи, який пішов в іншу локацію або не бив боса, частини не отримує.</div>

<div class="library-info-block library-info-block--tip"><strong>Порада:</strong> починайте з посібника про Щурячого ватажка — йому потрібно лише 3 знання. Копіть жмені: вивчати їх можна будь-коли, терміну придатності в них немає.</div>
HTML;
    }
}
