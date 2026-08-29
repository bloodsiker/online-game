<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const PROFESSIONS = [
        'Травник' => 'Собирает растения и другие природные компоненты.',
        'Рыбак' => 'Добывает рыбу и водные ресурсы.',
        'Геолог' => 'Добывает руду, камни и минералы.',
    ];

    public function up(): void
    {
        foreach (self::PROFESSIONS as $name => $description) {
            $skillId = DB::table('skills')->where('name', $name)->value('id');

            if ($skillId === null) {
                $skillId = DB::table('skills')->insertGetId([
                    'name' => $name,
                    'type' => 'peaceful',
                    'description' => $description,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if (DB::table('skill_level_requirements')->where('skill_id', $skillId)->exists()) {
                continue;
            }

            $requirements = [];
            $previous = 0;
            for ($level = 1; $level <= 100; $level++) {
                $required = 100 * $level * $level;
                $requirements[] = [
                    'skill_id' => $skillId,
                    'lvl' => $level,
                    'exp_required' => $required,
                    'exp_diff' => $required - $previous,
                ];
                $previous = $required;
            }

            DB::table('skill_level_requirements')->insert($requirements);
        }
    }

    public function down(): void
    {
        // These skills may already contain player progress or have existed before
        // this migration. Preserve game data when rolling the schema back.
    }
};
