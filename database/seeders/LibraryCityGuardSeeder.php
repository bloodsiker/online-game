<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Reputation\Domain\Services\CityGuardPerksService;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * Статья Библиотеки «Городская стража»: обновляет существующую (по slug или названию)
 * или создаёт новую в разделе «Репутации». Перед перезаписью сохраняет копию старого текста.
 * Запускать после CityGuardReputationSeeder.
 */
final class LibraryCityGuardSeeder extends Seeder
{
    private const SLUG = 'gorodskaya-strazha';

    /** min_points => [ru зоны, uk зоны, уровни] */
    private const ZONES = [
        0 => ['Канализация, опушка Шепчущего леса', 'Каналізація, узлісся Шепітливого лісу', '1–7'],
        500 => ['Шепчущий лес, Врата Опустевшей Долины', 'Шепітливий ліс, Брама Спорожнілої Долини', '7–14'],
        1000 => ['Опустевшая Долина, Заросли Кривых Игл', 'Спорожніла Долина, Зарості Кривих Голок', '13–20'],
        2000 => ['Забытый Курган, Дозорный пост', 'Забутий Курган, Дозорний пост', '19–24'],
        3000 => ['Овраг Багровой Пыли, глубины Кургана, Заросшая дорога', 'Яр Багряного Пилу, глибини Кургану, Заросла дорога', '24–31'],
        4000 => ['Овраг Гулкой Пустоты, Сторожевые Холмы', 'Яр Гулкої Порожнечі, Сторожові Пагорби', '32–36'],
        5000 => ['Сторожевые Холмы, Ожившая окраина, Пепельные Предгорья', 'Сторожові Пагорби, Ожила околиця, Попелясті Передгір\'я', '36–42'],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::where('slug', 'reputatsii')->firstOrFail();
            $reputation = Reputation::with(['npc', 'tiers'])->findOrFail(CityGuardPerksService::REPUTATION_ID);
            $shop = Structure::where('reputation_id', CityGuardPerksService::REPUTATION_ID)
                ->where('type', Structure::TYPE_SHOP)->with('shopItems.item')->firstOrFail();

            $article = LibraryArticle::withTrashed()->where('slug', self::SLUG)->lockForUpdate()->first()
                ?? LibraryArticle::withTrashed()->where('title->ru', 'Городская стража')->lockForUpdate()->first();

            if ($article !== null) {
                $backup = 'backups/library/city-guard-before-update-'.now()->format('Ymd-His-u').'.json';
                Storage::disk('local')->put($backup, json_encode([
                    'article_id' => $article->id,
                    'title' => $article->getTranslations('title'),
                    'excerpt' => $article->getTranslations('excerpt'),
                    'content' => $article->getTranslations('content'),
                ], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
                if ($article->trashed()) {
                    $article->restore();
                }
            } else {
                $article = new LibraryArticle([
                    'category_id' => $category->id,
                    'slug' => self::SLUG,
                    'status' => LibraryArticle::STATUS_PUBLISHED,
                    'published_at' => now(),
                    'sort_order' => 5,
                ]);
            }

            $article->fill([
                'title' => ['ru' => 'Городская стража', 'uk' => 'Міська варта'],
                'excerpt' => [
                    'ru' => 'Задания капитана Ольгерда, ранги стражи, скидка у торговцев Нейрина, «Благословение стражи» и Интендантская: плащи, флаконы, гримуары и Руна переноса.',
                    'uk' => 'Завдання капітана Ольгерда, ранги варти, знижка в торговців Нейрина, «Благословення варти» та Інтендантська: плащі, флакони, гримуари й Руна перенесення.',
                ],
                'content' => ['ru' => $this->content('ru', $reputation, $shop), 'uk' => $this->content('uk', $reputation, $shop)],
            ]);
            foreach (['title', 'excerpt', 'content'] as $field) {
                if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                    $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
                }
            }
            $article->save();

            $this->command?->info('Статья «Городская стража»: #'.$article->id);
        });
    }

    private function content(string $locale, Reputation $reputation, Structure $shop): string
    {
        $uk = $locale === 'uk';
        $perks = new CityGuardPerksService;

        $text = $uk
            ? '<h2>Служба у варті</h2><p>Нейрин росте швидше, ніж варта встигає набирати новобранців. Капітан Ольгерд доручає охочим зачищати околиці міста й приносити трофеї, а тим, хто довів вірність, відкриває Інтендантську варти.</p><p>Капітан чекає на локації <b>«Казарменний двір», №'.$reputation->npc?->location_id.'</b>. У його діалозі доступні завдання варти та Інтендантська.</p>'
            : '<h2>Служба в страже</h2><p>Нейрин растёт быстрее, чем стража успевает набирать новобранцев. Капитан Ольгерд поручает желающим зачищать окрестности города и приносить трофеи, а тем, кто доказал верность, открывает Интендантскую стражи.</p><p>Капитан ждёт на локации <b>«Казарменный двор», №'.$reputation->npc?->location_id.'</b>. В его диалоге доступны задания стражи и Интендантская.</p>';
        $text .= '<p>[[npc:'.$reputation->npc_id.';display:inline]]</p><p>[[reputation:'.$reputation->id.';display:inline]]</p>';

        $text .= $uk
            ? '<h2>Як здобувати репутацію</h2><ol><li>Візьміть завдання в Ольгерда: воно випадково обирається зі списку вашого поточного рангу.</li><li>Виконайте цілі: перемагайте вказаних монстрів або здобувайте трофеї. Трофеї (щурячі хвости, жетони банди, ікла гієн, пір\'я, роги, шкури тощо) випадають <b>лише з потрібних монстрів і лише поки завдання взяте</b>.</li><li>Поверніться до капітана й здайте завдання — отримаєте репутацію, досвід і монети.</li></ol><p><b>Одночасно можна виконувати одне завдання варти. Наступне доступне через 2 дні після здачі попереднього.</b></p>'
            : '<h2>Как получать репутацию</h2><ol><li>Возьмите задание у Ольгерда: оно случайно выбирается из списка вашего текущего ранга.</li><li>Выполните цели: побеждайте указанных монстров или добывайте трофеи. Трофеи (крысиные хвосты, жетоны банды, клыки гиен, перья, рога, шкуры и др.) выпадают <b>только с нужных монстров и только пока задание взято</b>.</li><li>Вернитесь к капитану и сдайте задание — получите репутацию, опыт и монеты.</li></ol><p><b>Одновременно можно выполнять одно задание стражи. Следующее доступно через 2 дня после сдачи предыдущего.</b></p>';

        $text .= '<h2>'.($uk ? 'Ранги та бонуси' : 'Ранги и бонусы').'</h2>'
            .'<table class="coll brd2-all p10h p4v" width="100%" cellspacing="0" cellpadding="0"><thead><tr class="bg_l">'
            .'<th>'.($uk ? 'Ранг' : 'Ранг').'</th><th nowrap>'.($uk ? 'Репутація від' : 'Репутация от').'</th>'
            .'<th>'.($uk ? 'Де служити (рівні)' : 'Где служить (уровни)').'</th>'
            .'<th nowrap>'.($uk ? 'Знижка' : 'Скидка').'</th><th nowrap>'.($uk ? 'Благословення' : 'Благословение').'</th></tr></thead><tbody>';
        foreach ($reputation->tiers->sortBy('min_points') as $tier) {
            $points = (int) $tier->min_points;
            $name = $tier->getTranslation('medal_name', $locale) ?: ($uk ? 'Без звання' : 'Без звания');
            [$discount, $blessing] = $perks->perksForPoints($points);
            $zone = self::ZONES[$points] ?? null;
            $text .= '<tr><td nowrap>'.e($name).'</td><td align="center">'.$points.'</td>'
                .'<td>'.($zone !== null ? e($zone[$uk ? 1 : 0]).' ('.$zone[2].')' : '—').'</td>'
                .'<td align="center" nowrap>'.($discount > 0 ? '−'.$discount.'%' : '—').'</td>'
                .'<td align="center" nowrap>'.($blessing > 0 ? '±'.$blessing.'%' : '—').'</td></tr>';
        }
        $text .= '</tbody></table>';

        $text .= $uk
            ? '<h2>Знижка в торговців Нейрина</h2><p>З ранга «Новобранець» звичайні крамниці Нейрина продають вам товари дешевше — від 2% до 12% залежно від рангу. Знижка діє лише на ціну в монетах. Не поширюється на крамниці репутацій (Ордену Мисливців, Вічності, Шукачів тощо), бартер, преміум-товари та крамниці за межами міста.</p><p>У магазині нова ціна показана поруч із закресленою старою, а під кошиком — розмір вашої знижки.</p>'
            : '<h2>Скидка у торговцев Нейрина</h2><p>С ранга «Новобранец» обычные лавки Нейрина продают вам товары дешевле — от 2% до 12% в зависимости от ранга. Скидка действует только на цену в монетах. Не распространяется на лавки репутаций (Ордена Охотников, Вечности, Искателей и др.), бартер, премиум-товары и лавки за пределами города.</p><p>В магазине новая цена показана рядом с зачёркнутой старой, а под корзиной — размер вашей скидки.</p>';

        $text .= $uk
            ? '<h2>Благословення варти</h2><p>Постійний бонус у боях із <b>будь-якими монстрами</b>, включно з босами: ваші удари завдають більше шкоди, а удари монстрів — менше, на однаковий відсоток (від 2% до 8%). Нічого вмикати не потрібно — бонус діє, щойно ви досягли рангу.</p>'
            : '<h2>Благословение стражи</h2><p>Постоянный бонус в боях с <b>любыми монстрами</b>, включая боссов: ваши удары наносят больше урона, а удары монстров — меньше, на одинаковый процент (от 2% до 8%). Ничего включать не нужно — бонус действует, как только вы достигли ранга.</p>';

        $text .= $uk
            ? '<h2>Інтендантська варти</h2><p>Товари відкриваються з рангами. Їх не можна передати іншим гравцям чи виставити на аукціон.</p><ul>'
                .'<li><b>Гербові плащі</b> вдягаються в окремий слот плаща (на ляльці героя під кольчугою) — це додаткова броня й здоров\'я поверх звичайного спорядження.</li>'
                .'<li><b>Великі флакони лікування</b> відновлюють 30%, 40% або 50% здоров\'я.</li>'
                .'<li><b>Гримуари</b> вивчаються в Наставника магії: пасивка «Витривалість вартового» (+3/5/8% до максимуму здоров\'я) і бафф «Пильність дозорця» (+10/15/20% до ухилення та криту). Новий ступінь замінює попередній.</li>'
                .'<li><b>Руна перенесення</b> — див. нижче.</li></ul>'
            : '<h2>Интендантская стражи</h2><p>Товары открываются с рангами. Их нельзя передать другим игрокам или выставить на аукцион.</p><ul>'
                .'<li><b>Гербовые плащи</b> надеваются в отдельный слот плаща (на кукле героя под кольчугой) — это дополнительная броня и здоровье поверх обычного снаряжения.</li>'
                .'<li><b>Большие флаконы лечения</b> восстанавливают 30%, 40% или 50% здоровья.</li>'
                .'<li><b>Гримуары</b> изучаются у Наставника магии: пассивка «Выносливость стража» (+3/5/8% к максимуму здоровья) и бафф «Бдительность дозорного» (+10/15/20% к увороту и криту). Новая ступень заменяет предыдущую.</li>'
                .'<li><b>Руна переноса</b> — см. ниже.</li></ul>';

        // Таблица «репутация → предметы»: ранг и порог слева, иконки товаров справа.
        $tierNames = $reputation->tiers->mapWithKeys(fn ($tier): array => [
            (int) $tier->min_points => $tier->getTranslation('medal_name', $locale) ?: '',
        ]);
        $text .= '<table class="coll brd2-all p10h p4v" width="100%" cellspacing="0" cellpadding="0"><thead><tr class="bg_l">'
            .'<th nowrap width="1%">'.($uk ? 'Репутація' : 'Репутация').'</th><th>'.($uk ? 'Предмети' : 'Предметы').'</th></tr></thead><tbody>';
        foreach ($shop->shopItems->groupBy('min_reputation_points')->sortKeys() as $points => $offers) {
            $rank = (string) ($tierNames[(int) $points] ?? '');
            $text .= '<tr><td align="center" nowrap><b>'.$points.'</b>'.($rank !== '' ? '<br>'.e($rank) : '').'</td><td><div class="structures">';
            foreach ($offers->sortBy('sort_order') as $offer) {
                $text .= '[[item:'.$offer->share_item_id.']] ';
            }
            $text .= '</div></td></tr>';
        }
        $text .= '</tbody></table>';
        $text .= '<p>'.($uk ? 'Наведіть курсор на іконку, щоб переглянути опис і ціну.' : 'Наведите курсор на иконку, чтобы посмотреть описание и цену.').'</p>';

        $text .= $uk
            ? '<h2>Руна перенесення</h2><p>З 2000 очок в Інтендантській можна придбати <b>Руну перенесення</b>. Вона не витрачається й переносить вас до <b>Міського серця</b> з будь-якого місця, крім бою, підземель і Темниці.</p><ul><li><b>Руна перенесення</b> — 1 раз на добу.</li><li><b>Посилена руна перенесення</b> — 2 рази на добу.</li><li><b>Досконала руна перенесення</b> — 3 рази на добу.</li></ul><p>Щоб посилити руну, купіть в Інтендантській сувій посилення (з 3000 та 5000 очок) і віднесіть його ковалю разом із руною (вкладка «Апгрейд»). Посилення завжди успішне. Руну й кожен сувій можна купити лише один раз.</p>'
            : '<h2>Руна переноса</h2><p>С 2000 очков в Интендантской можно купить <b>Руну переноса</b>. Она не расходуется и переносит вас в <b>Городское сердце</b> из любого места, кроме боя, подземелий и Темницы.</p><ul><li><b>Руна переноса</b> — 1 раз в сутки.</li><li><b>Усиленная руна переноса</b> — 2 раза в сутки.</li><li><b>Совершенная руна переноса</b> — 3 раза в сутки.</li></ul><p>Чтобы усилить руну, купите в Интендантской свиток усиления (с 3000 и 5000 очков) и отнесите его кузнецу вместе с руной (вкладка «Апгрейд»). Усиление всегда успешно. Руну и каждый свиток можно купить только один раз.</p>';

        return $this->frameSections($text);
    }

    private function frameSections(string $html): string
    {
        $parts = preg_split('~<h2>(.*?)</h2>~su', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = array_shift($parts);
        for ($index = 0; $index < count($parts); $index += 2) {
            $result .= $this->frame($parts[$index], $parts[$index + 1]);
        }

        return $result;
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
