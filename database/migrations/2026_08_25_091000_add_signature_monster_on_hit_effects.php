<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, array<string, array{chance: float, power: float|null}>> */
    private const ASSIGNMENTS = [
        'monster_bleed' => [
            'Волк' => ['chance' => 10, 'power' => 7],
            'Лесная Рысь' => ['chance' => 12, 'power' => 7],
            'Степной Шакал' => ['chance' => 12, 'power' => 7],
            'Свирепая Гиена' => ['chance' => 15, 'power' => 7],
            'Терновый Волк' => ['chance' => 15, 'power' => 7],
            'Кровавый Орёл' => ['chance' => 15, 'power' => 7],
            'Кровавый Гончий' => ['chance' => 18, 'power' => 7],
        ],
        'monster_poison' => [
            'Пепельный Скорпион' => ['chance' => 12, 'power' => 4],
            'Могильная Оса' => ['chance' => 12, 'power' => 4],
            'Могильный Паук-Трупоед' => ['chance' => 15, 'power' => 4],
            'Мантикора' => ['chance' => 15, 'power' => 4],
            'Виверна' => ['chance' => 15, 'power' => 4],
        ],
        'monster_burn' => [
            'Пепельный Страж' => ['chance' => 15, 'power' => 6],
            'Молодой дракон' => ['chance' => 15, 'power' => 6],
            'Древний дракон' => ['chance' => 20, 'power' => 6],
        ],
        'monster_armor_break' => [
            'Тролль' => ['chance' => 10, 'power' => null],
            'Огр' => ['chance' => 10, 'power' => null],
            'Циклоп' => ['chance' => 12, 'power' => null],
            'Каменный Голем' => ['chance' => 12, 'power' => null],
            'Глиняный Голем' => ['chance' => 12, 'power' => null],
            'Гранитный Голем' => ['chance' => 15, 'power' => null],
        ],
        'monster_chill' => [
            'Ледяной Волк' => ['chance' => 15, 'power' => null],
            'Ледяной великан' => ['chance' => 18, 'power' => null],
        ],
        'monster_weakness' => [
            'Дух Павшего Знаменосца' => ['chance' => 12, 'power' => null],
            'Лич Некромант' => ['chance' => 15, 'power' => null],
            'Плакальщица' => ['chance' => 12, 'power' => null],
            'Шёпот Тьмы' => ['chance' => 15, 'power' => null],
            'Кровавый Оракул' => ['chance' => 18, 'power' => null],
            'Тень Приговорённого' => ['chance' => 18, 'power' => null],
        ],
    ];

    public function up(): void
    {
        $now = now();
        $definitions = [
            'monster_bleed' => [
                'name' => 'Кровотечение',
                'active_type' => 'bleed',
                'duration' => 3,
                'tick_interval' => 1,
                'description' => 'Рваная рана наносит урон каждую секунду.',
                'stat_modifiers' => null,
            ],
            'monster_poison' => [
                'name' => 'Отравление от укуса',
                'active_type' => 'poison',
                'duration' => 6,
                'tick_interval' => 1,
                'description' => 'Яд продолжает наносить урон после попадания.',
                'stat_modifiers' => null,
            ],
            'monster_burn' => [
                'name' => 'Ожог от существа',
                'active_type' => 'burn',
                'duration' => 4,
                'tick_interval' => 1,
                'description' => 'Пламя обжигает цель каждую секунду.',
                'stat_modifiers' => null,
            ],
            'monster_armor_break' => [
                'name' => 'Разрыв брони',
                'active_type' => null,
                'duration' => 5,
                'tick_interval' => 1,
                'description' => 'Тяжёлый удар уменьшает броню на 12%.',
                'stat_modifiers' => json_encode([['type' => 'armor', 'value' => -12, 'is_percent' => true]]),
            ],
            'monster_chill' => [
                'name' => 'Обморожение',
                'active_type' => null,
                'duration' => 5,
                'tick_interval' => 1,
                'description' => 'Холод уменьшает уворот на 12%.',
                'stat_modifiers' => json_encode([['type' => 'dodge', 'value' => -12, 'is_percent' => true]]),
            ],
            'monster_weakness' => [
                'name' => 'Слабость',
                'active_type' => null,
                'duration' => 6,
                'tick_interval' => 1,
                'description' => 'Проклятие уменьшает физическую и магическую атаку на 10%.',
                'stat_modifiers' => json_encode([
                    ['type' => 'attack', 'value' => -10, 'is_percent' => true],
                    ['type' => 'magic_attack', 'value' => -10, 'is_percent' => true],
                ]),
            ],
        ];

        foreach ($definitions as $slug => $definition) {
            DB::table('effects')->updateOrInsert(
                ['slug' => $slug],
                [
                    ...$definition,
                    'type' => 'debuff',
                    'chance' => 0,
                    'value_per_tick' => 0,
                    'is_stackable' => false,
                    'max_stacks' => 1,
                    'is_dispellable' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }

        $effectIds = DB::table('effects')
            ->whereIn('slug', array_keys(self::ASSIGNMENTS))
            ->pluck('id', 'slug');

        foreach (self::ASSIGNMENTS as $slug => $monsters) {
            $effectId = $effectIds[$slug] ?? null;
            if ($effectId === null) {
                continue;
            }

            foreach ($monsters as $monsterName => $settings) {
                $monsterIds = DB::table('monsters')->where('name', $monsterName)->pluck('id');

                foreach ($monsterIds as $monsterId) {
                    DB::table('monster_effects')->updateOrInsert(
                        ['monster_id' => $monsterId, 'effect_id' => $effectId],
                        [
                            'chance' => $settings['chance'],
                            'power_percent' => $settings['power'],
                            'created_at' => $now,
                            'updated_at' => $now,
                        ],
                    );
                }
            }
        }
    }

    public function down(): void
    {
        $effectIds = DB::table('effects')
            ->whereIn('slug', array_keys(self::ASSIGNMENTS))
            ->pluck('id');

        DB::table('monster_effects')->whereIn('effect_id', $effectIds)->delete();
        DB::table('effects')->whereIn('id', $effectIds)->delete();
    }
};
