<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Привязывает дроп «Топор палача» (share_items.id=149 — уже существовал
 * в базе: common, двуручный, lvl20+, продаётся в оружейной лавке за 6749,
 * но ни разу не выпадал с монстров) к «Кровавый Палач» (map 6, Катакомбы
 * Жертвоприношений) — по прямому указанию пользователя: тематический
 * сигнатурный дроп для «комплекта Палача» (имя предмета буквально
 * совпадает с именем монстра).
 *
 * Первый в игре случай дропа оружия с монстра (до этого monster_has_items
 * содержал только resource/quest/chest — см. дискавери этого сидера),
 * поэтому шанс/количество не скопированы из существующей конвенции, а
 * подобраны по аналогии с редкими quest-дропами (сигнатурный предмет, не
 * расходник): 8% шанс, ровно 1 шт. (оружие не стакается, is_stackable=0).
 */
class SacrificeCatacombsExecutionerAxeDropSeeder extends Seeder
{
    private const AXE_ITEM_ID = 149;

    private const DROP_CHANCE_PERCENT = 8;

    public function run(): void
    {
        $monsterIds = DB::table('monsters')->where('name', 'Кровавый Палач')->pluck('id');

        $attached = 0;
        foreach ($monsterIds as $monsterId) {
            $exists = DB::table('monster_has_items')
                ->where('monster_id', $monsterId)
                ->where('share_item_id', self::AXE_ITEM_ID)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('monster_has_items')->insert([
                'monster_id' => $monsterId,
                'share_item_id' => self::AXE_ITEM_ID,
                'drop_chance' => self::DROP_CHANCE_PERCENT,
                'min_count' => 1,
                'max_count' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $attached++;
        }

        $this->command?->info("Топор палача (id=149): дроп привязан к {$attached} монстрам «Кровавый Палач» (из {$monsterIds->count()})");
    }
}
