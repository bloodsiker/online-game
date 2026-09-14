<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\LibraryArticle;
use App\Models\LibraryCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryCommissionShopSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->updateOrCreate(
                ['slug' => 'komissionnyi-magazin'],
                [
                    'parent_id' => null,
                    'name' => 'Комиссионный магазин',
                    'description' => 'Торговля между игроками через готовые аукционные лоты и заявки на бирже.',
                    'sort_order' => 70,
                    'is_active' => true,
                ],
            );

            $this->article(
                $category,
                'Аукцион',
                'komissionnyi-magazin-aukcion',
                'Покупка готовых лотов, выставление собственных предметов и получение выручки от продажи.',
                $this->auctionContent(),
                0,
            );
            $this->article(
                $category,
                'Биржа',
                'komissionnyi-magazin-birzha',
                'Создание заявок на покупку, продажа предметов по чужим заявкам и получение купленных товаров.',
                $this->exchangeContent(),
                10,
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

    private function auctionContent(): string
    {
        return <<<'HTML'
<p><b>Аукцион</b> в Комиссионном магазине предназначен для продажи готовых лотов между игроками. Продавец сам выбирает предмет, количество и общую цену, а покупатель сразу приобретает весь выставленный лот.</p>

<div class="library-info-block library-info-block--tip"><strong>Когда выбирать аукцион:</strong> если вы хотите назначить собственную цену и готовы подождать покупателя. Для быстрой продажи востребованных ресурсов также проверьте заявки на Бирже.</div>

<h2>Разделы аукциона</h2>
HTML
            .$this->frame('Навигация', <<<'HTML'
<table>
<thead><tr><th>Раздел</th><th>Назначение</th></tr></thead>
<tbody>
<tr><td><b>Купить товар</b></td><td>Просмотр и покупка активных лотов других игроков</td></tr>
<tr><td><b>Мои лоты</b></td><td>Список выставленных вами предметов и отмена продажи</td></tr>
<tr><td><b>Новый лот</b></td><td>Выбор предмета, количества и цены продажи</td></tr>
<tr><td><b>Выручка</b></td><td>Получение монет за уже проданные лоты</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<h2>Как выставить предмет</h2>
HTML
            .$this->frame('Создание лота', <<<'HTML'
<ol>
<li>Снимите предмет и оставьте его в рюкзаке.</li>
<li>Откройте вкладку <b>«Новый лот»</b> и выберите вещь.</li>
<li>Укажите количество и <b>общую цену всего лота</b>.</li>
<li>При необходимости включите анонимную продажу.</li>
<li>Проверьте рассчитанный налог и подтвердите выставление.</li>
</ol>
HTML)
            .<<<'HTML'

<p>На аукцион допускаются только предметы, для которых разрешена торговля между игроками. Надетые вещи в список не попадают. После создания лота выбранное количество временно исчезает из рюкзака и хранится в Комиссионном магазине до покупки или отмены.</p>

<div class="library-info-block library-info-block--warning"><strong>Налог за выставление:</strong> рассчитывается от указанной цены и списывается сразу при создании лота. При отмене продажи предмет вернётся в рюкзак, но уплаченный налог не возвращается.</div>

<h2>Покупка лота</h2>
<p>Во вкладке <b>«Купить товар»</b> выберите подходящее предложение и проверьте предмет, количество и цену. Покупается весь лот целиком — выбрать только часть количества нельзя.</p>

<ul>
<li>стоимость лота сразу списывается с покупателя;</li>
<li>предмет сразу переносится в рюкзак покупателя;</li>
<li>собственный лот купить нельзя;</li>
<li>если другой игрок уже приобрёл предложение, повторная покупка не состоится.</li>
</ul>

<h2>Получение выручки</h2>
<p>После покупки монеты не добавляются продавцу автоматически. Они появляются во вкладке <b>«Выручка»</b>, где можно посмотреть проданный предмет и забрать оплату.</p>

<div class="library-info-block library-info-block--important"><strong>Не забудьте забрать монеты:</strong> пока вы не нажали получение во вкладке «Выручка», сумма остаётся на хранении в Комиссионном магазине.</div>

<h2>Аукцион или Биржа</h2>
HTML
            .$this->frame('Разница способов торговли', <<<'HTML'
<table>
<thead><tr><th>Аукцион</th><th>Биржа</th></tr></thead>
<tbody>
<tr><td>Продавец предлагает конкретный лот</td><td>Покупатель публикует, какой товар ему нужен</td></tr>
<tr><td>Цена указывается за весь лот</td><td>Цена указывается за одну единицу</td></tr>
<tr><td>Покупатель забирает весь лот</td><td>Заявку можно выполнять частями</td></tr>
<tr><td>Продавец ждёт покупателя</td><td>Продавец сразу закрывает подходящую заявку</td></tr>
</tbody>
</table>
HTML);
    }

    private function exchangeContent(): string
    {
        return <<<'HTML'
<p><b>Биржа</b> работает через заявки на покупку. Покупатель заранее указывает нужный предмет, количество и цену за одну единицу, а продавцы передают ему товар полностью или частями.</p>

<div class="library-info-block library-info-block--tip"><strong>Когда выбирать Биржу:</strong> покупателю — когда нужен конкретный предмет; продавцу — когда хочется сразу продать востребованный ресурс по уже существующей заявке.</div>

<h2>Разделы Биржи</h2>
HTML
            .$this->frame('Навигация', <<<'HTML'
<table>
<thead><tr><th>Раздел</th><th>Назначение</th></tr></thead>
<tbody>
<tr><td><b>Продать</b></td><td>Просмотр чужих заявок и продажа подходящих предметов</td></tr>
<tr><td><b>Мои заявки</b></td><td>Активные заявки, оставшееся количество и их отмена</td></tr>
<tr><td><b>Новая заявка</b></td><td>Создание заявки на покупку нужного товара</td></tr>
<tr><td><b>Получить</b></td><td>Получение предметов, проданных вам через Биржу</td></tr>
</tbody>
</table>
HTML)
            .<<<'HTML'

<h2>Создание заявки на покупку</h2>
HTML
            .$this->frame('Порядок создания', <<<'HTML'
<ol>
<li>Откройте вкладку <b>«Новая заявка»</b>.</li>
<li>Выберите предмет, который хотите купить.</li>
<li>Укажите необходимое количество и цену за одну единицу.</li>
<li>При необходимости сделайте заявку анонимной.</li>
<li>Проверьте общую сумму и подтвердите создание.</li>
</ol>
HTML)
            .<<<'HTML'

<p>При создании заявки списывается <b>100 монет комиссии</b> и полная стоимость покупки: количество × цена за единицу. Стоимость товара помещается в эскроу — резерв, из которого автоматически оплачиваются продавцы.</p>

<div class="library-info-block library-info-block--important"><strong>Эскроу защищает сделку:</strong> продавец получит оплату за переданный товар даже если покупатель в этот момент не находится в игре.</div>

<h2>Отмена заявки</h2>
<p>Покупатель может отменить свою активную заявку во вкладке <b>«Мои заявки»</b>. Неиспользованная часть эскроу возвращается в монетах.</p>

<div class="library-info-block library-info-block--warning"><strong>Комиссия не возвращается:</strong> фиксированные 100 монет за создание заявки удерживаются даже при отмене. Если заявка уже частично выполнена, возвращаются только деньги за оставшееся количество.</div>

<h2>Продажа по чужой заявке</h2>
<p>Во вкладке <b>«Продать»</b> отображаются заявки других игроков. Фильтр «Подходящие» оставляет товары, которые есть у вас в рюкзаке. Дополнительно список можно фильтровать по названию, виду и количеству.</p>
HTML
            .$this->frame('Выполнение заявки', <<<'HTML'
<ol>
<li>Снимите продаваемый предмет и поместите его в рюкзак.</li>
<li>Найдите заявку и укажите количество для продажи.</li>
<li>Проверьте цену за единицу и подтвердите действие.</li>
<li>Товар перейдёт покупателю, а вы сразу получите оплату за вычетом налога.</li>
</ol>
HTML)
            .<<<'HTML'

<p>Заявку разрешено выполнять частично: количество продажи ограничивается остатком заявки и числом предметов в рюкзаке. После частичной сделки заявка остаётся активной, пока не будет закрыта полностью или отменена владельцем.</p>

<ul>
<li>нельзя выполнить собственную заявку;</li>
<li>продавцу нужны монеты для оплаты рассчитанного налога;</li>
<li>налог зависит от общей суммы конкретной продажи;</li>
<li>выплата продавцу равна стоимости проданного количества за вычетом налога.</li>
</ul>

<h2>Получение купленного товара</h2>
<p>Предметы по выполненным заявкам не попадают в рюкзак автоматически. Они сохраняются во вкладке <b>«Получить»</b>. Покупатель самостоятельно забирает каждую покупку, после чего она добавляется в рюкзак.</p>

<div class="library-info-block library-info-block--tip"><strong>Совет продавцу:</strong> сравнивайте цену за единицу на Бирже с ценами лотов на аукционе. Биржа даёт быструю продажу, но выгодное ожидание иногда приносит больше монет.</div>
HTML;
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }
}
