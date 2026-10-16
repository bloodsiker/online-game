<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * По прямому указанию пользователя для «Заброшенного кладбища»:
 *   1. Кровавый Слуга получает «Кровавый Голод» — % от нанесённого удара
 *      возвращается ему как HP. Реализовано через универсальную пару
 *      monsters.passive_type/passive_value (переиспользует RunePassiveType,
 *      тот же enum, что и у игровых рун/встроенных пассивок предметов) —
 *      см. миграцию 2026_10_05_000001 и MonsterAttackService::applyPassive().
 *      Изначально было отдельной колонкой lifesteal_percent, но заменено
 *      на общий механизм, чтобы под каждую новую пассивку монстра не
 *      заводить новую колонку.
 *   2. Зомби / Мертвец / Могильный Упырь получают тематический для
 *      кладбища яд — «Могильная Гниль» (60 сек, 60% от макс. HP цели,
 *      15% шанс на удар). Новый отдельный Effect (НЕ переиспользует
 *      «Отравление от укуса» id=15 и «Подземный Яд» id=46 — у обоих
 *      свои уже закреплённые пользователи/параметры), тот же механизм
 *      TARGET_MAX_HP, что и «Подземный Яд» (см. CollapsedMineMonsterPoisonSeeder).
 */
class CemeteryMonsterEffectsSeeder extends Seeder
{
    private const LIFESTEAL_PERCENT = 15;

    private const ROT_CHANCE_PERCENT = 15;

    private const ROT_DURATION_SECONDS = 60;

    private const ROT_POWER_PERCENT_OF_MAX_HP = 60;

    public function run(): void
    {
        $this->applyLifestealToBloodServant();
        $this->applyGraveRotEffect();
    }

    private function applyLifestealToBloodServant(): void
    {
        $updated = LocalizedContentSeederQuery::forTable('monsters')
            ->where('name', 'Кровавый Слуга')
            ->update([
                'passive_type' => 'vampirism',
                'passive_value' => self::LIFESTEAL_PERCENT,
                'updated_at' => now(),
            ]);

        $percent = self::LIFESTEAL_PERCENT;
        $this->command?->info("Кровавый Голод: lifesteal_percent={$percent}% проставлен {$updated} монстрам «Кровавый Слуга»");
    }

    private function applyGraveRotEffect(): void
    {
        $effectId = LocalizedContentSeederQuery::forTable('effects')->where('slug', 'grave_rot')->value('id');

        if ($effectId === null) {
            $effectId = LocalizedContentSeederQuery::forTable('effects')->insertGetId([
                'name' => 'Могильная Гниль',
                'slug' => 'grave_rot',
                'type' => 'debuff',
                'active_type' => 'poison',
                'damage_scaling_type' => 'target_max_hp',
                'description' => 'Прикосновение мертвечины заражает рану тленом — плоть гниёт заживо, медленно забирая силы.',
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
            $this->command?->info("создан эффект «Могильная Гниль» (id={$effectId})");
        }

        $monsterIds = LocalizedContentSeederQuery::forTable('monsters')
            ->whereIn('name', ['Зомби', 'Мертвец', 'Могильный Упырь'])
            ->pluck('id');

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
                'chance' => self::ROT_CHANCE_PERCENT,
                'duration_seconds' => self::ROT_DURATION_SECONDS,
                'power_percent' => self::ROT_POWER_PERCENT_OF_MAX_HP,
                'trigger_on_hit' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        $this->command?->info("Могильная Гниль привязана к {$attached} монстрам (из {$monsterIds->count()}: Зомби/Мертвец/Могильный Упырь)");
    }
}
