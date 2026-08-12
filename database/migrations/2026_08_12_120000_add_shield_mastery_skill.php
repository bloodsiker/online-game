<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Боевой навык «Владение щитом» + привязка к уже существующим щитам.
 *
 * Навыки 1-3 создаются командой мира GenerateSeed::createSkills(), которая
 * прогоняется только на пустой базе, — поэтому четвёртый навык добавляется
 * миграцией, иначе он не появился бы на уже живых базах.
 *
 * Навык качается ЗА УСПЕШНЫЙ БЛОК (MonsterAttackService::gainShieldSkill), а не
 * за каждый удар, как оружейные. Блок срабатывает в block_chance случаев (27%
 * у всех текущих щитов), поэтому skill_exp у щитов ВЫШЕ оружейного: 7 против 2.
 * Итоговый темп: 7 × 0.27 ≈ 1.9 опыта за раунд против 2 у оружия — то есть
 * примерно паритет, навык не отстаёт от оружейного в разы.
 *
 * Как и у оружейных навыков, «Владение щитом» пока НЕ влияет на бой — навыки
 * в игре только гейтят предметы (ItemRequirementService) и идут в рейтинг.
 */
return new class extends Migration
{
    private const SKILL_NAME = 'Владение щитом';

    private const SKILL_TYPE = 'combat';

    /** Опыт навыка за один успешный блок — см. докблок про паритет с оружием */
    private const SHIELD_SKILL_EXP = 7;

    /** Те же параметры таблицы опыта, что в SkillLevelRequirementSeeder */
    private const MAX_LEVELS = 2000;

    private const BASE_EXP = 18;

    /**
     * Гейт по навыку получают только щиты от этого уровня и выше: «Кожаный щит»
     * (16 lvl) остаётся свободным, иначе навык было бы негде начать качать.
     */
    private const GATE_FROM_ITEM_LEVEL = 44;

    /**
     * Требуемый уровень навыка = доля от уровня предмета. Коэффициент взят из
     * единственного существующего скилл-гейта: «Полуторный меч» — предмет
     * 10 уровня, требует 18 навыка (см. StarterEquipmentSeeder::
     * TIER1_WEAPON_UPGRADE_SKILL_REQUIREMENT), то есть 1.8×.
     *
     * ВАЖНО: это перенос коэффициента, а не расчёт от реального темпа боя —
     * мобов выше 50 уровня пока нет, проверить нечем. Пересчитать вместе с
     * калибровкой SkillLevelRequirementSeeder, когда появятся.
     */
    private const GATE_SKILL_PER_ITEM_LEVEL = 1.8;

    public function up(): void
    {
        $skillId = $this->ensureSkill();

        $this->seedLevelRequirements($skillId);
        $this->attachToShields($skillId);
    }

    public function down(): void
    {
        $skillId = DB::table('skills')->where('name', self::SKILL_NAME)->value('id');

        if ($skillId === null) {
            return;
        }

        DB::table('share_item_requirements')->where('skill_id', $skillId)->delete();
        DB::table('share_items')->where('skill_id', $skillId)->update([
            'skill_id' => null,
            'skill_exp' => null,
        ]);

        // player_skills и skill_level_requirements уйдут каскадом по skill_id
        DB::table('skills')->where('id', $skillId)->delete();
    }

    private function ensureSkill(): int
    {
        $existing = DB::table('skills')->where('name', self::SKILL_NAME)->value('id');

        if ($existing !== null) {
            return (int) $existing;
        }

        return (int) DB::table('skills')->insertGetId([
            'name' => self::SKILL_NAME,
            'type' => self::SKILL_TYPE,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Та же линейная формула, что в SkillLevelRequirementSeeder — только для нового навыка */
    private function seedLevelRequirements(int $skillId): void
    {
        DB::table('skill_level_requirements')->where('skill_id', $skillId)->delete();

        $rows = [];
        $totalExp = 0;

        for ($level = 1; $level <= self::MAX_LEVELS; $level++) {
            $expDiff = (int) round(self::BASE_EXP * $level);
            $totalExp += $expDiff;

            $rows[] = [
                'skill_id' => $skillId,
                'lvl' => $level,
                'exp_required' => $totalExp,
                'exp_diff' => $expDiff,
            ];
        }

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('skill_level_requirements')->insert($chunk);
        }
    }

    /**
     * Все щиты (текущие и те, что уже успели появиться) начинают качать навык.
     * Отбор по type='shield', а не по списку id — чтобы не разъехаться с щитами,
     * добавленными через админку.
     */
    private function attachToShields(int $skillId): void
    {
        $shields = DB::table('share_items')
            ->where('type', 'shield')
            ->pluck('id');

        if ($shields->isEmpty()) {
            return;
        }

        DB::table('share_items')->whereIn('id', $shields)->update([
            'skill_id' => $skillId,
            'skill_exp' => self::SHIELD_SKILL_EXP,
        ]);

        foreach ($shields as $shieldId) {
            $this->addSkillGate($skillId, (int) $shieldId);
        }
    }

    private function addSkillGate(int $skillId, int $shieldId): void
    {
        $itemLevel = (int) DB::table('share_item_requirements')
            ->where('share_item_id', $shieldId)
            ->where('type', 'level')
            ->value('min_value');

        if ($itemLevel < self::GATE_FROM_ITEM_LEVEL) {
            return;
        }

        $alreadyGated = DB::table('share_item_requirements')
            ->where('share_item_id', $shieldId)
            ->where('type', 'skill')
            ->where('skill_id', $skillId)
            ->exists();

        if ($alreadyGated) {
            return;
        }

        DB::table('share_item_requirements')->insert([
            'share_item_id' => $shieldId,
            'type' => 'skill',
            'skill_id' => $skillId,
            'min_value' => (int) round($itemLevel * self::GATE_SKILL_PER_ITEM_LEVEL),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
};
