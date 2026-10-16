<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * «Саван» (Заброшенное кладбище) накладывает на удар дебафф «Страх» —
 * по прямому указанию пользователя: атака -15%, уворот -15%, крит -15%,
 * броня -10%, магическая атака -15%, магическое сопротивление -15%,
 * магический крит -15%.
 *
 * Реализовано как чистый стат-дебафф (active_type=null, damage_scaling_type
 * не задан — тут нет урона за тик), тот же механизм, что и у «Разрыв
 * брони» (id=17, -12% armor, Тролль/Огр/Циклоп) и «Слабость» (id=19,
 * -10% attack/magic_attack) — PlayerStatService::modifiersFromEntry()
 * читает stat_modifiers как [{type, value, is_percent}], канонические
 * ключи: attack (бьёт по всем 4 полям урона), magic_attack, dodge,
 * critical, armor.
 *
 * Шанс — по аналогии с «Разрыв брони» (10-12%), 15%. Длительность —
 * по прямому указанию пользователя: 120 секунд (значительно дольше
 * одиночных стат-дебаффов вроде «Разрыв брони», 5 сек — «Страх» задуман
 * как надолго выбивающий игрока из колеи эффект).
 */
class CemeteryFearEffectSeeder extends Seeder
{
    private const CHANCE_PERCENT = 15;

    private const DURATION_SECONDS = 120;

    /** @var array<int, array{type: string, value: int, is_percent: bool}> */
    private const STAT_MODIFIERS = [
        ['type' => 'attack', 'value' => -15, 'is_percent' => true],
        ['type' => 'magic_attack', 'value' => -15, 'is_percent' => true],
        ['type' => 'dodge', 'value' => -15, 'is_percent' => true],
        ['type' => 'critical', 'value' => -15, 'is_percent' => true],
        ['type' => 'armor', 'value' => -10, 'is_percent' => true],
        ['type' => 'magic_resistance', 'value' => -15, 'is_percent' => true],
        ['type' => 'magic_critical', 'value' => -15, 'is_percent' => true],
    ];

    public function run(): void
    {
        $effectId = LocalizedContentSeederQuery::forTable('effects')->where('slug', 'fear')->value('id');

        if ($effectId === null) {
            $effectId = LocalizedContentSeederQuery::forTable('effects')->insertGetId([
                'name' => 'Страх',
                'slug' => 'fear',
                'type' => 'debuff',
                'active_type' => null,
                'damage_scaling_type' => null,
                'description' => 'Леденящий ужас сковывает тело — руки дрожат, ноги слабеют, разум путается. Атака, уворот, крит, броня и магическая сила заметно падают.',
                'image' => null,
                'chance' => 0,
                'is_stackable' => 0,
                'max_stacks' => 1,
                'tick_interval' => 1,
                'duration' => null,
                'value_per_tick' => 0,
                'stat_modifiers' => json_encode(self::STAT_MODIFIERS),
                'is_dispellable' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $this->command?->info("создан эффект «Страх» (id={$effectId})");
        }

        $monsterIds = LocalizedContentSeederQuery::forTable('monsters')->where('name', 'Саван')->pluck('id');

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
                'power_percent' => null,
                'trigger_on_hit' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        $this->command?->info("Страх привязан к {$attached} монстрам (из {$monsterIds->count()}: Саван)");
    }
}
