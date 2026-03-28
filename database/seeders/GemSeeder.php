<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ShareItemType;
use App\Models\Share\ShareItem;
use Illuminate\Database\Seeder;

/**
 * Gems and Socket Kit seeder.
 *
 * Gem tiers: Малый (Small), Обычный (Regular), Большой (Large), Совершенный (Perfect)
 *
 * Types:
 *  - Рубин        → +dmg (attack flat)
 *  - Сапфир       → +mp_max
 *  - Изумруд      → +hp_max
 *  - Топаз        → +agility
 *  - Аметист      → +strength
 *  - Алмаз        → +armor (flat)
 *  - Оникс        → +critical
 *  - Опал         → +dodge
 *  - Цитрин       → +intelligence
 *  - Аквамарин    → +int (магическая атака)
 *
 * SOCKET_KIT — набор для открытия нового сокета на предмете.
 */
class GemSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedGems();
        $this->seedSocketKit();
    }

    private function seedGems(): void
    {
        $tiers = [
            ['prefix' => 'Малый',       'mult' => 1],
            ['prefix' => 'Обычный',     'mult' => 2],
            ['prefix' => 'Большой',     'mult' => 3],
            ['prefix' => 'Совершенный', 'mult' => 5],
        ];

        // Base stat definitions per gem type
        // stat matches keys in PlayerStatService::buildSheet()
        $gemTypes = [
            [
                'name'  => 'Рубин',
                'desc'  => 'Увеличивает урон оружия.',
                'image' => 'img/items/gems/ruby.png',
                'stats' => [
                    ['stat' => 'left_min_dmg',  'value' => 3,  'is_percent' => false],
                    ['stat' => 'left_max_dmg',  'value' => 3,  'is_percent' => false],
                    ['stat' => 'right_min_dmg', 'value' => 3,  'is_percent' => false],
                    ['stat' => 'right_max_dmg', 'value' => 3,  'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Сапфир',
                'desc'  => 'Увеличивает максимальное количество маны.',
                'image' => 'img/items/gems/sapphire.png',
                'stats' => [
                    ['stat' => 'mp_max', 'value' => 20, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Изумруд',
                'desc'  => 'Увеличивает максимальное количество здоровья.',
                'image' => 'img/items/gems/emerald.png',
                'stats' => [
                    ['stat' => 'hp_max', 'value' => 30, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Топаз',
                'desc'  => 'Увеличивает ловкость.',
                'image' => 'img/items/gems/topaz.png',
                'stats' => [
                    ['stat' => 'agility', 'value' => 3, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Аметист',
                'desc'  => 'Увеличивает силу.',
                'image' => 'img/items/gems/amethyst.png',
                'stats' => [
                    ['stat' => 'strength', 'value' => 3, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Алмаз',
                'desc'  => 'Увеличивает защиту.',
                'image' => 'img/items/gems/diamond.png',
                'stats' => [
                    ['stat' => 'armor', 'value' => 10, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Оникс',
                'desc'  => 'Увеличивает шанс критического удара.',
                'image' => 'img/items/gems/onyx.png',
                'stats' => [
                    ['stat' => 'critical', 'value' => 2, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Опал',
                'desc'  => 'Увеличивает уклонение.',
                'image' => 'img/items/gems/opal.png',
                'stats' => [
                    ['stat' => 'dodge', 'value' => 2, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Цитрин',
                'desc'  => 'Увеличивает интеллект (магическая защита).',
                'image' => 'img/items/gems/citrine.png',
                'stats' => [
                    ['stat' => 'intelligence', 'value' => 3, 'is_percent' => false],
                ],
            ],
            [
                'name'  => 'Аквамарин',
                'desc'  => 'Увеличивает магическую силу (INT).',
                'image' => 'img/items/gems/aquamarine.png',
                'stats' => [
                    ['stat' => 'int', 'value' => 3, 'is_percent' => false],
                ],
            ],
        ];

        foreach ($gemTypes as $gemDef) {
            foreach ($tiers as $tier) {
                $mult  = $tier['mult'];
                $name  = $tier['prefix'].' '.$gemDef['name'];

                // Scale stats by tier multiplier
                $scaledStats = array_map(function (array $s) use ($mult): array {
                    return [
                        'stat'       => $s['stat'],
                        'value'      => $s['value'] * $mult,
                        'is_percent' => $s['is_percent'],
                    ];
                }, $gemDef['stats']);

                $statsText = implode(', ', array_map(
                    fn ($s) => '+'.$s['value'].($s['is_percent'] ? '%' : '').' '.$s['stat'],
                    $scaledStats
                ));

                ShareItem::firstOrCreate(
                    [
                        'type' => ShareItemType::GEM->value,
                        'name' => $name,
                    ],
                    [
                        'description' => $gemDef['desc'].' '.$statsText,
                        'image'       => $gemDef['image'],
                        'gem_stats'   => $scaledStats,
                        'price'       => 500 * $mult,
                        'is_sell'     => true,
                        'is_active'   => true,
                        'is_weight'   => false,
                    ]
                );
            }
        }

        $this->command->info('Gems seeded: '.count($gemTypes).' types × 4 tiers = '.(count($gemTypes) * 4).' items.');
    }

    private function seedSocketKit(): void
    {
        ShareItem::firstOrCreate(
            [
                'type' => ShareItemType::SOCKET_KIT->value,
                'name' => 'Набор кузнеца для сокета',
            ],
            [
                'description' => 'Позволяет открыть дополнительный сокет в предмете. Максимум 3 сокета на предмет.',
                'image'       => 'img/items/gems/socket_kit.png',
                'price'       => 5000,
                'is_sell'     => true,
                'is_active'   => true,
                'is_weight'   => false,
            ]
        );

        $this->command->info('Socket kit seeded.');
    }
}