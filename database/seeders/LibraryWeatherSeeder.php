<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Weather\Domain\Enums\WeatherType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Раздел библиотеки «Погода» и статья о погодных явлениях.
 * Список карт по явлениям берётся из map_weather_weights в момент запуска сидера.
 */
final class LibraryWeatherSeeder extends Seeder
{
    private const CATEGORY_SLUG = 'pogoda';

    private const ARTICLE_SLUG = 'pogoda-yavleniya-i-effekty';

    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->where('slug', self::CATEGORY_SLUG)->first() ?? new LibraryCategory(['slug' => self::CATEGORY_SLUG]);
            $category->fill([
                'parent_id' => null,
                'name' => ['ru' => 'Погода', 'uk' => 'Погода'],
                'description' => [
                    'ru' => 'Гроза, туман, зной, метель, ветер и ураган: как погода меняет путешествия и бои.',
                    'uk' => 'Гроза, туман, спека, хуртовина, вітер і ураган: як погода змінює подорожі та бої.',
                ],
                'content_type' => 'articles',
                'sort_order' => 59,
                'is_active' => true,
            ]);
            if (Schema::hasColumn('library_categories', 'name_legacy')) {
                $category->setAttribute('name_legacy', 'Погода');
            }
            $category->save();

            $article = LibraryArticle::withTrashed()->where('slug', self::ARTICLE_SLUG)->first() ?? new LibraryArticle(['slug' => self::ARTICLE_SLUG]);
            $article->fill([
                'category_id' => $category->id,
                'title' => ['ru' => 'Погода: явления и их действие', 'uk' => 'Погода: явища та їхня дія'],
                'excerpt' => [
                    'ru' => 'Какая бывает погода, где её увидеть, чем опасны гроза, туман, зной, метель и ураган и чем помогают радуга и попутный ветер.',
                    'uk' => 'Яка буває погода, де її побачити, чим небезпечні гроза, туман, спека, хуртовина й ураган і чим допомагають веселка та попутний вітер.',
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

            $this->command?->info('Погода: раздел #'.$category->id.', статья #'.$article->id);
        });
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }

    /** @return array<string, list<string>> карты по явлениям (радуга — там же, где гроза) */
    private function mapsByWeather(string $locale): array
    {
        $rows = DB::table('map_weather_weights as w')
            ->join('maps as m', 'm.id', '=', 'w.map_id')
            ->where('w.weight', '>', 0)
            ->orderBy('m.id')
            ->get(['w.weather', 'm.name']);
        $result = [];
        foreach ($rows as $row) {
            $names = json_decode((string) $row->name, true);
            $result[$row->weather][] = is_array($names) ? (string) ($names[$locale] ?? $names['ru'] ?? reset($names)) : (string) $row->name;
        }
        $result['rainbow'] = $result['storm'] ?? [];

        return $result;
    }

    private function phenomenaTable(string $locale): string
    {
        $uk = $locale === 'uk';
        $maps = $this->mapsByWeather($locale);
        $rows = [
            [WeatherType::STORM, $uk ? 'Погана' : 'Плохая', $uk ? 'Під зливою легко застудитися: «Застуда» на час помітно послаблює всі характеристики.' : 'Под ливнем легко простудиться: «Простуда» на время заметно ослабляет все характеристики.'],
            [WeatherType::RAINBOW, $uk ? 'Добра' : 'Хорошая', $uk ? 'Буває після грози. Кожен, хто приходить, отримує «Веселку»: на час характеристики трохи посилюються.' : 'Бывает после грозы. Каждый, кто приходит, получает «Радугу»: на время характеристики немного усиливаются.'],
            [WeatherType::FOG, $uk ? 'Погана' : 'Плохая', $uk ? 'Діє «Туман»: помітно знижено ухилення та крит, а суперникам легше ухилятися від ваших ударів.' : 'Действует «Туман»: заметно снижены уворот и крит, а противникам легче уклоняться от ваших ударов.'],
            [WeatherType::HEAT, $uk ? 'Погана' : 'Плохая', $uk ? 'Здоров’я та мана відновлюються помітно повільніше.' : 'Здоровье и мана восстанавливаются заметно медленнее.'],
            [WeatherType::BLIZZARD, $uk ? 'Погана' : 'Плохая', $uk ? 'Переходи між локаціями тривають помітно довше.' : 'Переходы между локациями занимают заметно больше времени.'],
            [WeatherType::HURRICANE, $uk ? 'Погана' : 'Плохая', $uk ? 'Пориви вітру іноді відносять мандрівника на сусідню локацію.' : 'Порывы ветра иногда уносят путника на соседнюю локацию.'],
            [WeatherType::TAILWIND, $uk ? 'Добра' : 'Хорошая', $uk ? 'Переходи між локаціями тривають помітно менше.' : 'Переходы между локациями занимают заметно меньше времени.'],
        ];
        $labels = ['storm' => ['Гроза', 'Гроза'], 'rainbow' => ['Радуга', 'Веселка'], 'fog' => ['Густой туман', 'Густий туман'], 'heat' => ['Зной', 'Спека'],
            'blizzard' => ['Метель', 'Хуртовина'], 'hurricane' => ['Ураган', 'Ураган'], 'tailwind' => ['Попутный ветер', 'Попутний вітер']];
        $html = '';
        foreach ($rows as [$type, $kind, $effect]) {
            $good = in_array($type, [WeatherType::RAINBOW, WeatherType::TAILWIND], true);
            $color = $good ? '#1f7a1f' : '#b80000';
            $where = array_unique($maps[$type->value] ?? []);
            $html .= '<div style="padding:8px 0;border-bottom:1px solid #db9f73;">'
                .'<p style="margin:0 0 4px;"><b style="color:'.$color.';font-size:13px;">'.e($labels[$type->value][$uk ? 1 : 0]).'</b>'
                .' <span style="color:'.$color.';">— '.e(mb_strtolower($kind)).($uk ? ' погода' : ' погода').'</span></p>'
                .'<p style="margin:0 0 4px;">'.e($effect).'</p>'
                .'<p style="margin:0;"><b>'.($uk ? 'Де буває:' : 'Где бывает:').'</b> '.($where === [] ? '—' : e(implode(', ', $where))).'</p>'
                .'</div>';
        }

        return $this->frame($uk ? 'Явища та їхня дія' : 'Явления и их действие', $html);
    }

    private function content(string $locale): string
    {
        $uk = $locale === 'uk';
        $intro = $uk
            ? '<p>Світ Вічності живе своїм життям: над лісами збираються грози, долини затягує туман, а на перевалах мете хуртовина. <b>У кожної карти своя погода</b>, і вона змінюється сама — явище триває якийсь час, потім настає ясна пора, а згодом приходить нова негода.</p>'
                .'<div class="library-info-block library-info-block--tip"><strong>Де подивитися:</strong> поточну погоду видно поруч із назвою локації, наприклад «Крисяче Гніздо <b style="color:#b80000">(Гроза)</b>». Погана погода позначена червоним, добра — зеленим. Натисніть на назву погоди — відкриється вікно з описанням і дією.</div>'
            : '<p>Мир Вечности живёт своей жизнью: над лесами собираются грозы, долины затягивает туман, а на перевалах метёт метель. <b>У каждой карты своя погода</b>, и меняется она сама — явление длится какое-то время, затем наступает ясная пора, а потом приходит новая непогода.</p>'
                .'<div class="library-info-block library-info-block--tip"><strong>Где посмотреть:</strong> текущая погода видна рядом с названием локации, например «Крысиное Гнездо <b style="color:#b80000">(Гроза)</b>». Плохая погода отмечена красным, хорошая — зелёным. Нажмите на название погоды — откроется окно с описанием и действием.</div>';
        $where = $uk
            ? '<p>Погода буває лише просто неба. <b>У містах і підземеллях погоди немає</b> — там можна перечекати негоду.</p>'
            : '<p>Погода бывает только под открытым небом. <b>В городах и подземельях погоды нет</b> — там можно переждать непогоду.</p>';
        $effects = $uk
            ? '<ul><li><b>«Застуда»</b> — може накластися під час грози, коли ви приходите на локацію або вже стоїте на ній. Минає сама через деякий час.</li>'
                .'<li><b>«Веселка»</b> — накладається на всіх, хто опинився на карті під час веселки. Повторно не продовжується, поки діє.</li>'
                .'<li><b>«Туман»</b> — діє, поки ви в тумані. Знімається, щойно ви підете на карту без туману або туман розсіється.</li></ul>'
            : '<ul><li><b>«Простуда»</b> — может наложиться во время грозы, когда вы приходите на локацию или уже стоите на ней. Проходит сама через некоторое время.</li>'
                .'<li><b>«Радуга»</b> — накладывается на всех, кто оказался на карте во время радуги. Повторно не продлевается, пока действует.</li>'
                .'<li><b>«Туман»</b> — действует, пока вы в тумане. Снимается, как только вы уйдёте на карту без тумана или туман рассеется.</li></ul>';
        $tips = $uk
            ? '<div class="library-info-block library-info-block--tip"><strong>Поради:</strong> у тумані важче влучати й ухилятися — серйозні бої краще відкласти. У спеку беріть із собою зілля. Під час урагану стежте, куди вас віднесло, а в хуртовину закладайте більше часу на дорогу. Попутний вітер — гарний час для далеких переходів і торгових рейсів.</div>'
            : '<div class="library-info-block library-info-block--tip"><strong>Советы:</strong> в тумане труднее попадать и уворачиваться — серьёзные бои лучше отложить. В зной берите с собой зелья. Во время урагана следите, куда вас унесло, а в метель закладывайте больше времени на дорогу. Попутный ветер — хорошее время для дальних переходов и торговых рейсов.</div>';

        return $intro
            ."\n\n".$this->frame($uk ? 'Погода в світі' : 'Погода в мире', $where)
            ."\n\n<h2>".($uk ? 'Погодні явища' : 'Погодные явления')."</h2>\n"
            .$this->phenomenaTable($locale)
            ."\n\n<h2>".($uk ? 'Погодні ефекти' : 'Погодные эффекты')."</h2>\n"
            .$this->frame($uk ? 'Коли накладаються і знімаються' : 'Когда накладываются и снимаются', $effects)
            ."\n\n".'<div class="library-info-block library-info-block--important"><strong>'.($uk ? 'Важливо:' : 'Важно:').'</strong> '
            .($uk ? 'погода змінюється сама, і передбачити її заздалегідь неможливо — дивіться на назву локації перед дорогою.' : 'погода меняется сама, и предсказать её заранее нельзя — смотрите на название локации перед дорогой.')
            .'</div>'
            ."\n\n".$tips;
    }
}
