<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UpdateClanBossGoldBudgetsSeeder extends Seeder
{
    private const DUNGEON_ID = 3;

    /** @var array<int, int> */
    private const GOLD_BUDGETS = [
        1 => 27000,
        2 => 36000,
        3 => 60750,
        4 => 90000,
        5 => 132000,
        6 => 180000,
        7 => 243750,
        8 => 315000,
        9 => 405000,
        10 => 540000,
        11 => 701250,
        12 => 877500,
        13 => 1111500,
        14 => 1380000,
        15 => 1701000,
        16 => 2079000,
        17 => 2518500,
        18 => 3024000,
        19 => 3600000,
        20 => 4290000,
    ];

    public function run(): void
    {
        DB::transaction(function (): void {
            $stages = DB::table('dungeon_stages')
                ->where('dungeon_id', self::DUNGEON_ID)
                ->whereIn('number', array_keys(self::GOLD_BUDGETS))
                ->get(['id', 'number']);

            if ($stages->count() !== count(self::GOLD_BUDGETS)) {
                throw new RuntimeException(sprintf(
                    'Expected %d clan-boss stages in dungeon %d; found %d. No budgets were changed.',
                    count(self::GOLD_BUDGETS),
                    self::DUNGEON_ID,
                    $stages->count(),
                ));
            }

            foreach ($stages as $stage) {
                DB::table('dungeon_stages')
                    ->where('id', $stage->id)
                    ->update(['gold_budget' => self::GOLD_BUDGETS[(int) $stage->number]]);
            }
        });

        $this->command?->info('Обновлены бюджеты монет всех 20 этажей кланового данжа (+50%).');
    }
}
