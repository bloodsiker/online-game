<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\RuneRarity;
use App\Enums\ShareItemType;
use App\Models\Share\ShareItem;
use Illuminate\Database\Seeder;

/**
 * Runic archetypes (templates in share_items).
 * Stats are rolled at imbue time — these only define rarity and stat pool.
 *
 * Themes (stat pool):
 *   Огонь   → attack
 *   Лёд     → armor, hp_max
 *   Молния  → agility, critical
 *   Тень    → dodge, critical, attack
 *   Земля   → strength, armor, hp_max
 *   Свет    → hp_max, mp_max, intelligence
 *   Хаос    → null (all stats)
 *
 * Each theme exists in all 4 rarities = 7 × 4 = 28 rune types.
 * Plus 1 Рунный ключ.
 */
class RuneSeeder extends Seeder
{
    private const THEMES = [
        'Огонь'   => ['pool' => ['attack'],                          'img' => 'img/items/runes/fire.png'],
        'Лёд'     => ['pool' => ['armor', 'hp_max'],                 'img' => 'img/items/runes/ice.png'],
        'Молния'  => ['pool' => ['agility', 'critical'],             'img' => 'img/items/runes/lightning.png'],
        'Тень'    => ['pool' => ['dodge', 'critical', 'attack'],     'img' => 'img/items/runes/shadow.png'],
        'Земля'   => ['pool' => ['strength', 'armor', 'hp_max'],     'img' => 'img/items/runes/earth.png'],
        'Свет'    => ['pool' => ['hp_max', 'mp_max', 'intelligence'],'img' => 'img/items/runes/holy.png'],
        'Хаос'    => ['pool' => null,                                'img' => 'img/items/runes/chaos.png'],
    ];

    public function run(): void
    {
        $this->seedRunes();
        $this->seedRuneKey();
    }

    private function seedRunes(): void
    {
        $count = 0;

        foreach (self::THEMES as $theme => $def) {
            foreach (RuneRarity::cases() as $rarity) {
                $name = "Руна {$theme} ({$rarity->label()})";

                ShareItem::firstOrCreate(
                    [
                        'type' => ShareItemType::RUNE->value,
                        'name' => $name,
                    ],
                    [
                        'description'    => "Тематика: {$theme}. Редкость: {$rarity->label()}. Статы генерируются при вплавлении.",
                        'image'          => $def['img'],
                        'rune_rarity'    => $rarity->value,
                        'rune_stat_pool' => $def['pool'],
                        'price'          => $this->price($rarity),
                        'is_sell'        => true,
                        'is_active'      => true,
                        'is_weight'      => false,
                    ]
                );

                $count++;
            }
        }

        $this->command->info("Runes seeded: {$count} archetypes (7 themes × 4 rarities).");
    }

    private function seedRuneKey(): void
    {
        ShareItem::firstOrCreate(
            [
                'type' => ShareItemType::RUNE_KEY->value,
                'name' => 'Рунный ключ',
            ],
            [
                'description' => 'Открывает новый рунный слот на оружии или щите. Максимум 3 слота.',
                'image'       => 'img/items/runes/rune_key.png',
                'price'       => 3000,
                'is_sell'     => true,
                'is_active'   => true,
                'is_weight'   => false,
            ]
        );

        $this->command->info('Rune key seeded.');
    }

    private function price(RuneRarity $rarity): int
    {
        return match ($rarity) {
            RuneRarity::COMMON    => 300,
            RuneRarity::RARE      => 1000,
            RuneRarity::EPIC      => 3500,
            RuneRarity::LEGENDARY => 12000,
        };
    }
}