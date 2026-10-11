<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryFreeTradeSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->where('slug', 'professii')->firstOrFail();
            $overview = LibraryArticle::query()->where('slug', 'mirnye-navyki')->firstOrFail();

            // Repeated runs must not replace subsequent edits made in the article editor.
            LibraryArticle::query()->firstOrCreate(
                ['slug' => 'volnyy-torg'],
                [
                    'category_id' => $category->id,
                    'title' => 'Вольный торг',
                    'excerpt' => 'Лицензии торговцев, перевозка груза между городами и развитие торгового навыка.',
                    'content' => $this->content(),
                    'status' => LibraryArticle::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'sort_order' => 90,
                ],
            );

            if (! str_contains($overview->content, '/library/volnyy-torg')) {
                $overview->content .= "\n\n".$this->frame('Вольный торг', '<p><b>Вольный торг</b> — мирный навык перевозки товаров между торговцами. Покупайте местный груз и продавайте его в другом городе: за продажу начисляются монеты и опыт. С ростом навыка увеличиваются вместимость груза и прибыль, а также открываются новые товары.</p><p>Лицензия приобретается отдельно у каждого торговца и необходима как для покупки, так и для продажи. Подробнее: <a href="/library/volnyy-torg?category=professii"><b>Вольный торг</b></a> в разделе «Профессии».</p>');
                $overview->save();
            }
        });

        $this->command?->info('Статья «Вольный торг» опубликована; ссылка добавлена в «Мирные навыки».');
    }

    private function content(): string
    {
        $content = <<<'HTML'
<p><b>Вольный торг</b> — мирный навык для тех, кто предпочитает зарабатывать на дорогах между городами. Покупайте местные товары, перевозите их к другому торговцу и продавайте дороже. Успешные продажи приносят опыт, открывая более ценный груз и увеличивая прибыль.</p>
<div class="library-info-block library-info-block--tip"><strong>С чего начать:</strong> найдите торговца Вольного торга, приобретите его лицензию и откройте вкладку «Купить товар». Перед отправлением подготовьте монеты на лицензию торговца, которому собираетесь продать груз.</div>
HTML;
        $content .= $this->frame('Первый торговый поход', <<<'HTML'
<ol>
<li>Купите лицензию у торговца, с которым начинаете работать.</li>
<li>Выберите доступный по навыку товар во вкладке «Купить товар» и укажите количество.</li>
<li>Перевезите груз к другому торговцу, который принимает этот товар.</li>
<li>Приобретите его лицензию, если ещё не сделали этого.</li>
<li>Откройте «Продать товар» и продайте груз. За каждую проданную единицу получите деньги и опыт навыка.</li>
</ol>
<p>Во вкладке продажи показываются только подходящие товары, которые уже есть в вашем рюкзаке. Покупка товара и лицензии сами по себе не дают опыта.</p>
HTML);
        $content .= $this->frame('Лицензии торговцев', <<<'HTML'
<p>Лицензия даёт право торговать <b>с конкретным NPC</b>, а не пользоваться отдельным маршрутом. Она нужна и для покупки, и для продажи. Лицензия одного торговца не заменяет лицензию другого.</p>
<p>Лицензия приобретается один раз. Её стоимость не увеличивает прибыль: доход зависит от товара, цены скупки у получателя и вашего навыка.</p>
HTML);

        $posts = DB::table('trade_posts as posts')->join('npcs', 'npcs.id', '=', 'posts.npc_id')
            ->where('posts.is_active', true)->where('npcs.is_active', true)
            ->orderBy('posts.id')->get(['posts.id', 'posts.name', 'posts.license_price', 'npcs.name as npc_name', 'npcs.location_id']);
        $rows = $posts->map(fn ($post): string => '<tr><td>'.e($post->name).'</td><td>'.e($post->npc_name).'</td><td>'.(int) $post->location_id.'</td><td>'.number_format((int) $post->license_price, 0, '', ' ').' монет</td></tr>')->implode("\n");
        $content .= $this->frame('Торговцы', '<table><thead><tr><th>Территория</th><th>Торговец</th><th>Локация</th><th>Лицензия</th></tr></thead><tbody>'.$rows.'</tbody></table>');
        $content .= $this->frame('Развитие навыка и вместимость', <<<'HTML'
<p>Максимальный уровень навыка — <b>300</b>. Опыт начисляется при продаже привезённого товара другому торговцу. Его количество зависит от маршрута и числа проданных единиц; более дальние маршруты могут давать больше опыта и большую разницу цен.</p>
<p>Лимит груза общий для всех торговых товаров в рюкзаке. Начальная вместимость — <b>10 единиц</b>; каждые 15 уровней навыка добавляют ещё одну.</p>
<table><thead><tr><th>Навык</th><th>Вместимость груза</th></tr></thead><tbody>
<tr><td>1</td><td>10</td></tr><tr><td>50</td><td>13</td></tr><tr><td>100</td><td>16</td></tr><tr><td>150</td><td>20</td></tr><tr><td>200</td><td>23</td></tr><tr><td>250</td><td>26</td></tr><tr><td>300</td><td>30</td></tr>
</tbody></table>
<p>Рядом с надписью «Навык Вольный торг» у торговца показана шкала опыта до следующего уровня и текущая загрузка.</p>
HTML);
        $content .= $this->frame('Прибыль', <<<'HTML'
<p>Цена покупки фиксирована для товара. Каждый принимающий торговец имеет свою цену скупки. Перед дорогой сравните цену покупки и сумму, которую получите у другого NPC.</p>
<p>Навык увеличивает именно <b>прибыль — разницу между покупкой и продажей</b>, а не всю стоимость товара. На навыке 300 базовая прибыль утраивается. Итоговая цена за единицу округляется вниз до целых монет.</p>
<p>Например, если товар куплен за 40 монет, а базовая цена скупки — 42, на первом уровне прибыль составляет 2 монеты. На навыке 300 цена продажи такого товара составит 46 монет, а прибыль — 6 монет.</p>
HTML);
        $content .= '<h2>Товары и требования</h2><p>Новые группы товаров открываются на навыках <b>1, 50, 100, 150, 200 и 250</b>. Ассортимент зависит от торговца.</p>';
        $goods = DB::table('trade_goods')->where('is_active', true)->whereIn('trade_post_id', $posts->pluck('id'))
            ->orderBy('required_skill_lvl')->orderBy('trade_post_id')->orderBy('sort_order')->get();
        foreach ($goods->groupBy('required_skill_lvl') as $level => $tier) {
            $shortcodes = $tier->map(fn ($good): string => '[[item:'.(int) $good->share_item_id.']]')->implode(' ');
            $content .= $this->frame('Навык '.(int) $level, '<div class="structures">'.$shortcodes.'</div>');
        }
        $content .= $this->frame('Запасы и ограничения', <<<'HTML'
<p>Запас товара у торговца общий для игроков и постепенно восстанавливается. Кто первым купил оставшийся груз, тот и сможет его перевезти. Доступное количество всегда показано во вкладке покупки; параметры пополнения могут отличаться у разных товаров.</p>
<p>Торговый груз хранится в рюкзаке, в разделе «Вещи». Его нельзя продать в обычную скупку, выставить на аукцион, передать другому игроку, положить в клановое хранилище или выбросить. Продавайте его через Вольный торг.</p>
<p>Торговля недоступна погибшему персонажу и во время активного боя на текущей локации.</p>
HTML);

        return $content.'<div class="library-info-block library-info-block--important"><strong>Совет:</strong> планируйте дорогу туда и обратно. После продажи можно купить местный груз для обратного маршрута, если он доступен вашему навыку и принимается в городе назначения.</div>';
    }

    private function frame(string $title, string $content): string
    {
        $title = e($title);

        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody>
<tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr>
<tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px">{$content}</td><td class="tbl-shp-sides rs">&nbsp;</td></tr>
<tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr>
</tbody></table>
HTML;
    }
}
