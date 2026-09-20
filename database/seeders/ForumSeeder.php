<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ForumSeeder extends Seeder
{
    private const SECTIONS = [
        ['name' => 'Объявления', 'slug' => 'obyavleniya', 'description' => 'Новости и объявления администрации. Писать могут только администраторы.', 'sort_order' => 10],
        ['name' => 'Общий раздел', 'slug' => 'obshchij-razdel', 'description' => 'Свободное общение игроков.', 'sort_order' => 20],
        ['name' => 'Помощь новичкам', 'slug' => 'pomoshch-novichkam', 'description' => 'Вопросы по игре и ответы опытных игроков.', 'sort_order' => 30],
        ['name' => 'Кланы', 'slug' => 'klany', 'description' => 'Дипломатия, набор и жизнь кланов.', 'sort_order' => 40],
    ];

    public function run(): void
    {
        foreach (self::SECTIONS as $section) {
            DB::table('forum_sections')->updateOrInsert(
                ['slug' => $section['slug']],
                $section + ['is_active' => true, 'updated_at' => now()],
            );
        }
    }
}
