<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('effects')->where('slug', 'weather_fog')->exists()) {
            return;
        }

        $name = ['ru' => 'Туман', 'uk' => 'Туман'];
        $description = [
            'ru' => 'Густой туман сбивает с толку: заметно снижены уворот и крит, а противникам легче уклоняться от ваших ударов. Проходит, когда вы покидаете туман.',
            'uk' => 'Густий туман збиває з пантелику: помітно знижено ухилення та крит, а суперникам легше ухилятися від ваших ударів. Минає, коли ви залишаєте туман.',
        ];
        $row = [
            'slug' => 'weather_fog',
            'type' => 'debuff',
            'duration' => null,
            'stat_modifiers' => json_encode([
                ['type' => 'critical', 'value' => -25, 'is_percent' => true],
                ['type' => 'dodge', 'value' => -25, 'is_percent' => true],
            ]),
            'is_dispellable' => false,
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
        DB::table('effects')->where('slug', 'weather_fog')->delete();
    }
};
