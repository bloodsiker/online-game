<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Seeder;

/**
 * Реагенты для изучения клановых скиллов (clan_skill_level_item_requirements) —
 * по образцу «яиц РБ» Lineage2: расходуемые ресурсы без привязки к конкретному
 * боссу в названии/описании, дропаются с монстров (расставляется отдельно,
 * см. monster_has_items), списываются при изучении уровня скилла
 * (ClanSkillService::consumeItemRequirements).
 *
 * Один реагент — одна характеристика ClanSkillEffectType (8 из 9 существующих
 * типов + один универсальный про запас под будущий 10-й скилл). Тип RESOURCE,
 * не QUEST — предметы стакаются и торгуются между игроками, как реальные яйца
 * РБ в L2, а не привязаны к конкретной квестовой цепочке.
 */
class ClanSkillReagentSeeder extends Seeder
{
    private const ITEMS = [
        [
            'name' => 'Сердце Могущества',
            'description' => 'Тяжёлый тёмно-железный шар, неестественно плотный для своего размера — держишь и чувствуешь, будто в руке кусок горы.',
        ],
        [
            'name' => 'Перо Быстроты',
            'description' => 'Невесомое перо, которое никогда не опускается на землю — вечно дрейфует, будто в неощутимом ветре.',
        ],
        [
            'name' => 'Всевидящее Око',
            'description' => 'Маленький кристалл в форме глаза — кажется, что он следит за движением в комнате.',
        ],
        [
            'name' => 'Кристалл Разума',
            'description' => 'Гранёный фиолетовый кристалл, внутри которого тихо гудит запертая мысль.',
        ],
        [
            'name' => 'Слеза Мудрости',
            'description' => 'Окаменевшая слеза, в которой будто застыли века памяти.',
        ],
        [
            'name' => 'Сердце Жизни',
            'description' => 'Тёплый золотисто-алый шар, едва заметно пульсирует, как сердцебиение.',
        ],
        [
            'name' => 'Сгусток Эфира',
            'description' => 'Клубящийся сине-фиолетовый сгусток чистой магической энергии, беспокойно меняющий форму.',
        ],
        [
            'name' => 'Клык Ярости',
            'description' => 'Зазубренный клык с алыми прожилками, тёплый на ощупь, будто всё ещё пышет злобой.',
        ],
        [
            'name' => 'Чешуя Стойкости',
            'description' => 'Бронированная чешуя невероятной твёрдости, холодная и абсолютно неподатливая.',
        ],
        [
            'name' => 'Осколок Вечности',
            'description' => 'Мерцающий фрагмент чего-то, что не совсем принадлежит текущему моменту времени — назначение неясно, сила ощутима.',
        ],
    ];

    public function run(): void
    {
        $created = 0;

        foreach (self::ITEMS as $data) {
            $item = ShareItem::firstOrNew(['name' => $data['name']]);

            if ($item->exists) {
                continue;
            }

            // Не через mass assignment (fillable у ShareItem узкий) — присваиваем
            // напрямую, чтобы rarity/price/is_sell и т.д. реально сохранились.
            $item->type = ShareItemType::RESOURCE;
            $item->description = $data['description'];
            $item->rarity = ItemRarity::RARE;
            $item->price = 3000;
            $item->is_two_hand = false;
            $item->is_sell = true;
            $item->is_give = true;
            $item->is_droppable = true;
            $item->is_active = true;
            $item->is_weight = false;
            $item->save();

            $created++;
        }

        $this->command?->info("ClanSkillReagentSeeder: создано предметов — {$created}");
    }
}
