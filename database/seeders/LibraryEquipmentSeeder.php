<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Library\Infrastructure\Persistence\Models\LibraryArticle;
use App\Modules\Library\Infrastructure\Persistence\Models\LibraryCategory;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibraryEquipmentSeeder extends Seeder
{
    /**
     * В каталог намеренно попадает базовая ступень каждого предмета.
     * Улучшенные редкости не дублируются: их характеристики доступны через
     * цепочку апгрейда в тултипе и в статье о кузне.
     *
     * @var array<int, array<int, array{title: string, description: string, items: array<int, string>}>>
     */
    private const SETS = [
        1 => [
            [
                'title' => 'Кожаный комплект',
                'description' => 'Универсальное снаряжение воина для уровней 1–20. Защитные ячейки открываются постепенно, а на 10-м уровне стартовый тесак можно заменить полуторным мечом.',
                'items' => [
                    'Тесак Головореза',
                    'Полуторный меч',
                    'Кожаный доспех',
                    'Кожаные сапоги',
                    'Кожаные наручи',
                    'Кожаный шлем',
                    'Кожаные поножи',
                    'Кожаные наплечники',
                    'Кожаный щит',
                    'Кожаная кольчуга',
                ],
            ],
            [
                'title' => 'Комплект Пепельной Башни',
                'description' => 'Универсальный комплект начинающего мага. Для оружия можно выбрать посох с устойчивым уроном, фолиант с магическим критом или сбалансированную сферу.',
                'items' => [
                    'Посох Пепельной Башни',
                    'Фолиант Пепельной Башни',
                    'Сфера Пепельной Башни',
                    'Роба Пепельной Башни',
                    'Сапоги Пепельной Башни',
                    'Наручи Пепельной Башни',
                    'Капюшон Пепельной Башни',
                    'Поножи Пепельной Башни',
                    'Наплечники Пепельной Башни',
                    'Подрясник Пепельной Башни',
                ],
            ],
        ],
        2 => [
            [
                'title' => 'Комплект «Мамонт»',
                'description' => 'Ветка защитника: выносливость, высокая живучесть и щит. Несколько видов одноручного оружия позволяют выбрать подходящий оружейный навык.',
                'items' => [
                    'Кастет «Мамонт»',
                    'Молот мамонта',
                    'Булава мамонта',
                    'Щит «Мамонт»',
                    'Нагрудник «Мамонт»',
                    'Сапоги «Мамонт»',
                    'Рукавицы «Мамонт»',
                    'Шлем «Мамонт»',
                    'Поножи «Мамонт»',
                    'Наплечники «Мамонт»',
                    'Кольчуга «Мамонт»',
                ],
            ],
            [
                'title' => 'Сумеречный комплект',
                'description' => 'Ветка ловкости и уклонения. Можно сражаться парными клинками либо выбрать лук, кинжал или кнут, сохраняя направленность комплекта на уворот.',
                'items' => [
                    'Сумеречный Дайто',
                    'Сумеречный лук',
                    'Сумеречный кинжал',
                    'Сумеречный кнут',
                    'Сумеречная броня',
                    'Сумеречные сапоги',
                    'Сумеречные перчатки',
                    'Сумеречная маска',
                    'Сумеречные поножи',
                    'Сумеречные наплечники',
                    'Сумеречная кольчуга',
                ],
            ],
            [
                'title' => 'Комплект «Палач»',
                'description' => 'Ветка физического критического удара. Два меча дают быстрый стиль боя, а двуручный топор — более сильный одиночный удар.',
                'items' => [
                    'Меч палача',
                    'Топор палача',
                    'Жилет палача',
                    'Сапоги палача',
                    'Наручи палача',
                    'Маска палача',
                    'Поножи палача',
                    'Наплечники палача',
                    'Рубаха палача',
                ],
            ],
            [
                'title' => 'Комплект «Иней»',
                'description' => 'Защитная магическая ветка с повышенной бронёй, интеллектом и устойчивым усилением заклинаний.',
                'items' => [
                    'Посох «Иней»',
                    'Жезл «Иней»',
                    'Сфера «Иней»',
                    'Мантия «Иней»',
                    'Сапоги «Иней»',
                    'Наручи «Иней»',
                    'Капюшон «Иней»',
                    'Поножи «Иней»',
                    'Наплечники «Иней»',
                    'Подрясник «Иней»',
                ],
            ],
            [
                'title' => 'Комплект «Всполох»',
                'description' => 'Атакующая магическая ветка: чаще наносит магические критические удары и увеличивает их силу, жертвуя частью защиты.',
                'items' => [
                    'Посох «Всполох»',
                    'Фолиант «Всполох»',
                    'Сфера «Всполох»',
                    'Мантия «Всполох»',
                    'Сапоги «Всполох»',
                    'Наручи «Всполох»',
                    'Капюшон «Всполох»',
                    'Поножи «Всполох»',
                    'Наплечники «Всполох»',
                    'Подрясник «Всполох»',
                ],
            ],
        ],
        3 => [
            [
                'title' => 'Комплект «Титан»',
                'description' => 'Продолжение защитной ветки «Мамонта»: выносливость, тяжёлая броня, щит и выбор одноручного дробящего оружия.',
                'items' => [
                    'Молот «Титан»',
                    'Булава «Титан»',
                    'Щит «Титан»',
                    'Нагрудник «Титан»',
                    'Сапоги «Титан»',
                    'Наручи «Титан»',
                    'Шлем «Титан»',
                    'Поножи «Титан»',
                    'Наплечники «Титан»',
                    'Кольчуга «Титан»',
                ],
            ],
            [
                'title' => 'Призрачный комплект',
                'description' => 'Продолжение Сумеречной ветки: высокая ловкость и уклонение с выбором клинка, лука или кнута.',
                'items' => [
                    'Призрачный клинок',
                    'Призрачный лук',
                    'Призрачный кнут',
                    'Призрачная броня',
                    'Призрачные сапоги',
                    'Призрачные перчатки',
                    'Призрачный шлем',
                    'Призрачные поножи',
                    'Призрачные наплечники',
                    'Призрачная кольчуга',
                ],
            ],
            [
                'title' => 'Комплект «Жнец»',
                'description' => 'Продолжение ветки «Палача»: физический крит и выбор между парным оружием и тяжёлым двуручным топором.',
                'items' => [
                    'Топор двуручный жнеца',
                    'Топор жнеца',
                    'Меч жнеца',
                    'Жилет жнеца',
                    'Сапоги жнеца',
                    'Наручи жнеца',
                    'Шлем жнеца',
                    'Поножи жнеца',
                    'Наплечники жнеца',
                    'Рубаха жнеца',
                ],
            ],
            [
                'title' => 'Комплект «Архимаг»',
                'description' => 'Магическая ветка критического удара. Снаряжение повышает шанс магического крита, а три вида оружия позволяют развивать выбранный навык.',
                'items' => [
                    'Посох «Архимаг»',
                    'Фолиант «Архимаг»',
                    'Сфера «Архимаг»',
                    'Мантия «Архимаг»',
                    'Сапоги «Архимаг»',
                    'Наручи «Архимаг»',
                    'Капюшон «Архимаг»',
                    'Поножи «Архимаг»',
                    'Наплечники «Архимаг»',
                    'Плащ «Архимаг»',
                ],
            ],
            [
                'title' => 'Комплект «Астрал»',
                'description' => 'Ветка стабильного магического урона. Каждый предмет усиливает атаку заклинаниями без зависимости от срабатывания критического удара.',
                'items' => [
                    'Посох «Астрал»',
                    'Фолиант «Астрал»',
                    'Сфера «Астрал»',
                    'Мантия «Астрал»',
                    'Сапоги «Астрал»',
                    'Наручи «Астрал»',
                    'Капюшон «Астрал»',
                    'Поножи «Астрал»',
                    'Наплечники «Астрал»',
                    'Плащ «Астрал»',
                ],
            ],
        ],
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $category = LibraryCategory::query()->updateOrCreate(
                ['slug' => 'snariazhenie'],
                [
                    'parent_id' => null,
                    'name' => 'Снаряжение',
                    'description' => 'Комплекты оружия и одежды по тирам и боевым направлениям.',
                    'sort_order' => 45,
                    'is_active' => true,
                ],
            );

            foreach (array_keys(self::SETS) as $tier) {
                LibraryArticle::withTrashed()->updateOrCreate(
                    ['slug' => 'snariazhenie-tir-'.$tier],
                    [
                        'category_id' => $category->id,
                        'author_id' => null,
                        'title' => 'Снаряжение: Тир '.$tier,
                        'excerpt' => 'Комплекты снаряжения Тира '.$tier.', их назначение и состав.',
                        'content' => $this->articleContent($tier),
                        'status' => LibraryArticle::STATUS_PUBLISHED,
                        'published_at' => now(),
                        'sort_order' => $tier,
                        'deleted_at' => null,
                    ],
                );
            }
        });
    }

    private function articleContent(int $tier): string
    {
        $levelRange = match ($tier) {
            1 => '1–20',
            2 => '20–50',
            3 => '55–90',
        };

        $frames = collect(self::SETS[$tier])
            ->map(fn (array $set): string => $this->frame(
                $set['title'],
                '<p class="library-equipment-set__description">'.e($set['description']).'</p>'
                .'<div class="library-equipment-set__items">'.$this->itemShortcodes($set['items']).'</div>',
            ))
            ->implode("\n");

        return '<p><b>Тир '.$tier.'</b> — снаряжение для персонажей '.$levelRange.' уровней. '
            .'Выбирайте комплект под свой боевой архетип, а точные характеристики и требования смотрите в тултипе предмета.</p>'
            .'<div class="library-info-block library-info-block--tip"><strong>Улучшение:</strong> в каталоге показана базовая ступень каждого предмета. Его редкость можно повышать в кузне, сохраняя принадлежность к тому же комплекту.</div>'
            .$frames;
    }

    /** @param array<int, string> $names */
    private function itemShortcodes(array $names): string
    {
        $idsByName = ShareItem::query()
            ->whereIn('name', $names)
            ->where('is_active', true)
            ->pluck('id', 'name');

        $shortcodes = collect($names)
            ->filter(fn (string $name): bool => $idsByName->has($name))
            ->map(fn (string $name): string => '[[item:'.$idsByName->get($name).']]')
            ->implode(' ');

        if ($shortcodes !== '') {
            return $shortcodes;
        }

        return '<span class="library-equipment-set__empty">Предметы комплекта пока не добавлены.</span>';
    }

    private function frame(string $title, string $content): string
    {
        return <<<HTML
<table class="library-game-frame library-equipment-set" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="20" align="right" valign="bottom" class="tbl-shp-sml lt"><b></b></td><td class="tbl-shp-sml tt" valign="top" align="center"><table class="library-game-frame__title" border="0" cellspacing="0" cellpadding="0"><tbody><tr height="22"><td width="27"><img src="/img/bg/info/tbl-usi_label-left.gif" width="27" height="22" alt=""></td><td align="center" class="tbl-usi_label-center">{$title}</td><td width="27"><img src="/img/bg/info/tbl-usi_label-right.gif" width="27" height="22" alt=""></td></tr></tbody></table></td><td width="20" align="left" valign="bottom" class="tbl-shp-sml rt"><b></b></td></tr><tr><td class="tbl-shp-sides ls">&nbsp;</td><td class="tbl-usi_bg" valign="top" style="padding:8px 10px"><div class="structures">{$content}</div></td><td class="tbl-shp-sides rs">&nbsp;</td></tr><tr height="18"><td width="20" align="right" valign="top" class="tbl-shp-sml lb"><b></b></td><td class="tbl-shp-sml bb" valign="top" align="center">&nbsp;</td><td width="20" align="left" valign="top" class="tbl-shp-sml rb"><b></b></td></tr></tbody></table>
HTML;
    }
}
