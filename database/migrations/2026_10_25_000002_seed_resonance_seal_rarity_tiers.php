<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Печати Резонанса по редкости - по аналогии с уже существующей линейкой
 * самоцветов (Малый/обычный/Великий/Большой/Абсолютный, см. share_items
 * id 227-251). Базовая «Печать Резонанса» (id=1878, см. 2026_10_25_000001)
 * стала uncommon-тиром этой линейки; здесь добавляются остальные 4.
 *
 * Предполагаемая логика будущей механики точки (ещё не реализована кодом):
 * тир печати должен совпадать с тиром точимого артефакта (common-арт
 * требует Малую печать и т.д.).
 */
return new class extends Migration
{
    /** @var list<array{name: array{ru: string, uk: string}, description: array{ru: string, uk: string}, rarity: string, chestIds: list<int>, dropChance: int}> */
    private const SEALS = [
        [
            'name' => ['ru' => 'Малая Печать Резонанса', 'uk' => 'Мала Печатка Резонансу'],
            'description' => [
                'ru' => 'Простая печать из потускневшей бронзы — резонирует слабо, но для скромного артефакта хватит и этого. Используется кузнецом при точке артефактов.',
                'uk' => 'Проста печатка з потьмянілої бронзи — резонує слабко, але для скромного артефакта вистачить і цього. Використовується ковалем під час заточки артефактів.',
            ],
            'rarity' => 'common',
            'chestIds' => [1665, 1666, 1667, 1668, 1669, 1686],
            'dropChance' => 12,
        ],
        [
            'name' => ['ru' => 'Великая Печать Резонанса', 'uk' => 'Велика Печатка Резонансу'],
            'description' => [
                'ru' => 'Печать искусной работы — руны на её лице не просто мерцают, а отзываются эхом, стоит поднести её к артефакту. Используется кузнецом при точке артефактов.',
                'uk' => 'Печатка вправної роботи — руни на її обличчі не просто мерехтять, а озиваються луною, варто піднести її до артефакта. Використовується ковалем під час заточки артефактів.',
            ],
            'rarity' => 'rare',
            'chestIds' => [1672, 1673, 1674, 1675, 1676, 1677, 1688, 1689],
            'dropChance' => 7,
        ],
        [
            'name' => ['ru' => 'Большая Печать Резонанса', 'uk' => 'Велика Печатка Резонансу (епічна)'],
            'description' => [
                'ru' => 'Тяжёлая печать, выкованная для самых сильных артефактов, — резонанс от неё чувствуется, даже если держать её в закрытом кулаке. Используется кузнецом при точке артефактов.',
                'uk' => 'Важка печатка, викувана для найсильніших артефактів, — резонанс від неї відчувається, навіть якщо тримати її в закритому кулаці. Використовується ковалем під час заточки артефактів.',
            ],
            'rarity' => 'epic',
            'chestIds' => [1676, 1677, 1689],
            'dropChance' => 4,
        ],
        [
            'name' => ['ru' => 'Абсолютная Печать Резонанса', 'uk' => 'Абсолютна Печатка Резонансу'],
            'description' => [
                'ru' => 'Последняя из печатей — говорят, кузнец, создавший её, использовал резонанс самого мира, чтобы она вообще заработала. Используется кузнецом при точке артефактов.',
                'uk' => 'Остання з печаток — кажуть, коваль, що створив її, використав резонанс самого світу, щоб вона взагалі запрацювала. Використовується ковалем під час заточки артефактів.',
            ],
            'rarity' => 'legendary',
            'chestIds' => [1677, 1689],
            'dropChance' => 1,
        ],
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::SEALS as $seal) {
            $itemId = DB::table('share_items')->where('type', 'misc')->where('name->ru', $seal['name']['ru'])->value('id');

            if ($itemId === null) {
                $itemId = DB::table('share_items')->insertGetId([
                    'type' => 'misc',
                    'name' => json_encode($seal['name'], JSON_UNESCAPED_UNICODE),
                    'description' => json_encode($seal['description'], JSON_UNESCAPED_UNICODE),
                    'image' => null,
                    'is_two_hand' => 0,
                    'count_use' => 0,
                    'is_active' => 1,
                    'is_sell' => 1,
                    'is_auction_sellable' => 1,
                    'is_give' => 1,
                    'is_clan_warehouse_allowed' => 1,
                    'is_droppable' => 1,
                    'is_stackable' => 1,
                    'is_use' => 0,
                    'is_slot_usable' => 0,
                    'is_weight' => 0,
                    'price' => 0,
                    'diamond_price' => 0,
                    'break_crystal' => 0,
                    'gathering_speed_bonus_percent' => 0,
                    'gathering_double_chance_percent' => 0,
                    'rarity' => $seal['rarity'],
                    'upgrade_gold_cost' => 0,
                    'drop_direct_to_backpack' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($seal['chestIds'] as $chestId) {
                $exists = DB::table('share_item_has_items')
                    ->where('parent_item_id', $chestId)
                    ->where('share_item_id', $itemId)
                    ->exists();

                if (! $exists) {
                    DB::table('share_item_has_items')->insert([
                        'parent_item_id' => $chestId,
                        'share_item_id' => $itemId,
                        'min_count' => 1,
                        'max_count' => 1,
                        'drop_chance' => $seal['dropChance'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        foreach (self::SEALS as $seal) {
            $itemId = DB::table('share_items')->where('type', 'misc')->where('name->ru', $seal['name']['ru'])->value('id');

            if ($itemId !== null) {
                DB::table('share_item_has_items')->where('share_item_id', $itemId)->delete();
                DB::table('share_items')->where('id', $itemId)->delete();
            }
        }
    }
};
