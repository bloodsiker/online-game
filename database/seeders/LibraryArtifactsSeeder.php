<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LibraryArticle;
use App\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryArtifactsSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->updateOrCreate(
                ['slug' => 'artefakty'],
                [
                    'parent_id' => null,
                    'name' => 'Артефакты',
                    'description' => 'Особые предметы, талисманы, их характеристики, экипировка и использование.',
                    'sort_order' => 90,
                    'is_active' => true,
                ],
            );

            LibraryArticle::withTrashed()->updateOrCreate(
                ['slug' => 'artefakty-i-talismany'],
                [
                    'category_id' => $category->id,
                    'author_id' => null,
                    'title' => 'Артефакты и талисманы',
                    'excerpt' => 'Какими бывают артефакты, как их экипировать и какие бонусы они дают персонажу.',
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
<p><b>Артефакты</b> — особые предметы, которые хранятся в отдельной вкладке рюкзака. Одни из них можно носить как постоянные талисманы и получать прибавку к характеристикам, другие предназначены для использования и имеют ограниченное количество зарядов.</p>

<div class="library-info-block library-info-block--tip"><strong>Где найти:</strong> откройте рюкзак и выберите вкладку «Артефакты». Наведите курсор на иконку предмета, чтобы увидеть полный тултип, требования и свойства.</div>

<h2>Постоянные талисманы</h2>
HTML
            .$this->frame('Как экипировать талисман', <<<'HTML'
<ol>
<li>Откройте раздел «Артефакты» в рюкзаке.</li>
<li>Нажмите на нужный талисман и выберите действие «Надеть».</li>
<li>Экипированный артефакт появится под манекеном персонажа.</li>
<li>Все его характеристики начнут учитываться сразу после экипировки.</li>
</ol>
HTML)
            .<<<'HTML'

<p>Артефакт не занимает ячейку оружия или брони. Можно одновременно носить несколько разных талисманов, однако два экземпляра одного и того же артефакта экипировать нельзя.</p>

<div class="library-info-block library-info-block--important"><strong>Требования:</strong> если для талисмана задан уровень персонажа, характеристика или навык, надеть его можно только после выполнения этого условия.</div>

<h2>Характеристики</h2>
HTML
            .$this->frame('Какие бонусы бывают', <<<'HTML'
<p>Талисманы могут усиливать основные и боевые параметры: выносливость, ловкость, интуицию, мудрость, интеллект, здоровье, броню, магическую атаку и сопротивление, обычный или магический крит, уворот, блок и силу критического удара.</p>
<p>Бонус бывает фиксированным или процентным. Все свойства одновременно входят в итоговые характеристики персонажа, пока артефакт экипирован.</p>
HTML)
            .<<<'HTML'

<h2>Расходуемые артефакты</h2>
<p>Если у артефакта указано количество использований, он работает как расходуемый предмет и не экипируется в список талисманов. После успешного применения списывается один заряд. Когда заканчивается последний заряд, предмет исчезает; у сложенных в стопку предметов начинает расходоваться следующий экземпляр.</p>

<div class="library-info-block library-info-block--warning"><strong>Важно:</strong> заряд не тратится, если предмет нельзя применить в текущей ситуации. Доступное действие и точный эффект всегда указаны в тултипе.</div>

<h2>Каталог артефактов</h2>
<p>Ниже показаны все активные артефакты игры. Каталог автоматически обновляется при добавлении или изменении предметов в админке. Нажатие на иконку открывает подробную информацию в отдельном окне.</p>

[[artifact_catalog]]
HTML;
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }
}
