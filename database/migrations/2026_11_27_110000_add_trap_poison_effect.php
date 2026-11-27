<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const SLUG = 'trap_poison';

    public function up(): void
    {
        if (DB::table('effects')->where('slug', self::SLUG)->exists()) {
            return;
        }

        $name = ['ru' => 'Отравление', 'uk' => 'Отруєння'];
        $description = [
            'ru' => 'Отравленная вода медленно разъедает здоровье.',
            'uk' => 'Отруєна вода повільно підточує здоров’я.',
        ];

        $row = [
            'slug' => self::SLUG,
            'type' => 'debuff',
            'active_type' => 'poison',
            'damage_scaling_type' => 'target_max_hp',
            'chance' => 0,
            'is_stackable' => false,
            'max_stacks' => 1,
            'tick_interval' => 5,
            'value_per_tick' => 0,
            'is_dispellable' => true,
            'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
            'description' => json_encode($description, JSON_UNESCAPED_UNICODE),
            'created_at' => now(),
            'updated_at' => now(),
        ];
        foreach (['name_legacy' => $name['ru'], 'description_legacy' => $description['ru']] as $column => $value) {
            if (Schema::hasColumn('effects', $column)) {
                $row[$column] = $value;
            }
        }

        DB::table('effects')->insert($row);
    }

    public function down(): void
    {
        DB::table('effects')->where('slug', self::SLUG)->delete();
    }
};
