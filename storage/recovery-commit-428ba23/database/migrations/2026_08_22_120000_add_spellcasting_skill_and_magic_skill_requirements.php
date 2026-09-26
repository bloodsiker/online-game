<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Навык «Колдовство» (качается за успешное применение атакующего заклинания,
 * см. MagicAttackStrategy::getHits() → AttackService::execute() →
 * PlayerSkillService::gainExperienceSkill(), тот же путь, что у оружейных
 * навыков) + требования на изучение/экипировку заклинаний
 * (magic_skill_requirements, зеркалит share_item_requirements: level/stat/skill).
 */
return new class extends Migration
{
    private const SKILL_NAME = 'Колдовство';

    private const SKILL_TYPE = 'magic';

    /** Те же параметры таблицы опыта, что у share_item_requirements навыков (см. 2026_08_12_120000_add_shield_mastery_skill.php) */
    private const MAX_LEVELS = 2000;

    private const BASE_EXP = 18;

    public function up(): void
    {
        Schema::create('magic_skill_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->enum('type', ['level', 'stat', 'skill']);
            $table->string('stat_key', 20)->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->unsignedSmallInteger('min_value');
            $table->timestamps();

            $table->foreign('magic_skill_id')->references('id')->on('magic_skills')->onDelete('cascade');
            $table->foreign('skill_id')->references('id')->on('skills')->onDelete('cascade');
        });

        Schema::table('magic_skills', function (Blueprint $table) {
            $table->foreignId('skill_id')->after('level')->nullable()->constrained('skills')->nullOnDelete();
        });

        $this->seedLevelRequirements($this->ensureSkill());
    }

    public function down(): void
    {
        Schema::dropIfExists('magic_skill_requirements');

        Schema::table('magic_skills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('skill_id');
        });

        $skillId = DB::table('skills')->where('name', self::SKILL_NAME)->value('id');

        if ($skillId !== null) {
            // player_skills и skill_level_requirements уйдут каскадом по skill_id
            DB::table('skills')->where('id', $skillId)->delete();
        }
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

    private function seedLevelRequirements(int $skillId): void
    {
        if (DB::table('skill_level_requirements')->where('skill_id', $skillId)->exists()) {
            return;
        }

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
};
