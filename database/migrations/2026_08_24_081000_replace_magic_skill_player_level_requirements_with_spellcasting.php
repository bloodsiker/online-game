<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Требования типа level раньше сравнивались с уровнем персонажа. Магические
     * книги должны зависеть от навыка «Колдовство», поэтому сохраняем прежнее
     * минимальное значение, но переводим его на skill_id заклинания.
     */
    public function up(): void
    {
        $spellcastingSkillId = DB::table('skills')->where('name', 'Колдовство')->value('id');

        DB::table('magic_skill_requirements as requirement')
            ->join('magic_skills as magic_skill', 'magic_skill.id', '=', 'requirement.magic_skill_id')
            ->where('requirement.type', 'level')
            ->select('requirement.id', 'magic_skill.skill_id')
            ->orderBy('requirement.id')
            ->each(function (object $requirement) use ($spellcastingSkillId): void {
                $skillId = $requirement->skill_id ?? $spellcastingSkillId;

                if ($skillId === null) {
                    DB::table('magic_skill_requirements')->where('id', $requirement->id)->delete();

                    return;
                }

                DB::table('magic_skill_requirements')->where('id', $requirement->id)->update([
                    'type' => 'skill',
                    'stat_key' => null,
                    'skill_id' => $skillId,
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Обратное преобразование невозможно: после миграции skill-требования
        // могут быть отредактированы администратором.
    }
};
