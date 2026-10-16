<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Share\Infrastructure\Localization\LocalizedContentSeederQuery;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * По прямому указанию пользователя для Карригмора (map_id=15):
 *   - 1596: Храм (type=heal) — полностью восстанавливает HP и MP
 *     (FullHealStrategy, см. RecoveryStrategyFactory — общий для всех
 *     heal-структур механизм, доп. конфиг не нужен).
 *   - 1606: магазин оружия и снаряжения (категории «Оружие»+«Броня»,
 *     те же, что у «Магазин снаряжения» id=2 — эталонный шаблон).
 *   - 1608: магазин промышленных товаров (категория «Инструменты»).
 *   - Торговец Гуннар (НПС id=81, точка торговли Карригмора в
 *     «Вольном торге», trade_posts.id=3) переезжает с 1582 на 1629 —
 *     безопасно: trade_posts/trade_routes ссылаются по npc_id, а не по
 *     location_id, маршруты не завязаны на конкретные координаты.
 */
class CarrigmoreStructuresSeeder extends Seeder
{
    public function run(): void
    {
        $this->createTemple();
        $this->createWeaponShop();
        $this->createIndustrialShop();
        $this->relocateGunnar();
    }

    private function createTemple(): void
    {
        $exists = LocalizedContentSeederQuery::forTable('structures')->where('type', 'heal')->where('location_id', 1596)->exists();
        if ($exists) {
            $this->command?->info('Храм на 1596 уже существует, пропускаю');

            return;
        }

        LocalizedContentSeederQuery::forTable('structures')->insert([
            'type' => 'heal',
            'exchange_type' => null,
            'name' => 'Храм Возрождения',
            'description' => 'Невысокий храм на въезде в Карригмор — немногословные служители принимают каждого путника, не спрашивая ни веры, ни платы, и возвращают силы телу и духу.',
            'location_id' => 1596,
            'npc_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command?->info('создан Храм Возрождения (heal) на 1596');
    }

    private function createWeaponShop(): void
    {
        $exists = LocalizedContentSeederQuery::forTable('structures')->where('type', 'shop')->where('location_id', 1606)->exists();
        if ($exists) {
            $this->command?->info('магазин оружия на 1606 уже существует, пропускаю');

            return;
        }

        $npcId = LocalizedContentSeederQuery::forTable('npcs')->where('name', 'Оружейник Кадарн')->value('id');
        if ($npcId === null) {
            $npcId = LocalizedContentSeederQuery::forTable('npcs')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'location_id' => 1606,
                'is_active' => 1,
                'name' => 'Оружейник Кадарн',
                'description' => 'Бывший наёмник, осевший в Карригморе, — торгует оружием и доспехами, которые сам же когда-то и носил. Перед продажей каждый клинок проверяет на вес и баланс лично.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'hide_location' => 0,
            ]);
            $this->command?->info("создан НПС «Оружейник Кадарн» (id={$npcId})");
        }

        $structureId = LocalizedContentSeederQuery::forTable('structures')->insertGetId([
            'type' => 'shop',
            'exchange_type' => null,
            'name' => 'Оружейная Лавка',
            'description' => null,
            'location_id' => 1606,
            'npc_id' => $npcId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach (['Оружие', 'Броня'] as $categoryName) {
            $categoryId = LocalizedContentSeederQuery::forTable('share_structure_categories')->where('name', $categoryName)->value('id');
            DB::table('shop_categories')->insert([
                'structure_id' => $structureId,
                'share_structure_category_id' => $categoryId,
            ]);
        }

        $this->command?->info("создана «Оружейная Лавка» (id={$structureId}) на 1606, категории: Оружие+Броня");
    }

    private function createIndustrialShop(): void
    {
        $exists = LocalizedContentSeederQuery::forTable('structures')->where('type', 'shop')->where('location_id', 1608)->exists();
        if ($exists) {
            $this->command?->info('магазин промтоваров на 1608 уже существует, пропускаю');

            return;
        }

        $npcId = LocalizedContentSeederQuery::forTable('npcs')->where('name', 'Лавочник Свенд')->value('id');
        if ($npcId === null) {
            $npcId = LocalizedContentSeederQuery::forTable('npcs')->insertGetId([
                'uuid' => (string) Str::uuid(),
                'location_id' => 1608,
                'is_active' => 1,
                'name' => 'Лавочник Свенд',
                'description' => 'Держит лавку всякой хозяйственной мелочи — от гвоздей и верёвок до инструмента на любой случай. Если нужной вещи нет на полке, Свенд найдёт её под прилавком.',
                'image' => null,
                'created_at' => now(),
                'updated_at' => now(),
                'hide_location' => 0,
            ]);
            $this->command?->info("создан НПС «Лавочник Свенд» (id={$npcId})");
        }

        $structureId = LocalizedContentSeederQuery::forTable('structures')->insertGetId([
            'type' => 'shop',
            'exchange_type' => null,
            'name' => 'Лавка Промышленных Товаров',
            'description' => null,
            'location_id' => 1608,
            'npc_id' => $npcId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $categoryId = LocalizedContentSeederQuery::forTable('share_structure_categories')->where('name', 'Инструменты')->value('id');
        DB::table('shop_categories')->insert([
            'structure_id' => $structureId,
            'share_structure_category_id' => $categoryId,
        ]);

        $this->command?->info("создана «Лавка Промышленных Товаров» (id={$structureId}) на 1608, категория: Инструменты");
    }

    private function relocateGunnar(): void
    {
        $npc = LocalizedContentSeederQuery::forTable('npcs')->where('id', 81)->first();
        if ($npc === null) {
            $this->command?->warn('НПС id=81 (Торговец Гуннар) не найден');

            return;
        }

        if ((int) $npc->location_id === 1629) {
            $this->command?->info('Торговец Гуннар уже на 1629');

            return;
        }

        LocalizedContentSeederQuery::forTable('npcs')->where('id', 81)->update([
            'location_id' => 1629,
            'updated_at' => now(),
        ]);

        $this->command?->info("Торговец Гуннар перемещён: {$npc->location_id} -> 1629");
    }
}
