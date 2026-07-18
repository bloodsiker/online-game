<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Services\MountRarityConfig;
use Illuminate\Database\Seeder;

/**
 * Gems and Mount seeder.
 *
 * MOUNT (Оправа) — разово устанавливается на предмет и открывает случайное
 * число сокетов в диапазоне редкости (см. MountRarityConfig::socketRange(),
 * редкость — стандартный share_items.rarity) за плату в монетах
 * (MountRarityConfig::openCost()).
 *
 * Самоцветы (seedGemstones) — плоский +N к одной из пяти первичных
 * характеристик (PlayerStatKey), картинки лежат в public/img/resource/stones/.
 */
class GemSeeder extends Seeder
{
    private const GEMSTONE_DESCRIPTION = 'Один из волшебных самоцветов мира. Можно инкрустировать в предметы с сокетами.';

    /** [prefix|null, значение бонуса, редкость] — порядок и соответствие редкости заданы заказчиком */
    private const GEMSTONE_TIERS = [
        ['prefix' => 'Малый', 'value' => 1, 'rarity' => ItemRarity::COMMON],
        ['prefix' => null, 'value' => 2, 'rarity' => ItemRarity::UNCOMMON],
        ['prefix' => 'Великий', 'value' => 3, 'rarity' => ItemRarity::RARE],
        ['prefix' => 'Большой', 'value' => 4, 'rarity' => ItemRarity::EPIC],
        ['prefix' => 'Абсолютный', 'value' => 5, 'rarity' => ItemRarity::LEGENDARY],
    ];

    /** stat — ключ PlayerStatKey, suffix — родительный падеж для имени, label — для описания */
    private const GEMSTONE_STATS = [
        ['stat' => 'strength', 'suffix' => 'силы', 'label' => 'сила'],
        ['stat' => 'wisdom', 'suffix' => 'мудрости', 'label' => 'мудрость'],
        ['stat' => 'agility', 'suffix' => 'ловкости', 'label' => 'ловкость'],
        ['stat' => 'intuition', 'suffix' => 'интуиции', 'label' => 'интуиция'],
        ['stat' => 'intelligence', 'suffix' => 'интеллекта', 'label' => 'интеллект'],
    ];

    /** Точное соответствие "[префикс]_[характеристика]" → переименованный (транслит, нижний регистр) файл в public/img/resource/stones/ */
    private const GEMSTONE_IMAGES = [
        'Малый_силы' => 'malyy_samotsvet_sily.gif',
        'Малый_мудрости' => 'malyy_samotsvet_mudrosti.gif',
        'Малый_ловкости' => 'malyy_samotsvet_lovkosti.gif',
        'Малый_интуиции' => 'malyy_samotsvet_intuitsii.gif',
        'Малый_интеллекта' => 'malyy_samotsvet_intellekta.gif',
        '_силы' => 'samotsvet_sily.gif',
        '_мудрости' => 'samotsvet_mudrosti.gif',
        '_ловкости' => 'samotsvet_lovkosti.gif',
        '_интуиции' => 'samotsvet_intuitsii.gif',
        '_интеллекта' => 'samotsvet_intellekta.gif',
        'Великий_силы' => 'velikiy_samotsvet_sily.gif',
        'Великий_мудрости' => 'velikiy_samotsvet_mudrosti.gif',
        'Великий_ловкости' => 'velikiy_samotsvet_lovkosti.gif',
        'Великий_интуиции' => 'velikiy_samotsvet_intuitsii.gif',
        'Великий_интеллекта' => 'velikiy_samotsvet_intellekta.gif',
        'Большой_силы' => 'bolshoy_samotsvet_sily.gif',
        'Большой_мудрости' => 'bolshoy_samotsvet_mudrosti.gif',
        'Большой_ловкости' => 'bolshoy_samotsvet_lovkosti.gif',
        'Большой_интуиции' => 'bolshoy_samotsvet_intuitsii.gif',
        'Большой_интеллекта' => 'bolshoy_samotsvet_intellekta.gif',
        'Абсолютный_силы' => 'absolyutnyy_samotsvet_sily.gif',
        'Абсолютный_мудрости' => 'absolyutnyy_samotsvet_mudrosti.gif',
        'Абсолютный_ловкости' => 'absolyutnyy_samotsvet_lovkosti.gif',
        'Абсолютный_интуиции' => 'absolyutnyy_samotsvet_intuitsii.gif',
        'Абсолютный_интеллекта' => 'absolyutnyy_samotsvet_intellekta.gif',
    ];

    public function run(): void
    {
        $this->seedMounts();
        $this->seedGemstones();
    }

    /** Картинки оправ (транслит, нижний регистр) в public/img/resource/sokets/ */
    private const MOUNT_IMAGES = [
        'common' => 'obychnaya_zazubrennaya_oprava.gif',
        'uncommon' => 'neobychnaya_zazubrennaya_oprava.gif',
        'rare' => 'redkaya_zazubrennaya_oprava.gif',
        'epic' => 'epicheskaya_zazubrennaya_oprava.gif',
    ];

    private function seedMounts(): void
    {
        $prices = [
            ItemRarity::COMMON->value => 1000,
            ItemRarity::UNCOMMON->value => 3000,
            ItemRarity::RARE->value => 8000,
            ItemRarity::EPIC->value => 20000,
        ];

        foreach (MountRarityConfig::supportedRarities() as $rarity) {
            [$min, $max] = MountRarityConfig::socketRange($rarity);

            ShareItem::firstOrCreate(
                [
                    'type' => ShareItemType::MOUNT->value,
                    'name' => MountRarityConfig::label($rarity).' зазубренная оправа',
                ],
                [
                    'description' => sprintf(
                        'Разово устанавливается на предмет и открывает %d-%d сокет(ов). Стоимость установки у кузнеца: %d монет.',
                        $min,
                        $max,
                        MountRarityConfig::openCost($rarity)
                    ),
                    'image' => '/img/resource/sokets/'.self::MOUNT_IMAGES[$rarity->value],
                    'rarity' => $rarity->value,
                    'price' => $prices[$rarity->value],
                    'is_sell' => true,
                    'is_active' => true,
                    'is_weight' => false,
                ]
            );
        }

        $this->command->info('Mounts seeded: '.count(MountRarityConfig::supportedRarities()).' rarities.');
    }

    private function seedGemstones(): void
    {
        $count = 0;

        foreach (self::GEMSTONE_STATS as $statDef) {
            foreach (self::GEMSTONE_TIERS as $tier) {
                $prefix = $tier['prefix'];
                $value = $tier['value'];

                $name = $prefix !== null
                    ? "{$prefix} самоцвет {$statDef['suffix']}"
                    : "Самоцвет {$statDef['suffix']}";

                $imageKey = ($prefix ?? '').'_'.$statDef['suffix'];
                $image = self::GEMSTONE_IMAGES[$imageKey];

                ShareItem::firstOrCreate(
                    [
                        'type' => ShareItemType::GEM->value,
                        'name' => $name,
                    ],
                    [
                        'description' => self::GEMSTONE_DESCRIPTION." +{$value} {$statDef['label']}",
                        'image' => '/img/resource/stones/'.$image,
                        'gem_stats' => [
                            ['stat' => $statDef['stat'], 'value' => $value, 'is_percent' => false],
                        ],
                        'rarity' => $tier['rarity']->value,
                        'price' => 500 * $value,
                        'is_sell' => true,
                        'is_active' => true,
                        'is_weight' => false,
                    ]
                );

                $count++;
            }
        }

        $this->command->info('Gemstones seeded: '.count(self::GEMSTONE_STATS).' stats × '.count(self::GEMSTONE_TIERS)." tiers = {$count} items.");
    }
}
