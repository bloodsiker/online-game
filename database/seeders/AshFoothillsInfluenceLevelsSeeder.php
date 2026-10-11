<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Лесенка репутации/влияния (5 тиров) для «Пепельные Предгорья» (map 16)
 * и «Пепельные Глубины» (map 17) — по прямому указанию пользователя.
 * Ранее сознательно не создавалась при постройке world-событий этих
 * карт (см. AshFoothillsWorldEventsSeeder) — отдельная подсистема наград.
 *
 * Точная копия конвенции остальных карт (см. map_influence_levels для
 * map_id=1/13 и т.д.): одни и те же 5 названий/порогов/очков рейтинга,
 * влияние копится в map_influences (per-player, без доп. настройки),
 * map_influence_levels.id ВСЕГДА совпадает с influence_medals.id — тот
 * же 1:1 паттерн, что и у всех прежних карт (id форсируется явно, а не
 * оставлен на волю параллельного auto_increment).
 *
 * map_influence_level_rewards/bonuses НЕ заполняются — как и у
 * «Гранитный Перевал», это необязательные доп. таблицы, у всех
 * существующих карт тоже пустые.
 */
class AshFoothillsInfluenceLevelsSeeder extends Seeder
{
    /** @var array<int, array{name: string, required_influence: int, rating_points: int}> */
    private const TIERS = [
        ['name' => 'Знакомый', 'required_influence' => 1000, 'rating_points' => 5],
        ['name' => 'Уважаемый', 'required_influence' => 5000, 'rating_points' => 10],
        ['name' => 'Союзник', 'required_influence' => 15000, 'rating_points' => 25],
        ['name' => 'Защитник', 'required_influence' => 50000, 'rating_points' => 50],
        ['name' => 'Герой территории', 'required_influence' => 100000, 'rating_points' => 100],
    ];

    /** @var array<int, array{map_id: int, map_name: string}> */
    private const MAPS = [
        ['map_id' => 16, 'map_name' => 'Пепельные Предгорья'],
        ['map_id' => 17, 'map_name' => 'Пепельные Глубины'],
    ];

    public function run(): void
    {
        if (DB::table('map_influence_levels')->where('map_id', 16)->exists()) {
            $this->command?->info('AshFoothillsInfluenceLevelsSeeder: уже существует, пропускаю.');

            return;
        }

        $now = now();
        $created = 0;

        foreach (self::MAPS as $map) {
            foreach (self::TIERS as $index => $tier) {
                $level = $index + 1;

                $medalId = DB::table('influence_medals')->insertGetId([
                    'map_id' => $map['map_id'],
                    'name' => $this->medalName($tier['name']).' — '.$map['map_name'],
                    'description' => sprintf('Награда за достижение уровня влияния «%s» на территории «%s».', $tier['name'], $map['map_name']),
                    'icon' => null,
                    'rating_points' => $tier['rating_points'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                DB::table('map_influence_levels')->insert([
                    'id' => $medalId,
                    'map_id' => $map['map_id'],
                    'level' => $level,
                    'name' => $tier['name'],
                    'required_influence' => $tier['required_influence'],
                    'influence_medal_id' => $medalId,
                    'is_active' => 1,
                    'sort_order' => $level,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $created++;
            }
        }

        $this->command?->info("AshFoothillsInfluenceLevelsSeeder: создано {$created} тиров влияния (2 карты × 5 тиров)");
    }

    private function medalName(string $tierName): string
    {
        return match ($tierName) {
            'Знакомый' => 'Знак знакомства',
            'Уважаемый' => 'Медаль уважения',
            'Союзник' => 'Знак союзника',
            'Защитник' => 'Медаль защитника',
            'Герой территории' => 'Герб героя',
            default => $tierName,
        };
    }
}
