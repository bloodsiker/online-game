<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Вешает ядовитый дебафф на удар всем 3 видам монстров «Заваленной
 * Штольни» (Обсидиановый Скорпион, Пещерный Паук, Чёрная Гадюка) — по
 * прямому указанию пользователя: 60 секунд, 60% от максимального HP
 * цели суммарно за это время, 15% шанс сработать на удар.
 *
 * НЕ переиспользует существующий effects.id=15 «Отравление от укуса» —
 * у него damage_scaling_type=hit_damage (% от урона удара) и его уже
 * используют 8 других монстров (Пепельный Скорпион, Могильная Оса,
 * Мантикора, Виверна и др.) с собственными мелкими значениями (4% за
 * тик) — смена scaling-типа на уровне effects сломала бы баланс всем им
 * разом, так как damage_scaling_type общий на весь эффект, а не per-pivot.
 *
 * Поэтому создан отдельный эффект с damage_scaling_type=target_max_hp —
 * именно этот режим считает ИТОГОВЫЙ урон как % от макс. HP цели за всю
 * длительность (см. MonsterOnHitEffectService::periodicDamage()), ровно
 * то, что нужно: 60% HP, разбитые на тики по 10 сек (6 тиков × 10% HP).
 */
class CollapsedMineMonsterPoisonSeeder extends Seeder
{
    private const MONSTER_NAMES = ['Обсидиановый Скорпион', 'Пещерный Паук', 'Чёрная Гадюка'];

    private const CHANCE_PERCENT = 15;

    private const DURATION_SECONDS = 60;

    private const POWER_PERCENT_OF_MAX_HP = 60;

    public function run(): void
    {
        $effectId = LocalizedContentSeederQuery::forTable('effects')->where('slug', 'underground_venom')->value('id');

        if ($effectId === null) {
            $effectId = LocalizedContentSeederQuery::forTable('effects')->insertGetId([
                'name' => 'Подземный Яд',
                'slug' => 'underground_venom',
                'type' => 'debuff',
                'active_type' => 'poison',
                'damage_scaling_type' => 'target_max_hp',
                'description' => 'Яд укуса подземной фауны — течёт по крови медленно, но за минуту забирает больше половины сил.',
                'image' => null,
                'chance' => 0,
                'is_stackable' => 0,
                'max_stacks' => 1,
                'tick_interval' => 10,
                'duration' => null,
                'value_per_tick' => 0,
                'stat_modifiers' => null,
                'is_dispellable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command?->info("создан эффект «Подземный Яд» (id={$effectId})");
        }

        $monsterIds = LocalizedContentSeederQuery::forTable('monsters')->whereIn('name', self::MONSTER_NAMES)->pluck('id');

        $attached = 0;
        foreach ($monsterIds as $monsterId) {
            $exists = DB::table('monster_effects')
                ->where('monster_id', $monsterId)
                ->where('effect_id', $effectId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('monster_effects')->insert([
                'monster_id' => $monsterId,
                'effect_id' => $effectId,
                'chance' => self::CHANCE_PERCENT,
                'duration_seconds' => self::DURATION_SECONDS,
                'power_percent' => self::POWER_PERCENT_OF_MAX_HP,
                'trigger_on_hit' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        $this->command?->info("CollapsedMineMonsterPoisonSeeder: эффект привязан к {$attached} монстрам (из {$monsterIds->count()})");
    }
}
