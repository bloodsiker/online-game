<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * По прямому указанию пользователя для «Катакомб Жертвоприношений»:
 *   - Кровавый Оракул: «Страх» (effects.id=48, уже существует — та же
 *     запись, что и у Савана на кладбище) — 15% шанс, 120 сек.
 *   - Кровавый Палач: «Разрыв брони» (effects.id=17, уже используют
 *     Тролль/Огр/Циклоп/Могильный Страж) — 12% шанс, 5 сек.
 *   - Кровопийца-Инквизитор: встроенная пассивка «Вампиризм»
 *     (CombatPassiveType, monsters.passive_type/passive_value) — 15%,
 *     срабатывает каждый удар без броска шанса (см. MonsterAttackService::applyVampirism()).
 *   - Тень Приговорённого: встроенная пассивка «Отражение» — НОВАЯ для
 *     монстров (раньше был реализован только вампиризм), добавлена
 *     AttackService::applyMonsterReflectPassive() этим же патчем — 15%
 *     от нанесённого игроком урона возвращается ему же, тоже без шанса.
 */
class SacrificeCatacombsEffectsSeeder extends Seeder
{
    private const FEAR_EFFECT_ID = 48;

    private const ARMOR_BREAK_EFFECT_ID = 17;

    private const FEAR_CHANCE_PERCENT = 15;

    private const FEAR_DURATION_SECONDS = 120;

    private const ARMOR_BREAK_CHANCE_PERCENT = 12;

    private const ARMOR_BREAK_DURATION_SECONDS = 5;

    private const VAMPIRISM_PERCENT = 15;

    private const REFLECT_PERCENT = 15;

    public function run(): void
    {
        $this->attachMonsterEffect('Кровавый Оракул', self::FEAR_EFFECT_ID, self::FEAR_CHANCE_PERCENT, self::FEAR_DURATION_SECONDS, null);
        $this->attachMonsterEffect('Кровавый Палач', self::ARMOR_BREAK_EFFECT_ID, self::ARMOR_BREAK_CHANCE_PERCENT, self::ARMOR_BREAK_DURATION_SECONDS, null);

        $vampirismUpdated = LocalizedContentSeederQuery::forTable('monsters')
            ->where('name', 'Кровопийца-Инквизитор')
            ->update(['passive_type' => 'vampirism', 'passive_value' => self::VAMPIRISM_PERCENT, 'updated_at' => now()]);
        $this->command?->info("Вампиризм: проставлен {$vampirismUpdated} монстрам «Кровопийца-Инквизитор»");

        $reflectUpdated = LocalizedContentSeederQuery::forTable('monsters')
            ->where('name', 'Тень Приговорённого')
            ->update(['passive_type' => 'reflect', 'passive_value' => self::REFLECT_PERCENT, 'updated_at' => now()]);
        $this->command?->info("Отражение: проставлен {$reflectUpdated} монстрам «Тень Приговорённого»");
    }

    private function attachMonsterEffect(string $monsterName, int $effectId, int $chance, int $durationSeconds, ?int $powerPercent): void
    {
        $monsterIds = LocalizedContentSeederQuery::forTable('monsters')->where('name', $monsterName)->pluck('id');

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
                'chance' => $chance,
                'duration_seconds' => $durationSeconds,
                'power_percent' => $powerPercent,
                'trigger_on_hit' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        $this->command?->info("эффект id={$effectId} привязан к {$attached} монстрам «{$monsterName}» (из {$monsterIds->count()})");
    }
}
