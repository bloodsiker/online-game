<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * После замены требований уровня на «Колдовство» у части стартовых
     * заклинаний оказалось два требования к одному навыку. Оба означают
     * «уровень навыка не ниже N», поэтому достаточно оставить самое высокое.
     */
    public function up(): void
    {
        $requirements = DB::table('magic_skill_requirements')
            ->where('type', 'skill')
            ->orderBy('magic_skill_id')
            ->orderBy('skill_id')
            ->orderByDesc('min_value')
            ->orderBy('id')
            ->get()
            ->groupBy(fn (object $requirement): string => $requirement->magic_skill_id.':'.$requirement->skill_id);

        foreach ($requirements as $group) {
            $duplicateIds = $group->skip(1)->pluck('id');

            if ($duplicateIds->isNotEmpty()) {
                DB::table('magic_skill_requirements')->whereIn('id', $duplicateIds)->delete();
            }
        }
    }

    public function down(): void
    {
        // Удалённые дубли не восстанавливаются: они не влияли на результат
        // проверки и могли быть вручную отредактированы до миграции.
    }
};
