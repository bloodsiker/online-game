<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class LibraryArtifactAssemblySeeder extends Seeder
{
    public function run(): void
    {
        $category = LibraryCategory::query()->where('slug', 'artefakty')->firstOrFail();

        $existing = LibraryArticle::withTrashed()->where('slug', 'sborka-artefaktov')->first();
        if ($existing) {
            foreach ($existing->getTranslations('content') as $locale => $content) {
                $existing->setTranslation('content', $locale, $this->frameItems($content));
            }
            $existing->save();

            return;
        }

        $article = new LibraryArticle;
        $article->fill([
            'slug' => 'sborka-artefaktov',
            'category_id' => $category->id,
            'title' => ['ru' => 'Сборка артефактов', 'uk' => 'Збирання артефактів'],
            'excerpt' => [
                'ru' => 'Как собрать осколки и получить случайный артефакт в кузне.',
                'uk' => 'Як зібрати уламки й отримати випадковий артефакт у кузні.',
            ],
            'content' => [
                'ru' => $this->frameItems($this->content('ru')),
                'uk' => $this->frameItems($this->content('uk')),
            ],
            'status' => LibraryArticle::STATUS_PUBLISHED,
            'published_at' => now(),
            'sort_order' => 10,
        ]);
        foreach (['title', 'excerpt', 'content'] as $field) {
            if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
            }
        }
        $article->save();
    }

    private function frameItems(string $content): string
    {
        return preg_replace_callback('/<p>(\[\[item:\d+\]\](?:\s+\[\[item:\d+\]\])*)<\/p>/u', static function (array $match): string {
            return '<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody>'
                .'<tr height="22"><td width="20" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt"></td><td width="20" class="tbl-shp-sml rt"><b></b></td></tr>'
                .'<tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" align="center" style="padding:8px 10px"><div class="structures">'.$match[1].'</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr>'
                .'<tr height="18"><td width="20" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb">&nbsp;</td><td width="20" class="tbl-shp-sml rb"><b></b></td></tr>'
                .'</tbody></table>';
        }, $content) ?? $content;
    }

    private function content(string $locale): string
    {
        if ($locale === 'uk') {
            return <<<'HTML'
<p>Навіть уламок давньої реліквії зберігає частину її сили. Зберіть повний набір компонентів — і в кузні вони перетворяться на один із можливих артефактів.</p>
<h2>Де збирати артефакти</h2>
<div class="library-info-block library-info-block--tip">Відкрийте <b>кузню → «Крафт» → «Артефакти»</b>. Тут показано необхідні компоненти, їхню кількість у рюкзаку та список можливих результатів. Вивчати рецепт або витрачати книгу рецепта не потрібно.</div>
<h2>Перший набір уламків</h2>
<p>Для цієї збірки потрібні чотири різні компоненти — по одному кожного:</p>
<p>[[item:1836]] [[item:1837]] [[item:1838]] [[item:1839]]</p>
<p>Числа під компонентами показують, скільки у вас є та скільки потрібно на одну спробу. Поки не зібрано повний набір, кнопка «Зібрати» недоступна.</p>
<h2>Як відбувається збирання</h2>
<ol><li>Покладіть усі потрібні компоненти в рюкзак і прийдіть до кузні.</li><li>Виберіть розділ «Артефакти» та натисніть «Зібрати».</li><li>З рюкзака спишеться один повний набір компонентів, а готовий артефакт надійде до рюкзака.</li><li>Підсвічування можливих результатів поступово сповільниться й зупиниться на отриманому предметі.</li></ol>
<h2>Можливі артефакти цього набору</h2>
<p>[[item:924]] [[item:925]] [[item:926]] [[item:1835]] [[item:1840]] [[item:1841]]</p>
<div class="library-info-block library-info-block--important"><b>Результат випадковий:</b> ви отримуєте один предмет зі списку, а не всі одразу. Вибрати конкретний артефакт не можна; повторні збірки можуть дати той самий предмет. Швидкість анімації та момент натискання не впливають на результат.</div>
<p>Наведіть курсор на іконку, щоб переглянути характеристики, або натисніть на неї, щоб відкрити інформацію. Актуальні компоненти й можливі нагороди завжди перевіряйте безпосередньо в кузні: для інших наборів вони можуть відрізнятися.</p>
HTML;
        }

        return <<<'HTML'
<p>Даже осколок древней реликвии хранит частицу её силы. Соберите полный набор компонентов — и в кузне они превратятся в один из возможных артефактов.</p>
<h2>Где собирать артефакты</h2>
<div class="library-info-block library-info-block--tip">Откройте <b>кузню → «Крафт» → «Артефакты»</b>. Здесь показаны необходимые компоненты, их количество в рюкзаке и список возможных результатов. Изучать рецепт или расходовать книгу рецепта не нужно.</div>
<h2>Первый набор осколков</h2>
<p>Для этой сборки нужны четыре разных компонента — по одному каждого:</p>
<p>[[item:1836]] [[item:1837]] [[item:1838]] [[item:1839]]</p>
<p>Числа под компонентами показывают, сколько у вас есть и сколько требуется на одну попытку. Пока не собран полный набор, кнопка «Собрать» недоступна.</p>
<h2>Как происходит сборка</h2>
<ol><li>Положите все необходимые компоненты в рюкзак и придите в кузню.</li><li>Выберите раздел «Артефакты» и нажмите «Собрать».</li><li>Из рюкзака спишется один полный набор компонентов, а готовый артефакт поступит в рюкзак.</li><li>Подсветка возможных результатов постепенно замедлится и остановится на полученном предмете.</li></ol>
<h2>Возможные артефакты этого набора</h2>
<p>[[item:924]] [[item:925]] [[item:926]] [[item:1835]] [[item:1840]] [[item:1841]]</p>
<div class="library-info-block library-info-block--important"><b>Результат случайный:</b> вы получаете один предмет из списка, а не все сразу. Выбрать конкретный артефакт нельзя; повторные сборки могут дать тот же предмет. Скорость анимации и момент нажатия не влияют на результат.</div>
<p>Наведите курсор на иконку, чтобы посмотреть характеристики, или нажмите на неё, чтобы открыть информацию. Актуальные компоненты и возможные награды всегда проверяйте непосредственно в кузне: у других наборов они могут отличаться.</p>
HTML;
    }
}
