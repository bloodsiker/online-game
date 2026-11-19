<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class LibraryEternityReputationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            if (LibraryArticle::withTrashed()->where('slug', 'vechnost')->exists()) {
                $this->command?->info('Статья «Вечность» уже существует; правки сохранены.');

                return;
            }
            $category = LibraryCategory::where('slug', 'reputatsii')->firstOrFail();
            $reputation = Reputation::with('npc')->where('name->ru', 'Вечность')->firstOrFail();
            $eternum = ShareItem::where('name->ru', 'Eternum')->firstOrFail();
            $shop = Structure::where('reputation_id', $reputation->id)->where('type', Structure::TYPE_SHOP)
                ->with('shopItems.requirements')->firstOrFail();
            $mechanisms = DB::table('share_item_effects')->where('effect_type', 'produce_item')
                ->where('target_share_item_id', $eternum->id)->orderBy('value')->get();
            $article = new LibraryArticle([
                'category_id' => $category->id,
                'slug' => 'vechnost',
                'title' => ['ru' => 'Вечность: Eternum и хронолиты', 'uk' => 'Вічність: Eternum і хронолити'],
                'excerpt' => [
                    'ru' => 'Как получать Eternum, улучшать хронолиты и развивать репутацию «Вечность». Сумки и пояса Эйдрана: требования и цены.',
                    'uk' => 'Як отримувати Eternum, покращувати хронолити та розвивати репутацію «Вічність». Сумки й пояси Ейдрана: вимоги та ціни.',
                ],
                'content' => [
                    'ru' => $this->content('ru', $reputation, $eternum, $shop, $mechanisms),
                    'uk' => $this->content('uk', $reputation, $eternum, $shop, $mechanisms),
                ],
                'status' => LibraryArticle::STATUS_PUBLISHED, 'published_at' => now(),
                'sort_order' => (int) $category->articles()->max('sort_order') + 10,
            ]);
            foreach (['title', 'excerpt', 'content'] as $field) {
                if (Schema::hasColumn('library_articles', $field.'_legacy')) {
                    $article->setAttribute($field.'_legacy', $article->getTranslation($field, 'ru'));
                }
            }
            $article->save();
            $this->command?->info('Опубликована статья о репутации «Вечность»: #'.$article->id);
        });
    }

    /** @param Collection<int, object> $mechanisms */
    private function content(string $locale, Reputation $reputation, ShareItem $eternum, Structure $shop, Collection $mechanisms): string
    {
        $uk = $locale === 'uk';
        $intro = $uk
            ? '<p>У кристалах <b>Eternum</b> застигло саме часове полотно світу. Ейдран веде літопис тих, хто вміє збирати цю рідкісну силу й зміцнювати зв’язок із Вічністю.</p><p>Репутація <b>не має верхньої межі та медалей</b>. Очки відкривають товари в крамниці Ейдрана; це не ланцюжок звичайних контрактів на полювання.</p>'
            : '<p>В кристаллах <b>Eternum</b> застыло само время мира. Эйдран ведёт летопись тех, кто умеет собирать эту редкую силу и укреплять связь с Вечностью.</p><p>Репутация <b>не имеет верхнего предела и медалей</b>. Очки открывают товары в магазине Эйдрана; это не цепочка обычных охотничьих контрактов.</p>';
        $intro .= '<p>[[reputation:'.$reputation->id.';display:inline]]</p><p>[[npc:'.$reputation->npc_id.';display:inline]]</p>';
        $sources = $uk
            ? '<p>Eternum можна отримати двома способами:</p><ul><li><b>Використовувати хронолит</b> із рюкзака: він створює кристали та залишається у вас. Вищий рівень хронолита дає більше Eternum за одне використання.</li><li><b>Перемагати босів</b>: Eternum може випасти як рідкісна здобич. Випадіння не гарантоване; актуальний шанс дивіться в інформації про конкретного боса.</li></ul><p>Кристали не можна передавати іншим гравцям. Отримання Eternum у рюкзак ще не нараховує репутацію.</p>'
            : '<p>Получить Eternum можно двумя способами:</p><ul><li><b>Использовать хронолит</b> из рюкзака: он создаёт кристаллы и остаётся у вас. Чем выше ступень хронолита, тем больше Eternum за одно использование.</li><li><b>Побеждать боссов</b>: Eternum может выпасть как редкая добыча. Выпадение не гарантировано; актуальный шанс смотрите в информации о конкретном боссе.</li></ul><p>Кристаллы нельзя передавать другим игрокам. Получение Eternum в рюкзак ещё не начисляет репутацию.</p>';
        $sources .= '<div class="structures">[[item:'.$eternum->id.']]</div>';
        $chronoliths = $uk
            ? '<p>Перший хронолит і креслення наступних ступенів продає <b>Хальдор</b> у крамниці Шукачів реліктів. Для покупки потрібні відповідна репутація Шукачів, монети та релікти.</p><p>Використання хронолита обмежене: <b>1 раз на добу</b>. Він не витрачається під час створення кристалів.</p>'
            : '<p>Первый хронолит и чертежи следующих ступеней продаёт <b>Хальдор</b> в лавке Искателей реликтов. Для покупки нужны соответствующая репутация Искателей, монеты и реликты.</p><p>Использование хронолита ограничено: <b>1 раз в сутки</b>. При создании кристаллов он не расходуется.</p>';
        $chronoliths .= '<p>[[npc:18;display:inline]]</p>';
        $chronoliths .= $this->tableStart($uk ? ['Хронолит', 'Eternum за використання'] : ['Хронолит', 'Eternum за использование']);
        $upgrades = '';
        foreach ($mechanisms as $mechanism) {
            $chronoliths .= '<tr><td align="center"><div class="structures">[[item:'.$mechanism->share_item_id.']]</div></td><td align="center">'.(int) $mechanism->value.'</td></tr>';
            $materials = DB::table('share_item_upgrade_materials')->where('share_item_id', $mechanism->share_item_id)->get();
            foreach ($materials as $material) {
                $upgrades .= '[[item:'.$material->required_share_item_id.']] ';
            }
        }
        $chronoliths .= '</tbody></table>';
        $upgradeText = $uk
            ? '<p>Віднесіть поточний хронолит і відповідне креслення до кузні, у розділ <b>«Апгрейд»</b>. Креслення витрачається, а хронолит перетворюється на наступний рівень. Покращення <b>завжди успішне</b>.</p><p>Проходьте ступені послідовно: від тріснутого хронолита до Хронолита Перших. Для кожного переходу потрібне своє креслення.</p>'
            : '<p>Отнесите текущий хронолит и подходящий чертёж в кузню, в раздел <b>«Апгрейд»</b>. Чертёж расходуется, а хронолит превращается в следующую ступень. Улучшение <b>всегда успешно</b>.</p><p>Проходите ступени последовательно: от треснувшего хронолита до Хронолита Первых. Для каждого перехода нужен свой чертёж.</p>';
        $upgradeText .= '<div class="structures">'.$upgrades.'</div>';
        $progress = $uk
            ? '<p>У рюкзаку виберіть Eternum і натисніть <b>«Використати»</b>. Можна використати одразу кілька кристалів.</p><p><b>1 Eternum = +1 очко репутації «Вічність».</b> Використані кристали витрачаються.</p><p>Пороги в магазині — це вимоги до накопиченої репутації, а не її ціна: покупка не віднімає очки репутації.</p>'
            : '<p>В рюкзаке выберите Eternum и нажмите <b>«Использовать»</b>. Можно использовать сразу несколько кристаллов.</p><p><b>1 Eternum = +1 очко репутации «Вечность».</b> Использованные кристаллы расходуются.</p><p>Пороги в магазине — требования к накопленной репутации, а не её цена: покупка не отнимает очки репутации.</p>';
        $rewards = $uk
            ? '<p>Крамниця Вічності доступна в діалозі <b>Ейдрана, локація №6</b>. Сумки збільшують місткість рюкзака, пояси — кількість комірок панелі швидкого доступу. Наведіть курсор на іконку, щоб побачити характеристики.</p><p>Для покупки потрібні <b>і монети, і невикористані кристали Eternum</b>. Заздалегідь розподіліть запас: частину використайте для репутації, решту залиште для оплати товарів. Ліміту кількості покупок цих сумок і поясів немає.</p>'
            : '<p>Магазин Вечности доступен в диалоге <b>Эйдрана, локация №6</b>. Сумки увеличивают вместимость рюкзака, пояса — количество ячеек панели быстрого доступа. Наведите курсор на иконку, чтобы увидеть характеристики.</p><p>Для покупки нужны <b>и монеты, и неиспользованные кристаллы Eternum</b>. Заранее распределите запас: часть используйте для репутации, остальные оставьте для оплаты товаров. Лимита количества покупок этих сумок и поясов нет.</p>';
        $rewards .= $this->tableStart($uk ? ['Предмет', 'Репутація від', 'Монети', 'Eternum'] : ['Предмет', 'Репутация от', 'Монеты', 'Eternum']);
        foreach ($shop->shopItems->sortBy('min_reputation_points') as $offer) {
            $quantity = (int) $offer->requirements->where('share_item_id', $eternum->id)->sum('quantity');
            $rewards .= '<tr><td align="center"><div class="structures">[[item:'.$offer->share_item_id.']]</div></td><td align="center">'.$offer->min_reputation_points.'</td><td align="center">'.format_money($offer->price).'</td><td align="center">'.$quantity.'</td></tr>';
        }
        $rewards .= '</tbody></table>';

        return $this->frame($uk ? 'Репутація «Вічність»' : 'Репутация «Вечность»', $intro)
            .$this->frame($uk ? 'Де здобути Eternum' : 'Где добыть Eternum', $sources)
            .$this->frame($uk ? 'Хронолити: кристали щодня' : 'Хронолиты: кристаллы каждый день', $chronoliths)
            .$this->frame($uk ? 'Покращення хронолита' : 'Улучшение хронолита', $upgradeText)
            .$this->frame($uk ? 'Як підвищувати репутацію' : 'Как повышать репутацию', $progress)
            .$this->frame($uk ? 'Нагороди крамниці Вічності' : 'Награды магазина Вечности', $rewards);
    }

    /** @param list<string> $headers */
    private function tableStart(array $headers): string
    {
        return '<table class="coll brd2-all p10h p4v" width="100%" cellspacing="0" cellpadding="0"><thead><tr class="bg_l">'
            .implode('', array_map(fn (string $header): string => '<th align="center">'.e($header).'</th>', $headers))
            .'</tr></thead><tbody>';
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
